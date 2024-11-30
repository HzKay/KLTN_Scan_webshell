<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./public/css/bootstrap.css">
    <link rel="stylesheet" href="./public/css/bootstrap.min.css">
    <link rel="stylesheet" href="./public/css/style.css">
    <title>Upload</title>
    <script src="./public/js/script.js"></script>
    <script src="./public/js/chart.js"></script>
    <script src="./public/js/jquery-3.6.1.min.js"></script>
    <script src="./public/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container-xxl">
        <div class="row">
            <div class="col-sm-2">
                <ul class="list-group list-group-flush p-3">
                    <li class="list-group-item list-group-action"><a class="nav-link active" href="./index.php">Dashboard</a></li>
                    <li class="list-group-item list-group-action"><a class="nav-link" href="./upload.php">Upload</a></li>
                    <li class="list-group-item list-group-action"><a class="nav-link" href="./scan.php">Quét</a></li>
                    <li class="list-group-item list-group-action"><a class="nav-link" href="./setting.php">Cài đặt</a></li>
                </ul>
            </div>
            <div class="col-sm-10 p-4">
                <div class="container d-flex justify-content-center align-items-center flex-wrap">
                    <div class="scan-req-box">
                        <?php
                            require_once('./class/clsUpload.php');
                            $upload = new clsUpload();
                            $upload->showFormUpload();
                            $upload->uploadFile();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>