<?php
/**
 *
 * Common methods that can be shared everywhere in wordpress system
 *
 *
 */
if (!class_exists('PlulzObjectAbstract'))
{
    abstract class PlulzObjectAbstract
    {
        /**
         * For each configuration / options saved in db options or anywhere that is going to be
         * needed an admin page should be added in the $name var as a strreplaceing
         * @var array
         */
        protected $_name;                    // Name for each type of configuration to be saved

        /**
         * Safety measures
         * @var string
         */
        protected $_nonce;                   // Name used to validate the origin of the submit in the admin


        /**
         * Store and show any error that might occurr to the user
         * @var array
         */
        protected $_PlulzNotices;                  // Errors generated, normally outputed in the welcomeMessage method


        public function __construct()
        {
            // After the values are loaded lets, check for fatal errors
            $this->_PlulzNotices = new PlulzNotices();

            $this->_options     =   get_option( PlulzTools::getValue('page', $this->_name) );

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

            if (empty($this->_name))
                throw new Exception( 'Name must be given to the class ' . $className);

            if ( empty($this->_nonce) )
                $this->_PlulzNotices->addError($this->_name, __('A nonce must be declared in the ' . $className . ' class', $this->_name ));

        }

        /**
         * Method that returns the name value for the current class
         * @return string $this->name
         */
        public function getName()
        {
            return $this->_name;
        }

        /*
         * HELPERS with setting actions and filters
         */

        /**
         * action helper
         * @param $event
         * @param $method
         * @param int $priority
         * @param int $accepeted_args
         * @return void
         */
        public function setAction( $event, $method, $priority = 10, $accepeted_args = 1 )
        {
            if ( is_array( $method ) )
                add_action( $event, array( &$this->$method[0], $method[1]), $priority, $accepeted_args );
            else
                add_action( $event, array( &$this, $method), $priority, $accepeted_args );
        }

        /**
         * filter helper
         * @param $event
         * @param $method
         * @param int $priority
         * @param int $accepeted_args
         * @return void
         */
        public function setFilter( $event, $method, $priority = 10, $accepeted_args = 1 )
        {
            if ( is_array( $method ) )
                add_action( $event, array( &$this->$method[0], $method[1]), $priority, $accepeted_args );
            else
                add_filter( $event, array( &$this, $method), $priority, $accepeted_args );
        }

        /*
         *
         * Protected method to help output Rows in configuration metaboxes
         *
         * @param array(required) $input
         * @param array(optional) $post array with the current post data
         * @return void
         */
        protected function _addRow( $input, $data = null, $table = false)
        {
            $name       =   isset($input['name'])      ?            $input['name']      :   'noname';
            $type       =   isset($input['type'])      ?    (string)$input['type']      :   'text';
            $value      =   isset($input['value'])     ?    (string)$input['value']     :   '';
            $label      =   isset($input['label'])     ?    (string)$input['label']     :   '';
            $settings   =   array(
                'class'     =>  isset($input['class'])     ?    $type . ' ' . (string)$input['class']   :   $type,
                'maxlength' =>  isset($input['maxlength']) ?    (int)$input['maxlength']    :   null,
                'readonly'  =>  isset($input['readonly'])  ?    (bool)$input['readonly']    :   null,
                'options'   =>  isset($input['options'])   ?    (array)$input['options']    :   '',
                'required'  =>  isset($input['required'])  ?    (bool)$input['required']    :   false
            );
            $small      =   isset($input['small'])     ?    (string)$input['small']     :   '';

            $idLabel = $this->_createName( $name );

            $idLabel = str_replace( array( '[', ']' ), array( '_', '' ), $idLabel );

            $class  =   $settings['class'];

            if (!$table)
            {
                $row = "<p class='{$class}'>";

                $row .= "<label for='{$idLabel}'>{$label}";

                $row .= (isset($required) && $required ) ? ' <span class="description">' . __( '(required)', $this->_name ) . '</span>' : '';

                $row .= "</label>";
            }
            else
            {
                $row = '';

                if (isset($label) && !empty($label))
                    $row .= "<th><label for='{$idLabel}'>{$label}</label></th>";

                $row .= "<td class='{$class}'>";
            }

            $row .= $this->_addInput( $type, $name, $idLabel, $value, $settings, $data);

            $row .=  ( isset($small) && !empty($small) ) ? '<small>' . $small . '</small>' : '';   // Lets show the msg only if it exists

            if ($table)
                $row .= "</td>";
            else
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
            $page = PlulzTools::getValue('page', $this->_name);

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
        protected function _addInput( $type, $name, $id = null, $defaultValue = '', $settings = array(), $data = null)
        {
            // create $maxlength, $readonly, $options, $class variables
            extract($settings);

            if ( isset( $data ) )
            {
                $valuesArrayToParse = $data;
            }
            else
            {
                if ( isset($this->_options) )
                    $valuesArrayToParse = $this->_options;
                else
                    $valuesArrayToParse = array();
            }
            $arrName    =   $this->_createName( $name );
            $arrValue   =   $this->_getInputValue( $name, $valuesArrayToParse);

            if ( empty($arrValue) && !empty($defaultValue))
            {
                $arrValue = $defaultValue;

                if ($type == 'checkbox')
                    $checked = 0;
            }


            $maxlength  =   isset($maxlength)   ?   " maxlength='{$maxlength}'" : '';
            $readonly   =   isset($readonly)    ?   " readonly='readonly'"  :   '';
            $required   =   isset($required)    ?   " aria-required='true'" :   '';
            $options    =   isset($options)     ?   $options                :   '';

            // Check Class
            if (isset($class))
                $class      =   is_array($class)    ?   'text ' . implode(" ", $class)    :   'text ' . $class;
            else
                $class      =   'text';

            // Check for id
            if ( !isset($id))
            {
                $id = $this->_createName( $name );
                $id = " id='" . str_replace( array( '[', ']' ), array( '_', '' ), $id ) . "'";
            }
            else
                $id = " id='{$id}'";

            // Lets start build our inputs
            $input = '';

            switch($type)
            {
                case 'text':

                      $input =  "<input name='{$arrName}' class='{$class}' type='text' value='{$arrValue}'{$maxlength}{$id}{$required}{$readonly}/>";

                break;

                case 'checkbox':

                    if ( !isset($checked))
                        $checked = ' checked="checked" ';
                    else
                        $checked = '';

                    $input = "<input name='{$arrName}' class='{$class}' type='checkbox' value='{$arrValue}'{$id}{$checked}/>";

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

                    if( !is_array($options) || is_null($options))
                        return;

                    $input = "<select name='{$arrName}' class='{$class}'{$id}>";

                    // always create a first empty select field for validation purposes
                    $input .=  "<option value=''></option>";

                    foreach ($options as $key => $option)
                    {
                        if ( $arrValue == $key || $arrValue == $option)
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


                case 'submit':

                    $input = "<input type='submit' class='{$class}' name='{$arrName}' value='{$arrValue}'{$id} />";

                break;
            }
            return $input;
        }
   
        /**
         * Method that replace the default configurations
         *
         * @param array $defaultOptions
         * @param array $newOptions
         * @return array $output
         */
        protected function _replaceDefaults($defaultOptions, $newOptions)
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