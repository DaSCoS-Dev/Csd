<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
class Default_actions extends Super_lib {

	public function __construct( ) {
		parent::__construct();
		$this->build_dependency();
		$this->load->library( "xajax/mainXajax" );
		$this->build_dependency();
	}

	public function index( ) {
		// Carico la vista
		$result = "<img src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"if ('undefined' != typeof xajax){ xajax_execute('Default_actions', 'menu_iniziale', true)}\" alt=\"Fatture\" border=\"0\">";
		$this->right = array ();
		$this->right [ "immagine_load" ] = $result;
		return $result;
	}

	public function menu_iniziale( $is_refresh = false ) {
		// Carico la lista delle sezioni
		$this->get_local_storage();
		$bottone_re = "<button type=\"button\" class=\"btn\" id=\"profile\" onclick=\"xajax_execute('Default_actions', 'register')\">Sign Up</button>";
		if ($this->is_logged_in()) {
			$bottone_ll = "<button type=\"button\" class=\"btn\" id=\"actions\" onclick=\"xajax_execute('Default_actions', 'logout')\">Logout</button>";
		} else {
			$bottone_ll = "<button type=\"button\" class=\"btn\" id=\"actions\" onclick=\"xajax_execute('Default_actions', 'login')\">Login</button>";
		}
		if (is_a( $this->response, "xajaxResponse" )) {
			// Proseguo
			$this->response->assign( "login_logout", "innerHTML", $bottone_ll );
			$this->response->assign( "register_profile", "innerHTML", $bottone_re );
			// Check db connection flag. If the option is NOT present, we are installing the framework
			if ($this->config->config [ "framework_configured" ] === false) {
				$this->response->script( "$('#register_profile').hide(); xajax_execute('Admin/Main_admin', 'index', 'installFramework', 1)" );
			} elseif ($this->is_logged_in() and $this->is_admin()) {
				$this->response->script( "$('#register_profile').hide(); xajax_execute('Admin/Main_admin', 'index')" );
			} elseif ($this->is_logged_in()) {
				$this->response->script( "$('#register_profile').hide(); xajax_execute('User/Main_user', 'index')" );
			} else {
				$this->response->script( "xajax_execute('Guest/Main_guest', 'index')" );
			}
			// Additional Script only for logged is users
			if ($this->is_logged_in()) {}
			$this->enable_tool_tips();
			return $this->response;
		}
	}

	public function login( ) {
		$this->load->helper( "form" );
		$login_form = $this->load->view( "auth/login_form.php", array (), true );
		$this->show_html( $login_form );
	}

	public function forgot_password( ) {
		$this->load->helper( "form" );
		$form = $this->load->view( 'auth/forgot_password_form', array (), true );
		$this->show_html( $form );
	}

	public function register( ) {
		$this->load->helper( "form" );
		$this->load->config( 'tank_auth', TRUE );
		if (! $this->config->config [ "allow_registration" ]) {
			// registration is off
			$indirizzo = current_url();
			$this->show_html( $this->load->view( 'auth/messaggi/registrazione_disabilitata', "", true ) );
			$this->response->script( " setTimeout( function() { window.location.href = '{$indirizzo}' } , 5000 ) " );
			return false;
		} else {
			$register_form = $this->load->view( "auth/register_form.php", array (
					"use_username" => $this->config->config [ "use_username" ] 
			), true );
			$this->show_html( $register_form );
		}
	}

	public function logout( ) {
		$result = "<img src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"xajax_execute('tank_auth/auth', 'logout')\" alt=\"Fatture\" border=\"0\">";
		$this->show_html( $result );
	}
}
?>