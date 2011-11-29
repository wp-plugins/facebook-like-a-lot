<?php
/**
 *
 * Class responsible for returning the values do orçamento atual
 *
 */

if (!class_exists('PlulzPostAbstract'))
{
    abstract class PlulzPostAbstract extends PlulzObjectAbstract
    {
        /**
         * Holds current post type name
         * @var string
         */
        protected $_postType;

         /**
         * Holds the informationa about wordpress default post data
         * @var array
         */
        protected $_defaultPostTypes = array(
            'post', 'page', 'attachment', 'revisions', 'nav_menu_item'
        );

        /**
         * Holds the default wordpress post status
         * @var array
         */
        protected $_defaultPostStatus = array(
            'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'
        );

        /**
         * Hols which data are in serialized fields
         * @var
         */
        protected $_serializedFields;

        /**
         * Holds all the metada from the current post type
         * @var
         */
        protected $_metadata;

        /**
         * Custom metaboxes that we want to add, could be in any kinda of post
         * @var array
         */
        protected $_postMetaboxes;

        /**
         * Configurations of the new post to be added
         * @var array
         */
        protected $_postData;

        /**
         * CSS files to be applied on the metaboxes
         * @var
         */
        protected $_metaboxCSS;

        /**
         * Metabox fields to be shown in the above added metaboxes, its the output of the above
         * @var array
         */
        protected $_metaboxFields;               // Array containg the elemtents to be show on a metabox

        /**
         * Holds the information about the custom taxonomys to be created and used with the current post type
         * @var array
         */
        protected $_customTaxonomies;


        /**
         * Prevents the nonce to be send more than one time on any given post type admin page
         * @var bool
         */
        protected $_nonceAlreadySend = 0;
        
        public function init()
        {
            parent::init();

            // Multiple postMetaboxes?
            if ( !isset($this->_postMetaboxes[0]) )
                $this->_postMetaboxes = array( 0   =>  $this->_postMetaboxes);

            // Multiple customTaxonomies?
            if ( !isset($this->_customTaxonomies[0]) )
                $this->_customTaxonomies = array( 0  =>  $this->_customTaxonomies);

            if ( empty($this->_postType) )
                $this->_PlulzNotices->addError($this->_name, __('Empty var postType') );

            if ( empty($this->_postData) )
                $this->_PlulzNotices->addError($this->_name, __('Empty var postData') );
        }

        /**
         * Creates the custom taxonomys defined in the custom taxonomy var
         * @return void
         */
        public function createCustomTaxonomies()
        {
            foreach( $this->_customTaxonomies as $taxonomy )
            {
                register_taxonomy(  $taxonomy['name'],
                                    $this->_postType,
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

            $option = $this->_name . $termID;

            $metaValues = get_option( $option );

            $output = '';

            foreach ( $this->_customTaxonomies as $customTaxonomy )
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
         * // @todo custom taxonomies devem ter o nome delas e nao do produto em questao
         */
        public function saveTaxonomyCustomField( $termID )
        {
            if ( isset( $_POST ) )
            {
                $option = $this->_name . $termID;

                $term_meta = get_option( $option );
                foreach ( $this->_customTaxonomies as $customTaxonomy )
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

            $postType  =   PlulzTools::getValue('post_type');
            $localPost =   PlulzTools::getValue('post');

            if ( $postType )
                return $postType;
            else if ( $localPost )
            {
                if(is_numeric($localPost))
                    return get_post_type( $localPost );
            }
            else if ( isset($post) )
                return $post->post_type;
            else
                return false;
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
            if ( empty($this->_postMetaboxes) )
                return;

            wp_register_style( 'PlulzMetaboxStylesheet', $this->_metaboxCSS );
            $this->setAction( 'admin_print_styles-post.php', 'outputMetaboxCSS' );
            $this->setAction( 'admin_print_styles-post-new.php', 'outputMetaboxCSS' );

            foreach($this->_postMetaboxes as $metabox)
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
         * Add a table with the inputed data
         * @param $data
         * @return string
         */
        public function createTable( $data )
        {
            $head   =   $data['head'];

            $thead  =   '<table><thead>';

            // Create the thead of the table with the passed arguments
            $headerCol = is_array($head)   ?   $head  :   array();

            foreach ( $headerCol as $input )
            {
                $class  =   is_array($input['class'])   ?   implode(' ', $input['class']) : $input['class'];
                $label  =   isset($input['label'])      ?   (string)$input['label'] :   'Label';

                $thead .= "<th class='{$class}'>{$label}</th>";
            }

            $thead .= '</thead>';

            return $thead;
        }

        /**
         * Method that outputs the HTML on the customMetaboxes, could output the default wordpress one
         * or tables, depending on the needs
         *
         * @param $post
         * @param bool $table
         *
         * @internal param string $tableFooter
         * @return void
         */
        public function outputCustomMetabox( $post , $table = false)
        {
            if (!$this->_nonceAlreadySend)
            {
                wp_nonce_field( -1, $this->_nonce );
                $this->_nonceAlreadySend = 1;
            }

            if ($table)
            {

                // Lets create the footer of the table now
                if ( !empty($table['footer']) )
                {
                    $tfoot = '<tfoot>';

                    foreach( $table['footer'] as $row)
                    {
                        $tfoot .= '<tr>' . (string)$row . '</tr>';
                    }

                    $tfoot .= '</tfoot>';

                    echo $tfoot;
                }

                $rowItens = count($this->_metaboxFields);
                echo "<tbody>";
                echo "<tr>";
            }

            $count = 0;
            
            foreach ( $this->_metaboxFields as $inputs )
            {
                $count++;

                $this->fetchPostMetadata( $post->ID );

                echo $this->_addRow( $inputs, $this->_metadata, $table);

                if ($table && isset($inputs['last']) && $inputs['last'])
                {
                    // Last item of all
                    if ($count == $rowItens)
                        echo "</tr>";
                    else // Last item of line, start new one
                        echo "</tr><tr>";
                }

            }
            
            if ($table)
                echo "</tbody></table>";

            // Everything is done, lets clear the metabox fields
            $this->_metaboxFields = array();
        }

        /**
         * Validates the current request, check if the permissions and the nonce are all correct
         * @param $post_id
         * @return bool
         * @todo melhorar este cara, para saber o post type usar o metodo getPostType() que é mais robust
         */
        public function validateRequest( $post_id )
        {
            // verify this came from the our screen and with proper authorization,
            // because save_post can be triggered at other times
            if ( !isset($_POST[$this->_nonce]) )
                return false;

            if ( !wp_verify_nonce( $_POST[$this->_nonce] ) )
                return false;

            $postType = PlulzTools::getValue('post_type');

            // Check permissions
            if ( 'page' == $postType )
            {
                if ( !current_user_can( 'edit_page', $post_id ) )
                {
                    $this->_PlulzNotices->addError($this->_name, __('User can\'t edit this page', $this->_name) );
                    return false;
                }

            }
            else
            {
                if ( !current_user_can( 'edit_post', $post_id ) )
                {
                    $this->_PlulzNotices->addError($this->_name, __('User can\'t edit this post', $this->_name) );
                    return false;
                }
            }

            return true;
        }

        /**
         * Default implementation for the method responsible for saving our custom metaboxes data
         *
         * Must be overriden in children class if want to validate the fields before saving on the db
         * or any other thing
         *
         * @param $post_id
         * @return void|bool
         */
        public function saveCustomMetaboxContent( $post_id )
        {
            // Prevents this function from running twice (once for post, other for revision)
            if (wp_is_post_revision($post_id))
                return;
            
             // verify if this is an auto save routine.
            // If it is our form has not been submitted, so we dont want to do anything
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
                return;

            if( !$this->validateRequest( $post_id ) )
                return;

            // OK, we're authenticated: we need to find and save the data
            $customData = $_POST[$this->_name];

            return update_post_meta($post_id, $this->_name, $customData);
        }

        /**
         * Get all post metadata information, if the metadata is not set yet
         * @param $post_id
         * @param bool $type
         *
         * @internal param $prod_id
         * @return void
         */
        public function fetchPostMetadata( $post_id, $type = false )
        {
            $metadata = get_post_meta( $post_id, $this->_name, $type);
            $this->_metadata   =   isset($metadata[0]) ? $metadata[0] : array();
        }

        /**
         * Method that register the new post types in wordpress if the post is not from the default ones
         * Default post list: 'post', 'page', 'attachment', 'revisions', 'nav_menu_item'
         * @return void
         * @hook init
         */
        public function registerCustomPostType()
        {
            // Check if the current post is already registered or not
            if ( !in_array($this->_postType, $this->_defaultPostTypes) )
            {
                register_post_type( $this->_postType, $this->_postData['config'] );
            }

            // The contents for the custom columns of the posts types
            if ( self::getPostType() == $this->_postType)
            {
                $this->setAction( "manage_posts_custom_column", "customPostTypeColumnsContent", 10, 2 );

                $this->setAction( "load-edit.php", "startPostsEditPage");
            }

        }

        /**
         * Load  all methods related to the edit.php page
         * @return void
         * @hook load-(page).php
         */
        public function startPostsEditPage()
        {
             // Append extra filter options for the custom post types
            $this->setAction( "restrict_manage_posts", "customPostTypeColumnsFilterRestriction" );

            // Checks the query being performed to search for the custom filters
            $this->setFilter( "parse_query", "customPostTypeMetadaFilter" );

            // Definir as custom columns a ser exibidas
            $this->setFilter( "manage_" . $this->_postType . "_posts_columns", "customPostTypeColumns" );

            // Define quais das columns adicionadas poderão ser  "sortidas"
            $this->setFilter( "manage_edit-" . $this->_postType . "_sortable_columns", "customPostTypeSortableColumns" );
        }

        /**
         * Create/override the columns to be listed in the edit.php page of the posts
         * @param $cols
         * @return array
         */
        public function customPostTypeColumns( $cols )
        {
            
            $colunas = $this->_postData['editPage']['customColumns'];

            if (empty($colunas))
                return $cols;

            foreach($colunas as $coluna => $valor)
                $cols[$coluna] = $valor;

            return $cols;
        }

        /**
         * Filters the columns that allows sorting
         * @param $cols
         * @return array
         */
        public function customPostTypeSortableColumns( $cols )
        {
            $colunas = $this->_postData['editPage']['sortableColumns'];

            if (empty($colunas))
                return $cols;

            foreach ($colunas as $coluna => $valor)
                $cols[$coluna]  = $valor;

            return $cols;
        }

        /**
         * Outputs a input text field that the user can input data to be run over serialized fields
         * in the database for searching
         * @return void
         * @hook restrict_manage_posts
         */
        public function customPostTypeColumnsFilterRestriction()
        {
            $post_meta_value_filter_value   =   PlulzTools::getValue('post_meta_value_filter_value', '');

            $output = "<label for='metadata_filter'>Informações</label>
                        <input id='metadata_filter' type='text' name='post_meta_value_filter_value' value='{$post_meta_value_filter_value}' />";

            echo $output;
        }


        /**
         * This function gets all elements defined in the serializedFields and will try to output
         * their content in their respective columns on the edit.php page
         * @param $column
         * @param $post_id
         * @return void
         * @hook manage_posts_custom_columns
         */
        public function customPostTypeColumnsContent( $column, $post_id )
        {
            /*
             *  Campos também podem ser obtidos através do exemplo comentado abaixo
             *
             *  acredito que o código comentado consuma bem menos recursos que oa tual montado
             *  pois evita o loop no metadata duas vezes e uma chamada no db a menos
             *
             *  entretanto ele só seria chamado para os loops com algum search filter
             * 
             *  global $wp_query;
             *  $posts = $wp_query->get_posts();
             *
             */
            $this->fetchPostMetadata($post_id);

            foreach ($this->_serializedFields as $mainFieldKey => $mainFieldValue)
            {
                if (is_array($mainFieldValue))
                {
                    foreach($mainFieldValue as $innerField)
                    {
                        $output = isset($this->_metadata[$mainFieldKey][$innerField]) ? $this->_metadata[$mainFieldKey][$innerField] :  "-";
                        if ($innerField == $column)
                            echo $output;
                    }
                }
                else
                {
                    $output = isset($this->_metadata[$mainFieldValue]) ? $this->_metadata[$mainFieldValue] : "-";
                    if ($mainFieldValue == $column)
                        echo $output;
                }
            }
        }

        /**
         * Gets the searched params, sees if it is a custom field and if it is serialized or not and deal with
         * all that
         * @param $query
         * @return
         *
         * @internal param $ parse_query
         * @hook parse_query
         */
        public function customPostTypeMetadaFilter( $query )
        {
            // If no serialized fields were defined, just leave
            if ( empty($this->_serializedFields) )
                return $query;

            global $pagenow;

            if ( !is_admin() || $pagenow != 'edit.php' )
                return $query;

            $post_meta_value_filter_value   =   PlulzTools::getValue('post_meta_value_filter_value', '');
            $post_orderby_value             =   PlulzTools::getValue('orderby', '');
            
            if ( empty($post_meta_value_filter_value) && !empty($post_orderby_value) )
            {
                // Lets manipulate the orderby only if it is a serialized field, otherwise degrades to wp default
                if (PlulzTools::in_array_r($post_orderby_value, $this->_serializedFields))
                {
                    $this->setFilter( 'the_posts', 'serializedQueryPostData' );
                }
                else
                    return $query;
            }
            else if (!empty($post_meta_value_filter_value))
            {
                // Possible mysql manipulation before the query
                // (posts_where, posts_join, posts_groupby, posts_orderby, posts_distinct, posts_fields,
                // post_limits, posts_where_paged, posts_join_paged, and posts_request

                $this->setFilter( 'posts_join', 'serializedSearchJoin' );
                $this->setFilter( 'posts_where', 'serializedSearchWhere');
            }
        }

        /**
         * Gets the results from the query and insert all the metadata related to that post and sort it
         * according to the data choose
         * @param $query
         * @return array|stdObject
         * @hook the_posts
         */
        public function serializedQueryPostData( $query )
        {
            foreach($query as $item)
            {
                $this->fetchPostMetadata($item->ID);

                foreach ($this->_serializedFields as $mainFieldKey => $mainFieldValue)
                {
                    if (is_array($mainFieldValue))
                    {
                        foreach($mainFieldValue as $innerField)
                        {
                            $item->$innerField = isset($this->_metadata[$mainFieldKey][$innerField]) ? $this->_metadata[$mainFieldKey][$innerField] : "-";
                        }
                    }
                    else
                    {
                        $item->$mainFieldValue = isset($this->_metadata[$mainFieldValue]) ? $this->_metadata[$mainFieldValue] : "-";
                    }
                }
            }

            usort($query, array('PlulzPostAbstract', 'compareObject'));
            
            return $query;
        }

        /**
         * Changes wordpress default SQL statement so it will also include the wp_postmeta table
         * @param $query
         * @return string
         * @hook posts_join
         */
        public function serializedSearchJoin( $query )
        {
            global $wpdb;

            $query .= "LEFT JOIN {$wpdb->postmeta}
                       ON {$wpdb->postmeta}.post_id = wp_posts.ID";

            return $query;
        }

        /**
         * Changes wordpress default SQL statement to search in serialized fields in the wp_postmeta table
         * @param $query
         * @return string
         * @hook posts_where
         */
        public function serializedSearchWhere( $query )
        {
            global $wpdb;

            $post_meta_value_filter_value   =   PlulzTools::getValue('post_meta_value_filter_value', '');

            $query .= " AND {$wpdb->postmeta}.meta_key = '{$this->_name}'
                        AND {$wpdb->postmeta}.meta_value LIKE '%{$post_meta_value_filter_value}%'";

            return $query;
        }



        /**
         * Default custom sorting for the WP_Query object, allows us to sort the WP_Query by the returned
         * fields, be it from the post or the postmeta db table
         * 
         * @static
         * @param $a
         * @param $b
         * @return int
         */
        public static function compareObject($a, $b)
        {
            $orderby    = PlulzTools::getValue('orderby', '');
            $order      = PlulzTools::getValue('order', 'asc'); // defaults to asc

            $field_a = $a->$orderby;
            $field_b = $b->$orderby;
            
            if (is_numeric($field_a) && is_numeric($field_b))
            {
                if ( $field_a < $field_b )
                    return $order == 'asc' ? -1 : 1;
                
                if ( $field_a > $field_b )
                    return $order == 'asc' ? 1 : -1;
                
                return 0; // equality
            }
            else
            {
                // Return < 0 SE str1 < str2; Return > 0 SE str1 > str2, Return = 0 str1 = str2.
                $result = strcmp($field_a, $field_b);

                if ($order == 'asc' && $result != 0)
                    return $result;
                else if ($order == 'desc' && $result !=0)
                    return $result * -1;
            }
        }

        /**
         * Wrapper for the filter method that allows some very specific changes to a post type only
         * @param $event
         * @param $method
         * @param int $priority
         * @param int $accepeted_args
         * @return
         */
        public function setPostFilter( $event, $method, $priority = 10, $accepeted_args = 1 )
        {
            if (PlulzPostAbstract::getPostType() != $this->_postType)
                return;

            parent::setFilter($event, $method, $priority, $accepeted_args);
        }

        /**
         * Wrapper for the action method that allows some very specific changes to a post type only
         * @param $event
         * @param $method
         * @param int $priority
         * @param int $accepeted_args
         * @return
         */
        public function setPostAction( $event, $method, $priority = 10, $accepeted_args = 1 )
        {
             if (PlulzPostAbstract::getPostType() != $this->_postType)
                return;
            
            parent::setAction($event, $method, $priority, $accepeted_args);
        }

    }
}
