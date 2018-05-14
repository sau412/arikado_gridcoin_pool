<?php
// HTML-returning functions

// Unescape strings from POST, COOKIES and GET
function html_strip($variable) {
    //if(is_array($var)) return "";
    //if(!preg_match('/^[-0-9A-Za-z!@#$%^&*().\\/]*$/',$var)) return "";
    $variable=(string)stripslashes($variable);
    return $variable;
}

// Begin HTML page
function html_page_begin() {
    global $pool_name;
    return <<<_END
<!DOCTYPE html>
<html>
<head>
<title>$pool_name</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="common.css">
<script src="common.js"></script>
</head>
<body>

_END;
}

// End html page
function html_page_end() {
    echo <<<_END
<script>
var hash = window.location.hash.substr(1);
if(hash != null && hash != '') {
        show_block(hash);
} else {
        show_block('pool_info');
}
</script>
</body>
</html>

_END;
}

// Message in p tags
function html_p($message) {
    return "<p>$message</p>\n";
}

// Return project name as url
function html_project_name_link($project_name,$project_url) {
        $project_name_html=htmlspecialchars($project_name);
        return "<a href='$project_url'>$project_name</a>";
}

// Return grc address as URL
function html_grc_address_link($grc_address) {
        $grc_address_html=htmlspecialchars($grc_address);
        return "<a href='https://www.gridcoinstats.eu/address/$grc_address'>$grc_address</a>";
}

// Return txid as URL
function html_txid_link($txid) {
        return "<a href='https://www.gridcoinstats.eu/tx/$txid'>$txid</a>";
}


// User menu and options
function html_page_header($flags_array) {
    global $action_message;
    global $pool_name;

    $greeting_user_text=html_greeting_user();
    $greeting_user_message=html_p($greeting_user_text);

    $menu=html_get_menu($flags_array);

    if($action_message!='') {
        $action_message_html=htmlspecialchars($action_message);
        $action_message_result=html_p($action_message_html);
    } else {
        $action_message_result="";
    }
    return <<<_END
<h1>$pool_name</h1>
$greeting_user_message
$action_message_result
$menu
_END;
}

// Menu
function html_get_menu($flag) {
        global $username_token;

        $result="";
        $result.="<ul>\n";

        $result.=html_menu_element("pool_info","Pool info");

        if($flag=="unknown") {
                $result.=html_menu_element("login_form","Login");
                $result.=html_menu_element("register_form","Register");
                $result.=html_menu_element("payouts","Payouts");
                $result.=html_menu_element("pool_stats","Pool stats");
        } else {
                $result.=html_menu_element("settings","Settings");
                $result.=html_menu_element("your_hosts","Your hosts");
                $result.=html_menu_element("boinc_results","BOINC results");
                $result.=html_menu_element("payouts","Payouts");
                $result.=html_menu_element("pool_stats","Pool stats");
                //$result.=html_menu_element("your_stats","Your stats");
                if($flag=="admin") {
                        $result.=html_menu_element("user_control","User control");
                        $result.=html_menu_element("project_control","Project control");
                        $result.=html_menu_element("billing","Billing");
                        $result.=html_menu_element("log","View log");
                }
        }
        $result.="</ul>\n";
        return $result;
}

// List element for menu
function html_menu_element($tag,$text) {
        return "<li><a href='#$tag' onClick='return show_block(\"$tag\");'>$text</a></li>\n";
}

// Greeting for user
function html_greeting_user() {
    global $username,$username_token;

    if($username!='') {
        $username_html=htmlspecialchars($username);
        return "Welcome, $username_html (<a href='./?action=logout&token=$username_token'>logout</a>)";
    } else {
        return "Hello, stranger";
    }
}

// Pool info
function html_pool_info() {
        global $pool_name,$message_pool_info;

        return <<<_END
<div id=pool_info class=selectable_block>
<h2>Pool info</h2>
<p>Welcome to $pool_name</p>
$message_pool_info
</div>

_END;
}

