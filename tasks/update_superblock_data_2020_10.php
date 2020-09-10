<?php
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/gridcoin_api.php");
require_once("../lib/boincmgr.php");
require_once("../lib/broker.php");

$f=fopen("/tmp/lockfile_superblock","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
                die("Lockfile locked\n");
        }
}

db_connect();

$projects_list = grc_api_get_projects_list();
//var_dump($projects_list);
$project_count = count($projects_list);


foreach($projects_list as $project_data) {
	$name = $project_data['display_name'];
	$stats_url = $project_data['stats_url'];
	echo "$name $stats_url\n";
	$user_stats_file = $stats_url."/user.gz";
	$user_stats = file_get_contents("$user_stats_file");
	$user_stats = gzdecode($user_stats);
	$user_stats = simplexml_load_string($user_stats);
	//var_dump($user_stats);
	$total_rac = 0;
	foreach($user_stats as $user_row) {
		$expavg_credit = $user_row['expavg_credit'];
		$total_rac += $expavg_credit;
	}
	echo "Total RAC $total_rac\n";
	die();
}

$magnitude_unit=grc_api_get_magnitude_unit();
echo "Magnitude unit: $magnitude_unit\n";
if($magnitude_unit!==FALSE && $magnitude_unit>0) boincmgr_set_variable("magnitude_unit",$magnitude_unit);

echo "Project count: $project_count\n";
if($project_count!==FALSE && $project_count>0) boincmgr_set_variable("project_count",$project_count);
//var_dump($projects);
?>
