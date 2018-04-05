<?php
function bill_get_current_period_uid() {
    return db_query_to_variable("SELECT `uid` FROM `boincmgr_billing_periods` WHERE `stop_date` IS NULL");
}

function bill_close_period($total_reward) {
    $reward_array=array();
    $whitelisted_projects_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects` WHERE `status`='whitelisted'");
    $whitelisted_count=count($whitelisted_projects_array);
    $billing_uid=bill_get_current_period_uid();
    foreach($whitelisted_projects_array as $project) {
        $project_uid=$project['uid'];
        $project_name=$project['name'];
        $project_reward=$total_reward/$whitelisted_count;
        $current_reward=bill_single_project($project_uid,$billing_uid,$project_reward);
        echo "Project $project_uid reward:";
        var_dump($current_reward);
        $reward_array=reward_array_combine($reward_array,$current_reward);
    }
    echo "Reward:";
    var_dump($reward_array);
}

function bill_single_project($project_uid,$billing_uid,$project_reward) {
    $reward_array=array();
    
    $project_total_rac=db_query_to_variable("SELECT SUM(`expavg_credit`) FROM `boincmgr_project_host_stats` WHERE `project_uid`='$project_uid' AND `billing_period_uid`='$billing_uid' AND `username`<>''");
    $projects_hosts_array=db_query_to_array("SELECT DISTINCT `host_cpid`,`username` FROM `boincmgr_project_host_stats` WHERE `project_uid`='$project_uid' AND `billing_period_uid`='$billing_uid' AND `username`<>''");
    foreach($projects_hosts_array as $host_data) {
        $host_cpid=$host_data['host_cpid'];
        $username=$host_data['username'];
        
        $username_escaped=db_escape($username);
        
        $host_rac=db_query_to_variable("SELECT SUM(`expavg_credit`) FROM `boincmgr_project_host_stats` WHERE `project_uid`='$project_uid' AND `billing_period_uid`='$billing_uid' AND `username`='$username_escaped'");
        $user_reward=($project_reward/$project_total_rac)*$host_rac;
        
        $grc_address=db_query_to_variable("SELECT `grc_address` FROM `boincmgr_users` WHERE `username`='$username_escaped'");
        if($grc_address!='') $reward_array[$grc_address]=$user_reward;
    }
    return $reward_array;
}

function reward_array_combine($reward_array_1,$reward_array_2) {
    $reward_array=$reward_array_1;
    foreach($reward_array_2 as $grc_address => $reward) {
        if(isset($reward_array[$grc_address])) $reward_array[$grc_address]+=$reward;
        else $reward_array[$grc_address]=$reward;
    }
    return $reward_array;
}

?>
