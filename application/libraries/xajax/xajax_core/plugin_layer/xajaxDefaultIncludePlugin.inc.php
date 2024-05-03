<?php
/*
 * File: xajaxDefaultIncludePlugin.inc.php
 * Contains the default script include plugin class.
 * Title: xajax default script include plugin class
 * Please see <copyright.inc.php> for a detailed description, copyright
 * and license information.
 */

/*
 * @package xajax
 * @version $Id: xajaxDefaultIncludePlugin.inc.php,v 1.1 2013-02-11 00:49:46 developer Exp $
 * @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
 * @license http://www.xajaxproject.org/bsd_license.txt BSD License
 */

/*
 * Class: xajaxIncludeClientScript
 * Generates the SCRIPT tags necessary to 'include' the xajax javascript
 * library on the browser.
 * This is called when the page is first loaded.
 */
class xajaxIncludeClientScriptPlugin extends xajaxRequestPlugin {
	var $sJsURI;
	var $aJsFiles;
	var $sDefer;
	var $sRequestURI;
	var $sStatusMessages;
	var $sWaitCursor;
	var $sVersion;
	var $sDefaultMode;
	var $sDefaultMethod;
	var $bDebug;
	var $bVerboseDebug;
	var $nScriptLoadTimeout;
	var $bUseUncompressedScripts;
	var $bDeferScriptGeneration;
	var $sLanguage;

	function xajaxIncludeClientScriptPlugin( ) {
		$this->sJsURI = '';
		$this->aJsFiles = array ();
		$this->sDefer = '';
		$this->sRequestURI = '';
		$this->sStatusMessages = 'false';
		$this->sWaitCursor = 'true';
		$this->sVersion = 'unknown';
		$this->sDefaultMode = 'asynchronous';
		$this->sDefaultMethod = 'POST'; // W3C: Method is case sensitive
		$this->bDebug = false;
		$this->bVerboseDebug = false;
		$this->nScriptLoadTimeout = 20;
		$this->bUseUncompressedScripts = false;
		$this->bDeferScriptGeneration = false;
		$this->sLanguage = null;
	}

	/*
	 * Function: configure
	 */
	function configure( $sName, $mValue ) {
		if ('javascript URI' == $sName) {
			$this->sJsURI = $mValue;
		} else if ("javascript files" == $sName) {
			$this->aJsFiles = $mValue;
		} else if ("scriptDefferal" == $sName) {
			if (true === $mValue)
				$this->sDefer = "defer ";
			else
				$this->sDefer = "";
		} else if ("requestURI" == $sName) {
			$this->sRequestURI = $mValue;
		} else if ("statusMessages" == $sName) {
			if (true === $mValue)
				$this->sStatusMessages = "true";
			else
				$this->sStatusMessages = "false";
		} else if ("waitCursor" == $sName) {
			if (true === $mValue)
				$this->sWaitCursor = "true";
			else
				$this->sWaitCursor = "false";
		} else if ("version" == $sName) {
			$this->sVersion = $mValue;
		} else if ("defaultMode" == $sName) {
			if ("asynchronous" == $mValue || "synchronous" == $mValue)
				$this->sDefaultMode = $mValue;
		} else if ("defaultMethod" == $sName) {
			if ("POST" == $mValue || "GET" == $mValue) // W3C: Method is case sensitive
				$this->sDefaultMethod = $mValue;
		} else if ("debug" == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bDebug = $mValue;
		} else if ("verboseDebug" == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bVerboseDebug = $mValue;
		} else if ("scriptLoadTimeout" == $sName) {
			$this->nScriptLoadTimeout = $mValue;
		} else if ("useUncompressedScripts" == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bUseUncompressedScripts = $mValue;
		} else if ('deferScriptGeneration' == $sName) {
			if (true === $mValue || false === $mValue)
				$this->bDeferScriptGeneration = $mValue;
			else if ('deferred' == $mValue)
				$this->bDeferScriptGeneration = $mValue;
		} else if ('language' == $sName) {
			$this->sLanguage = $mValue;
		}
	}

	/*
	 * Function: generateClientScript
	 */
	function generateClientScript( $asHtml = false ) {
		$html = "";
		if (false === $this->bDeferScriptGeneration) {
			$html .= $this->printJavascriptConfig( $asHtml );
			$html .= $this->printJavascriptInclude( $asHtml );
		} else if (true === $this->bDeferScriptGeneration) {
			$html .= $this->printJavascriptInclude( $asHtml );
		} else if ('deferred' == $this->bDeferScriptGeneration) {
			$html .= $this->printJavascriptConfig( $asHtml );
		}
		if ($asHtml) {
			return $html;
		}
	}

