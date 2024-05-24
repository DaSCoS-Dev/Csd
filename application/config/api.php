<?php
$config["api_error_codes"] = array(
		"000.0" => "Unknow error. Sorry, I'm unable to find a correct error code :-(",
		"302.0" => "You already have a valid auth_key, use it or wait another %extra% seconds and call get_auth again to get a new one",
		"400.0" => "!! The Url is not valid !!",
		"401.0" => "Unauthorized access. invalid auth_key, please verify it.",
		"401.1" => "Unauthorized access. The request comes from a different ip than the one from which the auth_key was requested",
		"402.0" => "Payment Required. Sorry, you've run out of links. Please login and buy a license",
		"402.1" => "Too many Urls. Sorry, you entered too many URLs to shorten. The maximum possible is %extra%. ",
		"404.0" => "User not found",
		"404.1" => "No Post data or invalid values....",
		"406.0" => "Not Acceptable. %extra%",
		"408.0" => "Request Timeout: the auth_key has expired. Request a new one via get_auth",
		"409.0" => "!! You cannot reference myself (tiny.top) !!",
);
// REST / API
$config['rest_keys_table'] = 'csd_keys';
$config['rest_key_column'] = 'key';
$config['rest_key_length'] = 40;
// Minutes.....
$config["rest_key_duration"] = 15;
// Max number of simultaneus records requests
$config["rest_max_requests"] = 20;
?>