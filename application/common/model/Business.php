<?php
namespace app\common\model;
use think\Model;

use think\Db;
use think\Cache;

class Business extends Base{
	
    protected $pk   = 'business_id';
    protected $tableName =  'business';
    protected $token = 'business';
    protected $orderby = array('orderby'=>'asc');

    public function _format($data){
        static $area = null;
        if($area == null){
            $area = model('Area')->fetchAll();
        }
        $data['area_name'] = $area[$data['area_id']]['area_name'];
        return $data;
    }
	
	
    public function setToken($token){
        $this->token = $token;
    }
	
	 public function getBusinessOrderTypes(){
        return array(
            '1' => '城主购买',
            '2' => '城主转让',
			'3' => '城主退单',
			'4' => '城主分佣',
        );
    }
	
	
	public function getBusinessOrderStatus(){
        return array(
            '0' => '未付款',
            '1' => '已付款',
        );
    }
	
	
	//订单回调
	public function updateOrder($order_id,$orderType = 0){
		Db::name('business_order')->where(array('order_id'=>$order_id))->update(array('status' =>1));//区域订单已付款
		
		
		$business_order = Db::name('business_order')->where(array('order_id'=>$order_id))->find();
		$rest = Db::name('business')->where(array('business_id'=>$business_order['business_id']))->update(array('lock'=>0,'lock_user_id'=>''));
		
		//p($AreaOrder);die;
		
		//购买的城市
		$business = Db::name('business')->where(array('business_id'=>$business_order['business_id']))->find();
		
		//城主购买
		if($business_order['orderType']== 1){
			Db::name('business')->where(array('business_id'=>$business_order['business_id']))->update(array('user_id' =>$business_order['user_id']));//旧城主
		}
		if($business_order['orderType'] == 2){
			
			Db::name('business')->where(array('business_id'=>$business_order['business_id']))->update(array('user_id' =>$business_order['user_id']));//更新新的城主
			
			
			//p($business_order);
			
			$rise = Db::name('business_rise')->where(array('old_order_id'=>$business_order['old_order_id']))->find();//以前的订单号
			if(empty($rise)){
				$rise = Db::name('business_order')->where(array('order_id'=>$business_order['old_order_id']))->find();//以前的订单号
			}
			//p($rise);
			
			//原价
			$y =$rise['money'];
			
			$x =$business_order['money'];
			
			//差价
			$c =$x-$y;
			
			
			
			$rise3 = $business['rise3'] ? $business['rise3'] : '100';
			$yongjin = ($c*$rise3)/10000;
			$yongjin = (int)$yongjin;
			
			
			
			
			//结算价给原来城主的价格  = 卖了多少钱【买家实际支付】 -佣金
			$money = $business_order['money'] - $yongjin;
			
			//原来转多少钱 = 差价-佣金
			
			
			if($x <= $y){
				$zhuan = 0;
			}else{
				$zhuan = $c - $yongjin;
			}
			
			/*
			p('佣金：'.$yongjin);
			p('原价：'.$y);
			p('购买人的价格：'.$x);
			p('差件：'.$c);
			p('赚钱：'.$zhuan);
			p('结算给卖家：'.$money);die;
			*/
			
			$msg = '当前购买价格【'.round($business_order['money']/100,2).'】元，给旧城主价格【'.round($money/100,2).'】元，平台佣金'.round($yongjin/100,2).'元费率【'.round($rise3/100,2).'】%，赚【'.round($zhuan/100,2).'】元';
			model('Users')->addMoney($business_order['old_user_id'],$money,$msg);
		}
		

		
		
		$price = $business['business_attorn_price'] ? $business['business_attorn_price'] : $business['price'];//当前交易价格
		
		
	
		$rise = $business['rise'] ? $business['rise'] : '100';
	
		$zhangjia = ($price*$rise)/10000;
		$zhangjia = (int)$zhangjia;
		
		
		//p('原价金额：'.$price);
		//p('佣金比例：'.$rise);
		//p('涨价金额：'.$zhangjia);
		//p('实际金额：'.$price+$zhangjia);die;
			
			

		$res2 = Db::name('business')->where(array('business_id'=>$business_order['business_id']))->update(array('business_attorn_price' =>$price+$zhangjia));//给区域涨价
		
		$arr['old_order_id'] = $business_order['old_order_id'];
		$arr['order_id'] = $order_id;
		$arr['area_id'] = $business_order['area_id'];
		$arr['business_id'] = $business_order['business_id'];
		$arr['price'] = $area['price'];
		$arr['money'] = $price;//交易多少钱
		$arr['zhuan'] = $zhuan;
		$arr['yuanjia'] = $price;
		$arr['zhangjia'] = $zhangjia;
		$arr['xianjia'] = $price+$zhangjia;
		$arr['rise'] = $rise;
		$arr['user_id'] = $business_order['user_id'];
		$arr['create_time'] = NOW_TIME;
		$arr['create_ip'] = request()->ip();
		
	    $res3 = Db::name('business_rise')->insertGetId($arr);//涨价记录
				
		
		return true;
	}
	
	
	
	//给城主分佣金
	public function addBusinessOrder($city_id,$business_id,$money= 0,$type = '',$type_order_id = 0,$order_id = 0,$types = 0){
		
		$business = Db::name('business')->where(array('business_id'=>$business_id))->find();
		
		if($types == 1){
			$info = '【置顶城主分成】';
		}elseif($types == 2){
			$info = '【刷新城主分成】';
		}else{
			$info = '【发布信息城主分成】';
		}
	
		$ratio = $business['ratio'] ? $business['ratio'] : 1;
		$money2 = ($money*$ratio)/10000;
		if($money > 0 && $business['user_id']){
			$data['user_id'] =$business['user_id'];
			$data['city_id'] = $city_id;
			$data['area_id'] = $business['area_id'];
			$data['business_id'] = $business_id;
			$data['type'] = $type ;
			$data['type_order_id'] = $type_order_id;
			$data['orderType'] = 4;
			$data['status'] = 1;
			$data['money'] = $money2;
			$data['closed'] = 0;
			$data['create_time'] = NOW_TIME;
			$data['create_ip'] = request()->ip();
			$order_id = Db::name('business_order')->insertGetId($data);	
			model('Users')->addMoney($business['user_id'],$money2,$info.'城市【'.$business['business_name'].'】城主【'.round($money2/100,2).'】元，订单总价【'.round($money/100,2).'】分成费率【'.round($ratio/100,2).'】%');
		}
		return true;
	}
	
	
	
}