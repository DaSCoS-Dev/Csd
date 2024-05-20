<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
class Main_admin extends Super_lib {
	public $model, $view;
	protected $record_limit = 5, $options = array (), $ci;

	public function __construct( $params = null ) {
		if ($params != null) {
			foreach ( $params as $var => $value ) {
				$this->$var = $value;
			}
		} else {
			parent::__construct( $params );
		}
		$this->ci = get_instance();
		$this->load->helper( "security" );
		// Carico il model
		$this->load->model_options( "Admin/model_admin", $params );
		// Carico vista
		$this->load->library( "Views_assembler/Admin/Main_admin_views", $this );
		// Costruisco le dipendenze
		$this->build_dependency();
		// Assegno le variabili
		$this->model = $this->model_admin;
		$this->model->super_lib = $this->super_lib;
		$this->view = $this->main_admin_views;
		$this->view->super_lib = $this->super_lib;
	}

	/**
	 * The user is logged in and he/she is an Admin.
	 * This is the default action upon login.
	 * Build the administrations Menu and the help
	 */
	protected function index( ) {
		$html = $this->view->build( "build_menu" );
		$this->response->assign( "admin_menu_struct", "innerHTML", $html );
		$help = $this->view->build( "main_admin_help" );
		$this->show_html( $help );
	}

	/**
	 * The Admin choose to change something about the main configuration of Framework
	 *
	 * @return xajaxResponse
	 */
	protected function configure( ) {
		if (! $this->is_admin() and ! $this->is_logged_in()) {
			return $this->error( "You are NOT allowed to access this class" );
		}
		$ci = get_instance();
		$ci->config->load( "email", true );
		// Presento le opzioni modificabili e le "possibilità" di creazione classi
		$this->load_options();
		$html = $this->view->build( "admin_config", $this->options );
		$this->show_html( $html );
		$this->enable_popover();
	}

	/**
	 * The Admin choose to install/reinstall the Framework.
	 * This is the default FIRST action upon unzipping the project into the root web folder
	 * and opening for the first time the relative web page in the browser.
	 * $step equals 1 for default (db config), become 2 or more for the next step
	 * Default 2ns step equals creation of Admin user
	 *
	 * @param number $step        	
	 */
	protected function install( $step = 1 ) {
		if (intval( $step ) === 1) {
			$this->ci->config->load( "database", true );
			$this->load_install_options();
			$html = $this->view->build( "install_framework", $this->options );
			// Show a varning about "default tables will be erased!"
			$this->error( "By clicking on \'Test DB Connection\' the Framework will test the connection parameters you have provided and, if the connection to the Database is successful, the standard tables (if present) will be completely deleted and recreated!<br>If you don\'t want this, you have to manually edit the Framework configuration file (/application/config/framework.php) and fix <strong>\$config[\"framework_configured\"] = true;</strong> in the Framework configuration file (/application/config/framework.php).<br><br>Click ok to continue or wait 30 seconds", "WARNING - Read Carefully", 30000 );
		} elseif (intval( $step ) === 2) {
			$html = $this->view->build( "create_default_user" );
		}
		$this->show_html( $html );
		$this->enable_popover();
	}

