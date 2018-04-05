<?php
require_once("settings.php");
require_once("db.php");
require_once("auth.php");
require_once("html.php");

db_connect();

// Get auth variables from cookies
if(isset($_COOKIE['username'])) $username=$_COOKIE['username'];
else $username="";
if(isset($_COOKIE['passwd_hash'])) $passwd_hash=$_COOKIE['passwd_hash'];
else $passwd_hash="";
if(isset($_COOKIE['action_message'])) {
    $action_message=$_COOKIE['action_message'];
    setcookie("action_message",'');
} else {
    $action_message="";
}


if(isset($_GET['action'])) {
    if($_GET['action']=='guide') {
        echo $message_guide;
        die();
    }
    if($_GET['action']=='pool_info') {
        echo $message_pool_info;
        die();
    }
    if($_GET['action']=='about') {
        echo $message_about;
        die();
    }
    
}

if(auth_check($username,$passwd_hash)) {
    if(isset($_POST['action'])) {
        if($_POST['action']=='change_settings') {
            $email=stripslashes($_POST['email']);
            $grc_address=stripslashes($_POST['grc_address']);
            $password=stripslashes($_POST['password']);
            
            $passwd_hash2=auth_hash($username,$password);
            
            $username_escaped=db_escape($username);
            $email_escaped=db_escape($email);
            $grc_address_escaped=db_escape($grc_address);
            $passwd_hash2_escaped=db_escape($passwd_hash2);
            
            db_query("UPDATE `boincmgr_users` SET `email`='$email_escaped',`grc_address`='$grc_address_escaped' WHERE `username`='$username_escaped' AND `passwd_hash`='$passwd_hash2_escaped'");
            
            header("Location: ./");
            die();
        }
    }
    if(isset($_GET['action'])) {
        if($_GET['action']=='logout') {
            auth_logout();
            setcookie("action_message",$message_logout_success);
            header("Location: ./");
            die();
        }
    }
    
    echo html_page_begin();
    echo html_page_header(array("logout"));

    echo html_change_settings_form();
    
    echo html_user_hosts();
    
    echo html_boinc_results();
    
    echo html_page_end();
} else {
    if(isset($_POST['action'])) {
        if($_POST['action']=="register") {
            $username=stripslashes($_POST['username']);
            $password_1=stripslashes($_POST['password_1']);
            $password_2=stripslashes($_POST['password_2']);
            $email=stripslashes($_POST['email']);
            $result=auth_add_user($username,$email,$password_1,$password_2);
            if($result==FALSE) {
                setcookie("action_message",$message_register_fail);
            } else {
                setcookie("action_message",$message_register_success);
            }
            header("Location: ./");
            die();
        }
        if($_POST['action']=='login') {
            $username=stripslashes($_POST['username']);
            $password=stripslashes($_POST['password']);
            auth_login($username,$password);
            header("Location: ./");
            die();
        }
    }
    
    if($username!="") $action_message=$message_login_error;
    
    echo html_page_begin();
    
    if(isset($_GET['action']) && $_GET['action']=='register') {
        echo html_page_header(array("login"));
        echo html_register_form();
    } else {
        echo html_page_header(array("register"));
        echo html_login_form();
    }
    
    echo html_page_end();
}

?>
