<?php
    class clsUpload{
        private $uploadFolder = "/upload/";

        public function showFormUpload ()
        {
          $formUpload = "
            <div>
                <div class='frm-upload text-center'>
                    <form action='./handleUpload.php' method='POST' enctype='multipart/form-data'>
                        <div class='form-upload'>
                            <input type='file' class='file-upload' id='file' name='file' onchange='showUpBtn()'>
                            <label for='file' class='btn-upload'>
                                <svg class='icon-upload mt-5' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 512'>
                                    <path d='M537.6 226.6c4.1-10.7 6.4-22.4 6.4-34.6 0-53-43-96-96-96-19.7 0-38.1 6-53.3 16.2C367 64.2 315.3 32 256 32c-88.4 0-160 71.6-160 160 0 2.7 .1 5.4 .2 8.1C40.2 219.8 0 273.2 0 336c0 79.5 64.5 144 144 144h368c70.7 0 128-57.3 128-128 0-61.9-44-113.6-102.4-125.4zM393.4 288H328v112c0 8.8-7.2 16-16 16h-48c-8.8 0-16-7.2-16-16V288h-65.4c-14.3 0-21.4-17.2-11.3-27.3l105.4-105.4c6.2-6.2 16.4-6.2 22.6 0l105.4 105.4c10.1 10.1 2.9 27.3-11.3 27.3z'/>
                                </svg>
                                <p class='mt-3' id='tenfile'>Bấm vào để chọn tệp</p>
                            </label>
                        </div>
                        
                        <label for='submitBtn' class='btn btn-submit mt-4' id='labelUpBtn'>Tải lên</label><br>
                        <input type='submit' value='uploadBtn' hidden id='submitBtn' name='btn'>
                    </form>
                </div>
            </div>
            <script>
                function showUpBtn() {
                    let fileName = document.getElementById('tenfile');
                    let inFile = document.getElementById('file').files[0].name; 
                    
                    fileName.innerHTML = inFile; 
                }
            </script>
            ";
            echo $formUpload;
        }

        public function makeFitFilePath ($filePath)
        {
            $search = array('\\', '/');
            $newFilePath = str_replace($search, DIRECTORY_SEPARATOR, $filePath);
            return $newFilePath;
        }

        public function uploadFile () 
        {
            require_once ("./model/mScan.php");
            require_once ('./class/clsSendReq.php');
            require_once ("./object/objectFile.php");
            $databse = new mScan();
            $fileObject = new ObjectFile();
            $sendReq = new clsSendReq();

            $file = $_FILES['file'];
            $isValidFile = $this->validateFile($file);

            if ($isValidFile)
            {
                $tmp_name = $file['tmp_name'];
                $folder = '.' . $this->uploadFolder . htmlspecialchars($file["name"], ENT_QUOTES);
                $folder = $this->makeFitFilePath($folder);
                $fileObject->setInfo($folder, $file['size'], '', '', '');
                $MD_check =  $sendReq->kiemTraFile();

                if ($MD_check == 0)
                {
                    $isMove = move_uploaded_file($tmp_name, $folder);
                    $isAddToDB = $databse->uploadFile($fileObject);
                    if ($isMove == true && $isAddToDB == 0)
                    
                    if ($isMove == true && $isAddToDB == 0)
                    {
                        echo "<script>alert('Tải lên thành công')</script>";                    
                    } else {
                        echo "<script>alert('Lỗi, gặp vấn đề khi tải lên!')</script>";
                    }
                } else {
                    echo "<script>alert('Lỗi, tệp tin có thể là webshell!')</script>";
                }
                
            } else {
                echo "<script>alert('Lỗi, tệp tin không hợp lệ!')</script>";
            }
            
            echo "<script>window.location.href = './upload.php';</script>";
        }

        public function validateFileSize ($fileSize, $setting)
        {
            if ($setting > $fileSize)
            {
                return 1;
            } else {
                return 0;
            }
        }

        public function downloadFile($filename, $download_rate=10000)
        {
            $urlfile = dirname(path: dirname(__FILE__)) . $this->uploadFolder . htmlspecialchars($filename, ENT_QUOTES);
            $urlfile = $this->makeFitFilePath($urlfile);

            if(file_exists($urlfile) && is_file($urlfile))
            {
                header('Cache-control: private');
                header('Content-Type: application/octet-stream');
                header('Content-Length: '.filesize($urlfile));
                header('Content-Disposition: filename='.$filename);
                flush();
                $file = fopen($urlfile, "r");

                while (!feof($file))
                {
                    print fread($file, round($download_rate * 1024));
                    flush();
                    sleep(1);
                }
                fclose($file);
            }else {
                echo "<script>alert('Lỗi: {$filename} không tồn tại');</script>";
            }
        }

        public function deleteFile ($fileName)
        {
            require_once ("./model/mScan.php");

            $databse = new mScan();
            $filePath = dirname(dirname( __FILE__ )) . $this->uploadFolder . htmlspecialchars($fileName, ENT_QUOTES);
            $filePath = $this->makeFitFilePath($filePath);
            $folder = $this->makeFitFilePath('.' . $this->uploadFolder);

            $isDelInDB = $databse->delUploadFile($fileName, $folder);

            if ($isDelInDB == 0)
            {
                $isDelete = unlink(filename: $filePath);

                if ($isDelete == true)
                {
                    echo "<script>alert('Xoá file thành công');
                        window.location.href = './upload.php';</script>";
                } else {
                    echo "<script>alert('Lỗi, không thể xoá file trong thư mục');</script>";
                }
            } else {
                echo "<script>alert('Lỗi, không thể xoá file trên csdl');</script>";
            }
        }

        public function handleBtnPush ()
        {
            if (isset($_POST["btn"]))
            {
                switch($_POST["btn"])
                {
                    case 'download': {
                        $fileName = $_POST['fileName'];
                        $this->downloadFile($fileName);
                        break;
                    }
                    case 'delete': {
                        $fileName = $_POST['fileName'];
                        $this->deleteFile($fileName);
                        break;
                    }
                    case 'uploadBtn': {
                        $this->uploadFile();
                        break;
                    }
                    case 'sync': {
                        require_once("./class/clsSendReq.php");
                        $req = new clsSendReq();
                        $req->addSignSync();
                        break;
                    }
                }
            }
        }

        public function validateFileExtent ($extension, $setting)
        {
            $allowed_extentions = explode(",", $setting);

            if (in_array($extension, $allowed_extentions))
            {
                return 1;
            } 

            return 0;
        }

        public function validateFile ($file)
        {
            $setting = $this->getSettingFile();
            $fileName = explode(".", $file['name']);
            $extention = end($fileName);

            $isValidExt = $this->validateFileExtent($extention, $setting["allowed_extentions"]);
            $isValidSize = $this->validateFileSize($file['size'], $setting["maxsize"]);

            if ($isValidExt == 1 && $isValidSize == 1)
            {
                return true;
            }

            return false;            
        }
        
        public function updateSetting($maxsize, $extentions, $useModelPredict) 
        {
            $content = "";
            $maxsize = $maxsize*1000;
            
            $content = $content . "maxsize = '{$maxsize}'\nallowed_extentions = '$extentions'\nuseModelPredict = '$useModelPredict'";

            $result = file_put_contents("./config.ini", $content);
            
            if($result == false) {
                echo "<script>alert('Lỗi, cài đặt không thành công');</>";
            } else {
                echo "<script>alert('Cài đặt thành công');</script>";
            }
        }
        
        public function showSettingUi ()
        {
            $this->showSetting();
            $this->showSyncFunction();
        }

        public function showSyncFunction ()
        {
            echo "<div class='container'><div class='card mt-3 mr-2 ml-2 bt-1'>
                        <div class='card-body'>
                            <h5>Cập nhật dữ liệu</h5>
                            <form action='./handleUpload.php' method='post'>
                                <button type='submit' name='btn' value='sync' id='sync-btn' class='btn btn-primary'>
                                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d='M142.9 142.9c-17.5 17.5-30.1 38-37.8 59.8c-5.9 16.7-24.2 25.4-40.8 19.5s-25.4-24.2-19.5-40.8C55.6 150.7 73.2 122 97.6 97.6c87.2-87.2 228.3-87.5 315.8-1L455 55c6.9-6.9 17.2-8.9 26.2-5.2s14.8 12.5 14.8 22.2l0 128c0 13.3-10.7 24-24 24l-8.4 0c0 0 0 0 0 0L344 224c-9.7 0-18.5-5.8-22.2-14.8s-1.7-19.3 5.2-26.2l41.1-41.1c-62.6-61.5-163.1-61.2-225.3 1zM16 312c0-13.3 10.7-24 24-24l7.6 0 .7 0L168 288c9.7 0 18.5 5.8 22.2 14.8s1.7 19.3-5.2 26.2l-41.1 41.1c62.6 61.5 163.1 61.2 225.3-1c17.5-17.5 30.1-38 37.8-59.8c5.9-16.7 24.2-25.4 40.8-19.5s25.4 24.2 19.5 40.8c-10.8 30.6-28.4 59.3-52.9 83.8c-87.2 87.2-228.3 87.5-315.8 1L57 457c-6.9 6.9-17.2 8.9-26.2 5.2S16 449.7 16 440l0-119.6 0-.7 0-7.6z'/></svg> 
                                    Cập nhật ngay
                                </button>
                            </form></div></div></div>";
        }
        
        public function showSetting()
        {
            $setting = $this->getSettingFile();
            $maxsize = ((int) $setting['maxsize']) / 1000;
            $fileExt= explode(",", $setting['allowed_extentions']);
            $useModel= (int) $setting['useModelPredict'];
            $temp = htmlspecialchars(implode(",", $fileExt), ENT_QUOTES, 'UTF-8');
            
            echo "
                <form id='form1' name='form1' method='post' action='' class='container mt-4'>
                    <input type='hidden' name='csrf' value='{}'>
                    <div class='card shadow-sm'>
                        <div class='card-body'>
                            <div class='mb-3 row'>
                                <label for='txtMaxSize' class='col-md-5 col-form-label'>Dung lượng tối đa của file tải lên (KB):</label>
                                <div class='col-md-7'>
                                    <input type='number' class='form-control' id='txtMaxSize' name='txtMaxSize' value='{$maxsize}' required>
                                </div>
                            </div>
                            <div class='mb-3 row'>
                                <label for='txtFileExt' class='col-md-5 col-form-label'>Đuôi mở rộng file được tải lên:</label>
                                <div class='col-md-7'>
                                    <input type='text' class='form-control' id='txtFileExt' name='txtFileExt' value='{$temp}' required>
                                </div>
                            </div>
                            <div class='mb-3'>
                                <div class='form-check'>";
            if ($useModel == 1)
            {
                echo "<input class='form-check-input' type='checkbox' name='useModel' id='useModel' checked>";
            } else {
                echo "<input class='form-check-input' type='checkbox' name='useModel' id='useModel'>";
            }
            echo "<label class='form-check-label' for='useModel'>
                                        Sử dụng mô hình dự đoán
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class='card-footer text-center'>
                            <button type='submit' name='btn' value='btn-setup' class='btn btn-primary'>Cài đặt</button>
                        </div>
                    </div>
                </form>";

        }

        public function getSettingFile()
        {
            $filepath = "./config.ini";
            $setting = parse_ini_file($filepath);

            return $setting;
        }

        public function showSize ($size) 
        {
            if($size > 0)
            {
                $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
                $unitIndex = floor(log($size, 1024));
                $formatSize = $size / pow(1024, $unitIndex);
                $formatSize = round($formatSize * 100) / 100;
                return $formatSize . ' ' . $units[$unitIndex];
            } else {
                return '0 KB';
            }
        }
        public function showUploadFiles()
        {
            require_once("./model/mScan.php");

            $mScan = new mScan();
            $uploadFiles = $mScan->getUploadFiles();

            echo "<table id='fileTable' class='table table-striped table-bordered'>
                        <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên file</th>
                            <th>Kích thước</th>
                            <th>Ngày tải lên</th>
                            <th>Hành động</th>
                        </tr>
                        </thead>
                        <tbody>";

            if (count($uploadFiles) > 0)
            {
                foreach ($uploadFiles as $index => $file)
                {
                    $number = $index + 1;
                    $fileName = basename($file->filePath);
                    $size = $this->showSize($file->size);
                    echo "<tr>
                                <td>{$number}</td>
                                <td>{$fileName}</td>
                                <td>{$size}</td>
                                <td>{$file->date}</td>
                                <td>
                                    <form action='./handleUpload.php' method='post'>
                                        <button class='btn btn-sm action-btn mr-3' name='btn' value='download'>
                                            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d='M216 0h80c13.3 0 24 10.7 24 24v168h87.7c17.8 0 26.7 21.5 14.1 34.1L269.7 378.3c-7.5 7.5-19.8 7.5-27.3 0L90.1 226.1c-12.6-12.6-3.7-34.1 14.1-34.1H192V24c0-13.3 10.7-24 24-24zm296 376v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h146.7l49 49c20.1 20.1 52.5 20.1 72.6 0l49-49H488c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z'/></svg>
                                        </button>
                                        <button type='button' data-toggle='modal' data-target='#confirmDel' class='btn btn-sm action-btn mr-3' onclick='confirmDel()'>
                                            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d='M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.7 23.7 0 0 0 -21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0 -16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z'/></svg>
                                        </button>
                                        <input type='text' hidden value='{$fileName}' id='itemFileName' name='fileName'>
                                    </form>
                                </td>
                                
                            </tr>
                    ";
                }
            } 
            
            echo "</tbody></table>";
        }
    }
?>