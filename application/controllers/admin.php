<?php
if (! defined( 'BASEPATH' ))
	exit( 'No direct script access allowed' );
class Admin extends CI_Controller {
	protected $segments = array ();
	public $left = "", $right = "", $scripts = array (), $styles = array (), $meta = array (), $super_lib = null, $view_assembler = null, $input_headers = null;

	public function __construct( ) {
		GLOBAL $BM;
		$BM->mark( "Csd_enter" );
		parent::__construct();		
		$this->segments = $this->uri->segments;
		// Recupero l'input
		$this->input_vars = $this->input->post();
		// Trick
		foreach ( is_loaded() as $var => $class ) {
			$this->$var = & load_class( $class );
		}
		// Carico la superLib
		if (! isset( $this->super_lib ) or $this->super_lib == null) {
			$this->load->library( "super_lib" );
		}
		// Carico xajax
		if (! isset( $this->mainxajax ) or $this->mainxajax == null) {
			$this->load->library( "xajax/mainXajax" );
		}		
		$this->super_lib->mainxajax = $this->mainxajax;
	}

	public function get_segments( ) {
		return $this->segments;
	}

	public function get_uri_segments( ) {
		return $this->segmenti_url;
	}

	public function index( ) {
		// Prima di tutto recupero il profilo
		if (isset( $_POST [ "xjxargs" ] [ "CUP" ] )) {
			$this->super_lib->get_local_storage();
		}
		$res = $this->do_switch();
		$this->view_assembler->index( $this->left, $this->right, $this->scripts, $this->styles, $this->meta );
	}

	/**
	 * Switch per capire che libreria dobbiamo chiamare.
	 * $sections e' un array numerico (uno based) indicante le seguenti parti
	 * 1 = admin (praticamente sempre!)
	 * 2 = sezione (es: gestione_categorie)
	 * 3 = metodo (es: lista_categorie)
	 * 4 = eventuale indice di partenza recupero records (es: 30)
	 * 5 = eventuale limite di recupero records (es: 15)
	 * Tradotto: carica la libreria gestione_categorie, esegui il metodo lista_categorie partendo
	 * dal record 30 e recuperandone massimo 15
	 * Eventuali altri dati (altri filtri, informazioni da salvare ecc) arriveranno
	 * ESCLUSIVAMENTE via $_POST (che in realta' viene "convertito" in una variabile di istanza
	 * $this->input_vars!!)
	 *
	 * @param array $sections        	
	 */
	private function do_switch( ) {
		$seg = implode( ";", $this->segments );
		$this->super_lib->check_logged_in();
		$in = $_POST;
		$inser = implode( ";", $in );
		switch ( $this->segments [ 2 ] ) {
			case "login" :
			case "execute_login" :
				$this->do_login();
				break;
			case "register" :
				$this->do_register();
				break;
			case "forgot_password" :
				$this->do_forgot_password();
				break;
			case "reset_password" :
				$this->do_reset_password();
				break;
			case "send_again" :
				$this->do_send_again();
				break;
			case "activate" :
				$this->do_activate_registration();
				break;
			case "execute_logout" :
			case "logout" :
				$this->do_logout();
				break;
			default :
				$this->do_default( "Default_actions" );
				break;
		}
	}

	private function do_register( ) {
		$this->super_lib->do_exec( "start_xajax" );
		$this->right .= "<img id= \"immagine_caricamento_registrazione_no_xajax\" src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"if ('undefined' != typeof xajax){ xajax_execute('Default_actions', 'register') }\" alt=\"Fatture\" border=\"0\">";
	}

	private function do_forgot_password( ) {
		$this->load->library( "tank_auth/auth" );
		$this->right = $this->auth->forgot_password();
	}

	private function do_reset_password( ) {
		$this->load->library( "tank_auth/auth" );
		if (! is_array( $this->right )) {
			$this->right = array ();
		}
		$this->right [ "immagine_load" ] = $this->auth->change_password();
	}

	private function do_send_again( ) {
		$this->load->library( "tank_auth/auth" );
		$this->right = $this->auth->send_again();
	}

	private function do_activate_registration( ) {
		// $this->get_login();
		if (! is_array( $this->right )) {
			$this->right = array ();
		}
		$this->right [ "immagine_load" ] = "<img src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"setTimeout( function() { xajax_execute( 'tank_auth/auth', 'activate', '{$this->uri->segment( 3 )}', '{$this->uri->segment( 4 )}' ) }, 500 );\" alt=\"Fatture\" border=\"0\">";
	}

	private function get_login( ) {
		$this->super_lib->do_exec( "start_xajax" );
	}

	private function do_login( ) {
		if (! $this->super_lib->check_logged_in() and isset( $this->input_vars [ "password" ] )) {
			$this->load->library( "tank_auth/auth" );
			$result = $this->auth->login( false );
		} elseif (! $this->super_lib->check_logged_in() and ! isset( $this->input_vars [ "password" ] )) {
			return $this->get_login();
		} else {
			$result = true;
		}
		if (! $result and isset( $this->input_vars [ "password" ] )) {
			return $this->get_login();
		} else {
			$this->do_default( "Default_actions" );
		}
	}

	private function do_logout( ) {
		$this->super_lib->do_exec( "start_xajax" );
		$this->right = array ();
		$this->right [ "immagine_load" ] = "<img id= \"immagine_caricamento_registrazione_no_xajax\" src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"if ('undefined' != typeof xajax){ show_layer=false; xajax_execute('tank_auth/auth', 'logout') }\" alt=\"Fatture\" border=\"0\">";
	}

	private function do_default( $override = null, $method = null ) {
		if ($override === null) {
			$lib = $this->segments [ 2 ];
			$meth = $this->segments [ 3 ];
		} else {
			$lib = $override;
			if (is_null( $method )) {
				$meth = "index";
			} else {
				$meth = $method;
			}
		}
		if (isset( $this->segments [ 4 ] )) {
			$start = $this->segments [ 4 ];
		} else {
			$start = null;
		}
		if (isset( $this->segments [ 5 ] )) {
			$limit = $this->segments [ 5 ];
		} else {
			$limit = null;
		}
		$this->load->library( $lib );
		// Converto in minuscolo ;)
		$lib = strtolower( $lib );
		// lib e' formata da un percorso? Allora devo prendere l'ultimo!!
		if (strpos( $lib, "/" ) !== false) {
			$lib = array_pop( explode( "/", $lib ) );
		}
		$this->build_dependency();
		$this->body = $this->$lib->$meth( $this, $start, $limit );
	}

	final protected function build_dependency( $ref = null ) {
		if (is_null( $ref )) {
			$this->ci = get_instance();
		} else {
			$this->ci = $ref;
		}
		$vars = get_object_vars( $this->ci );
		foreach ( $vars as $_ci_key => $_ci_var ) {
			if (! isset( $this->$_ci_key )) {
				$this->$_ci_key = &$this->ci->$_ci_key;
			}
		}
	}
}
?>