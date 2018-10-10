<?php
if(!file_exists("../lib/settings.php")) {
        header("Location: setup.php");
        die();
}

require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/html.php");
require_once("../lib/billing.php");
require_once("../lib/boincmgr.php");
require_once("../lib/canvas.php");
require_once("../lib/xml_parser.php");

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
                        $payout_currency=html_strip($_POST['payout_currency']);
                        $payout_address=html_strip($_POST['payout_address']);
                        $password=html_strip($_POST['password']);
                        $new_password1=html_strip($_POST['new_password1']);
                        $new_password2=html_strip($_POST['new_password2']);
                        $send_error_reports=isset($_POST['send_error_reports'])?"1":"0";

                        $result=auth_change_settings($username,$email,$password,$new_password1,$new_password2,$payout_currency,$payout_address,$send_error_reports);
                        if($result==TRUE) {
                                setcookie("action_message",$message_change_settings_ok);
                        } else {
                                setcookie("action_message",$message_change_settings_validation_fail);
                        }
                        // Attach project
                } else if($_POST['action']=='attach') {
                        $project_uid=html_strip($_POST['project_uid']);
                        $host_uid=html_strip($_POST['host_uid']);

                        $attach_result=boincmgr_attach($username,$host_uid,$project_uid);

                        setcookie("action_message",$message_project_attached);
                // Detach project
                } else if($_POST['action']=='detach') {
                        $attached_uid=html_strip($_POST['attached_uid']);
                        $detach_result=boincmgr_detach($username,$attached_uid);

                        setcookie("action_message",$message_project_detached);
                // Update project_settings
                } else if($_POST['action']=='update_project_settings') {
                        $attached_uid=html_strip($_POST['attached_uid']);
                        $resource_share=html_strip($_POST['resource_share']);
                        $options_array=array();
                        if(isset($_POST['detach'])) $options_array[]="detach";
                        if(isset($_POST['detach_when_done'])) $options_array[]="detach_when_done";
                        if(isset($_POST['suspend'])) $options_array[]="suspend";
                        if(isset($_POST['dont_request_more_work'])) $options_array[]="dont_request_more_work";
                        if(isset($_POST['abort_not_started'])) $options_array[]="abort_not_started";
                        if(isset($_POST['no_cpu'])) $options_array[]="no_cpu";
                        if(isset($_POST['no_cuda'])) $options_array[]="no_cuda";
                        if(isset($_POST['no_ati'])) $options_array[]="no_ati";
                        if(isset($_POST['no_intel'])) $options_array[]="no_intel";

                        boincmgr_set_project_settings($username,$attached_uid,$resource_share,$options_array);

                        setcookie("action_message",$message_project_settings_changed);
                // Delete host
                } else if($_POST['action']=='delete_host') {
                        $host_uid=html_strip($_POST['host_uid']);
                        boincmgr_delete_host($username,$host_uid);

                        setcookie("action_message",$message_host_deleted);
                // Send message
                } else if($_POST['action']=='send_message') {
                        $reply_to=html_strip($_POST['reply_to']);
                        $message=html_strip($_POST['message']);

                        boincmgr_message_send($username_uid,$reply_to,$message);

                        setcookie("action_message",$message_message_sent);
                // Claim faucet
                } else if($_POST['action']=='claim_faucet') {
                        boincmgr_claim_faucet($username_uid);

                        setcookie("action_message",$message_faucet_sent);
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
                        // Calculate payouts
                        } else if($_POST['action']=='billing') {
                                $start_date=html_strip($_POST['start_date']);
                                $stop_date=html_strip($_POST['stop_date']);
                                $reward=html_strip($_POST['reward']);
                                $check_rewards=isset($_POST['check_rewards'])?1:0;
                                $project_uid=html_strip($_POST['project_uid']);
                                $comment=html_strip($_POST['comment']);
                                if(isset($_POST['antiexp_rewards_flag'])) $antiexp_rac_flag=TRUE;
                                else $antiexp_rac_flag=FALSE;

                                $antiexp_rac_flag_text=$antiexp_rac_flag==TRUE?"antiexp flag is set":"antiexp flag is not set";

                                if(auth_validate_timestamp($start_date)==FALSE) die("Start date validation error");
                                if(auth_validate_timestamp($stop_date)==FALSE) die("Stop date validation error");
                                if(auth_validate_float($reward)==FALSE) die("Reward validation error");
                                if(auth_validate_integer($project_uid)==FALSE) die("Project validation error");
                                if(auth_validate_ascii($comment)==FALSE) die("Project validation error");

                                if(!$check_rewards) auth_log("Admin billing from '$start_date' to '$stop_date' reward '$reward' comment '$comment' $antiexp_rac_flag_text");
                                else auth_log("Admin check rewards from '$start_date' to '$stop_date' reward '$reward' comment '$comment' $antiexp_rac_flag_text");

                                bill_close_period($comment,$start_date,$stop_date,$reward,$check_rewards,$project_uid,$antiexp_rac_flag);
                                setcookie("action_message",$message_billing_ok);
                        // Edit pool info
                        } else if($_POST['action']=='edit_pool_info') {
                                $pool_info=html_strip($_POST['pool_info']);

                                auth_log("Pool info changed by $username");
                                boincmgr_set_pool_info($pool_info);
                                setcookie("action_message",$message_pool_info_changed);
                        } else if($_POST['action']=='set_txid') {
                                $payout_address=html_strip($_POST['payout_address']);
                                $txid=html_strip($_POST['txid']);

                                auth_log("TX ID sent on address '$payout_address' txid '$txid'");
                                boincmgr_set_txid($payout_address,$txid);
                                setcookie("action_message",$message_pool_txid_set);
                        }
                }
                html_redirect_and_die("./");
        }
        // Logout
        if(isset($_GET['action'])) {
                if($username_token!=$received_token) die($message_bad_token);

                if($_GET['action']=='logout') {
                        auth_logout();
                        setcookie("action_message",$message_logout_success);
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
                html_redirect_and_die("./");
        }

        if(isset($_GET['ajax']) && isset($_GET['block'])) {
                $block=html_strip($_GET['block']);
                switch($block) {
                        case "billing":
                                if(auth_is_admin($username)) echo html_billing_form();
                                break;
                        case "block_explorer":
                                echo html_block_explorer();
                                break;
                        case "boinc_results_by_host":
                                echo html_boinc_results_by_host();
                                break;
                        case "boinc_results_by_project":
                                echo html_boinc_results_by_project();
                                break;
                        case "boinc_results_by_user":
                                echo html_boinc_results_by_user();
                                break;
                        case "boinc_results_all_valuable":
                                echo html_boinc_results_all(1);
                                break;
                        case "boinc_results_all":
                                echo html_boinc_results_all(0);
                                break;
                        case "currencies":
                                echo html_currencies();
                                break;
                        case "email_view":
                                if(auth_is_admin($username)) echo html_email_view();
                                break;
                        case "faucet":
                                echo html_faucet();
                                break;
                        case "log":
                                if(auth_is_admin($username)) echo html_view_log();
                                break;
                        case "message_send":
                                echo html_message_send();
                                break;
                        case "messages_view":
                                echo html_messages_view();
                                break;
                        case "payouts":
                                echo html_payouts();
                                break;
                        case "project_control":
                                if(auth_is_admin($username)) echo html_project_control_form();
                                break;
                        default:
                        case "pool_info":
                                echo html_pool_info();
                                break;
                        case "pool_info_editor":
                                if(auth_is_admin($username)) echo html_pool_info_editor();
                                break;
                        case "pool_stats":
                                echo html_pool_stats();
                                break;
                        case "rating_by_host_mag":
                                echo html_rating_by_host_mag();
                                break;
                        case "rating_by_host_project_mag":
                                echo html_rating_by_host_project_mag();
                                break;
                        case "rating_by_user_mag":
                                echo html_rating_by_user_mag();
                                break;
                        case "rating_by_user_project_mag":
                                echo html_rating_by_user_project_mag();
                                break;
                        case "settings":
                                echo html_change_settings_form();
                                break;
                        case "user_control":
                                if(auth_is_admin($username)) echo html_user_control_form();
                                break;
                        case "your_hosts":
                                echo html_host_options_form();
                                echo html_user_hosts();
                                break;
                }
                die();
        }

        // Standard page beginning
        echo html_page_begin();

        // Menu for registered user
        if(auth_is_admin($username)) {
                echo html_page_header("admin");
        } else {
                echo html_page_header("user");
        }

} else {
        if(isset($_POST['action'])) {
                // Register new user
                if($_POST['action']=="register") {
                        $recaptcha_response=html_strip($_POST['g-recaptcha-response']);
                        if(auth_recaptcha_check($recaptcha_response)) {
                                $username=html_strip($_POST['username']);
                                $password_1=html_strip($_POST['password_1']);
                                $password_2=html_strip($_POST['password_2']);
                                $email=html_strip($_POST['email']);
                                $payout_address=html_strip($_POST['payout_address']);
                                $payout_currency=html_strip($_POST['payout_currency']);
                                $register_result=auth_add_user($username,$email,$password_1,$password_2,$payout_currency,$payout_address);
                                setcookie("action_message",$auth_register_result_to_message[$register_result]);
                        } else {
                                setcookie("action_message",$message_register_recaptcha_error);
                        }
                        header("Location: ./");
                        die();
                // Login existing user
                } else if($_POST['action']=='login') {
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
                // Send message
                } else if($_POST['action']=='send_message') {
                        $username_uid='';
                        $reply_to=html_strip($_POST['reply_to']);
                        $message=html_strip($_POST['message']);

                        boincmgr_message_send($username_uid,$reply_to,$message);

                        setcookie("action_message",$message_message_sent);
                        header("Location: ./");
                        die();
                }
        }

        if(isset($_GET['ajax']) && isset($_GET['block'])) {
                $block=html_strip($_GET['block']);
                switch($block) {
                        case "block_explorer":
                                echo html_block_explorer();
                                break;
                        case "currencies":
                                echo html_currencies();
                                break;
                        default:
                        case "login_form":
                                echo html_login_form();
                                break;
                        case "message_send":
                                echo html_message_send();
                                break;
                        case "payouts":
                                echo html_payouts();
                                break;
                        case "pool_info":
                                echo html_pool_info();
                                break;
                        case "pool_stats":
                                echo html_pool_stats();
                                break;
                        case "rating_by_host_mag":
                                echo html_rating_by_host_mag();
                                break;
                        case "rating_by_host_project_mag":
                                echo html_rating_by_host_project_mag();
                                break;
                        case "rating_by_user_mag":
                                echo html_rating_by_user_mag();
                                break;
                        case "rating_by_user_project_mag":
                                echo html_rating_by_user_project_mag();
                                break;
                        case "register_form":
                                echo html_register_form();
                                break;
                }
                die();
        }

        // Standard page begin
        echo html_page_begin();

        // For register form we have link to login, then register form
        echo html_page_header("unknown");

}

// Block for ajax contents
echo html_loadable_block();

// Standard page end
echo html_page_end();

?>
