<?php
defined('BASEPATH') or exit('No direct script access allowed');

$allowed_domains = array(
	APPLICATION_URL
);

if (php_sapi_name() != 'cli') {
	if (in_array($_SERVER['HTTP_HOST'], $allowed_domains, TRUE)) {
		$domain = $_SERVER['HTTP_HOST'];
	} else {

		$domain = $default_domain;
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: https://' . $default_domain);

		exit;

	}

	$config['base_url'] = 'https://' . $domain;
}