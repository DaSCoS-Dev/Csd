<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}
$disclaimer = "<strong>Keep your secret key safe and don't share it with anyone! When you need it, you can always retrieve it from here</strong>";
if ($show_disclaimer) {
	$disclaimer = <<<EOF
	<div class="w-100 gy-3 shadow p-3 mb-5 bg-body rounded rounded border border-3 border-danger">
		<strong>The "secret", "auth_key" and other data you see in the examples are fictitious and NOT usable.</strong><br>
		You need these examples only to understand how you can make "shortening" requests through our APIs
	</div>
EOF
;
}
$output = <<<EOF
<div class="w-100 row">
	<div class="col-auto me-auto">
		You need this section to understand how to make a url shortening request by calling our APIs.<br>
		{$disclaimer}
	</div>
	<div class="col-auto">
    	<button class="input-group-text btn btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="Go back to the ShortUrl form.<br>Click me" id="show_short_urls" onclick="hide_tooltip('show_short_urls'); xajax_execute('Shortener/Main_shortener', 'show_shortener');">
    		<i class="bi bi-backspace"></i>
    	</button>
    </div>
</div>

<div class="accordion accordion-flush" id="accordionFlushExample">
  
<div class="accordion-item">
	<h2 class="accordion-header" id="flush-headingOne">
		<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
			<i class="bi bi-1-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Ask for your personal auth_key"></i> &nbsp;<strong>Ask</strong> &nbsp;for your personal auth_key (get_auth request)
		</button>
	</h2>
    <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
		<div class="accordion-body">
      		In order to make a request for Url shortening via API you must first obtain a key.<br>
			To obtain this key you must make a POST request to the relative entry point ({$this->view_assembler->base_url}api/get_auth), indicating the following data in the POST data:<br>
			<ul>
				<li>user_id: {$user_id}</li>
				<li>secret: {$secret}</li>
			</ul>
			CURL Example:<br>
			<div class="w-100 gy-3 shadow p-3 mb-5 bg-body rounded rounded border border-3 border-success">
				curl -X POST "{$this->view_assembler->base_url}api/get_auth" -d user_id={$user_id} -d secret={$secret}  -H "Content-Type: application/x-www-form-urlencoded"
			</div>
      	</div>
  	</div>
