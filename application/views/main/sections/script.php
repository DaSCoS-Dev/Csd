<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}

$output = <<<EOF
<!-- JQUERY SCRIPTS -->
	<script defer src="/assets/js/jquery.min.js"></script>
<!-- // JQUERY SCRIPTS -->
<!-- BOOTSTRAP SCRIPTS -->
	<script defer src="/assets/js/bootstrap.bundle.min.js"></script>		
<!-- // BOOTSTRAP SCRIPTS -->
<!-- XAJAX SCRIPTS -->
	{$xajax}
	<script defer src="/assets/js/xajax_messages.min.js"></script>
	<script defer src="/assets/js/commons.min.js"></script>
<!-- // XAJAX SCRIPTS -->

EOF
;

print $output;	
?>