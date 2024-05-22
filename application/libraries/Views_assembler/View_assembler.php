<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
class CI_View_assembler {
	public $ci, $data, $base_url, $brow_type, $model_table_name;

	public function __construct( $params = null ) {
		if ($params != null) {
			foreach ( $params as $var => $value ) {
				$this->$var = $value;
			}
		}
		$this->ci = get_instance();
		$this->base_url = base_url();
		// Costruisco le dipendenze
		$this->build_dependency();
	}

	public function index( $left = "", $right = "", $scripts = array(), $styles = array(), $meta = array() ) {
		if (isset( $this->ci->main_entry_point->data [ "xajax" ] )) {
			$xajax = $this->ci->main_entry_point->data [ "xajax" ];
		} elseif (isset( $this->super_lib->data [ "xajax" ] )) {
			$xajax = $this->super_lib->data [ "xajax" ];
		} else {
			$xajax = "";
		}
		// Titolo browser
		$vars [ "title" ] = $this->ci->load->view( "main/sections/title", "", true );
		// Meta
		$vars [ "meta" ] = $this->ci->load->view( "main/sections/meta", array (
				"meta" => $meta 
		), true );
		// Scripts js
		$vars [ "scripts" ] = $this->ci->load->view( "main/sections/script", array (
				"scripts" => $scripts,
				"xajax" => $xajax,
				"landing" => $right [ "landing" ] 
		), true );
		// Stili css
		$vars [ "styles" ] = $this->ci->load->view( "main/sections/styles", array (
				"landing" => $right [ "landing" ] 
		), true );
		// Contenuto fisso: navigation, main_content, home_page content
		if (file_exists( "/views/main/sections/home_page.php" )) {
			$home_page = $this->load->view( "main/sections/home_page", null, true );
		} else {
			$home_page = "";
		}
		$vars [ "index" ] = $this->ci->load->view( "main/sections/navigation", "", true );
		$vars [ "index" ] .= $this->ci->load->view( "main/sections/main_content", array (
				"contenuto" => $home_page 
		), true );
		// Piede, ma con trucchettino per caricamento nascosto
		$vars [ "index" ] .= $this->ci->load->view( "main/sections/footer", array (
				"contenuto" => $this->right [ "immagine_load" ] 
		), true );
		// Prendo il contenuto fisso e lo passo al body
		$vars [ "body" ] = $this->ci->load->view( "main/sections/body", array (
				"contenuto" => $vars [ "index" ],
				"styles" => $vars [ "styles" ],
				"scripts" => $vars [ "scripts" ] 
		), true );
		// Assemblo i pezzetti che finiranno dentro a sections/html
		// Dentro head ci vanno meta, styles, script
		$vars [ "head" ] = $this->ci->load->view( "main/sections/head", array (
				"title" => $vars [ "title" ],
				"meta" => $vars [ "meta" ] 
		), true );
		// Ora assemblo finalmente tutta la struttura
		$this->ci->load->view( "main/sections/html", array (
				"head" => $vars [ "head" ],
				"contenuto" => $vars [ "body" ] 
		) );
	}

	public function login( ) {
		return $this->ci->load->view( "auth/login_form", "", true );
	}

	public function main_home( ) {
		if (file_exists( "/views/main/sections/home_page.php" )) {
			return $this->ci->load->view( "main/sections/home_page", "", true );
		}
	}

	public function build( $what = null, $with = array() ) {
		if ($what === null) {
			return;
		}
		return $this->$what( $with );
	}

	public function build_dependency( $ref = null ) {
		if (is_null( $ref )) {
			$this->ci = get_instance();
		} else {
			$this->ci = $ref;
		}
		foreach ( get_object_vars( $this->ci ) as $_ci_key => $_ci_var ) {
			if (! isset( $this->$_ci_key )) {
				$this->$_ci_key = &$this->ci->$_ci_key;
			}
		}
	}

	public function send_documento( $dim = 16 ) {
		return $this->bootstrap_icon( "send", $dim );
	}

	public function modifica_documento( $dim = 16 ) {
		return $this->bootstrap_icon( "pencil", $dim );
	}

	public function scarica_zip( $dim = 16 ) {
		return $this->bootstrap_icon( "file-earmark-zip", $dim );
	}

	public function doppia_freccia( $dim = 16 ) {
		return $this->bootstrap_icon( "arrow-left-right", $dim );
	}

	public function duplica_documento( $dim = 16 ) {
		return $this->bootstrap_icon( "clipboard", $dim );
	}

	public function download_documento( $dim = 16 ) {
		return $this->bootstrap_icon( "cloud-download", $dim );
	}

	public function preview_documento( $dim = 16 ) {
		return $this->bootstrap_icon( "printer", $dim );
	}

