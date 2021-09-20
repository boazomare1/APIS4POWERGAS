<?php
include("../common/common.php");
class Expenses{
  
    // database connection and table name
    private $conn;
 
  
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
    public function fetchExpenses($salesman_id){
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        try {
            $query = "SELECT id, date, company_id, reference, amount, note, status FROM sma_expenses WHERE salesman_id=? AND date >= DATE(NOW()) - INTERVAL 7 DAY ORDER BY id DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $salesman_id);
            $stmt->execute();
            

            $response = array();
            if($stmt->rowCount()>0){
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                    $response[] = array(
                        "id"=>$row['id'],
                        "date"=>$row['date'],
                        "company_id"=>$row['company_id'],
                        "reference"=>$row['reference'],
                        "amount"=>intval($row['amount']),
                        "approved"=>intval($row['approved']),
                        "note"=>$row['note'],
                        "status"=>$row['status']);
                }

                return $response;
            }else{
                return array();
            }

        } catch (Exception $e) {
            $response= array("success"=>"0", "message"=>"Failed");
            print_r($e);
        }
        return $response;
    } 
    
    public function createExpense($company_id, $vehicle_id, $distributor_id, $salesman_id, $reference, $amount, $note, $created_by, $image){
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        date_default_timezone_set('Africa/Nairobi'); 
        $date = date('Y-m-d H:i:s');

        $response= "";
        
        $time = time();
        $path = "../uploads/$time.png";
        $final_path = "https://powergas-home.techsavanna.technology/powergas_app/uploads/".$time.".png";
        if(file_put_contents($path,base64_decode($image))){
            
        try {
            $query = "INSERT INTO sma_expenses(company_id, vehicle_id, distributor_id, salesman_id, reference, amount, note, created_by, attachment, date) VALUES(?,?,?,?,?,?,?,?,?,?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1,$company_id);
            $stmt->bindParam(2,$vehicle_id);
            $stmt->bindParam(3,$distributor_id);
            $stmt->bindParam(4,$salesman_id);
            $stmt->bindParam(5,$reference);
            $stmt->bindParam(6,$amount);
            $stmt->bindParam(7,$note);
            $stmt->bindParam(8,$created_by);
            $stmt->bindValue(9,$final_path);
            $stmt->bindValue(10,$date);
            $stmt->execute();

            $response = array("success"=>"1", "message"=>"Expense created successfully");
            
        } catch (Exception $e) {
            print_r($e);
            $response = array("success"=>"0", "message"=>"Expense not created");
        }
        }else{
            http_response_code(404);
            $response= array("success" => "3", "message" => "image could not be inserted");
        }

        return $response;
    }
    
    
}
?>