<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}
$anno = date( "Y" );
$android = stripos($_SERVER['HTTP_USER_AGENT'], "android");
$iphone = stripos($_SERVER['HTTP_USER_AGENT'], "iphone");
$ipad = stripos($_SERVER['HTTP_USER_AGENT'], "ipad");
if($android !== false || $ipad !== false || $iphone !== false) {
	$endpoint = "whatsapp://";
} else {
	$endpoint = "https://web.whatsapp.com/";
}
$debug_button = "";
if ($this->config->item("debug_enabled")){
	$debug_button = <<<EOF
	<p class="text-secondary mb-0"  style="float: right !important">
		<a href="#" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#debugger_modal">Show Debug</a>		
	</p>
EOF
;
}
$output = <<<EOF
{$contenuto}
<!-- DEBUG -->
<div class="modal fade" id="debugger_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog  modal-fullscreen modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Debug</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="debugger_text">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- // DEBUG -->
<!-- SPACER -->
<div class="py-4 w-100"></div>
<!-- //SPACER -->
<!-- FOOTER -->
<footer class="bg-body bg-gradient footer fixed-bottom">
    <div class="row container-fluid justify-content-between" style="padding: 0px !important">
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 justify-content-start">
          <p class="text-secondary mb-0">{$this->config->config["azienda"]} &copy; 2013 - {$anno} - {$this->config->config["piva"]}</p>
        </div>
        <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12 justify-content-end">        	
        	<p class="text-secondary mb-0"  style="float: right !important">
				<a href="#" class="btn btn-outline-success btn-sm" onclick="show_loading_layer_telling('Privacy Policy', $('#testo_privacy_footer').html())">Privacy Policy</a>		
			</p>
        	<p class="text-secondary mb-0"  style="float: right !important">
				<a href="#" class="btn btn-outline-success btn-sm" onclick="show_loading_layer_telling('Cookies Policy', $('#testo_cookie_footer').html())">Cookies Policy</a>		
			</p>
			{$debug_button}
          	<div style="display: none">
          		<p  id="testo_cookie_footer">{$this->config->config["website_name"]} uses only technical cookies so no authorization is required (for now).</p>
          		<p  id="testo_privacy_footer">{$this->config->config["website_name"]} does not collect any data relating to your privacy except your IP, therefore no particular action or information on data processing is required (for now).</p>
          	</div>
        </div>
    </div>
  </footer>
  <!-- //FOOTER -->

<!-- Popup informativo  -->
<div class="modal fade" id="div_popup" tabindex="-1" aria-labelledby="div_popupLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" id="div_popup_header">
        <h5 class="modal-title" id="div_popup_label">Wait</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Ok"></button>
      </div>
      <div class="modal-body" id="div_popup_content">
          		Running, please wait a few...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal" id="modal_ok_button">Ok</button>
      </div>
    </div>
  </div>
</div>
<!-- / Popup informativo -->
<!-- Xajax dialog -->
<div id="xajax_dialog" title="Wait">
  <p id="xajax_text"></p>
</div>
<!-- / Xajax dialog -->
<!-- Scambio dati da ajax a form -->
<div id="magic_moment" style="display: none"></div>
<!-- / Scambio dati da ajax a form -->
EOF
;
print $output;
?>