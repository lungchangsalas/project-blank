<?php 

class MY_Controller extends MX_Controller {
	// Values to be obtained automatically from router
	protected $mModule = '';			// module name (empty = Frontend Website)
	protected $mCtrler = 'home';		// current controller
	protected $mAction = 'index';		// controller function being called
	protected $mMethod = 'GET';			// HTTP request method

	// Config values from config/site.php
	protected $mSiteConfig = array();
	protected $mSiteName = '';
	protected $mMetaData = array();
	protected $mScripts = array();
	protected $mStylesheets = array();

	// Values and objects to be overrided or accessible from child controllers
	protected $mTitle = '';
	protected $mMenu = array();
	protected $mBreadcrumb = array();
	protected $mBodyClass = '';

	// Multilingual
	protected $mMultilingual = FALSE;
	protected $mLanguage = 'english';
	protected $mAvailableLanguages = array();

	// Data to pass into views
	protected $mViewData = array();
	public $userTimezone;

	// Login user
	protected $mPageAuth = array();
	protected $mUser = NULL;
	protected $mUserGroups = array();
	protected $mUserMainGroup;
	
    public $userNotifications;
    public $userMessages;

    public function __construct()
	{
		parent::__construct();

		// router info
		$this->mModule = $this->router->fetch_module();
		$this->mCtrler = $this->router->fetch_class();
		$this->mAction = $this->router->fetch_method();
		$this->mMethod = $this->input->server('REQUEST_METHOD');
		
		// initial setup
		$this->_setup();
	}

	private function _setup()
    {   
        $site_config = $this->config->item('site');
        // load default values
        $this->mSiteName = $site_config['name'];
        $this->mTitle = $site_config['title'];

        $this->mMenu = empty($site_config['menu']) ? array() : $site_config['menu'];
        $this->mMenuUser = empty($site_config['menu_user']) ? array() : $site_config['menu_user'];
        $this->mMenuSettings = empty($site_config['menu_settings']) ? array() : $site_config['menu_settings'];
        $this->mMenuSupport = empty($site_config['menu_support']) ? array() : $site_config['menu_support'];

        $this->mMetaData = empty($site_config['meta']) ? array() : $site_config['meta'];
        $this->mScripts = $site_config['scripts'];
        $this->mStylesheets = $site_config['stylesheets'];
        $this->mPageAuth = empty($site_config['page_auth']) ? array() : $site_config['page_auth'];

        // restrict pages
        $uri = empty($this->mModule) ? $this->uri->uri_string() : str_replace($this->mModule.'/', '', $this->uri->uri_string());

        if ($this->mCtrler!='home')
        {
            $page = $this->mMultilingual ? lang('home') : 'Home';
            $this->push_breadcrumb($page, '');  
        }
        
        // get user data if logged in
        if (class_exists('ion_auth') && $this->ion_auth->logged_in() )
        {
            $this->mUser = $this->ion_auth->user()->row();
            if ( !empty($this->mUser) )
            {
                $this->mUserGroups = $this->ion_auth->get_users_groups($this->mUser->id)->result();

                // TODO: get group with most permissions (instead of getting first group)
                if(!empty($this->mUserGroups[0]->name)):
                    $this->mUserMainGroup = $this->mUserGroups[0]->name;    
                endif;
            }
        }
        
        $this->mSiteConfig = $site_config;
    }

	// Verify user login (regardless of user group)
	protected function verify_login($redirect_url = NULL)
	{ 
		if ( !$this->ion_auth->logged_in() )
		{
			if ( $redirect_url==NULL )
				$redirect_url = $this->mSiteConfig['login_url'];

			redirect($redirect_url);
		}
	}

	// Verify user authentication
	// $group parameter can be name, ID, name array, ID array, or mixed array
	// Reference: http://benedmunds.com/ion_auth/#in_group
	protected function verify_auth($group = 'members', $redirect_url = NULL)
	{
		if ( !$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group) )
		{
			if ( $redirect_url==NULL )
				$redirect_url = $this->mSiteConfig['login_url'];
			
			redirect($redirect_url);
		}
	}



	// Render template
	protected function render($view_file, $layout = 'default')
	{  
        //We need to revert back to make use of ion_auth logged in user in views, such as menu, ugly, but works.
        //$this->user_model->setModule($this->mModule);
		// automatically generate page title
		if ( empty($this->mTitle) )
		{
			/*if ( $this->mAction=='index' )
				$this->mTitle = humanize($this->mCtrler);
			else
				$this->mTitle = humanize($this->mAction);*/
		}

		$this->mViewData['module'] = $this->mModule;
		$this->mViewData['ctrler'] = $this->mCtrler;
		$this->mViewData['action'] = $this->mAction;

		$this->mViewData['site_name'] = $this->mSiteName;		
		$this->mViewData['page_title'] = $this->mTitle;
		$this->mViewData['current_uri'] = empty($this->mModule) ? uri_string(): str_replace($this->mModule.'/', '', uri_string());
		$this->mViewData['meta_data'] = $this->mMetaData;
		$this->mViewData['scripts'] = $this->mScripts;
		$this->mViewData['stylesheets'] = $this->mStylesheets;
		$this->mViewData['page_auth'] = $this->mPageAuth;

		$this->mViewData['base_url'] = empty($this->mModule) ? base_url() : base_url($this->mModule).'/';
		$this->mViewData['menu'] = $this->mMenu;
		$this->mViewData['menu_user'] = $this->mMenuUser;
		$this->mViewData['menu_settings'] = $this->mMenuSettings;
		$this->mViewData['menu_support'] = $this->mMenuSupport;

		if(empty($this->mViewData['user'])):
            $this->mViewData['user'] = $this->mUser;
        endif;

		$this->mViewData['ga_id'] = empty($this->mSiteConfig['ga_id']) ? '' : $this->mSiteConfig['ga_id'];
		$this->mViewData['body_class'] = $this->mBodyClass;

		
		$this->mViewData['inner_view'] = $view_file;
		$this->load->view('_base/head', $this->mViewData);
		$this->load->view('_layouts/'.$layout, $this->mViewData);
		$this->load->view('_base/foot', $this->mViewData);
		
	}

	// Output JSON string
	protected function render_json($data, $code = 200)
	{
		$this->output
			->set_status_header($code)
			->set_content_type('application/json')
			->set_output(json_encode($data));
			
		// force output immediately and interrupt other scripts
		global $OUT;
		$OUT->_display();
		exit;
	}

	// Add breadcrumb entry
	// (Link will be disabled when it is the last entry, or URL set as '#')
	protected function push_breadcrumb($name, $url = '#', $append = TRUE)
	{
		$entry = array('name' => $name, 'url' => $url);

		if ($append)
			$this->mBreadcrumb[] = $entry;
		else
			array_unshift($this->mBreadcrumb, $entry);
	}
}