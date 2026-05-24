<?php

namespace app\agent\controller;
use think\Db;

use app\common\model\Setting;


class Team extends Base{
	
	
	
	public function _initialize(){
        parent::_initialize();
    }
	
	public function daili(){
        return $this->fetch();
	}
	public function dailiload(){
		$map = array('city_id' =>$this->admin['city_id']);
		$map['user_id'] = array('neq','');
        $count = Db::name('area')->where($map)->count();
        $Page = new \Page($count, 8);
        $show = $Page->show();
        $p = input('p');
		if($Page->totalPages < $p){
			die('0');
		}
        $list = Db::name('area')->where($map)->order(array('area_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k =>$v){
			$list[$k]['users'] = Db::name('users')->find($v['user_id']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('level', $level);
        echo $this->fetch();
	}
	
	
	public function dailiedit(){
		$area_id = (int) input('area_id');
		$this->assign('area_id', $area_id);
		$detail = Db::name('area')->where(array('area_id'=>$area_id))->find();
		$this->assign('detail', $detail);
		
		if(request()->post()){
            $data = $this->checkFields(input('data/a', false), array('area_id','user_id','ratio_vip','ratio'));
			if($area_id==0){
				$data['area_id'] = $data['area_id'];
				if(!$data['area_id']){
					return json(array('code'=>'0','msg'=>'区县必须选择'));
				}
				$data['user_id'] = $data['user_id'];
				if(!$data['user_id']){
					return json(array('code'=>'0','msg'=>'UID不能为空'));
				}
				$users = Db::name('users')->where(array('user_id'=>$data['user_id']))->find();
				if(!$users){
					return json(array('code'=>'0','msg'=>'UID输入错误'));
				}
				$info = '添加成功';
			}else{
				$data['area_id'] = $area_id;
				$info = '更新成功';
			}
			$data['user_id'] = $data['user_id'];
			$data['ratio'] = (int) ($data['ratio']*100);
			if(!$data['ratio']){
				return json(array('code'=>'0','msg'=>'费率不能为空'));
			}
			if($data['ratio'] >= 5000){
				return json(array('code'=>'0','msg'=>'费率太高'));
			}
			$data['ratio_vip'] = (int) ($data['ratio_vip']*100);
			
            if(false !== Db::name('area')->update($data)){
				return json(array('code'=>'1','msg'=>$info,'url'=>url('team/daili')));
            }
			return json(array('code'=>'0','msg'=>'操作失败'));
        }else{
			$this->assign('areas',$areas = Db::name('area')->where(array('city_id'=>$this->admin['city_id']))->where('user_id','null')->limit(0,36)->select());
            return $this->fetch();
		}
	}
	
	
	public function useredit(){
		$city_id = (int) input('city_id');
		$this->assign('city_id', $city_id);
		
		$area_id = (int) input('area_id');
		$this->assign('area_id', $area_id);
		
		$user_id = (int) input('user_id');
		$this->assign('user_id',$user_id);
		
		$detail = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$this->assign('detail', $detail);
		
		
		if(request()->post()){
            $data = $this->checkFields(input('data/a', false), array('area_id','nickname','mobile','stock'));
			
			$data['area_id'] = $data['area_id'];
			$updateData['stock'] = (int) ($data['stock']*100);
			if(!$updateData['stock']){
				return json(array('code'=>'0','msg'=>'费率不能为空'));
			}
			if($updateData['stock'] >= 5000){
				return json(array('code'=>'0','msg'=>'费率太高'));
			}
			if($data['area_id']){
				$updateData['province'] = $this->admin['ParentId'];
				$updateData['city'] = $city_id;
				$updateData['area'] = $data['area_id'];
				
				$updateData['nickname'] = $data['nickname'];
				$updateData['mobile'] = $data['mobile'];
				if(!$updateData['mobile']){
					return json(array('code'=>'0','msg'=>'手机号不存在'));
				}
				if(!isPhone($updateData['mobile']) && !isMobile($updateData['mobile'])){
					return json(array('code'=>'0','msg'=>'手机号码格式不正确'));
				}
				$users = Db::name('users')->where(array('mobile'=>$updateData['mobile']))->find();
				if($users){
					return json(array('code'=>'0','msg'=>'手机号重复'));
				}
				if(!$updateData['city']){
					return json(array('code'=>'0','msg'=>'城市不能为空'));
				}
				if(!$updateData['area']){
					return json(array('code'=>'0','msg'=>'区县必须选择'));
				}
				$arr = array(
				   'account' => $updateData['nickname'], 
				   'mobile' => $updateData['mobile'],
				   'password' => $updateData['mobile'],
				   'stock' => $updateData['stock'], 
				   'province' => $updateData['province'], 
				   'city' => $updateData['city'], 
				   'area' => $updateData['area'], 
				   'face' => '/attachs/default.jpg', 
				   'nickname' => $updateData['nickname'], 
				   'reg_time' => time(), 
				   'reg_ip' =>request()->ip()
				);
				$rest = model('Passport')->register($arr,$this->uid,1);
				$info = '添加成功';
			}else{
				$data['area_id'] = $area_id;
				$info = '更新成功';
			}
			
			if($user_id){
				$updateData['nickname'] = $data['nickname'];
				$updateData['user_id'] = $user_id;
				$rest = Db::name('users')->update($updateData);
			}
            if($rest){
				return json(array('code'=>'1','msg'=>$info,'url'=>url('team/index')));
            }
			return json(array('code'=>'0','msg'=>'操作失败'));
        }else{
			$this->assign('areas',$areas = Db::name('area')->where(array('city_id'=>$this->admin['city_id']))->where('user_id','null')->limit(0,36)->select());
            return $this->fetch();
		}
	}
	
	
	
	
    public function index(){
        return $this->fetch();
	}
	
	
    public function load(){
       
		$user_id = (int) input('user_id');
		if(!$user_id){
		    $user_id = $this->uid;
		}
		$map = array('closed' => 0);
		if($this->role == 0){
			$map['parent_id'] = $user_id;
		}
		if($this->role == 1){
			$map['province'] = $this->admin['id'];
		}
		if($this->role == 2){
			$map['city'] = $this->admin['city_id'];
		}
		if($this->role == 3){
			$map['area'] = $this->admin['area_id'];
		}
        $count = Db::name('users')->where($map)->count();
        $Page = new \Page($count, 8);
        $show = $Page->show();
        $p = input('p');
		if($Page->totalPages < $p){
			die('0');
		}
        $list = Db::name('users')->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
			$val['cityName'] = Db::name('copy_city')->where(array('city_id'=>$val['city']))->value('name');
			$val['areaName'] = Db::name('copy_area')->where(array('area_id'=>$val['area']))->value('Name');
			$val['provinceName'] = Db::name('copy_province')->where(array('id'=>$val['province']))->value('name');
			$list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('level', $level);
        echo $this->fetch();
	}
	
	
}