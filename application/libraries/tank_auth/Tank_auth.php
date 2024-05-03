<?php
if (! defined( 'BASEPATH' ))
	exit( 'No direct script access allowed' );

require_once ( "{$_SERVER['DOCUMENT_ROOT']}/application/helpers/PasswordHash.php" );

define( 'STATUS_ACTIVATED', '1' );
define( 'STATUS_NOT_ACTIVATED', '0' );

/**
 * Tank_auth
 *
 * Authentication library for Code Igniter.
 *
 * @package Tank_auth
 * @author Ilya Konyukhov (http://konyukhov.com/soft/)
 * @version 1.0.9
 *          @based on DX Auth by Dexcell (http://dexcell.shinsengumiteam.com/dx_auth)
 * @license MIT License Copyright (c) 2008 Erick Hartanto
 */
class Tank_auth extends Super_lib {
	private $error = array ();

	function __construct( $params = null ) {
		parent::__construct( $params );
		if ($params != null) {
			foreach ( $params as $var => $value ) {
				$this->$var = $value;
			}
		}
		$this->is_autologin = false;
		$this->load->config( 'tank_auth', TRUE );
		$this->load->helper( 'date' );
		$this->load->model( 'tank_auth/users' );
		$this->load->model( 'tank_auth/login_attempts' );
		$this->build_dependency();
		$this->users->db = $this->db;
		$this->login_attempts->db = $this->db;
	}

	/**
	 * Login user on the site.
	 * Return TRUE if login is successful
	 * (user exists and activated, password is correct), otherwise FALSE.
	 *
	 * @param
	 *        	string (username or email or both depending on settings in config file)
	 * @param
	 *        	string
	 * @param
	 *        	bool
	 * @return bool
	 */
	function login( $login, $password, $remember = true, $login_by_username = true, $login_by_email = true, $special_bypass = false ) {
		if (( strlen( $login ) > 0 ) and ( strlen( $password ) > 0 )) {
			
			// Which function to use to login (based on config)
			if ($login_by_username and $login_by_email) {
				$get_user_func = 'get_user_by_login';
			} else if ($login_by_username) {
				$get_user_func = 'get_user_by_username';
			} else {
				$get_user_func = 'get_user_by_email';
			}
			if ($remember == null) {
				$remember = true;
			}
			if (! is_null( $user = $this->ci->users->$get_user_func( $login ) )) { // login ok
			                                                                       
				// Does password match hash in database?
				$hasher = new PasswordHash( $this->ci->config->item( 'phpass_hash_strength' ), $this->ci->config->item( 'phpass_hash_portable' ) );
				if ($hasher->CheckPassword( $password, $user->password ) and ! $special_bypass) { // password ok
					if ($user->banned == 1 and $user->activated == 1) { // fail - awaiting final activation
						$this->error = array (
								'awaiting' => $user->ban_reason 
						);
						if ($this->ci->config->config [ "session_use_cookie" ]) {
							$this->ci->session->set_userdata( array (
									'user_id' => $user->id 
							) );
						} else {}
					} elseif ($user->activated == 0) { // fail - not activated
						$this->error = array (
								'not_activated' => '' 
						);
					} elseif ($user->banned == 1) { // fail - banned
						$this->error = array (
								'banned' => $user->ban_reason 
						);
						if ($this->ci->config->config [ "session_use_cookie" ]) {
							$this->ci->session->set_userdata( array (
									'user_id' => $user->id 
							) );
						} else {}
					} else {
						// Save user data
						if ($this->ci->config->config [ "session_use_cookie" ]) {
							$this->ci->session->set_userdata( array (
									'user_id' => $user->id,
									'username' => $user->username,
									'status' => ( $user->activated == 1 ) ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED,
									'created' => mysql_to_unix( $user->created ) 
							) );
						} else {
							$user->created_ue = mysql_to_unix( $user->created );
						}
						
						if ($user->activated == 0) { // fail - not activated
							$this->error = array (
									'not_activated' => '' 
							);
						} else { // success
						         // if ($remember) {
							$this->create_autologin( $user->id );
							// }
							$this->clear_login_attempts( $login );
							
							$this->ci->users->update_login_info( $user->id, $this->ci->config->item( 'login_record_ip' ), $this->ci->config->item( 'login_record_time' ) );
							$profilo = $this->ci->users->get_profile_by_userid( $user->id );
							$this->build_profilo_utente( $profilo, $user );
							return TRUE;
						}
					}
				} elseif ($special_bypass) {
					$this->create_autologin( $user->id );
					$this->clear_login_attempts( $login );
					
					$this->ci->users->update_login_info( $user->id, $this->ci->config->item( 'login_record_ip' ), $this->ci->config->item( 'login_record_time' ) );
					return TRUE;
				} else { // fail - wrong password
					$this->increase_login_attempt( $login );
					$this->error = array (
							'password' => 'auth_incorrect_password' 
					);
				}
			} else { // fail - wrong login
				$this->increase_login_attempt( $login );
				$this->error = array (
						'login' => 'auth_incorrect_login' 
				);
			}
		}
		return FALSE;
	}

