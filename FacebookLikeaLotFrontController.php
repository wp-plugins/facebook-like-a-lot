<?php

define( "LIKE_A_LOT_ASSETS",  get_template_directory_uri() . '/plulz/webroot/');
define( "LIKE_A_LOT_LIB_CLASSES",  get_template_directory() . '/plulz/lib/Model/');

define( "LIKE_A_LOT_ASSETS",  WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) . 'assets/');
define( "LIKE_A_LOT_PLUGIN_LIB_CLASSES", plugin_dir_path(__FILE__) . 'plulz/lib/Model/');
define( "LIKE_A_LOT_PLUGIN_LIB_CONTROLLER", plugin_dir_path(__FILE__) . 'plulz/lib/Controller/');
define( "LIKE_A_LOT_PLUGIN_APP_CLASSES", plugin_dir_path(__FILE__) . 'plulz/app/Model/');
define( "LIKE_A_LOT_PLUGIN_APP_CONTROLLER", plugin_dir_path(__FILE__) . 'plulz/app/Controller/');

// Make sure there is no bizarre coincidence of someone creating a class with the exactly same name of this plugin
if ( !class_exists("FacebookLikeaLotFrontController") )
{
    require_once(  LIKE_A_LOT_PLUGIN_LIB_CLASSES . 'PlulzAbstractObjectClass.php'  );
    require_once(  LIKE_A_LOT_PLUGIN_LIB_CLASSES . 'PlulzAbstractAdminClass.php'  );
    require_once(  LIKE_A_LOT_PLUGIN_LIB_CLASSES . 'PlulzFacebookAbstractClass.php'  );
    require_once(  LIKE_A_LOT_PLUGIN_LIB_CLASSES . 'PlulzFacebookLikeaLotClass.php'  );

    require_once(  LIKE_A_LOT_PLUGIN_LIB_CONTROLLER . 'PlulzControllerAbstract.php'  );

    if (!class_exists("BaseFacebook"))
        require_once(  LIKE_A_LOT_PLUGIN_LIB_CLASSES . 'PlulzBaseFacebookAbstractClass.php'  );

    require_once(  LIKE_A_LOT_PLUGIN_APP_CLASSES . 'LikeaLotAdminClass.php'  );
    require_once(  LIKE_A_LOT_PLUGIN_APP_CLASSES . 'PlulzFacebookLikeaLotClass.php'  );

    class FacebookLikeaLotFrontController extends PlulzControllerAbstract
    {
        protected $_LikeaLotFacebook;      // Holds current theme main class

        public function __construct()
        {
            $this->_LikeaLotFacebook = new LikeaLotFacebook();

            // Add extra post type and Make it the default loop
            // Add Extra Taxonomies
            // Add new user roles
            $this->setAction( 'init', 'wpInit' );

            // og tags
			$this->setAction( 'wp_head', 'fbOpenGraph' ); // og tag

			// fbLanguages
			$this->setAction( 'language_attributes', 'fbLanguages' );

            // Add the extra css
            $this->setAction( 'wp_print_styles', 'loadCSS' );

            // Shortcodes
            $this->setShortCode( 'facebook_like_a_lot', 'shortSocialLike' );

        }

       /**
        *
        * This method insert the default config of the plugin and also creates a database
        * to control what comments were already added and those that were not
        * @return void
        */
        public function install()
        {

        }

        /**
        *
        * Method called when the plugin is uninstalled
        * @return void
        */
        public function remove()
        {
            delete_option($this->name);
        }

        public function startAdmin()
        {
            parent::startAdmin();

            $this->setAction( 'init', 'addNewMCEButton' );

            $this->setAction( 'admin_init', 'adminInit' );

            $this->setAction( 'admin_notices', 'adminMessage' );

            $this->setAction( 'wp_dashboard_setup', 'adminHookDashboard' );

            $this->setAction( 'admin_menu', 'adminMenu' );

            $this->setAction( 'add_meta_boxes', 'adminPostMetabox' );
        }

        public function startFrontEnd()
        {
            parent::startFrontEnd();
        }
        

        public function handleAjax($action) {}

        public function handlePost($data)   {}

        /****************************************************************************************
         *                            ADMIN RELATED METHODS
         ****************************************************************************************/

        public function addNewMCEButton()
        {
            $this->PlulzFacebookLikeaLot->createMCEButton();
        }

        /**
         * Creates new pages in the admin area
         * @return void
         */
        public function adminMenu()
        {
            $this->PlulzFacebookLikeaLot->page();
        }

        public function adminInit()
        {
            $this->PlulzFacebookLikeaLot->register();
        }

        public function adminHookDashboard()
        {
            $this->PlulzFacebookLikeaLot->hookDashboard();
        }

        public function adminMessage()
        {
            $this->PlulzFacebookLikeaLot->adminMessage();
        }

        public function wpAdminPostPage() {}

        public function fbOpenGraph()
        {
            $this->PlulzFacebookLikeaLot->PlulzFacebook->addOpenGraph();
        }

        public function fbLanguages()
        {
            $this->PlulzFacebookLikeaLot->PlulzFacebook->languages();
        }

        /**
         * Method that loads extra CSS into the Theme
         * @return void
         */
        public function loadCSS()
        {
            $this->PlulzFacebookLikeaLot->loadCSS();
        }

        /**********************************************************************************
         *                            SHORTCODES
         **********************************************************************************/

        public function shortSocialLike( $atts )
        {
            echo $this->PlulzFacebookLikeaLot->PlulzFacebook->socialLike( $atts );
        }

    }

    $PlulzFacebookLikeaLotFrontController = new PlulzFacebookLikeaLotFrontController();
}