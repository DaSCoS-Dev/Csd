<?php
class Super_lib {
	public $response = null;
	public $initialized = false;
	public $id_utente = null;
	public $dati_base = null;
	public $referer = null;
	public $input_vars = array ();
	public $logged_in = false;
	public $base_url;
	public $cartella_utente;
	protected $profilo_utente;

	public function __construct( $params = null ) {
		setlocale( LC_TIME, "it_IT.utf8" );
		$ci = get_instance();
		$caller = get_class( $ci );
		require_once ( "{$_SERVER['DOCUMENT_ROOT']}/application/classes/main_object.php" );
		if ($ci->config->config [ "session_use_cookie" ] and ( ! isset( $ci->session ) or ! is_a( $ci->session, "CI_Session" ) )) {
			$ci->load->library( "Session" );
		}
		if (! isset( $ci->Super_model ) or $ci->Super_model == null) {
			$ci->load->model_options( "Super_model", $params );
		}
		if (! isset( $ci->view_assembler ) or $ci->view_assembler == null) {
			$ci->load->library( "Views_assembler/View_assembler" );
		}
		if ($this->initialized()) {
			// Per sicurezza faccio solo il rebuild
			return $this->build_dependency();
		}
		if ($params != null) {
			foreach ( $params as $var => $value ) {
				$this->$var = $value;
			}
		}
		$this->build_dependency();
		$this->base_url = base_url();
		$name = get_class( $this );
		if (isset( $this->profilo_utente->ID )) {
			$this->id_utente = $this->profilo_utente->ID;
		}
		if ($name === "Super_lib") {
			$ci->super_lib = $this;
			$ci->view_assembler->super_lib = $this;
			$this->get_local_storage();
			$this->start_xajax();
			// Verifico login
			$this->check_logged_in();
		}
		$this->set_initialized();
		if ($name === "Super_lib") {
			$ci->super_lib = $this;
			$ci->view_assembler->super_lib = $this;
		}
	}

	public function load_class($class_name) {
		$subdir = "";
		if (stripos ( $class_name, "/" ) !== false) {
			$parts = explode ( "/", $class_name );
			if (sizeof ( $parts ) > 2) {
				$class_name = array_pop($parts);
				$subdir = implode("/", $parts) . "/";
			} else {
				$subdir = "{$parts[0]}/";
				$class_name = $parts [1];
			}
		}
		require_once (BASE_APPLICATION_PATH . "/classes/{$subdir}{$class_name}.php");
		return new $class_name (  );
	}

	protected function set_local_storage( $id = 0 ) {
		if (! isset( $this->profilo_utente->Codice )) {
			$this->check_unique_user_code();
			$this->profilo_utente->ID = 0;
		} elseif (intval( $id ) > 0) {
			$this->profilo_utente->ID = intval( $id );
		}
		if (intval( $id ) == 0 and $this->is_logged_in()) {
			return;
		}
		$profiloCriptato = cript_high_security( json_encode( $this->profilo_utente ) );
		if (is_a( $this->response, "xajaxResponse" )) {
			$this->response->script( "localStorage.setItem('CUP', '{$profiloCriptato}');" );
		} else {
			$this->set_local_storage_js();
		}
	}

	protected function check_cartella_utente( $cartella = "Guest" ) {
		if (isset( $this->profilo_utente->Opzioni_Codificate->Cartella_Utente ) and $cartella == "Guest") {
			if (! is_dir( "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->config ["cartella_utenti"]}/{$this->profilo_utente->Opzioni_Codificate->Cartella_Utente}" )) {
				mkdir( "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->config ["cartella_utenti"]}/{$this->profilo_utente->Opzioni_Codificate->Cartella_Utente}", 0755, true );
			}
		} else {
			if (! is_dir( "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->config ["cartella_utenti"]}/{$cartella}" )) {
				mkdir( "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->config ["cartella_utenti"]}/{$cartella}", 0755, true );
			}
		}
	}

