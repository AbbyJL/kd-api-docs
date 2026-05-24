<?php
namespace app\common\model;

use think\Db;
use think\Model;
use think\Cache;
use app\common\model\Setting;

include_once(ROOT_PATH . "extend/kuayue/openapi/sdk/KyeDefaultOpenApi.php");

class KuayueApi extends Base{
 	protected $pk = 'cate_id';
    protected $tableName = 'express_cate';
    protected $token = 'jin_express_cate';
    protected $settings = null;
	

	
	public function getError(){
        return $this->error;
    }
	
	
	
	public function cate_imports(){
		return array(
			'1' => array('name' => '跨越-当天达','productCode' => '10', 'productName' => 'KY','ioc' => 'ky.png'),
			'2' => array('name' => '跨越-次日达','productCode' => '20', 'productName' => 'KY','ioc' => 'ky.png'),
			'3' => array('name' => '跨越-隔日达','productCode' => '30', 'productName' => 'KY','ioc' => 'ky.png'),
			'4' => array('name' => '跨越-陆运件','productCode' => '40', 'productName' => 'KY','ioc' => 'ky.png'),
			'5' => array('name' => '跨越-同城次日','productCode' => '50', 'productName' => 'KY','ioc' => 'ky.png'),
			'6' => array('name' => '跨越-同城即日','productCode' => '70', 'productName' => 'KY','ioc' => 'ky.png'),
			'7' => array('name' => '跨越-省内次日','productCode' => '160', 'productName' => 'KY','ioc' => 'ky.png'),
			'8' => array('name' => '跨越-省内即日','productCode' => '170', 'productName' => 'KY','ioc' => 'ky.png'),
			'9' => array('name' => '跨越-空运','productCode' => '210', 'productName' => 'KY','ioc' => 'ky.png'),
			'10' => array('name' => '跨越-专运','productCode' => '220', 'productName' => 'KY','ioc' => 'ky.png')
		);
    }

	
	public function orderCreate($uid,$u,$e,$shop,$s,$r,$smail_id=0,$rmail_id=0,$is_dw,$coupon_code,$data,$cargodata,$thirdNo,$totalWeight,$totalNumber,$remark,$sendStartTime,$sendEndTime,$et=''){
		$configs = model('Setting')->fetchAll2();
		$config = $configs['wxapp'];
		$isSandbox = (int)$config['ky_isSandbox'];
		
		$long = $data['long'];
		$height = $data['height'];
		$width = $data['width'];
		$insuranceValue = $data['insuranceValue'];
		$insurancePrice = $data['insurancePrice'];
		$remark = $data['remark'];
		$orderType = $data['orderType'];
		
	
		//返回订单号
		$t = (int)$cargodata['type'];
		$d1 = strstr($r['address'],$r['province']);
		if($d1 == false){
			$r_address = $r['province'].''.$r['city'].''.$r['area'].''.$r['address'];
		}else{
			$r_address = $r['address'];
		}
		
		$d2 = strstr($s['address'],$s['province']);
		if($d2 == false){
			$s_address = $s['province'].''.$s['city'].''.$s['area'].''.$s['address'];
		}else{
			$s_address = $s['address'];
		}
		
		if($r['phone']!='' && $r['phone']){
			$receiveMobile = $r['phone'];
		}elseif($r['mobile']!='' && $r['mobile']){
			$receiveMobile = $r['mobile'];
		}else{
			$receiveMobile= '17194348715';
		}
		
		if($s['phone']!='' && $s['phone']){
			$senderMobile = $s['phone'];
		}elseif($s['mobile']!='' && $s['mobile']){
			$senderMobile = $s['mobile'];
		}else{
			$senderMobile= '17194348715';
		}
		$beginArea = @mb_substr($s['city'],0,2);
		$beginArea = Db::name('citycode')->where(array('city'=>array('LIKE','%'.$beginArea.'%')))->find();
		$beginAreaCode = $beginArea['citycode'];
		
		$endArea = @mb_substr($r['city'],0,2);
		$endArea = Db::name('citycode')->where(array('city'=>array('LIKE','%'.$endArea.'%')))->find();
		$endAreaCode = $endArea['citycode'];
		$method = "open.api.openCommon.queryFreightCharge";
		$bizBody['platformFlag'] =$config['ky_platformFlag'];
		$bizBody['customerCode'] =$config['ky_customerCode_1'];
		$bizBody['beginAreaCode'] =$beginAreaCode;
		$bizBody['endAreaCode'] =$endAreaCode;
		$bizBody['billingTime'] =date('Y-m-d H:i',time()+3600);
		$bizBody['pickupCustomerCode'] =$config['ky_paymentCustomer_1'];
		$bizBody['weight'] =$totalWeight;
	

		$KyeDefaultOpenApi = new \KyeDefaultOpenApi();
		if($isSandbox){
			$resultData = $KyeDefaultOpenApi->builder($config['ky_appkey'],$config['ky_appsecret'])->sandBox()->api($method)->body($bizBody)->request();
		}else{
			$resultData = $KyeDefaultOpenApi->builder($config['ky_appkey'],$config['ky_appsecret'])->api($method)->body($bizBody)->request();
		}
		$list = $resultData['data'];
		$i = 0;
		foreach($list as $key=>$val){
			$i++;
			if($val['serviceMode'] == $cargodata['transportType']){
				$v = $val;
			}
		}
		
		$originalFee = $v['beforeDiscountAmount']*100;
		$TotalFee = $v['afferDiscountAmount']*100;
		$priceA = 0;
		$priceB = 0;
		
		$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$TotalFee,$priceA,$priceB,$originalFee,0,$e,$insurancePrice,$co,$data['coupon_pmt'],$expressValue);
		$data['firstPrice'] = $getCatePrice['firstPrice'];
		$data['addPrice'] =$getCatePrice['addPrice'];
		$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
		$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
		$data['preOrderFee'] = $getCatePrice['preOrderFee'];
		$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
		$data['sumMoneyYuan_old'] =$getCatePrice['sumMoneyYuan_old'];
		$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
		$data['type'] =5;
		
		
		$bizBody['platformFlag'] =$config['ky_platformFlag'];
		$bizBody['customerCode'] =$config['ky_customerCode_1'];
		$bizBody['beginAreaCode'] =$beginAreaCode;
		$bizBody['endAreaCode'] =$beginAreaCode;
		$bizBody['billingTime'] =date('Y-m-d H:i',time()+3600);
		$bizBody['pickupCustomerCode'] =$config['ky_paymentCustomer_1'];
		