	/*
	 * Function: getJavascriptConfig
	 * Generates the xajax settings that will be used by the xajax javascript
	 * library when making requests back to the server.
	 * Returns:
	 * string - The javascript code necessary to configure the settings on
	 * the browser.
	 */
	function getJavascriptConfig( ) {
		ob_start();
		$this->printJavascriptConfig();
		return ob_get_clean();
	}

	/*
	 * Function: printJavascriptConfig
	 * See <xajaxIncludeClientScriptPlugin::getJavascriptConfig>
	 */
	function printJavascriptConfig( $asHtml = false ) {
		$html = "";
		$sCrLf = "\n";
		if ($asHtml) {
			$html .= $sCrLf;
			$html .= '<script ';
			// $html .= 'script type="text/javascript" ';
			$html .= $this->sDefer;
			// $html .= 'charset="UTF-8">';
			$html .= '>';
			$html .= $sCrLf;
			$html .= '/* <';
			$html .= '![CDATA[ */';
			$html .= $sCrLf;
			$html .= 'try { if (undefined == xajax.config) xajax.config = {}; } catch (e) { xajax = {}; xajax.config = {}; };';
			$html .= $sCrLf;
			$html .= 'xajax.config.requestURI = "';
			$html .= $this->sRequestURI;
			$html .= '";';
			$html .= $sCrLf;
			$html .= 'xajax.config.statusMessages = ';
			$html .= $this->sStatusMessages;
			$html .= ';';
			$html .= $sCrLf;
			$html .= 'xajax.config.waitCursor = ';
			$html .= $this->sWaitCursor;
			$html .= ';';
			$html .= $sCrLf;
			$html .= 'xajax.config.version = "';
			$html .= $this->sVersion;
			$html .= '";';
			$html .= $sCrLf;
			$html .= 'xajax.config.legacy = false;';
			$html .= $sCrLf;
			$html .= 'xajax.config.defaultMode = "';
			$html .= $this->sDefaultMode;
			$html .= '";';
			$html .= $sCrLf;
			$html .= 'xajax.config.defaultMethod = "';
			$html .= $this->sDefaultMethod;
			$html .= '";';
			$html .= $sCrLf;
			$html .= '/* ]]> */';
			$html .= $sCrLf;
			$html .= '<';
			$html .= '/script>';
			$html .= $sCrLf;
			return $html;
		} else {
			print $sCrLf;
			print '<script ';
			// print 'script type="text/javascript" ';
			print $this->sDefer;
			// print 'charset="UTF-8">';
			print '>';
			print $sCrLf;
			print '/* <';
			print '![CDATA[ */';
			print $sCrLf;
			print 'try { if (undefined == xajax.config) xajax.config = {}; } catch (e) { xajax = {}; xajax.config = {}; };';
			print $sCrLf;
			print 'xajax.config.requestURI = "';
			print $this->sRequestURI;
			print '";';
			print $sCrLf;
			print 'xajax.config.statusMessages = ';
			print $this->sStatusMessages;
			print ';';
			print $sCrLf;
			print 'xajax.config.waitCursor = ';
			print $this->sWaitCursor;
			print ';';
			print $sCrLf;
			print 'xajax.config.version = "';
			print $this->sVersion;
			print '";';
			print $sCrLf;
			print 'xajax.config.legacy = false;';
			print $sCrLf;
			print 'xajax.config.defaultMode = "';
			print $this->sDefaultMode;
			print '";';
			print $sCrLf;
			print 'xajax.config.defaultMethod = "';
			print $this->sDefaultMethod;
			print '";';
			print $sCrLf;
			print '/* ]]> */';
			print $sCrLf;
			print '<';
			print '/script>';
			print $sCrLf;
		}
	}

	/*
	 * Function: getJavascriptInclude
	 * Generates SCRIPT tags necessary to load the javascript libraries on
	 * the browser.
	 * sJsURI - (string): The relative or fully qualified PATH that will be
	 * used to compose the URI to the specified javascript files.
	 * aJsFiles - (array): List of javascript files to include.
	 * Returns:
	 * string - The SCRIPT tags that will cause the browser to load the
	 * specified files.
	 */
	function getJavascriptInclude( ) {
		ob_start();
		$this->printJavascriptInclude();
		return ob_get_clean();
	}

