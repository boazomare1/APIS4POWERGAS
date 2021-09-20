<?php

    function get_sma_sales($conn, $id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = array();
        $query = "SELECT product_id, product_code, unit_price, product_name, quantity, subtotal FROM sma_sale_items WHERE sale_id =?";
        
        try {
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $id);
            $stmt->execute();
        if($stmt->rowCount()>0){
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array("product_id"=>$row['product_id'],
                        "code"=>$row['product_code'], 
                        "name"=>$row['product_name'], 
                        "price"=>intval($row['unit_price']),
                        "quantity"=>intval($row['quantity']),
                        "total"=>intval($row['subtotal'])
                    );
                }
            
            }
        } catch (Exception $ex) {
            $response = array('resp_msg'=>'Operation Failed ', 'error'=>$ex);
        }
        return $response;
    } 
    
    function get_sma_discounts($conn, $id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = array();
        $query = "SELECT product_id, product_code, unit_price, product_name, quantity, subtotal FROM sma_discount_items WHERE sale_id =?";
        
        try {
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $id);
            $stmt->execute();
        if($stmt->rowCount()>0){
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array("product_id"=>$row['product_id'],
                        "code"=>$row['product_code'], 
                        "name"=>$row['product_name'], 
                        "price"=>intval($row['unit_price']),
                        "quantity"=>intval($row['quantity']),
                        "total"=>intval($row['subtotal'])
                    );
                }
            
            }
        } catch (Exception $ex) {
            $response = array('resp_msg'=>'Operation Failed ', 'error'=>$ex);
        }
        return $response;
    } 
    
   function get_sma_invoices($conn, $id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = array();
        $query = "SELECT product_id, product_code, unit_price, product_name, quantity, subtotal FROM sma_invoice_items WHERE sale_id =?";
        
        try {
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $id);
            $stmt->execute();
        if($stmt->rowCount()>0){
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array("product_id"=>$row['product_id'],
                        "code"=>$row['product_code'], 
                        "name"=>$row['product_name'], 
                        "price"=>intval($row['unit_price']),
                        "quantity"=>intval($row['quantity']),
                        "total"=>intval($row['subtotal'])
                    );
                }
            
            }
        } catch (Exception $ex) {
            $response = array('resp_msg'=>'Operation Failed ', 'error'=>$ex);
        }
        return $response;
    }  
    
    function get_sma_cheques($conn, $id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = array();
        $query = "SELECT product_id, product_code, unit_price, product_name, quantity, subtotal FROM sma_cheque_items WHERE sale_id =?";
        
        try {
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $id);
            $stmt->execute();
        if($stmt->rowCount()>0){
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array("product_id"=>$row['product_id'],
                        "code"=>$row['product_code'], 
                        "name"=>$row['product_name'], 
                        "price"=>intval($row['unit_price']),
                        "quantity"=>intval($row['quantity']),
                        "total"=>intval($row['subtotal'])
                    );
                }
            
            }
        } catch (Exception $ex) {
            $response = array('resp_msg'=>'Operation Failed ', 'error'=>$ex);
        }
        return $response;
    } 
    
    
    function get_cheque_payments($conn, $id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = array();
        $query = "SELECT paid_by, amount, type FROM sma_cheque_payments WHERE sale_id =? GROUP BY paid_by";
        
        try {
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $id);
            $stmt->execute();
        if($stmt->rowCount()>0){
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array(
                        "paid_by"=>$row['paid_by'],
                        "amount"=>intval($row['amount']), 
                        "cheque_no"=>"NULL",
                        "type"=>$row['type']
                    );
                }
            
            }
        } catch (Exception $ex) {
            $response = array('resp_msg'=>'Operation Failed ', 'error'=>$ex);
        }
        return $response;
    } 
    
    
    function get_sale_payments($conn, $id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = array();
        $query = "SELECT paid_by, amount, type FROM sma_payments WHERE sale_id =? GROUP BY paid_by";
        
        try {
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $id);
            $stmt->execute();
        if($stmt->rowCount()>0){
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array(
                        "paid_by"=>$row['paid_by'],
                        "amount"=>intval($row['amount']), 
                        "type"=>$row['type']
                    );
                }
            
            }
        } catch (Exception $ex) {
            $response = array('resp_msg'=>'Operation Failed ', 'error'=>$ex);
        }
        return $response;
    } 
    
    function get_invoice_payments($conn, $id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = array();
        $query = "SELECT paid_by, amount, type FROM sma_invoice_payments WHERE sale_id =? GROUP BY paid_by";
        
        try {
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $id);
            $stmt->execute();
        if($stmt->rowCount()>0){
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $response[] = array(
                        "paid_by"=>$row['paid_by'],
                        "amount"=>intval($row['amount']), 
                        "cheque_no"=>"NULL",
                        "type"=>$row['type']
                    );
                }
            
            }
        } catch (Exception $ex) {
            $response = array('resp_msg'=>'Operation Failed ', 'error'=>$ex);
        }
        return $response;
    } 
    
    
    function fetchPayments($conn, $payment_id){
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = '';
        $query = "SELECT country FROM 0_payment_terms WHERE id =?";
        
        try {
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $country_id);
            $stmt->execute();
        if($stmt->rowCount()>0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $response = $row['country'];
            }
        } catch (Exception $ex) {
            $response = array('resp_msg'=>'Operation Failed ', 'error'=>$ex);
        }
        return $response;
    }  
?>