		$requestParams['platformFlag']=$config['ky_platformFlag'];//客户/平台标识
		$requestParams['customerCode']=$config['ky_customerCode_1'];//客户编码
		$requestParams['callbackUrl']= $configs['site']['host'].'/app/api/push5';
				$preWaybillDelivery['person']= $s['name'];
				$preWaybillDelivery['mobile']= $senderMobile;
				$preWaybillDelivery['address']= $s_address;
			$orderInfos['preWaybillDelivery']= $preWaybillDelivery;
				$preWaybillPickup['person']= $r['name'];
				$preWaybillPickup['mobile']= $receiveMobile;
				$preWaybillPickup['address']= $r_address;
			$orderInfos['preWaybillPickup']= $preWaybillPickup;
			$orderInfos['serviceMode']= $cargodata['transportType'];//服务方式
			$orderInfos['payMode']= 30;//10-寄方付 ，20-收方付 ，30-第三方付 （传代码）
			$orderInfos['goodsType']= $cargodata['name'];//托寄物
			$orderInfos['orderId']= $thirdNo;//客户订单号
			$orderInfos['receiptFlag']= 20;//	回单类型
			$orderInfos['actualWeight']= $totalWeight;
			$orderInfos['count']= 1;
			if($et){
				$orderInfos['dismantling']= 10;//10-是，表示根据预约揽件的时间上门揽收，20-否，表示线下自主联系揽收（传代码，是否预约取货为“10”时，货好时间字段必填，同时根据货好时间安排司机上门揽收）
				$orderInfos['goodsTime']= $et;//货好时间（预约上门揽件的时间）
			}else{
				$orderInfos['dismantling']= 20;//10-是，表示根据预约揽件的时间上门揽收，20-否，表示线下自主联系揽收（传代码，是否预约取货为“10”时，货好时间字段必填，同时根据货好时间安排司机上门揽收）
				$orderInfos['goodsTime']= '';//货好时间（预约上门揽件的时间）
			}
			$orderInfos['subscriptionService']= 10;//路由订阅服务
			$orderInfos['paymentCustomer']= $config['ky_paymentCustomer_1'];//付款公司月结账号
		$requestParams['orderInfos']= $orderInfos;
		$data['requestParams']= iserializer($requestParams);
		return $data;
	}
	
	
	
	public function batchOrder($requestParams=''){
		$configs = model('Setting')->fetchAll2();
		$config = $configs['wxapp'];
		$isSandbox = (int)$config['ky_isSandbox'];
		
		$KyeDefaultOpenApi = new \KyeDefaultOpenApi();
		$method = "open.api.openCommon.batchOrder";
		if($isSandbox){
			$resultData = $KyeDefaultOpenApi->builder($config['ky_appkey'],$config['ky_appsecret'])->sandBox()->api($method)->body($requestParams)->request();
		}else{
			$resultData = $KyeDefaultOpenApi->builder($config['ky_appkey'],$config['ky_appsecret'])->api($method)->body($requestParams)->request();
		}
		return $resultData;
	}
	
	//取消订单
	public function cancelOrder($biz=''){
		$configs = model('Setting')->fetchAll2();
		$config = $configs['wxapp'];
		$isSandbox = (int)$config['ky_isSandbox'];
        $biz['customerCode'] = $config['ky_customerCode_1'];
		$KyeDefaultOpenApi = new \KyeDefaultOpenApi();
		$method = "open.api.openCommon.cancelOrder";
		if($isSandbox){
			$resultData = $KyeDefaultOpenApi->builder($config['ky_appkey'],$config['ky_appsecret'])->sandBox()->api($method)->body($biz)->request();
		}else{
			$resultData = $KyeDefaultOpenApi->builder($config['ky_appkey'],$config['ky_appsecret'])->api($method)->body($biz)->request();
		}
		return $resultData;
	}
	
	//查询路由
	public function queryPublicRoute($biz=''){
		$configs = model('Setting')->fetchAll2();
		$config = $configs['wxapp'];
		$isSandbox = (int)$config['ky_isSandbox'];
		
		$KyeDefaultOpenApi = new \KyeDefaultOpenApi();
		$method = "open.api.openCommon.queryPublicRoute";
		if($isSandbox){
			$resultData = $KyeDefaultOpenApi->builder($config['ky_appkey'],$config['ky_appsecret'])->sandBox()->api($method)->body($biz)->request();
		}else{
			$resultData = $KyeDefaultOpenApi->builder($config['ky_appkey'],$config['ky_appsecret'])->api($method)->body($biz)->request();
		}
		return $resultData;
	}

	//获取预查询
	public function getExpressList6($data){
		$config = model('Setting')->fetchAll2();
		$config = $config['wxapp'];
		$isSandbox = (int)$config['ky_isSandbox'];
		$cate = (int)$data['cate_id'];
		
		
		$beginArea = @mb_substr($data['recipients_city'],0,2);
		$beginArea = Db::name('citycode')->where(array('city'=>array('LIKE','%'.$beginArea.'%')))->find();
		$beginAreaCode = $beginArea['citycode'];
		
		$endArea = @mb_substr($data['sender_city'],0,2);
		$endArea = Db::name('citycode')->where(array('city'=>array('LIKE','%'.$endArea.'%')))->find();
		$endAreaCode = $endArea['citycode'];
		
	
		$method = "open.api.openCommon.queryFreightCharge";
		$bizBody['platformFlag'] =$config['ky_platformFlag'];
		$bizBody['customerCode'] =$config['ky_customerCode_1'];
		$bizBody['beginAreaCode'] =$beginAreaCode;
		$bizBody['endAreaCode'] =$endAreaCode;
		$bizBody['billingTime'] =date('Y-m-d H:i',time()+3600);
		$bizBody['pickupCustomerCode'] =$config['ky_paymentCustomer_1'];
		$bizBody['weight'] =$data['totalWeight'];
		


		$KyeDefaultOpenApi = new \KyeDefaultOpenApi();
		if($isSandbox){
			$resultData = $KyeDefaultOpenApi->builder($config['ky_appkey'],$config['ky_appsecret'])->sandBox()->api($method)->body($bizBody)->request();
		}else{
			$resultData = $KyeDefaultOpenApi->builder($config['ky_appkey'],$config['ky_appsecret'])->api($method)->body($bizBody)->request();
		}
		
		$list = $resultData['data'];
		$i = 0;
		foreach($list as $key=>$val){
			$i++;
			$c = Db::name('express_cate')->where(array('pinyin'=>$val['serviceMode'],'type'=>5))->find();
			if($c && $val['beforeDiscountAmount']){
				$expressList[$key]['freightInsured'] = 0;
				$expressList[$key]['c_type'] =$c['type'];
				$expressList[$key]['lanshou'] =$c['lanshou'];
				$expressList[$key]['info'] =$c['info'];
				$expressList[$key]['orderby'] =$c['orderby'];
				$expressList[$key]['firstPrice'] =(int)$c['firstPrice'];
				$expressList[$key]['img'] =config_weixin_img($c['photo']);
				$expressList[$key]['nickname'] = cut_msubstr($c['cate_name'],0,20,true);
				$expressList[$key]['name'] = cut_msubstr($c['cate_name'],0,20,true);
				$expressList[$key]['title'] = '';
				$expressList[$key]['channelId'] = $c['pinyin2'];
				$expressList[$key]['isBest'] = 1;
				$expressList[$key]['preOrderFee'] = $val['totalfee']*100;
				$expressList[$key]['channel'] = $c['pinyin2'];
				$expressList[$key]['transportType'] = $c['pinyin'];
				
				$originalFee = $val['beforeDiscountAmount']*100;
				$TotalFee = $val['afferDiscountAmount']*100;
				$priceA = 0;
				$priceB = 0;
			
				$getCatePrice = model('Setting')->getCatePrice($data['uid'],$data['totalWeight'],$TotalFee,$priceA,$priceB,$originalFee,0,$c);
				
				$expressList[$key]['originalFee'] = round($originalFee/100,2);
				$expressList[$key]['zhekou'] = round(($originalFee-$getCatePrice['sumMoneyYuan'])/100,2);
				$expressList[$key]['z'] = 1;
				$expressList[$key]['discount'] = $getCatePrice['discount'];
				$expressList[$key]['vip_discount'] = $getCatePrice['vip_discount'];
				$expressList[$key]['original_cost'] = $getCatePrice['original_cost'];
				$expressList[$key]['sumMoneyYuan'] = (int)$getCatePrice['sumMoneyYuan'];
				$expressList[$key]['yuanMoney'] = $getCatePrice['yuanMoney'];
				$expressList[$key]['type'] = 5;
				$expressList[$key]['tag']= $c['tag'] ? $c['tag'] : '';
				if($c['is_bao']==0){
					$expressList[$key]['is_baojia'] = 1;
				}else{
					$$expressList[$key]['is_baojia'] = 0;
				}
				if($c['is_yuyue']==0){
					$expressList[$key]['is_yuyue'] = 0;
				}else{
					$expressList[$key]['is_yuyue'] = 1;
				}
				if($cate_id && $c['cate_id'] != $cate_id){
					unset($expressList[$key]);
				}
				if($c['firstPrice']==1){
					unset($expressList[$key]);
				}	
				if($config['config']['company_moshi'] != 3){
					$expressList=@second_array_unique_bykey($expressList,'nickname');
					$expressList =@array_values($expressList);
				}
			}
			$expressList =@array_values($expressList);
		}
		return $expressList;
	}
		

	
	
		
		public function kuayue_push($eop,$eo){
		$config = model('Setting')->fetchAll2();
		$plan_order_cancel = (int)$config['config']['plan_order_cancel'];//自动退款
		$v = $eo;
		$context = @json_decode($eop['context'],true);
		

        $node = $context['node'];
		$desc = $context['desc'];
	 	$mailno = $context['mailno'];
		$desc = $context['desc'];
		
		if($node==0){
			$handle_info =$desc;
			$up['orderStatus'] =2;
			$up['realOrderState'] =$context['desc'];
		}elseif($node==1){
			$handle_info =$desc;
			$up['orderStatus'] =1;
			$up['realOrderState'] =$context['desc'];
		}elseif($node==2){
			$handle_info =$desc;
			$up['orderStatus'] =2;
			$up['realOrderName'] =$context['packager'];
			$up['realOrderMobile'] = $context['packagerTel'];
			$up['realOrderState'] =$context['packager'].'-'.$context['packagerTel'];
			model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '接单成功提醒',$context['packager'],$context['packagerTel']);
		}elseif($node==3){
			$handle_info =$desc;
			$up['realOrderState'] =$context['step'];
		}elseif($node==3){
			$handle_info =$desc;
			$up['orderStatus'] =3;
			$up['realOrderState'] =$context['step'];
		}elseif($node==4){
			$handle_info =$desc;
			$up['realOrderState'] =$context['step'];
		}elseif($node==5){
			$handle_info =$desc;
			$up['realOrderState'] =$context['step'];
		}elseif($node==6){
			$handle_info =$desc;
			$up['realOrderState'] =$context['step'];
		}elseif($node==8){
			$handle_info =$desc;
			$up['realOrderState'] =$context['step'];
		}elseif($node==35){
			$handle_info =$desc;
			$up['realOrderState'] =$context['step'];
		}elseif($node==45){
			$handle_info =$desc;
			$up['realOrderState'] =$context['step'];
		}elseif($node==98){
			$handle_info =$desc;
			$up['realOrderState'] =$context['step'];
		}elseif($node==90){
			$handle_info =$desc;
			$up['realOrderState'] =$context['desc'];
		}elseif($node==-1){
			$handle_info =$desc;
			$up['realOrderState'] =$context['desc'];
		}elseif($node==100){
			$handle_info =$desc;
			$up['orderStatus'] =4;
			Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>4,'requestParams'=>'','requestParams2'=>'','requestParams5'=>''));
			model('ExpressOrder')->completeProfit($v,$v['user_id'],'分销');//执行完成分销
			model('ExpressOrder')->orderAddIntegral($v,$v['user_id'],'给用户奖励积分');//赠送优惠券
			model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '签收成功通知');
		}elseif($node==200){
			$falg =1;
			$handle_info =$desc;
			
			$freightWeight = $context['freightWeight'];//计费重量
			$calWeight = @ceil($freightWeight);//重构返回的重量进一步定位法
			$calWeight = (int)$calWeight;
			$totalFreightAmount = $context['totalFreightAmount']*100;
			$totalIncreaseAmount = $context['totalIncreaseAmount']*100;
		
			$ky_zhekou = $config['wxapp']['ky_zhekou_1'];
			if($v['pdfUrl'] == $config['wxapp']['ky_customerCode_1']){
			    $ky_zhekou = $config['wxapp']['ky_zhekou_1'];
			}
			if($v['pdfUrl'] == $config['wxapp']['ky_customerCode_2']){
			    $ky_zhekou = $config['wxapp']['ky_zhekou_2'];
			}
			if($ky_zhekou <50 || $ky_zhekou>=100){
			    $ky_zhekou = 70;
			}
			
			$totalFreightAmount =(($totalFreightAmount-$totalIncreaseAmount)*$ky_zhekou)/100;
			$TotalFee = (int)$totalFreightAmount;
			$freightInsured = $totalIncreaseAmount;
			
			$Volume =  $context['volume'];
			$up['orderStatus'] =3;
			$up['packageServicePrice'] =0;//包装费用
			$up['insurancePrice'] =$totalIncreaseAmount;//保价费用
			if($calWeight){
				$up['review_weight'] = $calWeight;//计费重量
			}
			$up['review_vloumn'] = $Volume;//体积
			$up['TotalFee'] = $TotalFee;//平台实际收费
		
			if($TotalFee > $v['sumMoneyYuan_old'] && $v['diffStatus'] == 0 && $v['orderStatus'] != '0' && $v['orderStatus'] != '-1' && $v['orderStatus'] != '5'){
				$diffMoneyYuan = model('Setting')->getDiffMoney($v['user_id'],$calWeight,$v['wight'],$TotalFee,$v,$freightHaocai,$freightInsured);
				if($diffMoneyYuan){
					$v['diffMoneyYuan'] = $diffMoneyYuan;
					$handle_info = '实际重量【'.$calWeight.'】实际收费【'.round($TotalFee/100,2).'】补差价'.round($diffMoneyYuan/100,2);	
					$updataData['diffStatus']=1;
					$updataData['review_weight']=$calWeight;
					$updataData['diffMoneyYuan']=$diffMoneyYuan;
					$updataData['insurancePrice']=$freightInsured;
					$updataData['packageServicePrice']=$freightHaocai;//总耗材费用
					Db::name('express_order')->where(array('id'=>$v['id']))->update($updataData);//更新订单
					model('Sms')->sendSmsTmplSend($v,$v['user_id'],$title = '补差价通知');//判断补差价
					model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '补差价通知');
				}
		   }
		}elseif($node=='203'){
			$falg =0;
			$handle_info =$desc;
			if($plan_order_cancel){
				model('ExpressOrder')->cancel($v,$v['id'],$reason='接口方取消订单',$cancel_money=0,$checkOrderStatus=1);//接口方取消订单
			}else{
				Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>-1,'orderRightsStatus'=>1,));
			}
		}
		if($up){
			Db::name('express_order')->where(array('id'=>$v['id']))->update($up);//更新订单
		}
		$data['deliveryId'] = $mailno;
		$data['orderStatusName'] = '';
		$data['orderStatus'] = $up['orderStatus'];
		$data['handle_info'] = $handle_info;
		return $data;
	}
	
	

	
	
}