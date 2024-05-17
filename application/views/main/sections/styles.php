<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
$output = <<<EOF
<!-- CSS dependencies -->
	<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="	https://cdn.datatables.net/2.0.7/css/dataTables.bootstrap5.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<!-- // CSS dependencies -->
EOF
;

print $output;
?>