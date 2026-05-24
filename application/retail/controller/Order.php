<?php

namespace app\retail\controller;
use think\Db;

use app\common\model\Setting;


class Order extends Base{
	
	
	
	public function _initialize(){
        parent::_initialize();
		$this->assign('getorderStatus', $getorderStatus = model('Setting')->getorderStatus());
		$this->assign('getdiffStatus', $getdiffStatus = model('Setting')->getdiffStatus());
        $this->assign('getorderRightsStatus', $getorderRightsStatus = model('Setting')->getorderRightsStatus());
		$this->assign('getCompanyApiTypes', $getCompanyApiTypes = model('Setting')->getCompanyApiTypes());
    }
	
	
	
	 public function index(){
		$orderStatus = input('orderStatus');
        $this->assign('orderStatus', $orderStatus);
		
		$keyword = input('keyword','', 'trim,htmlspecialchars');
		$this->assign('keyword', $keyword);
		
        return $this->fetch();
	}
	
	
	
    public function load(){
       $map = array();
        $id = (int)input('id','', 'trim,htmlspecialchars');
        if($id){
            $map['id'] = $id;
            $this->assign('id', $id);
        }
		
		if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['deliveryId|id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
        $deliveryId = input('deliveryId');
        if($deliveryId){
            $map['deliveryId'] = $deliveryId;
			$this->assign('deliveryId', $deliveryId);
        }		
		
	
		if($this->role == 1){
			$map['pid'] =$this->uid;
		}
		if($this->role == 2){
			$map['rank2_uid'] =$this->uid;
		}
		if($this->role == 3){
			$map['rank3_uid'] =$this->uid;
		}
		
        if($user_id = (int) input('user_id')){
            $map['user_id'] = $user_id;
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		
 		$orderStatus = input('orderStatus');
		if($orderStatus){
            if($orderStatus != 999){
                $map['orderStatus'] = $orderStatus;
            }
            $this->assign('orderStatus', $orderStatus);
        }else{
            $this->assign('orderStatus', 999);
        }
		
		if(isset($input['diffStatus']) || isset($input['diffStatus'])){
            $diffStatus= (int) input('diffStatus');
            if($diffStatus != 999){
                $map['diffStatus'] = $diffStatus;
            }
            $this->assign('odiffStatus', $diffStatus);
        }else{
            $this->assign('diffStatus', 999);
        }
		
		if(isset($input['orderRightsStatus']) || isset($input['orderRightsStatus'])){
            $orderRightsStatus= (int) input('orderRightsStatus');
            if($orderRightsStatus != 999){
                $map['orderRightsStatus'] = $orderRightsStatus;
            }
            $this->assign('oorderRightsStatus', $orderRightsStatus);
        }else{
            $this->assign('orderRightsStatus', 999);
        }
	
        $count = Db::name('express_order')->where($map)->count();
        $Page = new \Page($count, 10);
        $show = $Page->show();
		$p = input('p');
        if($Page->totalPages < $p){
            die('0');
        }
		
		//p($map);die;
		
		
        $list = Db::name('express_order')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k =>$v){
			$list[$k]['user'] = Db::name('users')->find($v['user_id']);
			$list[$k]['user1'] = Db::name('users')->find($v['rank1_uid']);
			$list[$k]['user2'] = Db::name('users')->find($v['rank2_uid']);
			$list[$k]['user3'] = Db::name('users')->find($v['rank3_uid']);
			$list[$k]['rank1'] = Db::name('user_rank')->find($list[$k]['user1']['rank_id']);
			$list[$k]['rank2'] = Db::name('user_rank')->find($list[$k]['user2']['rank_id']);
			$list[$k]['rank3'] = Db::name('user_rank')->find($list[$k]['user3']['rank_id']);
			
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		
		cookie('express_order_map',$map);
		
		
        echo $this->fetch();
	}
	
	
	
    public function index2(){
		$orderStatus = input('orderStatus');
        $this->assign('orderStatus', $orderStatus);
		
		$keyword = input('keyword','', 'trim,htmlspecialchars');
		$this->assign('keyword', $keyword);
		
		
		$user_id = (int) input('user_id');
		$this->assign('user_id', $user_id);
		if(!$user_id){
		    $user_id = $this->uid;
		}
		$detail = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$this->assign('detail', $detail);
		
		
        return $this->fetch();
	}
	
	
	
    public function load2(){
       $map = array();
        $id = (int)input('id','', 'trim,htmlspecialchars');
        if($id){
            $map['id'] = $id;
            $this->assign('id', $id);
        }
		
		if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['deliveryId|id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
        $deliveryId = input('deliveryId');
        if($deliveryId){
            $map['deliveryId'] = $deliveryId;
			$this->assign('deliveryId', $deliveryId);
        }		
		
		
		$user_id = (int) input('user_id');
		$this->assign('user_id', $user_id);
		if(!$user_id){
		    $user_id = $this->uid;
		}

		$map['pid'] =$user_id;
	
 		$orderStatus = input('orderStatus');
		if($orderStatus){
            if($orderStatus != 999){
                $map['orderStatus'] = $orderStatus;
            }
            $this->assign('orderStatus', $orderStatus);
        }else{
            $this->assign('orderStatus', 999);
        }
		
		if(isset($input['diffStatus']) || isset($input['diffStatus'])){
            $diffStatus= (int) input('diffStatus');
            if($diffStatus != 999){
                $map['diffStatus'] = $diffStatus;
            }
            $this->assign('odiffStatus', $diffStatus);
        }else{
            $this->assign('diffStatus', 999);
        }
		
		if(isset($input['orderRightsStatus']) || isset($input['orderRightsStatus'])){
            $orderRightsStatus= (int) input('orderRightsStatus');
            if($orderRightsStatus != 999){
                $map['orderRightsStatus'] = $orderRightsStatus;
            }
            $this->assign('oorderRightsStatus', $orderRightsStatus);
        }else{
            $this->assign('orderRightsStatus', 999);
        }
	
        $count = Db::name('express_order')->where($map)->count();
        $Page = new \Page($count, 10);
        $show = $Page->show();
		$p = input('p');
        if($Page->totalPages < $p){
            die('0');
        }
		
		//p($map);die;
		
		
        $list = Db::name('express_order')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k =>$v){
			$list[$k]['user'] = Db::name('users')->find($v['user_id']);
			$list[$k]['user1'] = Db::name('users')->find($v['rank1_uid']);
			$list[$k]['user2'] = Db::name('users')->find($v['rank2_uid']);
			$list[$k]['user3'] = Db::name('users')->find($v['rank3_uid']);
			$list[$k]['rank1'] = Db::name('user_rank')->find($list[$k]['user1']['rank_id']);
			$list[$k]['rank2'] = Db::name('user_rank')->find($list[$k]['user2']['rank_id']);
			$list[$k]['rank3'] = Db::name('user_rank')->find($list[$k]['user3']['rank_id']);
			
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		
		cookie('express_order_map',$map);
		
		
        echo $this->fetch();
	}
	
	//订单详情
	public function detail($id = 0){
        if($id = (int) $id){
            if(!($detail = Db::name('express_order')->find($id))){
                $this->error('请选择要编辑');
            }
            $this->assign('var', $detail);
			$this->assign('detail', $detail);
			
			
			return $this->fetch();
           
        }else{
            $this->error('请选择要编辑的');
        }
    }
	
	
	


}