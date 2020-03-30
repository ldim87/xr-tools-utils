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

	/**
	 * @param string $message
	 * @param string|null $method
	 * @param null $data
	 */
	public function log(string $message, string $method = null, $data = null)
	{
		// prepend to message
		$message = (isset($method) ? $method . ': ' : '') . $message;

		// Путь к вызову
		$trace = (new \Exception)->getTrace()[0];
		$message .= " <br>\n".'File '.$trace['file'].' line '.$trace['line'];

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
