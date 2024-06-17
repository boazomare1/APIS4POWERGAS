<?php
include ("../common/common.php");


function fetchGroups()
{
    global $conn;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    try {
        $query = "SELECT id, name FROM sma_customer_groups";

        $stmt = $conn->prepare($query);
        $stmt->execute();


        $response = array();
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $response[] = array(
                    "id" => $row['id'],
                    "name" => $row['name']
                );
            }

            return $response;
        } else {
            return array();
        }

    } catch (Exception $e) {
        $response = array("success" => "0", "message" => "Failed");
        print_r($e);
    }
    return $response;
}

function fetchDistributor()
{
    global $conn;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    try {
        $query = "SELECT name, id FROM sma_companies WHERE group_id = 12";

        // prepare query statement
        $stmt = $conn->prepare($query);
        // execute query
        $response = array();
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $response[] = array(
                    "id" => $row['id'],
                    "name" => $row['name']
                );
            }

        } else {
            $response = array();
        }

    } catch (Exception $e) {
        print_r($e);
        http_response_code(404);
        $response = array("success" => "2", "message" => "error occured");
    }


    return $response;
}

// function fetchCustomers($vehicle_id, $day, $salesman_id, $page = 1, $limit = 150)
// {
//     global $conn;

//     // Set PDO attributes
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

//     try {
//         // Initialize variables
//         $current_date = date("Y-m-d") . ' 23:59:00';
//         $today = date("Y-m-d");
//         $offset = ($page - 1) * $limit; // Calculate offset for pagination

//         // Fetch status of the salesman
//         $statusQuery = "SELECT status FROM sma_companies WHERE id = :salesman_id";
//         $stmtStatus = $conn->prepare($statusQuery);
//         $stmtStatus->bindParam(':salesman_id', $salesman_id, PDO::PARAM_INT);
//         $stmtStatus->execute();

//         $status = null; // Initialize status variable
//         if ($stmtStatus->rowCount() > 0) {
//             $rowStatus = $stmtStatus->fetch(PDO::FETCH_ASSOC);
//             $status = $rowStatus['status'];
//         }

//         // Check if status is fetched and continue only if status is found
//         if ($status === null) {
//             return array("success" => "0", "message" => "Failed to fetch status");
//         }

//         // Prepare the SQL query for counting total records
//         $countQuery = "SELECT COUNT(DISTINCT sma_shops.id) as total
//             FROM sma_shops
//             LEFT JOIN sma_customers ON sma_customers.id = sma_shops.customer_id
//             LEFT JOIN sma_cities ON sma_cities.id = sma_customers.city
//             LEFT JOIN sma_currencies ON sma_currencies.id = sma_cities.county_id
//             LEFT JOIN sma_shop_allocations ON sma_shop_allocations.shop_id = sma_shops.id 
//             LEFT JOIN sma_vehicle_route ON sma_shop_allocations.route_id = sma_vehicle_route.route_id
//             LEFT JOIN sma_vehicles ON sma_vehicle_route.vehicle_id = sma_vehicles.id
//             LEFT JOIN sma_routes ON sma_vehicle_route.route_id = sma_routes.id 
//             LEFT JOIN sma_allocation_days ON sma_allocation_days.allocation_id = sma_shop_allocations.id 
//             WHERE NOT EXISTS (
//                 SELECT *
//                 FROM sma_sales
//                 WHERE sma_shops.id = sma_sales.shop_id AND sma_sales.date = CURRENT_DATE AND sma_sales.created < :current_date
//             ) 
//             AND NOT EXISTS (
//                 SELECT *
//                 FROM sma_tickets
//                 WHERE sma_shops.id = sma_tickets.shop_id AND sma_tickets.date = CURRENT_DATE AND sma_tickets.created_at < :current_date2
//             ) 
//             AND sma_allocation_days.id NOT IN (
//                 SELECT allocation_id
//                 FROM sma_temporary_alloc_disable
//                 WHERE disabled_date = :today AND vehicle_id = :vehicle_id1
//             )
//             AND sma_vehicles.id = :vehicle_id2 AND sma_customers.active = 1 
//             AND ((sma_allocation_days.active = 0 AND sma_allocation_days.disabled_date != :today2) OR sma_allocation_days.active = 1) 
//             AND sma_allocation_days.day = :day1 AND sma_vehicle_route.day = :day2";

