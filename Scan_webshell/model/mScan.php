<?php
    include_once ("./class/clsLogin.php");

    class mScan extends clsLoginDB {
        public function findHash ($hash)
        {
            $query = "SELECT MaWebshell FROM mabam WHERE MaBam = ? LIMIT 1";
            $resultExec = $this->execRequestDB($query, $hash);
            $numRow = mysqli_num_rows($resultExec);
            if ($numRow > 0) {
                while ($row = mysqli_fetch_assoc($resultExec)) {
                    return $row["id"];
                }
            }

            return -1;
        }
        
        public function importHashToDB ($family, $hash)
        {
            $query = "INSERT INTO mabam  (MaBam, MaWebshell)  VALUES (?, ?)";
            $resultExec = $this->execRequestDB($query, $hash, $family);
            if ($resultExec)
            {
                return 0;
            }

            return -1;
        }

        public function getScanHistory ()
        {
            $query = "
                SELECT CONVERT(NgayQuet, DATE) AS ngayThucHien, COUNT(t.TenTep) AS soWebshell
                FROM ketquaquet kq 
                INNER JOIN chitietketqua ct ON kq.MaQuet = ct.MaQuet
                INNER JOIN tep t ON t.MaTep = ct.MaTep
                WHERE ct.KetQua = 1
                GROUP BY CONVERT(NgayQuet, DATE)
                ORDER BY ngayThucHien ASC
                LIMIT 30;
            ";
            $resultExec = $this->execRequestDB($query);
            $numRow = mysqli_num_rows($resultExec);
            if ($numRow >= 0) {
                return $resultExec;
            }

            return -1;
        }

        public function getFilesRecentScan ()
        {
            $query = "
                SELECT t.TenTep, t.ViTriTep, t.NgayTaiLen, t.MaBam, ct.KetQua, ct.HanhDong
                FROM ketquaquet kq 
                INNER JOIN chitietketqua ct ON kq.MaQuet = ct.MaQuet
                INNER JOIN tep t ON t.MaTep = ct.MaTep
                WHERE kq.MaQuet = (SELECT MaQuet FROM ketquaquet ORDER BY MaQuet DESC LIMIT 1);
            ";

            $resultExec = $this->execRequestDB($query);
            $numRow = mysqli_num_rows($resultExec);
            if ($numRow >= 0) {
                return $resultExec;
            }

            return -1;
        }

        public function getDateRecentScan ()
        {
            $query = "SELECT DATE_FORMAT(NgayQuet, '%d/%m/%Y') AS NgayQuet FROM ketquaquet ORDER BY MaQuet DESC LIMIT 1;";

            $resultExec = $this->execRequestDB($query);
            $numRow = mysqli_num_rows($resultExec);
            if ($numRow > 0) {
                while ($row = mysqli_fetch_assoc($resultExec)) {
                    return $row["NgayQuet"];
                }
            }
            return -1;
        }
    }
?>