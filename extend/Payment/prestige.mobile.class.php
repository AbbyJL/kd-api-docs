<?php
class prestige{
	
    public function  getCode($logs,$setting = ''){ 
	
		$returnUrl = $setting['returnUrl'];
		$notifyUrl = $setting['notifyUrl'];
		
		$url = url('user/member/pay',array('logs_id'=>$logs['logs_id'],'t'=>'prestige'));
        return '<input type="button" name="syncbtn" class="button button-block bg-dot button-big btn_add" onclick="window.open(\''.$url.'\')" value=" 使用欢乐豆支付 " />';
    }

}