<?php
// Settings file

// DB variables
$db_host="localhost";
$db_login="login";
$db_password="password";
$db_base="database";

// Pool variables
$pool_name="pool name";
$pool_message="Welcome to $pool_name pool";
$pool_min_password_length=8;

// BOINC variables
$boinc_account="your boinc account here";
// Password hash is
// echo -n '<password><login>' | md5sum
// Login should be lowercase
$boinc_passwd_hash="your password hash here";

// Gridcoin RPC variables
$grc_rpc_host="localhost";
$grc_rpc_port="port";
$grc_rpc_login="login";
$grc_rpc_password="password";
$grc_rpc_wallet_passphrase="wallet passphrase";

// Gridcoin CPID to automated billings
$pool_cpid="YOUR CPID HERE";

// ReCAPTCHA
$recaptcha_public_key="public key here";
$recaptcha_private_key="private key here";

// Email service
$email_api_url="https://api.smtp2go.com/v3/email/send";
$email_api_key="";
$email_sender="";
$email_reply_to="";

// Salt for token
$token_salt="your_salt";

// Debug mode
$debug_mode=FALSE;

// Faucet
$faucet_plain_amount=0.1;

// Language file
require_once("language.php");

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
