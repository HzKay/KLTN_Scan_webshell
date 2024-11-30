<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./public/css/style.css">
    <link rel="stylesheet" href="./public/css/bootstrap.css">
    <link rel="stylesheet" href="./public/css/bootstrap.min.css">
    <title>Dashboard</title>
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
                <div class="mb-2">
                    <h5 class="chart-title text-uppercase font-weight-bold">
                            Kết quả quét
                    </h5>
                <div class="row">
                    <div class="col-sm-3">  
                        <canvas id="pieChart" style="height: 200px!important"></canvas>
                    </div>
                    
                    <ul class="dashboard-sumary col-sm-9 d-flex justify-content-around flex-wrap align-content-center">
                        <li class="dashboard-sumary-item">
                            <span>
                                Tổng số tệp:
                            </span>
                            <span id="total-file">
                                
                            </span>
                        </li>
                        <li class="dashboard-sumary-item">
                            <span>
                                Số Webshell: 
                            </span>
                            <span id="shell-file">
                                
                            </span>
                        </li>
                        <li class="dashboard-sumary-item">
                            <span>
                                Số tệp cách ly: 
                            </span>
                            <span id="quarant-file">
                                
                            </span>
                        </li>
                        <li class="dashboard-sumary-item">
                            <span>
                                Thời gian quét:
                            </span>
                            <span  id="scan-time">
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="p-2 ">
                <h5 class="chart-title text-uppercase font-weight-bold">
                    Lịch sử quét
                </h5>
                <canvas id="lineChart" height="20vw" width="100vw"></canvas>
            </div>
            </div>
        </div>
    </div>
    <?php 
        include_once ("./class/clsScan.php");
        $clsScan = new clsScan();
        $clsScan->getInfoDashboard();
    ?>
</body>
</html>