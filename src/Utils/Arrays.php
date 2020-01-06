<?php
/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

/**
 * Arrays utilities
 */
class Arrays {

	/**
	 * [print description]
	 * @param  [type]  $arr    [description]
	 * @param  integer $format [description]
	 * @return [type]          [description]
	 */
	function print($arr, $format = 0){
		// форматирование через nl2br
		if($format === 1){
			return nl2br(ival(print_r($arr, true)));
		}
		
		// дефолт
		return '<pre>'.print_r($arr,true).'</pre>';
	}


	/**
	 * Выдача данных из массива по названию ключа
	 * @param  array  	$arr     	Data array
	 * @param  string  	$key     	Key name
	 * @param  boolean 	$def_val 	Default value if data were not found
	 * @return mixed           		Array key value
	 */
	function key($arr, $key, $def_val = null){
		
		if(!$arr || !is_array($arr)){
			return $def_val;
		}
		
		return isset($arr[$key]) ? $arr[$key] : $def_val;
	}
}
