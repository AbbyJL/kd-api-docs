<?php

use app\common\model\Setting;

class alipay{
	
    private $alipay_gateway_new = 'http://wappaygw.alipay.com/service/rest.htm?';
	
    public function getCode($logs,$setting){

		//全局设置
		$config = Setting::config();
		//微信下面用支付宝当面付
		$isWeixin = is_weixin();
		//如果是微信
		if($isWeixin){
			$appid = $setting['alipay_app_id']; 
			$notifyUrl = $setting['notify_url'];//异步通知
			$returnUrl = $setting['return_url'];//页面跳转同步通知页面地址
			$outTradeNo = $logs['logs_id'].'-'.time();  
			$payAmount = $logs['logs_amount'];    
			$orderName = $logs['subject']; 
			$signType = 'RSA2';  
			$saPrivateKey=$setting['alipay_private_key'];//私匙
			$aliPay = new \Alipay2($appid,$returnUrl,$notifyUrl,$saPrivateKey);
			$result = $aliPay->doPay($payAmount,$outTradeNo,$orderName,$returnUrl,$notifyUrl);
			$result = $result['alipay_trade_precreate_response'];
			if($result['code'] && $result['code']=='10000'){
				$url2 = $result['qr_code'];
				$token = 'logs_id_' . time();
				$img = $this->buildCode($token,$url2);	
				return array('url'=>$result['qr_code'],'img'=>$img,'msg'=>$result['msg'].' : '.$result['sub_msg']);
			}else{
				return array('url'=>$result['qr_code'],'img'=>$img,'msg'=>$result['msg'].' : '.$result['sub_msg']);
			}
		}else{
			
			$notifyUrl = $setting['notify_url'] ? $setting['notify_url'] : $config['site']['host'].url('wap/payment/respond',array('code'=>'alipay'));//异步通知域名
			$returnUrl = $setting['return_url'] ? $setting['return_url'] : $config['site']['host'].url('wap/payment/yes',array('log_id'=>$logs['logs_id']));//跳转域名
			
			include (ROOT_PATH . 'extend/Payment/alipay/AopClient.php');
			include (ROOT_PATH . 'extend/Payment/alipay/request/AlipayTradeWapPayRequest.php');
				
			$aop = new \AopClient();
			$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
			
			$aop->appId = $setting['alipay_app_id'];//商户ID
			$aop->rsaPrivateKey = $setting['alipay_private_key'];//请填写开发者私钥去头去尾去回车，一行字符串'
			$aop->alipayrsaPublicKey=$setting['alipay_rsa_public_key'];//请填写支付宝公钥，一行字符串
			
			$aop->apiVersion = '2.0';
			$aop->signType = 'RSA2';
			$aop->postCharset='utf-8';
			$aop->format='json';
	

			$request = new \AlipayTradeWapPayRequest();
			
			$request->returnUrl = $returnUrl;
			$request->notifyUrl= $notifyUrl;
			$request->setBizContent("{" .
			"\"subject\":\"{$logs['subject']}\"," .
			"\"body\":\"{$logs['subject']}\"," .
			"\"out_trade_no\":\"{$logs['logs_id']}\"," .
			"\"total_amount\":\"{$logs['logs_amount']}\"," .
			"\"goods_type\":\"1\"," .//付款方姓名
			"\"product_code\":\"QUICK_WAP_WAY\"," .
			"\"return_url\":\"{$return_url}\"" .
			"}");
			
		
			$result = $aop->pageExecute($request); 
			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;
			return $result;
		}
		
    }
	
	
	 public function getCodeWindowToshow($logs,$setting){
		$config = Setting::config();
        $appid = $setting['alipay_app_id'];//这里是开放拼平台的
		$notifyUrl = $setting['notify_url'];//异步通知
		$returnUrl = $setting['return_url'];//页面跳转同步通知页面地址
		$outTradeNo = $logs['logs_id'].'-'.time();  
		$payAmount = $logs['logs_amount'];    
		$orderName = $logs['subject']; 
		$signType = 'RSA2';  
		$saPrivateKey=$setting['alipay_private_key'];//私匙
		
		$aliPay = new \Alipay2($appid,$returnUrl,$notifyUrl,$saPrivateKey);
		$result = $aliPay->doPay($payAmount,$outTradeNo,$orderName,$returnUrl,$notifyUrl);
		
		$result = $result['alipay_trade_precreate_response'];
		if($result['code'] && $result['code']=='10000'){
			$url2 = $result['qr_code'];
			$token = 'logs_id_' . time();
			$img = $this->buildCode($token,$url2);	
			return array('url'=>$result['qr_code'],'img'=>$img,'msg'=>$result['msg'].' : '.$result['sub_msg']);
		}else{
			return array('url'=>$result['qr_code'],'img'=>$img,'msg'=>$result['msg'].' : '.$result['sub_msg']);
		}
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
	
	
	public function respond(){
        if(!empty($_POST)){
            foreach($_POST as $key => $data){
                $_GET[$key] = $data;
            }
        }
        $payment = model('Payment')->getPayment($_GET['code']);
	
        $seller_email = rawurldecode($_GET['seller_email']);
        $logs_id = str_replace($_GET['subject'], '', $_GET['out_trade_no']);
        $logs_id = trim($logs_id);
		
        if(strtolower($_GET['result']) == 'success' || $_GET['trade_status'] == 'TRADE_SUCCESS'){
            model('Payment')->logsPaid($logs_id,$_GET['trade_no'],$_GET['out_trade_no']);
            return true;
        }else{
            return false;
        }
    }


    private function getHttpResponsePOST($url,$para){
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_CAINFO, APP_PATH . 'Lib/Payment/cacert.pem');
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $para);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $responseText = curl_exec($curl);
        curl_close($curl);
        return $responseText;
    }
   
   
    private function parseResponse($str_text){
        $para_split = explode('&', $str_text);
        foreach($para_split as $item) {
            $nPos = strpos($item, '=');
            $nLen = strlen($item);
            $key = substr($item, 0, $nPos);
            $value = substr($item, $nPos + 1, $nLen - $nPos - 1);
            $para_text[$key] = $value;
        }
        if(!empty($para_text['res_data'])){
            $doc = new DOMDocument();
            $doc->loadXML($para_text['res_data']);
            $para_text['request_token'] = $doc->getElementsByTagName("request_token")->item(0)->nodeValue;
        }
        return $para_text;
    }
	
	
	
}