	private function build_profilo_utente( $profilo, $user ) {
		if (! isset( $this->profilo_utente )) {
			$this->profilo_utente = new stdClass();
		}
		$this->profilo_utente->ID = $profilo->user_id;
		$this->profilo_utente->Codice = $user->code;
		$this->profilo_utente->Opzioni_Codificate = json_decode( $profilo->options );
		// $this->profilo_utente->Conteggio = $profilo->conteggio;
		if (! ( isset( $this->profilo_utente->Opzioni_Codificate->Cartella_Utente ) )) {
			$this->profilo_utente->Opzioni_Codificate->Cartella_Utente = md5( cript_high_security( "{$profilo->user_id}{$user->created_ue}" ) );
		}
		if ($this->ci->config->config [ "session_use_cookie" ]) {
			if (isset( $this->session->userdata [ "session_id" ] )) {
				$this->profilo_utente->session_id = $this->session->userdata [ "session_id" ];
			}
			$this->session->userdata [ "cartella_utente" ] = $this->profilo_utente->Opzioni_Codificate->Cartella_Utente;
		}
	}

	/**
	 * Logout user from the site
	 *
	 * @return void
	 */
	function logout( ) {
		if ($this->ci->config->config [ "session_use_cookie" ]) {
			$this->delete_autologin();
			// See http://codeigniter.com/forums/viewreply/662369/ as the reason for the next line
			$this->ci->session->set_userdata( array (
					'user_id' => '',
					'username' => '',
					'status' => '' 
			) );
			$this->ci->session->sess_destroy();
		} else {
			$this->profilo_utente->ID = 0;
		}
	}

	/**
	 * Check if user logged in.
	 * Also test if user is activated or not.
	 *
	 * @param
	 *        	bool
	 * @return bool
	 */
	function is_logged_in( $activated = TRUE ) {
		if (! $activated) {
			if (isset( $this->super_lib->profilo_utente->Codice )) {
				return $this->super_lib->profilo_utente->status === ( $activated ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED );
			} elseif ($this->ci->config->config [ "session_use_cookie" ]) {
				return $this->ci->session->userdata( 'status' ) === ( $activated ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED );
			} else {
				return parent::is_logged_in();
			}
		} else {
			return parent::is_logged_in();
		}
	}

	/**
	 * Get user_id
	 *
	 * @return string
	 */
	function get_user_id( ) {
		return $this->ci->session->userdata( 'user_id' );
	}

	/**
	 * Get username
	 *
	 * @return string
	 */
	function get_username( ) {
		return $this->ci->session->userdata( 'username' );
	}

