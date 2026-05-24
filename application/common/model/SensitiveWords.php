<?php
namespace app\common\model;

use think\Model;
use think\Db;



class SensitiveWords extends Base{

    protected $pk = 'words_id';
    protected $tableName = 'sensitive_words';
    protected $token = 'sensitive_words';
    protected $cacheTime = 8640000;


    public function checkWords($content){
        $words = $this->fetchAll();
        foreach($words as $val) {
            if(strstr($content, $val['words'])){
                return $val['words'];
            }
        }
        return false;
    }
}