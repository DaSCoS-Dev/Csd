<?php

if (! defined( 'BASEPATH' ))
	exit( 'No direct script access allowed' );
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package CodeIgniter
 * @author ExpressionEngine Dev Team
 * @copyright Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @link http://codeigniter.com
 * @since Version 1.0
 * @filesource
 *
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Email Helpers
 *
 * @package CodeIgniter
 * @subpackage Helpers
 * @category Helpers
 * @author ExpressionEngine Dev Team
 * @link http://codeigniter.com/user_guide/helpers/email_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Validate email address
 *
 * @access public
 * @return bool
 */
if (! function_exists( 'valid_email' )) {

	function valid_email( $address, $complete = false ) {
		//return (! preg_match( "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address )) ? FALSE : TRUE;
		$ci = get_instance();
		// Initialize library class
		$mail = new VerifyEmail();
		// Set the timeout value on stream
		$mail->setStreamTimeoutWait(10);
		// Set debug output mode
		$mail->Debug= false;		
		// Set email address for SMTP request
		$mail->setEmailFrom("{$ci->config->item("newsletter_email", "tank_auth")}");		
		// Email to check
		$email = $address;
		// Check
		$result = $mail->check($email, $complete);
		if ($result){
			return true;
		} elseif (verifyEmail::validate($email)) {
			return null;
		} else {
			return false;
		}
	}
}

// ------------------------------------------------------------------------

/**
 * Send an email
 *
 * @access public
 * @return bool
 */
if (! function_exists( 'send_email' )) {

	function send_email( $recipient, $subject = 'Test email', $message = 'Hello World' ) {
		return mail( $recipient, $subject, $message );
	}
}

/**
 *  Verifies email address by attempting to connect and check with the mail server of that account
 *
 *  Author: Sam Battat hbattat@msn.com
 *          http://github.com/hbattat
 *
 *  License: This code is released under the MIT Open Source License. (Feel free to do whatever)
 *
 *  Last update: Oct 11 2016
 *
 * @package VerifyEmail
 * @author  Husam (Sam) Battat <hbattat@msn.com>
 * This is a test message for packagist
 */

class VerifyEmail_smtp {
	public $email;
	public $verifier_email;
	public $port;
	private $mx;
	private $connect;
	private $errors;
	private $debug;
	private $debug_raw;
	
	private $_yahoo_signup_page_url = 'https://login.yahoo.com/account/create?specId=yidReg&lang=en-US&src=&done=https%3A%2F%2Fwww.yahoo.com&display=login';
	private $_yahoo_signup_ajax_url = 'https://login.yahoo.com/account/module/create?validateField=yid';
	private $_yahoo_domains = array('yahoo.com');
	private $_hotmail_signin_page_url = 'https://login.live.com/';
	private $_hotmail_username_check_url = 'https://login.live.com/GetCredentialType.srf?wa=wsignin1.0';
	private $_hotmail_domains = array('hotmail.com', 'live.com', 'outlook.com', 'msn.com');
	private $page_content;
	private $page_headers;
	
	public function __construct($email = null, $verifier_email = null, $port = 25){
		$this->debug = array();
		$this->debug_raw = array();
		if(!is_null($email) && !is_null($verifier_email)) {
			$this->debug[] = 'Initialized with Email: '.$email.', Verifier Email: '.$verifier_email.', Port: '.$port;
			$this->set_email($email);
			$this->set_verifier_email($verifier_email);
		}
		else {
			$this->debug[] = 'Initialized with no email or verifier email values';
		}
		$this->set_port($port);
	}
	
	
	public function set_verifier_email($email) {
		$this->verifier_email = $email;
		$this->debug[] = 'Verifier Email was set to '.$email;
	}
	
	public function get_verifier_email() {
		return $this->verifier_email;
	}
	
	
	public function set_email($email) {
		$this->email = $email;
		$this->debug[] = 'Email was set to '.$email;
	}
	
	public function get_email() {
		return $this->email;
	}
	
