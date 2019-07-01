<?php
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/auth.php");
require_once("../lib/boincmgr.php");

$f=fopen("/tmp/lockfile_rewards","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
                die("Lockfile locked\n");
        }
}

db_connect();

$magnitude_unit=boincmgr_get_variable("magnitude_unit");
$magnitude_unit_escaped=db_escape($magnitude_unit);
echo "Magnitude unit: $magnitude_unit\n";

$project_count=boincmgr_get_variable("project_count");
$project_count_escaped=db_escape($project_count);

$magnitude_total=115000;

//$bytes_per_wcg_point=;

//var_dump($magnitude_unit); die();

$count=db_query_to_variable("SELECT count(*) FROM `project_host_stats` WHERE `interval` IS NULL");
echo "Remaining results: $count\n";

$data_array=db_query_to_array("SELECT bphs.`uid`,bphs.`project_uid`,bphs.`host_uid`,bphs.`host_id`,bphs.`expavg_credit`,bphs.`total_credit`,bphs.`timestamp`,UNIX_TIMESTAMP(bphs.`timestamp`) AS unix_timestamp,
bp.`superblock_expavg_credit`,bp.`present_in_superblock`,bu.`currency`,bc.`rate_per_grc`,bu.`uid` AS user_uid
FROM `project_host_stats` AS bphs
LEFT OUTER JOIN `projects` AS bp ON bp.`uid`=bphs.`project_uid`
LEFT OUTER JOIN `hosts` AS bh ON bh.`uid`=bphs.`host_uid`
LEFT OUTER JOIN `users` AS bu ON bu.`uid`=bh.`username_uid`
LEFT OUTER JOIN `currency` AS bc ON bc.`name`=bu.`currency`
WHERE `interval` IS NULL ORDER BY `timestamp` DESC LIMIT 200");

foreach($data_array as $data_point) {
        $uid=$data_point['uid'];
        $user_uid=$data_point['user_uid'];
        $project_uid=$data_point['project_uid'];
        $host_uid=$data_point['host_uid'];
        $host_id=$data_point['host_id'];
        $expavg_credit=$data_point['expavg_credit'];
        $total_credit=$data_point['total_credit'];
        $timestamp=$data_point['timestamp'];
        $unix_timestamp=$data_point['unix_timestamp'];
        $project_superblock_expavg_credit=$data_point['superblock_expavg_credit'];
        $project_in_superblock=$data_point['present_in_superblock'];
        $currency=$data_point['currency'];
        $rate_per_grc=$data_point['rate_per_grc'];

        if($host_uid==0) {
                $currency="GRC";
                $rate_per_grc=1;
        }

        $project_uid_escaped=db_escape($project_uid);
        $host_uid_escaped=db_escape($host_uid);
        $host_id_escaped=db_escape($host_id);
        $timestamp_escaped=db_escape($timestamp);

        // Get previous data point
        $prev_point=db_query_to_array("SELECT `expavg_credit`,`total_credit`,`timestamp`,UNIX_TIMESTAMP(`timestamp`) AS unix_timestamp FROM `project_host_stats`
WHERE `project_uid`='$project_uid_escaped' AND `host_uid`='$host_uid_escaped' AND `host_id`='$host_id_escaped' AND `timestamp`<'$timestamp_escaped' ORDER BY `timestamp` DESC LIMIT 1");

        if(count($prev_point)==0) {
                echo "No prev point, skipping...\n";
                continue;
        }
//var_dump($prev_point);
        $prev_data_point=array_pop($prev_point);
        $prev_total_credit=$prev_data_point['total_credit'];
        $prev_unix_timestamp=$prev_data_point['unix_timestamp'];

        $credit_delta=$total_credit-$prev_total_credit;
        $time_delta=$unix_timestamp-$prev_unix_timestamp;

        if($project_in_superblock==0) {
                $grc_amount=0;
        } else {
                //echo "$grc_amount=($magnitude_total/$project_count) * $magnitude_unit * ($time_delta/86400) * $expavg_credit/$project_superblock_expavg_credit;\n";
                $grc_amount=($magnitude_total/$project_count) * $magnitude_unit * ($time_delta/86400) * $expavg_credit/$project_superblock_expavg_credit;
        }

        // Byteball rewards for WCG
        if($project_uid==15) {
                //$byteball_amount=0;
        }

        $currency_amount=$rate_per_grc*$grc_amount;

        echo "Time delta $time_delta credit delta $credit_delta grc amount $grc_amount currency $currency currency amount $currency_amount\n";

        $uid_escaped=db_escape($uid);
        $time_delta_escaped=db_escape($time_delta);
        $credit_delta_escaped=db_escape($credit_delta);
        $grc_amount_escaped=db_escape($grc_amount);
        $rate_per_grc_escaped=db_escape($rate_per_grc);
        $currency_escaped=db_escape($currency);
        $currency_amount_escaped=db_escape($currency_amount);
        $user_uid_escaped=db_escape($user_uid);

        db_query("UPDATE `project_host_stats` SET `interval`='$time_delta_escaped',`magnitude_unit`='$magnitude_unit_escaped',`grc_amount`='$grc_amount_escaped',
`exchange_rate`='$rate_per_grc_escaped',`currency`='$currency_escaped',`currency_amount`='$currency_amount_escaped',`is_payed_out`=0 WHERE `uid`='$uid_escaped'");
        db_query("UPDATE `users` SET `balance`=`balance`+'$currency_amount_escaped' WHERE `uid`='$user_uid_escaped'");
}
?>
