<?php
class money{
    public function  getCode($logs,$setting=array()){
       return '<input type="button" name="syncbtn" class="home-submit-button btn_add" onclick="window.open(\''.url('member/pay/pay',array('logs_id'=>$logs['logs_id'])).'\')" value="余额支付" />';
    }
}