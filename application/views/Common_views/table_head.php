<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
$html = <<<EOF
<th>{$header_name}</th>

EOF
;
print $html;
?>