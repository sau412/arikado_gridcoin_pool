<?php
// Get other cryptos rate from coingecko

if(!isset($argc)) die();

require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/billing.php");
require_once("../lib/boincmgr.php");

db_connect();

// Setup cURL
$ch=curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
curl_setopt($ch,CURLOPT_POST,FALSE);

// Get GRC price
curl_setopt($ch,CURLOPT_URL,"https://api.coingecko.com/api/v3/coins/gridcoin-research");
$result=curl_exec($ch);
if($result=="") {
        echo "No GRC price data\n";
        log_write("No GRC price data");
        die();
}
$parsed_data=json_decode($result);
$btc_per_grc_price=(string)$parsed_data->market_data->current_price->btc;

// Query and calculate data for every other coin
$currency_data_array=db_query_to_array("SELECT `uid`,`name`,`url_api` FROM `currency`");

foreach($currency_data_array as $currency_data) {
        $uid=$currency_data['uid'];
        $name=$currency_data['name'];
        $api_url=$currency_data['url_api'];

        if($api_url=='') continue;
        curl_setopt($ch,CURLOPT_URL,$api_url);
        $result=curl_exec($ch);
        if($result=="") {
                echo "No data for $name\n";
                auth_log("No data for $name");
                continue;
        }
        $parsed_data=json_decode($result);
        if(property_exists($result,'error')) {
                echo "Error for $name\n";
                auth_log("Error data for $name");
                continue;
        }

        // Getting data from coingecko
        $btc_per_coin=(float)$parsed_data->market_data->current_price->btc;
        $price=$btc_per_grc_price/$btc_per_coin;

        // Escaping and updating
        $uid_escaped=db_escape($uid);
        $price_escaped=db_escape($price);

        db_query("UPDATE `currency` SET `rate_per_grc`='$price_escaped' WHERE `uid`='$uid_escaped'");
        echo "$name updated, price: $price\n";
}

?>
