<?php

namespace app\common\model;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Payment extends Base{


    protected $pk = 'payment_id';
    protected $tableName = 'payment';
    protected $token = 'payment';
    protected $types = array(
        'express' => '订单支付',
		'rank' => '等级购买',
		'money' => '余额充值',
		'coupon' => '优惠券购买',
    );

    protected $type = null;
    protected $log_id = null;
    public function getType(){
        return $this->type;
    }

    public function getLogId(){
        return $this->log_id;
    }

    public function getTypes(){
        return $this->types;
    }


   //默认支付方式
    public function getPayments($mobile = false,$user_id = 0,$need_pay = 0){
       
		
		$datas = Db::name('payment')->where(array('is_open'=>1))->order('orderby asc')->select();
		
		
        $return = array();
		
        foreach($datas as $val){
            if($val['is_open']){
                if($mobile == false){
                    if(!$val['is_mobile_only'])
                        $return[$val['code']] = $val;
                }else{
                   if($val['code'] != 'tenpay' && $val['code'] != 'native' && $val['code'] != 'micro'){
                      $return[$val['code']] = $val;
                    }
                }
            }
        }
		
		
        //手机浏览器
        if(!is_weixin()){
            unset($return['weixin'],$return['wxapp']);
        }
		
		//小程序
		$miniprogram = (int)cookie('miniprogram');
		
		//微信
        if(is_weixin() && $miniprogram == 2){
            unset($return['native'],$return['weixinh5'],$return['wxapp']);
        }
		
		//小程序
		if(is_weixin() && $miniprogram == 1){
            unset($return['alipay'],$return['weixin'],$return['native'],$return['weixinh5']);
        }
		
		
		//小程序判断某种错误
		if(is_weixin() && $miniprogram == 0){
            unset($return['native'],$return['weixinh5'],$return['wxapp']);
        }
		
		if($user_id){
			$users = Db::name('users')->where(array('user_id'=>$user_id))->find();
			if($users['money'] < $need_pay){
				unset($return['money']);
			}else{
				foreach($return as $k2=>$v2){
				   $return[$k2]['money'] = round($users['money']/100,2); 
				}
			}
		}
        return $return;
    }
	
	

	//智能支付,是否手机版，支付金额，会员ID
	public function getNoopPayments($mobile = false,$money,$user_id){
        $datas = $this->fetchAll();
        $return = array();
        foreach($datas as $val){
            if($val['is_open']){
                if($mobile == false){
                    if(!$val['is_mobile_only'])
                        $return[$val['code']] = $val;
                }else{
                   if($val['code'] != 'tenpay' && $val['code'] != 'native' && $val['code'] != 'micro'){
                      $return[$val['code']] = $val;
                   }
                }
            }
        }
        if(!is_weixin()){
            unset($return['weixin']);
        }
        if(is_weixin()){
            unset($return['alipay']);
        }
		$Users = Db::name('users')->find($user_id);
		if($Users['money'] < $money){
			unset($return['money']);
		}
        return $return;
    }


	//获取用户应该用什么支付方式
	public function getUserPaymentCode($user_id,$mobile = true){

		//小程序判断
		$miniprogram = (int)cookie('miniprogram');

		$iscode = 'weixin';

        if(is_weixin() && $miniprogram == 2){
            $iscode = 'weixin';//微信公众号
        }elseif(is_weixin() && $miniprogram == 1){
            $iscode = 'wxapp';//微信小程序
        }else{


			//手机浏览器
			$ctl = strtolower(__JINTAO_CONTROLLER__);

			$logs = Db::name('payment_logs')->where(array('user_id'=>$user_id,'is_paid'=>1))->find();
			if($mobile && $logs && $logs['code'] != 'wxapp' && $logs['code'] != 'weixin' && $logs['code'] != 'native'){

				$iscode = $logs['code'];//手机浏览器

				if($ctl == 'money' && $iscode == 'money'){
					$iscode = 'weixinh5';//如果是手机浏览器充值调用H5支付
				}

			}elseif($mobile == false && $logs && $logs['code'] != 'wxapp' && $logs['code'] != 'weixin' && $logs['code'] != 'weixinh5'){

				$iscode = $logs['code'];//PC浏览器
				if($ctl == 'money' && $iscode == 'money'){
					$iscode = 'alipay';//如果是PC浏览器
				}
			}else{
				$config = Setting::config();//调用全局设置

				if($mobile == false){
					if($iscode == 'weixin'){
						$iscode = 'alipay';//PC如果是微信支付修改为电脑支付
					}
				}
			}
		}

		return $iscode;
    }



