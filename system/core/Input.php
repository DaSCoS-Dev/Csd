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
 * Input Class
 *
 * Pre-processes global input data for security
 *
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Input
 * @author ExpressionEngine Dev Team
 * @link http://codeigniter.com/user_guide/libraries/input.html
 */
class CI_Input {
	
	/**
	 * IP address of the current user
	 *
	 * @var string
	 */
	var $ip_address = FALSE;
	/**
	 * user agent (web browser) being used by the current user
	 *
	 * @var string
	 */
	var $user_agent = FALSE;
	/**
	 * If FALSE, then $_GET will be set to an empty array
	 *
	 * @var bool
	 */
	var $_allow_get_array = TRUE;
	/**
	 * If TRUE, then newlines are standardized
	 *
	 * @var bool
	 */
	var $_standardize_newlines = TRUE;
	/**
	 * Determines whether the XSS filter is always active when GET, POST or COOKIE data is encountered
	 * Set automatically based on config setting
	 *
	 * @var bool
	 */
	var $_enable_xss = FALSE;
	/**
	 * Enables a CSRF cookie token to be set.
	 * Set automatically based on config setting
	 *
	 * @var bool
	 */
	var $_enable_csrf = FALSE;
	/**
	 * List of all HTTP request headers
	 *
	 * @var array
	 */
	protected $headers = array ();

