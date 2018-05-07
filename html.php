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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="common.css">
</head>
<body>

_END;
}

// End html page
function html_page_end() {
    echo <<<_END
</body>
</html>

_END;
}

// Message in p tags
function html_p($message) {
    return "<p>$message</p>\n";
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
function html_get_menu($flags_array) {
    global $username_token;

    $result="";
    $result.="<ul>\n";

    if(in_array("login",$flags_array)) $result.="<li><a href='./'>Login</a></li>\n";
    if(in_array("register",$flags_array)) $result.="<li><a href='./?action=register'>Register</a></li>\n";

//    $result.="<li><a href='./?action=guide'>Start guide</a></li>\n";
//    $result.="<li><a href='./?action=pool_info'>Pool info</a></li>\n";
//    $result.="<li><a href='./?action=about'>About</a></li>\n";

    if(in_array("logout",$flags_array)) $result.="<li><a href='./?action=logout&token=$username_token'>Logout</a></li>\n";
    $result.="</ul>\n";
    return $result;
}

// Greeting for user
function html_greeting_user() {
    global $username;
    if($username!='') {
        $username_html=htmlspecialchars($username);
        return "Welcome, $username_html";
    } else {
        return "Hello, stranger";
    }
}

// Pool info
function html_pool_info() {
        global $pool_name;
        return <<<_END
<h2>Pool info</h2>
<p>Welcome to $pool_name</p>
$message_pool_info
<p>Guide: 1) Register, 2) Use this pool as account manager, 3) Wait for rewards</p>
_END;
}

// Register form
function html_register_form() {
    return <<<_END
<form name=register_form method=POST>
<h2>Register</h2>
<p>Username: <input type=text name=username></p>
<p>Password 1: <input type=password name=password_1></p>
<p>Password 2: <input type=password name=password_2></p>
<p>E-mail: <input type=text name=email> for password recovery</p>
<p>GRC address: <input type=text name=grc_address></p>
<p><input type=hidden name="action" value="register"></p>
<p><input type=submit value="Register"></p>
</form>

_END;
}

// Login form
function html_login_form() {
    echo <<<_END
<form name=login_form method=POST>
<h2>Login</h2>
<p>Username: <input type=text name=username></p>
<p>Password: <input type=password name=password></p>
<p><input type=hidden name="action" value="login"></p>
<p><input type=submit value="Login"></p>
</form>
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

    echo <<<_END
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
_END;
}