//         $stmtCount = $conn->prepare($countQuery);
//         $stmtCount->bindParam(':current_date', $current_date);
//         $stmtCount->bindParam(':current_date2', $current_date);
//         $stmtCount->bindParam(':today', $today);
//         $stmtCount->bindParam(':today2', $today);
//         $stmtCount->bindParam(':vehicle_id1', $vehicle_id, PDO::PARAM_INT);
//         $stmtCount->bindParam(':vehicle_id2', $vehicle_id, PDO::PARAM_INT);
//         $stmtCount->bindParam(':day1', $day, PDO::PARAM_STR);
//         $stmtCount->bindParam(':day2', $day, PDO::PARAM_STR);
//         $stmtCount->execute();
//         $totalRecords = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

//         if ($limit === null) {
//             $limit = $totalRecords;
//             $offset = 0; // Reset offset to fetch all records
//         }

//         // Prepare the SQL query for fetching limited records
//         // Prepare the SQL query for fetching limited records
// $query = "SELECT sma_customers.id as id, sma_customers.name, sma_customers.phone, sma_customers.active, sma_customers.email, sma_customers.customer_group_id, sma_customers.customer_group_name, sma_shops.image as logo, sma_shops.shop_name, sma_shops.id as shop_id, sma_shops.lat, sma_shops.lng, sma_currencies.french_name as county_name, sma_cities.city as town_name, sma_cities.id as town_id
// FROM sma_shops
// LEFT JOIN sma_customers ON sma_customers.id = sma_shops.customer_id
// LEFT JOIN sma_cities ON sma_cities.id = sma_customers.city
// LEFT JOIN sma_currencies ON sma_currencies.id = sma_cities.county_id
// LEFT JOIN sma_shop_allocations ON sma_shop_allocations.shop_id = sma_shops.id 
// LEFT JOIN sma_vehicle_route ON sma_shop_allocations.route_id = sma_vehicle_route.route_id
// LEFT JOIN sma_vehicles ON sma_vehicle_route.vehicle_id = sma_vehicles.id
// LEFT JOIN sma_routes ON sma_vehicle_route.route_id = sma_routes.id 
// LEFT JOIN sma_allocation_days ON sma_allocation_days.allocation_id = sma_shop_allocations.id 
// WHERE NOT EXISTS (
//     SELECT *
//     FROM sma_sales
//     WHERE sma_shops.id = sma_sales.shop_id AND sma_sales.date = CURRENT_DATE AND sma_sales.created < :current_date
// ) 
// AND NOT EXISTS (
//     SELECT *
//     FROM sma_tickets
//     WHERE sma_shops.id = sma_tickets.shop_id AND sma_tickets.date = CURRENT_DATE AND sma_tickets.created_at < :current_date2
// ) 
// AND sma_allocation_days.id NOT IN (
//     SELECT allocation_id
//     FROM sma_temporary_alloc_disable
//     WHERE disabled_date = :today AND vehicle_id = :vehicle_id1
// )
// AND sma_vehicles.id = :vehicle_id2 AND sma_customers.active = 1 
// AND ((sma_allocation_days.active = 0 AND sma_allocation_days.disabled_date != :today2) OR sma_allocation_days.active = 1) 
// AND sma_allocation_days.day = :day1 AND sma_vehicle_route.day = :day2 
// GROUP BY sma_shops.id 
// ORDER BY sma_allocation_days.position ASC";

// if ($limit !== null) {
// // Append LIMIT and OFFSET if $limit is not null
// $query .= " LIMIT :limit OFFSET :offset";
// }

// // Bind parameters and execute the query
// $stmt = $conn->prepare($query);
// $stmt->bindParam(':current_date', $current_date);
// $stmt->bindParam(':current_date2', $current_date);
// $stmt->bindParam(':today', $today);
// $stmt->bindParam(':today2', $today);
// $stmt->bindParam(':vehicle_id1', $vehicle_id, PDO::PARAM_INT);
// $stmt->bindParam(':vehicle_id2', $vehicle_id, PDO::PARAM_INT);
// $stmt->bindParam(':day1', $day, PDO::PARAM_STR);
// $stmt->bindParam(':day2', $day, PDO::PARAM_STR);
// if ($limit !== null) {
// $stmt->bindParam(':limit', $limit, PDO::PARAM_INT); // Bind the dynamic limit
// $stmt->bindParam(':offset', $offset, PDO::PARAM_INT); // Bind the dynamic offset
// }
// $stmt->execute();


