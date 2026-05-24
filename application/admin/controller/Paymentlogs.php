<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Paymentlogs extends Base{
	
	public function _initialize() {
        parent::_initialize(); 
		$this->assign('types', $types = model('PaymentLogs')->getType());
		
		$this->codes =  model('PaymentLogs')->getcode();
		$this->assign('codes',$this->codes);
    }
	
    public function index(){
		$map = array();
		
		if($p = input('p','','htmlspecialchars')){
            $this->assign('p',$p);
        }
		
        $getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		
		$getSearchCityId = $this->getSearchCityId($this->city_id);
		if($getSearchCityId){
			$map['city_id'] = $getSearchCityId;
			$this->assign('city_id',$getSearchCityId);
		}
		
        if($user_id = (int) input('user_id')){
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
		
		if($type = input('type')){
            if(!empty($type) && $type !=999){
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        }else{
            $this->assign('type', 999);
        }
		
		if($code = input('code','', 'htmlspecialchars')){
            if(!empty($code) && $code !=999 ){
                $map['code'] = $code;
            }
            $this->assign('code', $code);
        }else{
            $this->assign('code', 999);
        }
		
		if($status = input('status','', 'htmlspecialchars')){
            if($status == 1){
                $map['is_paid'] = 1;
            }else{
				$map['is_paid'] = 0;
			}
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
        }
		
		
        if($keyword = input('keyword','', 'htmlspecialchars')){
            $map['log_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if($order_id = input('order_id','', 'htmlspecialchars')){
            $map['order_id'] = array('LIKE', '%' . $order_id . '%');
            $this->assign('order_id', $order_id);
        }
		if($return_order_id = input('return_order_id','', 'htmlspecialchars')){
            $map['return_order_id'] = array('LIKE', '%' . $return_order_id . '%');
            $this->assign('return_order_id', $return_order_id);
        }
		if($return_trade_no = input('return_trade_no','', 'htmlspecialchars')){
            $map['return_trade_no'] = array('LIKE', '%' . $return_trade_no . '%');
            $this->assign('return_trade_no', $return_trade_no);
        }
		
        $count = Db::name('payment_logs')->where($map)->count();
        $Page = new \Page($count, 15);
        $show = $Page->show();
        $list = Db::name('payment_logs')->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
            $list[$k]['type'] = model('PaymentLogs')->get_payment_logs_type($val['type']);
			$list[$k]['user'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
			$list[$k]['city'] = Db::name('city')->where(array('city_id'=>$val['city_id']))->find();
			$list[$k]['area'] = Db::name('area')->where(array('area_id'=>$val['area_id']))->find();
			$list[$k]['business'] = Db::name('business')->where(array('business_id'=>$val['business_id']))->find();
        }
		
		$map2 = $map;
		$map2['is_paid'] = 0;
	
		
		$this->assign('money_is_paid_0',$money_is_paid_0 = Db::name('payment_logs')->where($map2)->sum('need_pay'));
		
		
		$map3 = $map;
		$map3['is_paid'] = 1;
		$this->assign('money_is_paid_1',$money_is_paid_0 = Db::name('payment_logs')->where($map3)->sum('need_pay'));
		
		$map4 = $map;
		$map4['is_paid'] = 0;
		$this->assign('sum_0', $sum = Db::name('payment_logs')->where($map4)->sum('need_pay'));
		
		$map5 = $map;
		$map5['is_paid'] = 1;
		$this->assign('sum_1', $sum = Db::name('payment_logs')->where($map5)->sum('need_pay'));
		
		
        $this->assign('list', $list);
        $this->assign('page', $show);
		session('payment_logs_map',$map);
        return $this->fetch();
    }
	
	//确认订单完成
	public function confirm($log_id = 0,$p = 0){
		$log_id = (int) $log_id;
        if(!$log_id){
			$this->jinMsg('log_id不存在');
		}
		$detail = Db::name('payment_logs')->where(array('log_id'=>$log_id))->find();
		if(!$detail){
			$this->jinMsg('信息不存在');
		}
		if($detail['is_paid'] == 1){
			$this->jinMsg('支付状态不正确');
		}
		
		
		$logsPaid = model('Payment')->logsPaid($log_id,'0','0');//3参数回调
		
		if($logsPaid){
			$this->jinMsg('操作成功', url('paymentlogs/index'));
		}else{
			$this->jinMsg('操作失败');
		}
    }
	
	
	
	public function detail($log_id = 0,$p = 0){
		$var = Db::name('payment_logs')->find($log_id);
        $var['type'] = model('PaymentLogs')->get_payment_logs_type($val['type']);
		$var['user'] = Db::name('users')->find($val['user_id']);
		
		
		$photos = unserialize($var['photos']);
        $this->assign('photos', $photos);
		
		$this->assign('var',$var);
        echo $this->fetch();
		
	}
	
	
	
	
	
	public function query($log_id = 0,$p = 0){
		
        $var = Db::name('payment_logs')->find($log_id);
        $var['type'] = model('PaymentLogs')->get_payment_logs_type($val['type']);
		$var['user'] = Db::name('users')->find($val['user_id']);
	
		//微信查账
		if($var['code'] == 'weixin' || $var['code'] == 'weixinh5' || $var['code'] == 'native' || $var['code'] == 'wxapp'){
			
			//查询开始
			include(ROOT_PATH . 'extend/Payment/WxPayPubHelper/WxPayPubHelper.php');
			$payment = model('Payment')->getPayment('weixin');
			$Redpack = new \OrderQuery_pub();
			$Redpack->setParameter('appid',$payment['appid']);
			$Redpack->setParameter('mch_id',$payment['mchid']);
			
			if($var['sub_mch_id']){
				$Redpack->setParameter('sub_mch_id',$var['sub_mch_id']);
			}
			
			
			if($var['return_order_id']){
				$Redpack->setParameter('out_trade_no',$var['return_order_id']);//商户订单号
			}
			if($var['return_trade_no']){
				$Redpack->setParameter('transaction_id',$var['return_trade_no']);//微信支付订单号
			}
			$result = $Redpack->getResult();
			//查询结束
			$list = $result;
		}elseif($var['code'] == 'alipay' ){
			
			
			
			$payment = model('Payment')->getPayment('alipay');
			include (ROOT_PATH . 'extend/Payment/alipay/AopClient.php');
			include (ROOT_PATH . 'extend/Payment/alipay/request/AlipayTradeQueryRequest.php');
			
			
			$aop = new \AopClient ();
			$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
			$aop->appId = $payment['alipay_app_id'];
			$aop->rsaPrivateKey = $payment['alipay_private_key'];
			$aop->alipayrsaPublicKey=$payment['alipay_rsa_public_key'];
			$aop->apiVersion = '1.0';
			$aop->signType = 'RSA2';
			$aop->postCharset='utf-8';
			$aop->format='json';
			
			$org_pid = '';
			$return_trade_no =  $var['return_trade_no'] ? $var['return_trade_no'] : $log_id;
			
			
			$request = new \AlipayTradeQueryRequest();
			$request->setBizContent("{" .
				"\"out_trade_no\":\"{$return_trade_no}\"," .
				"\"trade_no\":\"{$var['return_order_id']}\"," .
				"\"org_pid\":\"{$org_pid}\"," .
				"      \"query_options\":[" .
				"        \"TRADE_SETTE_INFO\"" .
				"      ]" .
				"  }");
			$result = $aop->execute($request); 
			
			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;
			$result = (array)$result->$responseNode;
			
			$list = $result;

			if(!empty($resultCode)&&$resultCode == 10000){
				$logs = Db::name('payment_logs')->where(array('log_id'=>$log_id))->find();
				
				//如果支付宝没回调
				if($logs['is_paid'] == 0){
					model('Payment')->logsPaid($log_id,$result['trade_no'],$result['out_trade_no']);
				}
			}	
			
		}else{
			$list = array('msg'=>'暂不支持查询');
		}
		
		$this->assign('var',$var);
   		$this->assign('list',$list);
        echo $this->fetch();
    }
	
	
	
	//支付列表导出
    public function export(){
		$NAME = '支付日志';
        $arr = Db::name('payment_logs')->where($_SESSION['payment_logs_map'])->order(array('log_id'=>'desc'))->select();
        $date = date("Y_m_d", time());
        $filetitle = $NAME."日志列表";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array(
			'aa' => '日志编号', 
			'bb' => '年', 
			'cc' => '月', 
			'dd' => '日', 
			'ee' => '日志生成时间', 
			'ff' => '会员ID', 
			'gg' => '会员姓名', 
			'hh' => '订单ID', 
			'ii' => '订单类型', 
			'jj' => '支付金额', 
			'kk' => '支付状态', 
			'll' => '支付时间', 
			'mm' => '支付类型',
			'nn' => '支付IP',   
			'oo' => '返回订单号', 
			'kk' => '返回交易号', 
		);
        foreach($filter as $key => $title){
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach($arr as $k => $v){
            $Users = Db::name('users')->find($v['user_id']);
            $createTime = date('H:i:s', $v['create_time']);
            $createTimeYear = date('Y', $v['create_time']);
            $createTimeMonth = date('m', $v['create_time']);
            $createTimeDay = date('d', $v['create_time']);
			
            $filter = array(
				'aa' => '日志编号', 
				'bb' => '年', 
				'cc' => '月', 
				'dd' => '日', 
				'ee' => '日志生成时间', 
				'ff' => '会员ID', 
				'gg' => '会员姓名', 
				'hh' => '订单ID', 
				'ii' => '订单类型', 
				'jj' => '支付金额', 
				'kk' => '支付状态', 
				'll' => '支付时间', 
				'mm' => '支付类型',
				'nn' => '支付IP',   
				'oo' => '返回订单号', 
				'kk' => '返回交易号', 
			);
            $arr[$k]['aa'] = $v['log_id'];
            $arr[$k]['bb'] = $createTimeYear;
            $arr[$k]['cc'] = $createTimeMonth;
            $arr[$k]['dd'] = $createTimeDay;
            $arr[$k]['ee'] = $createTime;
            $arr[$k]['ff'] = $v['user_id'];
            $arr[$k]['gg'] = $Users['nickname'];
            $arr[$k]['hh'] = $v['order_id'];
            $arr[$k]['ii'] = model('PaymentLogs')->get_payment_logs_type($v['type']);
            $arr[$k]['jj'] = round($v['need_pay']/100,2);
            $arr[$k]['kk'] = $v['is_paid'] == 1 ? '已支付' : '未支付';
            $arr[$k]['ll'] = date('Y-m-d H:i:s', $v['pay_time']);
			$arr[$k]['mm'] = $this->codes[$v['type']];
			$arr[$k]['nn'] = $v['pay_ip'];
			$arr[$k]['oo'] = $v['return_order_id'];
			$arr[$k]['kk'] = $v['return_trade_no'];
            foreach($filter as $key => $title){
                $html .= $arr[$k][$key] . "\t,";
            }
            $html .= "\n";
        }
        ob_end_clean();
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename={$fileName}.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
		session('payment_logs_map',null);
        echo $html;
        exit;
    }
}