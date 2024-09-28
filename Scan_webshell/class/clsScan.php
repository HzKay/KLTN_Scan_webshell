<?php
    class clsScan 
    {
        private $noDisplay = array(".", "..",);
        private $path = "../";
        private $errorList = array(
            "Đường dẫn không hợp lệ hoặc không tồn tại",
            "Đường dẫn không được bắt đầu bằng /",
        );
        public $fileListGlobal = array();
        private $keywords = array (
            // Các hàm nguy hiểm
            'eval ()',
            'exec ()',
            'system ()',
            'shell_exec ()',
            'passthru ()',
            'popen ()',
            'pclose ()',
            'proc_open ()',
            'unlink ()',
            'file_put_contents ()',
            'file_get_contents ()',
            'copy ()',
            'include',
            'require',
            'register_globals',
            'magic_quotes_gpc',
            '$GLOBALS',
            '$_COOKIE',
            '$_SESSION',
            '$_SERVER',

        );
        
        public function removeFolderInList($folderName)
        {
            if (!in_array($folderName, $this->noDisplay))
            {
                return $folderName;
            }
        }

        public function filterNoDisplay($filesInDir)
        {
            // Sử dụng array_filter với hàm callback là removeFolder
            return array_filter($filesInDir, array($this, 'removeFolderInList'));
        }

        public function checkValidFolderLocation ($url)
        {
            $paths = preg_split('#[\\\\/]+#', $url);
            $level = 0;

            if ($paths[0] == "" && (count($paths)>1)) 
            {
                return -1;
            }

            foreach ($paths as $path)
            {
                if($path === "..")
                {
                    --$level;
                } elseif ($path != ".")
                {
                    ++$level;
                }

                if ($level < 0) {
                    return 0;
                }
            }

            return 1;
        }

        public function checkValidUrl($url)
        {
            if (!is_dir($url))
            {
                return -1;
            }

            return 1;
        }
        
        public function scanFolder($url)
        {            
            $filesInDir = scandir($url, SCANDIR_SORT_NONE);
                
            if ($filesInDir == false) 
            {
                return false;
            }

            $filesInDir = $this->filterNoDisplay($filesInDir);
            return $filesInDir;
        }

        function getFullPath($dir) {
            $files = [];
        
            // Sử dụng RecursiveDirectoryIterator để duyệt thư mục
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        
            foreach ($iterator as $file) {
                // Chỉ lấy các tệp, bỏ qua các thư mục
                if (is_File($file)) {
                    $files[] = str_replace('\\', '/', $file->getPathname());
                }
            }
        
            return $files;
        }
        
        public function checkUrlInput ($url) 
        {
            $newUrl = $this->path .$url;
            $isValidUrl = $this->checkValidUrl($newUrl);
            $isValidLocation = $this->checkValidFolderLocation($url);

            $errorCode = -1;

            if ($isValidUrl != 1)
            {
                $errorCode = 0;
            } elseif ($isValidLocation != 1)
            {
                switch($isValidLocation)
                {
                    case 0:
                        $errorCode = 0;
                        break;
                    case -1:
                        $errorCode = 1;
                        break;
                }
            } 

            return $errorCode;
        }
        
        public function getAllFiles ($url)
        {
            $errorCode = $this->checkUrlInput($url);
            $newUrl = $this->path . $url;

            if ($errorCode == -1) {
                $filesInDir = $this->getFullPath($newUrl);
                return $filesInDir;
            }

            return $this->errorList[$errorCode];
        }

        public function getAllForlder ($url)
        {
            $directories = [];
            $dir = new DirectoryIterator($url);

            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                    $folderName = $fileinfo->getFilename();
                    $path = $url."/".$folderName;
                    $directories[] = $path;
                    $directories = array_merge($directories, $this->getAllForlder($path));
                }
            }

            return $directories;
        }

        public function normalizePath ($folderList)
        {
            $result = array();

            foreach ($folderList as $item)
            {
                $result[] = substr($item, 3);
            }

            return $result;
        }

        public function findFolderLocation($findFolderName)
        {
            $directories = $this->getAllForlder("..");
            

            $folderList = array_filter($directories, function ($folderPath) use ($findFolderName) {
                return stripos($folderPath, $findFolderName) !== false;
            });

            $folderList = $this->normalizePath($folderList);

            return $folderList;
        }

        public function getFileContent ($filePath)
        {
            $contentOfFide = file_get_contents($filePath);

            if ($contentOfFide === false) {
                return -1;
            } else {
                return $contentOfFide;
            }
        }

        public function validFileContent ($file)
        {
            $fileContent = $this->getFileContent($file);
            $score = 0;

            if ($fileContent === false) {
                return -1;
            }

            foreach ($this->keywords as $keyword)
            {
                // Kiểm tra xem từ khóa có tồn tại trong nội dung không
                if (strpos($fileContent, $keyword) !== false) {
                    $score = 1;
                }
            }   

            return $score; 
        }

        public function checkFilesContent ($filePath)
        {
            $resultScan = array();
            $originalFilePath = $this->getAllFiles($filePath);
            
            if (gettype($originalFilePath)  == "string")
            {
                echo $originalFilePath;
                die();
            }

            foreach ($originalFilePath as $file)
            {
                $result = $this->validFileContent($file);

                switch ($result)
                {
                    case -1: 
                        $resultScan[] = "Unknow";
                        break;
                    case 0: 
                        $resultScan[] = "Normal";
                        break;
                    default: 
                        $resultScan[] = "Webshell";
                        break;
                }
            }
            
            
            $this->fileListGlobal = $this->normalizePath($originalFilePath);            
            return $resultScan;
        }
    }
?>