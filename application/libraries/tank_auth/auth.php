<?php
if (! defined( 'BASEPATH' ))
	exit( 'No direct script access allowed' );
class Auth extends Super_lib {

	function __construct( ) {
		parent::__construct();
		$this->load->helper( array (
				'form',
				'url' 
		) );
		$this->load->library( 'form_validation' );
		$this->load->library( 'security' );
		$this->load->library( 'tank_auth/tank_auth' );
		$this->load->library( 'email' );
		$this->lang->load( 'tank_auth' );
		require_once "{$_SERVER['DOCUMENT_ROOT']}/application/third_party/Mail.php";
		require_once "{$_SERVER['DOCUMENT_ROOT']}/application/third_party/Mail/mime.php";
		$this->build_dependency();
	}

	function index( ) {
		redirect( '/auth/login/' );
	}

	/**
	 * Login user on the site
	 * OK
	 *
	 * @return void
	 */
	function login( $for_form = true ) {
		$this->login_effettuato = false;
		if ($this->tank_auth->is_logged_in()) {
			// logged in
			return $this->do_login();
		} elseif ($this->tank_auth->is_logged_in( FALSE )) {
			// logged in, not activated
			$this->send_again();
			return false;
		} else {
			$data [ 'login_by_username' ] = ( $this->config->item( 'login_by_username' ) and $this->config->item( 'use_username' ) );
			$data [ 'login_by_email' ] = $this->config->item( 'login_by_email' );
			$this->form_validation->set_rules( 'login', 'Login', 'trim|required|xss_clean' );
			$this->form_validation->set_rules( 'password', 'Password', 'trim|required|xss_clean' );
			$this->form_validation->set_rules( 'remember', 'Remember me', 'integer' );
			// Se è un array, arrivo da xajax
			if (is_array( $for_form )) {
				$_POST = array_merge( $_POST, $for_form );
				$this->tank_auth->response = $this->response;
				$this->tank_auth->force_local_storage = true;
			}
			// Get login for counting attempts to login
			if ($this->config->item( 'login_count_attempts' ) and ( $login = $this->input->post( 'login' ) )) {
				$login = $this->security->xss_clean( $login );
			} else {
				$login = '';
			}
			$data [ 'use_recaptcha' ] = $this->config->item( 'use_recaptcha' );
			if ($this->tank_auth->is_max_login_attempts_exceeded( $login )) {
				if ($data [ 'use_recaptcha' ])
					$this->form_validation->set_rules( 'recaptcha_response_field', 'Confirmation Code', 'trim|xss_clean|required|callback__check_recaptcha' );
				else
					$this->form_validation->set_rules( 'captcha', 'Confirmation Code', 'trim|xss_clean|required|callback__check_captcha' );
			}
			$data [ 'errors' ] = array ();
			if ($this->form_validation->run()) { 
				// validation ok
				$this->tank_auth->profilo_utente = $this->profilo_utente;
				$login_name = $this->form_validation->set_value( 'login' );
				$pwd = $this->form_validation->set_value( 'password' );
				$remember = $this->form_validation->set_value( 'remember' );
				if ($this->tank_auth->login( $login_name, $pwd, $remember, $data [ 'login_by_username' ], $data [ 'login_by_email' ] )) { 
					// success
					return $this->do_login();
				} else {
					$errors = $this->tank_auth->get_error_message();
					if (isset( $errors [ 'awaiting' ] )) { 
						// banned user but awaiting auth
						$registrazione_banned = $this->load->view( 'auth/messaggi/registrazione_banned', array (
								"errori" => $errors [ "awaiting" ] 
						), true );
						$this->show_html($registrazione_banned);
						return false;
					} elseif (isset( $errors [ 'not_activated' ] )) { 
						// not activated user
						$this->not_activated = true;
						$this->send_again();
						return false;
					} else { 
						// fail
						foreach ( $errors as $k => $v )
							$data [ 'errors' ] [ $k ] = $this->lang->line( $v );
					}
				}
			}
			$data [ 'show_captcha' ] = FALSE;
			if ($this->tank_auth->is_max_login_attempts_exceeded( $login )) {
				$data [ 'show_captcha' ] = TRUE;
				if ($data [ 'use_recaptcha' ]) {
					$data [ 'recaptcha_html' ] = $this->_create_recaptcha();
				} else {
					$data [ 'captcha_html' ] = $this->_create_captcha();
				}
			}
			if ($for_form and sizeof( $data [ 'errors' ] ) == 0) {
				$form_login = $this->load->view( 'auth/login_form', $data, true );
				$this->show_html($form_login);
			} elseif (isset( $data [ 'errors' ] ) and sizeof( $data [ 'errors' ] ) > 0) {
				foreach ( $data [ 'errors' ] as $errore ) {
					$errori [ ] = $errore;
				}
				$errori = implode( "<br>", $errori );
				$this->error( $errori );
				$form_login = $this->load->view( 'auth/login_form', $data, true );
				$this->show_html($form_login);
			}
		}
	}

