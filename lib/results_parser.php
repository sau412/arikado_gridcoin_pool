<?php

// Parse HTML page for tasks data
function results_parse_page($project_uid,$data) {
        // Some pages (Rosetta@home) has no newlines between tags, add them
        $data=str_replace("</td><td","</td>\n<td",$data);
        $str_array=explode("\n",$data);
        $result=FALSE;
        $counter=0;

        // Finite state machine
        foreach($str_array as $str) {
                $str=trim($str);
                // First column contents result_id and result_name
                if(preg_match('/^<tr[^>]*><td><a href="\\/?result.php\\?resultid=([^"]+)">([^<]+)<\\/a><\\/td>$/',$str,$matches)) {
                        $result_id=$matches[1];
                        $result_name=$matches[2];
                        $counter=1;
                // When first column found, we know wnere information from other columns located
                } else if($counter!=0 && preg_match('/>([^<]+)</',$str,$matches)) {
                        // Sometimes we need whole field in cell (status)
                        preg_match('/^<td[^>]*>(.+)<\\/td>$/',$str,$matches_whole);
                        if($counter==1) $workunit_id=$matches[1];
                        if($counter==2) $host_id=$matches[1];
                        if($counter==3) $sent=$matches[1];
                        if($counter==4) $deadline=$matches[1];
                        if($counter==5) $status=$matches_whole[1];
                        if($counter==6) $elapsed_time=$matches[1];
                        if($counter==7) $cpu_time=$matches[1];
                        if($counter==8) $score=$matches[1];
                        if($counter==9) $app=$matches[1];
                        $counter++;
                        // Tenth field is last
                        if($counter==10) {
                                //echo "'$result_id' '$result_name' '$workunit_id' '$host_id' '$sent' '$deadline' '$status' '$elapsed_time' '$cpu_time' '$score' '$app'\n";

                                // Remove thousand separators
                                $elapsed_time=str_replace(",","",$elapsed_time);
                                $cpu_time=str_replace(",","",$cpu_time);
                                $score=str_replace(",","",$score);

                                // Interpret text values as zeroes
                                if($elapsed_time=="---") $elapsed_time=0;
                                if($cpu_time=="---") $cpu_time=0;
                                if($score=="---") $score=0;
                                if($score=="pending") $score=0;

                                // Escaping data
                                $project_uid_escaped=db_escape($project_uid);
                                $result_id_escaped=db_escape($result_id);
                                $result_name_escaped=db_escape($result_name);
                                $workunit_id_escaped=db_escape($workunit_id);
                                $host_id_escaped=db_escape($host_id);
                                $sent_escaped=db_escape($sent);
                                $deadline_escaped=db_escape($deadline);
                                $status_escaped=db_escape($status);
                                $elapsed_time_escaped=db_escape($elapsed_time);
                                $cpu_time_escaped=db_escape($cpu_time);
                                $score_escaped=db_escape($score);
                                $app_escaped=db_escape($app);

                                // Write to DB
                                db_query("INSERT INTO `tasks` (`project_uid`,`result_name`,`result_id`,`workunit_id`,`host_id`,`sent`,`deadline`,`status`,`elapsed_time`,`cpu_time`,`score`,`app`)
VALUES ('$project_uid_escaped','$result_name_escaped','$result_id_escaped','$workunit_id_escaped','$host_id_escaped','$sent_escaped','$deadline_escaped','$status_escaped','$elapsed_time_escaped','$cpu_time_escaped','$score_escaped','$app_escaped')
ON DUPLICATE KEY UPDATE `result_name`=VALUES(`result_name`),`status`=VALUES(`status`),`elapsed_time`=VALUES(`elapsed_time`),`cpu_time`=VALUES(`cpu_time`),`score`=VALUES(`score`)");

                                // Mark page as resultative
                                $result=TRUE;
                                // Reset counter
                                $counter=0;
                        }
                }
        }
        // Returns TRUE if there was results on the page
        return $result;
}
?>
