<?php
if(file_exists("settings.php")) die("Pool already installed (settings.php already exists). Check pool's <a href='./'>main page</a>.\n");

require_once("db.php");
require_once("email.php");

if(isset($_POST['db_host'])) {
        // Pool settings
        $pool_name=stripslashes($_POST['pool_name']);
        $admin_login=stripslashes($_POST['admin_login']);
        $admin_password=stripslashes($_POST['admin_password']);
        $pool_cpid=stripslashes($_POST['pool_cpid']);
        $pool_salt=stripslashes($_POST['pool_salt']);

        // BOINC settings
        $boinc_account=stripslashes($_POST['boinc_account']);
        $boinc_password=stripslashes($_POST['boinc_password']);
        $boinc_passwd_hash=md5($boinc_password.strtolower($boinc_account));

        // Checking BOINC account
        echo "<p>Checking your BOINC account on Rosetta@home</p>\n";
        flush();
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_POST,FALSE);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
        curl_setopt($ch,CURLOPT_URL,"http://boinc.bakerlab.org/rosetta/lookup_account.php?email_addr=$boinc_account&passwd_hash=$boinc_passwd_hash");
        $data=curl_exec($ch);
        $xml=simplexml_load_string($data);
        if($xml==FALSE || isset($xml->error_msg)) {
                echo "<p>Error:</p>";
                echo "<pre><tt>$data</tt></pre>";
                die();
        } else {
                echo "<p>It works!</p>\n";
        }

        // Gridcoin RPC
        $rpc_host=stripslashes($_POST['rpc_host']);
        $rpc_port=stripslashes($_POST['rpc_port']);
        $rpc_login=stripslashes($_POST['rpc_login']);
        $rpc_password=stripslashes($_POST['rpc_password']);
        $rpc_unlock=stripslashes($_POST['rpc_unlock']);

        // Checking gridcoin RPC
        echo "<p>Checking gridcoin client RPC</p>";
        flush();
        $ch=curl_init("http://$rpc_host:$rpc_port");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_POST,TRUE);
        curl_setopt($ch,CURLOPT_USERPWD,"$rpc_login:$rpc_password");
        curl_setopt($ch, CURLOPT_POSTFIELDS,'{"id":1,"method":"getblockcount","params":[]}');
        $result=curl_exec($ch);
        $data=json_decode($result);
        $blocks=$data->result;
        if(isset($data->result)) {
                echo "<p>It works! Block count: $blocks</p>";
        } else {
                echo "<p>Error:</p>";
                echo "<pre><tt>$result</tt></pre>";
                die();
        }
        // ReCAPTCHA
        $recaptcha_public=stripslashes($_POST['recaptcha_public']);
        $recaptcha_private=stripslashes($_POST['recaptcha_private']);

        // Checking recaptcha
        echo "<p>If recaptcha works, then it configured properly</p>\n";
        echo "<script src='https://www.google.com/recaptcha/api.js'></script>\n";
        echo "<div class='g-recaptcha' data-sitekey='$recaptcha_public'></div>\n";
        flush();

        // Email
        $email_api_url=stripslashes($_POST['email_api_url']);
        $email_api_key=stripslashes($_POST['email_api_key']);
        $email_sender=stripslashes($_POST['email_sender']);
        $email_reply_to=stripslashes($_POST['email_reply_to']);

        // Checking email
        echo "<p>Check your inbox for test email</p>\n";
        email_send($email_reply_to,"Pool $pool_name test mail","If you read this, then mail sending functions are working");
        flush();

        // Database settings
        $db_host=stripslashes($_POST['db_host']);
        $db_login=stripslashes($_POST['db_login']);
        $db_password=stripslashes($_POST['db_password']);
        $db_base=stripslashes($_POST['db_database']);

        echo "<p>Connecting to database</p>\n";
        flush();
        db_connect();
        echo "<p>Creating database structure</p>\n";
        flush();
        $db_scheme=file("manual.sql");
        $query_buff="";
        foreach($db_scheme as $query) {
                $query_buff.=$query;
                if(preg_match('/;$/',trim($query))) {
//                      echo "Found ; query is: ";
//                      var_dump($query_buff);
                        if($query_buff!='') db_query($query_buff);
                        $query_buff="";
                }
        }

        $admin_login_escaped=db_escape($admin_login);
        $salt=bin2hex(random_bytes(16));
        $admin_passwd_hash=hash("sha256",md5($admin_password.strtolower($admin_login)).$salt);
        $salt_escaped=db_escape($salt);

        echo "<p>Add admin user</p>\n";
        flush();
        db_query("INSERT IGNORE INTO `boincmgr_users` (`username`,`email`,`salt`,`passwd_hash`,`payout_address`,`status`) VALUES ('$admin_login','','$salt_escaped','$admin_passwd_hash','','admin')");

        $settings_file=<<<_END
<?php
// Settings file

// DB variables
\$db_host="$db_host";
\$db_login="$db_login";
\$db_password="$db_password";
\$db_base="$db_base";

