<?php
/*
 * Plugin Name: Plulz Facebook Like
 * Plugin URI: http://www.plulz.com
 * Description: This plugin will allow you to insert a like button anywhere in the theme you want or auto embed in each post
 * Version: 1.0
 * Author: Fabio Zaffani
 * Author URI: http://www.plulz.com
 * License: GPL2
 *
 * Copyright 2011  Fabio Alves Zaffani ( email : fabiozaffani@gmail.com )
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 */


//$plulz_template_directory =   get_template_directory_uri();
//$plulz_server_directory   =   get_template_directory() . '/';
$plulz_server_directory     =   plugin_dir_path(__FILE__);
$plulz_template_directory =   WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__));

include( $plulz_server_directory .'plulz/lib/Model/PlulzImport.php' );

new PlulzImport($plulz_server_directory);

// Make sure there is no bizarre coincidence of someone creating a class with the exactly same name of this plugin
if ( !class_exists("FacebookLikeaLot") )
{
    class FacebookLikeaLot extends PlulzAdminControllerAbstract
    {
        protected $_pluginPage;

        protected $_admin;      // Holds current theme main class

        protected $_facebook;

        public function __construct()
        {

            $this->_facebook    =   new LikeFacebook();

            // actually, its not an "admin" is more like an api, so its a class not a controller
            $this->_admin       =   new PlulzAdmin($this->_facebook->getName());

            $this->_nonce       =   'likealot_nonce';

            $this->_options     =   $this->_facebook->getOption();

            register_activation_hook( __FILE__, array( &$this, 'install' ) );
            register_deactivation_hook( __FILE__, array( &$this, 'remove' ) );
            register_uninstall_hook( __FILE__, array( &$this, 'remove' ) );

            parent::__construct();

        }

        public function init()
        {

            $this->_menuPages        =  array(
                0   =>  array(
                    'page_title'    =>  'Facebook Like a Lot',
                    'menu_title'    =>  'FB Like',
                    'capability'    =>  'administrator',
                    'menu_slug'     =>  $this->_facebook->getName(),
                    'icon_url'      =>  $this->_assets . 'img/tiny-logo-plulz.png',
                    'position'      =>  100,
                    'callback'      =>  array(&$this, 'pageMain'),
                    'submenus'      =>  array()
                )
            );

            // The extra buttons to be added on the tinyMCE edittor
            $this->_mceButtons   =   array(
                0   =>  array(
                    'id'        => 'like',
                    'separator' =>  '|'
                )
            );

            $this->_pluginPage  =   admin_url('admin.php') . '?page=' . $this->_facebook->getName();

            $this->setShortCode('plulz_social_like', array( '_facebook', 'socialLike') );

            // Show like button in the content area
            if ($this->_options['content'])
                $this->setFilter( 'the_content', array( '_facebook', 'contentSocialLike' ) );

            $advanced = $this->_options['advanced'];
            if (isset($advanced) && !empty($advanced))
                $this->setFilter( $advanced, array('_facebook', 'advancedSocialLike') );

            parent::init();
        }

       /**
        *
        * This method insert the default config of the plugin and also creates a database
        * to control what comments were already added and those that were not
        * @return void
        */
        public function install()
        {
            $this->_facebook->install();
        }

        /**
        *
        * Method called when the plugin is uninstalled
        * @return void
        */
        public function remove(){}

        public function preLoad(){}

        public function startFrontEnd()
        {
            parent::startFrontEnd();

            // og tags
            if ($this->_options['openGraphTags'])
                $this->setAction( 'wp_head',	array('_facebook', 'addOpenGraph') ); // og tag

            // fbLanguages
            $this->setAction( 'language_attributes', array('_facebook', 'languages') );

            // share
            if ($this->_facebook->share())
                $this->setAction( 'wp_footer', 'share' );
        }

        public function handleAjax($action) {}

        public function handlePost($data)   {}


        public function pageMain()
        {
            $name = PlulzTools::getValue('page', $this->_name);

            $this->set('data', $this->_options );
            $this->set('group' , $name . $this->groupSuffix);
            $this->set('domain', PlulzAdmin::$DOMAIN['www']);
            $this->set('likeOptions', $this->_facebook->getLikeOptions());
            $this->includeTemplate('Main', 'Admin');
        }

        /**
         * Default welcome message to be show to the user after plugin installation
         * @return bool
         */
        public function adminMessage()
        {
            if( !empty($this->_options['app']) || !empty($this->_options['secret']) || !empty($this->_options['admin']))
                return false;

            // Check if user has the right privileges to see that warning message
            if ( !current_user_can('administrator') )
                return false;

            $message = '<b>Facebook Like a Lot is almost ready</b>, however you MUST insert the <a href="' . $this->_pluginPage . '"><strong>App ID and App Secret</strong></a>';

            $this->PlulzNotices->addUpdate($this->_name, $message);

            parent::adminMessage();
        }

        /**
         * Method that inserts a link in the footer if the user allowed it
         * @return bool|void
         */
        public function share()
        {
            $share = $this->_facebook->getShare();

            $output = '<div id="sharefbseo">';
            $output .= 'Plugin from the creators of <a href="http://www.ilet.com.br" target="_blank" title="' . $share . '" >' . $share . '</a> :: More at Plulz <a href="http://www.plulz.com" title="Wordpress Plugins" target="_blank">Wordpress Plugins</a>';
            $output .= '</div>';

            echo $output;
        }
    }

    new FacebookLikeaLot();

}