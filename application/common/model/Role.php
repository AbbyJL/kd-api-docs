<?php
namespace app\common\model;
use think\Model;

class Role extends Base{
 	protected $pk = 'role_id';
    protected $tableName = 'role';
    protected $token = 'role';
}