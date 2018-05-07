<?php

// Calculate rewards for hosts and add to db
function bill_close_period($start_date,$stop_date,$total_reward) {
    $start_date_escaped=db_escape($start_date);
    $stop_date_escaped=db_escape($stop_date);
    $total_reward_escaped=db_escape($total_reward);

    db_query("INSERT INTO `boincmgr_billing_periods` (`start_date`,`stop_date`,`reward`) VALUES ('$start_date_escaped','$stop_date_escaped','$total_reward_escaped')");

    $reward_array=array();
    $whitelisted_projects_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects` WHERE `status`='whitelisted'");

    $proportions_array=bill_calculate_projects_proportion($start_date,$stop_date);

    // Calculate rewards for each project
    foreach($whitelisted_projects_array as $project) {
        $project_uid=$project['uid'];
        $project_name=$project['name'];
        $project_uid_escaped=db_escape($project_uid);

        $project_reward=$proportions_array[$project_uid]*$total_reward;

        $current_reward=bill_single_project($project_uid,$start_date,$stop_date,$project_reward);

        $reward_array=reward_array_combine($reward_array,$current_reward);
    }

    // Write rewards to db
    foreach($reward_array as $grc_address => $reward) {
        $grc_address_escaped=db_escape($grc_address);
        $reward_escaped=db_escape($reward);
        db_query("INSERT INTO `boincmgr_payouts` (`grc_address`,`amount`) VALUES ('$grc_address_escaped','$reward_escaped')");
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
                $pool_expavg_sum=db_query_to_variable("SELECT SUM(`expavg_credit`) FROM `boincmgr_project_stats` WHERE `project_uid`='$project_uid_escaped' AND `timestamp`>'$start_date_escaped' AND `timestamp`<='$stop_date_escaped'");
                $team_expavg_sum=db_query_to_variable("SELECT SUM(`team_expavg_credit`) FROM `boincmgr_project_stats` WHERE `project_uid`='$project_uid_escaped' AND `timestamp`>'$start_date_escaped' AND `timestamp`<='$stop_date_escaped'");
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
function bill_single_project($project_uid,$start_date,$stop_date,$project_reward) {
        $reward_array=array();
        $start_date_escaped=db_escape($start_date);
        $stop_date_escaped=db_escape($stop_date);

        $project_total_rac=db_query_to_variable("SELECT SUM(`expavg_credit`) FROM `boincmgr_project_host_stats` WHERE `project_uid`='$project_uid' AND `timestamp`>'$start_date_escaped' AND `timestamp`<='$stop_date_escaped' AND `username`<>''");
        if($project_total_rac==0) return array();
        $projects_hosts_array=db_query_to_array("SELECT DISTINCT `host_cpid`,`username` FROM `boincmgr_project_host_stats` WHERE `project_uid`='$project_uid' AND `timestamp`>'$start_date_escaped' AND `timestamp`<='$stop_date_escaped' AND `username`<>''");

        foreach($projects_hosts_array as $host_data) {
                $host_cpid=$host_data['host_cpid'];
                $username=$host_data['username'];

                $username_escaped=db_escape($username);

                $host_rac=db_query_to_variable("SELECT SUM(`expavg_credit`) FROM `boincmgr_project_host_stats` WHERE `project_uid`='$project_uid' AND `timestamp`>'$start_date_escaped' AND `timestamp`<='$stop_date_escaped' AND `username`='$username_escaped'");
                if($host_rac==0) continue;
                $user_reward=($project_reward/$project_total_rac)*$host_rac;
                if($user_reward==0) continue;
                $grc_address=db_query_to_variable("SELECT `grc_address` FROM `boincmgr_users` WHERE `username`='$username_escaped'");
                if($grc_address!='') $reward_array[$grc_address]=$user_reward;
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
