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
	 * Init options
	 * @var array
	 */
	private $opt = [];

	/**
	 * Constructor
	 * @param array $opt See setOptions()
	 */
	function __construct(array $opt = null){
		if(isset($opt)){
			$this->setOptions($opt);
		}
	}

	/**
	 * Sets options for services (strings, files, arrays, remote, dbg)
	 * @param array $opt Options
	 */
	function setOptions(array $opt = []){
		$this->opt = $opt;
	}
	
	/**
	 * @return \XrTools\Utils\Strings
	 */
	private $strings; function strings(){
		return $this->strings ?: $this->strings = new Utils\Strings($this->opt['strings'] ?? null);
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
		return $this->files ?: $this->files = new Utils\Files($this->opt['files'] ?? null);
	}

	/**
	 * @return \XrTools\Utils\Arrays
	 */
	private $arrays; function arrays(){
		return $this->arrays ?: $this->arrays = new Utils\Arrays($this->opt['arrays'] ?? null);
	}

	/**
	 * @return \XrTools\Utils\Remote
	 */
	private $remote; function remote(){
		return $this->remote ?: $this->remote = new Utils\Remote($this->opt['remote'] ?? null);
	}
	
	/**
	 * @return \XrTools\Utils\DebugMessages
	 */
	private $dbg; function dbg(){
		return $this->dbg ?: $this->dbg = new Utils\DebugMessages($this->opt['dbg'] ?? null);
	}
}
