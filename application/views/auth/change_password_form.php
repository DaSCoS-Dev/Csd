<?php
$old_password = array (
		'name' => 'old_password',
		'id' => 'old_password',
		'value' => set_value( 'old_password' ),
		'size' => 30,
		"class" => "form-control" 
);
$new_password = array (
		'name' => 'new_password',
		'id' => 'new_password',
		'maxlength' => $this->config->item( 'password_max_length' ),
		'size' => 30,
		"class" => "form-control" 
);
$confirm_new_password = array (
		'name' => 'confirm_new_password',
		'id' => 'confirm_new_password',
		'maxlength' => $this->config->item( 'password_max_length' ),
		'size' => 30,
		"class" => "form-control" 
);
$form_attributes = array (
		"method" => "post",
		"id" => "form_login",
		"name" => "form_login",
		"onsubmit" => "return false" 
);
$submit = array (
		// "content" => "Entra",
		"type" => "button",
		"class" => "w-100 btn btn-lg btn-success" 
);
?>
<?php echo form_open($this->uri->uri_string(), $form_attributes); ?>
<input id="new_pass_key" value="<?php echo $new_pass_key?>"
	type="hidden">
<input id="user_id" value="<?php echo $user_id?>" type="hidden">
<table>
	<tr>
		<td><?php echo form_label('Vecchia Password', $old_password['id']); ?></td>
		<td><?php echo form_password($old_password); ?></td>
		<td style="color: red;"><?php echo form_error($old_password['name']); ?><?php echo isset($errors[$old_password['name']])?$errors[$old_password['name']]:''; ?></td>
	</tr>
	<tr>
		<td><?php echo form_label('Nuova Password', $new_password['id']); ?></td>
		<td><?php echo form_password($new_password); ?></td>
		<td style="color: red;"><?php echo form_error($new_password['name']); ?><?php echo isset($errors[$new_password['name']])?$errors[$new_password['name']]:''; ?></td>
	</tr>
	<tr>
		<td><?php echo form_label('Conferma Nuova Password', $confirm_new_password['id']); ?></td>
		<td><?php echo form_password($confirm_new_password); ?></td>
		<td style="color: red;"><?php echo form_error($confirm_new_password['name']); ?><?php echo isset($errors[$confirm_new_password['name']])?$errors[$confirm_new_password['name']]:''; ?></td>
	</tr>
</table>
<?php echo form_button($submit, 'Cambia Password', " onclick=\"xajax_execute('tank_auth/auth', 'change_password', xajax.getFormValues('form_login', true));\" "); ?>
<?php echo form_close(); ?>