	public function set_port($port) {
		$this->port = $port;
		$this->debug[] = 'Port was set to '.$port;
	}
	
	public function get_port() {
		return $this->port;
	}
	
	public function get_errors(){
		return array('errors' => $this->errors);
	}
	
	public function get_debug($raw = false) {
		if($raw) {
			return $this->debug_raw;
		}
		else {
			return $this->debug;
		}
	}
	
	public function verify() {
		$this->debug[] = 'Verify function was called.';
		
		$is_valid = false;
		
		//check if this is a yahoo email
		$domain = $this->get_domain($this->email);
		if(in_array(strtolower($domain), $this->_yahoo_domains)) {
			$is_valid = $this->validate_yahoo();
		}
		else if(in_array(strtolower($domain), $this->_hotmail_domains)){
			$is_valid = $this->validate_hotmail();
		}
		//otherwise check the normal way
		else {
			//find mx
			$this->debug[] = 'Finding MX record...';
			$this->find_mx();
			
			if(!$this->mx) {
				$this->debug[] = 'No MX record was found.';
				$this->add_error('100', 'No suitable MX records found.');
				return $is_valid;
			}
			else {
				$this->debug[] = 'Found MX: '.$this->mx;
			}
			
			
			$this->debug[] = 'Connecting to the server...';
			$this->connect_mx();
			
			if(!$this->connect) {
				$this->debug[] = 'Connection to server failed.';
				$this->add_error('110', 'Could not connect to the server.');
				return $is_valid;
			}
			else {
				$this->debug[] = 'Connection to server was successful.';
			}
			
			
			$this->debug[] = 'Starting veriffication...';
			if(preg_match("/^220/i", $out = fgets($this->connect))){
				$this->debug[] = 'Got a 220 response. Sending HELO...';
				fputs ($this->connect , "HELO ".$this->get_domain($this->verifier_email)."\r\n");
				$out = fgets ($this->connect);
				$this->debug_raw['helo'] = $out;
				$this->debug[] = 'Response: '.$out;
				
				$this->debug[] = 'Sending MAIL FROM...';
				fputs ($this->connect , "MAIL FROM: <".$this->verifier_email.">\r\n");
				$from = fgets ($this->connect);
				$this->debug_raw['mail_from'] = $from;
				$this->debug[] = 'Response: '.$from;
				
				$this->debug[] = 'Sending RCPT TO...';
				fputs ($this->connect , "RCPT TO: <".$this->email.">\r\n");
				$to = fgets ($this->connect);
				$this->debug_raw['rcpt_to'] = $to;
				$this->debug[] = 'Response: '.$to;
				
				$this->debug[] = 'Sending QUIT...';
				$quit = fputs ($this->connect , "QUIT");
				$this->debug_raw['quit'] = $quit;
				fclose($this->connect);
				
				$this->debug[] = 'Looking for 250 response...';
				if(!preg_match("/^250/i", $from) || !preg_match("/^250/i", $to)){
					$this->debug[] = 'Not found! Email is invalid.';
					$is_valid = false;
				}
				else{
					$this->debug[] = 'Found! Email is valid.';
					$is_valid = true;
				}
			}
			else {
				$this->debug[] = 'Encountered an unknown response code.';
			}
		}
		
		return $is_valid;
	}
	
	private function get_domain($email) {
		$email_arr = explode('@', $email);
		$domain = array_slice($email_arr, -1);
		return $domain[0];
	}
	private function find_mx() {
		$domain = $this->get_domain($this->email);
		$mx_ip = false;
		// Trim [ and ] from beginning and end of domain string, respectively
		$domain = ltrim($domain, '[');
		$domain = rtrim($domain, ']');
		
		if( 'IPv6:' == substr($domain, 0, strlen('IPv6:')) ) {
			$domain = substr($domain, strlen('IPv6') + 1);
		}
		
		$mxhosts = array();
		if( filter_var($domain, FILTER_VALIDATE_IP) ) {
			$mx_ip = $domain;
		}
		else {
			getmxrr($domain, $mxhosts, $mxweight);
		}
		
		if(!empty($mxhosts) ) {
			$mx_ip = $mxhosts[array_search(min($mxweight), $mxweight)];
		}
		else {
			if( filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
				$record_a = dns_get_record($domain, DNS_A);
			}
			elseif( filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
				$record_a = dns_get_record($domain, DNS_AAAA);
			}
			
			if( !empty($record_a) ) {
				$mx_ip = $record_a[0]['ip'];
			}
			
		}
		
		$this->mx = $mx_ip;
	}
	
	
	private function connect_mx() {
		//connect
		$this->connect = @fsockopen($this->mx, $this->port);
	}
	