// Pool variables
\$pool_name="$pool_name";
\$pool_message="Welcome to \$pool_name pool";
\$pool_min_password_length=8;

// BOINC variables
\$boinc_account="$boinc_account";
\$boinc_passwd_hash="$boinc_passwd_hash";

// Gridcoin RPC variables
\$grc_rpc_host="$rpc_host";
\$grc_rpc_port="$rpc_port";
\$grc_rpc_login="$rpc_login";
\$grc_rpc_password="$rpc_password";
\$grc_rpc_wallet_passphrase="$rpc_unlock";

// Gridcoin CPID to automated billings
\$pool_cpid="$pool_cpid";

// ReCAPTCHA
\$recaptcha_public_key="$recaptcha_public";
\$recaptcha_private_key="$recaptcha_private";

// Email service
\$email_api_url="$email_api_url";
\$email_api_key="$email_api_key";
\$email_sender="$email_sender";
\$email_reply_to="$email_reply_to";

// Salt for token (never change this on working pool)
\$token_salt="$pool_salt";

// Debug mode
\$debug_mode=FALSE;

// Language file
require_once("language.php");

// Public signing key for URLs
\$signing_key="1024
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

_END;
        file_put_contents("settings.php",$settings_file);
        if(file_exists("settings.php")) {
                echo "Settings are written to settings.php. You can check <a href='./'>main page</a>";
        } else {
                $settings_file_html=htmlspecialchars($settings_file);
                echo "Write these data to settings.php:<br><pre><tt>\n$settings_file_html</tt></pre>";
        }

        echo "Add these tasks to cron:<br><br><pre><tt>\n";
        $catalog=getcwd();
        $username=exec('whoami');
        echo <<<_END
# Update BOINC projects data
5 * * * * $username php $catalog/update_projects.php
# Update gridcoin research blocks data
15 * * * * $username php $catalog/update_blocks.php
# Update currency rates
20,50 * * * * $username php $catalog/update_rates.php
# Send rewards (if exists)
25,55 * * * * $username php $catalog/send_rewards.php
# Update graphs cache
30 * * * * $username php $catalog/update_graphs.php
# Update task stats and send alerts on errors
1 1 * * * $username php $catalog/update_task_stats.php

_END;
        die();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Pool setup</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="common.css">
</head>
<body>
<h1>Welcome to pool setup</h1>
<form name=setup method=POST>
<h2>Pool settings</h2>
<p>These fields are required</p>
<p>Pool name <input type=text name=pool_name> visible in page title and BOINC client</p>
<p>Admin account login <input type=text name=admin_login> login to maintain the pool</p>
<p>Admin account password <input type=password name=admin_password></p>
<p>Pool BOINC CPID <input type=text name=pool_cpid> if this CPID generates block then send equal rewards to users</p>
<p>Random string (used as salt): <input type=password name=pool_salt></p>
<h2>MySQL settings</h2>
<p>These fields are required</p>
<p>MySQL host <input type=text name=db_host></p>
<p>MySQL login <input type=text name=db_login></p>
<p>MySQL password <input type=password name=db_password></p>
<p>MySQL database <input type=text name=db_database></p>
<h2>BOINC account</h2>
<p>These fields are required</p>
<p>You have to create BOINC account in every whitelisted project with same login and password</p>
<p>BOINC account e-mail <input type=text name=boinc_account></p>
<p>BOINC account password <input type=password name=boinc_password></p>
<h2>Gridcoin RPC</h2>
<p>These fields are required:</p>
<p>Gridcoin RPC host <input type=text name=rpc_host></p>
<p>Gridcoin RPC port <input type=text name=rpc_port></p>
<p>Gridcoin RPC login <input type=text name=rpc_login></p>
<p>Gridcoin RPC password <input type=password name=rpc_password></p>
<p>Gridcoin RPC unlock wallet password <input type=password name=rpc_unlock></p>
<h2>Google ReCAPTCHA</h2>
<p>ReCAPTCHA used when new user registering. Go to <a href='https://www.google.com/recaptcha/'>google recaptcha</a> site to obtain keys</p>
<p>If you don't want recaptcha, you can leave fields blank</p>
<p>Public key <input type=text name=recaptcha_public></p>
<p>Private key <input type=text name=recaptcha_private></p>
<h2>Email service</h2>
<p>Pool software works with <a href='https://smtp2go.com/'>smtp2go.com</a> using their API. If you want to use different service, you might need to change email.php</p>
<p>If you don't want emails, you can leave fields blank</p>
<p>URL <input type=text name=email_api_url value='https://api.smtp2go.com/v3/email/send'></p>
<p>Key <input type=text name=email_api_key></p>
<p>Sender <input type=text name=email_sender></p>
<p>Reply to <input type=text name=email_reply_to> your email</p>
<h2>Final step</h2>
<p><input type=submit value='Submit'></p>
</form>
</body>
</html>

