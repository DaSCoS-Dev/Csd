<?php
$login = array(
		'name'	=> 'login',
		'id'	=> 'login',
		'value' => set_value('login'),
		'maxlength'	=> 80,
		'size'	=> 20,
		"type" => "email",
		"class" => "form-control",
		"autocomplete" => "username",
		"placeholder" => "name@example.com"
);
$form_attributes = array(
		"method" => "post",
		"id" => "form_reset",
		"name" => "form_reset",
		"onsubmit" => "return false"
);
$submit = array(
		//"content" => "Entra",
		"type" => "button",
		"class" => "w-100 btn btn-lg btn-success"
);
$cancel = array(
		//"content" => "Entra",
		"type" => "button",
		"class" => "w-100 btn btn-lg btn-danger"
);
if ($this->config->item('use_username')) {
	$login_label = 'Email or login name';
} else {
	$login_label = 'Email';
}
?>
<main class="form-signin">
 <h2 class="h3 mb-3 fw-normal">Reset Password</h2>
  <?php echo form_open($this->uri->uri_string(), $form_attributes); ?>

    <div class="form-floating">
      <?php echo form_input($login); ?>
      <label for="login"><?php echo $login_label ?></label>
      <p><?php echo form_error($login['name']); ?><?php echo isset($errors[$login['name']])?$errors[$login['name']]:''; ?></p>
    </div>
    <div class="form-floating">
    	<p>
    <?php echo form_button($submit, 'Get a new password', " onclick=\"xajax_execute('tank_auth/auth', 'forgot_password', xajax.getFormValues('form_reset', true));\" "); ?>
    	</p>
		<p>
<?php echo form_button($cancel, 'Dismiss', " onclick=\"xajax_execute('Default_actions', 'logout');\" "); ?>
		</p>
    </div>
	</rofm>    
</main>
