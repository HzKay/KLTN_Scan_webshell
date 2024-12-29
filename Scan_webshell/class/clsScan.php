<?php
    $direct = dirname(__DIR__);
    include ($direct . "/class/clsStatus.php");
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

        public function getAllFolder ($url)
        {
            if ($url == "..") {
                $url = dirname(__FILE__, 3);  // Di chuyển lên hai cấp từ file hiện tại
            }

            $directories = [];
            $dir = new DirectoryIterator($url);

            // Lặp qua các mục trong thư mục
            foreach ($dir as $fileinfo) {               
                if (!$fileinfo->isDot())
                {
                    if ($fileinfo->isDir()) {
                        $folderName = $fileinfo->getFilename();
                        $path = $url . DIRECTORY_SEPARATOR . $folderName;
                        $directories[] = $path;  

                        // Đệ quy lấy các thư mục con
                        $directories = array_merge($directories, $this->getAllFolder($path));
                    }
                    
                }                
            }

            return $directories;
        }

        public function normalizePath ($folderList)
        {
            $result = array();
            
            foreach ($folderList as $item)
            {
                $basePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
                $result[] = str_replace($basePath, '', $item);
            }

            return $result;
        }

        public function findFolderLocation($findFolderName)
        {
            $directories = $this->getAllFolder("..");
            
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
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/model/mScan.php"));
            $databse = new mScan();          
            $signs = $databse->getAllSigns();
            $fileContent = $this->getFileContent(filePath: $file);

            if ($fileContent === false) {
                return -1;
            }

            $signList = array();
            $signSample = array();

            foreach($signs as $sign) {
                $result = preg_match_all($sign[0], $fileContent, $ouput, PREG_OFFSET_CAPTURE);

                if ($result > 0)
                {                   
                    $signSample[] = $sign[1];
                    foreach ($ouput[0] as $oup)
                    {
                        $oup[0] = htmlspecialchars($oup[0],ENT_QUOTES, 'UTF-8');
                        $signList[] =  $oup;
                    }
                }
            }

            return array("signSample"=>$signSample, "signList"=>$signList); 
        }

        public function createHashCode ($filePath)
        {
            $hashCode = hash_file($this->hash_alg, $filePath);
            return $hashCode;
        }

        public function findHashInDB($hash)
        {
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/model/mScan.php"));
            $mScan = new mScan();

            $isAvailable = $mScan->findHash($hash);

            return $isAvailable;
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
            include_once ($this->makeFitFilePath(dirname(__DIR__) . "/object/objectFile.php"));
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/class/clsSendReq.php"));
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/class/clsUpload.php"));
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/model/mScan.php"));
            
            $database = new mScan();
            $svm = new clsSendReq();
            $clsUpload = new clsUpload();
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
                $isAvail = $this->findHashInDB($hashFile);
                $fileSize = filesize($file);
                $numSign = 0;
                $type = "Normal";
                $currentFile++;

                if ($isAvail != -1) {
                    $newFilePath[] = $file;
                    $file = str_replace($this->basePath, '', $file);
                    $type = "Webshell";
                    $family = $database->getDataFamilyShell($isAvail);
                    $numSign = 1;
                    $objectFile->setInfo($file, $fileSize, $type, $hashFile, array('Scan hash', 0));  
                    $objectFile->setFamily($family);  
                } else {    
                    $signList = $this->validFileContent($file);
                    $numSign = count($signList["signList"]);

                    if ($numSign > 0)
                    {
                        $type = "Webshell";
                        $newFilePath[] = $file;                        
                        $objectFile->addSignSample($signList["signSample"]);

                        $fileLocation = str_replace($this->basePath, '', $file);
                        $objectFile->setInfo($fileLocation, $fileSize, $type, $hashFile, $signList["signList"]);  
                    } else {
                        $isTick = $clsUpload->getSettingFile();
                        if ($isTick["useModelPredict"] == 1)
                        {
                            $svmcheck = $svm->svmCheckScan($file);
                            $fileLocation = str_replace($this->basePath, '', $file);

                            if ($svmcheck == 1)
                            {
                                $type = "Webshell";
                                $newFilePath[] = $file;  
                                
                                $objectFile->setInfo($fileLocation, $fileSize, $type, $hashFile, array('ML predict', 0));  
                            } else {
                                $objectFile->setInfo($fileLocation, $fileSize, $type, $hashFile, $signList["signList"]);  
                            }

                        }

                        
                    }
                }  
                
                $this->storeFiles($objectFile, $numSign);
                $this->setCurrentProcess($currentFile);
            }
            
            $this->fileListGlobal = $this->normalizePath($newFilePath, 0);      
            $this->addDataScan($filePath);      
            return $this->webshellList;
        }        

        public function quaranFile($fileList, $action)
        {
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/model/mScan.php"));
            $database = new mScan();
            $result = array();
            $scanId = $database->getLastScanId();
            $query = "
                UPDATE chitietketqua kq 
                LEFT JOIN tep t ON t.MaTep = kq.MaTep
                SET kq.HanhDong = 2
                WHERE t.ViTriTep=? AND t.TenTep=? AND kq.MaQuet = ?;
            ";
            
            for ($index = 0; $index < count($fileList); $index++)
            {
                if ($action[$index] == 1)
                {
                    $results[$index] = true;
                } elseif ($action[$index] == 2) {
                    $filePath = $fileList[$index];
                    $fileName = basename($filePath);
                    $fileLocation = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . $filePath;
                    $fileLocation = $this->makeFitFilePath($fileLocation);
                    $pathInDB = $this->makeFitFilePath(dirname($filePath) . DIRECTORY_SEPARATOR);
                    
                    $isUpdateInDB = $database->execRequestDB($query, $pathInDB, $fileName, $scanId);
                    
                    if ($isUpdateInDB == true)
                    {
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
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/model/mScan.php"));

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
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/model/mScan.php"));
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/object/objectResultScan.php"));

            $mScan = new mScan();
            $scanRecent = array();
            $resultScan = $mScan->getFilesRecentScan();

            while ($result = mysqli_fetch_array($resultScan))
            {             
                $scanRecent[] = new ObjectResultScan($result["TenTep"], $result["ViTriTep"], $result["MaBam"], $result["KetQua"], $result["HanhDong"]);
            }

            return $scanRecent;
        }

        public function calcDashboard($files)
        {
            $numShell = 0;
            $numQuarant = 0;
            $totalFile = count($files);
            
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

            $result = array("totalFile"=>$totalFile, "shell" => $numShell, "quarant" => $numQuarant);
            return $result;
        }

        public function getDateRecentScan()
        {
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/model/mScan.php"));
            $mScan = new mScan();
            $result = $mScan->getDateRecentScan();

            return $result;
        }

        public function getInfoDashboard()
        {
            $scan = $this->getDateRecentScan();
            $files = $this->getFilesRecentScan();
            $number = $this->calcDashboard($files);
            $scanHist = $this->getScanHistory();
            $percent = $number["shell"] - $number["quarant"];
            $scanDays = json_encode(value: implode(', ', $scanHist["ngayThucHien"]));
            $numShell = json_encode(implode(', ', $scanHist["soWebshell"]));
            
            echo "
                <script>
                    const ctx = document.getElementById('lineChart');
                    const pieCtx = document.getElementById('pieChart');
                    const time = document.getElementById('scan-time');
                    const quarant = document.getElementById('quarant-file');
                    const shell = document.getElementById('shell-file');
                    const path = document.getElementById('scan-location');
                    let delayed;
                    const scanDates = {$scanDays};
                    const webshellCounts = {$numShell};

                    path.innerHTML = '{$scan['viTriQuet']}';
                    shell.innerHTML = '{$number["shell"]}';
                    quarant.innerHTML = '{$number["quarant"]}';
                    time.innerHTML = '{$scan['ngayQuet']}';

                    new Chart(pieCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Cho phép', 'Cách ly'],
                            datasets: [{
                                label: ' Số lượng file',
                                data: [{$percent}, {$number["quarant"]}],
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
                        fetch(`./getValue.php`, {
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
                            clearAllTimeout();
                            printResult(data);
                        })
                        .catch(error => {
                            console.log(error);
                        });
                    }

                    function clearAllTimeout()
                    {
                        var id = setTimeout(function() {}, 0);
                        clearTimeout(id);

                        while (id--) {
                            clearTimeout(id); 
                        }
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

                    function getFileContent (id)
                    {
                        const url = "./resultScan.php";
                        const scanLocation = document.getElementById("scan-location").value;
                        const showContent = document.getElementById("contentFile");
                        const modelBox = document.getElementById("content-file-box");
                        const formData = new FormData();
                        formData.append("idFile", id);
                        formData.append("btn", "Lấy nội dung");
                        
                        fetch(url, {
                            method: "POST",      
                            body: formData
                        }).then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.text(); 
                        })
                        .then(htmlContent => {
                            showContent.innerHTML = htmlContent; 
                            const myModal = new bootstrap.Modal(modelBox);
                            myModal.show();
                        })
                        .catch(error => {
                            console.error("Error fetching HTML content:", error);
                        });
                    }
                    

                    function printResult(files) { 
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

                        if (numFile <= 0) {
                            resultHtml += `<tr><td colspan="3" class="text-center">Không tìm thấy webshell</td></tr>`;
                        } else {
                            for (let i = 0; i < numFile; i++) {
                                let number = i + 1;
                                resultHtml += `<tr class="cursor-point" data-bs-toggle="collapse" data-bs-target="#details${number}" aria-expanded="false" aria-controls="details${number}">
                                                    <th scope="row">${number}</th>
                                                    <td>${files[i].filePath}</td>
                                                    <td>${files[i].type}</td> 
                                                </tr>
                                                <tr class="collapse" id="details${number}">
                                                    <td colspan="3 border-0">
                                                        <div class="card p-3">
                                                            <div class="mb-2"><strong>Hash:</strong> ${files[i].SHA256Hash}</div>
                                                            <div class="mb-2"><strong>Size:</strong> ${files[i].size}</div>`;
                                if ((files[i].family["TenMau"]) != null)
                                {
                                    resultHtml += `<div class="mb-2"><strong>Nhóm shell:</strong> ${files[i].family["TenMau"]}</div>
                                                    <div class="mb-2"><strong>Mô tả:</strong> ${files[i].family["ThongTin"]}</div>`;
                                }
                                                      resultHtml += `<div class="mb-2"><strong>Signature:</strong></div>
                                                            <table class="table table-sm table-bordered range-width">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th scope="col">Chữ ký</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>`;
                                if (files[i].signature[0] != "Scan hash" && files[i].signature[0] != "ML predict")
                                {
                                    for (let j = 0; j < files[i].signature.length; j++) {
                                        resultHtml += `<tr>
                                                        <td  class="overflow-scroll">${files[i].signature[j][0]}</td>
                                                    </tr>`;
                                    }
                                } else if (files[i].signature[0] == "Scan hash") {
                                    resultHtml += `<tr>
                                                        <td  class="overflow-scroll">Scan hash</td>
                                                    </tr>`; 
                                } else {
                                    resultHtml += `<tr>
                                                        <td  class="overflow-scroll">ML predict</td>
                                                    </tr>`; 
                                }

                                resultHtml += `</tbody></table>
                                                <div class="row">
                                                    <input name="action-file-location[]" value="${files[i].filePath}" hidden>
                                                    <div class="col-sm-6 d-flex justify-content-between">
                                                        <label for="action-file${number}" class="col-sm-2 col-form-label">Hành động</label>
                                                        <select id="action-file${number}" name="action-file-chose[]" class="cursor-point form-select">
                                                            <option value="1">Cho phép</option>
                                                            <option selected value="2">Cách ly</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-6 text-end">
                                                        <span onclick="getFileContent(${i})" class="btn bg-pri-color whi-color">Xem nội dung</span>    
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>`;
                            }

                            resultHtml += `<input type="submit" value="Áp dụng" name="btn" class="btn btn-primary mb-3">`;
                        }

                        const endHtml = `</tbody></table></form>`;
                        resultHtml += endHtml;
                        result.innerHTML = resultHtml;
                    }

                </script>';
        }
    
        public function highlightText($content, $signList) {   
            try {
                $startHighLight = "<span style='background-color: yellow; font-weight: bold;'>";
                $endHighLight = "</span>";
                $startSignal = '@@@';
                $endSignal = '###';
                $temp = 0;
        
                usort($signList, function($a, $b) {
                    return $a[1] <=> $b[1];
                });

                foreach ($signList as $sign)
                {
                    // Vị trí cần làm nổi
                    $startPosition = $sign[1]+$temp;
                    // Độ dài cần làm nổi
                    $endPosition = $startPosition + strlen($sign[0]) + strlen($startSignal);
        
                    $content = substr_replace($content, $startSignal, $startPosition, 0);
                    $content = substr_replace($content, $endSignal, $endPosition, 0);
                    $temp += strlen($startSignal) + strlen($endSignal);
                }
                $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        
                
                $content = str_replace($startSignal, $startHighLight, $content);
                $content = str_replace($endSignal, $endHighLight, $content);
        
                return $content;
            } catch (Exception $e) {
                return false;
            }
        }

        public function showContentFile ($files, $id)
        {
            $filePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . $files[$id]->filePath;
            $content = file_get_contents($filePath);
            $sign = $files[$id]->signature;

            if ($sign[0] != 'Scan hash' && $sign[0] != 'ML predict')
            {
                $content = $this->highlightText($content, $sign);
            } else {
                $content = htmlspecialchars($content, ENT_QUOTES);
            }

            return $content;
        }

        public function addDataScan ($location)
        {
            require_once ($this->makeFitFilePath(dirname(__DIR__) . "/model/mScan.php"));

            $mScan = new mScan();
            $result = $mScan->addDataScan($location, $this->webshellList);
            
            switch ($result)
            {
                case -1:{
                    echo "<scriptalert('Thêm dữ liệu quét vào cơ sở dữ liệu thất bại')</script>";
                    break;
                }
                case -2:{
                    echo "<scriptalert('Không tìm thấy mã quét')</script>";
                    break;
                }
            }
        }

        // Tesst -----------------------------------------------------------------------------------
        

        // Tesst -----------------------------------------------------------------------------------
    }
?>