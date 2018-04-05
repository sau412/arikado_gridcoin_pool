<?php
// HTML-returning functions

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
    $result="";
    $result.="<ul>\n";
    
    if(in_array("login",$flags_array)) $result.="<li><a href='./'>Login</a></li>\n";
    if(in_array("register",$flags_array)) $result.="<li><a href='./?action=register'>Register</a></li>\n";
    
    $result.="<li><a href='./?action=guide'>Start guide</a></li>\n";
    $result.="<li><a href='./?action=pool_info'>Pool info</a></li>\n";
    $result.="<li><a href='./?action=about'>About</a></li>\n";
    
    if(in_array("logout",$flags_array)) $result.="<li><a href='./?action=logout'>Logout</a></li>\n";
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

// Register form
function html_register_form() {
    return <<<_END
<form name=register_form method=POST>
<h2>Register</h2>
<p>Username: <input type=text name=username></p>
<p>E-mail: <input type=text name=email></p>
<p>Password 1: <input type=password name=password_1></p>
<p>Password 2: <input type=password name=password_2></p>
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
    global $username;
    
    $username_escaped=db_escape($username);
    
    $email=db_query_to_variable("SELECT `email` FROM `boincmgr_users` WHERE `username`='$username_escaped'");
    $grc_address=db_query_to_variable("SELECT `grc_address` FROM `boincmgr_users` WHERE `username`='$username_escaped'");
    
    $email_html=htmlspecialchars($email);
    $grc_address_html=htmlspecialchars($grc_address);
    
    echo <<<_END
<form name=change_settings_form method=POST>
<h2>Settings</h2>
<p>E-mail: <input type=text name=email value='$email_html'></p>
<p>GRC address: <input type=text name=grc_address value='$grc_address_html'></p>
<p>Password: <input type=password name=password></p>
<p><input type=hidden name="action" value="change_settings"></p>
<p><input type=submit value="Update"></p>
</form>
_END;
}

function html_user_hosts() {
    global $username;
    
    echo "<h2>Your hosts</h2>\n";
    echo "<p>That information will be synced to your BOINC client:</p>\n";
    echo "<table>\n";
    echo "<tr><th>Domain name</th><th>CPU</th><th>Projects</th></tr>\n";
    
    $username_escaped=db_escape($username);
    
    $hosts_array=db_query_to_array("SELECT `host_cpid`,`domain_name`,`p_model` FROM `boincmgr_hosts_last` WHERE `username`='$username_escaped'");
    
    foreach($hosts_array as $host) {
        $host_cpid=$host['host_cpid'];
        $domain_name=$host['domain_name'];
        $p_mode=$host['p_model'];
        
        $host_cpid_html=htmlspecialchars($host_cpid);
        $domain_name_html=htmlspecialchars($domain_name);
        $p_mode_html=htmlspecialchars($p_mode);
        
        $host_cpid_escaped=db_escape($host_cpid);
        
        $attached_projects_array=db_query_to_array("SELECT p.`name`,ap.`detach` FROM `boincmgr_attach_projects` AS ap
LEFT JOIN `boincmgr_projects` AS p ON p.`uid`=ap.`project_uid`
LEFT JOIN `boincmgr_hosts` AS h ON h.`uid`=ap.`host_uid`
WHERE h.host_cpid='$host_cpid_escaped'");
        
        $projects_str="";
        foreach($attached_projects_array as $project_data)
            {
            $project_name=$project_data['name'];
            
            $project_name_html=htmlspecialchars($project_name);
            
            $projects_str.="$project_name_html<br>";
            }
        
        echo "<tr><td>$domain_name_html</td><td>$p_mode_html</td><td>$projects_str</td></tr>\n";
    }
    echo "</table>\n";
}

function html_boinc_results() {
    global $username;
    
    echo "<h2>BOINC results:</h2>\n";
    echo "<p>That information we received from various BOINC projects:</p>\n";
    
    echo "<table>\n";
    echo "<tr><th>Domain name</th><th>CPU</th><th>Project</th><th>RAC</th></tr>\n";
    
    $username_escaped=db_escape($username);
    
    $boinc_host_data_array=db_query_to_array("SELECT `domain_name`,`p_model`,`boincmgr_projects`.`name`,`expavg_credit` FROM `boincmgr_project_hosts`
LEFT JOIN `boincmgr_projects` ON `boincmgr_projects`.`uid`=`boincmgr_project_hosts`.project_uid
-- WHERE `host_cpid` IN (SELECT `host_cpid` FROM `boincmgr_hosts` WHERE `username`='$username_escaped')");

    foreach($boinc_host_data_array as $boinc_host_data) {
        $domain_name=$boinc_host_data['domain_name'];
        $p_model=$boinc_host_data['p_model'];
        $expavg_credit=$boinc_host_data['expavg_credit'];
        $project_name=$boinc_host_data['name'];
        
        $domain_name_html=htmlspecialchars($domain_name);
        $p_model_html=htmlspecialchars($p_model);
        $expavg_credit_html=htmlspecialchars($expavg_credit);
        $project_name_html=htmlspecialchars($project_name);

        echo "<tr><td>$domain_name_html</td><td>$p_model_html</td><td>$project_name_html</td><td>$expavg_credit_html</td></tr>\n";
    }
    echo "</table>\n";
}
?>
