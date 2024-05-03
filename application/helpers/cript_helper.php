<?php

function cript_high_security($string, $replacer_slash = "_-_-_", $replacer_plus = "__--__--__"){
	$obj = get_instance();
	// Encription Key, Private Key, Digest
	$private_key = $obj->config->config["encryption_private_key"]; // es recaptcha priv key
	$enc_key = $obj->config->config["encryption_key"]; // es "cnDD32.lkfei[453v"
	$digest = $obj->config->config["encryption_digest"]; // es AES256
	// Get max digest len
	$ivlen = openssl_cipher_iv_length($digest);
	$iv = substr(md5($private_key), 0, $ivlen);
	/* Create key */
	$key = md5($enc_key);
	$ciphertext = openssl_encrypt($string, $digest, $key, $options=0, $iv);
	/* return encripted data */
	$ciphertext = str_replace("/", $replacer_slash, $ciphertext);
	$ciphertext = str_replace("+", $replacer_plus, $ciphertext);
	return $ciphertext;
}

function decript_high_security($string){
	$obj = get_instance();
	// Encription Key, Private Key, Digest
	$private_key = $obj->config->config["encryption_private_key"]; // es recaptcha priv key
	$enc_key = $obj->config->config["encryption_key"]; // es "cnDD32.lkfei[453v"
	$digest = $obj->config->config["encryption_digest"]; // es AES256
	// Get max digest len
	$ivlen = openssl_cipher_iv_length($digest);
	$iv = substr(md5($private_key), 0, $ivlen);
	/* Create key */
	$key = md5($enc_key);
	$string = str_replace("_-_-_", "/", $string);
	$string = str_replace("__--__--__", "+", $string);
	$original_plaintext = openssl_decrypt($string, $digest, $key, $options=0, $iv);
	return $original_plaintext;
}

function do_cript($string){
	return base64_encode(cript_high_security($string));
}

function do_decript($string){
	return decript_high_security(base64_decode($string));
}
?>