	protected function get_cartella_utente( $cartella = "Guest" ) {
		if (isset( $this->profilo_utente->Opzioni_Codificate->Cartella_Utente ) and $cartella == "Guest") {
			$this->cartella_utente = "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->config ["cartella_utenti"]}/{$this->profilo_utente->Opzioni_Codificate->Cartella_Utente}";
		} else {
			$this->cartella_utente = "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->config ["cartella_utenti"]}/{$cartella}";
		}
		return $this->cartella_utente;
	}

	public function get_user_profile( ) {
		return $this->profilo_utente;
	}

	protected function enable_tool_tips( ) {
		$this->response->script( "setTimeout( enable_tool_tips(), 500);" );
	}
	
	protected function enable_popover( ) {
		$this->response->script( "setTimeout( enable_popover(), 500);" );
	}

	protected function check_unique_user_code( ) {
		$this->profilo_utente->Codice = random_string( 'allnum', 12 );
		// Cerco che non esista già come utente o come "short"
		$esiste = $this->Super_model->get_unique_user_code( $this->profilo_utente->Codice );
		if (isset( $esiste->ID )) {
			// unset($this->profilo_utente->Codice);
			return $this->check_unique_user_code();
		}
	}

	protected function rscandir( $dir ) {
		$dirs = array_fill_keys( array_diff( scandir( $dir ), array (
				'.',
				'..' 
		) ), array () );
		foreach ( $dirs as $d => $v ) {
			if (is_dir( $dir . "/" . $d )) {
				$dirs [ $d ] = $this->rscandir( $dir . "/" . $d );
			} else {
				unset( $dirs [ $d ] );
			}
		}
		return $dirs;
	}

	protected function set_local_storage_js( $echo = true ) {
		if ($echo === true) {
			$profiloCriptato = cript_high_security( json_encode( $this->profilo_utente ) );
			echo "<script>localStorage.setItem('CUP', '{$profiloCriptato}');</script>";
		} else {
			$profiloCriptato = cript_high_security( json_encode( $this->tank_auth->profilo_utente ) );
			return "<script>localStorage.setItem('CUP', '{$profiloCriptato}');</script>";
		}
	}

	public function get_local_storage( ) {
		if (isset( $this->mainxajax->ajax->objArgumentManager->aArgs [ "CUP" ] ) and trim( $this->mainxajax->ajax->objArgumentManager->aArgs [ "ls_profiloUtente" ] ) != "*") {
			$this->profilo_utente = json_decode( decript_high_security( $this->mainxajax->ajax->objArgumentManager->aArgs [ "CUP" ] ) );
			return $this->profilo_utente;
		} elseif (isset( $this->mainxajax->xajax->objArgumentManager->aArgs [ "CUP" ] ) and trim( $this->mainxajax->xajax->objArgumentManager->aArgs [ "ls_profiloUtente" ] ) != "*") {
			$this->profilo_utente = json_decode( decript_high_security( $this->mainxajax->xajax->objArgumentManager->aArgs [ "CUP" ] ) );
			return $this->profilo_utente;
		} elseif (isset( $this->session->userdata [ "profilo_utente" ] )) {
			$this->profilo_utente = $this->session->userdata [ "profilo_utente" ];
			return $this->profilo_utente;
		} elseif (isset($this->profilo_utente->Codice)) {
			return $this->profilo_utente;
		}
		$this->profilo_utente = new stdClass();
		return $this->profilo_utente;
	}
	
	// Overridable
	public function do_exec( $what, $with1 = "", $with2 = "", $with3 = "", $with4 = "", $with5 = "" ) {
		return $this->$what( $with1, $with2, $with3, $with4, $with5 );
	}

