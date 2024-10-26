<?php
    include_once ("./class/clsLogin.php");

    class modelScan extends clsLoginDB {
        public function findHash ($hash)
        {
            $query = "SELECT id FROM hashofwebshell WHERE Hash_code = ? LIMIT 1";
            $resultExec = $this->execRequestDB($query, $hash);
            $numRow = mysqli_num_rows($resultExec);

            if ($numRow > 0) {
                while ($row = mysqli_fetch_assoc($resultExec)) {
                    return $row["id"];
                }
            }

            return -1;
        }
        
        public function importHashToDB ($hash)
        {
            $query = "INSERT INTO id FROM hashofwebshell WHERE Hash_code = ? LIMIT 1";
            $resultExec = $this->execRequestDB($query, $hash);
            $numRow = mysqli_num_rows($resultExec);

            if ($numRow > 0) {
                while ($row = mysqli_fetch_assoc($resultExec)) {
                    return $row["id"];
                }
            }

            return -1;
        }
    }
?>