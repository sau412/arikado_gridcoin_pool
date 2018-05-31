// Current block name for page
var current_block_name;

// Show only one block from the page
function show_block(block_name) {
        var blocks_array = ["register_form","login_form","pool_info","settings","your_hosts","boinc_results",
                "user_control","project_control","billing","payouts","your_stats","pool_stats","log","pool_info_editor"];

        // If user clicks same block - reload page
        if(block_name==current_block_name) {
                document.location.reload(true);
                return true;
        }

        // Hide all
        blocks_array.forEach(function(element) {
                if(document.getElementById(element+'_block') != null) document.getElementById(element+'_block').style.display='none';
        });

        // Show block if exists
        if(document.getElementById(block_name+'_block') != null) document.getElementById(block_name+'_block').style.display='block';
        else document.getElementById("pool_info_block").style.display='block';

        current_block_name=block_name;

        return true;
}

// Require confirmation from user
function check_delete_host() {
        var result = prompt("You will not receive remaining rewards for this host, write 'i donate host rewards to pool' in the field below to delete host:");
        if (result === "i donate host rewards to pool") {
                //alert("correct");
                return true;
        } else {
                //alert("incorrect");
                return false;
        }
}
