<?php
// Contains authorization and authentification functions

// Costants and messages
define("AUTH_REGISTER_OK",1);
define("AUTH_REGISTER_FAIL_LOGIN",2);
define("AUTH_REGISTER_FAIL_PASSWORD",3);
define("AUTH_REGISTER_FAIL_PASSWORD_MISMATCH",4);
define("AUTH_REGISTER_FAIL_EMAIL",5);
define("AUTH_REGISTER_FAIL_GRC_ADDRESS",6);
define("AUTH_REGISTER_FAIL_DB",7);
define("AUTH_REGISTER_FAIL_USERNAME_EXISTS",8);

$auth_register_result_to_message=array(
        AUTH_REGISTER_OK=>$message_register_success,
        AUTH_REGISTER_FAIL_LOGIN=>$message_register_fail_login,
        AUTH_REGISTER_FAIL_PASSWORD=>$message_register_fail_password,
        AUTH_REGISTER_FAIL_EMAIL=>$message_register_fail_email,
        AUTH_REGISTER_FAIL_GRC_ADDRESS=>$message_register_fail_grc_address,
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

// Check auth
function auth_is_admin($username) {
        $username=strtolower($username);
        $username_escaped=db_escape($username);
        $count=db_query_to_variable("SELECT count(*) FROM `boincmgr_users` WHERE `username`='$username_escaped' AND `status`='admin'");
        if($count==1) return TRUE;
        else return FALSE;
}

// Auth hash check
function auth_check_hash($username,$passwd_hash) {
        $username=strtolower($username);
        $username_escaped=db_escape($username);
        //$passwd_hash_escaped=db_escape($passwd_hash);
        $passwd_hash_salted=auth_hash_salt($username,$passwd_hash);

        $count=db_query_to_variable("SELECT count(*) FROM `boincmgr_users` WHERE `username`='$username_escaped' AND `passwd_hash`='$passwd_hash_salted' AND `status`<>'banned'");
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

// Check GRC address format
function auth_validate_grc_address($grc_address) {
        if(preg_match('/^[A-Za-z0-9]{34,34}$/',$grc_address)) return TRUE;
        else return FALSE;
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

// Register new user
function auth_add_user($username,$email,$password_1,$password_2,$grc_address) {
        // Various checks
        if($password_1 != $password_2) return AUTH_REGISTER_FAIL_PASSWORD_MISMATCH;
        if(auth_validate_username($username)==FALSE) return AUTH_REGISTER_FAIL_LOGIN;
        if(auth_validate_email($email)==FALSE) return AUTH_REGISTER_FAIL_EMAIL;
        if(auth_validate_password($password_1)==FALSE) return AUTH_REGISTER_FAIL_PASSWORD;
        if(auth_validate_grc_address($grc_address)==FALSE) return AUTH_REGISTER_FAIL_GRC_ADDRESS;

        // Escaping and lowercasing
        $username=strtolower($username);
        $username_escaped=db_escape($username);
        $email_escaped=db_escape($email);
        $grc_address_escaped=db_escape($grc_address);
        $password_hash=auth_hash($username,$password_1);

        // Check is username exists
        $username_exists_flag=db_query_to_variable("SELECT 1 FROM `boincmgr_users` WHERE `username`='$username_escaped'");
        if($username_exists_flag) return AUTH_REGISTER_FAIL_USERNAME_EXISTS;

        // Add new user
        auth_log("Register username '$username' mail '$email' grc_address '$grc_address'");
        $salt=bin2hex(random_bytes(16));
        $salt_escaped=db_escape($salt);
        $password_hash_salted=hash("sha256",$password_hash.$salt);

        $result=db_query("INSERT INTO `boincmgr_users` (`username`,`email`,`salt`,`passwd_hash`,`grc_address`,`status`)
VALUES ('$username_escaped','$email_escaped','$salt_escaped','$password_hash_salted','$grc_address_escaped','user')");
        if($result) return AUTH_REGISTER_OK;
        else return AUTH_REGISTER_FAIL_DB;
}

// Change password and other settings
function auth_change_settings($username,$email,$password_1,$password_2,$grc_address) {
        if($password_1 != $password_2) return FALSE;
        if(auth_validate_grc_address($grc_address)==FALSE) return FALSE;
        if($password_1 != '' && auth_validate_password($password_1)==FALSE) return FALSE;

        auth_log("Change settings username '$username' mail '$email' grc_address '$grc_address'");

        $username=strtolower($username);
        $username_escaped=db_escape($username);
        $email_escaped=db_escape($email);
        $grc_address_escaped=db_escape($grc_address);
        if($password_1!='') {
                $password_hash=auth_hash($username,$password_1);
                auth_log("Change password username '$username'");
                $salt=bin2hex(random_bytes(16));
                $salt_escaped=db_escape($salt);
                $result=db_query("UPDATE `boincmgr_users` SET `salt`='$salt_escaped',`email`='$email_escaped',`passwd_hash`='$password_hash',`grc_address`='$grc_address_escaped' WHERE `username`='$username_escaped'");
        } else {
                $result=db_query("UPDATE `boincmgr_users` SET `email`='$email_escaped',`grc_address`='$grc_address_escaped' WHERE `username`='$username_escaped'");
        }
        if($result) return TRUE;
        else return FALSE;
}

// Auth existing user
function auth_login($username,$password) {
        if(auth_validate_username($username)==FALSE) return FALSE;
        if(auth_validate_password($password)==FALSE) return FALSE;

        $passwd_hash=auth_hash($username,$password);

        if(auth_check_hash($username,$passwd_hash)) {
                auth_log("Login username '$username'");
                $auth_cookie=bin2hex(random_bytes(32));
                $username_uid=boincmgr_get_username_uid($username);
                $username_uid_escaped=db_escape($username_uid);
                $auth_cookie_escaped=db_escape($auth_cookie);

                db_query("INSERT INTO `boincmgr_user_auth_cookies` (`username_uid`,`cookie_token`,`expire_date`) VALUES ('$username_uid_escaped','$auth_cookie_escaped',DATE_ADD(NOW(),INTERVAL 2 DAY))");
                setcookie("auth_cookie",$auth_cookie);
                return TRUE;
        } else {
                auth_log("Login failed username '$username'");
                return FALSE;
        }
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
        $salt=db_query_to_variable("SELECT `salt` FROM `boincmgr_users` WHERE `username`='$username_escaped'");
        return hash("sha256",$password_hash.$salt);
}

// Logout
function auth_logout() {
        global $username;
        auth_log("Logout '$username'");
        setcookie("auth_cookie","");
//        setcookie("username","");
//        setcookie("passwd_hash","");
}

// Generate new token and save to db
function auth_get_new_token($username) {
        global $token_salt;

        $username_escaped=db_escape($username);
        $token=md5(uniqid().$token_salt);

        db_query("UPDATE `boincmgr_users` SET `token`='$token' WHERE `username`='$username_escaped'");
        return $token;
}

// Get current user token
function auth_get_current_token($username) {
        $username_escaped=db_escape($username);
        $token=db_query_to_variable("SELECT `token` FROM `boincmgr_users` WHERE `username`='$username_escaped'");
        return $token;
}

// Check token for current user
function auth_check_token($username,$token) {
        $username_escaped=db_escape($username);
        $token_escaped=db_escape($token);
        $count=db_query_to_variable("SELECT 1 FROM `boincmgr_users` WHERE `username`='$username_escaped' AND `token`='$token_escaped'");
        if($count==1) return TRUE;
        else return FALSE;
}

// Write action to log
function auth_log($message) {
        $message_escaped=db_escape($message);
        db_query("INSERT INTO `boincmgr_log` (`message`) VALUES ('$message_escaped')");
}
?>
