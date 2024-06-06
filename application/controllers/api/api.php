<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
class api extends CI_Controller {
	protected $base_url, $profile, $key, $dati_in;

	public function __construct( ) {
		parent::__construct();
		$ci = get_instance();
		$this->caller = get_class( $ci );
		// Carico i model
		$this->load->model( "tank_auth/users" );
		// Configurazione api
		$this->config->load( "api" );
		$this->base_url = base_url();
	}

	/*
	 * ENTRY POINTS, public functions
	 */
	/**
	 * ENTRY POINT 1
	 * Get a new key.
	 * Inputs via POST data:
	 * user_id = integer/string->integer
	 * secret = string (the user folder)
	 * returns a json
	 */
	public function get_auth( ) {
		$this->dati_in = $this->input->post();
		// Check coerenza dati ingresso...uscita diretta in caso di fallimento
		$this->check_dati_auth_validi();
		// Verifico l'utente in base a id in arrivo e verifica tra secret in arrivo e cartella utente....uscita diretta in caso di fallimento
		$this->check_utente_esistente();
		// Verifico che non esista già una chiave valida...uscita diretta in caso di fallimento
		$this->check_chiave_esistente();
		// Creo la chiave
		$this->_generate_key();
		$scadenza = $this->salva_chiave();
		$this->general_success( $scadenza );
	}

	/**
	 * ENTRY POINT 2
	 * Do short a url.
	 * Inputs via POST data:
	 * key = string
	 * url = string
	 * Returns a json
	 */
	public function do_short( ) {
		$this->dati_in = $this->input->post();
		// Verifico i dati in ingresso
		$this->check_dati_short_validi();
		// Cerco la chiave in arrivo
		$this->key = $this->_get_key();
		// Verifico sia valida/esistente ecc
		$this->check_chiave_valida();
		// decode dell'input
		$this->urls = json_decode( $this->dati_in [ "url" ] );
		// Verifico che l'utente esista
		$this->check_utente_permessi();
		// Verifico che la url sia formalmente valida
		$this->check_url();
		// Ok, tutti i controlli passati. Aggiorno la data di scadenza della chiave
		$this->dati_in [ "user_id" ] = $this->profilo->user_id;
		$this->salva_chiave( true );
		// Già accorciata? Nel caso devo solo mostrare la url e aggiornare il conteggio
		$this->check_url_esistente();
		// Ok, devo salvare
		$this->do_short_url();
	}

	/*
	 * Utility functions, internal calls
	 * Checks
	 */

	/**
	 * Check if a given key is valid, not expired and from the same ip that generated the key
	 * On error exit with a coded json
	 */
	private function check_chiave_valida( ) {
		// Esiste la chiave, in senso assoluto?
		if (! sizeof( $this->key )) {
			// Invalid key
			$this->error( "401.0" );
		}
		// Verifico la data di scadenza
		if ($this->key->expiration_date <= time()) {
			// Request too old
			$this->error( "408.0" );
		}
		// Verifico che la richiesta arrivi dallo stesso ip che ha generato la chiave
		if ($this->key->ip_addresses !== $this->getClientIP()) {
			// Request from different ip
			$this->error( "401.1" );
		}
	}

	/**
	 * Check if the user (for the given POST key) exists and has enought links to be shortened
	 * On error returns a coded json
	 */
	private function check_utente_permessi( ) {
		$this->profilo = $this->users->get_profile_by_userid( $this->key->user_id );
		if (! sizeof( $this->profilo )) {
			// Not found
			$this->error( "404.0" );
		}
		if ($this->profilo->conteggio >= 0 or sizeof( $this->urls ) > abs( $this->profilo->conteggio )) {
			// Payment required
			$this->error( "402.0" );
		}
		if (sizeof( $this->urls ) > config_item( 'rest_max_requests' )) {
			// Too many Urls
			$this->error( "402.1", config_item( 'rest_max_requests' ) );
		}
		$this->profilo->Opzioni_Codificate = json_decode( $this->profilo->options );
	}

	/**
	 * Check if the user who requests a new key exists and if the secret equals the user_id secrets
	 * On error returns a coded json
	 */
	private function check_utente_esistente( ) {
		$this->dati_in [ "user_id" ] = intval( xss_clean( $this->dati_in [ "user_id" ] ) );
		$this->dati_in [ "secret" ] = trim( xss_clean( $this->dati_in [ "secret" ] ) );
		// Vado a cercare un user_id che abbia quella secret
		$profilo = $this->users->get_profile_by_userid( $this->dati_in [ "user_id" ] );
		$options = json_decode( $profilo->options );
		if (! sizeof( $profilo ) or $options->Cartella_Utente !== $this->dati_in [ "secret" ] or $profilo->conteggio >= 0) {
			if ($profilo->conteggio >= 0) {
				// Payment required
				$this->error( "402.0" );
			} else {
				// Not found
				$this->error( "404.0" );
			}
		}
	}

	/**
	 * Checks if we are trying to generate a key for a user BUT we have a key that is still valid
	 * On error returns a coded json
	 */
	private function check_chiave_esistente( ) {
		$dati = $this->_get_user_key( $this->dati_in [ "user_id" ] );
		if ($dati->expiration_date > time()) {
			$diff = $dati->expiration_date - time();
			// You have a key not expired
			$this->error( "302.0", $diff );
		}
	}

