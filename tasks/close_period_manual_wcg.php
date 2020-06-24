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
require_once("../lib/broker.php");

db_connect();

$comment="0.017735306 GBYTE for WCG";
$start_date="2019-08-14 00:00:00";
$stop_date="2019-09-13 00:00:00";
$reward="107.0778603";
$check_rewards=0;
$project_uid="15"; // WCG
$antiexp_rac_flag=0;
bill_close_period($comment,$start_date,$stop_date,$reward,$check_rewards,$project_uid,$antiexp_rac_flag);
?>
