<?php

/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

/**
 * Remotes utilities
 */
class Remote
{
	/**
	 * Check if the given user agent string is one of a crawler, spider, or bot. 
	 * (https://gist.github.com/geerlingguy/a438b41a9a8f988ee106) 
	 * Если в будущем нужен будет более сильный детектор ботов, то смотри здесь:
	 * https://github.com/JayBizzle/Crawler-Detect
	 *
	 * @param string $user_agent
	 *   A user agent string (e.g. Googlebot/2.1 (+http://www.google.com/bot.html))
	 *
	 * @return bool
	 *   TRUE if the user agent is a bot, FALSE if not.
	 */
	function detectCrawler($user_agent = null)
	{
		// User lowercase string for comparison.
		$user_agent = strtolower($user_agent ?? $_SERVER['HTTP_USER_AGENT']);

		if (! $user_agent) {
			return false;
		}
		
		// A list of some common words used only for bots and crawlers.
		$bot_identifiers = array(
			'bot',
			'slurp',
			'crawler',
			'spider',
			'curl',
			'facebook',
			'fetch',
		);

		// See if one of the identifiers is in the UA string.
		foreach ($bot_identifiers as $identifier)
		{
			if (strpos($user_agent, $identifier) !== false) {
				return true;
			}
		}

		return false;
	}
	
	// :TODO: WIP

	/**
	 * Get remote contents via cURL
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	function fileGetContents($url)
	{
		// set options
		$options = array(
			CURLOPT_RETURNTRANSFER => true,     // return web page
			CURLOPT_HEADER         => false,    // don't return headers
			CURLOPT_FOLLOWLOCATION => true,     // follow redirects
			CURLOPT_ENCODING       => "",       // handle all encodings
			CURLOPT_USERAGENT      => "1000.menu Embed Spider", // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 10,      // timeout on connect
			CURLOPT_TIMEOUT        => 20,      // timeout on response
			CURLOPT_MAXREDIRS      => 5,       // stop after 10 redirects
			CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS
		);

		$ch = curl_init( $url );
		curl_setopt_array( $ch, $options );

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

	/**
	 * Загрузка заголовков с УРЛ адреса
	 * @param  string $url URL address
	 * @return array       Info array (see curl_getinfo() PHP doc)
	 */
	function checkUrlHeaders($url, array $opt = [])
	{
		// disable local
		if (mb_substr($url, 0, 1) == '/') {
			return array();
		}

		// options
		$user_agent = $opt['user_agent'] ?? '';
		$no_body = $opt['no_body'] ?? true;

		// check headers
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
		
		if($user_agent){
			curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
		}
		
		if($no_body){
			curl_setopt($curl, CURLOPT_NOBODY, true);
		}

		curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);

		return $info;
	}
}
