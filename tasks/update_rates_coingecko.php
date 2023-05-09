<?php
// Get other cryptos rate from coingecko

if(!isset($argc)) die();

$f=fopen("/tmp/lockfile_rates","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
                die("Lockfile locked\n");
        }
}

require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/billing.php");
require_once("../lib/boincmgr.php");

db_connect();

// Setup cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_POST, FALSE);

// Get GRC price
curl_setopt($ch, CURLOPT_URL, "https://api.coingecko.com/api/v3/coins/gridcoin-research");
$result = curl_exec($ch);
if($result == "") {
	$message = "Coingecko incorrect reply for Gridcoin";
	echo "$message\n";
	auth_log($message);
	die();
}

$parsed_data = json_decode($result, true);
if(!isset($parsed_data['market_data']['current_price']['btc'])) {
	$message = "Coingecko Gridcoin data parsing error";
	echo "$message\n";
	auth_log($message);
	die();
}

$btc_per_grc_price = (float)$parsed_data['market_data']['current_price']['btc'];

// Query and calculate data for every other coin
$currency_data_array = db_query_to_array("SELECT `uid`, `name`, `url_api` FROM `currency`");

foreach($currency_data_array as $currency_data) {
	$uid = $currency_data['uid'];
	$name = $currency_data['name'];
	$api_url = $currency_data['url_api'];

	if($api_url == '') {
		continue;
	}
	curl_setopt($ch, CURLOPT_URL, $api_url);
	$result = curl_exec($ch);
	if($result == "") {
		$message = "Coingecko no data for for $name";
		echo "$message\n";
		auth_log($message);
		continue;
	}
	$parsed_data = json_decode($result, true);
	if(isset($result['error'])) {
		$message = "Coingecko reply parsing error for $name";
		echo "$message\n";
		auth_log($message);
		continue;
	}

	if(!isset($parsed_data['market_data']['current_price']['btc'])) {
		$message = "Coingecko no price in BTC for $name";
		echo "$message\n";
		auth_log($message);
		continue;
	}

	// Getting data from coingecko
	$btc_per_coin = (float)$parsed_data['market_data']['current_price']['btc'];
	if(is_nan($btc_per_coin) || is_infinite($btc_per_coin) || $btc_per_coin == 0) {
		$message = "Coingecko incorrect price in BTC for $name";
		echo "$message\n";
		auth_log($message);
		continue;
	}

	$price = $btc_per_grc_price / $btc_per_coin;

	// Escaping and updating
	$uid_escaped = db_escape($uid);
	$price_escaped = db_escape($price);

	db_query("UPDATE `currency` SET `rate_per_grc` = '$price_escaped' WHERE `uid` = '$uid_escaped'");
	echo "$name updated, price: $price\n";
}
