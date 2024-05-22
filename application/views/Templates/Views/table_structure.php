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
<a href="javascript:void(0)" class="btn btn-primary" onclick="redraw_data_table()">
	<i class="bi bi-arrow-clockwise">Reload</i>
</a>
<a href="javascript:void(0)" class="btn btn-success" onclick="xajax_execute('{$library_name_U}/Main_{$library_name_L}', 'index', 'new')">
	<i class="bi bi-file-earmark-plus">New</i>
</a>
<hr></hr>
<!-- START Tabella -->
		<table id="record_table"  class="w-100 hover table table-striped">
			<!-- START Intestazione Tabella -->
			<thead id="thead_record_table">
				<tr>
					{\$tds}
				</tr>
			</thead>
			<!-- END Intestazione Tabella -->
			<!-- START Corpo Tabella -->
			<tbody id="tbody_record_table">

			</tbody>
			<!-- END Corpo Tabella -->
			<!-- START Footer Tabella -->
			<tfoot id="tfoot_record_table">
				<tr>
					<td colspan="{\$tds_number}" id="footer_record_table" ></td>
				</tr>
			</tfoot>
			<!-- End Footer Tabella -->
		</table>
	<!-- END Tabella -->
HTML

;
print \$output;
?>
EOF
;
print $class_code;
?>