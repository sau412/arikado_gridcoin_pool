<?php
// Contains authorization and authentification functions

// Costants and messages
define("AUTH_REGISTER_OK",1);
define("AUTH_REGISTER_FAIL_LOGIN",2);
define("AUTH_REGISTER_FAIL_PASSWORD",3);
define("AUTH_REGISTER_FAIL_PASSWORD_MISMATCH",4);
define("AUTH_REGISTER_FAIL_EMAIL",5);
define("AUTH_REGISTER_FAIL_PAYOUT_ADDRESS",6);
define("AUTH_REGISTER_FAIL_PAYOUT_CURRENCY",7);
define("AUTH_REGISTER_FAIL_DB",8);
define("AUTH_REGISTER_FAIL_USERNAME_EXISTS",9);

$auth_register_result_to_message=array(
	AUTH_REGISTER_OK=>$message_register_success,
	AUTH_REGISTER_FAIL_LOGIN=>$message_register_fail_login,
	AUTH_REGISTER_FAIL_PASSWORD=>$message_register_fail_password,
	AUTH_REGISTER_FAIL_EMAIL=>$message_register_fail_email,
	AUTH_REGISTER_FAIL_PAYOUT_ADDRESS=>$message_register_fail_payout_address,
	AUTH_REGISTER_FAIL_PAYOUT_CURRENCY=>$message_register_fail_payout_currency,
	AUTH_REGISTER_FAIL_DB=>$message_register_fail_db,
	AUTH_REGISTER_FAIL_USERNAME_EXISTS=>$message_register_fail_username_exists,
);

// Check auth
function auth_check() {
	if(isset($_COOKIE['username'])) $username=$_COOKIE['username'];
	else return FALSE;
	if(isset($_COOKIE['passwd_hash'])) $passwd_hash=$_COOKIE['passwd_hash'];
	else return FALSE;

	return auth_check_hash($username,$passwd_hash);
}

// Checks is user admin
function auth_is_admin($username) {
	$username_escaped=db_escape($username);
	$admin_exists=db_query_to_variable("SELECT 1 FROM `users` WHERE LOWER(`username`)=LOWER('$username_escaped') AND `status`='admin'");
	if($admin_exists==1) return TRUE;
	else return FALSE;
}

// Checks is user can view everything (for translation purposes)
function auth_is_editor($username) {
	$username_escaped=db_escape($username);
	$right_exists=db_query_to_variable("SELECT 1 FROM `users` WHERE LOWER(`username`)=LOWER('$username_escaped') AND `status`='editor'");
	if($right_exists==1) return TRUE;
	else return FALSE;
}

// Auth hash check
function auth_check_hash($username,$passwd_hash) {
//	$username=strtolower($username);
	$username_escaped=db_escape($username);
	//$passwd_hash_escaped=db_escape($passwd_hash);
	$passwd_hash_salted=auth_hash_salt($username,$passwd_hash);

	$count=db_query_to_variable("SELECT count(*) FROM `users` WHERE LOWER(`username`)=LOWER('$username_escaped') AND `passwd_hash`='$passwd_hash_salted' AND `status`<>'banned'");
	if($count==1) return TRUE;
	else return FALSE;
}

// Check username format
function auth_validate_username($username) {
	if(preg_match('/^[-A-Za-z0-9_.]{1,100}$/',$username)) return TRUE;
	else return FALSE;
}

// Check email format
function auth_validate_email($email) {
	if(preg_match('/^.{0,100}$/',$email)) return TRUE;
	else return FALSE;
}

// Check password format
function auth_validate_password($password) {
	global $pool_min_password_length;
	if(strlen($password)>=$pool_min_password_length && strlen($password)<=100) return TRUE;
	else return FALSE;
}

// Check payout address format
function auth_validate_payout_address($payout_address) {
	if(auth_validate_ascii($payout_address)) {
		if(strlen($payout_address) > 0) return TRUE;
	}
	return FALSE;
	//if(preg_match('/^[A-Za-z0-9]{34,34}$/',$grc_address)) return TRUE;
	//else return FALSE;
}