	/**
	 * End of step 2 of "install".
	 * Creates the Admin user, changes the flag in the configuration that says "I'm (or not) installed".
	 * Shows a message for acknoweledge of the end of the installation process and shows the login form
	 *
	 * @param array $form_input        	
	 */
	protected function save_user( $form_input ) {
		$this->load->model( 'tank_auth/users' );
		require_once ( "{$_SERVER['DOCUMENT_ROOT']}/application/helpers/PasswordHash.php" );
		$this->build_dependency();
		$hasher = new PasswordHash( $this->ci->config->item( 'phpass_hash_strength' ), $this->ci->config->item( 'phpass_hash_portable' ) );
		$hashed_password = $hasher->HashPassword( $form_input [ "password" ] );
		$data = array (
				'username' => $form_input [ "username" ],
				'password' => $hashed_password,
				'email' => $form_input [ "email" ],
				'last_ip' => $this->getClientIP(),
				'invited_by' => 0,
				"banned" => 0,
				"ban_reason" => "",
				"code" => $this->profilo_utente->Codice 
		);
		$this->users->profilo_utente = $this->profilo_utente;
		$this->users->create_user( $data, true );
		// Change the configuration about the "status" of the framework
		$conf_file = file_get_contents( "{$_SERVER['DOCUMENT_ROOT']}/application/config/framework.php" );
		$conf_file = str_replace( "\$config[\"framework_configured\"] = false;", "\$config[\"framework_configured\"] = true;", $conf_file );
		file_put_contents( "{$_SERVER['DOCUMENT_ROOT']}/application/config/framework.php", $conf_file );
		// Should be all right cause it's the first user in pretty new tables
		$this->message( "Well Done!<br>Your Framework is installed and you can now login with the provided username ( {$form_input["username"]} ) and password ( {$form_input["password"]} ).", "Framework Installed" );
		$login_form = $this->load->view( "auth/login_form.php", array (), true );
		$this->show_html( $login_form );
	}

	/**
	 * Used to test the Db connection with submitted parameters.
	 * If successfully let's change the configuration file and show a popup for the next step (install -> step 2)
	 * On failure a popup warning will be displayed
	 *
	 * @param array $form_input        	
	 * @return boolean
	 */
	protected function test_db_connection( $form_input ) {
		$this->ci->config->load( "database", true );
		$this->load_install_options();
		if (! $this->check_base_input( $form_input, "install" )) {
			return false;
		}
		mysqli_report( MYSQLI_REPORT_ERROR );
		$conn = $this->create_connection( $form_input );
		if (! $this->validate_connection($conn, $form_input)) {
			if (!isset($form_input["force_db_creation"])) {
				return false;
			}
			$conn = $this->create_connection($form_input, false);
			if (! $this->validate_forced_connection($conn, $form_input)) {
				return false;
			}
		}
		if (! $this->validate_db_authorizations( $conn, $form_input )) {
			return false;
		}
		if (! $this->update_config_and_initialize_db( $conn, $form_input )) {
			return false;
		}
		$this->confirm_action( "Database connected", "The Db is configured.<br>Let go ahead and configure the Administrator!", "function() { xajax_execute('Admin/Main_admin', 'install', 2) }" );
		return true;
	}

	/**
	 * Take the given configuration parameters (from the form build on "configure"), do some formal checks,
	 * save the new configurations
	 *
	 * @param array $form_input        	
	 * @param string $action        	
	 * @param string $immediate_redirect        	
	 * @return boolean
	 */
	protected function change_config( $form_input, $action = "configure", $immediate_redirect = true ) {
		if ($form_input [ "discriminator" ] === "database") {
			$this->load_install_options();
		} else {
			$this->load_options();
		}
		// Check
		$base_check = $this->check_base_input( $form_input );
		if ($base_check === false) {
			return false;
		} else {
			$good_options = $base_check [ "good_options" ];
			$stream = $base_check [ "stream" ];
		}
		// Ciclo nelle options e vado a modificare, se esiste, ciò che mi arriva dal post
		if ($form_input [ "discriminator" ] === "database") {
			$this->do_database_replacements( $stream, $good_options, $form_input );
		} else {
			$this->do_config_replacements( $stream, $good_options, $form_input );
		}
		// Salvo il file
		$save = file_put_contents( "{$_SERVER['DOCUMENT_ROOT']}/application/config/{$form_input["discriminator"]}.php", $stream );
		if ($save === false) {
			$this->error( "Cannot open file {$form_input["discriminator"]} for write. Please check the file permissions", "Error", "4000" );
			if ($immediate_redirect) {
				$this->response->script( " setTimeout( function() { xajax_execute('Admin/Main_admin', '{$action}' ) } , 4100 ) " );
			}
			return false;
		} else {
			if ($immediate_redirect) {
				$this->message( "The new configuration for {$form_input["discriminator"]} has been saved. Please wait while reloading", "Success", "3000" );
				$this->response->script( " setTimeout( function() { xajax_execute('Admin/Main_admin', '{$action}' ) } , 3100 ) " );
			}
		}
		return true;
	}

