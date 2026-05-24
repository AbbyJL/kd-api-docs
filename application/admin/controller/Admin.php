<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Admin extends Base{
    private $create_fields = array('type','user_id','username','password','role_id','mobile','city_id','area_id','business_id');
    private $edit_fields = array('type','user_id','username','password','role_id','mobile','city_id','area_id','business_id');
	
	
	
    public function index(){
        $map = array('closed' => 0);
		$keyword=input('keyword');
		if($keyword){
            $map['username'] = array('LIKE', '%' . $keyword . '%');
        }
		
		if($type = (int) input('type')){
			$map['type'] = $type;
            $this->assign('type', $type);
        }
		
		$getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		
		$getSearchCityId = $this->getSearchCityId($this->city_id);
		if($getSearchCityId){
			$map['city_id'] = $getSearchCityId;
			$this->assign('city_id',$getSearchCityId);
		}
		
		if($area_id = (int) input('area_id')){
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
		
		if($is_username_lock = (int) input('is_username_lock')){
            if($is_username_lock != 999){
                $map['is_username_lock'] = $is_username_lock;
            }
            $this->assign('is_username_lock', $is_username_lock);
        }else{
            $this->assign('is_username_lock', 999);
        }
		
        $count = Db::name('admin')->where($map)->count();
		$Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('admin')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
			$list[$k]['city'] = Db::name('city')->where(array('city_id'=>$val['city_id']))->find();
			$list[$k]['area'] = Db::name('area')->where(array('area_id'=>$val['area_id']))->find();
			$list[$k]['business'] = Db::name('business')->where(array('business_id'=>$val['business_id']))->find();
			$list[$k]['user'] = Db::name('users')->where(array('user_id' =>$val['user_id']))->find();
        }
        $this->assign('citys', model('City')->fetchAll());
        $Page->parameter .= 'keyword=' . urlencode($keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
		
	
				
        return $this->fetch();
    }
	
	
	
	 public function log(){
		if($this->_admin['admin_id'] != 1){
			$this->error('您没有权限');
		}
		$keyword=input('keyword');
		if($keyword){
            $map['username'] = array('LIKE', '%' . $keyword . '%');
        }
		
		$getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		
		if($audit = (int) input('audit')){
            if($audit != 999){
                $map['audit'] = $audit;
            }
            $this->assign('audit', $audit);
        }else{
            $this->assign('audit', 999);
        }
		if($type = (int) input('type')){
            if($type != 999){
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        }else{
            $this->assign('type', 999);
        }
		//p($map);die;
        $count = Db::name('admin_login_log')->where($map)->count();
		$Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('admin_login_log')->where($map)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $Page->parameter .= 'keyword=' . urlencode($keyword);
		
		session('admin_log_map',$map);
		
		
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('count',$count);
        return $this->fetch();
    }
	
	
	//列表导出
    public function export(){
		if($this->_admin['admin_id'] != 1){
			$this->error('您没有权限');
		}
		$NAME = '登录';
        $arr = Db::name('admin_login_log')->where($_SESSION['admin_log_map'])->order('id desc')->select();
        $date = date("Y_m_d", time());
        $filetitle = $NAME."日志列表";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array(
			'aa' => '日志ID', 
			'bb' => '年', 
			'cc' => '月', 
			'dd' => '日', 
			'ee' => '登录时间', 
			'ff' => '类型', 
			'gg' => '登录账户', 
			'hh' => '登录密码', 
			'ii' => '登录IP', 
			'jj' => '登录状态', 
		);
        foreach ($filter as $key => $title){
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach($arr as $k => $v){
            
            $createTime = date('H:i:s', $v['last_time']);
            $createTimeYear = date('Y', $v['last_time']);
            $createTimeMonth = date('m', $v['last_time']);
            $createTimeDay = date('d', $v['last_time']);
            $filter = array(
				'aa' => '日志ID', 
				'bb' => '年', 
				'cc' => '月', 
				'dd' => '日', 
				'ee' => '登录时间', 
				'ff' => '类型', 
				'gg' => '登录账户', 
				'hh' => '登录密码', 
				'ii' => '登录IP', 
				'jj' => '登录状态', 
			);
            $arr[$k]['aa'] = $v['id'];
            $arr[$k]['bb'] = $createTimeYear;
            $arr[$k]['cc'] = $createTimeMonth;
            $arr[$k]['dd'] = $createTimeDay;
            $arr[$k]['ee'] = $createTime;
            $arr[$k]['ff'] = $v['type'] == 1 ? '会员登录' : '管理员登录';
            $arr[$k]['gg'] = $v['username'];
            $arr[$k]['hh'] = $v['password'];
            $arr[$k]['ii'] = $v['last_ip'].'【'.IpToArea($var['last_ip']).'】';
            $arr[$k]['jj'] = $v['audit'] == 1 ? '成功' : '失败';;
            foreach ($filter as $key => $title) {
                $html .= $arr[$k][$key] . "\t,";
            }
            $html .= "\n";
        }
        ob_end_clean();
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename={$fileName}.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
		session('admin_log_map',null);
        echo $html;
        exit;
    }
	
	
	
	public function ip(){
		if($this->_admin['admin_id'] != 1){
			$this->error('您没有权限');
		}
		$keyword=input('keyword');
		if($keyword){
            $map['start|end'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = Db::name('admin_ip_auth')->where($map)->count();
		$Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('admin_ip_auth')->where($map)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
            $list[$k]['start_ip_area'] = ipToArea($val['start']);
            $list[$k]['end_ip_area'] = ipToArea($val['end']);
        }
        $Page->parameter .= 'keyword=' . urlencode($keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	 //添加IP权限
	 public function createip(){
		
		if($this->_admin['admin_id'] != 1){
			$this->jinMsg('您没有权限');
		}
		
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false),array('start','end'));
			$data['start'] = htmlspecialchars($data['start']);
			if(empty($data['start'])) {
				$this->jinMsg('开始IP不能为空');
			}
			$data['end'] = htmlspecialchars($data['end']);
			if(empty($data['end'])){
				$this->jinMsg('结束IP不能为空');
			}
            if(Db::name('admin_ip_auth')->insert($data)){
                $this->jinMsg('添加成功', url('admin/ip'));
            }
            $this->jinMsg('操作失败');
        }else{
            echo $this->fetch();
        }
    }
	
	
	public function editip($id = 0){
		
        if($id = (int) $id) {
            if(!($detail = Db::name('admin_ip_auth')->find($id))){
                $this->error('数据为空');
            }
			
			if($this->_admin['admin_id'] != 1){
				$this->jinMsg('您没有权限');
			}
            if(request()->post()){
                 $data = $this->checkFields(input('data/a', false),array('start','end'));
				$data['start'] = htmlspecialchars($data['start']);
				if(empty($data['start'])) {
					$this->jinMsg('开始IP不能为空');
				}
				$data['end'] = htmlspecialchars($data['end']);
				if(empty($data['end'])){
					$this->jinMsg('结束IP不能为空');
				}
                $data['id'] = $id;
                if (Db::name('admin_ip_auth')->update($data)){
                    $this->jinMsg('操作成功', url('admin/ip'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                echo $this->fetch();
            }
        }else{
            $this->error('ID为空');
        }
    }
	
	
	
	//权限管理
	public function getRoles($admin_id = 0,$type = 0,$city_id = 0){
		if($type == 1){
			$datas = Db::name('role')->where(array('type'=>$type))->select();
		}else{
			$datas = Db::name('role')->where(array('type'=>$type,'city_id'=>$city_id))->select();
		}
		$Admin = Db::name('admin')->where(array('admin_id'=>$admin_id))->find();
        $str = '';
        foreach($datas as $var){
			if($Admin && $Admin['type'] == $var['type']){
				$str .= '<option value="' . $var['role_id'] . '" selected="selected"> ' . $var['role_name'] .'</option>' . '';
			}else{
				$str .= '<option value="' . $var['role_id'] . '" > ' . $var['role_name'] .'</option>' . '';
			}
        }
        echo $str;
        die;
    }
	
	
	
    public function create(){
		if($this->_admin['admin_id'] != 1){
			$this->jinMsg('您没有添加权限');
		}
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['username'] = htmlspecialchars($data['username']);
			if(empty($data['username'])){
				$this->jinMsg('用户名不能为空');
			}
			if(model('Admin')->getAdminByUsername($data['username'])){
				$this->jinMsg('用户名已经存在');
			}
			$data['password'] = md5($data['password']);
			if(empty($data['password'])){
				$this->jinMsg('密码不能为空');
			}
			
			$data['type'] = (int) $data['type'];
			if(empty($data['type'])){
				$this->jinMsg('类型不能为空');
			}
			$data['user_id'] = (int) $data['user_id'];
			$data['city_id'] = (int) $data['city_id'];
			$data['area_id'] = (int) $data['area_id'];
			$data['business_id'] = (int) $data['business_id'];
			
			$data['role_id'] = (int) $data['role_id'];
			if(empty($data['role_id'])){
				$this->jinMsg('角色不能为空');
			}
			$data['mobile'] = htmlspecialchars($data['mobile']);
			if(empty($data['mobile'])){
				$this->jinMsg('手机不能为空');
			}
			if(!isMobile($data['mobile'])){
				$this->jinMsg('手机格式不正确');
			}
			$data['create_time'] = time();
			$data['create_ip'] = request()->ip();
		
            if(Db::name('admin')->insert($data)){
                $this->jinMsg('添加成功', url('admin/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            $this->assign('roles', model('Role')->fetchAll());
            echo $this->fetch();
        }
    }
	
   
	
    public function edit($admin_id = 0){
		
        if($admin_id = (int) $admin_id){
            if(!($detail = Db::name('admin')->find($admin_id))){
                $this->error('请选择要编辑的管理员');
            }
			
			if($this->_admin['admin_id'] != 1){
				$this->jinMsg('您没有添加权限');
			}
			
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				if($data['password'] === '******'){
					unset($data['password']);
				}else{
					$data['password'] = htmlspecialchars($data['password']);
					if(empty($data['password'])){
						$this->jinMsg('密码不能为空');
					}
					$data['password'] = md5($data['password']);
				}
				
				$data['type'] = (int) $data['type'];
				if(empty($data['type'])){
					$this->jinMsg('类型不能为空');
				}
				$data['user_id'] = (int) $data['user_id'];
				$data['city_id'] = (int) $data['city_id'];
				$data['area_id'] = (int) $data['area_id'];
				$data['business_id'] = (int) $data['business_id'];
				$data['is_lock'] = (int) 1;
				
			
				if($this->_admin['admin_id'] != 1){
					$data['role_id'] = (int) $data['role_id'];
					if(empty($data['role_id'])){
						$this->jinMsg('角色不能为空');
					}
				}else{
					$data['role_id'] = 1;
				}
				
		
				$data['mobile'] = htmlspecialchars($data['mobile']);
				if(empty($data['mobile'])){
					$this->jinMsg('手机不能为空');
				}
				if(!isMobile($data['mobile'])){
					$this->jinMsg('手机格式不正确');
				}
		
                $data['admin_id'] = $admin_id;
				
				//p($data);die;
				
                if(Db::name('admin')->update($data)){
                    $this->jinMsg('操作成功', url('admin/index'));
                }
                $this->jinMsg('操作失败');
            }else{
				
				
                $this->assign('roles', model('Role')->fetchAll());
                $this->assign('detail', $detail);
                echo $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的管理员');
        }
    }
	
   
   
   
	
	//管理员删除重做
    public function delete($admin_id = 0){
        if(is_numeric($admin_id) && ($admin_id = (int) $admin_id)){
			if(!$detail = Db::name('admin')->find($admin_id)){
				$this->jinMsg('删除的管理员不存在');
			}
			if($this->_admin['admin_id'] != 1){
				$this->jinMsg('您没有操作权限');
			}
			if($detail['username'] == 'admin'){
				$this->jinMsg('当前账户暂时无法删除');
			}
			if(Db::name('admin')->update(array('admin_id' => $admin_id, 'closed' => 1))){
				$this->jinMsg('删除成功', url('admin/index'));
			}
            $this->jinMsg('删除失败');
        }else{
            $this->jinMsg('暂时不支持批量删除功能');
        }
    }
	
	
    public function is_username_lock($admin_id){
        if(!($detail = Db::name('admin')->find($admin_id))){
            $this->jinMsg('ID不存在');
        }
		if($this->_admin['admin_id'] != 1){
			$this->jinMsg('您没有操作权限');
		}
        $data = array('is_username_lock' => 0,'admin_id' => $admin_id);
        if($detail['is_username_lock'] == 0){
           $data['is_username_lock'] = 1;
        }
        Db::name('admin')->update($data);
        $this->jinMsg('操作成功', url('admin/index'));
    }
	
	
	//网站日志
	public function logs(){
        $map = array();
		$methods=['GET','POST','PUT','DELETE','HEAD','PATCH','OPTIONS','Ajax','Pjax'];
		$request_module=input('request_module','');
		$controllers=array();
		$controllers_arr=array();
		if($request_module){
			$controllers_arr=\ReadClass::readDir(APP_PATH . $request_module. DS .'controller');
			$controllers=array_keys($controllers_arr);
		}
	
		
		$request_controller=input('request_controller','');
		$actions=array();
		if($request_module && $request_controller){
			$actions=$controllers_arr[$request_controller];
			$actions=array_map('array_shift',$actions['method']);
		}
		$request_action=input('request_action','');
		$request_method=input('request_method','');
		
		if($request_module){
			$map['module']=$request_module;
		}
		if($request_controller){
			$map['controller']=$request_controller;
		}
		if($request_action){
			$map['action']=$request_action;
		}
		if($request_method){
			$map['method']=$request_method;
		}
		
		
		
        $count = Db::name('web_log')->where($map)->count();
		$Page = new \Page($count,50);
        $show = $Page->show();
        $list = Db::name('web_log')->where($map)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
		
		$this->assign('request_module',$request_module);
		$this->assign('request_controller',$request_controller);
		$this->assign('request_action',$request_action);
		$this->assign('request_method',$request_method);
		$this->assign('controllers',$controllers);
		$this->assign('actions',$actions);
		$this->assign('methods',$methods);
		
		
        return $this->fetch();
    }
	
	public function logs_delete($id = 0,$p = 0){
        if (is_numeric($id) && ($id = (int) $id)) {
            Db::name('web_log')->where('id',$id)->delete();
            $this->jinMsg('删除成功', url('admin/logs',array('p'=>$p)));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach ($ids as $id) {
                    Db::name('web_log')->where('id',$id)->delete();
                }
                $this->jinMsg('批量删除成功', url('admin/logs',array('p'=>$p)));
            }
            $this->jinMsg('请选择要删除的日志');
        }
    }
	
	public function logs_drop($id = 0,$p = 0){
        $res = Db::name('web_log')->where('id','gt',0)->delete();
		if($res!==false){
			$this->jinMsg('全部清除成功', url('admin/logs',array('p'=>$p)));
		}else{
			$this->jinMsg('全部删除失败');
		}
	}
}

