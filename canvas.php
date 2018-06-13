<?php
// Graphs functions

// Drawing canvas
function canvas_graph($data,$width,$height) {
        $result="";
        $canvas_id=uniqid();

        // Detecting min and max values for axes
        // For relative graphs just delete next string
        $min_value=0;
        foreach($data as $point) {
                $timestamp=$point['timestamp'];
                $value=$point['value'];
                if(isset($min_value)) $min_value=min($min_value,$value);
                else $min_value=$value;
                if(isset($max_value)) $max_value=max($max_value,$value);
                else $max_value=$value;
                if(isset($min_timestamp)) $min_timestamp=min($min_timestamp,$timestamp);
                else $min_timestamp=$timestamp;
                if(isset($max_timestamp)) $max_timestamp=max($max_timestamp,$timestamp);
                else $max_timestamp=$timestamp;
        }

        // If no data
        if(isset($min_value)==FALSE) $min_value=0;
        if(isset($max_value)==FALSE) $max_value=0;
        if(isset($min_timestamp)==FALSE) $min_timestamp=0;
        if(isset($max_timestamp)==FALSE) $max_timestamp=0;

        // If min=max
        $max_value=max($min_value+1,$max_value);
        $max_timestamp=max($min_timestamp+1,$max_timestamp);

        $result.="<canvas id=$canvas_id width='$width' height='$height'>Canvas is not supported</canvas>\n";
        $result.="<script>\n";
        $result.=<<<_END
var c = document.getElementById("$canvas_id");
var ctx = c.getContext("2d");
ctx.fillStyle = 'green';
ctx.beginPath();
_END;
        foreach($data as $point) {
                $timestamp=$point['timestamp'];
                $value=$point['value'];

                $rel_value=$height*0.1+($height*0.9)*(1-($value-$min_value)/($max_value-$min_value));
                $rel_timestamp=($width-1)*($timestamp-$min_timestamp)/($max_timestamp-$min_timestamp);

                if(isset($first_value)) {
                        $result.="ctx.lineTo($rel_timestamp,$rel_value);\n";
                } else {
                        $result.="ctx.moveTo($rel_timestamp,$rel_value);\n";
                }
        $first_value=TRUE;
        }

        $result.=<<<_END
ctx.lineTo($width,$height);
ctx.lineTo(0,$height);
ctx.fill();

_END;
        $result.="</script>\n";
        return $result;
}

function canvas_graph_host_project($host_uid,$project_uid) {
        $host_uid_escaped=db_escape($host_uid);
        $project_uid_escaped=db_escape($project_uid);
        $data=db_query_to_array("SELECT AVG(`expavg_credit`) AS value,TRUNCATE(UNIX_TIMESTAMP(`timestamp`),-4) AS timestamp FROM `boincmgr_project_host_stats` WHERE `host_uid`='$host_uid_escaped' AND `project_uid`='$project_uid_escaped' AND DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 7 DAY)<`timestamp` GROUP BY TRUNCATE(UNIX_TIMESTAMP(`timestamp`),-4) ORDER BY `timestamp` ASC");
        return canvas_graph($data,100,30);
}

// Graph by host and all projects
function canvas_graph_host_all_projects($host_uid) {
        $host_uid_escaped=db_escape($host_uid);
        $data=db_query_to_array("SELECT AVG(`expavg_credit`) AS value,TRUNCATE(UNIX_TIMESTAMP(`timestamp`),-4) AS timestamp FROM `boincmgr_project_host_stats` WHERE `host_uid`='$host_uid_escaped' AND DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 7 DAY)<`timestamp` GROUP BY TRUNCATE(UNIX_TIMESTAMP(`timestamp`),-4) ORDER BY `timestamp` ASC");
        return canvas_graph($data,100,30);
}

// Graph by host and all projects
function canvas_graph_username_project($username_uid,$project_uid) {
        $username_uid_escaped=db_escape($username_uid);
        $project_uid_escaped=db_escape($project_uid);
        $data=db_query_to_array("SELECT AVG(`expavg_credit`) AS value,TRUNCATE(UNIX_TIMESTAMP(`timestamp`),-4) AS timestamp FROM `boincmgr_project_host_stats` WHERE `project_uid`='$project_uid_escaped' AND `host_uid` IN (SELECT `uid` FROM `boincmgr_hosts` WHERE `username_uid`='$username_uid') AND DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 7 DAY)<`timestamp` GROUP BY TRUNCATE(UNIX_TIMESTAMP(`timestamp`),-4) ORDER BY `timestamp` ASC");
        return canvas_graph($data,100,30);
}

// Graph by username and all projects
function canvas_graph_username($username_uid) {
        $username_uid_escaped=db_escape($username_uid);
        $data=db_query_to_array("SELECT AVG(`expavg_credit`) AS value,TRUNCATE(UNIX_TIMESTAMP(`timestamp`),-4) AS timestamp FROM `boincmgr_project_host_stats` WHERE `host_uid` IN (SELECT `uid` FROM `boincmgr_hosts` WHERE `username_uid`='$username_uid') AND DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 7 DAY)<`timestamp` GROUP BY TRUNCATE(UNIX_TIMESTAMP(`timestamp`),-4) ORDER BY `timestamp` ASC");
        return canvas_graph($data,100,30);
}

// Graph by project
function canvas_graph_project_total($project_uid) {
        $project_uid_escaped=db_escape($project_uid);
        $data=db_query_to_array("SELECT AVG(`expavg_credit`) AS value,TRUNCATE(UNIX_TIMESTAMP(`timestamp`),-4) AS timestamp FROM `boincmgr_project_stats` WHERE `project_uid`='$project_uid_escaped' AND DATE_SUB(CURRENT_TIMESTAMP,INTERVAL 30 DAY)<`timestamp` GROUP BY TRUNCATE(UNIX_TIMESTAMP(`timestamp`),-4) ORDER BY `timestamp` ASC");
        return canvas_graph($data,150,30);
}

?>
