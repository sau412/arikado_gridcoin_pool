<?php
// Only for command line
if(!isset($argc)) die();

// Gridcoinresearch send rewards
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/billing.php");
require_once("../lib/boincmgr.php");
require_once("../lib/html.php");

// Send query to gridcoin client
function grc_rpc_send_query($query) {
        global $grc_rpc_host,$grc_rpc_port,$grc_rpc_login,$grc_rpc_password;
        $ch=curl_init("http://$grc_rpc_host:$grc_rpc_port");
//echo "http://$grc_rpc_host:$grc_rpc_port\n";
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_POST,TRUE);
        curl_setopt($ch,CURLOPT_USERPWD,"$grc_rpc_login:$grc_rpc_password");
//echo "$grc_rpc_login:$grc_rpc_password\n";
        curl_setopt($ch, CURLOPT_POSTFIELDS,$query);
        $result=curl_exec($ch);
//var_dump("curl error",curl_error($ch));
        curl_close($ch);

        return $result;
}

// Get balance
function grc_rpc_get_balance() {
        $query='{"id":1,"method":"getbalance","params":[]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        return $data->result;
}

// Unlock wallet
function grc_rpc_unlock_wallet() {
        global $grc_rpc_wallet_passphrase;
        $query='{"id":1,"method":"walletpassphrase","params":["'.$grc_rpc_wallet_passphrase.'",60]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        if($data->error == NULL) return TRUE;
        else return FALSE;
}

// Lock wallet
function grc_rpc_lock_wallet() {
        $query='{"id":1,"method":"walletlock","params":[]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        if($data->error == NULL) return TRUE;
        else return FALSE;
}

// Validate address
function grc_rpc_validate_address($grc_address) {
        if(auth_validate_payout_address($grc_address) == FALSE) return FALSE;
        $query='{"id":1,"method":"validateaddress","params":["'.$grc_address.'"]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
//var_dump($data);
        if($data->error == NULL) {
                if($data->result->isvalid == TRUE) return TRUE;
                else return FALSE;
        } else return FALSE;
}

// Send coins
function grc_rpc_send($grc_address,$amount) {
        $query='{"id":1,"method":"sendtoaddress","params":["'.$grc_address.'",'.$amount.']}';
//var_dump($query);
        $result=grc_rpc_send_query($query);
//var_dump($result);
        $data=json_decode($result);
        if($data->error == NULL) return $data->result;
        else return FALSE;
}

// Get whitelisted project list
function grc_rpc_get_projects() {
        $query='{"id":1,"method":"projects","params":[]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        if($data->error == NULL) return $data->result;
        else return FALSE;
}

// Get magnitude unit
function grc_rpc_get_magnitude_unit() {
        $query='{"id":1,"method":"getmininginfo","params":[]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
//      foreach($data->result as $key => $val) if($key=="") echo "$key => $val\n";
        if($data->error == NULL) return $data->result->{"Magnitude Unit"};
        else return FALSE;
}

// Check if unsent rewards exists
db_connect();

// Get balance
$current_balance=grc_rpc_get_balance();
echo "Current balance: $current_balance\n";
boincmgr_set_variable("hot_wallet_balance",$current_balance);

// Get magnitude unit
$magnitude_unit=grc_rpc_get_magnitude_unit();
boincmgr_set_variable("magnitude_unit",$magnitude_unit);
//$magnitude_unit_escaped=db_escape($magnitude_unit);
//db_query_to_variable("INSERT INTO `boincmgr_variables` (`name`,`value`) VALUES ('magnitude_unit','$magnitude_unit_escaped')");

// Get whitelisted projects
$whitelisted_projects_array=grc_rpc_get_projects();

// Setup cURL
$ch=curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
$whitelisted_names=array();

if(count($whitelisted_projects_array)==0) $no_errors_flag=FALSE;
else $no_errors_flag=TRUE;

foreach($whitelisted_projects_array as $whitelisted_project) {
        $project_name=$whitelisted_project->Project;
        $project_url=$whitelisted_project->URL;

        $project_url_http=str_replace("https:","http:",$project_url);
        $project_url_https=str_replace("http:","https:",$project_url_http);

        $exists_uid=db_query_to_variable("SELECT `uid` FROM `boincmgr_projects` WHERE `project_url` IN ('$project_url_http','$project_url_https')");

        if($exists_uid==FALSE) {
                curl_setopt($ch,CURLOPT_POST,FALSE);
                curl_setopt($ch,CURLOPT_URL,$project_url."get_project_config.php");
                $data = curl_exec ($ch);
                if($data=="") {
                        $no_errors_flag=FALSE;
                        continue;
                }
                $xml=simplexml_load_string($data);
                if($xml==FALSE) {
                        $no_errors_flag=FALSE;
                        continue;
                }
                $name=(string)$xml->name;
                $whitelisted_names[]=$name;
                $name_escaped=db_escape($name);
                $exists_uid=db_query_to_variable("SELECT `uid` FROM `boincmgr_projects` WHERE `name`='$name_escaped'");
                if($exists_uid!=FALSE) {
                        $whitelisted_uids[]=$exists_uid;
                } else {
                        echo "Unknown whitelisted project: name '$project_name' URL '$project_url'\n";
                        auth_log("Unknown whitelisted project: name '$project_name' URL '$project_url'");
                }
        } else {
                $whitelisted_uids[]=$exists_uid;
        }

        if($exists_uid) echo "Project $project_name URL $project_url is whitelisted\n";
        else echo "Project $project_name URL $project_url is not in whitelist\n\n";
}

