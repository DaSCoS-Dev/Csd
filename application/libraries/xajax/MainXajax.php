<?php
/**
 * This class manage the xajaxs
 *
 */
class mainXajax {
	/**
 * Variables and definitions
 *
 * @var class $xajax
 * @var string $form_name
 */
	public $xajax, $action, $library;
	private $ci, $root_dir, $core, $xajaxRootDir;

	public function __construct(){
		$this->ci =& get_instance();
		$this->inizializza("libraries");
	}

	private function inizializza($rootDir){
		$this->mainXajax($rootDir);
	}

	/**
 	* Initials steup, assignements and functions registration.
 	* $functionArray is an array of elements in the form type=>argument(s), pe. XAJAX_FUNCTION=>'getForm'
 	*
 	* @param Array $functionArray
 	* @param String $form_name
 	*/
	private function mainXajax($root_dir){
		$this->xajaxRootDir = APPPATH . "/{$root_dir}/xajax/";
		$this->root_dir = $GLOBALS["_SERVER"]["DOCUMENT_ROOT"] . "/" . APPPATH . $root_dir;
		$this->xajaxJsRootDir = "/assets/js/";
		//$this->root_dir = "{$GLOBALS["_SERVER"]["DOCUMENT_ROOT"]}/{$root_dir}";
		// Pulizia doppi slash
		$this->root_dir = str_replace("//", "/", $this->root_dir);
		//$this->ci =& get_instance();
		$this->initializeXajax();
		$this->buildXajax();
	}

	private function initializeXajax(){
		/*$cookie_name = $this->ci->session->sess_cookie_name;
		if (isset($_POST["{$cookie_name}"])) {
			session_id($_POST["{$cookie_name}"]);
		} elseif (isset($_COOKIE["{$cookie_name}"])){
			session_id($_COOKIE["{$cookie_name}"]);
		} 
		$sid = session_id();
		if (trim($sid) == "") {
			@session_start();
		}
		ini_set("display_errors", 0);
		error_reporting(0);*/
		$this->core = "xajax_core/";
		if (!class_exists('xajaxRequestPlugin')){
			require_once("{$this->xajaxRootDir}{$this->core}xajax.inc.php");
		}
	}

	private function buildXajax(){
		//if (!is_a($this->xajax, 'xajaxxajaxExtend')){
		if (!is_a($this->xajax, 'xajax')){
			$this->xajax = new xajax($this->ci->config->config["base_url"]);
			if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['xajax']))
			{
				$this->xajax->initProcessRequests();
			}
			$this->xajax->configure("statusMessages", true);
		}
		// La barra e' importantissima!!!!
		$this->xajax->configure("javascript URI",$this->xajaxJsRootDir);
		// Mo gli passo il "ci"
		$this->xajax->ci = $this->ci;
		$this->xajax->objPluginManager->ci = $this->ci;
	}

	public function printJavaScript($asHtml = false){
		$html = $this->xajax->printJavaScript("", "", $asHtml);
		return $html;
	}

	private function register($type, $functionName){
		return $this->xajax->register($type, $functionName);
	}

	public function registerFunction($functionName){
		return $this->register(XAJAX_FUNCTION, $functionName);
	}

	public function setFlag($flag, $value){
		$this->xajax->setFlag($flag, $value);
	}

	private function processRequest(){
		$this->xajax->processRequest();
	}

	public function process(){
		$this->processRequest();
	}
}
?>