	private function add_error($code, $msg) {
		$this->errors[] = array('code' => $code, 'message' => $msg);
	}
	
	private function clear_errors() {
		$this->errors = array();
	}
	
	private function validate_yahoo() {
		$this->debug[] = 'Validating a yahoo email address...';
		$this->debug[] = 'Getting the sign up page content...';
		$this->fetch_page('yahoo');
		
		$cookies = $this->get_cookies();
		$fields = $this->get_fields();
		
		$this->debug[] = 'Adding the email to fields...';
		$fields['yid'] = str_replace('@yahoo.com', '', strtolower($this->email));
		
		$this->debug[] = 'Ready to submit the POST request to validate the email.';
		
		$response = $this->request_validation('yahoo', $cookies, $fields);
		
		$this->debug[] = 'Parsing the response...';
		$response_errors = json_decode($response, true)['errors'];
		
		$this->debug[] = 'Searching errors for exisiting username error...';
		foreach($response_errors as $err){
			if($err['name'] == 'yid' && $err['error'] == 'IDENTIFIER_EXISTS'){
				$this->debug[] = 'Found an error about exisiting email.';
				return true;
			}
		}
		return false;
	}
	
	private function validate_hotmail() {
		$this->debug[] = 'Validating a hotmail email address...';
		$this->debug[] = 'Getting the sign up page content...';
		$this->fetch_page('hotmail');
		
		$cookies = $this->get_cookies();
		
		$this->debug[] = 'Sending another request to get the needed cookies for validation...';
		$this->fetch_page('hotmail', implode(' ', $cookies));
		$cookies = $this->get_cookies();
		
		$this->debug[] = 'Preparing fields...';
		$fields = $this->prep_hotmail_fields($cookies);
		
		$this->debug[] = 'Ready to submit the POST request to validate the email.';
		$response = $this->request_validation('hotmail', $cookies, $fields);
		
		$this->debug[] = 'Searching username error...';
		$json_response = json_decode($response, true);
		if(!$json_response['IfExistsResult']){
			return true;
		}
		return false;
	}
	
	private function fetch_page($service, $cookies = ''){
		if($cookies){
			$opts = array(
					'http'=>array(
							'method'=>"GET",
							'header'=>"Accept-language: en\r\n" .
							"Cookie: ".$cookies."\r\n"
					)
			);
			$context = stream_context_create($opts);
		}
		if($service == 'yahoo'){
			if($cookies){
				$this->page_content = file_get_contents($this->_yahoo_signup_page_url, false, $context);
			}
			else{
				$this->page_content = file_get_contents($this->_yahoo_signup_page_url);
			}
		}
		else if($service == 'hotmail'){
			if($cookies){
				$this->page_content = file_get_contents($this->_hotmail_signin_page_url, false, $context);
			}
			else{
				$this->page_content = file_get_contents($this->_hotmail_signin_page_url);
			}
		}
		
		if($this->page_content === false){
			$this->debug[] = 'Could not read the sign up page.';
			$this->add_error('200', 'Cannot not load the sign up page.');
		}
		else{
			$this->debug[] = 'Sign up page content stored.';
			$this->debug[] = 'Getting headers...';
			$this->page_headers = $http_response_header;
			$this->debug[] = 'Sign up page headers stored.';
		}
	}
	
