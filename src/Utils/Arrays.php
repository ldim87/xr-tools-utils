<?php

/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

/**
 * Arrays utilities
 */
class Arrays
{
	/**
	 * [print description]
	 * @param  [type]  $arr    [description]
	 * @param  integer $format [description]
	 * @return [type]          [description]
	 */
	function print($arr, $format = 0)
	{
		// форматирование через nl2br
		if($format === 1){
			return nl2br(ival(print_r($arr, true)));
		}
		
		// дефолт
		return '<pre>'.htmlspecialchars(print_r($arr,true)).'</pre>';
	}

	/**
	 * Выдача данных из массива по названию ключа
	 * @param  array  	$arr     	Data array
	 * @param  string  	$key     	Key name
	 * @param  boolean 	$def_val 	Default value if data were not found
	 * @return mixed           		Array key value
	 */
	function key($arr, $key, $def_val = null)
	{
		
		if(!$arr || !is_array($arr)){
			return $def_val;
		}
		
		return isset($arr[$key]) ? $arr[$key] : $def_val;
	}

	/**
	 * Создает массив из текста, разбитого по строкам
	 * @param  string $str 	String
	 * @return array      	Exploded string by lines
	 */
	function explodeByLines($str = '')
	{
		return !empty($str) ? explode("\n", str_replace(array("\r\n", "\r"), "\n", $str)) : array();
	}

	/**
	 * Группировка массива по выбранному индексу с возможностью фильтра
	 * @param  array   $arr       Array
	 * @param  string  $index     Selected key name to group array by
	 * @param  array   $selective Selective mode. Filter result array by selected keys
	 * @param  array   $params    Settings:
	 *                             <ul>
	 *                             		<li> <strong> direct_value </strong> bool (false)
	 *                             		 - Works only in Selective mode!
	 *                             		 Use direct value instead of array as item in the result array (e.g. when only one key in $selective).
	 *                             </ul>
	 * @return array             Groupped array
	 */
	function groupByKey($arr = array(), $index = '', $selective = array(), $params = array())
	{
		$result = array();

		if(empty($arr)){
			return $result;
		}

		// если нужно сохранить только выбранные ключи
		$save_full_row = empty($selective) || !is_array($selective);

		// если не нужен массив, а просто величина в selective
		$direct_value = !empty($params['direct_value']);

		// проходим по массиву и группируем
		foreach ($arr as $row){
			if(!isset($row[$index])){
				break;
			}

			if($save_full_row){
				$result[$row[$index]][] = $row;
			}
			// Если сохраняем только выбранные колонки
			else {
				$tmp = array();
				foreach ($selective as $col){
					if(!isset($row[$col]))
						continue;

					// Если настроена прямая запись (без-массивная)
					if($direct_value){
						$tmp = $row[$col];
						break;
					}

					$tmp[$col] = $row[$col];
				}
				$result[$row[$index]][] = $tmp;
			}
		}

		return $result;
	}

	/**
	 * Индексирует массив по заданным ключам элемента в данном массиве.
	 * Пример: <br>
	 * 	$original_array = [ 0 => ['id'=>1, 'name'=>'test 1'], 1 => ['id'=>2, 'name'=>'test 2'] ] <br>
	 * 	$result_array = arr_index( $original_array, 'id' ) <br>
	 *  => [ <b>1</b> => ['id'=>1, 'name'=>'test 1'], <b>2</b> => ['id'=>2, 'name'=>'test 2'] ]
	 * @param  array   $arr       Array to index
	 * @param  string  $columns   Items array key name to index the $arr by
	 * @param  bool    $in_lists  Collect in lists
	 * @param  string  $index_implode_glue
	 * @return array              Indexed array
	 */
	function index($arr, $columns, $in_lists = false, $index_implode_glue = ',')
	{

		if(empty($arr)){
			return [];
		}

		if (is_string($columns)) {
			$columns = [ $columns ];
		}

		$params = [];

		$params []= function() use ($index_implode_glue) {
			return implode($index_implode_glue, func_get_args());
		};

		foreach ($columns as $val) {
			$params []= array_column($arr, $val);
		}

		$tmp = call_user_func_array('array_map', $params);
		$ret = [];

		foreach ($arr as $key => $item)
		{
			if ($in_lists)
			{
				if (! isset($ret[ $tmp[ $key ] ])) {
					$ret[ $tmp[ $key ] ] = [];
				}

				$ret[ $tmp[ $key ] ][ $key ] = $item;
			}
			else {
				$ret[ $tmp[ $key ] ] = $item;
			}
		}

		return $ret;
	}

	/**
	 * Приведение элементов массива к int
	 * @param $array
	 * @return array
	 */
	function itemsIntval($array)
	{
		if (! is_array($array)) {
			$array = [$array];
		}

		return array_map(
			function($item) {
				return (int) $item;
			},
			$array
		);
	}

