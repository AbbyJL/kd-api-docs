<?php
namespace app\wap\controller;
use app\common\controller\Common;
use think\Db;


use think\Loader;
use EasyWeChat\Foundation\Application;

use app\common\model\Setting;



class Base extends Common{
	protected $uid = 0;
    protected $member = array();
    protected $city_id = 0;
    protected $_CONFIG = array();
    protected function _initialize(){
		
		define('__JINTAO_MODULE__', request()->module());
		define('__JINTAO_CONTROLLER__', request()->controller());
        define('__JINTAO_ACTION__', request()->action());
		define('TODAY', date("Y-m-d")); 
		define('time()', time()); 
		define('IS_MOBILE', is_mobile());
		define('IS_MINIPROGRAM', is_miniprogram());
		
		
		$this->_CONFIG = Setting::config();
		$config = $this->_CONFIG;
		
		$this->assign('CONFIG', $this->_CONFIG);
		
		
		config('app_debug',false);
        config('app_trace',false);
		
	
		
	
		
		
		define('IN_MOBILE', true);//设置手机
       
		$this->assign('is_weixin', is_weixin());
		$this->assign('isWx2', isWx2());//是否支持变色龙
	
		$this->assign('ctl', strtolower(__JINTAO_CONTROLLER__));
		$this->assign('act',strtolower(__JINTAO_ACTION__));

		
		$lang = cookie('lang');
		$this->assign('lang',$lang ? $lang : 'zh');//小程序
		
		
		$uid = input('uid','','trim,htmlspecialchars');
		$this->assign('uid',$uid);//小程序
		$this->uid = $uid;//获取用户UID
		
		
		$this->member = Db::name('users')->where('user_id',$this->uid)->find();
		$this->uid = $this->member ? $this->uid : false;
		
        if(!empty($this->uid) && !empty($this->member)){
			$this->assign('connect',$connect = Db::name('connect')->where(array('uid'=>$this->uid,'type'=>'weixin'))->find());//客户端缓存会员数据
            $this->member = Db::name('users')->where(array('user_id'=>$this->uid))->find($this->uid);//客户端缓存会员数据
            cookie('member', $this->member, 86000);//cookie保存时间，建议后台设置，暂时这样修改
        }
		$this->assign('MEMBER', $this->member);
		
	
		
		
        $this->assign('cartnum', (int) @array_sum(session('goods')));
		$this->assign('color', $this->_CONFIG['other']['color']);
		$this->assign('today', TODAY);
		$this->assign('sitehost',$this->_CONFIG['site']['host']);
		
		
		
		//必须填写后台appid
		$signPackage['timestamp'] = '';
		$signPackage['appId'] = '';
		$signPackage['nonceStr'] = '';
		$signPackage['signature'] = '';
		if($this->_CONFIG['weixin']['appid']){
			$this->options = model('WeixinConfig')->weixinconfig();//获取微信配置
			$this->app = new Application($this->options);//前端调用
			$js = $this->app->js;
			$this->assign('js',$js);//JSddk分享	
			
			$jsconfig = $js->config(array(
				'checkJsApi',
				'invokeMiniProgramAPI',
				'getLocation',
				'openLocation',
				'onMenuShareTimeline',
				'onMenuShareAppMessage',
				'onMenuShareQQ',
				'onMenuShareWeibo',
				'onMenuShareQZone'
			), false);
        	$jsconfig = json_decode($jsconfig,true);
			$signPackage['timestamp'] = $jsconfig['timestamp'];
			$signPackage['appId'] = $this->_CONFIG['weixin']['appid'];
			$signPackage['nonceStr'] = $jsconfig['nonceStr'];
			$signPackage['signature'] = $jsconfig['signature'];
		}
		$this->assign('signPackage',$signPackage);
	
		
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set("error_reporting","E_ALL & ~E_NOTICE");
		
		
		define('__HOST__', getServerHttpHost($this->_CONFIG['site']['https']));//传值
		$this->assign('version',$version= '1.38');//版本
		
		
		
		
    }
	
	
	
	
	//重组数组
    protected function checkFields($data = array(), $fields = array()){
        foreach($data as $k => $val){
            if(!in_array($k, $fields)){
                unset($data[$k]);
            }
        }
        return $data;
    }

 	
}