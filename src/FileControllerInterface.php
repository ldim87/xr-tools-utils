<?php

/**
 * @author Oleg Isaev (PandCar)
 * @contacts vk.com/id50416641, t.me/pandcar, github.com/pandcar
 */

namespace XrTools;

/**
 * File Controller Interface
 */
interface FileControllerInterface
{
	/**
	 * @param string $path      path to file
	 * @param array $arr_lenta  pass variables in array
	 * @param array $extract    extract variables by name
	 * @return string|bool
	 */
	function getFile(string $path, array $arr_lenta = [], array $extract = []);

	/**
	 * Echoes the result of the getFile method with error code if the result is false
	 * @param string $path      path to file
	 * @param int $err          http error code
	 * @param array $arr_lenta  pass variables in array
	 * @param array $extract    extract variables by name
	 * @return void
	 */
	function reqFile(string $path, $err = 404, array $arr_lenta = [], array $extract = []);
}
