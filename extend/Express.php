<?php
class Express{
	public $keys = '';
	public $company = '';
	public $customer = '';
	public $num = '0';


	public function getContent($type = ''){
		
		if($type == 1){
			$typeCom = trim($this->company);
			$typeNu = $this->num;
			$AppKey=$this->keys;
			$url ='http://api.kuaidi100.com/api?id='.$AppKey.'&com='.$typeCom.'&nu='.$typeNu.'&show=2&muti=1&order=asc';
			$powered = '查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 ';
			if(function_exists('curl_init') == 1){
				  $curl = curl_init();
				  curl_setopt($curl, CURLOPT_URL, $url);
				  curl_setopt($curl, CURLOPT_HEADER,0);
				  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				  curl_setopt($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
				  curl_setopt($curl, CURLOPT_TIMEOUT,5);
				  $get_content = curl_exec($curl);
				  curl_close($curl);
			}
			$res = $get_content . '<br>' . $powered;
			return $res;
		}else{
			$post_data = array();
			$post_data["customer"] = $this->customer;
			$key= $this->keys;
			$post_data["param"] = '{"com":"'.trim($this->company).'","num":"'.$this->num.'"}';
			$url='https://poll.kuaidi100.com/poll/query.do';
			$post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
			$post_data["sign"] = strtoupper($post_data["sign"]);
			$o=""; 
			foreach($post_data as $k=>$v){
				$o.= "$k=".urlencode($v)."&";
			}
			$post_data=substr($o,0,-1);
			$this->curl = new \Curl();
			$result = $this->curl->post($url,$post_data);
			$result = json_decode($result,true);
			if($result['message'] == 'ok'){
				$str .= '<p class="express-time" style="color:#f00;">快递单号：'.$this->num.'</p>';
				foreach($result['data'] as $k =>$val){
					$str .= '<p class="express-time">时间：'.$val['time'] .'</p>';
					$str .= '<p class="express-ftime">更新时间：'.$val['ftime'].'</p>';
					$str .= '<p class="express-context">说明：'.$val['context'].'</p>';
                }
				return $str;
			}else{
				return '查询数据错误'.$result['message'];
			}
		}
	}

}