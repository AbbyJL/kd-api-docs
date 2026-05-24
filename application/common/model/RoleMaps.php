<?php
namespace app\common\model;
use think\Model;
use think\Db;


class RoleMaps extends Model{
     protected $tableName = 'role_maps';
	 
     public function getMenuIdsByRoleId($role_id){
        $role_id = (int) $role_id;
        $datas = Db::name('role_maps')->where(array('role_id'=>$role_id))->select();
        $return = array();
        foreach ($datas as $val){
            $return[$val['menu_id']] = $val['menu_id'];
        }
        return $return;
    }
}