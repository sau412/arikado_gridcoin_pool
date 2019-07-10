<?php
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/boincmgr.php");

db_connect();

$mag_per_project=boincmgr_get_mag_per_project();

$mag=db_query_to_variable("SELECT $mag_per_project*SUM(`expavg_credit`/`team_expavg_credit`) FROM `projects` WHERE `present_in_superblock`=1");

$users=db_query_to_variable("SELECT count(*) FROM `users`");

$users_mag=db_query_to_variable("SELECT count(*) FROM (SELECT h.`username_uid`,$mag_per_project*SUM(phl.`expavg_credit`/p.`team_expavg_credit`) AS mag FROM `hosts` AS h
JOIN `project_hosts_last` AS phl ON phl.`host_uid`=h.`uid`
JOIN `projects` AS p ON p.`uid`=phl.`project_uid`
WHERE `present_in_superblock`=1
GROUP BY h.`username_uid`
HAVING $mag_per_project*SUM(phl.`expavg_credit`/p.`team_expavg_credit`)>1) AS a");

$hosts=db_query_to_variable("SELECT count(*) FROM `hosts`");

$hosts_mag=db_query_to_variable("SELECT count(*) FROM (SELECT phl.`host_uid`,$mag_per_project*SUM(phl.`expavg_credit`/p.`team_expavg_credit`) AS mag FROM `project_hosts_last` AS phl
JOIN `projects` AS p ON p.`uid`=phl.`project_uid`
WHERE `present_in_superblock`=1
GROUP BY `host_uid`
HAVING $mag_per_project*SUM(phl.`expavg_credit`/p.`team_expavg_credit`)>1) AS a");

echo "mag:$mag users:$users users_mag:$users_mag hosts:$hosts hosts_mag:$hosts_mag\n";
?>
