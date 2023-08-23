<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends User_Controller {
    public function __construct()
	{ 
        parent::__construct();
	}

    function index(){
        echo 'index de home en oficiales';
    }
}