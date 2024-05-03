<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
$output = <<<EOF
<button type="button" {$disabled} id="{$id}" name="{$id}" alt="{$tip}" title="{$tip}" value="{$valore}" {$action} class="btn btn-outline-success btn_sm">{$text}</button>
EOF
;
print $output;
?>