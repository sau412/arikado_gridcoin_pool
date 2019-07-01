<?php
// Email sending functions

// Send mail
function email_send($to,$subject,$html_message) {
        global $email_api_url,$email_api_key,$email_sender,$email_reply_to;
        $ch=curl_init($email_api_url);

        $plain_message=strip_tags($html_message);

        $query_array=array(
                "api_key"=>$email_api_key,
                "to"=>array($to),
                "sender"=>$email_sender,
                "subject"=>$subject,
                "html_body"=>$html_message,
                "text_body"=>$plain_message,
                "custom_headers"=>array(
                        array(
                                "header"=>"Reply-To",
                                "value"=>$email_reply_to,
                        ),
                ),
        );

        $headers_array=array(
                "Content-type: application/json",
        );

        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers_array);

        $post_query=json_encode($query_array);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_POST,TRUE);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_query);
        $result=curl_exec($ch);
        curl_close($ch);
        $result_array=json_decode($result);

        if(isset($result_array->data->succeeded) && $result_array->data->succeeded==1) return TRUE;
        else return FALSE;
}

// Add email to send query
function email_add($to,$subject,$body) {
        $to_escaped=db_escape($to);
        $subject_escaped=db_escape($subject);
        $body_escaped=db_escape($body);

        db_query("INSERT INTO `email` (`to`,`subject`,`message`) VALUES ('$to_escaped','$subject_escaped','$body_escaped')");
}

// Set email status
function email_set_status($email_uid,$status) {
        $email_uid_escaped=db_escape($email_uid);

        if($status==TRUE) $status=1;
        else $status=0;

        $status_escaped=db_escape($status);

        db_query("UPDATE `email` SET `is_sent`=1,`is_success`='$status_escaped' WHERE `uid`='$email_uid_escaped'");
}

// Send emails from query
function email_send_all() {
        $unsent_emails_array=db_query_to_array("SELECT `uid`,`to`,`subject`,`message` FROM `email` WHERE `is_sent`=0");
        foreach($unsent_emails_array as $email_data) {
                $email_uid=$email_data['uid'];
                $to=$email_data['to'];
                $subject=$email_data['subject'];
                $message=$email_data['message'];
                $status=email_send($to,$subject,$message);
                email_set_status($email_uid,$status);
        }
}
?>
