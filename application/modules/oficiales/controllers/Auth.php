<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {
    public function __construct(){ 
        parent::__construct();
        $this->load->model('Input_model');
        $this->load->model('user_model');
	}

    function Login(){
        $form = $this->form_builder->create_form('', true, array('class'=>'login-form'));
        
		$this->mViewData['form'] = $form;
        $this->render('Autenticacion/IniciarSesion','IniciarSesion');
    }
}