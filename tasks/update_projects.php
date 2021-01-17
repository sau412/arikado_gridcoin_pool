<?php
// Get hosts data from BOINC project

if(!isset($argc)) die();
if(isset($argv[1]) && $argv[1]=="test") $test_mode=TRUE;
else $test_mode=FALSE;

$pid_file = "/tmp/lockfile_projects.pid";
$prev_process_pid = file_get_contents($pid_file);

$f=fopen("/tmp/lockfile_projects","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
			//die("Lockfile locked\n");
			echo "Prev process is not ended yet, killing pid $prev_process_pid\n";
			posix_kill($prev_process_pid, SIGTERM);
	}
}

file_put_contents($pid_file,getmypid());

//$test_mode=TRUE;

require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/billing.php");
require_once("../lib/boincmgr.php");
require_once("../lib/results_parser.php");
require_once("../lib/broker.php");

//define('DB_DEBUG',1);

db_connect();

// Get enabled and stats only projects
$project_data_array=db_query_to_array("SELECT `uid`,`name`,`project_url`,`update_weak_auth` FROM `projects` WHERE `status` IN ('enabled','stats only')");

// Setup cURL
$ch=curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);

// For each project
$project_count=count($project_data_array);
$full_sync_count=0;
$faults_str_array=array();

