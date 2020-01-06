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
	function ival($str){
		return htmlspecialchars($str, ENT_QUOTES);
	}

	/**
	 * Быстрая проверка чисел (больше или равно 0)
	 * @param  mixed   $str      Checking value if it is a non-negative number (&gt;=0)
	 * @param  boolean $positive Demand number to be greater than 0
	 * @return boolean           Status result
	 */
	function is_num($str, $positive = false){
		return $str == '0' . $str && (!$positive || $str > 0);
	}

	// :TODO:REFACTOR: continue

	
}
