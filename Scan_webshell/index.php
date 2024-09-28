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
                    <input type="submit" value="Quét" name="scan-start-btn" class="whi-color btn scan-req-btn bg-pri-color">
                    <input type="submit" value="Tìm vị trí" name="find-folder-btn" class="whi-color btn scan-req-btn bg-pri-color">
                </div>
            </form>
        </div>
    <?php
        include("./class/clsScan.php");
        $btn = NULL;
        $p = new clsScan();

        if (isset($_POST["scan-start-btn"]))
        {
            $btn = htmlspecialchars($_POST["scan-start-btn"], ENT_QUOTES);
        } elseif (isset($_POST["find-folder-btn"]))
        {
            $btn = htmlspecialchars($_POST["find-folder-btn"], ENT_QUOTES);
        } 
        
        switch ($btn)
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
                        <table class='table table-striped w-75'>
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
                        <tr>
                            <th scope='row'>{$count}</th>
                            <td>{$filePath}</td>
                            <td>{$result}</td>
                            </tr>
                        <tr>";
                    }
                    
                    echo "</tbody></table>";
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

                    foreach ($filesInDir as $file)
                    {
                        $count++;

                        echo "
                            <tr>
                                <th scope='row'>{$count}</th>
                                <td>{$file}</td>
                                </tr>
                            <tr>
                        ";
                    }
                    echo "</tbody></table>";
                }
                            
                break;
            }
        }
    ?>
    
    </div>
</body>
</html>
