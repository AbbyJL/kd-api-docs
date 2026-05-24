<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Order extends Base{


	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->host = $this->config['site']['host'];
		$this->curl = new \Curl();
    }
	
	
	public function getCompanyTypes(){
        return array(
			'sto' => '申通快递',
			'zt' => '中通快递',
			'yt' => '圆通速递',
			'yd' => '韵达快递',
			'ys' => '优速快递'
		);
    }
	
	//PHP获取http请求的头信息
	public function getallheaders(){ 
       foreach($_SERVER as $name =>$value){ 
           if(substr($name,0,5) == 'HTTP_'){ 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
	
	public function getUserId(){
		$token = input('token','','trim,htmlspecialchars');
		$user_id = Db::name('users')->where(array('token'=>$token))->value('user_id');
		return (int)$user_id;
	}
	
	public function file_get_contents_input(){
		if(isset($GLOBALS['HTTP_RAW_POST_DATA'])) { 
		 	$input = $GLOBALS['HTTP_RAW_POST_DATA']; 
		}else{ 
		 	$input = file_get_contents('php://input'); 
		}
		$input = json_decode($input,true);
		return $input;
	}
	
	
	
	public function getTj(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$day = date('Ymd',time());
		$data['status_0'] =(int)Db::name('express_order')->where(array('orderStatus'=>0,'closed'=>0,'is_company'=>3,'day'=>$day))->count();
		$data['status_1'] =(int)Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3,4)),'closed'=>0,'is_company'=>3,'day'=>$day))->count();
		
		$money_0 =(int)Db::name('express_order')->where(array('orderStatus'=>0,'closed'=>0,'is_company'=>3,'day'=>$day))->sum('sumMoneyYuan');
		$money_1 =(int)Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3,4)),'closed'=>0,'is_company'=>3,'day'=>$day))->sum('sumMoneyYuan');

		$data['money_0'] =round($money_0/100,2);
		$data['money_1'] =round($money_1/100,2);
		return json(array('code'=>'1','time'=>time(),'msg'=>'操作成功','data'=>$data));
	}

    public function getUser(){
        $uid = $this->getUserId();
        if(!$uid){
            return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
        }
        $is_tuihuo = Db::name('users')->where(array('user_id'=>$uid))->value('is_tuihuo');
        return json(array('code'=>'1','msg'=>'操作成功','data'=>$is_tuihuo));
    }


    public function getOrderList(){
        $uid = $this->getUserId();
        if(!$uid){
            return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
        }
        $list = Db::name('express_order_tuihuo')->where(array('user_id'=>$uid))->order('id desc')->limit(0,10)->select();
        foreach($list as $k=>$v){
            $list[$k]['time'] = date('m-d H:i',$v['create_time']);
        }
        return json(array('code'=>'1','msg'=>'操作成功','data'=>$list));
    }

    public function submitOrder(){
        $uid = $this->getUserId();
        if (!$uid) {
            return json(array('code' => 0, 'time' => time(), 'msg' => 'TOKEN失效', 'data' => ''));
        }
        $img = input('img', '', 'trim,htmlspecialchars');
        $waybillNo = input('waybillNo', '', 'trim,htmlspecialchars');
        $banci = input('banci', '', 'trim,htmlspecialchars');
        $info = input('info', '', 'trim,htmlspecialchars');
        $data['waybillNo'] = $waybillNo;
        $data['img'] = $img;
        $data['user_id'] = $uid;
        $data['banci'] = $banci;
        $data['info'] = $info;
        $data['create_time'] = time();
        $data['year'] = date('Y',time());
        $data['month'] = date('Ym',time());
        $data['day'] = date('Ymd',time());
        $r = Db::name('express_order_tuihuo')->insertGetId($data);
        $data['tuihuo_id'] = $r;
        if($r){
            return json(array('code'=>'1','time'=>time(),'msg'=>'上传订单成功','data'=>$data));
        }
        return json(array('code'=>0,'time'=>time(),'msg'=>'上传订单失败','data'=>''));
    }



	public function add(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		
		$deliveryId = input('deliveryId','','trim,htmlspecialchars');
		$img = input('img','','trim,htmlspecialchars');
		
		$sumMoneyYuan = input('sumMoneyYuan','','trim,htmlspecialchars');
		if($sumMoneyYuan<=0){
			return json(array('code'=>0,'time'=>time(),'msg'=>'请填写支付金额','data'=>''));
		}
		$receiveName = input('receiveName','','trim,htmlspecialchars');
		$receiveMobile = input('receiveMobile','','trim,htmlspecialchars');
		$remark = input('remark','','trim,htmlspecialchars');
		$remark = input('remark','','trim,htmlspecialchars');
		$wight = input('wight','','trim');
		$order = input('order','','trim');
		$order =json_decode($order,true);
		
		$data = $order;
		if($data['user_id']==''){
			return json(array('code'=>0,'time'=>time(),'msg'=>'收件人手机号【'.$data['receiveMobile'].'】未识别到用户','data'=>''));
		}
		$u = Db::name('users')->where(array('user_id'=>$data['user_id']))->find();
		if(!$u){
			return json(array('code'=>0,'time'=>time(),'msg'=>'收件用户不存在','data'=>''));
		}
		$data['pid'] = $u['parent_id']?$u['parent_id']:0;
		$data['smail_id'] = 0;
		$data['rmail_id'] = 0;
		$data['rank1_uid'] = 0;
		$data['rank2_uid'] = 0;
		$data['rank3_uid'] = 0;
		$data['discountmoney'] =0;
		$data['discountmoney'] =0;
		$data['wight'] = $wight;//重量
		$data['expressId'] = $deliveryId?$deliveryId:$data['deliveryId'];
		$data['expressNo'] = 0;
		$data['sumMoneyYuan'] = $sumMoneyYuan*100;
		$data['remark'] = $remark;
		$data['closed'] = 0;
		$data['is_company'] = 3;
		$data['orderStatus'] = 0;
		$data['create_time'] = time();
		$data['createTime'] = time();
		$data['year'] = date('Y',time());
		$data['month'] = date('Ym',time());
		$data['day'] = date('Ymd',time());
		
		$r = Db::name('express_order')->insertGetId($data);
		if($r){
			model('Sms')->sendSmsPaySend($data,$data['user_id'],$title = '待支付运单提醒');//待支付运单提醒
			model('WeixinTmpl')->getWeixinTmplSend($data,$data['user_id'],$title = '待支付运单提醒');
			$dingTalkWebhook = model('Ad')->dingTalkWebhook($dd_msg=9,'自动上传单号【'.$data['expressId'].'】订单未付款请登陆小程序支付',$data['receiveMobile']);//钉钉通知
			return json(array('code'=>'1','time'=>time(),'msg'=>'上传订单成功','data'=>$data));
		}
		return json(array('code'=>0,'time'=>time(),'msg'=>'上传订单失败','data'=>''));
	}
	
	
}