	private function get_cookies(){
		$this->debug[] = 'Attempting to get the cookies from the sign up page...';
		if($this->page_content !== false){
			$this->debug[] = 'Extracting cookies from headers...';
			$cookies = array();
			foreach ($this->page_headers as $hdr) {
				if (preg_match('/^Set-Cookie:\s*(.*?;).*?$/i', $hdr, $matches)) {
					$cookies[] = $matches[1];
				}
			}
			
			if(count($cookies) > 0){
				$this->debug[] = 'Cookies found: '.implode(' ', $cookies);
				return $cookies;
			}
			else{
				$this->debug[] = 'Could not find any cookies.';
			}
		}
		return false;
	}
	
	private function get_fields(){
		$dom = new DOMDocument();
		$fields = array();
		if(@$dom->loadHTML($this->page_content)){
			$this->debug[] = 'Parsing the page for input fields...';
			$xp = new DOMXpath($dom);
			$nodes = $xp->query('//input');
			foreach($nodes as $node){
				$fields[$node->getAttribute('name')] = $node->getAttribute('value');
			}
			
			$this->debug[] = 'Extracted fields.';
		}
		else{
			$this->debug[] = 'Something is worng with the page HTML.';
			$this->add_error('210', 'Could not load the dom HTML.');
		}
		return $fields;
	}
	
	private function request_validation($service, $cookies, $fields){
		if($service == 'yahoo'){
			$headers = array();
			$headers[] = 'Origin: https://login.yahoo.com';
			$headers[] = 'X-Requested-With: XMLHttpRequest';
			$headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36';
			$headers[] = 'content-type: application/x-www-form-urlencoded; charset=UTF-8';
			$headers[] = 'Accept: */*';
			$headers[] = 'Referer: https://login.yahoo.com/account/create?specId=yidReg&lang=en-US&src=&done=https%3A%2F%2Fwww.yahoo.com&display=login';
			$headers[] = 'Accept-Encoding: gzip, deflate, br';
			$headers[] = 'Accept-Language: en-US,en;q=0.8,ar;q=0.6';
			
			$cookies_str = implode(' ', $cookies);
			$headers[] = 'Cookie: '.$cookies_str;
			
			
			$postdata = http_build_query($fields);
			
			$opts = array('http' =>
					array(
							'method'  => 'POST',
							'header'  => $headers,
							'content' => $postdata
					)
			);
			
			$context  = stream_context_create($opts);
			$result = file_get_contents($this->_yahoo_signup_ajax_url, false, $context);
		}
		else if($service == 'hotmail'){
			$headers = array();
			$headers[] = 'Origin: https://login.live.com';
			$headers[] = 'hpgid: 33';
			$headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36';
			$headers[] = 'Content-type: application/json; charset=UTF-8';
			$headers[] = 'Accept: application/json';
			$headers[] = 'Referer: https://login.live.com';
			$headers[] = 'Accept-Encoding: gzip, deflate, br';
			$headers[] = 'Accept-Language: en-US,en;q=0.8,ar;q=0.6';
			
			$cookies_str = implode(' ', $cookies);
			$headers[] = 'Cookie: '.$cookies_str;
			
			$postdata = json_encode($fields);
			
			$opts = array('http' =>
					array(
							'method'  => 'POST',
							'header'  => $headers,
							'content' => $postdata
					)
			);
			
			$context  = stream_context_create($opts);
			$result = file_get_contents($this->_hotmail_username_check_url, false, $context);
		}
		return $result;
	}
	
	private function prep_hotmail_fields($cookies){
		$fields = array();
		foreach($cookies as $cookie){
			list($key, $val) = explode('=', $cookie, 2);
			if($key == 'uaid'){
				$fields['uaid'] = $val;
				break;
			}
		}
		$fields['username'] = strtolower($this->email);
		return $fields;
	}
	
}

/**
 * Class to validate the email address
 *
 * @author CodexWorld.com <contact@codexworld.com>
 * @copyright Copyright (c) 2018, CodexWorld.com
 *            @url https://www.codexworld.com
 */
