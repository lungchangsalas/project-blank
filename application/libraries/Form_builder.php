<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Library to build form efficiently with following features:
 *  - render form with Bootstrap theme (support Vertical form only at this moment)
 *  - reduce effort to repeated create labels, setting placeholder, etc. with flexibility
 *  - shortcut functions to append form elements (currently support: text, password, textarea, submit)
 *  - form validation and redirect page when failed, with field values maintained in flashdata
 *
 * TODO:
 *  - support more field types (checkbox, dropdown, upload, etc.)
 *  - automatically set "required" fields (matching with rule set)
 *  - add inline error handling
 */
class Form_builder {

    protected $mFormCount = 0;
    
    public function __construct()
    {
        $CI =& get_instance();
        
        $CI->load->helper('form');
        $CI->load->library('form_validation');
        $CI->load->config('form_validation');

        // CI Bootstrap libraries
        $CI->load->library('system_message');
    }

    // Initialize a form and return the object
    public function create_form($url = NULL, $multipart = FALSE, $attributes = array())
    {
        $url = ($url===NULL) ? current_url() : $url;
        $form = new Form($url, $multipart, $attributes);
        $this->mFormCount++;
        $form->set_id($this->mFormCount);
        return $form;
    }
}

/**
 * Class to store components appear on a form
 */
class Form {

    protected $CI;

    protected $mPostUrl;            // target POST URL
    protected $mFormUrl;            // URL to display form (default: same as $mPostUrl)
    protected $mRuleGroup;          // name of validation rule group (match with keys inside application/config/form_validation.php)
    protected $mMultipart;          // whether the form supports multipart
    protected $mAttributes;

    // session key to store field data before redirection
    protected $mFormData;
    protected $mSessionKey;

    // Constructor
    public function __construct($url = null, $multipart = null, $attributes = null)
    {
        $this->CI =& get_instance();

        $this->mPostUrl = $url;
        $this->mFormUrl = current_url();
        $this->mMultipart = $multipart;
        $this->mAttributes = $attributes;
    }
    
    // Update form ID and according data from session (to support multiple forms on one page)
    public function set_id($id)
    {
        $this->mSessionKey = 'form-'.$id;
        $this->mFormData = $this->CI->session->flashdata($this->mSessionKey);
    }

    // Update Rule Group for validation
    // Reference: http://www.codeigniter.com/user_guide/libraries/form_validation.html#calling-a-specific-rule-group
    public function set_rule_group($rule_group)
    {
        $this->mRuleGroup = $rule_group;
    }

    // Update target URL:
    //  - $this->mPostUrl = the page where the form is submitted to (i.e. "action" attribute of the form)
    //  - $this->mFormUrl = the page where the form is located at (for redirection when failed)
    public function set_post_url($url)
    {
        $this->mPostUrl = $url;
    }
    public function set_form_url($url)
    {
        $this->mFormUrl = $url;
    }

    // Render form open tag
    public function open()
    {
        if ($this->mMultipart)
            return form_open_multipart($this->mPostUrl, $this->mAttributes);
        else
            return form_open($this->mPostUrl, $this->mAttributes);
    }

    // Render form close tag
    public function close()
    {
        return form_close();
    }

    // Get saved value for single field
    public function get_field_value($name,$value)
    { 

        return isset($this->mFormData[$name]) ? $this->mFormData[$name] : set_value($name,$value);
    }

    /**
     * Basic fields
     */
    // Input field (type = text)
    public function field_text($name, $value = NULL, $extra = array('class'=>'form-control'))
    {  
        $data = array('type' => 'text', 'id' => $name, 'name' => $name);
        $value = $this->get_field_value($name,$value);
        
        return form_input($data, $value, $extra);
    }

    // Input field (type = email)
    public function field_email($name = 'email', $value = NULL, $extra = array())
    {
        $data = array('type' => 'email', 'id' => $name, 'name'  => $name);
        $value = $this->get_field_value($name,$value);
        return form_input($data, $value, $extra);
    }

    // Password field
    public function field_password($name = 'password', $value = NULL, $extra = array())
    {
        $data = array('id' => $name, 'name' => $name);
        $value = ($value===NULL) ? '' : $value;
        return form_password($data, $value, $extra);
    }

    // Textarea field
    public function field_textarea($name, $value = NULL, $extra = array())
    {
        $data = array('name' => $name);

        if(!empty($extra['id'])):
            $data['id'] = $extra['id'];
        endif;
        
        $value = $this->get_field_value($name,$value);
        return form_textarea($data, $value, $extra);
    }
    
    // Upload field
    public function field_upload($name, $value = NULL, $extra = array())
    {
        $data = array('id' => $name, 'name' => $name);
        $value = $this->get_field_value($name,$value);
        return form_upload($data, $value, $extra);
    }
    
    // Hidden field
    public function field_hidden($name, $value = NULL, $extra = array())
    {
        $data = array('id' => $name, 'name' => $name);
        $value = ($value===NULL) ? '' : $value;
        return form_hidden($data, $value, $extra);
    }

    // Dropdown field
    public function field_dropdown($name, $options = array(), $selected = array(), $extra = array())
    {
        return form_dropdown($name, $options, $selected, $extra);
    }


    /**
     * reCAPTCHA
     */
    public function field_recaptcha()
    {
        $config = $this->CI->config->item('recaptcha');
        $site_key = $config['site_key'];
        return '<div class="g-recaptcha" data-sitekey="'.$site_key.'"></div>';
    }
    
    /**
     * Buttons
     */
    // Submit button
    public function btn_submit($label = 'Submit', $extra = array())
    {
        $data = array('type' => 'submit');
        return form_button($data, $label, $extra);
    }

    // Reset button
    public function btn_reset($label = 'Reset', $extra = array())
    {
        $data = array('type' => 'reset');
        return form_button($data, $label, $extra);
    }

    /**
     * Bootstrap 3 functions
     */
    public function bs3_weekly_start_end($label, $name, $increment, $value = NULL, $default_value = NULL, $extra = array()){

    }


    public function bs3_toggle_button($label, $name, $value = NULL, $default_value = NULL, $extra = array()){

        $id="";
        $class="";
        $datawidget="";

        if(isset($value) && $value == 1):
            $value = "checked";
        else:
            $value = "";
        endif;

        if(isset($extra['id'])):
            $id = $extra['id'];
        else:
            $id = $name;
        endif;

        if(isset($extra['class'])):
            $class = $extra['class'];
        endif;

        if(isset($extra['data-widget'])):
            $datawidget = $extra['data-widget'];
        endif;

        $html = '<input type="checkbox" value="'.$value.'" '.$value.' data-toggle="toggle"  data-size="small" class="'.$class.'" id="'.$id.'"><input type="hidden" name="'.$name.'_status" id="'.$id.'_status" value="'.$value.'">';
        
        return $html;
    }