	/**
	 * Check if we have correct input
	 * On error returns a coded json
	 */
	private function check_dati_auth_validi( ) {
		// Senza dati, restituisco un json con "che cacchio fai? Ti manca il POST"...
		if (! isset( $this->dati_in [ "user_id" ] ) or ! isset( $this->dati_in [ "secret" ] ) or trim( $this->dati_in [ "user_id" ] ) == "" or trim( $this->dati_in [ "secret" ] ) == "") {
			// Invalid input
			$this->error( "404.1" );
		}
	}

	/**
	 * Checks if we have the correct input for the short utility
	 * On error returns a coded json
	 */
	private function check_dati_short_validi( ) {
		// Senza dati, restituisco un json con "che cacchio fai? Ti manca il POST"...
		if (! isset( $this->dati_in [ "key" ] ) or ! isset( $this->dati_in [ "url" ] ) or trim( $this->dati_in [ "key" ] ) == "" or trim( $this->dati_in [ "url" ] ) == "") {
			// Invalid input
			$this->error( "404.1" );
		}
	}

	/*
	 * Utility functions, internal calls
	 * Create the auth-keys, shorts the urls
	 */
	/**
	 * Generate the unique auth key
	 *
	 * @return string
	 */
	private function _generate_key( ) {
		do {
			// Generate a random salt
			$salt = bin2hex( $this->security->get_random_bytes( 64 ) );
			// If an error occurred, then fall back to the previous method
			if ($salt === FALSE) {
				$salt = hash( 'sha256', time() . mt_rand() );
			}
			$new_key = substr( $salt, 0, config_item( 'rest_key_length' ) );
		} while ( $this->_check_if_key_exists( $new_key ) );
		$this->key->key = $new_key;
		return $new_key;
	}

	/*
	 * Query functions
	 */
	/**
	 * Save a private key for the authenticated user ($aggiornamento == false)
	 * or update the specific key ($aggiornamento == true)
	 *
	 * @param boolean $aggiornamento        	
	 * @return integer (the expiration date time in Unix time stamp)
	 */
	private function salva_chiave( $aggiornamento = false ) {
		// Adesso + 5 minuti + random tra 1 e 20
		$scadenza = time() + ( 60 * config_item( 'rest_key_duration' ) ) + rand( 1, 20 );
		// Creo l'array da salvare
		$data = array ();
		$data [ "user_id" ] = $this->dati_in [ "user_id" ];
		$data [ "ip_addresses" ] = $this->getClientIP();
		$data [ "expiration_date" ] = $scadenza;
		if ($aggiornamento) {
			$this->_update_key( $this->key->key, $data );
		} else {
			$this->_insert_key( $this->key->key, $data );
		}
		return $scadenza;
	}

	/**
	 * Update the short link available for a user
	 */
	private function update_conteggio( $how_many = 1 ) {
		$this->users->update_conteggio( $this->profilo->user_id, $this->profilo->conteggio + $how_many );
	}

	/**
	 * Return true if a key already exists
	 *
	 * @param string $key        	
	 * @return boolean
	 */
	private function _check_if_key_exists( $key ) {
		return $this->db->where( config_item( 'rest_key_column' ), $key )->count_all_results( config_item( 'rest_keys_table' ) ) > 0;
	}

	/**
	 * Insert a new key in the db
	 *
	 * @param string $key        	
	 * @param array $data        	
	 * @return boolean
	 */
	private function _insert_key( $key, $data ) {
		$data [ config_item( 'rest_key_column' ) ] = $key;
		return $this->db->set( $data )->insert( config_item( 'rest_keys_table' ) );
	}

	/**
	 * Update a key (generally the expiration date)
	 *
	 * @param string $key        	
	 * @param array $data        	
	 * @return boolean
	 */
	private function _update_key( $key, $data ) {
		return $this->db->where( config_item( 'rest_key_column' ), $key )->update( config_item( 'rest_keys_table' ), $data );
	}

	/**
	 * Gets the last generated key for the user asking a new key
	 *
	 * @param integer $user_id        	
	 * @return stdClass
	 */
	private function _get_user_key( $user_id ) {
		return $this->db->where( "user_id", $user_id )->order_by( "id", "DESC" )->get( config_item( 'rest_keys_table' ) )->row();
	}

	/**
	 * Get the record for the key
	 *
	 * @return stdClass
	 */
	private function _get_key( ) {
		return $this->db->where( config_item( 'rest_key_column' ), $this->dati_in [ "key" ] )->get( config_item( 'rest_keys_table' ) )->row();
	}

	/*
	 * END - Final steps for every error/request
	 * EXIT points
	 */
	/**
	 * Display a json coded general error message
	 *
	 * @param string $codice        	
	 * @param string $stringa        	
	 */
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

	private function error( $err_code, $extra = "", $direct_exit = 1 ) {
		$coded_errors = config_item( "api_error_codes" );
		if (! sizeof( $coded_errors )) {
			return $this->general_error( "000.0", "Unknow error. Sorry, I'm unable to find a correct error code :-(", $direct_exit );
		}
		if (! isset( $coded_errors [ $err_code ] )) {
			return $this->general_error( "000.0", $coded_errors [ "000.0" ], $direct_exit );
		}
		$string = $coded_errors [ $err_code ];
		$string = str_replace( "%extra%", $extra, $string );
		return $this->general_error( $err_code, $string, $direct_exit );
	}

	/**
	 * Display a json coded general success message for a key requests
	 *
	 * @param integer $scadenza        	
	 */
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