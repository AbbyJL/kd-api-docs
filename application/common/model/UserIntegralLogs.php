<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class UserIntegralLogs extends Base{
	
    protected $pk   = 'log_id';
    protected $tableName =  'user_integral_logs';
	
    
}