//         // Fetch the results
//         $response = array();
//         if ($stmt->rowCount() > 0) {
//             if ($status == 1) {
//                 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                     $response[] = $row;
//                 }
//                 return array("success" => "1", "message" => "ok", "total" => $totalRecords, "data" => $response);
//             } else {
//                 return array("success" => "2", "message" => "Account Deactivated", "data" => array());
//             }
//         } else {
//             return array("success" => "1", "message" => "no data available", "data" => array());
//         }
//     } catch (Exception $e) {
//         return array("success" => "0", "message" => "Failed: " . $e->getMessage(), "data" => array());
//     }
// }

function fetchCustomers($vehicle_id, $day, $salesman_id)
{
    global $conn;

    // Set PDO attributes
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    try {
        // Initialize variables
        $current_date = date("Y-m-d") . ' 23:59:00';
        $today = date("Y-m-d");

        // Fetch status of the salesman
        $statusQuery = "SELECT status FROM sma_companies WHERE id = :salesman_id";
        $stmtStatus = $conn->prepare($statusQuery);
        $stmtStatus->bindParam(':salesman_id', $salesman_id, PDO::PARAM_INT);
        $stmtStatus->execute();

        $status = null; // Initialize status variable
        if ($stmtStatus->rowCount() > 0) {
            $rowStatus = $stmtStatus->fetch(PDO::FETCH_ASSOC);
            $status = $rowStatus['status'];
        }

        // Check if status is fetched and continue only if status is found
        if ($status === null) {
            return array("success" => "0", "message" => "Failed to fetch status");
        }

        // Prepare the SQL query for fetching records without pagination
        $query = "SELECT sma_customers.id as id, sma_customers.name, sma_customers.phone, sma_customers.active, sma_customers.email, sma_customers.customer_group_id, sma_customers.customer_group_name, sma_shops.image as logo, sma_shops.shop_name, sma_shops.id as shop_id, sma_shops.lat, sma_shops.lng, sma_currencies.french_name as county_name, sma_cities.city as town_name, sma_cities.id as town_id
            FROM sma_shops
            LEFT JOIN sma_customers ON sma_customers.id = sma_shops.customer_id
            LEFT JOIN sma_cities ON sma_cities.id = sma_customers.city
            LEFT JOIN sma_currencies ON sma_currencies.id = sma_cities.county_id
            LEFT JOIN sma_shop_allocations ON sma_shop_allocations.shop_id = sma_shops.id 
            LEFT JOIN sma_vehicle_route ON sma_shop_allocations.route_id = sma_vehicle_route.route_id
            LEFT JOIN sma_vehicles ON sma_vehicle_route.vehicle_id = sma_vehicles.id
            LEFT JOIN sma_routes ON sma_vehicle_route.route_id = sma_routes.id 
            LEFT JOIN sma_allocation_days ON sma_allocation_days.allocation_id = sma_shop_allocations.id 
            WHERE NOT EXISTS (
                SELECT *
                FROM sma_sales
                WHERE sma_shops.id = sma_sales.shop_id AND sma_sales.date = CURRENT_DATE AND sma_sales.created < :current_date
            ) 
            AND NOT EXISTS (
                SELECT *
                FROM sma_tickets
                WHERE sma_shops.id = sma_tickets.shop_id AND sma_tickets.date = CURRENT_DATE AND sma_tickets.created_at < :current_date2
            ) 
            AND sma_allocation_days.id NOT IN (
                SELECT allocation_id
                FROM sma_temporary_alloc_disable
                WHERE disabled_date = :today AND vehicle_id = :vehicle_id1
            )
            AND sma_vehicles.id = :vehicle_id2 AND sma_customers.active = 1 
            AND ((sma_allocation_days.active = 0 AND sma_allocation_days.disabled_date != :today2) OR sma_allocation_days.active = 1) 
            AND sma_allocation_days.day = :day1 AND sma_vehicle_route.day = :day2 
            GROUP BY sma_shops.id 
            ORDER BY sma_allocation_days.position ASC";

        // Bind parameters and execute the query
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':current_date', $current_date);
        $stmt->bindParam(':current_date2', $current_date);
        $stmt->bindParam(':today', $today);
        $stmt->bindParam(':today2', $today);
        $stmt->bindParam(':vehicle_id1', $vehicle_id, PDO::PARAM_INT);
        $stmt->bindParam(':vehicle_id2', $vehicle_id, PDO::PARAM_INT);
        $stmt->bindParam(':day1', $day, PDO::PARAM_STR);
        $stmt->bindParam(':day2', $day, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the results
        $response = array();
        if ($stmt->rowCount() > 0) {
            if ($status == 1) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $response[] = $row;
                }
                return array("success" => "1", "message" => "ok", "data" => $response);
            } else {
                return array("success" => "2", "message" => "Account Deactivated", "data" => array());
            }
        } else {
            return array("success" => "1", "message" => "no data available", "data" => array());
        }
    } catch (Exception $e) {
        return array("success" => "0", "message" => "Failed: " . $e->getMessage(), "data" => array());
    }
}

