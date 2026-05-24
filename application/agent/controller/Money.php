<?php

namespace app\agent\controller;
use think\Db;

use app\common\model\Setting;


class Money extends Base{
	
	
	
	public function _initialize(){
        parent::_initialize();
		$this->assign('getorderStatus', $getorderStatus = model('Setting')->getorderStatus());
		$this->assign('getdiffStatus', $getdiffStatus = model('Setting')->getdiffStatus());
        $this->assign('getorderRightsStatus', $getorderRightsStatus = model('Setting')->getorderRightsStatus());
		$this->assign('getCompanyApiTypes', $getCompanyApiTypes = model('Setting')->getCompanyApiTypes());
    }
	
	
	public function tj(){
		
		$this->assign('v',date('Y-m-d',time()));
		$bg_time = time() - 86400 * 30;
		$bgtime = strtotime(TODAY);
		$str = '-30 day';
        $str2 = strtotime(date('Y-m-d', strtotime($str)));
		
		$counts['paymentlogs'] =(int)Db::name('payment_logs')->where(array('is_paid'=>'1'))->sum('need_pay');
		$counts['day_paymentlogs'] =(int)Db::name('payment_logs')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime)),'is_paid' => '1'))->sum('need_pay');
		
		$counts['money'] =(int)Db::name('users')->sum('money');
		$counts['user_cash'] =(int)Db::name('users_cash')->sum('money');
		$counts['user_cash_1'] =(int)Db::name('users_cash')->where(array('status'=>'1'))->sum('money');
		$counts['user_cash_2'] =(int)Db::name('users_cash')->where(array('status'=>'0'))->sum('money');
		
		
		$counts['users_vip'] =(int)Db::name('users')->where(array('rank_id'=>array('gt',0)))->count();
		$counts['users_company'] =(int)Db::name('users_company')->where(array('status'=>'1'))->count();
		
		$counts['day_express'] =(int)Db::name('express_order')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime)),'closed'=>0))->count();
		$counts['express'] =(int)Db::name('express_order')->where(array('closed'=>0))->count();
		
		$counts['push_day'] =(int)Db::name('express_order_push')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime))))->count();
		$counts['push'] =(int)Db::name('express_order_push')->count();
		
		
		$counts['day_exp_1'] =(int)Db::name('express_order')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime)),'orderStatus'=>array('in',array(1,2,3,4)),'closed'=>0))->sum('sumMoneyYuan');
		$counts['day_exp'] =(int)Db::name('express_order')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime)),'orderStatus'=>array('in',array(1,2,3,4)),'closed'=>0))->sum('sumMoneyYuan_jia');
		
		
		//p($bg_time);die;
		$counts['m_exp_1'] =(int)Db::name('express_order')->where(array('create_time' => array(array('ELT',$bgtime), array('EGT',$str2)),'orderStatus'=>array('in',array(1,2,3,4)),'closed'=>0))->sum('sumMoneyYuan');
		$counts['m_exp'] =(int)Db::name('express_order')->where(array('create_time' => array(array('ELT',$bgtime), array('EGT',$str2)),'orderStatus'=>array('in',array(1,2,3,4)),'closed'=>0))->sum('sumMoneyYuan_jia');
		
		$counts['exp_1'] =(int)Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3,4)),'closed'=>0))->sum('sumMoneyYuan');
		$counts['exp'] =(int)Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3,4)),'closed'=>0))->sum('sumMoneyYuan_jia');
		
		
		$counts['users'] = (int) Db::name('users')->count();
		$counts['totay_user'] = (int) Db::name('users')->where(array('reg_time' => array(array('ELT',time()), array('EGT',$bgtime))))->count();
		$counts['user_moblie'] = (int) Db::name('users')->where(array('mobile'=>array('EXP','IS NULL')))->count();
		
		
		$this->getOrderStatus = model('Setting')->getorderStatus();
		//统计数量
		$getOrderStatus = array();
		foreach($this->getOrderStatus as $k2 =>$v2){   
		    $getOrderStatus[$k2]['id'] = $k2; 
		    $getOrderStatus[$k2]['name'] = $v2; 
			$getOrderStatus[$k2]['count'] = (int)Db::name('express_order')->where(array('orderStatus'=>$k2,'closed'=>0))->count();
		}
		$this->assign('getOrderStatus',$getOrderStatus);
		$this->assign('counts', $counts);
        return $this->fetch();
	}
	
	
    public function index(){
		$type = input('type');
        if($type){
            $map['type'] = $type;
			$this->assign('type', $type);
        }
        return $this->fetch();
	}
	
	
	
    public function load(){
        $map = array('user_id'=>$this->uid);
		$type = input('type');
        if($type){
            $map['type'] = $type;
			$this->assign('type', $type);
        }
        $count = Db::name('user_profit_logs')->where($map)->count();
        $Page = new \Page($count, 10);
        $show = $Page->show();
		$p = input('p');
        if($Page->totalPages < $p){
            die('0');
        }		
        $list = Db::name('user_profit_logs')->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
            $list[$k]['users'] = Db::name('users')->where('user_id',$val['user_id'])->count();
			if($val['type'] == 'add'){
				$name = '邀请奖励';
			}
			if($val['type'] == 'express'){
				$name = '快递订单分成';
			}
			if($val['type'] == 'area'){
				$name = '区县代理';
			}
			if($val['type'] == 'city'){
				$name = '城市代理';
			}
			if($val['type'] == 'rank'){
				$name = '等级分成';
			}
			$list[$k]['name'] = $name;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        echo $this->fetch();
	}
	
	

}