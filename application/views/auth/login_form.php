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
if ($login_by_username AND $login_by_email) {
	$login_label = 'Email o login';
} else if ($login_by_username) {
	$login_label = 'Login';
} else {
	$login_label = 'Email';
}
$password = array(
	'name'	=> 'password',
	'id'	=> 'password',
	'size'	=> 20,
	"type" => "password",
	"class" => "form-control",
	"autocomplete" => "current-password",
	"placeholder" => "Password"
);
$remember = array(
	'name'	=> 'remember',
	'id'	=> 'remember',
	'value'	=> 1,
	'checked'	=> set_value('remember'),
	'style' => 'margin:0;padding:0',
);
$captcha = array(
	'name'	=> 'captcha',
	'id'	=> 'captcha',
	'maxlength'	=> 8,
);
$form_attributes = array(
		"method" => "post",
		"id" => "form_login",
		"name" => "form_login",
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
?>
<main class="form-signin">
 <h2 class="h3 mb-3 fw-normal">Login</h2>
  <?php 
  echo form_open($this->uri->uri_string(), $form_attributes); ?>
   
    <div class="form-floating">
      <?php echo form_input($login); ?>
      <label for="login"><?php echo $login_label ?></label>
      <p><?php echo form_error($login['name']); ?><?php echo isset($errors[$login['name']])?$errors[$login['name']]:''; ?></p>
    </div>
    <div class="form-floating">
      <?php echo form_password($password); ?>
      <label for="password">Password</label>
      <p><?php echo form_error($password['name']); ?><?php echo isset($errors[$password['name']])?$errors[$password['name']]:''; ?></p>
    </div>		
    <div class="checkbox mb-3">
      <label>
        <?php echo form_checkbox($remember); ?>Remember me
      </label>
    </div>
    <?php 
			if ($this->config->item('allow_registration')) {
				?>
		<div class="form-floating">
				<p><label style="cursor: pointer" onclick="xajax_execute('Default_actions', 'register');">Sign Up Now</label></p>
		</div>
			<?php
			}
				?>
	<div class="form-floating">
		<p><label style="cursor: pointer" onclick="xajax_execute('Default_actions', 'forgot_password');">Forgot your Password?</label></p>
	</div>
<?php if ($show_captcha) {	?>
	<div class="form-floating">
			Insert the code as appear:
		<br><?php echo $captcha_html; ?>
		<br><?php echo form_label('Codice di Conferma', $captcha['id']); ?>
		<br><?php echo form_input($captcha); ?>
		<br><?php echo form_error($captcha['name']); ?>
	</div>
<?php } ?>
	<div class="form-floating">
		<p>
<?php 		
echo form_button($submit, 'Login', " onclick=\"xajax_execute('tank_auth/auth', 'login', xajax.getFormValues('form_login', true));\" "); 
?>
		</p>
		<p>
<?php echo form_button($cancel, 'Dismiss', " onclick=\"xajax_execute('Default_actions', 'logout');\" "); ?>
		</p>
  </form>
</main>