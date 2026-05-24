<?php

namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Pay extends Base{
	
	
	
	public function SavePayLog(){
		$testxml = file_get_contents("php://input");
		$jsonxml = json_encode(simplexml_load_string($testxml,'SimpleXMLElement',LIBXML_NOCDATA));
		$result = json_decode($jsonxml, true);
		if($result){
			 if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
				 $trade = explode('-',$result['out_trade_no']);
				 $log_id = $trade[0];
				 $logs = Db::name('payment_logs')->where(array('log_id'=>$log_id))->find();
				 if($logs['is_paid'] == 0){
					Db::name('payment_logs')->where(array('log_id'=>$log_id))->update(array('return_order_id'=>$result['out_trade_no'],'return_trade_no'=>$result['transaction_id'])); 
					model('Payment')->logsPaid($log_id,$result['out_trade_no'],$result['transaction_id']);
				 }
				 return json(array('return_code'=>'SUCCESS','return_msg'=>'OK')); 
		  	}
		}
		return json(array('return_code'=>'SUCCESS','return_msg'=>'OK')); 
	}
	
	
	//抖音支付回调
	public function SaveDouyinPayLog(){
		$config = Setting::config();
		$ttPay = new \TtPay($config['toutiao']['AppID'],$config['toutiao']['token'],$config['toutiao']['SALT'],$out_trade_no=0,$info='',$need_pay=0);//支付接口
		$pay = $ttPay->notify();
		if($pay['err_no']==0 && $pay['err_tips']=='success'){
			$msg = $pay['msg'];
			$log_id = $msg['cp_orderno'];
			$out_trade_no = $msg['seller_uid'];
			$transaction_id = $msg['order_id'];
			$logs = Db::name('payment_logs')->where(array('log_id'=>$log_id))->find();
			if($logs['is_paid'] == 0){
				Db::name('payment_logs')->where(array('log_id'=>$log_id))->update(array('return_order_id'=>$out_trade_no,'return_trade_no'=>$transaction_id)); 
				model('Payment')->logsPaid($log_id,$out_trade_no,$transaction_id);
			}
			return json(array('err_no'=>'0','err_tips'=>'success')); 
		}
		return json(array('err_no'=>'1','err_tips'=>'推送失败')); 
	}
	
	
	
	public function yes2(){
		$log_id =(int) input('log_id');
		$return_order_id = input('return_order_id','','trim,htmlspecialchars');
		$return_trade_no = input('return_trade_no','','trim,htmlspecialchars');
		model('Payment')->logsPaid($log_id,$return_order_id,$return_trade_no);//通过订单ID回调，其他错误后期在写
		return json(array('status'=>0,'msg'=>'支付成功')); 
	}
	
	
	

	
	//支付旧版备用
    public function Pay(){
		 $config = Setting::config();
         $res = model('Payment')->getPayment('weixin');
         $openid= input('openid');
		 
         $out_trade_no = $res['mchid']. time();
		 
         $total_fee = input('money');
         if(empty($total_fee)){
            $body = "订单付款";
            $total_fee = floatval(99*100);
         }else{
             $body = "订单付款";
             $total_fee = floatval($total_fee*100);
         }
		 $types = input('types'); 
		 $user_id = input('user_id');  
	     $order_id = input('order_id');  
		 $arr = array(
			'type' => $types, 
			'user_id' => $user_id, 
			'order_id' => $order_id, 
			'order_ids' =>'', 
			'code' => 'wxapp', 
			'need_pay' => $total_fee, 
			'create_time' => NOW_TIME, 
			'create_ip' => request()->ip(), 
			'is_paid' => 0
		);
        $log_id = Db::name('payment_logs')->insertGetId($arr);
		
        $weixinpay = new \Wxpay($config['wxapp']['appid'],$openid,$res['mchid'],$res['mchid'],$out_trade_no,$body,$total_fee);//支付接口
        $return = $weixinpay->pay();
		$return['log_id'] = $log_id;//支付ID
        echo json_encode($return);
    }
	

  
	
}
