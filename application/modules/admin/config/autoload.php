<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| AUTO-LOADER (module-specific)
| -------------------------------------------------------------------
| For detailed usage, please check the comments from original file:
| application/config/autoload.php
|
*/

$autoload['packages'] = array(APPPATH.'third_party/ion_auth');

$autoload['libraries'] = array('ion_auth');

$autoload['drivers'] = array();

$autoload['helper'] = array('url');

$autoload['config'] = array();

$autoload['language'] = array();

$autoload['model'] = array();
