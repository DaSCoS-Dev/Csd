<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}
class ajax_exampletable extends CI_Controller {

	protected $table_headers, $ordered_table_headers, $primaryIndex, $relationships;
	
	public function __construct() {
		parent::__construct ();
		// Carico il model
		$this->load->model ( "Exampletable/model_exampletable" );
		$this->table_headers = $this->model_exampletable->table_structure;
		$this->ordered_table_headers = $this->super_lib->do_exec( "chooseTableHeaderOrder", $this->model_exampletable->get_table_header());
		$this->primaryIndex = $this->model_exampletable->getPrimaryIndex();
		$this->relationships = $this->model_exampletable->get_table_relationships();
	}

	public function get_exampletable_tabella($filtro = null) {
		if (! is_ajax ()) {
			$this->session->set_userdata ( "ko_msg", "Invalid call. You are not logged in or you have tried to access a system function incorrectly!" );
			redirect ( "/" );
		}
		$post_dati = $_POST;
		$sorting = $this->ordered_table_headers [ $post_dati [ "order" ] [ 0 ] [ "column" ] ]["name"];
		$sorting_dir = $post_dati [ "order" ] [ 0 ] [ "dir" ];
		if (trim( $sorting ) != "") {
			$this->model_exampletable->set_order_by( "`{$sorting}` {$sorting_dir}" );
		}		
		$objects = $this->model_exampletable->get_record ( null, "exampletable", $post_dati ["start"], $post_dati ["length"] );
		$this->prepare_table ( $objects, $post_dati );
	}

	private function prepare_table($records, $post_dati = null) {
		$dimensione = sizeof($records);
		$result = new stdClass ();
		$result->aaData = array ();
		$result->sEcho = $post_dati ["draw"];
		$result->iTotalRecords = sizeof ( $records );
		if ($dimensione <= $post_dati["length"]){
			$result->iTotalDisplayRecords = $dimensione;
		} else {
			$result->iTotalDisplayRecords = $post_dati["length"];
		}
		foreach ( $records as $idx => $row ) {
			$this->prepareHeaders( $result->aaData [ $idx ], $row );
		}
		$coded = json_encode ( $result );
		print $coded;
	}
							
	private function prepareHeaders( &$dataTableDefinition = array(), $row ) {
		foreach ( $this->ordered_table_headers as $index => $columnDefinition ) {
			$column_name = $columnDefinition["name"];
			$def = explode(",", $columnDefinition["definition"]);
			// n = numeric
			// d = date in unixTimeStamp !!!
			// t = textual
			// f(a-z) = function for conversion. Actually NOT used
			if (array_search("d", $def) !== false and $row->$column_name !== null){
				$data = unix_to_human($row->$column_name);
			} else {
				$data = $row->$column_name;
			}
			if ($column_name == $this->primaryIndex->Column_name){
				$dataTableDefinition["DT_RowId"] = "row_exampletable_{$row->$column_name}";
				$dataTableDefinition["DT_RowClass"] = "container";
				$dataTableDefinition[] = "
				<div class=\"row align-items-center\">
					<div class=\"col-3\">
						{$data}
					</div>
					<div class=\"col-9\">
						<div id=\"action_buttons_row_exampletable_{$row->$column_name}\" style=\"cursor: pointer;\">
 				 			<span id=\"image_edit\" class=\"btn btn-sm btn-outline-success\" title=\"Edit\" alt=\"Edit\" onclick=\"xajax_execute('Exampletable/Main_exampletable', 'index', 'edit', {$row->$column_name});\">
 				 				{$this->view_assembler->modifica_documento()}
 				 			</span>
 							<span id=\"image_delete\" class=\"btn btn-sm btn-outline-danger\" alt=\"Delete\" title=\"Delete\" onclick=\"xajax_execute('Exampletable/Main_exampletable', 'index', 'delete', {$row->$column_name});\">
 								{$this->view_assembler->cancella_documento()}
 							</span>
 						</div>
 					</div>";
			} else {
 				$hasRelations = $this->model_exampletable->checkRelations( $column_name, $this->relationships );
				if ($hasRelations !== false) {
					$data = $this->model_exampletable->getRelatedField( $hasRelations, $row );
				}
				$dataTableDefinition[] = $data;
			}
		}
	}
}
?>