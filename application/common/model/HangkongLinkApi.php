<?php
namespace app\common\model;

use think\Db;
use think\Model;
use think\Cache;


class HangkongLinkApi extends Base{
 	protected $pk = 'cate_id';
    protected $tableName = 'express_cate';
    protected $token = 'jin_express_cate';
    protected $settings = null;
	
	
	



	
	public function getError(){
        return $this->error;
    }
	
	
	public function getExpressList7($data){
		$config = model('Setting')->fetchAll2();
		$TotalFee = $config['wxapp']['hangkong_Money']*100;
		$cate = Db::name('express_cate')->where(array('type'=>6,'firstPrice'=>0))->limit(0,1)->select();
		if($cate){
			foreach($cate as $k => $v){
			$c = $v;
				if($c){
					$i++;
					$expressList[$i]['freightInsured'] = 0;
					$expressList[$i]['c_type'] =$c['type'];
					$expressList[$i]['lanshou'] =$c['lanshou'];
					$expressList[$i]['info'] =$c['info'];
					$expressList[$i]['orderby'] =$c['orderby'];
					$expressList[$i]['firstPrice'] =(int)$c['firstPrice'];
					$expressList[$i]['img'] =config_weixin_img($c['photo']);
					$expressList[$i]['nickname'] = cut_msubstr($c['cate_name'],0,6,true);
					$expressList[$i]['name'] = $c['cate_name'];
					$expressList[$i]['title'] = '';
					$expressList[$i]['freight'] = 0;
					$expressList[$i]['channelId'] = $c['cate_id'];
					$expressList[$i]['channel'] = $c['cate_id'];
					$expressList[$i]['transportType'] = $c['pinyin'];
					$expressList[$i]['type'] = 6;
					$expressList[$i]['tag']= $c['tag'] ? $c['tag'] : '未定义';
					
					
					$getCatePrice = model('Setting')->getCatePrice($data['uid'],1,$TotalFee,0,0,0,0,$c);//没有加价
					$expressList[$i]['discount'] = $getCatePrice['discount'];
					$expressList[$i]['vip_discount'] = $getCatePrice['vip_discount'];
					$expressList[$i]['original_cost'] = $getCatePrice['original_cost'];
					$expressList[$i]['sumMoneyYuan'] = (int)$getCatePrice['sumMoneyYuan'];
					$expressList[$i]['yuanMoney'] = $getCatePrice['yuanMoney'];
					
					if($c['is_bao']==0){
						$expressList[$i]['is_baojia'] = 0;
					}else{
						$expressList[$i]['is_baojia'] = 1;
					}
					if($c['is_yuyue']==0){
						$expressList[$i]['is_yuyue'] = 0;
					}else{
						$expressList[$i]['is_yuyue'] = 1;
					}
					if($getCatePrice['sumMoneyYuan'] == 0){
						unset($expressList[$i]);
					}
					if($cate_id && $c['cate_id'] != $cate_id){
						unset($expressList[$i]);
					}
					if($c['firstPrice']==1){
						unset($expressList[$i]);
					}
				}
			}
		}
		$expressList =@array_values($expressList);
		return $expressList;
	}
	
	public function get_status_name($status=0){
		if($status==1){$status_name = '预约中';}
		if($status==2){$status_name = '待支付';}
		if($status==3){$status_name = '待打印托运条';}
		if($status==4){$status_name = '派单中';}
		if($status==5){$status_name = '待取件';}
		if($status==6){$status_name = '运输中';}
		if($status==7){$status_name = '机场接收中';}
		if($status==8){$status_name = '已送达';}
		if($status==9){$status_name = '已入港';}
		if($status==11){$status_name = '出港中';}
		if($status==12){$status_name = '已出港';}
		if($status==13){$status_name = '出港待领取';}
		if($status==14){$status_name = '配送员接收中';}
		if($status==15){$status_name = '运输中';}
		if($status==16){$status_name = '已送达（待签收）';}
		if($status==17){$status_name = '已完成（已签收）';}
		if($status==18){$status_name = '已取消(系统)';}
		if($status==19){$status_name = '已取消(用户)';}
		return $status_name;
	}
	

