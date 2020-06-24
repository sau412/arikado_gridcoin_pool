<?php
// Update graphs cache
if(!isset($argc)) die();

$f=fopen("/tmp/lockfile_graphs","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
		die("Lockfile locked\n");
	}
}

require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/boincmgr.php");
require_once("../lib/auth.php");
require_once("../lib/canvas.php");
require_once("../lib/broker.php");

db_connect();

echo "Updating host graphs\n";
$host_uid_array=db_query_to_array("SELECT `uid` FROM `hosts`");
$index=1;
foreach($host_uid_array as $host_uid_row) {
	echo "Host $index of ".count($host_uid_array)."\n";
	$host_uid=$host_uid_row['uid'];
	boincmgr_cache_function("canvas_graph_host_all_projects",array($host_uid),TRUE);
	$project_uid_array=db_query_to_array("SELECT `uid` FROM `projects`");
	foreach($project_uid_array as $project_uid_row) {
		$project_uid=$project_uid_row['uid'];
		boincmgr_cache_function("canvas_graph_host_project",array($host_uid,$project_uid),TRUE);
	}
	$index++;
}
echo "\n";

echo "Updating users graphs\n";
$user_uid_array=db_query_to_array("SELECT `uid` FROM `users`");
$index=1;
foreach($user_uid_array as $user_uid_row) {
	echo "User $index of ".count($user_uid_array)."\n";
	$user_uid=$user_uid_row['uid'];
	boincmgr_cache_function("canvas_graph_username",array($user_uid),TRUE);
	$project_uid_array=db_query_to_array("SELECT `uid` FROM `projects`");
	foreach($project_uid_array as $project_uid_row) {
		$project_uid=$project_uid_row['uid'];
		boincmgr_cache_function("canvas_graph_username_project",array($user_uid,$project_uid),TRUE);
	}
	$index++;
}
echo "\n";

?>
