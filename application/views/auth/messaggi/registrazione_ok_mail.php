<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$output = <<<EOF

<h1 class="display-1">Registration</h1>
	<i class="bi bi-info-circle"></i>
          <p id="testo_home_1" class="lead">
			You have successfully signed up for <strong>Tiny.top</strong>. Check your {$registration_email} email address to activate your account.
		</p>
		<p>(redirecting, please wait)</p>

EOF;

print $output;
?>