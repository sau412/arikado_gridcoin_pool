<?php
// RPC for BOINC client

require_once("settings.php");
require_once("db.php");
require_once("auth.php");
require_once("boincmgr.php");
require_once("xml_parser.php");

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
db_query("INSERT INTO boincmgr_xml (`type`,`message`) VALUES ('client request','$data_escaped')");

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

$host_owner_uid=db_query_to_variable("SELECT `username_uid` FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped'");
if($host_owner_uid!="" && $username_uid!=$host_owner_uid) {
        auth_log("Sync username '$username' error, username is not owner host_uid '$host_uid'");
        echo xml_error_message($message_host_error,-102);
        die();
}

db_query("INSERT INTO `boincmgr_hosts` (`username_uid`,`internal_host_cpid`,`external_host_cpid`,`domain_name`,`p_model`)
VALUES ('$username_uid_escaped','$host_cpid_escaped','$external_host_cpid_escaped','$domain_name_escaped','$p_model_escaped')
ON DUPLICATE KEY UPDATE `username_uid`=VALUES(`username_uid`),`external_host_cpid`=VALUES(`external_host_cpid`),`domain_name`=VALUES(`domain_name`),`p_model`=VALUES(`p_model`),`timestamp`=CURRENT_TIMESTAMP");

// Attached projects list (for deleting detached projects)
$client_still_attached_project_uids=array();

foreach($xml_data["projects"] as $project_data) {
        // Get user data
        $project_name=$project_data["project_name"];
        $project_host_id=$project_data["hostid"];
        $weak_key=$project_data["account_key"];

        // Validate data
        if(auth_validate_ascii($project_name)==FALSE) continue;
        if(auth_validate_integer($project_host_id)==FALSE) continue;
        if(auth_validate_ascii($weak_key)==FALSE) continue;

        // Get project uid
        $project_uid=boincmgr_get_project_uid($project_name);

        // If project exists
        if($project_uid) {
                $weak_key_correct=boincmgr_check_weak_key($project_uid,$weak_key);
                if($weak_key_correct==TRUE) {
                        $project_name_escaped=db_escape($project_name);
                        $project_host_id_escaped=db_escape($project_host_id);
                        $project_uid_escaped=db_escape($project_uid);
                        $client_still_attached_project_uids[]=$project_uid_escaped;

                        db_query("INSERT INTO `boincmgr_host_projects` (`host_uid`,`project_uid`,`host_id`)
VALUES ('$host_uid_escaped','$project_uid_escaped','$project_host_id_escaped')
ON DUPLICATE KEY UPDATE `timestamp`=CURRENT_TIMESTAMP");
                }
        }
}

// Delete detached projects from db
$client_still_attached_project_uids_string=implode("','",$client_still_attached_project_uids);
db_query("DELETE FROM `boincmgr_attach_projects` WHERE `host_uid`='$host_uid_escaped' AND `detach`=1 AND `project_uid` NOT IN ('$client_still_attached_project_uids_string')");

// Get project data for this host
$project_data_array=db_query_to_array("SELECT bp.`project_url`,bp.`url_signature`,bp.`weak_auth`,bap.`detach` FROM `boincmgr_attach_projects` AS bap
LEFT JOIN `boincmgr_projects` AS bp ON bp.`uid`=bap.`project_uid`
WHERE bap.`host_uid`='$host_uid_escaped'");

$reply_xml.=<<<_END
<name>$pool_name</name>
<message>$pool_message</message>
<signing_key>
$signing_key
</signing_key>

_END;

foreach($project_data_array as $project_data) {
        $project_url=$project_data['project_url'];
        $weak_auth=$project_data['weak_auth'];
        $url_signature=$project_data['url_signature'];
        $detach=$project_data['detach'];

        $reply_xml.=<<<_END
<account>
<url>$project_url</url>
<url_signature>
$url_signature
</url_signature>
<authenticator>$weak_auth</authenticator>
<detach>$detach</detach>
</account>

_END;
}

$reply_xml.=<<<_END
</acct_mgr_reply>

_END;

$reply_xml_escaped=db_escape($reply_xml);

auth_log("Sync username '$username' host '$domain_name' p_model '$p_model'");

db_query("INSERT INTO boincmgr_xml (`type`,`message`) VALUES ('client reply','$reply_xml_escaped')");

echo $reply_xml;
?>
