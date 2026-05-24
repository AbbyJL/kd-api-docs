<?php

namespace app\retail\controller;
use think\Db;

use app\common\model\Setting;


class Team extends Base{
	
	
	
	public function _initialize(){
        parent::_initialize();
    }
	
	public function daili(){
	    
	    $user_id = (int) input('user_id');
		$this->assign('user_id', $user_id);
		if(!$user_id){
		    $user_id = $this->uid;
		}
		
		$detail = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$this->assign('detail', $detail);

		//p($detail);
		$ji = (int) input('ji');
		$this->assign('ji', $ji);
	
		
        return $this->fetch();
	}
	
	
	public function dailiload(){
	    
	    $user_id = (int) input('user_id');
		$this->assign('user_id', $user_id);
		if(!$user_id){
		    $user_id = $this->uid;
		}
		
		$ji = (int) input('ji');
		$this->assign('ji', $ji);
		
	    if($ji==0){
	       $rank_id = 2; 
	    }
	    if($ji==3){
	       $rank_id = 2; 
	    }
	    if($ji==2){
	       $rank_id = 1; 
	    }
	    if($ji==1){
	       $rank_id = 0; 
	    }
	    
	    
		$map = array('parent_id' =>$user_id,'rank_id' =>$rank_id);
		
		
		$map['user_id'] = array('neq','');
        $count = Db::name('users')->where($map)->count();
        $Page = new \Page($count, 8);
        $show = $Page->show();
        $p = input('p');
		if($Page->totalPages < $p){
			die('0');
		}
        $list = Db::name('users')->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k =>$v){
			$rank= Db::name('user_rank')->find($v['rank_id']);
			
			if($rank['rank_id']==3){
    	       $ji2 = 3; 
    	    }
    	    if($rank['rank_id']==2){
    	       $ji2 = 2; 
    	    }
    	    if($rank['rank_id']==1){
    	       $ji2 = 1; 
    	    }
    		if($rank['rank_id']==0){
    	       $ji2 = 0; 
    	    }
    	    $list[$k]['rank'] = $rank;
    	    $list[$k]['ji2'] = $ji2;
    	    
    	    $upl0 = (int)Db::name('user_profit_logs')->where(array('user_id'=>$v['user_id'],'is_separate'=>0))->sum('money');
			$list[$k]['upl0'] = round($upl0/100,2);//佣金待入账
			
			$upl = (int)Db::name('user_profit_logs')->where(array('user_id'=>$v['user_id'],'is_separate'=>1))->sum('money');
			$list[$k]['upl1'] = round($upl1/100,2);//佣金待入账
		
			
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('level', $level);
        echo $this->fetch();
	}
	
	
	public function dailiedit(){
		$user_id = (int) input('user_id');
		$this->assign('user_id', $user_id);
		$detail = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$this->assign('detail', $detail);
		
		
		if(request()->post()){
            $data = $this->checkFields(input('data/a', false), array('nickname','mobile','stock'));
			
			if($this->role == 1){
				$data['rank_id'] = 1;
			}
			if($this->role == 2){
				$data['rank_id'] = 1;
			}
			if($this->role == 3){
				$data['rank_id'] = 2;
			}
			//p($this->role);
			
			$data['user_id'] = $data['user_id'];
			$data['stock'] = (int) ($data['stock']*100);
			if(!$data['stock']){
				return json(array('code'=>'0','msg'=>'费率不能为空'));
			}
			if($data['stock'] >= 5000){
				return json(array('code'=>'0','msg'=>'费率太高'));
			}
			if($data['stock'] >= $this->member['stock']){
				return json(array('code'=>'0','msg'=>'费率不能高于【'.round($this->member['stock']/100,2).'】'));
			}
			//stock
			//p($this->member);die;
			
			if(!$user_id){
				$updateData['nickname'] = $data['nickname'];
				$updateData['mobile'] = $data['mobile'];
				if(!$updateData['mobile']){
					return json(array('code'=>'0','msg'=>'手机号不存在'));
				}
				if(!isPhone($data['mobile']) && !isMobile($data['mobile'])){
					return json(array('code'=>'0','msg'=>'手机号码格式不正确'));
				}
				$users = Db::name('users')->where(array('mobile'=>$data['mobile']))->find();
				if($users && $users['parent_id'] != $this->uid){
					return json(array('code'=>'0','msg'=>'添加区级代理用户已存在'));
				}
				//p($users);die;
				if($users && $users['parent_id'] == $this->uid){
					$updateData['ext0'] = time();
					$updateData['rank_id'] = $data['rank_id'];
					$updateData['stock'] = $data['stock'];
					$updateData['nickname'] = $data['nickname'];
					$updateData['user_id'] = $users['user_id'];
					//p($updateData);die;
					$rest = Db::name('users')->update($updateData);
					$info = '更新已有区级代理信息成功';
				}else{
					$arr = array(
					   'account' => $data['mobile'], 
					   'mobile' => $data['mobile'],
					   'parent_id' => $this->uid,
					   'rank_id' => $data['rank_id'],
					   'password' => $data['mobile'],
					   'stock' => $data['stock'], 
					   'face' => '/attachs/default.jpg', 
					   'nickname' => $data['nickname'], 
					   'reg_time' => time(), 
					   'reg_ip' =>request()->ip()
					);
					//p($arr);
					$rest = model('Passport')->register($arr,$this->uid,1);
					$info = '注册新的区级代【'.$rest.'】理成功';
				}
			}else{
				$updateData['ext0'] = time();
				$updateData['rank_id'] = $data['rank_id'];
				$updateData['stock'] = $data['stock'];
				$updateData['nickname'] = $data['nickname'];
				$updateData['user_id'] = $user_id;
				$rest = Db::name('users')->update($updateData);
				$info = '更新成功';
			}
			
		
			
            if($rest){
				return json(array('code'=>'1','msg'=>$info,'url'=>url('team/daili')));
            }
			return json(array('code'=>'0','msg'=>'操作失败'));
        }else{
            return $this->fetch();
		}
	}
	
	
	
	
	
	
	