    public function bs3_toggle($label, $name, $value = NULL, $default_value = NULL, $extra = array()){

        $id="";
        $class="";
        $datawidget="";

        if(isset($value) && $value == 1):
            $check_value = "checked";
        else:
            $check_value = "";
        endif;

        if(isset($extra['id'])):
            $id = $extra['id'];
        else:
            $id = $name;
        endif;

        if(isset($extra['class'])):
            $class = $extra['class'];
        endif;

        if(isset($extra['data-widget'])):
            $datawidget = $extra['data-widget'];
        endif;

        $html = '<input type="checkbox" value="'.$value.'" '.$check_value.' data-toggle="toggle"  data-size="small" class="'.$class.'" id="'.$id.'"><input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$value.'">';
        
        return $html;
    }

    public function bs3_toggle_switch($label, $name, $value = NULL, $default_value = NULL, $extra = array()){

        $id="";
        $class="";
        $datawidget="";

        if(isset($value) && $value == 1):
            $check_value = "checked";
        else:
            $check_value = "";
        endif;

        if(isset($extra['id'])):
            $id = $extra['id'];
        else:
            $id = $name;
        endif;

        if(isset($extra['class'])):
            $class = $extra['class'];
        endif;

        if(isset($extra['data-widget'])):
            $datawidget = $extra['data-widget'];
        endif;

        $html = '<input name="'.$name.'" type="checkbox" '.$check_value.' data-toggle="toggle"  data-size="small" class="'.$class.'" id="'.$id.'">';
        
        return $html;
    }


    public function bs3_timepicker($label, $name, $increment, $value = NULL, $default_value = NULL, $extra = array())
    {   
        if($value == NULL):
            $value = $default_value;
        endif;

        $extra['class'] = 'form-control timepicker';
        $html = '<div class="bootstrap-timepicker">
                  <div class="form-group">
                    '.form_label($label, $name).'
                    <div class="input-group">
                      '.$this->field_text($name, $value, $extra).'
                      <div class="input-group-addon">
                        <i class="fa fa-clock-o"></i>
                      </div>
                    </div>
                    <!-- /.input group -->
                  </div>
                  <!-- /.form group -->
                </div>';
        return $html;
        
    }

    public function bs3_timepicker_sm($label, $name, $increment, $value = NULL, $default_value = NULL, $extra = array())
    {   
        if($value == NULL):
            $value = $default_value;
        endif;

        $extra['class'] = 'form-control timepicker';
        $html = '<div class="input-group-addon">
                    '.form_label($label, $name).'
                 </div>
                      '.$this->field_text($name, $value, $extra);
        return $html;
        //return '<div class="form-group">'.form_label($label, $name).$this->field_text($name, $value, $extra).'</div>';
    }


    public function bs3_text($label, $name, $value = NULL, $extra = array())
    {   
        
        if(isset($extra['class'])):
            $extra['class'] .= ' form-control';
        else:
            $extra['class'] = 'form-control';
        endif;

        
        return '<div class="form-group">'.form_label($label, $name).$this->field_text($name, $value, $extra).'</div>';
    }

    public function bs3_text_simple($label, $name, $value = NULL, $extra = array())
    { 

        if(isset($extra['class'])):
            $extra['class'] .= '';
        else:
            $extra['class'] = 'form-control-sm';
        endif;

        return $this->field_text($name, $value, $extra);
    }

    
    public function bs3_dropdown_simple($label, $name, $options = array(), $selected = array(), $extra = array())
    {

        if(isset($extra['class'])):
            $extra['class'] .= '';
        else:
            $extra['class'] = 'form-control';
        endif;

        return $this->field_dropdown($name, $options, $selected, $extra);
        //return form_dropdown($name, $options, $selected, $extra);
    }

    public function bs3_textarea_simple($label, $name, $value = NULL, $extra = array())
    {   
        if(isset($extra['class'])):
            $extra['class'] .= '';
        else:
            $extra['class'] = 'form-control input-sm';
        endif;
    
        return $this->field_textarea($name, $value, $extra);
    }

    public function bs3_text_simple2($label, $name, $value = NULL, $extra = array())
    { 
                
        if(isset($extra['class'])):
            $extra['class'] .= ' form-control input-sm';
        else:
            $extra['class'] = 'form-control input-sm';
        endif;

        return $this->field_text($name, $value, $extra);
    }

    public function bs3_email($label, $name = 'email', $value = NULL, $extra = array())
    {
        $extra['class'] = 'form-control';
        return '<div class="form-group">'.form_label($label, $name).$this->field_email($name, $value, $extra).'</div>';
    }

    public function bs3_password($label, $name = 'password', $value = NULL, $extra = array())
    {
        $extra['class'] = 'form-control';
        return '<div class="form-group">'.form_label($label, $name).$this->field_password($name, $value, $extra).'</div>';
    }

    public function bs3_textarea($label, $name, $value = NULL, $extra = array())
    {   
        if(isset($extra['class'])):
            $extra['class'] .= ' form-control';
        else:
            $extra['class'] = 'form-control';
        endif;
    
        return '<div class="form-group">'.form_label($label, $name).$this->field_textarea($name, $value, $extra).'</div>';
    }

    public function bs3_submit($label = 'Submit', $class = 'btn btn-primary', $extra = array())
    {
        $extra['class'] = $class;
        return $this->btn_submit($label, $extra);
    }
    public function bs3_dropdown($label, $name, $options = array(), $selected = array(), $extra = array())
    {

        $extra['class'] = 'form-control';
        return '<div class="form-group">'.form_label($label, $name).$this->field_dropdown($name, $options, $selected, $extra).'</div>';
        //return form_dropdown($name, $options, $selected, $extra);
    }
    

