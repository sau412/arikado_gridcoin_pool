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
$tasks_data=db_query_to_array("SELECT bp.`name` as 'Name',bt.`status` AS 'Status',count(*) AS 'Count' FROM `boincmgr_tasks` AS bt
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bt.`project_uid`
WHERE $where
GROUP BY bp.`name`,bt.`status`");
$page.=array_to_table($tasks_data);

$page.="<h2>Project stats</h2>\n";
$tasks_data=db_query_to_array("SELECT bp.`name`,count(*) AS count,ROUND(SUM(bt.score)) AS score,ROUND(SUM(bt.`cpu_time`)) AS cpu_time,ROUND(SUM(bt.score)/SUM(bt.`cpu_time`),4) AS 'score/cpu_time' FROM `boincmgr_tasks` AS bt
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bt.`project_uid`
WHERE $where
GROUP BY bp.`name`");
$page.=array_to_table($tasks_data);

$page.="<h2>Project/app stats</h2>\n";
$tasks_data=db_query_to_array("SELECT bp.`name`,bt.`app`,count(*) AS count,ROUND(SUM(bt.score)) AS score,ROUND(SUM(bt.`cpu_time`)) AS cpu_time,ROUND(SUM(bt.score)/SUM(bt.`cpu_time`),4) AS 'score/cpu_time' FROM `boincmgr_tasks` AS bt
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bt.`project_uid`
WHERE $where
GROUP BY bp.`name`,bt.`app`");
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

