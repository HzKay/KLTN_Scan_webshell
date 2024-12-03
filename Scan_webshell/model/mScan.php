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
                SELECT t.TenTep, t.ViTriTep, mb.MaBam, ct.KetQua, ct.HanhDong
                FROM ketquaquet kq 
                INNER JOIN chitietketqua ct ON kq.MaQuet = ct.MaQuet
                INNER JOIN tep t ON t.MaTep = ct.MaTep
                INNER JOIN mabam mb ON t.MaTep = mb.MaTep
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

        public function addResultScan ($location)
        {
            $query = "INSERT INTO ketquaquet  (ViTriQuet)  VALUES (?)";
            $resultExec = $this->execRequesId($query, $location);
            if ($resultExec > 0)
            {
                return $resultExec;
            }

            return -1;
        }

        public function getLastScanId ()
        {
            $query = "SELECT MaQuet FROM ketquaquet ORDER BY MaQuet DESC LIMIT 1";
            $resultExec = $this->execRequestDB($query);

            $numRow = mysqli_num_rows($resultExec);
            if ($numRow > 0) {
                while ($row = mysqli_fetch_assoc($resultExec)) {
                    return $row["MaQuet"];
                }
            }

            return -1;
        }

        public function isExistFile ($fileName, $filePath) 
        {
            $query = "
                SELECT maTep 
                FROM tep 
                WHERE TenTep = ? AND ViTriTep = ?
                LIMIT 1;
            ";
            $result = $this->execRequestDB($query, $fileName, $filePath);

            $numRow = mysqli_num_rows($result);
            if ($numRow == 1) {
                while ($row = mysqli_fetch_assoc($result)) {
                    return $row["maTep"];
                }
            }
            return -1;
        }

        public function addOrUpdateFile ($file, $action, $idFile = -1)
        {
            $fileInfo = pathinfo($file->filePath);
            $directory = $fileInfo['dirname'] . DIRECTORY_SEPARATOR;
            $fileName = $fileInfo['basename'];
            $fileExt = $fileInfo['extension'];

            switch ($action)
            {
                case 'upload': {
                    if ($idFile > 0)
                    {
                        $query = "
                            UPDATE tep
                            SET 
                                KichThuoc = ?,
                                LoaiTep = ?,
                                ViTriTep = ?
                            WHERE 
                                maTep = ?;
                        ";
                        
                        $result = $this->execRequestDB($query, $file->size, $fileExt, $directory, $idFile);
                    } else {
                        $result = -1;
                    }
                    
                    break;
                }
                case 'add': {
                    $query = "
                        INSERT INTO tep
                            (TenTep, KichThuoc, LoaiTep, ViTriTep)
                        VALUES 
                            (?, ?, ?, ?);
                    ";
                    
                    $result = $this->execRequesId($query, $fileName, $file->size, $fileExt, $directory);
                    break;
                }
                default: {
                    $result = -1;
                    break;
                }
            }

            return $result;
        }

        public function addHashFile ($idFile, $hash, $family=6)
        {
            $query = "
               INSERT INTO mabam
                    (MaBam, MaWebshell, MaTep)
                VALUES 
                    (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    MaTep = ?;
            ";
            $result = $this->execRequestDB($query, $hash, $family, $idFile, $idFile);

            if ($result == true) {
                return 0;
            }
            return -1;
        }

        public function addDetailScan ($idFile, $idScan, $result=1, $family=6)
        {
            $query = "
                INSERT INTO chitietketqua
                    (MaQuet, KetQua, MaWebshell, MaTep)
                VALUES (?, ?, ?, ?);
            ";
            $result = $this->execRequestDB($query, $idScan, $result, $family, $idFile);

            if ($result == true) {
                return 0;
            }

            return -1;
        }

        public function getAllSigns ()
        {
            $query = "SELECT Mau FROM chuky";
            $signs = array();
            $resultExec = $this->execRequestDB($query);
            $numRow = mysqli_num_rows($resultExec);

            if ($numRow > 0) {
                while ($row = mysqli_fetch_assoc($resultExec)) {
                    $signs[] = $row["Mau"];
                }

                return $signs;
            }

            return -1;
        }

        public function addFileScan (&$files, $scanId)
        {
            foreach ($files as $file)
            {
                $fileInfo = pathinfo($file->filePath);
                $directory = $fileInfo['dirname'] . DIRECTORY_SEPARATOR;
                $fileName = $fileInfo['basename'];

                $maTep = $this->isExistFile($fileName, $directory);

                if ($maTep > 0)
                {
                    $this->addOrUpdateFile($file, 'upload', $maTep);
                } else {
                    $maTep = $this->addOrUpdateFile($file, 'add');
                }
                
                $isAddHash = $this->addHashFile($maTep, $file->SHA256Hash);
                $isAddDetail = $this->addDetailScan($maTep, $scanId);

                if ($isAddHash == -1 || $isAddDetail == -1) 
                {
                    return -1;
                } else {
                    $file->maTep = $maTep;
                }
            }
            
            return 0;
        }

        public function addDataScan ($location, $files)
        {
            $idScan = $this->addResultScan($location);
           
            if ($idScan > 0)
            {
                $addfile = $this->addFileScan($files, $idScan); 
                return $addfile;
            }

            return -2;
        }

        public function addUpload ($idFile)
        {
            $query = "
                INSERT INTO tailen
                    (maTep)
                VALUES 
                    (v_maTep)
                ON DUPLICATE KEY UPDATE
                    ngayTaiLen = CURRENT_TIMESTAMP
            ";
            $result = $this->execRequestDB($query, $hash, $family, $idFile, $idFile);

            if ($result == true) {
                return 0;
            }
            return -1;
        }

        public function uploadFile ($file)
        {
            $fileInfo = pathinfo($file->filePath);
            $directory = $fileInfo['dirname'] . DIRECTORY_SEPARATOR;
            $fileName = $fileInfo['basename'];

            $maTep = $this->isExistFile($fileName, $directory);
            if ($maTep > 0)
            {
                $maTep = $this->addOrUpdateFile($file, 'upload', $maTep);
            } else {
                $maTep = $this->addOrUpdateFile($file, 'add');
            }        

            $isUpSuccess = $this->addUpload($maTep);
            if ($isUpSuccess == 0 && $maTep > 0)
            {
                return 0;
            }

            return -1;
        }
    }
?>