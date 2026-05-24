<?php 

use app\common\model\Setting;

class Douyinpay{
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
        $return = $this->unifiedorder();
        return $return;
    }


    private function unifiedorder(){
		$config = Setting::config();
		$outOrderNo = $this->out_trade_no;
        $totalAmount = $this->total_fee;
        $subject = "订单号：".$this->out_trade_no; 
        $body = $this->body;
        $validTimestamp = 60 * 60;
        $notifyUrl = $config['site']['host'].'/app/pay/SaveDouyinPayLog'; //这里可以忽略，走字节跳动小程序 -支付 -担保配置-设置回调地址
        $response = $this->createOrder($outOrderNo, $totalAmount, $subject, $body, $validTimestamp, $notifyUrl);
		return $response;
    }
	
	public function CreateOrder($outOrderNo, $totalAmount, $subject, $body, $validTimestamp, $notifyUrl){
        $params = array(
            'app_id'       => $this->appid,
            'out_order_no' => $outOrderNo,
            'total_amount' => $totalAmount,
            'subject'      => $subject,
            'body'         => $body,
            'valid_time'   => $validTimestamp,
            'notify_url'   => $notifyUrl,
            //'cp_extra' => $cpExtra,
            //'thirdparty_id' => $thirdPartyId,
            //'disable_msg' => $disableMsg,
            //'msg_page' => $msgPage,
            //'store_uid' => $storeUid
        );
 
        $params = array_filter($params);
		
        $params['sign'] = $this->sign($params);
		
		p($params);
		p($this->key);
		
        $res = $this->post(
            'https://developer.toutiao.com/api/apps/ecpay/v1/create_order',
            $params
        );
		p($res);die;
        return $res;
    }
	
	 /**
     * 支付签名
     * @param array $body
     * @param string $secret
     * @return string
     * User: huweikeji
     * Date: 2021/8/1 16:14
     */
    public function getSign(array $body, string $secret){
        $filtered = [];
        foreach ($body as $key => $value) {
            if (in_array($key, ['sign', 'app_id', 'thirdparty_id'])) {
                continue;
            }
 
            $filtered[] =
                is_string($value)
                    ? trim($value)
                    : $value;
        }
 
        $filtered[] = trim($secret);
        sort($filtered, SORT_STRING);
        return md5(trim(implode('&', $filtered)));
    }
	
	
 
    public function getNotifySign(array $body, string $secret){
        $filtered = [];
        foreach ($body as $key => $value) {
            if (in_array($key, ['msg_signature', 'type'])) {
                continue;
            }
            $filtered[] =
                is_string($value)
                    ? trim($value)
                    : $value;
        }
        $filtered[] = trim($secret);
        sort($filtered, SORT_STRING);
        $filtered = trim(implode('', $filtered));
 
        return sha1($filtered);
    }


	public function notify(){
        $notify = \request()->param();
 
        if ($notify['msg_signature'] !== $this->getNotifySign($notify,$this->appid)) {
            Log::info('回调验证错误');
        } else {
            //获取订单信息
            $order = json_decode($notify['msg'], true);
            //处理订单
            $data = ['order_sn' => $order['cp_orderno']];
            Log::info('抖音担保支付效验成功');
        }
        $data = ['err_no' => '0', 'err_tips' => 'success'];
        return json($data);
    }

	
	
    public function post(string $url, array $params = [], array $headers = []){
        $headers[] = 'Content-type: application/json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

	
	

	public function sign($map) {
		$rList = array();
		foreach($map as $k =>$v) {
			if($k == "other_settle_params" || $k == "app_id" || $k == "sign" || $k == "thirdparty_id")
				continue;
			$value = trim(strval($v));
			if(is_array($v)){
			  $value = $this->arrayToStr($v);
			}
			$len = strlen($value);
			if ($len > 1 && substr($value, 0,1)=="\"" && substr($value, $len-1)=="\"")
				$value = substr($value,1, $len-1);
			$value = trim($value);
			if ($value == "" || $value == "null")
				continue;
			$rList[] = $value;
		}
		$rList[] = $this->key;
		sort($rList, SORT_STRING);
		return md5(implode('&', $rList));
	}
	
	public function arrayToStr($map) {
	  $isMap = $this->isArrMap($map);
	
		$result = "";
		if ($isMap){
		  $result = "map[";
		}
	
		$keyArr = array_keys($map);
		if ($isMap) {
			sort($keyArr);
		}
	
		$paramsArr = array();
		foreach($keyArr as  $k) {
		  $v = $map[$k];
		  if ($isMap) {
			if (is_array($v)) {
			  $paramsArr[] = sprintf("%s:%s", $k, arrayToStr($v));
			} else  {
			  $paramsArr[] = sprintf("%s:%s", $k, trim(strval($v)));
			}
		  } else {
			if (is_array($v)) {
			  $paramsArr[] = arrayToStr($v);
			} else  {
			  $paramsArr[] = trim(strval($v));
			}
		  }
		}
	
		$result = sprintf("%s%s", $result, join(" ", $paramsArr));
		if (!$isMap) {
		  $result = sprintf("[%s]", $result);
		} else {
		  $result = sprintf("%s]", $result);
		}
	
		return $result;
	}
	
	public function isArrMap($map) {
		foreach($map as $k =>$v) {
		  if (is_string($k)){
			  return true;
		  }
		}
		return false;
	}




	function json_post($url, $data = NULL){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		if(!$data){
			return 'data is null';
		}
		if(is_array($data)){
			$data = json_encode($data,320);
		}
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8',
			'Content-Length:' . strlen($data),
			'Cache-Control: no-cache',
			'Pragma: no-cache'
		));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$res = curl_exec($curl);
		curl_close($curl);
		return $res;
	}



}		
			
		
