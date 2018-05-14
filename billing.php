<?php

// Calculate rewards for hosts and add to db
function bill_close_period($start_date,$stop_date,$total_reward,$check_rewards) {
        $start_date_escaped=db_escape($start_date);
        $stop_date_escaped=db_escape($stop_date);
        $total_reward_escaped=db_escape($total_reward);

        if(!$check_rewards) {
                db_query("INSERT INTO `boincmgr_billing_periods` (`start_date`,`stop_date`,`reward`) VALUES ('$start_date_escaped','$stop_date_escaped','$total_reward_escaped')");
                $billing_uid=mysql_insert_id();
        }

        $reward_array=array();
        $whitelisted_projects_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects` WHERE `status`='whitelisted' ORDER BY `name` ASC");

        $proportions_array=bill_calculate_projects_proportion($start_date,$stop_date);

        // Calculate rewards for each project
        foreach($whitelisted_projects_array as $project) {
                $project_uid=$project['uid'];
                $project_name=$project['name'];
                $project_uid_escaped=db_escape($project_uid);

                $project_reward=$proportions_array[$project_uid]*$total_reward;

                $current_reward=bill_single_project($project_uid,$start_date,$stop_date,$project_reward,$check_rewards);

                $reward_array=reward_array_combine($reward_array,$current_reward);
        }

        if($check_rewards) echo "Total results:<br>\n";
        // Write rewards to db
        foreach($reward_array as $grc_address => $reward) {
                $grc_address_escaped=db_escape($grc_address);
                $reward_escaped=db_escape($reward);
                $billing_uid_escaped=db_escape($billing_uid);
                if($check_rewards) echo "$grc_address $reward<br>\n";
                else db_query("INSERT INTO `boincmgr_payouts` (`billing_uid`,`grc_address`,`amount`) VALUES ('$billing_uid_escaped','$grc_address_escaped','$reward_escaped')");
        }
        if($check_rewards) {
                flush();
                die();
        }
}

// Calculate projects proportions
function bill_calculate_projects_proportion($start_date,$stop_date) {
        $start_date_escaped=db_escape($start_date);
        $stop_date_escaped=db_escape($stop_date);
        $pre_result=array();
        $result=array();
        $whitelisted_projects_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects` WHERE `status`='whitelisted'");
        $contrib_sum=0;
        foreach($whitelisted_projects_array as $project) {
                $project_uid=$project['uid'];
                $project_name=$project['name'];
                $project_uid_escaped=db_escape($project_uid);
                $pool_expavg_sum=db_query_to_variable("SELECT AVG(`expavg_credit`) FROM `boincmgr_project_stats` WHERE `project_uid`='$project_uid_escaped' AND `timestamp`>'$start_date_escaped' AND `timestamp`<='$stop_date_escaped'");
                $team_expavg_sum=db_query_to_variable("SELECT AVG(`team_expavg_credit`) FROM `boincmgr_project_stats` WHERE `project_uid`='$project_uid_escaped' AND `timestamp`>'$start_date_escaped' AND `timestamp`<='$stop_date_escaped'");
                if($team_expavg_sum==0 || $pool_expavg_sum==0) continue;
                $contrib_pool_to_team=$pool_expavg_sum/$team_expavg_sum;
                $contrib_sum+=$contrib_pool_to_team;
                $pre_result[]=array(
                        "project_uid"=>$project_uid,
                        "contrib_pool_to_team"=>$contrib_pool_to_team,
                );
        }

        foreach($pre_result as $row) {
                $project_uid=$row['project_uid'];
                $contrib_pool_to_team=$row['contrib_pool_to_team'];
                $fraction=$contrib_pool_to_team/$contrib_sum;
                $result[$project_uid]=$fraction;
        }

        return $result;
}

// Calculate rewards for single project
function bill_single_project($project_uid,$start_date,$stop_date,$project_reward,$check_rewards) {
        if($check_rewards) {
                $project_name=boincmgr_get_project_name($project_uid);
                echo "Calculating project $project_name, reward for this project: $project_reward<br>\n";
        }
        $reward_array=array();
        $start_date_escaped=db_escape($start_date);
        $stop_date_escaped=db_escape($stop_date);

        $project_total_rac=db_query_to_variable("SELECT SUM(`expavg_credit`) FROM `boincmgr_project_host_stats` WHERE `project_uid`='$project_uid' AND `timestamp`>'$start_date_escaped' AND `timestamp`<='$stop_date_escaped'");
        if($check_rewards) echo "Project RAC $project_total_rac<br>\n";
        if($project_total_rac!=0) {
                $user_stats_array=db_query_to_array("SELECT bu.`grc_address`,SUM(`expavg_credit`) AS sum_credit FROM `boincmgr_project_host_stats` AS bphs
LEFT JOIN `boincmgr_host_projects` AS bhp ON bhp.`host_uid`=bphs.`host_uid` AND bhp.`host_id`=bphs.`host_id` AND bhp.`project_uid`=bphs.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bhp.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bphs.`project_uid`='$project_uid' AND `status` IN ('user','admin') AND bphs.`timestamp`>'$start_date_escaped' AND bphs.`timestamp`<='$stop_date_escaped'
GROUP BY bu.`grc_address`");

                foreach($user_stats_array as $user_data) {
                        $host_rac=$user_data['sum_credit'];
                        $grc_address=$user_data['grc_address'];
                        if($host_rac==0) continue;

                        $user_reward=($project_reward/$project_total_rac)*$host_rac;
                        if($user_reward==0) continue;

                        if($check_rewards) echo "Host $host_cpid RAC $host_rac reward $user_reward<br>\n";

                        if($grc_address!='') $reward_array[$grc_address]=$user_reward;
                }
        }
        if($check_rewards) {
                echo "<br>\n";
                flush();
        }
        return $reward_array;
}

// Combine rewards arrays to avoid multiple payouts to single address
function reward_array_combine($reward_array_1,$reward_array_2) {
    $reward_array=$reward_array_1;
    foreach($reward_array_2 as $grc_address => $reward) {
        if(isset($reward_array[$grc_address])) $reward_array[$grc_address]+=$reward;
        else $reward_array[$grc_address]=$reward;
    }
    return $reward_array;
}

?>
