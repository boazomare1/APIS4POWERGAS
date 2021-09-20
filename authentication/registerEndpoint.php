<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/register.php';

// instantiate database and product users
$database = new Database();

$conn = $database->getConnection();

$register = new Register($conn);
  
$username = isset($_GET['username']) ? $_GET['username'] : die();
$email = isset($_GET['email']) ? $_GET['email'] : die();
$password = isset($_GET['password']) ? $_GET['password'] : die();
$phone = isset($_GET['phone']) ? $_GET['phone'] : die();
$action = isset($_GET['action']) ? $_GET['action'] : die();
$first_name = isset($_GET['first_name']) ? $_GET['first_name'] : die();
$last_name = isset($_GET['last_name']) ? $_GET['last_name'] : die();

if($action == "register_user"){

    $response = $register->registerUser($username, $email, $password, $phone, $first_name, $last_name);
    echo json_encode($response);
}


?>
