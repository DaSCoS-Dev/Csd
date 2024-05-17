<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
$output = <<<EOF
<div id="input_container_wrapper_{$id}" class="{$class}">
	{$element}
</div>
EOF

;
print $output;
?>