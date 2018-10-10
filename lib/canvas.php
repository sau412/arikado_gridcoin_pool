<?php
// Graphs functions

// Drawing canvas
function canvas_graph($data,$days,$big_canvas=0,$graph_name='',$url='') {
        $result="";
        $canvas_id=uniqid();

        if($big_canvas==0) {
                $width=100;
                $height=30;
        } else {
                $width=800;
                $height=480;
        }

        // Detecting min and max values for axes
        // For relative graphs just delete next string
        foreach($data as $point) {
                $timestamp=$point['timestamp'];
                $value=$point['value'];

                // Min and max for graph, changed after
                if(isset($min_value)) $min_value=min($min_value,$value);
                else $min_value=$value;
                if(isset($max_value)) $max_value=max($max_value,$value);
                else $max_value=$value;

                // Min and max timestamp
                if(isset($min_timestamp)) $min_timestamp=min($min_timestamp,$timestamp);
                else $min_timestamp=$timestamp;
                if(isset($max_timestamp)) $max_timestamp=max($max_timestamp,$timestamp);
                else $max_timestamp=$timestamp;
        }

        $real_min_value=isset($min_value)?$min_value:0;
        $real_max_value=isset($max_value)?$max_value:0;

        // Min value is zero
        $min_value=0;

        // If no data
        if(isset($min_value)==FALSE) $min_value=0;
        if(isset($max_value)==FALSE) $max_value=0;
        if(isset($min_timestamp)==FALSE) $min_timestamp=0;
        if(isset($max_timestamp)==FALSE) $max_timestamp=0;
        $min_timestamp=min($max_timestamp-86400*$days,$min_timestamp);

        // If min=max
        if($max_value<=$min_value) $max_value=$min_value+1;
        if($max_timestamp<=$min_timestamp) $max_timestamp=$min_timestamp+1;

        // Calculate grid value (1/2/5 * 10^N)
        $grid_max=$max_value;
        $grid_order=1;
        while($grid_max<1) {
                $grid_order/=10;
                $grid_max*=10;
        }
        while($grid_max>10) {
                $grid_order*=10;
                $grid_max/=10;
        }

        if($grid_max<=2) {
                $grid_max=2*$grid_order;
                $grid_type=2;
        } else if($grid_max<=3) {
                $grid_max=3*$grid_order;
                $grid_type=3;
        } else if($grid_max<=5) {
                $grid_max=5*$grid_order;
                $grid_type=5;
        } else {
                $grid_max=10*$grid_order;
                $grid_type=1;
        }

        if($big_canvas) {
                $padding=20;
                $corr=0.5; // Correction for thin lines
                $radius=1;
                $font_size=12;
                $result.="<canvas id=$canvas_id width='$width' height='$height'>Canvas is not supported</canvas>\n";
                $result.="<script>\n";
                $result.=<<<_END
var c = document.getElementById("$canvas_id");
var ctx = c.getContext("2d");
ctx.fillStyle = 'lightgreen';
ctx.font = "${font_size}px Serif";
ctx.beginPath();

_END;

                foreach($data as $point) {
                        $timestamp=$point['timestamp'];
                        $value=$point['value'];

                        $rel_value=$padding+($height-$padding*2-1)*(1-($value-$min_value)/($grid_max));
                        //$rel_value=$padding+($height-$padding*2-1)*(1-($value-$min_value)/($max_value-$min_value));
                        $rel_timestamp=$padding+($width-$padding*2)*($timestamp-$min_timestamp)/($max_timestamp-$min_timestamp);

                        $rel_value=floor($rel_value)+$corr;
                        $rel_timestamp=floor($rel_timestamp)+$corr;

                        if($value==$real_max_value) {
                                $max_point_data=array(
                                        "timestamp"=>$timestamp,
                                        "value"=>$value,
                                        "rel_timestamp"=>$rel_timestamp,
                                        "rel_value"=>$rel_value,
                                );
                        }
                        if($value==$real_min_value) {
                                $min_point_data=array(
                                        "timestamp"=>$timestamp,
                                        "value"=>$value,
                                        "rel_timestamp"=>$rel_timestamp,
                                        "rel_value"=>$rel_value,
                                );
                        }

                        if(isset($first_value)) {
                                $result.="ctx.lineTo($rel_timestamp,$rel_value);\n";
                        } else {
                                $result.="ctx.moveTo($rel_timestamp,$rel_value);\n";
                        }
                $first_value=TRUE;
                }

                $result.=<<<_END
ctx.lineTo($width-$padding,$height-$padding);
ctx.lineTo($padding,$height-$padding);
ctx.fill();

ctx.beginPath();

ctx.strokeStyle = 'green';
ctx.lineWidth=1;
ctx.stroke();

ctx.beginPath();

ctx.strokeStyle = 'green';
ctx.fillStyle = 'green';

_END;

                // Vertical axes (days)
                foreach(range(0,$days) as $day) {
                        $grid_x=floor($padding+$day*($width-2*$padding)/$days)+$corr;
                        $grid_y_begin=floor($padding)+$corr;
                        $grid_y_end=floor($height-$padding)+$corr;
                        $result.="ctx.moveTo($grid_x,$grid_y_begin);\n";
                        $result.="ctx.lineTo($grid_x,$grid_y_end);\n";
                }

                // Horizontal axes (values)
                if($grid_type==1) {
                        $axes_array=array(0, 2, 4, 6, 8, 10);
                } else if($grid_type==2) {
                        $axes_array=array(0, 0.5, 1.0, 1.5, 2.0);
                } else if($grid_type==3) {
                        $axes_array=array(0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0);
                } else {
                        $axes_array=array(0, 1, 2, 3, 4, 5);
                }
                $axes_count=count($axes_array);

                foreach($axes_array as $index => $val) {
                        $grid_x_begin=floor($padding)+$corr;
                        $grid_x_end=floor($width-$padding)+$corr;
                        $grid_y=floor($padding+($axes_count-1-$index)*($height-$padding*2)/($axes_count-1))+$corr;
                        $grid_stroke=$val*$grid_order;
                        $result.="ctx.moveTo($grid_x_begin,$grid_y);\n";
                        $result.="ctx.lineTo($grid_x_end,$grid_y);\n";
                        $result.="ctx.textAlign = 'right';\n";
                        $result.="ctx.fillText($grid_stroke,$grid_x_begin-$font_size/2,$grid_y+$font_size/2);\n";
                }
                $result.="ctx.stroke();\n";

                $result.="ctx.fillStyle = 'red';\n";
                if(isset($max_point_data)) {
                        $value=$max_point_data['value'];
                        $rel_timestamp=$max_point_data['rel_timestamp'];
                        $rel_value=$max_point_data['rel_value'];
                        $show_value=sprintf("%0.4F",$value);
                        if($rel_timestamp>($width/2)) $result.="ctx.textAlign = 'right';\n";
                        else $result.="ctx.textAlign = 'left';\n";
                        $result.="ctx.fillText('$show_value',$rel_timestamp,$rel_value+$font_size);\n";
                }
                $result.="ctx.fillStyle = 'blue';\n";
                if(isset($min_point_data)) {
                        $value=$min_point_data['value'];
                        $rel_timestamp=$min_point_data['rel_timestamp'];
                        $rel_value=$min_point_data['rel_value'];
                        $show_value=sprintf("%0.4F",$value);
                        if($rel_timestamp>($width/2)) $result.="ctx.textAlign = 'right';\n";
                        else $result.="ctx.textAlign = 'left';\n";
                        $result.="ctx.fillText('$show_value',$rel_timestamp,$rel_value+$font_size);\n";
                }
                $result.="ctx.fillStyle = 'green';\n";
                $result.="ctx.textAlign = 'center';\n";
                $graph_name=html_escape($graph_name);
                $result.="ctx.fillText('$graph_name',$width/2,$padding/2+$font_size/2);\n";
                $result.="ctx.stroke();\n";
                $result.="ctx.fill();\n";
                $result.="</script>\n";
        } else {
                $result.="<a href='$url'>\n";
                $result.="<canvas id=$canvas_id width='$width' height='$height'>Canvas is not supported</canvas>\n";
                $result.="<script>\n";
                $result.=<<<_END
var c = document.getElementById("$canvas_id");
var ctx = c.getContext("2d");
ctx.fillStyle = 'lightgreen';
ctx.beginPath();
_END;
                foreach($data as $point) {
                        $timestamp=$point['timestamp'];
                        $value=$point['value'];

                        $rel_value=($height-1)*(1-($value-$min_value)/($max_value-$min_value));
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
                $result.="</a>\n";
        }
        return $result;
}

// Graph by host and and project
function canvas_graph_host_project($host_uid,$project_uid,$big_canvas=0) {
        $host_uid_escaped=db_escape($host_uid);
        $project_uid_escaped=db_escape($project_uid);
        $mag_per_project=boincmgr_get_mag_per_project();
        $project_name=boincmgr_get_project_name($project_uid);

        if($big_canvas) {
                $days=30;
        } else {
                $days=7;
        }
        $graph_name="Magnitude for single host for project $project_name for $days days";

        $data=db_query_to_array("SELECT $mag_per_project*AVG(bphs.`expavg_credit`/bp.`team_expavg_credit`) AS value,TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4) AS timestamp
FROM `boincmgr_project_host_stats` AS bphs
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphs.`project_uid`
WHERE bphs.`host_uid`='$host_uid_escaped' AND bphs.`project_uid`='$project_uid_escaped' AND DATE_SUB(CURRENT_TIMESTAMP,INTERVAL $days DAY)<bphs.`timestamp`
GROUP BY TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4)
ORDER BY TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4) ASC");

        return canvas_graph($data,$days,$big_canvas,$graph_name,"graph.php?host_uid=$host_uid&project_uid=$project_uid");
}

