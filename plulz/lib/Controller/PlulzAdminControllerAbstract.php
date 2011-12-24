<?php

if (!class_exists('PlulzAdminControllerAbstract'))
{

    abstract class PlulzAdminControllerAbstract extends PlulzControllerAbstract
    {
        /**
         * The admin menu pages
         * @var array
         */
        protected $_menuPages;

        /**
         * The admin object should be here
         * @var object
         */
        protected $_admin;

        /**
         * Metabox helper
         * @var \PlulzMetabox
         */
        public $PlulzMetabox;


        /**
         * Holds the new mcebuttons information
         * @var array
         */
        protected $_mceButtons;

        /**
         * Holds the options array for the current page/object
         * @var array
         */
        protected $_options;

        /**
         * Sufix stuf
         * @var string
         */
        public $groupSuffix = '_options_page';


        public function __construct()
        {
            $this->_name        =   $this->_admin->getName();

            if (empty($this->_options))
                $this->_options     =   get_option( PlulzTools::getValue('page', $this->_name) );

            $this->PlulzMetabox = new PlulzMetabox();

            parent::__construct();
        }

        public function startAdmin()
        {
            parent::startAdmin();

            $this->setAction( 'init', 'createMCEButton');

            // Admin notices
            $this->setAction( 'admin_notices', 'adminMessage' );

            $this->setAction( 'admin_init', 'register' );

            $this->setAction( 'wp_dashboard_setup', 'hookDashboard' );

            $this->setAction( 'admin_menu', 'page' );
        }

        public function adminMessage()
        {
            $this->PlulzNotices->showAdminNotices();
        }

        /**
         *
         * Method that register the Plugin dependencies to be rendered on the admin panel of the blog
         * this method must be called on the admin_init hook
         *
         * @return void
         */
        public function register()
        {
            foreach ( $this->_menuPages as $page )
            {
                $name   =   $page['menu_slug'];
                $group  =   $page['menu_slug'] . $this->groupSuffix;

                register_setting( $group, $name );

                if ( isset($page['submenus']) && !empty($page['submenus']) && is_array($page['submenus']))
                {
                    foreach( $page['submenus'] as $submenu )
                    {
                        $name   =   $submenu['menu_slug'];
                        $group  =   $submenu['menu_slug'] . $this->groupSuffix;

                        register_setting( $group, $name );
                    }
                }

            }
        }

        /**
         *
         * Method that add admin pages in Wordpress
         * @return void
         */
        public function page()
        {
            // @ref http://codex.wordpress.org/Function_Reference/add_menu_page
            foreach ($this->_menuPages as $menuPage)
            {
                $menuPagesCSS = add_menu_page(
                                   $menuPage['page_title'],           // $page_title
                                   $menuPage['menu_title'],           // $menu_title
                                   $menuPage['capability'],           // $capability
                                   $menuPage['menu_slug'],            // $menu_slug
                                   $menuPage['callback'],             // $function
                                   $menuPage['icon_url'],             // $icon_url
                                   $menuPage['position']              // $position
                                );

                $this->setAction( 'admin_print_styles-' . $menuPagesCSS , 'adminAssets'  );
                $this->setAction( 'admin_print_scripts-' . $menuPagesCSS, 'adminAssets' );

                $submenu = $menuPage['submenus'];

                if ( isset($submenu) && is_array($submenu) && !empty($submenu) )
                {
                    if ($submenu[0] == null)
                        $submenu = array(   0   =>  $submenu    );

                    foreach ($submenu as $innerMenu)
                    {
                        $subMenuCSS = add_submenu_page(
                                        $menuPage['menu_slug'],              // $parent_slug
                                        $innerMenu['page_title'],            // $page_title
                                        $innerMenu['menu_title'],            // $menu_title
                                        $innerMenu['capability'],            // $capability
                                        $innerMenu['menu_slug'],             // $menu_slug
                                        $innerMenu['callback']               // $function
                                    );

                        $this->setAction( 'admin_print_styles-' . $subMenuCSS , 'adminAssets' );
                        $this->setAction( 'admin_print_scripts-' . $subMenuCSS, 'adminAssets');
                    }
                }
            }
        }

        public function adminAssets()
        {
            wp_register_style( $this->_name . 'Stylesheet', $this->_assets .'css/plulz-admin-style.css');
            wp_enqueue_style( $this->_name . 'Stylesheet');
            wp_enqueue_script( 'plulz-admin', $this->_assets .'js/plulz-admin.js');
            
            wp_enqueue_script( 'dashboard' );
            wp_enqueue_script( 'postbox' );
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_script( 'json-form' );
            wp_enqueue_style(  'thickbox' );
            wp_enqueue_script( 'media-upload' );
        }

        /*
         * Hook functions to the Dashboard of Wordpress and try to append it to the top
         *
         * @return void
         */
        public function hookDashboard()
        {
            // Hook latest news only if its allowed
            wp_add_dashboard_widget('PlulzDashNews', 'Plulz Latest News', array( &$this, 'dashboardNews') );

            // Lets try to make our widget goes to the top
            global $wp_meta_boxes;

            // Get the regular dashboard widgets array
            // (which has our new widget already but at the end)
            $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

            // Backup and delete our new dashbaord widget from the end of the array
            $example_widget_backup = array('PlulzDashNews' => $normal_dashboard['PlulzDashNews']);
            unset($normal_dashboard['PlulzDashNews']);

            // Merge the two arrays together so our widget is at the beginning
            $sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);

            // Save the sorted array back into the original metaboxes
            $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
        }


        /**
         * Method that adds new buttons in the admin TinyMCE editor
         *
         * @return void
         */
        public function createMCEButton()
        {
            // Quit if no mceButtons were set but the function was wrongly called on the controller
            if ( empty($this->_mceButtons) )
                return;

            // Check for user permissions
            if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
                return;

            // Show only if the rich_editing mode is on
            if ( get_user_option('rich_editing') == 'true')
            {
                $this->setFilter('mce_external_plugins', 'actionMCEButton' );
                $this->setFilter('mce_buttons', 'addMCEButton' );
            }
        }

        /***************
         * CUSTOM MCE BUTTONS
         */

        /**
         * Method that add the MCE button in the Tiny MCE
         * @param $buttons
         * @return void
         */
        public function addMCEButton( $buttons )
        {
            foreach ($this->_mceButtons as $newButton)
                array_push($buttons, $newButton['separator'], $newButton['id']);

            return $buttons;
        }

        /**
         * Method that register the action the new button will do
         * @param $plugin_array
         * @return something
         */
        public function actionMCEButton( $plugin_array )
        {
            foreach ($this->_mceButtons as $newButton)
                $plugin_array[$newButton['id']] = $this->_assets . 'js/tinyMCE/custom-mce.js';

            return $plugin_array;
        }

        /**********************************
         * Widgets
         */

        /**
         * Renders dashboardNews widget
         */
        public function dashboardNews()
        {
            $this->set('content', $this->_admin->getNews());
            $this->set('domain', PlulzAdmin::$DOMAIN['www']);
            $this->includeAdminShared('DashboardNews');
        }

        /*
         * This method show options in the admin page about the creator, donations and helpfull links
         *
         * @param array $helpLinks
         * @return void
         */
        public function helpMetabox()
        {
            $this->set('content', $this->_admin->getHelp());
            $this->includeAdminShared('Help');
        }

        /*
         * This method show options in the admin page about the creator, donations and helpfull links
         *
         * @param array $links
         * @return void
         */
        public function lovedMetabox()
        {
            $this->set('content', $this->_admin->getLoved());
            $this->includeAdminShared('Loved');
        }

        /*
         * This method show options in the admin page about the creator, donations and helpfull links
         *
         * @param array @donateLinks
         * @return void
         */
        public function donateMetabox()
        {
            $this->set('content', $this->_admin->getDonate());
            $this->includeAdminShared('Donate');

        }
    }
}