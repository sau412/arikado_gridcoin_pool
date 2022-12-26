<?php

function totp_check_user_uid_current_time($user_uid, $userCode) {
    $user_uid_escaped = db_escape($user_uid);
    $userSecret = db_query_to_variable("SELECT `totp_secret` FROM `users` WHERE `uid` = '$user_uid_escaped'");
    return totp_check_current_time($userSecret, $userCode);
}

function totp_check_current_time($secret, $userCode) {
    return totp_check($secret, $userCode, time());
}

function totp_check($secret, $userCode, $time) {
        $time = dechex(floor($time / 30) + 1);
        $timeHex = str_pad((string)$time, 16, "0", STR_PAD_LEFT);

        $secretHex = totp_base32tohex($secret);

        $hash = hash_hmac("sha1", hex2bin($timeHex), hex2bin($secretHex));

        $offset = hexdec(substr($hash, -1, 1));
        $part = substr($hash, $offset * 2, 8);
        $code = sprintf("%06d", hexdec($part) % 1000000);

        if($code == $userCode) return true;
        return false;
}

function totp_base32tohex($base32) {
        $base32chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
        $bits = "";
        $hex = "";
        for($i = 0; $i < strlen($base32); $i ++) {
                $val = strpos($base32chars, strtoupper($base32[$i]));
                $bits .= sprintf("%05b", $val);
        }
        for($i = 0; $i < strlen($bits); $i += 8) {
                $byte = substr($bits, $i, 8);
                $hex .= base_convert($byte, 2, 16);
        }
        return $hex;
}

function totp_generate($length) {
    $base32chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
    $result = '';
    for($i = 0; $i <= $length; $i ++) {
        $result .= $base32chars[rand(0, strlen($base32chars) - 1)];
    }
    return $result;
}

function totp_set_user_secret($user_uid, $secret) {
    $user_uid_escaped = db_escape($user_uid);
    $secret_escaped = db_escape($secret);
    db_query("UPDATE `users` SET `totp_secret` = '$secret_escaped' WHERE `uid` = '$user_uid_escaped'");
}

function totp_clear_user_secret($user_uid) {
    $user_uid_escaped = db_escape($user_uid);
    db_query("UPDATE `users` SET `totp_secret` = NULL WHERE `uid` = '$user_uid_escaped'");
}

function get_totp_qr_link($user_uid) {
    $user_uid_escaped = db_escape($user_uid);
    $email = db_query_to_variable("SELECT `email` FROM `users` WHERE `uid` = '$user_uid_escaped'");
    $secret = db_query_to_variable("SELECT `totp_secret` FROM `users` WHERE `uid` = '$user_uid_escaped'");
    return "otpauth://totp/Arikado%20Pool%20($email)?secret=$secret";
}