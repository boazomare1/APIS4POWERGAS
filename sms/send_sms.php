<?php

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/database.php';

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : null;

if (!$user_id || !$customer_id) {
    echo json_encode(array("success" => "0", "message" => "user_id and customer_id are required"));
    exit;
}

// Onfon Media SMS API credentials - should be moved to environment variables in production
$api_key = getenv('SMS_API_KEY') ?: 'jJtqOshLAUu13Qxv6W4aBoGT5D8RXpM097mKirVHPzFdgkI2';
$client_id = getenv('SMS_CLIENT_ID') ?: 'benson';
$access_key = getenv('SMS_ACCESS_KEY') ?: 'benson';
$sender_id = 'Power_Gas';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode(array("success" => "0", "message" => "Database connection failed"));
    exit;
}

// Fetch phone number directly from database to ensure we use the latest/correct number
// This prevents issues with cached customer data in the mobile app
$customer_phone = null;
if (isset($_GET['customer_phone'])) {
    $customer_phone = $_GET['customer_phone'];
}

// Always fetch from database to ensure accuracy
$phone_query = "SELECT phone FROM sma_customers WHERE id = ?";
$phone_stmt = $conn->prepare($phone_query);
$phone_stmt->bindParam(1, $customer_id, PDO::PARAM_INT);
$phone_stmt->execute();

if ($phone_stmt->rowCount() > 0) {
    $phone_row = $phone_stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($phone_row['phone'])) {
        $customer_phone = $phone_row['phone'];
    }
}

if (empty($customer_phone)) {
    error_log("SMS Error: No phone number found for customer_id: $customer_id");
    echo json_encode(array("success" => "0", "message" => "Customer phone number not found in database"));
    exit;
}

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
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log("SMS API Error: " . $curl_error);
        curl_close($ch);
        echo json_encode(array("success" => "0", "message" => "Failed to send SMS: " . $curl_error));
        exit;
    }
    curl_close($ch);

    // Parse SMS API response
    $sms_response = json_decode($output, true);

    // Save verification code to database regardless of SMS API response
    // This ensures the code is available even if SMS fails
    $query = "INSERT INTO sma_verify_code (user_id, token, expiry, customer_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $pin, PDO::PARAM_STR);
    $stmt->bindValue(3, date('Y-m-d H:i:s', strtotime("+10 min")), PDO::PARAM_STR);
    $stmt->bindParam(4, $customer_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        error_log("SMS: Verification code generated and saved. PIN: $pin, Customer: $customer_id, Phone: $customer_phone");
        echo json_encode(array(
            "success" => "1",
            "message" => "Verification code sent successfully",
            "code" => $pin, // Include code for debugging (remove in production)
            "phone" => $customer_phone
        ));
    } else {
        error_log("SMS Error: Failed to save verification code to database");
        echo json_encode(array("success" => "0", "message" => "Failed to save verification code"));
    }

} catch (Exception $exception) {
    error_log("SMS Exception: " . $exception->getMessage());
    echo json_encode(array("success" => "0", "message" => "An error occurred: " . $exception->getMessage()));
}
