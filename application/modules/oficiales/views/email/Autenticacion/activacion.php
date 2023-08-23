<?php $this->load->view('email/header'); ?>


<h1><?php echo sprintf(lang('email_activate_heading'), $identity);?></h1>


<?php
if (ENVIRONMENT=='development'): 
	$url = "https://" . APPLICATION_URL_DEV . "/odontologo/Auth/Activacion/" . $id . "/" . $activation;
else:
	$url = "https://" . APPLICATION_URL ."/odontologo/Auth/Activacion/" . $id . "/" . $activation;
endif;
?>



<p>Click a este link para <a href="<?php echo $url; ?>">activar su cuenta.</a></p>


<?php $this->load->view('email/footer'); ?>