<?php
require '../common/common.php';
  
    function fetchSales($salesman_id, $vehicle_id){
        global $conn;
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        try {
            $query = "SELECT sma_sales.id as sale_id, sma_sales.updated_at as updated_at, sma_sales.date, sma_sales.customer_id, sma_sales.customer, sma_sales.grand_total, sma_sales.payment_status,sma_sales.shop_id, sma_shops.shop_name, sma_shops.lat, sma_shops.lng, sma_shops.image, sma_customers.phone, sma_customers.city, sma_customers.customer_group_name FROM sma_sales LEFT JOIN sma_shops ON sma_sales.shop_id = sma_shops.id LEFT JOIN sma_customers ON sma_shops.customer_id = sma_customers.id WHERE sma_sales.salesman_id=? AND sma_sales.vehicle_id=? AND sma_sales.sales_type='SSO'  AND sma_sales.date >= DATE(NOW()) - INTERVAL 7 DAY ORDER BY sma_sales.updated_at DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $salesman_id);
            $stmt->bindParam(2, $vehicle_id);
            $stmt->execute();
           
            $response = array();
            if($stmt->rowCount()>0){
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array(
                        "id"=>$row['sale_id'],
                        "date"=>$row['date'],
                        "shop_name"=>$row['shop_name'],
                        "lat"=>$row['lat'],
                        "lng"=>$row['lng'],
                        "image"=>$row['image'],
                        "phone"=>$row['phone'],
                        "customer_group_name"=>$row['customer_group_name'],
                        "customer"=>$row['customer'],
                        "customer_id"=>$row['customer_id'],
                        "payment_status"=>$row['payment_status'],
                        "grand_total"=>intval($row['grand_total']),
                        "city"=>$row['city'],
                        "updated_at"=>$row['updated_at'],
                        "shop_id"=>$row['shop_id'],
                        "payments"=>get_sale_payments($conn, $row['sale_id']),
                        "products"=>get_sma_sales($conn, $row['sale_id']),
                        
                    );
                }
                return $response;
            }else{
                return array();
            }

        } catch (Exception $e) {
            $response= array("success"=>"0", "message"=>"Order Failed");
            print_r($e);
        }
        return $response;
    } 
    
    
    function fetchDiscount($conn, $salesman_id, $vehicle_id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        try {
            $query = "SELECT sma_discounts.id as sale_id, sma_discounts.updated_at as updated_at, sma_discounts.date,sma_discounts.status,sma_discounts.sold, sma_discounts.customer_id, sma_discounts.customer, 
            sma_discounts.grand_total, sma_discounts.status as payment_status,sma_discounts.shop_id, sma_shops.shop_name, sma_shops.lat, sma_shops.lng, sma_shops.image, 
            sma_customers.phone, sma_customers.city, sma_customers.customer_group_name FROM sma_discounts 
            LEFT JOIN sma_shops ON sma_discounts.shop_id = sma_shops.id 
            LEFT JOIN sma_customers ON sma_shops.customer_id = sma_customers.id 
            WHERE sma_discounts.date = CURRENT_DATE AND sma_discounts.salesman_id=? AND 
            sma_discounts.vehicle_id=? AND 
            sma_discounts.sales_type='SSO' AND
            sma_discounts.sold = 0 AND (sma_discounts.status = 0 OR sma_discounts.status = 1)
            ORDER BY sma_discounts.updated_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $salesman_id);
            $stmt->bindParam(2, $vehicle_id);
            $stmt->execute();
         
            $response = array(); 
            if($stmt->rowCount()>0){
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array(
                        "id"=>$row['sale_id'],
                        "date"=>$row['date'],
                        "shop_name"=>$row['shop_name'],
                        "lat"=>$row['lat'],
                        "lng"=>$row['lng'],
                        "image"=>$row['image'],
                        "phone"=>$row['phone'],
                        "customer_group_name"=>$row['customer_group_name'],
                        "customer"=>$row['customer'],
                        "customer_id"=>$row['customer_id'],
                        "payment_status"=>$row['payment_status'],
                        "grand_total"=>intval($row['grand_total']),
                        "city"=>$row['city'],
                        "updated_at"=>$row['updated_at'],
                        "shop_id"=>$row['shop_id'],
                        "payments"=>"",
                        "products"=>get_sma_discounts($conn, $row['sale_id']),
                        
                    );
                     
                }
                return $response;
            }else{
                return array();
            }

        } catch (Exception $e) {
            $response= array("success"=>"0", "message"=>"Order Failed");
            print_r($e);
        }
        return $response;
    } 
    
    function fetchCheque($conn, $salesman_id, $vehicle_id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        try {
            $query = "SELECT sma_cheques.id as sale_id, sma_cheques.updated_at as updated_at, sma_cheques.date,sma_cheques.status,sma_cheques.sold, sma_cheques.customer_id, sma_cheques.customer, 
            sma_cheques.grand_total, sma_cheques.status as payment_status,sma_cheques.shop_id, sma_shops.shop_name, sma_shops.lat, sma_shops.lng, sma_shops.image, 
            sma_customers.phone, sma_customers.city, sma_customers.customer_group_name FROM sma_cheques 
            LEFT JOIN sma_shops ON sma_cheques.shop_id = sma_shops.id 
            LEFT JOIN sma_customers ON sma_shops.customer_id = sma_customers.id 
            WHERE sma_cheques.date = CURRENT_DATE AND sma_cheques.salesman_id=? AND 
            sma_cheques.vehicle_id=? AND 
            sma_cheques.sales_type='SSO' AND
            sma_cheques.sold = 0 AND (sma_cheques.status = 0 OR sma_cheques.status = 1)
            ORDER BY sma_cheques.updated_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $salesman_id);
            $stmt->bindParam(2, $vehicle_id);
            $stmt->execute();
         
            $response = array(); 
            if($stmt->rowCount()>0){
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array(
                        "id"=>$row['sale_id'],
                        "date"=>$row['date'],
                        "shop_name"=>$row['shop_name'],
                        "lat"=>$row['lat'],
                        "lng"=>$row['lng'],
                        "image"=>$row['image'],
                        "phone"=>$row['phone'],
                        "customer_group_name"=>$row['customer_group_name'],
                        "customer"=>$row['customer'],
                        "customer_id"=>$row['customer_id'],
                        "payment_status"=>$row['payment_status'],
                        "grand_total"=>intval($row['grand_total']),
                        "city"=>$row['city'],
                        "updated_at"=>$row['updated_at'],
                        "shop_id"=>$row['shop_id'],
                        "payments"=>get_cheque_payments($conn, $row['sale_id']),
                        "status"=>$row['status'],
                        "products"=>get_sma_cheques($conn, $row['sale_id']),
                        
                    );
                     
                }
                return $response;
            }else{
                return array();
            }

        } catch (Exception $e) {
            $response= array("success"=>"0", "message"=>"Order Failed");
            print_r($e);
        }
        return $response;
    } 
    
    
    function fetchInvoice($conn, $salesman_id, $vehicle_id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        try {
            $query = "SELECT sma_invoices.id as sale_id, sma_invoices.updated_at as updated_at, sma_invoices.date,sma_invoices.status,sma_invoices.sold, sma_invoices.customer_id, sma_invoices.customer, 
            sma_invoices.grand_total, sma_invoices.status as payment_status,sma_invoices.shop_id, sma_shops.shop_name, sma_shops.lat, sma_shops.lng, sma_shops.image, 
            sma_customers.phone, sma_customers.city, sma_customers.customer_group_name FROM sma_invoices 
            LEFT JOIN sma_shops ON sma_invoices.shop_id = sma_shops.id 
            LEFT JOIN sma_customers ON sma_shops.customer_id = sma_customers.id 
            WHERE sma_invoices.date = CURRENT_DATE AND sma_invoices.salesman_id=? AND 
            sma_invoices.vehicle_id=? AND 
            sma_invoices.sales_type='SSO' AND
            sma_invoices.sold = 0 AND (sma_invoices.status = 0 OR sma_invoices.status = 1)
            ORDER BY sma_invoices.updated_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $salesman_id);
            $stmt->bindParam(2, $vehicle_id);
            $stmt->execute();
         
            $response = array(); 
            if($stmt->rowCount()>0){
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array(
                        "id"=>$row['sale_id'],
                        "date"=>$row['date'],
                        "shop_name"=>$row['shop_name'],
                        "lat"=>$row['lat'],
                        "lng"=>$row['lng'],
                        "image"=>$row['image'],
                        "phone"=>$row['phone'],
                        "customer_group_name"=>$row['customer_group_name'],
                        "customer"=>$row['customer'],
                        "customer_id"=>$row['customer_id'],
                        "payment_status"=>$row['payment_status'],
                        "grand_total"=>intval($row['grand_total']),
                        "city"=>$row['city'],
                        "updated_at"=>$row['updated_at'],
                        "shop_id"=>$row['shop_id'],
                        "payments"=>get_invoice_payments($conn, $row['sale_id']),
                        "status"=>$row['status'],
                        "products"=>get_sma_invoices($conn, $row['sale_id']),
                        
                    );
                    
                     
                }
                return $response;
            }else{
                return array();
            }

        } catch (Exception $e) {
            $response= array("success"=>"0", "message"=>"Order Failed");
            print_r($e);
        }
        return $response;
    } 
    
    
    function fetchAllSales($distributor_id){
        global $conn;
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        try {
            $query = "SELECT id, date, distributor_id, customer_id, customer, total, delivery_status FROM sma_sales WHERE delivery_status = 'delivered' AND distributor_id = $distributor_id ORDER BY created DESC LIMIT 15";

            $stmt = $conn->prepare($query);
            $stmt->execute();
            

            $response = array();
            if($stmt->rowCount()>0){
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                    $response[] = array(
                        "id"=>$row['id'],
                        "date"=>$row['date'],
                        "agent"=>$row['customer'],
                        "agent_id"=>$row['customer_id'],
                        "total"=>$row['total'],
                        "products"=>$common->get_sma_sales($this->conn, $row['id'])
                    );
                }

                return $response;
            }else{
                return array();
            }

        } catch (Throwable $th) {
            $response= array("success"=>"0", "message"=>"Order Failed");
            print_r($th->getMessage());
        }
        return $response;
    } 
?>