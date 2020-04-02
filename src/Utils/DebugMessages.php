<?php

/**
 * @author Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

class DebugMessages
{
	/**
	 * @var array
	 */
	protected $messages = [];

	protected $html_enabled = true;
	
	protected $trace_enabled = true;

	function __construct(array $opt = []){

		if(isset($opt['trace_enabled']))
		{
			$this->trace_enabled = !empty($opt['trace_enabled']);
		}

		if(isset($opt['html_enabled']))
		{
			$this->html_enabled = !empty($opt['html_enabled']);
		}
		
	}

	/**
	 * @param string $message
	 * @param string|null $method
	 * @param null $data
	 */
	public function log(string $message, string $method = null, $data = null)
	{
		// prepend to message
		$message = (isset($method) ? $method . ': ' : '') . $message;

		// tracing
		if($this->trace_enabled)
		{
			$trace = (new \Exception)->getTrace()[0];

			$trace_message = "File {$trace['file']} line {$trace['line']}\n";

			if($this->html_enabled)
			{
				$trace_message = "<span class='dbg-trace-info'>".$trace_message.'</span>';
			}
			
			$message .= $trace_message;
		}
		
		$this->messages[] = [
			'message' => $message,
			'data' => $data,
		];
	}

	/**
	 * @return array
	 */
	public function getMessages()
	{
		return $this->messages;
	}
}
