<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| Email Settings
| -------------------------------------------------------------------
| Configuration of outgoing mail server.
| */
$config['protocol'] = 'smtp';
$config['smtp_host'] = SMTP_HOST;
$config['smtp_port'] = SMTP_PORT;
$config['smtp_timeout'] = 100;
$config['smtp_user'] = SMTP_USERNAME;
$config['smtp_pass'] =  SMTP_PASSWORD;
$config['charset'] = 'utf-8';
$config['mailtype'] = 'html';
$config['wordwrap'] = TRUE;
$config['newline'] = "\r\n";

// custom values from CI Bootstrap
//$config['from_email'] = "noreply@gmail.com";
//$config['from_name'] = "CI Bootstrap";
//$config['subject_prefix'] = "[CI Bootstrap] ";

// Mailgun API (to be used in Email Client library)
$config['mailgun'] = array(
	'domain'				=> '',
	'private_api_key'		=> '',
);