<?php
namespace app\common\model;

use think\Db;
use think\Model;
use think\Cache;


class YtApi extends Base{
 	protected $pk = 'cate_id';
    protected $tableName = 'express_cate';
    protected $token = 'jin_express_cate';
    protected $settings = null;
	
	
	public function productCodes(){
		return array(
			'0' => array('name' => '京东','productCode' => '1','ioc' => 'jd.png'),
			'1' => array('name' => '德邦','productCode' => '2','ioc' => 'db.png'),
			'2' => array('name' => '圆通','productCode' => '3','ioc' => 'yt.png'),
			'3' => array('name' => '申通','productCode' => '4','ioc' => 'icon_st.png'),
			'4' => array('name' => '极兔','productCode' => '5','ioc' => 'jt.png'),
			'5' => array('name' => '顺丰','productCode' => '6','ioc' => 'sf.png'),
			'6' => array('name' => '中通','productCode' => '7','ioc' => 'zt.png'),
			'7' => array('name' => '韵达','productCode' => '8','ioc' => 'yd.png'),
		);
    }
	
	
	public function buildSendAddress($updateData,$Method ='ADD_SENDADDRESS'){
		$content['sender']=$updateData['name'];
		$content['senderMobile']=$updateData['phone'];
		$content['province']=$updateData['province'];
		$content['city']=$updateData['city'];
		$content['county']=$updateData['area'];
		$content['town']= '';
		$content['location']=$updateData['detail'];
		$performance = model('YtApi')->performance($content,$Method ='ADD_SENDADDRESS');
		return true;
		
	}
	
	
	public function getExpressList11($data){
		$config = model('Setting')->fetchAll2();
		
		if($data['recipients_phone']!='undefined' && $data['recipients_phone']){
			$recipients_mobile = $data['recipients_phone'];
		}elseif($data['recipients_mobile']!='undefined' && $data['recipients_mobile']){
			$recipients_mobile = $data['recipients_mobile'];
		}else{
			$recipients_mobile = '17194348715';
		}
		
		if($data['sender_phone']!='undefined' && $data['sender_phone']){
			$sender_mobile = $data['sender_phone'];
		}elseif($data['sender_mobile']!='undefined' && $data['sender_mobile']){
			$sender_mobile = $data['sender_mobile'];
		}else{
			$sender_mobile = '17194348715';
		}
		if($data['type'] == '3'){
			$channelTag = "重货";
		}elseif($data['type'] == '1'){
			$channelTag = "智能";
		}elseif($data['type'] == '2'){
			$channelTag = "得物";
		}else{
			$channelTag = "智能";
		}
		$content['channelTag']=$channelTag;
		$content['channelSubTag']='';
		$content['channelSubType']='智能';//当channelSubTag为京东、德邦时必填，具体值参考指南内的产品类型;若要匹配全部渠道传智能  
		$content['sender']=$data['sender_name'];
		$content['senderMobile']= $sender_mobile;
		$content['senderProvince']= $data['sender_province'];
		$content['senderCity']= $data['sender_city'];
		$content['senderCounty']= $data['sender_area'];
		$content['senderTown']='';
		$content['senderLocation']=$data['sender_address'];
		$content['senderAddress']= $data['sender_province'].$data['sender_city'].$data['sender_area'].$data['sender_address'];
		$content['receiver']=$data['recipients_name'];
		$content['receiverMobile']=$recipients_mobile;
		$content['receiveProvince']= $data['recipients_province'];
		$content['receiveCity']= $data['recipients_city'];
		$content['receiveCounty']= $data['recipients_area'];
		$content['receiveTown']= '';
		$content['receiveLocation']= $data['recipients_address'];
		$content['receiveAddress']=$data['recipients_address'] ? $data['recipients_address'] : $data['recipients_province'].$data['recipients_city'].$data['recipients_area'];
		$content['weight']= (int)$data['totalWeight'];
		$content['itemName']= '日用品';
		$content['packageCount']= 1;
		$content['insured']= 0;
		$content['customerFreight']= 0;
		$content['collectionMoney']= 0;
		$content['vloumLong']= '';
		$content['vloumWidth']= '';
		$content['vloumHeight']='';
		
		
	
		$performance = model('YtApi')->performance($content,$Method ='CHECK_CHANNEL_INTELLECT');
		
		if($performance['code'] == 0){
			$expressList = array();
		}else{
			foreach($performance['result'] as $k=>$v){
				$c = Db::name('express_cate')->where(array('cate_name'=>$v['tagType'],'type'=>18,'firstPrice'=>0))->find();
				if(!$c){
					$c = Db::name('express_cate')->where(array('pinyin2'=>$v['tagCode'],'type'=>18,'firstPrice'=>0))->find();
					if(!$c && $data['type']==2){
						$cate_name = cut_msubstr($v['channel'],0,2,true);
						$c = Db::name('express_cate')->where(array('cate_name'=>$cate_name,'type'=>18,'firstPrice'=>0))->find();
					}
				}
				if($c){
					$expressList[$k]['freightInsured'] = $v['freightInsured'];//云洋保价费
					$expressList[$k]['c_type'] =$c['type'];
					$expressList[$k]['lanshou'] =$c['lanshou'];
					$expressList[$k]['info'] =$c['info'];
					$expressList[$k]['orderby'] =$c['orderby'];
					$expressList[$k]['firstPrice'] =(int)$c['firstPrice'];
					$expressList[$k]['img'] =config_weixin_img($c['photo']);
					$expressList[$k]['nickname'] = cut_msubstr($c['cate_name'],0,2,true);
					$expressList[$k]['name'] = $v['tagType'];
					$expressList[$k]['title'] = '';
					if($config['config']['company_moshi']==3){
					   $expressList[$k]['title'] = $v['channel']; 
					}
					$expressList[$k]['freight'] = $v['freight'];
					$expressList[$k]['channelId'] = $v['channelId'];
					$expressList[$k]['channel'] = $v['channelId'];
					$expressList[$k]['transportType'] = $v['channelId'];
					$expressList[$k]['type'] = 9;
					$expressList[$k]['tag']= $c['tag'] ? $c['tag'] : '未定义';
					$priceA = $v['priceOne']?$v['priceOne']:$v['price']['priceOne'];//云洋首重
					$priceB = $v['priceMore']?$v['priceMore']:$v['price']['priceMore'];//云洋续重
					
					if($data['totalWeight'] == 1 && $priceA < $v['freight']){
						//加起来价格不对
						$getCatePrice = model('Setting')->getCatePrice($data['uid'],$data['totalWeight'],$v['freight']*100,0,0,$v['originalPrice']*100,$v['freightInsured']*100,$c);
					}else{
						$getCatePrice = model('Setting')->getCatePrice($data['uid'],$data['totalWeight'],$v['freight']*100,$priceA*100,$priceB*100,$v['originalPrice']*100,$v['freightInsured']*100,$c);
					}
					$expressList[$k]['getCatePrice'] = $getCatePrice;
					$expressList[$k]['discount'] =$getCatePrice['discount'];//云洋普通用户运费
					$expressList[$k]['vip_discount'] = $getCatePrice['vip_discount'];
					$expressList[$k]['original_cost'] = $getCatePrice['original_cost'];
					$expressList[$k]['sumMoneyYuan'] = (int)$getCatePrice['sumMoneyYuan'];
					$expressList[$k]['getCatePrice'] = $getCatePrice;
					//保价预约
					if($c['is_bao']==0){
						$expressList[$k]['is_baojia'] = 1;
					}else{
						$expressList[$k]['is_baojia'] = 0;
					}
					if($c['is_yuyue']==0){
						$expressList[$k]['is_yuyue'] = 1;
					}else{
						$expressList[$k]['is_yuyue'] = 0;
					}
					if($cate_id && $c['cate_id'] != $cate_id){
						unset($expressList[$k]);
					}
					if($c['firstPrice']==1){
						unset($expressList[$k]);
					}
				}
			}
			if($config['config']['company_moshi'] != 3){
				$expressList=@second_array_unique_bykey($expressList,'name');
				$expressList =@array_values($expressList);
			}
		}
		return $expressList;
	}
	
	
	
	
	//云腾旺店管家
	public function yt_push($eop,$eo){
		$config = model('Setting')->fetchAll2();
		$plan_order_cancel = (int)$config['config']['plan_order_cancel'];//自动退款
		$v = $eo;
		$context = @json_decode($eop['context'],true);
		$transferWeight = $context['transferWeight'];
		$freightInsured = $context['freightInsured'];//保价费
		$comments = $context['comments'];
		$parseWeight = $context['parseWeight'];
		$totalPrice = $context['totalPrice'];
		$calWeight = $context['calWeight'];
		$billType = $context['billType'];
		$freight = $context['freight'];
		$totalFreight = $context['totalFreight']*100;
		$weight = $context['weight'];
		$realWeight = $context['realWeight'];
	    $type = $context['type'];
		$billOrderId = $context['billOrderId'];
		$changeBillFreight = $context['changeBillFreight'];
		$linkName = $context ['linkName'];
		$volume =  $context['volume'];
		$feeOver = $context['feeOver'];
		$changeBill =  $context['changeBill'];
		$freightHaocai =  $context['freightHaocai'];
		$shopbill = $context['shopbill'];
		$waybill =  $context['waybill'];
		$calWeight = $context['calWeight'] ? $context['calWeight'] : $context['weight'];
		
		if($calWeight){
			$up['real_weight'] = $calWeight;//实际重量
		}
		$calWeight = @ceil($calWeight);
		$calWeight = (int)$calWeight;
		
		
	    $get_yy_order_status = model('Push')->get_yy_order_status($v,$type);//获取云洋状态
		$falg = $get_yy_order_status['falg'];
		$orderStatus = $get_yy_order_status['orderStatus'];
		$fg = $get_yy_order_status['fg'];  
		  
		
		$handle_info = '云腾旺店管家【'.$type.'】';
		$realOrderData = model('ExpressOrder')->realOrderData($comments,$v['kuaidi'],$context);
		
		
	    $totalPrice = $totalPrice*100;
	    $freight = $freight*100;
	    $freightInsured = $freightInsured*100;
	    $freightHaocai = $freightHaocai*100;
	    $orderFee =  $freight;//云洋运费总价 
	    $orderFees = $freightHaocai+$freightInsured;//云洋保价费+耗材费
	    $orderFee = (int)($orderFee+$orderFees);
		
		if($falg==1 && $waybill){
			$up['totalNumber'] = $packageCount;
			$up['totalVolume'] = $parseWeight;
			if($fg==1){
				$up['review_weight'] = $calWeight;
			}
			$up['review_vloumn'] = $volume;
			$up['realOrderState'] = $comments;
			$up['realOrderName'] =$realOrderData['realOrderName'];
			$up['realOrderMobile'] = $realOrderData['realOrderMobile'];
			$up['realOrderCode'] = $realOrderData['realOrderCode'];
			$up['orderStatus'] = $orderStatus;
			$up['orderStatusName'] = $type;
			if($waybill){
				$up['deliveryId'] = $waybill;
			}
			$up['insurancePrice'] = $freightInsured*100;
			$up['packageServicePrice'] = $freightHaocaii*100;
			$up['insuranceValue'] = $freightInsured*100;
			$up['TotalFee']=$totalFreight;
		    $up['orderFee']=$totalFreight;
			
			
			$handle_info .= '云腾旺店管家更新订单状态【'.$type.'】';		
			Db::name('express_order')->where(array('id'=>$v['id']))->update($up); 	
			
			if($orderStatus==2){
				model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '接单成功提醒',$realOrderData['realOrderName'],$realOrderData['realOrderMobile']);
				model('Ad')->dingTalkWebhook($dd_msg=5,'云洋单号【'.$waybill.'】'.$handle_info,'');
			}
		}
	    


	


