<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
$output = <<<EOF
<div class="shadow-sm p-3 mb-5 bg-body rounded">
	<div class="alert alert-primary" role="alert">
  		{$title}
	</div>
	{$content}
</div>
EOF

;
print $output;
?>