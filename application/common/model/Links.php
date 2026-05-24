<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class Links extends Base{
	

    protected $pk = 'link_id';
    protected $tableName = 'links';
    protected $token = 'links';
    protected $orderby = array('orderby' => 'asc');
	
}