<?php
class Database{
  
    // specify your own database credentials
    private $host = "23.236.60.20";
    private $db_name = "techsava_powergas";
    private $username = "db-user";
    private $password = "Trymenot#123$";
    private $port = "3306";
    public $conn;
  
    // get the database connection
    public function getConnection(){
  
        $this->conn = null;
  
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name. ";port=" . $this->port, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
  
        return $this->conn;
    }
}
?>