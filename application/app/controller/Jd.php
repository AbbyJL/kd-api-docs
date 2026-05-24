<?php
namespace app\app\controller;
use think\Db;
use think\Cache;


use app\common\model\Setting;

class Jd extends Base{
	
	private $jd_url = 'https://oauth.jdl.com';
	private $jd_type = '0';
	
	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->curl = new \Curl();
		$this->host = $this->config['site']['host'];
		$this->jd_type = (int)$this->config['wxapp']['jd_type'];
		$this->jd_url = $this->config['wxapp']['jd_type'] ==0 ? 'https://oauth.jdl.com' : 'https://uat-oauth.jdl.com';
    }

	
    public function jdLogin(){
		$CONFIG = Setting::config();
		$state = md5(uniqid(rand(),TRUE));
        session('state', $state);
		$redirect_uri = urlencode(__HOST__ . url('app/jd/jdcallback'));
		$login_url = $this->jd_url.'/oauth/authorize?client_id='.$this->config['wxapp']['jd_AppKey'].'&redirect_uri='.$redirect_uri.'&response_type=code&state='.$state;
        header("Location:{$login_url}");
        die;
    }
	
    public function jdcallback(){
		$CONFIG = Setting::config();
        $state = session('state');
        if($_REQUEST['state']){
            if(empty($_REQUEST['code'])){
                echo '授权失败';
            }
			$token_url = $this->jd_url.'/oauth/token?code='.$_REQUEST['code'].'&client_secret='.$this->config['wxapp']['jd_AppSecret'].'&client_id='.$this->config['wxapp']['jd_AppKey'];
            $str = $this->curl->get($token_url);
            $params = json_decode($str,true);
			$params = json_decode($params,true);
			
			if($this->jd_url=='https://oauth.jdl.com'){
				$t = 0;
			}
			if($this->jd_url=='https://uat-oauth.jdl.com'){
				$t = 1;
			}
			
			$datas = new \stdClass();
			$datas->accessExpire = $params['accessExpire'];
			$datas->accessToken = $params['accessToken'];
			$datas->clientId = $params['clientId'];
			$datas->code = $params['code'];
			$datas->refreshExpire = $params['refreshExpire'];
			$datas->refreshToken = $params['refreshToken'];
			$datas->sellerId = $params['sellerId'];
			$fp = fopen(ROOT_PATH."/data/get_jd_".$t."_site_token.json", "w");
			fwrite($fp, json_encode($datas));
			fclose($fp);
			echo '获取成功'.$params['accessToken'];
        }
    }
}