	/**
	 * Used to initialize the building of a new set of Library, Model and View.
	 * Retrieve all the DataBase tables (except for the system ones, prefixed with "csd_"), check if exists the relative elements of LMV,
	 * build the various dropdown and form
	 */
	protected function create_new_functionality_form( ) {
		// Show available and unused database tables
		$tables = $this->model->get_db_tables();
		// Create the naming fields
		if (sizeof( $tables )) {
			// Get the Table structure
			$column_name = "Tables_in_{$this->db->database}";
			$html = $this->view->build( "create_new_functionality_form", array (
					"tables" => $tables,
					"column_name" => $column_name 
			) );
		} else {
			$this->error( "There are no tables, other than the system ones, in the database. You must create at least one to be able to create a new Csd function!", "Recoverable error", 10000 );
			return false;
		}
		$this->show_html( $html );
	}

	/**
	 * Let create a new set for accessing and manipulating the new functionality
	 *
	 * @param array $input        	
	 */
	protected function create_new_functionality( $input, $force = false ) {
		// No check is done cause there are no "custom fields", except the existance of at least a model
		if (! isset( $input [ "discriminator" ] ) or $input [ "discriminator" ] !== "functionality") {
			$this->error( "You have an error in the form. Please try again and ensure the field \'discriminator\' is present", "Error", 4000 );
			$this->response->script( " setTimeout( function() { xajax_execute('Admin/Main_admin', 'create_new_functionality_form') } , 4100 ) " );
			return false;
		}
		$stream = file_get_contents( "{$_SERVER['DOCUMENT_ROOT']}/application/config/functionality.php" );
		if ($stream === false) {
			$this->error( "Cannot open file config/functionality.php for read. Please check the existence of the file and its permissions", "Error", 4000 );
			$this->response->script( " setTimeout( function() { xajax_execute('Admin/Main_admin', 'create_new_functionality_form') } , 4100 ) " );
			return false;
		}
		$just_done = $this->check_for_functionality( $stream, $input [ "functionality_table" ] );
		if (! $just_done or $force) {
			// Take the templates and create the structure!!
			$this->buildFunctionalityStructure( $input [ "functionality_table" ] );
		} else {
			$this->error( "The chosen table has already been used to create the basic structures therefore it is not possible to reuse it", "Error", 4000 );
			$this->response->script( " setTimeout( function() { xajax_execute('Admin/Main_admin', 'create_new_functionality_form') } , 4100 ) " );
			return false;
		}
		$this->message( "Functionality has been built right now!", "Success" );
	}

