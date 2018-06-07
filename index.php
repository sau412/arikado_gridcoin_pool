<?php
require_once("settings.php");
require_once("db.php");
require_once("auth.php");
require_once("html.php");
require_once("billing.php");
require_once("boincmgr.php");
require_once("canvas.php");

db_connect();

// Get auth_cookie from cookies and check authentification
if(isset($_COOKIE['auth_cookie']) && auth_validate_auth_cookie($_COOKIE['auth_cookie'])) {
        $auth_cookie=html_strip($_COOKIE['auth_cookie']);
        $auth_cookie_escaped=db_escape($auth_cookie);
        $username_uid=db_query_to_variable("SELECT `username_uid` FROM `boincmgr_user_auth_cookies` WHERE `cookie_token`='$auth_cookie_escaped' AND `expire_date`>CURRENT_TIMESTAMP");
        if($username_uid) {
                $username=boincmgr_get_user_name($username_uid);
                db_query("UPDATE `boincmgr_user_auth_cookies` SET `expire_date`=DATE_ADD(NOW(),INTERVAL 2 DAY) WHERE `cookie_token`='$auth_cookie_escaped'");
        } else {
                $username="";
        }
} else {
        $username="";
}

// Check if action message exists
if(isset($_COOKIE['action_message'])) {
        $action_message=html_strip($_COOKIE['action_message']);
        setcookie("action_message",'');
} else {
        $action_message="";
}

// Get token from variables and from db
$username_token=auth_get_current_token($username);
if(isset($_GET['token']) && auth_validate_hash($_GET['token'])) $received_token=html_strip($_GET['token']);
else if(isset($_POST['token']) && auth_validate_hash($_POST['token'])) $received_token=html_strip($_POST['token']);
else $received_token="";

