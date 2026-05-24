<?php

use app\common\model\Setting;


class alipay{
    public function getCode($logs,$setting){
		$CONFIG = Setting::config();
        $real_method = $setting['service'];
        switch($real_method){
            case '0':
                $service = 'trade_create_by_buyer';
                break;
            case '1':
                $service = 'create_partner_trade_by_buyer';
                break;
            case '2':
                $service = 'create_direct_pay_by_user';
                break;
        }

        $parameter = array(
            'service' => $service,
            'partner' => $setting['alipay_partner'],
            '_input_charset' => 'utf-8',
            'notify_url' => $CONFIG['site']['host'] . url('home/payment/respond', array('code' => 'alipay')),
            'return_url' => $CONFIG['site']['host'] . url('home/payment/respond', array('code' => 'alipay')),
            'subject' => $logs['subject'],
            'out_trade_no' => $logs['subject'] . $logs['logs_id'],
            'price' => $logs['logs_amount'],
            'quantity' => 1,
            'payment_type' => 1,
            'logistics_type' => 'EXPRESS',
            'logistics_fee' => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',
            'seller_email' => $setting['alipay_account']
        );
        ksort($parameter);
        reset($parameter);
        $param = '';
        $sign = '';
        foreach($parameter as $key => $val){
            $param .= "$key=" . urlencode($val) . "&";
            $sign .= "$key=$val&";
        }
        $param = substr($param, 0, -1);
        $sign = substr($sign, 0, -1) . $setting['alipay_key'];
        $button = '<div style="text-align:center"><input type="button" class="home-submit-button" onclick="window.open(\'https://www.alipay.com/cooperate/gateway.do?'.$param.'&sign='.md5($sign).'&sign_type=MD5\')" value="立刻支付"/></div>';
        return $button;
    }



    public function respond(){
        if(!empty($_POST)){
            foreach($_POST as $key => $data){
                $_GET[$key] = $data;
            }
        }
        $payment = model('Payment')->getPayment($_GET['code']);
	
        $seller_email = rawurldecode($_GET['seller_email']);
        $logs_id = str_replace($_GET['subject'], '', $_GET['out_trade_no']);
        $logs_id = trim($logs_id);
        if(!model('Payment')->checkMoney($logs_id,$_GET['total_fee']*100)) {
            return false;
        }
        
		
        if($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS' || $_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS'){
            model('Payment')->logsPaid($logs_id,'','');
            return true;
        }else{
            return false;
        }
    }



}