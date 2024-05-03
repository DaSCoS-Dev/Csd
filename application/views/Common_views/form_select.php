<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

$output = <<<EOF

<div id="select_container_{$id}">
	<select id="{$name}" name="{$name}" {$action} class="{$class}">
		{$options}
	</select>
</div>

EOF

;

print $output;
?>