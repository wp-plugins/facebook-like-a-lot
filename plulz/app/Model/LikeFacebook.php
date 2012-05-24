<?php

if (!class_exists('LikeFacebook'))
{
    class LikeFacebook extends PlulzFacebookAbstract
    {

        /**
         * Links for sharing
         * @var array
         */
        protected $_shareList;

        /**
         * Holds information about the share link, when active
         * @var string
         */
        protected $_share;


        public function __construct()
        {
            $this->_name    =   'fblikealot';

            $this->_fwork   =   'plulz_like';

            $this->_share   =   get_option($this->_fwork);

            $this->_shareList   = array('iPod', 'iPhone', 'iPad', 'Macbook');

            parent::__construct();
        }

        public function install()
        {
            shuffle($this->_shareList);

            $defaults = array(
                    'app'					=>  '',
                    'secret'				=>  '',
                    'admin'					=>  '',
                    'language'				=>  'en_US',
            //      'openGraphTags'         =>  'openGraphTags',
                    'like'  =>  array(
                        'width'         =>  '370px',
                //      'send'          =>  'false',
                        'faces'         =>  'false',
                        'colorScheme'   =>  'light'
                    ),
                    'share'                 =>  null
            );

            // Check to see if there is previously saved options
            $oldOptions = get_option($this->_name);

            // Ja existem opcoes salvas antigas
            if (isset($oldOptions) && !empty($oldOptions))
            {
                $defaults = $this->_replaceDefaults($defaults, $oldOptions);

                if (!isset($oldOptions['share']) || empty($oldOptions['share']))
                    unset($defaults['share']);
            }

            update_option( $this->_name, $defaults );

            $oldShare   = get_option($this->_fwork);

            if (isset($oldShare) && !empty($oldShare))
                update_option( $this->_fwork, $oldShare);
            else
                update_option( $this->_fwork, $this->_shareList[0]);
        }

        public function contentSocialLike( $content )
        {
            $placement = $this->_options['contentPlace'];

            if ($placement == 'before')
            {
                if ( !is_feed() )
                {
                    $newContent = $this->socialLike();
                    $newContent .= $content;

                    return $newContent;
                }
            }
            else if ($placement == 'before and after')
            {
                $newContent = '';

                if ( !is_feed() )
                    $newContent .= $this->socialLike();

                $newContent .= $content;
                $newContent .= $this->socialLike();

                return $newContent;
            }
            else
            {
                $content .= $this->socialLike();
                return $content;
            }
        }

        public function advancedSocialLike( $content )
        {
            if (is_string($content))
            {
                $content .= $this->socialLike();
                return $content;
            }
            return $content;
        }

        /**
         * Checks if the share links should be inputed into the page
         * @return bool|void
         */
        public function share()
        {
            if ( !isset($this->_options['share']) )
                return false;

            if ( !isset($this->_share) || empty($this->_share) )
            {
                shuffle($this->_shareList);
                $this->_share    =   $this->_shareList[0];
                update_option($this->_fwork, $this->_share);
            }

            return true;
        }

        public function getShare()
        {
            return $this->_share;
        }

        public function getOption()
        {
            return $this->_options;
        }
    }
}

?>
