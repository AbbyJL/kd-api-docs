<?php

define("ROOT", dirname(dirname(__FILE__)));

require_once(ROOT . "/sdk/builder/AppBuilder.php");
require_once(ROOT . "/sdk/builder/impl/DefaultApiBuilder.php");

class DefaultAppBuilder implements AppBuilder
{
	public $_appKey;
	public $_appSecret;
	public $_isSandbox = false;
	public $_env = "prod";
	public $_doMain;
	public static $_urlMap = array("prod" => "https://open.ky-express.com/", "uat" => "https://open-uat.kyeapi.com/");

	public function __construct($appkey, $appsecret)
	{
	    $this->_appKey = $appkey;
	    $this->_appSecret = $appsecret;
	    $this->_doMain = self::$_urlMap[$this->_env];
	}

	public function sandBox()
	{
		$this->_isSandbox = true;
		return $this;
	}

	public function uat()
	{
		$this->_env = 'uat';
		return $this;
	}

	public function prod()
	{
		$this->_env = 'prod';
		return $this;
	}

	public function api($api)
	{
		$apiBuilder = new DefaultApiBuilder($this->_appKey, $this->_appSecret, $this->_doMain, $this->_isSandbox);
		$apiBuilder->api($api);
		return $apiBuilder;
	}
}

