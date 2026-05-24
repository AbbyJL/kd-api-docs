<?php

namespace app\common\model;

use think\Model;
use think\Db;

use app\common\model\Setting;

class WeixinShare extends Base{
	
    protected $pk = 'share_id';
    protected $tableName = 'weixin_share';
	

	
	 public function appendShare($fuid,$user_id,$controller,$action){
		 $data = array();
		 $data['fuid'] = $fuid;
		 $data['user_id'] = $user_id;
		 $data['controller'] = $controller;
		 $data['action'] = $action;
		 $data['create_time'] = time();
         $data['create_ip'] = request()->ip();
		 Db::name('weixin_share')->insert($data);
		 return true;
	}
}