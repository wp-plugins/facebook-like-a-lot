<?php
/*
 * The class with the application logic of this Theme (Model)
 * It is responsible for calling the other Models and is always needed as the brain center of the application and
 * for talking with the controller
 *
 *
 * CLASS OVERVIEW
 *
 * This class must always be named in reference to the theme or plugin that it is implementing and extend the
 * PlulzAdminClass that contains many usefull functions to help build any kind of theme or plugin
 *
 * It also is responsible for managing the default implementations that comes with wordpressl ike the posts and pages
 * the excerpts, admin configuration and custom page creation.
 *
 */
if (!class_exists('PlulzPazzaniBrindes'))
{
    class PlulzFacebookLikeaLot extends PlulzAbstractAdmin
    {
        public $PlulzFacebook;              // associated class


        public function __construct()
        {
            $this->name             =   'facebooklikealot';

            $this->nonce            =   'facebooklikealot_nonce';

            $this->PlulzFacebook    =   new PlulzFacebook($this->name, $this->nonce);

            $this->stylesheetName   =   $this->name . 'Stylesheet';

            $this->action           =   admin_url('options.php');

            // Set the admin assets
            $this->adminAssets      =   array(
                'css'   =>  LIKE_A_LOT_ASSETS .'css/',
                'js'    =>  LIKE_A_LOT_ASSETS .'js/',
                'img'   =>  LIKE_A_LOT_ASSETS .'img/'
            );

            $this->menuPages        =  array(
                'page_title'    =>  'Facebook Like a Lot',
                'menu_title'    =>  'Facebook Like a Lot',
                'capability'    =>  'administrator',
                'menu_slug'     =>  $this->name,
                'icon_url'      =>  LIKE_A_LOT_ASSETS . 'img/tiny-logo-plulz.png',
                'position'      =>  '',
                'callback'      =>  array(&$this, 'pageMain')
            );

            // The extra buttons to be added on the tinyMCE edittor
            $this->mceButtons   =   array(
                0   =>  array(
                    'id'        => 'like',
                    'separator' =>  '|'
                )
            );

            parent::__construct();

        }

        /**
         * Method to check if everything is ok with all the object instances
         * @return void
         */
        public function init()
        {
            parent::init();

            $errors['facebook']     =   $this->PlulzFacebook->getErrors();

            foreach ($errors as $key=>$errosArr)
            {
                foreach($errosArr as $erro)
                    $this->errors->add($key , $erro);
            }

        }

        public function pageMain()
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
                            )
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
                                'options'   =>  $this->PlulzFacebook->getFonts()
                            ),
                            3   =>  array(
                                'name'      =>  array('like', 'action'),
                                'type'      =>  'select',
                                'label'     =>  'Texto do Botão',
                                'small'     =>  __('You can choose beetween Like or Recommend', $this->name),
                                'required'  =>  false,
                                'options'   =>  $this->PlulzFacebook->getLikeOptions('action')
                            ),
                            4   =>  array(
                                'name'      =>  array('like', 'layout'),
                                'type'      =>  'select',
                                'label'     =>  'Texto do Botão',
                                'small'     =>  __('You can choose button_count, box_count or standard button layout', $this->name),
                                'required'  =>  false,
                                'options'   =>  $this->PlulzFacebook->getLikeOptions('layout')
                            ),
                            5   =>  array(
                                'name'      =>  array('like', 'colorscheme'),
                                'type'      =>  'select',
                                'label'     =>  'Cor do Layout',
                                'small'     =>  __('Choose from the default Facebook Light or Dark layout', $this->name),
                                'required'  =>  false,
                                'options'   =>  $this->PlulzFacebook->getColorScheme()
                            )
                        )
                    )
                )
            );

            $this->createAdminPage();
        }


        /**
        * Method that load extra css into the theme
        * @return void
        */
        public function loadCSS()
        {
        }

        public function install()
        {}

        public function remove()
        {
            delete_option($this->name);
        }
        
        /************************************************************************************************
         *                                  RETURN VALUES
         ************************************************************************************************/

        /**
         * Method to retrive the options values
         * @param $option
         * @return string
         */
        public function getOptionInfo( $option )
        {
            return isset($this->options[$option]) ? $this->options[$option] : '';
        }
    }

}