<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
//Controlla se la chiamata è effettivamente una chiamata AJAX
function is_ajax(){
	$ci = get_instance();
	if ($ci->config->config["is_live"]) {
		if (stripos($ci->config->item("base_url"), $_SERVER["HTTP_ORIGIN"]) !== false){
			$check_host = true;
		} else {
			$check_host = false;
		}
	} else {
		$check_host = true;
	}
	return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) and
	$_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" and
	$check_host
	;
}

function is_json(){
	$ci = get_instance();
	if ($ci->config->config["is_live"]) {
		$check_host = $_SERVER["HTTP_ORIGIN"] == $ci->config->item("base_url");
	} else {
		$check_host = true;
	}
	return ((isset($_GET["callback"]) or isset($_POST["callback"])) and $check_host) !== false;
}

/*
 * This is deprecated Ereg API wrapper
 *
 * Author: JoungKyun.Kim <http://oops.org>
 * Copyright (c) 2018 JoungKyun.Kim
 * License: BSD 2-Clause
 *
 * Warning!
 *
 * 1. PHP VERSION >= PHP7
 * 2. ! function_exists ('ereg')
 *
 */

if ( ! function_exists ('ereg') ) {
	
	// {{{ +-- (string) ereg_patten_quote ($pat, $i = false)
	function ereg_pattern_quote ($pat, $i = false) {
		$pat = '~' . preg_replace ('/~/', '\\~', $pat) . '~';
		$pat .= $i ? 'i' : '';
		return $pat;
	}
	// }}}
	
	// {{{ +-- public ereg ($pat, $subj, &$regs)
	function ereg ($pat, $subj, &$regs = null) {
		$pat = ereg_pattern_quote ($pat);
		return preg_match ($pat, $subj, $regs);
	}
	// }}}
	
	// {{{ +-- public eregi ($pat, $subj, &$regs)
	function eregi ($pat, $subj, &$regs = null) {
		$pat = ereg_pattern_quote ($pat, true);
		return preg_match ($pat, $subj, $regs);
	}
	// }}}
	
	// {{{ +-- public ereg_replace ($pat, $rep, $subj)
	function ereg_replace ($pat, $rep, $subj) {
		$pat = ereg_pattern_quote ($pat);
		return preg_replace ($pat, $rep, $subj);
	}
	// }}}
	
	// {{{ +-- public eregi_replace ($pat, $rep, $subj)
	function eregi_replace ($pat, $rep, $subj) {
		$pat = ereg_pattern_quote ($pat, true);
		return preg_replace ($pat, $rep, $subj);
	}
	// }}}
	
	// {{{ +-- public split ($pat, $subj, $limit = -1)
	function split ($pat, $subj, $limit = -1) {
		$pat = ereg_pattern_quote ($pat);
		return preg_split ($pat, $subj, $limit);
	}
	// }}}
	
	// {{{ +-- public spliti ($pat, $subj, $limit = -1)
	function spliti ($pat, $subj, $limit = -1) {
		$pat = ereg_pattern_quote ($pat, true);
		return preg_split ($pat, $subj, $limit);
	}
	// }}}
	
	// {{{ +-- (string) sql_regcase (string $subj)
	function sql_regcase ($subj) {
		$len = strlen ($subj);
		$ret = '';
		
		for ( $i=0; $i<$len; $i++ ) {
			$o = ord ($subj[$i]);
			if ( ($o > 64 && $o < 91) || ($o > 96 && $o < 123) ) {
				$ret .= sprintf ('[%s%s]', strtoupper ($subj[$i]), strtolower ($subj[$i]));
				continue;
			}
			$ret .= $subj[$i];
		}
		
		return $ret;
	}
	// }}}
	
}

?>