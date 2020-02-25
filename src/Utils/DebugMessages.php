<?php
/**
 * @author Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

/**
 * 
 */
class DebugMessages {
	
	protected $messages = [];

	function log(string $message, string $method = null, $data = null){
		// prepend to message
		$message = (isset($method) ? $method . ': ' : '') . $message;

		$this->messages[] = [
			'message' => $message,
			'data' => $data
		];
	}

	function getMessages(){
		return $this->messages;
	}
}
