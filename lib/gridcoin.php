<?php
// Gridcoin-client related functions

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

// Get block count
function grc_rpc_get_block_count() {
        $query='{"id":1,"method":"getblockcount","params":[]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        return $data->result;
}

// Get block hash
function grc_rpc_get_block_hash($number) {
        $query='{"id":1,"method":"getblockhash","params":['.$number.']}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        return $data->result;
}

// Get block info
function grc_rpc_get_block_info($hash) {
        $query='{"id":1,"method":"getblock","params":["'.$hash.'"]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        return $data->result;
}

// Get current superblock
function grc_rpc_get_current_superblock_number() {
        $query='{"id":1,"method":"superblockage"}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
        return $data->result->{"Superblock Block Number"};
}

// Get current superblock
function grc_rpc_get_transaction($hash) {
        $query='{"id":1,"method":"gettransaction","params":["'.$hash.'"]}';
        $result=grc_rpc_send_query($query);
//      $data=json_decode($result);
//var_dump($query,$data);
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
        $query='{"id":1,"method":"magnitude","params":[]}';
        $result=grc_rpc_send_query($query);
        $data=json_decode($result);
//var_dump($data->result[0][1]->{"Magnitude Unit (GRC payment per Magnitude per day)"});
//      foreach($data->result as $key => $val) if($key=="") echo "$key => $val\n";
        if($data->error == NULL) return $data->result[0][1]->{"Magnitude Unit (GRC payment per Magnitude per day)"};
        else return FALSE;
}

?>
