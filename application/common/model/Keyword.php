<?php

namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class Keyword extends Base{
	

    protected $pk = 'key_id';
    protected $tableName = 'keyword';

    public function getKeyType(){
        $res = array(
            '0' => '不限',
            '1' => '商家',
            '2' => '商品',
            '3' => '订座',
            '4' => '外卖',
            '5' => '贴吧',
			'6' => '文章',
        );
        return $res;
    }
}
