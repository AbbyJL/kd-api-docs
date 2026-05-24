<?php

define("ROOT", dirname(dirname(__FILE__)));
require_once(ROOT . "/sdk/builder/impl/DefaultAppBuilder.php");

class KyeDefaultOpenApi
{

	public static function builder($appkey, $appsecret)
	{
		$apiBuilder = new DefaultAppBuilder($appkey, $appsecret);
		return $apiBuilder;
	}

}
