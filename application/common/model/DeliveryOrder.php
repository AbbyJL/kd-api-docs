<?php
namespace app\common\model;
use think\Db;
use think\Cache;

use app\common\model\Setting;


class DeliveryOrder extends Base{



	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
    }
	
	public function getStatus(){
        return array(
            '0' => '未付款',
            '1' => '已付款',
			'2' => '已接单',
			'3' => '已取件',
			'8' => '已完成',
			'5' => '已取消',
        );
    }
	
}
