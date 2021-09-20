<?php

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require '../config/database.php';
// Step 1: set your API_KEY from https://mywebhost.com/sms-api/info

 $database = new Database();
 global $conn;
$conn = $database->getConnection();
 
 $action = isset($_GET['action']) ? $_GET['action'] : die();


if($action == "confirm_code"){
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    foreach($data as $codeObj){
        $response = confirmCode($codeObj->verification_code, $codeObj->signature, $codeObj->user_id, $codeObj->customer_id, $codeObj->cheque_image);
    } 
    echo json_encode($response);
    
}


function confirmCode($verification_code, $signature, $user_id, $customer_id, $cheque_image){
        global $conn;
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = "";
        $date=date('Y-m-d H:i:s');

        try {
            $query ="SELECT * FROM sma_verify_code  WHERE token=?  AND expiry >? AND user_id=? AND customer_id =?";
            // prepare query statement
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $verification_code);
            $stmt->bindParam(2, $date);
            $stmt->bindParam(3, $user_id);
            $stmt->bindParam(4, $customer_id);

            // execute query
            $stmt->execute();
            if($stmt->rowCount() > 0){
                
                $time = time();
                $path = "../uploads/$time.png";
                $path2 = "../uploads/Cheque$time.png";
                $final_path = "https://powergas-home.techsavanna.technology/powergas_app/uploads/".$time.".png";
                $final_path2 = "https://powergas-home.techsavanna.technology/powergas_app/uploads/Cheque".$time.".png";
                if(file_put_contents($path,base64_decode($signature))){
                    if($cheque_image != null){
                        if(file_put_contents($path2,base64_decode($cheque_image))){
                        $response = array("success" => "1", "message" => "Verification code successful", "image_url"=>$final_path, "cheque_image"=>$final_path2);
                    }else{
                       $response= array("success" => "7", "message" => "an error occured please try again."); 
                    }
                    
                    }else{
                         $response = array("success" => "1", "message" => "Verification code successful", "image_url"=>$final_path, "cheque_image"=>null);
                    }
                    
                }else{
                 http_response_code(404);
                  $response= array("success" => "3", "message" => "an error occured please try again.");
                   }

            }else{
                $response= array("success" => "0", "message" => "Verification not successfull please try again");
            }
            
            } catch (Exception $e) {
            print_r($e);
                http_response_code(404);
                $response= array("success" => "2", "message" => "error occured");
            }
        return $response;
    } 
 
 ?>