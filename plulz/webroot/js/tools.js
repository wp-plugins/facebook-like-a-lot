/**
* Function : dump()
* Arguments: The data - array,hash(associative array),object
*    The level - OPTIONAL
* Returns  : The textual representation of the array.
* This function was inspired by the print_r function of PHP.
* This will accept some data as the argument and return a
* text that will be a more readable version of the
* array/hash/object that is given.
*/
function dump(arr,level)
{
    var dumped_text = "";
    if(!level) level = 0;

    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";

    if(typeof(arr) == 'object') { //Array/Hashes/Objects

        for(var item in arr)
        {
            var value = arr[item];

            if(typeof(value) == 'object') { //If it is an array,
            dumped_text += level_padding + "'" + item + "' ...\n";
            dumped_text += dump(value,level+1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Stings/Chars/Numbers etc.
        dumped_text = "==> "+arr+" <==("+typeof(arr)+")";
    }
    return dumped_text;
}

/**
* Function : round_up()
* Arguments: value, precision (decimal fields)
* Returns  : The number rounded up according to the defined precision
*/
function round_up(value, precision)
{
	if (typeof(roundMode) == 'undefined')
		roundMode = 2;
	if (typeof(precision) == 'undefined')
		precision = 2;

	method = roundMode;
	if (method == 0)
		return ceilf(value, precision);
	else if (method == 1)
		return floorf(value, precision);
	precisionFactor = precision == 0 ? 1 : Math.pow(10, precision);
	return Math.round(value * precisionFactor) / precisionFactor;
}

/**
* Function : in_array()
* Arguments: value, array to be checked
* Returns  : Returns true if the passed $value exists in the defined $array
*/
function in_array($value, $array)
{
	var valueExists = false;
	for (var i = 0; i<$array.length;i++)
	{
		if ($value == $array[i])
			valueExists = true;
		if (valueExists)
			break;
	}
	return valueExists;
}

/**
 * Function: NumbersOnly()
 * Arguments: event
 * Returns: Bool, true if the key pressed is in the allowed ones
 * @param event
 */
function NumbersOnly(event)
{
    // Which key was pressed?
    var charCode = event.keyCode;

    // Allowed keys
    if ((charCode > 47 && charCode < 58)        // numeros telcado
        || (charCode > 95 && charCode < 106)    // numpad
        || (charCode > 34 && charCode < 40)     //setas direcionais + home + end
        || charCode == 8    //backspace
        || charCode == 46   //del
        || charCode == 13   //enter
        )
        return true;
    else
        return false;
}

/**
 * Function: isEmpty()
 * Arguments: string
 * Returns: Bool, true if str is empty
 * @param event
 */
function isEmpty(str) {
    return (!str || 0 === str.length);
}

/**
 * Function: isDefined
 * Argument: variable
 * Returns: Bool, true if the variable is defined
 * @param variable
 */
function isDefined(variable){
    return typeof variable != 'undefined';
}

/**
 * Function capitalize
 * Argument: none
 * Returns Capitalizes the first letter on each word
 * 
 */
String.prototype.capitalize = function(){
    return this.replace(/\S+/g, function(a){
        return a.charAt(0).toUpperCase() + a.slice(1).toLowerCase();
    });
};