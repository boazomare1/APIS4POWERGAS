<?php
  $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
  
  $consumerKey = 'juZJR3AFFGVohQDLE8wGSmWxQIMdXaTA'; //Fill with your app Consumer Key
  $consumerSecret = 'wVYtnwWK2em03skm'; // Fill with your app Secret

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  $credentials = base64_encode($consumerKey.':'.$consumerSecret);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials)); //setting a custom header
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
  $curl_response = curl_exec($curl);
  
  $access_token = json_decode($curl_response)->access_token;
  
  echo $access_token;
  ?>
  