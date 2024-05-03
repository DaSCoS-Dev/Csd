<?php
if ( ! defined('BASEPATH')){
	exit('No direct script access allowed');
}
$h = $l = $size;
if (intval($size) == 0) {
	//dimensione ZERO....tengo buona l'altezza standard a 32
	$h = 16;
	$l = 1;
}
$output = <<<EOF
<img src="/assets/img/bootstrap-icons/{$icona}.svg" alt="{$icona}" width="{$l}" height="{$h}" class="bi {$class}">
EOF
;
print $output;
?>