function registerCustomer($group_id, $group_name, $name, $country, $email, $phone, $logo, $lat, $lng, $town_id, $shop_name, $route_id, $distributor_id, $salesman_id, $phone_2)
{
    global $conn;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $response = "";
    try {

        $query9 = "SELECT * FROM sma_customers WHERE phone = ?";

        // prepare query statement
        $stmt9 = $conn->prepare($query9);
        $stmt9->bindParam(1, $phone);

        // execute query
        $stmt9->execute();
        if ($stmt9->rowCount() == 0) {
            $query = "INSERT INTO sma_customers(distributor_id, salesman_id, group_id, group_name, customer_group_id, customer_group_name, name, city, country, phone, phone2, email, is_subsidiary, customer_alignment) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $distributor_id);
            $stmt->bindValue(2, $salesman_id);
            $stmt->bindValue(3, 3);
            $stmt->bindValue(4, "customer");
            $stmt->bindValue(5, $group_id);
            $stmt->bindValue(6, $group_name);
            $stmt->bindParam(7, $name);
            $stmt->bindValue(8, $town_id);
            $stmt->bindValue(9, $country);
            $stmt->bindParam(10, $phone);
            $stmt->bindParam(11, $phone_2);
            $stmt->bindParam(12, $email);
            $stmt->bindValue(13, 0);
            $stmt->bindValue(14, 0);
            $stmt->execute();

            $customer_id = $conn->lastInsertId();

            addShop($customer_id, $route_id, $shop_name, $lat, $lng, $logo, $distributor_id, $salesman_id);

            addCustomerPaymentMethod($customer_id, 1);//default mpesa
            addCustomerPaymentMethod($customer_id, 2);//default cash

            addCustomerToAccount($name, $customer_id, $town_id);

            $response = array("success" => "1", "message" => "Customer added successfully. Please contact system admin for approval!");
        } else {
            $response = array("success" => "9", "message" => "The customer you entered already exist");
        }

    } catch (Exception $e) {
        print_r($e);
        http_response_code(404);
        $response = array("success" => "2", "message" => "error occured");
    }


    return $response;
}

function addShop($customer_id, $route_id, $shop_name, $lat, $lng, $logo, $distributor_id, $salesman_id)
{
    global $conn;
    $time = time();
    $path = "../uploads/$time.png";
    $final_path = "https://powergas-home.techsavanna.technology/powergas_app/uploads/" . $time . ".png";
    if (file_put_contents($path, base64_decode($logo))) {
        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO sma_shops (customer_id, route_id, shop_name, lat, lng, image) VALUES ('" . $customer_id . "', '" . $route_id . "', '" . $shop_name . "', '" . $lat . "', '" . $lng . "','" . $final_path . "')";
            $conn->exec($sql);

            $sql2 = "UPDATE sma_customers SET distributor_id=?, salesman_id =? WHERE id=?";

            $stmt = $conn->prepare($sql2);
            $stmt->bindParam(1, $distributor_id);
            $stmt->bindParam(2, $salesman_id);
            $stmt->bindParam(3, $customer_id);
            $stmt->execute();


            $response = array("success" => "1", "message" => "Shop Added successfully");
        } catch (PDOException $e) {
            print_r($e);
            $response = array("success" => "2", "message" => "Shop not added successfully");
        }
    } else {
        http_response_code(404);
        $response = array("success" => "3", "message" => "image could not be inserted");
    }
    return $response;
}