    /**
     * Bootstrap 4 functions
     */
    public function toggle_switch($label, $name, $value = null, $off_value = 0, $on_value = 1, $extra = array(), $info = null){

        if(isset($value) && $value == $on_value):
            $checked = 'checked=""';
        else:
            $checked = "";
        endif;

        $html = '<div class="form-group pb-1">
        <div class="float-right">
        <input name="'.@$name.'" type="hidden" value="'.$off_value.'">
            <input type="checkbox" name="'.$name.'" id="switchery0" class="switchery" '. $checked . 'data-switchery="true" style="display: none;" value="'.$on_value.'">
        </div>
        <label for="switchery0" class="">'.@$label.'</label>
        <p class="small">'.@$info.'</p>
        </div>';

        
        
        return $html;
    }
    public function bs4_toggle_switch($label, $name, $value = NULL, $default_value = NULL, $extra = array()){

        $id="";
        $class="";
        $datawidget="";

        if(isset($value) && $value == 1):
            $check_value = "checked";
        else:
            $check_value = "";
        endif;

        if(isset($extra['id'])):
            $id = $extra['id'];
        else:
            $id = $name;
        endif;

        if(isset($extra['class'])):
            $class = $extra['class'];
        endif;

        if(isset($extra['data-widget'])):
            $datawidget = $extra['data-widget'];
        endif;

        $html = '<input name="'.$name.'" type="checkbox" '.$check_value.' data-toggle="toggle"  data-size="small" class="'.$class.'" id="'.$id.'">';
        
        return $html;
    }
    public function bs4_text($label, $name, $value = NULL, $extra = array())
    {
        //Example function params
        array(
            'required' => 'required',
            'class'=>'form-control', 
            'pattern'=>'(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}', 
            'oninvalid'=>"this.setCustomValidity('Password must be atleast 6 characters. Must contain atleast 1 uppercase letter. Must contain 1 number.')", 
            'oninput'=>"setCustomValidity('')",
            'placeholder'=>'',
            'type'=>'text',
            'tooltip'=> array(
                'title'=>'',
                'data-content'=>'',
                'icon'=>'icon icon-question'
            )
        );

        if(isset($extra['required'])):
            $required = "required";
        else:
            $required = "";
        endif;

        if(!empty($label)):
            $labelHtml = form_label($label, $name);
        else:
            $labelHtml = form_label($label, $name);
        endif;

        if(isset($extra['type'])):
            $type = $extra['type'];
        else:
            $type = "text";
        endif;

        if(isset($extra['tooltip'])):
            $tooltip = '<div class="input-group-addon "><i class="'.@$extra['tooltip']['icon'].'" rel="popover" title="'.@$extra['tooltip']['title'].'" data-content="'.@$extra['tooltip']['data-content'].'" style=""></i></div> ';
        else:
            $tooltip = "";
        endif;


        if(!empty($extra['pattern'])):
            $pattern = 'pattern="'.$extra['pattern'].'"';
        else:
            $pattern = "";
        endif;

        if(!empty($extra['id'])):
            $id = 'id="'.$extra['id'].'"';
        else:
            $id = "";
        endif;


        return $html = '
            <div class="form-group">
                '.@$labelHtml.'
                <div class="input-group input-group-unstyled mb-1">                    
                    <input class="'.@$extra['class'].'" '.@$id.' type="'.@$type.'" value="'.@$value.'" placeholder="'.@$extra['placeholder'].'" name="'.@$name.'" '.@$pattern.' oninvalid="'.@$extra['oninvalid'].'" oninput="'.@$extra['oninput'].'" '.@$required.'>
                    '.@$tooltip.'                     
                </div>
            </div>
        ';
    }

    public function bs4_textarea($label, $name, $value = NULL, $extra = array())
    {   
        if(isset($extra['class'])):
            $extra['class'] .= ' form-control';
        else:
            $extra['class'] = 'form-control';
        endif;
    
        return '<div class="form-group">'.form_label($label, $name).$this->field_textarea($name, $value, $extra).'</div>';
    }


    public function bs4_multi_dropdown($label, $name, $options = array(), $selected = array(), $extra = array()){

        $extra['data-placeholder'] = $label;
        $extra['multiple'] = "multiple";
        $extra['style'] = "width:100%;";
        $extra['id'] = $name;

        $html = $this->bs3_dropdown_simple($label, $name ,(array)$options, (array)$selected, $extra);
        
        return $html;
    }
    
    public function bs4_dropdown($label, $name, $options = array(), $selected = array(), $extra = array())
    {
        //Example function params
        

        if(isset($extra['required'])):
            $required = "required";
        else:
            $required = "";
        endif;

        if(!empty($label)):
            $label = form_label($label, $name);
        else:
            $label = "";
        endif;

        if(isset($extra['type'])):
            $type = $extra['type'];
        else:
            $type = "text";
        endif;

        if(isset($extra['tooltip'])):
            $tooltip = '<div class="input-group-addon "><i class="'.@$extra['tooltip']['icon'].'" rel="popover" title="'.@$extra['tooltip']['title'].'" data-content="'.@$extra['tooltip']['data-content'].'" style=""></i></div> ';
        else:
            $tooltip = "";
        endif;

        return $html = '
            <div class="form-group">
                '.@$label.'
                <div class="input-group input-group-unstyled mb-1">                    
                    '.form_dropdown($name, $options, $selected, $extra).'
                    '.@$tooltip.'                     
                </div>
            </div>
        ';
    }

    public function bs4_email($label, $name = 'email', $value = NULL, $extra = array())
    {
        $extra['class'] = 'form-control';
        return '<div class="form-group">'.form_label($label, $name).$this->field_email($name, $value, $extra).'</div>';
    }

    public function bs4_password($label, $name = 'password', $value = NULL, $extra = array())
    {
        $extra['class'] = 'form-control';
        return '<div class="form-group">'.form_label($label, $name).$this->field_password($name, $value, $extra).'</div>';
    }



    public function bs4_datepicker_no_time($label = null, $name = null, $value = NULL, $default_value = NULL, $extra = array()){
        $html = '
        <label class="sr-only  calendar-click-'.$name.'" for="inlineFormInputGroup">'.$label.'</label>
        <div class="input-group mb-2">
            <div class="input-group-prepend">
                <div class="input-group-text  calendar-click-'.$name.'">'.$label.'</div>
            </div>'
            .$this->bs3_text_simple(' <i class="fa fa-calendar"></i>', $name ,$value ,array('class'=>'pickadate-empty-time form-control', 'autocomplete'=>'off','id'=>$name ,
            'pattern'=>'(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))')).'
            <div class="input-group-append">
                <div class="input-group-text  calendar-click-'.$name.'"><i class="fa fa-calendar"></i></div>
            </div>
        </div>
        <script>
         $(".calendar-click-'.$name.'").click(function() { 
            $("#'.$name.'").show().focus(); 
         });</script>';
        return $html;
    }