    public function _format($data){
        $data['setting'] = unserialize($data['setting']);
        return $data;
    }


	//支付方式include回调实例化
	public function respond($code){
        $payment = $this->checkPayment($code);
        if(empty($payment))
            return false;
		if($code == 'native' || $code == 'micro'){
			include(ROOT_PATH . 'extend/Payment/'.$code.'.weixin'.'.class.php');//扫码支付
		}elseif(defined('IN_MOBILE')){
            include(ROOT_PATH . 'extend/Payment/'.$code.'.mobile.class.php');
        }else{
            include(ROOT_PATH . 'extend/Payment/'.$code.'.class.php');
        }
        $obj = new $code();
        return $obj->respond();
    }

   //查询sub_mch_id
   public function getShopSubMchId($logs){
	    $sub_mch_id = '';
		//查询商家
	   
		if($logs['type'] == 'weixin' || $logs['type'] == 'weixin' || $logs['type'] == 'native' || $logs['type'] == 'weixinh5'){
			$payment = $this->getPayment($logs['code']);
			//是否开启子商户
			if($payment['is_sub_mch_id'] == 1){
				$sub_mch_id = $shop['sub_mch_id'];

				$data['sub_mch_id'] = $sub_mch_id;
                $data['mch_id'] = $payment['mchid'];
				//更新子商户
                Db::name('payment_logs')->where(array('log_id'=>$logs['log_id']))->update($data);
			}
		}
		return $sub_mch_id;
   }


    //获取详情
	public function getCode($sitename ='',$logs){
        $datas = array(
            'subject' => '支付' .'-'. $this->types[$logs['type']],
            'logs_id' => $logs['log_id'],
			'open_id' => $logs['open_id'],
			'sub_mch_id' => $this->getShopSubMchId($logs),
            'logs_amount' => $logs['need_pay']/100,
			'returnUrl' => $logs['returnUrl'],
			'notifyUrl' => $logs['notifyUrl'],
        );

        $payment = $this->getPayment($logs['code']);
		if($logs['code'] == 'native' || $logs['code'] == 'micro'){
			 include(ROOT_PATH.'extend/Payment/'.$logs['code'].'.weixin'.'.class.php' );//扫码支付
		}elseif(defined('IN_MOBILE')){
            include(ROOT_PATH.'extend/Payment/'.$logs['code'].'.mobile.class.php');
        }else{
            include(ROOT_PATH.'extend/Payment/'.$logs['code'].'.class.php');
        }
        $obj = new $logs['code']();
        return $obj->getCode($datas,$payment);
    }


	///getWindowToshowCode
	public function getWindowToshowCode($sitename ='',$logs){
        $datas = array(
            'subject' => '支付' .'-'. $this->types[$logs['type']],
            'logs_id' => $logs['log_id'],
			'open_id' => $logs['open_id'],
			'sub_mch_id' => $this->getShopSubMchId($logs),
            'logs_amount' => $logs['need_pay']/100,
			'returnUrl' => $logs['returnUrl'],
			'notifyUrl' => $logs['notifyUrl'],
        );
        include(ROOT_PATH.'extend/Payment/alipay.mobile.class.php');
		$payment = $this->getPayment($logs['code']);
        $obj = new $logs['code']();
        return $obj->getCodeWindowToshow($datas,$payment);
    }


	//检测支付金额正确与否
    public function checkMoney($logs_id, $money){
        $money = (int) ($money );
        $logs = model('PaymentLogs')->find($logs_id);
        if($logs['need_pay'] == $money){
			return true;
		}
        return false;
    }

