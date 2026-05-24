<?php
namespace app\common\model;

use think\Db;
use think\Model;
use think\Cache;


class Setting extends Base{
 	protected $pk = 'k';
    protected $tableName = 'setting';
    protected $token = 'jin_setting';
    protected $settings = null;
	
	
	
	public function getError(){
        return $this->error;
    }

    public function getBatchApiTypes(){
        return array(
            '1' => '云洋API',
            '2' => '快递鸟',
            '3' => '快递100',
            '4' => '易达',
        );
    }
	
	public function getCompanyApiTypes(){
        return array(
			'1' => '易达接口',
			'2' => '云洋API',
			'3' => '京东',
			'4' => '快递鸟',
			'5' => '跨越',
			'6' => '领航当日达',
			'7' => 'q必达',
			'8' => '自定义接口',
            '9' => '云腾电商寄',
            '10' => '自定义电商寄',
            '11' => '同城寄件',
		);
    }
	
	public function getorderStatus(){
        return array(
            '0' => '未付款',
            '1' => '已付款',
			'2' => '已接单',
			'3' => '已取件',
			'4' => '已完成',
			'5' => '已取消已退款',
			'9' => '订单异常',
			'-1' => '已取消',
        );
    }
	public function getdiffStatus(){
        return array(
            '0' => '暂无差价',
            '1' => '未补差价',
			'2' => '已完成补差价',
        );
    }
	
	public function getorderRightsStatus(){
        return array(
            '0' => '未申请退款',
            '1' => '退款审核中',
			'2' => '退款完成',
        );
    }
	//生成优惠券兑换码
    public function getCode($coupon_id=1){
        $i = 0;
        while (true) {
            $i++;
			$code1 = $coupon_id;//2个大写字母
			$code2 = rand_string(4,2,'');//2个大写字母
			$code3 = rand_string(10,1,'');//数字
			$code = str_shuffle($code1.''.$code2.''.$code3);//随机打乱字符串
            $data = Db::name('coupon_code')->where(array('code'=>$code))->find();
            if(empty($data)){
                return $code;
            }
            if($i > 20) {
                return $code;
            }
        }
    }
	
    public function fetchAll2(){
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        if(!($data = $cache->get($this->token))) {
            $result = $this->select();
            foreach ($result as $row) {
                $row['v'] = @unserialize($row['v']);
                $data[$row[$this->pk]] = $row['v'];
            }
            $cache->set($this->token, $data);
        }
        $this->settings = $data;
        return $this->settings;
    }
	
	
	//静态方法
	public static function config(){
		$config = model('Setting')->fetchAll2();
        return $config;
    }

    public function checkUsersCompany($uid){
        $t = time();
        $uc = Db::name('users_company')->where(array('user_id'=>$uid,'status'=>1,'endtime'=>array('gt',$t),'status'=>1))->find();
        $is_company = 1;
        if($uc){
            $is_company = 2;
        }
        return $is_company;
    }


    //易达接口获取公司类型参数封装
    public function getYyDeliveryType($cargodata){
        if($cargodata['express_code'] == '京东'){
            $deliveryType = 'JD';
        }elseif($cargodata['express_code'] == '圆通'){
            $deliveryType = 'YTO';
        }elseif($cargodata['express_code'] == '申通'){
            $deliveryType = 'STO-INT';
        }elseif($cargodata['express_code'] == '德邦'){
            $deliveryType = 'DOP';
        }elseif($cargodata['express_code'] == '极兔'){
            $deliveryType = 'JT';
        }elseif($cargodata['express_code'] == '中通'){
            $deliveryType = 'ZTO';
        }elseif($cargodata['express_code'] == '顺丰'){
            $deliveryType = 'SF';
        }elseif($cargodata['express_code'] == '韵达'){
            $deliveryType = 'YUND';
        }elseif($cargodata['express_code'] == '菜鸟'){
            $deliveryType = 'CNSD';
        }elseif($cargodata['express_code'] == '百世'){
            $deliveryType = 'BEST';
        }elseif(strstr($cargodata['express_code'],'京东') == true){
            $deliveryType = 'JD';
        }elseif(strstr($cargodata['express_code'],'圆通') == true){
            $deliveryType = 'YTO';
        }elseif(strstr($cargodata['express_code'],'申通') == true){
            $deliveryType = 'STO-INT';
        }elseif(strstr($cargodata['express_code'],'中通') == true){
            $deliveryType = 'ZTO';
        }elseif(strstr($cargodata['express_code'],'德邦') == true){
            $deliveryType = 'DOP';
        }elseif(strstr($cargodata['express_code'],'极兔') == true){
            $deliveryType = 'JT';
        }elseif(strstr($cargodata['express_code'],'顺丰') == true){
            $deliveryType = 'SF';
        }elseif(strstr($cargodata['express_code'],'韵达') == true){
            $deliveryType = 'YUND';
        }elseif(strstr($cargodata['express_code'],'菜鸟') == true){
            $deliveryType = 'CNSD';
        }elseif(strstr($cargodata['express_code'],'百世') == true){
            $deliveryType = 'BEST';
        }
        return $deliveryType;
    }
	//对接本地寄件城市
	public function getExpressList9($data,$citys){
		foreach($citys as $k=>$v){
			$datas[$k]['c_type']=8;
			$datas[$k]['channel']=$v['city_id'];
			$datas[$k]['channelId']=$v['city_id'];
			$datas[$k]['transportType']= 'city';
			$datas[$k]['type']= 8;
			$datas[$k]['tag']= $v['tag'] ? $v['tag'] : '';
			$datas[$k]['img']=config_weixin_img($v['img']);
			$datas[$k]['isBest']= true;
			if($v['is_bao']==0){
				$datas[$k]['is_baojia'] = 1;
			}else{
				$datas[$k]['is_baojia'] = 0;
			}
			if($v['is_yuyue']==0){
				$datas[$k]['is_yuyue'] = 1;
			}else{
				$datas[$k]['is_yuyue'] = 0;
			}
			$datas[$k]['lanshou']=$v['lanshou'];
			$datas[$k]['info'] ='';
			$datas[$k]['orderby'] =$v['orderby'];
			$datas[$k]['firstPrice'] =(int)$v['firstPrice'];
			$datas[$k]['name']= cut_msubstr($v['cate_name'],0,4,true);
			$datas[$k]['nickname']= cut_msubstr($v['cate_name'],0,4,true);
			$datas[$k]['title']= '';
			
			$v['ratio'] = $v['ratio1'];
			$getZhe = model('Setting')->getZhe($data['uid'],$v);
			$zhe = $getZhe['zhe'];
			$zhe2= $getZhe['zhe2'];
			
			if($v['shou'] && $v['xu']){
				$getCalculateWeight= $this->getCalculateWeight($data['long'],$data['width'],$data['height'],$v['cate_name'],$data['totalWeight']);
				$w = $getCalculateWeight-1;
				if($w > 1){
					$TotalFee = $v['shou']+($v['xu']*$w);
				}else{
					$TotalFee = $v['shou'];
				}
				$getCatePrice = model('Setting')->getCatePrice($data['uid'],$getCalculateWeight,$TotalFee,$v['shou'],$v['xu'],0,0,$v);
				$datas[$k]['discount']= $getCatePrice['discount'];
				$datas[$k]['freightInsured']=0;
				$datas[$k]['original_cost']= $getCatePrice['original_cost'];
				$datas[$k]['preOrderFee']=$getCatePrice['preOrderFee'];
				$datas[$k]['vip_discount']=$getCatePrice['vip_discount'];	
				$datas[$k]['sumMoneyYuan']=(int)$getCatePrice['sumMoneyYuan'];	
				$datas[$k]['yuanMoney'] = $getCatePrice['yuanMoney'];
				$datas[$k]['getCatePrice'] = $getCatePrice;
			}else{
				$datas[$k]['discount']= 0;
				$datas[$k]['freightInsured']=0;
				$datas[$k]['original_cost']= 0;
				$datas[$k]['preOrderFee']= "0";
				$datas[$k]['vip_discount']= 0;	
			}
		}
		
		foreach($datas as $k2=>$v2){
			if($v2['discount'] ==0){
				unset($datas[$k2]);
			}
		}
		return @array_values($datas);
	}
	
	

	public function getExpressList10($data,$areas){
		foreach($areas as $k=>$v){
			$datas[$k]['c_type']=8;
			$datas[$k]['channel']=$v['area_id'];
			$datas[$k]['channelId']=$v['area_id'];
			$datas[$k]['transportType']= 'area';
			$datas[$k]['type']= 8;
			$datas[$k]['tag']= $v['tag'] ? $v['tag'] : '';
			$datas[$k]['img']=config_weixin_img($v['img']);
			$datas[$k]['isBest']= true;
			if($v['is_bao']==0){
				$datas[$k]['is_baojia'] = 1;
			}else{
				$datas[$k]['is_baojia'] = 0;
			}
			if($v['is_yuyue']==0){
				$datas[$k]['is_yuyue'] = 1;
			}else{
				$datas[$k]['is_yuyue'] = 0;
			}
			$datas[$k]['lanshou']=$v['lanshou'];
			$datas[$k]['info'] ='';
			$datas[$k]['orderby'] =$v['orderby'];
			$datas[$k]['firstPrice'] =(int)$v['firstPrice'];
			$datas[$k]['name']= cut_msubstr($v['cate_name'],0,4,true);
			$datas[$k]['nickname']= cut_msubstr($v['cate_name'],0,4,true);
			$datas[$k]['title']= '';
			
			$v['ratio'] = $v['ratio1'];
			$getZhe = model('Setting')->getZhe($data['uid'],$v);
			$zhe = $getZhe['zhe'];
			$zhe2= $getZhe['zhe2'];
			
			if($v['shou'] && $v['xu']){
				$getCalculateWeight= $this->getCalculateWeight($data['long'],$data['width'],$data['height'],$v['cate_name'],$data['totalWeight']);
				$w = $getCalculateWeight-1;
				if($w > 1){
					$TotalFee = $v['shou']+($v['xu']*$w);
				}else{
					$TotalFee = $v['shou'];
				}
				$getCatePrice = model('Setting')->getCatePrice($data['uid'],$getCalculateWeight,$TotalFee,$v['shou'],$v['xu'],0,0,$v);
				$datas[$k]['discount']= $getCatePrice['discount'];
				$datas[$k]['freightInsured']=0;
				$datas[$k]['original_cost']= $getCatePrice['original_cost'];
				$datas[$k]['preOrderFee']=$getCatePrice['preOrderFee'];
				$datas[$k]['vip_discount']=$getCatePrice['vip_discount'];	
				$datas[$k]['sumMoneyYuan']=(int)$getCatePrice['sumMoneyYuan'];	
				$datas[$k]['yuanMoney'] = $getCatePrice['yuanMoney'];
				$datas[$k]['getCatePrice'] = $getCatePrice;
			}else{
				$datas[$k]['discount']= 0;
				$datas[$k]['freightInsured']=0;
				$datas[$k]['original_cost']= 0;
				$datas[$k]['preOrderFee']= "0";
				$datas[$k]['vip_discount']= 0;	
			}
		}
		
		foreach($datas as $k2=>$v2){
			if($v2['discount'] ==0){
				unset($datas[$k2]);
			}
		}
		return @array_values($datas);
	}


