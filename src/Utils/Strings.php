<?php

/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

/**
 * Custom utilities
 */
class Strings
{

	private $floatformat_decimals,
		$floatformat_dec_point,
		$floatformat_thousands_sep,
		$numformat_thousands_sep;

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
		if(isset($opt['numformat_thousands_sep'])){
			$this->setNumberFormat($opt['numformat_thousands_sep']);
		}

		if(
			isset($opt['floatformat_decimals']) &&
			isset($opt['floatformat_dec_point']) &&
			isset($opt['floatformat_thousands_sep'])
		){
			$this->setFloatFormat(
				$opt['floatformat_decimals'],
				$opt['floatformat_dec_point'],
				$opt['floatformat_thousands_sep']
			);
		}
	}

	/**
	 * Set default whole number format
	 * @param int    $decimals      [description]
	 * @param string $dec_point     [description]
	 * @param string $thousands_sep [description]
	 */
	function setNumberFormat(string $thousands_sep){
		$this->numformat_thousands_sep = $thousands_sep;
	}

	/**
	 * Set default float number format
	 * @param int    $decimals      [description]
	 * @param string $dec_point     [description]
	 * @param string $thousands_sep [description]
	 */
	function setFloatFormat(int $decimals, string $dec_point, string $thousands_sep){
		$this->floatformat_decimals = $decimals;
		$this->floatformat_dec_point = $dec_point;
		$this->floatformat_thousands_sep = $thousands_sep;
	}

	/**
	 * [ival description]
	 * @param  [type] $str [description]
	 * @return [type]      [description]
	 */
	function filter($str)
	{
		return htmlspecialchars((string) $str, ENT_QUOTES);
	}

	/**
	 * @param string $string
	 * @param int|null $length
	 * @param bool $br
	 * @return string
	 */
	function filterText(string $string, int $length = null, bool $br = true): string
	{
		if ($length) {
			$string = mb_strimwidth($string, 0, $length, '...');
		}

		$string = $this->filter($string);

		if ($br) {
			$string = nl2br($string);
		}

		return $string;
	}

	/**
	 * Быстрая проверка чисел (больше или равно 0)
	 * @param  mixed   $val      Checking value if it is a non-negative number (&gt;=0)
	 * @param  boolean $positive Demand number to be greater than 0
	 * @return boolean           Status result
	 */
	function isNum($val, bool $positive = false): bool
	{
		return $val == '0' . $val && (! $positive || $val > 0);
	}

	/**
	 * @param $val
	 * @return bool
	 */
	function isID($val): bool
	{
		return $this->isNum($val, true);
	}

	/**
	 * Перевод числа в байты, килобайты, мегабайты, гигабайты
	 * @param  integer  $num     	Number
	 * @param  integer $decimals  	Sets the number of decimal points. Default: 0 (auto)
	 * @param  integer $precision 	Precision:
	 *                             	<ul>
	 *                             		<li> <strong> 0 </strong> - Automatic precision by the number length (default)
	 *                             		<li> <strong> 1 </strong> - Bytes
	 *                             		<li> <strong> 2 </strong> - Kilobytes
	 *                             		<li> <strong> 3 </strong> - Megabytes
	 *                             		<li> <strong> 4 </strong> - Gigabytes
	 *                             	</ul>
	 * @return string           	Converted number to bytes
	 */
	function nameToBit(float $num, int $decimals = null, int $precision = null)
	{
		$precision = $precision ?? ceil(strlen($num) / 3);

		switch ($precision)
		{
			case 1: $return = $this->floatFormat($num, 0) . " B";
				break;
			case 2: $num = $num / 1024;
				$return = $this->floatFormat($num, $decimals ?? 1) . " KB";
				break;
			case 3: $num = $num / 1024 / 1024;
				$return = $this->floatFormat($num, $decimals ?? 2) . " MB";
				break;
			case 4:
			default: $num = $num / 1024 / 1024 / 1024;
				$return = $this->floatFormat($num, $decimals ?? 3) . " GB";
				break;
		}

		return $return;
	}

	/**
	 * Выводит JSON
	 * @param $array
	 * @param bool $header
	 * @return bool
	 */
	function echoJson(array $array, bool $header = false)
	{
		if (! empty($header)) {
			header('Content-Type: application/json');
		}

		echo $this->jsonEncode($array);
	}

	/**
	 * [jsonEncode description]
	 * @param mixed $array [description]
	 * @return false|string [type]        [description]
	 */
	function jsonEncode($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Проверка валидности даты
	 * @param $date
	 * @param string $format
	 * @return bool
	 */
	function isValidDate($date, $format = 'Y-m-d H:i:s')
	{
		$d = \DateTimeImmutable::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}

	/**
	 * Генератор рандомного текста
	 * @param  integer $length     Length of the output random text
	 * @param  string  $characters Used characters in the output random text. Default: "abcdefghijklmnopqrstuvwxyz0123456789"
	 * @return string              Random text
	 */
	function randomString($length, $characters = 'abcdefghijklmnopqrstuvwxyz0123456789')
	{
		$num_characters = strlen($characters) - 1;
		$return = '';
		
		while (strlen($return) < $length) {
			$return .= $characters[ mt_rand(0, $num_characters) ];
		}
		
		return $return;
	}

	/**
	 * Перевод даты в заданном формате в дату в MySQL формате
	 * @param  string $date        Input date
	 * @param  string $inputformat Input date format. Default: "d.m.Y"
	 * @return string              Converted input date in input format to MySQL format "YYYY-MM-DD"
	 */
	function convertToMysqlDate($date, $inputformat = 'd.m.Y')
	{
		$date = \DateTimeImmutable::createFromFormat($inputformat, $date);
		return $date->format('Y-m-d');
	}

	/**
	 * Проверка равенства транслитирированных текстов (смешанные тексты, где есть и кириллица латиница)
	 * @param  string  $str_1         String 1
	 * @param  string  $str_2         String 2
	 * @param  boolean $search_partly Exact match or search partly. Default: false (exact match)
	 * @return boolean|integer        If $search_partly is true, then position is returned in case of $str_1 is found in $str_1.
	 *                                Otherwise boolean result status is returned (FALSE means not found / not equal)
	 */
	function equalityTranslit($str_1, $str_2, $search_partly = false)
	{
		// в нижний регистр
		$str_1 = mb_strtolower($str_1);
		$str_2 = mb_strtolower($str_2);

		// транслитирируем
		$str_1 = $this->translit($str_1);
		$str_2 = $this->translit($str_2);

		// если ищем влючение
		if ($search_partly) {
			$return = mb_strpos($str_1, $str_2);
		}
		// если сравниваем значения
		elseif ($str_1 == $str_2) {
			$return = true;
		}
		// тексты не равны
		else {
			$return = false;
		}

		return $return;
	}

	/**
	 * Экранирование поступаемых данных
	 * @param  string $key         Ключ
	 * @param  int    $input_type  Тип входящих данных (default: INPUT_GET)
	 * @param  int    $filter_type Тип фильтра (default: FILTER_SANITIZE_SPECIAL_CHARS)
	 */
	function filterInput(string $key, int $input_type = INPUT_GET, int $filter_type = FILTER_SANITIZE_SPECIAL_CHARS, int $filter_flags = 0)
	{
		return filter_input($input_type, $key, $filter_type, $filter_flags);
	}

	/**
	 * Перевод даты из MySQL формата в любой формат
	 * @param  string $date   Date in MySQL format "YYYY-MM-DD"
	 * @param  string $format Desired format (see date())
	 * @return string         Converted date string from MySQL to desired format
	 */
	function formatMysqlDate($date, $format = 'd.m.y')
	{
		$y = $m = $d = 0;
		list($y, $m, $d) = explode('-', $date);

		return date($format, mktime(0, 0, 0, $m, $d, $y));
	}

	/**
	 * Перевод даты СО ВРЕМЕНЕМ из MySQL формата в любой формат
	 * @param  string $mysqldatetime Date in MySQL format "YYYY-MM-DD HH:II:SS"
	 * @param  string $format        Desired format (see date())
	 * @return string                Converted date <u>with time</u> string from MySQL to desired format
	 */
	function formatMysqlDatetime($mysqldatetime, $format)
	{
		$date = $time = $year = $month = $day = $hour = $minute = $second = 0;

		list($date, $time) = explode(' ', $mysqldatetime);
		list($year, $month, $day) = explode('-', $date);
		list($hour, $minute, $second) = explode(':', $time);

		return date($format, mktime($hour, $minute, $second, $month, $day, $year));
	}

	/**
	 * Replace the first occurrence of the given needle in subject string
	 * @param  mixed  $search  Searched needle
	 * @param  mixed  $replace The replacement string
	 * @param  string $subject Subject string (haystack)
	 * @return string          The result string
	 */
	function replaceFirst($search, $replace, $subject)
	{
		$pos = mb_strpos($subject, $search);

		if ($pos !== false) {
			return $this->substrReplace($subject, $replace, $pos, mb_strlen($search));
		}
		else {
			return $subject;
		}
	}

	/**
	 * Пошаговое округление. Напр. если шаг равен 10, то 85 округляется до 90, 84 до 80.
	 * @param  float|integer $value   Value to round to nearest step
	 * @param  float|integer $roundTo Round step
	 * @return float|integer          Value rounded to nearest step.<br>
	 *                                <em>Example</em>:<br>
	 *                                If $roundTo = 10, $value 15 is rounded to 20, $value 14 is rounded to 10 (steps are: 0, 10, 20, 30, …)<br>
	 *                                If $roundTo = 5, $value 14 is rounded to 15, $value 12 is rounded to 10 (steps are: 0, 5, 10, 15, …)
	 */
	function roundToNearest($value, $roundTo)
	{
		// explicit int conversion
		$value = (int) $value;

		$mod = $value % $roundTo;

		return floor($value + ($mod < ($roundTo / 2) ? -$mod : $roundTo - $mod));
	}

	/**
	 * Укорачивание текста
	 * @param  string  $str        String to be shorten
	 * @param  integer $max_length Max length of the string (chars)
	 * @param  array   $sys        Settings array with options:
	 *                             <ul>
	 *                             		<li> <strong> stop_char </strong> string
	 *                             		- Set the cutting point closest to this char. Default: " " (space)
	 *                             		<li> <strong> more_char </strong> string
	 *                             		- Char placed after truncated string. Default: "…"
	 *                             </ul>
	 * @return string              Shorted string
	 */
	function shorten($str, $max_length = 200, $sys = array())
	{
		if (! isset($sys['stop_char'])) {
			$sys['stop_char'] = ' ';
		}

		if (! isset($sys['more_char'])) {
			$sys['more_char'] = '…';
		}

		$return = $str;

		$str_l = mb_strlen($str);
		$nchar_l = $sys['more_char'] == '…' ? 1 : mb_strlen($sys['more_char']);

		if ($str_l > $max_length)
		{
			$pos = $sys['stop_char'] !== '' ? mb_strpos($str, $sys['stop_char'], $max_length) : false;

			if ($pos !== false && $pos + $nchar_l <= $max_length) {
				$return = mb_substr($str, 0, $pos) . $sys['more_char'];
			}
			else {
				$return = mb_substr($str, 0, $max_length - $nchar_l) . $sys['more_char'];
			}
		}

		return $return;
	}

	/**
	 * Multibyte version of substr_replace()
	 * @param  mixed  $string      The input string
	 * @param  mixed  $replacement The replacement string
	 * @param  mixed  $start       If start is non-negative, the replacing will begin at the start'th offset into string.
	 *                             If start is negative, the replacing will begin at the start'th character from the end of string.
	 * @param  mixed  $length      If given and is positive, it represents the length of the portion of string which is to be replaced.
	 *                             If it is negative, it represents the number of characters from the end of string at which to stop replacing.
	 *                             If it is not given, then it will default to strlen( string ); i.e. end the replacing at the end of string.
	 *                             Of course, if length is zero then this function will have the effect of inserting replacement into string at the given start offset.
	 * @return string              The result string is returned. If string is an array then array is returned.
	 */
	function substrReplace($string, $replacement, $start, $length = NULL)
	{
		if ($length === NULL) {
			return mb_substr($string, 0, $start) . $replacement;
		}
		else {
			return mb_substr($string, 0, $start) . $replacement . mb_substr($string, $start + $length);
		}
	}

	/**
	 * Транслитерация текста
	 * @param  string  $str      String for transliteration
	 * @param  boolean $url_name Prepare string for using in URL. Default: false (url non-friendly chars are kept)
	 * @return string            Transliterated string
	 */
	function translit($str, $url_name = false, $arr_translit = null)
	{
		// удаляем лишние строки
		$str = trim($str);

		// в нижний регистр
		$str = mb_strtolower($str);

		// массив транслитирации
		$arr_translit = $arr_translit ?? [
			'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'yo', 'ж'=>'j', 'з'=>'z',
			'и'=>'i', 'й'=>'i', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r',
			'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'х'=>'x', 'ц'=>'c', 'ч'=>'ch', 'ш'=>'sh', 'щ'=>'sch',
			'э'=>'e', 'ы'=>'y', 'ю'=>'u', 'я'=>'ya', 'ь'=>'', 'ъ'=>''
		];

		// транслитерация
		$str = str_replace( array_keys($arr_translit), array_values($arr_translit), $str);

		// если это для URL
		if ($url_name)
		{
			// оставляем только цифры, латинские буквы и знак "-" (заменяем все на "-")
			$str = preg_replace('/[^a-z0-9-]/u', '-', $str);
			// отрезаем лишние "-"
			$str = trim($str, '-');
			// переводим все "-" в одиночные
			$str = preg_replace('/--+/u', '-', $str);
		}

		return $str;
	}

	/**
	 * Перевод первого знака в верхний реестр
	 * @param  string $str    String
	 * @param  array $params  Settings array with options:
	 *                        <ul>
	 *                        	<li> <strong> rest_to_lower </strong> boolean
	 *                        		- Convert rest of the string to lower case. Default: false (leave as is)
	 *                        </ul>
	 * @return string         String with upper cased first letter
	 */
	function ucFirst($str, $params = array())
	{
		// check empty
		if (empty($str)) {
			return '';
		}

		if (mb_strlen($str) > 1)
		{
			$part_1 = mb_strtoupper(mb_substr($str, 0, 1));
			$part_2 = mb_substr($str, 1);

			if (! empty($params['rest_to_lower'])) {
				$part_2 = mb_strtolower($part_2);
			}

			$return = $part_1 . $part_2;
		}
		else {
			$return = mb_strtoupper($str);
		}

		return $return;
	}

	/**
	 * Formats whole number
	 * @param  float       $number
	 * @param  int|null    $decimals
	 * @param  string|null $dec_point
	 * @param  string|null $thousands_sep
	 * @return string
	 */
	function numberFormat (float $number, string $thousands_sep = null){

		$thousands_sep = $thousands_sep ?? $this->numformat_thousands_sep ?? " ";

		return number_format($number, 0, '', $thousands_sep);
	}

	/**
	 * Formats float number
	 * @param  float       $number
	 * @param  int|null    $decimals
	 * @param  string|null $dec_point
	 * @param  string|null $thousands_sep
	 * @return string
	 */
	function floatFormat (float $number, int $decimals = null, string $dec_point = null, string $thousands_sep = null){

		$decimals = $decimals ?? $this->floatformat_decimals ?? 0;
		$dec_point = $dec_point ?? $this->floatformat_dec_point ?? ".";
		$thousands_sep = $thousands_sep ?? $this->floatformat_thousands_sep ?? ",";

		return number_format($number, $decimals, $dec_point, $thousands_sep);
	}

	/**
	 * @param $str
	 * @return array
	 */
	function parseChapters(string $str, string $delimiter = '!$ ')
	{
		$items = explode($delimiter, $str);

		unset($items[0]);

		$arr = [];

		foreach ($items as $item)
		{
			$exp = explode("\n", $item, 2);
			$exp = array_pad($exp, 2, '');
			$exp = array_map('trim', $exp);

			$arr[ $exp[0] ] = $exp[1];
		}

		return $arr;
	}

	/**
	 * Filter keyword string - allow only letters, spaces and dashes (can also allow numbers). First letter is transformed to upper case.
	 * @param  string $str keyword name
	 * @param  array  $opt options: <ul>
	 *                     	<li> allow_numbers (bool: false) - allow numbers
	 * @return [type]      [description]
	 */
	function filterKeyword(string $str, array $opt = []){
		
		// allow numeric characters
		$allow_numbers = !empty($opt['allow_numbers']);

		// build regex
		$regex = '/[^\- \p{L}'.($allow_numbers ? '0-9' : '').']/u';

		// replace invalid characters with spaces
		$str = preg_replace($regex, ' ', $str);
		
		// remove extra spaces
		$str = preg_replace('/  +/', ' ', $str);

		// trim
		$str = trim($str);

		// transform to upper case
		$str = $this->ucFirst($str);
		
		return $str;
	}

	/**
	 * @param string $str
	 * @return string|null
	 */
	function filterKey(string $str): string|null
	{
		return preg_replace('/[^a-z0-9-_]/iu', '', $str);
	}

    /**
     * @param string $from
     * @return int|null
     */
    function convertToBytes(string $from): ?int
    {
        $units = [
            'B'  => 0,
            'KB' => 1, 'K' => 1,
            'MB' => 2, 'M' => 2,
            'GB' => 3, 'G' => 3,
            'TB' => 4, 'T' => 4,
            'PB' => 5, 'P' => 5,
        ];

        if (is_numeric( substr($from, -1))) {
            return $from;
        }

        if (is_numeric( substr($from, -2, 1))) {
            $suffix = strtoupper( substr($from, -1));
            $number = substr($from, 0, -1);
        } else {
            $suffix = strtoupper( substr($from,-2));
            $number = substr($from, 0, -2);
        }

        $exponent = $units[ $suffix ] ?? null;

        if ($exponent === null) {
            return null;
        }

        return $number * (1024 ** $exponent);
    }

	/**
	 * @param string|null $string
	 * @return bool
	 */
	function isJsonArray(string|null $string): bool
	{
		if (! $string) {
			return false;
		}

		$res = json_decode($string, true);
		return json_last_error() === JSON_ERROR_NONE && is_array($res);
	}

	/**
	 * @param string $param
	 * @return array
	 */
	function parseHttpQuery(string $param): array
	{
		$arrQuery = [];

		if (substr_count($param, '?')) {
			parse_str(
				parse_url($param, PHP_URL_QUERY),
				$arrQuery
			);
		} else {
			parse_str($param, $arrQuery);
		}

		return $arrQuery;
	}

	/**
	 * @param array $params
	 * @return string
	 */
	function buildHttpQuery(array $params): string
	{
		return http_build_query($params, '', '&');
	}

	/**
	 * @param mixed $var
	 * @return string
	 */
	function md5(mixed $var): string
	{
		return md5( json_encode($var));
	}
}

