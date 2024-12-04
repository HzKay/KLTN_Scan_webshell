<?php
    class statusScan 
    {
        public function __construct(){
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }

        public function updateProgress() {
            $data = array();
            $percent = 0;
            // Lấy tổng số file đã quét được
            $totalFiles = $this->getTotalFiles();
            // Lấy số file đã kiểm tra hiện tại
            $currentFile = $this->getCurrentProcess();

            if($totalFiles != 0)
            {
                $percent = round(($currentFile/$totalFiles)*100);
            }

            $data[] = array("current"=>$currentFile, "percent"=>$percent);
            file_put_contents('progress.json', json_encode($data)); // Lưu tiến trình vào file
        }

        public function restartProgress()
        {
            $data[] = array("total"=>0, "current"=>0, "percent"=>0);
            file_put_contents('progress.json', json_encode($data)); 
        }

        public function getTotalFiles ()
        {
            return $_SESSION["totalFiles"];
        }

        public function setTotalFiles ($totalFiles)
        {
            $_SESSION["totalFiles"] = $totalFiles;
        }

        public function getCurrentProcess ()
        {
            return $_SESSION["currentProcess"];
        }

        public function setCurrentProcess ($now)
        {
            $_SESSION["currentProcess"] = $now;
            $this->updateProgress();
        }
        
        public function getScanLocation ()
        {
            return $_SESSION["scanLocation"];
        }

        public function setScanLocation ($location)
        {
            $_SESSION["scanLocation"] = $location;
        }

    }
?>