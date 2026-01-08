<?php
// required headers
// Note: CORS policy allows all origins - this is intentional for mobile app API access
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Get the base directory - use absolute path
$base_dir = '/opt/lampp/htdocs/api_powergas';

require_once $base_dir . '/config/database.php';
require_once $base_dir . '/objects/login.php';

// Get email and password from GET or POST
$email = '';
if (isset($_GET['email'])) {
    $email = $_GET['email'];
} elseif (isset($_POST['email'])) {
    $email = $_POST['email'];
}

$password = '';
if (isset($_GET['password'])) {
    $password = $_GET['password'];
} elseif (isset($_POST['password'])) {
    $password = $_POST['password'];
}

$app_version = null;
if (isset($_GET['app_version'])) {
    $app_version = $_GET['app_version'];
} elseif (isset($_POST['app_version'])) {
    $app_version = $_POST['app_version'];
}

// Support both old format (with action) and new format (without action)
$action = 'login_user';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
}

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
