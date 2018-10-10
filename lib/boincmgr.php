<?php
// Internal functions for project

// Attach project
function boincmgr_attach($username,$host_uid,$project_uid) {
//      global $username;
        $project_uid_escaped=db_escape($project_uid);
        $host_uid_escaped=db_escape($host_uid);
        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        // Check if host_uid belongs to this user
        $host_correct=FALSE;
        $project_correct=FALSE;
        $project_name=boincmgr_get_project_name($project_uid);
        $host_name=boincmgr_get_host_name($host_uid);
        if(auth_is_admin($username)==FALSE) {
                $host_correct=db_query_to_variable("SELECT 1 FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
                $project_correct=db_query_to_variable("SELECT 1 FROM `boincmgr_projects` WHERE `uid`='$project_uid_escaped' AND `status` IN ('enabled','auto enabled')");
        }
        if(auth_is_admin($username) || ($host_correct && $project_correct)) {
                auth_log("Attach username '$username' project '$project_name' to host '$host_name'");
                db_query("INSERT INTO `boincmgr_attach_projects` (`project_uid`,`host_uid`,`status`) VALUES ('$project_uid_escaped','$host_uid_escaped','new') ON DUPLICATE KEY UPDATE `status`='new'");
                return TRUE;
        } else {
                auth_log("Attach fail username '$username' project '$project_name' to host '$host_name'");
                return FALSE;
        }
}

// Detach project
function boincmgr_detach($username,$attached_uid) {
        $attached_uid_escaped=db_escape($attached_uid);
        $host_uid=db_query_to_variable("SELECT `host_uid` FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");
        $project_uid=db_query_to_variable("SELECT `project_uid` FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");
        $status=db_query_to_variable("SELECT `status` FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);
        $host_uid_escaped=db_escape($host_uid);
        $project_name=boincmgr_get_project_name($project_uid);

        // Check if host_uid belongs to this user
        if(auth_is_admin($username)==FALSE) $host_uid=db_query_to_variable("SELECT `uid` FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
        if($host_uid || auth_is_admin($username)) {
                $host_name=boincmgr_get_host_name($host_uid);

                auth_log("Detach username '$username' project '$project_name' from host '$host_name'");

                // If status is "new" then we can just delete
                if($status=="new") {
                        db_query("DELETE FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");
                // Else detach first
                } else {
                        db_query("UPDATE `boincmgr_attach_projects` SET `status`='detach' WHERE `uid`='$attached_uid_escaped'");
                }
                return TRUE;
        } else {
                return FALSE;
        }
}

// Settings for project
function boincmgr_set_project_settings($username,$attached_uid,$resource_share,$options_array) {
        //var_dump($username,$attached_uid,$resource_share,$options_array);

        $attached_uid_escaped=db_escape($attached_uid);
        $host_uid=db_query_to_variable("SELECT `host_uid` FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");
        $project_uid=db_query_to_variable("SELECT `project_uid` FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");
        $status=db_query_to_variable("SELECT `status` FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);
        $host_uid_escaped=db_escape($host_uid);
        $project_name=boincmgr_get_project_name($project_uid);

        // Check if host_uid belongs to this user
        if(auth_is_admin($username)==FALSE) $host_uid=db_query_to_variable("SELECT `uid` FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
        if($host_uid || auth_is_admin($username)) {
                $host_name=boincmgr_get_host_name($host_uid);

                $resource_share_escaped=db_escape($resource_share);
                $options_str_array=array();
                $valid_options_array=array("detach","detach_when_done","suspend","dont_request_more_work","abort_not_started","no_cpu","no_cuda","no_ati","no_intel");
                foreach($valid_options_array as $valid_option) {
                        if(in_array($valid_option,$options_array)) $options_str_array[]=$valid_option;
                }
                $options_str=implode(",",$options_str_array);
                $options_str_escaped=db_escape($options_str);

                auth_log("Change project settings username '$username' project '$project_name' host '$host_name' resource share '$resource_share' options '$options_str'");

                db_query("UPDATE `boincmgr_attach_projects` SET `resource_share`='$resource_share_escaped',`options`='$options_str_escaped' WHERE `uid`='$attached_uid_escaped'");

                // If detach or detach_when_done, then update status
                if(in_array("detach",$options_array) || in_array("detach_when_done",$options_array)) {
                        db_query("UPDATE `boincmgr_attach_projects` SET `status`='detach' WHERE `uid`='$attached_uid_escaped'");
                } else {
                        db_query("UPDATE `boincmgr_attach_projects` SET `status`='sent' WHERE `uid`='$attached_uid_escaped'");
                }

                return TRUE;
        } else {
                return FALSE;
        }
}

// Get project name by uid
function boincmgr_get_project_name($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `name` FROM `boincmgr_projects` WHERE `uid` = '$uid_escaped'");
}

// Get host name by uid
function boincmgr_get_host_name($uid) {
        $uid_escaped=db_escape($uid);
        $hostname_encoded=db_query_to_variable("SELECT `domain_name` FROM `boincmgr_hosts` WHERE `uid` = '$uid_escaped'");

        $hostname_decoded=boincmgr_domain_decode($hostname_encoded);

        if(auth_validate_ascii($hostname_decoded)) return $hostname_decoded;
        else return $hostname_encoded;
}

// Get host info (CPU, GPU, domain)
function boincmgr_get_host_info($uid) {
        $uid_escaped=db_escape($uid);
        $query_data_encoded=db_query_to_variable("SELECT `last_query` FROM `boincmgr_hosts` WHERE `uid` = '$uid_escaped'");
        $query_data=boincmgr_domain_decode($query_data_encoded);
        $host_data=xml_parse_user_request($query_data);
//var_dump($host_data);
        $result="";
        // Hostname
        if(isset($host_data["domain_name"]) && $host_data["domain_name"]!="") {
                $result.="Domain name: ".$host_data["domain_name"]."\n";
        } else {
                $result.="No host name\n";
        }
        // OS data
        if(isset($host_data["os_name"]) && $host_data["os_name"]!="") {
                $os_version=$host_data["os_version"];
                if(strlen($os_version)>50) $os_version=substr($os_version,0,50)."...";
                $result.="OS name: ".$host_data["os_name"]."\nOS version: $os_version\n";
        } else {
                $result.="No OS info\n";
        }
        // Product name
        if(isset($host_data["product_name"]) && $host_data["product_name"]!="") {
                $result.="Product name: ".$host_data["product_name"]."\n";
        }
        // CPU data
        if(isset($host_data["p_model"]) && $host_data["p_model"]!="") {
                $result.="CPU: ".$host_data["p_ncpus"]." x ".$host_data["p_model"]."\n";
        } else {
                $result.="No CPU info\n";
        }
        // GPU data
        if(isset($host_data["gpus"])) {
                foreach($host_data["gpus"] as $gpu_info) {
                        $gpu_count=$gpu_info['count'];
                        $gpu_model=$gpu_info['name'];
                        $result.="GPU: $gpu_count x $gpu_model\n";
                }
        } else {
//              $result.="No GPU\n";
        }
        return $result;
}

// Get host short info (OS, number CPU, number GPU)
function boincmgr_get_host_short_info($uid) {
        $uid_escaped=db_escape($uid);
        $query_data_encoded=db_query_to_variable("SELECT `last_query` FROM `boincmgr_hosts` WHERE `uid` = '$uid_escaped'");
        $query_data=boincmgr_domain_decode($query_data_encoded);
        $host_data=xml_parse_user_request($query_data);
//var_dump($host_data);
        $result="";
        // CPU data
        if(isset($host_data["p_model"]) && $host_data["p_model"]!="") {
                $result.=$host_data["p_ncpus"]." CPU";
        } else {
                $result.="No CPU info";
        }
        // GPU data
        if(isset($host_data["gpus"])) {
                $gpus_count=0;
                foreach($host_data["gpus"] as $gpu_info) {
                        $gpu_count=$gpu_info['count'];
                        $gpu_model=$gpu_info['name'];
                        $gpus_count+=$gpu_count;
                }
                if($gpus_count==0) $result.=", no GPU";
                else $result.=", $gpus_count GPU";
        } else {
                $result.=", no GPU";
        }
        // OS data
        if(isset($host_data["os_name"]) && $host_data["os_name"]!="") {
                $os_name=$host_data["os_name"];
                $os_name=str_replace("Microsoft ","",$os_name);
                $os_name=str_replace("Darwin","Mac OS",$os_name);
                $result.=", $os_name";
        } else {
                $result.=", unknown OS";
        }
        return $result;
}

// Set TX ID
function boincmgr_set_txid($payout_address,$txid) {
        $payout_address_escaped=db_escape($payout_address);
        $txid_escaped=db_escape($txid);
        db_query("UPDATE `boincmgr_payouts` SET txid='$txid_escaped' WHERE `txid` IS NULL and `payout_address`='$payout_address_escaped'");
}

// Get user name by uid
function boincmgr_get_user_name($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `username` FROM `boincmgr_users` WHERE `uid` = '$uid_escaped'");
}

// Get user email by uid
function boincmgr_get_user_email($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `email` FROM `boincmgr_users` WHERE `uid` = '$uid_escaped'");
}

// Get user uid by username
function boincmgr_get_username_uid($username) {
        $username_escaped=db_escape($username);
        return db_query_to_variable("SELECT `uid` FROM `boincmgr_users` WHERE LOWER(`username`) = LOWER('$username_escaped')");
}

// Get project uid by project name
function boincmgr_get_project_uid($project_name) {
        $project_name_escaped=db_escape($project_name);
        return db_query_to_variable("SELECT `uid` FROM `boincmgr_projects` WHERE `name` = '$project_name_escaped'");
}

// Check project weak key
function boincmgr_check_weak_key($project_uid,$weak_key) {
        $project_uid_escaped=db_escape($project_uid);
        $weak_key_escaped=db_escape($weak_key);
        $weak_key_exists=db_query_to_variable("SELECT 1 FROM `boincmgr_projects` WHERE `uid` = '$project_uid_escaped' AND `weak_auth` = '$weak_key_escaped'");
        if($weak_key_exists) return TRUE;
        else return FALSE;
}

// Get host uid by user name and host_cpid
function boincmgr_get_host_uid($username_uid,$host_cpid) {
        $username_uid_escaped=db_escape($username_uid);
        $host_cpid_escaped=db_escape($host_cpid);
        return db_query_to_variable("SELECT `uid` FROM `boincmgr_hosts` WHERE `username_uid` = '$username_uid_escaped' AND `internal_host_cpid`='$host_cpid_escaped'");
}

// Encode domain to store in DB
function boincmgr_domain_encode($domain) {
        return base64_encode($domain);
}

// Decode domain form DB
function boincmgr_domain_decode($domain) {
        return base64_decode($domain);
}

// Delete host
function boincmgr_delete_host($username,$host_uid) {
        $host_uid_escaped=db_escape($host_uid);
        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        // Check if host_uid belongs to this user
        if(auth_is_admin($username)==FALSE) $host_uid=db_query_to_variable("SELECT `uid` FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
        if($host_uid || auth_is_admin($username)) {
                $host_name=boincmgr_get_host_name($host_uid);

                auth_log("Delete host '$host_name' by username '$username'");

                // Delete any attach project statuses
                db_query("DELETE FROM `boincmgr_attach_projects` WHERE `host_uid`='$host_uid_escaped'");

                // Delete attached projects
                db_query("DELETE FROM `boincmgr_host_projects` WHERE `host_uid`='$host_uid_escaped'");

                // Delete host stats
                db_query("DELETE FROM `boincmgr_project_hosts_last` WHERE `host_uid`='$host_uid_escaped'");
                db_query("DELETE FROM `boincmgr_project_host_stats` WHERE `host_uid`='$host_uid_escaped'");

                // Delete host
                db_query("DELETE FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped'");
        }
}

// Set pool info
function boincmgr_set_pool_info($pool_info) {
        boincmgr_set_variable("pool_info",$pool_info);
}

// Get pool info
function boincmgr_get_pool_info() {
        return boincmgr_get_variable("pool_info");
}

// Get variable
function boincmgr_get_variable($name) {
        $name_escaped=db_escape($name);
        return db_query_to_variable("SELECT `value` FROM `boincmgr_variables` WHERE `name`='$name_escaped'");
}

// Set variable
function boincmgr_set_variable($name,$value) {
        $name_escaped=db_escape($name);
        $value_escaped=db_escape($value);
        return db_query("INSERT INTO `boincmgr_variables` (`name`,`value`) VALUES ('$name_escaped','$value_escaped') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
}

// Clear project log
function boincmgr_project_last_query_clear($project_uid) {
        $project_uid_escaped=db_escape($project_uid);
        db_query("UPDATE `boincmgr_projects` SET `last_query`='' WHERE `uid`='$project_uid_escaped'");
}

// Get project log
function boincmgr_project_last_query_get($project_uid) {
        $project_uid_escaped=db_escape($project_uid);
        $result=db_query_to_variable("SELECT `last_query` FROM `boincmgr_projects` WHERE `uid`='$project_uid_escaped'");
        return base64_decode($result);
}

// Append text to project log
function boincmgr_project_last_query_append($project_uid,$text) {
        $project_uid_escaped=db_escape($project_uid);
        $current_text=boincmgr_project_last_query_get($project_uid);
        $new_text=$current_text.$text;
        $new_text_encoded=base64_encode($new_text);
        $new_text_encoded_escaped=db_escape($new_text_encoded);
        db_query("UPDATE `boincmgr_projects` SET `last_query`='$new_text_encoded_escaped' WHERE `uid`='$project_uid_escaped'");
}

// Cache function
function boincmgr_cache_function($function_name,$parameters,$force_update=0) {
        $call_str="$function_name(".implode(",",$parameters).")";
        $hash=hash("sha256",$call_str);
        $result=db_query_to_variable("SELECT `content` FROM `boincmgr_cache` WHERE `hash`='$hash' AND NOW()<`valid_until`");
        if($result=="" || $force_update) {
                $result=call_user_func_array($function_name,$parameters);
                $result_escaped=db_escape($result);
                db_query("INSERT INTO `boincmgr_cache` (`hash`,`content`,`valid_until`) VALUES ('$hash','$result_escaped',DATE_ADD(NOW(),INTERVAL 1 HOUR))
ON DUPLICATE KEY UPDATE `content`=VALUES(`content`),`valid_until`=DATE_ADD(NOW(),INTERVAL 1 HOUR)");
        }
        return $result;
}

// Get payout rate
function boincmgr_get_payout_rate($currency) {
        switch($currency) {
                case "GRC2":
                case "GRC":
                        return 1;
                case "DOGE":
                        $btc_grc_rate=boincmgr_get_variable("BTC_GRC");
                        $btc_doge_rate=boincmgr_get_variable("BTC_DOGE");
                        if($btc_doge_rate!=0) $doge_grc_rate=$btc_grc_rate/$btc_doge_rate;
                        else $doge_grc_rate=0;
                        return $doge_grc_rate;
                case "GBYTE":
                        $btc_grc_rate=boincmgr_get_variable("BTC_GRC");
                        $btc_gbyte_rate=boincmgr_get_variable("BTC_GBYTE");
                        if($btc_gbyte_rate!=0) $gbyte_grc_rate=$btc_grc_rate/$btc_gbyte_rate;
                        else $gbyte_grc_rate=0;
                        return $gbyte_grc_rate;
                case "LTC":
                        $btc_grc_rate=boincmgr_get_variable("BTC_GRC");
                        $btc_ltc_rate=boincmgr_get_variable("BTC_LTC");
                        if($btc_ltc_rate!=0) $ltc_grc_rate=$btc_grc_rate/$btc_ltc_rate;
                        else $ltc_grc_rate=0;
                        return $ltc_grc_rate;
                case "ETH":
                        $btc_grc_rate=boincmgr_get_variable("BTC_GRC");
                        $btc_eth_rate=boincmgr_get_variable("BTC_ETH");
                        if($btc_eth_rate!=0) $eth_grc_rate=$btc_grc_rate/$btc_eth_rate;
                        else $eth_grc_rate=0;
                        return $eth_grc_rate;
                case "WMZ":
                        $btc_grc_rate=boincmgr_get_variable("BTC_GRC");
                        $usdt_btc_rate=boincmgr_get_variable("USDT_BTC");
                        $wmz_grc_rate=$btc_grc_rate*$usdt_btc_rate;
                        return $wmz_grc_rate;
                case "XMR":
                        $btc_grc_rate=boincmgr_get_variable("BTC_GRC");
                        $btc_xmr_rate=boincmgr_get_variable("BTC_XMR");
                        if($btc_xmr_rate!=0) $xmr_grc_rate=$btc_grc_rate/$btc_xmr_rate;
                        else $xmr_grc_rate=0;
                        return $xmr_grc_rate;
                case "BTC":
                        $btc_grc_rate=boincmgr_get_variable("BTC_GRC");
                        return $btc_grc_rate;
                default:
                        return 0;
        }
}

// Get payout limit
function boincmgr_get_payout_limit($currency) {
        if($currency=="GRC2") $currency="GRC";
        $currency_escaped=db_escape($currency);
        return db_query_to_variable("SELECT `payout_limit` FROM `boincmgr_currency` WHERE `name`='$currency_escaped'");
}

// Get payout limit
function boincmgr_get_tx_fee_estimation($currency) {
        if($currency=="GRC2") $currency="GRC";
        $currency_escaped=db_escape($currency);
        return db_query_to_variable("SELECT `tx_fee` FROM `boincmgr_currency` WHERE `name`='$currency_escaped'");
}

// Get payout fee
function boincmgr_get_service_fee($currency) {
        if($currency=="GRC2") $currency="GRC";
        $currency_escaped=db_escape($currency);
        return db_query_to_variable("SELECT `project_fee` FROM `boincmgr_currency` WHERE `name`='$currency_escaped'");
}

// Add message
function boincmgr_message_send($username_uid,$reply_to,$message) {
        if($username_uid!='') $username_uid_escaped="'".db_escape($username_uid)."'";
        else $username_uid_escaped="NULL";
        $reply_to_escaped=db_escape($reply_to);
        $message_escaped=db_escape($message);
        db_query("INSERT INTO `boincmgr_messages` (`username_uid`,`reply_to`,`is_read`,`message`,`timestamp`) VALUES ($username_uid_escaped,'$reply_to_escaped','0','$message_escaped',NOW())");
}

// Strip non-ascii chars
function boincmgr_leave_only_ascii($string) {
        $result_string="";
        for($i=0;$i<strlen($string);$i++) {
                if(ord($string[$i])>=32 && ord($string[$i])<=127)
                        $result_string.=$string[$i];
        }
        return $result_string;
}

// Get magnitude per project
function boincmgr_get_mag_per_project() {
        $magnitude_total=115000;
        $whiltelisted_count=db_query_to_variable("SELECT count(*) FROM `boincmgr_projects` WHERE `status` IN ('enabled','auto enabled','stats only')");
        if($whiltelisted_count!=0) $mag_per_project=$magnitude_total/$whiltelisted_count;
        else $mag_per_project=0;
        return $mag_per_project;
}

// Get magnitude unit
function boincmgr_get_magnitude_unit() {
        return boincmgr_get_variable("magnitude_unit");
}

// Get project host relative contribution
function boincmgr_get_relative_contribution_project_host($project_uid,$host_uid) {
        $project_uid_escaped=db_escape($project_uid);
        $host_uid_escaped=db_escape($host_uid);
        $relative_contribution=db_query_to_variable("SELECT SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) FROM `boincmgr_project_hosts_last` AS bphl
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.uid=bphl.project_uid
WHERE bphl.`project_uid`='$project_uid_escaped' AND bphl.`host_uid`='$host_uid_escaped' AND bp.`status` IN ('enabled','auto enabled','stats only')");
        if($relative_contribution=="") $relative_contribution=0;
        return $relative_contribution;
}

// Get project user relative contribution
function boincmgr_get_relative_contribution_project_user($project_uid,$user_uid) {
        $project_uid_escaped=db_escape($project_uid);
        $user_uid_escaped=db_escape($user_uid);
        $relative_contribution=db_query_to_variable("SELECT SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) FROM `boincmgr_project_hosts_last` AS bphl
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.uid=bphl.project_uid
LEFT OUTER JOIN `boincmgr_hosts` AS bh ON bh.uid=bphl.host_uid
WHERE bphl.`project_uid`='$project_uid_escaped' AND bh.`username_uid`='$user_uid_escaped' AND bp.`status` IN ('enabled','auto enabled','stats only')");
        if($relative_contribution=="") $relative_contribution=0;
        return $relative_contribution;
}

// Get project relative contribution
function boincmgr_get_relative_contribution_project($project_uid) {
        $project_uid_escaped=db_escape($project_uid);
        $relative_contribution=db_query_to_variable("SELECT SUM(bp.`expavg_credit`/bp.`team_expavg_credit`) FROM `boincmgr_projects` AS bp
WHERE bp.`uid`='$project_uid_escaped' AND bp.`status` IN ('enabled','auto enabled','stats only')");
        if($relative_contribution=="") $relative_contribution=0;
        return $relative_contribution;
}

// Get host relative contribution
function boincmgr_get_relative_contribution_host($host_uid) {
        $host_uid_escaped=db_escape($host_uid);
        $relative_contribution=db_query_to_variable("SELECT SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) FROM `boincmgr_project_hosts_last` AS bphl
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.uid=bphl.project_uid
WHERE bphl.`host_uid`='$host_uid_escaped' AND bp.`status` IN ('enabled','auto enabled','stats only')");
        if($relative_contribution=="") $relative_contribution=0;
        return $relative_contribution;
}

// Get user relative contribution
function boincmgr_get_relative_contribution_user($user_uid) {
        $user_uid_escaped=db_escape($user_uid);
        $relative_contribution=db_query_to_variable("SELECT SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) FROM `boincmgr_project_hosts_last` AS bphl
LEFT OUTER JOIN `boincmgr_projects` AS bp ON bp.uid=bphl.project_uid
LEFT OUTER JOIN `boincmgr_hosts` AS bh ON bh.uid=bphl.host_uid
WHERE bh.`username_uid`='$user_uid_escaped' AND bp.`status` IN ('enabled','auto enabled','stats only')");
        if($relative_contribution=="") $relative_contribution=0;
        return $relative_contribution;
}

// Claim faucet
function boincmgr_claim_faucet($username_uid) {
        global $faucet_plain_amount;
        $amount=$faucet_plain_amount;

        $username_uid_escaped=db_escape($username_uid);
        $amount_escaped=db_escape($amount);

        $magnitude_unit=boincmgr_get_magnitude_unit();
        $mag_per_project=boincmgr_get_mag_per_project();

        $user_magnitude=db_query_to_variable("SELECT SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`)) AS magnitude
FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bu.`uid`='$username_uid_escaped' AND bp.`status` IN ('enabled','auto enabled','stats only')
GROUP BY bu.`username`
HAVING SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`))>=0.01
ORDER BY SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) DESC
LIMIT 100
");

        $claim_today = db_query_to_variable("SELECT 1 FROM `boincmgr_faucet` WHERE DATE_ADD(`date`,INTERVAL 1 DAY)>NOW() AND `user_uid`='$username_uid_escaped'");
        $currency = db_query_to_variable("SELECT `currency` FROM `boincmgr_users` WHERE `uid`='$username_uid_escaped'");

        if($user_magnitude>1 && $claim_today!=1 && ($currency=='GRC' || $currency=='GRC2')) {
                db_query("INSERT INTO `boincmgr_faucet` (`user_uid`,`grc_amount`,`date`) VALUES ('$username_uid_escaped','$amount_escaped',NOW())");

                $grc_address=db_query_to_variable("SELECT `payout_address` FROM `boincmgr_users` WHERE `uid`='$username_uid_escaped'");
                $grc_address_escaped=db_escape($grc_address);

                db_query("INSERT INTO `boincmgr_faucet_payouts` (`grc_address`,`amount`) VALUES ('$grc_address_escaped','$amount_escaped')");
        }
}

// For php 5 only variant for random_bytes is openssl_random_pseudo_bytes from openssl lib
if(!function_exists("random_bytes")) {
        function random_bytes($n) {
                return openssl_random_pseudo_bytes($n);
        }
}

?>
