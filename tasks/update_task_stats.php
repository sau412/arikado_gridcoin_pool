<?php
// Only for command line
if(!isset($argc)) die();

// Gridcoinresearch send rewards
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/boincmgr.php");
require_once("../lib/email.php");
require_once("../lib/broker.php");

$f=fopen("/tmp/lockfile_task_stats","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
                die("Lockfile locked\n");
        }
}

db_connect();


db_query("INSERT IGNORE INTO `task_stats` (`project_uid`,`host_id`,`app`,`status`,`count`,`date`)
SELECT `project_uid`,`host_id`,`app`,`status`,count(*),CURDATE() FROM `tasks`
GROUP BY `project_uid`,`host_id`,`app`,`status`
");

$yesterday_data_exists = db_query_to_variable("SELECT 1 FROM `task_stats` WHERE `date`=DATE_SUB(CURDATE(),INTERVAL 1 DAY)");

if($yesterday_data_exists) {
	$tasks_stats_array=db_query_to_array("SELECT bu.`username`,bu.`email`,bhp.`project_uid`,bh.`uid` AS host_uid,bts.`status`,
	SUM(IF(bts.`date`=DATE_SUB(CURDATE(),INTERVAL 0 DAY),bts.`count`,0)) AS 'today',
	SUM(IF(bts.`date`=DATE_SUB(CURDATE(),INTERVAL 1 DAY),bts.`count`,0)) AS 'yesterday'
	FROM `task_stats` AS bts
	LEFT OUTER JOIN `host_projects` AS bhp ON bhp.`host_id`=bts.`host_id` AND bhp.`project_uid`=bts.`project_uid`
	LEFT OUTER JOIN `hosts` AS bh ON bh.`uid`=bhp.`host_uid`
	LEFT OUTER JOIN `users` AS bu ON bh.`username_uid`=bu.`uid`
	WHERE bhp.host_id IS NOT NULL AND bu.send_error_reports=1
	GROUP BY bu.`username`,bu.`email`,bhp.`project_uid`,bh.`uid`,bts.`status`");

	$error_statuses_array=array(
		"Validate error",
		"Error while computing",
		"Error while downloading",
		"Completed, marked as invalid",
		"Completed, too late to validate",
		"Not started by deadline - canceled",
		"Timed out - no response",
	);

	$error_email_to_error=array();

	foreach($tasks_stats_array as $task_stats) {
		$username=$task_stats['username'];
		$email=$task_stats['email'];
		$project_uid=$task_stats['project_uid'];
		$host_uid=$task_stats['host_uid'];
		$status=$task_stats['status'];
		$today=$task_stats['today'];
		$yesterday=$task_stats['yesterday'];

		if($today>$yesterday && in_array($status,$error_statuses_array)) {
			$project_name=boincmgr_get_project_name($project_uid);
			$host_name=boincmgr_get_host_name($host_uid);
			$error_email_to_error[$email][]="Errors are growing for host '$host_name' project '$project_name' status '$status' count: today '$today' yesterday '$yesterday'";
		}
	}

	$subject="$pool_name errors alert";

	foreach($error_email_to_error as $email => $errors_array) {
		$body=implode("<br>\n<br>\n",$errors_array);
		email_add($email, $subject, $body);
	}
}
else {
	auth_log("No data to send daily tasks report", 5);
}