    public function getExpressList14($data,$express_cate){
        $datas=array();
        $expressCateList = $express_cate;
        foreach($expressCateList as $k=>$v){
            $datas[$k]['c_type']=$v['type'];
            $datas[$k]['channel']=$v['cate_id'];
            $datas[$k]['channelId']=$v['cate_id'];
            $datas[$k]['transportType']= 'customize';
            $datas[$k]['type']= $v['type'];
            $datas[$k]['tag']= $v['tag'] ? $v['tag'] : '未定义';
            $datas[$k]['img']=config_weixin_img($v['photo']);
            $datas[$k]['isBest']= true;
            if($v['is_bao']==0){
                $datas[$k]['is_baojia'] = 1;
            }else{
                $datas[$k]['is_baojia'] = 0;
            }
            if($v['is_yuyue']==0){
                $datas[$k]['is_yuyue'] = 1;
            }else{
                $datas[$k]['is_yuyue'] = 0;
            }
            $datas[$k]['lanshou']=$v['lanshou'];
            $datas[$k]['info'] =$v['info'];
            $datas[$k]['orderby'] =$v['orderby'];
            $datas[$k]['firstPrice'] =(int)$v['firstPrice'];
            $datas[$k]['name']= cut_msubstr($v['cate_name'],0,4,true);
            $datas[$k]['nickname']= cut_msubstr($v['cate_name'],0,4,true);
            $datas[$k]['title']= '';
            $getZhe = model('Setting')->getZhe($data['uid'],$v);
            $zhe = $getZhe['zhe'];
            $zhe2= $getZhe['zhe2'];
            $getCalculateWeight= $this->getCalculateWeight($data['long'],$data['width'],$data['height'],$v['cate_name'],$data['totalWeight']);
            $ecp = Db::name('express_cate_province')->where(array('cate_id'=>$v['cate_id'],'star_province_name'=>$data['sender_province'],'end_province_name'=>$data['recipients_province']))->find();
            if($ecp){
                $w = $getCalculateWeight-1;
                if($w >=1){
                    $TotalFee = $ecp['shou']+($ecp['xu']*$w);
                }else{
                    $TotalFee = $ecp['shou'];
                }
                $getCatePrice = model('Setting')->getCatePrice($data['uid'],$getCalculateWeight,$TotalFee,$ecp['shou'],$ecp['xu'],0,0,$v);
                $datas[$k]['discount']= $getCatePrice['discount'];
                $datas[$k]['freightInsured']=0;
                $datas[$k]['original_cost']= $getCatePrice['original_cost'];
                $datas[$k]['preOrderFee']=$getCatePrice['preOrderFee'];
                $datas[$k]['vip_discount']=$getCatePrice['vip_discount'];
                $datas[$k]['sumMoneyYuan']=(int)$getCatePrice['sumMoneyYuan'];
                $datas[$k]['yuanMoney']=$getCatePrice['yuanMoney'];
                $datas[$k]['getCatePrice'] = $getCatePrice;
            }else{
                $datas[$k]['discount']= 0;
                $datas[$k]['freightInsured']=0;
                $datas[$k]['original_cost']= 0;
                $datas[$k]['preOrderFee']= "0";
                $datas[$k]['vip_discount']= 0;
            }
        }
        foreach($datas as $k2=>$v2){
            if($v2['discount'] ==0){
                unset($datas[$k2]);
            }
        }
        return @array_values($datas);
    }


    public function getExpressList12($data){
        $datas=array();
        $expressCateList = Db::name('express_cate')->where(array('type'=>12,'firstPrice'=>0))->limit(0,5)->select();
        foreach($expressCateList as $k=>$v){
            $datas[$k]['c_type']=$v['type'];
            $datas[$k]['channel']=$v['cate_id'];
            $datas[$k]['channelId']=$v['cate_id'];
            $datas[$k]['transportType']= $v['cate_id'];
            $datas[$k]['type']= $v['type'];
            $datas[$k]['tag']= $v['tag'] ? $v['tag'] : '未定义';
            $datas[$k]['img']=config_weixin_img($v['photo']);
            $datas[$k]['isBest']= true;
            if($v['is_bao']==0){
                $datas[$k]['is_baojia'] = 1;
            }else{
                $datas[$k]['is_baojia'] = 0;
            }
            if($v['is_yuyue']==0){
                $datas[$k]['is_yuyue'] = 1;
            }else{
                $datas[$k]['is_yuyue'] = 0;
            }
            $datas[$k]['lanshou']=$v['lanshou'];
            $datas[$k]['info'] =$v['info'];
            $datas[$k]['orderby'] =$v['orderby'];
            $datas[$k]['firstPrice'] =(int)$v['firstPrice'];
            $datas[$k]['name']= cut_msubstr($v['cate_name'],0,4,true);
            $datas[$k]['nickname']= cut_msubstr($v['cate_name'],0,4,true);
            $datas[$k]['title']= '';
            $getZhe = model('Setting')->getZhe($data['uid'],$v);
            $zhe = $getZhe['zhe'];
            $zhe2= $getZhe['zhe2'];
            $getCalculateWeight= $this->getCalculateWeight($data['long'],$data['width'],$data['height'],$v['cate_name'],$data['totalWeight']);
            $ecp = Db::name('express_cate_province')->where(array('cate_id'=>$v['cate_id'],'star_province_name'=>$data['sender_province'],'end_province_name'=>$data['recipients_province']))->find();
            if($ecp){
                $w = $getCalculateWeight-1;
                if($w >=1){
                    $TotalFee = $ecp['shou']+($ecp['xu']*$w);
                }else{
                    $TotalFee = $ecp['shou'];
                }
                $getCatePrice = model('Setting')->getCatePrice($data['uid'],$getCalculateWeight,$TotalFee,$ecp['shou'],$ecp['xu'],0,0,$v);
                $datas[$k]['discount']= $getCatePrice['discount'];
                $datas[$k]['freightInsured']=0;
                $datas[$k]['original_cost']= $getCatePrice['original_cost'];
                $datas[$k]['preOrderFee']=$getCatePrice['preOrderFee'];
                $datas[$k]['vip_discount']=$getCatePrice['vip_discount'];
                $datas[$k]['sumMoneyYuan']=(int)$getCatePrice['sumMoneyYuan'];
                $datas[$k]['yuanMoney']=$getCatePrice['yuanMoney'];
                $datas[$k]['getCatePrice'] = $getCatePrice;
            }else{
                $datas[$k]['discount']= 0;
                $datas[$k]['freightInsured']=0;
                $datas[$k]['original_cost']= 0;
                $datas[$k]['preOrderFee']= "0";
                $datas[$k]['vip_discount']= 0;
            }
        }
        foreach($datas as $k2=>$v2){
            if($v2['discount'] ==0){
                unset($datas[$k2]);
            }
        }
        return @array_values($datas);
    }

    public function getExpressList13($data,$addr_id=0){
        $datas=array();
        $user_addr = Db::name('user_addr')->where(array('addr_id'=>$addr_id))->find();
        if($user_addr['community_id']){
            $community = Db::name('business_community')->where(array('community_id'=>$user_addr['community_id']))->find();
            $v = $community;
        }
        $k = 0;
        if($v){
            $datas[$k]['c_type']=8;
            $datas[$k]['channel']=$v['community_id'];
            $datas[$k]['channelId']=$v['community_id'];
            $datas[$k]['transportType']= 'community';
            $datas[$k]['type']= 8;
            $datas[$k]['tag']= $v['tag'] ? $v['tag'] : '未定义';
            $datas[$k]['img']=config_weixin_img($v['img']);
            $datas[$k]['isBest']= true;
            if($v['is_bao']==0){
                $datas[$k]['is_baojia'] = 1;
            }else{
                $datas[$k]['is_baojia'] = 0;
            }
            if($v['is_yuyue']==0){
                $datas[$k]['is_yuyue'] = 1;
            }else{
                $datas[$k]['is_yuyue'] = 0;
            }
            $datas[$k]['lanshou']=$v['lanshou'];
            $datas[$k]['info'] =$v['info'];
            $datas[$k]['orderby'] =$v['orderby'];
            $datas[$k]['firstPrice'] =(int)$v['firstPrice'];
            $datas[$k]['name']= cut_msubstr($v['cate_name'],0,4,true);
            $datas[$k]['nickname']= cut_msubstr($v['cate_name'],0,4,true);
            $datas[$k]['title']= '';
            $getZhe = model('Setting')->getZhe($data['uid'],$v);
            $zhe = $getZhe['zhe'];
            $zhe2= $getZhe['zhe2'];
            $getCalculateWeight= $this->getCalculateWeight($data['long'],$data['width'],$data['height'],$v['cate_name'],$data['totalWeight']);
            $ecp = Db::name('business_cate_provinces')->where(array('community_id'=>$v['community_id'],'star_province_name'=>$data['sender_province'],'end_province_name'=>$data['recipients_province']))->find();
            if($ecp){
                $w = $getCalculateWeight-1;
                if($w >=1){
                    $TotalFee = $ecp['shou']+($ecp['xu']*$w);
                }else{
                    $TotalFee = $ecp['shou'];
                }
                $getCatePrice = model('Setting')->getCatePrice($data['uid'],$getCalculateWeight,$TotalFee,$ecp['shou'],$ecp['xu'],0,0,$v);
                $datas[$k]['discount']= $getCatePrice['discount'];
                $datas[$k]['freightInsured']=0;
                $datas[$k]['original_cost']= $getCatePrice['original_cost'];
                $datas[$k]['preOrderFee']=$getCatePrice['preOrderFee'];
                $datas[$k]['vip_discount']=$getCatePrice['vip_discount'];
                $datas[$k]['sumMoneyYuan']=(int)$getCatePrice['sumMoneyYuan'];
                $datas[$k]['yuanMoney']=(int)$getCatePrice['yuanMoney'];
                $datas[$k]['getCatePrice'] = $getCatePrice;
            }else{
                $datas[$k]['discount']= 0;
                $datas[$k]['freightInsured']=0;
                $datas[$k]['original_cost']= 0;
                $datas[$k]['preOrderFee']= "0";
                $datas[$k]['vip_discount']= 0;
            }
        }
        foreach($datas as $k2=>$v2){
            if($v2['discount'] ==0){
                unset($datas[$k2]);
            }
        }
        return @array_values($datas);
    }
    /**
	 * 众发取消/查单用的平台 orderNo
	 */
	public function getZhongfaPlatformOrderNo($order){
		if(!is_array($order)){
			return '';
		}
		if(!empty($order['expressId']) && preg_match('/^ORD/i', (string)$order['expressId'])){
			return (string)$order['expressId'];
		}
		if(!empty($order['deliveryId']) && preg_match('/^ORD/i', (string)$order['deliveryId'])){
			return (string)$order['deliveryId'];
		}
		return !empty($order['expressId']) ? (string)$order['expressId'] : (!empty($order['deliveryId']) ? (string)$order['deliveryId'] : '');
	}
    
    /** 构建 /open/v1/order/query 请求体（orderNo 与 outOrderNo 至少一个） */
	public function buildZhongfaQueryParams($order){
		if(!is_array($order)){
			return array();
		}
		$params = array();
		$platformNo = $this->getZhongfaPlatformOrderNo($order);
		if($platformNo !== ''){
			$params['orderNo'] = $platformNo;
		}
		$outOrderNo = '';
		if(!empty($order['requestParams'])){
			$requestParams = iunserializer($order['requestParams']);
			if(is_array($requestParams) && !empty($requestParams['outOrderNo'])){
				$outOrderNo = trim((string)$requestParams['outOrderNo']);
			}
		}
		if($outOrderNo === '' && !empty($order['id'])){
			$outOrderNo = (string)$order['id'];
		}
		if($outOrderNo !== ''){
			$params['outOrderNo'] = $outOrderNo;
		}
		return $params;
	}
	
