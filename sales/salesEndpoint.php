<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require '../config/database.php';
require '../objects/sales.php';


$database = new Database();
global $conn;
$conn = $database->getConnection();

 

$action = isset($_GET['action']) ? $_GET['action'] : die();

$salesman_id = isset($_GET['salesman_id']) ? $_GET['salesman_id']  : fieldRequired('salesman_id');
$vehicle_id = isset($_GET['vehicle_id']) ? $_GET['vehicle_id']  : fieldRequired('vehicle_id');

if($action == "fetch_sales"){
    $response= fetchSales($salesman_id, $vehicle_id);

    echo json_encode($response);
    
}

if($action == "fetch_all_sales"){
    $response= fetchAllSales("954");

    echo json_encode($response);
    
}

if($action == "fetch_discount"){
    $response= fetchDiscount($conn, $salesman_id, $vehicle_id);

    echo json_encode($response);
    
}
if ($action == "fetch_sales_total") {
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    // Perform validation on $start_date and $end_date if necessary

    $response = fetchSalesWithTotal( $salesman_id, $vehicle_id, $start_date, $end_date);

    echo json_encode($response);
}

if($action == "fetch_cheque"){
    $response= fetchCheque($conn, $salesman_id, $vehicle_id);

    echo json_encode($response);
    
}

if($action == "fetch_invoice"){
    $response= fetchInvoice($conn, $salesman_id, $vehicle_id);

    echo json_encode($response);
    
}

function fieldRequired($fieldName){
    return $fieldName."Is required";
}