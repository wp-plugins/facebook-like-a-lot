<?php

if (!class_exists('LikeaLotFacebook'))
{
    class LikeaLotFacebook extends PlulzFacebookAbstract
    {

        public function __construct()
        {

            $this->_name     =   'likealot_facebook';

            $this->_nonce    =   'likealot_facebook_nonce';

            parent::__construct();

        }

        /**
         * This method will be executed if App ID or Secret can't be found in the options table or if there's any problem with they
         * @internal param void $
         */
		public function fbHandleNoAppId()
		{
			get_currentuserinfo(); // Get user info to see if the currently logged in user (if any) has the 'manage_options' capability

			if ( !current_user_can('manage_options') )
				echo 'You are not authorized to see this part';

			$url = 'options-general.php?page='.$this->name;
			$fb_optionsPage = admin_url($url);
			echo "<div id=\"fbSEOComments\" style=\"width:{$this->options['width']}\"> Please, insert a valid <a href='$fb_optionsPage' style='color: #c00;'>App ID</a>, otherwise your plugin won't work correctly.</div>";
		}

    }
}

?>