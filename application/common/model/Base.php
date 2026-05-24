<?php

namespace app\common\model;

use think\Db;
use think\Model;
use think\Cache;


use app\common\model\Setting;

class Base extends Model{
	
	protected $pk = '';
    protected $tableName = '';
    protected $token = '';
	protected $config = '';
    protected $cacheTime = 86400;
    protected $orderby = array();
	
	
	
	protected function _initialize(){
		
	}
	
	
	
    public function updateCount($id, $col, $num = 1){
        $id = (int) $id;
		return Db::name($this->tableName)->where(array($this->pk => $id))->setInc($col,$num);
    }
	
	public function fetchAll($field = '*', $where = array()){
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        if (!($data = $cache->get($this->token))) {
            $result = $this->field($field);
            if (!empty($where)) {
                $result = $result->where($where);
            }
            $result = $result->order($this->orderby)->select();
            $data = array();
            foreach ($result as $row) {
                $data[$row[$this->pk]] = $this->_format($row);
            }
            $cache->set($this->token, $data);
        }
        return $data;
    }
	
	public function _format($data){
        return $data;
    }
	//清理缓存
	 public function cleanCache(){
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        Cache::clear($this->token);
		Cache::clear();
    }
	
     //关联查询
    public function itemsByIds($ids = array()){
        if (empty($ids)) {
            return array();
        }
        $data = $this->where(array($this->pk => array('IN', $ids)))->select();
        $return = array();
        foreach ($data as $val) {
            $return[$val[$this->pk]] = $val;
        }
        return $return;
    }
  
}