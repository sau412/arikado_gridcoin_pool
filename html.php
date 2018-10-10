<?php
// HTML-returning functions

// Unescape strings from POST, COOKIES and GET
function html_strip($variable) {
        //if(is_array($var)) return "";
        //if(!preg_match('/^[-0-9A-Za-z!@#$%^&*().\\/]*$/',$var)) return "";
        $variable=(string)stripslashes($variable);
        return $variable;
}

// Escape for html facade
function html_escape($variable) {
        $result=htmlspecialchars($variable);
        if($variable!='' && $result=='') {
                $result=iconv('WINDOWS-1251','UTF-8',$variable);
                $result=htmlspecialchars($result);
        }
        $result=str_replace("'","&apos;",$result);
        return $result;
}

// Redirect and die
function html_redirect_and_die($url) {
        $file="";
        $line=0;
        if(headers_sent($file,$line)) {
                auth_log("Redirect warning: headers already sent, file '$file', line '$line'");
                echo "<br>Auto redirect failed, please <a href='$url'>click here</a>";
        } else {
                header("Location: $url");
        }
        die();
}

// Begin HTML page
function html_page_begin() {
        global $pool_name;
        $css_file="common.css";

        // Check is mobile
        // https://stackoverflow.com/questions/4117555/simplest-way-to-detect-a-mobile-device
        $user_agent=$_SERVER['HTTP_USER_AGENT'];
        if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $user_agent)) {
                $css_file='mobile.css';
        }

        return <<<_END
<!DOCTYPE html>
<html class="gr__grc_arikado_ru gr__" id="html">
<head>
<title>$pool_name gridcoin pool</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="$css_file">
<script src='./common.js'></script>
<link rel="icon" href="favicon.png" type="image/png">
<script src='jquery-3.3.1.min.js'></script>
</head>
<body data-gr-c-s-loaded="true">
<div style='float:right;'><input type=button value='&#9680;' onClick='toggle_night_mode();'></div>

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

function toggle_night_mode() {
        if (sessionStorage.getItem("night_mode") == 0) {
                set_night_mode(1);
                sessionStorage.setItem("night_mode",1);
        } else {
                set_night_mode(0);
                sessionStorage.setItem("night_mode",0);
        }
}

// Set night mode if flag == 1
// Else set day mode
function set_night_mode(flag) {
        if (flag == 1) {
                document.getElementById("html").classList.add("html_day");
                document.getElementById("main_bar").classList="main_bar_dark";
                var menu_items= document.getElementsByClassName("menu_item");
                for (i=0;i<menu_items.length;i++){
                        menu_items[i].classList.add("menu_item_light");
                        menu_items[i].classList.remove("menu_item_dark");
                }
        } else {
                document.getElementById("html").classList.remove("html_day");
                document.getElementById("main_bar").classList="main_bar_light";
                var menu_items= document.getElementsByClassName("menu_item");
                for (i=0;i<menu_items.length;i++){
                        menu_items[i].classList.add("menu_item_dark");
                        menu_items[i].classList.remove("menu_item_light");
                }
        }
}

set_night_mode(sessionStorage.getItem("night_mode"));

</script>
<hr>
<center>
<p>Opensource gridcoin pool (<a href='https://github.com/sau412/arikado_gridcoin_pool'>github link</a>) by Vladimir Tsarev, my nickname is sau412 on telegram, twitter, facebook, gmail, github, vk, gridcoin slack and discord.</p>
</center>
</body>
</html>

_END;
}

// Message in p tags
function html_p($message) {
        return "<p>$message</p>\n";
}

// Message after action
function html_top_message($message) {
        return "<p class=top_message>$message</p>\n";
}

// Message after action
function html_top_message_error($message) {
        return "<p class=top_message_error>$message</p>\n";
}

// Message after action
function html_top_message_info($message) {
        return "<p class=top_message_info>$message</p>\n";
}

// Return project name as url
function html_project_name_link($project_name,$project_url) {
        $project_name_html=html_escape($project_name);
        return "<a href='$project_url'>$project_name</a>";
}

// Return payout address as URL
function html_payout_address_link($currency,$payout_address) {
        $payout_address_html=html_escape($payout_address);
        $currency_escaped=db_escape($currency);
        $wallet_url=db_query_to_variable("SELECT `url_wallet` FROM `boincmgr_currency` WHERE `name`='$currency_escaped'");
        if($wallet_url!="") {
                return "<a href='${wallet_url}${payout_address_html}'>$payout_address_html</a>";
        } else {
                return $payout_address_html;
        }
}

// Return txid as URL
function html_txid_link($currency,$txid) {
        if($txid=="") {
                if($currency=="GRC" || $currency=="GRC2") return "no txid";
                else return "limit not reached";
        } else {
                $txid_short=substr($txid,0,10);
                $txid_short_html=html_escape($txid_short);
                $txid_html=html_escape($txid);

                $currency_escaped=db_escape($currency);
                $tx_url=db_query_to_variable("SELECT `url_tx` FROM `boincmgr_currency` WHERE `name`='$currency_escaped'");
                if($tx_url!="") {
                        return "<a href='${tx_url}${txid_html}'>${txid_short_html}&hellip;</a>";
                } else {
                        return $tx_short_html;
                }
        }
}

// Number with delimiters
function html_format_number($number) {
        if($number<10000) return $number;
        return number_format($number,0,".","&nbsp;");
}

// User menu and options
function html_page_header($flags_array) {
        global $action_message;
        global $pool_name;

        $greeting_user_text=html_greeting_user();
        $greeting_user_message=html_p($greeting_user_text);

        $menu=html_get_menu($flags_array);

        if($action_message!='') {
                $action_message_html=html_escape($action_message);
                $action_message_result=html_top_message($action_message_html);
        } else {
                $action_message_result="";
        }
        return <<<_END
$greeting_user_message
$action_message_result
$menu
_END;
}

// Menu
function html_get_menu($flag) {
        global $username_token;
        global $pool_name;

        $greeting_user_text=html_greeting_user();

        $result="";
        $result.="<ul id='main_bar' class='main_bar_light'>\n";
        $result.="<center class='main_bar_text'>\n";
//      $result.="<li><div style='background-color:#2b2b2b;color:white;display=block;text-align:center;padding:1em;cursor:not-allowed'>$pool_name</div></li>";
        $result.=html_menu_element("pool_info","Pool info");

        if($flag=="unknown") {
                $result.=html_menu_element("login_form","Login");
                $result.=html_menu_element("register_form","Register");
                $result.=html_menu_element("payouts","Payouts");

                $submenu="";
                $submenu.=html_menu_element("rating_by_host_mag","Rating by host mag");
                $submenu.=html_menu_element("rating_by_user_mag","Rating by user mag");
                $submenu.=html_menu_element("rating_by_host_project_mag","Rating by host project mag");
                $submenu.=html_menu_element("rating_by_user_project_mag","Rating by user project mag");
                $submenu.=html_menu_element("pool_stats","Pool project stats");
                $result.=html_dropdown_menu_element("statistics","Statistics",$submenu);

                $submenu="";
                $submenu.=html_menu_element("currencies","Currencies");
                $submenu.=html_menu_element("block_explorer","Block explorer");
                $result.=html_dropdown_menu_element("info","Info",$submenu);
                $result.=html_menu_element("message_send","Feedback");
        } else {
                $result.=html_menu_element("settings","Settings");
                $result.=html_menu_element("your_hosts","Your hosts");

                $submenu="";
                $submenu.=html_menu_element("boinc_results_by_host","Results by host");
                $submenu.=html_menu_element("boinc_results_by_project","Results by project");
                $submenu.=html_menu_element("boinc_results_by_user","Results by user");
                $submenu.=html_menu_element("boinc_results_all_valuable","All valuable");
                $submenu.=html_menu_element("boinc_results_all","All results");
                $result.=html_dropdown_menu_element("boinc","BOINC results",$submenu);

                $result.=html_menu_element("payouts","Payouts");

                $submenu="";
                $submenu.=html_menu_element("rating_by_host_mag","Rating by host mag");
                $submenu.=html_menu_element("rating_by_user_mag","Rating by user mag");
                $submenu.=html_menu_element("rating_by_host_project_mag","Rating by host project mag");
                $submenu.=html_menu_element("rating_by_user_project_mag","Rating by user project mag");
                $submenu.=html_menu_element("pool_stats","Pool project stats");
                $result.=html_dropdown_menu_element("statistics","Statistics",$submenu);

                $submenu="";
                $submenu.=html_menu_element("currencies","Currencies");
                $submenu.=html_menu_element("block_explorer","Block explorer");
                $result.=html_dropdown_menu_element("info","Info",$submenu);

                if($flag=="admin") {
                        $submenu="";
                        $submenu.=html_menu_element("user_control","User control");
                        $submenu.=html_menu_element("project_control","Project control");
                        $submenu.=html_menu_element("billing","Billing");
                        $submenu.=html_menu_element("pool_info_editor","Pool info editor");
                        $submenu.=html_menu_element("log","View log");
                        $submenu.=html_menu_element("messages_view","View messages");
                        $submenu.=html_menu_element("email_view","View emails");

                        $result.=html_dropdown_menu_element("control","Control",$submenu);
                }
                $result.=html_menu_element("message_send","Feedback");
                $result.=html_menu_element("faucet","Faucet");
        }
        $result.="</center>\n";
        $result.="</ul>\n";
        return $result;
}

// Dropdown element for menu
function html_dropdown_menu_element($id,$text,$submenu) {
        return <<<_END
<li><a href='#' onClick='hide_all_submenu("$id");toggle_block("$id");return false;'>$text &#9660;</a>
<ul id='$id'>
$submenu
</ul>
</li>

_END;
}

// List element for menu
function html_menu_element($tag,$text) {
        return "<li><a href='#$tag' onClick='hide_all_submenu(\"\");return show_block(\"$tag\");'>$text</a></li>\n";
}