function addCustomerToAccount($name, $customer_id, $town_id)
{

    $address = getTownName($town_id);

    $actual_name = (string) $name;
    $customer_id = (string) $customer_id;
    $actual_address = (string) $address;

    $json = array();

    $data = array(
        'CustName' => $actual_name,
        'CustId' => $customer_id,
        'Address' => $actual_address,
        'TaxId' => '',
        'CurrencyCode' => 'KS',
        'SalesType' => '1',
        'CreditStatus' => '0',
        'PaymentTerms' => '7',
        'Discount' => '0',
        'paymentDiscount' => '0',
        'CreditLimit' => '0',
        'Notes' => ''
    );

    $json[] = $data;
    $json_data = json_encode($json);
    $username = "pos-api";
    $password = "admin";
    $headers = array(
        'Authorization: Basic ' . base64_encode($username . ':' . $password),
    );

    //Perform curl post request to add item to the accounts erp
    $curl = curl_init();

    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => "https://powergaserp.techsavanna.technology/api/endpoints/customers.php?action=add-customer&company-id=KAMP",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $json_data,
            CURLOPT_HTTPHEADER => $headers,
        )
    );

    $response = curl_exec($curl);

    curl_close($curl);

    $response_data = json_decode($response);
    // Further processing ...
    foreach ($response_data as $itemObj) {
        $status = $itemObj->Status;
    }


}

function getTownName($town_id)
{
    global $conn;
    $response = '';
    try {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = "SELECT city FROM sma_cities WHERE id=?";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $town_id);
        $stmt->execute();

        $response = "";
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() > 0) {
            $response = $row['city'];
        }

    } catch (Exception $e) {
        $response = array("success" => "0", "message" => "Failed");
        print_r($e);
    }
    return $response;
}

function addCustomerPaymentMethod($customer_id, $payment_method_id)
{
    global $conn;
    try {
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "INSERT INTO sma_customer_payment_methods (customer_id, payment_method_id) VALUES ('" . $customer_id . "', '" . $payment_method_id . "')";
        // use exec() because no results are returned
        $conn->exec($sql);
        $response = array("success" => "1", "message" => "Customer payment method inserted successfully");
    } catch (PDOException $e) {
        $response = array("success" => "2", "message" => $sql . "<br>" . $e->getMessage());
    }
    return $response;
}

function fetchTowns()
{
    global $conn;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    try {
        $query = "SELECT id, city, county_id FROM sma_cities";

        $stmt = $conn->prepare($query);
        $stmt->execute();


        $response = array();
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $response[] = array(
                    "id" => $row['id'],
                    "city" => $row['city'],
                    "county_id" => $row['county_id'],
                    "county_name" => fetchCounty($row['county_id'])
                );
            }

            return $response;
        } else {
            return array();
        }

    } catch (Exception $e) {
        $response = array("success" => "0", "message" => "Failed");
        print_r($e);
    }
    return $response;
}


function fetchCounty($county_id)
{
    global $conn;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    try {
        $query = "SELECT french_name FROM sma_currencies WHERE id=?";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $county_id);
        $stmt->execute();

        $response = "";
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() > 0) {
            $response = $row['french_name'];
        }

    } catch (Exception $e) {
        $response = array("success" => "0", "message" => "Failed");
        print_r($e);
    }
    return $response;
}

function raiseTicket($customer_id, $salesman_id, $reason, $shop_id, $distributor_id, $vehicle_id)
{
    global $conn; // Assume $conn is your database connection
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    try {
        // Begin a transaction
        $conn->beginTransaction();

        // Insert into sma_tickets
        $insertQuery = "INSERT INTO sma_tickets (distributor_id, salesman_id, customer_id, vehicle_id, shop_id, reason, date, created_at, status)
                        VALUES (:distributor_id, :salesman_id, :customer_id, :vehicle_id, :shop_id, :reason, :date, :created_at, 1)";
        $stmtInsert = $conn->prepare($insertQuery);
        
        $date = date("Y-m-d");
        $created_at = date("Y-m-d H:i:s");

        $stmtInsert->execute([
            'distributor_id' => $distributor_id,
            'salesman_id' => $salesman_id,
            'customer_id' => $customer_id,
            'vehicle_id' => $vehicle_id,
            'shop_id' => $shop_id,
            'reason' => $reason,
            'date' => $date,
            'created_at' => $created_at
        ]);

        // Commit the transaction
        $conn->commit();

        // Return a success response
        $response = array();
        $response['ticket_id'] = $conn->lastInsertId();
        $response['details'] = [
            "customer_id" => $customer_id,
            "salesman_id" => $salesman_id,
            "reason" => $reason,
            "shop_id" => $shop_id,
            "distributor_id" => $distributor_id,
            "vehicle_id" => $vehicle_id
        ];

        return array("success" => "1", "message" => "Ticket raised successfully", "data" => $response);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollBack();

        // Log the error (optional)
        error_log($e->getMessage());

        // Return an error response
        return array("success" => "0", "message" => "Failed to raise ticket", "error" => $e->getMessage());
    }
}






