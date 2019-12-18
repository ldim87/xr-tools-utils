<?php
/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools;

/**
 * Utilities container
 */
class Utils {
	
	/**
	 * @return \XrTools\Utils\Strings
	 */
	private $strings; function strings(){
		return $this->strings ?: $this->strings = new Utils\Strings;
	}

	/**
	 * Once called, returns the same timestamp per instance
	 * @return integer
	 */
	private $inst_time; function inst_time(){
		return $this->inst_time ?: $this->inst_time = time();
	}

	/**
	 * @return \XrTools\Utils\Files
	 */
	private $files; function files(){
		return $this->files ?: $this->files = new Utils\Files;
	}

}
