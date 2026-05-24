<?php

require_once "weixin/WxPay.Api.php";
require_once "weixin/WxPay.NativePay.php";
require_once 'weixin/WxPay.Notify.php';
require_once 'weixin/notify.php';
require_once ROOT_PATH . 'extend/phpqrcode/phpqrcode.php';

use app\common\model\Setting;



class native{
	
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

    //获取支付
    public function getCode($logs,$payment){
		
		$config = Setting::config();
		
		$this->init($payment);
        $notify = new NativePay();
        $url1 = $notify->GetPrePayUrl($logs['logs_id']);
        $url1 = urlencode($url1);
        $input = new WxPayUnifiedOrder();
        $input->SetBody($logs['subject']);
        $input->SetAttach($logs['subject']);
        $input->SetDetail($logs['subject']);
        $input->SetOut_trade_no($logs['logs_id'].'-'.time());
		
		if($logs['sub_mch_id']){
			$input->SetSub_Mch_Id($logs['sub_mch_id']);
		}
		
		$SetNotify_url = $config['site']['host'].'/App/Pay/SavePayLog' ? $config['site']['host'].'/App/Pay/SavePayLog' : $config['site']['host'].'/home/payment/respond/code/native'; 
		
        $logs['logs_amount'] = $logs['logs_amount'] * 100;
        $input->SetTotal_fee($logs['logs_amount']);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis" , time() + 600));
        $input->SetGoods_tag($logs['subject']); 
        $input->SetNotify_url($SetNotify_url);
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($logs['logs_id']);
        $result = $notify->GetPayUrl($input);
        $url2 = $result["code_url"];
		$token = $logs['logs_id'].'-'.time();
		$img = $this->buildCode($token,$url2);	
        $img = '<img src='.'\''.$img . '\''.'class="tu-native-pay"/>';
        return $img;
    }
   
    
	//生成支付二维码
	public function buildCode($token,$url2){
		$config = Setting::config();
		$name = date('Y/m/d/',time());
		$md5 = md5($token);
		$patch =ROOT_PATH.'/attachs/'.'weixin/'.$name;
		if(!file_exists($patch)){
			mkdir($patch,0755,true);
		}
		$file = '/attachs/weixin/'.$name.$md5.'.png';
		$fileName  =ROOT_PATH.''.$file;
		if(!file_exists($fileName)){
			$level = 'L';
			QRcode::png($url2,$fileName,$level,$size = 8,2,true);
		}
		return $file; 
	}
	
	
	
	public function respond() {
		
        $xml = file_get_contents("php://input");
        if(empty($xml))
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
        $payment = model('Payment')->getPayment('native');
		
	   
        $sign = array();
        foreach($data as $key => $val){
            if($key != 'sign'){
                $sign[] = $key . '=' . $val;
            }
        }
        $sign[] = 'key=' . $payment['appkey'];
        $signstr = strtoupper(md5(join('&', $sign)));
        if($signstr != $data['sign']){
            return false;
        }   
		 
		$trade = explode('-',$data['out_trade_no']);//新版回调
	
		model('Payment')->logsPaid($trade[0],$data['out_trade_no'],$data['transaction_id']);
        return true;
    }   
	
}
