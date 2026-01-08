<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Get the base directory - use absolute path
$base_dir = '/opt/lampp/htdocs/api_powergas';

require_once $base_dir . '/config/database.php';
require_once $base_dir . '/objects/login.php';

// Get email and password from GET or POST
$email = isset($_GET['email']) ? $_GET['email'] : (isset($_POST['email']) ? $_POST['email'] : '');
$password = isset($_GET['password']) ? $_GET['password'] : (isset($_POST['password']) ? $_POST['password'] : '');
$app_version = isset($_GET['app_version']) ? $_GET['app_version'] : (isset($_POST['app_version']) ? $_POST['app_version'] : null);

// Support both old format (with action) and new format (without action)
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'login_user');

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(array("success" => "0", "message" => "Email and password are required"));
    exit;
}

if($action == "login_user"){
    $database = new Database();
    $conn = $database->getConnection();
    
    $login = new Login($conn);
    $response = $login->loginUser($email, $password, $app_version);
    
    http_response_code(200);
    echo json_encode($response);
    exit;
}

http_response_code(400);
echo json_encode(array("success" => "0", "message" => "Invalid action"));
exit;
