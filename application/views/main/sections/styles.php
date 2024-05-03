<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
$output = <<<EOF
<!-- CSS dependencies -->
	<link rel="preload" href="/assets/css/bootstrap-icons/bootstrap-icons.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="/assets/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<!-- // CSS dependencies -->
EOF
;

print $output;
?>