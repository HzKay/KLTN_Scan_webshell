<?php 
    class clsSetting {
        public function validateFileSize ($fileSize, $setting)
        {
            if ($setting > $fileSize)
            {
                return 1;
            } else {
                return 0;
            }
        }

        public function validateFileType ($filetype, $setting)
        {
            $allow_file_type = explode(",", $setting);

            if (in_array($filetype, $allow_file_type))
            {
                return 1;
            } 

            return 0;
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
            $extention = end(explode(".", $file['name']));

            $isValidExt = $this->validateFileExtent($extention, $setting["allowed_extentions"]);
            $isValidType = $this->validateFileType($file['type'], $setting["allowed_file_type"]);
            $isValidSize = $this->validateFileSize($file['size'], $setting["maxsize"]);

            if ($isValidExt == 1 &&  $isValidType == 1 && $isValidSize == 1)
            {
                return true;
            }

            return false;            
        }
        
        public function updateSetting($maxsize, $extentions, $useModelPredict) 
        {
            $content = "";
            $maxsize = $maxsize*1000;
            
            $content = $content . "maxsize = '{$maxsize}'\nallowed_file_type = 'text/plain,image/jpeg,image/png,application/pdf,image/webp,application/octet-stream,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'\nallowed_extentions = '$extentions'\nuseModelPredict = '$useModelPredict'";

            $result = file_put_contents("./config.ini", $content);
            
            if($result == false) {
                echo "<script>alert('Lỗi, cài đặt không thành công');</script>";
            } else {
                echo "<script>alert('Cài đặt thành công');</script>";
            }
        }
        
        public function showSetting()
        {
            $setting = $this->getSettingFile();
            $maxsize = ((int) $setting['maxsize']) / 1000;
            $fileExt= explode(",", $setting['allowed_extentions']);
            $useModel= (int) $setting['useModelPredict'];
            $temp = htmlspecialchars(implode(",", $fileExt), ENT_QUOTES);
            
            echo "
                <form id='form1' name='form1' method='post' action='' class='container mt-4'>
                    <input type='hidden' name='csrf' value='{}'>
                    <div class='card shadow-sm'>
                        <div class='card-body'>
                            <div class='mb-3 row'>
                                <label for='txtMaxSize' class='col-md-5 col-form-label'>Dung lượng tối đa của file tải lên (KB):</label>
                                <div class='col-md-7'>
                                    <input type='text' class='form-control' id='txtMaxSize' name='txtMaxSize' value='{$maxsize}'>
                                </div>
                            </div>
                            <div class='mb-3 row'>
                                <label for='txtFileExt' class='col-md-5 col-form-label'>Đuôi mở rộng file được tải lên:</label>
                                <div class='col-md-7'>
                                    <input type='text' class='form-control' id='txtFileExt' name='txtFileExt' value='{$temp}'>
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

        private function getSettingFile()
        {
            $filepath = "./config.ini";
            $setting = parse_ini_file($filepath);

            return $setting;
        }
    }
?>