	/**
	 * PRIVATE FUNCTIONS
	 */
	private function buildFunctionalityStructure( $table_name ) {
		$library_name_U = ucfirst( strtolower( $table_name ) );
		$library_name_L = strtolower( $library_name_U );
		// AjaxRequests
		$ajax_def = $this->load->view( "Templates/AjaxRequests/default.php", array (
				"library_name_U" => $library_name_U,
				"library_name_L" => $library_name_L 
		), true );
		mkdir( "{$_SERVER["DOCUMENT_ROOT"]}/application/controllers/ajax_requests/{$library_name_U}/", 0755, true );
		file_put_contents( "{$_SERVER["DOCUMENT_ROOT"]}/application/controllers/ajax_requests/{$library_name_U}/ajax_{$library_name_L}.php", $ajax_def );
		// Classes
		$class_def = $this->load->view( "Templates/Classes/default.php", array (
				"library_name_U" => $library_name_U,
				"library_name_L" => $library_name_L 
		), true );
		mkdir( "{$_SERVER["DOCUMENT_ROOT"]}/application/classes/{$library_name_U}/", 0755, true );
		file_put_contents( "{$_SERVER["DOCUMENT_ROOT"]}/application/classes/{$library_name_U}/{$library_name_L}.php", $class_def );
		// Libraries
		$lib_def = $this->load->view( "Templates/Libraries/default.php", array (
				"library_name_U" => $library_name_U,
				"library_name_L" => $library_name_L 
		), true );
		mkdir( "{$_SERVER["DOCUMENT_ROOT"]}/application/libraries/{$library_name_U}/", 0755, true );
		file_put_contents( "{$_SERVER["DOCUMENT_ROOT"]}/application/libraries/{$library_name_U}/Main_{$library_name_L}.php", $lib_def );
		// Models
		$model_def = $this->load->view( "Templates/Models/default.php", array (
				"library_name_U" => $library_name_U,
				"library_name_L" => $library_name_L,
				"table_name" => $table_name 
		), true );
		mkdir( "{$_SERVER["DOCUMENT_ROOT"]}/application/models/{$library_name_U}/", 0755, true );
		file_put_contents( "{$_SERVER["DOCUMENT_ROOT"]}/application/models/{$library_name_U}/model_{$library_name_L}.php", $model_def );
		// Views
		$view_struct_def = $this->load->view( "Templates/Views/general_structure.php", array (), true );
		mkdir( "{$_SERVER["DOCUMENT_ROOT"]}/application/views/{$library_name_U}/", 0755, true );
		file_put_contents( "{$_SERVER["DOCUMENT_ROOT"]}/application/views/{$library_name_U}/general_structure.php", $view_struct_def );
		$table_struct_def = $this->load->view( "Templates/Views/table_structure.php", array (), true );
		file_put_contents( "{$_SERVER["DOCUMENT_ROOT"]}/application/views/{$library_name_U}/table_structure.php", $table_struct_def );
		$table_edit_structure_def = $this->load->view( "Templates/Views/edit_structure.php", array (), true );
		file_put_contents( "{$_SERVER["DOCUMENT_ROOT"]}/application/views/{$library_name_U}/edit_structure.php", $table_edit_structure_def );
		// Views_assembler
		$views_assembler_def = $this->load->view( "Templates/Views_assembler/default.php", array (
				"library_name_U" => $library_name_U,
				"library_name_L" => $library_name_L 
		), true );
		mkdir( "{$_SERVER["DOCUMENT_ROOT"]}/application/libraries/Views_assembler/{$library_name_U}/", 0755, true );
		file_put_contents( "{$_SERVER["DOCUMENT_ROOT"]}/application/libraries/Views_assembler/{$library_name_U}/Main_{$library_name_L}_views.php", $views_assembler_def );
	}

	/**
	 * Check if, inside the relative "configuratios file", we already have this table
	 *
	 * @param unknown $table        	
	 */
	private function check_for_functionality( $config_file_content, $table ) {
		// Do we have the table in the file?
		// escape special characters in the query
		$pattern = preg_quote( $table, '/' );
		// finalise the regular expression, matching the whole line
		$pattern = "/^.*{$pattern}.*\$/m";
		// search, and store all matching occurences in $matches
		if (preg_match_all( $pattern, $config_file_content, $matches )) {
			return $matches [ 0 ];
		} else {
			return false;
		}
	}

	/**
	 * Db functions
	 */
	private function create_connection( $form_input, $withDatabase = true ) {
		$hostname = xss_clean( $form_input [ "hostname" ] );
		$username = xss_clean( $form_input [ "username" ] );
		$password = xss_clean( $form_input [ "password" ] );
		$database = $withDatabase ? xss_clean( $form_input [ "database" ] ) : null;
		return @new mysqli( $hostname, $username, $password, $database );
	}

	private function validate_connection( $conn, $form_input ) {
		if ($conn->connect_error) {
			if (! $form_input [ "force_db_creation" ]) {
				$this->display_db_error( $conn->connect_error );
			}
			return false;
		}
		return true;
	}

