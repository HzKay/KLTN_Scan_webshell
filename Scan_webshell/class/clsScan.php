<?php
    include ("./class/clsStatus.php");
    class clsScan extends statusScan 
    {
        private $noDisplay = array(".", "..",);
        private $path = "../";
        private $basePath = "";
        private $quaranFolder = "./quarantine/";
        private $logFolder = "./log/";
        private $errorList = array(
            "Đường dẫn không hợp lệ hoặc không tồn tại",
            "Đường dẫn không được bắt đầu bằng /",
        );
        public $fileListGlobal = array();
        public $webshellList = array();
        public $normalFile = array();
        private $hash_alg = "sha256";

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

        public function makeFitFilePath ($filePath)
        {
            $search = array('\\', '/');
            $newFilePath = str_replace($search, DIRECTORY_SEPARATOR, $filePath);
            return $newFilePath;
        }

        public function getFullPath($dir) {
            $files = array();
            

            $baseDirectory = dirname(dirname(__DIR__));
            $this->basePath = $baseDirectory . DIRECTORY_SEPARATOR;
            $realPath = $this->basePath . $dir;
            $realPath = $this->makeFitFilePath($realPath);
            
            // Sử dụng RecursiveDirectoryIterator để duyệt thư mục
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($realPath));
            
            foreach ($iterator as $file) {
                // Chỉ lấy các tệp, bỏ qua các thư mục
                if (is_File($file)) {
                    $files[] = $file->getPathname();
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
    
            if ($errorCode == -1) {
                $filesInDir = $this->getFullPath($url);
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

        public function normalizePath ($folderList, $mode = 1)
        {
            $result = array();

            foreach ($folderList as $item)
            {
                if ($mode == 1) 
                {
                    $result[] = substr($item, 3);
                } else {
                    $result[] = str_replace($this->basePath, '', $item);
                }
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
            include_once("./class/clsSign.php"); 
            
            //  Khởi tạo đối tượng Signatures
            $p = new Signatures();
           
            $fileContent = $this->getFileContent(filePath: $file);

            if ($fileContent === false) {
                return -1;
            }

            $temp = $p->getAll();
            $signList = array();
            foreach($temp as $sign) {
                $reg = '#' . $sign . '#smiS';
                $result = preg_match_all($reg, $fileContent, $ouput, PREG_OFFSET_CAPTURE);

                if ($result >= 0)
                {
                    foreach ($ouput[0] as $oup)
                    {
                        $oup[0] = htmlspecialchars($oup[0],ENT_QUOTES);
                        $signList[] =  $oup;
                    }
                }
            }

            return $signList; 
        }

        public function createHashCode ($filePath)
        {
            $hashCode = hash_file($this->hash_alg, $filePath);
            return $hashCode;
        }

        public function compareValue ($valueOne, $valueTwo)
        {
            $resultCompare = hash_equals($valueOne, $valueTwo);
            return $resultCompare;
        }

        public function findHashInDB($hash)
        {
            require_once("./model/mScan.php");
            $mScan = new mScan();

            $isAvailable = $mScan->findHash($hash);

            return $isAvailable;
        }

        public function importToCSV ( $filePath ,$hashCode, $status=false)
        {
            $bytesWrite = 0123;

            if ($status == true) 
            {
                $time = date("Y/m/d");
                $header = $time . ", filePath, hashCode \n";
                $bytesWrite = file_put_contents("./tennc.csv", (string)$header, FILE_APPEND);
            } else {
                $data = "0, " . $filePath . ", " . $hashCode . "\n";
                $bytesWrite = file_put_contents("./tennc.csv", (string)$data, FILE_APPEND);
            }

            return $bytesWrite;
        }

        public function importHash($hash, $family)
        {
            include ('./model/mScan.php');
            $mScan = new mScan();
            $mScan->importHashToDB($family, $hash);
        }        

        public function storeFiles ($file, $location) 
        {
            switch($location)
            {
                case 0: {
                    $this->normalFile[] = $file;
                    break;
                }

                default:{
                    $this->webshellList[] = $file; 
                    break;
                }
            }
        }

        public function checkFilesContent ($filePath)
        {
            include_once("./object/objectFile.php");
            $newFilePath = array();
            $originalFilePath = $this->getAllFiles($filePath);
            $currentFile = 0;
            
            if (gettype($originalFilePath)  == "string")
            { 
                echo $originalFilePath;
                die();
            }

            $this->setTotalFiles(count($originalFilePath));
            $this->setScanLocation($filePath);

            foreach ($originalFilePath as $file)
            {
                $objectFile = new ObjectFile();
                $hashFile = $this->createHashCode($file);
                // $isAvail = $this->findHashInDB($hashFile);
                $isAvail = -1;
                $currentFile++;

                if ($isAvail != -1) {
                    $newFilePath[] = $file;
                }
                else {    
                    $signList = $this->validFileContent($file);
                    $numSign = count($signList);
                    $type = "Normal";
                    $fileSize = filesize($file);

                    if ($numSign > 0)
                    {
                        $type = "Webshell";
                        $newFilePath[] = $file;                        
                    }
                    $filePath = str_replace($this->basePath, '', $file);

                    $objectFile->setInfo($filePath, $fileSize, $type, $hashFile, $signList);  
                    $this->storeFiles($objectFile, $numSign);
                }  
                
                $this->setCurrentProcess($currentFile);
            }

            $this->fileListGlobal = $this->normalizePath($newFilePath, 0);      
            $this->test();      
            return $this->webshellList;
        }        



        public function quaranFile($fileList, $action)
        {
            $result = array();

            for ($index = 0; $index < count($fileList); $index++)
            {
                if ($action[$index] == 1)
                {
                    $results[$index] = true;
                } elseif ($action[$index] == 2) {
                    $filePath = $fileList[$index];
                    $filePathSplit = preg_split('#[\\\\/]+#', $filePath);
                    $fileName = end($filePathSplit);
                    $fileLocation = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . $filePath;
                    $fileLocation = $this->makeFitFilePath($fileLocation);
                    
                    // Cách ly file (quarantine file)
                    $newLocation = $this->quaranFolder . time() . "-" . $fileName . ".txt";
                    
                    $isMove = rename($fileLocation, $newLocation);
                    $isLog = $this->addLogFile($filePath, $newLocation);
        
                    if ($isLog == 0)
                    {
                        $results[$index] = $isMove;
                    } else {
                        $results[$index] = $isLog;
                    }
                }             
            }

            return $result;
        }

        private function addLogFile ($oldPath, $newPath)
        {
            $currentTime = time();
            $dateLog = date('Y-m-d H:i:s', $currentTime);
            $formatLog = "[{$dateLog}] Move \"{$oldPath}\" to \"{$newPath}\"\n";
            $logPath = $this->logFolder . date('Y-m-d', $currentTime) . "-log.txt";

            $fileLog = fopen($logPath, "a");
            if (!$fileLog) {
                // Không thể mở file
                return -21;
           }
       
           if (fwrite($fileLog, $formatLog) === FALSE) {
                // Không thể ghi nội dung vào file
                return -22;
           }
       
           fclose($fileLog);
           return 0;
        }

        public function getScanHistory()
        {
            require_once("./model/mScan.php");

            $mScan = new mScan();
            $dates = array();
            $numShell = array();
            $resultScan = $mScan->getScanHistory();

            while ($result = mysqli_fetch_array($resultScan))
            {
                $dates[] = $result['ngayThucHien'];
                $numShell[] = $result['soWebshell'];
            }
            
            $result = array("ngayThucHien" => $dates, "soWebshell" => $numShell);
            return $result;
        }

        public function getFilesRecentScan()
        {
            require_once("./model/mScan.php");
            require_once("./object/objectResultScan.php");

            $mScan = new mScan();
            $scanRecent = array();
            $resultScan = $mScan->getFilesRecentScan();

            while ($result = mysqli_fetch_array($resultScan))
            {             
                $scanRecent[] = new ObjectResultScan($result["TenTep"], $result["ViTriTep"], $result["NgayTaiLen"], $result["MaBam"], $result["KetQua"], $result["HanhDong"]);
            }

            return $scanRecent;
        }

        public function calcDashboard($files)
        {
            $totalFile = count($files);
            $numShell = 0;
            $numQuarant = 0;
            
            foreach ($files as $file)
            {
                if ($file->ketQua == 1)
                {
                    $numShell += 1;
                } 
                if ($file->hanhDong == 2)
                {
                    $numQuarant += 1;
                } 
            }

            $result = array("totalFile" => $totalFile, "shell" => $numShell, "quarant" => $numQuarant);
            return $result;
        }

        public function getDateRecentScan()
        {
            require_once("./model/mScan.php");
            $mScan = new mScan();
            $result = $mScan->getDateRecentScan();

            return $result;
        }

        public function getInfoDashboard()
        {
            $date = $this->getDateRecentScan();
            $files = $this->getFilesRecentScan();
            $number = $this->calcDashboard($files);
            $scanHist = $this->getScanHistory();
            $normalFile = $number["totalFile"] - $number["shell"];
            $scanDays = json_encode(implode(', ', $scanHist["ngayThucHien"]));
            $numShell = json_encode(implode(', ', $scanHist["soWebshell"]));

            echo "
                <script>
                    const ctx = document.getElementById('lineChart');
                    const pieCtx = document.getElementById('pieChart');
                    const time = document.getElementById('scan-time');
                    const quarant = document.getElementById('quarant-file');
                    const shell = document.getElementById('shell-file');
                    const total = document.getElementById('total-file');
                    let delayed;
                    const scanDates = {$scanDays};
                    const webshellCounts = {$numShell};

                    total.innerHTML = '{$number["totalFile"]}';
                    shell.innerHTML = '{$number["shell"]}';
                    quarant.innerHTML = '{$number["quarant"]}';
                    time.innerHTML = '{$date}';

                    

                    new Chart(pieCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Webshell', 'Bình thường'],
                            datasets: [{
                                label: ' Số lượng file',
                                data: [{$number["shell"]}, {$normalFile}],
                                borderWidth: 1,
                                backgroundColor: ['#CB4335', '#27AE60'],
                            }]
                        }
                    });

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                        labels: scanDates.split(', '),
                        datasets: [{
                            label: ' Số lượng webshell',
                            data: webshellCounts.split(', '),
                            borderWidth: 2,
                            borderColor: ['#27AE60']
                        }]
                        },
                        options: {
                            animation: {
                                onComplete: () => {
                                    delayed = true;
                                },
                                delay: (context) => {
                                    let delay = 0;
                                    if (context.type === 'data' && context.mode === 'default' && !delayed) {
                                    delay = context.dataIndex * 300 + context.datasetIndex * 100;
                                    }
                                    return delay;
                                },
                            },
                            scales: {
                                y: {
                                beginAtZero: true,
                                suggestedMax: 30
                                }
                            }
                        }
                    });
                </script>
            ";
        }

        public function showJsScanBtn () 
        {
            echo '<script>
                    const btnScan = document.getElementById("scan-btn");
                    const progressBox = document.getElementById("progress-box");
                    const progressBar = document.getElementById("scan-progress-bar");
                    const result = document.getElementById("result");
                    
                    btnScan.addEventListener("click", sendRequest);

                    function updateProgress()
                    {
                        progressBox.classList.remove("invisible");
                        fetch(`./getValueProgress.php`, {
                                method: "GET",
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json(); // Parse JSON từ phản hồi
                        })
                        .then(data => {
                            const percent = data[0]?.percent || 0;

                            progressBar.setAttribute("aria-valuenow", percent);
                            progressBar.style.width = percent + "%";
                            progressBar.innerHTML = percent + "%";

                            if (percent >= 100) {
                                getResultScan();
                            }else {
                                setTimeout(updateProgress, 700);
                            }
                        })
                        .catch(error => {
                            setTimeout(updateProgress, 700);
                        });
                    }

                    function getResultScan ()
                    {
                        const url = "./resultScan.php";
                        const formData = new FormData();
                        formData.append("btn", "Lấy kết quả");

                        fetch(url, {
                                method: "POST",
                                body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json(); 
                        })
                        .then(data => {
                            printResult(data);
                        })
                        .catch(error => {
                            setTimeout(getResultScan, 500);
                        });
                    }

                    function sendRequest (e)
                    {
                        if (document.getElementById("find-form-box"))
                        {
                            document.getElementById("find-form-box").classList.add("d-none");
                        } 
                        e.preventDefault();
                        const url = "./resultScan.php";
                        const scanLocation = document.getElementById("scan-location").value;
                        const formData = new FormData();
                        formData.append("scan-location-input", scanLocation);
                        formData.append("btn", "Quét");
                        
                        fetch(url, {
                            method: "POST",                               
                            mode: "no-cors",
                            body: formData
                        }).catch(() => {});
                        updateProgress();
                    }      

                    function printResult(files)
                    { 
                        let resultHtml = `<form method="POST" class="form-group">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                            <th scope="col">STT</th>
                                            <th scope="col">Đường dẫn</th>
                                            <th scope="col">Kết quả</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                        let numFile = files.length;
                        
                        if (numFile <= 0)
                        {
                            resultHtml += `<tr><td colspan="3" class="text-center">Không tìm thấy webshell</td></tr>`;
                        } else {
                            for (let i=0; i < numFile; i++)
                            {
                                let number = i+1;
                                resultHtml += `<tr class="cursor_point" data-toggle="collapse" data-target="#details${number}" aria-expanded="false" aria-controls="details${number}">
                                                <th scope="row">${number}</th>
                                                <td>${files[i].filePath}</td>
                                                <td>${files[i].type}</td> 
                                            </tr>
                                            <tr class="collapse" id="details${number}">
                                                <td colspan="3">
                                                    <div class="card p-3">
                                                        <div class="mb-2"><strong>Hash:</strong> ${files[i].SHA256Hash}</div>
                                                        <div class="mb-2"><strong>Size:</strong> ${files[i].size}</div>
                                                        <div class="mb-2"><strong>Signature:</strong> </div>
                                                        <table class="table table-sm table-bordered">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th scope="col">Chữ ký</th>
                                                                    <th scope="col">Vị trí</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>`;
                                for (let j=0; j < files[i].signature.length; j++)
                                {
                                    resultHtml += `<tr>
                                        <td>${files[i].signature[j][0]}</td>
                                        <td>${files[i].signature[j][1]}</td>
                                    </tr>`;
                                }
                
                                resultHtml += `</tbody></table><div class="row">
                                                        <input name="action-file-location[]" value="${files[i].filePath}" hidden>
                                                        <label for="action-file${number}" class="col-sm-5 col-form-label">Hành động</label>
                                                        <select id="action-file${number}" name="action-file-chose[]" class="cursor_point form-select col-sm-5">
                                                            <option value="1">Cho phép</option>
                                                            <option selected value="2">Cách ly</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>`;
                            }
                            
                            resultHtml += `<input type="submit" value="Áp dụng" name="btn" class="whi-color btn scan-req-btn bg-pri-color mb-3">`;
                        }
                        
                        const endHtml = `</tbody></table></form>`;
                        result.innerHTML = resultHtml;
                    }
                </script>';
        }
    
        // Tesst -----------------------------------------------------------------------------------
        public function test ()
        {
            require_once("./model/mScan.php");

            $mScan = new mScan();

            $mScan->addFileList($this->webshellList);
        }
        
        // Tesst -----------------------------------------------------------------------------------
    }
?>