<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class WeixinKeyword extends Base{
    protected $pk = 'keyword_id';
    protected $tableName = 'weixin_keyword';
    protected $token = 'weixin_keyword';
	
    public function checkKeyword($keyword){
		
        $words = $this->fetchAll();
        foreach($words as $val){
            if($val['keyword'] == $keyword){
                return $val;
            }
        }
        return false;
    }
}