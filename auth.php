<?php
// Check auth
function auth_check() {
    if(isset($_COOKIE['username'])) $username=$_COOKIE['username'];
    else return FALSE;
    if(isset($_COOKIE['passwd_hash'])) $passwd_hash=$_COOKIE['passwd_hash'];
    else return FALSE;
    
    $username_escaped=db_escape($username);
    $passwd_hash_escaped=db_escape($passwd_hash);
    
    $count=db_query_to_variable("SELECT count(*) FROM `boincmgr_users` WHERE `username`='$username_escaped' AND `passwd_hash`='$passwd_hash_escaped'");
    if($count==1) return TRUE;
    else return FALSE;
}

// Register new user
function auth_add_user($username,$email,$password_1,$password_2) {
    if($password_1 != $password_2) return FALSE;
    $username_escaped=db_escape($username);
    $email_escaped=db_escape($email);
    $password_hash=auth_hash($username,$password_1);
    $result=db_query("INSERT INTO `boincmgr_users` (`username`,`email`,`passwd_hash`) VALUES ('$username_escaped','$email_escaped','$password_hash')");
    if($result) return TRUE;
    else return FALSE;
}

// Auth existing user
function auth_login($username,$password) {
    $passwd_hash=auth_hash($username,$password);
    
    setcookie("username",$username);
    setcookie("passwd_hash",$passwd_hash);
}

// Password to hash
function auth_hash($username,$password) {
    return md5($password.$username);
}

// Logout
function auth_logout() {
    setcookie("username","");
    setcookie("passwd_hash","");
}
?>
