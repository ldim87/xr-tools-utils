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

	/**
	 * ob_include_file()
	 *
	 * Include file and return buffered output
	 * @param  string $path
	 * @param  array  $arr_lenta 
	 * @return [type]            
	 */
	function ob_include(string $path, $arr_lenta = []){
		// not found
		if(!is_file($path)){
			return false;
		}

		// buffered include
		ob_start();
		include $path;
		return ob_get_clean();
	}





	// :TODO: WIP
}
