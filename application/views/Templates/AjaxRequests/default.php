<?php
if (! defined( "BASEPATH" )) {
	exit( "No direct script access allowed" );
}
$class_code = <<<EOF
<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}
class ajax_{$library_name_L} extends CI_Controller {

	protected \$table_headers, \$ordered_table_headers, \$primaryIndex;
	
	public function __construct() {
		parent::__construct ();
		// Carico il model
		\$this->load->model ( "{$library_name_U}/model_{$library_name_L}" );
		\$this->table_headers = \$this->model_{$library_name_L}->table_structure;
		\$this->ordered_table_headers = \$this->super_lib->do_exec( "chooseTableHeaderOrder", \$this->model_{$library_name_L}->get_table_header());
		\$this->primaryIndex = \$this->model_articoli->getPrimaryIndex();
	}

	public function get_{$library_name_L}_tabella(\$filtro = null) {
		if (! is_ajax ()) {
			\$this->session->set_userdata ( "ko_msg", "Invalid call. You are not logged in or you have tried to access a system function incorrectly!" );
			redirect ( "/" );
		}
		\$post_dati = \$_POST;
		\$sorting = \$this->ordered_table_headers [ \$post_dati [ "order" ] [ 0 ] [ "column" ] ];
		\$sorting_dir = \$post_dati [ "order" ] [ 0 ] [ "dir" ];
		if (trim( \$sorting ) != "") {
			\$this->model_{$library_name_L}->set_order_by( "`{\$sorting}` {\$sorting_dir}" );
		}
		\$objects = \$this->model_{$library_name_L}->get_record ( null, "{$library_name_L}", \$post_dati ["start"], \$post_dati ["length"] );
		\$this->prepare_table ( \$objects, \$post_dati );
	}

	private function prepare_table(\$records, \$post_dati = null) {
		\$dimensione = sizeof(\$records);
		\$result = new stdClass ();
		\$result->aaData = array ();
		\$result->sEcho = \$post_dati ["draw"];
		\$result->iTotalRecords = sizeof ( \$records );
		if (\$dimensione <= \$post_dati["length"]){
			\$result->iTotalDisplayRecords = \$dimensione;
		} else {
			\$result->iTotalDisplayRecords = \$post_dati["length"];
		}
		foreach ( \$records as \$idx => \$row ) {
			if (is_int ( \$idx / 2 )) {
				\$style_row = "table.dataTable tr.odd";
			} else {
				\$style_row = "table.dataTable tr.even";
			}
			\$result->aaData [\$idx ] = array (
					"DT_RowAttr" => array (
							"style" => \$style_row 
					)
			);
			\$this->prepareHeaders( \$result->aaData [ \$idx ], \$row );
		}
		\$coded = json_encode ( \$result );
		print \$coded;
	}
							
	private function prepareHeaders( &\$dataTableDefinition = array(), \$row ) {
		foreach ( \$this->ordered_table_headers as \$index => \$columnDefinition ) {
			\$column_name = \$columnDefinition["name"];
			\$def = explode(",", \$columnDefinition["definition"]);
			// n = numeric
			// d = date in unixTimeStamp !!!
			// t = textual
			// f(a-z) = function for conversion. Actually NOT used
			if (array_search("d", \$def) !== false and \$row->\$column_name !== null){
				\$data = unix_to_human(\$row->\$column_name);
			} else {
				\$data = \$row->\$column_name;
			}
			if (\$column_name == \$this->primaryIndex->Column_name){
				\$dataTableDefinition["DT_RowId"] = "row_{$library_name_L}_{\$row->\$column_name}";
				\$dataTableDefinition["DT_RowClass"] = "container";
				\$dataTableDefinition[] = "
				<div class=\"row align-items-center\">
					<div class=\"col-3\">
						{\$data}
					</div>
					<div class=\"col-9\">
						<div id=\"action_buttons_row_{$library_name_L}_{\$row->\$column_name}\" style=\"cursor: pointer;\">
 				 			<span id=\"image_edit\" title=\"Edit\" alt=\"Edit\" onclick=\"xajax_execute('{$library_name_U}/Main_{$library_name_L}', 'index', 'edit', {\$row->\$column_name});\">
 				 				{\$this->view_assembler->modifica_documento()}
 				 			</span>
 							<span id=\"image_delete\" alt=\"Delete\" title=\"Delete\" onclick=\"xajax_execute('{$library_name_U}/Main_{$library_name_L}', 'index', 'delete', {\$row->\$column_name});\">
 								{\$this->view_assembler->cancella_documento()}
 							</span>
 						</div>
 					</div>";
			} else {
				\$dataTableDefinition[] = \$data;
			}
		}
	}
}
?>
EOF
;
print $class_code;
?>