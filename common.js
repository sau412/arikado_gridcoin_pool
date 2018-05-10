// Later I'll redo it with arrays
function show_block(block_name) {
        // Hide all
        if(document.getElementById("register_form") != null) document.getElementById("register_form").style.display='none';
        if(document.getElementById("login_form") != null) document.getElementById("login_form").style.display='none';
        if(document.getElementById("pool_info") != null) document.getElementById("pool_info").style.display='none';
        if(document.getElementById("settings") != null) document.getElementById("settings").style.display='none';
        if(document.getElementById("your_hosts") != null) document.getElementById("your_hosts").style.display='none';
        if(document.getElementById("boinc_results") != null) document.getElementById("boinc_results").style.display='none';
        if(document.getElementById("user_control") != null) document.getElementById("user_control").style.display='none';
        if(document.getElementById("project_control") != null) document.getElementById("project_control").style.display='none';
        if(document.getElementById("billing") != null) document.getElementById("billing").style.display='none';
        if(document.getElementById("payouts") != null) document.getElementById("payouts").style.display='none';
        if(document.getElementById("your_stats") != null) document.getElementById("your_stats").style.display='none';
        if(document.getElementById("pool_stats") != null) document.getElementById("pool_stats").style.display='none';
        if(document.getElementById("log") != null) document.getElementById("log").style.display='none';

        if(document.getElementById(block_name) != null) document.getElementById(block_name).style.display='block';
        else document.getElementById("pool_info").style.display='block';
        return true;
}
