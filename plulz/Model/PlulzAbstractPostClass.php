<?php
/**
 *
 * Class responsible for returning the values do orçamento atual
 *
 */

if (!class_exists('PlulzAbstractPost'))
{
    abstract class PlulzAbstractPost extends PlulzAbstractObject
    {
        // @TODO criar um Helper Method para auxiliar na criação de box extras de filtros na listagem de produtos
        
        /**
         * Holds the informationa about wordpress default post data
         * @var array
         */
        protected $defaultPostTypes = array(
            'post', 'page', 'attachment', 'revisions', 'nav_menu_item'
        );
        
        /**
         * Custom metaboxes that we want to add, could be in any kinda of post
         * @var array
         */
        protected $postMetaboxes;

        /**
         * Configurations of the new post to be added
         * @var array
         */
        protected $postData;

        /**
         * CSS files to be applied on the metaboxes
         * @var
         */
        protected $metaboxCSS;

        /**
         * Metabox fields to be shown in the above added metaboxes, its the output of the above
         * @var array
         */
        protected $metaboxFields;               // Array containg the elemtents to be show on a metabox

        /**
         * Holds the information about the custom taxonomys to be created and used with the current post type
         * @var array
         */
        protected $customTaxonomies;

        /**
         * Everything that should happen before the post is presented to the user
         * any appending or logic can be embeded here. Normally used to append Javascript codes
         * @abstract
         * @return void
         */
        abstract public function preLoad();

        /**
         * The columns that should be show on the post, leave blank to use wordpress defaults
         * @abstract
         * @param $cols
         * @return void
         */
        abstract public function customPostTypeColumns( $cols );

        /**
         * Which of the columns are sortable
         * @abstract
         * @return void
         */
        abstract public function customPostTypeColumnsSortable();

        /**
         * The content of each column of the custom post
         * @abstract
         * @param $column
         * @param $post_id
         * @return void
         */
        abstract public function customPostTypeColumnsContent( $column, $post_id );

        public function init()
        {
            parent::init();

            // Multiple postMetaboxes?
            if ( !isset($this->postMetaboxes[0]) )
                $this->postMetaboxes = array( 0   =>  $this->postMetaboxes);

            // Multiple customTaxonomies?
            if ( !isset($this->customTaxonomies[0]) )
                $this->customTaxonomies = array( 0  =>  $this->customTaxonomies);

            if ( empty($this->postData) )
                $this->errors->add($this->name, __('Empty var postData') );
        }

        /**
         * Creates the custom taxonomys defined in the custom taxonomy var
         * @return void
         */
        public function createCustomTaxonomies()
        {
            foreach( $this->customTaxonomies as $taxonomy )
            {
                register_taxonomy(  $taxonomy['name'],
                                    $this->postData['type'],
                                    $taxonomy['args']
                );
            }
        }

        /**
         * Creates extra fields for any taxonomy
         * @param $tag
         * @return void
         */
        public function createTaxonomyCustomField( $tag )
        {
           // Check for existing taxonomy meta for the term you're editing
            $termID = $tag->term_id; // Get the ID of the term you're editing

            $option = $this->name . $termID;

            $metaValues = get_option( $option );

            $output = '';

            foreach ( $this->customTaxonomies as $customTaxonomy )
            {
                if ( isset($customTaxonomy['extraFields']) && !empty( $customTaxonomy['extraFields']) )
                {
                    foreach ( $customTaxonomy['extraFields'] as $field )
                    {
                        $label          =   $field['label'];
                        $name           =   $field['name'];
                        $description    =   $field['description'];
                        $value          =   $metaValues[$name];

                        $output .= "
                            <tr class='form-field'>
                                <th scope='row' valign='top'>
                                    <label for='{$name}'>{$label}</label>
                                </th>
                                <td>
                                    <input type='text' name='{$name}' id='{$name}' value='{$value}'><br />
                                    <span class='description'>{$description}</span>
                                </td>
                            </tr>";

                    }
                }
            }
            echo $output;
        }

        /**
         * Method that saves the custom fields on the custom taxonomys type
         * @param $termID
         * @return void
         */
        public function saveTaxonomyCustomField( $termID )
        {
            if ( isset( $_POST ) )
            {
                $option = $this->name . $termID;

                $term_meta = get_option( $option );
                foreach ( $this->customTaxonomies as $customTaxonomy )
                {
                    if ( isset($customTaxonomy['extraFields']) && !empty( $customTaxonomy['extraFields']) )
                    {
                        foreach ( $customTaxonomy['extraFields'] as $field )
                        {
                            $name = $field['name'];
                            
                            if ( isset($_POST[$name]) )
                                $term_meta[$name]   =   $_POST[$name];
                        }
                    }
                }

                //save the option array
                update_option( $option, $term_meta );
            }
        }

        /**
         * This method is to circumvent the need to know the post type with wordpress that does not make
         * life easier in this matter ( you never know what info you will have to find the post type)
         * This method is suposed to be used only on admin area
         * @static
         * @return bool|string
         */
        public static function getPostType()
        {
            if ( !is_admin() )
                return false;
            
            global $post;

            if ( isset($_REQUEST['post_type']) )
                return $_REQUEST['post_type'];
            else if ( isset($_REQUEST['post']) )
                return get_post_type( $_REQUEST['post'] );
            else if ( isset($post) )
                return $post->post_type;
            else
                return false;
        }

        /**
         * Method that register the new post types in wordpress if the post is not from the default ones
         * Default post list: 'post', 'page', 'attachment', 'revisions', 'nav_menu_item'
         * @return void
         */
        public function registerCustomPostType()
        {
            // Return if its a default post type that we are extending
            if ( in_array($this->postData['type'], $this->defaultPostTypes) )
                return;

            register_post_type( $this->postData['type'], $this->postData['config'] );

            // Custom post types overview page modification
            add_filter( "manage_orcamento_posts_columns", array( &$this, "customPostTypeColumns") );
            add_filter( "manage_edit-orcamento_sortable_columns", array( &$this, "customPostTypeColumnsSortable") );

            // The contents for the custom columns of the posts types
            // @TODO check if the 10, 2 ali no final é realmente needed
            add_action( "manage_posts_custom_column", array( &$this, "customPostTypeColumnsContent"), 10, 2 );
        }

         /**
         * Method that creates custom metaboxes in any type of post in wordpress
          * (posts, pages, custom posts type, etc)
          *
         * @return void
         */
        public function createCustomMetaboxes()
        {
            // No metaboxes values were defined
            if ( empty($this->postMetaboxes) )
                return;

            wp_register_style( 'PlulzMetaboxStylesheet', $this->metaboxCSS );
            add_action( 'admin_print_styles-post.php', array( &$this, 'outputMetaboxCSS') );
            add_action( 'admin_print_styles-post-new.php', array( &$this, 'outputMetaboxCSS') );

            foreach($this->postMetaboxes as $metabox)
            {
                $priority       = isset($metabox['priority'])  ? $metabox['priority'] : 'high';
                $context        = isset($metabox['context'])   ? $metabox['context'] : 'normal';
                $post_type      = isset($metabox['post_type']) ? $metabox['post_type'] : 'post';  // default
                $callbackArgs   = isset($metabox['callbackArgs']) ? $metabox['callbackArgs'] : null;

                // ref http://codex.wordpress.org/Function_Reference/add_meta_box
                add_meta_box(
                    $metabox['id'],         // (required) HTML 'id' attribute of the edit screen section
                    $metabox['title'],      // (required) Title of the edit screen section, visible to user
                    $metabox['callback'],   // (required) Function that prints out the HTML for the edit screen section.
                    $post_type,             // (required) The type of Write screen on which to show the edit screen section ('post', 'page', 'link', or 'custom_post_type' where custom_post_type is the custom post type slug)
                    $context,               // (optional) The part of the page where the edit screen section should be shown ('normal', 'advanced', or 'side'). (Note that 'side' doesn't exist before 2.7)
                    $priority,              // (optional) The priority within the context where the boxes should show ('high', 'core', 'default' or 'low')
                    $callbackArgs           // (optional) Arguments to pass into your callback function. The callback will receive the $post object and whatever parameters are passed through this variable.
                );
            }

        }

        /**
         * Outputs the CSS registered in the createCustomMetaboxes method
         * @return void
         */
        public function outputMetaboxCSS()
        {
            wp_enqueue_style( 'PlulzMetaboxStylesheet' );
        }

        /**
         * Method that outputs the HTML on the customMetaboxes
         * 
         * @param $post
         * @return void
         * @todo este método precisa ser flexivel , nem sempre vamos querer apenas adicionar input fields
         */
        public function outputCustomMetabox( $post )
        {
            wp_nonce_field( -1, $this->nonce );

            foreach ( $this->metaboxFields as $inputs)
               echo $this->_addRow( $inputs, $post);
        }

        /**
         * Default method responsible for saving our custom metaboxes data in wordpress custom post meta.
         * Must be overriden in children class if want to validate the fields before saving on the db
         * or any other thing
         *
         * @param $post_id
         * @return bool
         */
        public function saveCustomMetaboxContent( $post_id )
        {
             // verify if this is an auto save routine.
            // If it is our form has not been submitted, so we dont want to do anything
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
              return;

            // verify this came from the our screen and with proper authorization,
            // because save_post can be triggered at other times
            if ( !isset($_POST[$this->nonce]) )
                return;

            if ( !wp_verify_nonce( $_POST[$this->nonce] ) )
                return;

            // Check permissions
            if ( 'page' == $_POST['post_type'] )
            {
                if ( !current_user_can( 'edit_page', $post_id ) )
                    return;
            }
            else
            {
                if ( !current_user_can( 'edit_post', $post_id ) )
                    return;
            }

            // OK, we're authenticated: we need to find and save the data
            $customData = $_POST[$this->name];

            return update_post_meta($post_id, $this->name, $customData);
        }

    }
}
