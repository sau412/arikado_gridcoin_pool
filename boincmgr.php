<?php
// Internal functions for project

// Attach project
function boincmgr_attach($username,$host_uid,$project_uid) {
}

// Detach project
function boincmgr_detach($username,$attach_uid) {
}

// Get project name by uid
function boincmgr_get_project_name($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `name` FROM `boincmgr_projects` WHERE `uid` = '$uid_escaped'");
}

// Get host name by uid
function boincmgr_get_host_name($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `domain_name` FROM `boincmgr_hosts` WHERE `uid` = '$uid_escaped'");
}

// Get user name by uid
function boincmgr_get_user_name($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `username` FROM `boincmgr_users` WHERE `uid` = '$uid_escaped'");
}

// Get user uid by username
function boincmgr_get_username_uid($username) {
        $username_escaped=db_escape($username);
        return db_query_to_variable("SELECT `uid` FROM `boincmgr_users` WHERE `username` = '$username_escaped'");
}

// Get project uid by project name
function boincmgr_get_project_uid($project_name) {
        $project_name_escaped=db_escape($project_name);
        return db_query_to_variable("SELECT `uid` FROM `boincmgr_projects` WHERE `name` = '$project_name_escaped'");
}

// Get host uid by user name and host_cpid
function boincmgr_get_host_uid($username_uid,$host_cpid) {
        $username_uid_escaped=db_escape($username_uid);
        $host_cpid_escaped=db_escape($host_cpid);
        return db_query_to_variable("SELECT `uid` FROM `boincmgr_hosts` WHERE `username_uid` = '$username_uid_escaped' AND `internal_host_cpid`='$host_cpid_escaped'");
}
?>
