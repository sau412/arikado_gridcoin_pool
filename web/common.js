// Show only one block from the page
function show_block(block_name) {
        $("#main_block").load("./?ajax=1&block=" + encodeURI(block_name));
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

// Check checkbox on attribute
function set_checkbox_value(checkbox_id,value) {
        document.getElementById(checkbox_id).checked=value;
}

// Project options window
function show_project_options_window(attach_uid,host_name,project_name,resource_share,options) {
        // Fill form with data
        document.getElementById("host_options_form_attach_uid").value=attach_uid;
        document.getElementById("host_options_form_host_name").textContent=host_name;
        document.getElementById("host_options_form_project_name").textContent=project_name;
        document.getElementById("host_options_form_resource_share").value=resource_share;

        var options_array=options.split(',');

        set_checkbox_value("host_options_form_detach",options_array.includes('detach'));
        set_checkbox_value("host_options_form_detach_when_done",options_array.includes('detach_when_done'));
        set_checkbox_value("host_options_form_suspend",options_array.includes('suspend'));
        set_checkbox_value("host_options_form_no_more_work",options_array.includes('dont_request_more_work'));
        set_checkbox_value("host_options_form_abort",options_array.includes('abort_not_started'));
        set_checkbox_value("host_options_form_no_cpu",options_array.includes('no_cpu'));
        set_checkbox_value("host_options_form_no_cuda",options_array.includes('no_cuda'));
        set_checkbox_value("host_options_form_no_ati",options_array.includes('no_ati'));
        set_checkbox_value("host_options_form_no_intel",options_array.includes('no_intel'));

        document.getElementById("popup_form").style.display="block";
}

// Toggle block visibility (for menu)
function toggle_block(id) {
        if( document.getElementById(id).style.display == "block") {
                document.getElementById(id).style.display = "none";
        } else {
                document.getElementById(id).style.display = "block";
        }
}

// Hide all submenu (for menu)
function hide_all_submenu(id) {
        var blocks_array = ["boinc","control","info","statistics"];
        blocks_array.forEach(function(element) {
                if(document.getElementById(element) !== null && element != id) {
                        document.getElementById(element).style.display='none';
                }
        });
}

