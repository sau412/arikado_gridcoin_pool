<?php
// Only for command line
if(!isset($argc)) die();

// Gridcoinresearch send rewards
require_once("settings.php");
require_once("db.php");
require_once("auth.php");
require_once("boincmgr.php");
require_once("email.php");

db_connect();


db_query("INSERT IGNORE INTO `boincmgr_task_stats` (`project_uid`,`host_id`,`app`,`status`,`count`,`date`)
SELECT `project_uid`,`host_id`,`app`,`status`,count(*),CURDATE() FROM `boincmgr_tasks`
GROUP BY `project_uid`,`host_id`,`app`,`status`
");


$tasks_stats_array=db_query("SELECT bu.`username`,bu.`email`,bhp.`project_uid`,bh.`uid` AS host_uid,bts.`status`,
SUM(IF(bts.`date`=DATE_SUB(CURDATE(),INTERVAL 0 DAY),bts.`count`,0)) AS 'today',
SUM(IF(bts.`date`=DATE_SUB(CURDATE(),INTERVAL 1 DAY),bts.`count`,0)) AS 'yesterday'
FROM `boincmgr_task_stats` AS bts
LEFT OUTER JOIN `boincmgr_host_projects` AS bhp ON bhp.`host_id`=bts.`host_id` AND bhp.`project_uid`=bts.`project_uid`
LEFT OUTER JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bhp.`host_uid`
LEFT OUTER JOIN `boincmgr_users` AS bu ON bh.`username_uid`=bu.`uid`
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

$subject="gridcoinpool.ru errors alert";

foreach($error_email_to_error as $email => $errors_array) {
        $body=implode("<br>\n<br>\n",$errors_array);
        email_add($email,$subject,$body);
}

email_send_all();

?>