	    if($v['orderStatus'] != '0' && $v['orderStatus'] != '-1' && $v['orderStatus'] != '5' && $fg==1){
			$bu=0; 
			if($orderFee > $v['sumMoneyYuan_old'] && $v['diffStatus'] != 2 && $v['kuaidi'] == '德邦' && $feeOver==1){
				$bu=1; 
			}
			if($orderFee > $v['sumMoneyYuan_old'] && $v['diffStatus'] == 0 && $v['kuaidi'] != '德邦' && $feeOver==1){
				$bu=1 ; 
			}
			if($orderFee > $v['sumMoneyYuan_old'] && $v['diffStatus'] == 1 && $v['kuaidi'] != '德邦' && $feeOver==1){
				$bu=1 ; 
			}
			if($orderFee == $v['sumMoneyYuan_old'] && $v['diffStatus'] != 2 && $v['kuaidi'] == '德邦' && $orderFees && $feeOver==1){
				$bu=2; 
			}
			if($orderFee == $v['sumMoneyYuan_old'] && $v['diffStatus'] == 0 && $v['kuaidi'] != '德邦' && $orderFees && $feeOver==1){
				$bu=2; 
			}
			if($orderFee == $v['sumMoneyYuan_old'] && $v['diffStatus'] == 1 && $v['kuaidi'] != '德邦' && $orderFees && $feeOver==1){
				$bu=2; 
			}
			if($orderFee == $v['sumMoneyYuan_old'] && $v['diffStatus'] != 2  && $orderFees && $feeOver==1){
				$bu=2; 
			}
			if($orderFee == $v['sumMoneyYuan_old'] && $v['diffStatus'] == 0  && $orderFees && $feeOver==1){
				$bu=2; 
			}
			if($orderFee == $v['sumMoneyYuan_old'] && $v['diffStatus'] == 1 && $orderFees && $feeOver==1){
				$bu=2; 
			}
			if($orderFee < $v['sumMoneyYuan_old']  && $orderFees && $feeOver==1){
				$bu=2; 
			}
			if($orderFee < $v['sumMoneyYuan_old']  && !$orderFees && $feeOver==1){
				$bu=3; 
			}
			
			
			if($bu==2){
				$v['diffMoneyYuan'] = $orderFee-$v['sumMoneyYuan_old'];
				$v['diffMoneyYuan'] = (int)$v['diffMoneyYuan'];
				if($v['diffMoneyYuan'] >=0){
				    $v['diffMoneyYuan'] = (int)$v['diffMoneyYuan']+$orderFees;
				}else{
				    $v['diffMoneyYuan'] = (int)$orderFees;
				}
				
				if($v['diffMoneyYuan']){
					$handle_info .= '云腾旺店管家补差价耗材费【'.round($freightHaocai/100,2).'】+保价费'.round($freightInsured/100,2);	
					$updataData['diffStatus']=1;
					$updataData['review_weight']=$calWeight;
					$updataData['diffMoneyYuan']=$v['diffMoneyYuan'];
					$updataData['insurancePrice']=$freightInsured;
					$updataData['packageServicePrice']=$freightHaocai;
					Db::name('express_order')->where(array('id'=>$v['id']))->update($updataData);
					model('Sms')->sendSmsTmplSend($v,$v['user_id'],$title = '补差价通知');
					model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '补差价通知');
					model('Ad')->dingTalkWebhook($dd_msg=7,'云洋单号【'.$waybill.'】通知用户补差价','');//云洋钉钉通知
				}
			}
			if($bu==1){
				$diffMoneyYuan = model('Setting')->getDiffMoney($v['user_id'],$calWeight,$v['wight'],$orderFee,$v,0,0);
				if($diffMoneyYuan){
					$v['diffMoneyYuan'] = $diffMoneyYuan;
					$cha_weight = $calWeight-$v['wight'];
					$handle_info .= '实际重量'.$context["calWeight"].'KG-计费重量'.$calWeight.'KG-实际收费【'.round($orderFee/100,2).'】补差价'.round($diffMoneyYuan/100,2);
					$updataData['diffStatus']=1;
					$updataData['review_weight']=$calWeight;
					$updataData['diffMoneyYuan']=$diffMoneyYuan;
					$updataData['insurancePrice']=$freightInsured;
					$updataData['packageServicePrice']=$freightHaocai;
					Db::name('express_order')->where(array('id'=>$v['id']))->update($updataData);
					model('Sms')->sendSmsTmplSend($v,$v['user_id'],$title = '补差价通知');
					model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '补差价通知');
					model('Ad')->dingTalkWebhook($dd_msg=7,'云洋单号【'.$waybill.'】通知用户补差价','');//云洋钉钉通知
				}
			 }
	    }
		
		//云腾旺店管家取消订单
		$cancel=0; 
	    if($type=='下单取消'){
			$cancel=1; 
		}
		if($type=='已取消'){
			$cancel=1; 
		}
		if($cancel==1 && $v['orderStatus'] != '-1'  && $v['orderStatus'] != '5' && $v['orderRightsStatus'] == 0){
			$handle_info = '云腾旺店管家取消订单';
			if($plan_order_cancel){
				model('ExpressOrder')->cancel($v,$v['id'],$reason='接口方取消订单',$cancel_money=0,$checkOrderStatus=1);//云洋接口方取消订单
			}else{
				Db::name('express_order')->where(array('id'=>$v['id']))->update(array(
					'orderStatus'=>-1,
					'orderRightsStatus'=>1
				));
			}
		}
		$complete = model('Push')->get_yy_complete_status($v,$type);//获取云洋签收状态
		if($complete){
			$handle_info = '云腾旺店管家订单已完成';
			Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>4,'TotalFee'=>$totalFreight,'orderStatusName'=>$type,'requestParams'=>'','requestParams2'=>''));
			model('ExpressOrder')->completeOrder($v,$v['user_id'],'订单已完成');
		}
		$data['deliveryId'] = $waybill;
		$data['orderStatusName'] = $type;
		$data['orderStatus'] = $orderStatus;
		$data['handle_info'] = $handle_info;
		return $data;
	}
	
	
	
	//云腾旺店管家
	public function performance($content,$Method ='CHECK_CHANNEL'){
		$config = model('Setting')->fetchAll2();
		$url = trim($config['wxapp']['yt_url']);
		if($Method=='ADD_SENDADDRESS'){
			$url = 'https://www.yuntengwd.com/yuntenghost/api/wuliu/openServiceAddSendAddress';
		}
		if($Method=='BINDING_ZTORDER' || $Method=='QUERY_ZTCHANNEL' || $Method=='SUBMIT_ZTORDERPICKUPCODE'){
			$url = 'https://www.yuntengwd.com/yuntenghost/api/wuliu/openServiceZt';
		}
		$appid = trim($config['wxapp']['yt_appid']);
		$requestId = rand_string(32,3);
		list($t1,$t2) = explode(' ',microtime()); 
	    $timeStamp = (int)((floatval($t1)+floatval($t2))*1000);
		$timeStamp = (string) $timeStamp;
		$secretKey = trim($config['wxapp']['yt_secretKey']);
		$body = array(
			 "serviceCode" =>$Method,
			 "timeStamp" => $timeStamp,
			 "requestId"=> $requestId,
			 "appid" => $appid,
			 "sign"=> $this->getSign($appid,$requestId,$timeStamp,$secretKey),
			 "content"=> $content,
		 );
		 $header = array("Content-Type:application/json");
		 $curl = curl_init();
		 curl_setopt($curl, CURLOPT_URL,$url);
		 curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body,320));
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		 $result = curl_exec($curl);
		 curl_close($curl);
		 return json_decode($result, true);
	}
	
	
	
	public function getSign($appid,$requestId,$timeStamp,$secretKey){
		$sb = $appid.$requestId.$timeStamp.$secretKey;
        return md5($sb);
	}
	
	
	
	
}