</div>

  	<div class="accordion-item">
   		<h2 class="accordion-header" id="flush-headingTwo">
      		<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
        		<i class="bi bi-2-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Retrieve your personal auth key"></i> &nbsp;<strong>Retrieve</strong> &nbsp;your personal auth_key (get_auth response)
      		</button>
    	</h2>
    	<div id="flush-collapseTwo" class="accordion-collapse collapse" aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
      		<div class="accordion-body">
      			If the request is formally correct, the data sent are correct and there are no other errors, you will receive a <strong>"get_auth" response</strong> in json format similar to this:
				<div class="w-100 gy-3 shadow p-3 mb-5 bg-body rounded border border-3 border-success">
					<pre>
{
	"code":"200", // Result code. 200 is "that's fine"
	"result":{
			"key":<strong>"a5dfc131f73f2f603cb5b5dc9430ffd051db0241"</strong>, // Your auth_key. Save it somewhere
			"request_date":1675334809, // Date of the request, in unix timestamp format
			"expiration_date":1675335111 // Key expiration date, in unix timestamp format
	}
}
					</pre>
				</div>
				<div class="w-100">
					<strong>expiration_date</strong> If no "do_short request" (point <i class="bi bi-3-circle"></i>) is made within this date, the key will no longer be usable and you will need to request a new one.
				</div>
				<div class="w-100">
					In case of errors you will be notified what type of error was found and how to fix it.<br>
					Some examples:
					<div class="w-100 gy-3 shadow p-3 mb-5 bg-body rounded rounded border border-3 border-danger">
						<pre>
{"code":"404.1","error":"No Post data or invalid values...."} // Some POST missing or not well formed
{"code":"404.0","error":"User not found"} // The user_id and the secret key doesn't match
{"code":"302.0","error":"You already have a valid auth_key, use it or wait another 54 seconds and call get_auth again to get a new one"}
						</pre>
					</div>
				</div>
      		</div>
    	</div>
  	</div>
  	
  	<div class="accordion-item">
    	<h2 class="accordion-header" id="flush-headingThree">
      		<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
        		<i class="bi bi-3-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Use your auth_key to short a link"></i> &nbsp;<strong>Use</strong> &nbsp;your personal auth_key (do_short request) 
      		</button>
    	</h2>
    	<div id="flush-collapseThree" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
      		<div class="accordion-body">
      			Using your auth_key (the "key" field in the "get_auth" response, point <i class="bi bi-2-circle"></i> ) you can now send a new POST request to the "do_short" entry point ({$this->view_assembler->base_url}api/do_short), respecting this data format:
				<ul>
					<li>key: auth_key</li>
					<li>url: http://www.example.com/your_page.html</li>
				</ul>
				If you prefer to send multiple URLs at the same time, respect this format (json encoded array):
				<ul>
					<li>key: auth_key</li>
					<li>url: ["http://www.example.com/your_page.html", "http://www.another_example.com/your_second_page.html"]</li>
				</ul>
				CURL Example:<br>
				<div class="w-100 gy-3 shadow p-3 mb-5 bg-body rounded rounded border border-3 border-success">
					curl -X POST "{$this->view_assembler->base_url}api/do_short" -d key=a5dfc131f73f2f603cb5b5dc9430ffd051db0241 -d url="https://getbootstrap.com/docs/5.0/forms/validation/"  -H "Content-Type: application/x-www-form-urlencoded"
				</div>
      		</div>
    	</div>
  	</div>
  	
	<div class="accordion-item">
    	<h2 class="accordion-header" id="flush-headingFour">
      	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
        	<i class="bi bi-4-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Get your new ShortLink"></i> &nbsp;<strong>Get</strong> &nbsp;your new short_url (do_short response)
      	</button>
    	</h2>
    	<div id="flush-collapseFour" class="accordion-collapse collapse" aria-labelledby="flush-headingFour" data-bs-parent="#accordionFlushExample">
      		<div class="accordion-body">
				If the request is formally correct, the data sent are correct and there are no other errors, you will receive a <strong>"do_short" response</strong> in json format similar to this:
				<div class="w-100 gy-3 shadow p-3 mb-5 bg-body rounded rounded border border-3 border-success">
					<pre>
{
	"code":"200", // Result code. 200 is "that's fine"
	"short_url":<strong>"https://tiny.top/u/AbcDefG9"</strong>, // Your new ShortUrl
		"info":{
				"remaining_urls":5998, // How many more URLs can you still shorten
				"expiration":281 // Seconds before the authorization key is no longer valid
		}
}
					</pre>					
				</div>
				<div class="w-100">
					<strong>expiration</strong> Represents how many seconds left before the authorization key is no longer valid. If you send a short request within this time, the key is regenerated and becomes valid for another {$this->config->config["rest_key_duration"]} minutes
				</div>
				<div class="w-100">
					In case of errors you will be notified what type of error was found and how to fix it.<br>
					Some examples:
					<div class="w-100 gy-3 shadow p-3 mb-5 bg-body rounded rounded border border-3 border-danger">
						<pre>
{"code":"400.0","error":"!! The Url is not valid !!"} // The Url has some problems or isn't formelly valid (disallowed chars?)
{"code":"401.1","error":"Unauthorized access. The request comes from a different ip than the one from which the auth_key was requested"}
{"code":"408.0","error":"Request Timeout: the auth_key has expired. Request a new one via get_auth"}
						</pre>
					</div>
				</div>
			</div>
    	</div>
  	</div>
  
 </div>
EOF
;
print $output;
?>