	/**
	 * Create new user on the site and return some data about it:
	 * user_id, username, password, email, new_email_key (if any).
	 *
	 * @param
	 *        	string
	 * @param
	 *        	string
	 * @param
	 *        	string
	 * @param
	 *        	bool
	 * @return array
	 */
	function create_user( $username, $email, $password, $email_activation, $invited_by = 0 ) {
		if (strlen( $username ) > 0 and ! $this->ci->users->is_username_available( $username )) {
			$this->error = array (
					'username' => 'auth_username_in_use' 
			);
		} elseif (! $this->ci->users->is_email_available( $email )) {
			$this->error = array (
					'email' => 'auth_email_in_use' 
			);
		} else {
			// Hash password using phpass
			$hasher = new PasswordHash( $this->ci->config->item( 'phpass_hash_strength' ), $this->ci->config->item( 'phpass_hash_portable' ) );
			$hashed_password = $hasher->HashPassword( $password );
			
			$data = array (
					'username' => $username,
					'password' => $hashed_password,
					'email' => $email,
					'last_ip' => $this->getClientIP(),
					'invited_by' => $invited_by,
					"banned" => 0,
					"ban_reason" => "",
					"code" => $this->profilo_utente->Codice 
			);
			
			if ($email_activation) {
				$data [ 'new_email_key' ] = md5( rand() . microtime() );
			}
			if (! is_null( $res = $this->ci->users->create_user( $data, ! $email_activation ) )) {
				// $dati_utente = $this->crea_utente( $res [ 'user_id' ] );
				$data [ 'user_id' ] = $res [ 'user_id' ];
				return $data;
			}
		}
		return NULL;
	}

	function crea_utente( $data ) {
		$adesso = time();
		// Creo l'utente
		unset( $data [ 'last_ip' ] );
		$id_univoco_folder = md5( cript_high_security( "{$data ['user_id']}{$adesso}" ) );
		$opzioni = array (
				"ID_Utente" => $res [ "user_id" ],
				"ID_Univoco" => "{$id_univoco_folder}" 
		);
		// "Private_Key" => $private_key
		
		// intval( $data [ "user_id" ] ) <= 50
		if (! $this->is_live()) {
			// Abbuono sei mesi o 10 fatture? 10 fatture, meglio...
			$opzioni [ "Opzioni_Codificate" ] = array (
					"Abbonamento" => array (
							"Stato_Pagamento" => 1,
							"Data_Pagamento" => time() 
					) 
			);
			
			$opzioni [ "Opzioni_Codificate" ] = json_encode( $opzioni [ "Opzioni_Codificate" ] );
		}
		/**
		 * Recupero la partita iva, faccio un check sui dati tramite la chiamata check_piva, prendo i dati e li inserisco nel profilo
		 */
		$cliente = new stdClass();
		$cliente->ID_Utente = $res [ "user_id" ];
		$cliente->ID_Tipo_Record = 35;
		// $id_cliente = $this->model_clienti->salva_record( $cliente, "cliente" );
		// Salvo le opzioni
		// $this->main_entita->model_opzioni_utente->salva_record( $opzioni, "opzioni" );
		return $data;
	}

	/**
	 * Check if username available for registering.
	 * Can be called for instant form validation.
	 *
	 * @param
	 *        	string
	 * @return bool
	 */
	function is_username_available( $username ) {
		return ( ( strlen( $username ) > 0 ) and $this->ci->users->is_username_available( $username ) );
	}

	/**
	 * Check if email available for registering.
	 * Can be called for instant form validation.
	 *
	 * @param
	 *        	string
	 * @return bool
	 */
	function is_email_available( $email ) {
		return ( ( strlen( $email ) > 0 ) and $this->ci->users->is_email_available( $email ) );
	}

	/**
	 * Change email for activation and return some data about user:
	 * user_id, username, email, new_email_key.
	 * Can be called for not activated users only.
	 *
	 * @param
	 *        	string
	 * @return array
	 */
	function change_email( $email, $uid = null ) {
		if (trim( $email ) == "") {
			return null;
		}
		if ($uid == null) {
			$user_id = $this->ci->session->userdata( 'user_id' );
		} else {
			$user_id = $uid;
		}
		if (! is_null( $user = $this->ci->users->get_user_by_id( $user_id, FALSE ) )) {
			$data = array (
					'user_id' => $user_id,
					'username' => $user->username,
					'email' => $email 
			);
			if (strtolower( $user->email ) == strtolower( $email )) { // leave activation key as is
				$data [ 'new_email_key' ] = $user->new_email_key;
				return $data;
			} elseif ($this->ci->users->is_email_available( $email )) {
				$data [ 'new_email_key' ] = md5( rand() . microtime() );
				$this->ci->users->set_new_email( $user_id, $email, $data [ 'new_email_key' ], FALSE );
				return $data;
			} else {
				$this->error = array (
						'email' => 'auth_email_in_use' 
				);
			}
		}
		return NULL;
	}