	/**
	 * Constructor
	 *
	 * Sets whether to globally enable the XSS processing
	 * and whether to allow the $_GET array
	 */
	public function __construct( ) {
		log_message( 'debug', "Input Class Initialized" );
		
		$this->_allow_get_array = (config_item( 'allow_get_array' ) === TRUE);
		$this->_enable_xss = (config_item( 'global_xss_filtering' ) === TRUE);
		$this->_enable_csrf = (config_item( 'csrf_protection' ) === TRUE);
		
		global $SEC;
		$this->security =  $SEC;
		
		// Do we need the UTF-8 class?
		if (UTF8_ENABLED === TRUE) {
			global $UNI;
			$this->uni =  $UNI;
		}
		
		// Sanitize global arrays
		$this->_sanitize_globals();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Fetch from array
	 *
	 * This is a helper function to retrieve values from global arrays
	 *
	 * @access private
	 * @param
	 *        	array
	 * @param
	 *        	string
	 * @param
	 *        	bool
	 * @return string
	 */
	function _fetch_from_array( &$array, $index = null, $xss_clean = FALSE ) {
		// If $index is NULL, it means that the whole $array is requested
		isset( $index ) or $index = array_keys( $array );
		
		// allow fetching multiple keys at once
		if (is_array( $index )) {
			$output = array ();
			foreach ( $index as $key ) {
				$output [$key] = $this->_fetch_from_array( $array, $key, $xss_clean );
			}
			
			return $output;
		}
		
		if (isset( $array [$index] )) {
			$value = $array [$index];
		} elseif (($count = preg_match_all( '/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches )) > 1) // Does the index contain array notation
{
			$value = $array;
			for($i = 0; $i < $count; $i ++) {
				$key = trim( $matches [0] [$i], '[]' );
				if ($key === '') // Empty notation will return the value as array
{
					break;
				}
				
				if (isset( $value [$key] )) {
					$value = $value [$key];
				} else {
					return NULL;
				}
			}
		} else {
			return NULL;
		}
		
		return ($xss_clean === TRUE) ? $this->security->xss_clean( $value ) : $value;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Fetch an item from the GET array
	 *
	 * @access public
	 * @param
	 *        	string
	 * @param
	 *        	bool
	 * @return string
	 */
	function get( $index = NULL, $xss_clean = FALSE ) {
		return $this->_fetch_from_array( $_GET, $index, $xss_clean );
	}

	// --------------------------------------------------------------------
	
	/**
	 * Fetch an item from the POST array
	 *
	 * @access public
	 * @param
	 *        	string
	 * @param
	 *        	bool
	 * @return string
	 */
	function post( $index = NULL, $xss_clean = FALSE ) {
		return $this->_fetch_from_array( $_POST, $index, $xss_clean );
	}

	public function post_get( $index, $xss_clean = FALSE ) {
		$output = $this->post( $index, $xss_clean );
		return isset( $output ) ? $output : $this->get( $index, $xss_clean );
	}

	// --------------------------------------------------------------------
	
	/**
	 * Fetch an item from either the GET array or the POST
	 *
	 * @access public
	 * @param
	 *        	string The index key
	 * @param
	 *        	bool XSS cleaning
	 * @return string
	 */
	function get_post( $index = '', $xss_clean = FALSE ) {
		$output = $this->get( $index, $xss_clean );
		return isset( $output ) ? $output : $this->post( $index, $xss_clean );
	}

	// --------------------------------------------------------------------
	
	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @access public
	 * @param
	 *        	string
	 * @param
	 *        	bool
	 * @return string
	 */
	function cookie( $index = '', $xss_clean = FALSE ) {
		return $this->_fetch_from_array( $_COOKIE, $index, $xss_clean );
	}

	// ------------------------------------------------------------------------
	
	/**
	 * Set cookie
	 *
	 * Accepts an arbitrary number of parameters (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param string|mixed[] $name
	 *        	Cookie name or an array containing parameters
	 * @param string $value
	 *        	Cookie value
	 * @param int $expire
	 *        	Cookie expiration time in seconds
	 * @param string $domain
	 *        	Cookie domain (e.g.: '.yourdomain.com')
	 * @param string $path
	 *        	Cookie path (default: '/')
	 * @param string $prefix
	 *        	Cookie name prefix
	 * @param bool $secure
	 *        	Whether to only transfer cookies via SSL
	 * @param bool $httponly
	 *        	Whether to only makes the cookie accessible via HTTP (no javascript)
	 * @return void
	 */
	public function set_cookie( $name, $value = '', $expire = 0, $domain = '', $path = '/', $prefix = '', $secure = NULL, $httponly = NULL ) {
		if (is_array( $name )) {
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			foreach ( array (
					'value',
					'expire',
					'domain',
					'path',
					'prefix',
					'secure',
					'httponly',
					'name' 
			) as $item ) {
				if (isset( $name [$item] )) {
					$$item = $name [$item];
				}
			}
		}
		
		if ($prefix === '' && config_item( 'cookie_prefix' ) !== '') {
			$prefix = config_item( 'cookie_prefix' );
		}
		
		if ($domain == '' && config_item( 'cookie_domain' ) != '') {
			$domain = config_item( 'cookie_domain' );
		}
		
		if ($path === '/' && config_item( 'cookie_path' ) !== '/') {
			$path = config_item( 'cookie_path' );
		}
		
		$secure = ($secure === NULL && config_item( 'cookie_secure' ) !== NULL) ? ( bool ) config_item( 'cookie_secure' ) : ( bool ) $secure;
		
		$httponly = ($httponly === NULL && config_item( 'cookie_httponly' ) !== NULL) ? ( bool ) config_item( 'cookie_httponly' ) : ( bool ) $httponly;
		
		if (! is_numeric( $expire ) or $expire < 0) {
			$expire = 1;
		} else {
			$expire = ($expire > 0) ? time() + $expire : 0;
		}
		
		setcookie( $prefix . $name, $value, $expire, $path, $domain, $secure, $httponly );
	}

	// --------------------------------------------------------------------
	
	/**
	 * Fetch an item from the SERVER array
	 *
	 * @access public
	 * @param
	 *        	string
	 * @param
	 *        	bool
	 * @return string
	 */
	function server( $index = '', $xss_clean = FALSE ) {
		return $this->_fetch_from_array( $_SERVER, $index, $xss_clean );
	}

	public function method( $upper = FALSE ) {
		return ($upper) ? strtoupper( $this->server( 'REQUEST_METHOD' ) ) : strtolower( $this->server( 'REQUEST_METHOD' ) );
	}

	// --------------------------------------------------------------------
	
	final public function getClientIP( ) {
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
	 * Fetch the IP Address
	 *
	 * @access public
	 * @return string
	 */
	public function ip_address( ) {
		if ($this->ip_address !== FALSE) {
			return $this->ip_address;
		}
		
		$proxy_ips = config_item( 'proxy_ips' );
		if (! empty( $proxy_ips ) && ! is_array( $proxy_ips )) {
			$proxy_ips = explode( ',', str_replace( ' ', '', $proxy_ips ) );
		}
		
		$this->ip_address = $this->getClientIP();
		//$this->server( 'REMOTE_ADDR' );
		
		if ($proxy_ips) {
			foreach ( array (
					'HTTP_X_FORWARDED_FOR',
					'HTTP_CLIENT_IP',
					'HTTP_X_CLIENT_IP',
					'HTTP_X_CLUSTER_CLIENT_IP' 
			) as $header ) {
				if (($spoof = $this->server( $header )) !== NULL) {
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					sscanf( $spoof, '%[^,]', $spoof );
					
					if (! $this->valid_ip( $spoof )) {
						$spoof = NULL;
					} else {
						break;
					}
				}
			}
			
			if ($spoof) {
				for($i = 0, $c = count( $proxy_ips ); $i < $c; $i ++) {
					// Check if we have an IP address or a subnet
					if (strpos( $proxy_ips [$i], '/' ) === FALSE) {
						// An IP address (and not a subnet) is specified.
						// We can compare right away.
						if ($proxy_ips [$i] === $this->ip_address) {
							$this->ip_address = $spoof;
							break;
						}
						
						continue;
					}
					
					// We have a subnet ... now the heavy lifting begins
					isset( $separator ) or $separator = $this->valid_ip( $this->ip_address, 'ipv6' ) ? ':' : '.';
					
					// If the proxy entry doesn't match the IP protocol - skip it
					if (strpos( $proxy_ips [$i], $separator ) === FALSE) {
						continue;
					}
					
					// Convert the REMOTE_ADDR IP address to binary, if needed
					if (! isset( $ip, $sprintf )) {
						if ($separator === ':') {
							// Make sure we're have the "full" IPv6 format
							$ip = explode( ':', str_replace( '::', str_repeat( ':', 9 - substr_count( $this->ip_address, ':' ) ), $this->ip_address ) );
							
							for($j = 0; $j < 8; $j ++) {
								$ip [$j] = intval( $ip [$j], 16 );
							}
							
							$sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
						} else {
							$ip = explode( '.', $this->ip_address );
							$sprintf = '%08b%08b%08b%08b';
						}
						
						$ip = vsprintf( $sprintf, $ip );
					}
					
					// Split the netmask length off the network address
					sscanf( $proxy_ips [$i], '%[^/]/%d', $netaddr, $masklen );
					
					// Again, an IPv6 address is most likely in a compressed form
					if ($separator === ':') {
						$netaddr = explode( ':', str_replace( '::', str_repeat( ':', 9 - substr_count( $netaddr, ':' ) ), $netaddr ) );
						for($j = 0; $j < 8; $j ++) {
							$netaddr [$j] = intval( $netaddr [$j], 16 );
						}
					} else {
						$netaddr = explode( '.', $netaddr );
					}
					
					// Convert to binary and finally compare
					if (strncmp( $ip, vsprintf( $sprintf, $netaddr ), $masklen ) === 0) {
						$this->ip_address = $spoof;
						break;
					}
				}
			}
		}
		
		if (! $this->valid_ip( $this->ip_address )) {
			return $this->ip_address = '0.0.0.0';
		}
		
		return $this->ip_address;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Validate IP Address
	 *
	 * Updated version suggested by Geert De Deckere
	 *
	 * @access public
	 * @param
	 *        	string
	 * @return string
	 */
	public function valid_ip( $ip, $which = '' ) {
		switch (strtolower( $which )) {
			case 'ipv4' :
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6' :
				$which = FILTER_FLAG_IPV6;
				break;
			default :
				$which = NULL;
				break;
		}
		
		return ( bool ) filter_var( $ip, FILTER_VALIDATE_IP, $which );
	}

	// --------------------------------------------------------------------
	
	/**
	 * User Agent
	 *
	 * @access public
	 * @return string
	 */
	function user_agent($xss_clean = FALSE ) {
		return $this->_fetch_from_array( $_SERVER, 'HTTP_USER_AGENT', $xss_clean );
	}

	// --------------------------------------------------------------------
	
	/**
	 * Sanitize Globals
	 *
	 * This function does the following:
	 *
	 * Unsets $_GET data (if query strings are not enabled)
	 *
	 * Unsets all globals if register_globals is enabled
	 *
	 * Standardizes newline characters to \n
	 *
	 * @access private
	 * @return void
	 */
	function _sanitize_globals( ) {
		// It would be "wrong" to unset any of these GLOBALS.
		$protected = array (
				'_SERVER',
				'_GET',
				'_POST',
				'_FILES',
				'_REQUEST',
				'_SESSION',
				'_ENV',
				'GLOBALS',
				'HTTP_RAW_POST_DATA',
				'system_folder',
				'application_folder',
				'BM',
				'EXT',
				'CFG',
				'URI',
				'RTR',
				'OUT',
				'IN' 
		);
		
		// Unset globals for securiy.
		// This is effectively the same as register_globals = off
		foreach ( array (
				$_GET,
				$_POST,
				$_COOKIE 
		) as $global ) {
			if (! is_array( $global )) {
				if (! in_array( $global, $protected )) {
					global $$global;
					$$global = NULL;
				}
			} else {
				foreach ( $global as $key => $val ) {
					if (! in_array( $key, $protected )) {
						global $$key;
						$$key = NULL;
					}
				}
			}
		}
		
		// Is $_GET data allowed? If not we'll set the $_GET to an empty array
		if ($this->_allow_get_array == FALSE) {
			$_GET = array ();
		} else {
			if (is_array( $_GET ) and count( $_GET ) > 0) {
				foreach ( $_GET as $key => $val ) {
					$_GET [$this->_clean_input_keys( $key )] = $this->_clean_input_data( $val );
				}
			}
		}
		
		// Clean $_POST Data
		if (is_array( $_POST ) and count( $_POST ) > 0) {
			foreach ( $_POST as $key => $val ) {
				// DASCOS
				// Se e' xajax args, NON sanitizzo
				if ($key !== "xjxargs") {
					$_POST [$this->_clean_input_keys( $key )] = $this->_clean_input_data( $val );
				}
			}
		}
		
		// Clean $_COOKIE Data
		if (is_array( $_COOKIE ) and count( $_COOKIE ) > 0) {
			// Also get rid of specially treated cookies that might be set by a server
			// or silly application, that are of no use to a CI application anyway
			// but that when present will trip our 'Disallowed Key Characters' alarm
			// http://www.ietf.org/rfc/rfc2109.txt
			// note that the key names below are single quoted strings, and are not PHP variables
			unset( $_COOKIE ['$Version'] );
			unset( $_COOKIE ['$Path'] );
			unset( $_COOKIE ['$Domain'] );
			
			foreach ( $_COOKIE as $key => $val ) {
				$_COOKIE [$this->_clean_input_keys( $key )] = $this->_clean_input_data( $val );
			}
		}
		
		// Sanitize PHP_SELF
		$_SERVER ['PHP_SELF'] = strip_tags( $_SERVER ['PHP_SELF'] );
		
		// CSRF Protection check
		if ($this->_enable_csrf == TRUE) {
			$this->security->csrf_verify();
		}
		
		log_message( 'debug', "Global POST and COOKIE data sanitized" );
	}

	// --------------------------------------------------------------------
	
	/**
	 * Clean Input Data
	 *
	 * This is a helper function. It escapes data and
	 * standardizes newline characters to \n
	 *
	 * @access private
	 * @param
	 *        	string
	 * @return string
	 */
	function _clean_input_data( $str ) {
		if (is_array( $str )) {
			$new_array = array ();
			foreach ( $str as $key => $val ) {
				$new_array [$this->_clean_input_keys( $key )] = $this->_clean_input_data( $val );
			}
			return $new_array;
		}
		
		/*
		 * We strip slashes if magic quotes is on to keep things consistent
		 * NOTE: In PHP 5.4 get_magic_quotes_gpc() will always return 0 and
		 * it will probably not exist in future versions at all.
		 */
		if (! is_php( '5.4' ) && get_magic_quotes_gpc()) {
			$str = stripslashes( $str );
		}
		
		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE) {
			$str = $this->uni->clean_string( $str );
		}
		
		// Remove control characters
		$str = remove_invisible_characters( $str );
		
		// Should we filter the input data?
		if ($this->_enable_xss === TRUE) {
			$str = $this->security->xss_clean( $str );
		}
		
		// Standardize newlines if needed
		if ($this->_standardize_newlines == TRUE) {
			if (strpos( $str, "\r" ) !== FALSE) {
				$str = str_replace( array (
						"\r\n",
						"\r",
						"\r\n\n" 
				), PHP_EOL, $str );
			}
		}
		
		return $str;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Clean Keys
	 *
	 * This is a helper function. To prevent malicious users
	 * from trying to exploit keys we make sure that keys are
	 * only named with alpha-numeric text and a few other items.
	 *
	 * @access private
	 * @param
	 *        	string
	 * @return string
	 */
	function _clean_input_keys( $str ) {
		if (! preg_match( "/^[a-z0-9:\'_\/ -]+$/i", $str )) {
			exit( 'Disallowed Key Characters.' );
		}
		
		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE) {
			$str = $this->uni->clean_string( $str );
		}
		
		return $str;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Request Headers
	 *
	 * In Apache, you can simply call apache_request_headers(), however for
	 * people running other webservers the function is undefined.
	 *
	 * @param
	 *        	bool XSS cleaning
	 *        	
	 * @return array
	 */
	public function request_headers( $xss_clean = FALSE ) {
		// If header is already defined, return it immediately
		if (! empty( $this->headers )) {
			return $this->_fetch_from_array( $this->headers, NULL, $xss_clean );
		}
		
		// In Apache, you can simply call apache_request_headers()
		if (function_exists( 'apache_request_headers' )) {
			$this->headers = apache_request_headers();
		} else {
			isset( $_SERVER ['CONTENT_TYPE'] ) && $this->headers ['Content-Type'] = $_SERVER ['CONTENT_TYPE'];
			
			foreach ( $_SERVER as $key => $val ) {
				if (sscanf( $key, 'HTTP_%s', $header ) === 1) {
					// take SOME_HEADER and turn it into Some-Header
					$header = str_replace( '_', ' ', strtolower( $header ) );
					$header = str_replace( ' ', '-', ucwords( $header ) );
					
					$this->headers [$header] = $_SERVER [$key];
				}
			}
		}
		
		return $this->_fetch_from_array( $this->headers, NULL, $xss_clean );
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get Request Header
	 *
	 * Returns the value of a single member of the headers class member
	 *
	 * @param
	 *        	string array key for $this->headers
	 * @param
	 *        	boolean XSS Clean or not
	 * @return mixed FALSE on failure, string on success
	 */
	public function get_request_header( $index, $xss_clean = FALSE ) {
		static $headers;
		if (! isset( $headers )) {
			empty( $this->headers ) && $this->request_headers();
			foreach ( $this->headers as $key => $value ) {
				$headers [strtolower( $key )] = $value;
			}
		}
		$index = strtolower( $index );
		if (! isset( $headers [$index] )) {
			return NULL;
		}
		return ($xss_clean === TRUE) ? $this->security->xss_clean( $headers [$index] ) : $headers [$index];
	}

	/**
	 * Fetch an item from the php://input stream
	 *
	 * Useful when you need to access PUT, DELETE or PATCH request data.
	 *
	 * @param string $index
	 *        	Index for item to be fetched
	 * @param bool $xss_clean
	 *        	Whether to apply XSS filtering
	 * @return mixed
	 */
	public function input_stream( $index = NULL, $xss_clean = FALSE ) {
		// Prior to PHP 5.6, the input stream can only be read once,
		// so we'll need to check if we have already done that first.
		if (! is_array( $this->_input_stream )) {
			// $this->raw_input_stream will trigger __get().
			$this->_input_stream = array();
			parse_str( $this->raw_input_stream, $this->_input_stream );
			is_array( $this->_input_stream ) or $this->_input_stream = array ();
		}
		
		return $this->_fetch_from_array( $this->_input_stream, $index, $xss_clean );
	}

	// --------------------------------------------------------------------
	
	/**
	 * Is ajax Request?
	 *
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header
	 *
	 * @return boolean
	 */
	public function is_ajax_request( ) {
		return (! empty( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest');
	}

	/**
	 * Is SOAP request?
	 */
	public function get_soap_request( ) {
		return $this->get_request_header( "Soapaction", true );
	}

	// --------------------------------------------------------------------
	
	/**
	 * Is cli Request?
	 *
	 * Test to see if a request was made from the command line
	 *
	 * @return boolean
	 */
	public function is_cli_request( ) {
		return (php_sapi_name() == 'cli') or defined( 'STDIN' );
	}

	public function &__get( $name ) {
		if ($name === 'raw_input_stream') {
			isset( $this->_raw_input_stream ) or $this->_raw_input_stream = file_get_contents( 'php://input' );
			return $this->_raw_input_stream;
		} elseif ($name === 'ip_address') {
			return $this->ip_address;
		}
	}
}

/* End of file Input.php */
/* Location: ./system/core/Input.php */