class VerifyEmail {
	protected $stream = false;
	
	/**
	 * SMTP port number
	 * 
	 * @var int
	 */
	protected $port = 25;
	
	/**
	 * Email address for request
	 * 
	 * @var string
	 */
	protected $from = 'root@localhost';
	
	/**
	 * The connection timeout, in seconds.
	 * 
	 * @var int
	 */
	protected $max_connection_timeout = 30;
	
	/**
	 * Timeout value on stream, in seconds.
	 * 
	 * @var int
	 */
	protected $stream_timeout = 5;
	
	/**
	 * Wait timeout on stream, in seconds.
	 * * 0 - not wait
	 * 
	 * @var int
	 */
	protected $stream_timeout_wait = 0;
	
	/**
	 * Whether to throw exceptions for errors.
	 * @type boolean
	 * 
	 * @access protected
	 */
	protected $exceptions = false;
	
	/**
	 * The number of errors encountered.
	 * @type integer
	 * 
	 * @access protected
	 */
	protected $error_count = 0;
	
	/**
	 * class debug output mode.
	 * @type boolean
	 */
	public $Debug = false;
	
	/**
	 * How to handle debug output.
	 * Options:
	 * * `echo` Output plain-text as-is, appropriate for CLI
	 * * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
	 * * `log` Output to error log as configured in php.ini
	 * @type string
	 */
	public $Debugoutput = 'echo';
	
	/**
	 * SMTP RFC standard line ending.
	 */
	const CRLF = "\r\n";
	
	/**
	 * Holds the most recent error message.
	 * @type string
	 */
	public $ErrorInfo = '';

	/**
	 * Constructor.
	 * 
	 * @param boolean $exceptions
	 *        	Should we throw external exceptions?
	 */
	public function __construct( $exceptions = false ) {
		$this->exceptions = ( boolean ) $exceptions;
	}

	/**
	 * Set email address for SMTP request
	 * 
	 * @param string $email
	 *        	Email address
	 */
	public function setEmailFrom( $email ) {
		if (! self::validate( $email )) {
			$this->set_error( 'Invalid address : ' . $email );
			$this->edebug( $this->ErrorInfo );
			if ($this->exceptions) {
				throw new verifyEmailException( $this->ErrorInfo );
			}
		}
		$this->from = $email;
	}

	/**
	 * Set connection timeout, in seconds.
	 * 
	 * @param int $seconds        	
	 */
	public function setConnectionTimeout( $seconds ) {
		if ($seconds > 0) {
			$this->max_connection_timeout = ( int ) $seconds;
		}
	}

	/**
	 * Sets the timeout value on stream, expressed in the seconds
	 * 
	 * @param int $seconds        	
	 */
	public function setStreamTimeout( $seconds ) {
		if ($seconds > 0) {
			$this->stream_timeout = ( int ) $seconds;
		}
	}

	public function setStreamTimeoutWait( $seconds ) {
		if ($seconds >= 0) {
			$this->stream_timeout_wait = ( int ) $seconds;
		}
	}

	/**
	 * Validate email address.
	 * 
	 * @param string $email        	
	 * @return boolean True if valid.
	 */
	public static function validate( $email ) {
		return ( boolean ) filter_var( $email, FILTER_VALIDATE_EMAIL );
	}

	/**
	 * Get array of MX records for host.
	 * Sort by weight information.
	 * 
	 * @param string $hostname
	 *        	The Internet host name.
	 * @return array Array of the MX records found.
	 */
	public function getMXrecords( $hostname ) {
		$mxhosts = array ();
		$mxweights = array ();
		if (getmxrr( $hostname, $mxhosts, $mxweights ) === FALSE) {
			$this->set_error( 'MX records not found or an error occurred' );
			$this->edebug( $this->ErrorInfo );
		} else {
			array_multisort( $mxweights, $mxhosts );
		}
		/**
		 * Add A-record as last chance (e.g.
		 * if no MX record is there).
		 * Thanks Nicht Lieb.
		 * 
		 * @link http://www.faqs.org/rfcs/rfc2821.html RFC 2821 - Simple Mail Transfer Protocol
		 */
		if (empty( $mxhosts )) {
			$mxhosts [] = $hostname;
		}
		return $mxhosts;
	}

