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
                
                if(gettype($filesInDir) == "string")
                {
                    echo $filesInDir;
                } else {
                    $_SESSION["result_scan"] = $resultScan;
                }
            
                break;
            }
            case "Lấy kết quả": {
                header("content-Type: application/json; charset=UTF-8");
                if (isset($_SESSION["result_scan"]))
                {
                    $p->restartProgress();
                    echo json_encode($_SESSION["result_scan"]);
                }
                break;
            }
        }
    }
?>
