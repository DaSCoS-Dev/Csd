<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}

$help = <<<EOF
<div class="accordion" id="guestMainHelp">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        #1 - What is Csd?
      </button>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#guestMainHelp">
      <div class="accordion-body">
        <p>
			Csd, acronym of "<u>C</u>odeIgniter <u>S</u>pa by <u>D</u>aSCoS", is a framework for SPA applications (<u>S</u>ingle <u>P</u>age <u>A</u>pplication: <a href="https://it.wikipedia.org/wiki/Single-page_application" target="_blank" id="Spa_def" name="Spa_def" alt="Spa Definition" title="Spa Definition">more info</a>) based on the CodeIgniter 3.1.0 framework, which we have extended with the integration of the xajax framework to manage the logical part that creates the Spa, of jquery for advanced features and functions and Bootstrap for the graphic part. Csd framework allows you to create rich web applications that offer a smooth and interactive user experience.
		</p><p>
			<a href="https://codeigniter.com/userguide3/general/welcome.html" id="Ci_def" name="Ci_def" target="_blank" alt="Code Igniter Docs" tile="Code Igniter Docs">CodeIgniter</a> is a lightweight and flexible PHP framework that focuses on development speed and simplicity. It uses the Model-View-Controller (MVC) pattern to separate presentation logic from business logic. In this way, robust and maintainable web applications can be created.
		</p><p>
			<a href="https://github.com/Xajax/Xajax" id="Xajax_def" name="Xajax_def" target="_blank" alt="Xajax Docs" tile="Xajax Docs">Xajax</a>, on the other hand, is an AJAX framework for PHP that makes it easy to implement AJAX functionality in a web application. This framework allows you to make AJAX calls to the server without having to rewrite the PHP code. Additionally, xajax offers a number of useful features, such as data validation and error handling.
		</p><p>
			<a href="https://getbootstrap.com/docs/5.0/getting-started/introduction/" id="Bootstrap_def" name="Bootstrap_def" target="_blank" alt="Bootstrap Docs" tile="Bootstrap Docs">Bootstrap</a> is a CSS and JavaScript based front-end framework that provides pre-built and responsive UI components. With Bootstrap, you can quickly create an attractive and easy-to-use user interface, without having to write a lot of CSS or JavaScript code.
		</p><p>
			<a href="https://jquery.com/" id="JQuery_def" name="JQuery_def" target="_blank" alt="JQuery Docs" tile="JQuery Docs">JQuery</a> is a lightweight and fast JavaScript library that makes it easy to manipulate the DOM and interact with the server via AJAX. With jQuery, you can write clean and compact JavaScript code, saving time and effort.
		</p><p>
			Together, these frameworks allow you to build a powerful and interactive single-page web application with an attractive and responsive user interface. With xajax integration, you can use AJAX features to improve user experience and make your application more efficient. Also, thanks to the use of Bootstrap and jQuery, you can quickly create a high-quality user interface without having to write a lot of code.
		</p><p>
			In summary, Csd is a fork of CodeIgniter 3.1.0 that offers a rich and interactive user experience thanks to xajax integration and the use of Bootstrap and jQuery. With Csd, you can create attractive and efficient single page web applications quickly and easily.
		</p>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingTwo">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
        #2 - The Csd keystones!
      </button>
    </h2>
    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#guestMainHelp">
      <div class="accordion-body">
      	<p>
        	Csd is an innovative framework that is based on an MVL structure, i.e. Model, View and Library. The basic workings of this framework are very interesting and worth examining carefully.
		</p><p>
			First, CodeIgniter's standard admin.php Controller has been rewritten to load two libraries essential for the framework to function: "Super_lib" and "Main_xajax". These libraries perform specific functions within the framework and are essential to ensure proper execution of the application.
		</p><p>
			The standard method of the "index" Controller is the only one that is invoked within the framework via the xajax features. Based on the parameters coming from the xajax call, the "index" method understands which library and which method it should execute. This way, the Main Controller can call the proper library and its method rebuilt.
		</p><p>
			It is important to note that the core of the whole system is the single line of code "\$this->start_xajax();" in the initialization of the Super_lib. This line of code represents the keystone of the entire system, as it allows you to execute the methods of the library based on the parameters arriving from the xajax call.
		</p><p>
			Thanks to the MVL structure, Csd offers a very flexible development experience. In particular, the Model represents the part of the application that deals with data management, while the View manages the user interface. The Library, on the other hand, is a set of functions that can be called from different parts of the application and which offer advanced and specialized functionality.
		</p><p>
			Ultimately, Csd is an extremely powerful and flexible framework that allows you to create high-quality single-page web applications. Thanks to its MVL structure and specialized libraries, this framework represents an innovative and highly performing solution for developers who want to create advanced web applications.
		</p>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingThree">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
        #3 - Programming basics
      </button>
    </h2>
    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#guestMainHelp">
      <div class="accordion-body">
        <p>
			To develop a functionality within the CSD, it is necessary to build at least three objects: a Library, a Model and a View. The Library handles events arriving from the browser side, such as "onclick", "onsubmit" and "onchange", and actions on user interface elements. When an event is handled by the Library, it can query the related Model to retrieve, update or delete data from the database. The Model will then return a result to the Library, which will manipulate the result in order to send it to the View. The View takes care of representing the result in HTML code.
		</p>
		<p>
			To ensure good coding, it is important to assign each object a precise task and limit complex logics. The Model should only deal with database data manipulation and contain minimal logic, such as conditions, loops, and switches. The View, on the other hand, only has to compose the graphics, avoiding conditions, cycles and subdivisions as much as possible. The task of putting the results together must be entrusted to the Library, which acts as an intermediary between the Model and the View.
		</p>
		<p>
			An example of good coding would be to create a feature that displays all products of a given type. The Library will handle the click event on a button and will query the Model to retrieve data from the database. The Model will return a recordset to the Library, which will manipulate the result in order to send it to the View. The View will only take care of representing the data in a table.
		</p>
		<p>
			Conversely, an example of bad coding might be building a feature where the View contains complex logic and conditions. For example, the View could contain a for loop that processes the data returned by the Model and presents various options based on the conditions, or integrates database queries to recover "missing" data. This type of coding would be difficult to maintain and to change in the future.
		</p>
      </div>
    </div>
  </div>
</div>

EOF

;
print $help;
?>