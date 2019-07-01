<?php
// Captcha functions

function captcha_show($session_id) {
        $session_id_escaped=db_escape($session_id);

        $code=db_query_to_variable("SELECT `captcha` FROM `user_auth_cookies` WHERE `cookie_token`='$session_id_escaped'");
        if($code=='') {
                $code=captcha_regenerate($session_id);
        }
        $image=imagecreate(100,50);
        $background_color=imagecolorallocate($image,0,0,0);
        $text_color=imagecolorallocate($image,255,255,255);
        $code_symbols=array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");
        for($i=0;$i!=strlen($code);$i++) {
                //$text_color=imagecolorallocate($image,rand(0,64),0,0);
                //imagestring($image,rand(1,4),5+$i*15+rand(0,5),5+rand(0,20),$code_symbols[rand(0,15)],$text_color);

                //$text_color=imagecolorallocate($image,0,rand(0,64),0);
                //imagestring($image,rand(1,4),5+$i*15+rand(0,5),5+rand(0,20),$code_symbols[rand(0,15)],$text_color);

                //$text_color=imagecolorallocate($image,0,0,rand(0,64));
                //imagestring($image,rand(1,4),5+$i*15+rand(0,5),5+rand(0,20),$code_symbols[rand(0,15)],$text_color);

                $text_color=imagecolorallocate($image,127+rand(0,128),127+rand(0,128),127+rand(0,128));
                imagestring($image,5,5+$i*15+rand(0,5),5+rand(0,20),$code[$i],$text_color);
        }
        header("Content-Type: image/png");
        imagepng($image);
        imagedestroy($image);
}

function captcha_regenerate($session_id) {
        $session_id_escaped=db_escape($session_id);
        $code=bin2hex(random_bytes(3));
        $code_escaped=db_escape($code);
        db_query("UPDATE `user_auth_cookies` SET `captcha`='$code_escaped' WHERE `cookie_token`='$session_id_escaped'");
        return $code;
}

function captcha_check($session_id,$user_code) {
        $user_code=trim($user_code);
        $session_id_escaped=db_escape($session_id);
        $real_code=db_query_to_variable("SELECT `captcha` FROM `user_auth_cookies` WHERE `cookie_token`='$session_id_escaped'");
        if($user_code==$real_code) return TRUE;
        else return FALSE;
}
?>
