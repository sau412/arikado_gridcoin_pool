<?php
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/html.php");
require_once("../lib/billing.php");
require_once("../lib/boincmgr.php");
require_once("../lib/canvas.php");
require_once("../lib/xml_parser.php");
require_once("../lib/captcha.php");

db_connect();

$comment="Gridcoin forecast miner rewards";
$start_date="2019-07-01 07:18:33";
$stop_date="2019-07-02 07:22:04";
$reward="66.74750758";
$check_rewards=1;
$project_uid=0;
$antiexp_rac_flag=0;
bill_close_period($comment,$start_date,$stop_date,$reward,$check_rewards,$project_uid,$antiexp_rac_flag);
?>