	//检测支付方式
	public function checkPayment($code){
        $datas = $this->fetchAll();
        foreach($datas as $val){
            if($val['code'] == $code)
                return $val;
        }
        return array();
    }


    //获取支付方式详情
    public function getPayment($code){
        $datas = $this->fetchAll();
        foreach($datas as $val){
            if($val['code'] == $code)
                return $val['setting'];
        }
        return array();
    }




    public function logsPaid($logs_id,$return_order_id,$return_trade_no){
		$config = Setting::config();
        $this->log_id = $logs_id;
        $logs = Db::name('payment_logs')->find($logs_id);


        if(!empty($logs) && !$logs['is_paid']){

			$data['log_id'] = $logs_id;

			$data['is_paid'] = 1;
			$data['pay_time'] = time();
			$data['pay_ip'] = request()->ip();
			$data['return_order_id'] =$return_order_id;//返回订单号
			$data['return_trade_no'] =$return_trade_no;//返回交易号


            if(Db::name('payment_logs')->update($data)){
                  $this->type = $logs['type'];
                @file_put_contents('/tmp/zf_debug.log', '['.date('Y-m-d H:i:s').'][logsPaid] type='.$logs['type'].' log_id='.$logs_id.' order_id='.$logs['order_id'].' types='.$logs['types'].' need_pay='.$logs['need_pay'].' user_id='.$logs['user_id']."\n", FILE_APPEND);
              
                if($logs['type'] == 'money'){
					
					$info = '余额充值【'.round($logs['need_pay']/100,2).'】支付ID'.$logs['log_id'];
					if($logs['user_id']){
						model('Users')->addMoney($logs['user_id'],$logs['need_pay'],'余额充值',6);//奖励到余额
					}
					return true;
                }elseif($logs['type'] == 'vip'){
					$rank_id = $logs['order_id'] ? $logs['order_id'] :1;
					$rank=Db::name('user_rank')->where('rank_id',$rank_id)->find();
					$updatePayUserRank = model('Users')->updatePayUserRank($logs['user_id'],$rank['rank_id'],$rank['day'],'用户购买等级',0);
					$profit = (int)$config['profit']['profit'];
					if($profit == 1){
						model('ExpressOrder')->user_rank_profit($rank,$logs['user_id'],$logs['need_pay'],$logs['log_id']);
					}
					
					return true;
                }elseif($logs['type'] == 'coupon'){ 
					model('Setting')->updateCouponOrder($logs['order_id'],$logs['need_pay'],$logs['log_id'],$logs['user_id'],$logs['types']);
					return true;
				}elseif($logs['type'] == 'exchange'){
                    //积分兑换回调
                    model('IntegralExchange')->updateOrder($logs['order_id'],$logs['need_pay'],$logs['log_id'],$logs['user_id'],$logs['types']);
                    return true;
				}elseif($logs['type'] == 'express'){
					$eoBrief = Db::name('express_order')->where(array('id'=>$logs['order_id']))->field('id,type,orderStatus,deliveryId,expressNo,kuaidi')->find();
					@file_put_contents('/tmp/zf_debug.log', '['.date('Y-m-d H:i:s').'][logsPaid] 即将 updateExpressOrder order_id='.$logs['order_id'].' need_pay='.$logs['need_pay'].' log_id='.$logs['log_id'].' user_id='.$logs['user_id'].' types='.$logs['types'].' order='.json_encode($eoBrief, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);
			
					model('Setting')->updateExpressOrder($logs['order_id'],$logs['need_pay'],$logs['log_id'],$logs['user_id'],$logs['types']);
					return true;
				}elseif($logs['type'] == 'pao'){
                    $log_id = $logs['log_id'];
                    $order = Db::name('delivery_order')->where(array('id'=>$logs['order_id']))->find();
                    if($order['status']==0){
                        $update0rder['status'] = 1;
                        $update0rder['pay_time'] = time();
                        $update0rder['log_id'] = $log_id;
                        $update0rder['need_pay'] = $logs['need_pay'];
                        Db::name('delivery_order')->where(array('id'=>$logs['order_id']))->update($update0rder);
                    }
                    return true;
                }
            }
          return true;
      }
   }



}



