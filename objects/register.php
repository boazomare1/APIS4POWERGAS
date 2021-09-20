<?php
class Register{

    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function registerUser($username, $email, $password, $phone, $first_name, $last_name){
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = "";
        $full_name = $first_name." ".$last_name;

        try {
            $query = "SELECT * FROM sma_users WHERE email = ?";

            // prepare query statement
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $email);


            // execute query
            $stmt->execute();
            if($stmt->rowCount() == 0){
            $query3 = "INSERT INTO sma_companies (group_id, group_name, name, phone, email, is_subsidiary, also_distributor, password) VALUES (?,?,?,?,?,?,?,?)" ;

                $stmt3 = $this->conn->prepare($query3);
                $stmt3->bindValue(1, 12);
                $stmt3->bindValue(2, "distributor");
                $stmt3->bindParam(3, $full_name);
                $stmt3->bindParam(4, $phone);
                $stmt3->bindParam(5, $email);
                $stmt3->bindValue(6, 0);
                $stmt3->bindValue(7, "Y");
                $stmt3->bindParam(8, password_hash($password, PASSWORD_BCRYPT));
                $stmt3->execute();
                $company_id = $this->conn->lastInsertId();
                
                $query2 = "INSERT INTO sma_users (username, password, email, active, first_name, last_name, phone, gender, group_id, company_id) VALUES (?,?,?,?,?,?,?,?,?,?)" ;

                $stmt2 = $this->conn->prepare($query2);
                $stmt2->bindValue(1, $username);
                $stmt2->bindValue(2, password_hash($password, PASSWORD_BCRYPT));
                $stmt2->bindValue(3, $email);
                $stmt2->bindValue(4, 0);
                $stmt2->bindParam(5, $first_name);
                $stmt2->bindParam(6, $last_name);
                $stmt2->bindValue(7, $phone);
                $stmt2->bindValue(8, "male");
                $stmt2->bindValue(9, 12);
                $stmt2->bindParam(10, $company_id);
                $stmt2->execute();

                $response= array("success" => "1", "message" => "Registration successful");
                
            }else{
                $response= array("success" => "0", "message" => "The user you entered already exist");
            }
            
            } catch (Exception $e) {
            print_r($e);
                http_response_code(404);
                $response= array("success" => "2", "message" => "error occured");
            }
        

        return $response;
    }
    
}