function resetTicket($customer_id, $salesman_id, $reason, $shop_id, $distributor_id, $vehicle_id)
{
    global $conn; // Assume $conn is your database connection
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    try {
        // Begin a transaction
        $conn->beginTransaction();

        // Delete ticket record from sma_tickets
        $deleteTicketQuery = "DELETE FROM sma_tickets WHERE customer_id = :customer_id AND distributor_id = :distributor_id AND salesman_id = :salesman_id AND vehicle_id = :vehicle_id AND shop_id = :shop_id AND reason = :reason";
        $stmtDeleteTicket = $conn->prepare($deleteTicketQuery);
        $stmtDeleteTicket->execute([
            'customer_id' => $customer_id,
            'distributor_id' => $distributor_id,
            'salesman_id' => $salesman_id,
            'vehicle_id' => $vehicle_id,
            'shop_id' => $shop_id,
            'reason' => $reason
        ]);

        // Update customer status to active in sma_customers
        $updateCustomerQuery = "UPDATE sma_customers SET active = 1 WHERE id = :customer_id";
        $stmtUpdateCustomer = $conn->prepare($updateCustomerQuery);
        $stmtUpdateCustomer->execute(['customer_id' => $customer_id]);

        // Commit the transaction
        $conn->commit();

        // Return a success response
        $response = [
            "success" => true,
            "message" => "Ticket status reset successfully",
            "data" => [
                "customer_id" => $customer_id,
                "salesman_id" => $salesman_id,
                "reason" => $reason,
                "shop_id" => $shop_id,
                "distributor_id" => $distributor_id,
                "vehicle_id" => $vehicle_id
            ]
        ];

        return $response;
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollBack();

        // Log the error (optional)
        error_log($e->getMessage());

        // Return an error response
        $errorResponse = [
            "success" => false,
            "message" => "Failed to reset ticket status",
            "error" => $e->getMessage()
        ];

        return $errorResponse;
    }
}




function makeSale($discount, $invoice, $cheque, $image, $invoice_id, $discount_id, $cheque_id, $json, $customer_id, $distributor_id, $town_id, $salesman_id, $paid_by, $vehicle_id, $payment_status, $shop_id, $total, $payments, $signature) {
    global $conn; // Assume $conn is your PDO database connection

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    try {
        $conn->beginTransaction();

        // Decode JSON arrays
        $items = json_decode($json, true);
        $paymentDetails = json_decode($payments, true);

        // Insert into sales table
        $query = "INSERT INTO sales (discount, invoice, cheque, image, invoice_id, discount_id, cheque_id, customer_id, distributor_id, town_id, salesman_id, paid_by, vehicle_id, payment_status, shop_id, total, signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$discount, $invoice, $cheque, $image, $invoice_id, $discount_id, $cheque_id, $customer_id, $distributor_id, $town_id, $salesman_id, $paid_by, $vehicle_id, $payment_status, $shop_id, $total, $signature]);

        // Get the last inserted ID
        $sale_id = $conn->lastInsertId();

        // Insert sale items
        foreach ($items as $item) {
            $query = "INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$sale_id, $item['product_id'], $item['quantity'], $item['price']]);
        }

        // Insert payment details
        foreach ($paymentDetails as $payment) {
            $query = "INSERT INTO sale_payments (sale_id, payment_method, amount) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$sale_id, $payment['method'], $payment['amount']]);
        }

        $conn->commit();

        return [
            "status" => "success",
            "message" => "Sale added successfully",
            "sale_id" => $sale_id
        ];
    } catch (Exception $e) {
        $conn->rollBack();

        // Log the error (optional)
        error_log($e->getMessage());

        return [
            "status" => "error",
            "message" => "Failed to add sale",
            "error" => $e->getMessage()
        ];
    }
}

?>