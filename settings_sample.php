<?php
// Settings file

// DB variables
$db_host="";
$db_login="";
$db_password="";
$db_base="";

// Pool variables
$pool_name="";
$pool_message="";
$pool_min_password_length=8;

// BOINC variables
$boinc_account="";
$boinc_passwd_hash="";

// Gridcoin RPC variables
$grc_rpc_host="";
$grc_rpc_port="";
$grc_rpc_login="";
$grc_rpc_password="";
$grc_rpc_wallet_passphrase="";

// Gridcoin online wallet API key
$grc_api_url="https://wallet.arikado.ru/api.php";
$grc_api_key="";

// Gridcoin CPID to automated billings
$pool_cpid="";

// Email service
$email_api_url="https://api.smtp2go.com/v3/email/send";
$email_api_key="";
$email_sender="";
$email_reply_to="";

// Salt for token
$token_salt="ru4z6pdm";

// Debug mode
$debug_mode=FALSE;

// Language file
require_once("../lib/language.php");

// Public signing key for URLs
$signing_key="1024
e509d39ea20f7e16de049929fee95de785f6656baa318ba4504b8ded011296f9
08168b995d29e5398afbfb446ecc55ea8e7ad25d0b9dd29680023a96f28d3b49
615f86c92acaadfa91079991c95bdb17cff02d83feb71175b748a2dd32c16277
f2996330badc2aac8475e3a99e1a106f0538afc6162f770c22b32618078e1c21
0000000000000000000000000000000000000000000000000000000000000000
0000000000000000000000000000000000000000000000000000000000000000
0000000000000000000000000000000000000000000000000000000000000000
0000000000000000000000000000000000000000000000000000000000010001
.";
?>
