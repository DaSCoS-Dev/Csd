<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/general/controllers.html
 */
class CI_Controller {

	private static $instance;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		self::$instance =& $this;
		
		// Assign all the class objects that were instantiated by the
		// bootstrap file (CodeIgniter.php) to local class variables
		// so that CI can run as one big super object.
		foreach (is_loaded() as $var => $class)
		{
			$this->$var =& load_class($class);
		}

		$this->load =& load_class('Loader', 'core');

		$this->load->initialize();
		//$this->build_dependency();
		log_message('debug', "Controller Class Initialized");
	}

	public static function &get_instance()
	{
		return self::$instance;
	}
	
	protected function build_dependency( $ref = null ) {
		if (is_null( $ref )) {
			$this->ci = get_instance();
		} else {
			$this->ci = $ref;
		}
		foreach ( get_object_vars( $this->ci ) as $_ci_key => $_ci_var ) {
			if (! isset( $this->$_ci_key )) {
				$this->$_ci_key = &$this->ci->$_ci_key;
			}
		}
		$this->rebuild_session();
	}
	
	protected function is_live(){
		return ($this->ci->config->config ["is_live"]);
	}
	
	protected function rebuild_session(){
		$args = func_get_args();
		$lib = $args [0];
		$method = $args [1];
		if ($lib != null){
			$this->session->set_userdata( "lib_attuale", $lib );
		}
		if ($method != null){
			$this->session->set_userdata( "metodo_attuale", $method );
		}
	}
	
	protected function converti_campi_data( &$records, $custom = "" ) {
		foreach ( $records as $record ) {
			foreach ( $record as $campo => $valore ) {
				if (stripos( $campo, "data" ) !== false) {
					$date_format = "DATE_EURO_SHORT";
					// Custom?
					if (trim( $custom ) !== "") {
						$date_format = $custom;
					}
					// Se la data (trasformata in intero) non e' zero, e' una data effettiva
					if (intval( $valore ) !== 0) {
						$record->$campo = standard_date( $date_format, $valore );
					} else {
						$record->$campo = "";
					}
					$campi_data [ ] = $campo;
				}
			}
			$record->campi_data = $campi_data;
		}
	}
	
	public function getClientIP( ) {
		if (array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER )) {
			return $_SERVER [ "HTTP_X_FORWARDED_FOR" ];
		} else if (array_key_exists( 'REMOTE_ADDR', $_SERVER )) {
			return $_SERVER [ "REMOTE_ADDR" ];
		} else if (array_key_exists( 'HTTP_CLIENT_IP', $_SERVER )) {
			return $_SERVER [ "HTTP_CLIENT_IP" ];
		}
		return '';
	}
	
	public function load_class( $class_name ) {
		require_once ( "{$_SERVER['DOCUMENT_ROOT']}/application/classes/{$class_name}.php" );
		if (! is_a( $this, "super_lib" ) and is_a($this->super_lib, "super_lib")) {
			return new $class_name( $this->super_lib );
		} else {
			return new $class_name( $this );
		}
	}
	
}
// END Controller class

/* End of file Controller.php */
/* Location: ./system/core/Controller.php */