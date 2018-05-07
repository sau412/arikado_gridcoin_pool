<?php
// Get hosts data from BOINC project

require_once("settings.php");
require_once("db.php");
require_once("billing.php");

db_connect();

// Get whitelisted projects
$project_data_array=db_query_to_array("SELECT * FROM `boincmgr_projects` WHERE `status`='whitelisted'");

// Setup cURL
$ch=curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);

// For each project
foreach($project_data_array as $project_data)
        {
        $project_uid=$project_data['uid'];
        $project_url=$project_data['project_url'];

        // Get project config (name, master url,platforms)
        curl_setopt($ch,CURLOPT_POST,FALSE);
        curl_setopt($ch,CURLOPT_URL,$project_url."/get_project_config.php");
        $data = curl_exec ($ch);
//      var_dump($data);
        $xml=simplexml_load_string($data);

        if($xml==FALSE)
                {
                echo "Error: $project_url\n\n";
                continue;
                }

        $name=(string)$xml->name;
        $master_url=(string)$xml->master_url;
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
//die();
        echo "Project $name ($master_url)\n";
        //echo "$platform_names\n";
        $name_escaped=db_escape($name);
        $master_url_escaped=db_escape($master_url);
        //db_query("UPDATE `boincmgr_projects` SET `name`='$name_escaped',`project_url`='$master_url_escaped' WHERE `uid`='$project_uid'");

        // Login to project
        curl_setopt($ch,CURLOPT_URL,$project_url."/lookup_account.php?email_addr=$boinc_account&passwd_hash=$boinc_passwd_hash");
        $data=curl_exec($ch);

        $xml=simplexml_load_string($data);
        if($xml==FALSE) continue;
        $auth=$xml->authenticator;

        // Get weak auth key
        curl_setopt($ch,CURLOPT_URL,$project_url."/am_get_info.php?account_key=$auth");
        $data=curl_exec($ch);

        $xml=simplexml_load_string($data);
        if($xml==FALSE) continue;
        $weak_auth=$xml->weak_auth;
        $weak_auth_escaped=db_escape($weak_auth);

        db_query("UPDATE `boincmgr_projects` SET `name`='$name_escaped',`project_url`='$master_url_escaped',`weak_auth`='$weak_auth_escaped' WHERE `uid`='$project_uid'");

        // Get Gridcoin team stats (for billing purposes)
        curl_setopt($ch,CURLOPT_URL,$project_url."/team_lookup.php?team_name=Gridcoin&format=xml");
        $data=curl_exec($ch);
        $xml=simplexml_load_string($data);
        if($xml==FALSE) continue;

        $team_expavg_credit=(string)$xml->team->expavg_credit;
        $team_expavg_credit_escaped=db_escape($team_expavg_credit);

        // Get pool stats (for billing purposes)
        curl_setopt($ch,CURLOPT_URL,$project_url."/show_user.php?userid=$boinc_account&auth=$auth&format=xml");
        $data=curl_exec($ch);
        $xml=simplexml_load_string($data);
        if($xml==FALSE) continue;

        $expavg_credit=(string)$xml->expavg_credit;
        $expavg_credit_escaped=db_escape($expavg_credit);

        // Write project expavg_credit for billing purposes
        db_query("INSERT INTO `boincmgr_project_stats` (`project_uid`,`expavg_credit`,`team_expavg_credit`,`billing_period_uid`)
VALUES ('$project_uid','$expavg_credit_escaped','$team_expavg_credit_escaped','$billing_period_uid')");

        foreach($xml->host as $host_data) {
            //var_dump($host_data);
            $id=(string)$host_data->id;
            $host_cpid=(string)$host_data->host_cpid;
            $domain_name=(string)$host_data->domain_name;
            $p_model=(string)$host_data->p_model;
            $expavg_credit=(string)$host_data->expavg_credit;
            $expavg_time=(string)$host_data->expavg_time;

            $id_escaped=db_escape($id);
            $host_cpid_escaped=db_escape($host_cpid);
            $domain_name_escaped=db_escape($domain_name);
            $p_model_escaped=db_escape($p_model);
            $expavg_credit_escaped=db_escape($expavg_credit);
            $expavg_time_escaped=db_escape($expavg_time);

            $username=db_query_to_variable("SELECT `username` FROM `boincmgr_hosts` WHERE `external_host_cpid`='$host_cpid_escaped'");

            $username_escaped=db_escape($username);

            // Write last results
            db_query("INSERT INTO `boincmgr_project_hosts_last` (`project_uid`,`host_id`,`host_cpid`,`username`,`domain_name`,`p_model`,`expavg_credit`,`expavg_time`)
VALUES ($project_uid,'$id_escaped','$host_cpid_escaped','$username_escaped','$domain_name_escaped','$p_model_escaped','$expavg_credit_escaped','$expavg_time_escaped')
ON DUPLICATE KEY UPDATE `host_cpid`=VALUES(`host_cpid`),`username`=VALUES(`username`),`domain_name`=VALUES(`domain_name`),`p_model`=VALUES(`p_model`),`expavg_credit`=VALUES(`expavg_credit`),`expavg_time`=VALUES(`expavg_time`)");

            // Write hosts expavg_credit for billing purposes
            db_query("INSERT INTO `boincmgr_project_host_stats` (`project_uid`,`host_id`,`host_cpid`,`username`,`expavg_credit`,`billing_period_uid`)
VALUES ('$project_uid','$id_escaped','$host_cpid_escaped','$username_escaped','$expavg_credit_escaped','$billing_period_uid')");
        }
}
?>