// Check string has only ASCII characters
function auth_validate_ascii($string) {
	if(strlen($string)>100) return FALSE;
	for($i=0;$i!=strlen($string);$i++) {
		if(ord($string[$i])<32 || ord($string[$i])>127) return FALSE;
	}
	return TRUE;
}

// Check string is domain name
function auth_validate_domain($string) {
	if(strlen($string)>100) return FALSE;
	return TRUE;
}

// Check string is integer
function auth_validate_integer($number) {
	if(preg_match('/^[0-9]{1,100}$/',$number)) return TRUE;
	else return FALSE;
}

// Check string is float (not for exp format)
function auth_validate_float($number) {
	if(preg_match('/^[0-9.eE]{1,100}$/',$number)) return TRUE;
	else return FALSE;
}

// Check string is md5 hash
function auth_validate_hash($hash) {
	if(preg_match('/^[0-9a-fA-F]{32,32}$/',$hash)) return TRUE;
	else return FALSE;
}

// Check string is timestamp
function auth_validate_timestamp($timestamp) {
	if(preg_match('/^[0-9]{4,4}-[0-9]{1,2}-[0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}$/',$timestamp)) return TRUE;
	else return FALSE;
}

// Check auth_cookie
function auth_validate_auth_cookie($auth_cookie) {
	if(preg_match('/^[0-9a-fA-F]{64,64}$/',$auth_cookie)) return TRUE;
	else return FALSE;
}

// Check payout currency
function auth_validate_payout_currency($currency) {
	$currency_escaped=db_escape($currency);
	$exists=db_query_to_variable("SELECT 1 FROM `currency` WHERE `name`='$currency_escaped'");
	if($exists) return TRUE;
	else return FALSE;
	$valid_currency_array=array("GRC","DOGE","ETH","BTC","LTC");
	if(in_array($currency,$valid_currency_array)) return TRUE;
	else return FALSE;
}

