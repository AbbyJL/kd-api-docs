<?php
namespace app\admin\controller;
use app\common\controller\Common;
use think\Db;

use app\common\model\Setting;

class Base extends Common{

    protected $_admin = array();
    protected $_CONFIG = array();
	
	
    protected function _initialize(){
		
		define('__JINTAO_MODULE__', strtolower(request()->module()));
		define('__JINTAO_CONTROLLER__', strtolower(request()->controller()));
        define('__JINTAO_ACTION__', strtolower(request()->action()));
		define('TODAY', date("Y-m-d")); //不要遗漏
		define('time()', time()); //不要遗漏
		
		
		
		config('app_debug',false);
        config('app_trace',false);
        $this->_CONFIG = Setting::config();
        $this->assign('CONFIG', $this->_CONFIG);
		error_reporting(E_ERROR | E_WARNING | E_PARSE);//规避报错
		
		
		
        $this->_admin = session('admin');
		if(!$this->_admin){
			$this->_admin = cookie('admin');
			if($this->_admin){
				$this->_admin = @json_decode($this->_admin,true);
			}
		}
		
	
		$admin = Db::name('admin')->find($this->_admin['admin_id']);
		if(__JINTAO_CONTROLLER__ != 'login' && __JINTAO_CONTROLLER__ != 'public' && $admin['is_lock'] == 1){
			if($this->_admin['password'] != $admin['password']){
				session('admin', null);
				cookie('admin', null);
				header('Location:'.url('login/index'));die;
			}
		}
		
	
	
        if(__JINTAO_CONTROLLER__ != 'login' && __JINTAO_CONTROLLER__ != 'public'){
	
            if(empty($this->_admin)){
                header('Location:'.url('login/index'));die;
            }
            //演示账号不能操作结束
			
			
			
            if($this->_admin['role_id'] != 1){
                $this->_admin['menu_list'] = model('RoleMaps')->getMenuIdsByRoleId($this->_admin['role_id']);
				$this->city_id = $this->_admin['city_id'];//城市全局调用
				
				
                if(__JINTAO_CONTROLLER__ != 'index'){
                    $menu_action = strtolower(__JINTAO_CONTROLLER__ . '/' . __JINTAO_ACTION__);
                    $menus = model('Menu')->fetchAll();
                    $menu_id = 0;
                    foreach($menus as $k => $v){
                        if(strtolower($v['menu_action']) == strtolower($menu_action)){
                            $menu_id = (int) $v['menu_id'];
                            break;
                        }
                    }
                }
            }
            
        }
		
        if($this->_admin['is_username_lock'] == 1){
            session('admin', null);
			cookie('admin', null);
            $this->error('非法操作', url('login/index'));
        }
		
		
		
		$this->assign('ROLE',$ROLE = Db::name('role')->find($this->_admin['role_id']));
		$this->assign('ADMINCITY',$ADMINCITY = Db::name('city')->where(array('city_id'=>$this->_admin['city_id']))->find());
		
        $this->assign('admin', $this->_admin);
        $this->assign('today', TODAY);
        $this->assign('nowtime', time());
		$this->assign('ctl', __JINTAO_MODULE__);
        $this->assign('act', __JINTAO_ACTION__);
		
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set("error_reporting","E_ALL & ~E_NOTICE");
		
    }
   
   
   //分站城市搜索组合封装
	protected function getSearchCityId($_city_id){
		$city_id = (int) input('city_id');
		if($city_id){
			if($_city_id){
				$map['city_id'] = $_city_id;
			}else{
				$map['city_id'] = $city_id;
			}
        }else{
			if($_city_id){
				$map['city_id'] =$_city_id;
			}else{
				$map['city_id'] =0;
			}
		}
	    return $map['city_id'];
	}
	
	
	
	//搜索时间
	protected function getSearchDate(){
		if(($bg_date = input('bg_date','', 'htmlspecialchars')) && ($end_date = input('end_date','', 'htmlspecialchars'))){
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        }else{
            if($bg_date = input('bg_date','', 'htmlspecialchars')){
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map = array('EGT', $bg_time);
            }
            if($end_date = input('end_date','', 'htmlspecialchars')){
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map = array('ELT', $end_time);
            }
        }
		return $map;
	}
	
   
   protected function jinMsg($message, $jumpUrl = '', $time = 3000){
        $str = '<script>';
        $str .= 'parent.boxmsg("' . $message . '","' . $jumpUrl . '","' . $time . '");';
        $str .= '</script>';
        die($str);
    }
	
	protected function jinMsgCode($message, $jumpUrl = '', $time = 3000){
        $str = '<script>';
        $str .= 'parent.boxmsgcode("' . $message . '","' . $jumpUrl . '","' . $time . '");';
        $str .= '</script>';
        die($str);
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