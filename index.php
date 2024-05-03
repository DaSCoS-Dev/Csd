<?php

/*
 * ---------------------------------------------------------------
* APPLICATION ENVIRONMENT
* ---------------------------------------------------------------
* You can load different configurations depending on your
* current environment. Setting the environment also influences
* things like logging and error reporting.
* This can be set to anything, but default usage is:
* development
* testing
* production
* NOTE: If you change these, also change the error_reporting() code below
*/
define( 'ENVIRONMENT', 'development' );
/*
 * ---------------------------------------------------------------
 * ERROR REPORTING
 * ---------------------------------------------------------------
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */

if (defined( 'ENVIRONMENT' )) {
	switch ( ENVIRONMENT ) {
		case 'development' :
			error_reporting( E_ALL & ~ E_NOTICE & ~ E_DEPRECATED );
			break;

		case 'testing' :
		case 'production' :
			error_reporting( 0 );
			break;

		default :
			exit( 'The application environment is not set correctly.' );
	}
}

/*
 * ---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 * ---------------------------------------------------------------
 * This variable must contain the name of your "system" folder.
 * Include the path if the folder is not in the same directory
 * as this file.
 */
$system_path = 'system';

/*
 * ---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 * ---------------------------------------------------------------
 * If you want this front controller to use a different "application"
 * folder then the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server. If
 * you do, use a full server path. For more info please see the user guide:
 * http://codeigniter.com/user_guide/general/managing_apps.html
 * NO TRAILING SLASH!
 */
$application_folder = 'application';

/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here. For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 * IMPORTANT: If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller. Leave the function name blank if you need
 * to call functions dynamically via the URI.
 * Un-comment the $routing array below to use this feature
 */
// The directory name, relative to the "controllers" folder. Leave blank
// if your controller is not in a sub-folder within the "controllers" folder
// $routing['directory'] = '';

// The controller class file name. Example: Mycontroller
// $routing['controller'] = '';

// The controller function you wish to be called.
// $routing['function'] = '';

/*
 * -------------------------------------------------------------------
 * CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 * Un-comment the $assign_to_config array below to use this feature
 */
// $assign_to_config['name_of_config_item'] = 'value of config item';

// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS. DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 * Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

// Set the current directory correctly for CLI requests
if (defined( 'STDIN' )) {
	chdir( dirname( __FILE__ ) );
}

if (realpath( $system_path ) !== FALSE) {
	$system_path = realpath( $system_path ) . '/';
}

// ensure there's a trailing slash
$system_path = rtrim( $system_path, '/' ) . '/';

// Is the system path correct?
if (! is_dir( $system_path )) {
	exit( "Your system folder path does not appear to be set correctly. Please open the following file and correct this: " . pathinfo( __FILE__, PATHINFO_BASENAME ) );
}

/*
 * -------------------------------------------------------------------
 * Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
// The name of THIS file
define( 'SELF', pathinfo( __FILE__, PATHINFO_BASENAME ) );

// The PHP file extension
// this global constant is deprecated.
define( 'EXT', '.php' );

// Path to the system folder
define( 'BASEPATH', str_replace( "\\", "/", $system_path ) );

// Path to the front controller (this file)
define( 'FCPATH', str_replace( SELF, '', __FILE__ ) );

// Name of the "system folder"
define( 'SYSDIR', trim( strrchr( trim( BASEPATH, '/' ), '/' ), '/' ) );

// The path to the "application" folder
if (is_dir( $application_folder )) {
	define( 'APPPATH', $application_folder . '/' );
} else {
	if (! is_dir( BASEPATH . $application_folder . '/' )) {
		exit( "Your application folder path does not appear to be set correctly. Please open the following file and correct this: " . SELF );
	}

	define( 'APPPATH', BASEPATH . $application_folder . '/' );
}

/*
 * -----------------------------------------------------------------
 * CATCH ERRORS
 * -----------------------------------------------------------------
 */
define( 'E_FATAL', E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR );
define( 'DISPLAY_ERRORS', FALSE );
define( 'ERROR_REPORTING', E_ALL | E_STRICT );
define( 'LOG_ERRORS', TRUE );
// Register the functions
register_shutdown_function( 'shut' );
set_error_handler( 'handler' );

// Function to catch no user error handler function errors...
function shut( ) {
	$error = error_get_last();
	if ($error && ( $error [ 'type' ] & E_FATAL )) {
		$messaggio = $error [ 'message' ];
		handler( $error [ 'type' ], $messaggio, $error [ 'file' ], $error [ 'line' ] );
	}
}

