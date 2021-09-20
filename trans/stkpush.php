<?php
require 'vendor/autoload.php';

use Safaricom\Mpesa\Mpesa;

 $PhoneNumber = isset($_GET['PhoneNumber']) ? $_GET['PhoneNumber'] : die();
 $Amount = isset($_GET['Amount']) ? $_GET['Amount'] : die();

$mpesa= new Mpesa();
$BusinessShortCode="4069251";
$LipaNaMpesaPasskey="c20ada24d50eb05f6ec00481c0ceec5caa1599664429276722d09b35a76a9523";
$TransactionType="CustomerPayBillOnline";
$Amount= $Amount;
$PartyA=$PhoneNumber;
$PartyB="4069251";
$CallBackURL="https://techsavanna.technology/powergas_app/trans/callback_url.php";
$AccountReference="Powergas";
$TransactionDesc="Powergas Bussiness Shortcode";
$stkPushSimulation=$mpesa->STKPushSimulation($BusinessShortCode, $LipaNaMpesaPasskey, $TransactionType, $Amount, $PartyA, $PartyB, $PhoneNumber, $CallBackURL, $AccountReference, $TransactionDesc);

echo $stkPushSimulation;

?>
