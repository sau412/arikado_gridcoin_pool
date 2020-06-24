<?php
// Get hosts data from BOINC project

if(!isset($argc)) die();
if(isset($argv[1]) && $argv[1]=="test") $test_mode=TRUE;
else $test_mode=FALSE;

$f=fopen("/tmp/lockfile_projects_tasks","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
		die("Lockfile locked\n");
	}
}

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
	boincmgr_project_last_query_clear($project_uid);

	$project_uid_escaped=db_escape($project_uid);
	echo "Updating data for $project_name\n";

	// ================================================
	// Get project config (name, master url, rpc url)
	// ================================================
	curl_setopt($ch,CURLOPT_POST,FALSE);
	curl_setopt($ch,CURLOPT_URL,$project_url."get_project_config.php");
	$data = curl_exec ($ch);
	boincmgr_project_last_query_append($project_uid,"Query: GET ${project_url}get_project_config.php\n\nReply:\n$data\n\n");

	if($debug_mode==TRUE) {
		$data_escaped=db_escape($data);
		db_query("INSERT INTO boincmgr_xml (`type`,`message`) VALUES ('project $project_name get_project_config','$data_escaped')");
	}

	if($data=="") {
		$faults_str_array[]="$project_name (no data from project)";
		echo "No data from project\n";
		continue;
	}

	$xml=simplexml_load_string($data);

	if($xml==FALSE) {
		$faults_str_array[]="$project_name (get project config error)";
		echo "Error: $project_url\n\n";
		continue;
	}

	$name=(string)$xml->name;
	$rpc_url=(string)$xml->web_rpc_url_base;
	$master_url=(string)$xml->master_url;
	if($rpc_url=="") $rpc_url=$master_url;

	// Validate data
	if(auth_validate_ascii($name)==FALSE) {
		$faults_str_array[]="$project_name (validate project name error)";
		echo "Project name validation error\n";
		continue;
	}
	if(auth_validate_ascii($rpc_url)==FALSE) {
		$faults_str_array[]="$project_name (validate rpc url error)";
		echo "Project RPC URL validation error\n";
		continue;
	}
	if(auth_validate_ascii($master_url)==FALSE) {
		$faults_str_array[]="$project_name (validate master url error)";
		echo "Project master URL validation error\n";
		continue;
	}

	$name_escaped=db_escape($name);
	$master_url_escaped=db_escape($master_url);

	// ================================================
	// Login to project
	// ================================================
	curl_setopt($ch,CURLOPT_URL,$rpc_url."lookup_account.php?email_addr=$boinc_account&passwd_hash=$boinc_passwd_hash");
	$data=curl_exec($ch);
	boincmgr_project_last_query_append($project_uid,"Query: GET ${rpc_url}lookup_account.php?email_addr=$boinc_account&passwd_hash=$boinc_passwd_hash\n\nReply:\n$data\n\n");

	if($debug_mode==TRUE) {
		$data_escaped=db_escape($data);
		db_query("INSERT INTO boincmgr_xml (`type`,`message`) VALUES ('project $project_name lookup_account','$data_escaped')");
	}

	$xml=simplexml_load_string($data);
	if($xml==FALSE || isset($xml->error_msg)) {
		$faults_str_array[]="$project_name (login error)";
		echo "Login to project error\n";
		echo $rpc_url."/lookup_account.php?email_addr=$boinc_account&passwd_hash=$boinc_passwd_hash\n";
		continue;
	}

	$auth=$xml->authenticator;

	// ================================================
	// Get weak auth key
	// ================================================
	curl_setopt($ch,CURLOPT_URL,$rpc_url."am_get_info.php?account_key=$auth");
	$data=curl_exec($ch);
	boincmgr_project_last_query_append($project_uid,"Query: GET ${rpc_url}am_get_info.php?account_key=$auth\n\nReply:\n$data\n\n");

	if($debug_mode==TRUE) {
		$data_escaped=db_escape($data);
		db_query("INSERT INTO boincmgr_xml (`type`,`message`) VALUES ('project $project_name am_get_info','$data_escaped')");
	}

	$xml=simplexml_load_string($data);

	if($xml==FALSE) {
		$faults_str_array[]="$project_name (get weak key error)";
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

	// =============================================================
	// Update project tasks data
	// =============================================================
	$skip=0;
	do {
		echo $rpc_url."results.php?userid=$account_id&show_names=1&offset=$skip\n";
		curl_setopt($ch,CURLOPT_URL,$rpc_url."results.php?userid=$account_id&show_names=1&offset=$skip");
		curl_setopt($ch,CURLOPT_COOKIE,"auth=$auth");
		$data=curl_exec($ch);
		$skip+=20;
		//var_dump($data);
	} while(results_parse_page($project_uid,$data));
	echo "----\n";
	$full_sync_count++;
}

// Make faults info string
if(count($faults_str_array)>=1) {
	$faults_str=", errors: ".implode(", ",$faults_str_array);
} else {
	$faults_str="";
}

// Write results to log
if($test_mode==FALSE) {
	auth_log("Projects tasks to sync $project_count, synced $full_sync_count".$faults_str);
} else {
	auth_log("Projects tasks to sync (test mode) $project_count, synced $full_sync_count".$faults_str);
}
?>
