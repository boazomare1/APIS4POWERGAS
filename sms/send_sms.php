<?php
require '../config/database.php';
 
 $customer_phone = isset($_GET['customer_phone']) ? $_GET['customer_phone']  : fieldRequired('customer_phone');
 $user_id = isset($_GET['user_id']) ? $_GET['user_id']  : fieldRequired('user_id');
 $customer_id = isset($_GET['customer_id']) ? $_GET['customer_id']  : fieldRequired('customer_id');

// Onfon Media SMS API credentials
$api_key = 'jJtqOshLAUu13Qxv6W4aBoGT5D8RXpM097mKirVHPzFdgkI2';
$client_id = 'benson';
$access_key = 'benson';
$sender_id = 'Power_Gas';

$database = new Database();
$conn = $database->getConnection();

// Generate 4-digit verification code
$pin = mt_rand(1000, 9999);

// Format phone number with country code (254 for Kenya)
$destination = "254".ltrim($customer_phone, '0');

// SMS message body
$sms_text = 'Your verification code is '.$pin.'. Note the code expires within 10 minutes.';

// Onfon Media API endpoint
$url = 'https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS';

// Prepare JSON request body for Onfon Media API
$request_body = array(
    'SenderId' => $sender_id,
    'MessageParameters' => array(
        array(
            'Number' => $destination,
            'Text' => $sms_text
        )
    ),
    'ApiKey' => $api_key,
    'ClientId' => $client_id
);

$json_data = json_encode($request_body);

try {
    // Initialize cURL for POST request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'AccessKey: ' . $access_key,
        'Content-Type: application/json'
    ));
    
    $output = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $output = curl_error($ch);
        error_log("SMS API Error: " . $output);
    }
    curl_close($ch);

    // Save verification code to database
    $query = "insert into sma_verify_code (user_id,token,expiry,customer_id) values (?,?,?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(1, $user_id);
    $stmt->bindValue(2, $pin);
    $stmt->bindValue(3, date('Y-m-d H:i:s', strtotime("+10 min")));
    $stmt->bindParam(4, $customer_id);
    $stmt->execute();

}catch (Exception $exception){
    echo $exception->getMessage();
    error_log("SMS Exception: " . $exception->getMessage());
}

function fieldRequired($fieldName){
    return $fieldName."Is required";
}