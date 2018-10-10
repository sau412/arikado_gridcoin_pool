<?php
// Only for command line
if(!isset($argc)) die();

// Gridcoinresearch send rewards
require_once("settings.php");
require_once("db.php");
require_once("auth.php");
require_once("billing.php");
require_once("boincmgr.php");
require_once("html.php");

// Send query to gridcoin client
function grc_rpc_send_query($query) {
        global $grc_rpc_host,$grc_rpc_port,$grc_rpc_login,$grc_rpc_password;
        $ch=curl_init("http://$grc_rpc_host:$grc_rpc_port");
//echo "http://$grc_rpc_host:$grc_rpc_port\n";
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_POST,TRUE);
        curl_setopt($ch,CURLOPT_USERPWD,"$grc_rpc_login:$grc_rpc_password");
//echo "$grc_rpc_login:$grc_rpc_password\n";
        curl_setopt($ch, CURLOPT_POSTFIELDS,$query);
        $result=curl_exec($ch);
//var_dump("curl error",curl_error($ch));
        curl_close($ch);

        return $result;
}

// Get balance
function grc_rpc_get_balance() {
        $query='{"id":1,"method":"getbalance","params":[]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        return $data->result;
}

// Unlock wallet
function grc_rpc_unlock_wallet() {
        global $grc_rpc_wallet_passphrase;
        $query='{"id":1,"method":"walletpassphrase","params":["'.$grc_rpc_wallet_passphrase.'",60]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        if($data->error == NULL) return TRUE;
        else return FALSE;
}

// Lock wallet
function grc_rpc_lock_wallet() {
        $query='{"id":1,"method":"walletlock","params":[]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        if($data->error == NULL) return TRUE;
        else return FALSE;
}

// Validate address
function grc_rpc_validate_address($grc_address) {
        if(auth_validate_payout_address($grc_address) == FALSE) return FALSE;
        $query='{"id":1,"method":"validateaddress","params":["'.$grc_address.'"]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
//var_dump($data);
        if($data->error == NULL) {
                if($data->result->isvalid == TRUE) return TRUE;
                else return FALSE;
        } else return FALSE;
}

// Send coins
function grc_rpc_send($grc_address,$amount) {
        $query='{"id":1,"method":"sendtoaddress","params":["'.$grc_address.'",'.$amount.']}';
//var_dump($query);
        $result=grc_rpc_send_query($query);
//var_dump($result);
        $data=json_decode($result);
        if($data->error == NULL) return $data->result;
        else return FALSE;
}

// Get whitelisted project list
function grc_rpc_get_projects() {
        $query='{"id":1,"method":"projects","params":[]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        if($data->error == NULL) return $data->result;
        else return FALSE;
}

// Get magnitude unit
function grc_rpc_get_magnitude_unit() {
        $query='{"id":1,"method":"getmininginfo","params":[]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
//      foreach($data->result as $key => $val) if($key=="") echo "$key => $val\n";
        if($data->error == NULL) return $data->result->{"Magnitude Unit"};
        else return FALSE;
}

// Check if unsent rewards exists
db_connect();

// Get balance
$current_balance=grc_rpc_get_balance();
echo "Current balance: $current_balance\n";

// Get payout information for GRC
$payout_data_array=db_query_to_array("SELECT `uid`,`grc_address`,`amount` FROM `boincmgr_faucet_payouts` WHERE `txid`=''");

if(count($payout_data_array)==0) {
        echo "No unsend payouts\n";
        die();
}

// Unlock wallet
if(grc_rpc_unlock_wallet() == FALSE) {
        echo "Unlock wallet error\n";
        auth_log("Unlock wallet error");
        die();
}

foreach($payout_data_array as $payout_data) {
        $uid=$payout_data['uid'];
        $grc_address=$payout_data['grc_address'];
        $amount=$payout_data['amount'];

        // If we have funds for this
        if($amount<$current_balance) {
                echo "Sending $amount to $grc_address\n";
                // Check_address
                if(grc_rpc_validate_address($grc_address)==TRUE) {
                        echo "Address $grc_address is valid\n";
                        // Send coins, get txid
                        $txid=grc_rpc_send($grc_address,$amount);
                        if($txid != FALSE) {
                                // Write to log
                                echo "Sent faucet reward to '$grc_address' reward '$amount'\n";
                                auth_log("Sent faucet reward to '$grc_address' reward '$amount'");
                                $uid_escaped=db_escape($uid);
                                $txid_escaped=db_escape($txid);
                                db_query("UPDATE `boincmgr_faucet_payouts` SET `txid`='$txid_escaped' WHERE `uid`='$uid_escaped'");
                                $current_balance-=$amount;
                        } else {
                                // Sending error
                                echo "Sending faucet reward error address '$grc_address' reward '$amount'\n";
                                auth_log("Sending error '$grc_address' reward '$amount'");
                        }
                } else {
                        // Address error
                        echo "Invalid address: $grc_address\n";
                        auth_log("Coinhive invalid address '$grc_address' reward '$amount'");
                        $uid_escaped=db_escape($uid);
                        db_query("UPDATE `boincmgr_faucet_payouts` SET `txid`='invalid address' WHERE `uid`='$uid_escaped'");
                }
                echo "----\n";
        } else {
                // No funds
                echo "Insufficient funds for sending rewards";
                auth_log("Insufficient funds for sending rewards");
                break;
        }
}

// Lock wallet
if(grc_rpc_lock_wallet() == FALSE) {
        echo "Lock wallet error\n";
        auth_log("Lock wallet error");
        die();
}

?>