	/**
	 * Funzione di INGRESSO per ogni singola chiamata xajax.
	 * Da qui entro nelle varie librerie per eseguire il metodo richiesto del tipo
	 * xajax_execute('Fatture/Main_fatture', 'edit_fattura', 0)
	 *
	 * Restituisce i comandi xajax per manipolare gli elementi del browser, quindi è anche il punto di uscita "ultimo"
	 *
	 * @return xajaxResponse
	 */
	final public function execute( ) {
		$args = func_get_args() [ 0 ];
		// Local storage
		$pu = $args [ "CUP" ];
		if (! sizeof( $pu ) and ! isset( $this->profilo_utente->ID )) {
			// Non mi arriva un profilo utente, quindi per ora lo considero NUOVO utente anonimo
			$this->profilo_utente = new stdClass();
			$this->check_unique_user_code();
			$this->profilo_utente->ID = 0;
			$pu = $this->profilo_utente;
		} elseif (sizeof( $pu ) and ! isset( $this->profilo_utente )) {
			$this->profilo_utente = json_decode( decript_high_security( $args [ "CUP" ] ) );
			if ($this->session->session_updated == true) {
				$this->profilo_utente->session_id = $this->session->userdata [ "session_id" ];
			}
		} elseif ($this->session->session_updated == true and isset( $this->profilo_utente )) {
			$this->profilo_utente->session_id = $this->session->userdata [ "session_id" ];
		}
		unset( $args [ "CUP" ] );
		// Proseguo
		$lib = $args [ 0 ];
		$method = $args [ 1 ];
		// unset dell'array
		unset( $args [ 0 ] );
		unset( $args [ 1 ] );
		// Splitto la dir e la lib
		$sections = explode( "/", $lib );
		// Se la dimensione e' 1, allora non e' una lib specifica
		if (sizeof( $sections ) >= 2) {
			$to_check = $sections [ sizeof( $sections ) - 1 ];
		} else {
			$to_check = $sections [ 0 ];
		}
		// Carico il gestore per le viste
		$this->load_view_manager( $to_check, $sections );
		$this->check_xajax();
		// Assegno l'azione al mainxajax
		$this->mainxajax->action = $method;
		$this->mainxajax->library = $lib;
		if (is_a( $this->session, "CI_Session" )) {
			$this->id_utente = $this->session->userdata [ "user_id" ];
			if (intval( $this->id_utente ) > 0) {
				$this->logged_in = true;
				$this->get_cartella_utente();
			}
		}
		// Ora carico la libreria di gestione della sezione specifica
		$my_instance = strtolower( $to_check );
		if (! isset( $this->$my_instance ) and ! is_a( $this, $to_check )) {
			if ($GLOBALS [ "lib_caricate" ] [ $my_instance ] != true) {
				$GLOBALS [ "lib_caricate" ] [ $my_instance ] = true;
			}
			$this->load->library( $lib, get_object_vars( $this ) );
			// Rebuild delle istanze!
			$this->build_dependency();
			// Converto in minuscolo ;)
			$lib = strtolower( $to_check );
			// Rebuild delle istanze SULLA LIB!
			if (isset( $this->$lib )) {
				$this->$lib->build_dependency( $this );
			} else {
				$this->build_dependency( $this );
			}
		} elseif (is_a( $this, $to_check )) {
			$lib = $to_check;
			$this->$lib = $this;
		}
		$this->$lib->profilo_utente = $this->profilo_utente;
		// se metodo_attuale != edit_profilo_cliente E != save_cliente, resetto la unset_impersona_utente
		if (is_a( $this->session, "CI_Session" ) and $this->ci->config->config [ "session_use_cookie" ]) {
			// swappo la provenienza
			$precedente = $this->session->get_userdata( "lib_attuale" );
			$metodo_precedente = $this->session->get_userdata( "metodo_attuale" );
			$this->session->set_userdata( "lib_precedente", $this->session->get_userdata( "lib_attuale" ) );
			$this->session->set_userdata( "lib_attuale", $lib );
			$this->session->set_userdata( "metodo_attuale", $method );
		} else {
			$precedente = $this->profilo_utente->navigation->lib_attuale;
			$metodo_precedente = $this->profilo_utente->navigation->metodo_attuale;
			$this->profilo_utente->navigation->lib_precedente = $precedente;
			$this->profilo_utente->navigation->metodo_precedente = $metodo_precedente;
			$this->profilo_utente->navigation->lib_attuale = $lib;
			$this->profilo_utente->navigation->metodo_attuale = $method;
		}
		// Se ho errori password, ti "blocco" fino alla scadenza del cookie
		if (isset( $this->profilo_utente->Errori_password ) and $this->profilo_utente->Errori_password >= 5) {
			// Check sui cookie
			if (time() < $this->profilo_utente->Scadenza_Errori) {
				$time_left = $this->profilo_utente->Scadenza_Errori - time();
				return $this->error( " You have been banned for {$time_left} seconds due to repeated password errors or other violations", "", 10000 );
			} else {
				unset( $this->profilo_utente->Errori_password );
				unset( $this->profilo_utente->Scadenza_Errori );
			}
		}
		call_user_func_array( array (
				$this->$lib,
				$method 
		), $args );
		if ($this->ci->config->config [ "session_use_cookie" ]) {
			$this->session->userdata [ "last_activity" ] = time();
			$this->session->sess_write( true );
		} else {
			$this->profilo_utente->navigation->last_activity = time();
		}
		$this->set_local_storage( $this->profilo_utente->ID );
		// Per test, attualmente
		return $this->response;
	}

