<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}
$anno = date( "Y" );
$extra = "";
$testo_titolo = "text-success";
if (! $this->is_live()) {
	$extra = " - <span class=\"text-warning\">TEST</span>";
	$testo_titolo = "text-danger";
}

$output = <<<EOF
<!--  NAV  -->
<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
  <div class="container-fluid">
    <a class="{$testo_titolo} navbar-brand" href="/">{$this->config->config["website_name"]} {$extra}</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Options <i class="bi bi-gear"></i>
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            <li><span class="dropdown-item" id="login_logout" data-bs-toggle="collapse" data-bs-target=".navbar-collapse.show"></span></li>
			<li><span class="dropdown-item border border-success border-3" id="register_profile" data-bs-toggle="collapse" data-bs-target=".navbar-collapse.show"></span></li>
          </ul>
        </li>
      </ul>
     <ul class="navbar-nav" id="admin_menu_struct"></ul>
    </div>
  </div>
</nav>
  <!--  //NAV  -->
EOF
;
print $output;
?>