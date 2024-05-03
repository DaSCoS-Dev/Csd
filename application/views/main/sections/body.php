<?php
if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}
$extension = "";
if (!$this->super_lib->is_live()) {
	$extension = ".min";
} else {
	$extension = "";
}

$output = <<<EOF
<body style="margin: 8px !important">
<style>
.btn,
.form-control,
.nav,
.footer,
.p-3,
.container{
  will-change: transform;
  transform: translateZ(0);
}
</style>
{$scripts}
{$contenuto}
{$styles}
</body>
EOF
;
print $output;
?>