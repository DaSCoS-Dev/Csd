<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
if (isset($hidden) and trim($hidden) !== ""){
	$hidden_field =<<<EOF
	<input type="hidden" id="discriminator" name="discriminator" value="{$hidden}">
EOF
;
} else {
	$hidden_field = "";
}
$output = <<<EOF
<form class="row g-3" id="{$form_id}" onsubmit="return false">
{$hidden_field}
EOF

;
print $output;
?>