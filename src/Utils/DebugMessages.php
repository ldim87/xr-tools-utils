<?php

/**
 * @author Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

/**
 * Class DebugMessages
 * @package XrTools\Utils
 */
class DebugMessages
{
	/**
	 * @var array
	 */
	protected $messages = [];

	/**
	 * @var bool
	 */
	protected $html_enabled = false;

	/**
	 * @var bool
	 */
	protected $trace_enabled = false;

	/**
	 * Constructor
	 * @param array $opt See setOptions()
	 */
	function __construct($opt = null)
	{
		if(isset($opt)){
			$this->setOptions($opt);
		}
	}

	/**
	 * Sets options
	 * @param array $opt Options
	 */
	function setOptions(array $opt = [])
	{
		if (isset($opt['trace_enabled'])) {
			$this->trace_enabled = ! empty($opt['trace_enabled']);
		}

		if (isset($opt['html_enabled'])) {
			$this->html_enabled = ! empty($opt['html_enabled']);
		}
	}

	/**
	 * @param string $message
	 * @param array $opt
	 */
	function log2(string $message, array $opt = []): void
	{
		$checkDebug = $opt['checkDebug'] ?? true;

		if ($checkDebug && empty($opt['debug'])) {
			return;
		}

		$data = $opt['data'] ?? null;
		$method = $opt['method'] ?? null;

		$this->log($message, $method, $data, $opt);
	}

	/**
	 * @param string $message
	 * @param string|null $method
	 * @param null $data
	 * @param array $opt
	 */
	function log(string $message, string $method = null, $data = null, array $opt = []): void
	{
		// prepend to message
		$message = (isset($method) ? $method . ': ' : '') . $message;

		$trace_enabled = $this->trace_enabled || !empty($opt['trace_enabled']);
		$html_enabled = $this->html_enabled || !empty($opt['html_enabled']);

		// tracing
		if ($trace_enabled)
		{
			$except = new \Exception;

			if (! empty($opt['trace_method']))
			{
				$trace = $except->getTrace();
				$traceLast = $trace[ $opt['trace_key'] ?? 0 ];
				$message = $traceLast['class'].$traceLast['type'].$traceLast['function'].'(..) - '.$message;
			}

			$traceString = $this->traceAdaptive($except);

			if ($html_enabled) {
				$traceString = $this->traceAdaptiveStringToHtml($traceString);
			}
			
			$message .= "\n\n".$traceString;
		}
		
		$this->messages[] = [
			'message' => $message,
			'data'    => $data,
		];
	}

	/**
	 * @return array
	 */
	function getMessages()
	{
		return $this->messages;
	}

	/**
	 * @param string $traceString
	 * @return string
	 */
	protected function traceAdaptiveStringToHtml(string $traceString): string
	{
		$traceString = nl2br($traceString);

		$traceString = preg_replace('~(#\d+)~', '<span class="dbg-trace-num">$1</span>', $traceString);

		$traceString = preg_replace('~(\(\d+\):)~', '<span class="dbg-trace-line">$1</span>', $traceString);

		return '<span class="dbg-trace-info">'.$traceString.'</span>';
	}

	/**
	 * @param \Exception $except
	 * @return string
	 */
	protected function traceAdaptive(\Exception $except): string
	{
		return preg_replace_callback(
			"~#(\d+) ((.+)\(\d+\):|)(.+)(\n|)~i",
			function ($matc){
				if (empty($matc[2]) || substr_count($matc[4], __CLASS__)){
					return '';
				}
				$path = str_replace('\\', '/', $matc[2]);
				if (isset($_SERVER['DOCUMENT_ROOT'])) {
					$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
				}
				return '#'.$matc[1].' '.$path . $matc[4]."\n";
			},
			$except->getTraceAsString()
		);
	}
}

