<?php
include("../common/common.php");
    function fetchProducts($vehicle_id, $day, $distributor_id){
        global $conn;
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        try {
            $query = "SELECT sma_products.id as product_id, sma_products.name as product_name, sma_products.code as product_code, sma_products.price, sma_products.isKitchen,
            sma_products.portion1, sma_products.portion1qty, sma_products.portion2, sma_products.portion2qty, sma_products.portion3, sma_products.portion3qty, sma_products.portion4, sma_products.portion4qty, sma_products.portion5, sma_products.portion5qty,
            sma_product_vehicle_quantities.quantity, sma_vehicles.plate_no, sma_vehicles.discount_enabled, sma_salesman_targets.target from sma_products 
LEFT JOIN sma_product_vehicle_quantities ON sma_products.id = sma_product_vehicle_quantities.product_id
LEFT JOIN sma_vehicle_route ON sma_product_vehicle_quantities.vehicle_id = sma_vehicle_route.vehicle_id
LEFT JOIN sma_routes ON sma_vehicle_route.route_id = sma_routes.id
LEFT JOIN sma_vehicles ON sma_vehicle_route.vehicle_id = sma_vehicles.id
LEFT JOIN sma_companies ON sma_companies.vehicle_id = sma_vehicles.id
LEFT JOIN sma_salesman_targets ON sma_salesman_targets.salesman_id = sma_companies.id
WHERE sma_vehicle_route.vehicle_id = ? and sma_vehicle_route.day = ? and sma_vehicle_route.distributor_id = ? or sma_products.iskitchen = 1";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $vehicle_id);
            $stmt->bindParam(2, $day);
            $stmt->bindParam(3, $distributor_id);
            $stmt->execute();
            

            $response = array();
            if($stmt->rowCount()>0){
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                    $response[] = array(
                        "product_id"=>$row['product_id'],
                        "product_code"=>$row['product_code'],
                        "product_name"=>$row['product_name'],
                        "price"=>intval($row['price']),
                        "quantity"=>intval($row['quantity']),
                        "plate_no"=>$row['plate_no'],
                        "discount_enabled"=>$row['discount_enabled'],
                        "target"=>$row['target'],
                        "isKitchen"=>$row['isKitchen'],
                        "portion1"=>intval($row['portion1']),
                        "portion1qty"=>intval($row['portion1qty']),
                        "portion2"=>intval($row['portion2']),
                        "portion2qty"=>intval($row['portion2qty']),
                        "portion3"=>intval($row['portion3']),
                        "portion3qty"=>intval($row['portion3qty']),
                        "portion4"=>intval($row['portion4']),
                        "portion4qty"=>intval($row['portion4qty']),
                        "portion5"=>intval($row['portion5']),
                        "portion5qty"=>intval($row['portion5qty'])
                    
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
    
    
    
    
    function fetchProductQuantity($vehicle_id, $distributor_id){
        global $conn;
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        try {
            $query = "SELECT * FROM sma_product_vehicle_quantities WHERE vehicle_id =? AND distributor_id=?";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $vehicle_id);
            $stmt->bindParam(2, $distributor_id);
            $stmt->execute();
            

            $response = array();
            if($stmt->rowCount()>0){
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                    $response[] = array(
                        "product_id"=>$row['product_id'],
                        "quantity"=>$row['quantity']
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
?>