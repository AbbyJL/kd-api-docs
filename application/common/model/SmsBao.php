<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class SmsBao extends Base{
	
    protected $pk   = 'sms_id';
    protected $tableName =  'sms_bao';
	
	
	 protected $type = array(
        0 => '成功',
        30 => '密码错误',
        40 => '账户不存在',
		41 => '余额不足',
		42 => '账户已过期',		
        43 => 'IP地址限制',
		50 => '内容敏感词',
		51 => '手机号不正确',
    );

    public function getType(){
        return $this->type;
    }
	
	
	
	//扣除短信
	public function ToUpdate($sms_id,$shop_id,$res){
		$data = array();
		$data['sms_id'] = $sms_id;
		$data['shop_id'] = $shop_id;
		$data['status'] = $res;
		
		Db::name('sms_bao')->update($data);
		
		$res = Db::name('sms_shop')->where(array('type'=>'shop','status'=>'0','shop_id'=>$shop_id))->find();
		if($res['num'] > 1){
			Db::name('sms_shop')->where(array('type'=>'shop','status'=>'0','shop_id'=>$shop_id))->setDec('num');
		}
		return true;
	}
	
	
	 
}