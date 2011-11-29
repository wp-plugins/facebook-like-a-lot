<?
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
         * @param $string
         * @return string
         */
		public static function NormalizeStringToFileName($string)
		{
			$table = array(
				'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
				'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
				'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
				'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
				'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
				'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
				'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
				'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '/' => '', '\\' => '', '_' => '-',  '?' => '', '!' => '', '$' => '',
				'|' => '', '*' => '', '<' => '', '>' => '', '.' => '-', ',' => '-', ';' => '-', ':' => '-', '&' => '',
				'#' => '', '@' => '', '`' => '', '\'' => '', 'ˆ' => '', '(' => '', ')' => '', '˜' => '', '+' => '',
				'=' => '', ' ' => '-'
			);
			return strtr($string, $table);
		}

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
        public static function in_array_r($needle, $haystack)
        {
            foreach ($haystack as $item)
            {
                if ($item === $needle || (is_array($item) && self::in_array_r($needle, $item)))
                    return true;
            }

            return false;
        }
	}
}