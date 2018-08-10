<?php

// Calculate rewards for hosts and add to db
function bill_close_period($comment,$start_date,$stop_date,$total_reward,$check_rewards,$bill_single_project_uid=0,$anxtiexp_rac_flag=1) {
        $start_date_escaped=db_escape($start_date);
        $stop_date_escaped=db_escape($stop_date);
        $total_reward_escaped=db_escape($total_reward);
        $comment_escaped=db_escape($comment);

        if(!$check_rewards) {
                db_query("INSERT INTO `boincmgr_billing_periods` (`comment`,`start_date`,`stop_date`,`reward`) VALUES ('$comment_escaped','$start_date_escaped','$stop_date_escaped','$total_reward_escaped')");
                $billing_uid=mysql_insert_id();
        }

        $reward_array=array();
        $enabled_projects_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects` WHERE `status` IN ('enabled','auto enabled','stats only') ORDER BY `name` ASC");

        if($bill_single_project_uid==0) $proportions_array=bill_calculate_projects_proportion($start_date,$stop_date);
        else $proportions_array=array($bill_single_project_uid=>1);

        // Calculate rewards for each project
        foreach($enabled_projects_array as $project) {
                $project_uid=$project['uid'];
                $project_name=$project['name'];

                // If project specified, bill only that project
                if($bill_single_project_uid!=0 && $bill_single_project_uid!=$project_uid) continue;

                $project_uid_escaped=db_escape($project_uid);

                if(isset($proportions_array[$project_uid])) $project_reward=$proportions_array[$project_uid]*$total_reward;
                else $project_reward=0;

                $current_reward=bill_single_project($project_uid,$start_date,$stop_date,$project_reward,$check_rewards,$anxtiexp_rac_flag);

                $reward_array=reward_array_combine($reward_array,$current_reward);
        }

        if($check_rewards) echo "Total results:<br>\n";
        // Write rewards to db
        foreach($reward_array as $payout_address => $grc_reward) {
                $payout_address_escaped=db_escape($payout_address);
                $payout_currency=db_query_to_variable("SELECT `currency` FROM `boincmgr_users` WHERE `payout_address`='$payout_address_escaped'");
                $rate=boincmgr_get_payout_rate($payout_currency);
                $grc_reward_escaped=db_escape($grc_reward);
                $amount=$grc_reward*$rate;

                $billing_uid_escaped=db_escape($billing_uid);
                $payout_currency_escaped=db_escape($payout_currency);
                $rate_escaped=db_escape($rate);
                $amount_escaped=db_escape($amount);

                if($check_rewards) echo "Address $payout_address GRC reward $grc_reward currency $payout_currency rate $rate result amount $amount<br>\n";
                else db_query("INSERT INTO `boincmgr_payouts` (`billing_uid`,`payout_address`,`grc_amount`,`currency`,`rate`,`amount`)
VALUES ('$billing_uid_escaped','$payout_address_escaped','$grc_reward_escaped','$payout_currency_escaped','$rate_escaped','$amount_escaped')");
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
        $enabled_projects_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects` WHERE `status` IN ('enabled','auto enabled','stats only')");
        $contrib_sum=0;
        foreach($enabled_projects_array as $project) {
                $project_uid=$project['uid'];
                $project_name=$project['name'];
                $project_uid_escaped=db_escape($project_uid);
                // Some users are donators, their reward distributed between others
                $pool_expavg_sum=db_query_to_variable("SELECT AVG(bphs.`expavg_credit`) FROM `boincmgr_project_host_stats` AS bphs
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphs.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bphs.`project_uid`='$project_uid_escaped' AND bu.`status` IN ('user','admin') AND bphs.`timestamp`>'$start_date_escaped' AND bphs.`timestamp`<='$stop_date_escaped'");
                //$pool_expavg_sum=db_query_to_variable("SELECT AVG(`expavg_credit`) FROM `boincmgr_project_stats` WHERE `project_uid`='$project_uid_escaped' AND `timestamp`>'$start_date_escaped' AND `timestamp`<='$stop_date_escaped'");
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
function bill_single_project($project_uid,$start_date,$stop_date,$project_reward,$check_rewards,$anxtiexp_rac_flag) {
        if($check_rewards) {
                $project_name=boincmgr_get_project_name($project_uid);
                echo "Calculating project $project_name, reward for this project: $project_reward<br>\n";
        }
        $reward_array=array();
        $start_date_escaped=db_escape($start_date);
        $stop_date_escaped=db_escape($stop_date);
        $project_uid_escaped=db_escape($project_uid);

        $user_reward_data=array();
        $user_uids_array=db_query_to_array("SELECT `uid` FROM `boincmgr_users`");
        foreach($user_uids_array as $user_uid_data) {
                $user_uid=$user_uid_data['uid'];
                $user_reward_data[]=bill_single_user($project_uid,$user_uid,$start_date,$stop_date,$project_reward,$check_rewards,$anxtiexp_rac_flag);
        }

        foreach($user_reward_data as $single_user_reward_data) {
                $user_uid=$single_user_reward_data['user_uid'];
                $grc_reward=$single_user_reward_data['grc_reward'];
                $user_uid_escaped=db_escape($user_uid);
                $payout_address=db_query_to_variable("SELECT `payout_address` FROM `boincmgr_users` WHERE `uid`='$user_uid_escaped'");
                if($payout_address=="" || $grc_reward==0) continue;
                if(!isset($reward_array[$payout_address]))$reward_array[$payout_address]=0;
                $reward_array[$payout_address]+=$grc_reward;
        }
        return $reward_array;

/*
        $project_total_rac=db_query_to_variable("SELECT SUM(bphs.`expavg_credit`) FROM `boincmgr_project_host_stats` AS bphs
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphs.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bphs.`project_uid`='$project_uid_escaped' AND bu.`status` IN ('user','admin') AND bphs.`timestamp`>'$start_date_escaped' AND bphs.`timestamp`<='$stop_date_escaped'");
        if($check_rewards) echo "Project RAC $project_total_rac<br>\n";
        if($project_total_rac!=0) {
                $user_stats_array=db_query_to_array("SELECT bu.`payout_address`,SUM(bphs.`expavg_credit`) AS sum_credit FROM `boincmgr_project_host_stats` AS bphs
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphs.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bphs.`project_uid`='$project_uid_escaped' AND bu.`status` IN ('user','admin') AND bphs.`timestamp`>'$start_date_escaped' AND bphs.`timestamp`<='$stop_date_escaped'
GROUP BY bu.`payout_address`");

                foreach($user_stats_array as $user_data) {
                        $host_rac=$user_data['sum_credit'];
                        $payout_address=$user_data['payout_address'];
                        if($host_rac==0) continue;

                        $user_reward=($project_reward/$project_total_rac)*$host_rac;
                        if($user_reward==0) continue;

                        $payout_address_html=html_escape($payout_address);
                        if($check_rewards) echo "Payout address '$payout_address' RAC $host_rac reward $user_reward<br>\n";

                        if($payout_address!='') $reward_array[$payout_address]=$user_reward;
                }
        }
        if($check_rewards) {
                echo "<br>\n";
                flush();
        }
        return $reward_array;
*/
}

// Bill single user
function bill_single_user($project_uid,$user_uid,$start_date,$stop_date,$project_reward,$check_rewards,$anxtiexp_rac_flag) {
        $project_uid_escaped=db_escape($project_uid);
        $user_uid_escaped=db_escape($user_uid);
        $start_date_escaped=db_escape($start_date);
        $stop_date_escaped=db_escape($stop_date);

        $currency=db_query_to_variable("SELECT `currency` FROM `boincmgr_users` WHERE `uid`='$user_uid_escaped'");

        if($currency!="GRC" && $anxtiexp_rac_flag==TRUE) {
                $project_total_rac=db_query_to_variable("SELECT SUM(bphs.`expavg_credit`) FROM `boincmgr_project_host_stats` AS bphs
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphs.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bphs.`project_uid`='$project_uid_escaped' AND bu.`status` IN ('user','admin') AND bphs.`timestamp`>'$start_date_escaped' AND bphs.`timestamp`<='$stop_date_escaped'");
                if($project_total_rac=="") $project_total_rac=0;

                $user_total_rac_data=db_query_to_array("SELECT SUM(bphs.`expavg_credit`) AS expavg_credit,MIN(UNIX_TIMESTAMP(bphs.`timestamp`)) AS `timestamp` FROM `boincmgr_project_host_stats` AS bphs
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphs.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bphs.`project_uid`='$project_uid_escaped' AND bu.`uid`='$user_uid_escaped' AND bu.`status` IN ('user','admin') AND bphs.`timestamp`>'$start_date_escaped' AND bphs.`timestamp`<='$stop_date_escaped'
GROUP BY HOUR(bphs.`timestamp`),DAY(bphs.`timestamp`),MONTH(bphs.`timestamp`) ORDER BY MIN(UNIX_TIMESTAMP(bphs.`timestamp`)) ASC");

                $rac_coef=0.85;

                $antiexp_rac=0;
                foreach($user_total_rac_data as $data) {
                        $expavg_credit=$data['expavg_credit'];
                        $timestamp=$data['timestamp'];
                        if($user_uid==25) echo "$expavg_credit;$timestamp;<br>\n";
                        if(isset($prev_credit) && isset($prev_timestamp)) {
                                $time_interval=$timestamp-$prev_timestamp;
                                $exp_value=pow($rac_coef,$time_interval/86400);
                                $expavg_delta=$expavg_credit-$prev_credit;
                                $antiexp_rac+=(($expavg_credit-$prev_credit*$exp_value)/(1-$exp_value));
                        }
                        $prev_credit=$expavg_credit;
                        $prev_timestamp=$timestamp;
                }
                if($project_total_rac!=0) $grc_reward=$project_reward*$antiexp_rac/$project_total_rac;
                else $grc_reward=0;

                if($grc_reward<0) $grc_reward=0;

                if($check_rewards) {
                        $username=boincmgr_get_user_name($user_uid);
                        echo "User '$username' project antiexpRAC '$antiexp_rac' reward $grc_reward<br>\n";
                }

                return array(
                        "user_uid"=>$user_uid,
                        "project_uid"=>$project_uid,
                        "grc_reward"=>$grc_reward,
                );
        } else {
                $project_total_rac=db_query_to_variable("SELECT SUM(bphs.`expavg_credit`) FROM `boincmgr_project_host_stats` AS bphs
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphs.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bphs.`project_uid`='$project_uid_escaped' AND bu.`status` IN ('user','admin') AND bphs.`timestamp`>'$start_date_escaped' AND bphs.`timestamp`<='$stop_date_escaped'");
                if($project_total_rac=="") $project_total_rac=0;

                $user_total_rac=db_query_to_variable("SELECT SUM(bphs.`expavg_credit`) AS sum_credit FROM `boincmgr_project_host_stats` AS bphs
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphs.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bphs.`project_uid`='$project_uid_escaped' AND bu.`uid`='$user_uid_escaped' AND bu.`status` IN ('user','admin') AND bphs.`timestamp`>'$start_date_escaped' AND bphs.`timestamp`<='$stop_date_escaped'");
                if($user_total_rac=="") $user_total_rac=0;

                if($project_total_rac!=0) $grc_reward=$project_reward*$user_total_rac/$project_total_rac;
                else $grc_reward=0;

                if($check_rewards) {
                        $username=boincmgr_get_user_name($user_uid);
                        echo "User '$username' project RAC '$user_total_rac' reward $grc_reward<br>\n";
                }
                return array(
                        "user_uid"=>$user_uid,
                        "project_uid"=>$project_uid,
                        "grc_reward"=>$grc_reward,
                );
        }
}

// Combine rewards arrays to avoid multiple payouts to single address
function reward_array_combine($reward_array_1,$reward_array_2) {
        $reward_array=$reward_array_1;
        foreach($reward_array_2 as $payout_address => $reward) {
                if(isset($reward_array[$payout_address])) $reward_array[$payout_address]+=$reward;
                else $reward_array[$payout_address]=$reward;
        }
        return $reward_array;
}

?>