  	/** 众发物流轨迹查询 POST /open/v1/track/query */
	public function zhongfaQueryTrack($order){
		$orderIdIn = is_array($order) ? (isset($order['id']) ? $order['id'] : '') : $order;
		$this->zfDebugLog('zhongfaQueryTrack', '入参 order_id='.$orderIdIn);
		if(!is_array($order)){
			$queryId = (int)$order;
			$this->zfDebugLog('zhongfaQueryTrack', 'DB查询开始 order_id='.$queryId);
			$order = Db::name('express_order')->where(array('id' => $queryId))->find();
			if($order){
				$this->zfDebugLog('zhongfaQueryTrack', 'DB查询成功 order_id='.$queryId.' type='.($order['type'] ?? '').' expressId='.($order['expressId'] ?? '').' expressNo='.($order['expressNo'] ?? '').' deliveryId='.($order['deliveryId'] ?? ''));
			}else{
				$this->zfDebugLog('zhongfaQueryTrack', 'DB查询失败 order_id='.$queryId.' 未找到订单');
			}
		}
		if(!$order || !is_array($order)){
			$this->zfDebugLog('zhongfaQueryTrack', '跳过 order_id='.$orderIdIn.' 订单不存在');
			return array('code' => -1, 'msg' => '订单不存在');
		}
		$orderId = isset($order['id']) ? $order['id'] : '';
		$this->zfDebugLog('zhongfaQueryTrack', '订单字段 order_id='.$orderId.' type='.($order['type'] ?? '').' expressId='.($order['expressId'] ?? '').' expressNo='.($order['expressNo'] ?? '').' deliveryId='.($order['deliveryId'] ?? ''));
		$params = $this->buildZhongfaQueryParams($order);
		if(empty($params['orderNo']) && empty($params['outOrderNo'])){
			$this->zfDebugLog('zhongfaQueryTrack', '跳过 order_id='.$orderId.' 无 orderNo/outOrderNo expressId='.($order['expressId'] ?? '').' deliveryId='.($order['deliveryId'] ?? ''));
			return array('code' => -1, 'msg' => '缺少众发轨迹查询参数');
		}
		$this->zfDebugLog('zhongfaQueryTrack', '请求 order_id='.$orderId.' params='.json_encode($params, JSON_UNESCAPED_UNICODE));
		$result = $this->zhongfaExecute($params, 'track');
		$zfCode = is_array($result) && isset($result['code']) ? (string)$result['code'] : '';
		$zfMsg = is_array($result) ? (isset($result['msg']) ? $result['msg'] : (isset($result['message']) ? $result['message'] : '')) : '';
		$trackCount = 0;
		if(is_array($result) && !empty($result['data']['trackingList']) && is_array($result['data']['trackingList'])){
			$trackCount = count($result['data']['trackingList']);
		}
		$this->zfDebugLog('zhongfaQueryTrack', '响应 order_id='.$orderId.' code='.$zfCode.' msg='.$zfMsg.' trackingCount='.$trackCount.' body='.json_encode($result, JSON_UNESCAPED_UNICODE));
		return $result;
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
			$this->zfDebugLog('formatZhongfaTrackDisplay', '跳过 入参非数组 type='.gettype($trackResult));
			return array(
				'logistics_info' => $logistics_info,
				'logisticsInfo' => $logisticsInfo,
				'pressList' => $pressList,
			);
		}
		$zfCode = isset($trackResult['code']) ? (string)$trackResult['code'] : '';
		$zfMsg = isset($trackResult['msg']) ? (string)$trackResult['msg'] : (isset($trackResult['message']) ? (string)$trackResult['message'] : '');
		$ok = ($zfCode === '0' || $zfCode === '00')
			|| (isset($trackResult['success']) && $trackResult['success']);
		$this->zfDebugLog('formatZhongfaTrackDisplay', '入参 code='.$zfCode.' msg='.$zfMsg.' success='.(isset($trackResult['success']) ? ($trackResult['success'] ? '1' : '0') : '-').' ok='.($ok ? '1' : '0'));
		if(!$ok){
			$this->zfDebugLog('formatZhongfaTrackDisplay', '跳过 业务失败 code='.$zfCode.' msg='.$zfMsg);
			return array(
				'logistics_info' => $logistics_info,
				'logisticsInfo' => $logisticsInfo,
				'pressList' => $pressList,
			);
		}
		$zfData = isset($trackResult['data']) && is_array($trackResult['data']) ? $trackResult['data'] : array();
		$trackingList = !empty($zfData['trackingList']) && is_array($zfData['trackingList'])
			? $zfData['trackingList'] : array();
		$rawCount = count($trackingList);
		$this->zfDebugLog('formatZhongfaTrackDisplay', 'data字段 orderStatus='.(isset($zfData['orderStatus']) ? $zfData['orderStatus'] : '').' orderStatusDesc='.(isset($zfData['orderStatusDesc']) ? $zfData['orderStatusDesc'] : '').' logisticsStatusDesc='.(isset($zfData['logisticsStatusDesc']) ? $zfData['logisticsStatusDesc'] : '').' trackingListCount='.$rawCount);
		if(empty($trackingList)){
			$fallbackDesc = '';
			if(!empty($zfData['orderStatusDesc'])){
				$fallbackDesc = trim((string)$zfData['orderStatusDesc']);
			}
			if(!empty($zfData['logisticsStatusDesc'])){
				$lsd = trim((string)$zfData['logisticsStatusDesc']);
				$fallbackDesc = $fallbackDesc !== '' ? $fallbackDesc.' / '.$lsd : $lsd;
			}
			if($fallbackDesc === '' && !empty($zfData['orderStatus'])){
				$fallbackDesc = (string)$zfData['orderStatus'];
			}
			if($fallbackDesc !== ''){
				$this->zfDebugLog('formatZhongfaTrackDisplay', 'fallback 无trackingList 使用状态描述 desc='.$fallbackDesc);
				$trackingList = array(array(
					'trackTime' => '',
					'description' => $fallbackDesc,
					'location' => '',
				));
			}else{
				$this->zfDebugLog('formatZhongfaTrackDisplay', '空轨迹 trackingList为空且无状态兜底');
			}
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
			$logistics_info[$k] = array(
				'time' => $time,
				'description' => $desc,
				'desc' => $desc,
				'context' => $desc,
			);
			$logisticsInfo[$k]['desc'] = $desc;
			$logisticsInfo[$k]['time'] = $time;
			$pressList[$k] = array(
				'trackTime' => $time,
				'time' => $time,
				'description' => isset($v['description']) ? (string)$v['description'] : $desc,
				'desc' => $desc,
				'location' => $location,
				'logisticsStatus' => isset($v['logisticsStatus']) ? $v['logisticsStatus'] : '',
				'context' => $desc,
			);
		}
		$outCount = count($logistics_info);
		$firstDesc = $outCount > 0 && isset($logistics_info[0]['description']) ? $logistics_info[0]['description'] : '';
		$firstTime = $outCount > 0 && isset($logistics_info[0]['time']) ? $logistics_info[0]['time'] : '';
		$this->zfDebugLog('formatZhongfaTrackDisplay', '完成 count='.$outCount.' firstTime='.$firstTime.' firstDesc='.substr($firstDesc, 0, 80));
		return array(
			'logistics_info' => array_values($logistics_info),
			'logisticsInfo' => array_values($logisticsInfo),
			'pressList' => array_values($pressList),
		);
	}
    
    
	//众发物流获取accessToken
	public function getZhongfaAccessToken(){
		$config = model('Setting')->fetchAll2();
		$appKey = $config['wxapp']['zf_appkey'];
		$appSecret = $config['wxapp']['zf_appsecret'];
		$baseUrl = rtrim($config['wxapp']['zf_url'], '/');
		
		$cacheKey = 'zhongfa_access_token_' . $appKey;
		$cachedToken = cache($cacheKey);
		
		if($cachedToken){
			$this->zfDebugLog('getZhongfaAccessToken', '使用缓存 token');
			return $cachedToken;
		}
		$this->zfDebugLog('getZhongfaAccessToken', '请求新 token url='.$baseUrl.'/open/v1/auth/token');
		
		$timestamp = time();
		$nonce = $this->generateNonce();
		$signature = md5($appKey . $timestamp . $nonce . $appSecret);
		
		$postData = array(
			'appKey' => $appKey,
			'timestamp' => $timestamp,
			'nonce' => $nonce,
			'signature' => $signature
		);
		
		$url = $baseUrl . '/open/v1/auth/token';
		$header = array("Content-Type:application/json");
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData, 320));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		$result = curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($result === false){
			$this->zfDebugLog('getZhongfaAccessToken', 'curl错误: '.curl_error($curl).' url='.$url);
		}
		curl_close($curl);
		$this->zfDebugLog('getZhongfaAccessToken', 'HTTP='.$httpCode.' 响应='.substr((string)$result, 0, 500));
		$response = json_decode($result, true);
		
		if($response && isset($response['code']) && $response['code'] == 0){
			$accessToken = $response['data']['accessToken'];
			$expiresIn = $response['data']['expiresIn'];
			cache($cacheKey, $accessToken, $expiresIn - 300);
			$this->zfDebugLog('getZhongfaAccessToken', '成功 expiresIn='.$expiresIn);
			return $accessToken;
		}
		
		$this->zfDebugLog('getZhongfaAccessToken', '失败 response='.json_encode($response, JSON_UNESCAPED_UNICODE));
		return null;
	}

	/** 众发联系人手机号校验（不依赖 common.php 的 isMobile，避免未加载时 fatal） */
	private function isZhongfaMobile($mobile){
		$mobile = trim((string)$mobile);
		if($mobile === '' || $mobile === 'undefined' || $mobile === 'null'){
			return false;
		}
		if(!is_numeric($mobile)){
			return false;
		}
		return preg_match('/^1[3456789]\d{9}$/', $mobile) ? true : false;
	}

	public function buildZhongfaContact($name, $mobile, $phone, $province, $city, $area, $address, $company = '', $postCode = ''){
		$this->zfDebugLog('buildZhongfaContact', '进入 name='.trim((string)$name).' mobile='.trim((string)$mobile).' phone='.trim((string)$phone));
		$mobile = trim((string)$mobile);
		$phone = trim((string)$phone);
		if($mobile === 'undefined' || $mobile === 'null'){
			$mobile = '';
		}
		if($phone === 'undefined' || $phone === 'null'){
			$phone = '';
		}
		if(!$this->isZhongfaMobile($mobile) && $this->isZhongfaMobile($phone)){
			$mobile = $phone;
		}
		if(!$this->isZhongfaMobile($mobile) && $phone !== ''){
			$mobile = $phone;
		}
		if(!$this->isZhongfaMobile($mobile)){
			$this->zfDebugLog('buildZhongfaContact', '警告: 手机号无效 name='.$name.' mobile='.$mobile.' phone='.$phone);
		}

		$contact = array(
			'name' => trim((string)$name),
			'mobile' => $mobile,
			'province' => trim((string)$province),
			'city' => trim((string)$city),
			'area' => trim((string)$area),
			'address' => trim((string)$address),
		);

		$company = trim((string)$company);
		if($company !== ''){
			$contact['company'] = $company;
		}

		if($phone !== '' && $phone !== $mobile){
			$contact['phone'] = $phone;
		}

		$postCode = trim((string)$postCode);
		if(preg_match('/^\d{6}$/', $postCode)){
			$contact['postCode'] = $postCode;
		}

		$this->zfDebugLog('buildZhongfaContact', '完成 mobile='.$contact['mobile']);
		return $contact;
	}

	public function buildZhongfaPackageInfo($goodsName, $length, $width, $height, $num, $weightKg){
		$length = max(1, (int)$length);
		$width = max(1, (int)$width);
		$height = max(1, (int)$height);
		$num = max(1, (int)$num);
		$weightKg = max(1, (int)$weightKg);

		return array(
			'goodsName' => $goodsName ? $goodsName : '日用品',
			'length' => $length,
			'width' => $width,
			'height' => $height,
			'num' => $num,
			'weight' => max(100, $weightKg * 1000),
			'volume' => max(1, $length * $width * $height),
		);
	}
	
	//众发物流生成nonce
	private function generateNonce(){
		$data = random_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);
		$uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
		return str_replace('-', '', $uuid);
	}
	
	//众发物流执行方法
	public function zhongfaExecute($data,$Method='estimate'){
		$this->zfDebugLog('zhongfaExecute', '开始 method='.$Method.' data='.json_encode($data, JSON_UNESCAPED_UNICODE));
		$config = model('Setting')->fetchAll2();
		$baseUrl = rtrim((string)($config['wxapp']['zf_url'] ?? ''), '/');
		$appKey = trim((string)($config['wxapp']['zf_appkey'] ?? ''));
		if($baseUrl === '' || $appKey === ''){
			$this->zfDebugLog('zhongfaExecute', '失败[0]: 众发配置不完整 baseUrl='.($baseUrl ?: '(空)').' appKey='.($appKey ? '(已配置)' : '(空)'));
			return array('code' => -1, 'msg' => '众发物流配置不完整');
		}
		$this->zfDebugLog('zhongfaExecute', '配置 baseUrl='.$baseUrl.' appKey='.$appKey);
		
		$accessToken = $this->getZhongfaAccessToken();
		if(!$accessToken){
			$this->zfDebugLog('zhongfaExecute', '失败[1]: 获取accessToken失败 method='.$Method);
			return array('code' => -1, 'msg' => '获取accessToken失败');
		}
		$this->zfDebugLog('zhongfaExecute', 'token已获取 len='.strlen($accessToken));
		
		$url = $baseUrl;
		switch($Method){
			case 'estimate':
				$url .= '/open/v1/service/estimate';
				break;
			case 'order':
				$url .= '/open/v1/service/order';
				break;
			case 'cancel':
				$url .= '/open/v1/service/order/cancel';
				break;
			case 'query':
				$url .= '/open/v1/order/query';
				break;
			case 'track':
				$url .= '/open/v1/track/query';
				break;
			default:
				$url .= '/open/v1/service/estimate';
		}
		
		$header = array(
			"Content-Type:application/json",
			"Authorization: Bearer " . $accessToken
		);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_POST, 1);
		$postJson = json_encode($data, 320);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postJson);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		$this->zfDebugLog('zhongfaExecute', '请求[2] method='.$Method.' url='.$url);
		$this->zfDebugLog('zhongfaExecute', '请求体='.$postJson);
		$result = curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($result === false){
			$error = curl_error($curl);
			curl_close($curl);
			$this->zfDebugLog('zhongfaExecute', '失败[3]: curl错误 '.$error);
			return array('code' => -1, 'msg' => $error ? $error : '众发物流接口请求失败');
		}
		curl_close($curl);
		$this->zfDebugLog('zhongfaExecute', 'HTTP='.$httpCode.' 响应='.$result);
		$decoded = json_decode($result, true);
		if($decoded === null && json_last_error() !== JSON_ERROR_NONE){
			$this->zfDebugLog('zhongfaExecute', '失败[4]: JSON解析错误 '.json_last_error_msg());
			return array('code' => -1, 'msg' => '众发响应JSON解析失败');
		}
		$bizCode = isset($decoded['code']) ? $decoded['code'] : '(无code)';
		$bizMsg = isset($decoded['msg']) ? $decoded['msg'] : (isset($decoded['message']) ? $decoded['message'] : '');
		$this->zfDebugLog('zhongfaExecute', '完成 method='.$Method.' code='.$bizCode.' msg='.$bizMsg);
		return $decoded;
	}
    	
	/** 众发/choosecom 调试日志 → /tmp/zf_debug.log */
	private function zfDebugLog($step, $message = ''){
		$line = '['.date('Y-m-d H:i:s').']['.$step.'] '.$message."\n";
		@file_put_contents('/tmp/zf_debug.log', $line, FILE_APPEND);
	}
	
	/**
	 * 众发金额调试：按后台「调试价格除数」缩小展示/支付价（单位：分）
	 * 配置 wxapp.zf_debug_price_divisor：1=正常，10000=缩小一万倍（7880分→约1分）
	 */
	public function applyZhongfaDebugPriceFen($amountFen){
		$amountFen = (int)$amountFen;
		if($amountFen <= 0){
			return 0;
		}
		$config = model('Setting')->fetchAll2();
		$this->zfDebugLog('applyZhongfaDebugPriceFen', 'config='.json_encode($config, JSON_UNESCAPED_UNICODE));
		$divisor = (int)(isset($config['wxapp']['zf_debug_price_divisor']) ? $config['wxapp']['zf_debug_price_divisor'] : 1);
		$this->zfDebugLog('applyZhongfaDebugPriceFen', '从配置读取的divisor='.$divisor);
		$divisor = 10000; // 联调：强制缩小一万倍，上线前删掉这行
		$this->zfDebugLog('applyZhongfaDebugPriceFen', '强制设置后的divisor='.$divisor);
		if($divisor <= 1){
			return $amountFen;
		}
		$debugAmount = max(1, (int)round($amountFen / $divisor));
		$this->zfDebugLog('applyZhongfaDebugPriceFen', '原价(分)='.$amountFen.' 除数='.$divisor.' 调试价(分)='.$debugAmount.' 约'.round($debugAmount / 100, 4).'元');
		return $debugAmount;
	}
	/** 众发下单必填：补全 sendStartTime / sendEndTime（格式 Y-m-d H:i:s） */
	public function fillZhongfaPickupTimes(&$params, $order = null){
		if(!is_array($params)){
			$params = array();
		}
		$start = isset($params['sendStartTime']) ? trim((string)$params['sendStartTime']) : '';
		$end = isset($params['sendEndTime']) ? trim((string)$params['sendEndTime']) : '';
		if($start !== '' && $end !== ''){
			return $params;
		}

		$yuyuetime = '';
		if(is_array($order) && !empty($order['yuyuetime'])){
			$yuyuetime = trim((string)$order['yuyuetime']);
		}
		if($yuyuetime !== '' && $yuyuetime !== '0'){
			if(preg_match('/^(\d{4}-\d{2}-\d{2})\s+(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/', $yuyuetime, $m)){
				$date = $m[1];
				$params['sendStartTime'] = $date.' '.$this->normalizeZhongfaTimePart($m[2]);
				$params['sendEndTime'] = $date.' '.$this->normalizeZhongfaTimePart($m[3]);
				return $params;
			}
			if(preg_match('/^(\d{4}-\d{2}-\d{2})\s+(\d{1,2}:\d{2}:\d{2})\s+(\d{1,2}:\d{2}:\d{2})/', $yuyuetime, $m)){
				$params['sendStartTime'] = $m[1].' '.$m[2];
				$params['sendEndTime'] = $m[1].' '.$m[3];
				return $params;
			}
		}

		$pickupDate = date('Y-m-d', strtotime('+1 day'));
		$params['sendStartTime'] = $pickupDate.' 09:00:00';
		$params['sendEndTime'] = $pickupDate.' 11:00:00';
		$this->zfDebugLog('fillZhongfaPickupTimes', '使用默认预约时间 sendStartTime='.$params['sendStartTime'].' sendEndTime='.$params['sendEndTime']);
		return $params;
	}

	private function normalizeZhongfaTimePart($time){
		$time = trim((string)$time);
		if(preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $time)){
			return $time;
		}
		if(preg_match('/^\d{1,2}:\d{2}$/', $time)){
			return $time.':00';
		}
		return '09:00:00';
	}
		/** 众发是否允许小程序选取件时间（wxapp.zf_pickup_time：1=是 0=否，未配置时默认开启） */
	public function isZhongfaPickupTimeEnabled(){
		$config = model('Setting')->fetchAll2();
		if(!isset($config['wxapp']['zf_pickup_time']) || $config['wxapp']['zf_pickup_time'] === ''){
			return true;
		}
		return (int)$config['wxapp']['zf_pickup_time'] === 1;
	}

		//众发物流获取快递公司列表
	public function getExpressList15($data){
		$config = model('Setting')->fetchAll2();
		//	if($config['wxapp']['zf_open'] != 1){
			//	return array();
		//	}
		
		$datas = array();
						file_put_contents('/tmp/zf_debug.log', "data结果: ".json_encode($data, JSON_UNESCAPED_UNICODE)."\n\n", FILE_APPEND);
		//先从数据库获取众发物流的配置（需要在后台添加一个 type=15 的快递分类）
		$expressCateList = Db::name('express_cate')->where(array('type'=>1,'firstPrice'=>0))->limit(0,5)->select();
		
		//如果数据库没有配置，创建一个默认的众发物流选项
		if(empty($expressCateList)){
			$expressCateList = array(
				array(
					'cate_id' => 10015,
					'cate_name' => '众发物流',
					'type' => 1,
					'firstPrice' => 0,
					'photo' => '',
					'tag' => '众发物流',
					'lanshou' => '众发物流',
					'info' => '众发物流配送',
					'orderby' => 0,
					'is_bao' => 1,
					'is_yuyue' => 1,
				)
			);
		}
				file_put_contents('/tmp/zf_debug.log', "expressCateList: ".json_encode($expressCateList, JSON_UNESCAPED_UNICODE)."\n\n", FILE_APPEND);
		$cateJson = json_encode($expressCateList, JSON_UNESCAPED_UNICODE);
		if($cateJson === false){
			$this->zfDebugLog('getExpressList15', 'expressCateList json_encode失败: '.json_last_error_msg().' 改用print_r');
			$cateJson = print_r($expressCateList, true);
		}
		$this->zfDebugLog('getExpressList15', 'expressCateList条数='.count($expressCateList).' '.$cateJson);
		
		$template = $expressCateList[0];
		$this->zfDebugLog('getExpressList15', '步骤1 template cate_id='.(isset($template['cate_id']) ? $template['cate_id'] : ''));
		$zhongfaOrderType = 15;
		
		$this->zfDebugLog('getExpressList15', '步骤2 构建 sender');
		$sender = $this->buildZhongfaContact(
			isset($data['sender_name']) ? $data['sender_name'] : '',
			isset($data['sender_mobile']) ? $data['sender_mobile'] : '',
			isset($data['sender_phone']) ? $data['sender_phone'] : '',
			isset($data['sender_province']) ? $data['sender_province'] : '',
			isset($data['sender_city']) ? $data['sender_city'] : '',
			isset($data['sender_area']) ? $data['sender_area'] : '',
			isset($data['sender_address']) ? $data['sender_address'] : ''
		);
		$this->zfDebugLog('getExpressList15', '步骤2 sender='.json_encode($sender, JSON_UNESCAPED_UNICODE));
		
		$this->zfDebugLog('getExpressList15', '步骤3 构建 receiver');
		$receiver = $this->buildZhongfaContact(
			isset($data['recipients_name']) ? $data['recipients_name'] : '',
			isset($data['recipients_mobile']) ? $data['recipients_mobile'] : '',
			isset($data['recipients_phone']) ? $data['recipients_phone'] : '',
			isset($data['recipients_province']) ? $data['recipients_province'] : '',
			isset($data['recipients_city']) ? $data['recipients_city'] : '',
			isset($data['recipients_area']) ? $data['recipients_area'] : '',
			isset($data['recipients_address']) ? $data['recipients_address'] : ''
		);
		$this->zfDebugLog('getExpressList15', '步骤3 receiver='.json_encode($receiver, JSON_UNESCAPED_UNICODE));
		
		$this->zfDebugLog('getExpressList15', '步骤4 构建 packageInfo');
		$packageInfo = $this->buildZhongfaPackageInfo(
			isset($data['cargoName']) ? $data['cargoName'] : '日用品',
			isset($data['long']) ? $data['long'] : 1,
			isset($data['width']) ? $data['width'] : 1,
			isset($data['height']) ? $data['height'] : 1,
			isset($data['totalNumber']) ? $data['totalNumber'] : 1,
			isset($data['totalWeight']) ? $data['totalWeight'] : 1
		);
		$this->zfDebugLog('getExpressList15', '步骤4 packageInfo='.json_encode($packageInfo, JSON_UNESCAPED_UNICODE));
		
		$estimateData = array(
			'sender' => $sender,
			'receiver' => $receiver,
			'packageInfo' => $packageInfo,
			'additionalService' => array(
				'insuranceValue' => 0,
			),
			'remark' => '',
		);
		$this->zfDebugLog('getExpressList15', '步骤5 estimateData完成');
		$this->zfDebugLog('getExpressList15', '步骤6 调用 zhongfaExecute');
		$result = $this->zhongfaExecute($estimateData, 'estimate');
		
		if(!$result || !isset($result['code']) || $result['code'] != 0 || empty($result['data']) || !is_array($result['data'])){
			return array();
		}
		
		foreach($result['data'] as $k=>$item){
	    	$totalAmount = isset($item['totalAmount']) ? (int)round($item['totalAmount']) : 0;
			if($totalAmount > 0 && $totalAmount < 100){
				$totalAmount = (int)round($totalAmount * 100);
			}
			if($totalAmount <= 0){
				continue;
			}
			// 保存原始金额用于originalFee
			$originalAmount = $totalAmount;
			// 应用调试价格
			$totalAmount = $this->applyZhongfaDebugPriceFen($totalAmount);
			$this->zfDebugLog('getExpressList15', '线路#'.$k.' API金额(分)='.$totalAmount.' 约'.round($totalAmount/100, 2).'元 template_ratio='.(isset($template['ratio']) ? $template['ratio'] : 0));
			$getCatePrice = model('Setting')->getCatePrice(
				isset($data['uid']) ? $data['uid'] : 0,
				max(1,(int)$data['totalWeight']),
				$totalAmount,
				0,
				0,
				$originalAmount,
				0,
				$template
			);
			
			$datas[$k]['c_type'] = $template['type'];
			$datas[$k]['channel'] = isset($item['productCode']) ? $item['productCode'] : $template['cate_id'];
			$datas[$k]['channelId'] = $datas[$k]['channel'];
			$datas[$k]['transportType'] = 'zhongfa';
			$datas[$k]['type'] = $template['type'];
			$datas[$k]['tag'] = isset($item['productName']) && $item['productName'] ? $item['productName'] : ($template['tag'] ? $template['tag'] : '众发物流');
			$datas[$k]['img'] = config_weixin_img($template['photo']);
			$datas[$k]['isBest'] = true;
			$datas[$k]['is_baojia'] = $template['is_bao']==0 ? 1 : 0;
// 			$datas[$k]['is_yuyue'] = $template['is_yuyue']==0 ? 1 : 0;
            $datas[$k]['is_yuyue'] = $this->isZhongfaPickupTimeEnabled() ? 1 : 0;
			$datas[$k]['lanshou'] = $template['lanshou'];
			$datas[$k]['info'] = isset($item['arriveTime']) && $item['arriveTime'] ? $item['arriveTime'] : $template['info'];
			$datas[$k]['orderby'] = $template['orderby'];
			$datas[$k]['firstPrice'] = (int)$template['firstPrice'];
			$datas[$k]['name'] = cut_msubstr(isset($item['expressName']) ? $item['expressName'] : $template['cate_name'],0,4,true);
			$datas[$k]['nickname'] = cut_msubstr(isset($item['expressName']) ? $item['expressName'] : $template['cate_name'],0,4,true);
			$datas[$k]['title'] = isset($item['productName']) ? $item['productName'] : '';
			$datas[$k]['express_code'] = isset($item['expressName']) ? $item['expressName'] : $template['cate_name'];
			$datas[$k]['express_channel'] = isset($item['productCode']) ? $item['productCode'] : '';
			$datas[$k]['expressCode'] = isset($item['expressCode']) ? $item['expressCode'] : '';
			$datas[$k]['productCode'] = isset($item['productCode']) ? $item['productCode'] : '';
			$datas[$k]['orderNo'] = isset($item['orderNo']) ? $item['orderNo'] : '';
			$datas[$k]['providerId'] = isset($item['providerId']) ? $item['providerId'] : 0;
			$datas[$k]['providerProductId'] = isset($item['providerProductId']) ? $item['providerProductId'] : 0;
			$datas[$k]['discount'] = $getCatePrice['discount'];
			$datas[$k]['freightInsured'] = 0;
			$datas[$k]['original_cost'] = $getCatePrice['original_cost'];
			$datas[$k]['preOrderFee'] = $getCatePrice['preOrderFee'];
			$datas[$k]['vip_discount'] = $getCatePrice['vip_discount'];
			$datas[$k]['sumMoneyYuan'] = (int)$getCatePrice['sumMoneyYuan'];
			$datas[$k]['yuanMoney'] = (int)$getCatePrice['yuanMoney'];
		}
		
		return array_values($datas);
	}
	//云洋检测渠道接口
	public function choosecom($data){
		$config = model('Setting')->fetchAll2();
		$u = Db::name('users')->where(array('user_id'=>$data['uid']))->find();
		if(!$u){
			$this->error = '您的会员信息不存在';
			return false;
		}

        $money1 = (int)($config['dianshang']['money1']*100);
        $money2 = (int)($config['dianshang']['money2']*100);
        if($data['type']==4){
            if($u['money']<$money1){
                $this->error = '请先充值才能使用电商寄，点击下面按钮充值';
                return false;
            }
        }
        if($data['type']==5){
            if($u['money']<$money2){
                $this->error = '请先充值才能使用电商寄2，点击下面按钮充值';
                return false;
            }
        }
		
		$is_bind_mobile = (int)$config['wxapp']['is_bind_mobile'];
		$is_add_order = (int)$config['wxapp']['is_add_order'];
		$is_add_order_money = (int)($config['wxapp']['is_add_order_money']*100);
		$is_add_order_weight = (int)$config['wxapp']['is_add_order_weight'];
		$is_add_order_weight_zidingyi = (int)$config['wxapp']['is_add_order_weight_zidingyi'];
		if($is_bind_mobile==1){
			if(!$u['mobile'] || $u['mobile']==''){
				$this->error = '请先到会员中心绑定手机号后下单';
				return false;
			}
			if(!isMobile($u['mobile'])){
				$this->error = '请先到会员中心绑定手机号后下单';
				return false;
			}	
		}
		
		
		if($is_add_order_weight >= 999){
			$weight = 999;
		}elseif($is_add_order_weight <= 0){
			$weight = 999;
		}else{
			$weight = $is_add_order_weight;
		}
		
		if($is_add_order && !$u['rank_id']){
			if($u['money'] < $is_add_order_money){
				$this->error = '请先最低充值'.round($is_add_order_money/100,2).'元或购买VIP后再来下单';
				return false;
			}
		}
		if($data['totalWeight'] > $weight){
			$this->error = '高于【'.$weight.'】KG暂时无法下单';
			return false;
		}
		
		
		
		$sender_mobile = $data['sender_phone'] ? $data['sender_phone'] : $data['sender_mobile'];
		//查询易达黑名单
		$remark = Db::name('user_closed')->where(array('phone'=>$sender_mobile))->value('remark');
		if(!$remark){
			$recipients_mobile = $data['recipients_phone'] ? $data['recipients_phone'] : $data['recipients_mobile'];
			$remark = Db::name('user_closed')->where(array('phone'=>$data['recipients_mobile']))->value('remark');
		}
		if($remark){
			$this->error = '您被关进小黑屋了暂时无法下单【'.$remark.'】';
			return false;
		}
		
		
		//查询云洋黑名单
		$contents['phone']= $data['sender_mobile'];//发件人手机
		if($data['sender_mobile'] && $config['wxapp']['yy_appid'] && $config['wxapp']['yy_secretKey']){
			$performance = $this->performance($contents,$Method ='QUERY_BLACK');
			if($performance['code'] == 0){
				//添加云洋黑名单
				$insert['name'] = $performance['result']['name'];
				$insert['type'] = 3;
				$insert['phone'] = $performance['result']['phone'];
				$insert['remark'] = $performance['result']['reason'];
				$insert['createTime'] = time();
				$insert['create_time'] = time();
				Db::name('user_closed')->insert($insert);
				$this->error = '云洋黑名单'.$performance['result']['reason'];
				return false;
			}
		}


        if($data['type'] == '4'){
            //云腾旺店管家
            $yt_open = 0;
            $yt_uid = 0;
            $yt_uid = (int)$config['wxapp']['yt_uid'];

            if($config['wxapp']['yt_open']==1 && (int)$data['type'] == '4'){
                $yt_open = 1;
            }
            if($config['wxapp']['yt_open']==1 && $yt_uid && $yt_uid != $data['uid']){
                $yt_open = 0;
            }
            if($config['wxapp']['yt_open']==1 && $yt_uid && $yt_uid == $data['uid']){
                $yt_open = 1;
            }
            if($yt_open==1){
                $expressList11 = model('YtApi')->getExpressList11($data);
            }
        }elseif($data['type'] == '5'){
            $getExpressList12 = $this->getExpressList12($data);//自定义接口
        }elseif($data['type'] == '10'){
			//对接领航当日达
			$hangkong_open = 0;
			if($config['wxapp']['hangkong_open']==1 && (int)$data['type'] == '10'){
				$hangkong_open = 1;
			}
			if($hangkong_open==1){
				$expressList7 = model('HangkongLinkApi')->getExpressList7($data);
			}
		}elseif($data['type'] == 11){
            if(!$config['delivery']['yy_appid'] && !$config['delivery']['yy_secretKey']){
                $this->error = '未配置跑腿参数';
                return false;
            }
            $cityServiceList = model('city')->cityServiceList($data);
            if($cityServiceList == false){
                $this->error = model('city')->getError();
                return false;
            }
            return $cityServiceList;
        }elseif($data['type'] == 1){
			//众发物流 - 不走易达
			file_put_contents('/tmp/zf_debug.log', "=== type=15 走众发分支 ===\n", FILE_APPEND);
			$cate15 = Db::name('express_cate')->where(array('type'=>1,'firstPrice'=>0))->limit(0,5)->select();
			if($cate15){
				$getExpressList15 = $this->getExpressList15($data);
				file_put_contents('/tmp/zf_debug.log', "getExpressList15结果: ".json_encode($getExpressList15, JSON_UNESCAPED_UNICODE)."\n\n", FILE_APPEND);
			}else{
				file_put_contents('/tmp/zf_debug.log', "express_cate无type=15记录\n\n", FILE_APPEND);
			}
		}else{
			//易达云端请求数据
			$this->curl = new \Curl();
			$postData['data'] = $data;
			//折扣信息
			$u = Db::name('users')->where(array('user_id'=>$data['uid']))->field('rank_id,vip_rank_id,money')->find();
			$cate = Db::name('express_cate')->field('cate_id,cate_name,firstPrice,type,lanshou,photo,pinyin,pinyin2,tag,ratio,priceA_type,priceA_ratio,priceA_price,priceB_type,priceB_ratio,priceB_price,is_bao,is_yuyue,orderby')->select();
			file_put_contents('/tmp/zf_debug.log', '$cate(DB原始)数量: '.count((array)$cate)."\n", FILE_APPEND);
			file_put_contents('/tmp/zf_debug.log', '$cate(DB原始): '.json_encode($cate, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);
			foreach($cate as $k=>$c){
				$getZhe = model('Setting')->getZhe($data['uid'],$c);
				$cate[$k]['photo'] = config_weixin_img($c['photo']);
				$cate[$k]['zhe'] = $getZhe['zhe'];
				$cate[$k]['zhe2'] = $getZhe['zhe2'];
			}
			$postData['cate'] =$cate;
			file_put_contents('/tmp/zf_debug.log', '$cate(折后/postData[cate])数量: '.count((array)$cate)."\n", FILE_APPEND);
			file_put_contents('/tmp/zf_debug.log', '$cate(折后/postData[cate]): '.json_encode($cate, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);
			
			$postData['company_sort'] = $config['config']['company_sort'];
			$postData['company_moshi'] = $config['config']['company_moshi'];
			$postData['yy_open'] = $config['wxapp']['yy_open'];
			$postData['yy_appid'] = $config['wxapp']['yy_appid'];
			$postData['yy_secretKey'] = $config['wxapp']['yy_secretKey'];
			$postData['yd_open'] = $config['wxapp']['yd_open'];
			$postData['yd_name'] = $config['wxapp']['yd_name'];
			$postData['yd_secret'] = $config['wxapp']['yd_secret'];
			
			$postData['kdn_open'] = $config['wxapp']['kdn_open'];
			$postData['kdn_test'] = $config['wxapp']['kdn_test'];
			$postData['kdn_EBusinessID'] = $config['wxapp']['kdn_EBusinessID'];
			$postData['kdn_ApiKey'] = $config['wxapp']['kdn_ApiKey'];
			$postData['host'] = trim($config['site']['host']);
			$postData['mobile'] = trim($config['site']['mobile']);
			
			
			$url = getHost().'/api/AskApi/choosecom';
			file_put_contents('/tmp/zf_debug.log', "=== choosecom调用 ===\n", FILE_APPEND);
			file_put_contents('/tmp/zf_debug.log', "type=".$data['type']." | cate_id=".($data['cate_id'] ?? '')."\n", FILE_APPEND);
			file_put_contents('/tmp/zf_debug.log', "AskApi/choosecom data: ".json_encode($postData['data'], JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);
			file_put_contents('/tmp/zf_debug.log', "URL: ".$url."\n", FILE_APPEND);

			
			$postJson = json_encode($postData, JSON_UNESCAPED_UNICODE);
            file_put_contents('/tmp/zf_debug.log', "AskApi/choosecom postData(完整请求体): ".$postJson."\n", FILE_APPEND);
            $result = $this->curl->post($url, $postJson);
			$result = json_decode($result,true);
			$getExpressList = $result['data'];
			file_put_contents('/tmp/zf_debug.log', "getExpressList数量: ".count((array)$getExpressList)."\n\n", FILE_APPEND);
		
			//对接跨越
			$ky_open = 0;
			if($config['wxapp']['ky_open']==1 && (int)$data['type'] == '1'){
				$ky_open = 1;
			}
			if($config['wxapp']['ky_open']==1 && (int)$data['type'] == '0'){
				$ky_open = 1;
			}
			if($config['wxapp']['ky_open']==1 && (int)$data['type'] == '3'){
				$ky_open = 1;
			}
			if($ky_open==1){
				$expressList6 = model('KuayueApi')->getExpressList6($data);
			}
			
			//对接京东
			if($config['wxapp']['jd_open']==1){
				$expressList3 = model('JdApi')->getExpressList3($data);
			}
			
			
			
    	    //对接q必达
    		$ulifego_open = 0;
    		if($config['wxapp']['ulifego_open']==1 && (int)$data['type'] == '1'){
    			$ulifego_open = 1;
    		}
    		if($config['wxapp']['ulifego_open']==1 && (int)$data['type'] == '2'){
    			$ulifego_open = 1;
    		}
    		if($config['wxapp']['ulifego_open']==1 && (int)$data['type'] == '3' && $data['totalWeight'] >=30){
    			$ulifego_open = 1;
    		}
    		if($config['wxapp']['ulifego_open']==1 && (int)$data['type'] == '0'){
    			$ulifego_open = 1;
    		}
    		if($ulifego_open==1){
    			$expressList8 = model('UlifegoApi')->getExpressList8($data);
    		}
			
			//对接本地寄件
			$areas = Db::name('area')->where(array('area_name'=>$data['sender_area'],'open'=>1))->limit(0,30)->select();
			if($areas){
				$getExpressList10 = model('Setting')->getExpressList10($data,$areas);
			}
			
			//对接本地寄件
			$citys = Db::name('city')->where(array('name'=>$data['sender_city'],'open'=>1))->limit(0,30)->select();
			if($citys&&!$areas){
				$getExpressList9 = model('Setting')->getExpressList9($data,$citys);
			}
            if($data['sender_id']){
                $getExpressList13 = model('Setting')->getExpressList13($data,$data['sender_id']);
            }

            $express_cate = Db::name('express_cate')->where(array('firstPrice'=>0,'type'=>8))->limit(0,10)->select();
            if($express_cate){
                $getExpressList14 = model('Setting')->getExpressList14($data,$express_cate);
            }
		}
		
		$e = array_merge(
            (array)$getExpressList,(array)$expressList3,(array)$expressList6,(array)$expressList7,(array)$expressList8, (array)$getExpressList9,
            (array)$getExpressList10,(array)$getExpressList12,(array)$getExpressList13,(array)$getExpressList14,(array)$getExpressList15
        );
		$e = array_values($e);
		
		
		foreach($e as $k=>$v){
			if($v['name']=='德邦' && $v['type']=='2' && $data['totalWeight'] >= '20'){
			    unset($e[$k]);
			}
		} 
		$e = array_values($e);
		if(!$e){
			$this->error = '获取快递公司列表失败【请检查参数配置】';
			file_put_contents('/tmp/zf_debug.log', "result2: ".print_r($result, true)."\n", FILE_APPEND);
			file_put_contents('/tmp/zf_debug.log', "type=".$data['type']."\n", FILE_APPEND);
			return false;
		}
		
		
		$get = $e;
		$company_sort = (int)$config['config']['company_sort'];
		if($company_sort == 0){
			$get = arraySequence($get,'orderby','SORT_ASC');
		}elseif($company_sort == 1){
			$get = arraySequence($get,'sumMoneyYuan','SORT_ASC');
		}elseif($company_sort == 2){
			$get = arraySequence($get,'sumMoneyYuan','SORT_DESC');
		}elseif($company_sort == 3){
			$get = arraySequence($get,'lanshou','SORT_ASC');
		}elseif($company_sort == 4){
			$get = arraySequence($get,'lanshou','SORT_DESC');
		}elseif($company_sort == 4){
			$get = $get;
		}
		if(!$get){
			$this->error = '请联系客服下单';
			return false;
		}
		return array_values($get);
	}
	
	
	
	//获取差价
	public function getDiffMoney($uid,$calWeight,$totalWeight,$TotalFee,$v,$freightHaocai=0,$freightInsured=0){
		$logoUrl = model('ExpressOrder')->logoUrl($v['kuaidi'],$v['user_id'],$v['type'],$v);//获取快递公司信息
		$cha_weight = $calWeight-$v['wight'];//超重KG
		$chajia = $TotalFee-$v['sumMoneyYuan_old'];//差价
		//超重
		if($v['firstPrice'] && $v['addPrice'] >= 100 && $cha_weight>0){
			if($logoUrl['priceB_type'] == 0){
				$addPrice = (($v['addPrice']*$logoUrl['priceB_ratio'])/100);
				$addPrice = ($v['addPrice']+$addPrice)*$cha_weight;
				$addPrice = (int)$addPrice;
				$jia = $addPrice;
			}else{
				$addPrice = ($logoUrl['priceB_price']*100)*$cha_weight;
				$addPrice = (int)$addPrice;
				$jia =($v['addPrice']*$cha_weight)+$addPrice;
			}
			$diffMoneyYuan = $jia;//差价+加价
			$diffMoneyYuan= $diffMoneyYuan+$freightHaocai+$freightInsured;//耗材+保价
		}else{
			if($logoUrl['priceB_ratio'] && $logoUrl['priceB_ratio'] > $logoUrl['ratio']){
		        $ratio = $logoUrl['priceB_ratio'];
		    }else{
		        $ratio = $logoUrl['ratio'];
		    }
			//不超重
			if($cha_weight<= 0){
			    if($chajia > 0){
			       $diffMoneyYuan= $chajia;//差价
			    }else{
			        $diffMoneyYuan= $freightHaocai+$freightInsured;//耗材+保价
			    }
			}
			//差价大于100并超重
			if($chajia >= 100 && $cha_weight>=0){
				$jia = ($chajia*$ratio)/100;//实际收费加价%
				$diffMoneyYuan = $chajia+$jia;//差价+加价
				$mark=0;
				if($v['kuaidi'] == '德邦重货' || $v['kuaidi'] == '京东重货'  || $v['kuaidi'] == '顺心捷达'){
					$mark=1;
				}
				if($mark==1){
					$diffMoneyYuan= $diffMoneyYuan;//耗材+保价
				}else{
					$diffMoneyYuan= $diffMoneyYuan+$freightHaocai+$freightInsured;//耗材+保价 
				}	
			}
			//差价小于100并超重
			if($chajia < 100 && $cha_weight>=0){
				$z = ($TotalFee)/($calWeight);
				$z = (int)$z;
				if($z > 150 && $cha_weight<=1){
					$z = 150;
				}
				if($z <= 0 && $cha_weight<=1){
					$z = 150;
				}
				$chajia = $z*$cha_weight;
				$jia = ($chajia*$ratio)/100;//实际收费加价%
				$diffMoneyYuan = $chajia+$jia;//差价+加价
				$diffMoneyYuan= $diffMoneyYuan+$freightHaocai+$freightInsured;//耗材+保价	
			}
		}
		$diffMoneyYuan = (int)$diffMoneyYuan;
		return (int)$diffMoneyYuan;
	}
	
	
	
	 //返回易达首重续重
	public function getYidastartEndMoney($uid,$v,$totalWeight=1){
		if($v['calcFeeType']=='discount'){
			$priceA = 0;
			$priceB=0;
		};
		if($v['calcFeeType']=='profit'){
			$price = $v['price'];
			$price = json_decode($price,true);
			foreach($price as $ks=>$vs){
				if($totalWeight>=$vs['start']&&$totalWeight<=$vs['end']){
					$z=$vs;
				}
			}
			$originalPrice = $v['originalPrice'];
			$originalPrice = json_decode($originalPrice,true);
			if($z){
				$priceA = $z['first']*100;
				$priceB = $z['add']*100;
			}else{
				$priceA = 0;
				$priceB=0;
			}
		};
		$data['totalWeight'] = $totalWeight;
		$data['preOrderFee'] = $v['preOrderFee'];
		$data['priceA'] = $priceA;
		$data['priceB'] = $priceB;
		return $data;
	}
	
	//$uid会员ID$TotalFee总价$priceA首重$priceB续重$originalFee原价$preBjFeew强制保价
	//$logoUrl分类信息$insurancePrice=0系统保价$co优惠券数组$coupon_pmt优惠券金额$totalWeight重量
	public function getCatePrice($uid,$totalWeight=1,$TotalFee,$priceA,$priceB,$originalFee,$preBjFee,$logoUrl,$insurancePrice=0,$co=array(),$coupon_pmt=0,$expressValue=0){
		$config = model('Setting')->fetchAll2();
		$getZhe = model('Setting')->getZhe($uid,$logoUrl);
		$zhe = $getZhe['zhe'];
		$zhe2= $getZhe['zhe2'];
		$priceA = (int)$priceA;
		$priceB = (int)$priceB;
		
		if($logoUrl['is_piliang']==1){
			$logoUrl['ratio'] = $logoUrl['ratio'];
			$logoUrl['priceA_ratio'] = $logoUrl['priceA_ratio'];
			$logoUrl['priceB_ratio'] = $logoUrl['priceB_ratio'];
			$logoUrl['priceA_price'] = $logoUrl['priceA_price'];
			$logoUrl['priceB_price'] = $logoUrl['priceB_price'];
			$logoUrl['priceA_type'] = $logoUrl['priceA_type'];
			$logoUrl['priceB_type'] = $logoUrl['priceB_type'];
		}else{
			$logoUrl['ratio'] = $logoUrl['ratio'];
			$logoUrl['priceA_ratio'] = $logoUrl['priceA_ratio'];
			$logoUrl['priceB_ratio'] = $logoUrl['priceB_ratio'];
			$logoUrl['priceA_price'] = $logoUrl['priceA_price'];
			$logoUrl['priceB_price'] = $logoUrl['priceB_price'];
			$logoUrl['priceA_type'] = $logoUrl['priceA_type'];
			$logoUrl['priceB_type'] = $logoUrl['priceB_type'];
		}
		$xu = $TotalFee - $priceA;//续重总价
		$insurancePrices =0;
		if($priceA>300&&$xu>=0){
			$weight = (int)($totalWeight-1);//超重重量
			
			if($logoUrl['priceA_type'] == 0){
				$firstPrice_jia = ($priceA*$logoUrl['priceA_ratio'])/100;
				$firstPrice_jia = (int)$firstPrice_jia;
			}else{
				$firstPrice_jia = $logoUrl['priceA_price']*100;
				$firstPrice_jia = (int)$firstPrice_jia;
			}
			if($logoUrl['priceB_type'] == 0){
				if($weight){
					$addPrice_jia = (($priceB*$logoUrl['priceB_ratio'])/100)*$weight;
					$addPrice_jia = (int)$addPrice_jia;
				}else{
					$addPrice_jia = (($priceB*$logoUrl['priceB_ratio'])/100)*$weight;
					$addPrice_jia = (int)$addPrice_jia;
				}
			}else{
				$addPrice_jia = ($logoUrl['priceB_price']*100)*$weight;
				$addPrice_jia = (int)$addPrice_jia;
			}
			if($weight && $xu>=0){
				$preOrderFee = $priceA+$firstPrice_jia+$xu+$addPrice_jia+$preBjFee;//大于1KG=首重+首重加价+续重+续重加价+保价
			}else{
				$preOrderFee = $priceA+$firstPrice_jia+$preBjFee;//1KG=首重+首重加价+保价
			}
			
			$data['firstPrice'] = $priceA;//快递公司首重价格
			$data['firstPrice_jia'] = $firstPrice_jia;//后台加价首重价格
			$data['addPrice'] = $priceB;//续重价格
			$data['addPrice_jia'] = $addPrice_jia;//后台加价续重价格
			$data['preOrderFee'] = $TotalFee ? $TotalFee : $preOrderFee;//预支付金额
			$data['preBjFee'] =  $preBjFee;//保价金额
		}else{
			$firstPrice_jia = ($TotalFee*$logoUrl['ratio'])/100;
			$firstPrice_jia = (int)$firstPrice_jia;
			$preOrderFee = $TotalFee+$firstPrice_jia+$preBjFee;
			$data['firstPrice'] = 0;//快递公司首重价格
			$data['firstPrice_jia'] = $firstPrice_jia;//后台加价首重价格
			$data['addPrice'] = 0;//续重价格
			$data['addPrice_jia'] = 0;//后台加价续重价格
			$data['preOrderFee'] = $TotalFee;//预支付金额
			$data['preBjFee'] =  $preBjFee;//保价金额
		}
		$vipFeeYuan = ($preOrderFee*$zhe)/10;//用户自己的折扣
		$vipFeeYuan = (int)$vipFeeYuan;//用户自己的折扣
		$vipFeeYuan2 = ($preOrderFee*$zhe2)/10;//VIP等级折扣
		$vipFeeYuan2 = (int)$vipFeeYuan2;//VIP等级折扣
		
		$vipFeeYuan = $vipFeeYuan+$insurancePrices;//不加保费
		$vipFeeYuan2 = $vipFeeYuan2+$insurancePrices;//不加保费
		if(($vipFeeYuan-$coupon_pmt) > 0 && $vipFeeYuan >= $co['full_price']){
			$vipFeeYuan = $vipFeeYuan-$coupon_pmt;
		}
		$data['vipFeeYuan'] = $vipFeeYuan;
		$data['vipFeeYuan2'] = $vipFeeYuan2;
		$data['logoUrl'] = $logoUrl;
		$data['insurancePrice'] = $insurancePrice;
		$data['xu'] = $xu;
		$data['totalWeight'] = $totalWeight;
		$data['zhe'] = $zhe;
		$data['zhe2'] = $zhe2;
		$data['priceA'] = $priceA;
		$data['priceB'] = $priceB;
		$data['coupon_pmt'] = $coupon_pmt;//优惠金额
		if($insurancePrice && $expressValue>$vipFeeYuan){
			$data['sumMoneyYuan'] = $expressValue;//有保价支付金额
		}else{
			$data['sumMoneyYuan'] = $vipFeeYuan;//支付金额
		}
		$data['sumMoneyYuan_old'] = $data['preOrderFee'];//原始金额
		$data['sumMoneyYuan_jia'] = $vipFeeYuan-$data['preOrderFee'];//目前加价
		
		
		if($originalFee && $originalFee > $data['sumMoneyYuan']){
			$data['originalFee'] = $originalFee;
		}else{
			$data['originalFee'] = $data['sumMoneyYuan']*1.5;
		}
		
		if($zhe!=10){
			$data['vip_discount'] = round(($vipFeeYuan)/100,2);//VIP价格运费
			$data['discount'] = round($preOrderFee/100,2);//普通用户运费
			$data['yuanMoney'] = round($preOrderFee/100,2);//原价
		}else{
			$data['vip_discount'] = round(($vipFeeYuan2)/100,2);//无折扣价格运费
			$data['discount'] = round($vipFeeYuan/100,2);//普通用户运费
			$data['yuanMoney'] =round($preOrderFee/100,2);//原价
		}
		
		
		$data['original_cost'] = round(($data['originalFee'])/100,2);//原价
		
		$data['dikou2'] = $data['originalFee']-$vipFeeYuan;
		$data['dikou'] = round(($data['originalFee']-$vipFeeYuan)/100,2);
		$data['originalFee2'] = $data['originalFee'];
		$data['originalFee'] = round($data['originalFee']/100,2);
		
		return $data;
	}
	
	
	
	//获取折扣
	public function getZhe($uid,$c){
		$zhe = $zhe2 = 10;
		$u = Db::name('users')->where(array('user_id'=>$uid))->field('rank_id,vip_rank_id,money')->find();
		//等级价格
		$ecr = Db::name('express_cate_rank')->where(array('rank_id'=>$u['rank_id'],'cate_id'=>$c['cate_id']))->field('rank_id,zhe')->find();
		if($ecr){
			if((int)$ecr['zhe'] >10){
				$zhe = 10;
			}elseif((int)$ecr['zhe'] <=0){
				$zhe = 10;
			}elseif((int)$ecr['zhe'] <=8){
				$zhe = 10;
			}else{
				$zhe = $ecr['zhe'];
			}
		}else{
			$zhe = 10;
		}
		//p($uid);
		//折扣价格
		$ecr2 = Db::name('express_cate_rank')->where(array('cate_id'=>$c['cate_id']))->field('rank_id,zhe')->order('rank_id asc')->find();
		if($ecr2){
			if((int)$ecr2['zhe'] >10){
				$zhe2 = 10;
			}elseif((int)$ecr2['zhe'] <=0){
				$zhe2 = 10;
			}elseif((int)$ecr2['zhe'] <=8){
				$zhe2 = 10;
			}else{
				$zhe2 = $ecr2['zhe'];
			}
		}else{
			$zhe2 = 10;
		}
		return array('zhe'=>$zhe,'zhe2'=>$zhe2);
	}
	
	
	//计算重量
	public function getCalculateWeight($long,$width,$height,$cate_name,$weight){
	
		if($long>1 && $width>1 && $height){
			$volume = $long*$width*$height;
			if($cate_name=='德邦'){
				$tem = $volume/6000;
			}else{
				$tem = $volume/6000;
			}
			$totalWeight = @ceil($tem);
			if($totalWeight > $weight){
				$totalWeight = $totalWeight;
			}else{
				$totalWeight = $weight;
			}
		}else{
			$totalWeight = $weight;
		}
		return $totalWeight;
	}

    public function performance($content,$Method ='CHECK_CHANNEL'){
        $config = model('Setting')->fetchAll2();
        $appid = trim($config['wxapp']['yy_appid']);
        $requestId = rand_string(32,3);
        list($t1,$t2) = explode(' ',microtime());
        $timeStamp = (int)((floatval($t1)+floatval($t2))*1000);
        $timeStamp = (string) $timeStamp;
        $secretKey = trim($config['wxapp']['yy_secretKey']);
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

        if($Method == 'PRINT'){
            $url = 'https://api.yunyangwl.com/api/wuliu/printService';
        }else{
            $url = trim($config['wxapp']['yy_url']);
        }
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body,320));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($curl);
        curl_close($curl);
        if($Method == 'PRINT'){
            return $result;
        }else{
            return json_decode($result, true);
        }
    }




    public function getSign($appid,$requestId,$timeStamp,$secretKey){
		$sb = $appid.$requestId.$timeStamp.$secretKey;
        return md5($sb);
	}
	
	//购买优惠券支付
	public function updateCouponOrder($order_id,$need_pay,$log_id,$user_id,$types){
		$config = model('Setting')->fetchAll2();
		//发放优惠券
		model('ExpressOrder')->sendCouponDownload($user_id,'',$order_id,$need_pay);
        return true;
    }
	
	
	
	//支付后回调
	public function updateExpressOrder($order_id,$need_pay,$log_id,$user_id,$types){
	    $this->zfDebugLog('zhongfaExecute', '众发Webhook回调地址='.rtrim($config['site']['host'],'/').url('app/api/push8'));

		$config = model('Setting')->fetchAll2();
		$addOrderCancel = (int)$config['config']['add_order_cancel'];
		
		$order = Db::name('express_order')->where(array('id'=>$order_id))->find();
		$separate = 0;
		
		if($order['moneys']){
			model('Users')->addMoneys($user_id,-$order['moneys'],'【'.$order_id.'】用户小程序订单支付抵扣',1);//减去抵扣金
		}

		//本地寄件
		if($order['is_pei']==2){
			$separate = 1;
			$updateData['id'] = $order_id;
			$updateData['log_id'] = $log_id;
			$updateData['orderStatus'] = 1;//已接单
			$update = Db::name('express_order')->update($updateData);
			
			$insertData['order_id'] = $order_id;
			$insertData['log_id'] = $log_id;
			$insertData['user_id'] = $user_id;//已接单
            $insertData['business_id'] = $order['business'];
            $insertData['community_id'] = $order['community'];
			$insertData['area_id'] = $order['area'];
			$insertData['city_id'] = $order['city'];
			$insertData['province'] = $order['province'];
			$insertData['city'] = $order['city'];
			$insertData['area'] = $order['area'];
			$insertData['need_pay'] = $need_pay;
			$insertData['orderStatus'] = 1;
			$insertData['create_time'] = time();
			Db::name('city_delivery_order')->insert($insertData);
		}
		
		
        	if($types == 1){
                //众发物流：支付成功后正式下单
                $this->zfDebugLog('updateExpressOrder', '=== 众发 type=15 支付回调下单 === order_id='.$order_id.' user_id='.$user_id.' types='.$types.' need_pay='.$need_pay.' 原deliveryId='.(isset($order['deliveryId']) ? $order['deliveryId'] : ''));
                $requestParams = iunserializer($order['requestParams']);
                if(!is_array($requestParams)){
                    $this->zfDebugLog('updateExpressOrder', '众发 requestParams 反序列化失败或为空 order_id='.$order_id);
                    $requestParams = array();
                }
                if(empty($requestParams['outOrderNo'])){
                    $requestParams['outOrderNo'] = (string)$order_id;
                }
                $zfOrderBrief = array(
                    'outOrderNo' => isset($requestParams['outOrderNo']) ? $requestParams['outOrderNo'] : '',
                    'estimateOrderNo' => isset($requestParams['estimateOrderNo']) ? $requestParams['estimateOrderNo'] : '',
                    'expressCode' => isset($requestParams['expressCode']) ? $requestParams['expressCode'] : '',
                    'productCode' => isset($requestParams['productCode']) ? $requestParams['productCode'] : '',
                    'payType' => isset($requestParams['payType']) ? $requestParams['payType'] : '',
                );
                $this->zfDebugLog('updateExpressOrder', '众发下单请求摘要 order_id='.$order_id.' '.json_encode($zfOrderBrief, JSON_UNESCAPED_UNICODE));
                $zhongfaResult = $this->zhongfaExecute($requestParams, 'order');
                $zfCode = isset($zhongfaResult['code']) ? (string)$zhongfaResult['code'] : '';
                $this->zfDebugLog('updateExpressOrder', '众发下单响应 order_id='.$order_id.' code='.$zfCode.' body='.json_encode($zhongfaResult, JSON_UNESCAPED_UNICODE));
                if($zhongfaResult && ($zfCode === '0' || $zfCode === '00')){
                    $ud['id'] = $order_id;
                    $ud['orderStatus'] = 2;//已接单
                    $ud['deliveryId'] = isset($zhongfaResult['data']['waybillNo']) ? $zhongfaResult['data']['waybillNo'] : '';
                    $ud['expressId'] = isset($zhongfaResult['data']['orderNo']) ? $zhongfaResult['data']['orderNo'] : '';
                    $ud['expressNo'] = isset($zhongfaResult['data']['orderNo']) ? $zhongfaResult['data']['orderNo'] : '';
                    $ud['orderStatusName'] = isset($zhongfaResult['data']['orderStatus']) ? $zhongfaResult['data']['orderStatus'] : '已接单';
                    $ud['sumMoneyYuan'] = $need_pay;
                    $update = Db::name('express_order')->update($ud);
                    $this->zfDebugLog('updateExpressOrder', '众发下单成功 order_id='.$order_id.' db_update='.($update !== false ? 'ok' : 'fail').' deliveryId='.$ud['deliveryId'].' expressNo='.$ud['expressNo'].' expressId='.$ud['expressId'].' orderStatus=2');
                }else{
                    $separate = 1;
                    $ud['id'] = $order_id;
                    $ud['orderStatus'] = 9;
                    $ud['message'] = isset($zhongfaResult['msg']) ? $zhongfaResult['msg'] : (isset($zhongfaResult['message']) ? $zhongfaResult['message'] : '众发下单失败');
                    $update = Db::name('express_order')->update($ud);
                    $this->zfDebugLog('updateExpressOrder', '众发下单失败 order_id='.$order_id.' db_update='.($update !== false ? 'ok' : 'fail').' code='.$zfCode.' msg='.$ud['message'].' addOrderCancel='.$addOrderCancel);
                    if($addOrderCancel){
                        $order['orderStatus'] = 9;
                        model('ExpressOrder')->cancel($order, $order['id'], $reason='众发下单失败'.$ud['message'], $cancel_money=0, $checkOrderStatus=1);
                        $this->zfDebugLog('updateExpressOrder', '众发下单失败已触发自动取消退款 order_id='.$order_id);
                    }
                    return true;
                }
            }elseif($types == 1){
			$updateData['id'] = $order_id;
			$updateData['orderStatus'] = 1;
			$updateData['sumMoneyYuan'] = $need_pay;//支付金额
			$updateData['pay_time'] = time();
			$update = Db::name('express_order')->update($updateData);
			
			
			if($order['type'] == 1){
				//易达反序列化数据库
				$requestParams = iunserializer($order['requestParams2']);
				//易达创建正式订单
				$execute = model('Setting')->execute($requestParams,$Method='SUBMIT_ORDER_V2');
				if($execute['code'] == 200){
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 1;//已接单-待取货
					$ud['deliveryId'] =$execute['data']['deliveryId'];
					$ud['expressId'] =$execute['data']['upOrderId'];
					$ud['expressNo'] =$execute['data']['orderNo'];//运单编号
					$ud['sumMoneyYuan'] = $need_pay;//支付金额
					$update = Db::name('express_order')->update($ud);
				}else{
				    $separate = 1;
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 9;//订单异常
					$ud['message'] =$execute['msg'];
					$update = Db::name('express_order')->update($ud);
					if($addOrderCancel){
						$order['orderStatus'] = 9;
						model('ExpressOrder')->cancel($order,$order['id'],$reason='下单失败'.$execute['msg'],$cancel_money=0,$checkOrderStatus=1);
					}
					return ture;
				}
			}elseif($order['type'] == 2){
				$requestParams = iunserializer($order['requestParams']);
				$performance = model('Setting')->performance($requestParams,'ADD_BILL_INTELLECT');
				if($performance['code'] == 1){
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 1;//已接单-待取货
					$ud['deliveryId'] =$performance['result']['waybill'];
					$ud['expressId'] =$performance['result']['waybill'];
					$ud['expressNo'] =$performance['result']['shopbill'];
					$ud['sumMoneyYuan'] = $need_pay;//支付金额
					$update = Db::name('express_order')->update($ud);
				}else{
				    $separate = 1;
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 9;//订单异常
					$ud['message'] =$performance['message'];
					$update = Db::name('express_order')->update($ud);
					if($addOrderCancel){
						$order['orderStatus'] = 9;
						model('ExpressOrder')->cancel($order,$order['id'],$reason='下单失败'.$performance['message'],$cancel_money=0,$checkOrderStatus=1);
					}
					return ture;
				}
			}elseif($order['type'] == 3){
				$requestParams = iunserializer($order['requestParams']);
				$kjd_post = model('JdApi')->jd_post($requestParams,$method='/ecap/v1/orders/create');
				if($kjd_post['code']==0){
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 1;//已接单-待取货
					$ud['deliveryId'] =$kjd_post['data']['waybillCode'];
					$ud['expressId'] =$kjd_post['data']['orderCode'];
					$ud['expressNo'] =$kjd_post['data']['orderCode'];
					$ud['sumMoneyYuan'] = $need_pay;//支付金额
					$update = Db::name('express_order')->update($ud);
				}else{
				    $separate = 1;
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 9;//订单异常
					$ud['message'] =$kjd_post['msg'];
					$update = Db::name('express_order')->update($ud);
					if($addOrderCancel){
						$order['orderStatus'] = 9;
						model('ExpressOrder')->cancel($order,$order['id'],$reason='下单失败'.$kjd_post['msg'],$cancel_money=0,$checkOrderStatus=1);
					}
					return ture;
				}
			}elseif($order['type'] == 4){
			    $requestParams = iunserializer($order['requestParams5']);//快递鸟反序列化数据库
				$kdnSendPost= model('Setting')->kdnSendPost($requestParams,$RequestType='1801');
				if($kdnSendPost['Success'] == true){
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 1;//快递鸟已接单-待取货
					$ud['deliveryId'] =$kdnSendPost['Order']['LogisticCode'];
					$ud['expressId'] =$kdnSendPost['Order']['KDNOrderCode'];
					$ud['expressNo'] =$kdnSendPost['Order']['OrderCode'];
					$update = Db::name('express_order')->update($ud);
				}else{
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 9;//快递鸟订单异常
					$ud['message'] =$kdnSendPost['Reason'];
					$update = Db::name('express_order')->update($ud);
					if($addOrderCancel){
						$order['orderStatus'] = 9;
						model('ExpressOrder')->cancel($order,$order['id'],$reason='下单失败'.$requestParams['Reason'],$cancel_money=0,$checkOrderStatus=1);
					}
					$separate = 1;
					return true;
				}
			}elseif($order['type'] == 5){
			    $requestParams = iunserializer($order['requestParams']);
				$batchOrder= model('KuayueApi')->batchOrder($requestParams);
				$success = $batchOrder['data']['success'][0];
				$failure = $batchOrder['data']['failure'][0];
				if($success && $success['waybillNumber']){
					$success = $batchOrder['data']['success'][0];
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 1;//已接单-待取货
					$ud['deliveryId'] =$success['waybillNumber'];
					$ud['expressId'] =$success['printWaybillNumber'];
					$ud['expressNo'] =$success['orderId'];
					$update = Db::name('express_order')->update($ud);
				}else{
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 9;//订单异常
					$ud['message'] =$failure['message'];
					$update = Db::name('express_order')->update($ud);
					if($addOrderCancel){
						$order['orderStatus'] = 9;
						model('ExpressOrder')->cancel($order,$order['id'],$reason='下单失败'.$failure['message'],$cancel_money=0,$checkOrderStatus=1);
					}
					$separate = 1;
					return true;
				}
			}elseif($order['type'] == 6){
			    $requestParams = iunserializer($order['requestParams']);
				$post = model('HangkongLinkApi')->hangkongLink_post($requestParams,$method='/triplh-api/open/luggage/v1.0/submitReserve');
				if($post['code'] == 200000){
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 1;//已接单-待取货
					$ud['deliveryId'] =$post['data']['orderCode'];
					$ud['expressId'] =$post['data']['luggageInfos'][0]['luggageCode'];//行李编号
					$ud['expressNo'] =$post['data']['luggageInfos'][0]['thirdPartyLuggageCode'];
					$update = Db::name('express_order')->update($ud);
					
				}else{
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 9;//订单异常
					$ud['message'] =$post['msg'];
					$update = Db::name('express_order')->update($ud);
					if($addOrderCancel){
						$order['orderStatus'] = 9;
						$order['log_id'] = $log_id;
						model('ExpressOrder')->cancel($order,$order['id'],$reason='下单失败'.$post['msg'],$cancel_money=0,$checkOrderStatus=1,$log_id);
					}
					$separate = 1;
					return true;
				}
			}elseif($order['type'] == 7){
			    $requestParams = iunserializer($order['requestParams6']);
				$ulifego = model('UlifegoApi')->ulifego_post($requestParams,$method='/openApi/doOrder');
				if($ulifego['code'] == "0"){
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 1;//已接单-待取货
					$ud['deliveryId'] =$ulifego['data']['waybillNo'];
					$ud['expressId'] =$ulifego['data']['orderNo'];
					$update = Db::name('express_order')->update($ud);
				}else{
					$ud['id'] = $order_id;
					$ud['orderStatus'] = 9;//订单异常
					$ud['message'] =$ulifego['msg'];
					$update = Db::name('express_order')->update($ud);
					if($addOrderCancel){
						$order['orderStatus'] = 9;
						$order['log_id'] = $log_id;
						model('ExpressOrder')->cancel($order,$order['id'],$reason='下单失败'.$ulifego['msg'],$cancel_money=0,$checkOrderStatus=1,$log_id);
					}
					$separate = 1;
					return true;
				}
			}elseif($order['type'] == 9){
                $requestParams = iunserializer($order['requestParams']);
                $performance = model('YtApi')->performance($requestParams,'ADD_BILL_INTELLECT');
                if($performance['code'] == 1){
                    $ud['id'] = $order_id;
                    $ud['orderStatus'] = 1;
                    $ud['deliveryId'] =$performance['result']['waybill'];
                    $ud['expressId'] =$performance['result']['waybill'];
                    $ud['expressNo'] =$performance['result']['shopbill'];
                    $update = Db::name('express_order')->update($ud);
                }else{
                    $ud['id'] = $order_id;
                    $ud['orderStatus'] = 9;
                    $ud['message'] =$performance['message'];
                    $update = Db::name('express_order')->update($ud);
                    if($addOrderCancel){
                        $order['orderStatus'] = 9;
                        $order['log_id'] = $log_id;
                        model('ExpressOrder')->cancel($order,$order['id'],$reason='下单失败'.$performance['message'],$cancel_money=0,$checkOrderStatus=1,$log_id);
                    }
                    $separate = 1;
                    return true;
                }
            }elseif($order['type'] == 11){
                if($order['orderType']==0){
                    $content['waybill']=$order['expressId'];//云洋同城订单号
                    $content['third_logistics_id']=$order['expressNo'];//运力ID
                    $performance = model('City')->performance($content,$Method ='ADD_BILL');//云洋同城下单
                    if($performance['code'] == 1){
                        $ud['id'] = $order_id;
                        $ud['orderStatus'] = 1;//云洋同城已接单-待取货
                        $ud['deliveryId'] =$performance['result']['waybill'];//运单号
                        $ud['expressId'] =$performance['result']['shopbill'];//商家单号
                        $update = Db::name('express_order')->update($ud);
                    }else{
                        $ud['id'] = $order_id;
                        $ud['orderStatus'] = 9;//云洋同城订单异常
                        $ud['message'] =$performance['message'];
                        $update = Db::name('express_order')->update($ud);
                        if($addOrderCancel){
                            $order['orderStatus'] = 9;
                            $order['log_id'] = $log_id;
                            model('ExpressOrder')->cancel($order,$order['id'],$reason='【同城】下单失败'.$performance['message'],$cancel_money=0,$checkOrderStatus=1,$log_id);
                        }
                        $separate = 1;
                        return true;
                    }
                }
            }
		}else{
			//更新差价支付
			$updateData['id'] = $order_id;
			$updateData['diffStatus'] = 2;
			$updateData['diffMoneyYuan'] = $need_pay;//差价金额
			$update = Db::name('express_order')->update($updateData);
			$separate = 1;
		}
		
		
		if($order['coupon_download_id']){
			//让优惠券失效
			Db::name('coupon_download')->where(array('download_id'=>$order['coupon_download_id']))->update(array('used_time'=>time(),'is_used'=>1));
		}
		
		//开始分销
		$profit = (int)$config['profit']['profit'];
		$moshi2 = (int)$config['profit']['moshi2'];
		//模式1城市代理+传统3级分销
		if($moshi2==0 && $profit == 1 && $separate ==0){
			model('ExpressOrder')->profit($order,$order['user_id'],'开始分销');
		}
		//模式2等级代理分成模式
		if($moshi2==1 && $profit == 1 && $separate ==0){
			model('ExpressOrder')->profit_retail($order,$order['user_id'],'开始分销');
		}
		//模式2等级代理分成模式
		if($profit == 1 && $separate ==0){
			model('ExpressOrder')->PartnerRewards($order,$order['user_id'],'合伙人奖励');//合伙人奖励
		}
		//模式3城市代理+传统3级分销+等级代理分成
		if($moshi2==2 && $profit == 1 && $separate ==0){
			model('ExpressOrder')->profit($order,$order['user_id'],'开始分销');
			model('ExpressOrder')->profit_retail($order,$order['user_id'],'开始分销');
		}
		
		//模式1城市代理+传统3级分销区县分成
		$is_area = (int)$config['profit']['is_area'];
		if($is_area == 1 && $separate ==0 && $moshi2==0){
			model('ExpressOrder')->areaRate($order,$order['user_id'],$order['senderCounty']);
		}
		if($is_area == 1 && $separate ==0 && $moshi2==2){
			model('ExpressOrder')->areaRate($order,$order['user_id'],$order['senderCounty']);
		}
		
		$is_direct_push = (int)$config['profit']['is_direct_push'];	
		if($is_direct_push==1){
			model('ExpressOrder')->directPushRate($order,$order['user_id'],'直推人分成');
		}
		
		model('ExpressOrder')->RecruitingRewards($order,$order['user_id'],'拉新奖励');//拉新奖励
		
        return true;
    }
	
	
	
	
	//执行接口
	public function execute($requestParams,$Method){
		 $config = model('Setting')->fetchAll2();
		 list($t1,$t2) = explode(' ',microtime()); 
		 $timestamp = (int)((floatval($t1)+floatval($t2))*1000);
		 $timestamp = (string) $timestamp;
		 
		 $sign_Array = array(
			  "privateKey" => $config['wxapp']['yd_secret'],
			  "timestamp"  => $timestamp,
			  "username"   => $config['wxapp']['yd_name']
			);
		 $sign  = strtoupper(MD5(json_encode($sign_Array,320)));
		 $body = array(
			 "apiMethod"        => $Method,
			 "businessParams"   => $requestParams,
			 "sign"             => $sign,
			 "timestamp"        => $timestamp,
			 "username"         => $config['wxapp']['yd_name']
		 );
		 $header = array("Content-Type:application/json");
		 $curl = curl_init();
		 curl_setopt($curl, CURLOPT_URL,$config['wxapp']['yd_url']);
         curl_setopt($curl, CURLOPT_TIMEOUT,5);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0); 
		 curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body,320));
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		 $result = curl_exec($curl);
		 curl_close($curl);
		 return json_decode($result, true);
	}
	
	//快递鸟支持接口
	public function kdnSendPost($requestParams,$RequestType,$url=''){
		$config = model('Setting')->fetchAll2();
		$kdn_EBusinessID= trim($config['wxapp']['kdn_EBusinessID']);
		$ApiKey = trim($config['wxapp']['kdn_ApiKey']);
		
		$kdn_test = (int)($config['wxapp']['kdn_test']);
		if($kdn_test==0){
			$url = 'https://api.kdniao.com/api/OOrderService';
		}else{
			$url = 'http://183.62.170.46:8081/api/dist';
		}
		
		$requestParams = json_encode($requestParams,320);
		$datas = array(
			'EBusinessID' => $kdn_EBusinessID,
			'RequestType' => $RequestType,
			'RequestData' => urlencode($requestParams),
			'DataType' => '2',
		);
		$datas['DataSign'] = urlencode(base64_encode(md5($requestParams.$ApiKey)));
		$postdata = http_build_query($datas);
		$options = array(
		  'http' => array(
			'method' => 'POST',
			'header' => 'Content-type:application/x-www-form-urlencoded',
			'content' => $postdata,
			'timeout' => 15*60
		  )
		);
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return json_decode($result,true);
	}
	
}