<?php
/**
 *
 * Common methods that can be shared everywhere in wordpress system
 *
 *
 */
if (!class_exists('PlulzAbstractObject'))
{
    abstract class PlulzAbstractObject
    {
        /**
         * For each configuration / options saved in db options or anywhere that is going to be
         * needed an admin page should be added in the $name var as a string
         * @var array
         */
        protected $name;                    // Name for each type of configuration to be saved

        /**
         * Safety measures
         * @var string
         */
        protected $nonce;                   // Name used to validate the origin of the submit in the admin

        /**
         * Store and show any error that might occurr to the user
         * @var array
         */
        protected $errors;                  // Errors generated, normally outputed in the welcomeMessage method


        public function __construct()
        {
            // After the values are loaded lets, check for fatal errors
            $this->errors = new WP_Error();

            try{
                $this->init();
            } catch (Exception $e) {
                echo 'Problems: ' . $e->getMessage() . "<br/>";
            }
        }

        /**
         * Method that normalizes and checks everything before we can use the class
         * @throws Exception
         * @return void
         */
        public function init()
        {
            $className = get_class($this);

            if (empty($this->name))
                throw new Exception( 'Name must be given to the class ' . $className);

            if ( empty($this->nonce) )
                $this->errors->add($this->name, __('A nonce must be declared in the ' . $className . ' class', $this->name ));
        
        }

        /**
         * Method that returns the name value for the current class
         * @return string $this->name
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * Method that returns all the errors ocurred when trying to create the class
         * @return array|WP_Error
         */
        public function getErrors()
        {
            return $this->errors->get_error_messages();
        }

        /*
         *
         * Protected method to help output Rows in configuration metaboxes
         *
         * @param array(required) $input
         * @param array(optional) $post array with the current post data
         * @return void
         */
        protected function _addRow( $input, $post = null)
        {
            $name       =   isset($input['name'])      ?            $input['name']      :   'noname';
            $type       =   isset($input['type'])      ?    (string)$input['type']      :   'text';
            $label      =   isset($input['label'])     ?    (string)$input['label']     :   'Label';
            $class      =   isset($input['class'])     ?    (string)$input['class']     :   $type;
            $maxlength  =   isset($input['maxlength']) ?    (int)$input['maxlength']    :   null;
            $readonly   =   isset($input['readonly'])  ?    (bool)$input['readonly']    :   false;
            $required   =   isset($input['required'])  ?    (bool)$input['required']    :   false;
            $options    =   isset($input['options'])   ?    (array)$input['options']    :   '';
            $small      =   isset($input['small'])     ?    (string)$input['small']     :   '';

            $idLabel = $this->_createName( $name );

            $idLabel = str_replace( array( '[', ']' ), array( '_', '' ), $idLabel );
            
            $row =  (isset($required) && $required ) ? "<tr valign='top' class='form-field'>" : "<tr valign='top' class='form-field form-required'>";

            $row .= "<p>" .
                    "<label for='{$idLabel}'>{$label}";

            $row .= (isset($required) && $required ) ? ' <span class="description">' . __( '(required)', $this->name ) . '</span>' : '';

            $row .= "</label>";

            $row .= $this->_addInput( $name, $type, $required, $readonly, $class, $idLabel, $maxlength, $options, $post);

            $row .=  ( isset($small) && !empty($small) ) ? '<small>' . $small . '</small>' : '';   // Lets show the msg only if it exists

            $row .= "</p>";

            return $row;
        }

        /**
         * Method that returns the current value for the input field, accept any kinda of array
         *
         * @param $name
         * @param array|string $arrayToParse
         *
         * @internal param $string /array $name
         * @return string
         */
        protected function _getInputValue( $name, $arrayToParse = '')
        {
            if ( is_array($name) && is_numeric(implode("", array_keys($name)))) // NOT ASSOCIATIVE ARRAY
            {
                $value = isset($arrayToParse[$name[0]]) ? $arrayToParse[$name[0]] : '';

                if(count($name) > 1)
                    $valueArr = $this->_getInputValue(array_slice($name, 1), $value);
                else
                    $valueArr = $value;
            }
            else if (is_array($name))   // ASSOCIATIVE ARRAYS
            {
                $key = key($name);
                $valueArr = isset($arrayToParse["{$key}"]["{$name[$key]}"]) ? $arrayToParse["{$key}"]["{$name[$key]}"] : '';
            }
            else
            {
                $valueArr = isset($arrayToParse["{$name}"]) ? $arrayToParse["{$name}"] : '';
            }

            return $valueArr;
        }

        /**
         *
         * This method handle any nested level possible from the input name/label for attribute of
         * any kinda of input fields
         * @param string|array (required) $name
         * @internal param object $post
         * @return string
         */
        protected function _createName( $name )
        {
            $page = $this->name;

            $nameArr = '';
            if ( is_array($name) && is_numeric(implode("", array_keys($name)))) // NOT ASSOCIATIVE ARRAY
            {
                foreach($name as $current)
                    $nameArr .= '[' . $current . ']';

                $nameArr = $page . $nameArr;
            }
            else if (is_array($name))   // ASSOCIATIVE ARRAYS
            {
                $key = key($name);
                $nameArr = $page . '[' . $key . ']' . '[' . $name[$key] . ']';
            }
            else
            {
                $nameArr = $page . '[' . $name . ']';
            }

            return $nameArr;
        }

        /*
         * Method to make life easier when outputing <input> of any type
         *
         * @param string(required) $type (text, hidden, textarea, etc)
         * @param string(required) $name
         * @param bool(optional) $required
         * @param bool(optional) $readonly
         * @param string(optional) $class
         * @param string(optional) $id
         * @param int(optional) $maxlength
         * @param array(optional) $options a array for checkbox and select
         * @param array(optional) $post (the wordpress post object data, if exists)
         * @return void
         */
        protected function _addInput( $name, $type, $required = false, $readonly = false, $class = 'text', $id = null, $maxlength = null, $options = null, $post = null)
        {
            if ( isset($post) )
            {
                $metaInfo =  get_post_meta($post->ID, $this->name ,false);
                $valuesArrayToParse = isset($metaInfo[0]) ? $metaInfo[0] : array();   // wordpress output the initial [0] or empty if there is no saved data yet
            }
            else
            {
                $valuesArrayToParse = $this->options;
            }

            $arrName    =   $this->_createName( $name );
            $arrValue   =   $this->_getInputValue( $name, $valuesArrayToParse);

            $maxlength  =   isset($maxlength)   ?   " maxlength='{$maxlength}'" : '';
            $id         =   isset($id)          ?   " id='{$id}'" : '';
            $required   =   $required           ?   " aria-required='true'" :   '';
            $readonly   =   $readonly           ?   " readonly='readonly'"  :   '';

            $input = '';

            switch($type)
            {
                case 'text':

                      $input =  "<input name='{$arrName}' class='{$class}' type='text' value='{$arrValue}'{$maxlength}{$id}{$required}{$readonly}/>";

                break;

                case 'checkbox':

                    if ( isset($arrValue) && $arrValue == '1')
                        $checked = ' checked="checked" ';
                    else
                        $checked = '';

                    $input = "<input name='{$arrName}' class='{$class}' type='checkbox' value='1'{$id}{$checked}/>";

                break;

                case 'textarea':

                    $input = "<textarea name='{$arrName}' class='{$class}'{$id}{$required}>{$arrValue}</textarea>";

                break;

                case 'radio':

                     if( !is_array($options) || empty($options) || is_null($options))
                        return;

                     foreach( $options as $key => $option)
                     {
                         if ( $arrValue == $option)
                            $checked = ' checked="checked" ';
                        else
                            $checked = '';

                         $input .= "<input name='{$arrName}' class='{$class}' value='{$key}'{$id}{$required}{$checked}>{$option}";
                     }

                break;

                case 'select':

                    if( !is_array($options) || empty($options) || is_null($options))
                        return;

                    $input = "<select name='{$arrName}' class='{$class}'>";

                    foreach ($options as $key => $option)
                    {
                        if ( $arrValue == $option)
                            $selected = ' selected="selected" ';
                        else
                            $selected = '';

                        $input .= "<option value='{$key}'{$selected}>{$option}</option>";
                    }

                    $input .= '</select>';

                break;

                case 'hidden':

                    $input .= "<input name='{$arrName}' class='{$class}' type='hidden' value='{$arrValue}'{$id}{$readonly}/>";

                break;

                case 'upload':

                    if ($class != 'upload')
                        $class .= ' upload';
                    elseif (empty($class))
                        $class = 'upload';

                    $input  = "<input class='{$class}' type='text' name='{$arrName}' value='{$arrValue}'{$id}{$required}{$readonly}/>";
                    $input .= "<input class='upload-button' type='button' value='Upload'{$id}/>";

                break;
            }
            return $input;
        }

    }
}