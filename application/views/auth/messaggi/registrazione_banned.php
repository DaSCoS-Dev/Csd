<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$output = <<<EOF

<h1 class="display-1">Registration</h1>
	<i class="bi bi-exclamation-triangle" width="32" height="32"></i>
          <p id="testo_home_1" class="lead">
			Your account is still pending or awaiting activation. {$errori}
		</p>		

EOF;

print $output;
?>