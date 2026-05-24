<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Exps extends Base{



	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->host = $this->config['site']['host'];
		$this->curl = new \Curl();
    }
	
	
	//PHP获取http请求的头信息
	public function getallheaders(){ 
       foreach($_SERVER as $name =>$value){ 
           if(substr($name,0,5) == 'HTTP_'){ 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
	
	
	public function checkAuth(){
		$platform = platform();
		if($platform != 'wxapp' && $platform != 'mpapp'){
			echo('已记录您IP并自动报警2');die;
		}
		return true;
	}
	
	
	public function getUserId(){
		$getallheaders = $this->getallheaders();
		$Token = $getallheaders['Token'];
		$user_id = Db::name('users')->where(array('token'=>$Token))->value('user_id');
		$user_id = (int)$user_id;
		return (int)$user_id;
	}
	public function oneInfo(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$v = Db::name('users')->where(array('user_id'=>$uid,'closed'=>0))->find();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>array()));
	}
	
	//获取模板消息
	public function findSubscribeList(){
		$data[0] = Db::name('weixin_tmpl')->where(array('title'=>'接单成功提醒'))->value('template_id');
		$data[1] = Db::name('weixin_tmpl')->where(array('title'=>'补差价通知'))->value('template_id');
		$data[2] = Db::name('weixin_tmpl')->where(array('title'=>'签收成功通知'))->value('template_id');
		if($data[0]){
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}else{
			$data = array();
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}
	}

	//获取分类
	public function thingType(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$data[0] = '文件';
		$data[1] = '日用品';
		$data[2] = '化妆品';
		$data[3] = '图书';
		$data[4] = '服装鞋帽';
		$data[5] = '箱包';
		$data[6] = '其他';
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	//快递鸟获取数据封装
	public function getKdnList($uid,$sendPost,$totalWeight,$addr_id=0){
		$expressList4 = array();
		$requestParams= model('Setting')->kdnSendPost($sendPost,$RequestType='1815');
		if($requestParams['Success'] == false){
			$expressList4 = array();
		}else{
			$i = 0;
			foreach($requestParams['Data'] as $key=>$val){
				$i++;
				if($val['shipperCode'] == 'JTSD'){
					$cate_name = '极兔';
				}
				if($val['shipperCode'] == 'SF'){
					$cate_name = '顺丰';
				}
				if($val['shipperCode'] == 'STO'){
					$cate_name = '申通';
				}
				if($val['shipperCode'] == 'YD'){
					$cate_name = '韵达';
				}
				if($val['shipperCode'] == 'YTO'){
					$cate_name = '圆通';
				}
				if($val['shipperCode'] == 'JDKY'){
					$cate_name = '京东';
				}
				if($val['shipperCode'] == 'JD'){
					$cate_name = '京东';
				}
				if($val['shipperCode'] == 'DBL'){
					$cate_name = '德邦';
				}
				if($val['shipperCode'] == 'ZTO'){
					$cate_name = '中通';
				}
				if($val['shipperCode'] == 'ZTO'){
					$cate_name = '中通';
				}
				if($val['shipperCode'] == 'EMS'){
					$cate_name = 'EMS';
				}
				if($val['shipperCode'] == 'KYSY'){
					$cate_name = '跨域';
				}
				$c = Db::name('express_cates')->where(array('charging'=>1,'cate_name'=>$cate_name))->find();
				if($c){
					$expressList4[$key]['freightInsured'] = 0;//保价费
					$expressList4[$key]['c_type'] =$c['type'];
					$expressList4[$key]['cate_id'] =$c['cate_id'];
					$expressList4[$key]['lanshou'] =$c['lanshou'];
					$expressList4[$key]['info'] =$c['info'];
					$expressList4[$key]['orderby'] =$c['orderby'];
					$expressList4[$key]['img'] =config_weixin_img($c['photo']);
					$expressList4[$key]['nickname'] = cut_msubstr($cate_name,0,2,true);
					$expressList4[$key]['name'] = cut_msubstr($cate_name,0,2,true);
					$expressList4[$key]['title'] = '';
					$expressList4[$key]['channelId'] = $val['shipperCode'];
					$expressList4[$key]['isBest'] = 1;
					$expressList4[$key]['preOrderFee'] = $val['totalFee'];
					$expressList4[$key]['channel'] = $val['shipperCode'];
					$expressList4[$key]['transportType'] = $val['shipperCode'];
					$c['is_piliang']=1;//是否批量寄件
					
					$continuousWeightAmount = $val['continuousWeightAmount']*100;
					$continuousWeightAmount = $continuousWeightAmount/($totalWeight-1);
					$continuousWeightAmount = (int)$continuousWeightAmount;
					
					$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$val['totalFee']*100,$val['firstWeightAmount']*100,$continuousWeightAmount,0,0,$c);
					$expressList4[$key]['discount'] = $getCatePrice['discount'];
					$expressList4[$key]['vip_discount'] = $getCatePrice['vip_discount'];
					$expressList4[$key]['original_cost'] = $getCatePrice['original_cost'];
					$expressList4[$key]['sumMoneyYuan'] = (int)$getCatePrice['sumMoneyYuan'];
					$expressList4[$key]['type'] = 5;
				}
				
			}
			$expressList4 =@array_values($expressList4);
		}
		return $expressList4;
	}
	
	//云洋获取价格
	public function getYYList($uid,$content,$totalWeight,$addr_id=0){
		$expressList = array();
		$performance = model('Setting')->performance($content,$Method ='CHECK_CHANNEL_INTELLECT');
		//p($performance);die;
		if($performance['code'] == 0){
			$expressList = array();
		}else{
			foreach($performance['result'] as $k=>$v){
				$c = Db::name('express_cates')->where(array('charging'=>1,'cate_name'=>$v['tagType']))->find();
				if($c){
					$expressList[$k]['freightInsured'] = $v['freightInsured'];//保价费
					$expressList[$k]['addr_id'] =$addr_id;
					$expressList[$k]['c_type'] =$c['type'];
					$expressList[$k]['cate_id'] =$c['cate_id'];
					$expressList[$k]['lanshou'] =$c['lanshou'];
					$expressList[$k]['info'] =$c['info'];
					$expressList[$k]['orderby'] =$c['orderby'];
					$expressList[$k]['img'] =config_weixin_img($c['photo']);
					$expressList[$k]['nickname'] = cut_msubstr($c['cate_name'],0,2,true);
					$expressList[$k]['name'] = $v['tagType'];
					$expressList[$k]['title'] = $this->config['config']['company_moshi'] == 3 ? $v['channel'] : '';
					$expressList[$k]['freight'] = $v['freight'];
					$expressList[$k]['channelId'] = $v['channelId'];
					$expressList[$k]['channel'] = $v['channelId'];
					$expressList[$k]['transportType'] = $v['channelId'];
					$expressList[$k]['type'] = 2;
					$expressList[$k]['tag']= $c['tag'] ? $c['tag'] : '未定义';
					$priceA = $v['priceOne']?$v['priceOne']:$v['price']['priceOne'];//云洋首重
					$priceB = $v['priceMore']?$v['priceMore']:$v['price']['priceMore'];//云洋续重
					$c['is_piliang']=1;//是否批量寄件
					$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$v['freight']*100,$priceA*100,$priceB*100,$v['originalPrice']*100,$v['freightInsured']*100,$c);
					$expressList[$k]['discount'] =$getCatePrice['discount'];//普通用户运费
					$expressList[$k]['vip_discount'] = $getCatePrice['vip_discount'];
					$expressList[$k]['original_cost'] = $getCatePrice['original_cost'];
					$expressList[$k]['sumMoneyYuan'] = (int)$getCatePrice['sumMoneyYuan'];
				}
			}
			$expressList=@second_array_unique_bykey($expressList,'name');
			$expressList =@array_values($expressList);
		}
		return $expressList;
	}
	
	
	//快递100获取价格
	public function getKd100List($uid,$content,$totalWeight,$addr_id=0){
		$cates = Db::name('express_cates')->where(array('charging'=>1))->order('cate_id asc')->select();
		foreach($cates as $k=>$v){
			$getKuaidicom = model('Kuaidi100Api')->getKuaidicom($v);
			$content['kuaidiCom'] = $getKuaidicom['kuaidicom'];
			$kuaidi100_post = model('Kuaidi100Api')->kuaidi100_post($content,$method='price');
			if($kuaidi100_post['returnCode']=='200'){
				$list[$k]= $kuaidi100_post['data'];
				$list[$k]['cate']= $v;
			}
			$cates[$k] =$list;
		}
		$list = @array_values($list);
		foreach($list as $k=>$v){
			$c = Db::name('express_cates')->where(array('cate_name'=>$v['cate']['cate_name'],'charging'=>1))->find();
			if($c){
				//p($v);
				$expressList[$k]['freightInsured'] = $v['guarantFee'];
				$expressList[$k]['c_type'] =$c['type'];
				$expressList[$k]['cate_id'] =$c['cate_id'];
				$expressList[$k]['lanshou'] =$c['lanshou'];
				$expressList[$k]['info'] =$c['info'];
				$expressList[$k]['orderby'] =$c['orderby'];
				$expressList[$k]['firstPrice'] =(int)$c['firstPrice'];
				$expressList[$k]['img'] =config_weixin_img($c['photo']);
				$expressList[$k]['nickname'] = cut_msubstr($c['cate_name'],0,2,true);
				$expressList[$k]['name'] = $c['cate_name'];
				$expressList[$k]['title'] = '';
				$expressList[$k]['freight'] = $v['channelFee'];
				$expressList[$k]['channelId'] = $v['kuaidiCom'];
				$expressList[$k]['channel'] = $v['kuaidiCom'];
				$expressList[$k]['transportType'] = $v['kuaidiCom'];
				$expressList[$k]['type'] = 4;
				$expressList[$k]['tag']= $c['tag'] ? $c['tag'] : '未定义';
				$c['is_piliang']=1;//快递100是否批量寄件
				
				$overPrice = $v['overPrice']*100;
				$overPrice = $overPrice/($totalWeight-1);
				$overPrice = (int)$overPrice;
				
				$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$v['price']*100,$v['firstPrice']*100,$overPrice,$v['defPrice']*100,0,$c);//快递100没有加价
				$expressList[$k]['discount'] =$getCatePrice['discount'];//快递100普通用户运费
				$expressList[$k]['vip_discount'] = $getCatePrice['vip_discount'];
				$expressList[$k]['original_cost'] = $getCatePrice['original_cost'];
				$expressList[$k]['sumMoneyYuan'] = (int)$getCatePrice['sumMoneyYuan'];
			}
		}
		$expressList =@array_values($expressList);
		return $expressList;
	}
	
	//易达获取价格
	public function getYidaList($uid,$content,$totalWeight,$addr_id=0){
		$config = model('Setting')->fetchAll2();
		$execute = model('Setting')->execute($content,$Method='SMART_PRE_ORDER');
		if($execute['code'] != 200){
			$expressList2 = array();
		}else{
			$i = 0;
			foreach($execute['data'] as $key=>$val){
				if(is_array($val) && !empty($val)){
					foreach($val as $k=>$v){
						$i++;
						$c = Db::name('express_cates')->where(array('pinyin'=>$v['deliveryType'],'charging'=>1))->find();
						if($c){
							$expressList2[$i]['freightInsured'] = $v['preBjFee'];//保价费
							$expressList2[$i]['v'] =$v;
							$expressList2[$i]['c_type'] =$c['type'];
							$expressList2[$i]['cate_id'] =$c['cate_id'];
							$expressList2[$i]['lanshou'] =$c['lanshou'];
							$expressList2[$i]['info'] =$c['info'];
							$expressList2[$i]['orderby'] =$c['orderby'];
							$expressList2[$i]['firstPrice'] =(int)$c['firstPrice'];
							$expressList2[$i]['img'] =config_weixin_img($c['photo']);
							$expressList2[$i]['nickname'] = cut_msubstr($c['cate_name'],0,2,true);
							$expressList2[$i]['name'] = cut_msubstr($c['cate_name'],0,2,true);
							$expressList2[$i]['title'] = $config['config']['company_moshi'] == 3 ? $v['channelName'] : '';
							$expressList2[$i]['channelId'] = $v['channelId'];
							$expressList2[$i]['isBest'] = $v['isBest'];
							$expressList2[$i]['preOrderFee'] = $v['preOrderFee'];
							$expressList2[$i]['channel'] = $v['channelId'];
							$expressList2[$i]['transportType'] = $v['channelId'];
							$getYidastartEndMoney = model('Setting')->getYidastartEndMoney($data['uid'],$v,$data['totalWeight']);//获取易达首重续重
							$priceA = $getYidastartEndMoney['priceA'];
							$priceB = $getYidastartEndMoney['priceB'];
							$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$v['preOrderFee']*100,$priceA,$priceB,$v['originalFee']*100,0,$c);
							$expressList2[$i]['priceA'] = $priceA;
							$expressList2[$i]['priceB'] = $priceB;
							$expressList2[$i]['getCatePrice'] = $getCatePrice;
							$expressList2[$i]['discount'] = $getCatePrice['discount'];
							$expressList2[$i]['vip_discount'] = $getCatePrice['vip_discount'];
							$expressList2[$i]['original_cost'] = $getCatePrice['original_cost'];
							$expressList2[$i]['sumMoneyYuan'] = (int)$getCatePrice['sumMoneyYuan'];
							$expressList2[$i]['type'] = 1;
							$expressList2[$i]['tag']= $c['tag'] ? $c['tag'] : '未定义';
							$c['is_piliang']=1;//是否批量寄件
						}
					}
				}
			}
			if($config['config']['company_moshi'] != 3){
				foreach($expressList2 as $k2=>$v2){
					if($v2['isBest'] != true){
						unset($expressList2[$k2]);
					}
				}
			}
			$expressList2 =@array_values($expressList2);
			return $expressList2;
		}
	}
	
	//获取价格
	public function getExpressPrice(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$actualWeight = input('actualWeight','','trim,htmlspecialchars');
		$fromAddressBookId = input('fromAddressBookId','','trim,htmlspecialchars');
		$toAddressBookId = input('toAddressBookId','','trim,htmlspecialchars');
		$volume = input('volume','','trim,htmlspecialchars');
		$toAddressBookId=explode(",",$toAddressBookId);
		$list =array();
		foreach($toAddressBookId as $k=>$v){
			if($v){
				$list[$k] = $v;
			}
		}
		$s = Db::name('user_addr')->where(array('addr_id'=>$fromAddressBookId))->find();
		$d2 = strstr($s['address'],$s['province']);
		if($d2 == false){
			$s_address = $s['province'].''.$s['city'].''.$s['area'].''.$s['address'];
		}else{
			$s_address = $s['address'];
		}
		if($s['phone']!='' && $s['phone']){
			$senderMobile = $s['phone'];
		}elseif($s['mobile']!='' && $s['mobile']){
			$senderMobile = $s['mobile'];
		}else{
			$senderMobile= '17194348715';
		}
		$lists = $data = array();
		foreach($list as $k=>$v){
			$r = Db::name('user_addr')->where(array('addr_id'=>$v))->find();
			$d1 = strstr($r['address'],$r['province']);
			if($d1 == false){
				$r_address = $r['province'].''.$r['city'].''.$r['area'].''.$r['address'];
			}else{
				$r_address = $r['address'];
			}
			if($r['phone']!='' && $r['phone']){
				$receiveMobile = $r['phone'];
			}elseif($r['mobile']!='' && $r['mobile']){
				$receiveMobile = $r['mobile'];
			}else{
				$receiveMobile= '17194348715';
			}
			$type = (int)$this->config['batch']['type'];
			//云洋
			if($type==1){
				$content['channelTag']="智能";
				$content['sender']=$s['sender_name'];
				$content['senderMobile']= $sender_mobile;
				$content['senderProvince']= $s['province'];
				$content['senderCity']= $s['city'];
				$content['senderCounty']= $s['area'];
				$content['senderTown']=$s['area'];
				$content['senderLocation']= $s['address'] ? $s['address'] : $s['province'].$s['city'].$s['area'];
				$content['senderAddress']= $s['address'] ? $s['address'] : $s['province'].$s['city'].$s['area'];
				$content['receiver']=$s['name'];
				$content['receiverMobile']=$recipients_mobile;
				$content['receiveProvince']= $r['province'];
				$content['receiveCity']= $r['city'];
				$content['receiveCounty']= $r['area'];
				$content['receiveTown']= $data['province'].$r['rcity'].$r['area'];;
				$content['receiveLocation']= $r['address'] ? $r['address'] : $r['province'].$r['rcity'].$r['area'];
				$content['receiveAddress']=$r['address'] ? $r['address'] : $r['province'].$r['city'].$r['area'];
				$content['weight']= (int)$actualWeight;
				$content['packageCount']= 1;
				$content['insured']= 0;
				$content['vloumLong']= 1;
				$content['vloumWidth']= 1;
				$content['vloumHeight']=1;
				$getYYList= $this->getYYList($uid,$content,$actualWeight,$v);
				$lists[$k]=$getYYList;
			}
			if($type==2){
				//快递鸟
				$sendPost['Weight'] = $actualWeight;
				$sendPost['InsureAmount'] = '';
				$sendPost['PremiumFee'] = '';
				$Receivers['ProvinceName'] = $r['province'];
				$Receivers['CityName'] =  $r['city'];
				$Receivers['ExpAreaName'] = $r['city'];
				$sendPost['Receiver'] = $Receivers;
				$Senders['ProvinceName'] = $s['province'];
				$Senders['CityName'] = $s['city'];
				$Senders['ExpAreaName'] = $s['area'];
				$sendPost['Sender'] = $Senders;
				$getKdnList= $this->getKdnList($uid,$sendPost,$actualWeight,$addr_id);
				$lists[$k]=$getKdnList;
			}
			if($type==3){
				//快递100
				$content['sendManPrintAddr'] = $s_address;
				$content['recManPrintAddr'] = $r_address;
				$content['weight'] = (string)$actualWeight;
				$content['serviceType'] = 'price';
				if($e['cate_name']=='德邦' && $actualWeight<30){
					$content['serviceType'] = '标准快递';
				}elseif($e['cate_name']=='德邦' && $actualWeight>30){
					$content['serviceType'] = '德邦大件360';
				}else{
					$content['serviceType'] = '标准快递';
				}
				$content['channelSw'] = '';
				$getKd100List= $this->getKd100List($uid,$content,$actualWeight,$addr_id);
				$lists[$k]=$getKd100List;
			}
			if($type==4){
				//易达
				$requestParams['senderAddress']=$s_address;
				$requestParams['goods']='物品';
				$requestParams['thirdNo']=rand_string(10,1,'');
				$requestParams['senderName']=$s['name'];
				$requestParams['receiveName']=$r['name'];
				$requestParams['unitPrice']=0;//易达申通情况必填
				$isMobile = isMobile($r['mobile']?$r['mobile']:$r['phone']);
				if(!$isMobile){
					$requestParams['receiveTel']=$r['mobile']?$r['mobile']:$r['phone'];
				}elseif($customerType == 'poizon'){
					$requestParams['receiveTel']=$r['mobile']?$r['mobile']:$r['phone'];
				}else{
					$requestParams['receiveMobile']=$r['mobile']?$r['mobile']:$r['phone'];
				}
				$requestParams['receiveDistrict']=$r['area'];
				$requestParams['receiveAddress']=$r['address'];
				$requestParams['senderDistrict']=$s['area'];//易达寄件区县
				$requestParams['deliveryType']='';
				$isMobile1 = isMobile($s['mobile']?$s['mobile']:$s['phone']);
				if(!$isMobile1){
					$requestParams['senderTel']=$s['mobile']?$s['mobile']:$s['phone'];
				}else{
					$requestParams['senderMobile']=$s['mobile']?$s['mobile']:$s['phone'];
				}
				$requestParams['thirdNo']= rand_string(8,1);//易达重量
				$requestParams['weight']= $actualWeight;//重量
				$requestParams['customerType']='kd';
				$requestParams['senderProvince']=$s['province'];
				$requestParams['receiveProvince']=$r['province'];
				$requestParams['senderCity']=$s['city'];//易达收件城市
				$requestParams['receiveCity']=$r['city'];
				$requestParams['qty']=1;//易达申通情况必填 数量
				$requestParams['vloumLong']=1;
				$requestParams['vloumHeight']=1;
				$requestParams['vloumWidth']= 1;
				$requestParams['packageCount']='1';
				$requestParams['receiveProvinceCode']='';
				$requestParams['senderProvinceCode']='';
				$getYidaList= $this->getYidaList($uid,$requestParams,$actualWeight,$addr_id);
				$lists[$k]=$getYidaList;
			}
		}
		
		
		if(!$lists){
			return json(array('code'=>-1,'msg'=>'获取价格失败'));
		}
		foreach($lists as $value){  
			foreach($value as $v){  
				 $arr2[]=$v;  
			}  
		}
		
		//p($arr2);die;
		$arr = $this->dataGroup($arr2,'name');
		$i=0;
		foreach($arr as $key=>$val){
			$i++;
			$arr[$key]['t']=0;
			foreach($val as $k=>$v){
				$arr[$key]['t']++;
				$arr[$key]['sumMoneyYuan'] += $v['sumMoneyYuan'];
				$arr[$key]['costOrignPrice'] += $v['original_cost'];
				$arr[$key]['discountPrice'] += $v['vip_discount'];
				if($arr[$key]['t']!=1){
					$arr[$key]['channels2'] .= ','.$v['addr_id'].'-'.$v['channel'];
				}else{
					$arr[$key]['channels2'] .= $v['addr_id'].'-'.$v['channel'];
				}
				if($arr[$key]['t']!=1){
					$arr[$key]['channels'] .= ','.$v['channel'];
				}else{
					$arr[$key]['channels'] .= $v['channel'];
				}
			}
			$data[$key]['expresChannel'] = $v['cate_id'];
			$data[$key]['channel'] = $v['channelId'];
			$data[$key]['name'] = $key;
			$data[$key]['rate'] = $v['lanshou'];
			$data[$key]['payPrice'] = round($arr[$key]['sumMoneyYuan']/100,2);
			$data[$key]['nowPirce'] = round($arr[$key]['vip_discount']/1,2);
			$data[$key]['setFlag'] = true;
			$data[$key]['costPrice'] = $arr[$key]['discountPrice'];
			$data[$key]['costOrignPrice'] = '0.00';
			$data[$key]['discountPrice'] = '0.00';
			$data[$key]['bianma'] = $arr[$key]['channels'];
			$data[$key]['v'] = $v;
		}
		$data = array_values($data);
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	public function dataGroup($dataArr=array(),$keyStr){
		$newArr=array();
		foreach ($dataArr as $k => $val){
			$newArr[$val[$keyStr]][] = $val;
		}
		return $newArr;
	}

	//下单
	public function placeOrder(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$actualWeight = input('actualWeight','','trim,htmlspecialchars');//重量
		$expressChannel = input('expressChannel','','trim,htmlspecialchars');//快递鸟快递公司类别
		$channel = input('channel','','trim,htmlspecialchars');//云洋快递公司类别
		$couponId = input('couponId','','trim,htmlspecialchars');//优惠券
		$fromAddressBookId = input('fromAddressBookId','','trim,htmlspecialchars');//寄件地址
		$insuredPrice = input('insuredPrice','','trim,htmlspecialchars');//保价
		$orderMoney = input('orderMoney','','trim,htmlspecialchars');//下单金额
		$thingType = input('thingType','','trim,htmlspecialchars');//类型
		$payType= input('payType','','trim,htmlspecialchars');//支付方式1微信支付
		$toAddressBookId = input('toAddressBookId','','trim,htmlspecialchars');//收件地址多个
		$bianma = input('bianma','','trim,htmlspecialchars');//渠道编码
		$volume = input('volume','','trim,htmlspecialchars');//体积
		$volume=explode(",",$volume);
		$long = $volume['0'];//长
		$width= $volume['1'];//宽
		$hight = $volume['2'];//高
		$totalWeight = $actualWeight;//重量
		$insurancePrice = 0;//保价
		
		$toAddressBookId=explode(",",$toAddressBookId);
		$list =array();
		foreach($toAddressBookId as $k=>$v){
			if($v){
				$list[$k] = $v;
			}
		}
		if(!$list){
			return json(array('code'=>-1,'msg'=>'地址获取失败'));
		}
		$bianma=explode(",",$bianma);
		$bianmaList =array();
		foreach($bianma as $k=>$v){
			if($v){
				$bianmaList[$k] = $v;
			}
		}
		if(!$bianmaList){
			return json(array('code'=>-1,'msg'=>'编码获取失败'));
		}
		$list = array_values($list);
		$lists=array_combine($list,$bianmaList);
		if(!$lists){
			return json(array('code'=>-1,'msg'=>'编码地址合并数据失败'));
		}
		
		$s = Db::name('user_addr')->where(array('addr_id'=>$fromAddressBookId))->find();
		if(!$s){
			return json(array('code'=>-1,'msg'=>'寄件地址不存在请重新下单'));
		}
		$d2 = strstr($s['address'],$s['province']);
		if($d2 == false){
			$s_address = $s['province'].''.$s['city'].''.$s['area'].''.$s['address'];
		}else{
			$s_address = $s['address'];
		}
		if($s['phone']!='' && $s['phone']){
			$senderMobile = $s['phone'];
		}elseif($s['mobile']!='' && $s['mobile']){
			$senderMobile = $s['mobile'];
		}else{
			$senderMobile= '17194348715';
		}
		$u = Db::name('users')->where(array('user_id'=>$uid))->find();//会员信息
		$e = Db::name('express_cates')->where(array('charging'=>1,'cate_id'=>$expressChannel))->find();//批量下单快递公司
		
		//内部单号
		$oid = Db::name('express_order')->order('id desc')->limit(0,1)->value('id');
		$thirdNo = ($oid+1).rand_string(6,1);//外部单号
		
		
		$eos2 = Db::name('express_order')->where(array('user_id'=>$uid,'diffStatus'=>1,'diffMoneyYuan'=>array('gt',0)))->order('id desc')->field('user_id,id,diffMoneyYuan,create_time,diffStatus')->find();
		if($eos2){
			return json(array('code'=>-1,'msg'=>'订单【'.$eos2['id'].'】还有差价【'.round($eos2['diffMoneyYuan']/100,2).'】元未补齐，补齐差价后下单'));
		}
		
		
		foreach($lists as $k=>$vt){
			$r = Db::name('user_addr')->where(array('addr_id'=>$k))->find();
			$d1 = strstr($r['address'],$r['province']);
			if($d1 == false){
				$r_address = $r['province'].''.$r['city'].''.$r['area'].''.$r['address'];
			}else{
				$r_address = $r['address'];
			}
			if($r['phone']!='' && $r['phone']){
				$receiveMobile = $r['phone'];
			}elseif($r['mobile']!='' && $r['mobile']){
				$receiveMobile = $r['mobile'];
			}else{
				$receiveMobile= '17194348715';
			}
			//内部单号
			$oid = Db::name('express_order')->order('id desc')->limit(0,1)->value('id');
			$thirdNo = ($oid+1).rand_string(6,1);//外部单号
			//订单数据
			
			$data['is_company'] = model('Setting')->checkUsersCompany($uid);//检测是否有企业折扣
			$data['payMethod'] = (int)3;
			$data['is_pei'] = (int)0;
			$data['orderType'] = 0;
			$data['is_piliang'] = 2;
			$data['kuaidi'] = $e['cate_id'];
			$data['cargoName'] = $thingType;//商品名称
			$data['pid'] = $u['parent_id'];
			$data['deliveryId'] = 0;//快递公司返回ID
			$data['expressId'] = 0;//快递公司ID
			$data['closed'] = 0;
			$data['expressNo'] = 0;//快递公司单号
			$data['user_id'] = $uid;
			$data['orderStatus'] = 0;//0待付款,1已付款-待接单2已接单-待取货,3已取件-配送中4已完成5已取消已退款
			$data['diffStatus'] = 0;//1补差价
			$data['orderNo'] = $thirdNo;//orderNo订单号
			$data['orderRightsStatus'] = 0;//0代取件1退款审核中2退款完成
			$data['createTime'] = time();
			$data['totalNumber'] = 1;//数量
			$data['insuranceValue'] = $insuredPrice*100;//保障金额
			$data['insurancePrice'] = $insuredPrice*100;//保费
			$data['wight'] = $totalWeight;//重量
			$data['long'] = $long;//长
			$data['width'] = $width;//宽
			$data['height'] = $height;//高
			$data['totalVolume'] =  ($long/100) * ($width/100) * ($height/100);//体积
			$data['sumMoneyYuan'] = 0;//支付金额
			$data['diffMoneyYuan'] =0;//差价金额
			$data['sendName'] = $s['name'];
			$data['sendMobile'] = $senderMobile;
			$data['senderProvince']= $s['province'];
			$data['senderCity']= $s['city'];
			$data['senderCounty']= $s['area'];
			$data['sendAddress'] = $s_address;
			$data['receiveName'] = $r['name'];
			$data['receiveMobile'] = $receiveMobile;
			$data['receiveCity'] = $r['city'];
			$data['receiveAddress'] = $r_address;
			$data['create_time'] = time();
			$data['remark'] = '批量下单';//备注
			$data['yuyuetime'] = '';
			
			
			$ts = (int)$this->config['batch']['type'];
			if($ts==2){
				$ShipperType = '2';//快递鸟类型
				if($long && $width && $height){
					$totalVolume = ($long/100) * ($width/100) * ($height/100);
					if(!$totalVolume){
						$totalVolume = null;
					}else{
						$totalVolume =number_format($totalVolume,2);
					}
				}else{
					$totalVolume = null;
				}
				if($totalVolume=='0.00'){
					$totalVolume = null;
				}
				if($e['cate_name']=='顺丰'){
					$shipperCode = 'SF';
				}
				if($e['cate_name']=='极兔'){
					$shipperCode = 'JTSD';
				}
				if($e['cate_name']=='申通'){
					$shipperCode = 'STO';
				}
				if($e['cate_name']=='韵达'){
					$shipperCode = 'YD';
				}
				if($e['cate_name']=='圆通'){
					$shipperCode = 'YTO';
				}
				if($e['cate_name']=='京东'){
					$shipperCode = 'JDKY';
				}
				if($e['cate_name']=='京东'){
					$shipperCode = 'JD';
				}
				if($e['cate_name']=='德邦'){
					$shipperCode = 'DBL';
				}
				if($e['cate_name']=='中通'){
					$shipperCode = 'ZTO';
				}
				
				$requestParams['ShipperCode'] = $shipperCode;
				$requestParams['ShipperType'] = $ShipperType;
				$requestParams['OrderCode'] = rand_string(8,0);
				$requestParams['ExpType'] = 1;
				$requestParams['PayType'] = 3;
				$Receiver['Company'] = '';
				$Receiver['Name'] = $r['name'];
				if(isMobile($receiveMobile)){
					$Receiver['Mobile'] = $receiveMobile;
				}else{
					$Receiver['TeL'] = $receiveMobile;
				}
				$Receiver['ProvinceName'] = $r['province'];
				$Receiver['CityName'] = $r['city'];
				$Receiver['ExpAreaName'] = $r['area'];
				$Receiver['Address'] =$r['province']. $r['city'].$r['area'].$r['address'];
				$requestParams['Receiver'] = $Receiver;
				$Sender['Company'] = '';
				$Sender['Name'] = $s['name'];
				if(isMobile($senderMobile)){
					$Sender['Mobile'] = $senderMobile;
				}else{
					$Sender['TeL'] = $senderMobile;
				}
				$Sender['ProvinceName'] = $s['province'];
				$Sender['CityName'] = $s['city'];
				$Sender['ExpAreaName'] = $s['area'];
				$Sender['Address'] =$s['province']. $s['city'].$s['area'].$s['address'];
				$requestParams['Sender'] = $Sender;
				$requestParams['StartDate'] = null;
				$requestParams['EndDate'] = null;
				$requestParams['Weight'] = $totalWeight;
				$requestParams['Quantity'] = 1;
				$requestParams['Volume'] = $totalVolume;
				$requestParams['Remark'] = '';
				$Commodity['GoodsName'] = $thingType;
				$Commodity['Goodsquantity'] = 1;
				$Commodity['GoodsPrice'] = '';
				$requestParams['Commodity'] = $Commodity;
				$data['requestParams5'] = iserializer($requestParams);//快递鸟保存到数据库序列化
	
				$sendPost['Weight'] = $totalWeight;
				$sendPost['InsureAmount'] = '';
				$sendPost['PremiumFee'] = '';
				$Receivers['ProvinceName'] = $r['province'];
				$Receivers['CityName'] =  $r['city'];
				$Receivers['ExpAreaName'] = $r['city'];
				$sendPost['Receiver'] = $Receivers;
				$Senders['ProvinceName'] = $s['province'];
				$Senders['CityName'] = $s['city'];
				$Senders['ExpAreaName'] = $s['area'];
				$sendPost['Sender'] = $Senders;
				
			
				$kdnSendPost= model('Setting')->kdnSendPost($sendPost,$RequestType='1815');
				foreach($kdnSendPost['Data'] as $key=>$val){
					if($shipperCode == $val['shipperCode']){
						if($val['shipperCode'] == 'JTSD'){
							$cate_name = '极兔';
						}
						if($val['shipperCode'] == 'SF'){
							$cate_name = '顺丰';
						}
						if($val['shipperCode'] == 'STO'){
							$cate_name = '申通';
						}
						if($val['shipperCode'] == 'YD'){
							$cate_name = '韵达';
						}
						if($val['shipperCode'] == 'YTO'){
							$cate_name = '圆通';
						}
						if($val['shipperCode'] == 'JDKY'){
							$cate_name = '京东';
						}
						if($val['shipperCode'] == 'JD'){
							$cate_name = '京东';
						}
						if($val['shipperCode'] == 'DBL'){
							$cate_name = '德邦';
						}
						if($val['shipperCode'] == 'ZTO'){
							$cate_name = '中通';
						}
						if($val['shipperCode'] == 'ZTO'){
							$cate_name = '中通';
						}
						if($val['shipperCode'] == 'EMS'){
							$cate_name = 'EMS';
						}
						if($val['shipperCode'] == 'KYSY'){
							$cate_name = '跨域';
						}
						//是否批量寄件
						$e['is_piliang']=1;
						
						$continuousWeightAmount = $val['continuousWeightAmount']*100;
						$continuousWeightAmount = $continuousWeightAmount/($totalWeight-1);
						$continuousWeightAmount = (int)$continuousWeightAmount;
						
						$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$val['totalFee']*100,$val['firstWeightAmount']*100,$continuousWeightAmount,0,0,$e,$insurancePrice,$co,$data['coupon_pmt']);
					}
				}
				$data['firstPrice'] = $getCatePrice['firstPrice'];
				$data['addPrice'] = $getCatePrice['addPrice'];
				$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
				$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
				$data['preOrderFee'] = $getCatePrice['preOrderFee'];
				$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
				$data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
				$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
				$data['type']=5;//快递鸟接口模式
				$need += $getCatePrice['sumMoneyYuan'];
				if($getCatePrice['sumMoneyYuan']){
					$order_ids[] = Db::name('express_order')->insertGetId($data);	
				}
			}
			if($ts==1){
				$content['sender']=$s['name'];
				$content['senderMobile']= $senderMobile;
				$content['senderProvince']= $s['province'];
				$content['senderCity']= $s['city'];
				$content['senderCounty']= $s['area'];
				$content['senderTown']=$s['area'];
				$content['senderLocation']= $s['address'];
				$content['senderAddress']= $s['address'];
				$content['receiver']=$r['name'];
				$content['receiverMobile']= $receiveMobile;
				$content['receiveProvince']= $r['province'];
				$content['receiveCity']= $r['city'];
				$content['receiveCounty']= $r['area'];
				$content['receiveTown']= $r['area'];
				$content['receiveLocation']= $r['address'];
				$content['receiveAddress']=$r['address'];
				$content['weight']= $totalWeight;
				$content['packageCount']= 1;
				$content['insured']= 0;
				$content['vloumLong']= $long;
				$content['vloumWidth']= $width;
				$content['vloumHeight']=$height;
				$content['autoMatchLevel']= 1;
				$content['channelTag']= '智能';
				$content['billType']=0;
				$content['subType']= 'wds';
				$requestParams['channelTag']='智能';
				$requestParams['sender']=$s['name'];
				$requestParams['senderMobile']= $senderMobile;
				$requestParams['senderProvince']= $s['province'];
				$requestParams['senderCity']= $s['city'];
				$requestParams['senderCounty']= $s['area'];
				$requestParams['senderTown']= $s['area'];
				$requestParams['senderLocation']= $s['address'];
				$requestParams['senderAddress']= $s_address;
				$requestParams['receiver']= $r['name'];
				$requestParams['receiverMobile']= $receiveMobile;
				$requestParams['receiveProvince']= $r['province'];
				$requestParams['receiveCity']= $r['city'];
				$requestParams['receiveCounty']= $r['area'];
				$requestParams['receiveTown']= $r['area'];
				$requestParams['receiveLocation']= $r['address'];
				$requestParams['receiveAddress']= $r_address;
				$requestParams['weight']= $totalWeight;
				$requestParams['billType']= $e['cate_id'];
				$requestParams['packageCount']= 1;
				$requestParams['itemName']= $thingType;
				$requestParams['senderCompany']= "";
				$requestParams['receiveCompany']= "";
				$requestParams['insured']= '';//保费
				$requestParams['vloumLong']= $long;
				$requestParams['vloumWidth']= $width;
				$requestParams['vloumHeight']= $height;
				$requestParams['warehouseCode']= "";
				$requestParams['channelId']= $vt;
				$h = date('H');
				$h = $h+2;
				$requestParams['pickupStartTime']= null;
				$requestParams['pickupStopTime']= null;
				$requestParams['billRemark']= $remark;
				$requestParams['collectionMoney']= 0;
				$requestParams['subType']= "wds";
				$requestParams['autoMatchLevel']= '1';
				$requestParams['modelType']="ZK";
				$data['requestParams'] = iserializer($requestParams);//云洋保存到数据库序列化
				
				$performance = model('Setting')->performance($content,$Method ='CHECK_CHANNEL_INTELLECT');
				//云洋计算单价
				if($performance['code'] == 1){
					$result = $performance['result'];
					foreach($result as $ks=>$vs){
						if($vt == $vs['channelId']){
							$v = $vs;
						}
					}
					$priceA = $v['priceOne']?$v['priceOne']:$v['price']['priceOne'];//云洋首重
					$priceB = $v['priceMore']?$v['priceMore']:$v['price']['priceMore'];//云洋续重
					$e['is_piliang']=1;
					
					
					$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$v['freight']*100,$priceA*100,$priceB*100,$v['originalPrice']*100,$v['freightInsured']*100,$e,$insurancePrice,$co,$data['coupon_pmt']);
				}
				$data['firstPrice'] = $getCatePrice['firstPrice'];
				$data['addPrice'] = $getCatePrice['addPrice'];
				$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
				$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
				$data['preOrderFee'] = $getCatePrice['preOrderFee'];
				$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
				$data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
				$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
				$data['type']=2;//云洋接口模式
				$need += $getCatePrice['sumMoneyYuan'];
				if($getCatePrice['sumMoneyYuan']){
					$order_ids[] = Db::name('express_order')->insertGetId($data);	
				}
			}
			if($ts==3){
				//快递100
				$g['cate_name'] = $e['cate_name'];
				$getKuaidicom = model('Kuaidi100Api')->getKuaidicom($g);
				$content['kuaidiCom'] = $getKuaidicom['kuaidicom'];
				$content['sendManPrintAddr'] = $s_address;
				$content['recManPrintAddr'] = $r_address;
				$content['weight'] = (string)$totalWeight;
				$content['channelSw'] = '';
				if($e['cate_name']=='德邦' && $totalWeight<30){
					$content['serviceType'] = '标准快递';
				}elseif($e['cate_name']=='德邦' && $totalWeight>30){
					$content['serviceType'] = '德邦大件360';
				}else{
					$content['serviceType'] = '标准快递';
				}
				$kuaidi100_post = model('Kuaidi100Api')->kuaidi100_post($content,$method='price');
				if($kuaidi100_post['returnCode']=='200'){
					$requestParams['kuaidicom'] = $getKuaidicom['kuaidicom'];
					$requestParams['recManName'] = $r['name'];
					$requestParams['recManMobile'] = $receiveMobile;
					$requestParams['recManPrintAddr'] = $r_address;
					$requestParams['sendManName'] = $s['name'];
					$requestParams['sendManMobile'] = $senderMobile;
					$requestParams['sendManPrintAddr'] = $s_address;
					$requestParams['callBackUrl'] = $this->config['site']['host'].'/app/api/push4';
					$requestParams['cargo'] = $thingType;
					$requestParams['weight'] = (string)$totalWeight;
					$requestParams['remark'] = '';
					if($e['cate_name']=='德邦' && $totalWeight<30){
						$requestParams['serviceType'] = '标准快递';
					}elseif($e['cate_name']=='德邦' && $totalWeight>30){
						$requestParams['serviceType'] = '德邦大件360';
					}else{
						$requestParams['serviceType'] = '标准快递';
					}
					$requestParams['payment'] = 'SHIPPER';
					$requestParams['dayType'] = '';
					$requestParams['pickupStartTime'] = '';
					$requestParams['pickupEndTime'] = '';
					$requestParams['op'] = '1';
					$requestParams['pollCallBackUrl'] = $this->config['site']['host'].'/app/api/push4';
					$requestParams['resultv2'] = '1';
					
					
					//$kuaidi100_post = model('Kuaidi100Api')->kuaidi100_post($requestParams,$method='bOrder',$sandbox=0);
					//p($kuaidi100_post);die;
					//p($requestParams);die;
					
					$data['requestParams'] = iserializer($requestParams);//快递100保存到数据库序列化
					$v = $kuaidi100_post['data'];
					$e['is_piliang']=1;
					//p($v);die;
					
					$overPrice = $v['overPrice']*100;
					$overPrice = $overPrice/($totalWeight-1);
					$overPrice = (int)$overPrice;
					
					$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$v['price']*100,$v['firstPrice']*100,$overPrice,$v['defPrice']*100,0,$e,$insurancePrice,$co,$data['coupon_pmt']);
					//p($getCatePrice);die;
					if($getCatePrice['sumMoneyYuan']){
						$data['firstPrice'] = $getCatePrice['firstPrice'];
						$data['addPrice'] = $getCatePrice['addPrice'];
						$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
						$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
						$data['preOrderFee'] = $getCatePrice['preOrderFee'];
						$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
						$data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
						$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
						$data['type']=4;//快递100接口模式
						$need += $getCatePrice['sumMoneyYuan'];
						if($getCatePrice['sumMoneyYuan']){
							$order_ids[] = Db::name('express_order')->insertGetId($data);	
						}
					}else{
						return json(array('code'=>0,'msg'=>'获取价格错误'));
					}
				}
			}
			if($ts==4){
				//易达
				$cargodata['express_code'] = $e['cate_name'];
				$deliveryType = model('Setting')->getYyDeliveryType($cargodata);//易达接口获取公司类型参数封装
				$requestParams2['senderAddress']=$s_address;// 寄件人地址
				$requestParams2['goods']=$thingType;
				$requestParams2['thirdNo']=$thirdNo;
				$requestParams2['senderName']=$s['name'];
				$requestParams2['receiveName']= $r['name'];
				$isMobile = isMobile($receiveMobile);
				if(!$isMobile){
					$requestParams2['receiveTel']=$receiveMobile;
				}elseif($customerType == 'poizon'){
					$requestParams2['receiveTel']=$receiveMobile;
				}else{
					$requestParams2['receiveMobile']=$receiveMobile;
				}
				$requestParams2['receiveDistrict']=$r['area'];//收件区县
				$requestParams2['receiveAddress']=$r_address;//收件地址
				$requestParams2['senderDistrict']=$s['area'];//寄件区县
				$requestParams2['deliveryType']=$deliveryType;
				$isMobile1 = isMobile($senderMobile);
				if(!$isMobile1){
					$requestParams2['senderTel']=$senderMobile;
				}else{
					$requestParams2['senderMobile']=$senderMobile;
				}
				$requestParams2['weight']=$totalWeight;//重量
				$requestParams2['customerType']='kd';
				$requestParams2['senderProvince']=$s['province'];//收件省份
				$requestParams2['receiveProvince']=$r['province'];//寄件省份
				$requestParams2['senderCity']=$s['city'];//收件城市
				$requestParams2['receiveCity']=$r['city'];//寄件城市
				$requestParams2['unitPrice']=0;//申通情况必填 单价
				$requestParams2['qty']=1;//申通情况必填 数量
				$requestParams2['pickUpStartTime']='';//顺丰预约时间
				$requestParams2['pickUpEndTime']='';
				$requestParams2['vloumLong']=1;//长
				$requestParams2['vloumHeight']=1;//高
				$requestParams2['vloumWidth']=1;//宽
				$requestParams2['packageCount']=1;//包裹数
				$requestParams2['guaranteeValueAmount']=0;//保价
				$requestParams2['receiveProvinceCode']='';//收件省code-编码参照国务院最新颁布
				$requestParams2['senderProvinceCode']='';//寄件省code-编码参照国务院最新颁
				$requestParams2['channelId']=$channel;//寄件省code-编码参照国务院最新颁布
				//p($e);
				//p($requestParams2);die;
				$data['requestParams2'] = iserializer($requestParams2);//易达接口保存到数据库序列化
			
			
				$execute = model('Setting')->execute($requestParams2,$Method='SMART_PRE_ORDER');
				if($execute['code'] == 200){
					$v = $execute['data'][$e['pinyin']][0];
					$limitWeight = (int)$v['limitWeight'];
					if($totalWeight > $limitWeight){
						return json(array('code'=>0,'msg'=>'所邮寄物品超过限重【'.$limitWeight.'】'));
					}
					//原价计费规则 
					$originalPrice = $v['originalPrice'];
					$originalPrice =  @json_decode($originalPrice,true);
					$first = $v['price'];
					$first =  @json_decode($first,true);
					
					$getYidastartEndMoney = model('Setting')->getYidastartEndMoney($uid,$v,$totalWeight);
					$priceA = $getYidastartEndMoney['priceA'];
					$priceB = $getYidastartEndMoney['priceB'];
					$e['is_piliang']=1;
					$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$v['preOrderFee']*100,$priceA,$priceB,$v['originalFee']*100,0,$e,$insurancePrice,$co,$data['coupon_pmt']);
					$data['firstPrice'] = $getCatePrice['firstPrice'];
					$data['addPrice'] =$getCatePrice['addPrice'];
					$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
					$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
					$data['preOrderFee'] = $getCatePrice['preOrderFee'];
					$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
					$data['sumMoneyYuan_old'] =$getCatePrice['sumMoneyYuan_old'];
					$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
					$data['type'] =1;//易达接口模式
					if($getCatePrice['sumMoneyYuan']){
						$order_ids[] = Db::name('express_order')->insertGetId($data);	
					}
				}else{
					return json(array('code'=>0,'msg'=>'YD获取预支付订单详情失败'.$execute['msg']));
				}
			}
		}
		$need_pay = $need ? $need : $orderMoney*100;
		if($order_ids){
			$logs = array(
				'type' => 'exps', 
				'types' => 1, 
				'user_id' => $uid, 
				'order_id' => 0, 
				'order_ids' => join(',', $order_ids), 
				'code' => 'wxapp', 
				'info' => join(',', $order_ids).'批量下单', 
				'need_pay' => $need_pay, 
				'create_time' => time(), 
				'create_ip' => request()->ip(), 
				'is_paid' => 0
			);
            $log_id = Db::name('payment_logs')->insertGetId($logs);
			if($payType == 2){
				$datas['payType']= 5;
			}else{
				$datas['payType']= 0;
			}
			$datas['outTradeNo']= $log_id;
			$datas['expressId']= $log_id;
			return json(array('code'=>1,'msg'=>"微信支付下单成功",'data'=>$datas));
		}else{
			return json(array('code'=>-1,'msg'=>'写入数据库失败'));
		}
	}
	
	
	//支付
	public function wxUnifiedorder(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$expressId = input('expressId','','trim,htmlspecialchars');
		$payChannel = input('payChannel','','trim,htmlspecialchars');
		
		$logs = Db::name('payment_logs')->where(array('log_id'=>$expressId))->find();
		$u = Db::name('users')->where(array('user_id'=>$uid))->find();
		$need_pay = $logs['need_pay'];//支付金额
		
		if($payChannel==5){
			if($u['money'] < $need_pay){
				return json(array('code'=>-1,'msg'=>'余额不足'));
			}
			$order_ids = explode(',',$logs['order_ids']);
			foreach($order_ids as $k =>$v){
				$order = Db::name('express_order')->where(array('id'=>$v))->find();
				if($order){
					$rest = model('Users')->addMoney($uid,-$order['sumMoneyYuan'],'批量下单余额支付订单id-'.$v.'支付成功',1,$v,'express');
					model('Setting')->updateExpressOrder($v,$order['sumMoneyYuan'],$logs['log_id'],$logs['user_id'],$logs['types']);//单独回调
				}
			}
			//更新支付表
			$updateData['log_id'] = $expressId;
			$updateData['is_paid'] = 1;
			$updateData['pay_time'] = time();
			$updateData['pay_ip'] = request()->ip();
			$updateData['return_order_id'] ='';//返回订单号
			$updateData['return_trade_no'] ='';//返回交易号
			$update = Db::name('payment_logs')->update($updateData);//更新支付信息
			if($update){
				$data['outTradeNo']= $expressId;
				return json(array('code'=>1,'msg'=>"余额支付下单成功",'data'=>$data));
			}else{
				return json(array('code'=>-1,'msg'=>'扣费失败'));
			}
		}else{
			$info = '快递下单';
			$connect = Db::name('connect')->where(array('uid'=>$uid))->find();	
			$WX_OPENID = $connect['openid'] ? $connect['openid'] : $connect['open_id'];	
			$Payment = model('Payment')->getPayment('wxapp');
			$out_trade_no = $logs['log_id'].'-'.time();
			$weixinpay = new \Wxpay($this->config['wxapp']['appid'],$WX_OPENID,$Payment['mchid'],$Payment['appkey'],$out_trade_no,$info,$need_pay);//支付接口
			$return = $weixinpay->pay();
			if($return['package'] == 'prepay_id='){
				return json(array('code'=>-1,'msg'=>'预支付失败:'.$return['rest']['return_msg']));
			}
			$data['need_pay']= $need_pay;
			$data['outTradeNo']= $expressId;
			$data['timeStamp']= $return['timeStamp'];
			$data['nonceStr'] =$return['nonceStr'];
			$data['packaged'] =$return['package'];
			$data['signType'] = 'MD5';
			$data['paySign'] = $return['paySign'];
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}
	}
	
	//支付状态查询
	public function orderQuery(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$outTradeNo = input('outTradeNo','','trim,htmlspecialchars');
		$logs = Db::name('payment_logs')->where(array('log_id'=>$outTradeNo))->find();
		if($logs['is_paid']==0){
			return json(array('code'=>-1,'msg'=>'支付失败'));
		}
		return json(array('code'=>1,'msg'=>"余额支付成功",'data'=>$data));
	}
	
	//获取详细地址
	public function addressOneInfoSec(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$addressBookId = input('addressBookId','','trim,htmlspecialchars');
		$userType = input('userType','','trim,htmlspecialchars');
		$v = Db::name('user_addr')->where(array('addr_id'=>$addressBookId,'closed'=>0))->find();
		$data['isDefault'] = $v['is_default'];
		$data['id'] = $v['addr_id'];
		$data['addressBookId'] = $v['addr_id'];
		$data['contractName'] = $v['name'];
		$data['contractPhone'] = $v['phone'];
		$data['detailAddress'] = $v['address'];
		$data['contractProvince'] = $v['province'];
		$data['contractCity'] = $v['city'];
		$data['contractCounty'] = $v['area'];
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	//地址详情
	public function addressOneInfo(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$addressBookId = input('addressBookId','','trim,htmlspecialchars');
		$userType = input('userType','','trim,htmlspecialchars');
		$v = Db::name('user_addr')->where(array('addr_id'=>$addressBookId,'closed'=>0))->find();
		$data['isDefault'] = $v['is_default'];
		$data['id'] = $v['addr_id'];
		$data['addressBookId'] = $v['addr_id'];
		$data['contractName'] = $v['name'];
		$data['contractPhone'] = $v['phone'];
		$data['detailAddress'] = $v['address'];
		$data['contractProvince'] = $v['province'];
		$data['contractCity'] = $v['city'];
		$data['contractCounty'] = $v['area'];
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	//识别地址
	public function addressIntelligentRecognition(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$content = input('addressInfo','','trim,htmlspecialchars');
		$host = "https://jiexi8.market.alicloudapi.com";
		$path = "/address/analysis";
		$method = "GET";
		$appcode = trim($this->config['wxapp']['addr_app_code']);
		$headers = array();
		array_push($headers, "Authorization:APPCODE ".$appcode);
		$querys = "text=".urlencode($content)."";
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
		$tmpInfo = curl_exec($curl); // 执行操作      
		if (curl_errno($curl)) {
			echo 'Errno' . curl_error($curl);
		}
		curl_close($curl); // 关闭CURL会话      
		$tmpInfo = json_decode($tmpInfo,true);//将json解析成数组
		if($tmpInfo['showapi_res_erro'] != 0){
			return json(array('code'=>0,'msg'=>'错误提示：'.$tmpInfo['showapi_res_code']));
		}
		$showapi_res_body = $tmpInfo['showapi_res_body'];
		
		$data['phone'] = $showapi_res_body['phonenum'];
		$data['area'] =  $showapi_res_body['county'];
		$data['addr'] = $showapi_res_body['town'].$showapi_res_body['detail'];
		$data['name'] =  $showapi_res_body['person'];
		if($showapi_res_body['province'] =='天津'){
			$data['province'] =  '天津市';	
		}elseif($showapi_res_body['province'] =='重庆'){
			$data['province'] =  '重庆市';	
		}elseif($showapi_res_body['province'] =='北京'){
			$data['province'] =  '北京市';	
		}elseif($showapi_res_body['province'] =='上海'){
			$data['province'] =  '上海市';	
		}else{
			$data['province'] = $showapi_res_body['province'];
		}
		if($showapi_res_body['city'] == '市辖区' || $showapi_res_body['city'] == ''){
			$data['city'] =  $showapi_res_body['province'];
		}else{
			$data['city'] =  $showapi_res_body['city'];
		}
		$data['type'] =  1;
		
		$uData['isDefault']= 0;
		$uData['detailAddress']= $data['addr'];
		$uData['contractPhone']= $data['phone'];
		$uData['contractName']= $data['name'];
		$uData['contractProvince']= $data['province'];
		$uData['contractCity']=$data['city'];
		$uData['contractCounty']= $data['area'];
		$uData['data']= $data;
			
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$uData));	
	}
	
	//新增地址
	public function addressAdd(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$addressBookId = input('addressBookId','','trim,htmlspecialchars');
		$isFlag = input('isFlag','','trim,htmlspecialchars');
		
		$contractProvince = input('contractProvince','','trim,htmlspecialchars');
		$contractCity = input('contractCity','','trim,htmlspecialchars');
		$contractCounty = input('contractCounty','','trim,htmlspecialchars');
		$contractPhone = input('contractPhone','','trim,htmlspecialchars');
		$contractName = input('contractName','','trim,htmlspecialchars');
		$detailAddress = input('detailAddress','','trim,htmlspecialchars');
		$isDef = input('isDef','','trim,htmlspecialchars');
		$operateType = input('operateType','','trim,htmlspecialchars');
		
		if($operateType==1){
			$updateData['type'] = 1;
		}
		if($operateType=='add'){
			$updateData['type'] = 2;
		}

		$updateData['addr_id'] = $addressBookId;
		$updateData['name'] = $contractName;
		$updateData['linkMan'] = $contractName;
		$updateData['address'] = deleteHtml($detailAddress);
		$updateData['city'] =$contractCity;		
		$updateData['province']  = $contractProvince;		
		$updateData['area']  = $contractCounty;	
		$updateData['phone']  = $contractPhone;	
		$updateData['mobile']  = '';		
		$updateData['user_id'] = $uid;
		$updateData['lat'] = '';
		$updateData['lng'] = '';
		$updateData['is_default'] = $isDef;
		$updateData['createTime'] = time();
		$r = Db::name('user_addr')->insertGetId($updateData);
		if($r){
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>''));
		}else{
			return json(array('code'=>-1,'msg'=>'更新失败'));
		}
		
		if($isFlag==1){
			$u = Db::name('user_addr')->where(array('addr_id'=>$addressBookId))->update(array('closed'=>1));
			if($u){
				return json(array('code'=>1,'msg'=>"获取成功",'data'=>''));
			}else{
				return json(array('code'=>-1,'msg'=>'删除失败'));
			}
		}
	}
	//更新地址
	public function addressUpdate(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$addressBookId = input('addressBookId','','trim,htmlspecialchars');
		$isFlag = input('isFlag','','trim,htmlspecialchars');
		$userType = input('userType','','trim,htmlspecialchars');
		if(!$addressBookId){
			return json(array('code'=>-1,'msg'=>'地址不存在'));
		}
		
		$contractProvince = input('contractProvince','','trim,htmlspecialchars');
		$contractCity = input('contractCity','','trim,htmlspecialchars');
		$contractCounty = input('contractCounty','','trim,htmlspecialchars');
		$contractPhone = input('contractPhone','','trim,htmlspecialchars');
		$contractName = input('contractName','','trim,htmlspecialchars');
		$detailAddress = input('detailAddress','','trim,htmlspecialchars');
		$isDef = input('isDef','','trim,htmlspecialchars');
		$operateType = input('operateType','','trim,htmlspecialchars');

		$updateData['addr_id'] = $addressBookId;
		$updateData['name'] = $contractName;
		$updateData['linkMan'] = $contractName;
		$updateData['address'] = deleteHtml($detailAddress);
		$updateData['city'] =$contractCity;		
		$updateData['province']  = $contractProvince;		
		$updateData['area']  = $contractCounty;	
		$updateData['phone']  = $contractPhone;	
		$updateData['mobile']  = '';		
		$updateData['user_id'] = $uid;
		$updateData['lat'] = '';
		$updateData['lng'] = '';
		$updateData['is_default'] = $isDef;
		$updateData['createTime'] = time();
		$r = Db::name('user_addr')->update($updateData);
		if($r){
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>''));
		}else{
			return json(array('code'=>-1,'msg'=>'更新失败'));
		}
		
		if($isFlag==1){
			$u = Db::name('user_addr')->where(array('addr_id'=>$addressBookId))->update(array('closed'=>1));
			if($u){
				return json(array('code'=>1,'msg'=>"获取成功",'data'=>''));
			}else{
				return json(array('code'=>-1,'msg'=>'删除失败'));
			}
		}
	}
	
	public function addressList(){
		$checkAuth = $this->checkAuth();//权限检测
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>-1,'msg'=>'登录失效'));
		}
		$type = (int)input('type','','trim,htmlspecialchars');
		$pageNum = input('pageNum','','trim,htmlspecialchars');
		$pageSize = input('pageSize','','trim,htmlspecialchars');
		$userType = input('userType','','trim,htmlspecialchars');
		
		$map['closed'] =0;
		$map['user_id'] =$uid;
		$map['type'] =$type;
		$map['tc'] =1;
		
		$count = Db::name('user_addr')->where($map)->count();
		$Page = new \Page3($count,5);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('user_addr')->where($map)->limit(0,30)->order(array('is_default'=>'desc','addr_id'=>'desc'))->select(); 	
			foreach($list as $k=>$v){
				$list[$k]['isDefault'] = $v['is_default'];
				$list[$k]['id'] = $v['addr_id'];
				$list[$k]['addressBookId'] = $v['addr_id'];
				$list[$k]['contractName'] = $v['name'];
				$list[$k]['contractPhone'] = $v['phone'];
				$list[$k]['detailAddress'] = $v['address'];
				$list[$k]['contractProvince'] = $v['province'];
				$list[$k]['contractCity'] = $v['city'];
				$list[$k]['contractCounty'] = $v['area'];
				$list[$k]['lat'] = $v['lat'];
				$list[$k]['lng'] = $v['lng'];
			}
		}
		
		$data['total'] = $count;
		$data['list'] = $list;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	

	

	
}