	/**
	 * Parses input string to array(0=>user, 1=>domain)
	 * 
	 * @param string $email        	
	 * @param boolean $only_domain        	
	 * @return string|array
	 * @access private
	 */
	public static function parse_email( $email, $only_domain = TRUE ) {
		sscanf( $email, "%[^@]@%s", $user, $domain );
		return ($only_domain) ? $domain : array (
				$user,
				$domain 
		);
	}

	/**
	 * Add an error message to the error container.
	 * 
	 * @access protected
	 * @param string $msg        	
	 * @return void
	 */
	protected function set_error( $msg ) {
		$this->error_count ++;
		$this->ErrorInfo = $msg;
	}

	/**
	 * Check if an error occurred.
	 * 
	 * @access public
	 * @return boolean True if an error did occur.
	 */
	public function isError( ) {
		return ($this->error_count > 0);
	}

	/**
	 * Output debugging info
	 * Only generates output if debug output is enabled
	 * 
	 * @see verifyEmail::$Debugoutput
	 * @see verifyEmail::$Debug
	 * @param string $str        	
	 */
	protected function edebug( $str ) {
		if (! $this->Debug) {
			return;
		}
		switch ($this->Debugoutput) {
			case 'log' :
				// Don't output, just log
				error_log( $str );
				break;
			case 'html' :
				// Cleans up output a bit for a better looking, HTML-safe output
				echo htmlentities( preg_replace( '/[\r\n]+/', '', $str ), ENT_QUOTES, 'UTF-8' ) . "<br>\n";
				break;
			case 'echo' :
			default :
				// Normalize line breaks
				$str = preg_replace( '/(\r\n|\r|\n)/ms', "\n", $str );
				echo gmdate( 'Y-m-d H:i:s' ) . "\t" . str_replace( "\n", "\n \t ", trim( $str ) ) . "\n";
		}
	}

