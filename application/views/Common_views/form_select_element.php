<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
$output = <<<EOF
<option value="{$value}" {$selected}>{$text}</option>
EOF
;
print $output;
?>