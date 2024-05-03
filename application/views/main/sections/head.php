<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}
$output = <<<EOF
<head>
{$title}
{$meta}
</head>
EOF
;
print $output;
?>