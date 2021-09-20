<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
require '../config/database.php';
require '../objects/products.php';


$database = new Database();
global $conn;
$conn = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : die();
$vehicle_id = isset($_GET['vehicle_id']) ? $_GET['vehicle_id']  : fieldRequired('vehicle_id');
$day = isset($_GET['day']) ? $_GET['day']  : fieldRequired('day');
$distributor_id = isset($_GET['distributor_id']) ? $_GET['distributor_id']  : fieldRequired('distributor_id');

if($action == "fetch_products"){
    $response= fetchProducts($vehicle_id, $day, $distributor_id);
    echo json_encode($response);
    
}

if($action == "fetch_product_quantity"){
    $response= fetchProductQuantity($vehicle_id, $distributor_id);
    echo json_encode($response);
    
}


function fieldRequired($fieldName){
    return $fieldName."Is required";
}