foreach($project_data_array as $project_data)
	{
	$project_uid=$project_data['uid'];
	$project_name=$project_data['name'];
	$project_url=$project_data['project_url'];
	$update_weak_auth=$project_data['update_weak_auth'];
	
	$log_message = [];
	$log_message[] = [
		"project_name" => $project_name,
		"project_url" => $project_url,
	];

	$project_uid_escaped=db_escape($project_uid);
	echo "Updating data for $project_name\n";

	// ================================================
	// Get project config (name, master url, rpc url)
	// ================================================
	$url = $project_url . "get_project_config.php";
	curl_setopt($ch,CURLOPT_POST,FALSE);
	curl_setopt($ch,CURLOPT_URL, $url);
	$data = curl_exec ($ch);

	$log_message[] = [
		"url" => $url,
		"request" => "",
		"reply" => $data,
	];

	if($data == "") {
		echo "No data from project\n";
		$log_message[] = "No data from project";
		auth_log($log_message, 7);
		auth_log("No project config for project $project_name", 4);
		continue;
	}

	$xml=simplexml_load_string($data);

	if($xml==FALSE) {
		$log_message[] = "Error parsing XML from project";
		auth_log($log_message, 7);
		auth_log("Error parsing XML from project $project_name", 4);
		echo "Error: $project_url\n\n";
		continue;
	}

	$name=(string)$xml->name;
	$rpc_url=(string)$xml->web_rpc_url_base;
	$master_url=(string)$xml->master_url;
	if($rpc_url=="") $rpc_url=$master_url;

	// Validate data
	if(auth_validate_ascii($name)==FALSE) {
		$log_message[] = "Project name validation error";
		auth_log($log_message, 7);
		auth_log("Project name validation error for $project_name", 4);
		echo "Project name validation error\n";
		continue;
	}
	if(auth_validate_ascii($rpc_url)==FALSE) {
		$log_message[] = "Project RPC URL validation error";
		auth_log($log_message, 7);
		auth_log("Project RPC URL validation error for $project_name", 4);
		echo "Project RPC URL validation error\n";
		continue;
	}
	if(auth_validate_ascii($master_url)==FALSE) {
		$log_message[] = "Project master URL validation error";
		auth_log($log_message, 7);
		auth_log("Project master URL validation error for $project_name", 4);
		echo "Project master URL validation error\n";
		continue;
	}

	$name_escaped=db_escape($name);
	$master_url_escaped=db_escape($master_url);

	// ================================================
	// Login to project
	// ================================================
	$url = $rpc_url."lookup_account.php?email_addr=$boinc_account&passwd_hash=$boinc_passwd_hash";
	curl_setopt($ch,CURLOPT_URL, $url);
	$data=curl_exec($ch);
	
	$log_message[] = [
		"url" => $url,
		"request" => "",
		"reply" => $data,
	];

	$xml = simplexml_load_string($data);
	if($xml == false || isset($xml->error_msg)) {
		$log_message[] = "Login to project error\n";
		auth_log($log_message, 7);
		auth_log("Login to project error for project $project_name", 4);
		echo "Login to project error\n";
		continue;
	}

	$auth=$xml->authenticator;

	// ================================================
	// Get weak auth key
	// ================================================
	$url = $rpc_url."am_get_info.php?account_key=$auth";
	curl_setopt($ch,CURLOPT_URL, $url);
	$data = curl_exec($ch);

	$log_message[] = [
		"url" => $url,
		"request" => "",
		"reply" => $data,
	];

	$xml=simplexml_load_string($data);

	if($xml == false) {
		$log_message[] = "Get weak auth key error";
		auth_log($log_message, 7);
		auth_log("Get weak auth key error for project $project_name", 4);
		echo "Get weak auth key error\n";
		continue;
	}

	$account_id=$xml->id;
	$weak_auth=$xml->weak_auth;
	$weak_auth_escaped=db_escape($weak_auth);
	$team_id_from_account=(int)$xml->teamid;
	
	// World Community Grid returns wrong weak key, so do not update keys for now (update_weak_auth is false only for that project)
	if($update_weak_auth==TRUE && $weak_auth!='') {
		db_query("UPDATE `projects` SET `name`='$name_escaped',`weak_auth`='$weak_auth_escaped' WHERE `uid`='$project_uid'");
	} else {
		db_query("UPDATE `projects` SET `name`='$name_escaped' WHERE `uid`='$project_uid_escaped'");
	}

	// ================================================
	// Get Gridcoin team stats (for billing purposes)
	// ================================================
	$url = $rpc_url."team_lookup.php?team_name=Gridcoin&format=xml";
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);

	$log_message[] = [
		"url" => $url,
		"request" => "",
		"reply" => $data,
	];

	$xml=simplexml_load_string($data);
	if($xml==FALSE) {
		$log_message[] = "Get gridcoin team stats error";
		echo "Get gridcoin team stats error\n";
		auth_log($log_message, 7);
		auth_log("Get gridcoin team stats error for project $project_name", 4);
		continue;
	}

	$gridcoin_team_stats_found=FALSE;
	foreach($xml->team as $team_info) {
		if($team_info->name=="Gridcoin") {
			$team_id_from_team=(int)$team_info->id;
			$team_expavg_credit=(string)$team_info->expavg_credit;
			if(auth_validate_float($team_expavg_credit)==FALSE) {
				$log_message[] = "Gridcoin team expavg_credit validation error";
				echo "Gridcoin team expavg_credit validation error\n";
				continue;
			}
			$team_expavg_credit_escaped=db_escape($team_expavg_credit);
			$gridcoin_team_stats_found=TRUE;
			break;
		}
	}

	// ================================================
	// Get pool account stats (for billing purposes)
	// ================================================
	$url = $rpc_url."show_user.php?userid=$boinc_account&auth=$auth&format=xml";
	curl_setopt($ch, CURLOPT_URL, $url);
	$data=curl_exec($ch);
	
	$log_message[] = [
		"url" => $url,
		"request" => "",
		"reply" => $data,
	];
	
	$xml=simplexml_load_string($data);
	if($xml==FALSE) {
		$log_message[] = "Get hosts info error";
		auth_log($log_message, 7);
		auth_log("Get hosts info error for project $project_name", 4);
		echo "Get hosts info error\n";
		continue;
	}

	$project_cpid=(string)$xml->cpid;
	$expavg_credit=(string)$xml->expavg_credit;

	// Validate data
	if(auth_validate_hash($project_cpid)==FALSE) {
		$log_message[] = "Project cpid validation error";
		auth_log($log_message, 7);
		auth_log("Project cpid validation error for project $project_name", 4);
		echo "Project cpid validation error\n";
		continue;
	}
	if(auth_validate_float($expavg_credit)==FALSE) {
		$log_message[] = "Project expavg_credit validation error";
		auth_log($log_message, 7;
		auth_log("Project expavg_credit validation error for project $project_name", 4);
		echo "Project expavg_credit validation error\n";
		continue;
	}

	// Escape data
	$expavg_credit_escaped=db_escape($expavg_credit);
	$project_cpid_escaped=db_escape($project_cpid);

	// Expavg credit and gridcoin team expavg credit
	if($gridcoin_team_stats_found==FALSE) {
		$log_message[] = "Sync error: gridcoin team not found for project";
		auth_log($log_message, 7);
		auth_log("Sync error: gridcoin team not found for project $project_name", 4);
	} else {
		// Write project expavg_credit for billing purposes
		db_query("INSERT INTO `project_stats` (`project_uid`,`expavg_credit`,`team_expavg_credit`)
VALUES ('$project_uid','$expavg_credit_escaped','$team_expavg_credit_escaped')");

		if($team_id_from_account==$team_id_from_team) $team_name="Gridcoin";
		else $team_name=$team_id_from_account;

		$team_name_escaped=db_escape($team_name);
		// Team expavg credit cannot be obtained from project easily.
		// Pool will use superblock data instead
		db_query("UPDATE `projects`
					SET `team`='$team_name',
						`expavg_credit`='$expavg_credit_escaped',
						`timestamp`=CURRENT_TIMESTAMP
					WHERE `uid`='$project_uid_escaped'");
	}

	// Update project CPID
	db_query("UPDATE `projects` SET `cpid`='$project_cpid_escaped' WHERE uid='$project_uid_escaped'");

	foreach($xml->host as $host_data) {
		// Get data
		$host_id=(string)$host_data->id;
		$host_cpid=(string)$host_data->host_cpid;
		$domain_name=(string)$host_data->domain_name;
		$p_model=(string)$host_data->p_model;
		$expavg_credit=(string)$host_data->expavg_credit;
		$expavg_time=(string)$host_data->expavg_time;

		// Validate data
		if(auth_validate_integer($host_id)==FALSE) { echo "Host id validation error\n"; continue; }
		if(auth_validate_hash($host_cpid)==FALSE) { echo "Host cpid validation error\n"; continue; }
		if(auth_validate_domain($domain_name)==FALSE) { echo "Host domain name validation error\n"; continue; }
		if(auth_validate_ascii($p_model)==FALSE) { echo "Host CPU model validation error\n"; continue; }
		if(auth_validate_float($expavg_credit)==FALSE) { echo "Host expavg_credit validation error\n"; continue; }
		if(auth_validate_float($expavg_time)==FALSE) { echo "Host expavg_time validation error\n"; continue; }

		// If expavg not updated for a month then skip it
		if((time() - $expavg_time) > 86400 * 30) {
			$log_message[] = "Skipping host_id $host_id host cpid $host_cpid expavg time $expavg_time is too old";
			echo "Skipping host_id $host_id host cpid $host_cpid expavg time $expavg_time is too old\n";
			continue;
		}

		// Escape data
		$host_id_escaped=db_escape($host_id);
		$host_cpid_escaped=db_escape($host_cpid);
		$domain_name_escaped=db_escape(boincmgr_domain_encode($domain_name));
		$p_model_escaped=db_escape($p_model);
		$expavg_credit_escaped=db_escape($expavg_credit);

		// Get host uid by project_uid, host_id and host_cpid - most secure
		// Sometimes BOINC returns internal cpid, sometimes external cpid
		$host_uid=db_query_to_variable("SELECT bhp.`host_uid` FROM `host_projects` AS bhp
LEFT JOIN `hosts` AS bh ON bh.`uid`=bhp.`host_uid`
WHERE bhp.`host_id`='$host_id_escaped' AND bhp.`project_uid`='$project_uid_escaped' AND (bh.`external_host_cpid`='$host_cpid_escaped' OR bh.`internal_host_cpid`='$host_cpid_escaped')");

		// If host not found - check host by host cpid only (may be user didn't finish synchronization) - less secure, but rewards are not lost
		if($host_uid=='') {
			$host_uid=db_query_to_variable("SELECT `uid` FROM `hosts` WHERE `external_host_cpid`='$host_cpid_escaped' OR `internal_host_cpid`='$host_cpid_escaped'");
			// Unknown host - we can't find anything
			if($host_uid=='') {
				echo "Host host_id '$host_id' domain '$domain_name' model '$p_model' cpid $host_cpid not found\n";
				$host_uid=0;
			} else {
				echo "Host host_id '$host_id' domain '$domain_name' model '$p_model' cpid '$host_cpid' not fully synced, found by cpid\n";
			}
		}
		$host_uid_escaped=db_escape($host_uid);

		// Write last results
		db_query("INSERT INTO `project_hosts_last` (`project_uid`,`host_uid`,`host_id`,`host_cpid`,`domain_name`,`p_model`,`expavg_credit`)
VALUES ($project_uid_escaped,'$host_uid_escaped','$host_id_escaped','$host_cpid_escaped','$domain_name_escaped','$p_model_escaped','$expavg_credit_escaped')
ON DUPLICATE KEY UPDATE `host_id`=VALUES(`host_id`),`host_cpid`=VALUES(`host_cpid`),`domain_name`=VALUES(`domain_name`),`p_model`=VALUES(`p_model`),`expavg_credit`=VALUES(`expavg_credit`),`timestamp`=CURRENT_TIMESTAMP");

		if($test_mode==FALSE) {
			// Write hosts expavg_credit for billing purposes
			db_query("INSERT INTO `project_host_stats` (`project_uid`,`host_uid`,`host_id`,`expavg_credit`)
VALUES ('$project_uid_escaped','$host_uid_escaped','$host_id_escaped','$expavg_credit_escaped')");
		}
	}
	
	auth_log($log_message, 7);
	auth_log("Project $project_name synced", 6);
	echo "----\n";
	$full_sync_count++;
}

// Write results to log
if($test_mode==FALSE) {
	auth_log("Projects to sync $project_count, synced $full_sync_count", 6);
} else {
	auth_log("Projects to sync (test mode) $project_count, synced $full_sync_count", 6);
}

echo "DB queries count $db_queries_count\n";
