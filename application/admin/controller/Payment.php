<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;
use app\common\model\Setting;

class Payment extends Base{
	

    public function index(){
        $count = Db::name('payment')->count();
        $Page = new \Page($count, 15);
        $show = $Page->show();
        $list = Db::name('payment')->order('payment_id','asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
            $list[$k]['num_0'] = (int)Db::name('payment_logs')->where(array('code'=>$val['code'],'is_paid'=>0))->count();
			$list[$k]['num_1'] = (int)Db::name('payment_logs')->where(array('code'=>$val['code'],'is_paid'=>1))->count();
			$list[$k]['money_0'] = (int)Db::name('payment_logs')->where(array('code'=>$val['code'],'is_paid'=>0))->sum('need_pay');
			$list[$k]['money_1'] = (int)Db::name('payment_logs')->where(array('code'=>$val['code'],'is_paid'=>1))->sum('need_pay');
			$list[$k]['setting'] = unserialize($val['setting']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
    public function uninstall(){
        $payment_id = (int) input('payment_id');
        $payments = Db::name('payment')->where(array('payment_id'=>$payment_id))->find();
        if(!$payments){
            $this->jinMsg('没有该支付方式');
        }
        $datas = array('payment_id' => $payment_id,'is_open' =>0);
		if(Db::name('payment')->update($datas)){
			$this->jinMsg('卸载【'.$payments['name'].'】支付方式成功', url('payment/index'));
		}else{
			$this->jinMsg('卸载失败');
		}
    }
	
    public function install($payment_id){
		$CONFIG = Setting::config();//获取全局参数
        $payment_id = (int) input('payment_id');
        $payments = Db::name('payment')->where(array('payment_id'=>$payment_id))->find();
		
        if(!$payments){
            $this->jinMsg('没有该支付方式');
        }
        if($payments['code'] == 'money' || $payments['code'] == 'prestige'){
            Db::name('payment')->update(array('payment_id' => $payment_id, 'is_open' => 1));
            $this->jinMsg('余额支付安装成功', url('payment/index'));
        }
		
        if(request()->post()){
            $data = input('data/a', false);
			$safety = $data['safety'];
			
		
            $datas = array('payment_id' => $payment_id, 'setting' => serialize($data), 'is_open' => 1);
			if(Db::name('payment')->update($datas)){
				$this->jinMsg('恭喜您安装【'.$payments['name'].'】支付方式成功', url('payment/index'));
			}
            $this->jinMsg('安装支付失败');
        }else{
			$payments['setting'] = unserialize($payments['setting']);
            $this->assign('detail', $payments);
            return $this->fetch($payments ['code']);
        }
    }
}