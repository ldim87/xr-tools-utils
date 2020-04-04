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

	/**
	 * @return \XrTools\Utils\Arrays
	 */
	private $arrays; function arrays(){
		return $this->arrays ?: $this->arrays = new Utils\Arrays;
	}

	/**
	 * @return \XrTools\Utils\Remote
	 */
	private $remote; function remote(){
		return $this->remote ?: $this->remote = new Utils\Remote;
	}
	
	/**
	 * @return \XrTools\Utils\DebugMessages
	 */
	private $dbg; function dbg(array $opt = []){
		return $this->dbg ?: $this->dbg = new Utils\DebugMessages($opt);
	}

}
