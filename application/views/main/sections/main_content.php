<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}
$warning = "";
if ($this->config->item("debug_enabled")){
		$warning = <<<EOF
<div class="alert alert-danger" role="alert">
  All configurations, including any passwords, usernames, private keys etc, ARE VISIBLE via the "Show Debug" button at the bottom right of the page!!<br>
Before making the site public you MUST modify the \$config["debug_enabled"] parameter in the /application/config/framework.php file.
</div>		
EOF
;
}
$output = <<<EOF
<!-- HOME PAGE -->
<div class="p-3 mb-3 bg-body rounded container justify-content-center align-self-center" id="div_principale_globale">
	<div id="div_logo" class="w-100">
		<center><img src="/assets/img/{$this->config->config["logo"]}" width="158" height="139" alt="Logo Tiny.top"></center>
	</div>
	{$warning}
</div>
<div class="shadow p-3 mb-5 bg-body rounded container-fluid justify-content-center align-self-center" id="div_home_page">
	{$contenuto}
</div>
<!-- // HOME PAGE -->
EOF
;
print $output;
?>