<?php
/**
 * This is a abstract class that when the plugin need to use all the admin integration functionality it must extends it
 * and declares all the variables / methods inside it
 *
 * The advantage is that everything else is easier since there are many pre built functions that help manage the Wordpress Admin
 * Panel
 *
 * CLASS OVERVIEW
 *
 * This class should be extended whenever we want to create a Theme or Plugin since it contains many helpfull
 * methods and variables in order to make an easy integration with the wordpress CMS
 *
 */
// Make sure there is no bizarre coincidence of someone creating a class with the exactly same name of this plugin
if ( !class_exists("PlulzAdminAbstract") )
{
    abstract class PlulzAdminAbstract extends PlulzObjectAbstract
    {
        /**
         * The css files used in the above created pages
         * @var string
         */
        protected $_menuPagesCSS;                // CSS to be used in the Custom created admin page

        /**
         * Options saved in the options table of wordpress db, directly connected to the $names variable
         * @var array
         */
        protected $_options;                     // Options for the input in the current custom admin page

        /**
         * Very rarely a different page is needed to save options referent to the plugin/theme
         * if thats the case it should be stored in this variable
         * default = options.php
         * @var string
         */
        protected $_action;                      // Action where the submit of the admin page should occurr, default to options.php

        /**
         * The current path to the assets used mainly on the admin side of the theme/plugin
         *
         * @var array (css, js, img)
         */
        protected $_adminAssets;                 // The current theme / plugin where the assets are located

        /**
         * Deprecated
         * @var string
         */
        protected $_stylesheetName;

        /**
         * Information regarding the ajax requests that are going to be performed on the site
         * works both for back and front end
         * @var array
         */
        protected $_ajaxRequest;                 // Handle ajax requests on admin side

        /**
         * Array with the pages, its titles and configurations and its subpages in the admin panel
         * @var array
         */
        protected $_menuPages;                   // Array of every admin page and subpages of the current theme/plugin

        /**
         * The fields that must be showed to the user in the current custom admin page
         * @var array (multidimensional)
         */
        protected $_metaboxFields;                  // Array containing the elements to be show on the current page

        /**
         * Custom mce buttons to be added in the tinymce editor
         * @var array
         */
        protected $_mceButtons;              // Array containing new mce buttons if want to add it

        /**
         * Holds which metaboxes to be shown on each of the admin created pages
         * @var array
         */
        protected $_adminPageSidebarMetaboxes;


        protected $_groupSuffix = '_options_page';

        /**
         * Default curl options
         * @var array
         */
        public static $CURLOPT = array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_USERAGENT      => 'plulz-php-1.0'
        );

        /**
         * Default Plulz domain links
         * @var array
         */
        public static $DOMAIN = array(
            'www'   =>  'http://www.plulz.com',
            'feed'  =>  'http://www.plulz.com/feed/rss',
            'api'   =>  'http://api.plulz.com'
        );

        public function __construct()
        {
            $this->_action      =   admin_url('options.php');

            parent::__construct();
            
        }
        public function init()
        {
            parent::init();

            // Multiples main menu pages?
            if ( !isset($this->_menuPages[0]) )
                $this->_menuPages = array( 0 => $this->_menuPages );

            // Multiples new mce buttons?
            if ( !isset($this->_mceButtons[0]) )
                $this->_mceButtons = array( 0 => $this->_mceButtons );

            if ( empty($this->_action) )
                $this->_PlulzNotices->addError( $this->_name, __('Must define the $action property otherwise the options wont be correctly saved on the admin') );
        }

        /**
         *
         * Get the latest news from Pazzani Tech blog
         * @return xml
         */
        public function fetchRSS()
        {
            $args = array(
                'feed'  =>  1
            );

            $results = $this->_requestAPI( $args );

            // Return the fetched XML converted to an SimpleXML object
            if ( $results['feed'] && !empty($results['feed']) )
                return simplexml_load_string($results['feed']);
            else
                return false;
        }

        /**
         *
         * Method that returns the newest releases plugins from Plulz
         * @param $args
         * @return array
         */
        public function fetchApi( $args )
        {
            if (empty($args))
                return false;

            $services = array(
                'feed'  =>  0,
                'api'   =>  1
            );

            if (is_array($args))
            {
                foreach ($args as $key => $value) // the params passed could be like 'help' => true or type => 'xml'..
                    $data[$key] = $value;
            }
            else
                $data = array($args => true);

            $results = $this->_requestAPI($services, $data);

            // Return the fetched XML converted to an object
            if ( $results['api'] && !empty($results['api']) )
                return simplexml_load_string($results['api']);
            else
                return false;

        }

        /***************************************************************************************************************
         *                                   GENERIC ADMIN METHODS
         **************************************************************************************************************/

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

        /**
         * Method that add the MCE button in the Tiny MCE
         * @param $buttons
         * @return void
         */
        public function addMCEButton( $buttons )
        {
            foreach ($this->_mceButtons as $newButton)
            {
                array_push($buttons, $newButton['separator'], $newButton['id']);
            }
            return $buttons;
        }

        /**
         * Method that register the action the new button will do
         * @param $plugin_array
         * @return void
         */
        public function actionMCEButton( $plugin_array )
        {
            foreach ($this->_mceButtons as $newButton)
            {
                $plugin_array[$newButton['id']] = $this->_adminAssets['js'] . 'tinyMCE/custom-mce.js';
            }
            return $plugin_array;
        }

        /***************************************************************************************************************
         *                              WORDPRESS CUSTOM ADMIN PAGES
         **************************************************************************************************************/

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
                $group  =   $page['menu_slug'] . $this->_groupSuffix;

                register_setting( $group, $name );

                if ( isset($page['submenus']) && !empty($page['submenus']) )
                {
                    foreach( $page['submenus'] as $submenu )
                    {
                        $name   =   $submenu['menu_slug'];
                        $group  =   $submenu['menu_slug'] . $this->_groupSuffix;

                        register_setting( $group, $name );
                    }
                }

            }
        }

        /**
         *
         * Method to add the facebook in the left menu panel of wordpress
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

                // Custom Styles for Main Pages
                if ( !empty($this->_adminAssets['css']) )
                {
                    wp_register_style( $this->_stylesheetName, $this->_adminAssets['css'] . 'plulz-admin-style.css');
                    $this->setAction( 'admin_print_styles-' . $menuPagesCSS , 'addAdminStyle'  );
                }

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

                        // Custom styles for subpages
                        if ( !empty($this->_adminAssets['css']) )
                            $this->setAction( 'admin_print_styles-' . $subMenuCSS , 'addAdminStyle' );                    }
                }
            }
        }

        /**
         * Output Stylesheets and Javascripts only on plugin/themes admin pages
         * They are all called here to avoid conflicting with wordpress default admin pages as explained in the link
         * http://codex.wordpress.org/Function_Reference/wp_enqueue_script#Load_scripts_only_on_plugin_pages
         * @return void
         */
        public function addAdminStyle()
        {
            wp_enqueue_style( $this->_stylesheetName );
            wp_enqueue_script( 'dashboard' );
            wp_enqueue_script( 'postbox' );
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_script( 'json-form' );
            wp_enqueue_style(  'thickbox' );
            wp_enqueue_script( 'media-upload' );
            wp_enqueue_script( 'plulz-admin', $this->_adminAssets['js'] . 'plulz-admin.js', array( 'jquery','media-upload','thickbox', 'dashboard', 'postbox' ));
        }


        /**
         * Method that helps create metaboxes areas
         * @param string $width
         * @return void
         */
        public function createMetaboxArea( $width = '100%' )
        {
            echo    "<div class='postbox-container' style='width:{$width}'>";
            echo        "<div class='metabox-holder'>";
            echo            "<div class='meta-box-sortables ui-sortable'>";
        }

        /**
         * Close the metaboxarea
         * @return void
         */
        public function closeMetaboxArea()
        {
            echo            "</div>";
            echo        "</div>";
            echo    "</div>";
        }

        /**
         * Create any kinda of metabox in wordpress admin
         * @param string $title
         * @param string $content
         * @param null $extras
         * @return void
         */
        public function createMetabox( $title = 'Config', $content, $extras = null)
        {
            if (isset($extras) && !empty($extras))
            {
                if (!is_array($extras))
                {
                    $msg = 'The metabox $extras from ' . $title . ' must be an array';

                    $this->_PlulzNotices->addError($this->_name, $msg);
                    return;
                }

                extract($extras); // should have $id and $class
            }

            isset($class) && !empty($class) ?   $class = " class='postbox {$class}'"  :   $class = ' class="postbox"';
            isset($id) && !empty($id)   ?   $id = " id={$id}"   :   $id = '';


            echo    "<div{$class}{$id}>" .
                        "<div class='handlediv' title='Click to Toggle'><br/></div>" .
                        "<h3 class='hndle'>{$title}</h3>" .
                        "<div class='inside'><table>";
            echo            $content;
            echo        "</table></div>" .
                    "</div>";

        }

        /**
         * This method creates admin metaboxes with the $args passed
         *
         * @internal param string $width
         *
         * @internal param array $args
         * @param bool $table
         * @return void;
         */
        public function createAdminPage( $table = false )
        {
            $groupName = PlulzTools::getValue('page', $this->_name);

            $url    =   self::$DOMAIN['www'];
            $group  =   $groupName . $this->_groupSuffix;
            $title  =   $this->_metaboxFields['title'];

            echo "<div id='plulzwrapper' class='wrap'>";
            echo    "<a id='plulzico' href='{$url}' target='_blank'> {$title} </a>";
            echo    "<h2>{$title}</h2>";

                    $this->createMetaboxArea('70%');

            echo        "<form method='post' action='". $this->_action ."'>";

                            settings_fields( $group );

                            foreach ( $this->_metaboxFields['elements'] as $fields)
                            {
                                $title = $fields['title'];

                                $table ? $content = '<table>' : $content = '';

                                foreach ($fields['fields'] as $input)
                                {
                                    $table  ?   $content .= '<tr class="form-table">' : '';

                                    $content .= $this->_addRow( $input, null, $table );

                                    $table  ?   $content .= '</tr>' : '';
                                }

                                $table ? $content .= '</tbody></table>' : '';

                                $this->createMetabox( $title, $content );

                            }


            echo            "<p class='submit'><input type='submit' class='button-primary' value='" . __('Save Changes') ."'/></p>";

            echo        "</form>";

                    $this->closeMetaboxArea();

                    $this->createMetaboxArea('29%');
                        $this->createAdminPageSidebar();
                    $this->closeMetaboxArea();

            echo "</div>";

        }

        /**
         *
         * Method that outputs a sidebar block on plugins admin configuration pages
         * @param array $args
         * @return void
         */
        public function createAdminPageSidebar( $args = null )
        {
            $toFetch = array(
                'type'      =>  'xml',
                'plugin'    =>  $this->_name
            );

            $paginaAtual = PlulzTools::getValue('page', $this->_name);

            if (empty($this->_adminPageSidebarMetaboxes) || empty($this->_adminPageSidebarMetaboxes[$paginaAtual]))
                return;

            $boxes = isset($args) && !empty($args) ? $args : $this->_adminPageSidebarMetaboxes[$paginaAtual];

            // Procura as informações de quais boxes devem ser buscadas no servidor
            foreach($boxes as $box)
                $toFetch[$box]  =   true;

            $links = $this->fetchApi( $toFetch );

            foreach($boxes as $box)
            {
                $function = '_' . $box;

                if($links)
                    $this->$function( $links->$box );
                else
                    $this->$function();
            }

        }

       /*
         * This method show the latest news from the plulz blog on the wordpress dashboard, it is feeded with xml
         *
         * @return void
         */
        public function dashboardNews()
        {
            $news = $this->fetchRSS();

            // If somethings wrong with the feed, lets quietly leave this function...
            if (!$news)
                return;

            $maxHeadlines = 4;

            $output = '<ul>';

            // Atom or RSS ?
            if (isset($news->channel)) // RSS
            {
                for($i=0; $i<$maxHeadlines; $i++)
                {
                    $url 	= $news->channel->item[$i]->link;
                    $title 	= $news->channel->item[$i]->title;
                    $desc = $news->channel->item[$i]->description;

                    $output .= '<li><a class="rsswidget" href="'.$url.'">'.$title.'</a><div class="rssSummary">'.$desc.'</div></li>';
                }

            }
            else if (isset($news->entry)) // ATOM
            {
                for($i=0; $i<$maxHeadlines; $i++)
                {
                    $urlAtt = $news->entry->link[$i]->attributes();
                    $url	= $urlAtt['href'];
                    $title 	= $news->entry->title;
                    $desc	= strip_tags($news->entry->content);

                   $output .= '<li><a class="rsswidget" href="'.$url.'">'.$title.'</a><div class="rssSummary">'.$desc.'</div></li>';
                }
            }

            $output .=  '</ul>' .
                        '<br class="clear" />' .
                        '<div style="margin-top:10px;border-top:1px solid #ddd;padding-top:10px;text-align:left;position:relative">' .
                        '<img src="' . $this->_adminAssets['img'] . 'tiny-logo-plulz.png" style="position:absolute;bottom:0;left:0;" /><a href=' . self::$DOMAIN['www'] . ' style="padding-left:16px;");">Wordpress Plugins at Plulz</a>' .
                        '</div>';

            echo $output;
        }

        /*
         * Hook functions to the Dashboard of Wordpress and try to append it to the top
         *
         * @return void
         */
        public function hookDashboard()
        {
            // Hook latest news only if its allowed
            wp_add_dashboard_widget('PlulzDashNews', 'Plulz Latest News', array( &$this, 'dashboardNews'));

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


        /*************************************************************************************
         *                             PROTECTED METHODS
         *************************************************************************************/

        /**
         * Method responsible for checking and connecting to the pazzani tech API
         * @param array $args
         * @param array|string $params
         * @return array
         */
        protected function _requestAPI( $args, $params = array() )
        {
            // First check if curl is enabled
            if ( !function_exists('curl_init') )
                throw new Exception('PlulzAPI needs the CURL PHP extension.');

            $default = array(
                'api'   =>  0,
                'feed'  =>  1
            );

            // overwrite the default values (if there is any new values)
            if ( is_array($args) )
                $services = array_merge( $default, $args );
            else
                $services = $default;

            if ($services['api'])
            {
                if ( !empty($params) )
                {
                    $api = curl_init();
                    $apiOpts = self::$CURLOPT;

                    $apiOpts[CURLOPT_URL] = self::$DOMAIN['api'];
                    $apiOpts[CURLOPT_POST] = 1;
                    $apiOpts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
                    $apiOpts[CURLOPT_HTTPHEADER] = array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");

                    curl_setopt_array($api, $apiOpts);

                    $apiResults = curl_exec($api);

                    curl_close($api);
                }
                else
                    $apiResults = 'You need to send some params to fetch from the API';
            }

            if ($services['feed'])
            {
                $feed = curl_init();
                $feedOpts = self::$CURLOPT;

                $feedOpts[CURLOPT_URL] = self::$DOMAIN['feed'];

                curl_setopt_array($feed, $feedOpts);

                $feedResults = curl_exec($feed);

                curl_close($feed);
            }

            return array(
                    'api'   =>  isset($apiResults) ? $apiResults : '',
                    'feed'  =>  isset($feedResults) ? $feedResults : ''
            );
        }
    }
}