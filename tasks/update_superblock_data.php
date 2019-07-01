<?php
require_once("../lib/settings.php");
require_once("../lib/db.php");
require_once("../lib/gridcoin.php");
require_once("../lib/boincmgr.php");

$f=fopen("/tmp/lockfile_superblock","w");
if($f) {
        echo "Checking locks\n";
        if(!flock($f,LOCK_EX|LOCK_NB)) {
                die("Lockfile locked\n");
        }
}

db_connect();

$current_superblock_number=grc_rpc_get_current_superblock_number();

echo "Last cuperblock: $current_superblock_number\n";

$current_superblock_hash=grc_rpc_get_block_hash($current_superblock_number);

echo "Superblock hash: $current_superblock_hash\n";

$current_superblock_info=grc_rpc_get_block_info($current_superblock_hash);

$data_tx_hash=$current_superblock_info->tx[0];

echo "Project data tx hash: $data_tx_hash\n";

$transaction_data=grc_rpc_get_transaction($data_tx_hash);

//var_dump($transaction_data);
if(preg_match('/<AVERAGES>([^<]+)<\\/AVERAGES>/',$transaction_data,$matches)) {
        $projects_data_str=$matches[1];
        $projects_data_array=explode(";",$projects_data_str);
        $present_list=array();
        $project_count=0;
        foreach($projects_data_array as $single_project_data) {
                if($single_project_data=='') continue;
                list($project_name,$avg_rac,$total_rac)=explode(",",$single_project_data);
                if($project_name=='NeuralNetwork') continue;
                echo "$project_name $avg_rac $total_rac\n";
                $project_name_escaped=db_escape($project_name);
                $total_rac_escaped=db_escape($total_rac);
                db_query("UPDATE `projects` SET `present_in_superblock`=1,`superblock_expavg_credit`='$total_rac'  WHERE `superblock_name`='$project_name_escaped'");
                $present_list[]=db_escape($project_name);
                $project_count++;
        }

        // Mark other projects as absent
        $present_list_str=implode("','",$present_list);
        db_query("UPDATE `projects` SET `present_in_superblock`=0 WHERE `superblock_name` NOT IN ('$present_list_str')");

        // Add project_count
        echo "Project count in SB: $project_count\n";
//      boincmgr_set_variable("project_count",$project_count);
}

$magnitude_unit=grc_rpc_get_magnitude_unit();
echo "Magnitude unit: $magnitude_unit\n";
if($magnitude_unit!==FALSE && $magnitude_unit>0) boincmgr_set_variable("magnitude_unit",$magnitude_unit);

$projects=grc_rpc_get_projects();
$project_count=count($projects);
echo "Project count: $project_count\n";
if($project_count!==FALSE && $project_count>0) boincmgr_set_variable("project_count",$project_count);
//var_dump($projects);
?>
