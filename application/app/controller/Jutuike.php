<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Jutuike extends Base{



	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->curl = new \Curl();
		$this->host = $this->config['site']['host'];
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set("error_reporting","E_ALL & ~E_NOTICE");
    }
	
	

	
	public function takeOut(){
		
		$data['jutuike_apikey'] = trim($this->config['jutuike']['jutuike_apikey']);
		$data['host'] = trim($this->config['site']['host']);
		$data['mobile'] = trim($this->config['site']['mobile']);
		$url = getHost().'/app/RequestApi/takeOut';
		$result = $this->curl->post($url,json_encode($data));
		$result = json_decode($result,true);
		$list = $result['data'];
	
		return json(array('code'=>'1','msg' =>'获取成功','list'=>$list));
	}

	
	
	
}
