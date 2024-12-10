<?php 
    class ObjectFile {
        
        public $maTep;
        public $filePath;
        public $size;
        public $type;
        public $SHA256Hash;
        public $signature;

        public $signSample = array();
        public $date;

        public function setInfo($filePath, $size, $type, $SHA256Hash, $signature) {
            $this->filePath = $filePath;
            $this->size = $size;
            $this->type = $type;
            $this->SHA256Hash = $SHA256Hash;
            $this->signature = $signature;
            $this->maTep = 0;
            $this->date = null;
        }
        public function getMaTep ()
        {
            return $this->maTep;
        }

        public function setMaTep($maTep)
        {
            $this->maTep = $maTep;
        }

        public function getFilePath() {
            return $this->filePath;
        }
    
        public function setFilePath($filePath) {
            $this->filePath = $filePath;
        }
    
        public function getSize() {
            return $this->size;
        }
    
        public function setSize($size) {
            $this->size = $size;
        }
    
        public function getType() {
            return $this->type;
        }
        
        public function setType($type) {
            $this->type = $type;
        }
    
        public function getSHA256Hash() {
            return $this->SHA256Hash;
        }
    
        public function setSHA256Hash($SHA256Hash) {
            $this->SHA256Hash = $SHA256Hash;
        }
    
        public function getSignature() {
            return $this->signature;
        }
    
        public function setSignature($signature) {
            $this->signature = $signature;
        }

        public function getDate() {
            return $this->date;
        }

        public function setDate($date) {
            $this->date = $date;
        }

        public function addSignSample($feature) {
            $this->signSample[] = $feature;
        }

        public function getSignSample() {
            return $this->signSample;
        }
    }
?>