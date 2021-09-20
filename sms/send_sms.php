<?php
require '../config/database.php';
// Step 1: set your API_KEY from https://mywebhost.com/sms-api/info
 
 $customer_phone = isset($_GET['customer_phone']) ? $_GET['customer_phone']  : fieldRequired('customer_phone');
 $user_id = isset($_GET['user_id']) ? $_GET['user_id']  : fieldRequired('user_id');
 $customer_id = isset($_GET['customer_id']) ? $_GET['customer_id']  : fieldRequired('customer_id');

$api_key = 'UG93ZXJHYXM6cG93ZXJnYXMwMDE=';

$database = new Database();
$conn = $database->getConnection();

// Step 2: Change the from number below. It can be a valid phone number or a String
$from = 'POWER_GAS';

$pin = mt_rand(1000, 9999);

// Step 3: the number we are sending to - Any phone number. You must have to insert country code at beginning of the number
$destination = "254".ltrim($customer_phone, '0');

// Step 4: Replace your Install URL like https://mywebhost.com/sms/api with https://portal.paylifesms.com/sms/api is mandatory.

$url = 'https://portal.paylifesms.com/sms/api';

// the sms body
$sms = 'Your verification code is '.$pin.'. Note the code expires within 10 minutes.';
        

// Create SMS Body for request
$sms_body = array(
    'action' => 'send-sms',
    'api_key' => $api_key,
    'to' => $destination,
    'from' => $from,
    'sms' => $sms
);

$send_data = http_build_query($sms_body);

$gateway_url = $url . "?" . $send_data;

try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $gateway_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPGET, 1);
    $output = curl_exec($ch);

    if (curl_errno($ch)) {
        $output = curl_error($ch);
    }
    curl_close($ch);

    $query= "insert into sma_verify_code (user_id,token,expiry,customer_id) values (?,?,?,?)";
            // prepare query statement
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->bindValue(2, $pin);
            $stmt->bindValue(3, date('Y-m-d H:i:s', strtotime("+10 min")));
            $stmt->bindParam(4, $customer_id);
  
            // execute query
            $stmt->execute();

}catch (Exception $exception){
    echo $exception->getMessage();
}

function fieldRequired($fieldName){
    return $fieldName."Is required";
}