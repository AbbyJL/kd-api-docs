<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class DayuTag extends Base{

	protected $pk   = 'dayu_id';
	
    protected $tableName =  'dayu_tag';
	
}