// Graph by host and all projects
function canvas_graph_host_all_projects($host_uid,$big_canvas=0) {
        $host_uid_escaped=db_escape($host_uid);
        $mag_per_project=boincmgr_get_mag_per_project();

        if($big_canvas) {
                $days=30;
        } else {
                $days=7;
        }
        $graph_name="Magnitude for single host for all projects for $days days";

        $data=db_query_to_array("SELECT $mag_per_project*AVG(bphs.`expavg_credit`/bp.`team_expavg_credit`) AS value,TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4) AS timestamp
FROM `boincmgr_project_host_stats` AS bphs
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphs.`project_uid`
WHERE bphs.`host_uid`='$host_uid_escaped' AND DATE_SUB(CURRENT_TIMESTAMP,INTERVAL $days DAY)<bphs.`timestamp`
GROUP BY TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4)
ORDER BY TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4) ASC");
        return canvas_graph($data,$days,$big_canvas,$graph_name,"graph.php?host_uid=$host_uid");
}

// Graph by username and all hosts
function canvas_graph_username_project($username_uid,$project_uid,$big_canvas=0) {
        $username_uid_escaped=db_escape($username_uid);
        $project_uid_escaped=db_escape($project_uid);
        $mag_per_project=boincmgr_get_mag_per_project();
        $project_name=boincmgr_get_project_name($project_uid);
        $user_name=boincmgr_get_user_name($username_uid);

        if($big_canvas) {
                $days=30;
        } else {
                $days=7;
        }
        $graph_name="Magnitude for user $user_name for $project_name for $days days";

        $data=db_query_to_array("SELECT $mag_per_project*AVG(bphs.`expavg_credit`/bp.`team_expavg_credit`) AS value,TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4) AS timestamp
FROM `boincmgr_project_host_stats` AS bphs
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphs.`project_uid`
LEFT OUTER JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphs.`host_uid`
WHERE bphs.`project_uid`='$project_uid_escaped' AND bh.`username_uid`='$username_uid' AND DATE_SUB(CURRENT_TIMESTAMP,INTERVAL $days DAY)<bphs.`timestamp`
GROUP BY TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4)
ORDER BY TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4) ASC");

        return canvas_graph($data,$days,$big_canvas,$graph_name,"graph.php?username_uid=$username_uid&project_uid=$project_uid");
}

