<?php
namespace app\common\model;
use think\Db;
use think\Cache;

use app\common\model\Setting;


class Delivery extends Base{



	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
    }

    public function getTypes(){
        return array(
            '0' => '取快递',
            '1' => '帮我买',
            '2' => '帮我取',
            '3' => '帮我送',
            '4' => '帮排队',
            '5' => '万能帮手'
        );
    }
	
	public function getStatus(){
        return array(
            '0' => '待付款',
            '1' => '已付款',
			'2' => '配送中',
			'3' => '已取消',
            '8' => '已完成'
        );
    }




}
