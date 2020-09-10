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

if(!file_exists("../../scraper/ConvergedStats.csv.gz")) die("Stats file not exists\n");

$scraper_stats = file_get_contents("../../scraper/ConvergedStats.csv.gz");
$scraper_stats = gzdecode($scraper_stats);
$scraper_stats = explode("\n", $scraper_stats);

$project_count = 0;
$present_list = [];
foreach($scraper_stats as $str) {
	$row = explode(",", $str);
	if($row[0] != "byProject") continue;
	$project_name = $row[1];
	$total_rac = $row[5];
	$project_count ++;

	$project_name_escaped = db_escape($project_name);
	$present_list[] = $project_name_escaped;

	$exists = db_query_to_variable("SELECT 1 FROM `projects` WHERE `superblock_name`='$project_name_escaped'");
	if($exists) {
		echo "Project $project_name exists with RAC $total_rac\n";
		db_query("UPDATE `projects`
			SET `present_in_superblock` = 1,
				`superblock_expavg_credit` = '$total_rac'
			WHERE `superblock_name` = '$project_name_escaped'");
	}
	else {
		echo "Error: Project $project_name not exists\n";
	}	
}

if($project_count > 0) {
	// Mark other projects as absent
	$present_list_str=implode("','",$present_list);
	db_query("UPDATE `projects`
				SET `present_in_superblock`=0
				WHERE `superblock_name` NOT IN ('$present_list_str')");

	// Add project_count
	echo "Project count in SB: $project_count\n";
}

$magnitude_unit=grc_api_get_magnitude_unit();
echo "Magnitude unit: $magnitude_unit\n";
if($magnitude_unit!==FALSE && $magnitude_unit>0) boincmgr_set_variable("magnitude_unit",$magnitude_unit);

echo "Project count: $project_count\n";
if($project_count!==FALSE && $project_count>0) boincmgr_set_variable("project_count",$project_count);
//var_dump($projects);
?>
