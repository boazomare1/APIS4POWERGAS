<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once '../config/database.php';
include_once '../objects/expense.php';
  
// instantiate database and product users
$database = new Database();

$db = $database->getConnection();
  
// initialize object
$expenses = new Expenses($db);

$action = isset($_GET['action']) ? $_GET['action'] : die();
$salesman_id = isset($_GET['salesman_id']) ? $_GET['salesman_id']  : fieldRequired('salesman_id');

if($action == "fetch_expenses"){
    $response= $expenses->fetchExpenses($salesman_id);

    echo json_encode($response);
    
}

if($action == "create_expense"){
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    foreach($data as $expenseObj){
        $response = $expenses->createExpense($expenseObj->company_id,$expenseObj->vehicle_id,$expenseObj->distributor_id,$expenseObj->salesman_id, $expenseObj->reference, $expenseObj->amount, $expenseObj->note, $expenseObj->created_by, $expenseObj->image);
    } 
    echo json_encode($response);
    
}

function fieldRequired($fieldName){
    return $fieldName."Is required";
}