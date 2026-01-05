<?php
/**
 * API Login Endpoint for PowerGas Mobile App
 */

// Convert GET params to POST if needed
if (!empty($_GET['email']) && empty($_POST['email'])) {
    $_POST['email'] = $_GET['email'];
    $_POST['password'] = isset($_GET['password']) ? $_GET['password'] : '';
}

// Make internal HTTP request to CodeIgniter API
$url = 'http://localhost/api/login';
$postData = http_build_query(array(
    'email' => isset($_POST['email']) ? $_POST['email'] : $_GET['email'],
    'password' => isset($_POST['password']) ? $_POST['password'] : (isset($_GET['password']) ? $_GET['password'] : '')
));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json');
http_response_code($httpCode);
echo $response;
