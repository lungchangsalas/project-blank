<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_Controller extends MY_Controller {
    protected $mLoginUrl = 'oficiales/Auth/Login';
	protected $mUsefulLinks = array();
    
    // Constructor
	public function __construct()
	{ 
        parent::__construct();
		$this->verifyLogin();
	}

    protected function verify_token(){
		if(!$this->session->userdata('X_API_TOKEN')):
			$redirect_url = $this->mSiteConfig['login_url'];
			redirect($redirect_url);
		endif;
	}

    // Render template (override parent)
	protected function render($view_file, $layout = 'default')
	{ 
		parent::render($view_file, $layout);
	}

	protected function verifyLogin($redirect_url = NULL){

        if (!$this->ion_auth->logged_in()): 
            if($redirect_url==NULL):
                $redirect_url = $this->mSiteConfig['login_url'];
            endif;

            redirect($redirect_url);

        else: 
			redirect($redirect_url);  
        endif;

        if($this->ion_auth->user()->row_array()['email'] != $this->session->userdata('email')):
            $this->ion_auth->logout();
            redirect($this->router->fetch_module());
        endif;

        if(empty($this->ion_auth->user()->row()->id)):
            $this->ion_auth->logout();
            redirect($this->router->fetch_module());
        endif;

    }
}
