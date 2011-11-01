<?php

// Make sure there is no bizarre coincidence of someone creating a class with the exactly same name of this plugin
if ( !class_exists("PlulzFacebook") )
{
    if (!class_exists("BaseFacebook"))
        require_once(  PAZZANI_BRINDES_CLASSES . 'PlulzBaseFacebook.php'  );

    class PlulzFacebook extends BaseFacebook
    {
        /**
         * Store the current post id information, if it exists
         * @var int
         */
        protected $postID;

        /**
         * Default fonts used by facebook
         * @var array
         */
        protected $fonts =
        array( 'arial', 'lucida grande', 'segoe ui', 'tahoma', 'trebuchet ms', 'verdana');

        /**
         * Default facebook color schemes
         * @var array
         */
        protected $colorScheme =
        array( 'light', 'dark');
        
        /**
         * All allowed options of like button
         * @var array
         */
        protected $likeOptions;
        
        /**
         * Verifies if the facebook js is already loaded into the page
         * @var int
         */
        protected $loadedJS = 0;

        /**
         * Dunno
         * @var array
         */
        protected $errors;

        protected static $kSupportedKeys =
        array('state', 'code', 'access_token', 'user_id');

        public function __construct($name = 'plulzfacebook', $nonce = 'plulznonce')
        {
            $this->name     =   $name;
            $this->nonce    =   $nonce;
            $this->action   =   admin_url('options.php');

            $this->likeOptions = array(
                'layout'        =>  array('standard','button_count', 'box_count'),
                'action'        =>  array('like', 'recommend')
            );

            $this->appId    =   isset($this->options['app'])    ?   $this->options['app'] : '';
            $this->secret   =   isset($this->options['secret']) ?   $this->options['secret'] : '';


            // If the page is found, lets grab the current post id
            global $wp_query;
            $this->postID   =   isset( $wp_query->post->ID) ?   $wp_query->post->ID : null;
            
            $fbApiCredentials = array(
                    'appId'	 => $this->appId,
                    'secret' => $this->secret
            );

            parent::__construct( $fbApiCredentials );
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
            if (!in_array($key, self::$kSupportedKeys))
            {
                self::errorLog('Unsupported key passed to setPersistentData.');
                return;
            }

            $session_var_name = $this->constructSessionVariableName($key);
            $_SESSION[$session_var_name] = $value;
        }

        protected function getPersistentData($key, $default = false)
        {
            if (!in_array($key, self::$kSupportedKeys))
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
            if (!in_array($key, self::$kSupportedKeys))
            {
                self::errorLog('Unsupported key passed to clearPersistentData.');
                return;
            }

            $session_var_name = $this->constructSessionVariableName($key);
            unset($_SESSION[$session_var_name]);
        }

        protected function clearAllPersistentData()
        {
            foreach (self::$kSupportedKeys as $key)
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

        public function pageFacebook()
        {
            $this->metaboxFields = array(
                'title'     =>  'Facebook',
                'elements'  =>  array(
                    0   =>  array(
                        'title'     =>  __('Facebook General Config', $this->name),
                        'fields'    =>  array(
                            0   =>  array(
                                'name'      =>  'app',
                                'type'      =>  'text',
                                'label'     =>  __('APP ID', $this->name),
                                'required'  =>  true,
                                'small'     =>  'Need help creating your App ID? <a href="http://www.plulz.com/how-to-create-a-facebook-app" target="_blank">Find How to Create a Facebook APP</a>'
                            ),
                            1   =>  array(
                                'name'      =>  'secret',
                                'type'      =>  'text',
                                'label'     =>  __('APP Secret', $this->name),
                                'required'  =>  true,
                                'small'     =>  ''
                            ),
                            2   =>  array(
                                'name'      =>  'admin',
                                'type'      =>  'text',
                                'label'     =>  __('Admin/User ID', $this->name),
                                'required'  =>  true,
                                'small'     =>  'Need help to find your User ID? <a href="http://www.plulz.com/how-to-get-my-facebook-user-id" target="_blank">Find To Find My User ID</a>'
                            ),
                            3   =>  array(
                                'name'      =>  'fanpage',
                                'type'      =>  'text',
                                'label'     =>  __('Facebook Fan Page Link', $this->name),
                                'required'  =>  true,
                                'small'     =>  __('Could be other links but normally it\'s the fan page', $this->name)
                            ),
                        )
                    ),
                    1   =>  array(
                        'title'     =>  __('Like Button Config', $this->name),
                        'fields'    =>  array(
                            0   =>  array(
                                'name'      =>  array('like', 'send'),
                                'type'      =>  'checkbox',
                                'label'     =>  'Mostrar Send Button?',
                                'required'  =>  false,
                                'small'     =>  __('Choose to show the Send button or not', $this->name)
                            ),
                            1   =>  array(
                                'name'      =>  array('like', 'width'),
                                'type'      =>  'text',
                                'label'     =>  'Width do Like Button',
                                'required'  =>  false,
                                'small'     =>  __('Put "px" at end of the number. Ex: 600px', $this->name)
                            ),
                            2   =>  array(
                                'name'      =>  array('like', 'font'),
                                'type'      =>  'select',
                                'label'     =>  __('Like Button Font Type', $this->name),
                                'required'  =>  false,
                                'options'   =>  $this->getFonts()
                            ),
                            3   =>  array(
                                'name'      =>  array('like', 'action'),
                                'type'      =>  'select',
                                'label'     =>  'Texto do Botão',
                                'small'     =>  __('You can choose beetween Like or Recommend', $this->name),
                                'required'  =>  false,
                                'options'   =>  $this->getLikeOptions('action')
                            ),
                            4   =>  array(
                                'name'      =>  array('like', 'layout'),
                                'type'      =>  'select',
                                'label'     =>  'Texto do Botão',
                                'small'     =>  __('You can choose button_count, box_count or standard button layout', $this->name),
                                'required'  =>  false,
                                'options'   =>  $this->getLikeOptions('layout')
                            ),
                            5   =>  array(
                                'name'      =>  array('like', 'colorscheme'),
                                'type'      =>  'select',
                                'label'     =>  'Cor do Layout',
                                'small'     =>  __('Choose from the default Facebook Light or Dark layout', $this->name),
                                'required'  =>  false,
                                'options'   =>  $this->getColorScheme()
                            )
                        )
                    ),
                    2   =>  array(
                        'title'     =>  __('Comment Config', $this->name),
                        'fields'    =>  array(
                            0   =>  array(
                                'name'      =>  array('comment', 'num_posts'),
                                'type'      =>  'text',
                                'label'     =>  __('Number of Comments', $this->name),
                                'small'     =>  __('The default number of comments to be shown', $this->name),
                                'required'  =>  false,
                            ),
                            1   =>  array(
                                'name'      =>  array('comment', 'width'),
                                'type'      =>  'text',
                                'label'     =>  __('Like Button Width', $this->name),
                                'small'     =>  __('Put "px" at end of the number. Ex: 600px', $this->name),
                                'required'  =>  false,
                            ),
                            2   =>  array(
                                'name'      =>  array('comment', 'colorscheme'),
                                'type'      =>  'select',
                                'label'     =>  __('Layout Color', $this->name),
                                'small'     =>  __('Choose from the default Facebook Light or Dark layout', $this->name),
                                'required'  =>  false,
                                'options'   =>  $this->getColorScheme()
                            )
                        )
                    ),
                    3   =>  array(
                        'title'     =>  __('Activity Feed Config', $this->name),
                        'fields'    =>  array(
                            0   =>  array(
                                'name'      =>  array('activity', 'site'),
                                'type'      =>  'checkbox',
                                'label'     =>  __('Site link', $this->name),
                                'small'     =>  __('Leave blank to use the currently site', $this->name),
                                'required'  =>  false,
                            ),
                            1   =>  array(
                                'name'      =>  array('activity', 'width'),
                                'type'      =>  'text',
                                'label'     =>  __('Width', $this->name),
                                'small'     =>  __('The width of the activity feed. Use only numbers. Ex: 350', $this->name),
                                'required'  =>  false,
                            ),
                            2   =>  array(
                                'name'      =>  array('activity', 'height'),
                                'type'      =>  'text',
                                'label'     =>  __('Height', $this->name),
                                'small'     =>  __('The Height of the activity feed. Use only numbers. Ex: 350', $this->name),
                                'required'  =>  false,
                            ),
                            3   =>  array(
                                'name'      =>  array('activity', 'header'),
                                'type'      =>  'checkbox',
                                'label'     =>  __('Show Header', $this->name),
                                'small'     =>  __('Show the facebook header', $this->name),
                                'required'  =>  false,
                            ),
                            4   =>  array(
                                'name'      =>  array('activity', 'border_color'),
                                'type'      =>  'text',
                                'label'     =>  __('Border Color', $this->name),
                                'small'     =>  __('Must be in the format #ffffff', $this->name),
                                'required'  =>  false
                            ),
                            5   =>  array(
                                'name'      =>  array('activity', 'font'),
                                'type'      =>  'select',
                                'label'     =>  __('Font Type', $this->name),
                                'small'     =>  __('The allowed default fonts from facebook', $this->name),
                                'required'  =>  false,
                                'options'   =>  $this->getFonts()
                            ),
                            6   =>  array(
                                'name'      =>  array('activity', 'recommendations'),
                                'type'      =>  'checkbox',
                                'label'     =>  __('Recommendations', $this->name),
                                'small'     =>  __('Show recommendations to the current user', $this->name),
                                'required'  =>  false,
                            )
                        )
                    )
                )
            );

            $this->createAdminPage();
        }

        /**
         * Method that loads the default facebook JS, it checks to see if any previous plugin already loaded the JS
         * before it outputs the code
         * @return string
         */
        public function loadJS()
        {
            // Check if the js were loaded before, if so, quietly leaves the method
            if ($this->loadedJS)
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
            $this->loadedJS = 1;
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

            if ( isset($this->postID) )
            {
                $postUrl    =   get_permalink($this->postID);
                $output     .=  "<meta property='og:url' content='{$postUrl}' />";
            }


            $siteName   =   get_bloginfo('name');

            $appId      =   $this->options['app'];

            $admin      =   $this->options['admin'];

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
                $output .=  "<meta property='fb:admins' content='{$this->options['admin']}' />";

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
            if ( !isset($this->postID) || empty($this->postID) )
                $postUrl = get_bloginfo('url');
            else
                $postUrl = get_permalink( $this->postID );

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
            $options = (array)$this->options['like'];

            $likeOptions = $this->replaceDefaults($defaults, $options);

            // If there is some override args lets replace it now
            if ( !empty($newOptions) )
                $likeOptions = $this->replaceDefaults($likeOptions, $newOptions);

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
                            show_faces='{$faces}'>
                        </fb:like>";
            
            return $output;
        }

        public function getLikeOptions( $args )
        {
            return $this->likeOptions[$args];
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
            if ( !isset($this->postID) || $this->postID == '' )
                $postUrl = get_bloginfo('url');
            else
                $postUrl = get_permalink($this->postID);

            $defaults = array(
                'href'          =>  $postUrl,
                'num_posts'     =>  '10',
                'width'         =>  '450',
                'colorscheme'   =>  'light'
            );
            $options = (array)$this->options['comment'];

            $commentOptions = $this->replaceDefaults($defaults, $options);

            // If there is some override args lets replace it now
            if ( !empty($newOptions) )
                $commentOptions = $this->replaceDefaults($commentOptions, $newOptions);

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
            $options = (array)$this->options['recommendations'];

            $recommendOptions = $this->replaceDefaults($defaults, $options);

            // If there is some override args lets replace it now
            if ( !empty($newOptions) )
                $recommendOptions = $this->replaceDefaults($recommendOptions, $newOptions);

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
            
            $options = (array)$this->options['recommendations'];

            $activityOptions = $this->replaceDefaults($defaults, $options);

            // If there is some override args lets replace it now
            if ( !empty($newOptions) )
                $activityOptions = $this->replaceDefaults($activityOptions, $newOptions);

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
            return $this->fonts;
        }

        /**
         * Returns the available color schemes to use in the facebook social plugins
         * @return array
         */
        public function getColorScheme()
        {
            return $this->colorScheme;
        }

        /**************************************************************************************************************
         *                                   Private Methods
         **************************************************************************************************************/

        /**
         * Method that replace the default social configurations
         * 
         * @param array $defaultOptions
         * @param array $newOptions
         * @return array $output
         */
        private function replaceDefaults($defaultOptions, $newOptions)
        {
            $output = array();

            foreach($defaultOptions as $name => $value)
            {
                if ( array_key_exists($name, $newOptions) )
                    $output[$name] = $newOptions[$name];
                else
                    $output[$name] = $value;
	        }

            return $output;
        }
    }
}