// Register form
function html_register_form() {
    return <<<_END
<div id=register_form class=selectable_block>
<form name=register_form method=POST>
<h2>Register</h2>
<p>Username: <input type=text name=username> required</p>
<p>Password 1: <input type=password name=password_1> required</p>
<p>Password 2: <input type=password name=password_2> re-type password</p>
<p>E-mail: <input type=text name=email> for password recovery (you can write me, and I send you new password for account)</p>
<p>GRC address: <input type=text name=grc_address> required</p>
<p><input type=hidden name="action" value="register"></p>
<p><input type=submit value="Register"></p>
</form>
</div>

_END;
}

// Login form
function html_login_form() {
    return <<<_END
<div id=login_form class=selectable_block>
<form name=login_form method=POST>
<h2>Login</h2>
<p>Username: <input type=text name=username></p>
<p>Password: <input type=password name=password></p>
<p><input type=hidden name="action" value="login"></p>
<p><input type=submit value="Login"></p>
</form>
</div>

_END;
}

// Change settings form
function html_change_settings_form() {
    global $username,$username_token;

    $username_escaped=db_escape($username);

    $email=db_query_to_variable("SELECT `email` FROM `boincmgr_users` WHERE `username`='$username_escaped'");
    $grc_address=db_query_to_variable("SELECT `grc_address` FROM `boincmgr_users` WHERE `username`='$username_escaped'");

    $email_html=htmlspecialchars($email);
    $grc_address_html=htmlspecialchars($grc_address);

    return <<<_END
<div id=settings class=selectable_block>
<h2>Settings</h2>
<form name=change_settings_form method=POST>
<p><input type=hidden name="action" value="change_settings"></p>
<p><input type=hidden name="token" value="$username_token"></p>
<p>E-mail: <input type=text name=email value='$email_html'></p>
<p>GRC address: <input type=text name=grc_address value='$grc_address_html'></p>
<p>Password: <input type=password name=password> the password is required to change settings</p>
<p>New password: <input type=password name=new_password1> only if you wish to change password</p>
<p>New password: <input type=password name=new_password2></p>
<p><input type=submit value="Update"></p>
</form>
</div>

_END;
}

