<?php
// RPC for BOINC client

require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/boincmgr.php");
require_once("../lib/xml_parser.php");

function xml_error_message($message,$code) {
        global $pool_name;

        return <<<_END
<?xml version="1.0" encoding="UTF-8" ?>
<acct_mgr_reply>
    <error_num>$code</error_num>
    <error_msg>$message</error_msg>
    <error>$message</error>
    <name>$pool_name</name>
</acct_mgr_reply>

_END;
}

db_connect();

$data=file_get_contents("php://input");

$data_escaped=db_escape($data);
if($debug_mode==TRUE) db_query("INSERT INTO boincmgr_xml (`type`,`message`) VALUES ('client request','$data_escaped')");

// Parse user data
$xml_data=xml_parse_user_request($data);

if(count($xml_data)==0) {
        echo xml_error_message($message_xml_error,-101);
        auth_log("Sync error parsing XML");
        die();
}

// Get data from array
$username=$xml_data["name"];
$password_hash=$xml_data["password_hash"];
$host_cpid=$xml_data["host_cpid"];
$domain_name=$xml_data["domain_name"];
$p_model=$xml_data["p_model"];

// Validate host data
if(auth_validate_username($username)==FALSE) xml_error_message("Username validation error",-99);
if(auth_validate_hash($password_hash)==FALSE) xml_error_message("Password hash validation error",-98);
if(auth_validate_hash($host_cpid)==FALSE) xml_error_message("Host cpid validation error",-97);
if(auth_validate_domain($domain_name)==FALSE) { xml_error_message("Host domain name validation error\n",-96); }
if(auth_validate_ascii($p_model)==FALSE) xml_error_message("CPU model validation error",-96);

if(auth_check_hash($username,$password_hash)==FALSE) {
//var_dump($xml_data);
        echo xml_error_message($message_login_error,-100);
        auth_log("Sync username '$username' auth error");
        die();
}

// Calculate external host cpid
$external_host_cpid=md5($host_cpid.$boinc_account);

$username_escaped=db_escape($username);
$host_cpid_escaped=db_escape($host_cpid);
$external_host_cpid_escaped=db_escape($external_host_cpid);
$domain_name_escaped=db_escape(boincmgr_domain_encode($domain_name));
$p_model_escaped=db_escape($p_model);

$reply_xml=<<<_END
<?xml version="1.0" encoding="UTF-8" ?>
<acct_mgr_reply>

_END;

$username_uid=boincmgr_get_username_uid($username);
$username_uid_escaped=db_escape($username_uid);

$host_uid=boincmgr_get_host_uid($username_uid,$host_cpid);
$host_uid_escaped=db_escape($host_uid);

$host_owner_uid=db_query_to_variable("SELECT `username_uid` FROM `boincmgr_hosts` WHERE `internal_host_cpid`='$host_cpid_escaped'");
if(FALSE && $host_owner_uid!="" && $username_uid!=$host_owner_uid) {
        auth_log("Sync username '$username' error, username is not owner host_cpid '$host_cpid'");
        echo xml_error_message($message_host_error,-102);
        die();
}

$base64_query=base64_encode($data);
$base64_query_escaped=db_escape($base64_query);