	public function cancella_documento( $dim = 16 ) {
		return $this->bootstrap_icon( "trash", $dim );
	}

	public function ddt_documento( $dim = 16 ) {
		return $this->bootstrap_icon( "truck", $dim );
	}

	public function carrello_documento( $dim = 16 ) {
		return $this->bootstrap_icon( "cart-plus", $dim );
	}

	public function converti_documento( $dim = 16 ) {
		return $this->bootstrap_icon( "bag-plus", $dim );
	}

	public function avviso_nuovo_documento( $dim = 16 ) {
		return $this->bootstrap_icon( "shield-exclamation", $dim );
	}

	public function video_help( $dim = 16 ) {
		return $this->bootstrap_icon( "film", $dim );
	}

	public function play_video( $dim = 16 ) {
		return $this->bootstrap_icon( "youtube", $dim );
	}

	public function bootstrap_icon( $icona = "app", $size = 32, $class = "" ) {
		return $this->ci->load->view( "Common_views/bootstrap_icon", array (
				"icona" => $icona,
				"size" => $size,
				"class" => $class 
		), true );
	}

	/**
	 * Funzioni comuni per costruire alcuni tipi di campi standard
	 */
	protected function form_select_element( $value = "the_value", $text = "display text", $selected = "" ) {
		return $this->ci->load->view( "Common_views/form_select_element", array (
				"value" => $value,
				"text" => $text,
				"selected" => $selected 
		), true );
	}

	protected function form_select( $id = "", $name = "", $options = "", $label = "Label", $class = "form-select", $action = "" ) {
		return $this->ci->load->view( "Common_views/form_select", array (
				"id" => $id,
				"name" => $name,
				"options" => $options,
				"action" => $action,
				"class" => $class,
				"label" => $label 
		), true );
	}

	protected function form_input_container_wrapper( $id = "", $element = "", $class = "col" ) {
		return $this->ci->load->view( "Common_views/form_input_container_wrapper", array (
				"id" => $id,
				"element" => $element,
				"class" => $class 
		), true );
	}

	protected function form_field_normale( $id = "", $type = "text", $valore = "", $action = "", $label = "", $size = 25, $class = "form-control", $mandatory = false, $extra = "", $alert = "" ) {
		if (strpos( $type, "readonly" ) !== false) {
			$readonly = "readonly disabled";
		} else {
			$readonly = "";
		}
		if ($mandatory) {
			$mandatory = "required";
		} else {
			$mandatory = "";
		}
		return $this->ci->load->view( "Common_views/form_input_field", array (
				"id" => $id,
				"type" => $type,
				"valore" => $valore,
				"action" => $action,
				"label" => $label,
				"size" => $size,
				"class" => $class,
				"readonly" => $readonly,
				"mandatory" => $mandatory,
				"extra" => $extra,
				"alert" => $alert 
		), true );
	}

	protected function form_button_submit( $id = "", $tip = "", $valore = "", $action = "", $disabled = "" ) {
		return $this->ci->load->view( "Common_views/button_submit", array (
				"id" => $id,
				"tip" => $tip,
				"valore" => $valore,
				"action" => $action,
				"disabled" => $disabled 
		), true );
	}

	protected function action_button( $id = "", $tip = "", $valore = "", $action = "", $text = "", $disabled = "" ) {
		return $this->ci->load->view( "Common_views/action_button", array (
				"id" => $id,
				"tip" => $tip,
				"valore" => $valore,
				"action" => $action,
				"text" => $text,
				"disabled" => $disabled 
		), true );
	}

	protected function table_header( $value = array() ) {
		$header = array ();
		if (isset( $value [ "headers" ] )) {
			$header = $value [ "headers" ];
		} else {
			$header [ "name" ] = "n/a";
		}
		if (isset( $value [ "model" ] )) {
			$model_name = strtolower( $value [ "model" ] );
			$relationships = $this->super_lib->$model_name->checkRelations( $header [ "name" ], $this->super_lib->$model_name->get_table_relationships() );
			if ($relationships !== false) {
				$this->getTableInfos( $relationships->foreign_table );
				$column_label = $this->get_joinedColumnLabel($relationships);
				$header [ "name" ] = $column_label;
			}
		}
		$header [ "name" ] = str_replace( "_", " ", $header [ "name" ] );
		return $this->ci->load->view( "Common_views/table_head.php", array (
				"header_name" => $header 
		), true );
	}