    public function bs4_submit($label = 'Submit', $class = 'btn btn-primary', $extra = array())
    {
        $extra['class'] = $class;
        return $this->btn_submit($label, $extra);
    }



    public function bs4_input($label, $name, $value = NULL, $extra = array())
    {   

        if(isset($extra['class'])):
            $extra['class'] .= ' form-control';
        else:
            $extra['class'] = 'form-control';
        endif;

        if(!empty($extra['type'])):
            $type = $extra['type'];
        else:
            $type = "text";
        endif;

        return '<div class="form-group">'.form_label($label, $name).$this->field_text($name, $value, $extra).'</div>';
    }









    ///////XVS
    public function xvs_score($label = null, $xvs_score = null){

        if(isset($xvs_score) && is_numeric($xvs_score)):
            $xvs_percent = $xvs_score * 100;
        else:
            $xvs_score = "No Data";
            $xvs_percent = "-";
        endif;

        if($xvs_score == "No Data"):
            $css = "info";
            $risk = "-";
        else:

            if($xvs_score >= 0.667):
                $css = "danger";
                $risk = "HIGH";
            elseif($xvs_score >= 0.334):
                $css = "warning";
                $risk = "MEDIUM";
            elseif($xvs_score < 0.334):
                $css = "success";
                $risk = "LOW";
            else:
                $css = "info";
                $risk = "-";
            endif;

        endif;


        return '<div class="card">
            <div class="card-body">
                <div class="card-body text-xs-center text-center">
                    <div class="card-header mb-2">
                        <span class="info darken-1">XVS Score</span>
                        <h3 class="font-large-2 grey darken-1 text-bold-200">'.$xvs_score.'</h3>
                    </div>
                    <div class="card-body">
                        <progress class="progress xvs-progress mt-1 mb-0" value="'.$xvs_percent.'" max="100"></progress>
                        <ul class="list-inline clearfix mt-2 mb-0">
                            <li class="border-right-grey border-right-lighten-2 pr-2">
                                <h2 class="'.$css.'  darken-1 text-bold-400">'.$xvs_percent.'%</h2>
                                <span class="grey">Probability</span>
                            </li>
                            <li class="border-right-grey border-right-lighten-2 pr-2 pl-2">
                                <h2 class="'.$css.'  darken-1 text-bold-400">'.$risk.'</h2>
                                <span class="grey">Risk</span>
                            </li>
                            <li class="pl-2">
                                <i class="icon-shield deep-blue font-large-2 float-xs-right '.$css.'"></i>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>


<style>
.xvs-progress {

  position: relative;
  width: 100%;
  height: 20px;

  background:  linear-gradient(to left, #ff3232 0%,#f4f142 33%,#ffff00 66%,#00ff00 100%) !important;
  background: -webkit-linear-gradient(to left, #ff3232 0%,#f4f142 33%,#ffff00 66%,#00ff00 100%) !important;
  background: -moz-linear-gradient(to left, #ff3232 0%,#f4f142 33%,#ffff00 66%,#00ff00 100%) !important;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25) inset;


}

.xvs-progress { color: rgba(0, 0, 0, .2) !important;  }
.xvs-progress::-moz-progress-bar { background: rgba(0, 0, 0, .2) !important; }
.xvs-progress::-webkit-progress-value { background: rgba(0, 0, 0, .2) !important;  }


.xvs-progress { 
   -webkit-appearance: none;
   appearance: none;
}

.xvs-progress::-webkit-progress-bar {
    background-image:

    -webkit-linear-gradient(right, #ff3232,#f4f142,#ffff00,#00ff00);
    border-radius: 2px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25) inset;
}

</style>';

    }

    public function bs4_xvs_score($label = null, $xvs_score = null){

        if(isset($xvs_score) && is_numeric($xvs_score)):
            $xvs_percent = $xvs_score * 100;
        else:
            $xvs_score = "No Data";
            $xvs_percent = "-";
        endif;

        if($xvs_score == "No Data"):
            $css = "info";
            $risk = "-";
        else:

            if($xvs_score >= 0.667):
                $css = "danger";
                $risk = "HIGH";
            elseif($xvs_score >= 0.334):
                $css = "warning";
                $risk = "MEDIUM";
            elseif($xvs_score < 0.334):
                $css = "success";
                $risk = "LOW";
            else:
                $css = "info";
                $risk = "-";
            endif;

        endif;


        return '<div class="card">
            <div class="card-content">
                <div class="card-body text-xs-center text-center">
                    <div class="card-header mb-2">
                        <span class="info darken-1">XVS Score</span>
                        <h3 class="font-large-2 grey darken-1 text-bold-200">'.$xvs_score.'</h3>
                    </div>
                    <div class="card-content">
                        <progress class="progress xvs-progress mt-1 mb-0" value="'.$xvs_percent.'" max="100"></progress>
                        <ul class="list-inline clearfix mt-2 mb-0">
                            <li class="border-right-grey border-right-lighten-2 pr-2">
                                <h2 class="'.$css.'  darken-1 text-bold-400">'.$xvs_percent.'%</h2>
                                <span class="grey">Probability</span>
                            </li>
                            <li class="border-right-grey border-right-lighten-2 pr-2 pl-2">
                                <h2 class="'.$css.'  darken-1 text-bold-400">'.$risk.'</h2>
                                <span class="grey">Risk</span>
                            </li>
                            <li class="pl-2">
                                <i class="icon-shield deep-blue font-large-2 float-xs-right '.$css.'"></i>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>


<style>
.xvs-progress {

  position: relative;
  width: 100%;
  height: 20px;

  background:  linear-gradient(to left, #ff3232 0%,#f4f142 33%,#ffff00 66%,#00ff00 100%) !important;
  background: -webkit-linear-gradient(to left, #ff3232 0%,#f4f142 33%,#ffff00 66%,#00ff00 100%) !important;
  background: -moz-linear-gradient(to left, #ff3232 0%,#f4f142 33%,#ffff00 66%,#00ff00 100%) !important;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25) inset;


}

.xvs-progress { color: rgba(0, 0, 0, .2) !important;  }
.xvs-progress::-moz-progress-bar { background: rgba(0, 0, 0, .2) !important; }
.xvs-progress::-webkit-progress-value { background: rgba(0, 0, 0, .2) !important;  }


.xvs-progress { 
   -webkit-appearance: none;
   appearance: none;
}

.xvs-progress::-webkit-progress-bar {
    background-image:

    -webkit-linear-gradient(right, #ff3232,#f4f142,#ffff00,#00ff00);
    border-radius: 2px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25) inset;
}

</style>';

    }
    ///////GUAGE
    public function guage($label = null, $percentage = 0){
        return '<div id="wrapper">
                    <svg id="meter">
                        <circle id="outline_curves" class="circle outline" 
                        cx="50%" cy="50%"></circle>
                         
                        <circle id="low" class="circle range" cx="50%" cy="50%"
                        stroke="#16AC11"></circle>
                         
                        <circle id="avg" class="circle range" cx="50%" cy="50%"
                        stroke="#F6E60A"></circle>
                         
                        <circle id="high" class="circle range" cx="50%" cy="50%"
                        stroke="#F60A0A"></circle>
                         
                        <circle id="mask" class="circle" cx="50%" cy="50%" >
                        </circle>
                         
                        <circle id="outline_ends" class="circle outline"
                        cx="50%" cy="50%"></circle>
                    </svg>
                    <img id="meter_needle" src="data:image/png;base64,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/GMzLdsAADoyaVRYdFhNTDpjb20uYWRvYmUueG1wAAAAAAA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/Pgo8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjYtYzA2NyA3OS4xNTc3NDcsIDIwMTUvMDMvMzAtMjM6NDA6NDIgICAgICAgICI+CiAgIDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+CiAgICAgIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiCiAgICAgICAgICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgICAgICAgICAgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iCiAgICAgICAgICAgIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiCiAgICAgICAgICAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIKICAgICAgICAgICAgeG1sbnM6cGhvdG9zaG9wPSJodHRwOi8vbnMuYWRvYmUuY29tL3Bob3Rvc2hvcC8xLjAvIgogICAgICAgICAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgICAgICAgICAgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iPgogICAgICAgICA8eG1wOkNyZWF0b3JUb29sPkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE1IChXaW5kb3dzKTwveG1wOkNyZWF0b3JUb29sPgogICAgICAgICA8eG1wOkNyZWF0ZURhdGU+MjAxOC0xMS0xMVQwMDowNTozNy0wNTowMDwveG1wOkNyZWF0ZURhdGU+CiAgICAgICAgIDx4bXA6TWV0YWRhdGFEYXRlPjIwMTgtMTEtMTFUMDA6MDU6MzctMDU6MDA8L3htcDpNZXRhZGF0YURhdGU+CiAgICAgICAgIDx4bXA6TW9kaWZ5RGF0ZT4yMDE4LTExLTExVDAwOjA1OjM3LTA1OjAwPC94bXA6TW9kaWZ5RGF0ZT4KICAgICAgICAgPHhtcE1NOkluc3RhbmNlSUQ+eG1wLmlpZDowMjk5YTY4MC1hMDk0LWIyNGYtYWFhNi05OTI0NmEzOTE4ZjQ8L3htcE1NOkluc3RhbmNlSUQ+CiAgICAgICAgIDx4bXBNTTpEb2N1bWVudElEPmFkb2JlOmRvY2lkOnBob3Rvc2hvcDpkYWZlZDU0Yy1lNTk3LTExZTgtOGE3Zi1jMjY4NDg1MzhkZWY8L3htcE1NOkRvY3VtZW50SUQ+CiAgICAgICAgIDx4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ+eG1wLmRpZDpkNzUzNmY0ZC0yMDVlLWMyNDgtOWQ2ZS1hYzMzNTFlZTYwYWI8L3htcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD4KICAgICAgICAgPHhtcE1NOkhpc3Rvcnk+CiAgICAgICAgICAgIDxyZGY6U2VxPgogICAgICAgICAgICAgICA8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmFjdGlvbj5jcmVhdGVkPC9zdEV2dDphY3Rpb24+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDppbnN0YW5jZUlEPnhtcC5paWQ6ZDc1MzZmNGQtMjA1ZS1jMjQ4LTlkNmUtYWMzMzUxZWU2MGFiPC9zdEV2dDppbnN0YW5jZUlEPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6d2hlbj4yMDE4LTExLTExVDAwOjA1OjM3LTA1OjAwPC9zdEV2dDp3aGVuPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxNSAoV2luZG93cyk8L3N0RXZ0OnNvZnR3YXJlQWdlbnQ+CiAgICAgICAgICAgICAgIDwvcmRmOmxpPgogICAgICAgICAgICAgICA8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmFjdGlvbj5zYXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6aW5zdGFuY2VJRD54bXAuaWlkOjAyOTlhNjgwLWEwOTQtYjI0Zi1hYWE2LTk5MjQ2YTM5MThmNDwvc3RFdnQ6aW5zdGFuY2VJRD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxOC0xMS0xMVQwMDowNTozNy0wNTowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OnNvZnR3YXJlQWdlbnQ+QWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKFdpbmRvd3MpPC9zdEV2dDpzb2Z0d2FyZUFnZW50PgogICAgICAgICAgICAgICAgICA8c3RFdnQ6Y2hhbmdlZD4vPC9zdEV2dDpjaGFuZ2VkPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgPC9yZGY6U2VxPgogICAgICAgICA8L3htcE1NOkhpc3Rvcnk+CiAgICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2UvcG5nPC9kYzpmb3JtYXQ+CiAgICAgICAgIDxwaG90b3Nob3A6Q29sb3JNb2RlPjM8L3Bob3Rvc2hvcDpDb2xvck1vZGU+CiAgICAgICAgIDxwaG90b3Nob3A6SUNDUHJvZmlsZT5zUkdCIElFQzYxOTY2LTIuMTwvcGhvdG9zaG9wOklDQ1Byb2ZpbGU+CiAgICAgICAgIDx0aWZmOk9yaWVudGF0aW9uPjE8L3RpZmY6T3JpZW50YXRpb24+CiAgICAgICAgIDx0aWZmOlhSZXNvbHV0aW9uPjcyMDAwMC8xMDAwMDwvdGlmZjpYUmVzb2x1dGlvbj4KICAgICAgICAgPHRpZmY6WVJlc29sdXRpb24+NzIwMDAwLzEwMDAwPC90aWZmOllSZXNvbHV0aW9uPgogICAgICAgICA8dGlmZjpSZXNvbHV0aW9uVW5pdD4yPC90aWZmOlJlc29sdXRpb25Vbml0PgogICAgICAgICA8ZXhpZjpDb2xvclNwYWNlPjE8L2V4aWY6Q29sb3JTcGFjZT4KICAgICAgICAgPGV4aWY6UGl4ZWxYRGltZW5zaW9uPjEyNTwvZXhpZjpQaXhlbFhEaW1lbnNpb24+CiAgICAgICAgIDxleGlmOlBpeGVsWURpbWVuc2lvbj40NDY8L2V4aWY6UGl4ZWxZRGltZW5zaW9uPgogICAgICA8L3JkZjpEZXNjcmlwdGlvbj4KICAgPC9yZGY6UkRGPgo8L3g6eG1wbWV0YT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAKPD94cGFja2V0IGVuZD0idyI/PlS6Qd8AAAAgY0hSTQAAeiUAAICDAAD5/wAAgOkAAHUwAADqYAAAOpgAABdvkl/FRgAAEKNJREFUeNrsnc1uI8cRx2ua1JKU9xVycRwEtrPyQQa0pwWGfIEcckyAJLecEiAfcPwAeYdccskbOL7kA5BEGMgKsR3b59ixsw+wF5JakeJMLjPr3mbP8KtnpqrrXwCx2pUWGvav//+urqlpJnmeE0JXGAwBoCMAHQHoCEBHADoC0BGAjgB0BKAjAB0B6AhARwA6AtARgI4AdASgAzoC0BGAjgB0BKAjAB0B6AhARwA6AtARgI4AdMTR0e/6Ah4/ftza7/rqqy/Pz87e+RMR9ax/XhMRffHF5z9//fXvftL2+3/69Kk+6G3Ew4cPPyciOj9/d7Vc3p3N54tXvv/a6Smdn7/7x9VqdTKbzd6B0iMAfnJysnr+/Pl51c/MFwuaLxbn5c/HDr4fO/DZbHa2z/+ZzWZnsYM3sSv8kP9bggd0YbHN0jWDPxh6kiRBXk2p/BjgNvimAXQxZtEp/ZB1fJfMH0pXFG2oHdAZQopN7VFBjznjBnRYPKC3AScmF4kGOqxdGfSbm5uk6d8xm83O2vg9gL4f8MbvI0wmk89iAB+LvZvZbPZWC78nactZAL1e5eULY6ZI6clkMvm0pd3Bo3KSSVa7iUDlJs/ztpUOe+9S5W0DmEwmHwN698B7bUIoXEW0xYuEbls7EZn5fP52B+OGNb0jpZsO3oP9e6H0Dqy9tPfWonAVI9nixSt9PB5/1NG4QekdrOdJhwPfc9wG0Fu0906uv3AXKL2jZCppuTCz8fslrutGKHDqUunOtg323nLmbhaLxZttX4BVoDGA3k4SR0ySKOO4DqC3lMR1cv2FuxipGTyy9zDLjKjGCulretf2bqD0djP3JE3TfyB716d0OI4SpXNYz32TD2t6S9s1Dls2KL3NNb3LC0nT9K9SwWNND7imS9m2Sd+nc7oOKL2lQeakdqzpDa7nxDCDxz69rWRusVh8D66ja5/ObU2H0luyd45bSUBXoHSC0hsIhtU4WiwW3yeh9XeJ9s4lcxcbsPew+3Ss6U0OdpqmHzLL3mHvClTuvSYJ9Xfpz7JxGD/Yu7KtErJ3pXtjVOSUDWwiUe0GKtelcumJHCelA7oWhaVp+oFExWOfrtDyJdt7D7B1ZO8vlb5YLN5ASqHD3jkmT0jkQgfHe+lQejcqN0yvD9CVZu+ArkhVKMM2rHIkctr26Uy6ZrCmawyrI5ZIUCOF5AcYOV4T1nQlIa5PDtDDjKEotRshSkL9QKnScTsV9o4agibo6JNTAh11d9g7q64ZPOzQoH2y7JqxmiOh9IbBI2KFLrhrBhW5wCo3gI3sHRF59p7guvTYO5SueZ/OsGtGnNpRe1cIX2ILNPcdBpQOhQN6dCGxOdJAPcGXHqzpSuCLao6EvYcbRzFqR0WuIaUDuo4BRfaucLsmRu1Ssnd7MHtQuQ57fwkeBwxFDF1Y1wyecGnQ3pGDIHvnEdI6YvEsm8LrNcJUjk5Yhft0tEppg56m6V8wIfUpHaEIOtZzKB0B6HClaKBjX65Y6QnAw965A2ffMoUybICw2qDRIxcwKZKwZRPTEQt7DzuWItQuIXvn3iolzpVENVEIaJVC9n5oCD1gSMxtYEn2jufukL0jYs/ecYdNkb1LbJVCu5RGJQF6oGDeKiWqDRoVOYXuJOlhB8CG0hGADoWLh45MXbHSE6gd9o5QBF1i9o67bEcMnKQtGxI5ZPCAfsjASWmVEnUwsJgmCiGnSqEx8pAQ/Fls3mvm2AYtxd6l5B4i2qCRvSu8ZjzAiOydrWKkKh32rkDtKM6ECu6tUtICFbnm7B3QjxxEBJTOdhn6ANARLJM7g0HS9z5wpBjWdISGyYoyLJTOcr+LjljYOyLm7F1Mq9QWtwL0Q8AL+wA+JHL7hPBWKazpAe1d6ocLsW2ZQvYeMKQcDIwHGFuYsNw6Yo2AQZMGnn1HLCpyCpcm9tCFtkohe1cW7JcnCQ87QOVQOgLQoXTx0GNqlUK7lFL4gI4A9CgVX9H7jjLsnvtcJHWwd0Ss2XsMrVKAfih4Ya1SUPo+EVGrFHu1c7d35BzI3qH42LP3GKwdW7YDVI5yrMJ9OhI5bdAFnyrFuvcd2XHgkND7zv1hB8nWzrb3HUpvblzZqh3QFdYaDLOBig08sndF8FnXG2DvCicuyrBKs0wkQFA6IvZaA8fsXXqrFOz9GPBSW6W4HwHOAnqErVKs13nO9m4AG9k7IvLsHb1xiuwdSle+T4fatUHHB/DpVHpMWzXcZZM2SEjkEFjTEVuBs+uTM1BE+ODeBo0jxZrPTdi1QcPemx1blmpHGbZFpQO6oEFC9o44dBLD3mu2N7G0SqE4sy94nCoVMfSIW6Wg9D3tPaZWKbRLKcveoXRFe3TWbdCGqcpjVTrKsLGrnev7YQkdrVI6lQ6FK4KOzB1Kh9oBHREN9ESJ2nGXTQl8JHJY0wFdg+JZtkFzLcOKD85t0LD3liczhzZoTtl7jKdKsWyDZtlEEVGrFBojd1Q57rAptHcTGXB2ExrZu8LtJx5gVBiGocqhdIX79BgTObRL1QVapXQqPZrg2gbN8WGH2ANlWEWBihxCL3TYuWKloygDe4faNUGH4pVA1/JcOu6ywdqRvcf+AXxY0+vAR3iqFLs2aMNM5TgNWqG9x5hjsGuDRvbezhizUjseYFQ4qQ2zAcGpUgr36YkCpcPeFQSKM3WB/jhd0LGew96jXdcBHaEPOu6dK1Z61PAret/Vl2GxfVMMHXavcMsGhSuBzvaTh6F0RFRq59Y508Pc0GHvL8Er+dRFtXfZNBwlVvV+Ye8Ud38c7L0me9dwIAESOaX7dBY2bxipPErwHHvfUYbtaBnrsvedDfTIW6VY9b4jY1aYsHJ6wkUDeGTvipUOe4fKdUDHbVTFSk+gdNi7hnUd0GH3uqCrudnC7Qhw2LtCd+PURNEDbB32/hK8klYptfaurVWKXbLKxd6RWyB7h9pjz96xR1dk7xqVToS7bKrUjvvpdig6VQprurJg0wbN6VHlaINbGzSU3uEWtas2aEBvd6xZqL3LMqx6pWu090QpeGTvipWu+rEm1N2VQNdaglUNXau9q4W+0URRFC80retq7Z3NILQRnDpiTdcqx5qu09415xWqyrDala76hkuSpumHMFwd9q5V5SjDctu/NhmctqUoziic5J33vSsqzLCBD6W3GGma/k199p6m6d+h8Pihq66+LRaLN51xUFecsQdBo9L19ciNx+OpRrWPx+Mr6rhBsjOlz+fztzVCn8/nb2mzd9xRs7arqux9PB7/UzPx8Xj8kZbsPbGs/QeaoRdLW2dqNx2o/F9wd6LxeHyjJpHTrnJnHKJXenJxcbEC7m9jMpl8rGnLhiCi2Wz2qAuLNy2qfA3MXrX/G0rXq/a4oF9cXGTAW6v2z2KDjircdrWftTlOJrZZDLV3Dz0pZjGCkdoNVK5P7QYq16d2bNkURlPQE1j7URafiFQ6rJ3vuDUBHc+cMx/DRpQOa+c9fgYqZ2nxj5ocy0bsvYubCJFaPHul4wyZZlwz4a50fNxWuHX9Uyn2DugCLN4Eho1u10CR57l9+lbCWelQuYDxDK304LNSubUbrkrX+HHYbUWviSweSofSj1a6KRIQRJhEzkhROiKsvQe3eBNS5VjTG7H34LWP4PaONb1RpcPeFSmdtb1D6eGVbmjz0xtZ2PvLtefy8vKHYNXIli3Y4UShlY4bLoHi8vLyRx7onSs9qbF4RBg2jSTIIZTugk9Go9F/wCzorij4bAq9ZUMyd2SMRqMvyf/ZrGwSuQ3VX11d/RToDo+rq6ufhUramk7k8Flr4a2dOEKvUjsNh8P/gt3+4YybnBZoIqLr6+tfAOH+0ca4NQE9BzreY2mavMjhcPg1uO1l7V9b45hzVXpe97q+vv4lUO5l7b+SpvTcMxFoOBx+A5w7qfybbSIKNQlMINiZ9Wdm//36+vrXQLqTyn9jgc1Cg27K3jeAl98bDAb/A9bqKMYn9wjI/bfOoecV63pGRGv7oqfT6W+Btjqm0+nvagQUXPUhlE4e2O4FE9S+VeWZNYZrZyxzZ6xZZe+Z54IzIsqm0+l7QOxV+XuenGgbeDaJXFZz0TkR5Tc3Nz8G5m+jGI8S5trzyjhn7zb4+yq1F0ndM+Amurm5+Yln7NbO+JWvoFl86Ox9vW3GTqfT32MdHzyrAL52wNu2z64441r8vfXaSOqKWa55HX9/h3HzKZ2dveeORblv4BX4WsFbtp55bN03ZsGLNE0ofe15E24WShoV77zfvAb6umrMOCrdBr7yzN6NmasFvPU+XWd0x2jlgBen9JXnTWzM3NjBe95fvsdYsVS6Ha5VrbaAj9rqB4PBM4+l1wFfeZbE4JHk+WGTKEk22rfK40f6RPSAiIZENCKiUyJ6WPw5IqJB8X37icxX4uLi4s8xALeydPJY+oqI7ojolojmxWtRvO6IaGnBfwXSocyagl4+o35SwB0WsF8rXqPi3wbFz1T2yT958uQPd3d334nEzsmzu7kjohcW6BL6bQF8VbUcHgu9iXYpn8UvrTdy78xgb5IynU7fl1a989h5la2747J0QK+bWMubULpP7Q8KdY8KpZ8Wr2HxvdLmjfX/SZrqPVZeB3zlUXn5elF8774OPCd7L6GV4PsF+KFl86eWzZdre38X8Bzhb4FNFjAb+LKAe2vBvi1ed5YbVt5Z4wbdVrux1D5w1D6ywJ8U4M0u4DlMgB1g+4C7yZur8BdW8la7VeMKnazs/MTK5k+tjN5N6nr7gm9rApS5xQ6gfcDdbN2n8hee5I0kQbdtvmfZ/MDZxo0cmz8KvD0Byq8PmQh28rgH5Drg9jp+twV4ra1zh+4mdTb4kfMq1/yTUOA7jCrgSwv4rQPcXce3Zu3HQu+3MAilvW17ft3uAes5wBMhwH019aVl6y8s0EunUpk1VYFrE7o968kC78JMKgaPaPM4k4Q5cPeu2dJZx28roGcUsPGRg9JdxVMNaLffu1/8aZiq3p7Y7l3GpbOOu0r3WXouXel2ZBUZfh30zLL6HjPVu5M5d6qQS4/K6xTeGvC2oOcFoKxm4PIKi8yK5C6jzSMzu0j08i127gPuruPLLtbxLpReBd5XpnQbKtdWASezFJ/T5nEnSYOgiTYfNXK7hOyK250FflvilscIvQp81aM8NvgHxUCV4F27rzs/NTkScu6x8rpmEXsdv7P+viR/61jrwNuG7gO/bUDLQa0D36P6w3OTA4D7bDyvuDYX+NJRtgs87xJ4F9Bd8EmF4n2D+sCC3qdXb9TUKf+Y/bbv6Z37CuC+26S+/sBOgXcFvW6drGoNLgf2xAPeB38fu8+3ZOW+XMMF7n5d1/ff+Zk8/Y5/f76DzZcDXMKugt4n/1HZuyo+95RRc2sNdhXu622ra2xkAZwDdLfAkdBmS/V9cZ0rD2T3ZZytnTlA6XUJm++hhLqnUlqttEmCXqd6Yw18rxjYnvXqO18bJ8nbZvH5njsJ9wHDe6p+rDjjBpsjdF+Sl1tfr511u+eB7Mvod83g3ZJq5ikUrSu+rrJxlmfq9Rleky+xMlZhZu1k6vZr2/ZtnzXdl7n7XrkU2JyhV2X4PidwP1HCeNbyXfbreU32Xnf4D3mKTeyjTzIid6CTBT2j+pOoDy3O1J3jJkbVkqHvMgFoR9jJDsqsqsyRVNDSodfBSbZA3mdJiQKwL/4/AHrXpp6GSIBjAAAAAElFTkSuQmCC" alt="">
                 
                            <label id="lbl" id="value" for="">'.$label.': -</label>
                        </div>
        <Script>
            /* Set radius for all circles */
        var r = 50;
        var circles = document.querySelectorAll(".circle");
        var total_circles = circles.length;
        for (var i = 0; i < total_circles; i++) {
            circles[i].setAttribute("r", r);
        }
         

        var meter_dimension = (r * 2) + 100;
        var wrapper = document.querySelector("#wrapper");
        wrapper.style.width = meter_dimension + "px";
        wrapper.style.height = meter_dimension + "px";
         
        /* Add strokes to circles  */
        var cf = 2 * Math.PI * r;
        var semi_cf = cf / 2;
        var semi_cf_1by3 = semi_cf / 3;
        var semi_cf_2by3 = semi_cf_1by3 * 2;
        document.querySelector("#outline_curves")
            .setAttribute("stroke-dasharray", semi_cf + "," + cf);
        document.querySelector("#low")
            .setAttribute("stroke-dasharray", semi_cf + "," + cf);
        document.querySelector("#avg")
            .setAttribute("stroke-dasharray", semi_cf_2by3 + "," + cf);
        document.querySelector("#high")
            .setAttribute("stroke-dasharray", semi_cf_1by3 + "," + cf);
        document.querySelector("#outline_ends")
            .setAttribute("stroke-dasharray", 2 + "," + (semi_cf - 2));
        document.querySelector("#mask")
            .setAttribute("stroke-dasharray", semi_cf + "," + cf);

         
        function range_change_event(xvs) {
            var percent = xvs;
            var meter_value = semi_cf - ((percent * semi_cf) / 100);
            mask.setAttribute("stroke-dasharray", meter_value + "," + cf);
            meter_needle.style.transform = "rotate(" + 
                (270 + ((percent * 180) / 100)) + "deg)";
            lbl.textContent = "'.$label.': "+percent;
        }
        range_change_event('.$percentage.');
        </Script>

                        <style>
                       

        #wrapper {
          position: relative;
          margin: auto;
        }
        #meter {
          width: 100%; height: 100%;
          transform: rotateX(180deg);
        }
        .circle {
          fill: none;
        }
        .outline, #mask {
          stroke-width: 65;
        }
        .range {
          stroke-width: 60;
        }
        #slider, #lbl {
          position: absolute;
        }
        #slider {
          cursor: pointer;
          left: 0;
          margin: auto;
          right: 0;
          top: 58%;
          width: 94%;
        }
        #lbl {
            background-color: #4B4C51;
            border-radius: 2px;
            color: white;
            font-family: "courier new";
            font-size: 15pt;
            font-weight: bold;
            padding: 4px 4px 2px 4px;
            /* right: 41%; */
            top: 57%;
            /* margin: 0 auto; */
            /* display: block; */
            width: 100%;
            text-align: center;
        }
        #meter_needle {
          height: 40%;
          left: 0;
          margin: auto;
          position: absolute;
          right: 0;
          top: 10%;
          transform-origin: bottom center;
          /*orientation fix*/
          transform: rotate(270deg);
        }