// Register new user
function auth_add_user($username,$email,$password_1,$password_2,$payout_currency,$payout_address) {
	// Various checks
	if($password_1 != $password_2) return AUTH_REGISTER_FAIL_PASSWORD_MISMATCH;
	if(auth_validate_username($username)==FALSE) return AUTH_REGISTER_FAIL_LOGIN;
	if(auth_validate_email($email)==FALSE) return AUTH_REGISTER_FAIL_EMAIL;
	if(auth_validate_password($password_1)==FALSE) return AUTH_REGISTER_FAIL_PASSWORD;
	if(auth_validate_payout_currency($payout_currency)==FALSE) return AUTH_REGISTER_FAIL_PAYOUT_CURRENCY;
	if(auth_validate_payout_address($payout_address)==FALSE) return AUTH_REGISTER_FAIL_PAYOUT_ADDRESS;

	// Escaping
	$username_escaped=db_escape($username);
	$email_escaped=db_escape($email);
	$payout_address_escaped=db_escape($payout_address);
	$payout_currency_escaped=db_escape($payout_currency);
	$password_hash=auth_hash($username,$password_1);

	// Check is username exists
	$username_exists_flag=db_query_to_variable("SELECT 1 FROM `users` WHERE LOWER(`username`)=LOWER('$username_escaped')");
	if($username_exists_flag) return AUTH_REGISTER_FAIL_USERNAME_EXISTS;

	// Add new user
	auth_log("Register username '$username' mail '$email' payout_currency '$payout_currency' payout_address '$payout_address'");
	$salt=bin2hex(random_bytes(16));
	$salt_escaped=db_escape($salt);
	$password_hash_salted=hash("sha256",$password_hash.$salt);

	$result=db_query("INSERT INTO `users` (`username`,`email`,`salt`,`passwd_hash`,`currency`,`payout_address`,`status`)
VALUES ('$username_escaped','$email_escaped','$salt_escaped','$password_hash_salted','$payout_currency_escaped','$payout_address_escaped','user')");
	if($result) return AUTH_REGISTER_OK;
	else return AUTH_REGISTER_FAIL_DB;
}

// Change password and other settings
function auth_change_settings($username,$email,$current_password,$new_password_1,$new_password_2,$payout_currency,$payout_address,$send_error_reports) {
	if(auth_validate_password($current_password)==FALSE) return FALSE;

	if($new_password_1 != $new_password_2) return FALSE;
	if(auth_validate_payout_address($payout_address)==FALSE) return FALSE;
	if(auth_validate_payout_currency($payout_currency)==FALSE) return FALSE;
	if($new_password_1 != '' && auth_validate_password($new_password_1)==FALSE) return FALSE;
	$send_error_reports_text=$send_error_reports?"yes":"no";

	auth_log("Change settings username '$username' mail '$email' payout_currency '$payout_currency' payout_address '$payout_address' email_error_reports '$send_error_reports_text'");

	//$username=strtolower($username);
	$username_escaped=db_escape($username);
	$email_escaped=db_escape($email);
	$payout_address_escaped=db_escape($payout_address);
	$payout_currency_escaped=db_escape($payout_currency);
	$send_error_reports_escaped=$send_error_reports?"1":"0";

	$salt=db_query_to_variable("SELECT `salt` FROM `users` WHERE `username`='$username_escaped'");
	$password_hash=auth_hash($username,$current_password);
	$password_hash_salted=hash("sha256",$password_hash.$salt);
	$password_user_match=db_query_to_variable("SELECT 1 FROM `users` WHERE `username`='$username_escaped' AND `passwd_hash`='$password_hash_salted'");
	if($password_user_match==FALSE) return FALSE;

	if($new_password_1!='') {
		$password_hash=auth_hash($username,$new_password_1);
		auth_log("Change password username '$username'");
		$salt=bin2hex(random_bytes(16));
		$salt_escaped=db_escape($salt);
		$password_hash_salted=hash("sha256",$password_hash.$salt);
		$result=db_query("UPDATE `users` SET `send_error_reports`='$send_error_reports_escaped',`salt`='$salt_escaped',`email`='$email_escaped',`passwd_hash`='$password_hash_salted',`currency`='$payout_currency_escaped',`payout_address`='$payout_address_escaped' WHERE `username`='$username_escaped'");
	} else {
		$result=db_query("UPDATE `users` SET `email`='$email_escaped',`send_error_reports`='$send_error_reports_escaped',`currency`='$payout_currency_escaped',`payout_address`='$payout_address_escaped' WHERE `username`='$username_escaped'");
	}

	// Update user balance - required when changing currency
	$user_uid=boincmgr_get_username_uid($username);
	boincmgr_update_balance($user_uid);

	if($result) return TRUE;
	else return FALSE;
}

// Auth existing user
function auth_login($auth_cookie,$username,$password) {
	if(auth_validate_username($username)==FALSE) return FALSE;
	if(auth_validate_password($password)==FALSE) return FALSE;

	$passwd_hash=auth_hash($username,$password);

	if(auth_check_hash($username,$passwd_hash)) {
		auth_log("Login username '$username'");
		//$auth_cookie=bin2hex(random_bytes(32));
		$username_uid=boincmgr_get_username_uid($username);
		$username_uid_escaped=db_escape($username_uid);
		$auth_cookie_escaped=db_escape($auth_cookie);

		db_query("UPDATE `user_auth_cookies` SET `username_uid`='$username_uid_escaped' WHERE `cookie_token`='$auth_cookie_escaped'");
//echo "UPDATE `user_auth_cookies` SET `username_uid`='$username_uid_escaped' WHERE `cookie_token`='$auth_cookie_escaped'";
		//setcookie("auth_cookie",$auth_cookie,time()+30*24*60*60); // 30 days
		return TRUE;
	} else {
		auth_log("Login failed username '$username'");
		return FALSE;
	}
}

// Generate auth cookie
function auth_cookie() {
	if(isset($_COOKIE['auth_cookie']) && auth_validate_auth_cookie($_COOKIE['auth_cookie'])) {
		$auth_cookie=html_strip($_COOKIE['auth_cookie']);
		$auth_cookie_escaped=db_escape($auth_cookie);
		$exists=db_query_to_variable("SELECT 1 FROM `user_auth_cookies` WHERE `cookie_token`='$auth_cookie_escaped'");
		if($exists) {
			db_query("UPDATE `user_auth_cookies` SET `expire_date`=DATE_ADD(NOW(),INTERVAL 2 DAY) WHERE `cookie_token`='$auth_cookie_escaped'");
			return $auth_cookie;
		}
	}
	$auth_cookie=bin2hex(random_bytes(32));
	$auth_cookie_escaped=db_escape($auth_cookie);
	db_query("INSERT INTO `user_auth_cookies` (`cookie_token`,`expire_date`) VALUES ('$auth_cookie_escaped',DATE_ADD(NOW(),INTERVAL 2 DAY))");
	setcookie("auth_cookie",$auth_cookie,time()+30*24*60*60); // 30 days
	return $auth_cookie;
}

// Password to hash
// Like BOINC, md5($password.lowecase($username))
function auth_hash($username,$password) {
	return md5($password.strtolower($username));
}

// Hash password with username salt for check
function auth_hash_salt($username,$password_hash) {
	$username_escaped=db_escape($username);
	$password_hash_escaped=db_escape($password_hash);
	$salt=db_query_to_variable("SELECT `salt` FROM `users` WHERE `username`='$username_escaped'");
//auth_log("Debug: username '$username' hash '$hash' salt '$salt' salted hash '".hash("sha256",$password_hash.$salt)."'");
	return hash("sha256",$password_hash.$salt);
}

// Logout
function auth_logout() {
	global $username;
	auth_log("Logout '$username'");
	setcookie("auth_cookie","");
//	setcookie("username","");
//	setcookie("passwd_hash","");
}

// Generate new token and save to db
function auth_get_new_token($username) {
	global $token_salt;

	$username_escaped=db_escape($username);
	$token=md5(uniqid().$token_salt);

	db_query("UPDATE `users` SET `token`='$token' WHERE `username`='$username_escaped'");
	return $token;
}

// Get current user token
function auth_get_current_token($username) {
	$username_escaped=db_escape($username);
	$token=db_query_to_variable("SELECT `token` FROM `users` WHERE `username`='$username_escaped'");
	return $token;
}

// Check token for current user
function auth_check_token($username,$token) {
	$username_escaped=db_escape($username);
	$token_escaped=db_escape($token);
	$count=db_query_to_variable("SELECT 1 FROM `users` WHERE `username`='$username_escaped' AND `token`='$token_escaped'");
	if($count==1) return TRUE;
	else return FALSE;
}

// Write action to log
function auth_log($message, $severity = 7) {
	global $project_log_name;
	
	broker_add("logger", [
		"source" => $project_log_name,
		"severity" => $severity,
		"message" => $message,
	]);
}

// Write debug log
//function auth_log_debug($type,$message) {
//	$message_escaped=db_escape($message);
//	db_query("INSERT INTO `log` (`message`) VALUES ('$message_escaped')");
//}

function auth_recaptcha_check($response) {
	global $recaptcha_private_key;
	$recaptcha_url="https://www.google.com/recaptcha/api/siteverify";
	$query="secret=$recaptcha_private_key&response=$response&remoteip=".$_SERVER['REMOTE_ADDR'];
	$ch=curl_init();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
	curl_setopt($ch,CURLOPT_POST,TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$query);
	curl_setopt($ch,CURLOPT_URL,$recaptcha_url);
	$result = curl_exec ($ch);
	$data = json_decode($result);
	if($data->success) return TRUE;
	else return FALSE;
}
?>
