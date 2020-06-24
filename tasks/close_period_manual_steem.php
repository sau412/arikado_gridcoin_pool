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

$comment="0 SBD and 3.196 SP from steemit donation post";
$start_date="2019-09-05 00:00:00";
$stop_date="2019-09-13 00:00:00";
$reward="134.76";
$check_rewards=0;
$project_uid=NULL; // all
$antiexp_rac_flag=0;
bill_close_period($comment,$start_date,$stop_date,$reward,$check_rewards,$project_uid,$antiexp_rac_flag);
?>
