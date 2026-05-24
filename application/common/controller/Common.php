<?php
namespace app\common\controller;

use think\Controller;
use think\Lang;
use think\captcha\Captcha;

class Common extends Controller{
	
	
    // Request实例
	protected $lang;
	protected function _initialize(){
		parent::_initialize();
		
		
		
		
        if (!defined('__ROOT__')) {
            $_root = rtrim(dirname(rtrim($_SERVER['SCRIPT_NAME'], '/')), '/');
            define('__ROOT__', (('/' == $_root || '\\' == $_root) ? '' : $_root));
        }
		if (!file_exists(ROOT_PATH.'data/install.lock')) {
            //不存在，则进入安装
            header('Location: ' . url('install/Index/index'));
            exit();
        }
		

		define('__JINTAO_MODULE__', request()->module());
		define('__JINTAO_CONTROLLER__', request()->controller());
        define('__JINTAO_ACTION__', request()->action());
		
		
		
		define('TODAY', date("Y-m-d")); //不要遗漏
		define('time()', time()); //不要遗漏
	
		// 多语言
		if(config('lang_switch_on')){
			$this->lang=Lang::detect();
		}else{
			$this->lang=config('default_lang');
		}
		$this->assign('lang',$this->lang);
	
		
		
	}
	
	
	
	
    //空操作
    public function _empty(){
        $this->error(lang('operation not valid'));
    }
	protected function verify_build($id){
		ob_end_clean();
		$verify = new Captcha (config('verify'));
		return $verify->entry($id);
	}
	//验证验证码
	protected function verify_check($id){
		$verify =new Captcha ();
		if (!$verify->check(input('verify'), $id)) {
			return false;
		}
		return true;
	}
	
	//获取验证码，1传入验证码，2传入ID
	protected function get_verify_check($verify,$id){
		$Captcha = new Captcha();
		if(!$Captcha->check($verify,$id)) {
			return false;
		}
		return true;
	}
   
}