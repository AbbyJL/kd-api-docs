<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

use app\common\model\Setting;

class IntegralExchange extends Base{
	

    protected $pk = 'exchange_id';
    protected $tableName = 'integral_exchange';
	
	
	 public function getTypes(){
		 
		$config = Setting::config();
		
		$prestigeName = $config['prestige']['name'] ? $config['prestige']['name'] : '威望'; 
		$integralName = $config['integral']['name'] ? $config['integral']['name'] : '积分'; 

        return array(
            1 => $integralName,
            2  => $prestigeName,
        );
    }
	
	
	
	public function updateOrder($exchange_id,$need_pay=0,$log_id=0,$user_id=0,$types=1){
		$exchange = Db::name('integral_exchange')->where(array('exchange_id'=>$exchange_id))->find();
		$goods = Db::name('integral_goods')->where(array('goods_id'=>$exchange['goods_id']))->find();
		Db::name('integral_exchange')->where(array('exchange_id'=>$exchange_id))->update(array('status'=>2));
		Db::name('integral_goods')->where(array('goods_id'=>$goods['goods_id']))->update(array('num'=>$goods['num']-1,'exchange_num'=>$goods['exchange_num']+1));
		if($exchange['options_id']){
			Db::name('integral_goods_options')->where('id',$v['option_id'])->setDec('total',$exchange['num']);//减去库存
		}
		if($exchange['integral']){
			model('Users')->addIntegral($exchange['user_id'],-$exchange['integral'],'小程序兑换积分商品',7);
		}
		return true;
    }
	
	
	
}