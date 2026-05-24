<?php

use think\Db;
use think\Cache;
use app\common\model\Setting;

class Dada{
	
	protected $app;
	protected $app_secret;
	public $config = array();


	public function __construct(){
		$config = Setting::config();
		
		$this->config = $config['ele'];
		$this->app = array();
		$this->app_secret = $this->config['appsecret'];
		$api_urls = array('open' => 'http://newopen.imdada.cn', 'sandbox' => 'http://newopen.qa.imdada.cn');
		$this->api_url = $this->config['dada_bug'] == 1 ? $api_urls['sandbox'] : $api_urls['open'];
		$this->curl = new \Curl();
		
	}


	public function buildParams($body){
		$params = array('app_key' => $this->config['appkey'], 'source_id' => $this->config['sourceid'], 'body' => '', 'format' => 'json', 'timestamp' => time(), 'v' => '1.0');
		$params['body'] = json_encode($body);
		$params['signature'] = $this->buildSign($params);
		return $params;
	}


	public function buildSign($params){
		ksort($params);
		$str = '';

		foreach ($params as $key => $val) {
			$str .= $key . $val;
		}

		$str = $this->app_secret . $str . $this->app_secret;
		$sign = strtoupper(md5($str));
		return $sign;
	}


	public function httpPost($action, $params = ''){
		$buildparams = $this->buildParams($params);
		$response = ihttp_request($this->api_url . $action, json_encode($buildparams), array('Content-Type' => 'application/json'));

		if (is_error($response)) {
			return error('-2', '请求接口出错:' . $response['message']);
		}

		$result = @json_decode($response['content'], true);

		if ($result['status'] == 'fail') {
			return error(-1, $result['errorCode'] . ': ' . $result['msg'] . ',');
		}

		return $result['result'];
	}

	public function queryCityCode(){
		$response = $this->httpPost('/api/cityCode/list', '');
		return $response;
	}


	public function buildBodyParams($id){
		
		$config = Setting::config();
		
		$order = Db::name('ele_order')->where(array('order_id'=>$id))->find();
		$addr = Db::name('user_addr')->where(array('addr_id' =>$order['addr_id']))->find();
	
	    $dada = model('Ele')->store_get_data($order['shop_id'],'dada');
	 
		
		$params = array(
			'shop_no' => $this->config['dada_bug'] == 1 ? '11047059' : $dada['shop_no'],
			'origin_id' => $id, 
			'city_code' => $this->config['dada_bug'] == 1 ? '023' : $dada['citycode'],
			'cargo_price' => round($order['meed_pay']/100,2), 
			'is_prepay' => 0, 
			'expected_fetch_time' => time() + 60 * 10, 
			'receiver_name' => $addr['name'], 
			'receiver_address' => $addr['addr'], 
			'receiver_phone' => $addr['mobile'], 
			'receiver_lat' => $addr['lat'], 
			'receiver_lng' => $addr['lng'], 
			'info' => $order['message'], 
			'callback' => $config['site']['host'].'/app/dada/start'
		);
		
		//p($params);die;
		
		return $params;
	}



	public function queryDeliverFee($id){
		$params = $this->buildBodyParams($id);
		$response = $this->httpPost('/api/order/queryDeliverFee', $params);

		if (is_error($response)) {
			return error(-1, '达达订单编号获取失败,原因:' . $response['message']);
		}

		model('Ele')->set_order_data($id, 'dada.deliveryno', $response['deliveryNo']);
		return $response;
	}


	public function addAfterQuery($id){
		$deliveryNo = model('Ele')->get_order_data($id, 'dada.deliveryno');
		$params = array('deliveryNo' => $deliveryNo);
		$response = $this->httpPost('/api/order/addAfterQuery', $params);
		return $response;
	}


	public function orderAccept($id){
		
		$order = Db::name('ele_order')->where(array('order_id'=>$id))->find();
		
		$params = array('order_id' => $order['order_id']);
		$response = $this->httpPost('/api/order/accept', $params);
		
		return $response;
	}
	
	//模拟完成取货
	public function orderFetch($id){
		
		$order = Db::name('ele_order')->where(array('order_id'=>$id))->find();
		
		$params = array('order_id' => $order['order_id']);
		$response = $this->httpPost('/api/order/fetch', $params);
		return $response;
	}
	
	//模拟完成订单
	public function orderFinish($id){
		
		$order = Db::name('ele_order')->where(array('order_id'=>$id))->find();
		
		$params = array('order_id' => $order['order_id']);
		$response = $this->httpPost('/api/order/finish', $params);
		return $response;
	}
	
	//模拟取消订单
	public function orderCancel($id){
		
		$order = Db::name('ele_order')->where(array('order_id'=>$id))->find();
		
		$params = array('order_id' => $order['order_id']);
		$response = $this->httpPost('/api/order/cancel', $params);
		return $response;
	}
	

	public function orderDetailQuery($id){
		$order = Db::name('ele_order')->where(array('order_id'=>$id))->find();
		
		$params = array('order_id' => $order['order_id']);
		$response = $this->httpPost('/api/order/status/query', $params);
		return $response;
	}


	public function addOrder($id){
		$params = $this->buildBodyParams($id);
		$response = $this->httpPost('/api/order/addOrder', $params);
		return $response;
	}


	public function cancelReason(){
		$response = $this->httpPost('/api/order/cancel/reasons', '');
		return $response;
	}

	public function cancelOrder($id){
		
		$order = Db::name('ele_order')->where(array('order_id'=>$id))->find();
		
		$params = array('order_id' => $order['order_id'], 'cancel_reason_id' => 10000, 'cancel_reason' => '取消订单');
		$response = $this->httpPost('/api/order/formalCancel', $params);
		return $response;
	}
}

