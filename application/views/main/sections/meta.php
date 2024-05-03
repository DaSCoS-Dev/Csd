<?php
if ( ! defined('BASEPATH')){
	exit('No direct script access allowed');
}
if (isset($this->config->config["google_verification"]) and trim($this->config->config["google_verification"]) !== "") {
	$google_verification = "<meta name=\"google-site-verification\" content=\"{$this->config->config["google_verification"]}\">";
} else {
	$google_verification = "";
}
$anno = date("Y");
$output = <<<EOF
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="title" content="{$this->config->config["website_name"]}">
	<meta name="content-type" content="text/html; charset=utf-8">
	<meta name="keywords" content="">
	<meta name="description" content="">
	<meta name="generator" content="Code Igniter - Modded by DaSCoS">
	<meta name="Vary" content="Accept-Encoding">
	<meta name="owner" content="Daniele Continenza">
	<meta name="Classification" content="">
	<meta name="author" content="Dascos Realizzazione Siti Web, Software Web, Software">
	<meta name="copyright" content="copyright 2013 - {$anno} by Dascos - Realizzazione Siti Web, Software Web, Software">
	<meta name="revisit-after" content="3 days">
	<meta name="distribution" content="global">
	<meta name="Cache-Control" content="max-age=31536000">
		{$meta["extra_tags"]}
		{$google_verification}

EOF;

print $output;

?>