function handler( $errno, $errstr, $errfile, $errline ) {
	switch ( $errno ) {
		case E_ERROR : // 1 //
			$typestr = 'E_ERROR';
			break;
		case E_WARNING : // 2 //
			$typestr = 'E_WARNING';
			break;
		case E_PARSE : // 4 //
			$typestr = 'E_PARSE';
			break;
		case E_NOTICE : // 8 //
			$typestr = 'E_NOTICE';
			break;
		case E_CORE_ERROR : // 16 //
			$typestr = 'E_CORE_ERROR';
			break;
		case E_CORE_WARNING : // 32 //
			$typestr = 'E_CORE_WARNING';
			break;
		case E_COMPILE_ERROR : // 64 //
			$typestr = 'E_COMPILE_ERROR';
			break;
		case E_CORE_WARNING : // 128 //
			$typestr = 'E_COMPILE_WARNING';
			break;
		case E_USER_ERROR : // 256 //
			$typestr = 'E_USER_ERROR';
			break;
		case E_USER_WARNING : // 512 //
			$typestr = 'E_USER_WARNING';
			break;
		case E_USER_NOTICE : // 1024 //
			$typestr = 'E_USER_NOTICE';
			break;
		case E_STRICT : // 2048 //
			$typestr = 'E_STRICT';
			break;
		case E_RECOVERABLE_ERROR : // 4096 //
			$typestr = 'E_RECOVERABLE_ERROR';
			break;
		case E_DEPRECATED : // 8192 //
			$typestr = 'E_DEPRECATED';
			break;
		case E_USER_DEPRECATED : // 16384 //
			$typestr = 'E_USER_DEPRECATED';
			break;
	}
	$is_dev = false;
	$ambienti_dev = array (
			"dev.",
	);
	foreach ( $ambienti_dev as $ambiente ) {
		if (stripos( $GLOBALS [ "_SERVER" ] [ "HTTP_HOST" ], $ambiente ) !== false) {
			$is_dev = true;
			break;
		}
	}
	$errstr = str_replace( array (
			"\r\n",
			"\r",
			"\n"
	), "<br />", $errstr );
	$message = <<<EOF
		<h3><span class="badge bg-danger">Si &egrave; verificato un errore imprevisto</span></h3>
		<strong>Una mail di segnalazione &egrave; stata inviata al Team Di Supporto</strong><br>
	Grazie!
EOF
;
// In ambiente DEV mostro tutto lo spataffio
if ($is_dev) {
	$message .= "<br>PHP:<b>{$typestr}:</b>{$errstr} in <b>{$errfile}</b> on line <b>{$errline}</b><br/>";
}

if (( $errno & E_FATAL ) && ENVIRONMENT === 'production') {
	header( 'Location: 500.html' );
	header( 'Status: 500 Internal Server Error' );
} elseif (ENVIRONMENT === "development") {
	printf( '%s', $message );
}
if (! ( $errno & ERROR_REPORTING )) {
	return;
}
if (DISPLAY_ERRORS) {
	printf( '%s', $message );
}
// Logging error on php file error log...
if (LOG_ERRORS) {
	error_log( strip_tags( $message ), 0 );
}
// Alla fine devo inviare la mail di segnalazione SEMPRE e con TUTTO lo spataffio...
$message = "PHP:<b>{$typestr}:</b>{$errstr} in <b>{$errfile}</b> on line <b>{$errline}</b><br/>";
$ip = getClientIP();
$message .= <<<EOF
<br>Funzione chiamata come {$_SERVER["REQUEST_METHOD"]}<br>
	query string {$_SERVER["QUERY_STRING"]}<br>
	ip remoto {$ip}<br>
	request uri {$_SERVER["REQUEST_URI"]}<br>
	path info {$_SERVER["PATH_INFO"]}<br>
EOF
;
symple_send_mail( "from@email", "to@email", "Error in {$GLOBALS["_SERVER"]["HTTP_HOST"]} code!", $message );
}

function getClientIP( ) {
	if (array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER )) {
		return $_SERVER [ "HTTP_X_FORWARDED_FOR" ];
	} else if (array_key_exists( 'REMOTE_ADDR', $_SERVER )) {
		return $_SERVER [ "REMOTE_ADDR" ];
	} else if (array_key_exists( 'HTTP_CLIENT_IP', $_SERVER )) {
		return $_SERVER [ "HTTP_CLIENT_IP" ];
	}
	return '';
}

function symple_send_mail( $from = "your@email", $to = "default_to@email", $subject = "", $body = "", $allegati = array() ) {
	require_once "{$_SERVER['DOCUMENT_ROOT']}/application/third_party/Mail.php";
	require_once "{$_SERVER['DOCUMENT_ROOT']}/application/third_party/Mail/mime.php";

	$headers = array (
			'From' => $from,
			'To' => $to,
			'Subject' => $subject
	);

	$smtp = Mail::factory( 'smtp', array (
			'host' => '',
			'port' => '25',
			'auth' => true,
			'username' => '', // your email account
			'password' => '', // your email password
			"debug" => false
	) );
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

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 * And away we go...
 */
require_once BASEPATH . 'core/CodeIgniter.php';

/* End of file index.php */
/* Location: ./index.php */