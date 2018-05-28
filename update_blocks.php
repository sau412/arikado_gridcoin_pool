<?php
// Only for command line
if(!isset($argc)) die();

// Gridcoinresearch send rewards
require_once("settings.php");
require_once("db.php");
require_once("auth.php");

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

// Check if unsent rewards exists
db_connect();
$current_block=db_query_to_variable("SELECT MAX(`number`)+1 FROM `boincmgr_blocks`");

// 1110 = 1000 + 110 confirmations to stake blocks
if($current_block=="") $current_block=grc_rpc_get_block_count()-1110;

do{
        $block_hash=grc_rpc_get_block_hash($current_block);

        $block_info=grc_rpc_get_block_info($block_hash);

        $confirmations=$block_info->confirmations;
        $mint=$block_info->mint;
        $cpid=$block_info->CPID;
        $interest=$block_info->Interest;

        if($cpid=="") break;

        echo "Block $current_block confirmations $confirmations mint $mint cpid $cpid interest $interest\n";

        $current_block_escaped=db_escape($current_block);
        $block_hash_escaped=db_escape($block_hash);
        $mint_escaped=db_escape($mint);
        $cpid_escaped=db_escape($cpid);
        $interest_escaped=db_escape($interest);

        if($confirmations <= 110) break;

        db_query("INSERT INTO `boincmgr_blocks` (`number`,`hash`,`mint`,`cpid`,`interest`,`rewards_sent`) VALUES ('$current_block_escaped','$block_hash_escaped','$mint_escaped','$cpid_escaped','$interest_escaped',0)");

        $current_block++;

} while(TRUE);

echo "Finished importing blocks\n";

?>