	/**
	 * Logout user
	 * OK
	 *
	 * @return void
	 */
	function logout( ) {
		$this->tank_auth->logout();
		// Resetto Session, ID e Opzioni. Il resto va bene così
		$this->profilo_utente->ID = 0;
		$this->profilo_utente->Opzioni_Codificate = "";
		$this->profilo_utente->session_id = "";
		$this->set_local_storage();
		$indirizzo = current_url();
		$immagine_caricamento = "<img src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"window.location.href = '{$indirizzo}'\" alt=\"Fatture\" border=\"0\">";
		$this->response->script( " history.pushState({page: '{$this->get_site_name()}'}, '{$this->get_site_name()}', '{$indirizzo}')" );
		$this->response->assign( "magic_moment", "innerHTML", $immagine_caricamento );
	}

	/**
	 * Register user on the site
	 * OK
	 *
	 * @return void
	 */
	function register( $for_form = null ) {
		$indirizzo = current_url();
		if ($this->tank_auth->is_logged_in()) {
			// logged in
			return $this->do_login();
		} elseif ($this->tank_auth->is_logged_in( FALSE )) {
			// logged in, not activated
			$this->send_again();
			return true;
		} elseif (! $this->config->item( 'allow_registration' )) {
			// registration is off			
			$this->show_html($this->load->view( 'auth/messaggi/registrazione_disabilitata', "", true ) );
			$this->response->script( " setTimeout( function() { window.location.href = '{$indirizzo}' } , 5000 ) " );
			return false;
		} else {
			$use_username = $this->config->item( 'use_username' );
			if (is_array( $for_form )) {
				$_POST = array_merge( $_POST, $for_form );
				$this->tank_auth->response = $this->response;
				$this->tank_auth->force_local_storage = true;
			}
			if ($use_username) {
				$this->form_validation->set_rules( 'username', 'Username', 'trim|xss_clean|min_length[' . $this->config->item( 'username_min_length' ) . ']|max_length[' . $this->config->item( 'username_max_length' ) . ']|alpha_dash' );
			}
			$this->form_validation->set_rules( 'email', 'Email', 'trim|required|xss_clean|valid_email|valid_email_domain' );
			$this->form_validation->set_rules( 'password', 'Password', 'trim|required|xss_clean|min_length[' . $this->config->item( 'password_min_length' ) . ']|max_length[' . $this->config->item( 'password_max_length' ) . ']|alpha_dash' );
			$this->form_validation->set_rules( 'confirm_password', 'Confirm Password', 'trim|required|xss_clean|matches[password]' );
			$captcha_registration = $this->config->item( 'captcha_registration' );
			$use_recaptcha = $this->config->item( 'use_recaptcha' );
			if ($captcha_registration) {
				if ($use_recaptcha) {
					$this->form_validation->set_rules( 'recaptcha_response_field', 'Confirmation Code', 'trim|xss_clean|required|callback__check_recaptcha' );
				} else {
					$this->form_validation->set_rules( 'captcha', 'Confirmation Code', 'trim|xss_clean|required|callback__check_captcha' );
				}
			}
			$data [ 'errors' ] = array ();
			$email_activation = $this->config->item( 'email_activation' );
			if ($this->form_validation->run()) { 
				// validation ok
				if (! is_null( $data = $this->tank_auth->create_user( $use_username ? $this->form_validation->set_value( 'username', $this->form_validation->set_value( 'email' ) ) : $this->form_validation->set_value( 'email' ), $this->form_validation->set_value( 'email' ), $this->form_validation->set_value( 'password' ), $email_activation ) )) { // success
					$data [ 'site_name' ] = $this->config->item( 'website_name' );
					if ($email_activation) { 
						// send "activate" email
						$data [ 'activation_period' ] = $this->config->item( 'email_activation_expire' ) / 3600;
						// Clear password (just for any case)
						unset( $data [ 'password' ] ); 
						$this->_send_email( 'activate', $data [ 'email' ], $data );
						$this->show_html( $this->load->view( 'auth/messaggi/registrazione_ok_mail', array (
								"email_registrazione" => $data [ 'email' ] 
						), true ) );
						$this->response->script( " history.pushState({page: '{$this->get_site_name()}'}, '{$this->get_site_name()}', '{$this->config->config['base_url']}')" );
						$this->response->script( " setTimeout( function() { window.location.href = '{$indirizzo}' } , 5000 ) " );
						return true;
					} else {
						// Clear password (just for any case)
						unset( $data [ 'password' ] ); 
						if ($this->config->item( 'email_account_details' )) { 
							// send "welcome" email
							$this->_send_email( 'welcome', $data [ 'email' ], $data );
						}
						$this->show_html( $this->load->view( 'auth/messaggi/registrazione_ok', "", true ) );
						$this->response->script( " setTimeout( function() { window.location.href = '{$indirizzo}' } , 5000 ) " );
						return true;
					}
				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ( $errors as $k => $v ) {
						$data [ 'errors' ] [ $k ] = $this->lang->line( $v );
					}
					/*
					 * $this->invia_evento_google( "event", "page_view", array (
					 * "page_title" => "Registrazione ERRORE",
					 * "page_location" => "registrazione_errore.html"
					 * ) );
					 */
				}
			} else {
				$errors = $this->tank_auth->get_error_message();
				foreach ( $errors as $k => $v ) {
					$data [ 'errors' ] [ $k ] = $this->lang->line( $v );
				}
			}
			/*
			 * $this->invia_evento_google( "event", "page_view", array (
			 * "page_title" => "Registrazione Richiesta",
			 * "page_location" => "registrazione.html"
			 * ) );
			 */
			if ($captcha_registration) {
				if ($use_recaptcha) {
					$data [ 'recaptcha_html' ] = $this->_create_recaptcha();
				} else {
					$data [ 'captcha_html' ] = $this->_create_captcha();
				}
			}
			$data [ 'use_username' ] = $use_username;
			$data [ 'captcha_registration' ] = $captcha_registration;
			$data [ 'use_recaptcha' ] = $use_recaptcha;
			// return $this->load->view( 'auth/register_form', $data, true );
			$form_register = $this->load->view( 'auth/register_form', $data, true );
			if (! is_null( $this->response )) {
				$this->show_html( $form_register );
			} else {
				return $form_register;
			}
		}
	}

