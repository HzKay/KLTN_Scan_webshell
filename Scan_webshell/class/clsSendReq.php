<?php 
    class clsSendReq 
    {
        private $urlModel = "https://webshell-check-flaskapp.fly.dev/";
        
        private $dir;
        
        public function __construct() {
            $this->dir = dirname(__DIR__);
        }
        public function svmCheckUpload ($file)
        {
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($file)) {
                $url = $this->urlModel . 'predict';
                $tmpName = $file["tmp_name"];
                $fileName = $file["name"];
                $fileType = $file["type"];
    
                $curl = curl_init($url);
    
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);

    
                $file = new CURLFile($tmpName, $fileType, $fileName);
    
                $postData = ["file" => $file];
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    
                
                $response = curl_exec($curl);
    
                if (curl_errno($curl)) {
                    echo "Error: " . curl_error($curl);
                }
    
                curl_close($curl);
    
                if (!is_null(json_decode(json: $response)))
                {
                    return json_decode($response)->result;
                }
    
                return 1;
            }
        }

        public function svmCheckScan ($file)
        {
            $url = $this->urlModel . 'predict';
            $fileType = mime_content_type($file);

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);

            $file = new CURLFile($file, $fileType);
            $postData = ["file" => $file];
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            
            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                echo "Error: " . curl_error($curl);
            }

            curl_close($curl);

            if (!is_null(json_decode(json: $response)))
            {
                return json_decode($response)->result;
            }

            return 1;
        }

        public function kiemTraFile () {
            require_once ($this->dir . "/class/clsScan.php");
            require_once ($this->dir . "/class/clsUpload.php");
            $clsScan = new clsScan();
            $clsUpload = new clsUpload();
            $file = $_FILES["file"];
            $fileNameTmp = $file ["tmp_name"];
            $hashCode = $clsScan->createHashCode($fileNameTmp);
            $hashCheck = $clsScan->findHashInDB($hashCode);
           
            if ($hashCheck < 0)
            { 
                $patternCheck = $clsScan->validFileContent($fileNameTmp);
                $numSign = count($patternCheck["signList"]);
    
                if ($numSign <= 0)
                {
                    $isTick = $clsUpload->getSettingFile();
                    if ($isTick["useModelPredict"] == 1)
                    {
                        $svmCheckUpload = $this->svmCheckUpload($file);
                        return $svmCheckUpload;
                    }

                    return 0;
                }
            }

            return 1;
        }

        public function getSignSync ()
        {
            $url = $this->urlModel . 'sync';
            $curl = curl_init($url );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPGET, true);    
            
            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                echo "Error: " . curl_error($curl);
            }

            curl_close($curl);

            if (!is_null(json_decode(json: $response)))
            {
                $data = json_decode($response, true);
                return $data;
            }
    
            return 1;
        }

        public function addSignSync ()
        {
            require_once ($this->dir . "/model/mScan.php");
            $database = new mScan();

            $data = $this->getSignSync();

            try {
                foreach ($data['signature'] as $item) {
                    foreach ($item as $number => $base64_pattern) {
                        $decoded_pattern = base64_decode($base64_pattern);
                        $database->addSignsSync($decoded_pattern, $number);
                    }
                }

                echo "<script>alert('Đồng bộ thành công')</script>";      
            } catch (Exception $e){
                echo "<script>alert('Lỗi, {$e->getMessage()}')</script>";
            }
            
            echo "<script>window.location.href = './setting.php';</script>";
        }
    }
?>