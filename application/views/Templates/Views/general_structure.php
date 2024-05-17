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

<div id="sectionTitle_Container" class="container-fluid p-2 bg-white text-dark">
	<span class="fs-3" id="sectionTitle"></span>
</div>
<div id="edit_record_wrapper" style="display: none" class="col w-100 bg-white text-dark">
</div>
<div id="show_records_wrapper" style="display: none" class="col w-100 bg-white text-dark p-2">
	<a href="javascript:void(0)" class="btn btn-primary" onclick="xajax_execute('{$library_name_U}/Main_{$library_name_L}', 'index')">
		<i class="bi bi-arrow-clockwise">Reload</i>
	</a>
	<a href="javascript:void(0)" class="btn btn-primary" onclick="xajax_execute('{$library_name_U}/Main_{$library_name_L}', 'index', 'new')">
		<i class="bi bi-file-earmark-plus">New</i>
	</a>
	<hr></hr>
	{\$table_structure["table_structure"]}
</div>
HTML

;
print \$output;
?>
EOF
;
print $class_code;
?>