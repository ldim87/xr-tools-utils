<?php
/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

/**
 * Custom utilities
 */
class Strings {

	/**
	 * [ival description]
	 * @param  [type] $str [description]
	 * @return [type]      [description]
	 */
	function filter($str){
		return htmlspecialchars($str, ENT_QUOTES);
	}

	/**
	 * Быстрая проверка чисел (больше или равно 0)
	 * @param  mixed   $str      Checking value if it is a non-negative number (&gt;=0)
	 * @param  boolean $positive Demand number to be greater than 0
	 * @return boolean           Status result
	 */
	function isNum($str, $positive = false){
		return $str == '0' . $str && (!$positive || $str > 0);
	}

	/**
	 * Перевод числа в байты, килобайты, мегабайты, гигабайты
	 * @param  integer  $num     	Number
	 * @param  integer $decimals  	Sets the number of decimal points. Default: 0 (auto)
	 * @param  integer $precision 	Precision:
	 *                             	<ul>
	 *                             		<li> <strong> 0 </strong> - Automatic precision by the number length (default)
	 *                             		<li> <strong> 1 </strong> - Bytes
	 *                             		<li> <strong> 2 </strong> - Kilobytes
	 *                             		<li> <strong> 3 </strong> - Megabytes
	 *                             		<li> <strong> 4 </strong> - Gigabytes
	 *                             	</ul>
	 * @return string           	Converted number to bytes
	 */
	function nameToBit($num, $decimals = 0, $precision = 0){
		
		$precision = $precision ? $precision : ceil(strlen($num) / 3);

		switch ($precision){
			case 1: $return = number_format($num, 0, '.', ' ') . " B";
				break;
			case 2: $num = $num / 1024;
				$return = number_format($num, $decimals ? $decimals : 1, '.', ' ') . " KB";
				break;
			case 3: $num = $num / 1024 / 1024;
				$return = number_format($num, $decimals ? $decimals : 2, '.', ' ') . " MB";
				break;
			case 4:
			default: $num = $num / 1024 / 1024 / 1024;
				$return = number_format($num, $decimals ? $decimals : 3, '.', ' ') . " GB";
				break;
		}

		return $return;
	}

	/**
	 * Выводит JSON
	 * @param $array
	 * @param bool $header
	 * @return bool
	 */
	function echoJson(array $array, bool $header = false)
	{
		if (! empty($header)) {
			header('Content-Type: application/json');
		}

		echo $this->jsonEncode($array);
	}

	/**
	 * [jsonEncode description]
	 * @param  array  $array [description]
	 * @return [type]        [description]
	 */
	function jsonEncode(array $array){
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Проверка валидности даты
	 * @param $date
	 * @param string $format
	 * @return bool
	 */
	function isValidDate($date, $format = 'Y-m-d H:i:s')
	{
		$d = \DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}


	// :TODO:REFACTOR: continue
	
	
}
