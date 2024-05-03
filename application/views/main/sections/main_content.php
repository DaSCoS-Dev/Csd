<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}

$output = <<<EOF
<!-- HOME PAGE -->
<div class="p-3 mb-3 bg-body rounded container justify-content-center align-self-center" id="div_principale_globale">
	<div id="div_logo" class="w-100">
		<center><img src="/assets/img/{$this->config->config["logo"]}" width="158" height="139" alt="Logo Tiny.top"></center>
	</div>
</div>
<div class="shadow p-3 mb-5 bg-body rounded container justify-content-center align-self-center" id="div_home_page">
	{$contenuto}
</div>
<!-- // HOME PAGE -->
EOF
;
print $output;
?>