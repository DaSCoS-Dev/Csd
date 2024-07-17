<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
class Api extends CI_Controller {
	protected $base_url, $profile, $key, $dati_in;

	public function __construct( ) {
		parent::__construct();
		$this->load->model( "tank_auth/users" );
		$this->config->load( "api" );
		$this->base_url = base_url();
	}

	public function test_api_auth( $method_name = "get_auth" ) {
		$postFields = [ 
				'code' => 'ZOcFt5oMrvqY',
				'user_id' => 1 
		];
		$_POST = $postFields;
		if (method_exists( $this, $method_name )) {
			return $this->$method_name();
		}
	}

	public function test_api_get( $method_name = "do_apiCall" ) {
		$postFields = [ 
				'code' => 'ZOcFt5oMrvqY',
				'key' => '1234567890abcdef1234567890abcdef12345678',
				'payload' => array (
						'class' => 'Articolo',
						'method' => 'get',
						'input' => array (
								'id' => 1 
						) 
				) 
		];
		$_POST = $postFields;
		if (method_exists( $this, $method_name )) {
			return $this->$method_name();
		}
	}

	public function get_auth( ) {
		$this->dati_in = $this->input->post();
		if (! $this->validate_auth_input()) {
			$this->error( "404.1" );
		}
		if (! $this->validate_user()) {
			$this->error( "404.0" );
		}
		$expired_key = $this->check_user_key_expired();
		if ($expired_key !== true) {
			$this->error( "302.0", $expired_key );
		}
		$this->key = $this->_generate_key();
		$scadenza = $this->_save_key();
		$this->general_success( $scadenza );
	}

	public function do_apiCall( ) {
		$this->dati_in = $this->input->post();
		if (! $this->validate_api_input()) {
			$this->error( "404.1" );
		}
		if (!$this->validate_key( xss_clean($this->dati_in [ 'key' ]), xss_clean($this->dati_in [ 'code' ] ))){
			$this->error("401.0");
		}
		if (! $this->validate_key_expiration( )) {
			$this->error( "408.0" );
		}
		if (!$this->validate_key_ip()){
			$this->error( "401.1" );
		}
		$library_name_U = ucfirst( $this->dati_in [ 'payload' ] [ 'class' ] );
		$library_name_L = strtolower( $this->dati_in [ 'payload' ] [ 'class' ] );
		$file_path = "{$_SERVER['DOCUMENT_ROOT']}/application/libraries/{$library_name_U}/Main_{$library_name_L}.php";
		if (!file_exists( $file_path )) {
			$this->error( "404.2",  $library_name_U);
		}
		try {
			$this->super_lib->load->library( "{$library_name_U}/Main_{$library_name_L}" );
		} catch ( Exception $e ) {
			$this->error( "404.3",$library_name_L );
		}
		$method = $this->dati_in [ 'payload' ] [ 'method' ];
		if (method_exists( $instance, $method )) {
			$params = $this->dati_in [ 'payload' ] [ 'input' ];
			call_user_func_array( [ 
					$instance,
					$method 
			], $params );
		} else {
			$this->error( "405.0", $method );
		}
	}

	private function validate_api_input( ) {
		return isset( $this->dati_in [ 'code' ] ) && isset( $this->dati_in [ 'key' ] ) && isset( $this->dati_in [ 'payload' ] [ 'class' ] ) && isset( $this->dati_in [ 'payload' ] [ 'method' ] ) && isset( $this->dati_in [ 'payload' ] [ 'input' ] ) && is_array( $this->dati_in [ 'payload' ] [ 'input' ] );
	}

	private function validate_auth_input( ) {
		return isset( $this->dati_in [ 'user_id' ] ) && isset( $this->dati_in [ 'code' ] ) && ! empty( trim( $this->dati_in [ 'user_id' ] ) ) && ! empty( trim( $this->dati_in [ 'code' ] ) );
	}

	private function validate_user( ) {
		$this->dati_in [ 'user_id' ] = intval( xss_clean( $this->dati_in [ 'user_id' ] ) );
		$this->dati_in [ 'code' ] = trim( xss_clean( $this->dati_in [ 'code' ] ) );
		$profile = $this->users->get_user_by_id( $this->dati_in [ 'user_id' ], true );
		if (! $profile || $profile->code !== $this->dati_in [ 'code' ]) {
			return false;
		}
		$this->profile = $profile;
		return true;
	}