// Graph by username and all projects
function canvas_graph_username($username_uid,$big_canvas=0) {
        $username_uid_escaped=db_escape($username_uid);
        $mag_per_project=boincmgr_get_mag_per_project();

        $user_name=boincmgr_get_user_name($username_uid);

        if($big_canvas) {
                $days=30;
        } else {
                $days=7;
        }
        $graph_name="Magnitude for user $user_name for all projects for $days days";

        $data=db_query_to_array("SELECT $mag_per_project*AVG(bphs.`expavg_credit`/bp.`team_expavg_credit`) AS value,TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4) AS timestamp
FROM `boincmgr_project_host_stats` AS bphs
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphs.`project_uid`
LEFT OUTER JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphs.`host_uid`
WHERE bh.`username_uid`='$username_uid' AND DATE_SUB(CURRENT_TIMESTAMP,INTERVAL $days DAY)<bphs.`timestamp`
GROUP BY TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4)
ORDER BY TRUNCATE(UNIX_TIMESTAMP(bphs.`timestamp`),-4) ASC");

        return canvas_graph($data,$days,$big_canvas,$graph_name,"graph.php?username_uid=$username_uid");
}

// Graph by project
function canvas_graph_project_total($project_uid,$big_canvas=0) {
        $project_uid_escaped=db_escape($project_uid);
        $mag_per_project=boincmgr_get_mag_per_project();
        $project_name=boincmgr_get_project_name($project_uid);

        if($big_canvas) {
                $days=30;
        } else {
                $days=7;
        }
        $graph_name="Magnitude for project $project_name for $days days";

        $data=db_query_to_array("SELECT $mag_per_project*AVG(bps.`expavg_credit`/bp.`team_expavg_credit`) AS value,TRUNCATE(UNIX_TIMESTAMP(bps.`timestamp`),-4) AS timestamp
FROM `boincmgr_project_stats` AS bps
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.`uid`=bps.`project_uid`
WHERE bps.`project_uid`='$project_uid_escaped' AND DATE_SUB(CURRENT_TIMESTAMP,INTERVAL $days DAY)<bps.`timestamp`
GROUP BY TRUNCATE(UNIX_TIMESTAMP(bps.`timestamp`),-4)
ORDER BY TRUNCATE(UNIX_TIMESTAMP(bps.`timestamp`),-4) ASC");

        return canvas_graph($data,$days,$big_canvas,$graph_name,"graph.php?project_uid=$project_uid");
}

?>