	/**
	 * Activate user using given key
	 *
	 * @param
	 *        	string
	 * @param
	 *        	string
	 * @param
	 *        	bool
	 * @return bool
	 */
	function activate_user( $user_id, $activation_key, $activate_by_email = TRUE ) {
		// $this->ci->users->purge_na( $this->ci->config->item( 'email_activation_expire' ) );
		if (( strlen( $user_id ) > 0 ) and ( strlen( $activation_key ) > 0 )) {
			$this->ci->users->profilo_utente = $this->profilo_utente;
			$res = $this->ci->users->activate_user( $user_id, $activation_key, $activate_by_email );
			$this->check_cartella_utente();
			return $res;
		}
		return FALSE;
	}

	/**
	 * Set new password key for user and return some data about user:
	 * user_id, username, email, new_pass_key.
	 * The password key can be used to verify user when resetting his/her password.
	 *
	 * @param
	 *        	string
	 * @return array
	 */
	function forgot_password( $login ) {
		if (strlen( $login ) > 0) {
			if (! is_null( $user = $this->ci->users->get_user_by_login( $login ) )) {
				$data = array (
						'user_id' => $user->id,
						'username' => $user->username,
						'email' => $user->email,
						'new_pass_key' => md5( rand() . microtime() ) 
				);
				$this->ci->users->set_password_key( $user->id, $data [ 'new_pass_key' ] );
				return $data;
			} else {
				$this->error = array (
						'login' => 'auth_incorrect_email_or_username' 
				);
			}
		}
		return NULL;
	}

	/**
	 * Check if given password key is valid and user is authenticated.
	 *
	 * @param
	 *        	string
	 * @param
	 *        	string
	 * @return bool
	 */
	function can_reset_password( $user_id, $new_pass_key ) {
		if (( strlen( $user_id ) > 0 ) and ( strlen( $new_pass_key ) > 0 )) {
			return $this->ci->users->can_reset_password( $user_id, $new_pass_key, $this->ci->config->item( 'forgot_password_expire' ) );
		}
		return FALSE;
	}

	/**
	 * Replace user password (forgotten) with a new one (set by user)
	 * and return some data about it: user_id, username, new_password, email.
	 *
	 * @param
	 *        	string
	 * @param
	 *        	string
	 * @return bool
	 */
	function reset_password( $user_id, $new_pass_key, $new_password ) {
		if (( strlen( $user_id ) > 0 ) and ( strlen( $new_pass_key ) > 0 ) and ( strlen( $new_password ) > 0 )) {
			if (! is_null( $user = $this->ci->users->get_user_by_id( $user_id, TRUE ) )) {
				// Hash password using phpass
				$hasher = new PasswordHash( $this->ci->config->item( 'phpass_hash_strength' ), $this->ci->config->item( 'phpass_hash_portable' ) );
				$hashed_password = $hasher->HashPassword( $new_password );
				
				if ($this->ci->users->reset_password( $user_id, $hashed_password, $new_pass_key, $this->ci->config->item( 'forgot_password_expire' ) )) {
					// success
					// Clear all user's autologins
					$this->ci->load->model( 'tank_auth/user_autologin' );
					$this->ci->user_autologin->clear( $user->id );
					return array (
							'user_id' => $user_id,
							'username' => $user->username,
							'email' => $user->email,
							'new_password' => $new_password 
					);
				}
			}
		}
		return NULL;
	}

	/**
	 * Change user password (only when user is logged in)
	 *
	 * @param
	 *        	string
	 * @param
	 *        	string
	 * @return bool
	 */
	function change_password( $old_pass, $new_pass ) {
		$user_id = $this->ci->session->userdata( 'user_id' );
		if (! is_null( $user = $this->ci->users->get_user_by_id( $user_id, TRUE ) )) {
			// Check if old password correct
			$hasher = new PasswordHash( $this->ci->config->item( 'phpass_hash_strength' ), $this->ci->config->item( 'phpass_hash_portable' ) );
			if ($hasher->CheckPassword( $old_pass, $user->password )) { // success
			                                                            // Hash new password using phpass
				$hashed_password = $hasher->HashPassword( $new_pass );
				// Replace old password with new one
				$this->ci->users->change_password( $user_id, $hashed_password );
				return TRUE;
			} else { // fail
				$this->error = array (
						'old_password' => 'auth_incorrect_password' 
				);
			}
		}
		return FALSE;
	}

