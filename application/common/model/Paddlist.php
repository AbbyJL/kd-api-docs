<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class Paddlist extends Base{

    protected $pk = 'id';
    protected $tableName = 'paddlist';

	//返回地址
	public function getIntro($id){
		$res = Db::name('paddlist')->where('id',$id)->find();//自己地址
		$res1 = Db::name('paddlist')->where('id',$res['upid'])->find();
		$res2 = Db::name('paddlist')->where('id',$res1['upid'])->find();
		$res3 = Db::name('paddlist')->where('id',$res3['upid'])->find();
		$res4 = Db::name('paddlist')->where('id',$res4['upid'])->find();
		return $res4['name'].'<b>&nbsp;</b>'.$res3['name'].'<b>&nbsp;</b>'.$res2['name'].'<b>&nbsp;</b>'.$res1['name'].'<b>&nbsp;</b>'.$res['name'];
	}


	//返回商城订单总价
	public function getGoodsOrderPrice($id){
		$ids = $this->getAddressIds($id);
		$need_pay = (int) Db::name('order')->where(array('status'=>array('in',array(1,2,3,8)),'closed'=>0,'address_id'=>array('in',$ids)))->sum('need_pay');
		return $need_pay;
	}


	//返回商城订单数量
	public function getGoodsOrderNum($id){
		$ids = $this->getAddressIds($id);
		$count = (int) Db::name('order')->where(array('status'=>array('in',array(1,2,3,8)),'closed'=>0,'address_id'=>array('in',$ids)))->count();
		return $count;
	}

	public function getAddressIds ($id){
		$Paddlist = Db::name('paddlist')->where(array('id'=>$id))->find();
		if($Paddlist['upid'] == 0){
			$list = Db::name('paddress')->where(array('province_id'=>$id))->select();
		}
		if($Paddlist['upid'] == 1){
			$list = Db::name('paddress')->where(array('city_id'=>$id))->select();
		}
		if($Paddlist['upid'] == 2){
			$list = Db::name('paddress')->where(array('area_id'=>$id))->select();
		}
		if(!$list){
			return false;
		}
		foreach ($list as $key => $val) {
            $ids[$val['id']] = $val['id'];
        }
		return $ids;
	}


}
