<?php
$CLIENT_ID      = "XXXXXX";
$CLIENT_SECRET  = "XXXXXX";
$RETURN_URL     = "";
$CANCEL_URL     = "";
$AMOUNT         = 1;
$CURRENCY       = "USD";
$PRODUCT_ID     = "abc123";

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api-m.sandbox.paypal.com/v1/oauth2/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded',
    'Authorization: Basic '.base64_encode($CLIENT_ID.':'.$CLIENT_SECRET)
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$res = json_decode($response);

if($res) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api-m.sandbox.paypal.com/v2/checkout/orders',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
    "intent": "CAPTURE",
    "purchase_units": [
      {
        "reference_id": '. $PRODUCT_ID . ',
        "amount": {
          "currency_code": '. $CURRENCY . ',
          "value": '. $AMOUNT . '
        }
      }
    ],
    "payment_source": {
      "paypal": {
        "experience_context": {
          "payment_method_preference": "IMMEDIATE_PAYMENT_REQUIRED",
          "brand_name": "EXAMPLE INC",
          "locale": "en-US",
          "landing_page": "GUEST_CHECKOUT",
          "shipping_preference": "NO_SHIPPING",
          "user_action": "PAY_NOW",
          "return_url": '.$RETURN_URL . ',
          "cancel_url": '.$CANCEL_URL . '
        }
      }
    }
  }',
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
      'Authorization: Bearer '.$res->access_token
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $res = json_decode($response);

  if($res && count($res->links) != 0) {
    foreach ($res->links as $key => $link) {
      // print_r($link->rel);
      if($link->rel == 'payer-action') {
        header("location:".$link->href);
      }
    }
  }
} else {
  throw new \Exception("Error Generating Access Token", 1);
}