	/**
	 * Validate email
	 * 
	 * @param string $email
	 *        	Email address
	 * @return boolean True if the valid email also exist
	 */
	public function check( $email, $complete = true ) {
		$result = FALSE;
		
		if (! self::validate( $email )) {
			$this->set_error( "{$email} incorrect e-mail" );
			$this->edebug( $this->ErrorInfo );
			if ($this->exceptions) {
				throw new verifyEmailException( $this->ErrorInfo );
			}
			return FALSE;
		}
		$this->error_count = 0; // Reset errors
		$this->stream = FALSE;
		
		$mxs = $this->getMXrecords( self::parse_email( $email ) );
		$timeout = ceil( $this->max_connection_timeout / count( $mxs ) );
		foreach ( $mxs as $host ) {
			/**
			 * suppress error output from stream socket client...
			 * Thanks Michael.
			 */
			$this->stream = @stream_socket_client( "tcp://" . $host . ":" . $this->port, $errno, $errstr, $timeout );
			// Prova su porta 465
			if ($this->stream === FALSE) {
				$this->stream = @stream_socket_client( "tcp://" . $host . ":" . 465, $errno, $errstr, $timeout );
			}
			if ($this->stream === FALSE) {
				if ($errno == 0) {
					$this->set_error( "Problem initializing the socket" );
					$this->edebug( $this->ErrorInfo );
					if ($this->exceptions) {
						throw new verifyEmailException( $this->ErrorInfo );
					}
					return FALSE;
				} else {
					$this->edebug( $host . ":" . $errstr );
				}
			} else {
				stream_set_timeout( $this->stream, $this->stream_timeout );
				stream_set_blocking( $this->stream, 1 );
				
				if ($this->_streamCode( $this->_streamResponse() ) == '220') {
					$this->edebug( "Connection success {$host}" );
					break;
				} else {
					fclose( $this->stream );
					$this->stream = FALSE;
				}
			}
		}
		
		if ($this->stream === FALSE) {
			$this->set_error( "All connection fails" );
			$this->edebug( $this->ErrorInfo );
			if ($this->exceptions) {
				throw new verifyEmailException( $this->ErrorInfo );
			}
			return FALSE;
		}
		if (!$complete){
			fclose( $this->stream );
			return true;
		}
		$this->_streamQuery( "HELO " . self::parse_email( $this->from ) );
		$this->_streamResponse();
		$this->_streamQuery( "MAIL FROM: <{$this->from}>" );
		$this->_streamResponse();
		$this->_streamQuery( "RCPT TO: <{$email}>" );
		$code = $this->_streamCode( $this->_streamResponse() );
		$this->_streamResponse();
		$this->_streamQuery( "RSET" );
		$this->_streamResponse();
		$code2 = $this->_streamCode( $this->_streamResponse() );
		$this->_streamQuery( "QUIT" );
		fclose( $this->stream );
		
		$code = ! empty( $code2 ) ? $code2 : $code;
		switch ($code) {
			case '250' :
			/**
			 * http://www.ietf.org/rfc/rfc0821.txt
			 * 250 Requested mail action okay, completed
			 * email address was accepted
			 */
			case '450' :
			case '451' :
			case '452' :
				/**
				 * http://www.ietf.org/rfc/rfc0821.txt
				 * 450 Requested action not taken: the remote mail server
				 * does not want to accept mail from your server for
				 * some reason (IP address, blacklisting, etc..)
				 * Thanks Nicht Lieb.
				 * 451 Requested action aborted: local error in processing
				 * 452 Requested action not taken: insufficient system storage
				 * email address was greylisted (or some temporary error occured on the MTA)
				 * i believe that e-mail exists
				 */
				return TRUE;
			case '550' :
				return FALSE;
			default :
				return FALSE;
		}
	}

	/**
	 * writes the contents of string to the file stream pointed to by handle
	 * If an error occurs, returns FALSE.
	 * 
	 * @access protected
	 * @param string $string
	 *        	The string that is to be written
	 * @return string Returns a result code, as an integer.
	 */
	protected function _streamQuery( $query ) {
		$this->edebug( $query );
		return stream_socket_sendto( $this->stream, $query . self::CRLF );
	}

	/**
	 * Reads all the line long the answer and analyze it.
	 * If an error occurs, returns FALSE
	 * 
	 * @access protected
	 * @return string Response
	 */
	protected function _streamResponse( $timed = 0 ) {
		$reply = stream_get_line( $this->stream, 1 );
		$status = stream_get_meta_data( $this->stream );
		
		if (! empty( $status ['timed_out'] )) {
			$this->edebug( "Timed out while waiting for data! (timeout {$this->stream_timeout} seconds)" );
		}
		
		if ($reply === FALSE && $status ['timed_out'] && $timed < $this->stream_timeout_wait) {
			return $this->_streamResponse( $timed + $this->stream_timeout );
		}
		
		if ($reply !== FALSE && $status ['unread_bytes'] > 0) {
			$reply .= stream_get_line( $this->stream, $status ['unread_bytes'], self::CRLF );
		}
		$this->edebug( $reply );
		return $reply;
	}

	/**
	 * Get Response code from Response
	 * 
	 * @param string $str        	
	 * @return string
	 */
	protected function _streamCode( $str ) {
		preg_match( '/^(?<code>[0-9]{3})(\s|-)(.*)$/ims', $str, $matches );
		$code = isset( $matches ['code'] ) ? $matches ['code'] : false;
		return $code;
	}
}

/**
 * verifyEmail exception handler
 */
class verifyEmailException extends Exception {

	/**
	 * Prettify error message output
	 * 
	 * @return string
	 */
	public function errorMessage( ) {
		$errorMsg = $this->getMessage();
		return $errorMsg;
	}
}


/* End of file email_helper.php */
/* Location: ./system/helpers/email_helper.php */