	private function validate_forced_connection( $conn, $form_input ) {
		if (! $this->validate_connection( $conn, $form_input )) {
			return false;
		}
		return true;
	}

	private function validate_db_authorizations( $conn, $form_input ) {
		if (! $this->validate_db_existence( $conn, $form_input )) {
			return false;
		}
		if (! $this->validate_db_creation( $conn, $form_input )) {
			return false;
		}
		if (! $this->validate_db_permissions( $conn, $form_input )) {
			return false;
		}
		return true;
	}

	private function validate_db_existence( $conn, $form_input ) {
		$database = xss_clean( $form_input [ "database" ] );
		$sql = "SHOW DATABASES LIKE '{$database}'";
		$conn->query( $sql );
		if ($conn->error !== "") {
			return $this->got_db_error($conn);
		}
		return true;
	}

	private function validate_db_creation( $conn, $form_input ) {
		$database = xss_clean( $form_input [ "database" ] );
		$sql = "CREATE DATABASE `test_{$database}_validation` DEFAULT CHARACTER SET utf8mb4;";
		$conn->query( $sql );
		if ($conn->error !== "") {
			return $this->got_db_error($conn);
		} else {			
			return true;
		}
	}

	private function validate_db_permissions( $conn, $form_input ) {
		$database = xss_clean( $form_input [ "database" ] );
		$sql = "CREATE TABLE `test_{$database}_validation`.`test` ( `field` INT NOT NULL ) ENGINE = MyISAM;";
		$conn->query( $sql );
		if ($conn->error !== "") {
			return $this->got_db_error($conn);
		} else {
			$conn->query( "DROP DATABASE `test_{$database}_validation`" );
			return true;
		}
	}

	private function got_db_error($conn){
		$this->display_db_error( $conn->error );
		$conn->close();
		return false;
	}
	
	private function display_db_error( $error ) {
		$conn_err = escape_single_quote( $error );
		$this->error( "Database Error. Please check the form.<br>The Database Server tells: <strong>{$conn_err}</strong>", "Error", "5000" );
	}

	private function update_config_and_initialize_db( $conn, $form_input ) {
		if (! $this->change_config( $form_input, "install", false )) {
			$conn->close();
			return false;
		} else {
			$this->Super_model->db->conn_id = $conn;
			$this->create_database_tables( xss_clean( $form_input [ "database" ] ) );
			return true;
		}
	}

	private function create_database_tables( $dbname = "" ) {
		// Open the db tables definition
		$db_def = $this->load->view( "Templates/db_structure.php", array (
				"dbname" => $dbname 
		), true );
		// Let's execute queryes
		$this->Super_model->multi_query( $db_def );
	}

	private function do_config_replacements( &$stream, $good_options, $form_input ) {
		foreach ( $good_options as $key => $value ) {
			// Xss clean of form input. $key and $value comes from config, so is already cleaned
			$form_input [ "{$key}" ] = xss_clean( $form_input [ "{$key}" ] );
			// Build pattern to search for and replacement
			$pattern = "\$config[\"{$key}\"] = \"{$good_options["{$key}"]["value"]}\";";
			$replace = "\$config[\"{$key}\"] = \"{$form_input["{$key}"]}\";";
			if (stripos( $stream, $pattern ) !== false and $pattern != $replace) {
				$stream = str_replace( $pattern, $replace, $stream );
			}
		}
	}

	private function do_database_replacements( &$stream, $good_options, $form_input ) {
		foreach ( $good_options as $key => $value ) {
			// Xss clean of form input. $key and $value comes from config, so is already cleaned
			$form_input [ "{$key}" ] = xss_clean( $form_input [ "{$key}" ] );
			// Build pattern to search for and replacement
			$pattern = "\$db[\"default\"][\"{$key}\"] = \"{$good_options["{$key}"]["value"]}\";";
			$replace = "\$db[\"default\"][\"{$key}\"] = \"{$form_input["{$key}"]}\";";
			if (stripos( $stream, $pattern ) !== false and $pattern != $replace) {
				$stream = str_replace( $pattern, $replace, $stream );
			}
		}
	}

