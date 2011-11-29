<?php
/*
 * This class is responsible for handling all application controlling
 * sending the correct requests to the correct place on the models
 *
 *
 * CLASS OVERVIEW
 *
 *
 */
if (!class_exists('PlulzControllerAbstract'))
{
    /**
     * Avoid db bloating with milions of revisions
     */
    if (!defined('WP_POST_REVISIONS')) define('WP_POST_REVISIONS', 5);

    abstract class PlulzControllerAbstract extends PlulzObjectAbstract
    {
        protected $_ajaxEvents;

        protected $_blogurl;

        protected $_homeUrl;

        abstract function handleAjax( $action );

        abstract function handlePost( $data );
        
        public function __construct()
        {
            $this->_blogurl  = get_bloginfo('home');

            $this->_homeUrl  = get_bloginfo('url');

            if ( is_admin() )
                $this->startAdmin();
            else
                $this->startFrontEnd();

            $this->startAjax();

            // Lets check for the post events only after all wp is loaded, this will avoid A LOT of troubles
            $this->setAction('wp_loaded', 'post');

            parent::__construct();

        }

        /**
         * Starts the admin hooks, by default append the notice system to the usser
         * @return void
         */
        public function startAdmin()
        {
            // Admin notices
            $this->setAction( 'admin_notices', 'adminMessage' );
        }

        public function startFrontEnd()
        {
            // Front end notices
            $this->setAction( 'front_notices', 'frontMessage' );
        }

        /**
         * Start the ajax wordpress handling system, capturing all ajax events coming from
         * both admin or front end areas
         * 
         * @return
         */
        public function startAjax()
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
         * handling object , handleAjax or handlePost
         * @return void
         */
        public function post()
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

            $this->handlePost( $data );
        }

        /**
         * Hooked on admin message system, show notices and errors
         * @return void
         */
        public function adminMessage()
        {
            $this->_PlulzNotices->showAdminNotices();
        }

        /**
         * Called on template
         * @return void
         */
        public function frontMessage()
        {
            echo $this->_PlulzNotices->showFrontNotices();
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