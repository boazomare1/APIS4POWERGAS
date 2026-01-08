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
        $cheque_image = isset($codeObj->cheque_image) ? $codeObj->cheque_image : null;
        $response = confirmCode($codeObj->verification_code, $codeObj->signature, $codeObj->user_id, $codeObj->customer_id, $cheque_image);
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
                // Use absolute path to avoid permission issues
                $upload_dir = dirname(__DIR__) . "/uploads/";
                
                // Ensure uploads directory exists and is writable
                if (!is_dir($upload_dir)) {
                    @mkdir($upload_dir, 0777, true);
                }
                
                $path = $upload_dir . $time . ".png";
                $path2 = $upload_dir . "Cheque" . $time . ".png";
                
                // Detect environment: local vs production
                $is_local = (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1' || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false));
                $base_url = $is_local ? "http://localhost:8075/api_powergas/uploads/" : "http://powergas.techsavanna.co.ke:8083/uploads/";
                
                $final_path = $base_url . $time . ".png";
                $final_path2 = $base_url . "Cheque" . $time . ".png";
                
                // Try to write signature image
                $signature_data = base64_decode($signature);
                if ($signature_data === false) {
                    error_log("Confirm SMS Error: Failed to decode signature base64");
                    $response = array("success" => "5", "message" => "Invalid signature data");
                    return $response;
                }
                
                $write_result = @file_put_contents($path, $signature_data);
                if($write_result === false){
                    $error_info = error_get_last();
                    $error_msg = $error_info ? $error_info['message'] : 'Unknown error';
                    $perms = @fileperms(dirname($path));
                    $perms_str = $perms ? substr(sprintf('%o', $perms), -4) : 'unknown';
                    error_log("Confirm SMS Error: Failed to write signature file. Path: $path, Error: $error_msg, Permissions: $perms_str, Upload dir: $upload_dir");
                    http_response_code(404);
                    $response = array("success" => "3", "message" => "Failed to save signature image. Path: $path, Error: $error_msg");
                    return $response;
                }
                
                // Signature saved successfully, now handle cheque image
                if($cheque_image != null && !empty($cheque_image)){
                    $cheque_data = base64_decode($cheque_image);
                    if ($cheque_data === false) {
                        error_log("Confirm SMS Error: Failed to decode cheque image base64");
                        $response = array("success" => "6", "message" => "Invalid cheque image data", "image_url"=>$final_path);
                    } else {
                        $cheque_write = @file_put_contents($path2, $cheque_data);
                        if($cheque_write === false){
                            $error_info = error_get_last();
                            $error_msg = $error_info ? $error_info['message'] : 'Unknown error';
                            error_log("Confirm SMS Error: Failed to write cheque image. Path: $path2, Error: $error_msg");
                            $response = array("success" => "7", "message" => "Failed to save cheque image", "image_url"=>$final_path);
                        } else {
                            $response = array("success" => "1", "message" => "Verification code successful", "image_url"=>$final_path, "cheque_image"=>$final_path2);
                        }
                    }
                } else {
                    $response = array("success" => "1", "message" => "Verification code successful", "image_url"=>$final_path, "cheque_image"=>null);
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
