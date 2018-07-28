<?php
// Get other cryptos rate from exchanges
// https://poloniex.com/public?command=returnTicker

if(!isset($argc)) die();

require_once("settings.php");
require_once("db.php");
require_once("auth.php");
require_once("billing.php");
require_once("boincmgr.php");

$poloniex_url="https://poloniex.com/public?command=returnTicker";
$enabled_pairs_array=array("BTC_GRC","BTC_DOGE","BTC_LTC","USDT_BTC","BTC_ETH","BTC_XMR");

db_connect();

// Setup cURL
$ch=curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
curl_setopt($ch,CURLOPT_POST,FALSE);
curl_setopt($ch,CURLOPT_URL,$poloniex_url);
$result = curl_exec ($ch);

if($result=="") die("No data from poloniex");

$data=json_decode($result);

// Store data
foreach($data as $pair => $pair_data) {
        if(!in_array($pair,$enabled_pairs_array)) continue;
        $rate=($pair_data->lowestAsk + $pair_data->highestBid)/2;
        boincmgr_set_variable($pair,$rate);
        echo "$pair $rate\n";
}

// Get data from bittrex
$bittrex_url="https://bittrex.com/api/v1.1/public/getticker?market=BTC-GBYTE";
curl_setopt($ch,CURLOPT_URL,$bittrex_url);
$result = curl_exec ($ch);
if($result=="") die("No data from bittrex");

$data=json_decode($result);
if($data->success=="true") {
        $pair="BTC_GBYTE";
        $rate=($data->result->Bid+$data->result->Ask)/2;
        boincmgr_set_variable($pair,$rate);
        echo "$pair $rate\n";
}
?>
