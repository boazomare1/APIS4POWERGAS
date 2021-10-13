<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require '../config/database.php';
require '../objects/customers.php';


$database = new Database();
global $conn;
$conn = $database->getConnection();
  

$action = isset($_GET['action']) ? $_GET['action'] : die();
$vehicle_id = isset($_GET['vehicle_id']) ? $_GET['vehicle_id']  : fieldRequired('vehicle_id');
$day = isset($_GET['day']) ? $_GET['day']  : fieldRequired('day');
$salesman_id = isset($_GET['salesman_id']) ? $_GET['salesman_id']  : fieldRequired('salesman_id');

if($action == "fetch_distributor"){

    $response = fetchDistributor();
    echo json_encode($response);
}

if($action == "fetch_customers"){
    $response= fetchCustomers($vehicle_id, $day,$salesman_id);

    echo json_encode($response);
    
}

if($action == "fetch_towns"){
    $response= fetchTowns();

    echo json_encode($response);
    
}

if($action == "customer_groups"){
    $response= fetchGroups();

    echo json_encode($response);
    
}

if($action == "register_customers"){
    die(print_r("reached"));
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    die(print_r($json));
    foreach($data as $customerObj){
        $response = registerCustomer(
            $customerObj->group_id,
            $customerObj->group_name,
            $customerObj->name,
            $customerObj->country,
            $customerObj->email,
            $customerObj->phone,
            $customerObj->logo,
            $customerObj->lat,
            $customerObj->lng,
            $customerObj->town_id,
            $customerObj->shop_name,
            $customerObj->route_id,
            $customerObj->distributor_id,
            $customerObj->salesman_id,
            $customerObj->phone_2);
    } 
    echo json_encode($response);
    
}


if($action == "add_shop"){
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    foreach($data as $shopObj){
        $response = addShop($shopObj->customer_id, $shopObj->route_id, $shopObj->shop_name, $shopObj->lat, $shopObj->lng, $shopObj->image, $shopObj->distributor_id, $shopObj->salesman_id);
    } 
    echo json_encode($response);
    
}



function fieldRequired($fieldName){
    return $fieldName."Is required";
}