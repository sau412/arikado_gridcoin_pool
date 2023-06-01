<?php
// Internal functions for project

// Attach project
function boincmgr_attach($username,$host_uid,$project_uid) {
//	global $username;
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
		$host_correct=db_query_to_variable("SELECT 1 FROM `hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
		$project_correct=db_query_to_variable("SELECT 1 FROM `projects` WHERE `uid`='$project_uid_escaped' AND `status` IN ('enabled')");
	}
	if(auth_is_admin($username) || ($host_correct && $project_correct)) {
		auth_log("Attach username '$username' project '$project_name' to host '$host_name'");
		db_query("INSERT INTO `attach_projects` (`project_uid`,`host_uid`,`status`) VALUES ('$project_uid_escaped','$host_uid_escaped','new') ON DUPLICATE KEY UPDATE `status`='new'");
		return TRUE;
	} else {
		auth_log("Attach fail username '$username' project '$project_name' to host '$host_name'");
		return FALSE;
	}
}

// Detach project
function boincmgr_detach($username,$attached_uid) {
	$attached_uid_escaped=db_escape($attached_uid);
	$host_uid=db_query_to_variable("SELECT `host_uid` FROM `attach_projects` WHERE `uid`='$attached_uid_escaped'");
	$project_uid=db_query_to_variable("SELECT `project_uid` FROM `attach_projects` WHERE `uid`='$attached_uid_escaped'");
	$status=db_query_to_variable("SELECT `status` FROM `attach_projects` WHERE `uid`='$attached_uid_escaped'");

	$username_uid=boincmgr_get_username_uid($username);
	$username_uid_escaped=db_escape($username_uid);
	$host_uid_escaped=db_escape($host_uid);
	$project_name=boincmgr_get_project_name($project_uid);

	// Check if host_uid belongs to this user
	if(auth_is_admin($username)==FALSE) $host_uid=db_query_to_variable("SELECT `uid` FROM `hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
	if($host_uid || auth_is_admin($username)) {
		$host_name=boincmgr_get_host_name($host_uid);

		auth_log("Detach username '$username' project '$project_name' from host '$host_name'");

		// If status is "new" then we can just delete
		if($status=="new") {
			db_query("DELETE FROM `attach_projects` WHERE `uid`='$attached_uid_escaped'");
		// Else detach first
		} else {
			db_query("UPDATE `attach_projects` SET `status`='detach' WHERE `uid`='$attached_uid_escaped'");
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
	$host_uid=db_query_to_variable("SELECT `host_uid` FROM `attach_projects` WHERE `uid`='$attached_uid_escaped'");
	$project_uid=db_query_to_variable("SELECT `project_uid` FROM `attach_projects` WHERE `uid`='$attached_uid_escaped'");
	$status=db_query_to_variable("SELECT `status` FROM `attach_projects` WHERE `uid`='$attached_uid_escaped'");

	$username_uid=boincmgr_get_username_uid($username);
	$username_uid_escaped=db_escape($username_uid);
	$host_uid_escaped=db_escape($host_uid);
	$project_name=boincmgr_get_project_name($project_uid);

	// Check if host_uid belongs to this user
	if(auth_is_admin($username)==FALSE) $host_uid=db_query_to_variable("SELECT `uid` FROM `hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
	if($host_uid || auth_is_admin($username)) {
		$host_name=boincmgr_get_host_name($host_uid);

		$resource_share_escaped=db_escape($resource_share);
		$options_str_array=array();
		$valid_options_array=array("detach","detach_when_done","suspend","dont_request_more_work","abort_not_started","no_cpu","no_cuda","no_ati","no_intel","user_override");
		foreach($valid_options_array as $valid_option) {
			if(in_array($valid_option,$options_array)) $options_str_array[]=$valid_option;
		}
		$options_str=implode(",",$options_str_array);
		$options_str_escaped=db_escape($options_str);

		auth_log("Change project settings username '$username' project '$project_name' host '$host_name' resource share '$resource_share' options '$options_str'");

		db_query("UPDATE `attach_projects` SET `resource_share`='$resource_share_escaped',`options`='$options_str_escaped' WHERE `uid`='$attached_uid_escaped'");

		// If detach or detach_when_done, then update status
		if(in_array("detach",$options_array) || in_array("detach_when_done",$options_array)) {
			db_query("UPDATE `attach_projects` SET `status`='detach' WHERE `uid`='$attached_uid_escaped'");
		} else {
			db_query("UPDATE `attach_projects` SET `status`='sent' WHERE `uid`='$attached_uid_escaped'");
		}

		return TRUE;
	} else {
		return FALSE;
	}
}

// Get project name by uid
function boincmgr_get_project_name($uid) {
	$uid_escaped=db_escape($uid);
	return db_query_to_variable("SELECT `name` FROM `projects` WHERE `uid` = '$uid_escaped'");
}

// Get host name by uid
function boincmgr_get_host_name($uid) {
	$uid_escaped=db_escape($uid);
	$hostname_encoded=db_query_to_variable("SELECT `domain_name` FROM `hosts` WHERE `uid` = '$uid_escaped'");

	$hostname_decoded=boincmgr_domain_decode($hostname_encoded);

	if(auth_validate_ascii($hostname_decoded)) return $hostname_decoded;
	else return $hostname_encoded;
}

// Get host info (CPU, GPU, domain)
function boincmgr_get_host_info($uid) {
	$uid_escaped=db_escape($uid);
	$query_data_encoded=db_query_to_variable("SELECT `last_query` FROM `hosts` WHERE `uid` = '$uid_escaped'");
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
//		$result.="No GPU\n";
	}
	return $result;
}

// Get host short info (OS, number CPU, number GPU)
function boincmgr_get_host_short_info($uid) {
	$uid_escaped=db_escape($uid);
	$query_data_encoded=db_query_to_variable("SELECT `last_query` FROM `hosts` WHERE `uid` = '$uid_escaped'");
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
	db_query("UPDATE `payouts` SET txid='$txid_escaped' WHERE `txid` IS NULL and `payout_address`='$payout_address_escaped'");
}

// Get user name by uid
function boincmgr_get_user_name($uid) {
	$uid_escaped=db_escape($uid);
	return db_query_to_variable("SELECT `username` FROM `users` WHERE `uid` = '$uid_escaped'");
}

// Get user email by uid
function boincmgr_get_user_email($uid) {
	$uid_escaped=db_escape($uid);
	return db_query_to_variable("SELECT `email` FROM `users` WHERE `uid` = '$uid_escaped'");
}

// Get user uid by username
function boincmgr_get_username_uid($username) {
	$username_escaped=db_escape($username);
	return db_query_to_variable("SELECT `uid` FROM `users` WHERE LOWER(`username`) = LOWER('$username_escaped')");
}

// Get project uid by project name
function boincmgr_get_project_uid($project_name) {
	$project_name_escaped=db_escape($project_name);
	return db_query_to_variable("SELECT `uid` FROM `projects` WHERE `name` = '$project_name_escaped'");
}

// Check project weak key
function boincmgr_check_weak_key($project_uid,$weak_key) {
	$project_uid_escaped=db_escape($project_uid);
	$weak_key_escaped=db_escape($weak_key);
	$weak_key_exists=db_query_to_variable("SELECT 1 FROM `projects` WHERE `uid` = '$project_uid_escaped' AND `weak_auth` = '$weak_key_escaped'");
	if($weak_key_exists) return TRUE;
	else return FALSE;
}

// Get host uid by user name and host_cpid
function boincmgr_get_host_uid($username_uid,$host_cpid) {
	$username_uid_escaped=db_escape($username_uid);
	$host_cpid_escaped=db_escape($host_cpid);
	return db_query_to_variable("SELECT `uid` FROM `hosts` WHERE `username_uid` = '$username_uid_escaped' AND `internal_host_cpid`='$host_cpid_escaped'");
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
	if(auth_is_admin($username)==FALSE) $host_uid=db_query_to_variable("SELECT `uid` FROM `hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
	if($host_uid || auth_is_admin($username)) {
		$host_name=boincmgr_get_host_name($host_uid);

		auth_log("Delete host '$host_name' by username '$username'");

		// Delete any attach project statuses
		db_query("DELETE FROM `attach_projects` WHERE `host_uid`='$host_uid_escaped'");

		// Delete attached projects
		db_query("DELETE FROM `host_projects` WHERE `host_uid`='$host_uid_escaped'");

		// Delete host stats
		db_query("DELETE FROM `project_hosts_last` WHERE `host_uid`='$host_uid_escaped'");
		db_query("DELETE FROM `project_host_stats` WHERE `host_uid`='$host_uid_escaped'");

		// Delete host
		db_query("DELETE FROM `hosts` WHERE `uid`='$host_uid_escaped'");
	}
}

// Set pool info
function boincmgr_set_pool_info($pool_info) {
	global $current_language;
	$news_variable=$current_language["news_variable"];
	boincmgr_set_variable($news_variable,$pool_info);
}

// Get pool info
function boincmgr_get_pool_info() {
	global $current_language;
	$news_variable=$current_language["news_variable"];
	return boincmgr_get_variable($news_variable);
}

// Get variable
function boincmgr_get_variable($name) {
	$name_escaped=db_escape($name);
	return db_query_to_variable("SELECT `value` FROM `variables` WHERE `name`='$name_escaped'");
}

// Set variable
function boincmgr_set_variable($name,$value) {
	$name_escaped=db_escape($name);
	$value_escaped=db_escape($value);
	return db_query("INSERT INTO `variables` (`name`,`value`) VALUES ('$name_escaped','$value_escaped') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
}

// Clear project log
function boincmgr_project_last_query_clear($project_uid) {
	$project_uid_escaped=db_escape($project_uid);
	db_query("UPDATE `projects` SET `last_query`='' WHERE `uid`='$project_uid_escaped'");
}

// Get project log
function boincmgr_project_last_query_get($project_uid) {
	$project_uid_escaped=db_escape($project_uid);
	$result=db_query_to_variable("SELECT `last_query` FROM `projects` WHERE `uid`='$project_uid_escaped'");
	return base64_decode($result);
}

// Append text to project log
function boincmgr_project_last_query_append($project_uid,$text) {
	$project_uid_escaped=db_escape($project_uid);
	$current_text=boincmgr_project_last_query_get($project_uid);
	$new_text=$current_text.$text;
	$new_text_encoded=base64_encode($new_text);
	$new_text_encoded_escaped=db_escape($new_text_encoded);
	db_query("UPDATE `projects` SET `last_query`='$new_text_encoded_escaped' WHERE `uid`='$project_uid_escaped'");
}

// Cache function
// If memcached server set, store in memcached
// Otherwise store in db
function boincmgr_cache_function($function_name, $parameters, $force_update = 0) {
	$call_str = "$function_name(" . implode(",",$parameters) . ")";
	$hash = hash("sha256", $call_str);
	$result = cache_get($hash);
	if($result === FALSE || $result == "" || $force_update) {
		$result = call_user_func_array($function_name, $parameters);
		cache_set($hash, $result);
	}
	return $result;
}

// Load currency cache
$boincmgr_cache_payout_limit=NULL;
$boincmgr_cache_tx_fee=NULL;
$boincmgr_cache_service_fee=NULL;
$boincmgr_cache_tx_url=NULL;
$boicnmgr_cache_address_url=NULL;
$boincmgr_cache_rate_per_grc=NULL;
function boincmgr_load_currency_data() {
	global $boincmgr_cache_payout_limit;
	global $boincmgr_cache_tx_fee;
	global $boincmgr_cache_service_fee;
	global $boincmgr_cache_tx_url;
	global $boincmgr_cache_address_url;
	global $boincmgr_cache_rate_per_grc;

	$data=db_query_to_array("SELECT `name`,`payout_limit`,`tx_fee`,
					`project_fee`,`url_tx`,`url_wallet`,`rate_per_grc`
				FROM `currency`");
	foreach($data as $row) {
		$name = $row['name'];
		$payout_limit = $row['payout_limit'];
		$tx_fee = $row['tx_fee'];
		$service_fee = $row['project_fee'];
		$tx_url = $row['url_tx'];
		$address_url = $row['url_wallet'];
		$rate_per_grc = $row['rate_per_grc'];

		$boincmgr_cache_payout_limit[$name] = $payout_limit;
		$boincmgr_cache_tx_fee[$name] = $tx_fee;
		$boincmgr_cache_service_fee[$name] = $service_fee;
		$boincmgr_cache_tx_url[$name] = $tx_url;
		$boincmgr_cache_address_url[$name] = $address_url;
		$boincmgr_cache_rate_per_grc[$name] = $rate_per_grc;
	}
}

// Get payout rate
function boincmgr_get_payout_rate($currency) {
	global $boincmgr_cache_rate_per_grc;
	if(is_null($boincmgr_cache_rate_per_grc)) {
		boincmgr_load_currency_data();
	}
	return $boincmgr_cache_rate_per_grc[$currency];
}

// Get tx url
function boincmgr_get_tx_url($currency) {
	global $boincmgr_cache_tx_url;
	if(is_null($boincmgr_cache_tx_url)) {
		boincmgr_load_currency_data();
	}
	return $boincmgr_cache_tx_url[$currency];
}

// Get addres url
function boincmgr_get_address_url($currency) {
	global $boincmgr_cache_address_url;
	if(is_null($boincmgr_cache_address_url)) {
		boincmgr_load_currency_data();
	}
	return $boincmgr_cache_address_url[$currency];
}

// Get payout limit
function boincmgr_get_payout_limit($currency) {
	global $boincmgr_cache_payout_limit;
	if(is_null($boincmgr_cache_payout_limit)) {
		boincmgr_load_currency_data();
	}
	return $boincmgr_cache_payout_limit[$currency];
}

// Get tx fee
function boincmgr_get_tx_fee_estimation($currency) {
	global $boincmgr_cache_tx_fee;
	if(is_null($boincmgr_cache_tx_fee)) {
		boincmgr_load_currency_data();
	}
	return $boincmgr_cache_tx_fee[$currency];
}

// Get payout fee
function boincmgr_get_service_fee($currency) {
	global $boincmgr_cache_service_fee;
	if(is_null($boincmgr_cache_service_fee)) {
		boincmgr_load_currency_data();
	}
	return $boincmgr_cache_service_fee[$currency];
}

// Add message
function boincmgr_message_send($username_uid,$reply_to,$message) {
	global $feedback_email;

	if($username_uid!='') $username_uid_escaped="'".db_escape($username_uid)."'";
	else $username_uid_escaped="NULL";
	$reply_to_escaped=db_escape($reply_to);
	$message_escaped=db_escape($message);
	// Save message into messages
	db_query("INSERT INTO `messages` (`username_uid`,`reply_to`,`is_read`,`message`,`timestamp`)
		VALUES ($username_uid_escaped,'$reply_to_escaped','0','$message_escaped',NOW())");
	
	$username = boincmgr_get_user_name($username_uid);
	$body = "";
	$body .= "Username: $username\n";
	$body .= "Reply-to: $reply_to\n";
	$body .= "Message: $message\n";

	email_add($feedback_email, 'Pool feedback query', $body);
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
	$whiltelisted_count=db_query_to_variable("SELECT count(*) FROM `projects` WHERE `status` IN ('enabled','stats only')");
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
	$relative_contribution=db_query_to_variable("SELECT SUM(bphl.`expavg_credit`/bp.`superblock_expavg_credit`) FROM `project_hosts_last` AS bphl
LEFT OUTER JOIN `projects` AS bp ON bp.uid=bphl.project_uid
WHERE bphl.`project_uid`='$project_uid_escaped' AND bphl.`host_uid`='$host_uid_escaped' AND bp.`status` IN ('enabled','stats only')");
	if($relative_contribution=="") $relative_contribution=0;
	return $relative_contribution;
}

// Get project user relative contribution
function boincmgr_get_relative_contribution_project_user($project_uid,$user_uid) {
	$project_uid_escaped=db_escape($project_uid);
	$user_uid_escaped=db_escape($user_uid);
	$relative_contribution=db_query_to_variable("SELECT SUM(bphl.`expavg_credit`/bp.`superblock_expavg_credit`) FROM `project_hosts_last` AS bphl
LEFT OUTER JOIN `projects` AS bp ON bp.uid=bphl.project_uid
LEFT OUTER JOIN `hosts` AS bh ON bh.uid=bphl.host_uid
WHERE bphl.`project_uid`='$project_uid_escaped' AND bh.`username_uid`='$user_uid_escaped' AND bp.`status` IN ('enabled','stats only')");
	if($relative_contribution=="") $relative_contribution=0;
	return $relative_contribution;
}

// Get project relative contribution
function boincmgr_get_relative_contribution_project($project_uid) {
	$project_uid_escaped=db_escape($project_uid);
	$relative_contribution=db_query_to_variable("SELECT SUM(bp.`expavg_credit`/bp.`superblock_expavg_credit`) FROM `projects` AS bp
WHERE bp.`uid`='$project_uid_escaped' AND bp.`status` IN ('enabled','stats only')");
	if($relative_contribution=="") $relative_contribution=0;
	return $relative_contribution;
}

// Get host relative contribution
function boincmgr_get_relative_contribution_host($host_uid) {
	$host_uid_escaped=db_escape($host_uid);
	$relative_contribution=db_query_to_variable("SELECT SUM(bphl.`expavg_credit`/bp.`superblock_expavg_credit`) FROM `project_hosts_last` AS bphl
LEFT OUTER JOIN `projects` AS bp ON bp.uid=bphl.project_uid
WHERE bphl.`host_uid`='$host_uid_escaped' AND bp.`status` IN ('enabled','stats only')");
	if($relative_contribution=="") $relative_contribution=0;
	return $relative_contribution;
}

// Get user relative contribution
function boincmgr_get_relative_contribution_user($user_uid) {
	$user_uid_escaped=db_escape($user_uid);
	$relative_contribution=db_query_to_variable("SELECT SUM(bphl.`expavg_credit`/bp.`superblock_expavg_credit`) FROM `project_hosts_last` AS bphl
LEFT OUTER JOIN `projects` AS bp ON bp.uid=bphl.project_uid
LEFT OUTER JOIN `hosts` AS bh ON bh.uid=bphl.host_uid
WHERE bh.`username_uid`='$user_uid_escaped' AND bp.`status` IN ('enabled','stats only')");
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

	$user_magnitude=db_query_to_variable("SELECT SUM($mag_per_project*bphl.`expavg_credit`/(bp.`superblock_expavg_credit`)) AS magnitude
FROM `project_hosts_last` AS bphl
LEFT JOIN `hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bu.`uid`='$username_uid_escaped' AND bp.`status` IN ('enabled','stats only')
GROUP BY bu.`username`
HAVING SUM($mag_per_project*bphl.`expavg_credit`/(bp.`superblock_expavg_credit`))>=0.01
ORDER BY SUM(bphl.`expavg_credit`/bp.`superblock_expavg_credit`) DESC
LIMIT 100
");

	$claim_today = db_query_to_variable("SELECT 1 FROM `faucet` WHERE DATE_ADD(`date`,INTERVAL 1 DAY)>NOW() AND `user_uid`='$username_uid_escaped'");
	$currency = db_query_to_variable("SELECT `currency` FROM `users` WHERE `uid`='$username_uid_escaped'");

	if($user_magnitude>1 && $claim_today!=1 && ($currency=='GRC' || $currency=='GRC2')) {
		$grc_address=db_query_to_variable("SELECT `payout_address` FROM `users` WHERE `uid`='$username_uid_escaped'");
		if($grc_address == '') return;

		$grc_address_escaped=db_escape($grc_address);

		db_query("INSERT INTO `faucet` (`user_uid`,`grc_amount`,`date`) VALUES ('$username_uid_escaped','$amount_escaped',NOW())");
		db_query("INSERT INTO `faucet_payouts` (`grc_address`,`amount`) VALUES ('$grc_address_escaped','$amount_escaped')");
	}
}

// Get mined balance
function boincmgr_get_balance($user_uid) {
	$user_uid_escaped = db_escape($user_uid);
	$currency = db_query_to_variable("SELECT `currency` FROM `users` WHERE `uid` = '$user_uid_escaped'");
	$balance = db_query_to_variable("SELECT `balance` FROM `users` WHERE `uid` = '$user_uid_escaped'");

	return [
		"currency" => $currency,
		"currency_amount" => $balance,
	];
}

// Recalculate mined balance
function boincmgr_update_balance($user_uid) {
	$prev_balance_data = boincmgr_get_balance($user_uid);

	$user_uid_escaped=db_escape($user_uid);

	// Get user currency
	$currency=db_query_to_variable("SELECT `currency` FROM `users` WHERE `uid`='$user_uid_escaped'");
	$currency_escaped=db_escape($currency);

	// Calculate balance from stats
	$balance=db_query_to_variable("SELECT SUM(bphs.`currency_amount`) AS currency_amount
FROM `project_host_stats` AS bphs
LEFT OUTER JOIN `hosts` AS bh ON bh.`uid`=bphs.`host_uid`
WHERE bh.`username_uid`='$user_uid_escaped' AND `currency`='$currency_escaped' AND `is_payed_out`=0
GROUP BY bphs.`currency`");

	if($balance=='') $balance=0;

	// Not sent yet
	$balance_owed=db_query_to_variable("SELECT SUM(`amount`) FROM `payouts` WHERE `user_uid`='$user_uid_escaped' AND `currency`='$currency_escaped' AND `txid`=''");

	if($balance_owed=='') $balance_owed=0;

	$balance_escaped=db_escape($balance+$balance_owed);

	// Update balance
	db_query("UPDATE `users` SET `balance`='$balance_escaped' WHERE `uid`='$user_uid_escaped'");

	$new_balance_data = boincmgr_get_balance($user_uid);

	auth_log([
		"function" => "boincmgr_update_balance",
		"user_uid" => $user_uid,
		"prev_balance_data" => $prev_balance_data,
		"new_balance_data" => $new_balance_data,
		"balance" => $balance,
		"balance_owed" => $balance_owed,
		]);
	
	return $balance;
}

// For php 5 only variant for random_bytes is openssl_random_pseudo_bytes from openssl lib
if(!function_exists("random_bytes")) {
	function random_bytes($n) {
		return openssl_random_pseudo_bytes($n);
	}
}
