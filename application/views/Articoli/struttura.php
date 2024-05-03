<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
$output = <<<EOF

<div id="container_of_table" class="col w-100">
	<div id="new_record" class="w-100"></div>
	<!-- START Tabella -->
	<table id="show_records"  class="table table-striped hover" style="width:100%">
		<!-- START Intestazione Tabella -->
		<thead id="thead_table">
			<tr>
				{$table_headers}
			</tr>
		</thead>
		<!-- END Intestazione Tabella -->
		<!-- START Corpo Tabella -->
		<tbody id="tbody_table">

		</tbody>
		<!-- END Corpo Tabella -->
		<!-- START Footer Tabella -->
		<tfoot id="tfoot_table">
			
		</tfoot>
		<!-- End Footer Tabella -->
	</table>
	<!-- END Tabella -->
	<div id="edit_record" style="display: none; "></div>
</div>

EOF

;
print $output;
?>