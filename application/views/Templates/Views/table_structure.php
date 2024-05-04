<?php
if (! defined( "BASEPATH" )) {
	exit( "No direct script access allowed" );
}
$class_code = <<<EOF

<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}

\$output = <<<EOF

<!-- START Tabella -->
		<table id="record_table"  class="col w-100 fs-6 hover">
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
\EOF

;
print \$output;
?>
EOF
;
print $class_code;
?>