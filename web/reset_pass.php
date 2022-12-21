<?php
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/boincmgr.php");
require_once("../lib/auth.php");
require_once("../lib/broker.php");

db_connect();

$hash=isset($_GET['hash'])?$_GET['hash']:'';

$hash_escaped=db_escape($hash);
$username=db_query_to_variable("SELECT `username` FROM `users` WHERE `passwd_hash`='$hash_escaped'");

if($username!='') {
        if(isset($_POST['password'])) {
                $login=stripslashes($_POST['login']);
                if($login!=$username) die("Invalid username");
                $password=stripslashes($_POST['password']);
                $salt=bin2hex(random_bytes(16));
                $salt_escaped=db_escape($salt);
                $password_hash=auth_hash($username,$password);
                $password_hash_salted=hash("sha256",$password_hash.$salt);
                
                $new_password_hash_escaped=db_escape($password_hash_salted);
                db_query("UPDATE `users` SET `passwd_hash`='$new_password_hash_escaped',salt='$salt_escaped' WHERE `passwd_hash`='$hash_escaped'");
                die("Password changed. <a href='./'>Try to log in with new data</a>");
        }
} else {
        die("Unknown token");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Arikado Gridcoin Pool</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="icon" href="favicon.png" type="image/png">
<script src='jquery-3.3.1.min.js'></script>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<h1>Arikado Gridcoin pool password changer</h1>
<form name=change_pass method=post>
<p>Login: <input type=text name=login></p>
<p>New password: <input type=password name=password></p>
<p><input type=submit value='Change password'></p>
</form>

