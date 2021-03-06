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

$f=fopen("/tmp/lockfile_billing","w");
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

$owes_amount=db_query_to_variable("SELECT SUM(`currency_amount`) FROM `project_host_stats` WHERE `currency` IN ('GRC','GRC2') AND `is_payed_out`=0");

if($owes_amount=='') $owes_amount=0;

echo "Pool GRC owes: $owes_amount\n";

if($owes_amount>=$current_balance) {
	auth_log("Send rewards: Insufficient GRC balance: owes '$owes_amount' wallet balance '$current_balance'");
	die("Insufficient GRC balance: owes '$owes_amount' wallet balance '$current_balance'\n");
}

if($owes_amount==0) {
	auth_log("Send rewards: nothing to bill");
	echo "Nothing to bill\n";
	//die("Nothing to send\n");
} else {
	// Do billing
	$billing_today=db_query_to_variable("SELECT 1 FROM `billing_periods` HAVING DATE(MAX(`stop_date`))=DATE(NOW())");
	if($billing_today=='') {
		auth_log("Send rewards: billing today started");
		echo "Billing today started\n";
		bill_forecast_close_period();
	} else {
		echo "Billing already done today\n";
		auth_log("Send rewards: Billing already done today");
//		die("Billing already done today\n");
	}
}

?>