	public function useredit(){
		$city_id = (int) input('city_id');
		$this->assign('city_id', $city_id);
		
		$user_id = (int) input('user_id');
		$this->assign('user_id', $user_id);
	
		$detail = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$this->assign('detail',$detail);
		
		
		if(request()->post()){
            $data = $this->checkFields(input('data/a', false), array('nickname','mobile','stock'));
			
			$updateData['stock'] = (int) ($data['stock']*100);
			if(!$updateData['stock']){
				return json(array('code'=>'0','msg'=>'费率不能为空'));
			}
			if($updateData['stock'] >= 5000){
				return json(array('code'=>'0','msg'=>'费率太高'));
			}
			
			
			//p($this->member['stock']);die;
			$u = Db::name('users')->where(array('user_id'=>$this->member['parent_id']))->find();
			if($u){
			    if($updateData['stock'] >= $u['stock']){
					return json(array('code'=>'0','msg'=>'费率不能高于上级费率【'.round($u['stock']/100,2).'】'));
				}
				if($updateData['stock'] >= ($u['stock']-$this->member['stock'])){
					return json(array('code'=>'0','msg'=>'费率不能高于上上级费率-上级费率【'.round(($u['stock']-$this->member['stock'])/100,2).'】'));
				}
			}else{
				if($updateData['stock'] >= $this->member['stock']){
					return json(array('code'=>'0','msg'=>'费率不能高于上上级【'.round($this->member['stock']/100,2).'】'));
				}
			}
			
			if($this->role == 0 || $this->role == ''){
				$rank_id = 1;
			}
			if($this->role == 1){
				$rank_id = 1;
			}
			if($this->role == 2){
				$rank_id = 1;
			}
			if($this->role == 3){
				$rank_id = 2;
			}
			
			if(!$user_id){
				$updateData['nickname'] = $data['nickname'];
				$updateData['mobile'] = $data['mobile'];
				if(!$updateData['mobile']){
					return json(array('code'=>'0','msg'=>'手机号不存在'));
				}
				if(!isPhone($updateData['mobile']) && !isMobile($updateData['mobile'])){
					return json(array('code'=>'0','msg'=>'手机号码格式不正确'));
				}
				$users = Db::name('users')->where(array('mobile'=>$updateData['mobile']))->find();
				if($users && $users['parent_id'] != $this->uid){
					return json(array('code'=>'0','msg'=>'添加业务员用户已存在'));
				}
				if($users && $users['parent_id'] == $this->uid){
					$updateData['ext0'] = time();
					$updateData['rank_id'] = $rank_id;
					$updateData['stock'] = $updateData['stock'];
					$updateData['nickname'] = $data['nickname'];
					$updateData['user_id'] = $users['user_id'];
					$rest = Db::name('users')->update($updateData);
					$info = '更新已有业务员信息成功';
				}else{
					$arr = array(
					   'account' => $updateData['mobile'], 
					   'mobile' => $updateData['mobile'],
					   'rank_id' => $rank_id,
					   'password' => $updateData['mobile'],
					   'stock' => $updateData['stock'], 
					   'face' => '/attachs/default.jpg', 
					   'nickname' => $updateData['nickname'], 
					   'reg_time' => time(), 
					   'reg_ip' =>request()->ip()
					);
					$rest = model('Passport')->register($arr,$this->uid,1);
					$info = '注册成功';
				}
				
			}else{
				$data['area_id'] = $area_id;
				$info = '更新成功';
			}
			
			if($user_id){
				$updateData['ext0'] = time();
				$updateData['rank_id'] = $rank_id;
				$updateData['stock'] = $updateData['stock'];
				$updateData['nickname'] = $data['nickname'];
				$updateData['user_id'] = $user_id;
				$rest = Db::name('users')->update($updateData);
			}
            if($rest){
				return json(array('code'=>'1','msg'=>$info,'url'=>url('team/index')));
            }
			return json(array('code'=>'0','msg'=>'操作失败'));
        }else{
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
			$map['rank_id'] = 0;
		}
		if($this->role == 1){
			$map['parent_id'] = $user_id;
			$map['rank_id'] = 0;
		}
		if($this->role == 2){
			$map['parent_id'] = $user_id;
			$map['rank_id'] = 1;
		}
		if($this->role == 3){
			$map['parent_id'] = $user_id;
			$map['rank_id'] = 2;
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