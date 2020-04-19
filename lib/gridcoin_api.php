<?php
// Functions for Gridcoin API

// Get superblock number
function grc_api_get_superblock_number() {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=superblockage"));
}

// Get block hash
function grc_api_get_block_hash($number) {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=getblockhash&number=$number"));
}

// Get block info
function grc_api_get_block_info($hash) {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=getblock&hash=$hash"));
}

// Get transaction
function grc_api_get_transaction($hash) {
	global $grc_api_url;
	return file_get_contents($grc_api_url."?method=gettransaction&hash=$hash");
}

// Validate address
function grc_api_validate_address($address) {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=validateaddress&address=$address"));
}

// Get magnitude unit
function grc_api_get_magnitude_unit() {
	global $grc_api_url;
	return json_decode(file_get_contents($grc_api_url."?method=megnitude_unit"));
}
