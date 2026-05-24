<?php
namespace app\common\model;
use think\Db;
use think\Model;

class Admin extends Base{
 	protected $pk   = 'admin_id';
    protected $tableName =  'admin';
	
    public function getAdminByUsername($username){
        $data = Db::name('admin')->where(array('username'=>$username))->find();
        return $this->_format($data);
    }
    
	
    public  function _format($data){
        static  $roles;
        if(empty($roles)){
			$roles = model('Role')->select();
		}
        if(!empty($data)){
			$data['role_name'] = $roles[$data['role_id']-1]['role_name'];   
		}  
        return $data;
    }

}