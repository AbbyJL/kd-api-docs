<?php
namespace app\app\controller;
use app\common\controller\Common;
use think\Db;

use app\common\model\Setting;



class Base extends Common{

    protected $_CONFIG = array();
    protected function _initialize() {
		
		define('__JINTAO_MODULE__', request()->module());
		define('__JINTAO_CONTROLLER__', request()->controller());
        define('__JINTAO_ACTION__', request()->action());
		
		
		define('TODAY', date("Y-m-d")); 
		define('time()', time()); 
		define('NOW_TIME', time()); 
        $this->_CONFIG = Setting::config();
		config('app_debug',false);
        config('app_trace',false);
		
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set("error_reporting","E_ALL & ~E_NOTICE");
		
		define('__HOST__', 'https://' . $_SERVER['HTTP_HOST']);
		
		
		
		
    }
	
	
    public function checkLogin($rd_session = ''){
		
        if(!empty($rd_session)){
            $user = Db::name('connect')->where('rd_session',$rd_session)->find();
            if(empty($user))
                exit(json_encode(array('status'=>-2,'msg'=>'token无效，请重新登录获取','data'=>'')));
        }else{
            exit(json_encode(array('status'=>-1,'msg'=>'token不能为空','data'=>'')));
        }
        return $user;
    }
	
    //获取OPENID
    public function getOpenId($rd_session){
        $user = $this->checkLogin($rd_session);
        return $user['open_id'];
    }
   

  
}