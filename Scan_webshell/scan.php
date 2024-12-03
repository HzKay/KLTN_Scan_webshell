<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./public/css/style.css">
    <link rel="stylesheet" href="./public/css/bootstrap.css">
    <link rel="stylesheet" href="./public/css/bootstrap.min.css">
    <title>Quét</title>
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
                    <li class="list-group-item list-group-action sidebar-menu"><a class="nav-link active" href="./scan.php">Quét</a></li>
                    <li class="list-group-item list-group-action sidebar-menu"><a class="nav-link" href="./setting.php">Cài đặt</a></li>
                </ul>
            </div>
            <div class="col-sm-10 p-4">
                <div class="container d-flex justify-content-center align-items-center flex-wrap">
                    <div class="scan-req-box">
                        <h4 class="h4 font-weight-bold text-center">QUÉT SERVER</h4>
                        <form action="" method="post" class="scan-req-form">
                            <div class="form-group">
                                <label for="scan-location" id="" class="scan-location-label">Vị trí quét</label>
                                <input type="text" name="scan-location-input" class="scan-req-input form-control" id="scan-location" placeholder="./" value="<?php $folderLocation=$_POST["scan-location-input"] ?? ""; echo htmlspecialchars($folderLocation, ENT_QUOTES, 'UTF-8');?>"> <br>
                                <small id="" class="form-text text-muted">Mặc định sẽ là từ thư mục chứa module.</small>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="Quét" name="btn" class="mr-3 whi-color btn scan-req-btn bg-pri-color" id="scan-btn">
                                <input type="submit" value="Tìm vị trí" name="btn" class="mr-3 whi-color btn scan-req-btn bg-pri-color">
                            </div>
                        </form>
                        <div class="progress invisible mt-3" id="progress-box">
                            <div class="progress-bar" id="scan-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <?php    
                        include("./class/clsScan.php");
                        $p = new clsScan();

                        if (isset($_POST["btn"]))
                        {
                            switch ($_POST["btn"])
                            {
                                case "Tìm vị trí": {
                                    $findFolderName = $_POST["scan-location-input"];
                                    if ($findFolderName == "")
                                    {
                                        echo "Tên folder không được để trống";
                                    } else {
                                        $filesInDir = $p->findFolderLocation($findFolderName);
                                        $count = 0;
                                        echo "
                                            <table class='table table-striped' id='find-form-box'>
                                                    <thead>
                                                        <tr>
                                                        <th scope='col'>STT</th>
                                                        <th scope='col'>Đường dẫn</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                        ";

                                        foreach ($filesInDir as $filePath)
                                        {
                                            $count++;

                                            echo "
                                                <tr>
                                                    <th scope='row'>{$count}</th>
                                                    <td>{$filePath}</td>
                                                    </tr>
                                                <tr>
                                            ";
                                        }
                                        echo "</tbody></table>";
                                    }
                                                
                                    break;
                                }
                                case "Áp dụng": {
                                    $inputFile = $_POST["action-file-location"];
                                    $action = $_POST["action-file-chose"];
                                    $p->quaranFile($inputFile, $action);
                                    break;
                                }
                            }
                        }
                    ?>
                    <div id="result" class="scan-req-box"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="content-file-box" tabindex="-1" aria-labelledby="contentFileTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable mw-80">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contentFileTitle">Nội dung file</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contentFile">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>


    <?php
        $p->showJsScanBtn();
    ?>
</body>
</html>