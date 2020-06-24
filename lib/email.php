<?php
// Email sending functions

// Add email to send query
function email_add($to, $subject, $body) {
	global $email_sender;
	global $email_reply_to;
	global $broker_project_name;
	
	$message = [
		"source" => $broker_project_name,
		"to" => $to,
		"from" => $email_sender,
		"reply" => $email_reply_to,
		"subject" => $subject,
		"body" => $body,
	];
	
	auth_log($message, 6);
	
	broker_add("mailer", $message);
}
