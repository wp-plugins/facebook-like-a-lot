<?php
/*
 * The class with the admin implementation for configurations and stuff when needed
 *
 *
 * CLASS OVERVIEW
 *
 *
 * It also is responsible for managing the default implementations that comes with wordpressl ike the posts and pages
 * the excerpts, admin configuration and custom page creation.
 *
 */
if (!class_exists('PazzaniBrindesAdmin'))
{
    class PazzaniBrindesAdmin extends PlulzAdminAbstract
    {
        public $PazzaniBrindesFacebook;              // associated class

        public $PazzaniBrindesCarrinho;

        public function __construct()
        {
            $this->_name        =   'pazzanibrindes_admin';

            $this->_nonce       =   'pazzanibrindes_admin_nonce';

            parent::__construct();

        }

        public function init()
        {
            $this->PazzaniBrindesFacebook  =   new PazzaniBrindesFacebook();

            $this->PazzaniBrindesCarrinho  =   new PazzaniBrindesCarrinho();

            $this->_stylesheetName   =   $this->_name . 'Stylesheet';

            // Set the admin assets
            $this->_adminAssets      =   array(
                'css'   =>  PAZZANI_BRINDES_ASSETS .'css/',
                'js'    =>  PAZZANI_BRINDES_ASSETS .'js/',
                'img'   =>  PAZZANI_BRINDES_ASSETS .'img/'
            );

            $this->_menuPages        =  array(
                    'page_title'    =>  'Pazzani Brindes',
                    'menu_title'    =>  'Pazzani Brindes',
                    'capability'    =>  'administrator',
                    'menu_slug'     =>  $this->_name,
                    'icon_url'      =>  PAZZANI_BRINDES_ASSETS . 'img/tiny-logo-plulz.png',
                    'position'      =>  '',
                    'callback'      =>  array(&$this, 'pageMain'),
                    'submenus'      =>  array(
                        0               =>  array(
                            'page_title'    =>  'Facebook',
                            'menu_title'    =>  'Facebook',
                            'capability'    =>  'administrator',
                            'menu_slug'     =>  $this->PazzaniBrindesFacebook->getName(),
                            'callback'      =>  array( &$this, 'pageFacebook' )
                        ),
                        1               =>  array(
                            'page_title'    =>  'Orçamento',
                            'menu_title'    =>  'Orçamento',
                            'capability'    =>  'administrator',
                            'menu_slug'     =>  $this->PazzaniBrindesCarrinho->getName(),
                            'callback'      =>  array( &$this, 'pageCarrinho')
                        )
                    )
            );

            $this->_adminPageSidebarMetaboxes   =   array(
                $this->_name    =>   array(
                    'loved', 'donate', 'help'
                )
            ); 

            // The extra buttons to be added on the tinyMCE edittor
            $this->_mceButtons   =   array(
                0   =>  array(
                    'id'        => 'like',
                    'separator' =>  '|'
                )
            );

            parent::init();

        }

        public function pageMain()
        {
             $this->_metaboxFields = array(
                'title'     =>  'Configurações Gerais',
                'elements'  =>  array(
                    0   =>  array(
                        'title'     =>  'General Config',
                        'fields'    =>  array(
                            0           =>  array(
                                'name'      =>  'twitter',
                                'type'      =>  'text',
                                'label'     =>  'Link Twitter',
                                'required'  =>  true,
                                'small'     =>  'Perfil do Twitter. Ex.: iletstore para http://www.twitter.com/iletstore'
                            ),
                            1           =>  array(
                                'name'      =>  'atendimento',
                                'type'      =>  'text',
                                'label'     =>  'Telefone de Atendimento do Site',
                                'required'  =>  true,
                                'small'     =>  'Pode conter texto'
                            ),
                            2           =>  array(
                                'name'      =>  'footerText',
                                'type'      =>  'text',
                                'label'     =>  'Texto do Footer',
                                'required'  =>  true,
                                'small'     =>  'Texto de informações que aparecerá na parte inferior do rodapé da página'
                            )
                        )
                    )
                )
             );

            $this->createAdminPage();
        }

        public function pageCarrinho()
        {
            $this->_metaboxFields = array(
                'title'     =>  'Configurações Carrinho',
                'elements'  =>  array(
                    0   =>  array(
                        'title'     =>  'Páginas',
                        'fields'    =>  array(
                            0           =>  array(
                                'name'      =>   'fechamento',
                                'type'      =>  'text',
                                'label'     =>  __('Página de Fechamento', $this->_name),
                                'required'  =>  true,
                                'small'     =>  'Link página de fechamento. Ex: /fechamento'
                            ),
                            1           =>  array(
                                'name'      =>  'sucesso',
                                'type'      =>  'text',
                                'label'     =>  __('Página de Sucesso', $this->_name),
                                'required'  =>  true,
                                'small'     =>  'Link página de sucesso. Ex: /sucesso'
                            )
                        )
                    )
                )
             );

            $this->createAdminPage();
        }

         public function pageFacebook()
        {

            $this->_metaboxFields = array(
                'title'     =>  'Facebook',
                'elements'  =>  array(
                    0   =>  array(
                        'title'     =>  __('Facebook General Config', $this->_name),
                        'fields'    =>  array(
                            0   =>  array(
                                'name'      =>  'app',
                                'type'      =>  'text',
                                'label'     =>  __('APP ID', $this->_name),
                                'required'  =>  true,
                                'small'     =>  'Need help creating your App ID? <a href="http://www.plulz.com/how-to-create-a-facebook-app" target="_blank">Find How to Create a Facebook APP</a>'
                            ),
                            1   =>  array(
                                'name'      =>  'secret',
                                'type'      =>  'text',
                                'label'     =>  __('APP Secret', $this->_name),
                                'required'  =>  true,
                                'small'     =>  ''
                            ),
                            2   =>  array(
                                'name'      =>  'admin',
                                'type'      =>  'text',
                                'label'     =>  __('Admin/User ID', $this->_name),
                                'required'  =>  true,
                                'small'     =>  'Need help to find your User ID? <a href="http://www.plulz.com/how-to-get-my-facebook-user-id" target="_blank">Find To Find My User ID</a>'
                            ),
                            3   =>  array(
                                'name'      =>  'fanpage',
                                'type'      =>  'text',
                                'label'     =>  __('Facebook Fan Page Link', $this->_name),
                                'required'  =>  true,
                                'small'     =>  __('Could be other links but normally it\'s the fan page', $this->_name)
                            ),
                        )
                    ),
                )
            );

            $this->createAdminPage();
        }

        /**
         * Function that loads extra widgets in the Template
         * @return void
         */
        public function widgets()
        {
            register_sidebar( array(
                'name' => __( 'Sidebar Area One', $this->_name ),
                'id' => 'sidebar-1',
                'description' => __( 'An optional widget area for your sidebar', $this->_name ),
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => "</aside>",
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );

            register_sidebar( array(
                'name' => __( 'Sidebar Area Two', $this->_name ),
                'id' => 'sidebar-2',
                'description' => __( 'An optional widget area for your sidebar', $this->_name ),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => "</div>",
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );

            register_sidebar( array(
                'name' => __( 'Footer Area One', $this->_name ),
                'id' => 'sidebar-3',
                'description' => __( 'An optional widget area for your site footer', $this->_name ),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => "</div>",
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );

            register_sidebar( array(
                'name' => __( 'Footer Area Two', $this->_name ),
                'id' => 'sidebar-4',
                'description' => __( 'An optional widget area for your site footer', $this->_name ),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => "</asidivde>",
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );

            register_sidebar( array(
                'name' => __( 'Footer Area Three', $this->_name ),
                'id' => 'sidebar-5',
                'description' => __( 'An optional widget area for your site footer',  $this->_name ),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => "</div>",
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );
        }

        /************************************************************************************************
         *                                  RETURN VALUES
         ************************************************************************************************/


        public function getFooterText()
        {
            return isset($this->_options['footerText']) ? $this->_options['footerText'] : '';
        }

        public function getAtendimento()
        {
            return isset($this->_options['atendimento']) ? $this->_options['atendimento'] : '';
        }

        /*
         * This method show options in the admin page about the creator, donations and helpfull links
         *
         * @param array $helpLinks
         * @return void
         */
        protected function _help( $helpLinks = null )
        {
            $content = "<p>Problems? The links bellow can be very helpful to you</p>";
            $content .= "<ul>";

            if (!$helpLinks)    // Api is unreachable or slow
            {
               $content .= "<li><a href='http://www.plulz.com' target='_blank'>Plulz</a></li>";
            }
            else
            {
                foreach( $helpLinks->node as $element )
                {
                    $url = (string)$element->url;
                    $title = (string)$element->title;

                    if( !empty($url) && !empty($title) )
                        $content .= "<li><a href='{$url}' target='_blank' > {$title} </a></li>";
                }
            }

            $this->createMetabox('Need Assistance?', $content);
        }

        /*
         * This method show options in the admin page about the creator, donations and helpfull links
         *
         * @param array $links
         * @return void
         */
        protected function _loved( $links )
        {
            $content = "<p>Below are some links to help spread this plugin to other users</p>";
            $content .= "<ul>";

            if (!isset($links))    // Api is unreachable or slow
            {
               $content .= "<li><a href='http://wordpress.org/extend/plugins' target='_blank'>Give it a 5 star on Wordpress.org</a></li>";
               $content .= "<li><a href='http://wordpress.org/extend/plugins' target='_blank'>Link to it so others can easily find it</a></li>";
            }
            else
            {
                foreach( $links->node as $element )
                {
                    $url = (string)$element->url;
                    $title = (string)$element->title;

                    if( !empty($url) && !empty($title) )
                        $content .= "<li><a href='{$url}' target='_blank' > {$title} </a></li>";
                }
            }

            $this->createMetabox('Loved this Plugin?', $content);
        }

        /*
         * This method show options in the admin page about the creator, donations and helpfull links
         *
         * @param array @donateLinks
         * @return void
         */
        protected function _donate( $donateLinks = null )
        {
            $content = '';

            if ( $donateLinks && is_string($donateLinks->description) )
                $content .= "<p>{$donateLinks->description}</p>";
            else
                $content .= "<p>I spend a lot of time making and improving this plugin, any donation would be very helpful for me, thank you very much :)</p>";

            if ( $donateLinks && is_string($donateLinks->form) )
                $content .= "{$donateLinks->form}";
            else
                $content .= '<form id="paypalform" action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="NMR62HAEAHCRL"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1"></form>';

            $this->createMetabox('Donate via PayPal', $content);
        }
    }

}