	function invia_mail_conferma( $data ) {
		$data [ 'site_name' ] = $this->config->item( 'website_name' );
		$data [ 'activation_period' ] = $this->config->item( 'email_activation_expire' ) / 3600;
		return $this->_send_email( 'activate', $data [ 'email' ], $data );
	}

	/**
	 * Send activation email again, to the same or new email address
	 *
	 * @return void
	 */
	function send_again( $form_values = null ) {
		if ($this->response == null) {
			$this->super_lib->do_exec( "start_xajax" );
			return "<img src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"setTimeout( function() { xajax_execute( 'tank_auth/auth', 'send_again' ) }, 500 );\" alt=\"Fatture\" border=\"0\">";
		}
		$indirizzo = current_url();
		if (! $this->tank_auth->is_logged_in( FALSE ) and $form_values == null) {
			// not logged in or activated
			// redirect a login
			/*
			 * $form_login = $this->load->view( 'auth/login_form', "", true );
			 * $this->response->assign( "div_contenuti_left", "innerHTML", $form_login );
			 * $home = $this->view_assembler->main_home();
			 * $this->response->assign( "div_contenuti_right_homepage", "innerHTML", $home );
			 */
			$form_register = $this->load->view( 'auth/send_again_form', array (
					"not_activated" => $this->not_activated 
			), true );
			$this->show_html( $form_register );
			return true;
		} else {
			$this->form_validation->set_rules( 'email', 'Email', 'trim|required|xss_clean|valid_email' );
			$data [ 'errors' ] = array ();
			$_POST = array_merge( $_POST, $form_values );
			if ($this->form_validation->run()) { 
				// validation ok
				if (! is_null( $data = $this->tank_auth->change_email( $this->form_validation->set_value( 'email' ) ) )) { 
					// success
					$this->invia_mail_conferma( $data );
					$this->show_html( $this->load->view( 'auth/messaggi/registrazione_mail_attivazione', array (
							"email" => $data [ "email" ] 
					), true ) );
					$this->response->script( "setTimeout( function() { $('#div_home_page').effect( 'pulsate', 1500 );  history.pushState({page: '{$this->get_site_name()}'}, '{$this->get_site_name()}', '{$this->config->config['base_url']}') } , 300);" );
					$this->response->script( " setTimeout( function() { window.location.href = '{$indirizzo}' } , 5000 ) " );
					return false;
				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ( $errors as $k => $v )
						$data [ 'errors' ] [ $k ] = $this->lang->line( $v );
				}
			}
			$form = $this->load->view( 'auth/send_again_form', $data, true );
			$this->show_html( $form );
		}
	}

