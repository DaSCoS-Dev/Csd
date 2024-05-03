<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


function tooltip_new($tip = "Tip", $title = "Titolo", $array_valori = null){
	// Scelta dei vari valori
	$type = isset($array_valori["type"]) ? $array_valori["type"] : "dashed";
	$font_color = isset($array_valori["font_color"]) ? $array_valori["font_color"] : "fff";
	$font_face = isset($array_valori["font_face"]) ? $array_valori["font_face"] : "Arial, Helvetica, sans-serif";
	$font_size = isset($array_valori["font_size"]) ? $array_valori["font_size"] : "12px";
	$title_bg_color = isset($array_valori["title_bg_color"]) ? $array_valori["title_bg_color"] : "343a40";
	$bg_color = isset($array_valori["bg_color"]) ? $array_valori["bg_color"] : "6c757d";
	$border_color = isset($array_valori["border_color"]) ? $array_valori["border_color"] : "6c757d";
	if ($array_valori["follow"] or !isset($array_valori["follow"])){
		$follow = "true";
	} else {
		$follow = "false";
	}
	$fix = isset($array_valori["fix"]) ? $array_valori["fix"] : "null";
	$OffsetX = isset($array_valori["OffsetX"]) ? $array_valori["OffsetX"] : "15";
	$OffsetY = isset($array_valori["$OffsetY"]) ? $array_valori["$OffsetY"] : "30";
	// Creazione stringa tooltip
	$tooltip = <<<EOF
	Tip('{$tip}', DELAY, 10, 
		CLICKCLOSE, true, 
		CLOSEBTN, true, 
		SHADOW, true, 
		TITLE, '{$title}', 
		PADDING, 3, 
		BORDERSTYLE, '{$type}', 
		FONTCOLOR, '#{$font_color}', 
		FONTFACE, '{$font_face}', 
		FONTSIZE, '{$font_size}', 
		TITLEBGCOLOR, '#{$title_bg_color}', 
		BGCOLOR, '#{$bg_color}', 
		BORDERCOLOR, '#{$border_color}', 
		FOLLOWMOUSE, {$follow}, 
		FIX, {$fix}, 
		OFFSETX, {$OffsetX}, 
		OFFSETY, {$OffsetY}
		)
	
EOF
;
	return $tooltip;
}

function onmouseover_tooltip($titolo, $testo, $array_valori = null){
	$tip = tooltip_new($testo, $titolo, $array_valori);
	return " onmouseover=\"{$tip}\" onmouseout=\"UnTip()\" ";
}

?>