// Show user hosts
function html_user_hosts() {
    global $username,$username_token;

    echo "<h2>Your hosts</h2>\n";
    echo "<p>That information will be synced to your BOINC client:</p>\n";
    echo "<table>\n";
    //echo "<tr><th>Host CPID</th><th>Domain name</th><th>CPU</th><th>Projects</th></tr>\n";
    echo "<tr><th>Domain name</th><th>CPU</th><th>Projects</th></tr>\n";

    $username_escaped=db_escape($username);

    $hosts_array=db_query_to_array("SELECT `uid`,`external_host_cpid`,`domain_name`,`p_model` FROM `boincmgr_hosts` WHERE `username`='$username_escaped'");

    foreach($hosts_array as $host) {
        $host_uid=$host['uid'];
        $host_cpid=$host['external_host_cpid'];
        $domain_name=$host['domain_name'];
        $p_mode=$host['p_model'];

        $host_cpid_html=htmlspecialchars($host_cpid);
        $domain_name_html=htmlspecialchars($domain_name);
        $p_mode_html=htmlspecialchars($p_mode);

        $host_cpid_escaped=db_escape($host_cpid);

        $attached_projects_array=db_query_to_array("SELECT ap.`uid`,ap.`host_uid`,p.`name`,ap.`detach` FROM `boincmgr_attach_projects` AS ap
LEFT JOIN `boincmgr_projects` AS p ON p.`uid`=ap.`project_uid`
LEFT JOIN `boincmgr_hosts` AS h ON h.`uid`=ap.`host_uid`
WHERE h.external_host_cpid='$host_cpid_escaped' AND ap.`detach`=0");

        $projects_str="";
        foreach($attached_projects_array as $project_data)
            {
            $attached_project_uid=$project_data['uid'];
            $host_uid=$project_data['host_uid'];
            $project_name=$project_data['name'];

            $project_name_html=htmlspecialchars($project_name);

            $detach_form=<<<_END
<form name=detach method=post>
$project_name_html
<input type=hidden name=action value='detach'>
<input type=hidden name=attached_uid value='$attached_project_uid'>
<input type=hidden name=host_uid value='$host_uid'>
<input type=hidden name=token value='$username_token'>
<input type=submit value='detach'>
</form>
_END;

            $projects_str.="$detach_form<br>";
            }

        $projects_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects`
WHERE `status`='whitelisted' AND `uid` NOT IN (
        SELECT `project_uid` FROM `boincmgr_hosts` h
        LEFT JOIN `boincmgr_attach_projects` bap ON bap.`host_uid`=h.`uid`
        WHERE `external_host_cpid`='$host_cpid_escaped' AND bap.detach=0
) ORDER BY `name`");
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
        //echo "<tr><td>$host_cpid_html</td><td>$domain_name_html</td><td>$p_mode_html</td><td>$projects_str</td></tr>\n";
        echo "<tr><td>$domain_name_html</td><td>$p_mode_html</td><td>$projects_str</td></tr>\n";
    }

    echo "</table>\n";
}

// Show BOINC results for user
function html_boinc_results() {
    global $username;

    echo "<h2>BOINC results:</h2>\n";
    echo "<p>That information we received from various BOINC projects:</p>\n";

    echo "<table>\n";
    //echo "<tr><th>Host CPID</th><th>Domain name</th><th>CPU</th><th>Project</th><th>RAC</th></tr>\n";
    echo "<tr><th>Domain name</th><th>CPU</th><th>Project</th><th>RAC</th></tr>\n";

    $username_escaped=db_escape($username);

    $boinc_host_data_array=db_query_to_array("SELECT `host_cpid`,`domain_name`,`p_model`,`boincmgr_projects`.`name`,`expavg_credit` FROM `boincmgr_project_hosts_last`
LEFT JOIN `boincmgr_projects` ON `boincmgr_projects`.`uid`=`boincmgr_project_hosts_last`.project_uid
WHERE `host_cpid` IN (SELECT `external_host_cpid` FROM `boincmgr_hosts` WHERE `username`='$username_escaped')");

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

        // echo "<tr><td>$host_cpid_html</td><td>$domain_name_html</td><td>$p_model_html</td><td>$project_name_html</td><td>$expavg_credit_html</td></tr>\n";
        echo "<tr><td>$domain_name_html</td><td>$p_model_html</td><td>$project_name_html</td><td>$expavg_credit_html</td></tr>\n";
    }
    echo "</table>\n";
}

// Show billing form
function html_billing_form() {
        global $username_token;

        $start_date=db_query_to_variable("SELECT MAX(`stop_date`) FROM `boincmgr_billing_periods`");
        if($start_date=="") $start_date="2018-01-01 20:20:16";
        $stop_date=db_query_to_variable("SELECT NOW()");

        return <<<_END
<form name=billing method=post>
<input type=hidden name=action value='billing'>
<input type=hidden name=token value='$username_token'>
<p>Begin of period <input type=text name=start_date value='$start_date'></p>
<p>End of period <input type=text name=stop_date value='$stop_date'></p>
<p>Total reward <input type=text name=reward value='0.0000'></p>
<p><input type=submit value='Send rewards'></p>
</form>
_END;
}

// Show user control form
function html_user_control_form() {
        global $username_token;

        $result="";
        $users_array=db_query_to_array("SELECT `uid`,`username`,`email`,`grc_address`,`status` FROM `boincmgr_users`");
        $result.="<table>\n";
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
        $result.="</table>\n";
        return $result;
}

// Show project control form
function html_project_control_form() {
        global $username_token;
        $result="";
        $projects_array=db_query_to_array("SELECT `uid`,`name`,`project_url`,`url_signature`,`status` FROM `boincmgr_projects`");
        $result.="<table>\n";
        $result.="<tr><th>Name</th><th>URL</th><th>Status</th><th>Action</th></tr>\n";

        $form_hidden_action="<input type=hidden name=action value='change_project_status'>";
        $form_hidden_token="<input type=hidden name=token value='$username_token'>";
        $project_options="<select name=status><option>whitelisted</option><option>greylisted</option></select>";
        $submit_button="<input type=submit value='change'>";

        foreach($projects_array as $project_record) {
                $uid=$project_record['uid'];
                $name=$project_record['name'];
                $project_url=$project_record['project_url'];
                $url_signature=$project_record['url_signature'];
                $status=$project_record['status'];

                $name_html=htmlspecialchars($name);
                $project_url_html=htmlspecialchars($project_url);
                $url_signature_html=htmlspecialchars($url_signature);
                $status_html=htmlspecialchars($status);

                $form_hidden_project_uid="<input type=hidden name=project_uid value='$uid'>";

                $actions="<form name=change_project method=post>".$form_hidden_action.$form_hidden_project_uid.$form_hidden_token.$project_options.$submit_button."</form>";
                $result.="<tr><td>$name_html</td><td>$project_url_html</td><td>$status_html</td><td>$actions</td></tr>\n";
        }
        $result.="</table>\n";
        return $result;
}

?>
