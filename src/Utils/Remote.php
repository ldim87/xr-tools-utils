<?php
/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools\Utils;

/**
 * Remotes utilities
 */
class Remote {


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
	function detectCrawler($user_agent = null) {
		// User lowercase string for comparison.
		$user_agent = strtolower($user_agent ?? $_SERVER['HTTP_USER_AGENT']);

		if(!$user_agent){
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
		foreach ($bot_identifiers as $identifier) {
			if (strpos($user_agent, $identifier) !== false) {
				return true;
			}
		}
		return false;
	}
	
	// :TODO: WIP
	
}











