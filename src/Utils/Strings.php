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
		if(empty($str)){
			return '';
		}
		else {
			return htmlspecialchars($str, ENT_QUOTES);
		}
	}

	// :TODO:
}
