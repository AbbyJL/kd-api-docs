<?php

namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

use app\common\model\Setting;


class SmsShop extends Base{
	
    protected $pk = 'log_id';
    protected $tableName = 'sms_shop';
    protected $token = 'tu_sms_shop';
	
	
	public function getError(){
        return $this->error;
    }
	
	
	//购买短信商家版本
	public function buy($num,$user_id,$shop_id){
		
		$config = Setting::config();
		
		$detail = Db::name('sms_shop')->where(array('type'=>'shop','user_id'=>$user_id,'status'=>0))->find();
		
		$Users = Db::name('users')->find($user_id);
		if($num % 100 != 0) {
           $this->error = '短信数量必须为100的倍数';
		   return false;
        }
		if($num < $config['sms_shop']['sms_shop_small']) {
		   $this->error = '购买短信数量不得小于' . $CONFIG['sms_shop']['sms_shop_small'] . '条';
		   return false;
        }
		if($num > $config['sms_shop']['sms_shop_big']) {
		   $this->error = '购买短信数量不得大于' . $CONFIG['sms_shop']['sms_shop_big'] . '条';
		   return false;
        }
        if($detail['num'] >= 1000) {
		   $this->error = '您当前还有' . $detail['num'] . '条短信，用完再来买吧';
		   return false;
        }    
            
			
        $money = $num * ($config['sms_shop']['sms_shop_money'] * 100);//总金额
		
        if($money > $Users['money'] || $Users['money'] == 0) {
			$this->error = '你的余额不足，请先充值，商户资金不能用来购买短信';
			return false;
        }
			
			
        if(model('Users')->addMoney($user_id,-$money,'商户购买短信：' . $num . '条')){
            if(empty($detail)){
				
                $data['user_id'] = $user_id;
                $data['shop_id'] = $shop_id;
                $data['type'] = 'shop';
                $data['num'] = $num;
                $data['create_time'] = time();
                $data['create_ip'] = request()->ip();
				
                Db::name('sms_shop')->insert($data);
				
				return true;
             }else{
                Db::name('sms_shop')->where(array('log_id'=>$detail['log_id']))->setInc('num',$num);
				return true;
             }
        }else{
            $this->error = '付款失败';
			return false;
        }
	}
	
}