        </style>';
    }

    /**
     * Success / Error messages
     */
    public function messages()
    {
        return $this->CI->system_message->render();
    }

    /**
     * Form Validation
     */
    public function validate()
    {
        // only run validation upon form submission
        $post_data = $this->CI->input->post();
    
        if ( !empty($post_data) )
        {
            // Step 1. reCAPTCHA verification (skipped in development mode)
            $recaptcha_response = $this->CI->input->post('g-recaptcha-response');
            if ( isset($recaptcha_response) && ENVIRONMENT!='development' )
            {
                $config = $this->CI->config->item('recaptcha');
                $secret_key = $config['secret_key'];
                $recaptcha = new \ReCaptcha\ReCaptcha($secret_key);
                $resp = $recaptcha->verify($recaptcha_response, $_SERVER['REMOTE_ADDR']);
                
                if (!$resp->isSuccess())
                {
                    // save POST data to flashdata
                    $this->CI->session->set_flashdata($this->mSessionKey, $post_data);

                    // failed
                    //$errors = $resp->getErrorCodes();
                    $this->CI->system_message->set_error('ReCAPTCHA failed.');

                    // redirect to form page (interrupt other operations)
                    redirect($this->mFormUrl);
                }
            }

            // Step 2. CodeIgniter form validation

            $result = $this->CI->form_validation->run($this->mRuleGroup);
            
            if ($result===FALSE)
            {   
                
                // save POST data to flashdata
                $this->CI->session->set_flashdata($this->mSessionKey, $post_data);

                // store validation error message from CodeIgniter
                $this->CI->system_message->set_error(validation_errors());

                // redirect to form page (interrupt other operations)
                redirect($this->mFormUrl);
            }
            else
            {
                // return TRUE to indicate the result is positive
                return TRUE;
            }
        }
    }
}