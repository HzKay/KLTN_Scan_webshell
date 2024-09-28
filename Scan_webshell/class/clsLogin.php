<?php
    class clsLogin {
        private $host = "localhost";
        private $username = "kltn";
        private $password = "123456";
        private $database = "webshell_check";

        public function connectDB()
        {
            $conn=mysqli_connect($this->host,$this->username,$this->password,$this->database);
            if(!$conn)
            {
                echo 'Không kết nối với CSDL';
                exit();
            }
            else
            {
                mysqli_query($conn,"SET NAMES UTF8");
                return $conn;
            }
        }
    }
?>