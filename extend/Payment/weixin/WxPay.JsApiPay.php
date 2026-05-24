<?php

use think\Db;
use app\common\model\Setting;

require_once 'WxPay.Api.php';


class JsApiPay{
    public $data = null;
	
    public function GetOpenid($logs){
		$CONFIG = Setting::config();
        if(!isset($_GET['code'])){
            $baseUrl = urlencode($CONFIG['site']['host'] . url('payment/payment',array('log_id'=>$logs['logs_id'])));
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: {$url}");
            die;
        }else{
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }

    }

    public function GetJsApiParameters($UnifiedOrderResult){
        if(!array_key_exists('appid', $UnifiedOrderResult) || !array_key_exists('prepay_id', $UnifiedOrderResult) || $UnifiedOrderResult['prepay_id'] == ''){
			Db::name('payment')->where(array('payment_id'=>3))->update(array('error_intro'=>$UnifiedOrderResult['return_msg'].''.$UnifiedOrderResult['err_code_des']));
            throw new WxPayException('微信支付配置错误:'.$UnifiedOrderResult['return_msg'].''.$UnifiedOrderResult['err_code_des']);
        }
        $jsapi = new WxPayJsApiPay();
        $jsapi->SetAppid($UnifiedOrderResult['appid']);
        $timeStamp = '"' . time() . '"';
        $jsapi->SetTimeStamp($timeStamp);
        $jsapi->SetNonceStr(WxPayApi::getNonceStr());
        $jsapi->SetPackage('prepay_id=' . $UnifiedOrderResult['prepay_id']);
        $jsapi->SetSignType('MD5');
        $jsapi->SetPaySign($jsapi->MakeSign());
        $parameters = json_encode($jsapi->GetValues());
        return $parameters;

    }

    public function GetOpenidFromMp($code){
        $url = $this->__CreateOauthUrlForOpenid($code);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if (WEIXIN_CURL_PROXY_HOST != '0.0.0.0' && WEIXIN_CURL_PROXY_PORT != 0){
            curl_setopt($ch, CURLOPT_PROXY, WEIXIN_CURL_PROXY_HOST);
            curl_setopt($ch, CURLOPT_PROXYPORT, WEIXIN_CURL_PROXY_PORT);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($res, true);
        $this->data = $data;
        $openid = $data['openid'];
        return $openid;

    }

    private function ToUrlParams($urlObj){
        $buff = '';
        foreach($urlObj as $k => $v){
            if($k != 'sign'){
                $buff .= $k . '=' . $v . '&';
            }
        }
        $buff = trim($buff, '&');
        return $buff;
    }

    public function GetEditAddressParameters(){
		$CONFIG = Setting::config();
        $getData = $this->data;
        $data = array();
        $data['appid'] = $CONFIG['weixin']['appid'];
        $data['url'] = $CONFIG['site']['host'] . $_SERVER['REQUEST_URI'];
        $time = time();
        $data['timestamp'] = "{$time}";
        $data['noncestr'] = '1234568';
        $data['accesstoken'] = $getData['access_token'];
        ksort($data);
        $params = $this->ToUrlParams($data);
        $addrSign = sha1($params);
        $afterData = array('addrSign' => $addrSign, 'signType' => 'sha1', 'scope' => 'jsapi_address', 'appId' => $CONFIG['weixin']['appid'], 'timeStamp' => $data['timestamp'], 'nonceStr' => $data['noncestr']);
        $parameters = json_encode($afterData);
        return $parameters;

    }

    private function __CreateOauthUrlForCode($redirectUrl){
		$CONFIG = Setting::config();
        $urlObj['appid'] = $CONFIG['weixin']['appid'];
        $urlObj['redirect_uri'] = "{$redirectUrl}";
        $urlObj['response_type'] = 'code';
        $urlObj['scope'] = 'snsapi_base';
        $urlObj['state'] = 'STATE' . '#wechat_redirect';
        $bizString = $this->ToUrlParams($urlObj);
        return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . $bizString;

    }

    private function __CreateOauthUrlForOpenid($code){
		$CONFIG = Setting::config();
        $urlObj['appid'] = $CONFIG['weixin']['appid'];
        $urlObj['secret'] = $CONFIG['weixin']['appsecret'];
        $urlObj['code'] = $code;
        $urlObj['grant_type'] = 'authorization_code';
        $bizString = $this->ToUrlParams($urlObj);
        return 'https://api.weixin.qq.com/sns/oauth2/access_token?' . $bizString;

    }

}