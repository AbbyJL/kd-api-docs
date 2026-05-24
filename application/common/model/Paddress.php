<?php

namespace app\common\model;
use think\model;
use think\Db;
use think\Cache;

class Paddress extends Base{
	

    protected $pk = 'id';
    protected $tableName = 'paddress';
	
	//单个付款获取用户地址
	public function order_address_id($uid,$order_id){
		if(empty($order_id)){
			return false;
		}
		$order = Db::name('order')->where(array('order_id' =>$order_id))->find();
		$res = Db::name('paddress')->where(array('id'=>$order['address_id'],'closed' =>0))->find();
		return $res;
		
	}
	
	public function getUserDefaultPaddress($uid,$type){
		$count = (int)Db::name('paddress')->where(array('user_id'=>$uid,'closed'=>0))->count();
		if($count == 0){
			return false; 
		}
		$paddress = Db::name('paddress')->where(array('user_id'=>$uid,'default'=>1,'closed'=>0))->order('id desc')->find();
		if(!$paddress){
			$paddress = Db::name('paddress')->where(array('user_id'=>$uid,'closed'=>0))->order('id desc')->find();
		}
		return $paddress; 
	}
	
	
	//PC合并付款获取第一个地址
	public function pc_paycode_address($log_id,$uid){
		if(empty($log_id)){
			return false;
		}
		$logs = Db::name('payment_logs')->where(array('log_id'=>$log_id))->find();
		if(empty($logs)){
			return false;
		}
		
	    $order_ids = explode(',',$logs['order_ids']);
	    foreach($order_ids as $v){
		   $order = Db::name('order')->where(array('order_id'=>$v))->find();
	    }
		
	    if(!empty($order['address_id'])){
		   $addrs = Db::name('paddress')->where(array('user_id'=>$uid,'closed' =>0))->order(array('default'=>'desc','id'=>'desc'))->limit(0,6)->select();
		   return $addrs; 
	    }
		return false;
	}
	
	
	
	
	//PC合并付款修改默认地址
	public function paycode_replace_default_address($uid,$id){
		$list = Db::name('paddress')->where(array('user_id'=>$uid,'default'=> 1,'closed' =>0))->select();
		if(empty($list)){
			return false;
		}
		foreach($list as $k => $val){
			Db::name('paddress')->where(array('id'=>$val['id']))->setField('default',0);
		}
		Db::name('paddress')->where(array('id'=>$id))->setField('default',1);	
		return true; 
	}
	
	
	//PC单独付款获取收货地址
	public function pc_pay_address($address_id,$uid){
		
		if($address_id){
			$thisaddr[] = Db::name('paddress')->where(array('id'=>$address_id))->find();
		    $addrs = Db::name('paddress')->where(array('user_id'=>$uid,'closed' =>0,'id'=>array('NEQ',$address_id)))->order(array('id'=>'desc'))->limit(0,6)->select();
            if(empty($addrs)) {             
				return $thisaddr; 
            }else{
                $addrss = array_merge($thisaddr,$addrs);
				return $addrss; 
            }
		}else{
            $addrs = Db::name('paddress')->where(array('user_id'=>$uid,'closed' =>0))->order(array('default' => 'desc', 'id' => 'desc'))->limit(0, 6)->select();
			return $addrs; 
        }
	}
	
	
	
	
	public function defaultAddress($uid,$type){
		$count = Db::name('paddress')->where(array('user_id'=>$uid,'closed' => 0))->count();
		if($count == 0){
			//这里有问题，莫须有跳转
		}else{
			$count2 = Db::name('paddress')->where(array('user_id'=> $uid,'default'=>1,'closed'=> 0))->count();
			if($count2 == 0){
				$rest = Db::name('paddress')->where(array('user_id'=>$uid,'closed' =>0))->order("id desc")->find();
				return $rest; 
			}else{
				$rest = Db::name('paddress')->where(array('user_id'=>$uid,'default'=>1,'closed'=>0))->find();
				return $rest; 
			}
		}
		 return false; 
	}
	
	
	public function check_cat_address($uid,$type){
		if(empty($uid)){
			return false;
		}
		$paddress = Db::name('paddress')->where(array('user_id'=>$uid,'default'=>1,'closed' => 0))->find();
		if(!empty($paddress)){
			$id = $paddress['id'];
			return $aid;
		}else{
			$paddress = Db::name('paddress')->where(array('user_id'=>$uid,'closed'=> 0))->order(array('id desc'))->find();
			$id = $paddress['id'];
			return $id;
		}
		return false;
	}	
	
	
}