	/**
	 * Генерация ключей для запросов к базе с оператором WHERE {col} IN (?,?,…).
	 * Могут генерироваться либо знаки вопроса (?), либо наименования.
	 * Пример:
	 * <pre>
	 * // default (question marks):
	 * $data = array('foo', 'bar');
	 * $result = mysql_do('INSERT INTO some_table (col_name) VALUES ('.arr_in($data).')', $data);
	 * // Query: INSERT INTO some_table (col_name) VALUES (?,?)
	 *
	 * // named indexes
	 * $data = array(':foo' => 'bar', ':not_foo' => 'not_bar');
	 * $result = mysql_do('INSERT INTO some_table (col_name) VALUES ('.arr_in($data).')', $data);
	 * // Query: INSERT INTO some_table (col_name) VALUES (:foo, :not_foo)
	 * </pre>
	 *
	 * @param  array   			$arr     	Data array
	 * @param  boolean|string 	$prefix  	If FALSE (default), then question marks (?) are generated if data array is numerically indexed [0=>…, 1=>…],
	 *                                   		otherwise array keys names are used.<br>
	 *                                  	IF STRING is passed, then it is used as prefix to every array key number (not key name itself, but it's numerical position)
	 * @param  boolean 			$force_q 	Force function to generate question marks (?) even if data array is not numerically indexed (see $prefix = FALSE)
	 * @return string           			Generated string
	 */
	function labels($arr = array(), $prefix = false, $force_q = false)
	{
		$return = '';

		if (is_array($arr) && !empty($arr))
		{
			// если мы используем наименования и нужен префикс к ключам
			if ($prefix !== false)
			{
				for ($i = 0, $c = count($arr); $i < $c; $i++) {
					$return .= ($i ? ',' : '') . $prefix . $i;
				}
			}
			// если мы используем пронумерованное поле
			elseif (isset($arr[0]) || $force_q) {
				$return = implode(',', array_fill(1, count($arr), '?'));
			}
			// если мы используем наименования
			else
				$return = implode(',', array_keys($arr));
		}
		return $return;
	}

	/**
	 * Cортирует мультидим.массив по одному из ключей (замена SORT BY в MySQL)
	 * @param  array   $arr         Array to sort
	 * @param  string  $by_key      Key to sort the array by
	 * @param  integer $method      Sorting method (use constants: SORT_ASC / SORT_DESC). Default: SORT_ASC
	 * @param  integer $method_type Sorting method type (see <u>sort_flag</u> in <a href="http://php.net/manual/en/function.sort.php">PHP manual</a>). Default: SORT_NUMERIC
	 * @return array                Sorted array
	 */
	function sortByKey($arr, $by_key, $method = SORT_ASC, $method_type = SORT_NUMERIC)
	{
		if (! $arr)
			return array();

		// переводим ключи в текстовый формат, потому что иначе их array_multisort не запоминает
		$keys_order = array();
		$tmp = array();

		foreach ($arr as $k_index => $k_arr)
		{
			$keys_order['key-' . $k_index] = isset($k_arr[$by_key]) ? $k_arr[$by_key] : 0;
			$tmp['key-' . $k_index] = $k_arr;
			unset($arr[$k_index]);
		}

		unset($arr);

		// для запоминания ключей у одинаковых значений
		$order_dupl = range(1, count($keys_order));

		// сортировка
		array_multisort($keys_order, $method_type, $method, $order_dupl, SORT_ASC, $tmp);

		// Удаляем текстовые ключи и возвращаем
		return array_values($tmp);
	}

	/**
	 * Удаляет элементы массива по значению
	 * @param array $array
	 * @param mixed $value
	 * @param bool  $save_keys
	 */
	function unsetByValue(&$arr, $value, $save_keys = true)
	{
		$keys = array_keys($arr, $value);

		foreach ($keys as $key) {
			unset($arr[ $key ]);
		}

		if (! $save_keys) {
			$arr = array_values($arr);
		}
	}

	/**
	 * Фильтрует список ids
	 * @param $arr
	 * @param bool $unique
	 * @return array
	 */
	function ids($arr, $unique = true)
	{
		$arr = $this->itemsIntval($arr);

		if ($unique) {
			$arr = array_unique($arr);
		}

		return array_filter(
			$arr,
			function ($var) {
				return $var > 0;
			}
		);
	}

	/**
	 * @param $arr
	 * @param bool $unique
	 * @return array
	 */
	function words($arr, $unique = true)
	{
		$arr = array_map('trim', $arr);

		$arr = array_filter($arr, function($item) {
			return $item != '';
		});

		if ($unique) {
			$arr = array_unique($arr);
		}

		return $arr;
	}

	/**
	 * Выбирает из массива заданные столбцы
	 * @param $arr
	 * @param $columns
	 * @param bool $list
	 * @return array|mixed
	 */
	function selectColumns($arr, $columns, $list = false)
	{
		if (! $list) {
			$arr = [ $arr ];
		}

		$result = [];
		$tmp = array_flip($columns);

		foreach ($arr as $key1 => $item)
		{
			$row = [];

			foreach ($tmp as $key2 => $val)
			{
				if (isset($item[ $key2 ])) {
					$row[ $key2 ] = $item[ $key2 ];
				}
			}

			$result[ $key1 ] = $row;
		}

		return $list ? $result : $result[0];
	}

	/**
	 * Выбирает из массива заданные индексы
	 * @param  array  $arr  
	 * @param  array  $keys
	 * @return array
	 */
	function selectKeys(array $arr, array $keys)
	{
		return array_intersect_key($arr, array_flip($keys));
	}

	/**
	 * @param $arr
	 * @param $pattern
	 * @param bool $by_keys
	 * @return array
	 */
	function grep($arr, $pattern, $by_keys = false)
	{
		if ($by_keys)
		{
			return array_intersect_key(
				$arr,
				array_flip(
					preg_grep(
						$pattern,
						array_keys($arr)
					)
				)
			);
		}
		else {
			return preg_grep($pattern, $arr);
		}
	}
}
