<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}

$output = <<<EOF
  <!-- Register -->
	<button type="submit" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Click me to register" onclick="hide_loading_layer(); xajax_execute('Default_actions', 'register')">Sign Up</button>
  <!-- // Register -->
EOF
;
print $output;
?>