	/**
	 * Activate user account.
	 * User is verified by user_id and authentication code in the URL.
	 * Can be called by clicking on link in mail.
	 * OK
	 *
	 * @return void
	 */
	function activate( $user_id = null, $new_email_key = null ) {		
		if (trim( $user_id ) === "") {
			$user_id = intval( $this->uri->segment( 3 ) );
		}
		if (trim( $new_email_key ) === "") {
			$new_email_key = $this->uri->segment( 4 );
		}
		if ($this->response == null) {
			$this->super_lib->do_exec( "start_xajax" );
			return "<img src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"setTimeout( function() { xajax_execute( 'tank_auth/auth', 'activate', '{$user_id}', '{$new_email_key}' ) }, 500 );\" alt=\"Fatture\" border=\"0\">";
		}
		$indirizzo = current_url();
		$this->tank_auth->profilo_utente = $this->profilo_utente;
		// Activate user
		if ($this->tank_auth->activate_user( $user_id, $new_email_key )) {
			// success
			$this->tank_auth->logout();
			$this->show_html($this->load->view( 'auth/messaggi/registrazione_attivazione_ok', "", true ) );
			/*
			 * $this->invia_evento_google( "event", "generate_lead", array (
			 * "currency" => "EUR",
			 * "value" => "2"
			 * ) );
			 */
		} else {
			// fail
			$this->show_html($this->load->view( 'auth/messaggi/registrazione_attivazione_ko', "", true ) );
		}
		// Setto un timeout che mi reindirizza alla home, necessario perchè ho i vari segmenti nella url :-(
		$this->response->script( "setTimeout( function() { $('#div_home_page').effect( 'pulsate', 1500 );  history.pushState({page: '{$this->get_site_name()}'}, '{$this->get_site_name()}', '{$this->config->config['base_url']}') } , 300);" );
		$this->response->script( " setTimeout( function() { window.location.href = '{$indirizzo}' } , 5000 ) " );
		return "";
	}

	/**
	 * Generate reset code (to change password) and send it to user
	 *
	 * @return void
	 */
	function forgot_password( $dati = null ) {
		if ($this->tank_auth->is_logged_in()) { 
			// logged in
			return $this->do_login();
		} elseif ($this->tank_auth->is_logged_in( FALSE )) { 
			// logged in, not activated
			return $this->send_again();
		} else {
			$indirizzo = current_url();
			$this->form_validation->set_rules( 'login', 'Email or login', 'trim|required|xss_clean' );
			$data [ 'errors' ] = array ();
			if (is_array( $dati ) and is_a( $this->response, "xajaxResponse" )) {
				$_POST = array_merge( $_POST, $dati );
			}
			if ($this->form_validation->run()) { 
				// validation ok
				if (! is_null( $data = $this->tank_auth->forgot_password( $this->form_validation->set_value( 'login' ) ) )) {
					$data [ 'site_name' ] = $this->config->item( 'website_name' );
					// Send email with password activation link
					$this->_send_email( 'forgot_password', $data [ 'email' ], $data );
					// $this->_show_message( $this->lang->line( 'auth_message_new_password_sent' ) );
					$this->show_html( $this->load->view( 'auth/messaggi/registrazione_nuova_password', "", true ) );
					$this->response->script( "setTimeout( function() { $('#div_home_page').effect( 'pulsate', 1500 );  history.pushState({page: '{$this->get_site_name()}'}, '{$this->get_site_name()}', '{$this->config->config['base_url']}') } , 300);" );
					$this->response->script( " setTimeout( function() { window.location.href = '{$indirizzo}' } , 5000 ) " );
					return false;
				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ( $errors as $k => $v )
						$data [ 'errors' ] [ $k ] = $this->lang->line( $v );
				}
			}
			$form = $this->load->view( 'auth/forgot_password_form', $data, true );
			if ($this->response == null) {
				$this->super_lib->do_exec( "start_xajax" );
				return "<img src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"setTimeout( function() { xajax_execute( 'tank_auth/auth', 'forgot_password' ) }, 500 );\" alt=\"Fatture\" border=\"0\">";
			}
			$this->show_html( $form );
		}
	}

