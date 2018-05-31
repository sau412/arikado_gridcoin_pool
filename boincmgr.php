<?php
// Internal functions for project

// Attach project
function boincmgr_attach($username,$host_uid,$project_uid) {
//      global $username;
        $project_uid_escaped=db_escape($project_uid);
        $host_uid_escaped=db_escape($host_uid);
        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        // Check if host_uid belongs to this user
        if(auth_is_admin($username)==FALSE) $host_uid=db_query_to_variable("SELECT `uid` FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
        if($host_uid || auth_is_admin($username)) {
                $project_name=boincmgr_get_project_name($project_uid);
                $host_name=boincmgr_get_host_name($host_uid);

                auth_log("Attach username '$username' project '$project_name' to host '$host_name'");
                $host_uid_escaped=db_escape($host_uid);
                db_query("INSERT INTO `boincmgr_attach_projects` (`project_uid`,`host_uid`,`status`) VALUES ('$project_uid_escaped','$host_uid_escaped','new') ON DUPLICATE KEY UPDATE `status`='new'");
                return TRUE;
        } else {
                return FALSE;
        }
}

// Detach project
function boincmgr_detach($username,$attached_uid) {
        $attached_uid_escaped=db_escape($attached_uid);
        $host_uid=db_query_to_variable("SELECT `host_uid` FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");
        $project_uid=db_query_to_variable("SELECT `project_uid` FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");
        $status=db_query_to_variable("SELECT `status` FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");

        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);
        $host_uid_escaped=db_escape($host_uid);
        $project_name=boincmgr_get_project_name($project_uid);

        // Check if host_uid belongs to this user
        if(auth_is_admin($username)==FALSE) $host_uid=db_query_to_variable("SELECT `uid` FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
        if($host_uid || auth_is_admin($username)) {
                $host_uid_escaped=db_escape($host_uid);
                $host_name=boincmgr_get_host_name($host_uid);

                auth_log("Detach username '$username' project '$project_name' from host '$host_name'");
                $host_uid_escaped=db_escape($host_uid);

                // If status is "new" then we can just delete
                if($status=="new") {
                        db_query("DELETE FROM `boincmgr_attach_projects` WHERE `uid`='$attached_uid_escaped'");
                // Else detach first
                } else {
                        db_query("UPDATE `boincmgr_attach_projects` SET `status`='detach' WHERE `uid`='$attached_uid_escaped'");
                }
                return TRUE;
        } else {
                return FALSE;
        }
}

// Get project name by uid
function boincmgr_get_project_name($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `name` FROM `boincmgr_projects` WHERE `uid` = '$uid_escaped'");
}

// Get host name by uid
function boincmgr_get_host_name($uid) {
        $uid_escaped=db_escape($uid);
        $hostname_encoded=db_query_to_variable("SELECT `domain_name` FROM `boincmgr_hosts` WHERE `uid` = '$uid_escaped'");

        $hostname_decoded=boincmgr_domain_decode($hostname_encoded);

        if(auth_validate_ascii($hostname_decoded)) return $hostname_decoded;
        else return $hostname_encoded;
}

// Get user name by uid
function boincmgr_get_user_name($uid) {
        $uid_escaped=db_escape($uid);
        return db_query_to_variable("SELECT `username` FROM `boincmgr_users` WHERE `uid` = '$uid_escaped'");
}

// Get user uid by username
function boincmgr_get_username_uid($username) {
        $username_escaped=db_escape($username);
        return db_query_to_variable("SELECT `uid` FROM `boincmgr_users` WHERE LOWER(`username`) = LOWER('$username_escaped')");
}

// Get project uid by project name
function boincmgr_get_project_uid($project_name) {
        $project_name_escaped=db_escape($project_name);
        return db_query_to_variable("SELECT `uid` FROM `boincmgr_projects` WHERE `name` = '$project_name_escaped'");
}

// Check project weak key
function boincmgr_check_weak_key($project_uid,$weak_key) {
        $project_uid_escaped=db_escape($project_uid);
        $weak_key_escaped=db_escape($weak_key);
        $weak_key_exists=db_query_to_variable("SELECT 1 FROM `boincmgr_projects` WHERE `uid` = '$project_uid_escaped' AND `weak_auth` = '$weak_key_escaped'");
        if($weak_key_exists) return TRUE;
        else return FALSE;
}

// Get host uid by user name and host_cpid
function boincmgr_get_host_uid($username_uid,$host_cpid) {
        $username_uid_escaped=db_escape($username_uid);
        $host_cpid_escaped=db_escape($host_cpid);
        return db_query_to_variable("SELECT `uid` FROM `boincmgr_hosts` WHERE `username_uid` = '$username_uid_escaped' AND `internal_host_cpid`='$host_cpid_escaped'");
}

// Encode domain to store in DB
function boincmgr_domain_encode($domain) {
        return base64_encode($domain);
}

// Decode domain form DB
function boincmgr_domain_decode($domain) {
        return base64_decode($domain);
}

// Delete host
function boincmgr_delete_host($username,$host_uid) {
        $host_uid_escaped=db_escape($host_uid);
        $username_uid=boincmgr_get_username_uid($username);
        $username_uid_escaped=db_escape($username_uid);

        // Check if host_uid belongs to this user
        if(auth_is_admin($username)==FALSE) $host_uid=db_query_to_variable("SELECT `uid` FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped' AND `username_uid`='$username_uid_escaped'");
        if($host_uid || auth_is_admin($username)) {
                $host_name=boincmgr_get_host_name($host_uid);

                auth_log("Delete host '$host_name' by username '$username'");

                // Delete any attach project statuses
                db_query("DELETE FROM `boincmgr_attach_projects` WHERE `host_uid`='$host_uid_escaped'");

                // Delete attached projects
                db_query("DELETE FROM `boincmgr_host_projects` WHERE `host_uid`='$host_uid_escaped'");

                // Delete host stats
                db_query("DELETE FROM `boincmgr_hosts_last` WHERE `host_uid`='$host_uid_escaped'");
                db_query("DELETE FROM `boincmgr_host_stats` WHERE `host_uid`='$host_uid_escaped'");

                // Delete host
                db_query("DELETE FROM `boincmgr_hosts` WHERE `uid`='$host_uid_escaped'");
        }
}

// Set pool info
function boincmgr_set_pool_info($pool_info) {
        $pool_info_escaped=db_escape($pool_info);
        db_query("UPDATE `boincmgr_variables` SET `value`='$pool_info_escaped' WHERE `name`='pool_info'");
}

// Get pool info
function boincmgr_get_pool_info() {
        return db_query_to_variable("SELECT `value` FROM `boincmgr_variables` WHERE `name`='pool_info'");
}

?>
