<?php




class Alipay2{
  protected $appId;
  protected $returnUrl;
  protected $notifyUrl;
  //私钥文件路径
  protected $rsaPrivateKeyFilePath;
  //私钥值

  protected $rsaPrivateKey;

  public function __construct($appid, $returnUrl, $notifyUrl,$saPrivateKey){
    $this->appId = $appid;
    $this->returnUrl = $returnUrl;
    $this->notifyUrl = $notifyUrl;
    $this->charset = 'utf8';
    $this->rsaPrivateKey=$saPrivateKey;
  }



  /**
   * 发起订单
   * @param float $totalFee 收款总费用 单位元
   * @param string $outTradeNo 唯一的订单号
   * @param string $orderName 订单名称
   * @param string $notifyUrl 支付结果通知url 不要有问号
   * @param string $timestamp 订单发起时间
   * @return array
   */


  public function doPay($totalFee, $outTradeNo, $orderName, $returnUrl,$notifyUrl){
    //请求参数
    $requestConfigs = array(
      'out_trade_no'=>$outTradeNo,
      'total_amount'=>$totalFee, //单位 元
      'subject'=>$orderName, //订单标题
    );
    $commonConfigs = array(
      //公共参数
      'app_id' => $this->appId,
      'method' => 'alipay.trade.precreate',//接口名称
      'format' => 'JSON',
      'charset'=>$this->charset,
      'sign_type'=>'RSA2',
      'timestamp'=>date('Y-m-d H:i:s'),
      'version'=>'1.0',
      'notify_url' => $notifyUrl,
      'biz_content'=>json_encode($requestConfigs),
    );
	
	//p($commonConfigs);
	
	
    $commonConfigs["sign"] = $this->generateSign($commonConfigs, $commonConfigs['sign_type']);
    $result = $this->curlPost('https://openapi.alipay.com/gateway.do',$commonConfigs);//正式环境
	//p($result);die;
    return json_decode($result,true);

  }



  public function generateSign($params, $signType = "RSA"){
    return $this->sign($this->getSignContent($params), $signType);

  }


  protected function sign($data, $signType = "RSA"){
    $priKey=$this->rsaPrivateKey;
    $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
      wordwrap($priKey, 64, "\n", true) .
      "\n-----END RSA PRIVATE KEY-----";
    ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
    if("RSA2" == $signType){
      openssl_sign($data, $sign, $res, version_compare(PHP_VERSION,'5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256); //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
    }else{
      openssl_sign($data, $sign, $res);
    }
    $sign = base64_encode($sign);
    return $sign;

  }

  /**
   * 校验$value是否非空
   * if not set ,return true;
   *  if is null , return true;
   **/

  protected function checkEmpty($value){
    if(!isset($value))
      return true;
    if($value === null)
      return true;
    if(trim($value) === "")
      return true;
    return false;

  }


  public function getSignContent($params){
    ksort($params);
    $stringToBeSigned = "";
    $i = 0;
    foreach($params as $k => $v){
      if(false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)){
        //转换成目标字符集
        $v = $this->characet($v, $this->charset);
        if($i == 0){
          $stringToBeSigned .= "$k" . "=" . "$v";
        }else{
          $stringToBeSigned .= "&" . "$k" . "=" . "$v";
        }
        $i++;
      }
    }
    unset ($k, $v);
    return $stringToBeSigned;

  }

  /**
   * 转换字符集编码
   * @param $data
   * @param $targetCharset
   * @return string
   */

  function characet($data, $targetCharset){
    if(!empty($data)){
      $fileType = $this->charset;
      if(strcasecmp($fileType, $targetCharset) != 0){
        $data = mb_convert_encoding($data, $targetCharset, $fileType);
        //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
      }
    }
    return $data;
  }





  public function curlPost($url = '', $postData = '', $options = array()){
    if(is_array($postData)){
      $postData = http_build_query($postData);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
    if(!empty($options)){
      curl_setopt_array($ch, $options);
    }
    //https请求 不验证证书和host
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
  }
	
	

	
}	

