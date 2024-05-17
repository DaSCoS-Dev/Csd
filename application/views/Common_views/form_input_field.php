<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
$output = <<<EOF
<div id="input_container_{$id}">
	<label for="{$id}" class="form-label">{$label}</label><br>{$alert}
	<input {$mandatory} {$readonly} type="{$type}" id="{$id}" name="{$id}" value="{$valore}" {$action} size="{$size}" class="{$class}" {$extra}>
</div>
EOF

;
print $output;
?>