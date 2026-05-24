<?php

use app\common\model\Setting;

class weixinh5{
	
    public function init($payment){
        define('WEIXIN_APPID', $payment['appid']);
        define('WEIXIN_MCHID', $payment['mchid']);
        define('WEIXIN_APPSECRET', $payment['appsecret']);
        define('WEIXIN_KEY',$payment['appkey']);
    }
	
	//微信H5支付
	public function getCode($logs,$payment){
		$config = Setting::config();
		require_once "weixin/wechatH5Pay.php";
		
		
		//跳转域名如果没有设置则跳转到默认域名	
		$notifyUrl = $payment['notifyUrl'] ? $payment['notifyUrl'] : $payment['notify_url'];//异步通知域名
		$returnUrl = $payment['returnUrl'] ? $payment['returnUrl'] : $config['site']['host'].url('wap/payment/callback', array('log_id'=>$logs['logs_id']));//回调域名
			
			//p($returnUrl);die;
		
		$wechatAppPay = new wechatAppPay($payment['appid'],$payment['mchid'],$logs['sub_mch_id'],$payment['is_sub_mch_id'] = 0,$payment['notify_url'],$payment['appkey']);
		
		$params['body'] = $logs['subject']; //商品描述
        $params['out_trade_no'] = $logs['logs_id'].'-'.time();  //自定义的订单号
        $params['total_fee'] = $logs['logs_amount'] *100;    //订单金额只能为整数单位为分
        $params['trade_type'] = 'MWEB'; 
        $params['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": "'.$config['site']['host'].'","wap_name": "'.$logs['subject'].'"}}';
        $result = $wechatAppPay->unifiedOrder($params);
		if($result['mweb_url']){
			$url = $result['mweb_url'].'&redirect_url='.$returnUrl;
			$button = '<a href="'.$url. '" type="button" class="button button-block bg-dot button-big text-center">立刻微信H5支付</a>';
		}else{
			$button = '<a type="button" class="button button-block bg-gray button-big text-center">微信H5支付配置有误</a>';
		}
        return $button;
    }

	
    //支付回调2
    public function respond(){
        $xml = file_get_contents("php://input");
        if (empty($xml))
            return false;
        $xml = new SimpleXMLElement($xml);
        if(!$xml)
            return false;
        $data = array();
		
        foreach($xml as $key => $value){
            $data[$key] = strval($value);
        }
        if(empty($data['return_code']) || $data['return_code'] != 'SUCCESS'){
            return false;
        }
        if(empty($data['result_code']) || $data['result_code'] != 'SUCCESS'){
            return false;
        }
        if(empty($data['out_trade_no'])){
            return false;
        }
        ksort($data);
        reset($data);
        $payment = model('Payment')->getPayment('weixin');
        $trade = explode('-',$data['out_trade_no']);
       
	   
		model('Payment')->logsPaid($trade[0],$data['out_trade_no'],$data['transaction_id']);
        return true;
    }
	
	

}