	private function validate_key_expiration( ) {		
		return ( $this->key && $this->key->expiration_date > time() ) ? ( $this->key->expiration_date - time() ) : false;
	}

	private function validate_key_ip(){
		if ($this->key->ip_addresses !== $this->getClientIP()){
			return false;
		}
		return true;
	}
	
	private function validate_key($key, $code){
		$key_data = $this->_check_if_user_key_exists( $key, $code );
		$this->key = $key_data;
		if (!$key_data){
			return false;
		}
		return true;
	}
	
	private function check_user_key_expired( ) {
		$key_data = $this->_get_user_key( $this->profile->id );
		$this->key = $key_data;
		return ( $key_data && $key_data->expiration_date < time() ) ? true : ( $key_data->expiration_date - time() );
	}

	private function _generate_key( ) {
		do {
			$salt = bin2hex( $this->security->get_random_bytes( 64 ) );
			if ($salt === FALSE) {
				$salt = hash( 'sha256', time() . mt_rand() );
			}
			$new_key = substr( $salt, 0, config_item( 'rest_key_length' ) );
		} while ( $this->_check_if_key_exists( $new_key ) );
		$this->key = new stdClass();
		$this->key->key = $new_key;
		return $new_key;
	}

	private function _save_key( $aggiornamento = false ) {
		$scadenza = time() + ( 60 * config_item( 'rest_key_duration' ) ) + rand( 1, 20 );
		$data = [ 
				"user_id" => $this->dati_in [ "user_id" ],
				"code" => $this->dati_in [ "code" ],
				"ip_addresses" => $this->getClientIP(),
				"expiration_date" => $scadenza 
		];
		if ($aggiornamento) {
			$this->_update_key( $this->key->key, $data );
		} else {
			$this->_insert_key( $this->key->key, $data );
		}
		return $scadenza;
	}

	private function _check_if_key_exists( $key ) {
		return $this->db->where( config_item( 'rest_key_column' ), $key )->count_all_results( config_item( 'rest_keys_table' ) ) > 0;
	}

	private function _check_if_user_key_exists( $key, $code ) {
		return $this->db->where( config_item( 'rest_key_column' ), $key )->where( 'code', $code )->get( config_item( 'rest_keys_table' ) )->row();
	}

	private function _insert_key( $key, $data ) {
		$data [ config_item( 'rest_key_column' ) ] = $key;
		return $this->db->insert( config_item( 'rest_keys_table' ), $data );
	}

	private function _update_key( $key, $data ) {
		return $this->db->where( config_item( 'rest_key_column' ), $key )->update( config_item( 'rest_keys_table' ), $data );
	}

	private function _get_user_key( $user_id ) {
		return $this->db->where( "user_id", $user_id )->order_by( "id", "DESC" )->get( config_item( 'rest_keys_table' ) )->row();
	}

	private function error( $err_code, $extra = "", $direct_exit = 1 ) {
		$coded_errors = config_item( "api_error_codes" );
		if (! sizeof( $coded_errors )) {
			return $this->general_error( "000.0", "Unknown error. Sorry, I'm unable to find a correct error code :-(", $direct_exit );
		}
		if (! isset( $coded_errors [ $err_code ] )) {
			return $this->general_error( "000.0", $coded_errors [ "000.0" ], $direct_exit );
		}
		$string = $coded_errors [ $err_code ];
		$string = str_replace( "%extra%", $extra, $string );
		return $this->general_error( $err_code, $string, $direct_exit );
	}

	private function general_error( $codice, $stringa, $direct_exit = 1 ) {
		$return = new stdClass();
		$return->code = "{$codice}";
		$return->error = "{$stringa}";
		if ($direct_exit) {
			exit( json_encode( $return ) );
		} else {
			return json_encode( $return );
		}
	}

	private function general_success( $scadenza ) {
		$return = new stdClass();
		$return->code = "200";
		$return->result = new stdClass();
		$return->result->key = $this->key->key;
		$return->result->request_date = time();
		$return->result->expiration_date = $scadenza;
		exit( json_encode( $return ) );
	}
}
?>