<?php
// Only for command line
if(!isset($argc)) die();

// Gridcoinresearch send rewards
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/billing.php");
require_once("../lib/boincmgr.php");
require_once("../lib/html.php");
require_once("../lib/gridcoin_web_wallet.php");
require_once("../lib/broker.php");

$f=fopen("/tmp/lockfile_faucet","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
		die("Lockfile locked\n");
	}
}

// Check if unsent rewards exists
db_connect();

// Get balance
$current_balance=grc_web_get_balance();
echo "Current balance: $current_balance\n";
boincmgr_set_variable("hot_wallet_balance",$current_balance);

$unsent_count=db_query_to_variable("SELECT count(*) FROM `faucet_payouts` WHERE `txid`=''");

if($unsent_count==0) {
	echo "No unsent rewards for now\n";
	die();
}

// Get payout information for GRC
$payout_data_array=db_query_to_array("SELECT `uid`,`grc_address`,`amount`,`wallet_send_uid` FROM `faucet_payouts` WHERE `txid`=''");

//var_dump($payout_data_array);

foreach($payout_data_array as $payout_data) {
	$uid=$payout_data['uid'];
	$grc_address=$payout_data['grc_address'];
	$amount=$payout_data['amount'];
	$wallet_send_uid=$payout_data['wallet_send_uid'];

	$uid_escaped=db_escape($uid);

	// If we have funds for this
	if($wallet_send_uid) {
		//auth_log("Sending error, no wallet uid for address '$grc_address' amount '$amount'");
		$tx_data=grc_web_get_tx_status($wallet_send_uid);
		if($tx_data) {
			switch($tx_data->status) {
				case 'address error':
					db_query("UPDATE `faucet_payouts` SET `txid`='address error' WHERE `uid`='$uid_escaped'");
					auth_log("Faucet address error wallet uid '$wallet_send_uid' for address '$grc_address' amount '$amount'");
					break;
				case 'sending error':
					db_query("UPDATE `faucet_payouts` SET `txid`='sending error' WHERE `uid`='$uid_escaped'");
					auth_log("Faucet sending error wallet uid '$wallet_send_uid' for address '$grc_address' amount '$amount'");
					break;
				case 'sent':
					$tx_id=$tx_data->tx_id;
					$tx_id_escaped=db_escape($tx_id);
					db_query("UPDATE `faucet_payouts` SET `txid`='$tx_id_escaped' WHERE `uid`='$uid_escaped'");
					auth_log("Faucet sent wallet uid '$wallet_send_uid' for address '$grc_address' amount '$amount'");
					break;
			}
		}
	} else if($amount<$current_balance) {
		echo "Sending $amount to $grc_address\n";

		// Send coins, get txid
		$wallet_send_uid=grc_web_send($grc_address,$amount);
		$wallet_send_uid_escaped=db_escape($wallet_send_uid);
		if($wallet_send_uid && is_numeric($wallet_send_uid)) {
			db_query("UPDATE `faucet_payouts` SET `wallet_send_uid`='$wallet_send_uid_escaped' WHERE `uid`='$uid_escaped'");
			auth_log("Faucet sending queried, wallet uid '$wallet_send_uid' for address '$grc_address' amount '$amount'");
		} else {
			auth_log("Faucet sending error, no wallet uid for address '$grc_address' amount '$amount'");
		}
		echo "----\n";
	} else {
		// No funds
		echo "Insufficient funds for sending rewards\n";
		auth_log("Insufficient funds for sending rewards");
		break;
	}
}
?>
