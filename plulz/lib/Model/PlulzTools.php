<?php
/**
 * This class have severall helpfull functions to help in the most diverses situations
 * (creating filename, sending email, sanitize everything, creating numbers and rounding, etc)
 *
 */
if (!class_exists('PlulzTools'))
{
	class PlulzTools
	{
		public function __construct()	{	}

        /**
         * Convert any type of string in a valid money number format
         * @static
         * @param $value
         * @return mixed
         */
		public static function ConvertMoneyToNumber($value)
		{
			$findArray = array('/[^0-9,.]/', '/[,]/');
			$changeArray = array('','.');   
			$value = preg_replace($findArray, $changeArray, $value);
			return $value;
		}

        /**
         * Round to the nearest valid number based on the decimal places
         * @static
         * @param $value
         * @param int $places
         * @return float
         */
		public static function RoundUp($value, $places=2)
		{
			if ($places < 0) { $places = 0; }
			$mult = pow(10, $places);
			return ceil($value * $mult) / $mult;
		}

        /**
         * Dictionary of chars and their respective valid substitutes
         * @static
         * @param $str
         * @param array $replace
         * @param string $delimiter
         * @internal param $string
         * @return string
         */
        public static function NormalizeString($str, $replace=array(), $delimiter='-')
        {
            setlocale(LC_ALL, 'en_US.UTF8');

            if( !empty($replace) ) {
                $str = str_replace((array)$replace, ' ', $str);
            }

            $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
            $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
            $clean = strtolower(trim($clean, '-'));
            $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

            return $clean;
        }

        /**
         * Forgot
         * @static
         * @param $character
         * @param $string
         * @param $side
         * @param bool $keep_character
         * @return bool|string
         */
		public static function CutStringUsintLast($character, $string, $side, $keep_character=true)
		{
			$offset = ($keep_character ? 1 : 0);
			$whole_length = strlen($string);
			$right_length = (strlen(strrchr($string, $character)) - 1);
			$left_length = ($whole_length - $right_length - 1);
			switch($side) {
				case 'left':
					$piece = substr($string, 0, ($left_length + $offset));
					break;
				case 'right':
					$start = (0 - ($right_length + $offset));
					$piece = substr($string, $start);
					break;
				default:
					$piece = false;
					break;
			}
			return($piece);
		}

       /**
        * Get a value from $_POST / $_GET
        * if unavailable, take a default value
        *
        * @param string $key Value key
        * @param mixed $defaultValue (optional)
        * @return mixed Value
        */
        public static function getValue($key, $defaultValue = false)
        {
            if (!isset($key) OR empty($key) OR !is_string($key))
                return false;
            
            $ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));

            if (is_string($ret) === true)
                $ret = urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret)));
            
            return !is_string($ret)? $ret : stripslashes($ret);
        }

        /**
         * In array recursive function for searching multidimensional arrays
         * @static
         * @param $needle
         * @param $haystack
         * @return bool
         */
        public static function inMultidimensionalArray($needle, $haystack)
        {
            foreach ($haystack as $item)
            {
                if ($item === $needle || (is_array($item) && self::inMultidimensionalArray($needle, $item)))
                    return true;
            }

            return false;
        }

        /**
         * Search for a value in any multidimensional array
         * @static
         * @param $array
         * @param $key
         * @param $value
         * @internal param $parents
         * @internal param $searched
         * @return array
         */
        public static function searchMultidimensionalArray( $array, $key, $value )
        {
            $results = array();

            if (is_array($array))
            {
               if ($array[$key] == $value)
                   $results[] = $array;

               foreach ($array as $subarray)
                   $results = array_merge($results, self::searchMultidimensionalArray($subarray, $key, $value));
            }

            return $results;
        }

        public static function isMultiDimensionalArrayEmpty( $array )
        {
            if (is_array($array))
            {
                foreach ($array as $value)
                {
                    if (!self::isMultiDimensionalArrayEmpty($value))
                    {
                        return false;
                    }
                }
            }
            elseif (!empty($array))
            {
                return false;
            }

            return true;
        }

        public static function getCurrentPageUrl()
        {
            $url = 'http';

            if ($_SERVER["HTTPS"] == "on")
                $url .= "s";

            $url .= "://";

            if ($_SERVER["SERVER_PORT"] != "80")
                $url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            else
                $url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

            return $url;
        }
	}
}