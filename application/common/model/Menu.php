<?php
namespace app\common\model;
use think\Db;
use think\Cache;
use think\Model;

class Menu extends Base{
 	protected $pk = 'menu_id';
    protected $tableName = 'menu';
    protected $token = 'menu';
    protected $orderby = array('orderby' => 'asc');
	


	
    public function checkAuth($auth){
        $data = Db::name('menu')->select();
        foreach($data as $row){
            if($auth == $row['menu_action']){
                return true;
            }
        }
        return false;
    }
	

}