 <?php
include("../common/common.php");
    
    
    function fetchGroups(){
        global $conn;
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        try {
            $query = "SELECT id, name FROM sma_customer_groups";

            $stmt = $conn->prepare($query);
            $stmt->execute();
            

            $response = array();
            if($stmt->rowCount()>0){
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                    $response[] = array(
                        "id"=>$row['id'],
                        "name"=>$row['name']);
                }

                return $response;
            }else{
                return array();
            }

        } catch (Exception $e) {
            $response= array("success"=>"0", "message"=>"Failed");
            print_r($e);
        }
        return $response;
    } 
    
    function fetchDistributor(){
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
            if($stmt->rowCount() > 0){
             while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                    $response[] = array(
                        "id"=>$row['id'],
                        "name"=>$row['name']);
                }
                
            }else{
                $response= array();
            }
            
            } catch (Exception $e) {
            print_r($e);
                http_response_code(404);
                $response= array("success" => "2", "message" => "error occured");
            }
        

        return $response;
    }
    
    function fetchCustomers($vehicle_id, $day,$salesman_id){
        global $conn;
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        try {
            $query = "SELECT sma_customers.id as id, sma_customers.name, sma_customers.phone, sma_customers.active, sma_customers.email, sma_customers.customer_group_id, sma_customers.customer_group_name, sma_shops.image as logo, sma_shops.shop_name, sma_shops.id as shop_id, sma_shops.lat, sma_shops.lng, sma_currencies.french_name as county_name, sma_cities.city as town_name,sma_cities.id as town_id
FROM   sma_shops
				left join sma_customers on sma_customers.id = sma_shops.customer_id
                left join sma_cities on sma_cities.id = sma_customers.city
                left join sma_currencies on sma_currencies.id = sma_cities.county_id
                left join sma_shop_allocations on sma_shop_allocations.shop_id = sma_shops.id 
                left join sma_vehicle_route on sma_shop_allocations.route_id=sma_vehicle_route.route_id
                left join sma_vehicles on sma_vehicle_route.vehicle_id = sma_vehicles.id
                left join sma_routes on sma_vehicle_route.route_id = sma_routes.id 
                left join sma_allocation_days on sma_allocation_days.allocation_id = sma_shop_allocations.id 
WHERE NOT EXISTS
  (SELECT *
   FROM   sma_sales
   WHERE  sma_shops.id = sma_sales.shop_id and sma_sales.date = CURRENT_DATE and sma_sales.created < ?) 
   
AND NOT EXISTS
  (SELECT *
   FROM   sma_tickets
   WHERE  sma_shops.id = sma_tickets.shop_id and sma_tickets.date = CURRENT_DATE and sma_tickets.created_at < ?) and 
   sma_vehicles.id = ? and sma_customers.active = 1 and sma_allocation_days.day = ? and sma_vehicle_route.day = ? and 
   sma_allocation_days.expiry IS NULL or sma_allocation_days.expiry <= CURRENT_TIMESTAMP GROUP BY sma_shops.id ORDER BY sma_shop_allocations.id";

            $current_date = date("Y-m-d").' '.'23:59:00';
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $current_date);
            $stmt->bindParam(2, $current_date);
            $stmt->bindParam(3, $vehicle_id);
            $stmt->bindParam(4, $day);
            $stmt->bindParam(5, $day);
            $stmt->execute();
            
        $status= "SELECT status FROM sma_companies WHERE sma_companies.id=?";
            $stmtst = $conn->prepare($status);
            $stmtst->bindParam(1, $salesman_id);
            $stmtst->execute();
            if($stmtst->rowCount()>0){
                $rowst = $stmtst->fetch(PDO::FETCH_ASSOC);
                $status=$rowst['status'];
            }
            $response = array();
            if($stmt->rowCount()>0){
                if($status==1)
                {
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                    $responseraw[] = $row;
                }
                return $response= array("success"=>"1", "message"=>"ok","data"=>$responseraw);
                }
                else{
                return $response= array("success"=>"2", "message"=>"Account Deactivated","data"=>array());
            }
             
            }else{
                return $response= array("success"=>"1", "message"=>"no data available","data"=>array());
            }

        } catch (Exception $e) {
            $response= array("success"=>"0", "message"=>"Failed");
            print_r($e);
        }
        return $response;
    } 


 
    function registerCustomer($group_id, $group_name, $name, $country, $email, $phone, $logo, $lat, $lng, $town_id, $shop_name, $route_id, $distributor_id,$salesman_id, $phone_2){
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
            if($stmt9->rowCount() == 0){
            $query = "INSERT INTO sma_customers(distributor_id, salesman_id, group_id, group_name, customer_group_id, customer_group_name, name, city, country, phone, phone2, email, is_subsidiary) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
 
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
                $stmt->execute();

                $customer_id = $conn->lastInsertId();
                die(print_r($customer_id));
                addShop($customer_id,$route_id,$shop_name,$lat,$lng, $logo, $distributor_id, $salesman_id);
               
                addCustomerPaymentMethod($customer_id,1);//default mpesa
                addCustomerPaymentMethod($customer_id,2);//default cash
                
                addCustomerToAccount($name, $customer_id, $town_id);
                
                $response= array("success" => "1", "message" => "Customer added successfully. Please contact system admin for approval!");
            }else{
                $response= array("success" => "9", "message" => "The customer you entered already exist");
            }
            
            } catch (Exception $e) {
                print_r($e);
                http_response_code(404);
                $response= array("success" => "2", "message" => "error occured");
            }
        

        return $response;
    }
    
    function addShop($customer_id, $route_id, $shop_name, $lat, $lng, $logo, $distributor_id, $salesman_id){
        global $conn;
        $time = time();
        $path = "../uploads/$time.png";
        $final_path = "https://powergas-home.techsavanna.technology/powergas_app/uploads/".$time.".png";
        if(file_put_contents($path,base64_decode($logo))){
        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO sma_shops (customer_id, route_id, shop_name, lat, lng, image) VALUES ('".$customer_id."', '".$route_id."', '".$shop_name."', '".$lat."', '".$lng."','".$final_path."')";
            $conn->exec($sql);
            
            $sql2 = "UPDATE sma_customers SET distributor_id=?, salesman_id =? WHERE id=?";
            
            $stmt = $conn->prepare($sql2);
            $stmt->bindParam(1, $distributor_id);
			$stmt->bindParam(2, $salesman_id);
			$stmt->bindParam(3, $customer_id);
     		$stmt->execute();

     		 
            $response= array("success" => "1", "message" => "Shop Added successfully");
        } catch(PDOException $e) {
            print_r($e);
            $response= array("success" => "2", "message" => "Shop not added successfully");
        }
        }else{
            http_response_code(404);
            $response= array("success" => "3", "message" => "image could not be inserted");
        }
        return $response;
    }
    
    function addCustomerToAccount($name, $customer_id, $town_id){
        
                $address = getTownName($town_id);
                
                $actual_name = (string) $name;
                $customer_id = (string) $customer_id;
                $actual_address = (string) $address;
                
    			$json = array();
    			
    			$data = array('CustName' => $actual_name,
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
    						'Notes' => '');
    			
    			$json[] = $data;
                $json_data = json_encode($json);
                $username = "admin";
                $password = "admin";
                $headers = array(
                    'Authorization: Basic '. base64_encode($username.':'.$password),
                );
    
                //Perform curl post request to add item to the accounts erp
                $curl = curl_init();
    
                curl_setopt_array($curl, array(
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
    		    ));
    
    		    $response = curl_exec($curl);
    	
    		    curl_close($curl);
                
                $response_data = json_decode($response);
                // Further processing ...
                foreach($response_data as $itemObj){
                	$status = $itemObj->Status;
                }
    
            
    }
    
    function getTownName($town_id){
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
            if($stmt->rowCount()>0){
                $response = $row['city'];
            }

        } catch (Exception $e) {
            $response= array("success"=>"0", "message"=>"Failed");
            print_r($e);
        }
        return $response;
    }

    function addCustomerPaymentMethod($customer_id, $payment_method_id){
        global $conn;
        try {
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO sma_customer_payment_methods (customer_id, payment_method_id) VALUES ('".$customer_id."', '".$payment_method_id."')";
            // use exec() because no results are returned
            $conn->exec($sql);
            $response= array("success" => "1", "message" => "Customer payment method inserted successfully");
        } catch(PDOException $e) {
            $response= array("success" => "2", "message" => $sql . "<br>" . $e->getMessage());
        }
        return $response;
    }

    function fetchTowns(){
        global $conn;
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        try {
            $query = "SELECT id, city, county_id FROM sma_cities";

            $stmt = $conn->prepare($query);
            $stmt->execute();
            

            $response = array();
            if($stmt->rowCount()>0){
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                    $response[] = array(
                        "id"=>$row['id'],
                        "city"=>$row['city'],
                        "county_id"=>$row['county_id'],
                        "county_name"=>fetchCounty($row['county_id']));
                }

                return $response;
            }else{
                return array();
            }

        } catch (Exception $e) {
            $response= array("success"=>"0", "message"=>"Failed");
            print_r($e);
        }
        return $response;
    } 
    
    
    function fetchCounty($county_id){
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
            if($stmt->rowCount()>0){
                $response = $row['french_name'];
            }

        } catch (Exception $e) {
            $response= array("success"=>"0", "message"=>"Failed");
            print_r($e);
        }
        return $response;
    } 
?>