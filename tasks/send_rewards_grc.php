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

$f=fopen("/tmp/lockfile_send","w");
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

$owes_amount=db_query_to_variable("SELECT SUM(`amount`) FROM `payouts` WHERE `currency` IN ('GRC','GRC2') AND (`txid` IS NULL OR `txid`='')");

if($owes_amount=='') $owes_amount=0;

echo "Pool GRC owes: $owes_amount\n";

if($owes_amount>=$current_balance) {
	auth_log("Send rewards: Insufficient GRC balance: owes '$owes_amount' wallet balance '$current_balance'");
	die("Insufficient GRC balance: owes '$owes_amount' wallet balance '$current_balance'\n");
}

// Get payout information for GRC
$minimal_amount=db_query_to_variable("SELECT `payout_limit` FROM `currency` WHERE `name`='GRC'");
$minimal_amount_escaped=db_escape($minimal_amount);
$payout_data_array=db_query_to_array("SELECT
	GROUP_CONCAT(`uid`) AS uid_list,
	`user_uid`,
	`payout_address`,
	`currency`,
	SUM(`amount`) AS amount_sum,
	`wallet_send_uid`
FROM `payouts`
WHERE `currency` IN ('GRC','GRC2') AND (`txid` IS NULL OR `txid`='')
GROUP BY `user_uid`,`payout_address`,`currency`,`wallet_send_uid`
HAVING SUM(`amount`)>='$minimal_amount_escaped'");

foreach($payout_data_array as $payout_data) {
	$uid_list=$payout_data['uid_list'];
	$user_uid=$payout_data['user_uid'];
	$grc_address=$payout_data['payout_address'];
	$amount=$payout_data['amount_sum'];
	$currency=$payout_data['currency'];
	$wallet_send_uid=$payout_data['wallet_send_uid'];

	$amount=sprintf("%0.8F",$amount);

	// Only GRC payouts here
	if($currency!="GRC" && $currency!="GRC2") continue;

	// If we have funds for this
	if($wallet_send_uid) {
		$wallet_send_uid_escaped=db_escape($wallet_send_uid);
		//auth_log("Sending error, no wallet uid for address '$grc_address' amount '$amount'");
		$tx_data=grc_web_get_tx_status($wallet_send_uid);
		if($tx_data) {
			switch($tx_data->status) {
				case 'address error':
					db_query("UPDATE `payouts` SET `txid`='address error' WHERE `wallet_send_uid`='$wallet_send_uid_escaped'");
					auth_log("Address error wallet uid '$wallet_send_uid' for address '$grc_address' amount '$amount'");
					break;
				case 'sending error':
					//db_query("UPDATE `payouts` SET `txid`='sending error' WHERE `uid`='$uid_escaped'");
					//auth_log("Sending error wallet uid '$wallet_send_uid' for address '$grc_address' amount '$amount'");
					break;
				case 'sent':
					$tx_id=$tx_data->tx_id;
					$tx_id_escaped=db_escape($tx_id);
					db_query("UPDATE `payouts` SET `txid`='$tx_id_escaped' WHERE `wallet_send_uid`='$wallet_send_uid_escaped'");
					auth_log("Sent wallet uid '$wallet_send_uid' for address '$grc_address' amount '$amount'");
					boincmgr_update_balance($user_uid);
					break;
			}
		}
	} else if($amount<$current_balance) {
		echo "Sending $amount to $grc_address\n";

		// Send coins, get txid
		$wallet_send_uid=grc_web_send($grc_address,$amount);
		$wallet_send_uid_escaped=db_escape($wallet_send_uid);
		if($wallet_send_uid && is_numeric($wallet_send_uid)) {
			$user_uid_escaped=db_escape($user_uid);
			$payout_address_escaped=db_escape($grc_address);
			db_query("UPDATE `payouts` SET `wallet_send_uid`='$wallet_send_uid_escaped' WHERE `uid` IN ($uid_list)");
		} else {
			auth_log("Sending error, no wallet uid for address '$grc_address' amount '$amount'");
		}

		$current_balance=grc_web_get_balance();
		echo "Pool GRC wallet balance: $current_balance\n";
		boincmgr_set_variable("hot_wallet_balance",$current_balance);

		echo "----\n";
	} else {
		// No funds
		echo "Insufficient funds for sending rewards\n";
		auth_log("Insufficient funds for sending rewards");
		break;
	}
}
?>
