<?php

namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;
use EasyWeChat\Foundation\Application;

use app\common\model\Setting;




class WeixinConfig extends Base{
	protected $resultSetType = 'collection';
	
	
	public function weixinconfig(){
		 
		 $setting = Setting::config();
		 $options=[
		 	//基本配置
			'debug'  => true,
			'app_id' => $setting['weixin']['appid'],
			'secret' => $setting['weixin']['appsecret'],
			'token'  => $setting['weixin']['token'],
			'aes_key' => '',
			'we_name'=>'',
			'we_id'=>'',
			'we_number'=>'',
			'we_type'=>1,
			 //日志配置
			'log' => [
				'level'      => 'debug',
				'permission' => 0777,
				'file'       => './data/runtime/easywechat.log',
			],
			'oauth' => [
				'scopes'   => ['snsapi_userinfo'],
				'callback' => url('wap/passport/wxback'),//不知道怎么样设置
			],
			//微信支付
			'payment' => [
				'merchant_id'        => 'your-mch-id',
				'key'                => 'key-for-signature',
				'cert_path'          => 'path/to/your/cert.pem',
				'key_path'           => 'path/to/your/key',   
			],
			'guzzle' => [
				'timeout' => 300.0, // 超时时间（秒）
			],
		];
		return $options;
   }
   
   
   //获取分享图片
	public function getFile($uid){
		$options = $this->weixinconfig();
		$app = new Application($options);
		$qrcode = $app->qrcode;
		$result = $qrcode->forever($uid);
		$ticket = $result->ticket; 
		$url = $qrcode->url($ticket);
		$content = file_get_contents($url); 
		$file = '/data/weixin/fuid_'.$uid.'_code.jpg';
		$fileName  = ROOT_PATH.''.$file;
		file_put_contents($fileName, $content); 
		return $file;
	}
	
	
}