	/**
	 * Change user password
	 *
	 * @return void
	 */
	function change_password( $user_id = "", $new_pass_key = "", $primo_giro = false ) {
		// Se $user_id è array e $new_pass_key è "", allora sono al giro di "conferma"
		if (is_array( $user_id ) and trim( $new_pass_key ) == "") {
			// faccio un bel merge tra post e user_id
			$_POST = array_merge( $_POST, $user_id );
			// E ora recupero i due valori che mi servono...
			$new_pass_key = $user_id [ "new_pass_key" ];
			$user_id = $user_id [ "user_id" ];
		}
		if (trim( $user_id ) == "") {
			$data [ "user_id" ] = $this->uri->segment( 3 );
		} else {
			$data [ "user_id" ] = $user_id;
		}
		if (trim( $new_pass_key ) == "") {
			$data [ "new_pass_key" ] = $this->uri->segment( 4 );
		} else {
			$data [ "new_pass_key" ] = $new_pass_key;
		}
		if (! $primo_giro) {
			$this->form_validation->set_rules( 'new_password', 'Nuova Password', 'trim|required|xss_clean|min_length[' . $this->config->item( 'password_min_length' ) . ']|max_length[' . $this->config->item( 'password_max_length' ) . ']|alpha_dash' );
			$this->form_validation->set_rules( 'confirm_new_password', 'Conferma nuova Password', 'trim|required|xss_clean|matches[new_password]' );
		}
		$data [ 'errors' ] = array ();
		$indirizzo = current_url();
		if ($this->form_validation->run()) { 
			// validation ok
			if ($this->tank_auth->reset_password( $user_id, $new_pass_key, $this->form_validation->set_value( 'new_password' ) ) !== null) {
				$this->show_html( $this->load->view( 'auth/messaggi/password_cambio_ok', "", true ) );
				$this->response->script( "setTimeout( function() { $('#div_home_page').effect( 'pulsate', 1500 );  history.pushState({page: '{$this->get_site_name()}'}, '{$this->get_site_name()}', '{$this->config->config['base_url']}') } , 300);" );
				//$this->response->script( " setTimeout( function() { window.location.href = '{$indirizzo}' } , 5000 ) " );
			} else {
				$this->show_html( $this->load->view( 'auth/messaggi/password_cambio_ko', "", true ) );
				$this->response->script( "setTimeout( function() { $('#div_home_page').effect( 'pulsate', 1500 );  history.pushState({page: '{$this->get_site_name()}'}, '{$this->get_site_name()}', '{$this->config->config['base_url']}') } , 300);" );
			}
			// Se passo i check del form, in ogni caso devo uscire altrimenti mi riprende il form standard e non vedo nè errori nè successi
			//$this->response->script( " setTimeout( function() { window.location.href = '{$indirizzo}' } , 5000 ) " );
			return;
		} else { 
			// fail
			if (! $primo_giro) {
				$errors = $this->tank_auth->get_error_message();
			}
			foreach ( $errors as $k => $v )
				$data [ 'errors' ] [ $k ] = $this->lang->line( $v );
		}
		if (isset( $this->response )) {
			$this->show_html($this->load->view( 'auth/reset_password_form', $data, true ) );
		} else {
			$this->super_lib->do_exec( "start_xajax" );
			return "<img src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"setTimeout( function() { xajax_execute( 'tank_auth/auth', 'change_password', '{$this->uri->segment( 3 )}', '{$this->uri->segment( 4 )}', 'true' ) }, 500 );\" alt=\"Fatture\" border=\"0\">";
		}
	}
	
	/**
	 * Send email message of given type (activate, forgot_password, etc.)
	 *
	 * @param
	 *        	string
	 * @param
	 *        	string
	 * @param
	 *        	array
	 * @return void
	 */
	function _send_email( $type, $email, &$data, $allegati = array() ) {
		$suff = "";
		if (! $this->is_live() and $type == "activate") {
			$suff = "test";
		}
		$subject = sprintf( $this->lang->line( 'auth_subject_' . $type ), $this->config->item( 'website_name' ) );
		$body = $this->load->view( 'email/' . $type . $suff . '-html', $data, TRUE );
		return $this->invia_email_generica( $email, $subject, $body, $allegati, $this->config->item( 'webmaster_email' ) );
	}