	private function check_base_input( $form_input, $action = "index" ) {
		if (! isset( $form_input [ "discriminator" ] ) or trim( $form_input [ "discriminator" ] ) == "") {
			$this->error( "You have an error in the form. Please try again and ensure the field \'discriminator\' is present", "Error", 4000 );
			$this->response->script( " setTimeout( function() { xajax_execute('Admin/Main_admin', '{$action}') } , 4100 ) " );
			return false;
		}
		$good_options = $this->options [ $form_input [ "discriminator" ] ];
		if (! sizeof( $good_options )) {
			$this->error( "You have an error in the form. Please try again and ensure the field \'discriminator\' has a correct value", "Error", 4000 );
			$this->response->script( " setTimeout( function() { xajax_execute('Admin/Main_admin', '{$action}') } , 4100 ) " );
			return false;
		}
		// Apro il file relativo
		$stream = file_get_contents( "{$_SERVER['DOCUMENT_ROOT']}/application/config/{$form_input["discriminator"]}.php" );
		if ($stream === false) {
			$this->error( "Cannot open file {$form_input["discriminator"]} for read. Please check the existence of the file and its permissions", "Error", 4000 );
			$this->response->script( " setTimeout( function() { xajax_execute('Admin/Main_admin', '{$action}') } , 4100 ) " );
			return false;
		}
		return array (
				"good_options" => $good_options,
				"stream" => $stream 
		);
	}

	private function load_install_options( ) {
		$this->options [ "database" ] [ "hostname" ] = array (
				"value" => $this->ci->config->config [ "database" ] [ "default" ] [ "hostname" ],
				"Title" => "About Hostname",
				"Popover" => "This is the hostname of your Database Engine. Usually 'localhost' or '127.0.0.1'.<br>Try first with 'localhost'." 
		);
		$this->options [ "database" ] [ "username" ] = array (
				"value" => $this->ci->config->config [ "database" ] [ "default" ] [ "username" ],
				"Title" => "About Username",
				"Popover" => "This is the user name of your Database." 
		);
		$this->options [ "database" ] [ "password" ] = array (
				"value" => $this->ci->config->config [ "database" ] [ "default" ] [ "password" ],
				"Title" => "About Password",
				"Popover" => "This is the password of your database user." 
		);
		$this->options [ "database" ] [ "database" ] = array (
				"value" => $this->ci->config->config [ "database" ] [ "default" ] [ "database" ],
				"Title" => "About Database",
				"Popover" => "This is your database name. Keep in mind that if you also use a 'dev' environment, by default we use a 'database_dev' name for developing/test." 
		);
		$this->options [ "database" ] [ "dbdriver" ] = array (
				"value" => $this->ci->config->config [ "database" ] [ "default" ] [ "dbdriver" ],
				"Title" => "About Driver",
				"Popover" => "This is the driver to use when connecting to the database. Usually 'mysql' or 'mysqli'." 
		);
	}