	/**
	 * Loop functions to retrieve fields for "edit" or "new" record
	 * In the end of class to simplify the flow
	 */
	protected function buildEditView( $object ) {
		$fields = [ ];
		$table_name = $this->model_table_name;
		$modelName = "model_{$table_name}";
		$tablePrimaryIndex = $this->ci->$modelName->getPrimaryIndex();
		$ordered_table_headers = $this->ci->super_lib->do_exec( "chooseTableHeaderOrder", $this->ci->$modelName->get_table_header() );
		$relationships = $this->ci->$modelName->get_table_relationships();
		foreach ( $ordered_table_headers as $columnDefinition ) {
			$column_name = $columnDefinition [ "name" ];
			$def = explode( ",", $columnDefinition [ "definition" ] );
			if ($column_name === $tablePrimaryIndex->Column_name) {
				$fields [ "hidden" ] [ ] = $this->createHiddenField( $column_name, $object->$column_name );
				continue;
			}
			if (in_array( "d", $def )) {
				$fields [ "normal" ] [ ] = $this->createDateField( $column_name, $object->$column_name );
			} else {
				if ($this->hasRelations( $modelName, $column_name, $relationships )) {
					$fields [ "select" ] [ ] = $this->createSelectField( $column_name, $object, $relationships[0] );
				} else {
					$fields [ "normal" ] [ ] = $this->createTextField( $columnDefinition, $column_name, $object->$column_name );
				}
			}
		}
		return $fields;
	}

	private function createHiddenField( $column_name, $value ) {
		return $this->form_field_normale( $column_name, "hidden", $value );
	}

	private function createDateField( $column_name, $value ) {
		$data = standard_date( "DATE_W3C_FORM", $value );
		$element = $this->form_field_normale( $column_name, "datetime-local", $data, "", $column_name );
		return $this->form_input_container_wrapper( $column_name, $element, "col col-xl-2" );
	}

	private function hasRelations( $modelName, $column_name, $relationships ) {
		return $this->ci->$modelName->checkRelations( $column_name, $relationships ) !== false;
	}

	private function createSelectField( $column_name, $object, $relationships ) {
		$tableInfos = $this->getTableInfos( $relationships->foreign_table );
		$column_label = $this->get_joinedColumnLabel($relationships);
		$joinedRecords = $this->getJoinedRecords( $relationships->foreign_table );
		$options = $this->buildOptions( $joinedRecords, $object, $relationships, $tableInfos );		
		$element = $this->form_select( $column_name, $column_name, $options, $column_label );
		return $this->form_input_container_wrapper( $column_name, $element, "col col-xl-3" );
	}

	private function get_joinedColumnLabel($relationships){
		$joinedTable = strtolower( $relationships->foreign_table );
		$joinedTableModel = "model_{$joinedTable}";
		$column_label = $this->ci->$joinedTableModel->getRelatedFieldName( $relationships );
		return $column_label;
	}
	
	private function getTableInfos( $foreign_table ) {
		$joinedTableModel = strtolower("model_{$foreign_table}");
		if ($foreign_table !== null && $this->ci->$joinedTableModel == null) {
			$ucfirst = ucfirst( strtolower($foreign_table ));
			$this->ci->super_lib->load->model( "{$ucfirst}/{$joinedTableModel}" );
			$this->ci->super_lib->build_dependency();
		}
		return $this->ci->$joinedTableModel->table_structure["tableInfos"];
	}

	private function getJoinedRecords( $foreign_table ) {
		$joinedTable = strtolower( $foreign_table );
		$joinedTableModel = "model_{$joinedTable}";
		if ($joinedTable !== null && $this->ci->$joinedTableModel == null) {
			$ucfirst = ucfirst( $joinedTable );
			$this->ci->super_lib->load->model( "{$ucfirst}/{$joinedTableModel}" );
			$this->ci->super_lib->build_dependency();			
		} elseif ($joinedTable !== null && is_a($this->ci->$joinedTableModel, "Super_model")) {
			return $this->ci->$joinedTableModel->get_record( null, $joinedTable );
		}
		return [ ];
	}

	private function buildOptions( $joinedRecords, $object, $hasRelations, $tableInfos ) {
		$options = "";
		foreach ( $joinedRecords as $value ) {
			$selected = ( $object->{$hasRelations->master_field} == $value->{$hasRelations->foreign_field} ) ? "selected" : "";
			$options .= $this->form_select_element( $value->{$hasRelations->foreign_field}, $value->{$tableInfos [ 0 ]->display_field}, $selected );
		}
		return $options;
	}

	private function createTextField( $columnDefinition, $column_name, $value ) {
		$label = str_replace("_", " ", $column_name);
		$element = $this->form_field_normale( $column_name, "text", $value, "", $label );
		$class = ( stripos( $columnDefinition [ "type" ], "int" ) !== false ) ? "col col-xl-1" : "col col-xl-2";
		return $this->form_input_container_wrapper( $column_name, $element, $class );
	}
}
?>