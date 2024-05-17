<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}

$output = <<<EOF
<!-- JQUERY SCRIPTS -->
	<script defer src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- // JQUERY SCRIPTS -->
<!-- BOOTSTRAP SCRIPTS -->
	<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script defer src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
	<script defer src="https://cdn.datatables.net/2.0.7/js/dataTables.bootstrap5.min.js"></script>
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