<?php
    class clsLoginDB {
        private $host = "localhost";
        private $username = "kltn";
        private $password = "123456";
        private $database = "webshell_check";

        private function connectDB()
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

        public function execRequestDB ($query, ...$vars)
        {
            $conn = $this->connectDB();
            $stmt = mysqli_prepare($conn, $query);
            $typeVarList = "";

            if (!empty($vars)) {
                foreach ($vars as $var)
                {
                    $typeVar = gettype($var);
                    
                    switch($typeVar)
                    {
                        case "":
                            $typeVar = "d";
                            break;
                        case "integer":
                            $typeVar = "i";
                            break;
                        case "string":
                            $typeVar = "s";
                            break;
                        case "boolean":
                            $typeVar = "b";
                            break;
                    }
    
                    $typeVarList = $typeVarList . $typeVar;
                }
                
                mysqli_stmt_bind_param($stmt, $typeVarList, ...$vars);
            }            

            $isExecSuccess = mysqli_stmt_execute($stmt);

            if ($isExecSuccess) {
                $resultExec = mysqli_stmt_get_result($stmt);
                $this->closeConnectDB($conn);
                return $resultExec;
            }
            
            $this->closeConnectDB($conn);
            return mysqli_stmt_errno($stmt);
        }

        private function closeConnectDB($conn)
        {
            mysqli_close($conn);
        }
    }
?>