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
                    $_SESSION["result_scan"] = json_encode( $resultScan);
                }
            
                break;
            }
            case "Lấy kết quả": {
                header("content-Type: application/json; charset=UTF-8");
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: POST");
                header(header: "Access-Control-Allow-Headers: Content-Type, Authorization"); 
                if (isset($_SESSION["result_scan"]))
                {
                    $p->restartProgress();
                    echo $_SESSION["result_scan"];
                }
                break;
            }
            case "Lấy nội dung": {
                require_once ('./object/objectFile.php');
                header("content-Type: text/html; charset=UTF-8");
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: POST");
                header("Access-Control-Allow-Headers: Content-Type, Authorization"); 
                
                if (isset($_SESSION["result_scan"]))
                {
                    $idFile = $_POST['idFile'];
                    $files = json_decode($_SESSION["result_scan"]);
                    $content = $p->showContentFile($files, $idFile);

                    echo $content;
                }
                break;
            }
        }
    }
?>
