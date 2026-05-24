<?php

define("ROOT", dirname(dirname(__FILE__)));
require_once(ROOT . "/sdk/builder/ApiBuilder.php");
require_once(ROOT . "/sdk/builder/impl/Session.php");

class DefaultApiBuilder implements ApiBuilder
{
 
    private $_api;
    private $_body;
    private $_headers = array();
    protected $session;
    
    private $_appKey;
    private $_appSecret;
    private $_doMain;
    private $_isSandbox;
    
    public function __construct($appKey, $appSecret, $doMain, $isSandbox)
    {
        $this->_appKey = $appKey;
        $this->_appSecret = $appSecret;
        $this->_doMain = $doMain;
        $this->_isSandbox = $isSandbox;
        
        session_start();
        $this->session = new Session();
    }
    
    public function api($api)
    {
        $this->_api = $api; 
        return $this;
    }
    
    public function body($body)
    {
        $this->_body = $body;
        return $this;
    }
    
    public function header($key, $value)
    {
        $this->_headers[$key] = $value;
        return $this;
    }
    
    protected function initHeader()
    {
        $token = $this->session->get('token');
        $time  = $this->getUnixTimestamp();
        $sign  = $this->getSign($time, $this->_body);
        
        $this->_headers["Content-Type"] = "application/json";
        $this->_headers["token"] = $token;
        $this->_headers["sign"] = $sign;
        $this->_headers["appkey"] = $this->_appKey;
        $this->_headers["method"] = $this->_api;
        $this->_headers["timestamp"] = $time;
        
        $header = array();
        foreach($this->_headers as $key => $value){
            array_push($header, "{$key}:{$value}");
        }
    
        return $header;
    } 
    
    /**
    * 获取token
    * @return
    */
    private function getToken()
    {
        //如果token未过期，则不重新获取
        if (!empty($this->session->get('token'))) {
            return true;
        }
    
        if ($this->_isSandbox) {
            //沙盒环境
            $url = $this->_doMain . "security/sandbox/accessToken";
        } else {
            //正式环境
            $url = $this->_doMain . "security/token";
        }
    
        $postData = [
            'appkey' => $this->_appKey,
            'appsecret' => $this->_appSecret
        ];
        
        $header = [
            'Content-Type:application/json'
        ];
        
        $tokenRes = $this->post($url, $postData, $header);
        
        if ($tokenRes && $tokenRes['success'] && $tokenRes['code'] = 1) {
            $this->session->set('token', $tokenRes['data']['token'], $tokenRes['data']['expire_time']);
        }
        return true;
    }
    
    public function request()
    {
        //如果token失效，重新请求
        if (!$this->session->get('token')) {
            $this->getToken();
        }
        
        $header = $this->initHeader();
        
        if ($this->_isSandbox) {
            //沙盒环境
            $url = $this->_doMain . "sandbox/router/rest";
        } else {
            //正式环境
            $url = $this->_doMain . "router/rest";
        }
        
        $result = $this->post($url, $this->_body, $header);
        
        //重新获取token再调用一次接口
        if ($result['code'] >= 6001 && $result['code'] <= 6003) {
            $this->getToken();
            $header = $this->initHeader();  
            $result = $this->post($url, $this->_body, $header);
        }
        
        return $result;
    }
    
    /**
    * 获取API签名API签名
    * @param string $time 13位时间戳
    * @param string $bizBody 业务参数
    *
    * @return string
    */
    private function getSign($time, $bizBody)
    {
        $appSecret = $this->_appSecret;
        return strtoupper(MD5($appSecret . $time . json_encode($bizBody)));
    }
 
    /**
    * 获取13位的时间戳
    * @return string
    */
    private function getUnixTimestamp()
    {
     list($s1, $s2) = explode(' ', microtime());
     return sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
 
    /**
    * 发送post请求
    * @param string $url 请求地址
    * @param array $postData 数据
    * @param array $header
    * @return array
    */
    private function post($url, $postData, $header)
    {
        if (empty($url) || empty($postData) || empty($header)) {
            return false;
        }
        
        $data_string = json_encode($postData);
        
        $ch = curl_init();
        //要访问的地址
        curl_setopt($ch, CURLOPT_URL, $url);
        //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //post请求
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        //获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        
        //执行操作
        $output = curl_exec($ch);
        
        //关闭CURL会话
        curl_close($ch);
        
        //打印获得的数据
        return json_decode($output, true);
    }
 
}

