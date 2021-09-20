<?php

    $servername = "23.236.60.20";
    $username = "db-user";
    $password = "Trymenot#123$";
    $dbname = "techsava_powergas_mpesa";
    
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    $callbackJSONData = file_get_contents('php://input');
    
        $callbackData = json_decode($callbackJSONData);
        
        $merchantRequestID = $callbackData->Body->stkCallback->MerchantRequestID;
        
        $checkoutRequestID = $callbackData->Body->stkCallback->CheckoutRequestID;
        
        $resultCode = $callbackData->Body->stkCallback->ResultCode;
        
        $resultDesc = $callbackData->Body->stkCallback->ResultDesc;
    
        $amount = $callbackData->Body->stkCallback->CallbackMetadata->Item[0]->Value;
        
        $mpesaReceiptNumber = $callbackData->Body->stkCallback->CallbackMetadata->Item[1]->Value;
        
        $transactionDate = $callbackData->Body->stkCallback->CallbackMetadata->Item[3]->Value;
    
        $phoneNumber = $callbackData->Body->stkCallback->CallbackMetadata->Item[4]->Value;
        
     $sql = "INSERT INTO mpesa_transactions (merchantRequestID, checkoutRequestID, resultCode, resultDesc, amount, mpesaReceiptNumber, transactionDate, phoneNumber)
            VALUES ('".$merchantRequestID."', '".$checkoutRequestID."', '".$resultCode."', '".$resultDesc."', '".$amount."', '".$mpesaReceiptNumber."', '".$transactionDate."', '".$phoneNumber."')";
            
            mysqli_query($conn, $sql);
            
            mysqli_close($conn);
           
    
    
?>
