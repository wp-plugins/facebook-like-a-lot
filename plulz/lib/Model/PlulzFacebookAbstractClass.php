<?php

// Make sure there is no bizarre coincidence of someone creating a class with the exactly same name of this plugin
if ( !class_exists("PlulzFacebookAbstract") )
{
    abstract class PlulzFacebookAbstract extends BaseFacebook
    {
        /**
         * Store the current post id information, if it exists
         * @var int
         */
        protected $_postID;

        /**
         * Default fonts used by facebook
         * @var array
         */
        protected $_fonts =
        array( 'arial', 'lucida grande', 'segoe ui', 'tahoma', 'trebuchet ms', 'verdana');

        /**
         * Default facebook color schemes
         * @var array
         */
        protected $_colorScheme =
        array( 'light', 'dark');
        
        /**
         * All allowed options of like button
         * @var array
         */
        protected $_likeOptions;

        /**
         * All Allowed options for the comment box
         * @var array
         */
        protected $_commentOptions;

        /**
         * All allowed options for the comment box
         * @var array
         */
        protected $_activityOptions;


        /** All allowed options for the feed box
         * @var array
         */
        protected $_feedOptions;
        
        /**
         * Verifies if the facebook js is already loaded into the page
         * @var int
         */
        protected $_loadedJS = 0;

        protected static $_SupportedKeys =
        array('state', 'code', 'access_token', 'user_id');

        public function __construct()
        {
            $options = get_option( $this->_name );

            $this->appId    =   isset($options['app'])    ?   $options['app'] : '';
            $this->secret   =   isset($options['secret']) ?   $options['secret'] : '';


            $fbApiCredentials = array(
                    'appId'	 => $this->appId,
                    'secret' => $this->secret
            );

            parent::__construct( $fbApiCredentials );
        }

        public function init()
        {
            global $wp_query;

            // If the page is found, lets grab the current post id\
            $this->_postID   =   isset( $wp_query->post->ID) ?   $wp_query->post->ID : null;

            $this->_likeOptions = array(
                'send'          =>  array('send'),      // checkbox
                'layout'        =>  array('standard','button_count', 'box_count'), // select
                'action'        =>  array('like', 'recommend'), // select
                'width'         =>  '200px',            //text
                'font'          =>  $this->getFonts(),  // select
                'colorscheme'   =>  $this->getColorScheme() // select
            );

            $this->_commentOptions = array(
                'num_posts'     =>  '10',           //text
                'width'         =>  '500px',        // text
                'colorscheme'   =>  $this->getColorScheme() // select
            );

            $this->_activityOptions = array(
                'site'              =>  '',         // text
                'width'             =>  '250px',    // text
                'height'            =>  '350px',    // text
                'header'            =>  false,      // checkbox|bool
                'border_color'      =>  '#fff',     // text
                'font'              =>  $this->getFonts(),
                'recommendations'   =>  false       //checkbox
            );

            $this->_feedOptions = array();

            parent::init();
        }

        /**
         * Provides the implementations of the inherited abstract
         * methods.  The implementation uses PHP sessions to maintain
         * a store for authorization codes, user ids, CSRF states, and
         * access tokens.
         * @param $key
         * @param $value
         * @return
         */
        protected function setPersistentData($key, $value)
        {
            if (!in_array($key, self::$_SupportedKeys))
            {
                self::errorLog('Unsupported key passed to setPersistentData.');
                return;
            }

            $session_var_name = $this->constructSessionVariableName($key);
            $_SESSION[$session_var_name] = $value;
        }

        protected function getPersistentData($key, $default = false)
        {
            if (!in_array($key, self::$_SupportedKeys))
            {
                self::errorLog('Unsupported key passed to getPersistentData.');
                return $default;
            }

            $session_var_name = $this->constructSessionVariableName($key);
            return isset($_SESSION[$session_var_name]) ?
            $_SESSION[$session_var_name] : $default;
        }

        protected function clearPersistentData($key)
        {
            if (!in_array($key, self::$_SupportedKeys))
            {
                self::errorLog('Unsupported key passed to clearPersistentData.');
                return;
            }

            $session_var_name = $this->constructSessionVariableName($key);
            unset($_SESSION[$session_var_name]);
        }

        protected function clearAllPersistentData()
        {
            foreach (self::$_SupportedKeys as $key)
            {
                $this->clearPersistentData($key);
            }
        }

        protected function constructSessionVariableName($key)
        {
            return implode('_', array('fb', $this->getAppId(), $key));
        }
        /***********************************************************************************************************
         *                                  My Stuff Now
         ***********************************************************************************************************/

        /**
         * Method that loads the default facebook JS, it checks to see if any previous plugin already loaded the JS
         * before it outputs the code
         * @return string
         */
        public function loadJS()
        {
            // Check if the js were loaded before, if so, quietly leaves the method
            if ($this->_loadedJS)
                return;

            $output = "
                <div id='fb-root'></div>
                <script>
                  window.fbAsyncInit = function() {
                    FB.init({
                      appId      : '{$this->appId}', // App ID
                      status     : true, // check login status
                      cookie     : true, // enable cookies to allow the server to access the session
                      oauth      : true, // enable OAuth 2.0
                      xfbml      : true  // parse XFBML
                    });
                  };
                  (function(d){
                     var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
                     js = d.createElement('script'); js.id = id; js.async = true;
                     js.src = '//connect.facebook.net/pt_BR/all.js';
                     d.getElementsByTagName('head')[0].appendChild(js);
                   }(document));
                </script>";

            return $output;

            // After we load the JS lets flag it
            $this->_loadedJS = 1;
        }

        /**
         * Method that includes Open Graph tags on the head of the page
         * The controller / plugin/theme must verify if the open graphs can be added before this method is called
         * normally associated with the wp_head hook
         *
         * @return void
         */
        public function addOpenGraph()
		{
            $output = '';

            if ( isset($this->_postID) )
            {
                $postUrl    =   get_permalink($this->_postID);
                $output     .=  "<meta property='og:url' content='{$postUrl}' />";
            }


            $siteName   =   get_bloginfo('name');

            $appId      =   $this->_options['app'];

            $admin      =   $this->_options['admin'];

			if ( is_home() || is_search() )
            {
				$postTitle = $siteName;
            }
            else if ( is_category() )
			{
				$category = get_the_category();
				$postTitle = $category[0]->cat_name . ' - ' . $siteName;
			}
			else
				$postTitle = single_post_title('', false);

            if ( isset($admin) && !empty($admin) )
                $output .=  "<meta property='fb:admins' content='{$this->_options['admin']}' />";

            $output .=  "<meta property='og:title' content='{$postTitle}' />";
            $output .=  "<meta property='og:site_name' content='{$siteName}' />";
            $output .=  "<meta property='og:type' content='article' />";
            $output .=  "<meta property='fb:app_id' content='{$appId}' />";

            echo $output;

		}

        /**
         * Insert the tags in the <html> on the head, it also helps with IE compatibility
         * associated with the language_attributes function, so must be called as a filter
         * @param string $attributes
         * @return string $attributes
         */
		public function languages( $attributes='' )
		{
            $attributes .= ' xmlns:fb="http://www.facebook.com/2008/fbml"';
            $attributes .= ' xmlns:og="http://opengraphprotocol.org/schema/"';
            return $attributes;
		}

        /**************************************************************************************************************
          *                                 Social Plugins
          **************************************************************************************************************/


        /**
         * Returns the pre-configured like button to be embed on the page
         *
         * @param array|string $newOptions
         * @return string $output
         */
        public function socialLike( $newOptions = '' )
        {
            // Lets get the current page being viewed
            if ( !isset($this->_postID) || empty($this->_postID) )
                $postUrl = get_bloginfo('url');
            else
                $postUrl = get_permalink( $this->_postID );

            $defaults = array(
                'link'          =>  $postUrl,
                'send'          =>  'false',
                'width'         =>  '200',
                'font'          =>  'arial',
                'faces'         =>  'false',
                'action'        =>  'like',
                'layout'        =>  'standard',
                'colorscheme'   =>  'light'
            );
            $options = (array)$this->_options['like'];

            $likeOptions = $this->_replaceDefaults($defaults, $options);

            // If there is some override args lets replace it now
            if ( !empty($newOptions) )
                $likeOptions = $this->_replaceDefaults($likeOptions, $newOptions);

            $likeUrl        =   $likeOptions['link'];
            $send           =   $likeOptions['send'];
            $width          =   $likeOptions['width'];
            $font           =   $likeOptions['font'];
            $faces          =   $likeOptions['faces'];
            $action         =   $likeOptions['action'];
            $layout         =   $likeOptions['layout'];
            $colorscheme    =   $likeOptions['colorscheme'];

            $output  =   $this->loadJS();
            $output .=   "<fb:like
                            href='{$likeUrl}'
                            send='{$send}'
                            width='{$width}'
                            font='{$font}'
                            colorscheme='{$colorscheme}'
                            action='{$action}'
                            layout='{$layout}'
                            show_faces='{$faces}'>
                        </fb:like>";
            
            return $output;
        }

        /**
         * Returns the pre configured social comment box to be embed in the page
         *
         * @param \could|string $newOptions ovewrite the default options defined on the options db
         * @return string $output
         */
        public function socialComments( $newOptions = '')
        {
            // Lets get the current page being viewed
            if ( !isset($this->_postID) || $this->_postID == '' )
                $postUrl = get_bloginfo('url');
            else
                $postUrl = get_permalink($this->_postID);

            $defaults = array(
                'href'          =>  $postUrl,
                'num_posts'     =>  '10',
                'width'         =>  '450',
                'colorscheme'   =>  'light'
            );
            $options = (array)$this->_options['comment'];

            $commentOptions = $this->_replaceDefaults($defaults, $options);

            // If there is some override args lets replace it now
            if ( !empty($newOptions) )
                $commentOptions = $this->_replaceDefaults($commentOptions, $newOptions);

            $href           =   $commentOptions['href'];
            $num_posts      =   $commentOptions['num_posts'];
            $width          =   $commentOptions['width'];
            $colorscheme    =   $commentOptions['colorscheme'];

            $output  =      $this->loadJS();
            $output .=      "<fb:comments
                                href='{$href}'
                                num_posts='{$num_posts}'
                                width='{$width}'
                                colorscheme='{$colorscheme}'>
                            </fb:comments>";

            return $output;
        }

        /**
         *
         * Method that inserts social recommendations into a page
         * @param string $newOptions
         * @internal param string $args
         * @return void
         */
        public function socialRecommendations( $newOptions = '' )
        {
            $domainUrl = get_bloginfo('url');

            $defaults = array(
                'site'          =>  $domainUrl,
                'width'         =>  '300',
                'height'        =>  '300',
                'header'        =>  'true',
                'colorscheme'   =>  'light',
                'linktarget'    =>  '_top',
                'border_color'  =>  '#fff',
                'font'          =>  'arial'
            );
            $options = (array)$this->_options['recommendations'];

            $recommendOptions = $this->_replaceDefaults($defaults, $options);

            // If there is some override args lets replace it now
            if ( !empty($newOptions) )
                $recommendOptions = $this->_replaceDefaults($recommendOptions, $newOptions);

            $site           =   $recommendOptions['site'];
            $width          =   $recommendOptions['width'];
            $height         =   $recommendOptions['height'];
            $header         =   $recommendOptions['header'];
            $colorscheme    =   $recommendOptions['colorscheme'];
            $linktarget     =   $recommendOptions['linktarget'];
            $border_color   =   $recommendOptions['border_color'];
            $font           =   $recommendOptions['font'];


            $output =   $this->loadJS();
            $output .=  "<fb:recommendations
                            site='{$site}'
                            width='{$width}'
                            height='{$height}'
                            header='{$header}'
                            colorscheme='{$colorscheme}'
                            linktarget='{$linktarget}'
                            border_color='{$border_color}'
                            font='{$font}'>
                        </fb:recommendations>";

            return $output;

        }

        /**
         * Method to embed a Activity facebook plugin
         * @param string $newOptions
         * @return void
         */
        public function socialActivity( $newOptions = '' )
        {
            $domainUrl = get_bloginfo('url');

            $defaults = array(
                'site'              =>  $domainUrl,
                'width'             =>  '300',
                'height'            =>  '300',
                'header'            =>  'true',
                'colorscheme'       =>  'light',
                'filter'            =>  '',
                'border_color'      =>  '#fff',
                'linktarget'        =>  '_blank',
                'max_age'           =>  '0',
                'font'              =>  'arial',
                'recommendations'   =>  'false'
            );
            
            $options = (array)$this->_options['recommendations'];

            $activityOptions = $this->_replaceDefaults($defaults, $options);

            // If there is some override args lets replace it now
            if ( !empty($newOptions) )
                $activityOptions = $this->_replaceDefaults($activityOptions, $newOptions);

            $site           =   $activityOptions['site'];
            $width          =   $activityOptions['width'];
            $height         =   $activityOptions['height'];
            $header         =   $activityOptions['header'];
            $colorscheme    =   $activityOptions['colorscheme'];
            $filter         =   $activityOptions['filter'];
            $border_color   =   $activityOptions['border_color'];
            $linktarget     =   $activityOptions['linktarget'];
            $max_age        =   $activityOptions['max_age'];
            $font           =   $activityOptions['font'];
            $recommendations=   $activityOptions['recommendation'];


            $output =   $this->loadJS();
            $output .=  "<fb:fb-activity
                            site='{$site}'
                            width='{$width}'
                            height='{$height}'
                            header='{$header}'
                            colorscheme='{$colorscheme}'
                            linktarget='{$linktarget}'
                            border_color='{$border_color}'
                            font='{$font}'
                            recommendations='{$recommendations}'
                            max_age='{$max_age}'
                            filter='{$filter}'>
                        </fb:fb-activity>";

            return $output;

        }

        /**************************************************************************************************************
         *                                  Return Values
         **************************************************************************************************************/

         /**
         * Returns the available fonts to use in the facebook social plugins
         * @return array
         */
        public function getFonts()
        {
            return $this->_fonts;
        }

        /**
         * Returns the available color schemes to use in the facebook social plugins
         * @return array
         */
        public function getColorScheme()
        {
            return $this->_colorScheme;
        }

        /**
         * Return the available options for the Like widget
         * @return array
         */
        public function getLikeOptions()
        {
            return $this->_likeOptions;
        }

        /**
         * Return the available options for the Comment Box Widget
         * @return array
         */
        public function getCommentOptions()
        {
            return $this->_commentOptions;
        }

        /**
         * Return the available options for the Activity feed box widget
         * @return array
         */
        public function getActivityOptions()
        {
            return $this->_activityOptions;
        }
    }
}