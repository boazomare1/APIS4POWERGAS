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
    $salesman_id = isset($_GET['salesman_id']) ? intval($_GET['salesman_id']) : 0;
    $vehicle_id = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : 0;
    $day = isset($_GET['day']) ? intval($_GET['day']) : 0;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10; // Default to 10 if not provided

    $response = fetchCustomers($vehicle_id, $day, $salesman_id);
    echo json_encode($response);
    
}
if ($action == "check_sales_status") {
    $salesmanId = isset($_GET['salesman_id']) ? intval($_GET['salesman_id']) : null;
    $vehicleId = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : null;
    $day = isset($_GET['day']) ? $_GET['day'] : null;

    // Validate the input parameters
    if ($salesmanId === null || $vehicleId === null || $day === null) {
        echo json_encode(array(
            "success" => "0", 
            "message" => "Missing required parameters", 
            "total_customers" => 0, 
            "served_count" => 0, 
            "ticket_sales_count" => 0, 
            "unserved_count" => 0
        ));
        exit;
    }

    // Call the function to check sales status
    $response = checkSalesStatus($salesmanId, $vehicleId, $day);
    echo json_encode($response);
}



if ($action == "raise_ticket") {
    $customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
    $salesman_id = isset($_GET['salesman_id']) ? intval($_GET['salesman_id']) : 0;
    $reason = isset($_GET['reason']) ? $_GET['reason'] : '';
    $shop_id = isset($_GET['shop_id']) ? intval($_GET['shop_id']) : 0;
    $distributor_id = isset($_GET['distributor_id']) ? intval($_GET['distributor_id']) : 0;
    $vehicle_id = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : 0;

    $response = raiseTicket($customer_id, $salesman_id, $reason, $shop_id, $distributor_id, $vehicle_id);
    echo json_encode($response);
}

if ($action == "reset_ticket") {
    $customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
    $salesman_id = isset($_GET['salesman_id']) ? intval($_GET['salesman_id']) : 0;
    $reason = isset($_GET['reason']) ? $_GET['reason'] : '';
    $shop_id = isset($_GET['shop_id']) ? intval($_GET['shop_id']) : 0;
    $distributor_id = isset($_GET['distributor_id']) ? intval($_GET['distributor_id']) : 0;
    $vehicle_id = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : 0;

    $response = resetTicket($customer_id, $salesman_id, $reason, $shop_id, $distributor_id, $vehicle_id);
    echo json_encode($response);
}

if ($action == 'make_sale') {
    $discount = isset($_POST['discount']) ? $_POST['discount'] : 0;
    $invoice = isset($_POST['invoice']) ? $_POST['invoice'] : 0;
    $cheque = isset($_POST['cheque']) ? $_POST['cheque'] : 0;
    $image = isset($_POST['image']) ? $_POST['image'] : 'null';
    $invoice_id = isset($_POST['invoice_id']) ? $_POST['invoice_id'] : '';
    $discount_id = isset($_POST['discount_id']) ? $_POST['discount_id'] : '';
    $cheque_id = isset($_POST['cheque_id']) ? $_POST['cheque_id'] : '';
    $json = isset($_POST['json']) ? $_POST['json'] : '';
    $customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
    $distributor_id = isset($_POST['distributor_id']) ? $_POST['distributor_id'] : '';
    $town_id = isset($_POST['town_id']) ? $_POST['town_id'] : '';
    $salesman_id = isset($_POST['salesman_id']) ? $_POST['salesman_id'] : '';
    $paid_by = isset($_POST['paid_by']) ? $_POST['paid_by'] : '';
    $vehicle_id = isset($_POST['vehicle_id']) ? $_POST['vehicle_id'] : '';
    $payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : 'unpaid';
    $shop_id = isset($_POST['shop_id']) ? $_POST['shop_id'] : '';
    $total = isset($_POST['total']) ? $_POST['total'] : '';
    $payments = isset($_POST['payments']) ? $_POST['payments'] : '';
    $signature = isset($_POST['signature']) ? $_POST['signature'] : '';

    $response = makeSale($discount, $invoice, $cheque, $image, $invoice_id, $discount_id, $cheque_id, $json, $customer_id, $distributor_id, $town_id, $salesman_id, $paid_by, $vehicle_id, $payment_status, $shop_id, $total, $payments, $signature);
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
    $json = file_get_contents('php://input');
    $data = json_decode($json);
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
