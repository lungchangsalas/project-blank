<?php $this->load->view('email/header'); ?>



<?php
	$url = "https://" . APPLICATION_URL ."/oficiales/Auth/CambiarContrasena/" . $forgotten_password_code;
?>


<h1><?php echo sprintf(lang('email_forgot_password_heading'), $identity);?></h1>

<p>Click a este link para <a href="<?php echo $url; ?>">cambiar de contraseña.</a></p>


<?php $this->load->view('email/footer'); ?>