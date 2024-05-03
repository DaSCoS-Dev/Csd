<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$output = <<<EOF

<h1 class="display-1">Reset Password</h1>
	<i class="bi bi-exclamation-triangle" width="32" height="32"></i>
          <p id="testo_home_1" class="lead">
			Unfortunately you have waited too long before resetting your password, so you have to make a new request.<br>
			<div class="form-floating">
		<p><button class="w-100 btn btn-lg btn-success" onclick="xajax_execute('Default_actions', 'forgot_password');">Get a new password</button></p>
		<p><button class="w-100 btn btn-lg btn-danger" onclick="xajax_execute('Default_actions', 'logout');">Dismiss</button></p>
	</div>
		</p>

EOF;

print $output;
?>