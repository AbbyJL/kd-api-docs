<?php 

//微信小程序支付
use app\common\model\Setting;

class TtPay{
    private $api_url='https://developer.toutiao.com/api/apps/ecpay/v1/';
    private $app_id;
    private $token;
    private $salt;
    private $key;
    private $out_trade_no;
    private $body;
    private $total_fee;
	

    public function __construct($appid,$token,$key,$out_trade_no,$body,$total_fee) {
        $this->app_id = $appid;
        $this->token=$token;
        $this->salt=$key;
		$this->key = $key;
        $this->out_trade_no = $out_trade_no;
        $this->body = $body;
        $this->total_fee = $total_fee;
    }        
        
    public function run(){
         $action=addslashes($_GET['ac']);
         $action=$action?$action:'order';
         if(!in_array($action,['order','query','refund','settle','notify','set'])){
            return false;
        }
        call_user_func(array($this,$action));
    }
    
    //下单
    public function  order(){
        $data=[
            'out_order_no'=>$this->out_trade_no,
            'total_amount'=>$this->total_fee,
            'subject'=>$this->body,
            'body'=>$this->body,
            'valid_time'=>7200,
        ];
        $res=$this->post('create_order',$data);
        return $res;
    }
    
    //查询订单
    public function  query(){
        $data=[
            'out_order_no'=>$this->out_trade_no
        ];
        $res=$this->post('query_order',$data,false);
        return $res;
    }
    
    //订单退款
    public function refund(){
        $data=[
            'out_order_no'=>$this->out_trade_no,
            'out_refund_no'=>$this->order_number(),
            'reason'=>$this->body,
            'refund_amount'=>$this->total_fee,
        ];
        $res=$this->post('create_refund',$data);
        return $res;
    }
    
    //订单分账
    public function settle(){
        $data=[
            'out_order_no'=>$this->out_trade_no,
            'out_settle_no'=>$this->order_number(),
            'settle_desc'=>$this->body,
            'settle_params'=>json_encode([]),//分润方参数 如[['merchant_uid'=>'商户号','amount'=>'10']]  可以有多个分账商户
        ];
        $res=$this->post('settle',$data);
        return $res;
    }
    
  
    
    //回调
   public function notify(){
        $content=file_get_contents('php://input');
        if(empty($content)) return false;
        $this->logs(APP_PATH.'/app/controller/notify.txt',$content);
        $content=json_decode($content,true);
        $sign=$this->handler($content);
        if($sign==$content['msg_signature']){
            $msg=json_decode($content['msg'],true); 
			$res=['err_no'=>0,'err_tips'=>'success','msg'=>$msg];
            return $res;
        }
		return false;
    }
    
    
    /**
    * 测试订单号，实际应用根据自己应用实际生成
    * @return string
    */
    public function order_number(){
        return date('YmdHis').rand(10000,99999);
    }
    
     /**
     * 请求小程序平台服务端
     * @param string $url 接口地址
     * @param array $data 参数内容
     * @param boolean $notify 是否有回调
     * @return array
    */
    private function post($method,$data,$notify=true){
		$config = Setting::config();
		$notifyUrl = $config['site']['host'].'/app/pay/SaveDouyinPayLog';
        $data['app_id']=$this->app_id;
        if(!empty($notify)){
            $data['notify_url']=$notifyUrl;//也可以在调用的时候分别设置
        }
        $data['sign']=$this->sign($data);
        $url=$this->api_url.$method;
        $res=$this->http('POST',$url,json_encode($data),['Content-Type: application/json'],true);
        return json_decode($res,true);
    }
    

    /**
     * 回调验签
     * @param array $map 验签参数
     * @return stirng
    */
    private function handler($map){
        $rList = array();
        array_push($rList, $this->token);
        foreach($map as $k =>$v) {
            if ( $k == "type" || $k=='msg_signature')
                continue;
            $value = trim(strval($v));
            if ($value == "" || $value == "null")
                continue;
            array_push($rList, $value);
        }
        sort($rList,2);
        return sha1(implode($rList));
    }
    
    /**
     * 请求签名
     * @param array $map 请求参数
     * @return stirng
    */
    private function sign($map) {
        $rList = array();
        foreach($map as $k =>$v) {
            if ($k == "other_settle_params" || $k == "app_id" || $k == "sign" || $k == "thirdparty_id")
                continue;
            $value = trim(strval($v));
            $len = strlen($value);
            if ($len > 1 && substr($value, 0,1)=="\"" && substr($value,$len, $len-1)=="\"")
                $value = substr($value,1, $len-1);
            $value = trim($value);
            if ($value == "" || $value == "null")
                continue;
            array_push($rList, $value);
        }
        array_push($rList, $this->salt);
        sort($rList, 2);
        return md5(implode('&', $rList));
    }

     /**
     * 写日志
     * @param string $path 日志路径
     * @param string $content 内容
    */
    private function logs($path='',$content){
        $file=fopen($path,"a");
        fwrite($file, date('Y-m-d H:i:s').'-----'.$content."\n");
        fclose($file);
    }
    
    
    
    
    /**
     * 网络请求
     * @param stirng $method 请求模式
     * @param stirng  $url请求网关
     * @param array $params 请求参数
     * @param stirng  $header 自定义头
     * @param boolean  $multi 文件上传
     * @return array
     */
    private function http( $method = 'GET', $url,$params,$header = array(), $multi = false){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        );
        switch(strtoupper($method)){
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }
    	
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error) throw new Exception('请求发生错误：' . $error);
        return  $data;
    }


}		
			
		
