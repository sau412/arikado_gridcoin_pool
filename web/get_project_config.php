<?php
// Project config for BOINC client

require_once("../lib/settings.php");
echo <<<_END
<?xml version="1.0" encoding="UTF-8" ?>
<project_config>
    <name>$pool_name</name>
    <min_passwd_length>$pool_min_password_length</min_passwd_length>
    <account_manager/>
    <uses_username/>
    <client_account_creation_disabled/>
</project_config>
_END;
?>
