<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$output = <<<EOF

<h1 class="display-1">Registration</h1>
	<i class="bi bi-info-circle"></i>
          <p id="testo_home_1" class="lead">
			A new activation email has been sent to {$email}. Follow the instructions in the email to activate your account.
		</p>

EOF;

print $output;
?>