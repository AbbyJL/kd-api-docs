<?php
namespace app\retail\controller;
use app\common\controller\Common;
use think\Db;

use EasyWeChat\Foundation\Application;

use app\common\model\Setting;
class Base extends Common{
	
    protected $_CONFIG = array();
	protected $uid = 0;
    protected $member = array();
	protected $roleName = '';
	protected $role = 0;
	protected $rank = array();


	
    protected function _initialize(){
		
		define('__JINTAO_MODULE__', request()->module());
		define('__JINTAO_CONTROLLER__', request()->controller());
        define('__JINTAO_ACTION__', request()->action());
		define('TODAY', date("Y-m-d")); 
		define('time()', time()); 
		
		$this->_CONFIG = Setting::config();
		$this->assign('CONFIG', $this->_CONFIG);
		
		
	
		$this->assign('is_weixin', is_weixin());
		$is_weixin = is_weixin();
		
		$this->assign('ctl', strtolower(__JINTAO_CONTROLLER__));
		$this->assign('act',strtolower(__JINTAO_ACTION__));

        
		
		$uid = (int) input('uid');
		$userToken = input('userToken','','');
		$user_id = Db::name('users')->where(array('token'=>$userToken))->value('user_id');
		if($uid == $user_id && $userToken && $uid){
			setUid($uid,time());
			$this->uid = $uid;
		}else{
			$this->uid = getUid();//获取用户UID
		}
		
		
        if(!empty($this->uid)){
            $this->member = Db::name('users')->find($this->uid);//客户端缓存会员数据
			$this->assign('MEMBER', $this->member);
        }
		
	
        if(__JINTAO_CONTROLLER__ != 'Passport'){
            if(empty($this->uid)){
                header("Location: " . url('passport/login'));
                die;
            }
			$this->rank = Db::name('user_rank')->where(array('rank_id'=>$this->member['rank_id']))->find();
			if($this->rank['rank_id'] <1){
				header("Location: " . url('passport/login'));
				die;
			}
			$this->roleName = $this->rank['rank_name'];
			$this->role = $this->rank['rank_id'];
			$this->assign('rank',$this->rank);  
		}
		//p($this->role);die;
        $this->assign('roleName',$this->roleName);  
		$this->assign('role',$this->role);  
		
		//必须填写后台appid
		if($this->_CONFIG['weixin']['appid']){
			$this->options = model('WeixinConfig')->weixinconfig();//获取微信配置
			$this->app = new Application($this->options);//前端调用
			$js = $this->app->js;
			$this->assign('js',$js);//JSddk分享	
		}
		
		
		config('app_debug',false);
        config('app_trace',false);
		
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set("error_reporting","E_ALL & ~E_NOTICE");
		
		
		define('__HOST__', getServerHttpHost($this->_CONFIG['site']['https']));//获取域名
    }
	
	
	
	//获取cookie定位
	protected function getCookieLatLng($t = 0){
		
		$lat = '';
		$lng = '';
		
		//首先判断腾讯定位
		$qq_lat = cookie('qq_lat');
		$qq_lng = cookie('qq_lng');
		
		$lat = $qq_lat;
		$lng = $qq_lng;
		
		if(empty($lat) || empty($lng)){
			$lat = addslashes(cookie('lat'));//H5定位
        	$lng = addslashes(cookie('lng'));
			if(empty($lat) || empty($lng)){
				 $lat = $this->city['lat'];//系统位置
				 $lng = $this->city['lng'];
			}
        }
		
		return array('lat'=>$lat,'lng'=>$lng);
	}
	
	
    protected function checkFields($data = array(), $fields = array()){
        foreach($data as $k => $val){
            if(!in_array($k, $fields)){
                unset($data[$k]);
            }
        }
        return $data;
    }
	
  
}