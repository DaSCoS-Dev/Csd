<?php
if ($use_username) {
	$username = array (
			'name' => 'username',
			'id' => 'username',
			'value' => set_value( 'username' ),
			'maxlength' => $this->config->item( 'username_max_length' ),
			'size' => 20,
			"class" => "form-control",
			"AutoCompleteType" => "Disabled",
			"autocomplete" => "false",
			"type" => "text",
			"placeholder" => "mario_rossi" 
	);
}
$email = array (
		'name' => 'email',
		'id' => 'email',
		'value' => set_value( 'email' ),
		'maxlength' => 80,
		'size' => 20,
		"type" => "email",
		"class" => "form-control",
		"placeholder" => "name@example.com",
		"autocomplete" => "false",
		"AutoCompleteType" => "Disabled" 
);
$password = array (
		'name' => 'password',
		'id' => 'password',
		'size' => 20,
		"type" => "password",
		"class" => "form-control",
		'value' => set_value( 'password' ),
		'maxlength' => $this->config->item( 'password_max_length' ),
		"placeholder" => "Password",
		"AutoCompleteType" => "Disabled",
		"autocomplete" => "new-password" 
);
$confirm_password = array (
		'name' => 'confirm_password',
		'id' => 'confirm_password',
		'size' => 20,
		"type" => "password",
		"class" => "form-control",
		'value' => set_value( 'confirm_password' ),
		'maxlength' => $this->config->item( 'password_max_length' ),
		"placeholder" => "Password",
		"AutoCompleteType" => "Disabled",
		"autocomplete" => "new-password" 
);
$captcha = array (
		'name' => 'captcha',
		'id' => 'captcha',
		'maxlength' => 8 
);
$form_attributes = array (
		"method" => "post",
		"id" => "form_register",
		"name" => "form_register",
		"onsubmit" => "return false",
		"autocomplete" => "off" 
);
$submit = array (
		"type" => "submit",
		"class" => "w-100 btn btn-lg btn-success" 
);
$cancel = array (
		// "content" => "Entra",
		"type" => "button",
		"class" => "w-100 btn btn-lg btn-danger" 
);
?>
<main class="form-signin">
<h2 class="h2 mb-2 fw-normal">Register</h2>
<?php

echo form_open( $this->uri->uri_string(), $form_attributes );

?>
<input autocomplete="false" name="hidden" type="text"
	style="display: none;">
<?php
if ($use_username) {
	?>
<div class="form-floating">
      <?php echo form_input($username); ?>
      <label for="username">Username (alfanumeric + underscores ( _ ),
		4 to 30 chars)</label>
      <?php echo form_error($username['name']); ?><?php echo isset($errors[$username['name']])?$errors[$username['name']]:''; ?>
    </div>
	<?php } ?>
<div class="form-floating">
      <?php echo form_input($email); ?>
      <label for="email">* Email</label>
      <?php echo form_error($email['name']); ?><?php echo isset($errors[$email['name']])?$errors[$email['name']]:''; ?>
    </div>
<div class="form-floating">
      <?php echo form_password($password); ?>
      <label for="password" >* Password (alfanumeric + underscores ( _ ),
		6 to 30 chars)</label>
      <?php echo form_error($password['name']); ?><?php echo isset($errors[$password['name']])?$errors[$password['name']]:''; ?>
    </div>
<div class="form-floating">
      <?php echo form_password($confirm_password); ?>
      <label for="password">* Confirm Password</label>
      <?php echo form_error($confirm_password['name']); ?><?php echo isset($errors[$confirm_password['name']])?$errors[$confirm_password['name']]:''; ?>
    </div>	
	<?php if ($captcha_registration) { ?>
	<div class="form-floating">
	Insert this code: <br><?php echo $captcha_html; ?>
		<br><?php echo form_label('Captcha', $captcha['id']); ?>
		<br><?php echo form_input($captcha); ?>
		<br><?php echo form_error($captcha['name']); ?><br>
</div>
<?php } ?>	
	<div class="form-floating">
	<p>	
<?php echo form_button($submit, 'Register', " onclick=\"xajax_execute('tank_auth/auth', 'register', xajax.getFormValues('form_register', true));\" "); ?>
		</p>
	<p>
<?php echo form_button($cancel, 'Dismiss', " onclick=\"xajax_execute('Default_actions', 'logout');\" "); ?>
		</p>
</div>
</form>
</main>