<?php
require_once("../lin/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/boincmgr.php");

db_connect();

$data_array=db_query_to_array("SELECT `uid` FROM `boincmgr_users`");

foreach($data_array as $data_point) {
	$user_uid=$data_point['uid'];
	boincmgr_update_balance($user_uid);
}
?>
