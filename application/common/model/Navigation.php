<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class Navigation extends Base{
	
    protected $pk = 'nav_id';
    protected $tableName = 'navigation';
    protected $token = 'navigation';

    protected $orderby = array('orderby' => 'asc');
    public function getParentsId($id){
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id'];
        $parent_id2 = $data[$parent_id]['parent_id'];
        if ($parent_id2 == 0) {
            return $parent_id;
        }
        return $parent_id2;
    }
	
	public function getStatus(){
        return array(
			'1' => '首页顶部导航',
			'2' => '首页中间导航',
		);
    }
	
	public function navigation_click($nav_id){
		$obj = model('Navigation');
		if(false!== $obj->where(array('nav_id'=>$nav_id))->setInc('click',1)){
            return true;
        }else{
            return true;
        }

	}
	
	
    public function getChildren($id){
        $local = array();
        $data = $this->fetchAll();
        foreach ($data as $val) {
            if ($val['parent_id'] == $id){
                $child = true;
                foreach ($data as $val1){
                    if ($val1['parent_id'] == $val['nav_id']){
                        $child = FALSE;
                        $local[] = $val1['nav_id'];
                    }
                }
                if ($child) {
                    $local[] = $val['nav_id'];
                }
            }
        }
        return $local;
    }
}