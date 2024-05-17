<?php
if (! defined( "BASEPATH" )) {
	exit( "No direct script access allowed" );
}
$class_code = <<<EOF
<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}

\$output = <<<HTML
<!-- main container -->
<div id="edit_record_{$id}" class="container-fluid g-0">
	<!-- main row -->
	<div class="row row-cols g-1 gap-2">
		<!-- record form pieces -->
		<div id="record_form_pieces_{$id}" class="row shadow bg-body rounded">
			<!-- record header -->
			<div id="record_header_{$id}" class="w-100 h5 accordion-header bg-secondary bg-gradient text-center text-white rounded">
			</div>
			<!-- // record header -->
			<!-- tab container -->
			<div id="tab_record_{$id}" class="row" style="padding-right: 5px !important; padding-left: 5px !important">
				<!-- hiddens fields -->
				<div id="hiddenFields">
				</div>
				<!-- // hiddens fields -->
				<!-- normal fields section -->
				<div class="row row-cols-1 row-cols-sm-2 row-cols-md-4">
					<!-- container normal field -->
					<div id="nornalFields" class="row w-100 pb-2">
					</div>
					<!-- // container normal field -->
				</div>
				<!-- // normal fields section -->
			</div>
			<!-- // tab container -->
		</div>
		<!-- // record form pieces -->
	</div>
	<!-- // main row -->
</div>
<!-- // main container -->
HTML

;
print \$output;
?>
EOF
;
print $class_code;
?>