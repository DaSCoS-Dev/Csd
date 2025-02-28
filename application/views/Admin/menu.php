<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}

$output = <<<EOF
      <ul class="navbar-nav" id="admin_menu_struct">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLinkAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Framework Administration
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLinkAdmin">
            <li>
				<span class="dropdown-item" id="admin_configure" data-bs-toggle="collapse" data-bs-target=".navbar-collapse.show">
					<button type="button" class="btn" id="admin_configure_actions" onclick="xajax_execute('Admin/Main_admin', 'index', 'configureFrameworkOptions')">Configure Main Options</button>
				</span>
			</li>
			<li>
				<span class="dropdown-item" id="admin_new_functionality" data-bs-toggle="collapse" data-bs-target=".navbar-collapse.show">
					<button type="button" class="btn" id="admin_new_functionality_actions" onclick="xajax_execute('Admin/Main_admin', 'index', 'buildFormNewFunctionality')">Build New Function</button>
				</span>
			</li>
          </ul>
        </li>
      </ul>
EOF
;
print $output;
?>