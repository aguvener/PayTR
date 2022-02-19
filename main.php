<?php

require 'vendor/autoload.php';
include "function.php";

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

$ini = parse_ini_file("config.ini");

$merchant_id 	= $ini['merchant_id'];
$merchant_key 	= $ini['merchant_key'];
$merchant_salt	= $ini['merchant_salt'];

$client = new Client([
    'base_uri' => 'https://www.paytr.com/odeme/api/',
]);



function binsorgu($bin_number){
    global $client, $merchant_id, $merchant_key, $merchant_salt;
    $hash_str = $bin_number . $merchant_id . $merchant_salt;
    $paytr_token=base64_encode(hash_hmac('sha256', $hash_str, $merchant_key, true));
    $post_vals=array(
        'merchant_id'=>$merchant_id,
        'bin_number'=>$bin_number,
        'paytr_token'=>$paytr_token
    );
    try {
        $result = $client->request('POST', 'bin-detail', [
            'connect_timeout' => 20.0,
            'form_params' => $post_vals,
            'headers' => [
                'Content-Type' => 'multipart/form-data'
            ]
        ]);
    }catch(ClientException $e){
        $result = $e->getResponse();
    }
    $result = json_decode($result->getBody()->getContents(),1);
    
    if($result['status']=='error')
        return "PAYTR BIN detail request error. Error: ".$result['err_msg'];
    elseif($result['status']=='failed')
        return "BIN tanımlı değil. (Örneğin bir yurtdışı kartı)";
    else
        return json_encode($result);
}

$bin_number = $_GET['bin'];

if (isset($bin_number)) {
    echo binsorgu($bin_number);
}