<?php 
if (isset($motivo)){
	$extra = <<<EOF
	<big style="font: 16px/18px Arial, Helvetica, sans-serif;"><b>You are receiving this email because an Administrator has noticed that you have not yet confirmed your Email, therefore you cannot access the System yet.</b></big><br>
EOF
;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head><title>Welcome on <?php echo $site_name; ?>!</title></head>
<body>
<div style="max-width: 800px; margin: 0; padding: 30px 0;">
<table width="80%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td width="5%"></td>
<td align="left" width="95%" style="font: 13px/18px Arial, Helvetica, sans-serif;">
<h2 style="font: normal 20px/23px Arial, Helvetica, sans-serif; margin: 0; padding: 0 0 18px; color: black;">Welcome on <?php echo $site_name; ?>!</h2>
<?php echo $extra?>Thank you for subscribing to <?php echo $site_name; ?>. We have listed your login details in detail below, please be sure to keep them safe.<br />
To access <?php echo $site_name; ?> please follow this link:<br />
<br />
<big style="font: 16px/18px Arial, Helvetica, sans-serif;"><b><a href="<?php echo site_url('/auth/activate/'.$user_id.'/'.$new_email_key); ?>" style="color: #3366cc;">Complete your registration...</a></b></big><br />
<br />
The link does not work? Copy it into your browser's address bar:<br />
<nobr><a href="<?php echo site_url('/auth/activate/'.$user_id.'/'.$new_email_key); ?>" style="color: #3366cc;"><?php echo site_url('/auth/activate/'.$user_id.'/'.$new_email_key); ?></a></nobr><br />
<br />
Please confirm your email within <?php echo $activation_period; ?> hours, otherwise your registration will become invalid and you will have to register again.<br />
<br />
<br />
<?php if (strlen($username) > 0) { ?>Your username: <?php echo $username; ?><br /><?php } ?>
Your registration email: <?php echo $email; ?><br />
<?php if (isset($password)) { ?>Your Password: <?php echo $password; ?><br /><?php } ?>
<br />
<br />
Have fun!<br />
The <?php echo $site_name; ?> Team
</td>
</tr>
</table>
</div>
</body>
</html>