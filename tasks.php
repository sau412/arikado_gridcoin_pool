<?php
require_once("settings.php");
require_once("db.php");
require_once("html.php");
require_once("auth.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>grc.arikado.ru task status</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="common.css">
<script src="common.js"></script>
<link rel="icon" href="favicon.png" type="image/png">
</head>
<body>
<h1>Pool task stats</h1>
<p>No data for Einstein@home, yoyo@home, WCG</p>
<p><a href='./'>Back to pool main page</a></p>
<?php

db_connect();

$page="";

if(isset($_GET['username_uid'])) {
        $username_uid=html_strip($_GET['username_uid']);
        $username_uid_escaped=db_escape($username_uid);
        $page.="<h2>Exists task stats</h2>\n";
        $exists_stats=db_query_to_array("SELECT IF(FROM_BASE64(bh.`domain_name`)=CONVERT(FROM_BASE64(bh.`domain_name`) USING ASCII),FROM_BASE64(bh.`domain_name`),bh.`domain_name`) AS 'Domain name',
bp.`name`,count(bt.`uid`) AS 'tasks count',
CONCAT('<a href=\'tasks.php?project_uid=',bhp.`project_uid`,'&host_id=',bhp.`host_id`,'\'>view</a>') AS URL
FROM `boincmgr_host_projects` AS bhp
LEFT OUTER JOIN `boincmgr_hosts` AS bh ON bh.uid=bhp.host_uid
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bhp.`project_uid`=bp.`uid`
LEFT OUTER JOIN `boincmgr_tasks` AS bt ON bt.host_id=bhp.host_id AND bt.project_uid=bhp.project_uid
WHERE bh.username_uid='$username_uid_escaped'
GROUP BY bh.`domain_name`,bp.`name`,bhp.`project_uid`,bhp.`host_id`
HAVING count(bt.`uid`)>0");
        $page.=array_to_table($exists_stats);
        echo $page;
        flush();
        die();
}

if(isset($_GET['project_uid'])) {
        $project_uid=html_strip($_GET['project_uid']);
        $project_uid_escaped=db_escape($project_uid);
        $where="bt.`project_uid`='$project_uid_escaped'";
        if(isset($_GET['host_id'])) {
                $host_id=html_strip($_GET['host_id']);
                $host_id_escaped=db_escape($host_id);
                $where.=" AND bt.`host_id`='$host_id_escaped'";
        }
} else {
        $where="1";
}

$page.="<h2>Status stats</h2>\n";
if(isset($project_uid)) {
        $tasks_data=db_query_to_array("SELECT bp.`name` as 'Name',bt.`app` AS App,bt.`status` AS 'Status',count(*) AS 'Count',ROUND(SUM(bt.`score`)) AS Score FROM `boincmgr_tasks` AS bt
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bt.`project_uid`
WHERE $where
GROUP BY bp.`name`,bt.`app`,bt.`status`");
        $page.=array_to_table($tasks_data);
} else {
        $tasks_data=db_query_to_array("SELECT bp.`name` as 'Name',bt.`status` AS 'Status',count(*) AS 'Count',ROUND(SUM(bt.`score`)) AS Score FROM `boincmgr_tasks` AS bt
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bt.`project_uid`
WHERE $where
GROUP BY bp.`name`,bt.`status`");
        $page.=array_to_table($tasks_data);
}

$page.="<h2>Status history</h2>\n";
if(isset($project_uid)) {
        $tasks_data=db_query_to_array("SELECT bp.`name` as 'Name',bt.`app` AS App,bt.`status` AS 'Status',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 0 DAY),bt.`count`,0)) AS 'Today',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 1 DAY),bt.`count`,0)) AS 'day ago',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 2 DAY),bt.`count`,0)) AS '2 day',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 3 DAY),bt.`count`,0)) AS '3 day',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 4 DAY),bt.`count`,0)) AS '4 day',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 5 DAY),bt.`count`,0)) AS '5 day',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 6 DAY),bt.`count`,0)) AS '6 day',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 7 DAY),bt.`count`,0)) AS 'week ago'
FROM `boincmgr_task_stats` AS bt
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bt.`project_uid`
WHERE $where
GROUP BY bp.`name`,bt.`app`,bt.`status`");
        $page.=array_to_table($tasks_data);
} else {
        $tasks_data=db_query_to_array("SELECT bp.`name` as 'Name',bt.`status` AS 'Status',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 0 DAY),bt.`count`,0)) AS 'Today',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 1 DAY),bt.`count`,0)) AS 'day ago',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 2 DAY),bt.`count`,0)) AS '2 day',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 3 DAY),bt.`count`,0)) AS '3 day',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 4 DAY),bt.`count`,0)) AS '4 day',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 5 DAY),bt.`count`,0)) AS '5 day',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 6 DAY),bt.`count`,0)) AS '6 day',
SUM(IF(bt.`date`=DATE_SUB(CURDATE(),INTERVAL 7 DAY),bt.`count`,0)) AS 'week ago'
FROM `boincmgr_task_stats` AS bt
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bt.`project_uid`
WHERE $where
GROUP BY bp.`name`,bt.`status`");
        $page.=array_to_table($tasks_data);
}

$page.="<h2>Project stats</h2>\n";
$tasks_data=db_query_to_array("SELECT bp.`name`,count(*) AS 'total count',SUM(IF(bt.`status` IN ('Completed and validated','Completed and validated (1<sup>st</sup>)'),1,0)) AS 'completed',ROUND(SUM(bt.score)) AS score,ROUND(SUM(bt.`cpu_time`)) AS cpu_time,
ROUND(AVG(bp.`team_expavg_credit`)) AS total_rac,ROUND(1E9*SUM(bt.score)/(SUM(bt.`elapsed_time`)*AVG(bp.`team_expavg_credit`)),4) AS '10<sup>9</sup>*score/(elapsed_time*total_rac)' FROM `boincmgr_tasks` AS bt
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bt.`project_uid`
WHERE $where
GROUP BY bp.`name`");
$page.=array_to_table($tasks_data);

$page.="<h2>Project/app stats (completed)</h2>\n";
$tasks_data=db_query_to_array("SELECT bp.`name`,bt.`app`,count(*) AS count,ROUND(SUM(bt.score)) AS sum_score,ROUND(SUM(bt.`cpu_time`)) AS cpu_time,
ROUND(AVG(bp.`team_expavg_credit`)) AS total_rac,ROUND(1E9*SUM(bt.score)/(SUM(bt.`elapsed_time`)*AVG(bp.`team_expavg_credit`)),4) AS '10<sup>9</sup>*score/(elapsed_time*total_rac)' FROM `boincmgr_tasks` AS bt
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bt.`project_uid`
WHERE $where AND bt.status IN ('Completed and validated','Completed and validated (1<sup>st</sup>)')
GROUP BY bp.`name`,bt.`app`
-- ORDER BY SUM(bt.score)/(SUM(bt.`elapsed_time`)*AVG(bp.`team_expavg_credit`))
");
$page.=array_to_table($tasks_data);

if(isset($host_id)) {
        $page.="<h2>Tasks status</h2>\n";
        $tasks_data=db_query_to_array("SELECT bp.`name`,bt.`result_name`,bt.`result_id`,bt.`workunit_id`,bt.`host_id`,bt.`sent`,bt.`deadline`,bt.`status`,bt.`elapsed_time`,bt.`cpu_time`,bt.`score`,bt.`app` FROM `boincmgr_tasks` AS `bt`
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bt.`project_uid`
WHERE $where");
        $page.=array_to_table($tasks_data);
}
function array_to_table($data) {
        $result="";
        $result.="<table>\n";
        foreach($data as $row) {
                $result.="<tr>";
                foreach($row as $key=>$value) {
                        $result.="<th>$key</th>";
                }
                $result.="</tr>\n";
                break;
        }
        foreach($data as $row) {
                $result.="<tr>";
                foreach($row as $key=>$value) {
                        $result.="<td>$value</td>";
                }
                $result.="</tr>\n";
        }
        $result.="</table>\n";
        return $result;
}

function count_memo($count) {
        $result="";
        $result.="$count ";
        if($count<10) $result.=" <span class='status_bad'>not very representative</span>";
        else if($count<40) $result.=" <span class='status_orange'>average representativity</span>";
        else $result.=" <span class='status_good'>good representativity</span>";
        return $result;
}

echo $page;
?>
<p><a href='./'>Back to pool main page</a></p>
</body>
</html>

