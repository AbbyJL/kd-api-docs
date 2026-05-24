<?php
namespace app\weixin\controller;

use think\Db;
use app\common\controller\Common;
use EasyWeChat\Foundation\Application;

class Base extends Common{
	public function _initialize(){
        parent::_initialize();
		//微信平台
		
		$this->options = model('WeixinConfig')->weixinconfig();//获取微信配置
		$this->app = new Application($this->options);//前端调用
	}
}