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
	 * @param string $path
	 * @param array $arr_lenta
	 * @param array $extract
	 * @return string|bool
	 */
	public function getFile(string $path, array $arr_lenta = [], array $extract = []);
}
