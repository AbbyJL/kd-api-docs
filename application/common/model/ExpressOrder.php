<?php

namespace app\common\model;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class ExpressOrder extends Base{


    protected $pk = 'id';
    protected $tableName = 'express_order';
    protected $token = 'express_order';
	
	protected function _initialize(){
        parent::_initialize();
		$this->config = model('Setting')->fetchAll2();
    }
	
	public function getError(){
        return $this->error;
    }
	
	
	//最低分销权限
	public function determinePower($u){
		$rank_id = (int)$this->config['profit']['profit_min_rank_id'];
		if($rank_id == 0){
			return true;
		}
		if($u['rank_id'] >= $rank_id){
			return true;
		}
		return false;
	}
	
	
	//寻找自己最近的上级
	public function get_parent_branch($user_id,$rank_id=0,$num=1,$uid=0){
        $u = Db::name('users')->where(array('user_id'=>$user_id,'closed'=>0))->field('user_id,parent_id,rank_id,stock,nickname')->find();
		if($u){
			if($u['rank_id'] == $rank_id && $user_id != $uid){
				 return $u;
			}
		}
		if($u['parent_id'] && $num<31){
			$num++;
			return $this->get_parent_branch($u['parent_id'],$rank_id,$num+1,$uid);
		}
    }
	
	
    //获取优惠券核销码
	public function getCode(){       
        $i=0;
        while(true){
            $i++;
            $code = rand_string(8,1);
            $data = Db::name('coupon_download')->where(array('code'=>$code))->find();
            if(empty($data)) return $code;
            if($i > 10) return $code;
        }
    }
	
	//赠送优惠券
	public function giveCoupon($v,$user_id,$title){
		//新人有礼关注公众号送
		//满额返礼
		$flag = 0;
		if($v['sumMoneyYuan'] >= 3000){
			model('ExpressOrder')->sendCouponDownload($user_id,'满额返礼');//送优惠券满额返礼
		}else{
			model('ExpressOrder')->sendCouponDownload($user_id,'寄件返礼');//送优惠券寄件返礼
		}
		return true;
	}
	public function getSubstr($str, $leftStr){
		$left = strpos($str, $leftStr);
    	return substr($str, $left + strlen($leftStr));
	}
	
	public function get_preg_replace($str){
		$str=preg_replace("/\\d+/",'', $str);
    	return $str;
	}
	
	
	