	/**
	 * Create CAPTCHA image to verify user as a human
	 *
	 * @return string
	 */
	function _create_captcha( ) {
		$this->load->helper( 'captcha' );
		
		$cap = create_captcha( array (
				'img_path' => './' . $this->config->item( 'captcha_path' ),
				'img_url' => base_url() . $this->config->item( 'captcha_path' ),
				'font_path' => './' . $this->config->item( 'captcha_fonts_path' ),
				'font_size' => $this->config->item( 'captcha_font_size' ),
				'img_width' => $this->config->item( 'captcha_width' ),
				'img_height' => $this->config->item( 'captcha_height' ),
				'show_grid' => $this->config->item( 'captcha_grid' ),
				'expiration' => $this->config->item( 'captcha_expire' ),
				'char_number' => $this->config->item( 'captcha_char_number' ) 
		) );
		
		// Save captcha params in session
		$this->session->set_flashdata( array (
				'captcha_word' => $cap [ 'word' ],
				'captcha_time' => $cap [ 'time' ] 
		) );
		
		return $cap [ 'image' ];
	}

	/**
	 * Callback function.
	 * Check if CAPTCHA test is passed.
	 *
	 * @param
	 *        	string
	 * @return bool
	 */
	function _check_captcha( $code ) {
		$time = $this->session->flashdata( 'captcha_time' );
		$word = $this->session->flashdata( 'captcha_word' );
		
		list ( $usec, $sec ) = explode( " ", microtime() );
		$now = ( ( float ) $usec + ( float ) $sec );
		
		if ($now - $time > $this->config->item( 'captcha_expire' )) {
			$this->form_validation->set_message( '_check_captcha', $this->lang->line( 'auth_captcha_expired' ) );
			return FALSE;
		} elseif (( $this->config->item( 'captcha_case_sensitive' ) and $code != $word ) or strtolower( $code ) != strtolower( $word )) {
			$this->form_validation->set_message( '_check_captcha', $this->lang->line( 'auth_incorrect_captcha' ) );
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Create reCAPTCHA JS and non-JS HTML to verify user as a human
	 *
	 * @return string
	 */
	function _create_recaptcha( ) {
		$this->load->helper( 'recaptcha' );
		
		// Add custom theme so we can get only image
		$options = "<script>var RecaptchaOptions = {theme: 'custom', custom_theme_widget: 'recaptcha_widget'};</script>\n";
		$ssl = is_ssl();
		// Get reCAPTCHA JS and non-JS HTML
		$html = recaptcha_get_html( $this->config->item( 'recaptcha_public_key' ), null, $ssl );
		
		return $options . $html;
	}

	/**
	 * Callback function.
	 * Check if reCAPTCHA test is passed.
	 *
	 * @return bool
	 */
	function _check_recaptcha( ) {
		$this->load->helper( 'recaptcha' );
		
		$resp = recaptcha_check_answer( $this->config->item( 'recaptcha_private_key' ), $this->getClientIP(), $_POST [ 'recaptcha_challenge_field' ], $_POST [ 'recaptcha_response_field' ] );
		
		if (! $resp->is_valid) {
			$this->form_validation->set_message( '_check_recaptcha', $this->lang->line( 'auth_incorrect_captcha' ) );
			return FALSE;
		}
		return TRUE;
	}
	
	private function do_login( ) {
		$this->login_effettuato = true;
		$indirizzo = current_url();
		// Creo l'immagine per fare l'update della lista
		$immagine_caricamento = "<img src=\"{$this->config->config['base_url']}assets/img/blank.gif\" style=\"display:none\" onload=\"xajax_execute('Default_actions', 'menu_iniziale')\" alt=\"Fatture\" border=\"0\">";
		// Carico la vista "shortener"
		$home_page = $this->view_assembler->main_home();
		// Push history
		$this->response->script( " history.pushState({page: '{$this->get_site_name()}'}, '{$this->get_site_name()}', '{$indirizzo}')" );
		// Assegnazione azioni e sezioni
		$this->response->assign( "magic_moment", "innerHTML", $immagine_caricamento );
		$this->show_html( $home_page);
		return true;
	}
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */