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

// Begin HTML page
function html_page_begin() {
        global $pool_name;
        return <<<_END
<!DOCTYPE html>
<html>
<head>
<title>$pool_name gridcoin pool</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="common.css">
<script src="common.js"></script>
<link rel="icon" href="favicon.png" type="image/png">
<script src='https://www.google.com/recaptcha/api.js'></script>
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

// Message after action
function html_top_message($message) {
        return "<p class=top_message>$message</p>\n";
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
                if($currency=="GRC") return "no txid";
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
                        $result.=html_menu_element("log","View log");
                        $result.=html_menu_element("pool_info_editor","Pool info editor");
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
        $username_html=html_escape($username);
                return "Welcome, $username_html (<a href='./?action=logout&token=$username_token'>logout</a>)";
        } else {
                return "Hello, stranger";
        }
}

// Currency selector
function html_currency_selector($selected_currency="") {
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

        $email_html=html_escape($email);
        $payout_address_html=html_escape($payout_address);
        $payout_currency_html=html_escape($payout_currency);

        $currency_selector=html_currency_selector($payout_currency);

        return <<<_END
<div id=settings_block class=selectable_block>
<h2>Settings</h2>
<p>GRC payouts are instant, alternative currencies payouts are cumulative and manual. It takes 1-2 days when payout limit reached to send payout (because manual mode now).</p>
<p>Changing alternative (non-GRC) currency or address notice: please note, owed amount linked to address, not to user. If you change address your previous address owed amount will not lost, but won't payed out until payout limit for previous address reached. You can contact admin for manual payout or change address back and receive payout when payout limit reached.</p>
<form name=change_settings_form method=POST>
<p><input type=hidden name="action" value="change_settings"></p>
<p><input type=hidden name="token" value="$username_token"></p>
<p>E-mail: <input type=text name=email value='$email_html' size=40></p>
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
        $result.="<table>\n";

        if(auth_is_admin($username)) {
                $result.="<tr><th>Username</th><th>Host info</th><th>Debug info</th><th>Projects</th></tr>\n";
                $hosts_array=db_query_to_array("SELECT bh.`uid`,bu.`username`,bh.`internal_host_cpid`,bh.`external_host_cpid`,bh.`domain_name`,bh.`p_model`,bh.`timestamp` FROM `boincmgr_hosts` AS bh
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
ORDER BY bu.`username`,bh.`domain_name` ASC");
        } else {
                $result.="<tr><th>Domain name</th><th>CPU</th><th>Projects</th></tr>\n";
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

                $attached_projects_array=db_query_to_array("SELECT bap.`uid`,bap.`host_uid`,bp.`uid` as project_uid,bp.`name`,bap.`status`,bap.`resource_share`,bap.`options`,bp.`status` AS project_status FROM `boincmgr_attach_projects` AS bap
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

                        $detach_form=<<<_END
$project_name_html $options_show_html
<input type=button value='options' onClick="show_project_options_window('$attached_project_uid','$domain_name_html','$project_name_html','$resource_share_html','$options_html');">
$attached_project_msg
<br>

_END;

                        $projects_str.="$detach_form<br>";
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
//                      $result.="<p>Host <b>$domain_name_html</b></p>\n";
//                      $result.="$projects_str\n";
                $p_model_html=str_replace("[","<br>[",$p_model_html);
                if(auth_is_admin($username)) {
                        $result.="<tr><td>$host_username_html</td><td><p>Domain name: $domain_name_html</p><p>$p_model_html</p><p>$host_delete_form</p></td><td><p>Last sync: $timestamp_html</p><p><a href='?action=view_host_last_query&host_uid=$host_uid&token=$username_token'>View last query</a></p></td><td>$projects_str</td></tr>\n";
                } else {
                        $result.="<tr><td>$domain_name_html $host_delete_form</td><td>$p_model_html</td><td>$projects_str</td></tr>\n";
                }
        }
        $result.="</table>\n";
        $result.="</div>\n";
        return $result;
}

// Show BOINC results for user
function html_boinc_results() {
        global $username;

        $result="";

        // GRC per last day
        $total_grc_per_day=db_query_to_variable("SELECT SUM(`mint`-`interest`) FROM `boincmgr_blocks` WHERE cpid<>'INVESTOR' AND date_sub(NOW(), INTERVAL 1 DAY)<`timestamp`");
        // Whitelisted projects count
        $whiltelisted_count=db_query_to_variable("SELECT count(*) FROM `boincmgr_projects` WHERE `status` IN ('enabled','auto enabled','stats only')");

        $result.="<div id=boinc_results_block class=selectable_block>\n";
        $result.="<h2>BOINC results:</h2>\n";

        $result.="<p>That information we received from various BOINC projects:</p>\n";

        $result.="<h3>Results by host</h3>\n";
        $result.="<table>\n";

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        if(auth_is_admin($username)) {
                $result.="<tr><th>Username</th><th>Domain name</th><th>CPU</th><th>&Sigma; RAC</th><th>&Sigma; RAC 7d graph</th><th>GRC/day est</th></tr>\n";
                $boinc_host_data_array=db_query_to_array("SELECT bu.`username`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model`,SUM(bphl.`expavg_credit`) AS rac,SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
GROUP BY bu.`username`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model` ORDER BY bu.`username`,bphl.`domain_name`,bphl.`p_model` ASC");
        } else {
                $result.="<tr><th>Domain name</th><th>CPU</th><th>&Sigma; RAC</th><th>&Sigma; RAC 7d graph</th><th>GRC/day est</th></tr>\n";
                $boinc_host_data_array=db_query_to_array("SELECT bu.`username`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model`,SUM(bphl.`expavg_credit`) AS rac,SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
WHERE bh.`username_uid`='$username_uid_escaped' GROUP BY  bu.`username`,bphl.`host_uid`,bphl.`domain_name`,bphl.`p_model` ORDER BY bu.`username`,bphl.`domain_name`,bphl.`p_model` ASC");
        }
        foreach($boinc_host_data_array as $boinc_host_data) {
                $host_username=$boinc_host_data['username'];
                $host_uid=$boinc_host_data['host_uid'];
                $domain_name=boincmgr_domain_decode($boinc_host_data['domain_name']);
                $p_model=$boinc_host_data['p_model'];
                $expavg_credit=round($boinc_host_data['rac']);
                $relative_credit=$boinc_host_data['relative_credit'];

                $host_username_html=html_escape($host_username);
                $domain_name_html=html_escape($domain_name);
                $p_model_html=html_escape($p_model);
                $expavg_credit_html=html_escape($expavg_credit);

                $expavg_credit_html=html_format_number($expavg_credit_html);

                $grc_per_day_est=($total_grc_per_day/$whiltelisted_count)*($relative_credit);
                $grc_per_day_est=round($grc_per_day_est,4);

                $graph=boincmgr_cache_function("canvas_graph_host_all_projects",array($host_uid));

                $p_model_html=str_replace("[","<br>[",$p_model_html);
                if(auth_is_admin($username))
                        $result.="<tr><td>$host_username_html</td><td>$domain_name_html</td><td>$p_model_html</td><td align=right>$expavg_credit_html</td><td>$graph</td><td>$grc_per_day_est</td></tr>\n";
                else
                        $result.="<tr><td>$domain_name_html</td><td>$p_model_html</td><td align=right>$expavg_credit_html</td><td>$graph</td><td>$grc_per_day_est</td></tr>\n";
        }
        $result.="</table>\n";

        // Projects stats for admin is the pool stats
        if(auth_is_admin($username)==FALSE) {
                $result.="<h3>Results by project</h3>\n";
                $result.="<table>\n";
                $result.="<tr><th>Project</th><th>&Sigma; RAC</th><th>&Sigma; RAC 7d graph</th><th>GRC/day est</th></tr>\n";

                $boinc_host_data_array=db_query_to_array("SELECT bphl.`project_uid`,bp.`name`,SUM(bphl.`expavg_credit`) AS rac,SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
WHERE bh.`username_uid`='$username_uid_escaped' GROUP BY bphl.`project_uid`,bp.`name` HAVING SUM(bphl.`expavg_credit`)>=1 ORDER BY bp.`name` ASC");

                foreach($boinc_host_data_array as $boinc_host_data) {
                        $project_uid=$boinc_host_data['project_uid'];
                        $expavg_credit=round($boinc_host_data['rac']);
                        $project_name=$boinc_host_data['name'];
                        $relative_credit=$boinc_host_data['relative_credit'];

                        $expavg_credit_html=html_escape($expavg_credit);
                        $project_name_html=html_escape($project_name);

                        $expavg_credit_html=html_format_number($expavg_credit_html);

                        $grc_per_day_est=($total_grc_per_day/$whiltelisted_count)*($relative_credit);
                        $grc_per_day_est=round($grc_per_day_est,4);
                        $graph=boincmgr_cache_function("canvas_graph_username_project",array($username_uid,$project_uid));

                        $result.="<tr><td>$project_name_html</td><td align=right>$expavg_credit_html</td><td>$graph</td><td>$grc_per_day_est</td></tr>\n";
                }
                $result.="</table>\n";
        } else {
                $result.="<h3>Results by user</h3>\n";
                $result.="<table>\n";
                $result.="<tr><th>Username</th><th>&Sigma; RAC</th><th>&Sigma; RAC 7d graph</th><th>GRC/day est</th></tr>\n";

                $boinc_host_data_array=db_query_to_array("SELECT bh.`username_uid`,bu.`username`,SUM(bphl.`expavg_credit`) AS rac,SUM(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
GROUP BY bh.`username_uid`,bu.`username` HAVING SUM(bphl.`expavg_credit`)>=1 ORDER BY bu.`username` ASC");

                foreach($boinc_host_data_array as $boinc_host_data) {
                        $username_uid=$boinc_host_data['username_uid'];
                        $expavg_credit=round($boinc_host_data['rac']);
                        $user_name=$boinc_host_data['username'];
                        $relative_credit=$boinc_host_data['relative_credit'];

                        $expavg_credit_html=html_escape($expavg_credit);
                        $user_name_html=html_escape($user_name);

                        $expavg_credit_html=html_format_number($expavg_credit_html);

                        $grc_per_day_est=($total_grc_per_day/$whiltelisted_count)*($relative_credit);
                        $grc_per_day_est=round($grc_per_day_est,4);
                        $graph=boincmgr_cache_function("canvas_graph_username",array($username_uid));

                        $result.="<tr><td>$user_name_html</td><td align=right>$expavg_credit_html</td><td>$graph</td><td>$grc_per_day_est</td></tr>\n";
                }
                $result.="</table>\n";
        }
        $result.="<h3>Results for each project and each host</h3>\n";
        $result.="<table>\n";

        if(auth_is_admin($username)) {
                $result.="<tr><th>Username</th><th>Domain name</th><th>CPU</th><th>Project</th><th>RAC</th><th>RAC 7d graph</th><th>GRC/day est</th></tr>\n";
                $boinc_host_data_array=db_query_to_array("
SELECT bu.`username`,bphl.`host_uid`,bphl.`project_uid`,bphl.`host_cpid`,bphl.`domain_name`,bphl.`p_model`,bp.`name`,bphl.`expavg_credit`,(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit
FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
LEFT JOIN `boincmgr_users` AS bu ON bu.`uid`=bh.`username_uid`
ORDER BY bu.`username`,bphl.`domain_name`,bp.`name` ASC");
        } else {
                $result.="<tr><th>Domain name</th><th>CPU</th><th>Project</th><th>RAC</th><th>RAC 7d graph</th><th>GRC/day est</th></tr>\n";
                $boinc_host_data_array=db_query_to_array("
SELECT bphl.`host_uid`,bphl.`project_uid`,bphl.`host_cpid`,bphl.`domain_name`,bphl.`p_model`,bp.`name`,bphl.`expavg_credit`,(bphl.`expavg_credit`/bp.`team_expavg_credit`) AS relative_credit
FROM `boincmgr_project_hosts_last` AS bphl
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bphl.`project_uid`
LEFT JOIN `boincmgr_hosts` AS bh ON bh.`uid`=bphl.`host_uid`
WHERE bh.`username_uid`='$username_uid_escaped' ORDER BY bphl.`domain_name`,bp.`name` ASC");
        }
        foreach($boinc_host_data_array as $boinc_host_data) {
                if(isset($boinc_host_data['username'])) $host_username=$boinc_host_data['username'];
                else $host_username="";
                $host_uid=$boinc_host_data['host_uid'];
                $project_uid=$boinc_host_data['project_uid'];
                $host_cpid=$boinc_host_data['host_cpid'];
                $domain_name=boincmgr_domain_decode($boinc_host_data['domain_name']);
                $p_model=$boinc_host_data['p_model'];
                $expavg_credit=$boinc_host_data['expavg_credit'];
                $project_name=$boinc_host_data['name'];
                $relative_credit=$boinc_host_data['relative_credit'];

                $host_username_html=html_escape($host_username);
                $host_cpid_html=html_escape($host_cpid);
                $domain_name_html=html_escape($domain_name);
                $p_model_html=html_escape($p_model);
                $expavg_credit_html=html_escape($expavg_credit);
                $project_name_html=html_escape($project_name);

//              $expavg_credit_html=html_format_number($expavg_credit_html);
                $grc_per_day_est=($total_grc_per_day/$whiltelisted_count)*($relative_credit);
                $grc_per_day_est=round($grc_per_day_est,4);
                $graph=boincmgr_cache_function("canvas_graph_host_project",array($host_uid,$project_uid));

                $p_model_html=str_replace("[","<br>[",$p_model_html);
                if(auth_is_admin($username))
                        $result.="<tr><td>$host_username_html</td><td>$domain_name_html</td><td>$p_model_html</td><td>$project_name_html</td><td align=right>$expavg_credit_html</td><td>$graph</td><td>$grc_per_day_est</td></tr>\n";
                else
                        $result.="<tr><td>$domain_name_html</td><td>$p_model_html</td><td>$project_name_html</td><td align=right>$expavg_credit_html</td><td>$graph</td><td>$grc_per_day_est</td></tr>\n";
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
        $result.="<p><table>\n";
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
        $result.="<p><table>\n";
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
                $result.="<p>These rewards not send yet, because payout limit is not reached. Fee is tx fee + service fee.</p>";
                $result.="<table>\n";
                $result.="<tr></th><th>Address</th><th>Amount</th><th>Currency</th><th>Payout threshold</th><th>Fee<th>Interval from</th><th>Interval to</th></tr>\n";
                foreach($owes_data_array as $owe_data) {
                        $payout_address=$owe_data['payout_address'];
                        $amount=$owe_data['amount'];
                        $currency=$owe_data['currency'];
                        $start_date=$owe_data['start_date'];
                        $stop_date=$owe_data['stop_date'];

                        $payout_address_link=html_payout_address_link($currency,$payout_address);

                        $payout_threshold=boincmgr_get_payout_limit($currency);
                        $payout_fee=boincmgr_get_tx_fee_estimation($currency);
                        $payout_service_fee=boincmgr_get_service_fee($currency);
                        $total_fee=$payout_fee+$payout_service_fee;

                        $result.="<tr><td>$payout_address_link</td><td>$amount</td><td>$currency</td><td>$payout_threshold</td><td>$total_fee</td><td>$start_date</td><td>$stop_date</td></tr>\n";
                }
                $result.="</table>\n";
        }
        $result.="<p>Last 10 billings from pool:</p>\n";

        $billings_array=db_query_to_array("SELECT `uid`,`start_date`,`stop_date`,`reward` FROM `boincmgr_billing_periods` ORDER BY `stop_date` DESC LIMIT 10");
        foreach($billings_array as $billing) {
                $billing_uid=$billing['uid'];
                $start_date=$billing['start_date'];
                $stop_date=$billing['stop_date'];
                $reward=$billing['reward'];

                $reward=round($reward,4);

                $billing_uid_escaped=db_escape($billing_uid);

                $result.="<h3>For period from $start_date to $stop_date pool rewarded with $reward gridcoins</h3>\n";
                $payout_data_array=db_query_to_array("SELECT `currency`,`grc_amount`,`rate`,`payout_address`,`amount`,`txid`,`timestamp` FROM `boincmgr_payouts` WHERE `billing_uid`='$billing_uid_escaped' AND `currency`='GRC' ORDER BY `payout_address` ASC");

                $result.="<p>Gridcoin payouts</p>\n";
                $result.="<p><table>\n";
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
                        $grc_amount_html=html_escape($grc_amount);
                        $currency_html=html_escape($currency);
                        $rate_html=html_escape($rate);
                        $amount_html=html_escape($amount);
                        $txid_link=html_txid_link($currency,$txid);
                        $timestamp_html=html_escape($timestamp);

                        $result.="<tr><td>$payout_address_link</td><td>$grc_amount_html</td><td>$txid_link</td><td>$timestamp_html</td></tr>\n";
                        }
                $result.="</table></p>\n";

                $payout_data_array=db_query_to_array("SELECT `currency`,`grc_amount`,`rate`,`payout_address`,`amount`,`txid`,`timestamp` FROM `boincmgr_payouts` WHERE `billing_uid`='$billing_uid_escaped' AND `currency`<>'GRC' ORDER BY `payout_address` ASC");
                if(count($payout_data_array)==0) continue;

                $result.="<p>Alternative currencies</p>\n";
                $result.="<p><table>\n";
                $result.="<tr></th><th>Address</th><th>GRC amount</th><th>Payout <br>currency</th><th>Rate per<br>1 GRC</th><th>Currency<br>amount</th><th>TX ID</th><th>Timestamp</th></tr>\n";
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
                        $grc_amount_html=html_escape($grc_amount);
                        $currency_html=html_escape($currency);
                        $rate_html=html_escape($rate);
                        $amount_html=html_escape($amount);
                        $txid_link=html_txid_link($currency,$txid);
                        $timestamp_html=html_escape($timestamp);

                        $result.="<tr><td>$payout_address_link</td><td>$grc_amount_html</td><td>$currency_html</td><td>$rate_html</td><td>$amount_html</td><td>$txid_link</td><td>$timestamp_html</td></tr>\n";
                }
                $result.="</table></p>\n";
        }

        if(auth_is_admin($username)) {
                $start_date=db_query_to_variable("SELECT MAX(`stop_date`) FROM `boincmgr_billing_periods`");
                if($start_date=="") $start_date="2018-01-01 20:20:16";
                $stop_date=db_query_to_variable("SELECT NOW()");
                $balance=boincmgr_get_variable("hot_wallet_balance");

                $result.=<<<_END
<h2>Billing</h2>
<form name=billing method=post>
<p>Fill data carefully, that cannot be undone!</p>
<p>Hot wallet balance: $balance GRC</p>
<input type=hidden name=action value='billing'>
<input type=hidden name=token value='$username_token'>
<p>Begin of period <input type=text name=start_date value='$start_date'></p>
<p>End of period <input type=text name=stop_date value='$stop_date'></p>
<p>Total reward <input type=text name=reward value='0.0000'></p>
<p><label><input type=checkbox name=check_rewards checked> check only, do not send</label></p>
<p><input type=submit value='Send rewards'></p>
</form>
_END;
        }


        $result.="</div>\n";
        return $result;
}

// Show log
function html_view_log() {
        $result="";
        $result.="<div id=log_block class=selectable_block>\n";
        $result.="<h2>View log</h2>\n";
        $result.="<p>Last 100 messages:</p>\n";
        $result.="<p><table>\n";
        $result.="<tr><th>Timestamp</th><th>Message</th></tr>\n";
        $log_array=db_query_to_array("SELECT `message`,`timestamp` FROM `boincmgr_log` ORDER BY `timestamp` DESC LIMIT 100");
        foreach($log_array as $data) {
                $message=$data['message'];
                $timestamp=$data['timestamp'];

                $message_html=html_escape($message);
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
        $result.="<p>Enabled (or auto enabled) means project data updated and rewards are on. Stats only - users cannot attach by themselves, rewards on, auto disabled - only downloading stats, no rewards, disabled - do not check anything about this project (no rewards too).</p>";

        $start_date=db_query_to_variable("SELECT MAX(`stop_date`) FROM `boincmgr_billing_periods`");
        if($start_date=="") $start_date="2018-01-01 20:20:16";
        $stop_date=db_query_to_variable("SELECT NOW()");

        $result.="<p><table>\n";
        $result.="<tr><th>Project</th><th>Team RAC</th><th>Pool RAC</th><th>Pool mag</th><th>Pool GRC/day est</th><th>Hosts</th><th>Status</th><th>Pool RAC 30d graph</th></tr>\n";

        $project_array=db_query_to_array("SELECT `uid`,`name`,`project_url`,`expavg_credit`,`team_expavg_credit`,`status` FROM `boincmgr_projects` ORDER BY `name` ASC");

        // GRC per last day
        $total_grc_per_day=db_query_to_variable("SELECT SUM(`mint`-`interest`) FROM `boincmgr_blocks` WHERE cpid<>'INVESTOR' AND date_sub(NOW(), INTERVAL 1 DAY)<`timestamp`");
        // Whitelisted projects count
        $whiltelisted_count=db_query_to_variable("SELECT count(*) FROM `boincmgr_projects` WHERE `status` IN ('enabled','auto enabled','stats only')");
        // Magnitude unit
        $total_magnitude=115000;
        $magnitude_unit=db_query_to_variable("SELECT `value` FROM `boincmgr_variables` WHERE `name`='magnitude_unit'");

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

                $project_magnitude=$total_magnitude*($expavg_credit/$team_expavg_credit)/$whiltelisted_count;
                $project_magnitude=round($project_magnitude,4);

                $project_grc_per_day=$total_grc_per_day/$whiltelisted_count;
                if($team_expavg_credit==0) $pool_grc_per_day=0;
                else $pool_grc_per_day=round(($project_grc_per_day/$team_expavg_credit)*$expavg_credit,4);

                $expavg_credit=round($expavg_credit);

                $name_link=html_project_name_link($name,$project_url);
                $team_expavg_credit_html=html_escape($team_expavg_credit);
                $expavg_credit_html=html_escape($expavg_credit);
                $pool_project_hosts_html=html_escape($pool_project_hosts);

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

                $pool_grc_per_day_html=html_escape($pool_grc_per_day);

                $team_expavg_credit_html=html_format_number($team_expavg_credit_html);
                $expavg_credit_html=html_format_number($expavg_credit_html);
                $project_magnitude_html=html_format_number($project_magnitude);
                $graph=canvas_graph_project_total($uid);

                $result.="<tr><td>$name_link</td><td align=right>$team_expavg_credit_html</td><td align=right>$expavg_credit_html</td><td align=right>$project_magnitude_html</td><td align=right>$pool_grc_per_day_html</td><td>$pool_project_hosts_html</td><td>$status_html</td><td>$graph</td></tr>\n";
        }
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
?>
