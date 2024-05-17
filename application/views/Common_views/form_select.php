<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

$output = <<<EOF

<div id="select_container_{$id}">
	<label for="{$name}" class="form-label">{$label}</label><br>{$alert}
	<select id="{$name}" name="{$name}" {$action} class="{$class}">
		{$options}
	</select>
</div>

EOF

;

print $output;
?>