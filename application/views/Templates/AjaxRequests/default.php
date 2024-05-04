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

	public function __construct() {
		parent::__construct ();
		// Carico il model
		\$this->load->model ( "{$library_name_U}/model_{$library_name_L}" );
		\$this->corrispondenza_nomi_colonne = array (
				0 => "descrizione",
				1 => "calcolo",
				2 => "cancellato",
				3 => "null"
		);
	}

	public function get_{$library_name_L}_tabella(\$filtro = null) {
		if (! is_ajax ()) {
			\$this->session->set_userdata ( "ko_msg", "Chiamata non valida. Risulti non loggato o hai provato ad accedere in modo non corretto a una funzione del Sistema!" );
			redirect ( "/" );
		}
		\$post_dati = \$_POST;
		\$sorting = \$this->corrispondenza_nomi_colonne [ \$post_dati [ "order" ] [ 0 ] [ "column" ] ];
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
		foreach ( \$records as \$idx => \$riga ) {
			if (is_int ( \$idx / 2 )) {
				\$style_row = "table.dataTable tr.odd";
			} else {
				\$style_row = "table.dataTable tr.even";
			}
			\$class_row = "";
			\$result->aaData [] = array (
					"DT_RowAttr" => array (
							"style" => \$style_row 
					),
					"DT_RowClass" => \$class_row,
					"DT_RowId" => "row_{$library_name_L}_{\$riga->id}",
					"DT_RowClick" => "xajax_execute('{$library_name_U}/Main_{$library_name_U}', 'index', 'edit', {\$riga->id})",
					0 => "{\$riga->descrizione}",
					1 => "{\$riga->calcolo}",
					2 => "{\$abilitata}",
					3 => "<div id=\"action_buttons\" style=\"cursor: pointer; margin-top: 0px; margin-left: 0px\">
								<span class=\"fs-4 mb-3\" id=\"image_edit\" title=\"Edit\" alt=\"Edit\" style=\"cursor: pointer\" onclick=\"xajax_execute('{$library_name_U}/Main_{$library_name_U}', 'index', 'edit', {\$riga->id});\">{\$this->view_assembler->modifica_documento()}</span>
								<span class=\"fs-4 mb-3\" id=\"image_delete\" alt=\"Delete\" title=\"Delete\" onclick=\"xajax_execute('{$library_name_U}/Main_{$library_name_U}', 'index', 'delete', {\$riga->id});\">{\$this->view_assembler->elimina_documento()}</span>
				</div>" 
			);
		}
		\$coded = json_encode ( \$result );
		print \$coded;
	}
}
?>

EOF
;
print $class_code;
?>