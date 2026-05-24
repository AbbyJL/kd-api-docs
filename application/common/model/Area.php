<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class Area extends Base{
	
	
    protected $pk   = 'area_id';
    protected $tableName =  'area';
    protected $token = 'area';
    protected $orderby = array('orderby'=>'asc');
   
    public function setToken($token){
        $this->token = $token;
    }
	
	
	public function getBusinessNum($area_id){
        $Business = (int)Db::name('business')->where(array('area_id' => $area_id))->count();
        return $Business;
	}
 
}