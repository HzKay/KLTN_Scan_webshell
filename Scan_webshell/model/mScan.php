<?php
    include_once ("./class/clsLogin.php");

    class mScan extends clsLoginDB {
        public function getDataFamilyShell ($hash)
        {
            $query = "SELECT TenMau, ThongTin  FROM mauwebshell  WHERE MaWebshell = ?";

            $resultExec = $this->execRequestDB($query, $hash);
            $numRow = mysqli_num_rows($resultExec);
            if ($numRow >= 0) {
                while ($row = mysqli_fetch_array($resultExec))
                {
                    $result = array("TenMau" => $row["TenMau"], "ThongTin" => $row["ThongTin"]);
                    return $result;
                }
            }

            return -1;
        }

        public function findHash ($hash)
        {
            $query = "SELECT mb.MaWebshell FROM mabam mb  LEFT JOIN chitietketqua ct  ON mb.MaTep = ct.MaTep  WHERE (ct.HanhDong = 2 OR ct.MaTep IS NULL) AND MaBam = ? LIMIT 1";
            $resultExec = $this->execRequestDB($query, $hash);
            $numRow = mysqli_num_rows($resultExec);
            if ($numRow > 0) {
                while ($row = mysqli_fetch_assoc($resultExec)) {
                    return $row["MaWebshell"];
                }
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
            $query = "SELECT ViTriQuet, DATE_FORMAT(NgayQuet, '%d/%m/%Y') AS NgayQuet FROM ketquaquet ORDER BY MaQuet DESC LIMIT 1;";
            $resultExec = $this->execRequestDB($query);

            $numRow = mysqli_num_rows($resultExec);
            if ($numRow > 0) {
                while ($row = mysqli_fetch_assoc($resultExec)) {
                    $result = array("viTriQuet"=>$row["ViTriQuet"], "ngayQuet"=>$row["NgayQuet"]);
                }

                return $result;
            }
            return -1;
        }

        public function delUploadFile ($fileName, $filePath)
        {
            $query = "DELETE FROM tep WHERE TenTep = ? AND ViTriTep = ?";
            $resultExec = $this->execRequestDB($query, $fileName, $filePath);

            if ($resultExec == true)
            {
                return 0;
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
            $query = "SELECT MaChuKy, Mau FROM chuky ORDER BY MaChuKy ASC";
            $signs = array();
            $resultExec = $this->execRequestDB($query);
            $numRow = mysqli_num_rows($resultExec);

            if ($numRow > 0) {
                while ($row = mysqli_fetch_assoc($resultExec)) {
                    $signs[] = array($row["Mau"], $row["MaChuKy"]);
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
                $isAddSigns = 0;

                $maTep = $this->isExistFile($fileName, $directory);

                if ($maTep > 0)
                {
                    $this->addOrUpdateFile($file, 'upload', $maTep);
                } else {
                    $maTep = $this->addOrUpdateFile($file, 'add');
                }
                
                $isAddHash = $this->addHashFile($maTep, $file->SHA256Hash);
                $isAddDetail = $this->addDetailScan($maTep, $scanId);
              
                if (count($file->signSample) > 0)
                {
                    $isAddSigns = $this->addSignsFile($maTep, $file->signSample);
                }                

                if ($isAddHash == -1 || $isAddDetail == -1 || $isAddSigns == -1) 
                {
                    return -1;
                } else {
                    $file->maTep = $maTep;
                }
            }
            
            return 0;
        }

        public function addSignsFile ($maTep ,$signs)
        {
            $place = implode(',', array_fill(0, count($signs[0]), "(?, ?)"));
            $params = [];

            foreach ($signs[0] as $item) {
                $params[] = $maTep;  
                $params[] = $item;  
            }

            $query = "INSERT INTO tep_chuky  (maTep, maChuKy)  VALUES {$place}";
            $result = $this->execRequestDB($query, ...$params);

            if ($result == true) {
                return 0;
            }
            return -1;
        }

        public function addSignsSync ($sign, $family)
        {
            $queryInSigns = "INSERT INTO chuky (Mau)  VALUES (?)";
            $idSign = $this->execRequesId($queryInSigns, $sign);
            
            if ($idSign > 0) {
                if ($family != 'None')
                {
                    $queryAddShell = "INSERT INTO shell_chuky (maWebshell, maChuKy) VALUES (?, ?)";
                    $result = $this->execRequestDB($queryAddShell, $family, $idSign);

                    if($result == true)
                    {
                        return 0;
                    } else {
                        return -1;
                    }
                } else {
                    return 0;
                }
            }

            return -1;
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
                    (?)
                ON DUPLICATE KEY UPDATE
                    ngayTaiLen = CURRENT_TIMESTAMP
            ";
            
            $result = $this->execRequestDB($query, $idFile);

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
                $this->addOrUpdateFile($file, 'upload', $maTep);
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

        public function getUploadFiles ()
        {
            require_once ('object/objectFile.php');
            $fileList = array();
            $query = "SELECT t.MaTep, tl.ngayTaiLen, t.TenTep, t.KichThuoc, t.LoaiTep, t.ViTriTep FROM tailen tl LEFT JOIN tep t ON tl.maTep = t.MaTep";

            $resultExec = $this->execRequestDB($query);
            $numRow = mysqli_num_rows($resultExec);
            if ($numRow > 0) {
                while ($row = mysqli_fetch_assoc($resultExec)) {
                    $filePath = $row['ViTriTep'] . $row['TenTep'];
                    $file = new ObjectFile;
                    $file->setInfo($filePath, $row['KichThuoc'], $row['LoaiTep'], '', '');
                    $file->setDate($row['ngayTaiLen']);
                    $file->setMaTep($row['MaTep']);
                    
                    $fileList[] = $file;
                }
            }

            return $fileList;
        }
    }
?>