	public function getLogisticsInfo($info=array(),$t=0,$mailNo=''){
		@file_put_contents('/tmp/zf_debug.log', '['.date('Y-m-d H:i:s').'][logisticsInfo] 入参 type='.($info['type'] ?? '').' order_id='.($info['id'] ?? '').' deliveryId='.($info['deliveryId'] ?? '').' expressNo='.($info['expressNo'] ?? '').' expressId='.($info['expressId'] ?? '').' t='.$t.' mailNo='.$mailNo."\n", FILE_APPEND);
        @file_put_contents('/tmp/zf_debug.log', '['.date('Y-m-d H:i:s').'][getLogisticsInfo] 入参 type='.($info['type'] ?? '').' order_id='.($info['id'] ?? '').' deliveryId='.($info['deliveryId'] ?? '').' mailNo='.$mailNo.' t='.$t."\n", FILE_APPEND);
		$config = model('Setting')->fetchAll2();
		$express_api_type = (int)$config['config']['express_api_type'];
		@file_put_contents('/tmp/zf_debug.log', '['.date('Y-m-d H:i:s').'][getLogisticsInfo] fetchAll2 express_api_type='.$express_api_type.' AppCode='.(trim($config['config']['AppCode'] ?? '') !== '' ? '已配置' : '空').' kuaidi100_customer='.(trim($config['config']['express_api_customer'] ?? '') !== '' ? '已配置' : '空')."\n", FILE_APPEND);
	
		$AppCode = trim($config['config']['AppCode']);
		if($express_api_type==1){
			$host = "https://ali-deliver.showapi.com";
			$path = "/showapi_expInfo";
			$method = "GET";
			$appcode = $AppCode;
			$headers = array();
			array_push($headers, "Authorization:APPCODE ".$appcode);
			$querys = "nu=".trim($mailNo);
		    $querys = "com=auto&nu=".trim($mailNo)."&receiverPhone=".$info['receiveMobile']."&senderPhone=".$info['sendMobile'];
			$bodys = "";
			$url = $host . $path . "?" . $querys;
			$curl = curl_init(); 
			curl_setopt($curl, CURLOPT_URL, $url);            
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			if(ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);//使用自动跳转
			}
			curl_setopt($curl, CURLOPT_AUTOREFERER, 1); 
			curl_setopt($curl, CURLOPT_HTTPGET, 1); 
			curl_setopt($curl, CURLOPT_TIMEOUT, 30); 
			curl_setopt($curl, CURLOPT_HEADER, 0); 
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);    
			$tmpInfo = curl_exec($curl); 
			if(curl_errno($curl)){
				echo 'Errno' . curl_error($curl);
			}
			curl_close($curl);
			$tmpInfo = json_decode($tmpInfo,true);
			$traceList = $tmpInfo['showapi_res_body']['data'];
			foreach($traceList as $k => $v){
				$logistics_info[$k] .= $v['time'].'-'.$v['context'];
			}
			foreach($traceList as $k => $v){
				$logisticsInfo[$k]['desc'] = $v['desc'];
				$logisticsInfo[$k]['time'] = $v['time'];
			}
			$pressList = $traceList;
			$result['logistics_info']= $logistics_info;
			$result['logisticsInfo']= $logisticsInfo;
			$result['mailNo']= $mailNo;
			$result['pressList']= $pressList;
		}
		if($express_api_type==2){
			$post_data = array();
			$post_data["customer"] = $config['config']['express_api_customer'];
			$key= $config['config']['express_api_key'];
			$post_data["param"] = '{"com":"'.trim('').'","num":"'.$mailNo.'"}';
			$url='https://poll.kuaidi100.com/poll/query.do';
			$post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
			$post_data["sign"] = strtoupper($post_data["sign"]);
			$o=""; 
			foreach($post_data as $k=>$v){
				$o.= "$k=".urlencode($v)."&";
			}
			$post_data=substr($o,0,-1);
			$this->curl = new \Curl();
			$result = $this->curl->post($url,$post_data);
			$result = json_decode($result,true);
			$traceList = $result['data'];
			foreach($traceList as $k => $v){
				$logistics_info[$k] .= $v['time'].'-'.$v['context'];
			}
			foreach($traceList as $k => $v){
				$logisticsInfo[$k]['desc'] = $v['desc'];
				$logisticsInfo[$k]['time'] = $v['time'];
			}
			$pressList = $traceList;
			$result['logistics_info']= $logistics_info;
			$result['logisticsInfo']= $logisticsInfo;
			$result['mailNo']= $mailNo;
			$result['lanshou_time']= '';
			$result['express_status']= '';
			$result['pressList']= $pressList;
		}
		return  $result;	
	}
	
	//获取取件码
	public function realOrderData($str='',$kuaidi='圆通',$context=''){
		
	    if($context['courierName'] && $context['courierPhone']){
	        $data['realOrderName'] = $context['courierName'];
    	    $data['realOrderMobile'] = $context['courierPhone'];
    		$data['realOrderCode'] = $context['pickupCode']; 
	    }elseif($context['courierPhone'] && $kuaidi=='顺心捷达'){
            $courierPhone = explode(',',$context['courierPhone']);
            if($courierPhone[0]){
                $data['realOrderMobile'] = $courierPhone[0];
            }else{
                $data['realOrderMobile'] = $context['courierPhone'];
            }

            if($context['courierName']){
                $data['realOrderName'] = $context['courierName'];
            }else{
                $courierName= $this->getSubstr($str,"订单已受理，网点:","");
                $courierName = explode('，',$courierName);
                if( $courierName[0]){
                    $data['realOrderName'] = $courierName[0];
                }else{
                    $data['realOrderName'] = '网点受理';
                }
            }
            $data['realOrderCode'] = $context['pickupCode'];
        }elseif($kuaidi=='圆通'){
	        $realOrderName= $this->getSubstr($str,"快递员姓名:","");
    		$realOrderName=@msubstr($realOrderName,0,3);
    		$realOrderMobile= $this->getSubstr($str,"电话:","");
    		$realOrderMobile=@msubstr($realOrderMobile,0,11);
    		$realOrderCode= $this->getSubstr($str,"取件码:","");
    		$realOrderCode=@msubstr($realOrderCode,0,13);
    		$realOrderCode = explode(" ",$realOrderCode);
    		$data['realOrderName'] = $this->get_preg_replace($realOrderName);
    		$data['realOrderMobile'] = $realOrderMobile;
    		$data['realOrderCode'] = $realOrderCode[0];
	    }elseif($kuaidi=='顺丰'){
	        $realOrderName= $this->getSubstr($str,"快递员工号：","");
    		$realOrderName=@msubstr($realOrderName,0,8);
    		$realOrderMobile= $this->getSubstr($str,"快递员电话：","");
    		$realOrderMobile=@msubstr($realOrderMobile,0,11);
    		$data['realOrderName'] = $this->get_preg_replace($realOrderName);
    		$data['realOrderMobile'] = $realOrderMobile;
    		$data['realOrderCode'] = '';
	    }elseif($kuaidi=='德邦'){
	        $realOrderName= $this->getSubstr($str,"接货中","");
    		$realOrderName=@msubstr($realOrderName,0,3);
    		$realOrderMobile= $this->getSubstr($str,"快递员电话：","");
    		$realOrderMobile=@msubstr($realOrderMobile,0,11);
    		$data['realOrderName'] = $this->get_preg_replace($realOrderName);
    		$data['realOrderMobile'] = $realOrderMobile;
    		$data['realOrderCode'] = '';
	    }else{
	        $realOrderName= $this->getSubstr($str,"快递员名称：","");
    		$realOrderName=@msubstr($realOrderName,0,3);
    		$realOrderMobile= $this->getSubstr($str,"快递员电话：","");
    		$realOrderMobile=@msubstr($realOrderMobile,0,11);
    		$realOrderCode= $this->getSubstr($str,"取件码：","");
    		$realOrderCode=@msubstr($realOrderCode,0,13);
    		$realOrderCode = explode(",",$realOrderCode);
    		$data['realOrderName'] = $this->get_preg_replace($realOrderName);
    		$data['realOrderMobile'] = $realOrderMobile;
    		$data['realOrderCode'] = $realOrderCode[0];
	    }
		return  $data;
	}
	
	/**
	 * 众发轨迹 API 响应 → 统一展示结构（logistics_info / logisticsInfo / pressList）
	 * trackingList 按时间倒序（最新在前），与接口文档一致
	 */
	public function formatZhongfaTrackDisplay($trackResult){
		$logistics_info = array();
		$logisticsInfo = array();
		$pressList = array();
		if(!is_array($trackResult)){
			return array(
				'logistics_info' => $logistics_info,
				'logisticsInfo' => $logisticsInfo,
				'pressList' => $pressList,
			);
		}
		$zfCode = isset($trackResult['code']) ? (string)$trackResult['code'] : '';
		$ok = ($zfCode === '0' || $zfCode === '00')
			|| (isset($trackResult['success']) && $trackResult['success']);
		if(!$ok){
			return array(
				'logistics_info' => $logistics_info,
				'logisticsInfo' => $logisticsInfo,
				'pressList' => $pressList,
			);
		}
		$zfData = isset($trackResult['data']) && is_array($trackResult['data']) ? $trackResult['data'] : array();
		$trackingList = !empty($zfData['trackingList']) && is_array($zfData['trackingList'])
			? $zfData['trackingList'] : array();
		if(empty($trackingList) && !empty($zfData['logisticsStatusDesc'])){
			$trackingList = array(array(
				'trackTime' => '',
				'description' => (string)$zfData['logisticsStatusDesc'],
				'location' => '',
			));
		}
		foreach($trackingList as $k => $v){
			if(!is_array($v)){
				continue;
			}
			$time = isset($v['trackTime']) ? trim((string)$v['trackTime']) : '';
			$desc = isset($v['description']) ? trim((string)$v['description']) : '';
			$location = isset($v['location']) ? trim((string)$v['location']) : '';
			if($location !== ''){
				$desc = $desc !== '' ? $desc.' ['.$location.']' : $location;
			}
			$logistics_info[$k] = ($time !== '' ? $time.'-' : '').$desc;
			$logisticsInfo[$k]['desc'] = $desc;
			$logisticsInfo[$k]['time'] = $time;
			$pressList[$k] = array(
				'trackTime' => $time,
				'time' => $time,
				'description' => isset($v['description']) ? (string)$v['description'] : '',
				'desc' => $desc,
				'location' => $location,
				'logisticsStatus' => isset($v['logisticsStatus']) ? $v['logisticsStatus'] : '',
				'context' => $desc,
			);
		}
		return array(
			'logistics_info' => $logistics_info,
			'logisticsInfo' => $logisticsInfo,
			'pressList' => $pressList,
		);
	}

	
	public function logisticsInfo($info,$t=1,$mailNo=0){
		if($info['type'] == 1){
			$logoUrl = model('ExpressOrder')->logoUrl($info['kuaidi'],$info['user_id'],$info['type']);
			$requestParams = array(
				'deliveryId'=>$info['deliveryId'],
				'deliveryType'=>$logoUrl['deliveryType'],
			);
			$execute = model('Setting')->execute($requestParams,$Method='DELIVERY_TRACE');
			$logistics_info = array();
			if($execute['data']){
				foreach($execute['data'] as $k=>$v){
					$logistics_info[$k][] = $v['desc'].$v['time'];
				}
			}
            foreach($execute['data'] as $k => $v){
                $logisticsInfo[$k]['desc'] = $v['desc'];
                $logisticsInfo[$k]['time'] = $v['time'];
            }
            $pressList = $execute['data'];
		}elseif($info['type'] == 2){
		    
			if($info['expressNo'] && $info['expressNo'] != $info['deliveryId']){
				$requestParams = array('waybill'=>$info['deliveryId'],'shopbill'=>$info['expressNo'],'traceFormat'=>'obj');
			}else{
				$requestParams = array('waybill'=>$info['deliveryId'],'shopbill'=>NULL,'traceFormat'=>'obj');
			}
			$performance = model('Setting')->performance($requestParams,$Method ='QUERY_TRANCE');
			$logistics_info = $performance['result'];
			$pressList = $performance['result'];
			foreach($pressList as $k => $v){
				if($v['comments']){
					$logistics_info[$k] = $v['comments'];
				}elseif($v['title']){
					$logistics_info[$k] = $v['title'].$v['desc'].$v['time']; 
				}else{
					$logistics_info[$k] = $v['desc'].'-'.$v['time'];
				}
			}
			foreach($pressList as $k => $v){
                if($v['comments']) {
                    $logisticsInfo[$k]['desc'] = $v['comments'];
                    $logisticsInfo[$k]['time'] = '';
                }else{
                    $logisticsInfo[$k]['desc'] = $v['title'].'-'.$v['desc'];
                    $logisticsInfo[$k]['time'] = $v['time'];
                }
			}
		}elseif($info['type'] == 3){
			$content['orderOrigin'] = 0;
			$content['waybillCode'] = $info['deliveryId'];
			$content['orderCode'] = $info['expressNo'];
			$content = array($content);
			$kjd_post = model('JdApi')->jd_post($content,$method='/ecap/v1/orders/trace/query');
			$pressList = $kjd_post['data']['traceDetails'];
			foreach($pressList as $k=>$v){
				$logistics_info[$k][] = $v['categoryName'].$v['operationRemark'].$v['operationTime'];
			}
		}elseif ($info['type'] == 4) {
            $requestParams['kdnOrderCode'] = $info['expressId'];
            $kdnSendPost = model('Setting')->kdnSendPost($requestParams, $RequestType = '1816');
            if ($kdnSendPost['Success'] == true) {
                $Data = $kdnSendPost['Data'];
                foreach ($Data as $k => $v) {
                    foreach ($v as $ka => $va) {
                        $new_arr[] = $va;
                    }
                }
            }
            $traceList = $new_arr;
            foreach ($new_arr as $k => $v) {
                $logistics_info[$k] .= $v['pushTime'] . '-' . $v['reason'];
            }
         }elseif($info['type'] == 5){
			$requestParams = iunserializer($info['requestParams']);
			$biz['waybillNumbers'][0] = $info['deliveryId'];
			$biz['customerCode'] = $requestParams['customerCode'];
			$biz['platformFlag'] = $requestParams['platformFlag'];
			$queryPublicRoute= model('KuayueApi')->queryPublicRoute($biz);
			$exteriorRouteList = $queryPublicRoute['data']['esWaybill'];
			$exteriorRouteList = $exteriorRouteList[0]['exteriorRouteList'];
			foreach($exteriorRouteList as $k => $v){
				if($v['dispatchDriverPhone']){
			        $logistics_info[$k] .= $v['routeStep'].$v['routeDescription'].' '.$v['dispatchDriverName'].'-'.$v['dispatchDriverPhone'].' 时间'.$v['uploadDate'];
			    }else{
			       $logistics_info[$k] .= $v['routeStep'].$v['routeDescription'].'-'.$v['uploadDate']; 
			    }
			}
			$pressList = $exteriorRouteList;
		}elseif($info['type'] == 6){
			$requestParams['orderCode'] = $info['deliveryId'];
			$post = model('HangkongLinkApi')->hangkongLink_post($requestParams,$method='/triplh-api/open/luggage/v1.0/traceList');
			$Traces = $post['data']['traceList'];
			foreach($Traces as $k => $v){
				$logistics_info[$k] .= $v['statusDesc"'].'-'.$v['statusTime'];
			}
			foreach($Traces as $k => $v){
				$logisticsInfo[$k]['desc'] = $v['statusDesc"'];
				$logisticsInfo[$k]['time'] = $v['statusTime'];
			}
			$pressList = $Traces;
		}elseif($info['type'] == 7){
			$requestParams6 = iunserializer($info['requestParams6']);
			$sendPost['type'] = $requestParams6['type'];
			$sendPost['orderNo'] = $info['expressId'];
			$ulifego = model('UlifegoApi')->ulifego_post($sendPost,$method='/openApi/getOrderDetail');
			$data = $ulifego['data'];
			$traceList = $data['traceList'];
			foreach($traceList as $k => $v){
				$logistics_info[$k] .= $v['opeTitle'].'-'.$v['createDate'].'-'.$v['opeRemark'];
			}
			foreach($traceList as $k => $v){
				$logisticsInfo[$k]['desc'] = $v['opeTitle'].'-'.$v['opeRemark'];
				$logisticsInfo[$k]['time'] = $v['createDate'];
			}
			$pressList = $traceList;
		}elseif($info['type'] == 8 || $info['type'] == 10){
			$mailNo = $info['deliveryId'];
			$result = model('ExpressOrder')->getLogisticsInfo($info,$t,$mailNo);
			$logistics_info = $result['logistics_info'];
			$pressList = $result['pressList'];
			$mailNo = $result['logistics_info'];
		}elseif($info['type'] == 9){
            if($info['expressNo'] && $info['expressNo'] != $info['deliveryId']){
                $requestParams = array('waybill'=>$info['deliveryId'],'shopbill'=>$info['expressNo'],'traceFormat'=>'obj');
            }else{
                $requestParams = array('waybill'=>$info['deliveryId'],'shopbill'=>NULL,'traceFormat'=>'obj');
            }
            $performance = model('YtApi')->performance($requestParams,$Method ='QUERY_TRANCE');
            $logistics_info = $performance['result'];
            $pressList = $performance['result'];
            foreach($pressList as $k => $v){
                if($v['comments']){
                    $logistics_info[$k] = $v['comments'];
                }elseif($v['title']){
                    $logistics_info[$k] = $v['title'].$v['desc'].$v['time'];
                }else{
                    $logistics_info[$k] = $v['desc'].'-'.$v['time'];
                }
            }
            foreach($pressList as $k => $v){
                $logisticsInfo[$k]['desc'] = $v['title'].'-'.$v['desc'];
                $logisticsInfo[$k]['time'] = $v['time'];
            }
        }elseif($info['type'] == 11){
            if($info['orderType']==0){
                $requestParams = array(
                    'waybill'=>$info['deliveryId'],
                    'shopbill'=>$info['expressId'],
                );
                $performance = model('City')->performance($requestParams,$Method ='QUERY_TRANCE');
                $logistics_info = $performance['result'];
                $pressList = $performance['result'];
            }
        }elseif($info['type'] == 15){
			@file_put_contents('/tmp/zf_debug.log', '['.date('Y-m-d H:i:s').'][logisticsInfo] 众发轨迹1 order_id='.($info['id'] ?? '').' expressId='.($info['expressId'] ?? '').' expressNo='.($info['expressNo'] ?? '').' deliveryId='.($info['deliveryId'] ?? '')."\n", FILE_APPEND);
			$trackResult = model('Setting')->zhongfaQueryTrack($info);
			@file_put_contents('/tmp/zf_debug.log', '['.date('Y-m-d H:i:s').'][logisticsInfo] 众发轨迹2 order_id='.($info['id'] ?? '').' expressId='.($info['expressId'] ?? '').' expressNo='.($info['expressNo'] ?? '').' deliveryId='.($info['deliveryId'] ?? '')."\n", FILE_APPEND);
			$formatted = model('Setting')->formatZhongfaTrackDisplay($trackResult);
			$logistics_info = $formatted['logistics_info'];
			$logisticsInfo = $formatted['logisticsInfo'];
			$pressList = $formatted['pressList'];
			@file_put_contents('/tmp/zf_debug.log', '['.date('Y-m-d H:i:s').'][logisticsInfo] 众发轨迹响应 code='.(isset($trackResult['code']) ? $trackResult['code'] : '').' msg='.(isset($trackResult['msg']) ? $trackResult['msg'] : (isset($trackResult['message']) ? $trackResult['message'] : '')).' count='.count((array)$pressList)."\n", FILE_APPEND);
		}
		
		if($t == 0){
			return $logistics_info;
		}elseif($t == 1){
			return $pressList;
		}elseif($t == 2){
            return $logisticsInfo;
        }
	}

    public function cancelCompleted($sign){
        if($sign['coupon_download_id']){
            $where['id'] = array('<>',$sign['id']);
            $where['coupon_download_id'] = array('=',$sign['coupon_download_id']);
            $where['orderStatus'] = array('in',array(0,1,2,3,4));
            $where['closed'] = array('=',0);
            $c =(int)Db::name('express_order')->where($where)->count();
            if($c==0){
                $did = $sign['coupon_download_id'];
                Db::name('coupon_download')->where(array('download_id'=>$did))->update(array('used_time'=>'','order_id'=>'','used_info'=>$sign['id'].'-退款返回','is_used'=>0));
            }
        }
        if($sign['id']){
            model('ExpressOrder')->delete_user_profit_logs($sign['id']);
        }
        return true;
    }
	
	
	//取消订单
	public function cancel($sign,$id,$reason='',$cancel_money=0,$checkOrderStatus=0){
		//退款金额
		if($cancel_money > 0){
			$cancel_money = $cancel_money;	
		}else{
			$cancel_money = $sign['sumMoneyYuan'];
		}
		if($cancel_money <=0){
			$this->error = '退款金额有误1';
			return false;
		}
		if($sign['orderStatus'] == 0){
			$up2 = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>-1,'orderRightsStatus'=>2,'reason'=>$reason));
			if($up2){
				if($sign['coupon_download_id']){
					Db::name('coupon_download')->where(array('download_id'=>$sign['coupon_download_id']))->update(array('used_time'=>'','is_used'=>0));
				}
				return true;
			}
			$this->error = '订单状态【'.$sign['orderStatus'].'】取消失败';
			return false;
		}
		if($sign['coupon_download_id']){
			Db::name('coupon_download')->where(array('download_id'=>$sign['coupon_download_id']))->update(array('used_time'=>'','order_id'=>'','download_info'=>'退款返回优惠券','is_used'=>0));
		}
		model('ExpressOrder')->delete_user_profit_logs($sign['id']);
		if($sign['orderStatus'] == 9){
			$checkOrderStatus=1;
		}
		if($checkOrderStatus){
			model("express_order")->startTrans();
			try{
				$logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express'))->find();
				if($logs){
					$orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','订单异常退款');
					if($orderWeixinRefund == false){
						$this->error = '【'.model('PaymentLogs')->getError().'】退款失败';
						return false;
					}else{
						$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2,'reason'=>$reason));
						return true;
					}
				}else{
					
					if($sign['moneys']){
						model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
					}
					
					$rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'【'.$id.'】订单用户取消订单退款',5);
					if($rest){
						$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
						return true;
					}else{
						$this->error = '订单异常退款失败';
						return false;
					}
				}
				model('express_order')->commit();
				return true;
			}catch(\Exception $e){
				model('express_order')->rollback();
				$this->error = '错误'.$e->getMessage();
				return false;
			}
		}
		$falg=0;
		if($sign['type'] == 8){
			$falg =1;
		}
		if($sign['type'] == 6){
			$falg =1;
		}
		if($sign['type'] == 5){
			$falg =1;
		}
	    if($sign['type'] == 15){
			$falg =1;
		}
		if(!$sign['deliveryId'] && $falg==0){
			$this->error = '运单号不存在';
			return false;
		}
		
		if($sign['type'] == 1){
			$logoUrl = model('ExpressOrder')->logoUrl($sign['kuaidi']);
			$requestParams['deliveryId'] = $sign['deliveryId'];
			$requestParams['deliveryType'] = $logoUrl['deliveryType'];
			//易达取消订单
			$execute = model('Setting')->execute($requestParams,$Method='CANCEL_ORDER');
			if($execute['code'] == 200){
				model("express_order")->startTrans();
				try{
					
					if($sign['moneys']){
						model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
					}
					
					$logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
					if($logs){
						$orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','易达订单用户取消订单退款');
						if($orderWeixinRefund == false){
							$this->error = '失败'.model('PaymentLogs')->getError();
							return false;
						}else{
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}
					}else{
						$rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'1-【'.$id.'】订单用户取消订单退款',5);
						if($rest){
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}else{
							$this->error = '订单异常退款失败';
							return false;
						}
					}
					model('express_order')->commit();
					return true;
				}catch(\Exception $e){
					model('express_order')->rollback();
					$this->error = '错误'.$e->getMessage();
					return false;
				}
			}else{
				$this->error = '取消订单失败，退款失败，可以尝试人工退款，接口返回【'.$execute['msg'].'】';
				return false;
			}
		}elseif($sign['type'] == 2){
			
			if($sign['kuaidi'] == '德邦'){
				$requestParams = array(
					'waybill'=>$sign['expressNo'],
					'shopbill'=>$sign['deliveryId'],
				);
			}else{
				$requestParams = array(
					'waybill'=>$sign['deliveryId'],
					'shopbill'=>$sign['expressNo'],
				);
			}
			
			//云洋取消订单接口
			$performance = model('Setting')->performance($requestParams,$Method ='CANCEL');
			if($performance['code'] ==1){
				model("express_order")->startTrans();
				try{	
				
					if($sign['moneys']){
						model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
					}
				
					$logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
					if($logs){
						$orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','云洋订单用户取消订单退款');
						if($orderWeixinRefund == false){
							$this->error = '失败'.model('PaymentLogs')->getError();
							return false;
						}else{
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}
					}else{
						$rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'2-【'.$id.'】订单用户取消订单退款',5);
						if($rest){
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}else{
							$this->error = '订单异常退款失败';
							return false;
						}
					}
					model('express_order')->commit();
					return true;
				}catch(\Exception $e){
					model('express_order')->rollback();
					$this->error = '错误'.$e->getMessage();
					return false;
				}
			}else{
				$this->error = '取消订单失败'.$performance['message'];
				return false;
			}
		}elseif($sign['type'] == 3){
			
			$content['orderOrigin'] = 0;
			$content['cancelReasonCode'] = 1;
			$content['waybillCode'] = $sign['deliveryId'];
			$content['orderCode'] = $sign['expressNo'];
			$content['cancelReason'] = $reason ? $reason : '取消订单';
			$content = array($content);
			
			$kjd_post = model('JdApi')->jd_post($content,$method='/ecap/v1/orders/cancel');
			if($kjd_post['code'] ==0){
				model("express_order")->startTrans();
				try{	
				
					if($sign['moneys']){
						model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
					}
					
					
					$logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
					if($logs){
						$orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','京东订单用户取消订单退款');
						if($orderWeixinRefund == false){
							$this->error = '失败'.model('PaymentLogs')->getError();
							return false;
						}else{
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}
					}else{
						$rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'3-【'.$id.'】订单用户取消订单退款',5);
						if($rest){
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}else{
							$this->error = '订单异常退款失败';
							return false;
						}
					}
					model('express_order')->commit();
					return true;
				}catch(\Exception $e){
					model('express_order')->rollback();
					$this->error = '错误'.$e->getMessage();
					return false;
				}
			}else{
				$this->error = '取消订单失败'.$kjd_post['msg'];
				return false;
			}
		}elseif($sign['type'] == 4){
			$sendPost['OrderCode'] = $sign['expressNo'] ? $sign['expressNo'] : $sign['expressId'];
			$kdnSendPost= model('Setting')->kdnSendPost($sendPost,$RequestType='1802',$url='http://183.62.170.46:8081/api/dist');
			if($kdnSendPost['Success'] == true){
				model("express_order")->startTrans();
				try{	
					if($sign['moneys']){
						model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
					}
					
					$logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
					if($logs){
						$orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','快递鸟订单用户取消订单退款');
						if($orderWeixinRefund == false){
							$this->error = '失败'.model('PaymentLogs')->getError();
							return false;
						}else{
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}
					}else{
						$rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'4-【'.$id.'】订单用户取消订单退款',5);
						if($rest){
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}else{
							$this->error = '订单异常退款失败';
							return false;
						}
					}
					model('express_order')->commit();
					return true;
				}catch(\Exception $e){
					model('express_order')->rollback();
					$this->error = '错误'.$e->getMessage();
					return false;
				}
			}else{
				$this->error = '取消订单失败'.$kdnSendPost['Reason'];
				return false;
			}
		}elseif($sign['type'] == 5){

			
			$biz['waybillNumber'] = $sign['deliveryId'];
			$biz['customerCode'] = $sign['pdfUrl'];
			$biz['xdCode'] = '';
			
			$batchOrder= model('KuayueApi')->cancelOrder($biz);
			
			if($batchOrder['code'] == '10000'){
				model("express_order")->startTrans();
				try{	
					if($sign['moneys']){
						model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
					}
					
					$logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
					if($logs){
						$orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','订单用户取消订单退款');
						if($orderWeixinRefund == false){
							$this->error = '失败'.model('PaymentLogs')->getError();
							return false;
						}else{
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}
					}else{
						$rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'4-【'.$id.'】订单用户取消订单退款',5);
						if($rest){
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}else{
							$this->error = '订单异常退款失败';
							return false;
						}
					}
					model('express_order')->commit();
					return true;
				}catch(\Exception $e){
					model('express_order')->rollback();
					$this->error = '错误'.$e->getMessage();
					return false;
				}
			}else{
				$this->error = '取消订单失败'.$batchOrdert['msg'];
				return false;
			}
		}elseif($sign['type'] == 6){
			$requestParams['status'] = '18';
			$requestParams['orderCode'] = $sign['deliveryId'];
			$requestParams['cancelReason'] = $reason;
			$post = model('HangkongLinkApi')->hangkongLink_post($requestParams,$method='/triplh-api/open/luggage/v1.0/updateOrderStatus');
			if($post['code'] == 200000){
				model("express_order")->startTrans();
				try{	
					if($sign['moneys']){
						model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
					}
					
					$logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
					if($logs){
						$orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','订单用户取消订单退款');
						if($orderWeixinRefund == false){
							$this->error = '失败'.model('PaymentLogs')->getError();
							return false;
						}else{
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}
					}else{
						$rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'4-【'.$id.'】订单用户取消订单退款',5);
						if($rest){
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}else{
							$this->error = '订单异常退款失败';
							return false;
						}
					}
					model('express_order')->commit();
					return true;
				}catch(\Exception $e){
					model('express_order')->rollback();
					$this->error = '错误'.$e->getMessage();
					return false;
				}
			}else{
				$this->error = '取消订单失败-'.$post['msg'];
				return false;
			}
		}elseif($sign['type'] == 7){
			
			$requestParams = iunserializer($sign['requestParams6']);
			$sendPost['genre'] = 1;
			$sendPost['orderNo'] = $sign['expressId'];
		
			$ulifego = model('UlifegoApi')->ulifego_post($sendPost,$method='/openApi/doCancel');
			if($ulifego['code'] == 0){
				model("express_order")->startTrans();
				try{	
					if($sign['moneys']){
						model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
					}
					
					$logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
					if($logs){
						$orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','订单用户取消订单退款');
						if($orderWeixinRefund == false){
							$this->error = '失败'.model('PaymentLogs')->getError();
							return false;
						}else{
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}
					}else{
						$rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'4-【'.$id.'】订单用户取消订单退款',5);
						if($rest){
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}else{
							$this->error = '订单异常退款失败';
							return false;
						}
					}
					model('express_order')->commit();
					return true;
				}catch(\Exception $e){
					model('express_order')->rollback();
					$this->error = $e->getMessage();
					return false;
				}
			}else{
				$this->error = '取消订单失败'.$ulifego['msg'];
				return false;
			}
		}elseif($sign['type'] == 8){
			//自定义接口退款
			model("express_order")->startTrans();
			try{	
				if($sign['moneys']){
					model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
				}
					
				$logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
				if($logs){
					$orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','订单用户取消订单退款');
					if($orderWeixinRefund == false){
						$this->error = '失败'.model('PaymentLogs')->getError();
						return false;
					}else{
						$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
						$cdo = Db::name('city_delivery_order')->where(array('order_id'=>$id))->update(array('orderStatus'=>5,'refund_time'=>time(),'refund_info'=>$reason));
						return true;
					}
				}else{
					$rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'4-【'.$id.'】订单用户取消订单退款',5);
					if($rest){
						$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
						$cdo = Db::name('city_delivery_order')->where(array('order_id'=>$id))->update(array('orderStatus'=>5,'refund_time'=>time(),'refund_info'=>$reason));
						return true;
					}else{
						$this->error = '订单异常退款失败';
						return false;
					}
				}
			}catch(\Exception $e){
				model('express_order')->rollback();
				$this->error = $e->getMessage();
				return false;
			}
		}elseif($sign['type'] == 9){

            $requestParams = array('waybill'=>$sign['deliveryId'],'shopbill'=>$sign['expressNo']);
            $performance = model('YtApi')->performance($requestParams,$Method ='CANCEL');

            if($performance['code'] ==1){
                model("express_order")->startTrans();
                try{
                    if($sign['moneys']){
                        model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
                    }
                    $logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
                    if($logs){
                        $orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','订单用户取消订单退款');
                        if($orderWeixinRefund == false){
                            $this->error = '失败'.model('PaymentLogs')->getError();
                            return false;
                        }else{
                            $r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
                            return true;
                        }
                    }else{
                        $rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'4-【'.$id.'】订单用户取消订单退款',5);
                        if($rest){
                            $r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
                            return true;
                        }else{
                            $this->error = '订单异常退款失败';
                            return false;
                        }
                    }
                    model('express_order')->commit();
                    return true;
                }catch(\Exception $e){
                    model('express_order')->rollback();
                    $this->error = $e->getMessage();
                    return false;
                }
            }else{
                $this->error = '取消订单失败'.$performance['message'];
                return false;
            }
        }elseif($sign['type'] == 10){
            //电商寄自定义接口退款
            model("express_order")->startTrans();
            try{
                if($sign['moneys']){
                    model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
                }
                $logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
                if($logs){
                    $orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','订单用户取消订单退款');
                    if($orderWeixinRefund == false){
                        $this->error = '失败'.model('PaymentLogs')->getError();
                        return false;
                    }else{
                        $r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
                        return true;
                    }
                }else{
                    $rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'4-【'.$id.'】订单用户取消订单退款',5);
                    if($rest){
                        $r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
                        return true;
                    }else{
                        $this->error = '订单异常退款失败';
                        return false;
                    }
                }
            }catch(\Exception $e){
                model('express_order')->rollback();
                $this->error = $e->getMessage();
                return false;
            }
        }elseif($sign['type'] == 11) {
            if ($sign['orderType'] == 0) {
                $content['waybill'] = $sign['deliveryId'];
                $performance = model('City')->performance($content, $Method = 'CANCEL');
                if ($performance['code'] == 1) {
                    model("express_order")->startTrans();
                    try {
                        $uml = Db::name('user_money_logs')->where(array('order_id' => $id, 'type' => 5, 'user_id' => $sign['user_id']))->find();
                        if (!$uml) {
                            $orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','订单用户取消订单退款');
                            if ($orderWeixinRefund == false) {
                                $this->error = '退款异常' . $id . '【' . model('ExpressOrder')->getError() . '】请稍后再试';
                                return false;
                            }
                            model('express_order')->commit();
                            model('ExpressOrder')->cancelCompleted($sign);
                            model('Ad')->dingTalkWebhook($dd_msg = 6, '云洋同城订单号【' . $id . '】订单异常取消订单', '');//钉钉通知
                            return true;
                        } else {
                            $this->error = '德邦接口退款重复操作';
                            return false;
                        }
                    } catch (\Exception $e) {
                        model('express_order')->rollback();
                        $this->error = $e->getMessage();
                        return false;
                    }
                } else {
                    $this->error = '取消订单失败' . $performance['message'];
                    return false;
                }
            }
        }
        elseif($sign['type'] == 15){
			//众发物流取消订单
			$requestParams = array();
			$orderRequestParams = iunserializer($sign['requestParams']);
			if(!empty($orderRequestParams['outOrderNo'])){
				$requestParams['outOrderNo'] = $orderRequestParams['outOrderNo'];
			}else{
				$requestParams['outOrderNo'] = (string)$sign['id'];
			}
			$requestParams = array(
				'outOrderNo' => $requestParams['outOrderNo'],

				'code' => 6,
				'remark' => $reason ? $reason : '取消订单',
			);
			$zhongfaResult = model('Setting')->zhongfaExecute($requestParams,'cancel');
			if($zhongfaResult && isset($zhongfaResult['code']) && $zhongfaResult['code'] == 0){
				model("express_order")->startTrans();
				try{	
					if($sign['moneys']){
						model('Users')->addMoneys($sign['user_id'],$sign['moneys'],'【'.$sign['id'].'】订单用户取消订单退款',2);//新增抵扣金
					}
					
					$logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'express','code'=>'wxapp'))->find();
					if($logs){
						$orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($id,$sign['user_id'],$sign['sumMoneyYuan'],$type = 'express','众发物流订单用户取消订单退款');
						if($orderWeixinRefund == false){
							$this->error = '失败'.model('PaymentLogs')->getError();
							return false;
						}else{
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}
					}else{
						$rest = model('Users')->addMoney($sign['user_id'],$sign['sumMoneyYuan'],'15-【'.$id.'】订单用户取消订单退款',5);
						if($rest){
							$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>5,'orderRightsStatus'=>2));
							return true;
						}else{
							$this->error = '订单异常退款失败';
							return false;
						}
					}
					model('express_order')->commit();
					return true;
				}catch(\Exception $e){
					model('express_order')->rollback();
					$this->error = $e->getMessage();
					return false;
				}
			}else{
				$this->error = '取消订单失败'.(isset($zhongfaResult['msg']) ? $zhongfaResult['msg'] : (isset($zhongfaResult['message']) ? $zhongfaResult['message'] : '未知错误'));
				return false;
			}
		}
	}
	
	
	
	
 	public function sendCouponDownload($user_id,$title='新人有礼',$coupon_id=0,$need_pay=0){
		if($coupon_id){
			$c = Db::name('coupon')->where(array('audit'=>1,'expire_date'=>array('EGT',TODAY),'coupon_id'=>$coupon_id,'closed' => 0))->find();
			$cd = 0;
			$status = 1;
			$money = $need_pay;
		}else{
			$c = Db::name('coupon')->where(array('audit'=>1,'expire_date'=>array('EGT',TODAY),'title'=>$title,'closed' => 0))->find();
			$cd = 0;
			$money = 0;
			$status = 0;
		}
		$c_day = time()+($c['day']*86400);
		$expire_date = date('Y-m-d',$c_day);
		$number= 1;
		if($c['num'] <= 1){
			$number = 1;
		}elseif($c['num'] < $c['number']){
			$number = $c['num'];
		}else{
			$number = $c['number'];
		}
		if($c && !$cd && $c['num']){
			for($k=1;$k<=$number;$k++){
				$data = array(
					'user_id' => $user_id,
					'type' => $c['type'],
					'shop_id' => 0,
					'title' => $c['title'],
					'coupon_id' => $c['coupon_id'],
					'create_time' => time(),
					'mobile' => '',
					'download_info'=>'第'.$k.'次for下载【'.$c['title'].'】',
					'status' => $status,
					'day' => $c['day'],
					'money' => $money,
					'expire_date' => $expire_date,
					'full_price' => $c['full_price'],
					'limit_num' => $c['limit_num'],
					'reduce_price' => $c['reduce_price'],
					'create_ip' => request()->ip(),
					'code' => $this->getCode(),
				);
				$download_id = Db::name('coupon_download')->insertGetId($data);
			}
			$num = $c['num']-$number;
			if($num <=0){
				$num = 0;
			}
			Db::name('coupon')->where(array('coupon_id'=>$c['coupon_id']))->update(array('num'=>$num));
			model('WeixinTmpl')->getWeixinTmplSend($c,$user_id,$title = '优惠券发放通知');
			return true;
		}
		$this->error = '当前优惠券没库存或者优惠券不存在或者优惠券已经过期';
		return false;
	}
	
	
	//给用户奖励积分
	public function orderAddIntegral($v,$user_id,$title){
		$config = model('Setting')->fetchAll2();
		$np = (int)($v['sumMoneyYuan']/100);
		$exp = (int)$config['integral']['exp'];
		$integral = $np * $exp;
		if($integral){
			model('Users')->addIntegral($v['user_id'],$integral,'寄快递'.$id.'获取积分',4);
		}
		return true;
	}
	
	
	
	
	//会员等级分销
	public function user_rank_profit($rank,$user_id,$need_pay,$log_id=0){
		$config = model('Setting')->fetchAll2();
		$rate1 = (int)$config['profit']['profit_vip_rate1'];
		$rate2 = (int)$config['profit']['profit_vip_rate2'];
		$rate3 = (int)$config['profit']['profit_vip_rate3'];
		$p = $need_pay;
		$id = $log_id;
		$name = $user_id.'-购买会员等级'.$rank['rank_name'];
		$money1 = $money2 = $money3 = 0;
		
		//购买等级1级分成
		$u = Db::name('users')->where(array('user_id'=>$user_id))->field('user_id,parent_id,nickname')->find();
		$u1 = Db::name('users')->where(array('user_id'=>$u['parent_id']))->field('user_id,parent_id,rank_id,nickname')->find();
		$rate1 = $rate1;	
		$m1 = round($p*$rate1/100);	
		$checkProfitLogs = model('ExpressOrder')->checkProfitLogs($u1['user_id'],$id,'rank');
		if($m1 > 0 && $u1 && $checkProfitLogs==0 && true == $this->determinePower($u1)){
			$info1=$name.'-1级分成费率'.$rate1.'%';
			model('Users')->addMoney($u1['user_id'],$m1,$info1,4,$id,'rank');
			model('Users')->addProfit($u1['user_id'], $order_type = 0, 'rank', $id, $shop_id = '0',$m1, $is_separate = '1',$info1,$name);
		}
		
		//购买等级2级分成
		$u2 = Db::name('users')->where(array('user_id'=>$u1['parent_id']))->field('user_id,parent_id,rank_id,nickname')->find();
		$rate2 = $rate2;	
		$m2 = round($p*$rate2/100);	
		$checkProfitLogs = model('ExpressOrder')->checkProfitLogs($u2['user_id'],$id,'rank');
		if($m2 > 0 && $u2 && $checkProfitLogs==0 && true == $this->determinePower($u2)){
			$info2=$name.'-2级分成费率'.$rate2.'%';
			model('Users')->addMoney($u2['user_id'],$m2,$info2,4,$id,'rank');
			model('Users')->addProfit($u2['user_id'], $order_type = 0,'rank', $id, $shop_id = '0',$m2, $is_separate = '1',$info2,$name);
		}
		
		//购买等级3级分成
		$u3 = Db::name('users')->where(array('user_id'=>$u2['parent_id']))->field('user_id,parent_id,rank_id,nickname')->find();
		$rate3 = $rate3;	
		$m3 = round($p*$rate3/100);	
		$checkProfitLogs = model('ExpressOrder')->checkProfitLogs($u3['user_id'],$id,'rank');
		if($m3 > 0 && $u3 && $checkProfitLogs==0 && true == $this->determinePower($u3)){
			$info3=$name.'-3级分成费率'.$rate3.'%';
			model('Users')->addMoney($u3['user_id'],$m3,$info3,4,$id,'rank');
			model('Users')->addProfit($u3['user_id'], $order_type = 0,'rank',$id, $shop_id = '0',$m3, $is_separate = '1',$info3,$name);
		}
		return $money1+$money2+$money3;
	}
	
	
	//寻找自己最近的上级
	public function get_parent_branchs($user_id,$rank_id,$num=1,$uid){
        $u = Db::name('users')->where(array('user_id'=>$user_id,'closed'=>0))->field('user_id,parent_id,rank_id,stock,nickname')->find();
		if($u){
			if($u['rank_id'] == $rank_id && $user_id != $uid){
				 return $u;
			}
		}
		if($u['parent_id'] && $num<31){
			$num++;
			return $this->get_parent_branchs($u['parent_id'],$rank_id,$num+1,$uid);
		}
    }
	
	
	public function profit_uu($user_id=0){
		$u = Db::name('users')->where(array('user_id'=>$user_id,'closed'=>0))->field('user_id,parent_id,stock,nickname')->find();
		$u1 = Db::name('users')->where(array('user_id'=>$u['parent_id'],'closed'=>0))->field('user_id,parent_id,rank_id,stock,nickname')->find();
		$u2 = Db::name('users')->where(array('user_id'=>$u1['parent_id'],'closed'=>0))->field('user_id,parent_id,rank_id,stock,nickname')->find();
		$u3 = Db::name('users')->where(array('user_id'=>$u2['parent_id'],'closed'=>0))->field('user_id,parent_id,rank_id,stock,nickname')->find();
		$uu1= model('ExpressOrder')->get_parent_branchs($user_id,1,$num=1,$user_id);
		if(!$uu1){
			$uu2= model('ExpressOrder')->get_parent_branchs($user_id,2,$num=1,$user_id);
			if(!$uu2){
				$uu2 = '';
				$uu2= model('ExpressOrder')->get_parent_branchs($user_id,3,$num=1,$user_id);
				if(!$uu2){
					$uu2 = '';
					$uu3= model('ExpressOrder')->get_parent_branchs($user_id,3,$num=1,$user_id);
				}else{
					$uu3= model('ExpressOrder')->get_parent_branchs($user_id,3,$num=1,$user_id);
				}
			}else{
				$uu3= model('ExpressOrder')->get_parent_branchs($user_id,3,$num=1,$user_id);
			}
		}else{
			$uu2= model('ExpressOrder')->get_parent_branchs($user_id,2,$num=1,$user_id);
			if(!$uu2){
				$uu2 = '';
				$uu3= model('ExpressOrder')->get_parent_branchs($user_id,3,$num=1,$user_id);
			}else{
				$uu3= model('ExpressOrder')->get_parent_branchs($user_id,3,$num=1,$user_id);
			}
			
		}
		$data['uu1'] = $uu1;
		$data['uu2'] = $uu2;
		$data['uu3'] = $uu3;
		return $data;
	}
	
	
	//开始分销retail
	public function profit_retail($v,$user_id,$title){
		$id = $v['id'];
		$config = model('Setting')->fetchAll2();
		$moshi = (int)$config['profit']['moshi'];
		if($moshi == 1){
			$p = $v['sumMoneyYuan'];//分成订单总金额
		}else{
			$p = $v['sumMoneyYuan_jia'];//分成加价金额
		}
		$money1 = $money2 = $money3 = 0;
		$m1 = $m2 = $m3 = 0;
		
		
		
		$profit_uu= model('ExpressOrder')->profit_uu($user_id);
		$uu1 = $profit_uu['uu1'];
		$uu2 = $profit_uu['uu2'];
		$uu3 = $profit_uu['uu3'];
	
		
		$stock1 = $stock2 = $stock3 = 0;
		$stock1 = $uu1['stock'];
		$stock2 = $uu2['stock'];
		//p($stock2);
		$stock2 = $stock2-$uu1['stock'];
		
		$stock3 = $uu3['stock'];
		//p($stock3);
		$stock3 = $stock3- $stock2-$stock1;
		//p($stock3);die;
		
		
		if($uu1['stock'] > 0 && $stock1){
			$m1 = round($p*$stock1/10000);	
		}
		$m1 = (int)$m1;
		if($m1 > 0 && $uu1){
			model('Users')->addProfit($uu1['user_id'], $order_type = 0, 'express1', $id, $shop_id = '0',$m1, $is_separate = '0',$id.'-拓展员1-订单1级分成');
		}
		
		
		if($uu2['stock'] > 0 && $stock2){
			$m2 = round($p*$stock2/10000);	
		}
		$m2 = (int)$m2;
		if($m2 > 0 && $uu2){
			model('Users')->addProfit($uu2['user_id'], $order_type = 0,'express1', $id, $shop_id = '0',$m2, $is_separate = '0',$id.'拓展员2-订单2级分成');
		}
		
		
		if($uu3['stock'] > 0 && $stock3){
			$m3 = round($p*$stock3/10000);	
		}
		$m3 = (int)$m3;
		if($m3 > 0 && $uu3){
			model('Users')->addProfit($uu3['user_id'], $order_type = 0,'express1',$id, $shop_id = '0',$m3, $is_separate = '0',$id.'-服务商-订单3级分成');
		}
		return $m1+$m2+$m3;
	}
	
	
	
	//拉新奖励
	public function RecruitingRewards($v,$user_id,$title){
		$m1 = $m2  = 0;
		$id = $v['id'];
		//是否第一单
		$count =(int)Db::name('express_order')->where(array('user_id'=>$user_id,'orderStatus'=>array('in',array(0,1,2,3,4)),'closed'=>0))->count();
		$u = Db::name('users')->where(array('user_id'=>$user_id))->field('user_id,parent_id,nickname')->find();//自己的信息
		
		//直推
		$u1 = Db::name('users')->where(array('user_id'=>$u['parent_id']))->field('user_id,parent_id,rank_id,nickname')->find();//自己的上级信息
		$ur1 = Db::name('user_rank')->where(array('rank_id'=>$u1['rank_id']))->field('rank_name,number,number2,day,prestige,price,total,total1,money,money1,moshi,photo,weight')->find();//上级等级信息
		$m1 = (int)$ur1['total'];
		$check1 = model('ExpressOrder')->checkProfitLogs($u1['user_id'],$id,'laxin');
		if($count==1 && $m1 && $check1==0){
			model('Users')->addProfit($u1['user_id'], $order_type = 4, 'laxin', $id, $shopid = '0',$m1, $is_separate = '0',$id.'拉新直推分成');
		}
		
	
		
		//间接推
		$u2 = Db::name('users')->where(array('user_id'=>$u1['parent_id']))->field('user_id,parent_id,rank_id,nickname')->find();
		$ur2 = Db::name('user_rank')->where(array('rank_id'=>$u2['rank_id']))->field('rank_name,number,number2,day,prestige,price,total,total1,money,money1,moshi,photo,weight')->find();
		$m2 = (int)$ur2['total1'];
		$check2 = model('ExpressOrder')->checkProfitLogs($u2['user_id'],$id,'laxin');
		if($count==1 && $m2 && $check2==0){
			model('Users')->addProfit($u2['user_id'], $order_type = 5, 'express', $id, $shopid = '0',$m2, $is_separate = '0',$id.'拉新间推级分成');
		}
		Db::commit();
		return $m1+$m2;
	}
	
	
	//开始分销
	public function profit($v,$user_id,$title){
		$id = $v['id'];
		$config = model('Setting')->fetchAll2();
		$moshi = (int)$config['profit']['moshi'];
		$moshi1 = (int)$config['profit']['moshi1'];
		$profit_guding_rate1 = (int)($config['profit']['profit_guding_rate1']*100);
		$profit_guding_rate2 = (int)($config['profit']['profit_guding_rate2']*100);
		$profit_guding_rate3 = (int)($config['profit']['profit_guding_rate3']*100);
		if($moshi == 1){
			$p = $v['sumMoneyYuan'];//分成订单总金额
		}else{
			$p = $v['sumMoneyYuan_jia'];//分成加价金额
		}
		$money1 = $money2 = $money3 = 0;
		$m1 = $m2 = $m3 = 0;
		$rate1 = (int)$config['profit']['profit_rate1'];
		$rate2 = (int)$config['profit']['profit_rate2'];
		$rate3 = (int)$config['profit']['profit_rate3'];
		
		$u = Db::name('users')->where(array('user_id'=>$user_id))->field('user_id,parent_id,stock,nickname')->find();
		$u1 = Db::name('users')->where(array('user_id'=>$u['parent_id']))->field('user_id,parent_id,rank_id,stock,nickname')->find();
		$ur1 = Db::name('user_rank')->where(array('rank_id'=>$u1['rank_id']))->field('rank_id,rank_name,rate1,rate2,rate3')->find();
		
		
		
		if($moshi1 == 0){
			if($ur1['rate1']){
				$rate1 = $ur1['rate1'];	
			}else{
				$rate1 = $rate1;
			}
			$m1 = round($p*$rate1/100);	
		}elseif($moshi1 == 1){
			$m1 = $profit_guding_rate1;
		}
		$m1 = (int)$m1;
		if($m1 > 0 && true == $this->determinePower($u1) && $u1){
			model('Users')->addProfit($u1['user_id'], $order_type = 0, 'express', $id, $shop_id = '0',$m1, $is_separate = '0',$id.'订单1级分成');
		}
		
		
		$u2 = Db::name('users')->where(array('user_id'=>$u1['parent_id']))->field('user_id,parent_id,rank_id,stock,nickname')->find();
		$ur2 = Db::name('user_rank')->where(array('rank_id'=>$u2['rank_id']))->field('rank_id,rank_name,rate1,rate2,rate3')->find();
		
		if($moshi1 == 0){
			if($ur2['rate2']){
				$rate2 = $ur2['rate2'];	
			}else{
				$rate2 = $rate2;
			}
			$m2 = round($p*$rate2/100);	
		}elseif($moshi1 == 1){
			$m2 = $profit_guding_rate2;
		}
		$m2 = (int)$m2;
		if($m2 > 0 && true == $this->determinePower($u2) && $u2){
			model('Users')->addProfit($u2['user_id'], $order_type = 0,'express', $id, $shop_id = '0',$m2, $is_separate = '0',$id.'订单2级分成');
		}
		
		$u3 = Db::name('users')->where(array('user_id'=>$u2['parent_id']))->field('user_id,parent_id,rank_id,stock,nickname')->find();
		$ur3 = Db::name('user_rank')->where(array('rank_id'=>$u3['rank_id']))->field('rank_id,rank_name,rate1,rate2,rate3')->find();
		if($moshi1 == 0){
			if($ur3['rate3']){
				$rate3 = $ur3['rate3'];	
			}else{
				$rate3 = $rate3;
			}
			$m3 = round($p*$rate3/100);	
		}elseif($moshi1 == 1){
			$m3 = $profit_guding_rate3;
		}
		$m3 = (int)$m3;
		if($m3 > 0  && true == $this->determinePower($u3) && $u3){
			model('Users')->addProfit($u3['user_id'], $order_type = 0,'express',$id, $shop_id = '0',$m3, $is_separate = '0',$id.'订单3级分成');
		}
		return $m1+$m2+$m3;
	}
	
	
	//合伙人奖励
	public function PartnerRewards($v,$user_id,$title){
		$m1 = $m2 = 0;
		$id = $v['id'];
		$user_rank =Db::name('user_rank')->where(array('is_prestige'=>1))->order('rank_id desc')->find();
		if($user_rank && $user_rank['prestige']){
			$m1 = (int)$user_rank['prestige'];
			$u = $this->get_parent_branch($user_id,$user_rank['rank_id'],$num=1);
			if($u && $m1){
				$info = $id.'【'.$user_rank['rank_name'].'】额外享受团队每笔订单奖励';
				model('Users')->addProfit($u['user_id'], $order_type = 0, 'rewards', $id, $shopid = '0',$m1, $is_separate = '0',$info);
			}
		}
		return $m1;
	}
	
	
	//给直推人下级签收多少订单分成
	public function directPushRate($v,$user_id,$title){
		$config = model('Setting')->fetchAll2();
		$is_direct_push = (int)$config['profit']['is_direct_push'];	
		$is_direct_push_num = (int)$config['profit']['is_direct_push_num'];	
		$is_direct_push_money = $config['profit']['is_direct_push_money']*100;	
		$is_direct_push_money = $is_direct_push_money;	
		
		
		$parent = Db::name('users')->where(array('user_id'=>$v['pid']))->field('user_id,parent_id,rank_id,province,area,city,nickname')->find();
		$num = (int)Db::name('express_order')->where(array('user_id'=>$v['user_id'],'orderStatus'=>4))->count();
		
		
		if($is_direct_push==1 && $is_direct_push_num>=1 && $num >= $is_direct_push_num && $is_direct_push_money){
			$m1 = $is_direct_push_money;
			if($m1 > 0 && true == $this->determinePower($parent) && $parent['user_id']){
				model('Users')->addProfit($parent['user_id'],0,'direct',$v['id'],0,$m1,0,'满足条件给上级分成','满足条件分成',$parent['province'],$parent['city'],$parent['area']);
			}
		}
		return true;
	}
	
	
	public function areaRate($v,$user_id,$title){
		//p($city_agent_type);die;
		
		$config = model('Setting')->fetchAll2();
		$city_agent_type = (int)$config['profit']['city_agent_type'];	
		$is_direct_push = (int)$config['profit']['is_direct_push'];	
		$is_direct_push_num = (int)$config['profit']['is_direct_push_num'];	
		$is_direct_push_money = $config['profit']['is_direct_push_money']*100;	
		$is_direct_push_money = $is_direct_push_money;	
		
		
		if($city_agent_type==0){
			model('ExpressOrder')->areaRate_0($v,$user_id,$title);
		}
		if($city_agent_type==1){
			model('ExpressOrder')->areaRate_1($v,$user_id,$title);
		}
		return true;
	}
	
	
	
	//区县分成
	public function areaRate_0($v,$user_id,$title){
		$id = $v['id'];
		$config = model('Setting')->fetchAll2();
		$is_area = (int)$config['profit']['is_area'];
		$is_area_rate = (int)$config['profit']['is_area_rate'];
		$is_area_rate_vip = (int)$config['profit']['is_area_rate_vip'];
		$is_area_rate = $is_area_rate*100;
		$is_area_rate_vip  = $is_area_rate_vip *100;
		
		$is_city = (int)$config['profit']['is_city'];
		$is_city_rate = (int)$config['profit']['is_city_rate'];
		$is_city_rate_vip = (int)$config['profit']['is_city_rate_vip'];
		$is_city_rate = $is_city_rate*100;
		$is_city_rate_vip = $is_city_rate_vip*100;
		$is_jicha = (int)$config['profit']['is_jicha'];
		
		$m1 = $m2 =  $m3 =0;
		$ratio1 = $ratio2 =  $ratio3 =0;
		
		$moshi = (int)$config['profit']['moshi'];
		$moshi1 = (int)$config['profit']['moshi1'];
		$p = $v['sumMoneyYuan'];
		if($moshi == 1){
			$p = $v['sumMoneyYuan'];//分成订单总金额
		}else{
			$p = $v['sumMoneyYuan_jia'];//分成加价金额
		}
		$is_vip=0;
		$users = Db::name('users')->where(array('user_id'=>$user_id))->field('user_id,parent_id,area,city,nickname')->find();
		//推荐人信息
		$parent = Db::name('users')->where(array('user_id'=>$users['parent_id']))->field('user_id,parent_id,rank_id,area,city,nickname')->find();
		if($parent['rank_id']){
			$is_vip=1;
		}
		
		//p($is_vip);die;
		
		
		$area['user_id'] = 0;
		//检测会员绑定的城市
		$area = Db::name('area')->where(array('Name'=>$title))->field('Name,user_id,ratio,city_id,area_id')->find();
		if(!$area['user_id']){
			//检测地址绑定的城市
			$area = Db::name('area')->where(array('area_id'=>$users['area']))->field('Name,user_id,ratio,city_id,area_id')->find();
		}
		$city = Db::name('city')->where(array('city_id'=>$area['city_id']))->field('name,user_id,ratio,ParentId,city_id')->find();
		//p($area);die;
		//有区县代理
		if($area['user_id'] && $is_area){
			$ratio1 = $area['ratio'] ? $area['ratio'] : $is_area_rate;
			
			if($is_vip==1){
				$ratio1 = $area['ratio_vip'] ? $area['ratio_vip'] : $is_area_rate_vip;
			}else{
				$ratio1 = $area['ratio'] ? $area['ratio'] : $is_area_rate;
			}
			$m1 = round($p*$ratio1/10000);
			$m1 = (int)$m1;	
			
			$checkProfitLogs = model('ExpressOrder')->checkProfitLogs($area['user_id'],$v['id'],'area');
			$u = Db::name('users')->where(array('user_id'=>$area['user_id']))->field('user_id,parent_id,rank_id,nickname')->find();
			if($m1 > 0 && $area['user_id'] && $checkProfitLogs==0 && true == $this->determinePower($u)){
				model('Users')->addProfit($area['user_id'],0,'area', $v['id'],0,$m1,0,$area['Name'].'-区县代理分成',$area['Name'],$city['ParentId'],$city['city_id'],$area['area_id']);
			}
		}
		
		
		//有区县代理正常城市分成
		if($area['user_id'] && $city['user_id'] && $is_city){
			if($is_vip==1){
				$ratio2 = $city['ratio_vip'] ? $city['ratio_vip'] : $is_city_rate_vip;
			}else{
				$ratio2 = $city['ratio'] ? $city['ratio'] : $is_city_rate;
			}
			$m2 = round($p*$ratio2/10000);
			$m2 = (int)$m2;	
			
			$checkProfitLogs = model('ExpressOrder')->checkProfitLogs($city['user_id'],$v['id'],'city');
			$u = Db::name('users')->where(array('user_id'=>$city['user_id']))->field('user_id,parent_id,rank_id,nickname')->find();
			if($m2 > 0 && true == $this->determinePower($u) && $city['user_id'] && $checkProfitLogs==0){
				model('Users')->addProfit($city['user_id'],0,'city', $v['id'],0,$m2,0,$city['name'].'-城市代理分成',$city['name'],$city['ParentId'],$city['city_id'],$area['area_id']);
			}
		}
		
		
		//无区县代理极差分成
		if(!$area['user_id'] && $city['user_id'] && $is_city && $is_jicha){
			
			if($is_vip==1){
				$ratio1 = $area['ratio_vip'] ? $area['ratio_vip'] : $is_area_rate_vip;
			}else{
				$ratio1 = $area['ratio'] ? $area['ratio'] : $is_area_rate;
			}
			if($is_vip==1){
				$ratio2 = $city['ratio_vip'] ? $city['ratio_vip'] : $is_city_rate_vip;
			}else{
				$ratio2 = $city['ratio'] ? $city['ratio'] : $is_city_rate;
			}
			$ratio3 = $ratio2+$ratio1;
			$m3 = round($p*($ratio3)/10000);
			$m3 = (int)$m3;	
			//p($m3);die;	
			
			$checkProfitLogs = model('ExpressOrder')->checkProfitLogs($city['user_id'],$v['id'],'city');
			$u = Db::name('users')->where(array('user_id'=>$city['user_id']))->field('user_id,parent_id,rank_id,nickname')->find();
			if($m3 > 0 && true == $this->determinePower($u) && $city['user_id'] && $checkProfitLogs==0){
				model('Users')->addProfit($city['user_id'],0,'city', $v['id'],0,$m3,0,$city['name'].'-极差代理分成',$city['name'],$city['ParentId'],$city['city_id'],$area['area_id']);
			}
		}
		return $m1+$m2+$m3;
	}
	
	//检测是否已分成
	public function checkProfitLogs($user_id,$order_id,$type){
		$logs = (int)Db::name('user_profit_logs')->where(array('user_id'=>$user_id,'order_id'=>$order_id,'type'=>$type))->count();
		return $logs;
	}
	
	//多个会员分成
	public function areaRate_1($v,$user_id,$title){
		$id = $v['id'];
		$config = model('Setting')->fetchAll2();
		$is_area = (int)$config['profit']['is_area'];
		$is_area_rate = (int)$config['profit']['is_area_rate'];
		$is_area_rate_vip = (int)$config['profit']['is_area_rate_vip'];
		$is_area_rate = $is_area_rate*100;
		$is_area_rate_vip  = $is_area_rate_vip *100;
		
		$is_city = (int)$config['profit']['is_city'];
		$is_city_rate = (int)$config['profit']['is_city_rate'];
		$is_city_rate_vip = (int)$config['profit']['is_city_rate_vip'];
		$is_city_rate = $is_city_rate*100;
		$is_city_rate_vip = $is_city_rate_vip*100;
		$is_jicha = (int)$config['profit']['is_jicha'];
		
		$m1 = $m2 =  $m3 =0;
		$ratio1 = $ratio2 =  $ratio3 =0;
		
		$moshi = (int)$config['profit']['moshi'];
		$moshi1 = (int)$config['profit']['moshi1'];
		$p = $v['sumMoneyYuan'];
		if($moshi == 1){
			$p = $v['sumMoneyYuan'];//分成订单总金额
		}else{
			$p = $v['sumMoneyYuan_jia'];//分成加价金额
		}
		$is_vip=0;
		$users = Db::name('users')->where(array('user_id'=>$user_id))->field('user_id,parent_id,area,city,nickname')->find();
		//推荐人信息
		$parent = Db::name('users')->where(array('user_id'=>$users['parent_id']))->field('user_id,parent_id,rank_id,area,city,nickname')->find();
		if($parent['rank_id']){
			$is_vip=1;
		}
		
		//p($is_vip);die;
		
		
		$area['user_id'] = 0;
		//检测会员绑定的城市
		$area = Db::name('area')->where(array('Name'=>$title))->field('Name,user_id,ratio,city_id,area_id')->find();
		if(!$area['user_id']){
			//检测地址绑定的城市
			$area = Db::name('area')->where(array('area_id'=>$users['area']))->field('Name,user_id,ratio,city_id,area_id')->find();
		}
		if($area){
			$areas = Db::name('city_agent')->where(array('area_id'=>$area['area_id'],'type'=>3))->select();
		}
		
		$citys = Db::name('city_agent')->where(array('city_id'=>$area['city_id'],'type'=>2))->select();
		//p($area);
		//p($areas);die;

		//给多个区县代理分成
		if($is_area){
			foreach($areas as $k =>$v){
				$ratio1 = $v['ratio'] ? $v['ratio'] : $is_area_rate;
				if($is_vip==1){
					$ratio1 = $v['ratio'] ? $v['ratio'] : $is_area_rate;
				}else{
					$ratio1 = $v['ratio'] ? $v['ratio'] : $is_area_rate;
				}
				$m1 = round($p*$ratio1/10000);
				$m1 = (int)$m1;	
				$area = Db::name('area')->where(array('area_id'=>$v['area_id']))->field('Name,user_id,ratio,city_id,area_id')->find();
				$city = Db::name('city')->where(array('city_id'=>$area['city_id']))->field('name,user_id,ratio,ParentId,city_id')->find();
				$checkProfitLogs = model('ExpressOrder')->checkProfitLogs($v['user_id'],$id,'areas');
				$u = Db::name('users')->where(array('user_id'=>$v['user_id']))->field('user_id,parent_id,rank_id,nickname')->find();
				if($m1 > 0 && true == $this->determinePower($u) && $area['user_id'] && $checkProfitLogs==0){
					model('Users')->addProfit($v['user_id'],0,'areas',$id,0,$m1,0,'区县代理分成',$city['name'],$city['ParentId'],$city['city_id'],$area['area_id']);
				}
			}
		}
		
		
		//给多个城市代理分成
		if($is_city){
			foreach($citys as $k =>$v){
				if($is_vip==1){
					$ratio2 = $v['ratio'] ? $v['ratio'] : $is_city_rate;
				}else{
					$ratio2 = $v['ratio'] ? $v['ratio'] : $is_city_rate;
				}
				$m2 = round($p*$ratio2/10000);
				$m2 = (int)$m2;	
				$city = Db::name('city')->where(array('city_id'=>$v['city_id']))->field('name,user_id,ratio,ParentId,city_id')->find();
				$checkProfitLogs = model('ExpressOrder')->checkProfitLogs($city['user_id'],$id,'citys');
				$u = Db::name('users')->where(array('user_id'=>$v['user_id']))->field('user_id,parent_id,rank_id,nickname')->find();
				if($m2 > 0 && true == $this->determinePower($u) && $v['user_id'] && $checkProfitLogs==0){
					model('Users')->addProfit($v['user_id'],0,'citys',$id,0,$m2,0,'城市代理分成',$city['name'],$city['ParentId'],$city['city_id'],'');
				}
			}
		}
		
		
		
		return $m1+$m2+$m3;
	}
	
	
	
	
	
	//执行分销
	public function completeProfit($v,$user_id,$title){
		$id = $v['id'];
		$logs = Db::name('user_profit_logs')->where(array('order_id'=>$id,'is_separate'=>0))->limit(0,10)->select();
		if($logs){
			foreach($logs as $k2 =>$v2){
				if($v2['is_separate']==0){
					Db::name('user_profit_logs')->where(array('log_id'=>$v2['log_id']))->update(array('complete_time'=>time(),'is_separate'=>1));
					model('Users')->addMoney($v2['user_id'],$v2['money'],$v2['info'],4,$v2['order_id'],'profit');
					model('WeixinTmpl')->getWeixinTmplSend(array(),$v2['user_id'],$title = '收益到账通知',$type='订单完成',$v2['money']);  
				}
			}
		}
		$users = Db::name('users')->where(array('user_id'=>$user_id,'closed'=>0))->field('user_id,parent_id')->find();
		if($users['parent_id']){
			$logs3 = Db::name('user_profit_logs')->where(array('user_id'=>$users['parent_id'],'type'=>'add','is_separate'=>0))->limit(0,10)->select();
			if($logs3){
				foreach($logs3 as $k3 =>$v3){
					if($v3['is_separate']==0){
						Db::name('user_profit_logs')->where(array('log_id'=>$v3['log_id']))->update(array('complete_time'=>time(),'is_separate'=>1));
						model('Users')->addMoney($v3['user_id'],$v3['money'],$v3['info'],7,$v3['order_id'],'add');
					}
				}
			}
		}
		return true;
	}
	
	//删除分成
	public function delete_user_profit_logs($id){
		$logs = Db::name('user_profit_logs')->where(array('order_id'=>$id,'is_separate'=>0))->limit(0,10)->select();
		if($logs){
		   foreach($logs as $kg =>$vg){
    			Db::name('user_profit_logs')->where(array('log_id'=>$vg['log_id']))->delete();
    		}
		}
		return true;
	}
	
		//获取快递公司的图片价格
	public function logoUrl($kuaidi = '京东',$uid = 0){
		$config = model('Setting')->fetchAll2();
		//易达接口下单保存数据
		if($kuaidi == '京东'){
			$kuaidi = '京东';
		}elseif($kuaidi == '圆通'){
			$kuaidi = '圆通';
		}elseif($kuaidi == '申通'){
			$kuaidi = '申通';
		}elseif($kuaidi == '德邦'){
			$kuaidi = '德邦';
		}elseif($kuaidi == '极兔'){
			$kuaidi = '极兔';
		}elseif($kuaidi == '顺丰'){
			$kuaidi = '顺丰';
		}elseif($kuaidi == '中通'){
			$kuaidi = '中通';
		}elseif($kuaidi == '韵达'){
			$kuaidi = '韵达';
		}elseif(strstr($kuaidi,'京东') == true){
			$kuaidi = '京东';
		}elseif(strstr($kuaidi,'圆通') == true){
			$kuaidi = '圆通';
		}elseif(strstr($kuaidi,'申通') == true){
			$kuaidi = '申通';
		}elseif(strstr($kuaidi,'德邦重货') == true){
			$kuaidi = '德邦';
		}elseif(strstr($kuaidi,'德邦') == true){
			$kuaidi = '德邦';
		}elseif(strstr($kuaidi,'极兔') == true){
			$kuaidi = '极兔';
		}elseif(strstr($kuaidi,'顺丰') == true){
			$kuaidi = '顺丰';
		}elseif(strstr($kuaidi,'中通') == true){
			$kuaidi = '中通';
		}elseif(strstr($kuaidi,'韵达') == true){
			$kuaidi = '韵达';
		}elseif(strstr($kuaidi,'顺心捷达') == true){
			$kuaidi = '顺心捷达';
		}elseif(strstr($kuaidi,'跨越') == true){
			$kuaidi = '跨越';
		}elseif(strstr($kuaidi,'达达优质') == true){
			$kuaidi = '达达优质';
		}elseif(strstr($kuaidi,'达达') == true){
			$kuaidi = '达达';
		}elseif(strstr($kuaidi,'365跑腿') == true){
			$kuaidi = '365跑腿';
		}elseif(strstr($kuaidi,'闪送') == true){
			$kuaidi = '闪送';
		}
		$cate = Db::name('express_cate')->where(array('cate_name'=>$kuaidi))->find();
		if($cate){
			$deliveryType = $cate['pinyin'];
			$desc = $cate['info'] ? $cate['info'] : '该快递方式暂无说明';
			$expressId = $cate['cate_id'];
			$logoUrl = config_weixin_img($cate['photo']);
			$firstPrice = $cate['firstPrice'];
			$firstPrice1 = $cate['firstPrice1'];
			$firstPrice2 = $cate['firstPrice2'];
			$addPrice = $cate['addPrice'];
			$ratio = $cate['ratio'];
			$priceA_type = $cate['priceA_type'];
			$priceA_ratio = $cate['priceA_ratio'];
			$priceA_price = $cate['priceA_price'];
			$priceB_type = $cate['priceB_type'];
			$priceB_ratio = $cate['priceB_ratio'];
			$priceB_price = $cate['priceB_price'];
			$limitFirstPrice = $cate['limitFirstPrice'];
			$limitAddPrice = $cate['limitAddPrice'];
			$type = $cate['type'];
			$c_type = $cate['type'];
			$lanshou = $cate['lanshou'];
			$info = $cate['info'];
			$orderby = $cate['orderby'];
		}else{
			if($kuaidi == '京东'){
				$deliveryType = 'JD';
				$desc = '京东-JD';
				$expressId = 6;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/jd.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi == '圆通'){
				$deliveryType = 'YTO';
				$desc = '圆通-YTO';
				$expressId = 3;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/yt.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi == '申通'){
				$deliveryType = 'STO-INT';
				$desc = '申通-STO-INT';
				$expressId = 4;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/zt.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi== '德邦'){
				$deliveryType = 'DOP';
				$desc = '德邦-DB';
				$expressId = 2;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/db.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi== '极兔'){
				$deliveryType = 'JT';
				$desc = '极兔-JT';
				$expressId = 5;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/jt.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi== '顺丰'){
				$deliveryType = 'SF';
				$desc = '顺丰-DOP';
				$expressId = 6;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/sf.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi== '中通'){
				$deliveryType = 'ZTO';
				$desc = '中通-ZTO';
				$expressId = 7;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/zt.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi== '韵达'){
				$deliveryType = 'YUND';
				$desc = '韵达-YUND';
				$expressId = 8;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/yd.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi== '顺心捷达'){
				$deliveryType = '顺心捷达';
				$desc = '顺心捷达';
				$expressId = 9;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/jieda.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi== '跨越'){
				$deliveryType = 'KUAYUE';
				$desc = '跨越';
				$expressId = 10;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/kuayue.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi== '达达'){
				$deliveryType = 'DADA';
				$desc = '达达';
				$expressId = 10;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/dada.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi== '365跑腿'){
				$deliveryType = 'PT';
				$desc = '365跑腿';
				$expressId = 10;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/365.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}elseif($kuaidi== '闪送'){
				$deliveryType = 'SS';
				$desc = '闪送';
				$expressId = 10;
				$logoUrl = $config['site']['host'].'/static/default/wap/img/ss2.png';
				$firstPrice = $config['wxapp']['firstPrice'];//首重百分比
				$firstPrice1 = $config['wxapp']['firstPrice1'];//续重百分比
				$firstPrice2 = 0;
				$addPrice = 0;
				$limitFirstPrice = 0;
				$limitAddPrice = 0;
				$type = 2;
				$c_type = 2;
				$lanshou = 0;
				$info = '';
				$orderby = 0;
			}
		}
		return array(
			'cate_id'=>(int)$cate['cate_id'],
			'cate_name'=>$kuaidi,
			'deliveryType'=>$deliveryType,
			'expressName'=>$kuaidi,
			'photo'=>$logoUrl,
			'logoUrl'=>$logoUrl,
			'desc'=>$desc,
			'ratio'=>$ratio?$ratio:20,
			'priceA_type'=>$priceA_type?$priceA_type:0,
			'priceA_ratio'=>$priceA_ratio?$priceA_ratio:1,
			'priceA_price'=>$priceA_price?$priceA_price:50,
			'priceB_type'=>$priceB_type?$priceB_type:0,
			'priceB_ratio'=>$priceB_ratio?$priceB_ratio:0,
			'priceB_price'=>$priceB_price?$priceB_price:1,
			'firstPrice'=>$firstPrice,
			'firstPrice1'=>$firstPrice1,
			'firstPrice2'=>$firstPrice2,
			'addPrice'=>$addPrice,
			'limitFirstPrice'=>$limitFirstPrice,
			'limitAddPrice'=>$limitAddPrice,
			'expressId'=>$expressId,
			'type'=>$type,
			'c_type'=>$c_type,
			'lanshou'=>$lanshou,
			'info'=>$info,
			'is_bao'=>0,
			'is_yuyue'=>0,
			'orderby'=>$orderby
		);
	}
}



