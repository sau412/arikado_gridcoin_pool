<?php
// Get hosts data from BOINC project

if(!isset($argc)) die();

require_once("settings.php");
require_once("db.php");
require_once("billing.php");
require_once("auth.php");

db_connect();

// Get whitelisted and greylisted projects
$project_data_array=db_query_to_array("SELECT * FROM `boincmgr_projects` WHERE `status` IN ('whitelisted','greylisted')");

// Setup cURL
$ch=curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);

// For each project
$project_count=count($project_data_array);
$full_sync_count=0;
foreach($project_data_array as $project_data)
        {
        $project_uid=$project_data['uid'];
        $project_name=$project_data['name'];
        $project_url=$project_data['project_url'];

        $project_uid_escaped=db_escape($project_uid);
echo "Updating data for $project_name\n";

        // Get project config (name, master url,platforms)
        curl_setopt($ch,CURLOPT_POST,FALSE);
        curl_setopt($ch,CURLOPT_URL,$project_url."get_project_config.php");
        $data = curl_exec ($ch);
if($data=="") { echo "No data from project\n"; continue; }
//      var_dump($data);
        $xml=simplexml_load_string($data);

        if($xml==FALSE)
                {
                echo "Error: $project_url\n\n";
                continue;
                }

        $name=(string)$xml->name;
        $rpc_url=(string)$xml->web_rpc_url_base;
        $master_url=(string)$xml->master_url;
        if($rpc_url=="") $rpc_url=$master_url;

echo "web_rpc_url_base: $rpc_url\n";
        $platform_names="";
        foreach($xml->platforms->platform as $platform)
                {
                //var_dump($platform);
                $pl_name=$platform->platform_name;
                $pl_fr_name=$platform->user_friendly_name;
                $plan_class=$platform->plan_class;
                if($plan_class)
                        $platform_names.="$pl_name -- $pl_fr_name -- $plan_class\n";
                else
                        $platform_names.="$pl_name -- $pl_fr_name -- no plan class\n";
                }

        //echo "Project $name ($master_url)\n";
        //echo "$platform_names\n";
        $name_escaped=db_escape($name);
        $master_url_escaped=db_escape($master_url);

        // Login to project
        curl_setopt($ch,CURLOPT_URL,$rpc_url."lookup_account.php?email_addr=$boinc_account&passwd_hash=$boinc_passwd_hash");
        $data=curl_exec($ch);

        $xml=simplexml_load_string($data);
        if($xml==FALSE) { echo "Login to project error\n"; echo $rpc_url."/lookup_account.php?email_addr=$boinc_account&passwd_hash=$boinc_passwd_hash\n"; continue; }
        $auth=$xml->authenticator;

        // Get weak auth key
        curl_setopt($ch,CURLOPT_URL,$rpc_url."am_get_info.php?account_key=$auth");
        $data=curl_exec($ch);

        $xml=simplexml_load_string($data);
        if($xml==FALSE) { echo "Get weak auth key error\n"; continue; }
        $weak_auth=$xml->weak_auth;
        $weak_auth_escaped=db_escape($weak_auth);

        // World Community Grid returns wrong weak key
        //db_query("UPDATE `boincmgr_projects` SET `name`='$name_escaped',`project_url`='$master_url_escaped',`weak_auth`='$weak_auth_escaped' WHERE `uid`='$project_uid'");
        db_query("UPDATE `boincmgr_projects` SET `name`='$name_escaped',`project_url`='$master_url_escaped' WHERE `uid`='$project_uid_escaped'");

        // Get Gridcoin team stats (for billing purposes)
        curl_setopt($ch,CURLOPT_URL,$rpc_url."team_lookup.php?team_name=Gridcoin&format=xml");
        $data=curl_exec($ch);
        $xml=simplexml_load_string($data);
        if($xml==FALSE) { echo "Get gridcoin team stats error\n"; continue; }

        $gridcoin_team_stats_found=FALSE;
        foreach($xml->team as $team_info) {
                if($team_info->name=="Gridcoin") {
                        $team_expavg_credit=(string)$team_info->expavg_credit;
                        $team_expavg_credit_escaped=db_escape($team_expavg_credit);
                        $gridcoin_team_stats_found=TRUE;
                        break;
                }
        }

        // Get pool account stats (for billing purposes)
        curl_setopt($ch,CURLOPT_URL,$rpc_url."show_user.php?userid=$boinc_account&auth=$auth&format=xml");
        $data=curl_exec($ch);
        $xml=simplexml_load_string($data);
        if($xml==FALSE) { echo "Get hosts info error\n"; echo $rpc_url."show_user.php?userid=$boinc_account&auth=$auth&format=xml\n"; continue; }
//var_dump($data);
        $project_cpid=(string)$xml->cpid;
        $expavg_credit=(string)$xml->expavg_credit;
        $expavg_credit_escaped=db_escape($expavg_credit);

        // Expavg credit and gridcoin team expavg credit
        if($gridcoin_team_stats_found==FALSE) {
                auth_log("Sync error: gridcoin team not found for project $project_name");
        } else {
                // Write project expavg_credit for billing purposes
                db_query("INSERT INTO `boincmgr_project_stats` (`project_uid`,`expavg_credit`,`team_expavg_credit`)
VALUES ('$project_uid','$expavg_credit_escaped','$team_expavg_credit_escaped')");

                db_query("UPDATE `boincmgr_projects` SET `expavg_credit`='$expavg_credit_escaped',`team_expavg_credit`='$team_expavg_credit_escaped',`timestamp`=CURRENT_TIMESTAMP WHERE `uid`='$project_uid_escaped'");
        }

        // Update project CPID
        $project_cpid_escaped=db_escape($project_cpid);
        db_query("UPDATE `boincmgr_projects` SET `cpid`='$project_cpid_escaped' WHERE uid='$project_uid_escaped'");

        foreach($xml->host as $host_data) {
                $host_id=(string)$host_data->id;
                $host_cpid=(string)$host_data->host_cpid;
                $domain_name=(string)$host_data->domain_name;
                $p_model=(string)$host_data->p_model;
                $expavg_credit=(string)$host_data->expavg_credit;
                $expavg_time=(string)$host_data->expavg_time;

                $host_id_escaped=db_escape($host_id);
                $host_cpid_escaped=db_escape($host_cpid);
                $domain_name_escaped=db_escape($domain_name);
                $p_model_escaped=db_escape($p_model);
                $expavg_credit_escaped=db_escape($expavg_credit);
                $expavg_time_escaped=db_escape($expavg_time);

                $host_uid=db_query_to_variable("SELECT `host_uid` FROM `boincmgr_host_projects` WHERE `host_id`='$host_id_escaped' AND `project_uid`='$project_uid_escaped'");
                if($host_uid=='') $host_uid=0;
                $host_uid_escaped=db_escape($host_uid);

                // Write last results
                db_query("INSERT INTO `boincmgr_project_hosts_last` (`project_uid`,`host_uid`,`host_id`,`host_cpid`,`domain_name`,`p_model`,`expavg_credit`,`expavg_time`)
VALUES ($project_uid_escaped,'$host_uid_escaped','$host_id_escaped','$host_cpid_escaped','$domain_name_escaped','$p_model_escaped','$expavg_credit_escaped','$expavg_time_escaped')
ON DUPLICATE KEY UPDATE `host_id`=VALUES(`host_id`),`host_cpid`=VALUES(`host_cpid`),`domain_name`=VALUES(`domain_name`),`p_model`=VALUES(`p_model`),`expavg_credit`=VALUES(`expavg_credit`),`expavg_time`=VALUES(`expavg_time`),`timestamp`=CURRENT_TIMESTAMP");

                // Write hosts expavg_credit for billing purposes
                db_query("INSERT INTO `boincmgr_project_host_stats` (`project_uid`,`host_uid`,`host_id`,`expavg_credit`)
VALUES ('$project_uid_escaped','$host_uid_escaped','$host_id_escaped','$expavg_credit_escaped')");
        }
        echo "----\n";
        $full_sync_count++;
}

auth_log("Projects to sync $project_count, synced $full_sync_count");
?>