	/**
	 * Change user email (only when user is logged in) and return some data about user:
	 * user_id, username, new_email, new_email_key.
	 * The new email cannot be used for login or notification before it is activated.
	 *
	 * @param
	 *        	string
	 * @param
	 *        	string
	 * @return array
	 */
	function set_new_email( $new_email, $password ) {
		$user_id = $this->ci->session->userdata( 'user_id' );
		if (! is_null( $user = $this->ci->users->get_user_by_id( $user_id, TRUE ) )) {
			// Check if password correct
			$hasher = new PasswordHash( $this->ci->config->item( 'phpass_hash_strength' ), $this->ci->config->item( 'phpass_hash_portable' ) );
			if ($hasher->CheckPassword( $password, $user->password )) { // success
				$data = array (
						'user_id' => $user_id,
						'username' => $user->username,
						'new_email' => $new_email 
				);
				if ($user->email == $new_email) {
					$this->error = array (
							'email' => 'auth_current_email' 
					);
				} elseif ($user->new_email == $new_email) { // leave email key as is
					$data [ 'new_email_key' ] = $user->new_email_key;
					return $data;
				} elseif ($this->ci->users->is_email_available( $new_email )) {
					$data [ 'new_email_key' ] = md5( rand() . microtime() );
					$this->ci->users->set_new_email( $user_id, $new_email, $data [ 'new_email_key' ], TRUE );
					return $data;
				} else {
					$this->error = array (
							'email' => 'auth_email_in_use' 
					);
				}
			} else { // fail
				$this->error = array (
						'password' => 'auth_incorrect_password' 
				);
			}
		}
		return NULL;
	}

	/**
	 * Activate new email, if email activation key is valid.
	 *
	 * @param
	 *        	string
	 * @param
	 *        	string
	 * @return bool
	 */
	function activate_new_email( $user_id, $new_email_key ) {
		if (( strlen( $user_id ) > 0 ) and ( strlen( $new_email_key ) > 0 )) {
			return $this->ci->users->activate_new_email( $user_id, $new_email_key );
		}
		return FALSE;
	}

	/**
	 * Delete user from the site (only when user is logged in)
	 *
	 * @param
	 *        	string
	 * @return bool
	 */
	function delete_user( $password ) {
		$user_id = $this->ci->session->userdata( 'user_id' );
		if (! is_null( $user = $this->ci->users->get_user_by_id( $user_id, TRUE ) )) {
			// Check if password correct
			$hasher = new PasswordHash( $this->ci->config->item( 'phpass_hash_strength' ), $this->ci->config->item( 'phpass_hash_portable' ) );
			if ($hasher->CheckPassword( $password, $user->password )) { // success
				$this->ci->users->delete_user( $user_id );
				$this->logout();
				return TRUE;
			} else { // fail
				$this->error = array (
						'password' => 'auth_incorrect_password' 
				);
			}
		}
		return FALSE;
	}

	/**
	 * Get error message.
	 * Can be invoked after any failed operation such as login or register.
	 *
	 * @return string
	 */
	function get_error_message( ) {
		return $this->error;
	}

