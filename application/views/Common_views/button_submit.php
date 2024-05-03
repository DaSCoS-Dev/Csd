<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
$output = <<<EOF
<input type="submit" {$disabled} id="{$id}" name="{$id}" alt="{$tip}" title="{$tip}" value="{$valore}" {$action} class="btn btn-success btn-sm">
EOF
;
print $output;
?>