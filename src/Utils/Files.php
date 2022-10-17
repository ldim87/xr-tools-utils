<?php

/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

/**
 * Files utilities
 */
class Files
{
    /**
     * @param Strings $strings
     */
    function __construct(
        private \XrTools\Utils\Strings $strings
    ){}

    /**
	 * [dir_create description]
	 * @param  [type] $new_path [description]
	 * @return [type]           [description]
	 */
	function dirCreate($new_path)
	{
		if (! is_dir($new_path))
		{
			$arr_path = explode(DIRECTORY_SEPARATOR, $new_path);

			if ($arr_path)
			{
				// relative path prefix
				$tmp = mb_substr($new_path, 0, 1) == '/' ? '' : '.';

				foreach ($arr_path as $a_val)
				{
					// filter
					$a_val = ltrim(trim($a_val), '.');

					if (!mb_strlen($a_val)) {
						continue;
					}

					$tmp .= '/' . $a_val;

					if (! is_dir($tmp) && ! mkdir($tmp)) {
						return false;
					}
				}
			}
		}

		return true;
	}

	// :TODO: WIP

	/**
	 * Копирование файла (создает путь, если его нет)
	 * @param  string $path     File to copy
	 * @param  string $new_path New file path (directories are created on fly if they don't exist)
	 * @return boolean          Result status
	 */
	function copy($path, $new_path)
	{
		if (! $path || ! is_file($path)) {
			return false;
		}

		$dirname = pathinfo($new_path, PATHINFO_DIRNAME);

		if ($dirname && ! $this->dirCreate($dirname)) {
			return false;
		}

		return copy($path, $new_path);
	}

	/**
	 * Удаление папки
	 * @param  [type] $dir [description]
	 * @return [type]      [description]
	 */
	function dirRemove($dir)
	{
		if (! is_dir($dir)) {
			return true;
		}

		foreach (scandir($dir) as $file)
		{
			if ('.' === $file || '..' === $file){
				continue;
			}

			if (is_dir("$dir/$file") && !is_link("$dir/$file")){
				$this->dirRemove("$dir/$file");
			}
			else {
				unlink("$dir/$file");
			}
		}

		return rmdir($dir);
	}

	/**
	 * Определение типа файла (расширение)
	 * @param  string $name File path or name
	 * @return string       File extension
	 */
	function getType($name)
	{
		// разбиваем на массив
		$name = explode('.', $name);

		// выделяем расширения
		$name = array_pop($name);

		// возвращаем
		return strtolower($name);
	}

	/**
	 * Возвращает максимальный размер загружаемых файлов
	 * @return int  Max upload size from php.ini
	 */
	function getUploadMaxSize(): int
    {
        $max_upload = $this->strings->convertToBytes( ini_get('upload_max_filesize'));
        $max_post = $this->strings->convertToBytes( ini_get('post_max_size'));

		return min($max_upload, $max_post);
	}

	/**
	 * Загрузка файла из кэша
	 * @param  string  $file_name   File path relative to "cache/" folder and without extension (".html" is appended automatically)
	 * @param  integer $expire_time File max expire time (in seconds)
	 * @return string|boolean       File contents or FALSE if cache expired or not found
	 */
	function loadCacheFile(string $file_name, int $expire_time, string $dir_path = null)
	{
		// generate path
		$file_path = ($dir_path ?? 'cache') . '/' . $file_name . '.html';

		// file not found
		if (! is_file($file_path)) {
			return false;
		}

		// cache expired
		if (time() > (filemtime($file_path) + $expire_time)) {
			return false;
		}

		// valid cache contents
		return file_get_contents($file_path);
	}

	/**
	 * Запись контента в файловый кэш
	 * @param  string $file_name File path relative to "cache/" folder and without extension (".html" is appended automatically)
	 * @param  string $content   File content to store
	 * @return boolean           Result status
	 */
	function saveCacheFile($file_name, $content, string $dir_path = null)
	{
		$file_path = ($dir_path ?? 'cache') . '/' . $file_name . '.html';

		if (! $this->dirCreate(pathinfo($file_path, PATHINFO_DIRNAME))) {
			return false;
		}

		return file_put_contents($file_path, $content);
	}

	/**
	 * Шардинг путей
	 *
	 * @param $id
	 * @return string
	 */
	function shardingPath($id)
	{
		$hash = md5($id);
		$patch = [
			substr($hash, -4, 2),
			substr($hash, -2),
			$id,
		];

		return implode('/', $patch);
	}

	/**
	 * Запись контента в файл. Папка создается на лету, если ее нет.
	 * @param  string $path File path. Folders will be created if not exist
	 * @param  string $str  File content
	 * @return boolean      Operation status
	 */
	function strToFile($path, $str = '')
	{
		// если не задан путь
		if (! $path)
			return false;

		// пытаемся создать файл
		if (! is_file($path) && ! $this->dirCreate(pathinfo($path, PATHINFO_DIRNAME)))
			return false;

		//пишем в файл
		return file_put_contents($path, $str);
	}

	/**
	 * @param string $extension
	 * @return string
	 */
	function generateName(string $extension = ''): string
	{
		return md5(microtime(true).'_'.mt_rand().'_'.mt_rand()) . ($extension ? '.'.$extension : '');
	}
}

