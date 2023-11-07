<?php

/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

use JetBrains\PhpStorm\ArrayShape;

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
	function print($arr)
	{
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
	 * @return array             Grouped array
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
	 * @param array $arr
	 * @param mixed $value
	 * @param bool $saveKeys
	 */
	function unsetByValue(array &$arr, $value, bool $saveKeys = true)
	{
		$keys = array_keys($arr, $value);

		foreach ($keys as $key) {
			unset($arr[ $key ]);
		}

		if (! $saveKeys) {
			$arr = array_values($arr);
		}
	}

	/**
	 * @param string $string
	 * @param string $separator
	 * @param bool $unique
	 * @return array
	 */
	function explodeIds(string $string, string $separator = ',', bool $unique = true): array
	{
		return $this->ids(
			explode($separator, $string),
			$unique
		);
	}

	/**
	 * Фильтрует список ids
	 * @param array $arr
	 * @param bool $unique
	 * @return array
	 */
	function ids(array $arr, bool $unique = true): array
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
	 * @param array $arr
	 * @param bool $unique
	 * @return array
	 */
	function words(array $arr, bool $unique = true): array
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
	 * @param array $array
	 * @param array $columns
	 * @return array
	 */
	function selectColumnsList(array $array, array $columns): array
	{
		return $this->selectColumns($array, $columns, true);
	}

	/**
	 * Выбирает из массива заданные столбцы
	 * @param array $array
	 * @param array $columns
	 * @param bool $list
	 * @param bool $nonExistentNull
	 * @return array|mixed
	 */
	function selectColumns(array $array, array $columns, bool $list = false, bool $nonExistentNull = false)
	{
		if (! $list) {
			$array = [ $array ];
		}

		$result = [];

		foreach ($array as $itemKey => $item)
		{
			$row = [];

			foreach ($columns as $key => $val)
			{
				$get = is_int($key) ? $val : $key;

				if (array_key_exists($get, $item)) {
					$row[ $val ] = $item[ $get ];
				} elseif ($nonExistentNull) {
					$row[ $val ] = null;
				}
			}

			$result[ $itemKey ] = $row;
		}

		if (! $list) {
			return $result[0] ?? null;
		}

		return $result;
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
	 * @param array $arr
	 * @param string $pattern
	 * @param bool $byKeys
	 * @return array
	 */
	function grep(array $arr, string $pattern, bool $byKeys = false): array
	{
		if ($byKeys) {
			$grep = preg_grep($pattern,	array_keys($arr));
			return array_intersect_key($arr, array_flip($grep));
		} else {
			return preg_grep($pattern, $arr);
		}
	}

	/**
	 * Разбить массив на заданное количество частей
	 * @param array $array
	 * @param int $pieces
	 * @param bool $emptyItems
	 * @return array
	 */
	function splitPieces(array $array, int $pieces = 2, $emptyItems = true): array
	{
		if ($pieces < 2) {
			return [ $array ];
		}

		$newCount = ceil( count($array) / $pieces);
		$a = array_slice($array, 0, $newCount);
		$b = $this->splitPieces( array_slice($array, $newCount), $pieces - 1);

		$res = array_merge( [ $a ], $b );

		if (! $emptyItems) {
			$res = array_filter($res, function($item) {
				return $item !== [];
			});
		}

		return $res;
	}

	/**
	 * Получить все вложенные parent_id по parent_id
	 * @param array $list
	 * @param array $ids
	 * @return array
	 */
	function categoryNestedParentIds(array $list, array $ids): array
	{
		$list = $this->index($list, 'parent_id', true);

		return $this->categoryNestedParentIdsRecursion($list, $ids);
	}

	/**
	 * Получить всех детей по parent_id
	 * @param array $list
	 * @param array $ids
	 * @return array
	 */
	function categoryChildrenIds(array $list, array $ids): array
	{
		$list = $this->index($list, 'parent_id', true);

		$res = $this->categoryChildrenRecursion($list, $ids);

		unset($res[0]);

		return $res;
	}

	/**
	 * @param string|null $json
	 * @return array
	 */
	function arrayFromJson(string $json = null): array
	{
		if (empty($json)) {
			return [];
		}

		$arr = json_decode($json, true);

		if (! is_array($arr)) {
			return [];
		}

		return $arr;
	}

	/**
	 * @param string|null $serialize
	 * @return array
	 */
	function arrayFromSerialize(string $serialize = null): array
	{
		if (empty($serialize)) {
			return [];
		}

		$arr = unserialize($serialize);

		if (! is_array($arr)) {
			return [];
		}

		return $arr;
	}

	/**
	 * Update targetList values with sourceList values given the column (item key) name
	 * @param array  $targetList List to update
	 * @param array  $sourceList List with updated data
	 * @param string $column     Item key name by which to index the sourceList
	 * @param array  $defaultRow Default item data if sourceList item is not found
	 * @return array
	 */
	function updateListByColumn(array $targetList, array $sourceList, string $column, array $defaultRow = []): array
	{
		$sourceList = $this->index($sourceList, $column);

		foreach ($targetList as $key => $row)
		{
			$info = $sourceList[ $row[$column] ] ?? $defaultRow;

			$targetList[ $key ] = array_merge($targetList[ $key ], $info);
		}

		return $targetList;
	}

	/**
	 * Merge two lists given the column name (item key) name
	 * @param array  $list
	 * @param array  $list2
	 * @param string $column      Item key name by which to index the sourceList
	 * @param array  $defaultRow  Default item data if sourceList item is not found
	 * @return array
	 */
	function mergeListsByColumn(array $list, array $list2, string $column, array $defaultRow = []): array
	{
		$list2 = $this->index($list2, $column);

		$merged_keys = [];

		foreach ($list as $key => $row)
		{
			$info = $list2[ $row[$column] ] ?? $defaultRow;

			$list[ $key ] = array_merge($list[ $key ], $info);

			$merged_keys[] = $row[$column];
		}

		// добавляем ключи из list2, которых нет в list
		foreach ($list2 as $key => $row)
		{
			if(!in_array($key, $merged_keys)){
				$list[] = $row;
			}
		}

		return $list;
	}

	/**
	 * Рандом из массива
	 * @param array $array
	 * @param int $count
	 * @param bool $saveKeys
	 * @return array
	 */
	function randItems(array $array, int $count, bool $saveKeys = false): array
	{
		if (! $array) {
			return [];
		}

		if ($count >= count($array)) {
			shuffle($array);
			return $array;
		}

		$keys = array_rand($array, $count);

		if (is_null($keys)) {
			return [];
		}

		if (! is_array($keys)) {
			$keys = (array) $keys;
		}

		shuffle($keys);

		$newArray = [];

		foreach ($keys as $key)
		{
			if ($saveKeys) {
				$newArray[ $key ] = $array[ $key ];
			} else {
				$newArray []= $array[ $key ];
			}
		}

		return $newArray;
	}

	/**
	 * @param array $arr
	 * @param array $keys
	 * @return bool
	 */
	function issetKeys(array $arr, array $keys): bool
	{
		$count = 0;

		foreach ($keys as $key)
		{
			if (isset($arr[ $key ])) {
				$count++;
			}
		}

		return count($keys) == $count;
	}

	/**
	 * @param array $arr
	 * @param int|string $column
	 * @param mixed $val
	 * @return int|null
	 */
	function keyByColumn(array $arr, $column, $val): ?int
	{
		$res = array_search($val, array_column($arr, $column));
		return is_int($res) ? $res : null;
	}

	/**
	 * @param array $arr
	 * @param string|int|float $column
	 * @param mixed $value
	 * @return mixed
	 */
	function getByColumn(array $arr, string|int|float $column, mixed $value): mixed
	{
		foreach ($arr as $item)
		{
			if (is_array($item) && array_key_exists($column, $item) && $item[$column] == $value) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * @param array $current
	 * @param array $input
	 * @return array|null
	 */
	function getChanges(array $current, array $input): ?array
	{
		$add = array_diff($input, $current);
		$delete = array_diff($current, $input);

		if (! $add && ! $delete) {
			return null;
		}

		return [
			'add' => $add,
			'delete' => $delete
		];
	}

	/**
	 * @param array $array
	 * @param array $keys
	 * @return array
	 */
	function removeKeys(array $array, array $keys): array
	{
		return array_diff_key(
			$array,
			array_flip($keys)
		);
	}

	/**
	 * @param array $array
	 * @param array $values
	 * @return array
	 */
	function removeValues(array $array, array $values): array
	{
		return array_diff($array, $values);
	}

	/**
	 * @param array $arr
	 * @param string $key
	 * @param mixed $val
	 * @return void
	 */
	function append(array &$arr, string $key, mixed $val): void
	{
		if (empty($arr[$key]) || ! is_array($arr[$key])) {
			$arr[$key] = [];
		}

		$arr[$key] []= $val;
	}

	/**
	 * @param array $arr
	 * @param int $flags
	 * @return array
	 */
	function sort(array $arr, int $flags = SORT_REGULAR): array
	{
		sort($arr, $flags);

		return $arr;
	}

	/**
	 * @param array $arr
	 * @param array|string $columns
	 * @return array
	 */
	function removeColumns(array $arr, array|string $columns): array
	{
		if (! $columns) {
			return $arr;
		}

		if (! is_array($columns)) {
			$columns = (array) $columns;
		}

		return array_map(function ($item) use ($columns){

			foreach ($columns as $column) {
				unset($item[$column]);
			}

			return $item;

		}, $arr);
	}

	/**
	 * @param array $list
	 * @param array $ids
	 * @return array
	 */
	private function categoryNestedParentIdsRecursion(array $list, array $ids): array
	{
		$res = [];

		foreach ($ids as $id)
		{
			if (isset($list[ $id ]))
			{
				$res []= $id;

				$nextIds = array_column($list[ $id ], 'id');

				$res = array_merge(
					$res,
					$this->categoryNestedParentIdsRecursion($list, $nextIds)
				);
			}
		}

		return $res;
	}

	/**
	 * @param array $list
	 * @param array $ids
	 * @return array
	 */
	private function categoryChildrenRecursion(array $list, array $ids): array
	{
		$res = [];

		foreach ($ids as $id)
		{
			$res []= $id;

			if (isset($list[ $id ]))
			{
				$newIds = array_column($list[ $id ], 'id');

				$newRes = $this->categoryChildrenRecursion($list, $newIds);

				$res = array_merge($res, $newRes);
			}
		}

		return $res;
	}
}