// Branch for registered user
if($username!="") {
        if(isset($_POST['action'])) {
                if($username_token!=$received_token) die($message_bad_token);

                // Change settings
                if($_POST['action']=='change_settings') {
                        $email=html_strip($_POST['email']);
                        $grc_address=html_strip($_POST['grc_address']);
                        $password=html_strip($_POST['password']);
                        $new_password1=html_strip($_POST['new_password1']);
                        $new_password2=html_strip($_POST['new_password2']);

                        $result=auth_change_settings($username,$email,$password,$new_password1,$new_password2,$grc_address);
                        if($result==TRUE) {
                                setcookie("action_message",$message_change_settings_ok);
                        } else {
                                setcookie("action_message",$message_change_settings_validation_fail);
                        }
                        header("Location: ./");
                        die();
                        // Attach project
                } else if($_POST['action']=='attach') {
                        $project_uid=html_strip($_POST['project_uid']);
                        $host_uid=html_strip($_POST['host_uid']);

                        $attach_result=boincmgr_attach($username,$host_uid,$project_uid);

                        setcookie("action_message",$message_project_attached);
                        header("Location: ./");
                        die();
                // Detach project
                } else if($_POST['action']=='detach') {
                        $attached_uid=html_strip($_POST['attached_uid']);
                        $detach_result=boincmgr_detach($username,$attached_uid);

                        setcookie("action_message",$message_project_detached);
                        header("Location: ./");
                        die();
                // Delete host
                } else if($_POST['action']=='delete_host') {
                        $host_uid=html_strip($_POST['host_uid']);
                        boincmgr_delete_host($username,$host_uid);

                        setcookie("action_message",$message_host_deleted);
                        header("Location: ./");
                        die();
                // Next actions for admins
                } else if(auth_is_admin($username)) {
                        // Change user status (for admin)
                        if($_POST['action']=='change_user_status') {
                                $user_uid=html_strip($_POST['user_uid']);
                                $status=html_strip($_POST['status']);

                                if(auth_validate_ascii($status)==FALSE) die("Status validation error");

                                $username=boincmgr_get_user_name($user_uid);
                                auth_log("Admin change user status user '$username' status '$status'");

                                $user_uid_escaped=db_escape($user_uid);
                                $status_escaped=db_escape($status);
                                db_query("UPDATE `boincmgr_users` SET `status`='$status_escaped' WHERE `uid`='$user_uid_escaped'");
                                setcookie("action_message",$message_user_status_changed);
                                header("Location: ./");
                                die();
                        // Change project_status (for admin)
                        } else if($_POST['action']=='change_project_status') {
                                $project_uid=html_strip($_POST['project_uid']);
                                $status=html_strip($_POST['status']);

                                if(auth_validate_ascii($status)==FALSE) die("Status validation error");

                                $project_name=boincmgr_get_project_name($project_uid);
                                auth_log("Admin change project status project '$project_name' status '$status'");

                                $project_uid_escaped=db_escape($project_uid);
                                $status_escaped=db_escape($status);
                                db_query("UPDATE `boincmgr_projects` SET `status`='$status_escaped' WHERE `uid`='$project_uid_escaped'");
                                setcookie("action_message",$message_project_status_changed);
                                header("Location: ./");
                                die();
                        // Calculate payouts
                        } else if($_POST['action']=='billing') {
                                $start_date=html_strip($_POST['start_date']);
                                $stop_date=html_strip($_POST['stop_date']);
                                $reward=html_strip($_POST['reward']);
                                $check_rewards=html_strip($_POST['check_rewards']);

                                if(auth_validate_timestamp($start_date)==FALSE) die("Start date validation error");
                                if(auth_validate_timestamp($stop_date)==FALSE) die("Stop date validation error");
                                if(auth_validate_float($reward)==FALSE) die("Reward validation error");

                                if(!$check_rewards) auth_log("Admin billing from '$start_date' to '$stop_date' reward '$reward'");
                                else auth_log("Admin check rewards from '$start_date' to '$stop_date' reward '$reward'");

                                bill_close_period($start_date,$stop_date,$reward,$check_rewards);
                                setcookie("action_message",$message_billing_ok);
                                header("Location: ./");
                                die();
                        // Edit pool info
                        } else if($_POST['action']=='edit_pool_info') {
                                $pool_info=html_strip($_POST['pool_info']);

                                auth_log("Pool info changed by $username");
                                boincmgr_set_pool_info($pool_info);
                                setcookie("action_message",$message_pool_info_changed);

                                header("Location: ./");
                                die();
                        }
                }
        }
        // Logout
        if(isset($_GET['action'])) {
                if($username_token!=$received_token) die($message_bad_token);

                if($_GET['action']=='logout') {
                        auth_logout();
                        setcookie("action_message",$message_logout_success);
                        header("Location: ./");
                        die();
                } else if($_GET['action']=='view_host_last_query') {
                        $host_uid=$_GET['host_uid'];
                        $host_uid_escaped=db_escape($host_uid);
                        $result_encoded=db_query_to_variable("SELECT `last_query` FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped'");
                        echo "<pre><tt>";
                        echo html_escape(base64_decode($result_encoded));
                        echo "</tt></pre>";
                        die();
                } else if($_GET['action']=='view_project_last_query') {
                        $project_uid=$_GET['project_uid'];
                        $result=boincmgr_project_last_query_get($project_uid);
                        echo "<pre><tt>";
                        echo html_escape($result);
                        echo "</tt></pre>";
                        die();
                }
        }

        // Standard page beginning
        echo html_page_begin();

        // Menu for registered user
        if(auth_is_admin($username)) {
                echo html_page_header("admin");
        } else {
                echo html_page_header("user");
        }

        // Admin menu
        if(auth_is_admin($username)) {
                // Grant user privelegies
                echo html_user_control_form();

                // Control projects
                echo html_project_control_form();

                // Calculate rewards
                echo html_billing_form();

                // View log
                echo html_view_log();

                // Pool info editor
                echo html_pool_info_editor();
        }

        // Pool info
        echo html_pool_info();

        // Change settings form
        echo html_change_settings_form();

        // Current user hosts
        echo html_user_hosts();

        // Current user BOINC results (for his hosts)
        echo html_boinc_results();

        // Payouts
        echo html_payouts();

        // Pool stats
        echo html_pool_stats();

        // Standard page end
        echo html_page_end();
} else {
        if(isset($_POST['action'])) {
                // Register new user
                if($_POST['action']=="register") {
                        $username=html_strip($_POST['username']);
                        $password_1=html_strip($_POST['password_1']);
                        $password_2=html_strip($_POST['password_2']);
                        $email=html_strip($_POST['email']);
                        $grc_address=html_strip($_POST['grc_address']);
                        $register_result=auth_add_user($username,$email,$password_1,$password_2,$grc_address);
                        setcookie("action_message",$auth_register_result_to_message[$register_result]);
                        header("Location: ./");
                        die();
                }
                // Login existing user
                if($_POST['action']=='login') {
                        $username=html_strip($_POST['username']);
                        $password=html_strip($_POST['password']);
                        $login_result=auth_login($username,$password);

                        if($login_result==TRUE) {
                                auth_get_new_token($username);
                        } else {
                                setcookie("action_message",$message_login_fail);
                        }
                        header("Location: ./");
                        die();
                }
        }

        // Standard page begin
        echo html_page_begin();

        // For register form we have link to login, then register form
        echo html_page_header("unknown");

        // Pool info
        echo html_pool_info();

        // Login form
        echo html_login_form();

        // Register form
        echo html_register_form();

        // Payouts
        echo html_payouts();

        // Pool stats
        echo html_pool_stats();

        // End page
        echo html_page_end();
}

?>
