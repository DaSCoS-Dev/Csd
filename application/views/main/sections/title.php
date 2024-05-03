<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$extra = "";
if (!$this->is_live()){
	$extra = " - TEST";
}
$output = <<<EOF

<title>{$GLOBALS["_SERVER"]["HTTP_HOST"]}{$extra}</title>
<link rel="icon" type="image/x-icon" href="/favicon.ico">
EOF

;
print $output;

?>