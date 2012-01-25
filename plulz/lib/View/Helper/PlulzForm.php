<?php

if (!class_exists('PlulzForm'))
{
    class PlulzForm
    {
        protected $_name;

        public function __construct($name)
        {
            $this->_name = $name;
        }

        public function create($action, $type = 'post')
        {
            echo "<form action='$action' method='$type'>";
        }

        public function close()
        {
           echo "</form>";
        }

       /**
        * Method to make life easier when outputing <input> of any type
        * @param $type
        * @param $name
        * @param null $id
        * @param string $defaultValue
        * @param array $settings
        * @param null $data
        * @return string
        */
        public function addInput( $type, $name, $id = null, $defaultValue = '', $settings = array(), $data = null )
        {
            // create $maxlength, $readonly, $options, $class variables
            if (!empty($settings))
                extract($settings);

            if ( isset( $data ) )
               $valuesArrayToParse = $data;
            else
               $valuesArrayToParse = array();

            $arrName    =   $this->_createName( $name );
            $arrValue   =   $this->_getInputValue( $name, $valuesArrayToParse);

            if (empty($arrValue))
            {
                $arrValue = $defaultValue;

                $checked = 0;
            }
            else
                $checked = 1;

            $maxlength  =   isset($maxlength)   ?   " maxlength='{$maxlength}'" : '';
            $readonly   =   isset($readonly)    ?   " readonly='readonly'"  :   '';
            $required   =   isset($required)    ?   " aria-required='true'" :   '';
            $options    =   isset($options)     ?   $options                :   '';

           // Check Class
           if (isset($class))
               $class      =   is_array($class)    ?   $type . ' ' . implode(" ", $class)    :   $type . ' ' . $class;
           else
               $class      =   $type;

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

                   if ( $checked )
                       $checkedValue = ' checked="checked" ';
                   else
                       $checkedValue = '';

                   $input = "<input name='{$arrName}' class='{$class}' type='checkbox' value='1'{$id}{$checkedValue}/>";

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

        /*
        *
        * Protected method to help output Rows in configuration metaboxes
        *
        * @param array(required) $input
        * @param array(optional) $post array with the current post data
        * @return void
        */
       public function addRow( $input, $data = null, $table = false)
       {
           $name       =   isset($input['name'])      ?            $input['name']      :   'noname';
           $type       =   isset($input['type'])      ?    (string)$input['type']      :   'text';
           $value      =   isset($input['value'])     ?    (string)$input['value']     :   '';
           $label      =   isset($input['label'])     ?    (string)$input['label']     :   '';
           $settings   =   array(
               'class'     =>  isset($input['class'])     ?    (string)$input['class']     :   '',
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

               $row .= (isset($required) && $required ) ? ' <span class="description">required</span>' : '';

               $row .= "</label>";
           }
           else
           {
               $row = '';

               if (isset($label) && !empty($label))
                   $row .= "<th><label for='{$idLabel}'>{$label}</label></th>";

               $row .= "<td class='{$class}'>";
           }

           $row .= $this->addInput( $type, $name, $idLabel, $value, $settings, $data);

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
    }
}