<?php
$email = array(
	'name'	=> 'email',
	'id'	=> 'email',
	'value'	=> set_value('email'),
	'maxlength'	=> 80,
	'size'	=> 30,
		"type" => "email",
		"class" => "form-control",
		"autocomplete" => "username",
		"placeholder" => "name@example.com"
);
$submit = array(
		//"content" => "Entra",
		"type" => "button",
		"class" => "w-100 btn btn-lg btn-success"
);
if ($not_activated == true){
	$extra = <<<EOF
	<div class="shadow p-3 mb-5 bg-body rounded">
		Il tuo Profilo risulta ancora in attesa di Conferma. Se non hai ricevuto la mail di conferma o non la trovi pi&ugrave;, usa il form sotante
			per richiederne una nuova.<br>Grazie
	</div>
EOF
;
}
?>
<main class="form-signin">
 	<h2 class="h3 mb-3 fw-normal">Reinvio Mail di Conferma</h2>
 	<?php echo $extra; ?>
	<form id="form_resend">
    	<div class="form-floating">
      		<?php echo form_input($email); ?>
      		<label for="email"><?php echo form_label('Indirizzo Email', $email['id']); ?></label>
      		<p><?php echo form_error($email['name']); ?><?php echo isset($errors[$email['name']])?$errors[$email['name']]:''; ?></p>
    	</div>
    	 </form>
		<div class="form-floating">
			<p>
<?php 		
echo form_button($submit, 'Invia Mail', " onclick=\"xajax_execute('tank_auth/auth', 'send_again', xajax.getFormValues('form_resend', true));\" "); 
?>
		</p>
		</div>
 
</main>