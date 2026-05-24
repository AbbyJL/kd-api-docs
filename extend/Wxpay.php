<?php 

//微信小程序支付
use app\common\model\Setting;

class Wxpay{
    protected $appid;
    protected $mch_id;
    protected $key;
    protected $openid;
    protected $out_trade_no;
    protected $body;
    protected $total_fee;
	
    function __construct($appid, $openid,$mch_id,$key,$out_trade_no,$body,$total_fee){
        $this->appid = $appid;
        $this->openid = $openid;
        $this->mch_id = $mch_id;
        $this->key = $key;
        $this->out_trade_no = $out_trade_no;
        $this->body = $body;
        $this->total_fee = $total_fee;
    }


    public function pay(){
        $return = $this->weixinapp();
        return $return;
    }


    private function unifiedorder(){
		$config = Setting::config();
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $parameters = array(
            'appid' => $this->appid,
            'mch_id' => $this->mch_id,
            'nonce_str' => $this->createNoncestr(),
            'body' => $this->body,
            'out_trade_no'=> $this->out_trade_no,
            'total_fee' => $this->total_fee,
            'spbill_create_ip' => request()->ip(), //终端IP
            'notify_url' => $config['site']['host'].'/app/pay/SavePayLog', //通知地址  确保外网能正常访问
            'openid' => $this->openid, 
            'trade_type' => 'JSAPI'//交易类型
        );
        $parameters['sign'] = $this->getSign($parameters);
        $xmlData = $this->arrayToXml($parameters);
        $return = $this->xmlToArray($this->postXmlCurl($xmlData,$url,60));
		
        return $return;
		
    }


    private static function postXmlCurl($xml,$url,$second = 30){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch,CURLOPT_HEADER, FALSE);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch,CURLOPT_POST, TRUE);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,20);
        curl_setopt($ch,CURLOPT_TIMEOUT,40);
        set_time_limit(0);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }else{
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }
    
    
    
    //数组转换成xml
    private function arrayToXml($arr){
        $xml = "<root>";
        foreach($arr as $key => $val){
            if(is_array($val)){
                $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
            }else{
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</root>";
        return $xml;
    }


    //xml转换成数组
    private function xmlToArray($xml){
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }


    //微信小程序接口
    private function weixinapp(){
        $unifiedorder = $this->unifiedorder();
        $parameters = array(
            'appId' => $this->appid, //小程序ID
            'timeStamp' => '' . time() . '', //时间戳
            'nonceStr' => $this->createNoncestr(), //随机串
            'package' => 'prepay_id=' . $unifiedorder['prepay_id'], //数据包
            'signType' => 'MD5'
        );
        $parameters['paySign'] = $this->getSign($parameters);
		$parameters['rest'] = $unifiedorder;
        return $parameters;
    }


    private function createNoncestr($length = 32){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for($i = 0; $i < $length; $i++){
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }


    private function getSign($Obj){
        foreach($Obj as $k => $v){
            $Parameters[$k] = $v;
        }
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        $String = $String . "&key=" . $this->key;
        $String = md5($String);
        $result_ = strtoupper($String);
        return $result_;
    }


    private function formatBizQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach($paraMap as $k => $v){
            if($urlencode){
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if(strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }


}		
			
		