// Greeting for user
function html_greeting_user() {
        global $username,$username_token;

        if($username!='') {
        $username_html=html_escape($username);
                return "Welcome, $username_html (<a href='./?action=logout&token=$username_token'>logout</a>)";
        } else {
                return "";
        }
}

// Loadable block for ajax
function html_loadable_block() {
        return "<div id='main_block'>Loading block...</div>\n";
}

// Currency selector
function html_currency_selector($selected_currency="") {
        if($selected_currency=="") $selected_currency="GRC2";

        $currency_data_array=db_query_to_array("SELECT `name`,`full_name` FROM `boincmgr_currency` ORDER BY `uid`");
        $result="";
        $result.="<select name=payout_currency>";
        foreach($currency_data_array as $currency_data) {
                $currency_name=$currency_data["name"];
                $currency_full_name=$currency_data["full_name"];
                if($currency_name==$selected_currency) $result.="<option value='$currency_name' selected>$currency_full_name</option>";
                else $result.="<option value='$currency_name'>$currency_full_name</option>";
        }
        $result.="</select>";
        return $result;
}

// Project selector
function html_project_selector($selected_project="") {
        $project_data_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects` ORDER BY `name`");
        $result="";
        $result.="<select name=project_uid>";
        $result.="<option value='0'>All</option>";
        foreach($project_data_array as $project_data) {
                $project_name=$project_data["name"];
                $project_uid=$project_data["uid"];
                $result.="<option value='$project_uid'>$project_name</option>";
        }
        $result.="</select>";
        return $result;
}

// Pool info
function html_pool_info() {
        global $pool_name;

        $pool_info=boincmgr_get_pool_info();
        return <<<_END
<div id=pool_info_block class=selectable_block>
<h2>Pool info</h2>
$pool_info
</div>

_END;
}

// Register form
function html_register_form() {
        global $pool_min_password_length;
        global $recaptcha_public_key;

        $currency_selector=html_currency_selector();

        return <<<_END
<script src='https://www.google.com/recaptcha/api.js'></script>
<div id=register_form_block class=selectable_block>
<form name=register_form method=POST>
<h2>Register</h2>
<p>Username: <input type=text name=username> required, only letters A-Z, a-z, </p>
<p>Password: <input type=password name=password_1> required at least $pool_min_password_length characters</p>
<p>Re-type password: <input type=password name=password_2></p>
<p>E-mail: <input type=text name=email size=40> for password recovery (you can write me from that mail, and I send you new password for account)</p>
<p>Payout address: <input type=text name=payout_address size=40> payout currency $currency_selector both required</p>
<p><input type=hidden name="action" value="register"></p>
<div class="g-recaptcha" data-sitekey="$recaptcha_public_key"></div>
<p><input type=submit value="Register"></p>
</form>
</div>

_END;
}

// Login form
function html_login_form() {
        return <<<_END
<div id=login_form_block class=selectable_block>
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
        $payout_currency=db_query_to_variable("SELECT `currency` FROM `boincmgr_users` WHERE `username`='$username_escaped'");
        $payout_address=db_query_to_variable("SELECT `payout_address` FROM `boincmgr_users` WHERE `username`='$username_escaped'");
        $send_error_reports=db_query_to_variable("SELECT `send_error_reports` FROM `boincmgr_users` WHERE `username`='$username_escaped'");

        $email_html=html_escape($email);
        $payout_address_html=html_escape($payout_address);
        $payout_currency_html=html_escape($payout_currency);

        $currency_selector=html_currency_selector($payout_currency);

        if($send_error_reports) $send_error_reports_status="checked";
        else $send_error_reports_status="";

        return <<<_END
<div id=settings_block class=selectable_block>
<h2>Settings</h2>
<p>GRC payouts are instant, alternative currencies payouts are cumulative and manual. It takes 1-2 days when payout limit reached to send payout (because manual mode now).</p>
<p>Changing alternative (non-GRC) currency or address notice: please note, owed amount linked to address, not to user. If you change address your previous address owed amount will not lost, but won't payed out until payout limit for previous address reached. You can contact admin for manual payout or change address back and receive payout when payout limit reached.</p>
<form name=change_settings_form method=POST>
<input type=hidden name="action" value="change_settings">
<input type=hidden name="token" value="$username_token">
<p>E-mail: <input type=text name=email value='$email_html' size=40></p>
<p><label><input type=checkbox name=send_error_reports $send_error_reports_status> send reports to email if task errors found</label></p>
<p>Payout address: <input type=text name=payout_address value='$payout_address_html' size=40> currency $currency_selector (look notice above)</p>
<p>Password: <input type=password name=password> the password is required to change settings</p>
<p>New password: <input type=password name=new_password1> only if you wish to change password</p>
<p>Re-type new password: <input type=password name=new_password2></p>
<!--<p><label><input type=checkbox onClick='check_deletion();' name=delete_account> delete my account</label></p>-->
<p><input type=submit value="Update"></p>
</form>
</div>

_END;
}

// Show user hosts
function html_user_hosts() {
        global $username,$username_token;

        $result="";
        $result.="<div id=your_hosts_block class=selectable_block>\n";
        $result.="<h2>Your hosts</h2>\n";
        $result.="<p>That information will be synced to your BOINC client. When attaching new project sync second time after 1-2 minutes to avoid incomplete sync. If you sync correctly, then you see your host in BOINC results after 1-3 hours.</p>\n";
        $result.="<table align=center>\n";

        $mag_per_project=boincmgr_get_mag_per_project();

        if(auth_is_admin($username)) {
                $result.="<tr><th>Host info</th><th>Projects</th></tr>\n";
                $hosts_array=db_query_to_array("SELECT bh.`uid`,bu.`username`,bh.`internal_host_cpid`,bh.`external_host_cpid`,bh.`domain_name`,bh.`p_model`,bh.`timestamp` FROM `boincmgr_hosts` AS bh
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
ORDER BY bu.`username`,bh.`domain_name` ASC");
        } else {
                $username_uid=boincmgr_get_username_uid($username);
                $result.="<p><a href='tasks.php?username_uid=$username_uid'>View task stats</a></p>";
                $result.="<tr><th>Host info</th><th>Projects</th></tr>\n";
                $username_uid=boincmgr_get_username_uid($username);
                $username_uid_escaped=db_escape($username_uid);
                $hosts_array=db_query_to_array("SELECT bh.`uid`,bu.`username`,bh.`internal_host_cpid`,bh.`external_host_cpid`,bh.`domain_name`,bh.`p_model`,bh.`timestamp` FROM `boincmgr_hosts` AS bh
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bh.`username_uid`='$username_uid_escaped' ORDER BY bh.`domain_name` ASC");
        }
        foreach($hosts_array as $host) {
                $host_username=$host['username'];
                $host_uid=$host['uid'];
                $host_cpid=$host['external_host_cpid'];
                $internal_host_cpid=$host['internal_host_cpid'];
                $domain_name=$host['domain_name'];
                $domain_name_decoded=boincmgr_domain_decode($host['domain_name']);
                if(auth_validate_ascii($domain_name_decoded)==TRUE) {
                        $domain_name=$domain_name_decoded;
                }
                $p_model=$host['p_model'];
                $timestamp=$host['timestamp'];

                $host_username_html=html_escape($host_username);
                $host_cpid_html=html_escape($host_cpid);
                $domain_name_html=html_escape($domain_name);
                $p_model_html=html_escape($p_model);
                $timestamp_html=html_escape($timestamp);

                // Delete host button
                $host_delete_form=<<<_END
<form name=delete_host method=post>
<input type=hidden name=action value='delete_host'>
<input type=hidden name=host_uid value='$host_uid'>
<input type=hidden name=token value='$username_token'>
<input type=submit value='delete host' onClick='return check_delete_host();'>
</form>
_END;

                // Project list for this host
                $host_uid_escaped=db_escape($host_uid);

                $attached_projects_array=db_query_to_array("SELECT bap.`uid`,bap.`host_uid`,bp.`uid` as project_uid,bp.`name`,bap.`status`,bap.`resource_share`,bap.`options`,bp.`status` AS project_status
FROM `boincmgr_attach_projects` AS bap
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bap.`project_uid`
WHERE bap.host_uid='$host_uid_escaped' ORDER BY bp.`name` ASC");

                $projects_str="";
                foreach($attached_projects_array as $project_data) {
                        $attached_project_uid=$project_data['uid'];
                        $host_uid=$project_data['host_uid'];
                        $project_name=$project_data['name'];
                        $project_uid=$project_data['project_uid'];
                        $status=$project_data['status'];
                        $project_status=$project_data['project_status'];
                        $resource_share=$project_data['resource_share'];
                        $options=$project_data['options'];

                        $project_uid_escaped=db_escape($project_uid);
                        $project_name_html=html_escape($project_name);
                        $options_html=html_escape($options);
                        $options_array=explode(",",$options);
                        $resource_share_html=html_escape($resource_share);

                        if($options=="") $options_show_html="";
                        else $options_show_html=str_replace(",",", ","($options_html)");

                        if($resource_share!=100) {
                                if($options_show_html!='') $options_show_html=str_replace(")",", resource_share=$resource_share)",$options_show_html);
                                else $options_show_html="(resource_share=$resource_share)";
                        }


                        switch($status) {
                                default:
                                case "new":
                                        $attached_project_msg="<span class=host_status_new>just added, sync required</span>";
                                        break;
                                case "sent":
                                        $attached_project_msg="<span class=host_status_sent>synced, not checked, sync required</span>";
                                        break;
                                case "attached":
                                        $attached_project_msg="<span class=host_status_attached>synced, checked</span>";
                                        break;
                                case "incorrect":
                                        $attached_project_msg="<span class=host_status_incorrect>remove this project from BOINC manager and resync</span>";
                                        break;
                                case "unknown":
                                        $attached_project_msg="<span class=host_status_unknown>remove this project from BOINC manager and resync</span>";
                                        break;
                                case "detach":
                                        $attached_project_msg="<span class=host_status_detach>detached, sync required</span>";
                                        break;
                        }
                        if(in_array("detach_when_done",$options_array)) {
                                $attached_project_msg="<span class=host_status_detach>detach when done</span>";
                        }
                        if($project_status=="disabled" || $project_status=="auto disabled") {
                                $attached_project_msg="<span class=host_status_incorrect>project not whitelisted, no rewards</span>";
                        }

                        // Tasks report link
                        $host_uid_escaped=db_escape($host_uid);
                        $project_uid_escaped=db_escape($project_uid);
                        $host_id=db_query_to_variable("SELECT `host_id` FROM `boincmgr_host_projects` WHERE `host_uid`='$host_uid_escaped' AND `project_uid`='$project_uid_escaped'");

                        if($host_id!='') {
                                $host_relative_contribution=boincmgr_get_relative_contribution_project_host($project_uid,$host_uid);
                                $mag_formatted=sprintf("%0.2f",$mag_per_project*$host_relative_contribution);
                                $tasks_report_link="(mag $mag_formatted) <a href='./tasks.php?project_uid=$project_uid&host_id=$host_id'>tasks report</a>";
                        } else {
                                $tasks_report_link="";
                        }

                        $options_form=<<<_END
$project_name_html $options_show_html
<input type=button value='options' onClick="show_project_options_window('$attached_project_uid','$domain_name_html','$project_name_html','$resource_share_html','$options_html');">
$attached_project_msg
$tasks_report_link
<br>

_END;

                        $projects_str.="$options_form<br>";
                }

                if(auth_is_admin($username)) {
                        $projects_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects`
WHERE `uid` NOT IN (
        SELECT bap.`project_uid` FROM `boincmgr_hosts` h
        LEFT JOIN `boincmgr_attach_projects` bap ON bap.`host_uid`=h.`uid`
        WHERE `host_uid`='$host_uid_escaped' AND bap.`status`<>'detach'
) ORDER BY `name` ASC");
                } else {
                        $projects_array=db_query_to_array("SELECT `uid`,`name` FROM `boincmgr_projects`
WHERE `status` IN ('enabled','auto enabled') AND `uid` NOT IN (
        SELECT bap.`project_uid` FROM `boincmgr_hosts` h
        LEFT JOIN `boincmgr_attach_projects` bap ON bap.`host_uid`=h.`uid`
        WHERE `host_uid`='$host_uid_escaped' AND bap.`status`<>'detach'
) ORDER BY `name` ASC");
                }
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
                $p_model_html=str_replace("[","<br>[",$p_model_html);
                $host_info=boincmgr_get_host_info($host_uid);
                $host_info_html=html_escape($host_info);
                $host_info_html=str_replace("\n","<br>\n",$host_info_html);
                $host_info_html=str_replace("[","<br>\n[",$host_info_html);
//              $host_info_html=str_replace("&amp;","&",$host_info_html);

                if(auth_is_admin($username)) {
                        $host_info_full="";
                        $host_info_full.="<p>User: <strong>$host_username_html</strong></p>";
                        $host_info_full.="<p>$host_info_html</p>";
                        $host_info_full.="<p>Last sync: $timestamp_html</p>";
                        $host_info_full.="<p><a href='?action=view_host_last_query&host_uid=$host_uid&token=$username_token'>View last query</a></p>";
                        $host_info_full.="<p>$host_delete_form</p>";
                        $result.="<tr><td>$host_info_full</td><td>$projects_str</td></tr>\n";
                } else {
                        $result.="<tr><td><p>$host_info_html</p><p>$host_delete_form</p></td><td>$projects_str</td></tr>\n";
                }
        }
        $result.="</table>\n";
        $result.="</div>\n";
        return $result;
}

