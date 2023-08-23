<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input_model extends MY_Model {
    public function __construct() {
		parent::__construct();
	}

    public function getPostVariables($post = null){
    
        if(empty($post)):
            $post = $this->input->post();
        endif;
        
        $data = null;

        if(!empty($post)):
            foreach($post as $key => $value):
                $data[$key] = trim($value);
            endforeach;
        endif;

        return $data;
    }

}