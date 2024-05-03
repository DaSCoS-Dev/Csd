<?php
/**
* @author RainChen @ Mon Mar 12 23:25:46 CST 2007
* @uses xajax file upload extend
* @access public
* @version 0.1
*/
class xajaxExtend extends xajax
{

    /*
    function processRequest()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['xajax']))
        {
            $this->initProcessRequests();
        }
        parent::processRequest();
    }
    */

    function initProcessRequests()
    {
        $xajaxRequest = array();
        $method = @$_GET["xajax"];
        $xajaxRequest[$method] = @$_GET[$method];
        $xajaxRequest["xjxr"] = @$_GET[$method];
        // reset RequestMode
        if(isset($_GET['xajax']))
        {
            $_GET['xajax'] = null;
            unset($_GET['xajax']);
        }
        // get the upload file local path
        foreach(array_keys($_FILES) as $name)
        {
            if(isset($_GET[$name]) && !isset($_POST[$name]))
            {
                $_POST[$name] = $this->_decodeUTF8Data($_GET[$name]);
            }
        }
        $xajaxargs = array(get_original_data($_POST));
        $xajaxRequest['xajaxargs'] = $xajaxargs[0];
        $_POST = $xajaxRequest;
    }

    function getJavascript($sJsURI="", $sJsFile=NULL)
    {
        $html = parent::getJavascript($sJsURI,$sJsFile);
        // get the extend js
        if ($sJsFile == NULL) $sJsFile = "xajax_js/xajax_extend_working.js";
        if ($sJsURI != "" && substr($sJsURI, -1) != "/") $sJsURI .= "/";
        $html .= "\t<script type=\"text/javascript\" src=\"" . $sJsURI . $sJsFile . "\"></script>\n";
        return $html;
    }

}

/**
 * get original request data from GET POST
**/
if(!function_exists('get_original_data'))
{
    function get_original_data($data)
    {
        if($data)
        {
            if(get_magic_quotes_gpc())
            {
                if (is_array($data))
                {
                    foreach($data as $key=>$value)
                    {
                        $data[$key] = get_original_data($value);
                    }
                }
                else
                {
                    $data = stripslashes($data);
                }
            }
        }
        return $data;
    }
}

?>