	private function load_options( ) {
		// CONFIGURABLE OPTIONS
		$this->options [ "config" ] [ "language" ] = array (
				"value" => $this->ci->config->config [ "language" ],
				"Title" => "About Language",
				"Popover" => "This determines which set of language files should be used.<br>Make sure there is an available translation if you intend to use something other than english." 
		);
		$this->options [ "config" ] [ "encryption_key" ] = array (
				"value" => $this->ci->config->config [ "encryption_key" ],
				"Title" => "About Key",
				"Popover" => "This is the Secret Key (like a password) used to encrypt/decrypt the Session and any other data with cript_high_security and decrypt_high_security.<br>If you change it, you will cause the immediate disconnection of each user and possible inconsistencies in the database" 
		);
		$this->options [ "config" ] [ "encryption_digest" ] = array (
				"value" => $this->ci->config->config [ "encryption_digest" ],
				"Title" => "About Digest",
				"Popover" => "The cipher method. For a list of available cipher methods, use openssl_get_cipher_methods().<br>Usually aes256" 
		);
		$this->options [ "config" ] [ "encryption_private_key" ] = array (
				"value" => $this->ci->config->config [ "encryption_private_key" ],
				"Title" => "About Private Key",
				"Popover" => "A non-NULL Initialization Vector.<br>An arbitrary alpha-numeric string that will be used with the Secret Key for data encryption" 
		);
		$this->options [ "config" ] [ "proxy_ips" ] = array (
				"value" => $this->ci->config->config [ "proxy_ips" ],
				"Title" => "About Proxyes",
				"Popover" => "If your server is behind a reverse proxy, you must whitelist the proxy IP addresses from which CodeIgniter should trust the HTTP_X_FORWARDED_FOR header in order to properly identify the visitor's IP address.<br>Comma-delimited, e.g. '10.0.1.200,10.0.1.201'" 
		);
		// GENERIC FRAMEWORK OPTIONS
		$this->options [ "framework" ] [ "logo" ] = array (
				"value" => $this->ci->config->config [ "logo" ],
				"Title" => "About Logo",
				"Popover" => "This is the Logo of your Framework, relative to {$_SERVER['DOCUMENT_ROOT']}/assets/img/." 
		);
		$this->options [ "framework" ] [ "site_name" ] = $this->ci->config->config [ "site_name" ];
		// EMAIL OPTIONS
		$this->options [ "email" ] [ "mail_host" ] = array (
				"value" => $this->ci->config->config [ "email" ] [ "mail_host" ],
				"Title" => "About Mail Host",
				"Popover" => "This is the Mail Server Host used to send outgoing mails.<br>No consistency check is done, it is your responsibility to enter the correct parameters.<div class='alert alert-danger'>These values are automatically generated during first installation and may not be correct. Check carefully.</div>" 
		);
		$this->options [ "email" ] [ "mail_port" ] = array (
				"value" => $this->ci->config->config [ "email" ] [ "mail_port" ],
				"Title" => "About Mail Port",
				"Popover" => "This is the Mail Server Port. Usually 25 or, for ssl enabled server, 465.<br>No consistency check is done, it is your responsibility to enter the correct parameters.<div class='alert alert-danger'>These values are automatically generated during first installation and may not be correct. Check carefully.</div>" 
		);
		$this->options [ "email" ] [ "mail_protocol" ] = array (
				"value" => $this->ci->config->config [ "email" ] [ "mail_protocol" ],
				"Title" => "About Protocol",
				"Popover" => "This is the protocol to use for communication with your Mail Server Host. Insert 'ssl' for ssl enabled Server, leave blank in other cases.<br>No consistency check is done, it is your responsibility to enter the correct parameters.<div class='alert alert-danger'>These values are automatically generated during first installation and may not be correct. Check carefully.</div>" 
		);
		$this->options [ "email" ] [ "mail_username" ] = array (
				"value" => $this->ci->config->config [ "email" ] [ "mail_username" ],
				"Title" => "About Username",
				"Popover" => "The User Name for authentication with the Mail Server Host.<br>No consistency check is done, it is your responsibility to enter the correct parameters.<div class='alert alert-danger'>These values are automatically generated during first installation and may not be correct. Check carefully.</div>" 
		);
		$this->options [ "email" ] [ "mail_password" ] = array (
				"value" => $this->ci->config->config [ "email" ] [ "mail_password" ],
				"Title" => "About Password",
				"Popover" => "The Password for authentication with the Mail Server Host.<br>No consistency check is done, it is your responsibility to enter the correct parameters.<div class='alert alert-danger'>These values are automatically generated during first installation and may not be correct. Check carefully.</div>" 
		);
	}
}
?>