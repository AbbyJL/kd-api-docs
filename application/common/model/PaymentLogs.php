<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class PaymentLogs extends Base{

        protected $pk = 'log_id';
        protected $tableName =  'payment_logs';

        protected $type = array(
            'express' => '订单支付',
			'rank' => '等级购买',
        	'money' => '余额充值',
			'coupon' => '优惠券购买',
        );


        protected $is_paid = array(
            0 => '未支付',
            1 => '已支付',
        );

        protected $code = array(
            money => '余额支付',
			wxapp => '小程序支付',
        );

        public function getType(){
            return $this->type;
        }

        public function getis_paid(){
            return $this->is_paid;
        }

        public function getcode(){
            return $this->code;
        }

   //返回商户订单表的支付类型
	public function get_payment_logs_type($type){
		$types = model('Payment')->getTypes();
		$result = array_flip($types);//反转数组
		$types = array_search($type, $result);
		if(!empty($types)){
			return $types;
		}else{
			return false;
		}
        return false;
	}


	//退款原路返回orderWeixinRefund
    public function orderWeixinRefund($order_id,$user_id,$need_pay,$type = 'express',$info){
		if(empty($order_id)){
			$this->error = '退款订单号不能为空';
			return false;
		}
		if(empty($need_pay)){
			$this->error = '退款金额错误';
			return false;
		}


        //支付信息
        $logs = Db::name('payment_logs')->where(array('type'=>$type,'order_id'=>$order_id,'is_paid'=>1))->find();
        if(!$logs){
            //批量支付
            $eo = Db::name('express_order')->where(array('id'=>$order_id))->find();
            $logs = Db::name('payment_logs')->where(array('log_id'=>$eo['log_id'],'type'=>'exps','code'=>'wxapp','is_paid'=>1))->find();
        }


		if(empty($logs['return_trade_no'])){
			$this->error = '商户订单号错误';
			return false;;
		}
		
		if(empty($logs['return_order_id'])){
			$this->error = '微信交易单号错误';
			return false;
		}
		$payment = model('Payment')->getPayment('wxapp');
		if(empty($payment['appid'])){
			$this->error = 'appid不能为空';
			return false;
		}
		if(empty($payment['mchid'])){
			$this->error = 'mchid不能为空';
			return false;
		}


		$connect = Db::name('connect')->where(array('type'=>'weixin','uid'=>$logs['user_id']))->find();
		if(empty($connect['openid'])){
			$this->error = '当前会员的openid不存在';
			return false;
		}

		require_once(ROOT_PATH . 'extend/Payment/WxPayPubHelper/WxPayPubHelper.php');
		//调用请求接口基类
        $Redpack = new \Refund_pub();

		$Redpack->setParameter('transaction_id',$logs['return_trade_no']);//商户订单号
		$Redpack->setParameter('out_trade_no',$logs['return_order_id']);//商户订单号
		$Redpack->setParameter('out_refund_no',$order_id);//商户退款单号
		$Redpack->setParameter('total_fee',$logs['need_pay']);//订单金额
		$Redpack->setParameter('refund_fee',$need_pay);//退款金额
		$Redpack->setParameter('op_user_id',$connect['openid']);//操作员，会员的openid
		$Redpack->setParameter('appid', $payment['appid']);
		$Redpack->setParameter('mch_id', $payment['mchid']);

        $result = $Redpack->getResult();
		$result = (array)$result;

		if(is_array($result) && $result['result_code'] == 'SUCCESS'){
			$data['out_refund_no'] = $result['out_refund_no'];//退款单号
			$data['refund_id'] = $result['refund_id'];//微信退款单号
			$data['refund_fee'] = $result['refund_fee'];//退款金额
			$data['settlement_refund_fee'] = $result['settlement_refund_fee'];//应结退款金额
			$data['refund_time'] = time();
			$data['refund_info'] = $info;
			$data['is_paid'] = 4;
			Db::name('PaymentLogs')->where(array('type'=>$type,'order_id'=>$order_id))->update($data);
			return true;
		}else{
			$this->error = '操作失败:原因【'.$result['return_msg'] .''.$result['err_code_des'].'】【'.$logs['log_id'].'】';
			return false;
		}
        return false;
     }



	//返回支付日志数据
    public function getLogsByOrderId($type,$order_id){
         $order_id = (int)$order_id;
         $type = addslashes($type);
         return Db::name('payment_logs')->where(array('type'=>$type,'order_id'=>$order_id))->order('log_id desc')->find();
     }
}