	//领航当日达回调
	public function hangkongLink_push($eop,$eo){
		$config = model('Setting')->fetchAll2();
		$plan_order_cancel = (int)$config['config']['plan_order_cancel'];//自动退款
		$v = $eo;
		$context = @json_decode($eop['context'],true);
		
		$luggageOrder = $context['luggageOrder'];
		$distribuitor = $context['distribuitor'];
		
		$luggageInfos = $input['luggageInfos'][0];
		$status = $luggageOrder['status'];
		$orderStatusName = model('HangkongLinkApi')->get_status_name($status);
		
		if($status==4 || $status==5){
			$name = $distribuitor['name'];
			if($name){
				$up['realOrderName'] = $name;
			}
			$phone = $distribuitor['phone'];
			if($phone){
				$up['realOrderMobile'] = $phone;
			}
			if($name&&$phone){
				$up['realOrderState'] = $name.'-'.$phone;
			}
			$up['orderStatus'] = 2;
			$up['orderStatusName'] = $orderStatusName;
			Db::name('express_order')->where(array('id'=>$v['id']))->update($up); 	
			model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '领航当日达接单成功提醒',$name,$phone);
		}
		if($status>=5 && $status<=15){
			$up['orderStatus'] = 3;
			$up['orderStatusName'] = $orderStatusName;
		}
		
		if($status==18){
			$up['orderStatusName'] = $orderStatusName;
			if($v['orderStatus'] != '-1'  && $v['orderStatus'] != '5' && $v['orderRightsStatus'] == 0){
				if($plan_order_cancel){
					model('ExpressOrder')->cancel($v,$v['id'],$reason='接口方取消订单',$cancel_money=0,$checkOrderStatus=1);
				}else{
					Db::name('express_order')->where(array('id'=>$v['id']))->update(array(
						'orderStatus'=>-1,
						'orderRightsStatus'=>1
					)); 	
				}
			}
		}
		if($status==16 || $status==17){
			$up['orderStatus'] = 4;
			$up['orderStatusName'] = $orderStatusName;
			Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>4,'requestParams'=>'','requestParams2'=>'','requestParams5'=>''));
			model('ExpressOrder')->completeOrder($v,$v['user_id'],'订单已完成');
		}
		if($up){
		   Db::name('express_order')->where(array('id'=>$v['id']))->update($up); 	 
		}
		$data['handle_info'] = $orderStatusName;
		return $data;
	}
	
	
	//支付
	public function orderPay($v,$shop_id=0){
		$requestParams['orderCode'] = $v['deliveryId'];
		$post = model('HangkongLinkApi')->hangkongLink_post($requestParams,$method='/triplh-api/open/luggage/v1.0/orderPay');
		if($post['code'] == 200000){
			return true;
		}else{
			$this->error = '支付失败-'.$post['msg'];
			return false;
		}
		$this->error = '未知错误';
		return false;
	}
	
	//支付
	public function orderInfo($v,$shop_id=0){
		$requestParams['orderCode'] = $v['deliveryId'];
		$post = model('HangkongLinkApi')->hangkongLink_post($requestParams,$method='/triplh-api/open/luggage/v1.0/orderInfo');
		if($post['code'] == 200000){
			return $post['data'];
		}else{
			$this->error = '查看失败-'.$post['msg'];
			return false;
		}
		$this->error = '未知错误';
		return false;
	}
	
 
 	public function hangkongLink_post($requestParams,$Method,$shop_id=0){
		 $config = model('Setting')->fetchAll2(0);
		 list($t1,$t2) = explode(' ',microtime()); 
		 $timestamp = (int)((floatval($t1)+floatval($t2))*1000);
		 $timestamp = (string) $timestamp;
		 
		 $url = 'https://t-api.triplh.com'.$Method;
		 $hangkong_appId = trim($config['wxapp']['hangkong_appId']);
		 $hangkong_Key = trim($config['wxapp']['hangkong_Key']);
		 
		 
		 $myArray['appId'] = $hangkong_appId;
		 $myArray['timestamp'] = $timestamp;
		 $requestParams = $myArray + $requestParams;
		 $sign  = strtoupper(md5(json_encode($requestParams,320).$hangkong_Key));
		 $sign_Array = array(
		  	"appId" => $hangkong_appId,
		  	"sign"  => $sign,
		  	"timestamp"   => $timestamp
		 );
		 $body = array_merge($sign_Array,$requestParams);
		 $json = json_encode($body,320);
		
		 $header = array("Content-Type:application/json");
		 $curl = curl_init();
		 curl_setopt($curl, CURLOPT_URL,$url);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0); 
		 curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,$json);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		 $result = curl_exec($curl);
		 curl_close($curl);
		 return json_decode($result, true);
	}
 
	
	
	
}