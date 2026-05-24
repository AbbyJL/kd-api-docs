<?php
namespace app\common\model;

use think\Db;
use think\Model;
use think\Cache;


class UlifegoApi extends Base{
 	protected $pk = 'cate_id';
    protected $tableName = 'express_cate';
    protected $token = 'jin_express_cate';
    protected $settings = null;
	

	
	public function getError(){
        return $this->error;
    }

	public function dewus(){
		return array(
			'0' => array('sender_id' => '10','sender_province'=>'上海市','sender_name' => '龙盘路888号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'1' => array('sender_id' => '11','sender_province'=>'上海市','sender_name' => '茭白园路100号(上海仓)', 'sender_address' => '上海市上海市杨浦区江浦路街道'),
			'2' => array('sender_id' => '12','sender_province'=>'上海市','sender_name' => '彭封路333号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'3' => array('sender_id' => '13','sender_province'=>'上海市','sender_name' => '沈石路777号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'4' => array('sender_id' => '14','sender_province'=>'上海市','sender_name' => '龙盘路999号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'5' => array('sender_id' => '15','sender_province'=>'上海市','sender_name' => '广灵二路317号(上海仓)', 'sender_address' => '上海市上海市虹口区凉城新村街道'),
			'6' => array('sender_id' => '16','sender_province'=>'广东省','sender_name' => '山门大道700号(广州仓)', 'sender_address' => '广东省广州市番禺区化龙镇'),
			'7' => array('sender_id' => '17','sender_province'=>'上海市','sender_name' => '沈石路1000号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'8' => array('sender_id' => '18','sender_province'=>'湖北省','sender_name' => '临空北路100号(武汉仓)', 'sender_address' => '湖北省武汉市黄陂区横店街道'),
			'9' => array('sender_id' => '19','sender_province'=>'上海市','sender_name' => '沈石路800号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'10' => array('sender_id' => '20','sender_province'=>'上海市','sender_name' => '丰登路7777号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'11' => array('sender_id' => '34','sender_province'=>'四川省','sender_name' => '南六路1号(成都仓)', 'sender_address' => '四川省成都市龙泉驿区柏合镇'),
			'12' => array('sender_id' => '90','sender_province'=>'上海市','sender_name' => '银丽路800号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'13' => array('sender_id' => '119','sender_province'=>'上海市','sender_name' => '茭白园路200号(上海仓)', 'sender_address' => '上海市上海市杨浦区江浦路街道'),
			'14' => array('sender_id' => '157','sender_province'=>'河北省','sender_name' => '富文道888号(廊坊仓)', 'sender_address' => '河北省廊坊市安次区北史家务乡'),
			'15' => array('sender_id' => '895','sender_province'=>'上海市','sender_name' => '银丽路900号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'16' => array('sender_id' => '993','sender_province'=>'广东省','sender_name' => '宾月大街1999号(广州仓)	', 'sender_address' => '广东省广州市番禺区化龙镇'),
			'17' => array('sender_id' => '1333','sender_province'=>'上海市','sender_name' => '丰登路8888号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'18' => array('sender_id' => '1334','sender_province'=>'湖北省','sender_name' => '临空西路110号(武汉仓)', 'sender_address' => '湖北省武汉市黄陂区横店街道'),
			'19' => array('sender_id' => '1335','sender_province'=>'上海市','sender_name' => '汇方路1000号(上海仓)', 'sender_address' => '上海市上海市嘉定区外岗镇'),
			'20' => array('sender_id' => '1336','sender_province'=>'上海市','sender_name' => '昌盛路100(95分上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'21' => array('sender_id' => '1337','sender_province'=>'上海市','sender_name' => '龙盘路2000(95分上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'22' => array('sender_id' => '1338','sender_province'=>'广东省','sender_name' => '世门路1000(95分广州仓)', 'sender_address' => '广东省广州市番禺区化龙镇'),
			'23' => array('sender_id' => '1452','sender_province'=>'上海市','sender_name' => '沈石路2000号(上海仓)', 'sender_address' => '上海市上海市嘉定区马陆镇'),
			'24' => array('sender_id' => '1472','sender_province'=>'湖北省','sender_name' => '昌盛路222号(上海仓)', 'sender_address' => '湖北省武汉市黄陂区横店街道'),
			'25' => array('sender_id' => '1473','sender_province'=>'陕西省','sender_name' => '窑村8888号(西安仓)', 'sender_address' => '陕西省西安市临潼区斜口街道'),
			'26' => array('sender_id' => '1475','sender_province'=>'河北省','sender_name' => '富文道2000号(廊坊仓)', 'sender_address' => '河北省廊坊市安次区北史家务乡'),
			'27' => array('sender_id' => '1749','sender_province'=>'广东省','sender_name' => '宾月大街800号(广州仓)', 'sender_address' => '广东省广州市番禺区化龙镇'),
			'28' => array('sender_id' => '1757','sender_province'=>'湖北省','sender_name' => '飞虹街8888号(武汉仓)', 'sender_address' => '湖北省武汉市黄陂区横店街道'),
			'29' => array('sender_id' => '1758','sender_province'=>'广东省','sender_name' => '世门路2000号(广州仓)', 'sender_address' => '广东省广州市番禺区化龙镇'),
			'30' => array('sender_id' => '1768','sender_province'=>'广东省','sender_name' => '南六路1000号(成都仓)', 'sender_address' => '四川省成都市龙泉驿区柏合镇'),
			'31' => array('sender_id' => '2598','sender_province'=>'四川省','sender_name' => '广兴路9999号(广州仓)', 'sender_address' => '广东省广州市南沙区南沙街道'),
			'32' => array('sender_id' => '3356','sender_province'=>'湖北省','sender_name' => '横天公路999号(武汉仓)', 'sender_address' => '湖北省武汉市黄陂区横店街道'),
			'33' => array('sender_id' => '3357','sender_province'=>'陕西省','sender_name' => '汇泉路888号(西安仓)', 'sender_address' => '陕西省西安市临潼区现代物流区斜口街道'),
		);
    }
	
	
	public function productCodes(){
		return array(
			'1' => array('name' => '申通快递','productCode' => '1', 'expressType' => 1, 'type' => 5, 'deliveryType' => '', 'promiseTimeType' => 1,'ioc' => 'icon_st.png'),
			'2' => array('name' => '圆通快递','productCode' => '2', 'expressType' => 1, 'type' => 6, 'deliveryType' => '', 'promiseTimeType' => 1,'ioc' => 'yt.png'),
			'3' => array('name' => '德邦快递','productCode' => '3', 'expressType' => 1, 'type' => 2, 'deliveryType' => '', 'promiseTimeType' => 101,'ioc' => 'db.png'),
			'4' => array('name' => '德邦时效件','productCode' => '4', 'expressType' => 1, 'type' => 3, 'deliveryType' => '', 'promiseTimeType' => 108,'ioc' => 'db.png'),
			'5' => array('name' => '顺丰(标快)','productCode' => '5', 'expressType' => 1, 'type' => 8, 'deliveryType' => '', 'promiseTimeType' => 1,'ioc' => 'sf.png'),
			'6' => array('name' => '顺丰(特快)','productCode' => '6', 'expressType' => 1, 'type' => 8, 'deliveryType' => '', 'promiseTimeType' => 2,'ioc' => 'sf.png'),
			'7' => array('name' => '顺丰(时效标快)','productCode' => '7', 'expressType' => 1, 'type' => 8, 'deliveryType' => '', 'promiseTimeType' => 7,'ioc' => 'sf.png'),
			'8' => array('name' => '顺丰(时效特快)','productCode' => '8', 'expressType' => 1, 'type' => 5, 'deliveryType' => '', 'promiseTimeType' => '8','ioc' => 'sf.png'),
			'9' => array('name' => '德邦航空','productCode' => '9', 'expressType' => 1, 'type' => 7, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'db.png'),
			'10' => array('name' => '极兔速递','productCode' => '10', 'expressType' => 1, 'type' => 13, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'jt.png'),
			'11' => array('name' => '中通快递','productCode' => '11', 'expressType' => 1, 'type' => 12, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'zt.png'),
			'12' => array('name' => '韵达快递','productCode' => '12', 'expressType' => 1, 'type' => 14, 'deliveryType' => '', 'promiseTimeType' => 1,'ioc' => 'yd.png'),
			'13' => array('name' => '京东快递','productCode' => '13', 'expressType' => 1, 'type' => 1, 'deliveryType' => '', 'promiseTimeType' => 1,'ioc' => 'jd.png'),
			'14' => array('name' => '申通时效件','productCode' => '18', 'expressType' => 1, 'type' => 5, 'deliveryType' => '', 'promiseTimeType' => 2,'ioc' => 'icon_st.png'),
			'15' => array('name' => '圆通时效件','productCode' => '19', 'expressType' => 1, 'type' => 6, 'deliveryType' => '', 'promiseTimeType' => 2,'ioc' => 'yt.png'),
			'16' => array('name' => '菜鸟速递','productCode' => '58', 'expressType' => 1, 'type' => 20, 'deliveryType' => '', 'promiseTimeType' => 1,'ioc' => 'cn.png'),
			'17' => array('name' => '菜鸟裹裹','productCode' => '36', 'expressType' => 1, 'type' => 18, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'db.png'),
			'18' => array('name' => '德邦物流(汽运)','productCode' => '20', 'expressType' => 2, 'type' => 4, 'deliveryType' => 1, 'promiseTimeType' => '','ioc' => 'db.png'),
			'19' => array('name' => '德邦重包','productCode' => '22', 'expressType' => 3, 'type' => 4, 'deliveryType' => '', 'promiseTimeType' => 103,'ioc' => 'icon_st.png'),
			'20' => array('name' => '顺心捷达','productCode' => '23', 'expressType' => 2, 'type' => 15, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'sf.png'),
			'21' => array('name' => '顺丰快运','productCode' => '25', 'expressType' => 2, 'type' => 16, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'jieda.png'),
			'22' => array('name' => '京东物流(重货)','productCode' => '25', 'expressType' => 2, 'type' => 3, 'deliveryType' => 25, 'promiseTimeType' => 100,'ioc' => 'jd.png'),
			'23' => array('name' => '京东物流(特担)','productCode' => '26', 'expressType' => 2, 'type' => 3, 'deliveryType' => 6, 'promiseTimeType' => 100,'ioc' => 'jd.png'),
			'24' => array('name' => '中通快运','productCode' => '39', 'expressType' => 2, 'type' => 17, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'zt.png'),
			'25' => array('name' => '百世快运','productCode' => '40', 'expressType' => 2, 'type' => 19, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'baishi.png'),
			'26' => array('name' => '跨越陆运','productCode' => '42', 'expressType' => 2, 'type' => 11, 'deliveryType' => '', 'promiseTimeType' => 1,'ioc' => 'ky.png'),
			'27' => array('name' => '跨越专运','productCode' => '43', 'expressType' => 2, 'type' => 11, 'deliveryType' => 220, 'promiseTimeType' => 1,'ioc' => 'ky.png'),
			'28' => array('name' => '京东得物','productCode' => '28', 'expressType' => 9, 'type' => 9, 'deliveryType' => '', 'promiseTimeType' => 1,'ioc' => 'jd.png'),
			'29' => array('name' => '德邦得物','productCode' => '29', 'expressType' => 3, 'type' => 2, 'deliveryType' => '', 'promiseTimeType' => 102,'ioc' => 'db.png'),
			'30' => array('name' => '顺丰得物','productCode' => '30', 'expressType' => 3, 'type' => 8, 'deliveryType' => '', 'promiseTimeType' => 3,'ioc' => 'sf.png'),
			'31' => array('name' => '京东物流(特担)','productCode' => '31', 'expressType' => 3, 'type' => 3, 'deliveryType' => 6, 'promiseTimeType' => '','ioc' => 'jd.png'),
			'32' => array('name' => '京东物流(重货)','productCode' => '32', 'expressType' => 3, 'type' => 3, 'deliveryType' => 25, 'promiseTimeType' => '','ioc' => 'jd.png'),
			'33' => array('name' => '德邦物流(汽运)','productCode' => '33', 'expressType' => 3, 'type' => 4, 'deliveryType' => 1, 'promiseTimeType' => '','ioc' => 'db.png'),
			'34' => array('name' => '德邦物流(卡航)','productCode' => '34', 'expressType' => 3, 'type' => 4, 'deliveryType' => 3, 'promiseTimeType' => '','ioc' => 'db.png'),
			'35' => array('name' => '顺丰快运','productCode' => '35', 'expressType' => 3, 'type' => 3, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'sf.png'),
			'36' => array('name' => 'EMS特快','productCode' => '47', 'expressType' => 1, 'type' => 3, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'ems.png'),
			'37' => array('name' => '壹米滴答','productCode' => '48', 'expressType' => 3, 'type' => 3, 'deliveryType' => '', 'promiseTimeType' => '','ioc' => 'ems.png'),
			'38' => array('name' => '德邦大件(卡航)','productCode' => '21', 'expressType' => 2, 'type' => 4, 'deliveryType' => 1, 'promiseTimeType' => '','ioc' => 'db.png'),
		);
    }
	
	
	public function pushTypes(){
		return array(
			'1' => array('name' => '京东快递','productCode' => '1', 'productName' => '特惠送'),
			'2' => array('name' => '京东快递','productCode' => '2', 'productName' => '特快送'),
			'3' => array('name' => '京东快递','productCode' => '16', 'productName' => '生鲜特快'),
			'4' => array('name' => '京东快递','productCode' => '17', 'productName' => '生鲜特惠'),
			'5' => array('name' => '京东快递','productCode' => '20', 'productName' => '京东文件'),
			'6' => array('name' => '京东快递','productCode' => '5', 'productName' => '特惠重货'),
			'7' => array('name' => '京东物流','productCode' => '6', 'productName' => '特快零担'),
			'8' => array('name' => '京东物流','productCode' => '25', 'productName' => '特快重货'),
			'9' => array('name' => '京东物流','productCode' => '1', 'productName' => '特惠送'),
			'10' => array('name' => '德邦快递','productCode' => 'PACKAGE', 'productName' => '标准快递'),
			'11' => array('name' => '德邦快递','productCode' => 'RCP', 'productName' => '大件快递'),
			'12' => array('name' => '德邦快递','productCode' => 'TZKJC', 'productName' => '特快专递'),
			'13' => array('name' => '德邦物流','productCode' => 'NZBRH', 'productName' => '重包入户'),
			'14' => array('name' => '德邦物流','productCode' => 'HK_JZKY', 'productName' => '精准空运'),
			'15' => array('name' => '德邦物流','productCode' => 'QC_JZKH', 'productName' => '精准卡航'),
			'16' => array('name' => '德邦物流','productCode' => 'QC_JZQYC', 'productName' => '精准汽运'),
			'17' => array('name' => '跨越速运','productCode' => '10', 'productName' => '省外当天达'),
			'18' => array('name' => '跨越速运','productCode' => '20', 'productName' => '省外次日达'),
			'19' => array('name' => '跨越速运','productCode' => '30', 'productName' => '省外隔日达'),
			'20' => array('name' => '跨越速运','productCode' => '40', 'productName' => '省外陆运件'),
			'21' => array('name' => '跨越速运','productCode' => '50', 'productName' => '同城次日'),
			'22' => array('name' => '跨越速运流','productCode' => '160', 'productName' => '省内次日'),
			'23' => array('name' => '跨越速运','productCode' => '220', 'productName' => '专运'),
			'25' => array('name' => '顺心捷达','productCode' => '1', 'productName' => '顺心包裹'),
			'26' => array('name' => '顺心捷达','productCode' => '2', 'productName' => '顺心零担'),
		);
    }
	

	
	public function getUlifegoTypes(){
        return array(
			'1' => '京东',
			'2' => '德邦',
			'3' => '京东物流',
			'4' => '德邦物流',
			'5' => '申通',
			'6' => '圆通',
			'7' => '德邦航空',
			'8' => '顺丰',
			'9' => '京东得物',
			'10' => '京东商家',
			'11' => '跨越速运',
			'12' => '中通',
			'13' => '极兔',
			'14' => '韵达',
			'15' => '顺心捷达',
			'16' => '顺丰快运',
			'17' => '中通快运',
			'18' => '菜鸟裹裹',
			'19' => '百世快运',
			'20' => 'EMS',
			'21' => '壹米滴答'
		);
    }

	
	//获取type
	public function getExpressCateType($v){
		$getUlifegoTypes = model('UlifegoApi')->getUlifegoTypes();
		$type = @array_search($v['cate_name'],$getUlifegoTypes);
		if($type==false){
			$cate_name = cut_msubstr($v['cate_name'],0,2);
			$type = @array_search($cate_name,$getUlifegoTypes);
		}
		return $type;
	}
	
	public function getExpressList8($data){
		$cate_id = (int)$data['cate_id'];
		
		if($data['type'] == '3'){
			$expressType = '2';
		}elseif($data['type'] == '1'){
			$expressType = '1';
		}elseif($data['type'] == '2'){
			$expressType = '3';
		}else{
			$expressType = '1';
		}
		if($data['totalWeight']>=30){
		   $expressType = '2';
		}
		
		$content['productCode'] = (int)1;
		$content['sendPhone'] = $data['sender_phone'];
		$content['sendAddress'] = $data['sender_address'];
		$content['receiveAddress'] = $data['recipients_address'];
		$content['weight'] = (int)$data['totalWeight'];
		$content['packageNum'] = (int)1;
		$content['goodsValue'] = (int)0;
		$content['length'] = $data['long'] ? $data['long'] :1;
		$content['width'] = $data['width'] ? $data['width'] : 1;
		$content['height'] =$data['height'] ? $data['height'] : 1;
		$content['payMethod'] = (int)$data['payMethod'];
		$content['expressType'] = $expressType;
		
		
		$ulifego = model('UlifegoApi')->ulifego_post($content,$method='/openApi/getPriceList');
		$list = @array_values($ulifego['data']);

		foreach($list as $k=>$v){
			$c = Db::name('express_cate')->where(array('pinyin'=>$v['productCode'],'type'=>7,'firstPrice'=>0))->find();
			if($c){
				$expressList[$k]['freightInsured'] = $v['guarantFee'];//保价费
				$expressList[$k]['c_type'] =$c['type'];
				$expressList[$k]['lanshou'] =$c['lanshou'];
				$expressList[$k]['info'] =$c['info'];
				$expressList[$k]['orderby'] =$c['orderby'];
				$expressList[$k]['firstPrice'] =(int)$c['firstPrice'];
				$expressList[$k]['img'] =config_weixin_img($c['photo']);
				$expressList[$k]['nickname'] = cut_msubstr($v['typeName'],0,20,true);
				$expressList[$k]['name'] = $v['typeName'];
				$expressList[$k]['title'] = '';
				$expressList[$k]['freight'] = $v['channelFee'];
				$expressList[$k]['channelId'] = $v['productCode'];
				$expressList[$k]['channel'] = $v['productCode'];
				$expressList[$k]['transportType'] = $v['productCode'];
				$expressList[$k]['type'] = 7;
				$expressList[$k]['tag']= $c['tag'] ? $c['tag'] : '未定义';
				
				
				$getCatePrice = model('Setting')->getCatePrice($data['uid'],$data['totalWeight'],$v['channelFee']*100,$v['priceA']*100,$v['priceB']*100,$v['originalFee']*100,0,$c);//没有加价
			
				
				$expressList[$k]['getCatePrice'] = $getCatePrice;
				$expressList[$k]['discount'] =$getCatePrice['discount'];//普通用户运费
				$expressList[$k]['vip_discount'] = $getCatePrice['vip_discount'];
				$expressList[$k]['original_cost'] = $getCatePrice['original_cost'];
				$expressList[$k]['sumMoneyYuan'] = (int)$getCatePrice['sumMoneyYuan'];
				$expressList[$k]['yuanMoney'] = $getCatePrice['yuanMoney'];
				
				if($c['is_bao']==0){
					$expressList[$k]['is_baojia'] = 0;
				}else{
					$expressList[$k]['is_baojia'] = 1;
				}
				if($c['is_yuyue']==0){
					$expressList[$k]['is_yuyue'] = 0;
				}else{
					$expressList[$k]['is_yuyue'] = 1;
				}
				if($getCatePrice['sumMoneyYuan'] == 0){
					unset($expressList[$k]);
				}
				if($cate_id && $c['cate_id'] != $cate_id){
					unset($expressList[$k]);
				}
				if($c['firstPrice']==1){
					unset($expressList[$k]);
				}
				
			}
		}
		$expressList =@array_values($expressList);
		return $expressList;
	}
	
	
	//q必达签名
	public function getUlifegoSign($appid,$version,$timeStamp,$secretKey){
		$sb = $appid.$version.$timeStamp.$secretKey;
        return md5($sb);
	}
	
	
	public function sendPost($headers,$url, $datas){
		$postdata = http_build_query($datas);
		$postdata = json_encode($datas,320);
		$options = array(
		  'http' => array(
			'method' => 'POST',
			'header' => $headers,
			'content' => $postdata,
			'timeout' => 15*60
		  )
		);
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return json_decode($result,true);
	}
	
	
	//q必达执行接口
	public function ulifego_post($content,$method){
		$config = model('Setting')->fetchAll2();
		$appid = trim($config['wxapp']['ulifego_appid']);
		$version = 'V1.0';
		
		$url = $config['wxapp']['ulifego_url'] ? $config['wxapp']['ulifego_url'] : 'http://open.szscsmgyl.com';
		$url = trim($url);
		$url = $url.$method;
	
		list($t1,$t2) = explode(' ',microtime()); 
	    $timeStamp = (int)((floatval($t1)+floatval($t2))*1000);
		$timeStamp = (string) $timeStamp;
		$secretKey = trim($config['wxapp']['ulifego_secretKey']);
		$header = array(
			"sign"=> $this->getUlifegoSign($appid,$version,$timeStamp,$secretKey),
			"timestamp" => $timeStamp,
			"version"=> $version,
			"appid" => $appid,
		);
		$jsonStr = json_encode($content,320);
		$headers = array(
			"Content-Type: application/json",
			"Content-Length: " . strlen($jsonStr) . "",
			"Accept: application/json",
			"sign:".$this->getUlifegoSign($appid,$version,$timeStamp,$secretKey)."",
			"timestamp:".$timeStamp."",
			"version:".$version."",
			"appid:".$appid.""
		);
		return $this->sendPost($headers,$url,$content);
	}
	
	
	
}