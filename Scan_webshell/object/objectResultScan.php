<?php 
    class ObjectResultScan 
    {
        public $maTep;
        public $tenTep;
        public $viTri;
        public $maBam;
        public $ketQua;
        public $hanhDong;

        public function __construct($tenTep, $viTri, $maBam, $ketQua, $hanhDong)
        {
            $this->tenTep = $tenTep;
            $this->viTri = $viTri;
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