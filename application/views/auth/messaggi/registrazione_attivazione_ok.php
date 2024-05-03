<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$output = <<<EOF

<h1 class="display-1">Registration</h1>
	<i class="bi bi-info-circle"></i>
          <p id="testo_home_1" class="lead">
			Your account has been activated.
		</p>
		<p><button class="w-100 btn btn-lg btn-success" onclick="xajax_execute('Default_actions', 'login');">Login</button></p>

EOF;

print $output;
?>