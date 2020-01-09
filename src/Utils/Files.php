<?php
/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

/**
 * Files utilities
 */
class Files {


	/**
	 * [dir_create description]
	 * @param  [type] $new_path [description]
	 * @return [type]           [description]
	 */
	function dir_create($new_path){
		
		if(!is_dir($new_path)){
			$arr_path = explode(DIRECTORY_SEPARATOR, $new_path);
			
			if($arr_path){
				$tmp = '.';
				foreach ($arr_path as $a_val){
					// filter
					$a_val = trim($a_val);
					if(!strlen($a_val)){
						continue;
					}
					$tmp .= '/' . $a_val;
					if(!is_dir($tmp) && !mkdir($tmp)){
						return false;
					}
				}
			}
		}

		return true;
	}
	
	// :TODO: WIP
}
