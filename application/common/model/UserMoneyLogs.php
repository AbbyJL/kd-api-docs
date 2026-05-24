<?php

namespace app\common\model;
use think\Db;
use think\Cache;

class UserMoneyLogs extends Base{

     protected $pk   = 'log_id';
     protected $tableName =  'user_money_logs';
    
}