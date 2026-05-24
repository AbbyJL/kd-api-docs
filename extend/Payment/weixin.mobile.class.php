<?php

use app\common\model\Setting;

class weixin{
    public function init($payment){
        define('WEIXIN_APPID', $payment['appid']);
        define('WEIXIN_MCHID', $payment['mchid']);
        define('WEIXIN_APPSECRET', $payment['appsecret']);
        define('WEIXIN_KEY',$payment['appkey']);
        define('WEIXIN_SSLCERT_PATH', '/cert/apiclient_cert.pem');
        define('WEIXIN_SSLKEY_PATH', '/cert/apiclient_key.pem');
        define('WEIXIN_CURL_PROXY_HOST', "0.0.0.0"); 
        define('WEIXIN_CURL_PROXY_PORT', 0); 
        define('WEIXIN_REPORT_LEVENL', 1);
        require_once "weixin/WxPay.Api.php";
        require_once "weixin/WxPay.JsApiPay.php";
    }

    public function getCode($logs,$payment){
		
		$CONFIG = Setting::config();
	
		//跳转域名如果没有设置则跳转到默认域名	
		$notifyUrl = $payment['notify_url'] ? $payment['notify_url'] : $CONFIG['site']['host'].url('wap/payment/respond',array('code'=>'weixin'));//异步通知
		$returnUrl = $payment['return_url'] ? $payment['return_url'] : $CONFIG['site']['host'].url('wap/payment/yes',array('log_id'=>$logs['logs_id']));//跳转域名
	
	
        $this->init($payment);
        $tools = new JsApiPay();
        $input = new WxPayUnifiedOrder();
		
		
        $input->SetBody($logs['subject']);
        $input->SetAttach($logs['subject']);
		
		if($logs['sub_mch_id']){
			$input->SetSub_Mch_Id($logs['sub_mch_id']);
		}
		
		
        $input->SetOut_trade_no($logs['logs_id'].'-'.time());
        $logs['logs_amount'] = $logs['logs_amount'] *100;
        $input->SetTotal_fee("{$logs['logs_amount']}");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time()+600));
        $input->SetGoods_tag($logs['subject']);
        $input->SetNotify_url($notifyUrl);//异步通知域名
        $input->SetTrade_type("JSAPI");
		
		
		if($logs['open_id']){
			$input->SetOpenid($logs['open_id']);
		}else{
			$openId = $tools->GetOpenid($logs);//最新获取
            $input->SetOpenid($openId);
		}
		
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);

		
		
		
        $str = '<script>
					function jsApiCall(){
						WeixinJSBridge.invoke(
							\'getBrandWCPayRequest\',
							'.$jsApiParameters.',
							function(res){
								if(res.err_msg ==\'get_brand_wcpay_request:ok\'){ 
									location.href="'.$returnUrl.'";
								}
							}
						);
					}
					function callpay(){
						if(typeof WeixinJSBridge == "undefined"){
							if(document.addEventListener){
								document.addEventListener(\'WeixinJSBridgeReady\',jsApiCall, false);
							}else if(document.attachEvent){
								document.attachEvent(\'WeixinJSBridgeReady\',jsApiCall); 
								document.attachEvent(\'onWeixinJSBridgeReady\',jsApiCall);
							}
						}else{
							jsApiCall();
						}
					}
				</script>
			<button class="button button-block bg-dot button-big" type="button" onclick="callpay()" >立即支付</button>';
        return $str;
    }
	
	
	
	
	
    //微信支付回调
    public function respond() {
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
