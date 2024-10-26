<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/bootstrap.css">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <title>Scan Server</title>
    <script src="./js/script.js"></script>
    <script src="./js/jquery-3.6.1.min.js"></script>
    <script src="./js/bootstrap.bundle.min.js"></script> <!-- Sử dụng bootstrap.bundle để có popper.js -->
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center flex-wrap">
        <div class="scan-req-box">
            <h4 class="h4 font-weight-bold text-center">QUÉT SERVER</h4>
            <form action="" method="post" class="scan-req-form">
                <div class="form-group">
                    <label for="scan-location" id="" class="scan-location-label">Vị trí quét</label>
                    <input type="text" name="scan-location-input" class="scan-req-input form-control" id="scan-location" placeholder="./" value="<?php $folderLocation=$_POST["scan-location-input"] ?? ""; echo htmlspecialchars($folderLocation, ENT_QUOTES);?>"> <br>
                    <small id="" class="form-text text-muted">Mặc định sẽ là từ thư mục chứa module.</small>
                </div>
                <div class="form-group">
                    <input type="submit" value="Quét" name="btn" class="whi-color btn scan-req-btn bg-pri-color">
                    <input type="submit" value="Tìm vị trí" name="btn" class="whi-color btn scan-req-btn bg-pri-color">
                </div>
            </form>
        </div>
    <?php
        include("./class/clsScan.php");
        $p = new clsScan();

        if (isset($_POST["btn"]))
        {
            switch ($_POST["btn"])
            {
                case "Quét": {
                    $urlToScan = $_POST["scan-location-input"];
                    $resultScan = $p->checkFilesContent($urlToScan); 
                    $filesInDir = $p->fileListGlobal;
                    $numFiles = count($filesInDir);
                    
                    if(gettype($filesInDir) == "string")
                    {
                        echo $filesInDir;
                    } else {
                        echo "
                        <form method='POST' class='form-group row scan-req-box'>
                            <table class='table table-striped'>
                                <thead>
                                    <tr>
                                    <th scope='col'>STT</th>
                                    <th scope='col'>Đường dẫn</th>
                                    <th scope='col'>Kết quả</th>
                                    </tr>
                                </thead>
                                <tbody>
                        ";
    
                        for ($i=0; $i < $numFiles; $i++)
                        {
                            $filePath = $filesInDir[$i]; 
                            $result = $resultScan[$i];
                            $count = $i+1;
    
                            echo "
                                <tr data-toggle='collapse' data-target='#details{$count}' aria-expanded='false' aria-controls='details{$count}'>
                                    <th scope='row'>{$count}</th>
                                    <td>{$filePath}</td>
                                    <td>{$result}</td> 
                                </tr>
                                <tr class='collapse' id='details{$count}'>
                                    <td colspan='3'>
                                        <div class='p-3'>
                                            <input name='action-file-location[]' value='{$filePath}' hidden>
                                            <label for='action-file' class='col-sm-2 col-form-label'>Hành động</label>
                                            <select id='action-file' name='action-file-chose[]' class='col-sm-10 form-select p-1'>
                                                <option value='1'>Cho phép</option>
                                                <option selected value='2'>Cách ly</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            ";
                        }
                        echo "
                            <input type='submit' value='Áp dụng' name='btn' class='whi-color btn scan-req-btn bg-pri-color mb-3'>
                            </tbody></table></form>";
                    }
                
                    break;
                }
                case "Tìm vị trí": {
                    $findFolderName = $_POST["scan-location-input"];
                    if ($findFolderName == "")
                    {
                        echo "Tên folder không được để trống";
                    } else {
                        $filesInDir = $p->findFolderLocation($findFolderName);
                        $count = 0;
                        echo "
                            <table class='table table-striped w-75'>
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
    
    </div>
</body>
</html>
