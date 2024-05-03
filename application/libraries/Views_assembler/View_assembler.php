<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}

class CI_View_assembler {
	public $ci, $data, $base_url, $brow_type;

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
	
	public function doppia_freccia( $dim = 16){
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

	protected function form_select( $id = "", $name = "", $options = "", $class = "form-select", $action = "" ) {
		return $this->ci->load->view( "Common_views/form_select", array (
				"id" => $id,
				"name" => $name,
				"options" => $options,
				"action" => $action,
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
	
	protected function table_header($value = "T Header"){
		return $this->ci->load->view("Common_views/table_head.php", array("header_name" => $value), true);
	}

}
?>