// Show user hosts
function html_user_hosts() {
        global $username,$username_token;

        $result="";
        $result.="<div id=your_hosts class=selectable_block>\n";
        $result.="<h2>Your hosts</h2>\n";
        $result.="<p>That information will be synced to your BOINC client. Sync second time after 10-20 minutes to avoid incomplete sync. If you sync correctly, then you see your host in BOINC results after 1-3 hours.</p>\n";
        $result.="<table>\n";
        $result.="<tr><th>Domain name</th><th>CPU</th><th>Projects</th></tr>\n";

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);
        $hosts_array=db_query_to_array("SELECT `uid`,`internal_host_cpid`,`external_host_cpid`,`domain_name`,`p_model` FROM `boincmgr_hosts` WHERE `username_uid`='$username_uid_escaped'");

        foreach($hosts_array as $host) {
                $host_uid=$host['uid'];
                $host_cpid=$host['external_host_cpid'];
                $internal_host_cpid=$host['internal_host_cpid'];
                $domain_name=$host['domain_name'];
                $p_mode=$host['p_model'];

                $host_cpid_html=htmlspecialchars($host_cpid);
                $domain_name_html=htmlspecialchars($domain_name);
                $p_mode_html=htmlspecialchars($p_mode);

                $host_uid_escaped=db_escape($host_uid);

                $attached_projects_array=db_query_to_array("SELECT bap.`uid`,bap.`host_uid`,bp.`uid` as project_uid,bp.`name`,bap.`detach`,bhp.`host_id` FROM `boincmgr_attach_projects` AS bap
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bap.`project_uid`
LEFT JOIN `boincmgr_host_projects` AS bhp ON bhp.`project_uid`=bap.`project_uid` AND bhp.`host_uid`=bap.`host_uid`
WHERE bap.host_uid='$host_uid_escaped' AND bap.`detach`=0 ORDER BY bp.`name` ASC");

                $projects_str="";
                foreach($attached_projects_array as $project_data) {
                        $attached_project_uid=$project_data['uid'];
                        $host_uid=$project_data['host_uid'];
                        $host_id=$project_data['host_id'];
                        $project_name=$project_data['name'];
                        $project_uid=$project_data['project_uid'];

                        $project_uid_escaped=db_escape($project_uid);
                        $project_name_html=htmlspecialchars($project_name);

                        if($host_id=="" || $host_id==0) $attached_project_msg="not synced properly";
                        else $attached_project_msg="";

                        $detach_form=<<<_END
<form name=detach method=post>
$project_name_html
<input type=hidden name=action value='detach'>
<input type=hidden name=attached_uid value='$attached_project_uid'>
<input type=hidden name=host_uid value='$host_uid'>
<input type=hidden name=token value='$username_token'>
<input type=submit value='detach'>
$attached_project_msg
</form>
_END;

            $projects_str.="$detach_form<br>";
            }

        $projects_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects`
WHERE `status` IN ('whitelisted') AND `uid` NOT IN (
        SELECT bap.`project_uid` FROM `boincmgr_hosts` h
        LEFT JOIN `boincmgr_attach_projects` bap ON bap.`host_uid`=h.`uid`
        WHERE `host_uid`='$host_uid_escaped' AND bap.detach=0
) ORDER BY `name` ASC");
        if(count($projects_array)==0) {
                $projects_str.="No more projects to attach<br>";
        } else {
                $attach_form=<<<_END
<form name=attach method=post>
<input type=hidden name=action value='attach'>
<input type=hidden name=host_uid value='$host_uid'>
<input type=hidden name=token value='$username_token'>
<select name=project_uid>
_END;
                foreach($projects_array as $project_data) {
                        $project_uid=$project_data['uid'];
                        $project_name=$project_data['name'];
                        $attach_form.="<option value='$project_uid'>$project_name</option>";
                }
                $attach_form.=<<<_END
<input type=submit value='attach'>
</form>
_END;

        $projects_str.="$attach_form<br>";
        }
        $result.="<tr><td>$domain_name_html</td><td>$p_mode_html</td><td>$projects_str</td></tr>\n";
    }

    $result.="</table>\n";
    $result.="</div>\n";
    return $result;
}

// Show BOINC results for user
function html_boinc_results() {
        global $username;

        $result="";

        $result.="<div id=boinc_results class=selectable_block>\n";
        $result.="<h2>BOINC results:</h2>\n";

        $result.="<p>That information we received from various BOINC projects:</p>\n";

        $result.="<h3>Results by host</h3>\n";
        $result.="<table>\n";
        $result.="<tr><th>Domain name</th><th>CPU</th><th>&Sigma; RAC</th></tr>\n";

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        $boinc_host_data_array=db_query_to_array("SELECT bphl.`domain_name`,bphl.`p_model`,SUM(bphl.`expavg_credit`) AS rac FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
WHERE bh.`username_uid`='$username_uid_escaped' GROUP BY bphl.`domain_name`,bphl.`p_model` ORDER BY bphl.`domain_name`,bphl.`p_model` ASC");

        foreach($boinc_host_data_array as $boinc_host_data) {
                $host_cpid=$boinc_host_data['host_cpid'];
                $domain_name=$boinc_host_data['domain_name'];
                $p_model=$boinc_host_data['p_model'];
                $expavg_credit=round($boinc_host_data['rac']);

                $host_cpid_html=htmlspecialchars($host_cpid);
                $domain_name_html=htmlspecialchars($domain_name);
                $p_model_html=htmlspecialchars($p_model);
                $expavg_credit_html=htmlspecialchars($expavg_credit);

                $result.="<tr><td>$domain_name_html</td><td>$p_model_html</td><td>$expavg_credit_html</td></tr>\n";
        }
        $result.="</table>\n";

        $result.="<h3>Results by project</h3>\n";
        $result.="<table>\n";
        $result.="<tr><th>Project</th><th>&Sigma; RAC</th></tr>\n";

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        $boinc_host_data_array=db_query_to_array("SELECT bp.`name`,SUM(bphl.`expavg_credit`) AS rac FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
WHERE bh.`username_uid`='$username_uid_escaped' GROUP BY bp.`name` ORDER BY bp.`name` ASC");

        foreach($boinc_host_data_array as $boinc_host_data) {
                $expavg_credit=round($boinc_host_data['rac']);
                $project_name=$boinc_host_data['name'];

                $expavg_credit_html=htmlspecialchars($expavg_credit);
                $project_name_html=htmlspecialchars($project_name);

                $result.="<tr><td>$project_name_html</td><td>$expavg_credit_html</td></tr>\n";
        }
        $result.="</table>\n";

        $result.="<h3>Results for each project and each host</h3>\n";
        $result.="<table>\n";
        $result.="<tr><th>Domain name</th><th>CPU</th><th>Project</th><th>RAC</th></tr>\n";

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        $boinc_host_data_array=db_query_to_array("SELECT bphl.`host_cpid`,bphl.`domain_name`,bphl.`p_model`,bp.`name`,bphl.`expavg_credit` FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
WHERE bh.`username_uid`='$username_uid_escaped' ORDER BY bphl.`domain_name`,bp.`name` ASC");

        foreach($boinc_host_data_array as $boinc_host_data) {
                $host_cpid=$boinc_host_data['host_cpid'];
                $domain_name=$boinc_host_data['domain_name'];
                $p_model=$boinc_host_data['p_model'];
                $expavg_credit=$boinc_host_data['expavg_credit'];
                $project_name=$boinc_host_data['name'];

                $host_cpid_html=htmlspecialchars($host_cpid);
                $domain_name_html=htmlspecialchars($domain_name);
                $p_model_html=htmlspecialchars($p_model);
                $expavg_credit_html=htmlspecialchars($expavg_credit);
                $project_name_html=htmlspecialchars($project_name);

                $result.="<tr><td>$domain_name_html</td><td>$p_model_html</td><td>$project_name_html</td><td>$expavg_credit_html</td></tr>\n";
        }
        $result.="</table>\n";
        $result.="</div>\n";

        return $result;
}

// Show billing form
function html_billing_form() {
        global $username_token;

        $start_date=db_query_to_variable("SELECT MAX(`stop_date`) FROM `boincmgr_billing_periods`");
        if($start_date=="") $start_date="2018-01-01 20:20:16";
        $stop_date=db_query_to_variable("SELECT NOW()");

        return <<<_END
<div id=billing class=selectable_block>
<h2>Billing</h2>
<form name=billing method=post>
<p>Fill data carefully, that cannot be undone!</p>
<input type=hidden name=action value='billing'>
<input type=hidden name=token value='$username_token'>
<p>Begin of period <input type=text name=start_date value='$start_date'></p>
<p>End of period <input type=text name=stop_date value='$stop_date'></p>
<p>Total reward <input type=text name=reward value='0.0000'></p>
<p><label><input type=checkbox name=check_rewards checked> check only, do not send</label></p>
<p><input type=submit value='Send rewards'></p>
</form>
</div>
_END;
}

// Show user control form
function html_user_control_form() {
        global $username_token;

        $result="";
        $users_array=db_query_to_array("SELECT `uid`,`username`,`email`,`grc_address`,`status` FROM `boincmgr_users`");
        $result.="<div id=user_control class=selectable_block>\n";
        $result.="<h2>User control</h2>\n";
        $result.="<p><table>\n";
        $result.="<tr><th>Username</th><th>email</th><th>grc_address</th><th>Status</th><th>Action</th></tr>\n";

        $form_hidden_action="<input type=hidden name=action value='change_user_status'>";
        $form_hidden_token="<input type=hidden name=token value='$username_token'>";
        $user_options="<select name=status><option>banned</option><option selected>user</option><option>admin</option></select>";
        $submit_button="<input type=submit value='change'>";

        foreach($users_array as $user_record) {
                $uid=$user_record['uid'];
                $username=$user_record['username'];
                $email=$user_record['email'];
                $grc_address=$user_record['grc_address'];
                $status=$user_record['status'];

                $username_html=htmlspecialchars($username);
                $email_html=htmlspecialchars($email);
                $grc_address_html=htmlspecialchars($grc_address);
                $status_html=htmlspecialchars($status);
                $form_hidden_user_uid="<input type=hidden name=user_uid value='$uid'>";

                $actions="<form name=change_user method=post>".$form_hidden_action.$form_hidden_user_uid.$form_hidden_token.$user_options.$submit_button."</form>";
                $result.="<tr><td>$username_html</td><td>$email_html</td><td>$grc_address_html</td><td>$status_html</td><td>$actions</td></tr>\n";
        }
        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// Show project control form
function html_project_control_form() {
        global $username_token;
        $result="";
        $projects_array=db_query_to_array("SELECT `uid`,`name`,`project_url`,`cpid`,`url_signature`,`status` FROM `boincmgr_projects` ORDER BY `name` ASC");
        $result.="<div id=project_control class=selectable_block>\n";
        $result.="<h2>Project control</h2>\n";
        $result.="<p>Whitelisted means project data updated and rewards are on. Greylisted - only update project data, blacklisted - do not check anything about this project.</p>";
        $result.="<p><table>\n";
        $result.="<tr><th>Name</th><th>URL</th><th>CPID</th><th>Status</th><th>Action</th></tr>\n";

        $form_hidden_action="<input type=hidden name=action value='change_project_status'>";
        $form_hidden_token="<input type=hidden name=token value='$username_token'>";
        $project_options="<select name=status><option>whitelisted</option><option>greylisted</option><option>blacklisted</option></select>";
        $submit_button="<input type=submit value='change'>";

        foreach($projects_array as $project_record) {
                $uid=$project_record['uid'];
                $name=$project_record['name'];
                $project_url=$project_record['project_url'];
                $url_signature=$project_record['url_signature'];
                $status=$project_record['status'];
                $cpid=$project_record['cpid'];

                $name_html=htmlspecialchars($name);
                $project_url_html=htmlspecialchars($project_url);
                $url_signature_html=htmlspecialchars($url_signature);
                $status_html=htmlspecialchars($status);
                $cpid_html=htmlspecialchars($cpid);
                $form_hidden_project_uid="<input type=hidden name=project_uid value='$uid'>";

                $actions="<form name=change_project method=post>".$form_hidden_action.$form_hidden_project_uid.$form_hidden_token.$project_options.$submit_button."</form>";
                $result.="<tr><td>$name_html</td><td>$project_url_html</td><td>$cpid_html</td><td>$status_html</td><td>$actions</td></tr>\n";
        }
        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// Show payouts
function html_payouts() {
        $result="";
        $result.="<div id=payouts class=selectable_block>\n";
        $result.="<h2>Payouts</h2>\n";
        $result.="<p>Last 10 billings from pool:</p>\n";
        $billings_array=db_query_to_array("SELECT `uid`,`start_date`,`stop_date`,`reward` FROM `boincmgr_billing_periods` ORDER BY `stop_date` DESC");
        foreach($billings_array as $billing) {
                $billing_uid=$billing['uid'];
                $start_date=$billing['start_date'];
                $stop_date=$billing['stop_date'];
                $reward=$billing['reward'];

                $billing_uid_escaped=db_escape($billing_uid);

                $result.="<h3>At $stop_date pool rewarded with $reward gridcoins</h3>\n";
//              $result.="<p>Rewards distribution</p>\n";
                $payout_data_array=db_query_to_array("SELECT `grc_address`,`amount`,`txid`,`timestamp` FROM `boincmgr_payouts` WHERE `billing_uid`='$billing_uid_escaped' ORDER BY `grc_address` ASC");
                $result.="<p><table>\n";
                $result.="<tr><th>GRC address</th><th>TX ID</th><th>Amount</th><th>Timestamp</th></tr>\n";
                foreach($payout_data_array as $payout_data) {
                        $grc_address=$payout_data['grc_address'];
                        $amount=$payout_data['amount'];
                        $txid=$payout_data['txid'];
                        $timestamp=$payout_data['timestamp'];

                        $grc_address_link=html_grc_address_link($grc_address);
                        $amount_html=htmlspecialchars($amount);
                        $txid_link=html_txid_link($txid);
                        $timestamp_html=htmlspecialchars($timestamp);

                        $result.="<tr><td>$grc_address_link</td><td>$txid_link</td><td>$amount_html</td><td>$timestamp_html</td></tr>\n";
                }
        $result.="</table></p>\n";
        }
/*      $result.="<p><table>\n";
        $result.="<tr><th>GRC address</th><th>TX ID</th><th>Amount</th><th>Timestamp</th></tr>\n";
        $payout_data_array=db_query_to_array("SELECT `grc_address`,`amount`,`txid`,`timestamp` FROM `boincmgr_payouts` ORDER BY `timestamp` DESC LIMIT 100");
        foreach($payout_data_array as $payout_data) {
                $grc_address=$payout_data['grc_address'];
                $amount=$payout_data['amount'];
                $txid=$payout_data['txid'];
                $timestamp=$payout_data['timestamp'];

                $grc_address_html=htmlspecialchars($grc_address);
                $amount_html=htmlspecialchars($amount);
                $txid_html=htmlspecialchars($txid);
                $timestamp_html=htmlspecialchars($timestamp);

                $result.="<tr><td>$grc_address_html</td><td>$txid_html</td><td>$amount_html</td><td>$timestamp_html</td></tr>\n";
        }
        $result.="</table></p>\n";*/
        $result.="</div>\n";
        return $result;
}

// Show log
function html_view_log() {
        $result="";
        $result.="<div id=log class=selectable_block>\n";
        $result.="<h2>View log</h2>\n";
        $result.="<p>Last 100 messages:</p>\n";
        $result.="<p><table>\n";
        $result.="<tr><th>Timestamp</th><th>Message</th></tr>\n";
        $log_array=db_query_to_array("SELECT `message`,`timestamp` FROM `boincmgr_log` ORDER BY `timestamp` DESC LIMIT 100");
        foreach($log_array as $data) {
                $message=$data['message'];
                $timestamp=$data['timestamp'];

                $message_html=htmlspecialchars($message);
                $timestamp_html=htmlspecialchars($timestamp);

                $result.="<tr><td>$timestamp_html</td><td>$message_html</td></tr>\n";
        }
        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// Show pool stats
function html_pool_stats() {
        $result="";
        $result.="<div id=pool_stats class=selectable_block>\n";
        $result.="<h2>Pool stats</h2>\n";
        $start_date=db_query_to_variable("SELECT MAX(`stop_date`) FROM `boincmgr_billing_periods`");
        if($start_date=="") $start_date="2018-01-01 20:20:16";
        $stop_date=db_query_to_variable("SELECT NOW()");

        $result.="<p><table>\n";
        $result.="<tr><th>Project</th><th>Team RAC</th><th>Pool RAC</th><th>Team proportion</th><th>Pool proportion</th><th>Status</th></tr>\n";
        $proportions=bill_calculate_projects_proportion($start_date,$stop_date);
//      var_dump($proportions);
        $project_array=db_query_to_array("SELECT `uid`,`name`,`project_url`,`expavg_credit`,`team_expavg_credit`,`status` FROM `boincmgr_projects` ORDER BY `name` ASC");
        foreach($project_array as $project_data) {
                $name=$project_data['name'];
                $project_url=$project_data['project_url'];
                $uid=$project_data['uid'];
                $expavg_credit=$project_data['expavg_credit'];
                $team_expavg_credit=$project_data['team_expavg_credit'];
                $status=$project_data['status'];
                if($team_expavg_credit!=0) $team_proportion=round(($expavg_credit/$team_expavg_credit)*100,4);
                else $team_proportion=0;
                $proportion=round($proportions[$uid]*100,2);

                $expavg_credit=round($expavg_credit);

                $name_link=html_project_name_link($name,$project_url);
                $team_expavg_credit_html=htmlspecialchars($team_expavg_credit);
                $expavg_credit_html=htmlspecialchars($expavg_credit);
                $team_proportion_html=htmlspecialchars($team_proportion);
                $proportion_html=htmlspecialchars($proportion);
                $status_html=htmlspecialchars($status);

                $result.="<tr><td>$name_link</td><td>$team_expavg_credit_html</td><td>$expavg_credit_html</td><td>$team_proportion_html %</td><td>$proportion_html %</td><td>$status_html</td></tr>\n";
        }
        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// Show user stats
function html_user_stats() {
        $result="";
        $result.="<div id=your_stats class=selectable_block>\n";
        $result.="<h2>Your stats</h2>\n";
        $result.="</div>\n";
        return $result;
}
?>