	protected function tentativo_hacking( ) {
		$this->profilo_utente->Errori_password += 1;
		$this->profilo_utente->Scadenza_Errori = time() + $this->config->config [ "sess_banned_time" ];
	}

	private function check_xajax( ) {
		if (! is_a( $this->response, "xajaxResponse" )) {
			if (! isset( $this->mainxajax ) or ! is_a( $this->mainxajax, "mainXajax" )) {
				$this->load->library( "xajax/mainXajax" );
			}
			$this->response = new xajaxResponse();
		}
	}

	private function load_view_manager( $to_check, $sections ) {
		// Recuperati i nomi corretti, carico il gestore delle viste
		$view_assembler = "{$to_check}_views";
		// Sezione vista
		$sezioni = explode( "_", $to_check );
		// Se le sezioni sono > 2, ho qualcosa tipo "main_prima_nota"...
		if (sizeof( $sezioni ) > 2) {
			$sezione = ucfirst( $sezioni [ 1 ] ) . "_" . array_pop( $sezioni );
		} else {
			$sezione = ucfirst( array_pop( $sezioni ) );
		}
		// Ok, carico la libreria opportuna, se esiste!
		if (file_exists( "{$GLOBALS["_SERVER"]["DOCUMENT_ROOT"]}/application/libraries/Views_assembler/{$sezione}/{$view_assembler}.php" )) {
			$this->load->library( "Views_assembler/{$sezione}/{$view_assembler}" );
		} elseif (file_exists( "{$GLOBALS["_SERVER"]["DOCUMENT_ROOT"]}/application/libraries/Views_assembler/{$sections[0]}/{$view_assembler}.php" )) {
			$this->load->library( "Views_assembler/{$sections[0]}/{$view_assembler}" );
		}
	}

	final public function check_logged_in( ) {
		// Controllo...
		$ci = get_instance();
		if ($ci->config->config [ "session_use_cookie" ] and ( ! isset( $this->session ) or ! is_a( $this->session, "CI_Session" ) )) {
			if (isset( $ci->session )) {
				$this->session = $ci->session;
			} else {
				$this->session = new stdClass();
			}
		}
		if (isset( $this->session->userdata [ "user_id" ] ) and intval( $this->session->userdata [ "user_id" ] ) !== 0 and intval( $this->session->userdata [ "status" ] ) == 1) {
			$this->logged_in = true;
		} elseif (isset( $this->profilo_utente->ID ) and intval( $this->profilo_utente->ID ) > 0) {
			$this->logged_in = true;
		} else {
			$this->logged_in = false;
		}
		return $this->logged_in;
	}

	final public function is_admin( ) {
		if ($this->id_utente == null) {
			return intval( $this->profilo_utente->ID ) != 0 and intval( $this->profilo_utente->ID ) <= 3;
		}
		return intval( $this->id_utente ) <= 3 and intval( $this->id_utente ) > 0;
	}

