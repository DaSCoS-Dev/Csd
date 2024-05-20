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
		// Pulizia doppi slash
		$this->root_dir = str_replace("//", "/", $this->root_dir);
		$this->initializeXajax();
		$this->buildXajax();
	}

	private function initializeXajax(){
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
		// .....
		//$this->xajax->objPluginManager->aConfigurable[99]->configure("deferScriptGeneration", false);
		//$this->xajax->objPluginManager->aConfigurable[99]->configure("scriptLoadTimeout", 20);
		
		$html = $this->xajax->printJavaScript("", [], $asHtml);
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