	/**
	 * Save data for user's autologin
	 *
	 * @param
	 *        	int
	 * @return bool
	 * @todo verificare, visto che forse non si usano più i cookie....
	 */
	private function create_autologin( $user_id ) {
		$this->ci->load->helper( 'cookie' );
		$key = substr( md5( uniqid( rand() . get_cookie( $this->ci->config->item( 'sess_cookie_name' ) ) ) ), 0, 16 );
		$this->ci->load->model( 'tank_auth/user_autologin' );
		$this->ci->user_autologin->purge( $user_id );
		if ($this->ci->user_autologin->set( $user_id, md5( $key ) )) {
			if ($this->session->sess_encrypt_cookie == TRUE) {
				$cookie_data = $this->encrypt->encode( serialize( array (
						'user_id' => $user_id,
						'key' => $key 
				) ) );
			}
			set_cookie( array (
					'name' => $this->ci->config->item( 'autologin_cookie_name' ),
					'value' => $cookie_data,
					'expire' => $this->ci->config->item( 'autologin_cookie_life' ) 
			) );
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Clear user's autologin data
	 *
	 * @return void
	 */
	private function delete_autologin( ) {
		if (! $this->ci->config->config [ "session_use_cookie" ]) {
			return;
		}
		$this->ci->load->helper( 'cookie' );
		$cookie = get_cookie( $this->ci->config->item( 'autologin_cookie_name' ), TRUE );
		if ($cookie != false) {
			$decoded = $this->encrypt->decode( $cookie );
			$data = unserialize( $decoded );
			$this->ci->load->model( 'tank_auth/user_autologin' );
			$this->ci->user_autologin->delete( $data [ 'user_id' ], md5( $data [ 'key' ] ) );
			delete_cookie( $this->ci->config->item( 'autologin_cookie_name' ) );
		}
	}

	/**
	 * Login user automatically if he/she provides correct autologin verification
	 *
	 * @return void
	 */
	private function autologin( ) {
		if (! $this->is_logged_in() and ! $this->is_logged_in( FALSE )) { // not logged in (as any user)
			$this->ci->load->helper( 'cookie' );
			$cookie = get_cookie( $this->ci->config->item( 'autologin_cookie_name' ), TRUE );
			if ($cookie != false) {
				$decoded = $this->encrypt->decode( $cookie );
				$data = unserialize( $decoded );
				if (isset( $data [ 'key' ] ) and isset( $data [ 'user_id' ] )) {
					$this->ci->load->model( 'tank_auth/user_autologin' );
					if (! is_null( $user = $this->ci->user_autologin->get( $data [ 'user_id' ], md5( $data [ 'key' ] ) ) )) {
						// Login user
						$this->ci->session->set_userdata( array (
								'user_id' => $user->id,
								'username' => $user->username,
								'status' => STATUS_ACTIVATED,
								'created' => $user->created 
						) );
						// Get profile
						$this->is_autologin = true;
						// Renew users cookie to prevent it from expiring
						set_cookie( array (
								'name' => $this->ci->config->item( 'autologin_cookie_name' ),
								'value' => $cookie,
								'expire' => $this->ci->config->item( 'autologin_cookie_life' ) 
						) );
						$this->ci->users->update_login_info( $user->id, $this->ci->config->item( 'login_record_ip' ), $this->ci->config->item( 'login_record_time' ) );
						return TRUE;
					}
				}
			}
		}
		return FALSE;
	}

	/**
	 * Check if login attempts exceeded max login attempts (specified in config)
	 *
	 * @param
	 *        	string
	 * @return bool
	 */
	function is_max_login_attempts_exceeded( $login ) {
		if ($this->config->item( 'login_count_attempts' )) {
			// $this->load->model( 'tank_auth/login_attempts' );
			// $this->build_dependency();
			return $this->login_attempts->get_attempts_num( $this->getClientIP(), $login ) >= $this->config->item( 'login_max_attempts' );
		}
		return FALSE;
	}

	/**
	 * Increase number of attempts for given IP-address and login
	 * (if attempts to login is being counted)
	 *
	 * @param
	 *        	string
	 * @return void
	 */
	private function increase_login_attempt( $login ) {
		if ($this->ci->config->item( 'login_count_attempts' )) {
			if (! $this->is_max_login_attempts_exceeded( $login )) {
				$this->ci->load->model( 'tank_auth/login_attempts' );
				$this->ci->login_attempts->increase_attempt( $this->ci->input->getClientIP(), $login );
			}
		}
	}

	/**
	 * Clear all attempt records for given IP-address and login
	 * (if attempts to login is being counted)
	 *
	 * @param
	 *        	string
	 * @return void
	 */
	private function clear_login_attempts( $login ) {
		if ($this->ci->config->item( 'login_count_attempts' )) {
			$this->ci->load->model( 'tank_auth/login_attempts' );
			$this->ci->login_attempts->clear_attempts( $this->ci->input->getClientIP(), $login, $this->ci->config->item( 'login_attempt_expire' ) );
		}
	}
}

/* End of file Tank_auth.php */
/* Location: ./application/libraries/Tank_auth.php */