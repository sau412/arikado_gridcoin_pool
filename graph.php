<?php
require_once("settings.php");
require_once("db.php");
require_once("html.php");
require_once("boincmgr.php");
require_once("canvas.php");

db_connect();

$canvas_size=1;

if(isset($_GET['project_uid'])) {
        $project_uid=$_GET['project_uid'];
        if(isset($_GET['username_uid'])) {
                $username_uid=$_GET['username_uid'];
                echo canvas_graph_username_project($username_uid,$project_uid,$canvas_size);
        } else if(isset($_GET['host_uid'])) {
                $host_uid=$_GET['host_uid'];
                echo canvas_graph_host_project($host_uid,$project_uid,$canvas_size);
        } else {
                echo canvas_graph_project_total($project_uid,$canvas_size);
        }
} else if(isset($_GET['host_uid'])) {
        $host_uid=$_GET['host_uid'];
        echo canvas_graph_host_all_projects($host_uid,$canvas_size);
} else if(isset($_GET['username_uid'])) {
        $username_uid=$_GET['username_uid'];
        echo canvas_graph_username($username_uid,$canvas_size);
}
?>
