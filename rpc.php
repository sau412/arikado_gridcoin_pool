<?php
// RPC for BOINC client

require_once("settings.php");
require_once("db.php");
require_once("auth.php");
require_once("boincmgr.php");

db_connect();

$data=file_get_contents("php://input");
$data=iconv('WINDOWS-1250','UTF-8',$data);
libxml_use_internal_errors(TRUE);
libxml_disable_entity_loader(TRUE);

$data_escaped=db_escape($data);
db_query("INSERT INTO boincmgr_xml (`type`,`message`) VALUES ('client request','$data_escaped')");

$xml_data = simplexml_load_string($data);

if($xml_data === FALSE) {
        echo <<<_END
<?xml version="1.0" encoding="UTF-8" ?>
<acct_mgr_reply>
    <error_num>-101</error_num>
    <error_msg>$message_xml_error</error_msg>
    <error>$message_xml_error</error>
    <name>$pool_name</name>
</acct_mgr_reply>

_END;
        auth_log("Sync error parsing XML");
        die();
}

$username=(string)$xml_data->name;
$password_hash=(string)$xml_data->password_hash;
$host_cpid=(string)$xml_data->host_cpid;
$external_host_cpid=md5($host_cpid.$boinc_account);
$domain_name=(string)$xml_data->host_info->domain_name;
$p_model=(string)$xml_data->host_info->p_model;
$p_ncpus=(string)$xml_data->host_info->p_ncpus;
$n_usable_coprocs=(string)$xml_data->host_info->n_usable_coprocs;

$username_escaped=db_escape($username);
$host_cpid_escaped=db_escape($host_cpid);
$external_host_cpid_escaped=db_escape($external_host_cpid);
$domain_name_escaped=db_escape($domain_name);
$p_model_escaped=db_escape($p_model);
$p_ncpus_escaped=db_escape($p_ncpus);
$n_usable_coprocs_escaped=db_escape($n_usable_coprocs);

$reply_xml=<<<_END
<?xml version="1.0" encoding="UTF-8" ?>
<acct_mgr_reply>

_END;

if(auth_check_hash($username,$password_hash)==FALSE) {
    $reply_xml.=<<<_END
    <error_num>-100</error_num>
    <error_msg>$message_login_error</error_msg>
    <error>$message_login_error</error>
    <name>$pool_name</name>

_END;
} else {
        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        db_query("INSERT INTO `boincmgr_hosts` (`username_uid`,`internal_host_cpid`,`external_host_cpid`,`domain_name`,`p_model`,`p_ncpus`,`n_usable_coprocs`)
VALUES ('$username_uid_escaped','$host_cpid_escaped','$external_host_cpid_escaped','$domain_name_escaped','$p_model_escaped','$p_ncpus_escaped','$n_usable_coprocs_escaped')
ON DUPLICATE KEY UPDATE `username_uid`=VALUES(`username_uid`),`external_host_cpid`=VALUES(`external_host_cpid`),`domain_name`=VALUES(`domain_name`),`p_model`=VALUES(`p_model`),`p_ncpus`=VALUES(`p_ncpus`),`n_usable_coprocs`=VALUES(`n_usable_coprocs`),`timestamp`=CURRENT_TIMESTAMP");

        $host_uid=boincmgr_get_host_uid($username_uid,$host_cpid);
        $host_uid_escaped=db_escape($host_uid);

        foreach($xml_data->project as $project_data) {
                $project_name=(string)$project_data->project_name;
                $project_host_id=(string)$project_data->hostid;
                $project_uid=boincmgr_get_project_uid($project_name);

                $project_name_escaped=db_escape($project_name);
                $project_host_id_escaped=db_escape($project_host_id);
                $project_uid_escaped=db_escape($project_uid);

                db_query("INSERT INTO `boincmgr_host_projects` (`host_uid`,`project_uid`,`host_id`)
VALUES ('$host_uid_escaped','$project_uid_escaped','$project_host_id_escaped')
ON DUPLICATE KEY UPDATE `timestamp`=CURRENT_TIMESTAMP");
        }

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
}
$reply_xml.=<<<_END
</acct_mgr_reply>

_END;

$reply_xml_escaped=db_escape($reply_xml);

auth_log("Sync username '$username' host '$domain_name' p_model '$p_model'");

db_query("INSERT INTO boincmgr_xml (`type`,`message`) VALUES ('client reply','$reply_xml_escaped')");

echo $reply_xml;
?>
