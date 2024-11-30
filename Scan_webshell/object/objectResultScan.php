<?php 
    class ObjectResultScan 
    {
        public $maTep;
        public $tenTep;
        public $viTri;
        public $ngayTaiLen;
        public $maBam;
        public $ketQua;
        public $hanhDong;

        public function __construct($tenTep, $viTri, $ngayTaiLen, $maBam, $ketQua, $hanhDong)
        {
            $this->tenTep = $tenTep;
            $this->viTri = $viTri;
            $this->ngayTaiLen = $ngayTaiLen;
            $this->maBam = $maBam;
            $this->ketQua = $ketQua;
            $this->hanhDong = $hanhDong;
            $this->maTep = 0;
        }

        public function getMaTep ()
        {
            return $this->maTep;
        }

        public function setMaTep($maTep)
        {
            $this->maTep = $maTep;
        }

        public function getTenTep ()
        {
            return $this->tenTep;
        }
        
        public function setTenTep ($tenTep)
        {
            $this->tenTep = $tenTep;
        }

        public function getViTri ()
        {
            return $this->viTri;
        }
        
        public function setViTri ($viTri)
        {
            $this->viTri = $viTri;
        }

        public function getNgayTaiLen ()
        {
            return $this->ngayTaiLen;
        }
        
        public function setNgayTaiLen ($ngayTaiLen)
        {
            $this->ngayTaiLen = $ngayTaiLen;
        }

        public function getMaBam ()
        {
            return $this->maBam;
        }
        
        public function setMaBam ($maBam)
        {
            $this->maBam = $maBam;
        }

        public function getKetQua ()
        {
            return $this->ketQua;
        }
        
        public function setKetQua ($ketQua)
        {
            $this->ketQua = $ketQua;
        }

        public function getHanhDong ()
        {
            return $this->hanhDong;
        }
        
        public function setHanhDong ($hanhDong)
        {
            $this->hanhDong = $hanhDong;
        }
    }
?>