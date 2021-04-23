<?php
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/boincmgr.php");
require_once("../lib/broker.php");

$f=fopen("/tmp/lockfile_payouts","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
		die("Lockfile locked\n");
	}
}

db_connect();

db_query("UPDATE `payouts` p
            LEFT OUTER JOIN `users` u ON u.uid = p.user_uid
            JOIN `currency` uc ON uc.`name` = u.`currency`
            SET p.rate = uc.rate_per_grc,
                p.payout_address = u.payout_address,
                p.currency = u.currency,
                p.amount = p.grc_amount * uc.rate_per_grc
            WHERE (u.payout_address <> p.payout_address OR u.currency <> p.currency) AND txid IS NULL");