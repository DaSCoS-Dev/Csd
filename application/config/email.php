<?php 
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
/*
| -------------------------------------------------------------------------
| Email
| -------------------------------------------------------------------------
| This file lets you define parameters for sending emails.
| Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/libraries/email.html
|
*/
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['newline'] = "\r\n";
$config["mail_host"] = "mail.{$GLOBALS["_SERVER"]["HTTP_HOST"]}";
$config["mail_port"] = "465";
$config["mail_protocol"] = "ssl";
$config["mail_username"] = "@{$GLOBALS["_SERVER"]["HTTP_HOST"]}";
$config["mail_password"] = "";
/* End of file email.php */
/* Location: ./application/config/email.php */