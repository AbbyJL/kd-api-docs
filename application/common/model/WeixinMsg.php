<?php
namespace app\common\model;

use think\Model;
use think\Db;

use EasyWeChat\Foundation\Application;
use app\common\model\Setting;

class WeixinMsg extends Base{


 	protected $pk = 'msg_id';
    protected $tableName = 'weixin_msg';

	protected $_type = array(
		'1' => '商城',
	);



}
