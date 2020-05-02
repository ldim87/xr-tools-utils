<?php

/**
 * @author Oleg Isaev (PandCar)
 * @contacts vk.com/id50416641, t.me/pandcar, github.com/pandcar
 */

namespace XrTools;

/**
 * CSRF Token protection
 */
class TokenCSRF
{
	
	protected $secretKey = 'xmr9nhy76wlnjy789mjylo',
		$varName = '_token',
		$token = '';

	function __construct($secretKey = null, $varName = null)
	{
		if (isset($secretKey)) {
			$this->secretKey = $secretKey;
		}
		
		if (isset($varName)) {
			$this->varName = $varName;
		}
	}

	/**
	 * @return string
	 */
	function getVarName()
	{
		return $this->varName;
	}

	/**
	 * @param null $format
	 * @return string
	 */
	function get($format = null)
	{

		// Генерируем токен если нет в кэшэ
		if (empty($this->token)){
			$this->token = $this->generateToken();
		}
		
		if ($format == 'form'){
			return '<input type="hidden" name="'.$this->varName.'" value="'.$this->token.'">';
		}
		elseif ($format == 'url'){
			return $this->varName.'='.$this->token;
		}
		elseif($format == 'js'){
			return $this->varName.':"'.$this->token.'"';
		}
		
		return $this->token;
	}

	/**
	 * @param null $inputToken
	 * @return bool
	 */
	function check(string $inputToken = null)
	{

		$times = [
			'now', 
			'-1 day'
		];

		$tokens = [];

		if(isset($inputToken)){
			$tokens[] = $inputToken;
		}

		if(isset($_REQUEST[ $this->varName ])){
			$tokens[] = $_REQUEST[ $this->varName ];
		}

		if(!$tokens){
			return false;
		}
		
		// Проверяем токены за 2 дня
		foreach ($times as $time)
		{
			$token = $this->generateToken($time);
			
			if (in_array($token, $tokens)){
				return true;
			}
		}
		
		return false;
	}

	/**
	 * @param string $strTime
	 * @return string
	 */
	protected function generateToken($strTime = 'now')
	{
		$md5_secret = md5($this->secretKey);
		
		$exp1 = substr($md5_secret, 0, 16);
		$exp2 = substr($md5_secret, -16);
		
		return md5( $exp2 . date('dmY', strtotime($strTime)) . $exp1 );
	}
}