	/*
	 * Function: printJavascriptInclude
	 * See <xajaxIncludeClientScriptPlugin::getJavascriptInclude>
	 */
	function printJavascriptInclude( $asHtml ) {
		$aJsFiles = $this->aJsFiles;
		$sJsURI = $this->sJsURI;
		
		if (0 == count( $aJsFiles ) or ! is_array( $aJsFiles )) {
			$aJsFiles = array ();
			$rnd = "";
			$aJsFiles [ ] = array (
					$this->_getScriptFilename( "xajax_js/xajax_core.min.js{$rnd}" ),
					'xajax' 
			);
			/*$aJsFiles [ ] = array (
					$this->_getScriptFilename( "xajax_js/xajax_extend.min.js{$rnd}" ),
					'xajax' 
			);*/
			
			if (true === $this->bDebug)
				$aJsFiles [ ] = array (
						$this->_getScriptFilename( 'xajax_js/xajax_debug_uncompressed.js' ),
						'xajax.debug' 
				);
			
			if (true === $this->bVerboseDebug)
				$aJsFiles [ ] = array (
						$this->_getScriptFilename( 'xajax_js/xajax_verbose_uncompressed.js' ),
						'xajax.debug.verbose' 
				);
			
			if (null !== $this->sLanguage)
				$aJsFiles [ ] = array (
						$this->_getScriptFilename( 'xajax_js/xajax_lang_' . $this->sLanguage . '_uncompressed.js' ),
						'xajax' 
				);
		}
		
		if ($sJsURI != '' && substr( $sJsURI, - 1 ) != '/')
			$sJsURI .= '/';
		
		$sCrLf = "\n";
		$html = "";
		foreach ( $aJsFiles as $aJsFile ) {
			if ($asHtml) {
				$html .= <<<EOF
				<script defer src="{$sJsURI}{$aJsFile [ 0 ]}" {$this->sDefer}>
				</script>{$sCrLf}
EOF
;
			} else {
				print '<script defer src="';
				// print 'script type="text/javascript" src="';
				print $sJsURI;
				print $aJsFile [ 0 ];
				print '" ';
				print $this->sDefer;
				// print 'charset="UTF-8"><';
				print ' ><';
				print '/script>';
				print $sCrLf;
			}
		}
		
		if (0 < $this->nScriptLoadTimeout) {
			foreach ( $aJsFiles as $aJsFile ) {
				if ($asHtml) {
					$html .= <<<EOF
				<script>
				/* <![CDATA[ */
					window.setTimeout(
 						function() {
  							var scriptExists = false;
  							try { 
  								if ( {$aJsFile [ 1 ]}.isLoaded ) scriptExists = true; 
								}
  							catch (e) {}
  							if (!scriptExists) {
   								//alert("Error: the xajax Javascript component could not be included. Perhaps the URL is incorrect?URL: {$sJsURI}{$aJsFile [ 0 ]}");
  							}
 						}, {$this->nScriptLoadTimeout});
					/* ]]> */
					</script>
EOF
;
				} else {
					print '<script defer ';
					// print 'script type="text/javascript" ';
					print $this->sDefer;
					// print 'charset="UTF-8">';
					print '>';
					print $sCrLf;
					print '/* <';
					print '![CDATA[ */';
					print $sCrLf;
					print 'window.setTimeout(';
					print $sCrLf;
					print ' function() {';
					print $sCrLf;
					print '  var scriptExists = false;';
					print $sCrLf;
					print '  try { if (';
					print $aJsFile [ 1 ];
					print '.isLoaded) scriptExists = true; }';
					print $sCrLf;
					print '  catch (e) {}';
					print $sCrLf;
					print '  if (!scriptExists) {';
					print $sCrLf;
					print '   alert("Error: the ';
					print $aJsFile [ 1 ];
					print ' Javascript component could not be included. Perhaps the URL is incorrect?\nURL: ';
					print $sJsURI;
					print $aJsFile [ 0 ];
					print '");';
					print $sCrLf;
					print '  }';
					print $sCrLf;
					print ' }, ';
					print $this->nScriptLoadTimeout;
					print ');';
					print $sCrLf;
					print '/* ]]> */';
					print $sCrLf;
					print '<';
					print '/script>';
					print $sCrLf;
				}
			}
		}
		if ($asHtml) {
			return $html;
		}
	}

	/*
	 * Function: _getScriptFilename
	 * Returns the name of the script file, based on the current settings.
	 * sFilename - (string): The base filename.
	 * Returns:
	 * string - The filename as it should be specified in the script tags
	 * on the browser.
	 */
	function _getScriptFilename( $sFilename ) {
		if ($this->bUseUncompressedScripts) {
			return str_replace( '.js', '_uncompressed.js', $sFilename );
		}
		return $sFilename;
	}
}

/*
 * Register the xajaxIncludeClientScriptPlugin object with the xajaxPluginManager.
 */
$objPluginManager = & xajaxPluginManager::getInstance();
$objPluginManager->registerPlugin( new xajaxIncludeClientScriptPlugin(), 99 );