// Update projects only if no errors when checking whitelisted projects
if($no_errors_flag==TRUE) {
        $whitelisted_uids_str=implode("','",$whitelisted_uids);
        $to_be_enabled=db_query_to_variable("SELECT GROUP_CONCAT(`name` SEPARATOR ', ') FROM `boincmgr_projects` WHERE `uid` IN ('$whitelisted_uids_str') AND `status` IN ('auto','auto disabled')");
        $to_be_disabled=db_query_to_variable("SELECT GROUP_CONCAT(`name` SEPARATOR ', ') FROM `boincmgr_projects` WHERE `uid` NOT IN ('$whitelisted_uids_str') AND `status` IN ('auto','auto enabled')");
        if($to_be_enabled!='') auth_log("Auto change project status: enable projects: $to_be_enabled");
        if($to_be_disabled!='') auth_log("Auto change project status: disable projects: $to_be_disabled");

        db_query("UPDATE `boincmgr_projects` SET `status`='auto enabled' WHERE `uid` IN ('$whitelisted_uids_str') AND `status` IN ('auto','auto enabled','auto disabled')");
        db_query("UPDATE `boincmgr_projects` SET `status`='auto disabled' WHERE `uid` NOT IN ('$whitelisted_uids_str') AND `status` IN ('auto','auto enabled','auto disabled')");
        echo "Auto project statuses updated\n";
} else {
        echo "Errors while updating project statuses, skipping automatic change\n";
}

// Check if exists blocks, mined with pool cpid
$rewarding_array=db_query_to_array("SELECT `number`,`mint`,`interest`,`timestamp` FROM `boincmgr_blocks` WHERE `cpid`='$pool_cpid' AND `rewards_sent`=0 ORDER BY `number` ASC");

if(count($rewarding_array)==0) {
        echo "No reward blocks for now\n";
} else {
        foreach($rewarding_array as $reward_row) {
                $block_number=$reward_row['number'];
                $mint=$reward_row['mint'];
                $interest=$reward_row['interest'];
                $timestamp=$reward_row['timestamp'];
                // If interval is less than 12 hours, then use stats for 12 hours
                $prev_billing_timestamp=db_query_to_variable("SELECT LEAST(MAX(`timestamp`),DATE_SUB('$timestamp',INTERVAL 12 HOUR)) FROM `boincmgr_blocks` WHERE `cpid`='$pool_cpid' AND `rewards_sent`=1");
                if($prev_billing_timestamp=="") $prev_billing_timestamp="SELECT MIN(`timestamp`) FROM `boincmgr_project_host_stats`";

                echo "Billing from $prev_billing_timestamp to $timestamp reward $mint\n";
                $start_date=$prev_billing_timestamp;
                $stop_date=$timestamp;
                $check_rewards=FALSE;
                $antiexp_rac_flag=TRUE;
                $project_uid=FALSE;
                $reward=$mint;
                auth_log("Auto billing from '$start_date' to '$stop_date' reward '$reward'");
                bill_close_period("Gridcoin miner rewards",$start_date,$stop_date,$reward,$check_rewards,$project_uid,$antiexp_rac_flag);
                db_query("UPDATE `boincmgr_blocks` SET `rewards_sent`=1 WHERE `number`='$block_number'");
        }
}

$unsent_count=db_query_to_variable("SELECT count(*) FROM `boincmgr_payouts` WHERE `txid` IS NULL");

if($unsent_count==0) {
        echo "No unsent rewards for now\n";
        die();
}
// Unlock wallet
if(grc_rpc_unlock_wallet() == FALSE) {
        echo "Unlock wallet error\n";
        auth_log("Unlock wallet error");
        die();
}

// Get payout information for GRC
$payout_data_array=db_query_to_array("SELECT `uid`,`payout_address`,`currency`,`amount` FROM `boincmgr_payouts` WHERE `currency` IN ('GRC','GRC2') AND `txid` IS NULL");

foreach($payout_data_array as $payout_data) {
        $uid=$payout_data['uid'];
        $grc_address=$payout_data['payout_address'];
        $amount=$payout_data['amount'];
        $currency=$payout_data['currency'];

        // Only GRC payouts here
        if($currency!="GRC" && $currency!="GRC2") continue;

        // If we have funds for this
        if($amount<$current_balance) {
                echo "Sending $amount to $grc_address\n";
                // Check_address
                if(grc_rpc_validate_address($grc_address)==TRUE) {
                        echo "Address $grc_address is valid\n";
                        // Send coins, get txid
                        $txid=grc_rpc_send($grc_address,$amount);
                        if($txid != FALSE) {
                                // Write to log
                                echo "Sent reward to '$grc_address' reward '$amount'\n";
                                auth_log("Sent reward to '$grc_address' reward '$amount'");
                                $uid_escaped=db_escape($uid);
                                $txid_escaped=db_escape($txid);
                                db_query("UPDATE `boincmgr_payouts` SET `txid`='$txid_escaped' WHERE `uid`='$uid_escaped'");
                                $current_balance-=$amount;
                        } else {
                                // Sending error
                                echo "Sending reward error address '$grc_address' reward '$amount'\n";
                                auth_log("Sending error '$grc_address' reward '$amount'");
                        }
                } else {
                        // Address error
                        echo "Invalid address: $grc_address\n";
                        auth_log("Invalid address '$grc_address' reward '$amount'");
                }
                echo "----\n";
        } else {
                // No funds
                echo "Insufficient funds for sending rewards";
                auth_log("Insufficient funds for sending rewards");
                break;
        }
}

// Lock wallet
if(grc_rpc_lock_wallet() == FALSE) {
        echo "Lock wallet error\n";
        auth_log("Lock wallet error");
        die();
}

?>
