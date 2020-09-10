<?php
// Functions for Gridcoin API

// Get superblock number
function grc_api_get_superblock_number() {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=superblockage"), true);
}

// Get block hash
function grc_api_get_block_hash($number) {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=getblockhash&number=$number"), true);
}

// Get block info
function grc_api_get_block_info($hash) {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=getblock&hash=$hash"), true);
}

// Get transaction
function grc_api_get_transaction($hash) {
	global $grc_api_url;
	return file_get_contents($grc_api_url."?method=gettransaction&hash=$hash");
}

// Validate address
function grc_api_validate_address($address) {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=validateaddress&address=$address"), true);
}

// Get magnitude unit
function grc_api_get_magnitude_unit() {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=magnitude_unit"), true);
}

// Get projects list
function grc_api_get_projects_list() {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=listprojects"), true);
}
