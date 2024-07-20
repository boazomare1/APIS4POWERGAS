<?php
include("../common/common.php");
class Expenses{
  
    // database connection and table name
    private $conn;
 
  
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
    
    public function fetchExpenses($salesman_id) {
        // Set PDO error mode to exception and disable emulated prepares
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
        // Initialize response array
        $response = array();
    
        try {
            // Prepare the SQL query with named parameters
            $query = "SELECT id, date, company_id, reference, amount, note, status 
                      FROM sma_expenses 
                      WHERE salesman_id = :salesman_id 
                      AND date >= DATE(NOW()) - INTERVAL 7 DAY 
                      ORDER BY id DESC";
    
            $stmt = $this->conn->prepare($query);
    
            // Bind the salesman_id parameter
            $stmt->bindParam(':salesman_id', $salesman_id, PDO::PARAM_INT);
    
            // Execute the query
            $stmt->execute();
    
            // Log for debugging
            error_log("Executed query: $query with salesman_id: $salesman_id");
    
            // Fetch results
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $response[] = array(
                        "id" => $row['id'],
                        "date" => $row['date'],
                        "company_id" => $row['company_id'],
                        "reference" => $row['reference'],
                        "amount" => intval($row['amount']),
                        "note" => $row['note'],
                        "status" => $row['status']
                    );
                }
            } else {
                // Log no records found for debugging
                error_log("No records found for salesman_id: " . $salesman_id);
            }
    
        } catch (PDOException $e) {
            // Handle PDO exceptions
            $response = array("success" => "0", "message" => "Failed: " . $e->getMessage());
            error_log("PDOException: " . $e->getMessage());
        }
    
        // Return response array
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