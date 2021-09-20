<?php
    $host = "localhost";
    $db_name = "techsava_powergas_mpesa";
    $username = "techsava_powergas_mpesa";
    $password = "techsava_powergas_mpesa";
  
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    
    $checkoutRequestID = isset($_GET['checkoutRequestID']) ? $_GET['checkoutRequestID'] : die();
    
    echo json_encode(confirmURL($conn, $checkoutRequestID));
  
     function confirmURL($conn, $checkoutRequestID){
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
              
            try {
                $sql = "SELECT * FROM mpesa_transactions WHERE checkoutRequestID =? LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(1, $checkoutRequestID);
                $stmt->execute();
                if($stmt->rowCount()>0){
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    return array("success"=>"1", "message"=>"Payment Successful", "response"=>$row);
                }else{
                   return array("success"=>"0", "message"=>"Payment not successful");
                }
                
            } catch (Exception $ex) {
                print_r($ex);
                return array("success"=>"2", "message"=>"An error occured... Please try again");
            }
            
     }

?>
