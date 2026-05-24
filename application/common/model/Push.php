<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class Push extends Base{


	public function yiDaDeliveryBusiness(){
		return array(
			'1' => array('name' => '顺丰特快','productCode' => 'SF_GZ_TK', 'productName' => 'SF','ioc' => 'sf.png'),
			'2' => array('name' => '顺丰标快','productCode' => 'SF_GZ_BK', 'productName' => 'SF','ioc' => 'sf.png'),
			'3' => array('name' => '顺丰陆运包裹','productCode' => 'SF_LYBG', 'productName' => 'SF','ioc' => 'db.png'),
			'4' => array('name' => '德邦大件360','productCode' => 'DOP_RCP', 'productName' => 'DOP','ioc' => 'db.png'),
			'5' => array('name' => '德邦标准快递','productCode' => 'DOP_PACKAGE', 'productName' => 'DOP','ioc' => 'db.png'),
			'6' => array('name' => '德邦精准汽运','productCode' => 'DOP_LRF', 'productName' => 'DOP','ioc' => 'db.png'),
			'7' => array('name' => '德邦精准卡航','productCode' => 'DOP_FLF', 'productName' => 'DOP','ioc' => 'db.png'),
			'8' => array('name' => '德邦重包入户','productCode' => 'DOP_NZBRH', 'productName' => 'DOP','ioc' => 'db.png'),
			'9' => array('name' => '京东特快零担','productCode' => 'JD_TK_LD', 'productName' => 'JD','ioc' => 'jd.png'),
			'10' => array('name' => '京东特快重货','productCode' => 'JD_TK_ZH', 'productName' => 'JD','ioc' => 'jd.png'),
			'11' => array('name' => '京东特惠送','productCode' => 'JD_THS', 'productName' => 'JD','ioc' => 'jd.png'),
			'12' => array('name' => '京东特快送','productCode' => 'JD_TKS', 'productName' => 'JD','ioc' => 'jd.png'),
			'13' => array('name' => '申通','productCode' => 'STO_INT_BK', 'productName' => 'STO-INT','ioc' => 'jd.png'),
			'14' => array('name' => '极兔','productCode' => 'JT_BK', 'productName' => 'JT','ioc' => 'jt.png'),
			'15' => array('name' => '圆通','productCode' => 'YTO_BK', 'productName' => 'YTO','ioc' => 'yt.png'),
			'16' => array('name' => '中通','productCode' => 'ZTO_BK', 'productName' => 'ZTO','ioc' => 'zt.png'),
			'17' => array('name' => '韵达','productCode' => 'YUND_BK', 'productName' => 'YUND','ioc' => 'yd.png'),
			'18' => array('name' => '菜鸟','productCode' => 'CAINIAO_BK', 'productName' => 'CAINIAO','ioc' => 'cn.png'),
			'19' => array('name' => '菜鸟速递','productCode' => 'CNSD', 'productName' => 'CNSD','ioc' => 'cn.png'),
			'20' => array('name' => '百世','productCode' => 'BEST_KY', 'productName' => 'BEST','ioc' => 'baishi.png'),
			'21' => array('name' => '跨越速运','productCode' => 'KY_ZY', 'productName' => 'KY','ioc' => 'ky.png'),
			'22' => array('name' => '邮政','productCode' => 'EMS_TKZD', 'productName' => 'EMS','ioc' => 'ems.png'),
		);
    }
  
	//获取云洋状态
    public function get_yy_order_status($v,$type){
		$orderStatus =1;
        $falg =0;
		$fg =0;
		if($v['orderStatus'] == 1 && $type==''){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 2 && $type==''){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 1 && $type=='分配网点'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 2 && $type=='分配网点'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == '' && $type=='待揽收'){
			$falg =1;
			$orderStatus =2;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='待揽收'){
			$falg =1;
			$orderStatus =2;
			$fg =1;
		}elseif($v['orderStatus'] == 2 && $type=='待揽收'){
			$falg =1;
			$orderStatus =2;
			$fg =1;
		}elseif($v['orderStatus'] == 3 && $type=='待揽收'){
			$falg =1;
			$orderStatus =2;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='待揽收' && $v['kuaidi'] == '顺丰'){
			$falg =1;
			$orderStatus =1;
			$fg =1;
		}elseif($v['orderStatus'] == 2 && $type=='待揽收' && $v['kuaidi'] == '顺丰'){
			$falg =1;
			$orderStatus =1;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='接单'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 2 && $type=='接单'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 1 && $type=='分单'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 2 && $type=='分单'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 1 && $type=='已接单'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 1 && $type=='已开单'){
			$falg =1;
			$orderStatus =1;
		}elseif($v['orderStatus'] == 1 && $type=='揽收任务分配'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 2 && $type=='揽收任务分配'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 2 && $type=='接货中'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 1 && $type=='接货中'){
			$falg =1;
			$orderStatus =1;
			$fg =0;
		}elseif($v['orderStatus'] == 2 && $type=='分拣中心发货'){
			$falg =1;
			$orderStatus =1;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='分拣中心发货'){
			$falg =1;
			$orderStatus =1;
			$fg =1;
		}elseif($v['orderStatus'] == 2 && $type=='配送员完成揽收'){
			$falg =1;
			$orderStatus =2;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='配送员完成揽收'){
			$falg =1;
			$orderStatus =2;
			$fg =1;
		}elseif($v['orderStatus'] == 2 && $type=='已接单'){
			$falg =1;
			$orderStatus =2;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='在途中'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 2 && $type=='在途中'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='运输中'){
			$falg =1;
			$orderStatus =2;
			$fg =1;
		}elseif($v['orderStatus'] == 2 && $type=='运输中'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 3 && $type=='运输中'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 4 && $type=='运输中'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='已揽件'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='已正常收件状态'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 2 && $type=='已正常收件状态'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='揽收成功'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 2 && $type=='揽收成功'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 3 && $type=='揽收成功'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 2 && $type=='已揽收'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 3 && $type=='已揽收'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='已揽收'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 1 && $type=='正常揽件'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}elseif($v['orderStatus'] == 2 && $type=='正常揽件'){
			$falg =1;
			$orderStatus =3;
			$fg =1;
		}
		$data['falg'] = $falg;
		$data['orderStatus'] = $orderStatus;
		$data['fg'] = $fg;
		return $data;
    }
	
	//获取云洋签收状态
    public function get_yy_complete_status($v,$type){
		$complete = 0;
		if($type=='已签收' && $v['orderStatus'] == 3){
			$complete = 1;
		}
		if($type=='已签收' && $v['orderStatus'] == 2){
				$complete = 1;
		}
		if($type=='签收' && $v['orderStatus'] == 3){
			$complete = 1;
		}
		if($type=='签收' && $v['orderStatus'] == 2){
			$complete = 1;
		}
		if($type=='正常签收' && $v['orderStatus'] == 3){
			$complete = 1;
		}
		if($type=='正常签收' && $v['orderStatus'] == 2){
			$complete = 1;
		}
		if($type=='已结算' && $v['orderStatus'] == 3){
			$complete = 1;
		}
		if($type=='已结算' && $v['orderStatus'] == 2){
			$complete = 1;
		}
		if($type=='已签收' && $v['orderStatus'] == 3){
			$complete = 1;
		}
		if($type=='已签收' && $v['orderStatus'] == 2){
			$complete = 1;
		}
		return $complete;
	}

	//获取风火递状态
    public function get_fhd_status_type_name($statusType){
		if($statusType=='ACCEPT'){
			$statusTypeTypeName = '已受理';
		}elseif($statusType=='CANCEL'){
			$statusTypeTypeName = '已取消';
		}elseif($statusType=='FAILGOT'){
			$statusTypeTypeName = '揽货失败';
		}elseif($statusType=='GOBACK'){
			$statusTypeTypeName = '已退回';
		}elseif($statusType=='GOT'){
			$statusTypeTypeName = '已开单';
		}elseif($statusType=='INVALID'){
			$statusTypeTypeName = '已作废';
		}elseif($statusType=='RECEIPTING'){
			$statusTypeTypeName = '接货中';
		}elseif($statusType=='REJECT'){
			$statusTypeTypeName = '已拒绝';
		}elseif($statusType=='SHOUTCAR'){
			$statusTypeTypeName = '已约车';
		}elseif($statusType=='SIGNFAILED'){
			$statusTypeTypeName = '异常签收';
		}elseif($statusType=='SIGNSUCCESS'){
			$statusTypeTypeName = '正常签收';
		}
		return $statusTypeTypeName;
	}
	//获取德邦状态
    public function get_debang_status_type_name($statusType){
		if($statusType=='ACCEPT'){
			$statusTypeTypeName = '已受理';
		}elseif($statusType=='CANCEL'){
			$statusTypeTypeName = '已取消';
		}elseif($statusType=='FAILGOT'){
			$statusTypeTypeName = '揽货失败';
		}elseif($statusType=='GOBACK'){
			$statusTypeTypeName = '已退回';
		}elseif($statusType=='GOT'){
			$statusTypeTypeName = '已开单';
		}elseif($statusType=='INVALID'){
			$statusTypeTypeName = '已作废';
		}elseif($statusType=='RECEIPTING'){
			$statusTypeTypeName = '接货中';
		}elseif($statusType=='REJECT'){
			$statusTypeTypeName = '已拒绝';
		}elseif($statusType=='SHOUTCAR'){
			$statusTypeTypeName = '已约车';
		}elseif($statusType=='SIGNFAILED'){
			$statusTypeTypeName = '异常签收';
		}elseif($statusType=='SIGNSUCCESS'){
			$statusTypeTypeName = '正常签收';
		}
		return $statusTypeTypeName;
	}
	
	//获取q必达订单状态
    public function get_ulifego_order_status_name($status){
		if($status == 0){
			$orderStatusName = '预下单';
		}
		if($status == 1){
			$orderStatus = 2;
			$orderStatusName = '待取件';
		}
		if($status == 2){
			$orderStatus = 3;
			$orderStatusName = '运输中';
		}
		if($status == 5){
			$orderStatus = 4;
			$orderStatusName = '已签收';
		}
		if($status == 6){
			$orderStatusName = '取消订单';
		}
		if($status == 7){
			$orderStatusName = '终止揽收';
		}
		if($status == 8){
			$orderStatusName = '特殊关闭';
		}
		if($status == 9){
			$orderStatusName = '已退款';
		}
		$data['orderStatusName'] = $orderStatusName;
		$data['orderStatus'] = $orderStatus;
		return $data;
	}

}

