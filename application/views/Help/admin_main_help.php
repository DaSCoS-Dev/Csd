<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}

$help = <<<EOF
<div class="accordion" id="guestMainHelp">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        #1 - What You Can Do?
      </button>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#guestMainHelp">
      <div class="accordion-body">
        <p>
			The "Framework Administration" menu of Csd provides numerous functions for managing the installation. In particular, there are two features that allow you to easily manage the Framework and quickly create a complete functionality to manage a database table: "Configure Main Options" and "Build New Function".
		</p>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingTwo">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
        #2 - Configure Main Options
      </button>
    </h2>
    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#guestMainHelp">
      <div class="accordion-body">
      	<p>
        	The first, "Configure Main Options", allows you to change the main options of your installation quickly and easily. You can set options for data encryption, customize your site's logo, set up your email system, and more.
		</p>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingThree">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
        #3 - Build New Function
      </button>
    </h2>
    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#guestMainHelp">
      <div class="accordion-body">
        <p>
			The second feature, "Build New Function", is particularly interesting for simplifying the process of creating features that view, modify or delete data from a database table.
		</p>
		<p>
			This feature allows you to quickly create a complete starting base, with a standardized jquery table that includes dynamic sorting, filtering and automatic pagination functions. Furthermore, editing of records is simplified through a bootstrap "grid", while deletion is done through specific icons in the view.
		</p>
		<p>
			In this way, Csd simplifies the creation of a complete functionality to manage records, saving valuable time in the development phase. Naturally, you will then be able to make changes to the code to customize the functionality and adapt it to the specific needs of the application.
		</p>
      </div>
    </div>
  </div>
</div>

EOF

;
print $help;
?>