// Show BOINC results by host
function html_boinc_results_by_host() {
        global $username;

        $result="";

        $mag_per_project=boincmgr_get_mag_per_project();
        $magnitude_unit=boincmgr_get_magnitude_unit();

        $result.="<div id=boinc_results_block class=selectable_block>\n";
        $result.="<h2>BOINC results:</h2>\n";

        $result.="<p>That information we received from various BOINC projects:</p>\n";

        $result.="<h3>Results by host</h3>\n";
        $result.="<table align=center>\n";

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        if(auth_is_admin($username)) {
                $result.="<tr><th>Username</th><th>Domain name</th><th>CPU</th><th>Mag 7d graph</th><th>Mag</th><th>&#8776;GRC/day</th></tr>\n";
                $boinc_host_data_array=db_query_to_array("SELECT bu.`username`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model`,SUM(bphl.`expavg_credit`) AS rac,SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
GROUP BY bu.`username`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model` ORDER BY bu.`username`,bphl.`domain_name`,bphl.`p_model` ASC");
        } else {
                $result.="<tr><th>Domain name</th><th>CPU</th><th>Mag 7d graph</th><th>Mag</th><th>&#8776;GRC/day est</th></tr>\n";
                $boinc_host_data_array=db_query_to_array("SELECT bu.`username`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model`,SUM(bphl.`expavg_credit`) AS rac,SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bh.`username_uid`='$username_uid_escaped'
GROUP BY  bu.`username`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model` ORDER BY bu.`username`,bphl.`domain_name`,bphl.`p_model` ASC");
        }
        foreach($boinc_host_data_array as $boinc_host_data) {
                $host_username=$boinc_host_data['username'];
                $host_uid=$boinc_host_data['host_uid'];
                $domain_name=boincmgr_domain_decode($boinc_host_data['domain_name']);
                $expavg_credit=round($boinc_host_data['rac']);
                $relative_credit=$boinc_host_data['relative_credit'];

                $host_username_html=html_escape($host_username);
                $domain_name_html=html_escape($domain_name);
                $expavg_credit_html=html_escape($expavg_credit);

                $expavg_credit_html=html_format_number($expavg_credit_html);
                $host_info=boincmgr_get_host_short_info($host_uid);

                $host_relative_contribution=boincmgr_get_relative_contribution_host($host_uid);
                $mag_formatted=sprintf("%0.2f",$mag_per_project*$host_relative_contribution);
                $grc_per_day=sprintf("%0.4f",$mag_per_project*$host_relative_contribution*$magnitude_unit);

                $graph=boincmgr_cache_function("canvas_graph_host_all_projects",array($host_uid));

                if(auth_is_admin($username))
                        $result.="<tr><td>$host_username_html</td><td>$domain_name_html</td><td>$host_info</td><td>$graph</td><td>$mag_formatted</td><td>$grc_per_day</td></tr>\n";
                else
                        $result.="<tr><td>$domain_name_html</td><td>$host_info</td><td>$graph</td><td>$mag_formatted</td><td>$grc_per_day</td></tr>\n";
        }
        $result.="</table>\n";
        $result.="</div>\n";

        return $result;
}

// Show BOINC results by project
function html_boinc_results_by_project() {
        global $username;

        $result="";

        $result.="<div id=boinc_results_block class=selectable_block>\n";
        $result.="<h2>BOINC results:</h2>\n";

        $result.="<p>That information we received from various BOINC projects:</p>\n";

        // Projects stats for admin is the pool stats
        $result.="<h3>Results by project</h3>\n";
        $result.="<table align=center>\n";
        $result.="<tr><th>Project</th><th>&Sigma; RAC</th><th>Mag 7d graph</th><th>Mag</th><th>&#8776;GRC/day</th></tr>\n";

        $magnitude_unit=boincmgr_get_magnitude_unit();
        $mag_per_project=boincmgr_get_mag_per_project();

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        if(auth_is_admin($username)) {
                $boinc_host_data_array=db_query_to_array("SELECT bphl.`project_uid`,bp.`name`,SUM(bphl.`expavg_credit`) AS rac,SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
GROUP BY bphl.`project_uid`,bp.`name` HAVING SUM(bphl.`expavg_credit`)>=1 ORDER BY bp.`name` ASC");
        } else {
                $boinc_host_data_array=db_query_to_array("SELECT bphl.`project_uid`,bp.`name`,SUM(bphl.`expavg_credit`) AS rac,SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
WHERE bh.`username_uid`='$username_uid_escaped' GROUP BY bphl.`project_uid`,bp.`name` HAVING SUM(bphl.`expavg_credit`)>=1 ORDER BY bp.`name` ASC");
        }
        foreach($boinc_host_data_array as $boinc_host_data) {
                $project_uid=$boinc_host_data['project_uid'];
                $expavg_credit=round($boinc_host_data['rac']);
                $project_name=$boinc_host_data['name'];
                $relative_credit=$boinc_host_data['relative_credit'];

                $expavg_credit_html=html_escape($expavg_credit);
                $project_name_html=html_escape($project_name);

                $expavg_credit_html=html_format_number($expavg_credit_html);

                if(auth_is_admin($username)) $project_relative_contribution=boincmgr_get_relative_contribution_project($project_uid);
                else $project_relative_contribution=boincmgr_get_relative_contribution_project_user($project_uid,$username_uid);

                $mag_formatted=sprintf("%0.2f",$mag_per_project*$project_relative_contribution);
                $grc_per_day=sprintf("%0.4f",$mag_per_project*$project_relative_contribution*$magnitude_unit);

                if(auth_is_admin($username)) $graph=boincmgr_cache_function("canvas_graph_project_total",array($project_uid));
                else $graph=boincmgr_cache_function("canvas_graph_username_project",array($username_uid,$project_uid));

                $result.="<tr><td>$project_name_html</td><td align=right>$expavg_credit_html</td><td>$graph</td><td>$mag_formatted</td><td>$grc_per_day</td></tr>\n";
        }
        $result.="</table>\n";
        $result.="</div>\n";

        return $result;
}

// Show BOINC results by user
function html_boinc_results_by_user() {
        global $username;

        $result="";

        $result.="<div id=boinc_results_block class=selectable_block>\n";
        $result.="<h2>BOINC results:</h2>\n";

        $result.="<p>That information we received from various BOINC projects:</p>\n";

        $result.="<h3>Results by user</h3>\n";
        $result.="<table align=center>\n";
        $result.="<tr><th>Username</th><th>Task stats</th><th>Mag 7d graph</th><th>Mag</th><th>&#8776;GRC/day</th></tr>\n";

        $magnitude_unit=boincmgr_get_magnitude_unit();
        $mag_per_project=boincmgr_get_mag_per_project();

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        if(auth_is_admin($username)) {
                $boinc_host_data_array=db_query_to_array("SELECT bh.`username_uid`,bu.`username`,SUM(bphl.`expavg_credit`) AS rac,SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
GROUP BY bh.`username_uid`,bu.`username` HAVING SUM(bphl.`expavg_credit`)>=1 ORDER BY bu.`username` ASC");
        } else {
                $boinc_host_data_array=db_query_to_array("SELECT bh.`username_uid`,bu.`username`,SUM(bphl.`expavg_credit`) AS rac,SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bh.`username_uid`='$username_uid_escaped'
GROUP BY bh.`username_uid`,bu.`username` HAVING SUM(bphl.`expavg_credit`)>=1 ORDER BY bu.`username` ASC");
        }
        foreach($boinc_host_data_array as $boinc_host_data) {
                $username_uid=$boinc_host_data['username_uid'];
                $expavg_credit=round($boinc_host_data['rac']);
                $user_name=$boinc_host_data['username'];
                $relative_credit=$boinc_host_data['relative_credit'];

                $expavg_credit_html=html_escape($expavg_credit);
                $user_name_html=html_escape($user_name);

                $expavg_credit_html=html_format_number($expavg_credit_html);

                $user_relative_contribution=boincmgr_get_relative_contribution_user($username_uid);

                $mag_formatted=sprintf("%0.2f",$mag_per_project*$user_relative_contribution);
                $grc_per_day=sprintf("%0.4f",$mag_per_project*$user_relative_contribution*$magnitude_unit);

                $graph=boincmgr_cache_function("canvas_graph_username",array($username_uid));
                if($username_uid=="") $tasks_url="";
                else $tasks_url="<a href='tasks.php?username_uid=$username_uid'>view</a>";

                $result.="<tr><td>$user_name_html</td><td>$tasks_url</td><td>$graph</td><td>$mag_formatted</td><td>$grc_per_day</td></tr>\n";
        }
        $result.="</table>\n";
        $result.="</div>\n";

        return $result;
}

// Show BOINC results for user
function html_boinc_results_all($threshold) {
        global $username;

        $result="";

        $threshold_escaped=db_escape($threshold);

        $result.="<div id=boinc_results_block class=selectable_block>\n";
        $result.="<h2>BOINC results:</h2>\n";

        $result.="<p>That information we received from various BOINC projects:</p>\n";

        $result.="<h3>Results for each project and each host</h3>\n";
        $result.="<table align=center>\n";

        $magnitude_unit=boincmgr_get_magnitude_unit();
        $mag_per_project=boincmgr_get_mag_per_project();

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        if(auth_is_admin($username)) {
                $result.="<tr><th>Username</th><th>Domain name</th><th>Project</th><th>RAC</th><th>Mag 7d graph</th><th>Mag</th><th>&#8776;GRC/day</th><th>Tasks</th></tr>\n";
                $boinc_host_data_array=db_query_to_array("
SELECT bu.`username`,bphl.`host_uid`,bphl.`project_uid`,bphl.`host_id`,bphl.`host_cpid`,bphl.`domain_name`,bphl.`p_model`,bp.`name`,bphl.`expavg_credit`,(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit
FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bphl.`expavg_credit`>'$threshold_escaped'
ORDER BY bu.`username`,bphl.`domain_name`,bp.`name` ASC");
        } else {
                $result.="<tr><th>Domain name</th><th>Project</th><th>RAC</th><th>Mag 7d graph</th><th>Mag</th><th>&#8776;GRC/day</th><th>Tasks</th></tr>\n";
                $boinc_host_data_array=db_query_to_array("
SELECT bphl.`host_uid`,bphl.`project_uid`,bphl.`host_id`,bphl.`host_cpid`,bphl.`domain_name`,bphl.`p_model`,bp.`name`,bphl.`expavg_credit`,(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit
FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
WHERE bphl.`expavg_credit`>'$threshold_escaped' AND bh.`username_uid`='$username_uid_escaped' ORDER BY bphl.`domain_name`,bp.`name` ASC");
        }
        foreach($boinc_host_data_array as $boinc_host_data) {
                if(isset($boinc_host_data['username'])) $host_username=$boinc_host_data['username'];
                else $host_username="";
                $host_uid=$boinc_host_data['host_uid'];
                $project_uid=$boinc_host_data['project_uid'];
                $host_cpid=$boinc_host_data['host_cpid'];
                $host_id=$boinc_host_data['host_id'];
                $domain_name=boincmgr_domain_decode($boinc_host_data['domain_name']);
                $p_model=$boinc_host_data['p_model'];
                $expavg_credit=$boinc_host_data['expavg_credit'];
                $project_name=$boinc_host_data['name'];
                $relative_credit=$boinc_host_data['relative_credit'];

                //$host_info=boincmgr_get_host_short_info($host_uid);

                $host_username_html=html_escape($host_username);
                $host_cpid_html=html_escape($host_cpid);
                $domain_name_html=html_escape($domain_name);
                $p_model_html=html_escape($p_model);
                $expavg_credit=sprintf("%0.4f",$expavg_credit);
                $expavg_credit_html=html_escape($expavg_credit);
                $project_name_html=html_escape($project_name);

                $tasks_url="<a href='tasks.php?project_uid=$project_uid&host_id=$host_id'>view</a>";

                $project_host_relative_contribution=boincmgr_get_relative_contribution_project_host($project_uid,$host_uid);

                $mag_formatted=sprintf("%0.2f",$mag_per_project*$project_host_relative_contribution);
                $grc_per_day=sprintf("%0.4f",$mag_per_project*$project_host_relative_contribution*$magnitude_unit);

                $graph=boincmgr_cache_function("canvas_graph_host_project",array($host_uid,$project_uid));

                $p_model_html=str_replace("[","<br>[",$p_model_html);
                if(auth_is_admin($username))
                        $result.="<tr><td>$host_username_html</td><td>$domain_name_html</td><td>$project_name_html</td><td align=right>$expavg_credit_html</td><td>$graph</td><td>$mag_formatted</td><td>$grc_per_day</td><td>$tasks_url</td></tr>\n";
                else
                        $result.="<tr><td>$domain_name_html</td><td>$project_name_html</td><td align=right>$expavg_credit_html</td><td>$graph</td><td>$mag_formatted</td><td>$grc_per_day</td><td>$tasks_url</td></tr>\n";
        }
        $result.="</table>\n";
        $result.="</div>\n";

        return $result;
}

// Show user control form
function html_user_control_form() {
        global $username_token;

        $result="";
        $users_array=db_query_to_array("SELECT `uid`,`username`,`email`,`currency`,`payout_address`,`status` FROM `boincmgr_users`");
        $result.="<div id=user_control_block class=selectable_block>\n";
        $result.="<h2>User control</h2>\n";
        $result.="<p><table align=center>\n";
        $result.="<tr><th>Username</th><th>e-mail</th><th>Currency</th><th>Address</th><th>Last sync</th><th>Status</th><th>Action</th></tr>\n";

        $form_hidden_action="<input type=hidden name=action value='change_user_status'>";
        $form_hidden_token="<input type=hidden name=token value='$username_token'>";
        $user_options="<select name=status><option>banned</option><option selected>user</option><option>admin</option><option>donator</option></select>";
        $submit_button="<input type=submit value='change'>";

        foreach($users_array as $user_record) {
                $uid=$user_record['uid'];
                $username=$user_record['username'];
                $email=$user_record['email'];
                $currency=$user_record['currency'];
                $payout_address=$user_record['payout_address'];
                $status=$user_record['status'];

                $user_uid_escaped=db_escape($uid);
                $last_sync=db_query_to_variable("SELECT MAX(`timestamp`) FROM `boincmgr_hosts` WHERE `username_uid`='$user_uid_escaped'");

                $username_html=html_escape($username);
                $email_html=html_escape($email);
                $currency_html=html_escape($currency);
                $payout_address_html=html_escape($payout_address);
                $last_sync_html=html_escape($last_sync);
                $form_hidden_user_uid="<input type=hidden name=user_uid value='$uid'>";

                switch($status) {
                        case "user":
                                $status_html="<span class='user_status_user'>".html_escape($status)."</span>";
                                break;
                        case "admin":
                                $status_html="<span class='user_status_admin'>".html_escape($status)."</span>";
                                break;
                        case "banned":
                                $status_html="<span class='user_status_banned'>".html_escape($status)."</span>";
                                break;
                        default:
                        case "donator":
                                $status_html="<span class='user_status_donator'>".html_escape($status)."</span>";
                                break;
                }

                $actions="<form name=change_user method=post>".$form_hidden_action.$form_hidden_user_uid.$form_hidden_token.$user_options.$submit_button."</form>";
                $result.="<tr><td>$username_html</td><td>$email_html</td><td>$currency</td><td>$payout_address_html</td><td>$last_sync_html</td><td>$status_html</td><td>$actions</td></tr>\n";
        }
        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// Show project control form
function html_project_control_form() {
        global $username_token,$pool_cpid;
        $result="";
        $projects_array=db_query_to_array("SELECT `uid`,`name`,`project_url`,`cpid`,`url_signature`,`weak_auth`,`team`,`status`,`timestamp` FROM `boincmgr_projects` ORDER BY `name` ASC");
        $result.="<div id=project_control_block class=selectable_block>\n";
        $result.="<h2>Project control</h2>\n";
        $result.="<p>Enabled (or auto enabled) means project data updated and rewards are on. Stats only - users cannot attach by themselves, rewards on, auto disabled - only downloading stats, no rewards, disabled - do not check anything about this project (no rewards too).</p>";
        $result.="<p><table align=center>\n";
        $result.="<tr><th>Name</th><th>URL</th><th>CPID</th><th>Weak auth</th><th>Team</th><th>Last query</th><th>Last update</th><th>Status</th><th>Action</th></tr>\n";

        $form_hidden_action="<input type=hidden name=action value='change_project_status'>";
        $form_hidden_token="<input type=hidden name=token value='$username_token'>";
        $project_options="<select name=status><option>auto</option><option>enabled</option><option>stats only</option><option>disabled</option></select>";
        $submit_button="<input type=submit value='change'>";

        foreach($projects_array as $project_record) {
                $uid=$project_record['uid'];
                $name=$project_record['name'];
                $project_url=$project_record['project_url'];
                $url_signature=$project_record['url_signature'];
                $status=$project_record['status'];
                $cpid=$project_record['cpid'];
                $weak_auth=$project_record['weak_auth'];
                $team=$project_record['team'];
                $timestamp=$project_record['timestamp'];
                $name_html=html_escape($name);
                $project_url_html=html_escape($project_url);
                $url_signature_html=html_escape($url_signature);

                if($pool_cpid==$cpid) $cpid_html="<span class='status_good'>match</span>";
                else if ($cpid=="") $cpid_html="<span class='status_unknown'>no cpid</span>";
                else $cpid_html="<span class='status_bad'>mismatch</span>";

                $form_hidden_project_uid="<input type=hidden name=project_uid value='$uid'>";

                switch($status) {
                        case "auto":
                                $status_html="<span class='project_status_auto'>".html_escape($status)."</span>";
                                break;
                        case "auto enabled":
                        case "enabled":
                                $status_html="<span class='project_status_enabled'>".html_escape($status)."</span>";
                                break;
                        default:
                        case "stats only":
                                $status_html="<span class='project_status_stats_only'>".html_escape($status)."</span>";
                                break;
                        case "auto disabled":
                        case "disabled":
                                $status_html="<span class='project_status_disabled'>".html_escape($status)."</span>";
                                break;
                }

                if($weak_auth=="") $weak_auth_html="<span class='status_unknown'>no key</span>";
                else $weak_auth_html="<span class='status_good'>present</span>";

                if($team=="Gridcoin") $team_html="<span class='status_good'>$team</span>";
                else $team_html="<span class='status_unknown'>unknown</span>";

                $timestamp_html=html_escape($timestamp);

                $view_query="<a href='?action=view_project_last_query&project_uid=$uid&token=$username_token'>view</a>";

                $actions="<form name=change_project method=post>".$form_hidden_action.$form_hidden_project_uid.$form_hidden_token.$project_options.$submit_button."</form>";
                $result.="<tr><td>$name_html</td><td>$project_url_html</td><td>$cpid_html</td><td>$weak_auth_html</td><td>$team_html</td><td>$view_query</td><td>$timestamp_html</td><td>$status_html</td><td>$actions</td></tr>\n";
        }
        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// Show payouts
function html_payouts() {
        global $username;
        global $username_token;

        $result="";
        $result.="<div id=payouts_block class=selectable_block>\n";

        $result.="<h2>Payouts</h2>\n";
        $owes_data_array=db_query_to_array("SELECT bp.`payout_address`,bp.`currency`,SUM(bp.`amount`) AS amount,MIN(bbp.`start_date`) AS start_date,MAX(bbp.`stop_date`) AS stop_date
FROM `boincmgr_payouts` AS bp
LEFT OUTER JOIN `boincmgr_billing_periods` AS bbp ON bbp.`uid`=bp.`billing_uid`
WHERE bp.`txid` IS NULL GROUP BY bp.`payout_address`,bp.`currency` ORDER BY bp.`payout_address` ASC");
        if(count($owes_data_array)) {
                $result.="<h3>Pool owes:</h3>\n";
//              $result.="<p>These rewards not send yet, because payout limit is not reached. Fee is tx fee + service fee.</p>";
                $result.="<table align=center>\n";
                $result.="<caption>Pool owes table. These rewards not send yet, because payout limit is not reached. Fee is tx fee + service fee.</caption>";
                $result.="<tr></th><th>Address</th><th>Currency amount</th><th>Interval from</th><th>Interval to</th></tr>\n";
                foreach($owes_data_array as $owe_data) {
                        $payout_address=$owe_data['payout_address'];
                        $amount=$owe_data['amount'];
                        $currency=$owe_data['currency'];
                        $start_date=$owe_data['start_date'];
                        $stop_date=$owe_data['stop_date'];

                        $payout_address_link=html_payout_address_link($currency,$payout_address);
                        $amount=sprintf("%0.8f",$amount);

                        $payout_threshold=boincmgr_get_payout_limit($currency);
                        $payout_fee=boincmgr_get_tx_fee_estimation($currency);
                        $payout_service_fee=boincmgr_get_service_fee($currency);
                        $total_fee=$payout_fee+$payout_service_fee;
                        if($currency=="GRC2") $currency="GRC";

                        $result.="<tr><td>$payout_address_link</td><td title='Payout after $payout_threshold $currency'>$amount $currency</td><td>$start_date</td><td>$stop_date</td></tr>\n";
                }
                $result.="</table>\n";
        }
        $result.="<p>Last 10 billings from pool:</p>\n";

        $billings_array=db_query_to_array("SELECT `uid`,`comment`,`start_date`,`stop_date`,`reward` FROM `boincmgr_billing_periods` ORDER BY `stop_date` DESC LIMIT 10");
        foreach($billings_array as $billing) {
                $billing_uid=$billing['uid'];
                $comment=$billing['comment'];
                $start_date=$billing['start_date'];
                $stop_date=$billing['stop_date'];
                $reward=$billing['reward'];

                $reward=round($reward,4);

                $billing_uid_escaped=db_escape($billing_uid);

                $result.="<h3>For period from $start_date to $stop_date pool rewarded with $reward gridcoins ($comment)</h3>\n";
                $payout_data_array=db_query_to_array("SELECT `currency`,`grc_amount`,`rate`,`payout_address`,`amount`,`txid`,`timestamp` FROM `boincmgr_payouts`
WHERE `billing_uid`='$billing_uid_escaped' AND `currency` IN ('GRC','GRC2') ORDER BY `payout_address` ASC");

//              $result.="<p>Gridcoin payouts</p>\n";
                $result.="<p><table align=center>\n";
                $result.="<caption>Gridcoin payouts</caption>";
                $result.="<tr></th><th>Address</th><th>GRC amount</th><th>TX ID</th><th>Timestamp</th></tr>\n";
                foreach($payout_data_array as $payout_data) {
                        $payout_address=$payout_data['payout_address'];
                        $grc_amount=$payout_data['grc_amount'];
                        $rate=$payout_data['rate'];
                        $currency=$payout_data['currency'];
                        $amount=$payout_data['amount'];
                        $txid=$payout_data['txid'];
                        $timestamp=$payout_data['timestamp'];

                        $grc_amount=round($grc_amount,8);
                        $amount=round($amount,8);

                        $payout_address_link=html_payout_address_link($currency,$payout_address);
                        $grc_amount_html=html_escape(sprintf("%0.8F",$grc_amount));
                        $currency_html=html_escape($currency);
                        $rate_html=html_escape($rate);
                        $amount_html=html_escape($amount);
                        $txid_link=html_txid_link($currency,$txid);
                        $timestamp_html=html_escape($timestamp);

                        $result.="<tr><td>$payout_address_link</td><td>$grc_amount_html</td><td>$txid_link</td><td>$timestamp_html</td></tr>\n";
                        }
                $result.="</table></p>\n";

                $payout_data_array=db_query_to_array("SELECT `currency`,`grc_amount`,`rate`,`payout_address`,`amount`,`txid`,`timestamp` FROM `boincmgr_payouts` WHERE `billing_uid`='$billing_uid_escaped' AND `currency` NOT IN ('GRC','GRC2') ORDER BY `payout_address` ASC");
                if(count($payout_data_array)==0) continue;

//              $result.="<p>Alternative currencies</p>\n";
                $result.="<p><table align=center>\n";
                $result.="<caption>Alternative currencies</caption>";
                $result.="<tr></th><th>Address</th><th>Amount</th><th>TX ID</th><th>Timestamp</th></tr>\n";
                foreach($payout_data_array as $payout_data) {
                        $payout_address=$payout_data['payout_address'];
                        $grc_amount=$payout_data['grc_amount'];
                        $rate=$payout_data['rate'];
                        $currency=$payout_data['currency'];
                        $amount=$payout_data['amount'];
                        $txid=$payout_data['txid'];
                        $timestamp=$payout_data['timestamp'];

                        $grc_amount=round($grc_amount,8);
                        $amount=round($amount,8);

                        $payout_address_link=html_payout_address_link($currency,$payout_address);
                        $grc_amount_html=html_escape(sprintf("%0.8F",$grc_amount));
                        $currency_html=html_escape($currency);
                        $rate_html=html_escape(sprintf("%0.8F",$rate));
                        $amount_html=html_escape(sprintf("%0.8F",$amount));
                        $txid_link=html_txid_link($currency,$txid);
                        $timestamp_html=html_escape($timestamp);

                        $result.="<tr><td>$payout_address_link</td><td title='Rate $rate_html $currency_html/GRC'>$amount_html $currency_html<br>($grc_amount_html GRC)</td><td>$txid_link</td><td>$timestamp_html</td></tr>\n";
                }
                $result.="</table></p>\n";
        }

        $result.="</div>\n";
        return $result;
}

function html_billing_form() {
        global $username;
        global $username_token;

        $result="";
        $result.="<div id=billing_block class=selectable_block>\n";

        $start_date=db_query_to_variable("SELECT MAX(`stop_date`) FROM `boincmgr_billing_periods`");
        if($start_date=="") $start_date="2018-01-01 20:20:16";
        $stop_date=db_query_to_variable("SELECT NOW()");
        $balance=boincmgr_get_variable("hot_wallet_balance");
        $project_selector=html_project_selector();

        $result.=<<<_END
<h2>Billing</h2>
<form name=billing method=post>
<p>Fill data carefully, that cannot be undone!</p>
<p>Hot wallet balance: $balance GRC</p>
<input type=hidden name=action value='billing'>
<input type=hidden name=token value='$username_token'>
<p>Project to pay rewards $project_selector</p>
<p><label><input type=checkbox name=antiexp_rewards_flag> use antiexp rewards</label></p>
<p>Comment <input type=text name=comment placeholder="Reward comment"></p>
<p>Begin of period <input type=text name=start_date value='$start_date'></p>
<p>End of period <input type=text name=stop_date value='$stop_date'></p>
<p>Total reward <input type=text name=reward value='0.0000'></p>
<p><label><input type=checkbox name=check_rewards checked> check only, do not send</label></p>
<p><input type=submit value='Send rewards'></p>
</form>
_END;


        $result.="</div>\n";

        $owes_data_array=db_query_to_array("SELECT bp.`payout_address`,bp.`currency`,SUM(bp.`amount`) AS amount,MIN(bbp.`start_date`) AS start_date,MAX(bbp.`stop_date`) AS stop_date
FROM `boincmgr_payouts` AS bp
LEFT OUTER JOIN `boincmgr_billing_periods` AS bbp ON bbp.`uid`=bp.`billing_uid`
WHERE bp.`txid` IS NULL GROUP BY bp.`payout_address`,bp.`currency` ORDER BY bp.`payout_address` ASC");
        if(count($owes_data_array)) {
                $result.="<h3>Pool owes:</h3>\n";
                foreach($owes_data_array as $owe_data) {
                        $result.="<p>\n";
                        $result.="<table align=center>\n";
                        $payout_address=$owe_data['payout_address'];
                        $amount=$owe_data['amount'];
                        $currency=$owe_data['currency'];
                        $start_date=$owe_data['start_date'];
                        $stop_date=$owe_data['stop_date'];

                        $payout_address_link=html_payout_address_link($currency,$payout_address);
                        $payout_address_html=html_escape($payout_address);
                        $amount=sprintf("%0.8f",$amount);

                        $payout_threshold=boincmgr_get_payout_limit($currency);
                        $payout_fee=boincmgr_get_tx_fee_estimation($currency);
                        $payout_service_fee=boincmgr_get_service_fee($currency);
                        $total_fee=$payout_fee+$payout_service_fee;
                        if($currency=="GRC2") $currency="GRC";

                        $result.="<tr><th>Address</th><td>$payout_address_link</td><td rowspan=6><img src='qr.php?str=$payout_address_html'></td></tr>\n";
                        $result.="<tr><th>Amount</th><td>$amount $currency</td></tr>\n";
                        $result.="<tr><th>Payout threshold</th><td>$payout_threshold</td></tr>\n";
                        $result.="<tr><th>Fee</th><td>$total_fee</td></tr>\n";
                        $result.="<tr><th>Interval</th><td>From $start_date to $stop_date</td></tr>\n";
                        //$result.="<tr><td>$payout_address_link</td><td>$amount</td><td>$currency</td><td>$payout_threshold</td><td>$total_fee</td><td>$start_date</td><td>$stop_date</td></tr>\n";
                        $tx_set_form=<<<_END
<form name=set_tx method=post>
TX ID <input type=text name=txid>
<input type=hidden name=action value='set_txid'>
<input type=hidden name=token value='$username_token'>
<input type=hidden name=payout_address value='$payout_address'>
<input type=submit value='Set TX ID'>
</form>

_END;
                        $result.="<tr><th>Set TX ID</th><td>$tx_set_form</td></tr>\n";
                        $result.="</table>\n";
                        $result.="</p>\n";
                }
        }

        return $result;
}

// Show log
function html_view_log() {
        $result="";
        $result.="<div id=log_block class=selectable_block>\n";
        $result.="<h2>View log</h2>\n";
        $result.="<p>Last 100 messages:</p>\n";
        $result.="<p><table align=center>\n";
        $result.="<tr><th>Timestamp</th><th>Message</th></tr>\n";
        $log_array=db_query_to_array("SELECT `message`,`timestamp` FROM `boincmgr_log` ORDER BY `timestamp` DESC LIMIT 100");
        foreach($log_array as $data) {
                $message=$data['message'];
                $timestamp=$data['timestamp'];

                $message_html=html_escape($message);

                $message_html=str_replace("\n","<br>\n",$message_html);
                $timestamp_html=html_escape($timestamp);

                $result.="<tr><td>$timestamp_html</td><td>$message_html</td></tr>\n";
        }
        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// Show pool stats
function html_pool_stats() {
        $result="";
        $result.="<div id=pool_stats_block class=selectable_block>\n";
        $result.="<h2>Pool stats</h2>\n";
        $result.="<p><a href='tasks.php'>Global task report page</a></p>\n";
        $result.="<p>Enabled (or auto enabled) means project data updated and rewards are on. Stats only - users cannot attach by themselves, rewards on, auto disabled - only downloading stats, no rewards, disabled - do not check anything about this project (no rewards too).</p>";

        $start_date=db_query_to_variable("SELECT MAX(`stop_date`) FROM `boincmgr_billing_periods`");
        if($start_date=="") $start_date="2018-01-01 20:20:16";
        $stop_date=db_query_to_variable("SELECT NOW()");

        $result.="<p><table align=center>\n";
        $result.="<tr><th>Project</th><th>Team RAC</th><th>Pool RAC</th><th>Pool mag</th><th>&#8776;Pool GRC/day</th><th>Hosts</th><th>Task report</th><th>Status</th><th>Pool mag 7d graph</th></tr>\n";

        $project_array=db_query_to_array("SELECT `uid`,`name`,`project_url`,`expavg_credit`,`team_expavg_credit`,`status` FROM `boincmgr_projects` ORDER BY `name` ASC");

        $magnitude_unit=boincmgr_get_magnitude_unit();
        $mag_per_project=boincmgr_get_mag_per_project();

        $total_pool_mag=0;
        $total_pool_grc_per_day=0;

        foreach($project_array as $project_data) {
                $name=$project_data['name'];
                $project_url=$project_data['project_url'];
                $uid=$project_data['uid'];
                $expavg_credit=$project_data['expavg_credit'];
                $team_expavg_credit=$project_data['team_expavg_credit'];
                $status=$project_data['status'];

                $project_uid_escaped=db_escape($uid);
                $pool_project_hosts=db_query_to_variable("SELECT count(*) FROM `boincmgr_attach_projects` AS bap
LEFT OUTER JOIN `boincmgr_host_projects` AS bhp ON bhp.`project_uid`=bap.`project_uid` AND bhp.`host_uid`=bap.`host_uid`
WHERE bap.`project_uid`='$project_uid_escaped' AND bap.`host_uid` IS NOT NULL");

                $project_relative_contribution=boincmgr_get_relative_contribution_project($uid);
                $mag_formatted=sprintf("%0.2f",$mag_per_project*$project_relative_contribution);
                $grc_per_day=sprintf("%0.4f",$mag_per_project*$project_relative_contribution*$magnitude_unit);

                $expavg_credit=round($expavg_credit);

                $name_link=html_project_name_link($name,$project_url);
                $team_expavg_credit_html=html_escape($team_expavg_credit);
                $expavg_credit_html=html_escape($expavg_credit);
                $pool_project_hosts_html=html_escape($pool_project_hosts);
                $task_report_url="<a href='tasks.php?project_uid=$uid'>view</a>";

                switch($status) {
                        case "auto enabled":
                                $status_html="<span class='project_status_auto'>".html_escape($status)."</span>";
                        case "enabled":
                                $status_html="<span class='project_status_enabled'>".html_escape($status)."</span>";
                                break;
                        default:
                        case "auto":
                                $pool_grc_per_day=0;
                                $project_magnitude=0;
                        case "stats only":
                                $status_html="<span class='project_status_stats_only'>".html_escape($status)."</span>";
                                break;
                        case "auto disabled":
                        case "disabled":
                                $status_html="<span class='project_status_disabled'>".html_escape($status)."</span>";
                                $pool_grc_per_day=0;
                                break;
                }

                $pool_grc_per_day_html=html_escape($grc_per_day);

                $total_pool_grc_per_day+=$grc_per_day;
                $total_pool_mag+=$mag_formatted;

                $team_expavg_credit_html=html_format_number($team_expavg_credit_html);
                $expavg_credit_html=html_format_number($expavg_credit_html);
                $graph=boincmgr_cache_function("canvas_graph_project_total",array($uid));
                //$graph=canvas_graph_project_total($uid);

                $result.="<tr><td>$name_link</td><td align=right>$team_expavg_credit_html</td><td align=right>$expavg_credit_html</td><td align=right>$mag_formatted</td><td align=right>$grc_per_day</td><td>$pool_project_hosts_html</td><td>$task_report_url</td><td>$status_html</td><td>$graph</td></tr>\n";
        }

        $total_pool_mag_html=html_format_number($total_pool_mag);
        $total_pool_grc_per_day_html=html_format_number($total_pool_grc_per_day);
        $result.="<tr><td><strong>Total</strong></td><td align=right></td><td align=right></td><td align=right>$total_pool_mag_html</td><td align=right>$total_pool_grc_per_day_html</td><td></td><td></td><td></td><td></td></tr>\n";

        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

function html_pool_info_editor() {
        global $username_token;

        $pool_info=db_query_to_variable("SELECT `value` FROM `boincmgr_variables` WHERE `name`='pool_info'");
        $pool_info_html=html_escape($pool_info);

        $result=<<<_END
<div id=pool_info_editor_block class=selectable_block>
<h2>Pool info editor</h2>
<form name=edit_pool_info method=post>
<input type=hidden name=action value='edit_pool_info'>
<input type=hidden name=token value='$username_token'>
<p><textarea name=pool_info cols=120 rows=25>
$pool_info_html
</textarea></p>
<p><input type=submit value='Save'></p>
</form>
</div>
_END;

        return $result;
}

function html_host_options_form() {
        global $username_token;
        $result="";
        $result.=<<<_END
<div class='pre_window' id='popup_form'>
<div class='window'>
<form name=host_options method=post>
<input type=hidden name=action value='update_project_settings'>
<input type=hidden name=token value='$username_token'>
<input type=hidden id='host_options_form_attach_uid' name=attached_uid value=''>
<p>Host name: <span id='host_options_form_host_name'></span></p>
<p>Project name: <span id='host_options_form_project_name'></span></p>
<p>Resource share: <input id='host_options_form_resource_share' type=text name='resource_share' value='100'></p>
<p><label><input type=checkbox id='host_options_form_detach' name='detach'> detach and drop all tasks</label></p>
<p><label><input type=checkbox id='host_options_form_detach_when_done' name='detach_when_done'> detach when done</label></p>
<p><label><input type=checkbox id='host_options_form_suspend' name='suspend'> suspend</label></p>
<p><label><input type=checkbox id='host_options_form_no_more_work' name='dont_request_more_work'> don't request more work</label></p>
<p><label><input type=checkbox id='host_options_form_abort' name='abort_not_started'> abort not started</label></p>
<p><label><input type=checkbox id='host_options_form_no_cpu' name='no_cpu'> no CPU tasks</label></p>
<p><label><input type=checkbox id='host_options_form_no_cuda' name='no_cuda'> no CUDA tasks</label></p>
<p><label><input type=checkbox id='host_options_form_no_ati' name='no_ati'> no ATI tasks</label></p>
<p><label><input type=checkbox id='host_options_form_no_intel' name='no_intel'> no intel GPU tasks</label></p>
<p><input type=submit value='Save'> <input type=button value='Cancel' onClick='document.getElementById("popup_form").style.display="none";'></p>
</form>
</div>
</div>
_END;
        return $result;
}

// Exchange rates block
function html_currencies() {
        $result="";
        $result.="<div id=currencies_block class=selectable_block>\n";
        $result.="<h2>Payout currencies</h2>\n";

        $result.="<p><table align=center>\n";
        $result.="<caption>Data for payout currencies:</caption>\n";
        $result.="<tr><th>Full name</th><th>Rate per 1 GRC</th><th>Payout limit</th><th>TX fee</th><th>Project fee</th></tr>\n";

        $currency_data_array=db_query_to_array("SELECT `name`,`full_name`,`payout_limit`,`tx_fee`,`project_fee` FROM `boincmgr_currency` ORDER BY `name`");

        foreach($currency_data_array as $currency_data) {
                $name=$currency_data['name'];
                $full_name=$currency_data['full_name'];
                $payout_limit=$currency_data['payout_limit'];
                $tx_fee=$currency_data['tx_fee'];
                $project_fee=$currency_data['project_fee'];
                $exchange_rate=boincmgr_get_payout_rate($name);

                $payout_limit_in_grc=sprintf("%0.2f",$payout_limit/$exchange_rate);
                $tx_fee_in_grc=sprintf("%0.2f",$tx_fee/$exchange_rate);
                $exchange_rate=sprintf("%0.8f",$exchange_rate);

                $name_html=html_escape($name);
                $full_name_html=html_escape($full_name);
                $payout_limit_html=html_escape($payout_limit." $name ($payout_limit_in_grc GRC)");
                $payout_limit_in_grc_html=html_escape($payout_limit_in_grc);
                $tx_fee_html=html_escape($tx_fee." $name ($tx_fee_in_grc GRC)");
                $project_fee_html=html_escape($project_fee);
                $exchange_rate_html=html_escape($exchange_rate." $name");

                $result.="<tr><td>$full_name_html</td><td>$exchange_rate_html</td><td align=right>$payout_limit_html</td><td align=right>$tx_fee_html</td><td align=right>$project_fee_html</td></tr>\n";
        }

        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// Block explorer block
function html_block_explorer() {
        global $pool_cpid;

        $result="";
        $result.="<div id=block_explorer_block class=selectable_block>\n";
        $result.="<h2>Last 500 blocks</h2>\n";

        $result.="<p><table align=center>\n";
        $result.="<caption>Data from blockchain (at least 110 confirmations, updated hourly):</caption>\n";
        $result.="<tr><th>Number</th><th>Hash</th><th>Mint</th><th>Interest</th><th>CPID</th><th>Rewards</th><th>Timestamp</th></tr>\n";

        $blocks_data_array=db_query_to_array("SELECT `number`,`hash`,`mint`,`interest`,`cpid`,`rewards_sent`,`timestamp` FROM `boincmgr_blocks` ORDER BY `number` DESC LIMIT 500");

        foreach($blocks_data_array as $blocks_data) {
                $number=$blocks_data['number'];
                $hash=$blocks_data['hash'];
                $mint=$blocks_data['mint'];
                $interest=$blocks_data['interest'];
                $cpid=$blocks_data['cpid'];
                $rewards_sent=$blocks_data['rewards_sent'];
                $timestamp=$blocks_data['timestamp'];

                $number_html=html_escape($number);
                $number_link="<a href='https://www.gridcoinstats.eu/block/$number_html'>$number_html</a>";

                $hash_short_html=html_escape(substr($hash,0,10)."...");
                $hash_html=html_escape($hash);
                $hash_link="<a href='https://www.gridcoinstats.eu/block/$hash_html'>$hash_short_html</a>";

                $mint_html=html_escape($mint);
                $interest_html=html_escape($interest);

                $cpid_html=html_escape($cpid);
                if($cpid=="INVESTOR") $cpid_link=$cpid_html;
                else $cpid_link="<a href='https://www.gridcoinstats.eu/cpid/$cpid_html'>$cpid_html</a>";

                $timestamp_html=html_escape($timestamp);
                if($pool_cpid==$cpid) {
                        if($rewards_sent) $rewards_html="<span class='status_good'>yes</span>";
                        else $rewards_html="<span class='status_bad'>no</span>";
                } else {
                        $rewards_html="not mine";
                }

                $result.="<tr><td>$number_link</td><td>$hash_link</td><td>$mint_html</td><td>$interest_html</td><td>$cpid_link</td><td>$rewards_html</td><td>$timestamp_html</td></tr>\n";
        }

        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// Messages form
function html_message_send() {
        global $username;
        global $username_token;

        $username_uid=boincmgr_get_username_uid($username);
        $email=boincmgr_get_user_email($username_uid);

        $result=<<<_END
<div id=message_send_block class=selectable_block>
<h2>Feedback</h2>
<p>You can ask questions here or just send a random message to the pool administration. Don't forget to set a reply address if you to get a reply.</p>
<form name=messages method=POST>
<input type=hidden name="action" value="send_message">
<input type=hidden name="token" value="$username_token">
<p>Reply to (if you want a reply) <input type=text name=reply_to value='$email' size=50></p>
<p><textarea name=message cols=60 rows=10></textarea></p>
<p><input type=submit value='Send'></p>
</form>
_END;
        return $result;
}

// View messages form
function html_messages_view() {
        $result="";
        $result.="<div id=messages_view_block class=selectable_block>\n";
        $result.="<h2>Last 100 messages</h2>\n";

        $result.="<p><table align=center>\n";
        $result.="<tr><th>Username</th><th>Reply to</th><th>Message</th><th>Timestamp</th></tr>\n";

        $messages_data_array=db_query_to_array("SELECT `username_uid`,`reply_to`,`message`,`timestamp` FROM `boincmgr_messages` ORDER BY `timestamp` DESC LIMIT 100");

        foreach($messages_data_array as $messages_data) {
                $username_uid=$messages_data['username_uid'];
                $reply_to=$messages_data['reply_to'];
                $message=$messages_data['message'];
                $timestamp=$messages_data['timestamp'];

                if($username_uid!='') {
                        $username_html=html_escape(boincmgr_get_user_name($username_uid));
                } else {
                        $username_html="";
                }
                $reply_to_html=html_escape($reply_to);
                $message_html=html_escape($message);
                $message_html=str_replace("\n","<br>\n",$message_html);
                $timestamp_html=html_escape($timestamp);

                $result.="<tr><td>$username_html</td><td>$reply_to_html</td><td>$message_html</td><td>$timestamp_html</td></tr>\n";
        }

        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// View email form
function html_email_view() {
        $result="";
        $result.="<div id=email_view_block class=selectable_block>\n";
        $result.="<h2>Last 100 email messages from pool</h2>\n";

        $result.="<p><table align=center>\n";
        $result.="<tr><th>To</th><th>Subject</th><th>Body</th><th>Is sent</th><th>Is success</th><th>Timestamp</th></tr>\n";

        $messages_data_array=db_query_to_array("SELECT `to`,`subject`,`message`,`is_sent`,`is_success`,`timestamp` FROM `boincmgr_email` ORDER BY `timestamp` DESC LIMIT 100");

        foreach($messages_data_array as $messages_data) {
                $to=$messages_data['to'];
                $subject=$messages_data['subject'];
                $message=$messages_data['message'];
                $is_sent_html=$messages_data['is_sent']?"yes":"no";
                $is_success_html=$messages_data['is_success']?"yes":"no";
                $timestamp=$messages_data['timestamp'];

                $to_html=html_escape($to);
                $subject_html=html_escape($subject);
                $message_html=html_escape($message);
                $message_html=str_replace("\n","<br>\n",$message_html);
                $timestamp_html=html_escape($timestamp);

                $result.="<tr><td>$to_html</td><td>$subject_html</td><td>$message_html</td><td>$is_sent_html</td><td>$is_success_html</td><td>$timestamp_html</td></tr>\n";
        }

        $result.="</table></p>\n";
        $result.="</div>\n";
        return $result;
}

// Rating by host magnitude
function html_rating_by_host_mag() {
        global $username;

        $result="";

        $result.="<div id=rating_by_host_mag_block class=selectable_block>\n";
        $result.="<h3>Rating by host magnitude</h3>\n";
        $result.="<table align=center>\n";

        $magnitude_unit=boincmgr_get_magnitude_unit();
        $mag_per_project=boincmgr_get_mag_per_project();

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        $result.="<tr><th>№</th><th>Username</th><th>Domain name</th><th>Summary</th><th>Magnitude</th></tr>\n";
        $host_stats_data_array=db_query_to_array("SELECT bu.`username`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model`,SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`)) AS magnitude
FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bu.`username`<>'' AND bp.`status` IN ('enabled','auto enabled','stats only')
GROUP BY bu.`username`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model`
HAVING SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`))>=0.01
ORDER BY SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) DESC
LIMIT 100");

        $n=1;
        foreach($host_stats_data_array as $host_data) {
                $host_username=$host_data['username'];
                $host_uid=$host_data['host_uid'];
                $domain_name=boincmgr_domain_decode($host_data['domain_name']);
                $p_model=$host_data['p_model'];
                $magnitude=round($host_data['magnitude'],2);
                $host_short_info=boincmgr_get_host_short_info($host_uid);

                $host_username_html=html_escape($host_username);
                $domain_name_html=html_escape($domain_name);
                $p_model_html=html_escape($p_model);

                $magnitude_html=html_format_number($magnitude);
                $host_short_info_html=html_escape($host_short_info);

                $p_model_html=str_replace("[","<br>[",$p_model_html);
                $result.="<tr><td>$n</td><td>$host_username_html</td><td>$domain_name_html</td><td>$host_short_info_html</td><td align=right>$magnitude_html</td></tr>\n";
                $n++;
        }
        $result.="</table>\n";
        $result.="</div>\n";

        return $result;
}

// Rating by host and project magnitude
function html_rating_by_host_project_mag() {
        global $username;

        $result="";

        $result.="<div id=rating_by_host_project_mag_block class=selectable_block>\n";
        $result.="<h3>Rating by host magnitude</h3>\n";
        $result.="<table align=center>\n";

        $magnitude_unit=boincmgr_get_magnitude_unit();
        $mag_per_project=boincmgr_get_mag_per_project();

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        $result.="<tr><th>№</th><th>Username</th><th>Project name</th><th>Domain name</th><th>Summary</th><th>Magnitude</th></tr>\n";
        $host_stats_data_array=db_query_to_array("SELECT bu.`username`,bp.`name` AS project_name,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model`,SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`)) AS magnitude
FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bu.`username`<>'' AND bp.`status` IN ('enabled','auto enabled','stats only')
GROUP BY bu.`username`,bp.`name`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model`
HAVING SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`))>=0.01
ORDER BY SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) DESC
LIMIT 100");

        $n=1;
        foreach($host_stats_data_array as $host_data) {
                $host_username=$host_data['username'];
                $host_uid=$host_data['host_uid'];
                $project_name=$host_data['project_name'];
                $domain_name=boincmgr_domain_decode($host_data['domain_name']);
                $p_model=$host_data['p_model'];
                $magnitude=round($host_data['magnitude'],2);
                $host_short_info=boincmgr_get_host_short_info($host_uid);

                $host_username_html=html_escape($host_username);
                $domain_name_html=html_escape($domain_name);
                $p_model_html=html_escape($p_model);
                $project_name_html=html_escape($project_name);
                $magnitude_html=html_format_number($magnitude);
                $host_short_info_html=html_escape($host_short_info);

                $result.="<tr><td>$n</td><td>$host_username_html</td><td>$project_name_html</td><td>$domain_name_html</td><td>$host_short_info_html</td><td align=right>$magnitude_html</td></tr>\n";
                $n++;
        }
        $result.="</table>\n";
        $result.="</div>\n";

        return $result;
}

// Rating by user magnitude
function html_rating_by_user_mag() {
        global $username;

        $result="";

        $result.="<div id=rating_by_user_mag_block class=selectable_block>\n";
        $result.="<h3>Rating by user magnitude</h3>\n";
        $result.="<table align=center>\n";

        $magnitude_unit=boincmgr_get_magnitude_unit();
        $mag_per_project=boincmgr_get_mag_per_project();

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        $result.="<tr><th>№</th><th>Username</th><th>Hosts count</th><th>Magnitude</th></tr>\n";
        $user_stats_data_array=db_query_to_array("SELECT bu.`username`,count(DISTINCT bphl.`host_uid`) as host_count,SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`)) AS magnitude
FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bu.`username`<>'' AND bp.`status` IN ('enabled','auto enabled','stats only')
GROUP BY bu.`username`
HAVING SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`))>=0.01
ORDER BY SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) DESC
LIMIT 100");

        $n=1;
        foreach($user_stats_data_array as $user_data) {
                $stats_username=$user_data['username'];
                $host_count=$user_data['host_count'];
                $magnitude=round($user_data['magnitude'],2);

                $stats_username_html=html_escape($stats_username);
                $host_count_html=html_format_number($host_count);
                $magnitude_html=html_format_number($magnitude);

                $result.="<tr><td>$n</td><td>$stats_username_html</td><td align=right>$host_count_html</td><td align=right>$magnitude_html</td></tr>\n";
                $n++;
        }
        $result.="</table>\n";
        $result.="</div>\n";

        return $result;
}

// Rating by user and project magnitude
function html_rating_by_user_project_mag() {
        global $username;

        $result="";

        $result.="<div id=rating_by_user_project_mag_block class=selectable_block>\n";
        $result.="<h3>Rating by user magnitude</h3>\n";
        $result.="<table align=center>\n";

        $magnitude_unit=boincmgr_get_magnitude_unit();
        $mag_per_project=boincmgr_get_mag_per_project();

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        $result.="<tr><th>№</th><th>Username</th><th>Project name</th><th>Hosts count</th><th>Magnitude</th></tr>\n";
        $user_stats_data_array=db_query_to_array("SELECT bu.`username`,bp.`name` AS project_name,count(DISTINCT bphl.`host_uid`) as host_count,SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`)) AS magnitude
FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bu.`username`<>'' AND bp.`status` IN ('enabled','auto enabled','stats only')
GROUP BY bu.`username`,bp.`name`
HAVING SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`))>=0.01
ORDER BY SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) DESC
LIMIT 100");

        $n=1;
        foreach($user_stats_data_array as $user_data) {
                $stats_username=$user_data['username'];
                $stats_project_name=$user_data['project_name'];

                $host_count=$user_data['host_count'];
                $magnitude=round($user_data['magnitude'],2);

                $stats_username_html=html_escape($stats_username);
                $stats_project_name_html=html_escape($stats_project_name);
                $host_count_html=html_format_number($host_count);
                $magnitude_html=html_format_number($magnitude);

                $result.="<tr><td>$n</td><td>$stats_username_html</td><td>$stats_project_name_html</td><td align=right>$host_count_html</td><td align=right>$magnitude_html</td></tr>\n";
                $n++;
        }
        $result.="</table>\n";
        $result.="</div>\n";

        return $result;
}

function html_faucet() {
        global $username;
        global $username_token;
        global $faucet_plain_amount;

        $result="";

        $magnitude_unit=boincmgr_get_magnitude_unit();
        $mag_per_project=boincmgr_get_mag_per_project();

        $username_escaped=db_escape($username);

        $user_magnitude=db_query_to_variable("SELECT SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`)) AS magnitude
FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bu.`username`='$username_escaped' AND bp.`status` IN ('enabled','auto enabled','stats only')
GROUP BY bu.`username`
HAVING SUM($mag_per_project*bphl.`expavg_credit`/(bp.`team_expavg_credit`))>=0.01
ORDER BY SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) DESC
LIMIT 100
");

        $claim_today = db_query_to_variable("SELECT 1 FROM `boincmgr_faucet` AS bf JOIN `boincmgr_users` AS bu ON bu.`uid`=bf.`user_uid` WHERE DATE_ADD(`date`,INTERVAL 1 DAY)>NOW() AND bu.`username`='$username_escaped'");
        $currency = db_query_to_variable("SELECT `currency` FROM `boincmgr_users` WHERE `username`='$username_escaped'");

        if($user_magnitude == 0) $user_magnitude=0;
        $user_magnitude=sprintf("%0.2F",$user_magnitude);

        $result.="<h3>Faucet</h3>\n";
        if($currency!='GRC' && $currency!='GRC2'){
                $result.="<p>You can claim only GRC</p>";
        } else if($claim_today == 1) {
                $result.="<p>You already received coins today</p>";
        } else if($user_magnitude > 1) {
                //$amount=sprintf("%0.2F",log($user_magnitude,10));
                $amount=$faucet_plain_amount;

                $result.=<<<_END
<form name=faucet_claim method=POST>
<p>You can claim $amount GRC today (your mag is $user_magnitude)</p>
<input type=hidden name=action value='claim_faucet'>
<input type=hidden name=token value='$username_token'>
<p><input type=submit value='Claim'></p>
</form>

_END;
        } else {
                $result.="<p>You need magnutude>=1 for use faucet (your magnitude currently is $user_magnitude)</p>";
        }
        return $result;
}

?>
