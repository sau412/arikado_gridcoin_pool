"use strict";

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

function generate_totp_token() {
        let result = '';
        let base32chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
        for(let i = 0; i < 32; i++) {
                result += base32chars[Math.floor(Math.random() * base32chars.length)]
        }
        return result;
}

function generate_totp_link(totp_secret) {
        return "otpauth://totp/Arikado Gridcoin Pool?secret=" + totp_secret;
}

function enable_2fa(token) {
        $("#totp_enable_button").hide();
        let totp_secret = generate_totp_token();
        let totp_link = generate_totp_link(totp_secret);
        let html = "<form name=totp_enable>\n";
        html += "<input type=hidden name=token value='" + token + "'>\n";
        html += "<input type=hidden name=totp_secret value='" + totp_secret + "'>\n";
        html += "<input type=hidden name=action value='enable_totp'>\n";
        html += "<p>Scan QR code with any 2FA app and enter valid code below to enable 2FA:</p>\n";
        html += "<p><img src='qr.php?str=" + encodeURIComponent(totp_link) + "'></p>\n";
        html += "<p>2FA code <input type=totp_secret name=text></p>\n";
        html += "<p>Password <input type=password name=password></p>\n";
        html += "<p><input type=submit value='Enable 2FA'></p>\n";
        html += "</form>\n";
        $("#totp_settings_block").html(html);
}

function disable_2fa(token) {
        $("#totp_disable_button").hide();
        let html = "<form name=totp_disable>\n";
        html += "<input type=hidden name=token value='" + token + "'>\n";
        html += "<input type=hidden name=action value='disable_totp'>\n";
        html += "<p>Enter valid 2FA code below to disable 2FA:</p>\n";
        html += "<p>2FA code <input type=totp_secret name=text></p>\n";
        html += "<p>Password <input type=password name=password></p>\n";
        html += "<p><input type=submit value='Disable 2FA'></p>\n";
        html += "</form>\n";
        $("#totp_settings_block").html(html);
}
