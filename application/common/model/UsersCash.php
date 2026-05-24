<?php
namespace app\common\model;


use think\Db;
use think\Model;
use think\Cache;
use think\Loader;


use app\common\model\Setting;

class UsersCash extends Base{
	
    protected $pk = 'cash_id';
    protected $tableName = 'users_cash';
	
	public function getError(){
        return $this->error;
    }
	
	
	//支付宝企业付款封装
	public function alipayUserCach($cash_id,$tpye){
		$config = Setting::config();
		$detail = Db::name('users_cash')->where(array('cash_id'=>$cash_id))->find();
		if(!$detail){
           $this->error = '提现的订单不存在';
		   return false;
        }
		if($detail['status'] !=0){
           $this->error = '提现订单状态不正确';
		   return false;
        }
		if($detail['type'] =='money'){
			$money = $detail['money'];
		}elseif($detail['type'] =='prestige'){
			$money = $detail['prestige'];
		}elseif($detail['type'] =='gold'){
			$money = $detail['gold'];
		}
		if($money < 10){
			$this->error = '申请提现的金额不合法'.$money;
		    return false;
		}
		
		$money = round($money/100,2);//金额优化
		$payment = model('Payment')->getPayment('alipay');
		if(!$payment){
			$this->error = '网站没有配置支付宝支付';
		    return false;
		}
		
	
		
		include (ROOT_PATH . 'extend/Payment/alipay/AopClient.php');
		include (ROOT_PATH . 'extend/Payment/alipay/request/AlipayFundTransToaccountTransferRequest.php');
		
		
		$aop = new \AopClient();
		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
		
		$aop->appId = $payment['alipay_app_id'];//商户ID
		$aop->rsaPrivateKey = $payment['alipay_private_key'];//请填写开发者私钥去头去尾去回车，一行字符串'
		$aop->alipayrsaPublicKey=$payment['alipay_rsa_public_key'];//请填写支付宝公钥，一行字符串
		
		$aop->apiVersion = '1.0';
		$aop->signType = 'RSA2';
		$aop->postCharset='utf-8';
		$aop->format='json';
		
		
		$request = new \AlipayFundTransToaccountTransferRequest();
		
		
		$request->setBizContent("{" .
		"\"out_biz_no\":\"{$detail['cash_id']}\"," .//商户转账唯一订单号
		"\"payee_type\":\"ALIPAY_LOGONID\"," .//收款方账户类型
		"\"payee_account\":\"{$detail['alipay_account']}\"," .//收款方账户
		"\"amount\":\"{$money}\"," .//转账金额
		"\"payer_show_name\":\"{$config['site']['sitename']}\"," .//付款方姓名
		"\"payee_real_name\":\"{$detail['alipay_real_name']}\"," .//付款人真实姓名
		"\"remark\":\"申请提现\"" .//备注
		"}");
		$result = $aop->execute($request); 
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		
		$res = (array)$result->$responseNode;

		if(!empty($resultCode)&&$resultCode == 10000){
			$arr = array();
			$arr['mch_billno'] = $res['order_id'];//支付宝转账单据号
			$arr['return_msg'] = $res['msg'];//业务返回码描述，参见具体的API接口文档
			$arr['partner_trade_no'] = $res['out_biz_no'];//商户转账唯一订单号
			$arr['mpayment_time'] = $res['pay_date'];
			$arr['status'] = 1;
			Db::name('users_cash')->where(array('cash_id'=>$cash_id))->update($arr);
			return true;
			//如果退款成功
		}else{
			$this->error = '退款失败:错误编码【'.$res['code'].'】sub_code：【'.$res['sub_code'].'】错误说明：【'.$res['sub_msg'].'】';
			return false;
		}	
    }
	
	
	
	//微信企业付款封装
	public function weixinUserCach($cash_id,$tpye){
		$detail = Db::name('users_cash')->where(array('cash_id'=>$cash_id))->find();
		if(!$detail){
           $this->error = '提现的订单不存在';
		   return false;
        }
		if($detail['status'] !=0){
           $this->error = '提现订单状态不正确';
		   return false;
        }
		
		if($detail['type'] =='user'){
			$money = $detail['money'];
		}
			
		if($money < 100){
			$this->error = '申请提现的金额不合法-'.$money;
		    return false;
		}
		$payment = model('Payment')->getPayment('wxapp');
		if(!$payment){
			$this->error = '网站没有配置微信支付';
		    return false;
		}
		$connect = Db::name('connect')->where(array('uid'=>$detail['user_id'],'type'=>'weixin'))->order(array('connect_id'=>'asc'))->find();
		if(empty($connect['openid'])){
			$this->error = '您没有关注微信或者不是微信登录';
		    return false;
		}
		
		
		include(ROOT_PATH . 'extend/Payment/WxPayPubHelper/WxPayPubHelper.php');
        $Redpack = new \Withdrawals();
        $Redpack->setParameter('mch_appid',$payment['appid']);
        $Redpack->setParameter('mchid',$payment['mchid']);
		
		
		if($shop['sub_mch_id']){
			$Redpack->setParameter('sub_mch_id',$shop['sub_mch_id']);
		}
		
        $Redpack->setParameter('partner_trade_no',$cash_id.'a'.time());//商户订单号
        $Redpack->setParameter('re_user_name','申请提现');//收款人姓名
        $Redpack->setParameter('amount',$money);
        $Redpack->setParameter('desc','申请提现付款');
        $Redpack->setParameter('openid',$connect['openid']);
        $Redpack->setParameter('check_name', 'NO_CHECK');
        $result = $Redpack->sendMerchantCash();
		
		//p($Redpack);
		//p($result);die;
		
		if (is_array($result) && $result['result_code'] == 'SUCCESS'){
			$arr = array();
			$arr['mch_billno'] = $result['mch_billno'];
			$arr['return_msg'] = $result['return_msg'];
			$arr['payment_no'] = $result['payment_no'];
			$arr['partner_trade_no'] = $result['partner_trade_no'];
			$arr['payment_time'] = time();
			Db::name('users_cash')->where('cash_id',$cash_id)->update($arr);
			return true;
			//如果退款成功
		}else{
			$this->error = '退款失败openid-'.$connect['openid'].'可能是没开通企业付款到领钱:原因【'.$result['return_msg'] .'---'.$result['err_code_des'].'】';
			return false;
		}	
			
		
    }
	
	

    //查询提现次数
	public function testingAddtime($user_id,$type){
		$config = Setting::config();
		$bg_time = strtotime(TODAY);
		
		
		if($type == 1 || $type == 'user'){
			
			$count = Db::name('users_cash')->where(array('user_id'=>$user_id,'type'=>user,'addtime' => array(array('ELT', time()),array('EGT', $bg_time))))->count();
			
			$second = $config['cash']['user_cash_second'] ? $config['cash']['user_cash_second'] : 1;
			
			
			if($second){
				if($count >= $second){
					$this->error = '会员每天只能提现【'.$second.'】次，您今日提现【'.$count.'】次';
					return false;
				}
			}
			return true; 
		}elseif($type == 2 || $type == 'shop'){
			
			$count = Db::name('users_cash')->where(array('user_id'=>$user_id,'type'=>shop,'addtime'=> array(array('ELT', time()),array('EGT', $bg_time))))->count();
			
			$second = $config['cash']['shop_cash_second'] ? $config['cash']['shop_cash_second'] : 1;
			
			if($second){
				if($count >= $second){
					$this->error = '商家员每天只能提现【'.$second.'】次，您今日提现【'.$count.'】次';
					return false;
				}
			}
			return true;
		}
		return true;
    }
}