	final protected function getClientIP( ) {
		if (array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER )) {
			return $_SERVER [ "HTTP_X_FORWARDED_FOR" ];
		} else if (array_key_exists( 'REMOTE_ADDR', $_SERVER )) {
			return $_SERVER [ "REMOTE_ADDR" ];
		} else if (array_key_exists( 'HTTP_CLIENT_IP', $_SERVER )) {
			return $_SERVER [ "HTTP_CLIENT_IP" ];
		}
		return '';
	}

	/**
	 * popup di alert con link al pagamento!
	 */
	final protected function no_auth( $text = "" ) {
		if (trim( $text ) == "") {
			$text = "Non hai i permessi sufficienti per l&apos;operazione richiesta";
		}
		$text .= "<br>Per poter utilizzare appieno FattureWeb devi fare l&apos;upgrade del tuo profilo, sezione \"Abbonamento\"";
		return $this->response->script( "alert('{$text}');" );
	}

	final protected function error( $text = "", $title = "", $time_out = 5000 ) {
		return $this->response->script( "show_layer = true; alert('{$text}', '{$title}', {$time_out});" );
	}

	final protected function message( $text = "", $title = "Info", $timeout = 5000 ) {
		return $this->response->script( "show_layer = true; popup('{$title}', '{$text}', 50, {$timeout})" );
	}
	
	final protected function confirm_action($title = "", $text = "", $function = ""){
		return $this->response->script("modal_confirm('{$title}', '{$text}', $function);");
	}

	protected function converti_campi_data( &$elementi, $complete = false, $custom = "" ) {
		foreach ( $elementi as $idx => $elemento ) {
			foreach ( $elemento as $campo => $valore ) {
				if (stripos( $campo, "data" ) !== false) {
					if (! $complete) {
						$date_format = "DATE_EURO_SHORT";
					} else {
						$date_format = "DATE_EURO_LONG";
					}
					// Custom?
					if (trim( $custom ) !== "") {
						$date_format = $custom;
					}
					// Se la data (trasformata in intero) non e' zero, e' una data effettiva
					if (intval( $valore ) !== 0 and is_numeric( $valore )) {
						$elementi [ $idx ]->$campo = standard_date( $date_format, $valore );
						$cl = "{$campo}_Long";
						$elementi [ $idx ]->$cl = standard_date( "DATE_EURO_LONG", $valore );
					} elseif (stripos( $campo, "_Long" ) === false) {
						$elementi [ $idx ]->$campo = "";
					}
					$campi_data [ ] = $campo;
				}
			}
			$elementi [ $idx ]->campi_data = $campi_data;
		}
	}

	final public function get_secret( ) {
		return $this->config->config [ "codice_segreto_operazioni_remote" ];
	}

	final protected function get_id_profilo_utente( ) {
		if(isset($this->session->userdata [ "profile" ])){
			return intval( $this->session->userdata [ "profile" ]->anagrafica->ID );
		} else {
			return intval($this->profilo_utente->anagrafica->ID);
		}
	}

	final protected function get_id_utente( ) {
		return intval( $this->id_utente );
	}

	final protected function start_xajax( ) {
		// Se arrivo da chiamata xajax, carico la libreria di supporto che esegue le varie
		// operazioni
		if (isset( $this->input_vars [ "xjxfun" ] )) {
			$this->initialize_xajax( false );
			$this->response = new xajaxResponse();
		}
		// A questo punto provo a verificare l'esistenza di "pu" nell'input. SE c'è, bypasso un sacco di query e funzioni
		if (isset( $this->mainxajax->xajax->objArgumentManager->aArgs [ "CUP" ] )) {
			$this->profilo_utente = json_decode( decript_high_security( $this->mainxajax->xajax->objArgumentManager->aArgs [ "CUP" ] ) );
		}
		// Xajax!!!!
		$this->initialize_xajax( true );
	}

	final public function build_dependency( $ref = null ) {
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

	final public function initialize_xajax( $with_elaborations = true ) {
		// Xajax!!!!!
		// Ocio!! Il caricamento della lib e' stato esguito...ma (ovviamente?)
		// la lib e' finita nel $this->ci...
		if (! isset( $this->mainxajax ) or ! is_a( $this->mainxajax, "mainXajax" )) {
			$this->load->library( "xajax/mainXajax" );
			$this->build_dependency();
		}
		if (isset( $this->mainxajax->xajax->objArgumentManager->aArgs [ "CUP" ] )) {
			$this->profilo_utente = json_decode( decript_high_security( $this->mainxajax->xajax->objArgumentManager->aArgs [ "CUP" ] ) );
		}
		if ($with_elaborations) {
			// Carico le funzioni ajax
			$this->load_xajax();
			// E le eseguo
			$this->data [ "xajax" ] = $this->execute_xjax();
		}
	}

	/**
	 * Funzione di registrazione xajax.
	 * Dichiariamo le funzioni disponibili/chiamabili tramite onclick, onload ecc
	 * delle varie view e registriamo queste funzioni, cosi' che il sistema possa
	 * intercettare le chiamate xajax
	 */
	public function load_xajax( ) {
		// Registro la funzione principale ajax
		$this->reg [ ] = array (
				$this,
				"execute" 
		);
		// Registro le funzioni
		$this->register_xajax( $this->reg, true );
	}

	/**
	 * Funzione che esegue materialmente le chiamate ai metodi dichiarati
	 * in loadAjax (se necessario farle...lo decide il "process", che e' un "mini wrapper"
	 * del metodo relativo della classe xajax).
	 * Se non deve eseguire nulla, procede col printJavascript, che come riportato nel
	 * commento, torna un HTML.
	 *
	 * @return string
	 */
	final public function execute_xjax( ) {
		$this->mainxajax->process();
		// Il Js per xajax torna come html!!
		return $this->mainxajax->printJavascript( true );
	}

	/**
	 * Funzione che effettua la registrazione dei metodi DI QUESTA CLASSSE
	 * da chiamare sui vari "onclick", "onchange" eccetera, presenti
	 * nella grafica.
	 */
	final protected function register_xajax( $reg, $multi = false ) {
		if ($multi) {
			foreach ( $reg as $register ) {
				$this->mainxajax->registerFunction( $register );
			}
		} else {
			$this->mainxajax->registerFunction( $reg );
		}
	}

	final public function is_live( ) {
		return ( $this->ci->config->config [ "is_live" ] );
	}

	public function is_logged_in( ) {
		if (get_class( $this ) == "Super_lib") {
			return $this->logged_in;
		} elseif (isset( $this->super_lib ) and get_class( $this->super_lib ) == "Super_lib") {
			return $this->super_lib->check_logged_in();
		} elseif ($this->logged_in !== null) {
			return $this->id_utente > 0;
		} else {
			return $this->check_logged_in();
		}
	}

	protected function get_web_address( ) {
		return $this->config->config [ "base_url" ];
	}
	
	protected function get_site_name( ) {
		return $this->config->config [ "site_name" ];
	}

	final protected function in_same_function( ) {
		return $this->was_in() == $this->am_in();
	}

	final protected function was_in( ) {
		if (isset($this->session->userdata [ "lib_precedente" ])){
			$pos = $this->session->userdata [ "lib_precedente" ];
		} else {
			$pos = $this->profilo_utente->navigation->lib_precedente;
		}
		return $pos;
	}

	final protected function am_in( ) {
		if (isset($this->session->userdata [ "lib_attuale" ])){
			$pos = $this->session->userdata [ "lib_attuale" ];
		} else {
			$pos = $this->profilo_utente->navigation->lib_attuale;
		}
		return $pos;
	}

	final protected function current_function( ) {
		if (isset($this->session->userdata [ "posizione" ])){
			$pos = $this->session->userdata [ "posizione" ];
		} else {
			$pos = $this->profilo_utente->navigation->posizione;
		}
		return $pos;
	}

	final protected function show_dom( $id_elemento ) {
		$this->response->script( " $('#{$id_elemento}').show(); " );
	}

	final protected function hide_dom( $id_elemento ) {
		$this->response->script( " $('#{$id_elemento}').hide(); " );
	}

	final protected function show_edit_records($timeout = 50 ) {
		$this->response->script( "
				setTimeout(
					function() {
						$('#show_records_wrapper').hide('blind', 
							function() {
								$('#edit_record_wrapper').show('blind') 
							}
						)
					}, {$timeout}
				)
" );
	}

	final protected function show_html($html = "", $div = "div_home_page"){
		if (! is_a( $this->response, "xajaxResponse" )) {
			return $this->error("You called <i>show_html</i> without having a <i>xajax_response</i>");
		}
		$this->response->assign( $div, "innerHTML", $html );
	}
	
	final protected function show_records_table( $timeout = 50 ) {
		$this->response->script( "
				setTimeout(
					function() {
						$('#edit_record_wrapper').hide('blind',
							function() {
								$('#show_records_wrapper').show('blind');
								redraw_data_table('show_records');
							}
						)
					}, {$timeout}
				)
				" );
	}

	protected function initialized( ) {
		$globali = $GLOBALS [ "lib_caricate" ];
		return $GLOBALS [ "lib_caricate" ] [ get_class( $this ) ];
	}

	protected function set_initialized( ) {
		$globali = $GLOBALS [ "lib_caricate" ];
		$GLOBALS [ "lib_caricate" ] [ get_class( $this ) ] = true;
		$this->initialized = true;
	}

	public function invia_email( $to = "", $subject = "", $body = "", $allegati = array() ) {
		// Carico la configurazione...
		$ci = get_instance();
		$ci->config->load("email");
		return $this->invia_email_generica( $to, $subject, $body, $allegati );
	}

	protected function invia_email_generica( $to = "", $subject = "", $body = "", $allegati = array(), $bcc = "" ) {
		require_once "{$_SERVER['DOCUMENT_ROOT']}/application/third_party/Mail.php";
		require_once "{$_SERVER['DOCUMENT_ROOT']}/application/third_party/Mail/mime.php";
		$headers = array (
				'From' => "{$this->config->config["website_name"]} <{$this->config->config["generic_email"]}>",
				'To' => xss_clean($to),
				'Subject' => xss_clean($subject) 
		);
		if (trim( $bcc ) !== "") {
			$headers [ "Bcc" ] = xss_clean( $bcc );
		}
		/**
		 * Configurazione
		 */
		if ($this->config["mail_protocol"] == "ssl"){
			$socket_options =  array (
						"ssl" => array (
								"verify_peer" => false,
								"verify_peer_name" => false,
								"allow_self_signed" => true 
						) 
				);
		} else {
			$socket_options = "";
		}
		$smtp = Mail::factory( 'smtp', array (
				"host" => "{$this->config["mail_protocol"]}://{$this->config["mail_host"]}",
				"port" => "{$this->config["mail_port"]}",
				"auth" => true,
				"username" => "{$this->config["mail_username"]}", // your gmail account
				"password" => "{$this->config["mail_password"]}", // your password
				"debug" => false,
				$socket_options
		) );
		$body = xss_clean($body);
		$nohtml = strip_tags( $body );
		$mime = new Mail_mime();
		$mime->setTXTBody( $nohtml );
		$mime->setHTMLBody( $body );
		// Aggiungo gli allegati //
		if (sizeof( $allegati )) {
			foreach ( $allegati as $file ) {
				// Riconverto il nome!
				$file->name = str_replace( "---", "/", $file->name );
				$mime->addAttachment( $file->name, "application/octet-stream", $file->attach_name );
			}
		}
		$body = $mime->get();
		// the 2nd parameter allows the header to be overwritten
		// @see http://pear.php.net/bugs/18256
		$headers = $mime->headers( $headers, true );
		// Send the mail
		$mail = $smtp->send( $to, $headers, $body );
		return $mail;
	}
	
	protected function parseRecord(&$record){
		return true;
	}
}
?>