db_query("INSERT INTO `boincmgr_hosts` (`username_uid`,`internal_host_cpid`,`external_host_cpid`,`domain_name`,`p_model`,`last_query`)
VALUES ('$username_uid_escaped','$host_cpid_escaped','$external_host_cpid_escaped','$domain_name_escaped','$p_model_escaped','$base64_query_escaped')
ON DUPLICATE KEY UPDATE `username_uid`=VALUES(`username_uid`),`external_host_cpid`=VALUES(`external_host_cpid`),`domain_name`=VALUES(`domain_name`),`p_model`=VALUES(`p_model`),`last_query`=VALUES(`last_query`),`timestamp`=CURRENT_TIMESTAMP");

// Attached projects list (for deleting detached projects)
$client_still_attached_project_uids=array();

if(!isset($xml_data["projects"])) $xml_data["projects"]=array();

foreach($xml_data["projects"] as $project_data) {
        // Get user data
        $project_name=$project_data["project_name"];
        $project_host_id=$project_data["hostid"];
        if(isset($project_data["account_key"])) $weak_key=$project_data["account_key"];
        else $weak_key="";

        // Validate data
        if(auth_validate_ascii($project_name)==FALSE) continue;
        if(auth_validate_integer($project_host_id)==FALSE) continue;
        if(auth_validate_ascii($weak_key)==FALSE) continue;

        // Get project uid
        $project_uid=boincmgr_get_project_uid($project_name);
        $project_uid_escaped=db_escape($project_uid);

        // If project exists
        if($project_uid) {
                // No weak key for foreighn accounts, or after reattaching pool
                // Just check that it was attached correctly earlier
                if($weak_key=="") {
                        $project_host_id_escaped=db_escape($project_host_id);
                        $host_id_exists=db_query_to_variable("SELECT `host_id` FROM `boincmgr_host_projects` WHERE `project_uid`='$project_uid_escaped' AND `host_uid`='$host_uid_escaped' AND `host_id`='$project_host_id_escaped'");
                        if($host_id_exists==TRUE) {
                                // If pool has host_id, then state unknown (may be attached correcly)
                                db_query("UPDATE `boincmgr_attach_projects` SET `status`='unknown',`timestamp`=NOW() WHERE `project_uid`='$project_uid_escaped' AND `host_uid`='$host_uid_escaped' AND `status`<>'detach'");
                        } else {
                                // If pool has no host_id, then state incorrect (not attached correctly)
                                db_query("UPDATE `boincmgr_attach_projects` SET `status`='incorrect',`timestamp`=NOW() WHERE `project_uid`='$project_uid_escaped' AND `host_uid`='$host_uid_escaped' AND `status`<>'detach'");
                        }
                } else {
                        $weak_key_correct=boincmgr_check_weak_key($project_uid,$weak_key);
                        if($weak_key_correct==TRUE && $project_host_id!=0) {
                                $project_name_escaped=db_escape($project_name);
                                $project_host_id_escaped=db_escape($project_host_id);
                                $project_uid_escaped=db_escape($project_uid);
                                $client_still_attached_project_uids[]=$project_uid_escaped;

                                // Search for duplicating host of another user
                                $exists_host_owner_uid=db_query_to_variable("SELECT `username_uid` FROM `boincmgr_host_projects` AS bhp
LEFT JOIN `boincmgr_hosts` AS bh ON bh.uid=bhp.host_uid
WHERE bhp.`project_uid`='$project_uid_escaped' AND bhp.`host_id`='$project_host_id_escaped' AND bh.`internal_host_cpid`='$host_cpid_escaped'");

                                // If new host_id or user match, then store data
                                if($exists_host_owner_uid==FALSE || $exists_host_owner_uid==$username_uid_escaped) {
                                        // Store host_id in DB
                                        db_query("INSERT INTO `boincmgr_host_projects` (`host_uid`,`project_uid`,`host_id`)
VALUES ('$host_uid_escaped','$project_uid_escaped','$project_host_id_escaped')
ON DUPLICATE KEY UPDATE `timestamp`=CURRENT_TIMESTAMP");

                                        // Mark attachment as correct
                                        db_query("UPDATE `boincmgr_attach_projects` SET `status`='attached',`timestamp`=NOW() WHERE `project_uid`='$project_uid_escaped' AND `host_uid`='$host_uid_escaped' AND `status` NOT IN ('detach')");
                                } else {
                                        // Stop sync, may be host stealing attempt
                                        auth_log("Sync username '$username' error, username is not owner host_cpid '$host_cpid' project '$project_name' host_id '$project_host_id'");
                                        echo xml_error_message($message_host_error,-102);
                                        die();
                                }
                        }
                }
        }
}

// Delete detached projects from db
$client_still_attached_project_uids_string=implode("','",$client_still_attached_project_uids);
db_query("DELETE FROM `boincmgr_attach_projects` WHERE `host_uid`='$host_uid_escaped' AND `status` IN ('detach') AND `project_uid` NOT IN ('$client_still_attached_project_uids_string')");

// Get project data for this host
$project_data_array=db_query_to_array("SELECT bp.`project_url`,bp.`url_signature`,bp.`weak_auth`,bap.`status`,bap.`resource_share`,bap.`options` FROM `boincmgr_attach_projects` AS bap
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bap.`project_uid`
WHERE bap.`host_uid`='$host_uid_escaped'");

// Update attaching status
db_query("UPDATE `boincmgr_attach_projects` SET `status`='sent' WHERE `host_uid`='$host_uid_escaped' AND (`status`='new' OR `status`='')");

$reply_xml.=<<<_END
<name>$pool_name</name>
<message>$pool_message</message>
<signing_key>
$signing_key
</signing_key>

_END;

$valid_options_array=array("detach","detach_when_done","suspend","dont_request_more_work","abort_not_started","no_cpu","no_cuda","no_ati","no_intel");

foreach($project_data_array as $project_data) {
        $project_url=$project_data['project_url'];
        $weak_auth=$project_data['weak_auth'];
        $url_signature=$project_data['url_signature'];
        $status=$project_data['status'];
        $resource_share=$project_data['resource_share'];
        $options=$project_data['options'];

        $option_tags="<resource_share>$resource_share</resource_share>\n";
        $options_array=explode(",",$options);
        foreach($valid_options_array as $valid_option) {
                if(in_array($valid_option,$options_array)) $option_value=1;
                else $option_value=0;
                $option_tags.="<$valid_option>$option_value</$valid_option>\n";
                if($valid_option=="no_cpu" && $option_value==1) $option_tags.="<no_rsc>CPU</no_rsc>\n";
                if($valid_option=="no_cuda" && $option_value==1) $option_tags.="<no_rsc>CUDA</no_rsc>\n";
                if($valid_option=="no_ati" && $option_value==1) $option_tags.="<no_rsc>ATI</no_rsc>\n";
                if($valid_option=="no_intel" && $option_value==1) $option_tags.="<no_rsc>intel_gpu</no_rsc>\n";
        }

        $reply_xml.=<<<_END
<account>
<url>$project_url</url>
<url_signature>
$url_signature
</url_signature>
<authenticator>$weak_auth</authenticator>
$option_tags
</account>

_END;
}

$reply_xml.=<<<_END
</acct_mgr_reply>

_END;

$reply_xml_escaped=db_escape($reply_xml);

if(auth_validate_ascii($domain_name)==FALSE) {
        $domain_name=base64_encode($domain_name);
}

auth_log("Sync username '$username' host '$domain_name' p_model '$p_model'");

if($debug_mode==TRUE) db_query("INSERT INTO boincmgr_xml (`type`,`message`) VALUES ('client reply','$reply_xml_escaped')");

$file="";
$line=0;
if(headers_sent($file,$line)) auth_log("Headers warning: already sent, file '$file', line '$line'");

echo $reply_xml;
?>
