<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/login.php';

// instantiate database and product users
$database = new Database();

$conn = $database->getConnection();

$login = new Login($conn);
  
$email = isset($_GET['email']) ? $_GET['email'] : die();
$password = isset($_GET['password']) ? $_GET['password'] : die();
$action = isset($_GET['action']) ? $_GET['action'] : die();

if($action == "login_user"){
    // echo password_hash($password, PASSWORD_BCRYPT);
    $response = $login->loginUser($email, $password);
    echo json_encode($response);
}

?>
