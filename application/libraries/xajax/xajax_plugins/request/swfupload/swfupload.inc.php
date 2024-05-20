<?php
if (false == class_exists( 'xajaxPlugin' ) || false == class_exists( 'xajaxPluginManager' )) {
	$sBaseFolder = dirname( dirname( dirname( __FILE__ ) ) );
	$sXajaxCore = $sBaseFolder . '/xajax_core';
	if (false == class_exists( 'xajaxPlugin' ))
		require $sXajaxCore . '/xajaxPlugin.inc.php';
	if (false == class_exists( 'xajaxPluginManager' ))
		require $sXajaxCore . '/xajaxPluginManager.inc.php';
}
class clsSwfUpload extends xajaxResponsePlugin {
	// --------------------------------------------------------------------------------------------------------------------------------
	private $sCallName = "SWFUpload";
	private $sDefer;
	private $sJavascriptURI;
	private $bInlineScript;
	private $sRequestedFunction = NULL;
	private $sXajaxPrefix = "xajax_";
	// --------------------------------------------------------------------------------------------------------------------------------
	public function __construct( ) {
		$this->sDefer = '';
		$this->sJavascriptURI = '';
		$this->bInlineScript = false;
	}
	// --------------------------------------------------------------------------------------------------------------------------------
	function getName( ) {
		return get_class( $this );
	}
	// --------------------------------------------------------------------------------------------------------------------------------
	public function configure( $sName, $mValue ) {
		switch ( $sName ) {
			case 'scriptDeferral' :
				if (true === $mValue || false === $mValue) {
					if ($mValue)
						$this->sDefer = 'defer ';
					else
						$this->sDefer = '';
				}
				break;
			case 'javascript URI' :
				$this->sJavascriptURI = $mValue;
				break;
			case 'inlineScript' :
				if (true === $mValue || false === $mValue)
					$this->bInlineScript = $mValue;
				break;
		}
	}
	// --------------------------------------------------------------------------------------------------------------------------------
	public function generateClientScript( ) {
		echo "\n<script  " . $this->sDefer . ">\n";
		echo "/* <![CDATA[ */\n";
		echo "if (undefined == xajax.ext)	xajax.ext = {};";
		echo "xajax.ext.SWFupload = {};";
		echo "xajax.ext.SWFupload.config = {};";
		echo "xajax.ext.SWFupload.config.javascript_URI='" . $this->sJavascriptURI . "xajax_plugins/request/swfupload/'";
		echo "/* ]]> */\n";
		echo "</script>\n";
		if ($this->bInlineScript) {
			echo "\n<script  " . $this->sDefer . ">\n";
			echo "/* <![CDATA[ */\n";
			include ( dirname( __FILE__ ) . 'xajax_plugins/request/swfupload/swfupload.js' );
			include ( dirname( __FILE__ ) . 'xajax_plugins/request/swfupload/swfupload.queue.js' );
			include ( dirname( __FILE__ ) . 'xajax_plugins/request/swfupload/swfupload.xajax.js' );
			echo "/* ]]> */\n";
			echo "</script>\n";
		} else {
			echo "\n<script  src='" . $this->sJavascriptURI . "xajax_plugins/request/swfupload/swfupload.js' " . $this->sDefer . "></script>\n";
			echo "\n<script  src='" . $this->sJavascriptURI . "xajax_plugins/request/swfupload/swfupload.queue.js' " . $this->sDefer . "></script>\n";
			echo "\n<script  src='" . $this->sJavascriptURI . "xajax_plugins/request/swfupload/swfupload.xajax.js' " . $this->sDefer . "></script>\n";
		}
	}
	// --------------------------------------------------------------------------------------------------------------------------------
	function transForm( $id, $config, $multi = false ) {
		$command = array (
				'n' => 'SWFup_tfo',
				't' => $id 
		);
		$this->addCommand( $command, array (
				"config" => $config,
				"multi" => $multi 
		) );
	}
	// --------------------------------------------------------------------------------------------------------------------------------
	function transField( $id, $config, $multi = false ) {
		$command = array (
				'n' => 'SWFup_tfi',
				't' => $id 
		);
		$this->addCommand( $command, array (
				"config" => $config,
				"multi" => $multi 
		) );
	}
	// --------------------------------------------------------------------------------------------------------------------------------
	function destroyField( $id ) {
		$command = array (
				'n' => 'SWFup_dfi',
				't' => $id 
		);
		$this->addCommand( $command, array () );
	}
	// --------------------------------------------------------------------------------------------------------------------------------
	function destroyForm( $id ) {
		$command = array (
				'n' => 'SWFup_dfo',
				't' => $id 
		);
		$this->addCommand( $command, array () );
	}
	// --------------------------------------------------------------------------------------------------------------------------------
}
$objPluginManager = & xajaxPluginManager::getInstance();
$objPluginManager->registerPlugin( new clsSwfUpload(), 100 );