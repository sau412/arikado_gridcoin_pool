<?php
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/boincmgr.php");

$f = fopen("/tmp/lockfile_cleanup_tasks","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
		die("Lockfile locked\n");
	}
}

db_connect();

$query = "DELETE FROM `tasks` WHERE DATE_SUB(NOW(), INTERVAL 3 MONTH) > `timestamp`";
db_query($query);

db_query("OPTIMIZE TABLE `tasks`");

$query = "DELETE FROM `task_stats` WHERE DATE_SUB(NOW(), INTERVAL 3 MONTH) > `date`";
db_query($query);

db_query("OPTIMIZE TABLE `task_stats`");
