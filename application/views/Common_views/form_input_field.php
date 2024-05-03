<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
$output = <<<EOF
<div class="col-12">
	<label for="{$id}" class="form-label">{$label}</label><br>{$alert}
	<input {$mandatory} {$readonly} type="{$type}" id="{$id}" name="{$id}" value="{$valore}" {$action} size="{$size}" class="{$class}" {$extra}>
</div>
EOF

;
print $output;
?>