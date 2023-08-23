<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="wrap ">
    <?php echo $form->open();?>
        <div class="form-header ">
            <?php  if($this->system_message){ echo $this->system_message->render(); } ?>
            <img src="/images/logo.png" alt="logo" class="img-fluid logo">
        </div>

        <?php echo $form->bs4_text('Correo Electronico', 'email', '',
            array(
                'class' => 'form-control',
                'required' => 'required',
                'type'=>'email',
                'id' => 'correo',
            )
        ); ?>

        <?php echo $form->bs4_text('Contraseña', 'contrasena', '',
                                            array(
                                                'class' => 'form-control',
                                                'required' => 'required',
                                                'type'=>'password',
                                                'id' => 'contrasena',
                                            )
                                        ); ?>
        <?php echo $form->btn_submit('Iniciar Sesión', ['class' => 'btn btn-success btn-block', 'id' => 'iniciarSesion'])?>

        <div class="form-footer mt-4">
            <a href="#">Cambiar Contraseña</a>
        </div>
    <?php echo $form->close(); ?>
</div><!--/.wrap-->