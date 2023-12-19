<?php
class Database{
  
    // specify your own database credentials
    private $host = "127.0.0.1";
    private $db_name = "techsava_powergas";
    private $username = "root";
    private $password = "Trymenot#123$";
    private $port = "3320";
    public $conn;
  
    // get the database connection
    public function getConnection(){
  
        $this->conn = null;
  
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name. ";port=" . $this->port, $this->username, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="TRADITIONAL"') );
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
  
        return $this->conn;
    }
}
?>
