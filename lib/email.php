<?php
// Email sending functions

// Add email to send query
function email_add($to, $subject, $body) {
	$headers = "From: $email_sender\r\n" .
    	"Reply-To: $email_reply_to\r\n";
	mail($to, $subject, $body, $headers);

	$message = [
		"to" => $to,
		"from" => $email_sender,
		"reply" => $email_reply_to,
		"subject" => $subject,
		"body" => $body,
	];
	
	auth_log($message, 6);
}
