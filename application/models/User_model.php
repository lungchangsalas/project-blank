<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model{
    public function __construct(){
        parent::__construct();
    }

    public function RecuperarContrasena($email){
        $admin = $this->ion_auth->forgotten_password(trim($email));

        if($admin):             
            echo 'Se ha enviado un correo exitosamente';
        else:
            echo 'Error al enviar correo.';
        endif;
        die;
    }
}