<?php
function boincmgr_attach($username,$host_uid,$project_uid) {
}

function boincmgr_detach($username,$attach_uid) {
}

function boincmgr_get_project_name($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `name` FROM `boincmgr_projects` WHERE `uid` = '$uid_escaped'");
}

function boincmgr_get_host_name($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `domain_name` FROM `boincmgr_hosts` WHERE `uid` = '$uid_escaped'");
}

function boincmgr_get_user_name($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `username` FROM `boincmgr_users` WHERE `uid` = '$uid_escaped'");
}
?>
