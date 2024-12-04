<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./public/css/style.css">
    <link rel="stylesheet" href="./public/css/bootstrap.css">
    <link rel="stylesheet" href="./public/css/bootstrap.min.css">
    <title>Cài đặt</title>
    <script src="./public/js/chart.js"></script>
    <script src="./public/js/jquery-3.6.4.min.js"></script>
    <script src="./public/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-2">
                <ul class="list-group list-group-flush p-3">
                    <li class="list-group-item list-group-action sidebar-menu"><a class="nav-link" href="./index.php">Dashboard</a></li>
                    <li class="list-group-item list-group-action sidebar-menu"><a class="nav-link" href="./upload.php">Tải lên</a></li>
                    <li class="list-group-item list-group-action sidebar-menu"><a class="nav-link" href="./scan.php">Quét</a></li>
                    <li class="list-group-item list-group-action sidebar-menu"><a class="nav-link active" href="./setting.php">Cài đặt</a></li>
                </ul>
            </div>
            <div class="col-sm-10 p-4">
                <h4 class="h4 font-weight-bold text-center mb-2">CÀI ĐẶT TẢI LÊN</h4>
                <div class="px-2">
                    <?php
                        include_once ("./class/clsUpload.php");
                        $setting = new clsUpload();

                        if (isset($_POST["btn"]))
                        {
                            $button = $_POST["btn"];
                            switch ($button)
                            {
                                case "btn-setup":{
                                    $size = $_POST["txtMaxSize"];
                                    $fileExt = $_POST["txtFileExt"];
                                    if(isset($_POST["useModel"]))
                                    {
                                        $useModel = 1;
                                    } else {
                                        $useModel = 0;
                                    }
                                    $setting->updateSetting($size, $fileExt, $useModel);
                                    $setting->showSettingUi();
                                }
                            }
                        } else {
                            $setting->showSettingUi();
                        }
                    ?>                
                </div>
            </div>
        </div>
    </div>
</body>
</html>