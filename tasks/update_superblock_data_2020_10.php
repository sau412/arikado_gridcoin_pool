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

$scraper_stats = file_get_contents("../../scraper/ConvergedStats.csv.gz");
$scraper_stats = gzdecode($scraper_stats);
$scraper_stats = explode("\n", $scraper_stats);

$project_count = 0;
foreach($scraper_stats as $str) {
	$row = explode(",", $str);
	if($row[0] != "byProject") continue;
	$project_name = $row[1];
	$total_rac = $row[5];

	$project_name_escaped = db_escape($project_name);
	$exists = db_query_to_variable("SELECT 1 FROM `projects` WHERE `superblock_name`='$project_name_escaped'");
	if($exists) echo "Project $project_name exists with RAC $total_rac\n";
	else echo "Project $project_name not exists with RAC $total_rac\n";
	/*	db_query("UPDATE `projects`
				SET `present_in_superblock`=1,`superblock_expavg_credit`='$total_rac'
				WHERE `superblock_name`='$project_name_escaped'");
*/
	$project_count ++;
}

//var_dump($scraper_stats);
/*
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
	var_dump($user_stats);
	$total_rac = 0;
	foreach($user_stats->user as $user_row) {
		$expavg_credit = $user_row['expavg_credit'];
		$total_rac += $expavg_credit;
	}
	echo "Total RAC $total_rac\n";
	die();
}*/

$magnitude_unit=grc_api_get_magnitude_unit();
echo "Magnitude unit: $magnitude_unit\n";
if($magnitude_unit!==FALSE && $magnitude_unit>0) boincmgr_set_variable("magnitude_unit",$magnitude_unit);

echo "Project count: $project_count\n";
if($project_count!==FALSE && $project_count>0) boincmgr_set_variable("project_count",$project_count);
//var_dump($projects);
?>
