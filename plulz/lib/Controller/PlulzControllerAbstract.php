<?php
/*
 * This class is responsible for handling all application instances
 * sending the correct requests to the correct place / models
 *
 * CLASS OVERVIEW
 *
 *
 * Class also sets a important configuration that, a maximum number of revisions for posts in wordpress
 *
 */

if (!class_exists('PlulzControllerAbstract'))
{
    abstract class PlulzControllerAbstract extends PlulzObjectAbstract
    {
        protected $_serverDir;

        protected $_templateDir;

        protected $_appControllerDir;

        protected $_libControllerDir;

        protected $_serverAssets;

        protected $_assets;

        protected $_nonce;

        protected $_blogurl;

        protected $_homeUrl;

        protected $_adminAjaxUrl;

        protected $_adminOptionsUrl;

        public $PlulzForm;

        /**
          * Holds all registered ajax events
          * @var array
          */
        protected $_ajaxEvents;


        public function __construct()
        {
            global $plulz_server_directory, $plulz_template_directory;

            $this->_serverDir = $plulz_server_directory;

            $this->_templateDir = $plulz_template_directory;

            $this->_appControllerDir = $this->_serverDir . '/plulz/app/Controller/';

            $this->_libControllerDir = $this->_serverDir . '/plulz/lib/Controller/';

            $this->_serverAssets = $this->_serverDir . '/plulz/webroot/';

            $this->_assets = $this->_templateDir . '/plulz/webroot/';

            $this->_blogurl         =   get_bloginfo('home');

            $this->_homeUrl         =   get_bloginfo('url');

            $this->_adminAjaxUrl    =   admin_url( 'admin-ajax.php' );

            $this->_adminOptionsUrl =   admin_url('options.php');

            $this->PlulzForm    =  new PlulzForm( $this->_name );

            parent::__construct();
        }

        public function init()
        {
            parent::init();

            if ( empty($this->_nonce) )
                $this->PlulzNotices->addError($this->_name, __('A nonce must be declared in the ' . $this->_className . ' class', $this->_name ));

            if ( is_admin() )
                $this->startAdmin();
            else
                $this->startFrontEnd();
            
            $this->setAjaxEvents();

            // Handle Ajax events
            $this->setAction( 'wp_loaded', 'ajax');
        }

        public function startAdmin(){}

        public function startFrontEnd(){}

        public function adminAssets(){}

        public function handleAjax( $action ) {}

        public function handlePost( $data ) {}


        /**
         * Start the ajax wordpress handling system, capturing all ajax events coming from
         * both admin or front end areas and registering the event accordingly with an wordpress
         * action
         *
         * @return void
         */
        public function setAjaxEvents()
        {
            if ( empty($this->_ajaxEvents) )
                return;

            foreach ($this->_ajaxEvents as $type)
            {
                if ( $type == 'public' )
                    $wpajax = 'wp_ajax_nopriv_';
                else
                    $wpajax = 'wp_ajax_';

                foreach( $type as $event )
                    $this->setAction( $wpajax . $event, 'post' );
            }
        }

        /**
         * Handle all incoming post requests from the site and redirect the event to the correct
         * handling object, they could be a handleAjax or handlePost method
         *
         * @return void
         */
        public function ajax()
        {
            // Theres no post, just leave
            if ( empty($_POST) )
                return;

            $data = PlulzTools::getValue($this->_name);

            // Try to get an action from anywhere
            $action = isset($data['action']) && !empty($data['action']) ? $data['action'] : PlulzTools::getValue('action');

            // No action means no post or missing info from the $_POST
            if ( !isset($action) || empty($action) )
                return;

            // Check to see if the action is an ajax action
            if (!empty($this->_ajaxEvents))
            {
                foreach($this->_ajaxEvents as $type) // restricted or public
                {
                    if ( in_array( $action, $type ) )
                    {
                        $this->handleAjax( $action );
                        exit;
                    }
                }
            }
        }


        /**
         * Include any template file
         * @param $folder
         * @param $name
         * @return bool
         */
        public function includeTemplate($name, $folder = '')
        {
            $name = ucfirst($name);

            if (empty($folder))
                $file = "$this->_serverDir/plulz/app/View/$name.php";
            else
                $file = "$this->_serverDir/plulz/app/View/$folder/$name.php";

            if(file_exists($file))
                include($file);
            else
            {
                $file = "$this->_serverDir/plulz/lib/View/$folder/$name.php";

                if(file_exists($file))
                    include($file);
            }

            return false;
        }

        /**
         * Shorthand for the theme shared folder
         * @param $name
         * @return bool
         */
        public function includeThemeShared($name)
        {
            $this->includeTemplate( $name, 'Shared/Theme');
        }

        /**
         * Shorthand for the admin shared folder
         * @param $name
         */
        public function includeAdminShared($name)
        {
            $this->includeTemplate( $name, 'Shared/Admin');
        }

        /**
         * action helper
         * @param $event
         * @param $method
         * @param int $priority
         * @param int $accepeted_args
         * @return void
         */
        public function setAction( $event, $method, $priority = 10, $accepeted_args = 1 )
        {
            if ( is_array( $method ) )
                add_action( $event, array( &$this->$method[0], $method[1]), $priority, $accepeted_args );
            else
                add_action( $event, array( &$this, $method), $priority, $accepeted_args );
        }

        /**
         * filter helper
         * @param $event
         * @param $method
         * @param int $priority
         * @param int $accepeted_args
         * @return void
         */
        public function setFilter( $event, $method, $priority = 10, $accepeted_args = 1 )
        {
            if ( is_array( $method ) )
                add_filter( $event, array( &$this->$method[0], $method[1]), $priority, $accepeted_args );
            else
                add_filter( $event, array( &$this, $method), $priority, $accepeted_args );
        }

        /**
         * Shorthand to create shortcodes
         * @param $event
         * @param $method
         * @return void
         */
        public function setShortCode( $event, $method )
        {
            if ( is_array( $method ) )
                add_shortcode( $event, array( &$this->$method[0], $method[1]) );
            else
                add_shortcode( $event, array( &$this, $method ) );
        }
    }

}