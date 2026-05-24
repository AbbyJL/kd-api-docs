<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Express extends Base{



	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->host = $this->config['site']['host'];
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
	

	//userNotice
 	public function userNotice(){
		$d['mark'] = 1;
		
		$notices = Db::name('user_profit_logs')->order('log_id desc')->limit(0,10)->select();
		foreach($notices as $k=>$v){
			$notices[$k]['createTimes'] =  $v['create_time'];
			$notices[$k]['isShow'] = '1';
			$notices[$k]['page'] = '1';
			$notices[$k]['pageCount'] = '20';
			$notices[$k]['titile'] = $v['info'];
		}
		
		$notice[0]['createTimes'] =  '';
		$notice[0]['isShow'] = '1';
		$notice[0]['page'] = '1';
		$notice[0]['pageCount'] = '20';
		$notice[0]['titile'] = '用户下单成功';
	
		$d['notices'] = $notices ? $notices : $notice;
		$d['wxapp'] = $this->config['wxapp'];
		
		
		//订阅消息ID
		$tmplIds[0] = Db::name('weixin_tmpl')->where(array('title'=>'接单成功提醒'))->value('template_id');
		$tmplIds[1] = Db::name('weixin_tmpl')->where(array('title'=>'补差价通知'))->value('template_id');
		$tmplIds[2] = Db::name('weixin_tmpl')->where(array('title'=>'签收成功通知'))->value('template_id');
		$d['tmplIds'] = $tmplIds;
		
	
		$unitId[0] = $this->config['wxapp']['unitId_0'];
		$unitId[1] = $this->config['wxapp']['unitId_1'];
		$unitId[2] = $this->config['wxapp']['unitId_2'];
		$unitId[3] = $this->config['wxapp']['unitId_3'];
		$unitId[4] = $this->config['wxapp']['unitId_4'];
		$unitId[5] = $this->config['wxapp']['unitId_5'];
		$unitId[6] = $this->config['wxapp']['unitId_6'];
		$unitId[7] = $this->config['wxapp']['unitId_7'];
		$unitId[8] = $this->config['wxapp']['unitId_8'];
		$unitId[9] = $this->config['wxapp']['unitId_9'];
		$d['unitId'] = $unitId;
		
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	
	public function inArrayKey($array, $inarray, $field){
		if(!is_array($inarray)){
			$inarray = explode(',', $inarray);
		}
		$arr = array();
		foreach($array as $key=>$value){
			if(in_array($value[$field], $inarray)){
				$arr[] = $value;
			}
		}
		return $arr;
	}
	
	
	//更新订单状态
	public function checkOrderUpdate($r,$v,$deliveryType){
		$i = $i1 = $i2 = $i3 = 0;
		if($r){
			foreach($r as $va){
				$r = $va;
			}
		}
		
		//p($r);die;
		//实际收取快递费
		$feeBlockList = $r['feeBlockList'];//快递费用
		$orderFee = $this->inArrayKey($feeBlockList,'实收快递费','name');
		if($orderFee){
			$orderFee = $orderFee[0]['fee']*100;
		}
		if(!$orderFee){
			$orderFee = $r['orderFee']*100;//快递公司返回实际扣费
		}
		//快递费用结束
		
		
		$preOrderFee = $r['preOrderFee']*100;//预付款
		$orderStatus = $r['orderStatus'];//快递公司状态1-待取件  2-运输中  3-已签收  6-异常     10-已取消  
		$weight = $r['weight'];//客户下单重量
		$realOrderState = $r['realOrderState'];
		$packageCount = $r['packageCount'];//数量
		$volume = $r['volume'];
		$realWeight = $r['realWeight'];
		$realVolume = $r['realVolume'];
		$guaranteeValueAmount = $r['guaranteeValueAmount'];//保价金额
		$deliveryId = $r['deliveryId'];//运单编号
	
		//p($orderStatus);die;
		
		
		//p($orderFee);
	//p($v['sumMoneyYuan_old']);die;
	
		if($v['orderStatus'] != '0' && $v['orderStatus'] != '-1' && $v['orderStatus'] != '5'){
			
			
			if($orderFee > $v['sumMoneyYuan_old'] && $v['diffStatus'] == 0){
				
				$v['diffMoneyYuan'] = $orderFee-$v['sumMoneyYuan_old'];//实际扣费之间的差价
				$logoUrl = model('ExpressOrder')->logoUrl($v['kuaidi']);
				$jia = ($v['diffMoneyYuan']*$logoUrl['firstPrice'])/100;
				$v['diffMoneyYuan'] = $v['diffMoneyYuan']+$jia;
				$v['diffMoneyYuan'] = (int)$v['diffMoneyYuan'];
				
				
				if($v['diffMoneyYuan']){
					$i++; 
					//更新订单
					Db::name('express_order')->where(array('id'=>$v['id']))->update(array('diffStatus'=>1,'diffMoneyYuan'=>$v['diffMoneyYuan'])); 	
					//判断补差价
					model('Sms')->sendSmsTmplSend($v,$v['user_id'],$title = '补差价通知');
					model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '补差价通知');
				}
			}
		}
		
		//快递公司已经取消订单
		if($orderStatus==10 && $v['orderStatus'] != '-1'  && $v['orderStatus'] != '5' && $v['orderRightsStatus'] == 0){
			$i1++; 
			//已取消待后台退款
			Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>-1,'orderRightsStatus'=>1)); 	
		}
		
		//p($v['orderStatus']);
		//p($orderStatus);die;
		
	
		
		$u =0;
		if($v['orderStatus'] == 1 && $orderStatus==1){
			$u =1;
		}elseif($v['orderStatus'] == 1 && $orderStatus==2){
			$u =1;
		}elseif(!$v['realOrderState'] && $orderStatus==1){
			$u =1;
		}elseif(!$v['realOrderState'] && $orderStatus==2){
			$u =1;
		}elseif(!$v['deliveryId'] == 1 && $deliveryId){
			$u =1;
		}
		
		//p($u);die;
		if($u==1){
			$i2++; 
			//已接单
			$up['totalNumber'] = $packageCount;
			$up['totalVolume'] = $volume;
			$up['review_weight'] = $weight;
			$up['review_vloumn'] = $realVolume;
			$up['realOrderState'] = $realOrderState;
			$up['orderStatus'] = 2;
			$up['deliveryId'] = $deliveryId;
			Db::name('express_order')->where(array('id'=>$v['id']))->update($up); 	
			//更新状态
			model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '接单成功提醒');
		}
		
		
		//已派送
		if($v['orderStatus'] == 2 && $realOrderState=='配送揽收成功'){
			$up['orderStatus'] = 3;
			Db::name('express_order')->where(array('id'=>$v['id']))->update($up); 	
		}
		
		
		
		$q=0;
		if($orderStatus==3 && $v['orderStatus'] == 2){
			$q=1;
		}
		if($orderStatus==3 && $v['orderStatus'] == 3){
			$q=1;
		}
		if($q){
			$i3++; 
			//订单完成
			Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>4)); 	
			//订单完成分销
			model('ExpressOrder')->profit($v,$v['user_id'],'分销');
			model('ExpressOrder')->orderAddIntegral($v,$v['user_id'],'给用户奖励积分');
			//完成订单发送通知
			model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '签收成功通知');
		}
		$msg = $kuaidi.'更新成功其中补差价【'.$i.'】条，取消订单【'.$i1.'】条，更新已接单【'.$i2.'】条，更新订单完成【'.$i3.'】条';
		return $msg;
	}
	
	
	
	//检测订单checkOrder
 	public function checkOrder(){
		$time = input('time','','trim,htmlspecialchars');		
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		$id = (int)input('id','','trim,htmlspecialchars');		
		if($id){
			$list = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3,4)),'type'=>'1','id'=>$id))->order('id desc')->limit(0,100)->select(); 
			//p($list);die;
		}elseif($user_id){
			$list = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'user_id'=>$user_id,'type'=>'1'))->order('id desc')->limit(0,50)->select(); 
		}else{
			$list = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'type'=>'1'))->order('id desc')->limit(0,100)->select(); 
		}
		
		$i=0;
		foreach($list as $k=>$v){
			if($v['expressId'] || $v['expressNo']){
				$logoUrl = $this->logoUrl($v['kuaidi']);
				$requestParams['deliveryType'] =$logoUrl['deliveryType'];
				if(!empty($v['deliveryId'])){
					$requestParams['deliveryIds'] = array($v['deliveryId']);
				}else{
					$requestParams['orderNos'] = array($v['expressNo']);
				}
				//新的查询接口
				//p($requestParams);
				$execute = model('Setting')->execute($requestParams,$Method='QUERY_ORDER_INFO');
				//p($v);die;
				if($execute['code'] == 200){
					$i++;
					$c .= $this->checkOrderUpdate($execute['data'],$v,$logoUrl['deliveryType']);
				}
			}
			
		}
		//计算差价订单
		$count = Db::name('express_order')->where(array('user_id'=>$user_id,'diffMoneyYuan'=>array('gt',0),'diffStatus'=>1))->count(); 	
		if($count){
			$d['status'] = 1;
		}else{
			$d['status'] = 0;
		}
		$msg .= '一共更新【'.$i.'】次';
		$msg .= $c.'';
		return json(array('c'=>0,'d'=>$d,'c'=>$c,'msg'=>$msg));
	}
	
	
	
	//检测订单checkOrder
 	public function checkOrders(){
		$time = input('time','','trim,htmlspecialchars');		
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		
		$deliveryIds1 = $deliveryIds2 = $deliveryIds3 = $deliveryIds4 = array();
		
		if($user_id){
			$list1 = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'user_id'=>$user_id,'kuaidi'=>'申通','type'=>'1'))->order('id desc')->limit(0,20)->select(); 
		}else{
			$list1 = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'kuaidi'=>'申通','type'=>'1'))->limit(0,20)->order('id desc')->select(); 
		}
		foreach($list1 as $k=>$v){
			$deliveryIds1[] = $v['deliveryId'];
		}
		$requestParams1['deliveryIds'] = $deliveryIds1;
		$requestParams1['deliveryType'] = 'STO-INT';
		if($deliveryIds1){
			$execute = model('Setting')->execute($requestParams1,$Method='BATCH_QUERY_ORDER_TRACE_INFO');
			if($execute['code'] == 200){
				foreach($execute['data'] as $k=>$v){
					$c1 = $this->checkOrderUpdate($v,'申通');
				}
			}
		}
		
		
		if($user_id){
			$list2 = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'user_id'=>$user_id,'kuaidi'=>'京东','type'=>'1'))->order('id desc')->limit(0,20)->select(); 
		}else{
			$list2 = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'kuaidi'=>'京东','type'=>'1'))->limit(0,20)->order('id desc')->select(); 
		}
		foreach($list2 as $k=>$v){
			$deliveryIds2[] = $v['deliveryId'];
		}
		$requestParams2['deliveryIds'] = $deliveryIds2;
		$requestParams2['deliveryType'] = 'JD';
		if($deliveryIds2){
			$execute = model('Setting')->execute($requestParams2,$Method='BATCH_QUERY_ORDER_TRACE_INFO');
			if($execute['code'] == 200){
				foreach($execute['data'] as $k=>$v){
					$c2 = $this->checkOrderUpdate($v,'京东');
				}
			}
		}
		
		
		
		if($user_id){
			$list3 = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'user_id'=>$user_id,'kuaidi'=>'圆通','type'=>'1'))->order('id desc')->limit(0,20)->select(); 
		}else{
			$list3 = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'kuaidi'=>'圆通','type'=>'1'))->limit(0,20)->order('id desc')->select(); 
		}
		foreach($list3 as $k=>$v){
			$deliveryIds3[] = $v['deliveryId'];
		}
		$requestParams3['deliveryIds'] = $deliveryIds3;
		$requestParams3['deliveryType'] = 'YTO';
		if($deliveryIds3){
			$execute = model('Setting')->execute($requestParams3,$Method='BATCH_QUERY_ORDER_TRACE_INFO');
			if($execute['code'] == 200){
				foreach($execute['data'] as $k=>$v){
					$c3 = $this->checkOrderUpdate($v,'圆通');
				}
			}
		}
		
		if($user_id){
			$list4 = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'user_id'=>$user_id,'kuaidi'=>'德邦','type'=>'1'))->order('id desc')->limit(0,20)->select(); 
		}else{
			$list4 = Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'kuaidi'=>'德邦','type'=>'1'))->limit(0,20)->order('id desc')->select(); 
		}
		foreach($list4 as $k=>$v){
			$deliveryIds4[] = $v['deliveryId'];
		}
		$requestParams4['deliveryIds'] = $deliveryIds4;
		$requestParams4['deliveryType'] = 'DOP';
		if($deliveryIds4){
			$execute = model('Setting')->execute($requestParams4,$Method='BATCH_QUERY_ORDER_TRACE_INFO');
			if($execute['code'] == 200){
				foreach($execute['data'] as $k=>$v){
					$c4 = $this->checkOrderUpdate($v,'德邦');
				}
			}
		}
		
		
		$count = Db::name('express_order')->where(array('user_id'=>$user_id,'diffMoneyYuan'=>array('gt',0),'diffStatus'=>1))->count(); 	
		if($count){
			$d['status'] = 1;
		}else{
			$d['status'] = 0;
		}
		$msg .= $c1.'<br>';
		$msg .= $c2.'<br>';
		$msg .= $c3.'<br>';
		$msg .= $c4.'<br>';
		return json(array('c'=>0,'d'=>$d,'c1'=>$c1,'c2'=>$c2,'c3'=>$c3,'c4'=>$c4,'msg'=>$msg));
	}
	
	//分销
	public function profit($v,$user_id,$title){
		$id = $v['id'];
		$p = $v['sumMoneyYuan'];
		$money1 = $money2 = $money3 = 0;
		$rate1 = $this->config['profit']['profit_rate1'];
		$rate2 = $this->config['profit']['profit_rate2'];
		$rate3 = $this->config['profit']['profit_rate3'];
		
		$u = Db::name('users')->where(array('user_id'=>$user_id))->field('user_id,pid,parent_id,nickname')->find();
		
		
		$u1 = Db::name('users')->where(array('user_id'=>$u['parent_id']))->field('user_id,pid,parent_id,nickname')->find();
		$m1 = round($p*$rate1/100);
		if($m1 > 0 && $u1){
			model('Users')->addMoney($u1['user_id'], $m1,$id.'订单1级分成');
			model('Users')->addProfit($u1['user_id'], $order_type = 0, 'express', $id, $shop_id = '0',$m1, $is_separate = '1',$id.'订单1级分成');
		}
		
		$u2 = Db::name('users')->where(array('user_id'=>$u1['parent_id']))->field('user_id,pid,parent_id,nickname')->find();
		$m2 = round($p*$rate2/100);
		if($m2 > 0 && $u2){
			model('Users')->addMoney($u2['user_id'], $m2,$id.'订单4级分成');
			model('Users')->addProfit($u2['user_id'], $order_type = 0,'express', $id, $shop_id = '0',$m2, $is_separate = '1',$id.'订单4级分成');
		}
		
		$u3 = Db::name('users')->where(array('user_id'=>$u2['parent_id']))->field('user_id,pid,parent_id,nickname')->find();
		$m3 = round($p*$rate3/100);
		if($m3 > 0 && $u3){
			model('Users')->addMoney($u3['user_id'], $m3,$id.'订单3级分成');
			model('Users')->addProfit($u3['user_id'], $order_type = 0,'express',$id, $shop_id = '0',$m3, $is_separate = '1',$id.'订单3级分成');
		}
		return $money1+$money2+$money3;
	}
	
	
	//看广告领取优惠券
	public function reward(){
		$time = input('time','','trim,htmlspecialchars');		
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		if(!$user_id){
			return json(array('c'=>20020,'m'=>'token失效'));
		}
		
		//领取金额
		$money = '0';
		$moneyYuan = '1';
		
		
		$data['user_id'] = $user_id;
		$data['expireTime'] = time();//过期时间
		$data['moneyYuan'] = $money;
		$data['bgExprite'] = '';//结束日期
		$data['create_time'] = time();
		
		//领取成功
		$rest = Db::name('coupon_download')->insertGetId($data);
		if($rest){
			$d['money'] = $money;
			$d['moneyYuan'] = $moneyYuan;
		}else{
			//领取逻辑
			return json(array('c'=>20020,'m'=>'领取失败'));
		}
	}
	
	
	
	
	public function bannerApi(){
		$data = model('Ad')->get_ad_list(array(),115);
		foreach($data as $k=>$v){
			$data[$k]['banner_url'] = config_weixin_img($v['photo']);
			$data[$k]['jump_url'] = $v['link_url'];
			$data[$k]['id'] = $v['ad_id'];
			$data[$k]['click'] = '1';
		}
		return json(array('c'=>0,'data'=>$data));
	}
	
	//快讯
	public function newsListApi(){
	
		$data[0]['title'] = "恭喜150*****545已入账13.45元,点击分享返佣吧";
		$data[0]['jump_url'] = "/pages/member/invite/invite";
		$data[0]['click'] = '1';
		
		return json(array('c'=>0,'data'=>$data));
	}
	
	
	
	//删除弹窗
	public function defendpop(){
		$data['content'] = "快隆惠递正在升级维护中，寄件下单请晚些再试，如有不便敬请谅解！";
		$data['status'] = 0;
		$data['title'] = "升级维护公告";
		return json(array('c'=>0,'data'=>$data));
	}

	//获取数据
	public function settravel(){
		return json(array('code'=>1,'msg'=>"添加用户轨迹成功",'data'=>1));
	}

	//sentoptxt
	public function sentoptxt(){
		$data['content'] = "您好，如需修改运单信息，可点【再来一单】重新下单，并将错误运单撤消。";
		$data['id'] = 11;
		$data['title'] = "注意事项";
		return json(array('code'=>1,'msg'=>'获取成功','data'=>$data));
	}

	
	
	//获取默认地址
 	public function getDefault(){
		$time = input('time','','trim,htmlspecialchars');		
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		if(!$user_id){
			return json(array('c'=>20020,'m'=>'token失效'));
		}
		$address = Db::name('user_addr')->where(array('user_id'=>$user_id,'is_default'=>1))->find();
		if($address){
			$d['address'] = $address;
			return json(array('c'=>0,'d'=>$d));
		}else{
			return json(array('c'=>20020,'m'=>'没有默认地址'));
		}
	}
	
	//获取省份
 	public function provinceList(){
		$time = input('time','','trim,htmlspecialchars');		
		$cityList = Db::name('copy_province')->limit(0,36)->select(); 	
		foreach($cityList as $k=>$v){
			$cityList[$k]['areaName'] = $v['name'];
			$cityList[$k]['parentId'] = 0;
			$cityList[$k]['shortName'] = $v['name'];
		}
		$d['cityList'] = $cityList;
		return json(array('c'=>20020,'d'=>$d));
	}
	
	
	//获取城市
 	public function cityList(){
		$pId = input('pId','','trim,htmlspecialchars');		
		$cityList = Db::name('copy_city')->where(array('ParentId'=>$pId))->limit(0,300)->select(); 	
		foreach($cityList as $k=>$v){
			$cityList[$k]['id'] = $v['city_id'];
			$cityList[$k]['areaName'] = $v['name'];
			$cityList[$k]['shortName'] = $v['name'];
		}
		if(!$cityList){
			$cityList = Db::name('copy_area')->where(array('city_id'=>$pId))->limit(0,300)->select(); 	
			foreach($cityList as $k=>$v){
				$cityList[$k]['id'] = $v['area_id'];
				$cityList[$k]['areaName'] = $v['area_name'];
				$cityList[$k]['shortName'] = $v['area_name'];
			}
		}
		$d['cityList'] = $cityList;
		return json(array('c'=>20020,'d'=>$d));
	}
	
	
	//地址列表
 	public function addressList(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$keyworld = input('keyworld','','trim,htmlspecialchars');
	
		$map['closed'] =0;
		$map['user_id'] =$user_id;
		if($keyworld && $keyworld != '' ){
            $map['address'] = array('LIKE', '%'.$keyworld.'%');
        }		
	
		$addressList = Db::name('user_addr')->where($map)->limit(0,30)->select(); 	
		foreach($addressList as $k=>$v){
			$addressList[$k]['id'] = $v['addr_id'];
			$addressList[$k]['isDefault'] = $v['is_default'];
			
		}
		$d['addressList'] = $addressList;
		return json(array('c'=>0,'refer'=>1,'d'=>$d));
	}

	//识别地址
 	public function parseAddress(){
		$text = input('text','','trim,htmlspecialchars');		
		$apiAnalysisPublic = model('Users')->apiAnalysisPublic($text);
		$d['address'] = $apiAnalysisPublic;
		return json(array('c'=>20020,'d'=>$d));
	}
	
	
	//保存地址
 	public function saveAddress(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		if(!$user_id){
			return json(array('c'=>20020,'m'=>'TOKEN不存在'));
		}
		$id  = input('id','','trim,htmlspecialchars');	
		
		
		
		
		$updateData['address'] = input('address','','trim,htmlspecialchars');//去除左右空格
		$updateData['address'] =  @str_replace(' ', '',$updateData['address']);//去除中间空格
			
		$updateData['city'] =input('city','','trim,htmlspecialchars');		
		$updateData['linkMan']  = input('linkMan','','trim,htmlspecialchars');		
		$updateData['mobile']  = input('mobile','','trim,htmlspecialchars');		
		$updateData['user_id'] = $user_id;
		$updateData['createTime'] = time();
		
		if($id){
			$updateData['addr_id'] = $id;
			Db::name('user_addr')->update($updateData);
		}else{
			Db::name('user_addr')->insertGetId($updateData);
		}
		$d['address'] = $updateData;
		return json(array('c'=>0,'refer'=>1,'d'=>$d));
	}

	//设置默认
 	public function setDefault(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		if(!$user_id){
			return json(array('c'=>20020,'m'=>'TOKEN不存在'));
		}
		$addr_id  = input('addressId','','trim,htmlspecialchars');	
		if(!$addr_id){
			return json(array('c'=>20020,'m'=>'ID不存在'));
		}
		$rest = Db::name('user_addr')->where(array('user_id'=>$user_id))->update(array('is_default'=>0));
		if($rest){
			$rest = Db::name('user_addr')->where(array('user_id'=>$user_id,'addr_id'=>$addr_id))->update(array('is_default'=>1));
			$d = array();
			return json(array('c'=>0,'d'=>$d));
		}else{
			return json(array('c'=>20020,'m'=>'设置是吧'));
		}
	}
	
	//删除地址
 	public function addressDel(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		if(!$user_id){
			return json(array('c'=>20020,'m'=>'TOKEN不存在'));
		}
		$addr_id  = input('addressId','','trim,htmlspecialchars');	
		if(!$addr_id){
			return json(array('c'=>20020,'m'=>'ID不存在'));
		}
		$rest = Db::name('user_addr')->where(array('user_id'=>$user_id,'addr_id'=>$addr_id))->find();
		if($rest){
			$r = Db::name('user_addr')->where(array('user_id'=>$user_id,'addr_id'=>$addr_id))->delete();
			if($r){
				return json(array('c'=>0,'d'=>'d','m'=>'删除成功'));
			}else{
				return json(array('c'=>20020,'m'=>'删除失败'));
			}
			return json(array('c'=>0,'d'=>$d));
		}else{
			return json(array('c'=>20020,'m'=>'地址不存在'));
		}
	}
	
	//获取二维码
	public function qrcode(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$page="pages/index/index";//路径
		$width = '200';
        $img = $this->set_msg($user_id,$page,$width);//scene,page,width
		$res = model('Api')->qrcodeWxapp($user_id,$page,$width,$parameter='userId',$user_id);
		$d['qrUrl'] = $res;
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	//获取token
	public function getaccess_token(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->config['wxapp']['appid'] . "&secret=" . $this->config['wxapp']['appsecret'] . "";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($data, true);
        return $data['access_token'];
    }
	
	//获取小程序码
	public function set_msg($storeid,$page,$width){
        $access_token = $this->getaccess_token();
        $data2 = array("scene" =>$storeid,"page"=>$page,"width" =>$width);
        $data2 = json_encode($data2);
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token."";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
     }
	
	
	
	//绑定手机
	public function bindMobile(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		$sessionKey = $getallheaders['Sessionkey'];
		
		$encryptedData = input('encryptedData','','trim,htmlspecialchars');
		$iv = input('iv','','trim,htmlspecialchars');
		
		
	    $rest['phoneNumber'] = '';
		include ROOT_PATH.'extend/jiemi/WXBizDataCrypt.php';
		$WXBizDataCrypt = new \WXBizDataCrypt($this->config['wxapp']['appid'],$sessionKey);
		$errCode = $WXBizDataCrypt->decryptData($encryptedData,$iv,$data);
		$rest = json_decode($data,true);  
		
		if($rest['phoneNumber']){
			Db::name('users')->where(array('user_id'=>$user_id ))->update(array('mobile'=>$rest['phoneNumber']));
		}
		
		$d = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$d['mobile'] = $rest['phoneNumber'];
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	//获取快递公司的图片价格
	public function logoUrl($kuaidi = '京东',$uid = 0){
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
		}elseif(strstr($kuaidi,'京东') == true){
			$kuaidi = '京东';
		}elseif(strstr($kuaidi,'圆通') == true){
			$kuaidi = '圆通';
		}elseif(strstr($kuaidi,'申通') == true){
			$kuaidi = '申通';
		}elseif(strstr($kuaidi,'德邦') == true){
			$kuaidi = '德邦';
		}elseif(strstr($kuaidi,'极兔') == true){
			$kuaidi = '极兔';
		}elseif(strstr($kuaidi,'顺丰') == true){
			$kuaidi = '顺丰';
		}
		
		$u = Db::name('users')->where(array('user_id'=>$uid))->find();
		
		$cate = Db::name('express_cate')->where(array('cate_name'=>$kuaidi))->find();
		if($cate){
			$deliveryType = $cate['pinyin'];
			$desc = $cate['info'] ? $cate['info'] : '该快递方式暂无说明';
			$expressId = $cate['cate_id'];
			$logoUrl = config_weixin_img($cate['photo']);
			if($u['rank_id']){
			//vip价格
				$firstPrice = $cate['firstPrice2'];
				$addPrice = $cate['addPrice2'];
			}elseif($u['money'] >= 10000){
				//储值价格
				$firstPrice = $cate['firstPrice1'];
				$addPrice = $cate['addPrice1'];
			}else{
				//普通会员价格
				$firstPrice = $cate['firstPrice'];
				$addPrice = $cate['addPrice'];
			}
			$limitFirstPrice = $cate['limitFirstPrice'];
			$limitAddPrice = $cate['limitAddPrice'];
		}else{
			if($kuaidi == '京东'){
				$deliveryType = 'JD';
				$desc = '京东-JD';
				$expressId = 6;
				$logoUrl = $this->config['site']['host'].'/static/default/wap/img/jd.png';
				$firstPrice = $this->config['wxapp']['firstPrice'];
				$addPrice = $this->config['wxapp']['addPrice'];
				$limitFirstPrice = $this->config['wxapp']['limitFirstPrice'];
				$limitAddPrice = $this->config['wxapp']['limitAddPrice'];
			}elseif($kuaidi == '圆通'){
				$deliveryType = 'YTO';
				$desc = '圆通-YTO';
				$expressId = 3;
				$logoUrl = $this->config['site']['host'].'/static/default/wap/img/yt.png';
				$firstPrice = $this->config['wxapp']['firstPrice'];
				$addPrice = $this->config['wxapp']['addPrice'];
				$limitFirstPrice = $this->config['wxapp']['limitFirstPrice'];
				$limitAddPrice = $this->config['wxapp']['limitAddPrice'];
			}elseif($kuaidi == '申通'){
				$deliveryType = 'STO-INT';
				$desc = '申通-STO-INT';
				$expressId = 4;
				$logoUrl = $this->config['site']['host'].'/static/default/wap/img/zt.png';
				$firstPrice = $this->config['wxapp']['firstPrice'];
				$addPrice = $this->config['wxapp']['addPrice'];
				$limitFirstPrice = $this->config['wxapp']['limitFirstPrice'];
				$limitAddPrice = $this->config['wxapp']['limitAddPrice'];
			}elseif($kuaidi== '德邦'){
				$deliveryType = 'DOP';
				$desc = '德邦-DB';
				$expressId = 2;
				$logoUrl = $this->config['site']['host'].'/static/default/wap/img/db.png';
				$firstPrice = $this->config['wxapp']['firstPrice'];
				$addPrice = $this->config['wxapp']['addPrice'];
				$limitFirstPrice = $this->config['wxapp']['limitFirstPrice'];
				$limitAddPrice = $this->config['wxapp']['limitAddPrice'];
			}elseif($kuaidi== '极兔'){
				$deliveryType = 'JT';
				$desc = '极兔-JT';
				$expressId = 5;
				$logoUrl = $this->config['site']['host'].'/static/default/wap/img/jt.png';
				$firstPrice = $this->config['wxapp']['firstPrice'];
				$addPrice = $this->config['wxapp']['addPrice'];
				$limitFirstPrice = $this->config['wxapp']['limitFirstPrice'];
				$limitAddPrice = $this->config['wxapp']['limitAddPrice'];
			}elseif($kuaidi== '顺丰'){
				$deliveryType = 'SF';
				$desc = '顺丰-DOP';
				$expressId = 6;
				$logoUrl = $this->config['site']['host'].'/static/default/wap/img/sf.png';
				$firstPrice = $this->config['wxapp']['firstPrice'];
				$addPrice = $this->config['wxapp']['addPrice'];
				$limitFirstPrice = $this->config['wxapp']['limitFirstPrice'];
				$limitAddPrice = $this->config['wxapp']['limitAddPrice'];
			}
		}
		return array(
			'deliveryType'=>$deliveryType,
			'expressName'=>$kuaidi,
			'logoUrl'=>$logoUrl,
			'desc'=>$desc,
			'firstPrice'=>$firstPrice,
			'addPrice'=>$addPrice,
			'limitFirstPrice'=>$limitFirstPrice,
			'limitAddPrice'=>$limitAddPrice,
			'expressId'=>$expressId
		);
	}
	
	
	//查询价格
	public function enquiry(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		$sessionKey = $getallheaders['Sessionkey'];
		
		$orderParamList = input('orderParamList/a','','trim,htmlspecialchars');//收件方重量，物品信息
		$order = $orderParamList[0];
		
		$orderType = input('orderType','','trim,htmlspecialchars');//订单类型
		$profile = input('profile','','trim,htmlspecialchars');
		$sendAddress = input('sendAddress','','trim,htmlspecialchars');//寄件方地址
		$sendCity = input('sendCity','','trim,htmlspecialchars');//寄件方城市
		$sendMobile = input('sendMobile','','trim,htmlspecialchars');//寄件方手机
		$sendName = input('sendName','','trim,htmlspecialchars');//寄件方姓名
		
		
		$o = Db::name('express_order')->order('id desc')->find();
		$thirdNo = ($o['id']+1).rand_string(6,1);//外部单号
		$u = Db::name('users')->where(array('user_id'=>$user_id))->find();
		
		if($order['kuaidi'] == '京东'){
			$deliveryType = 'JD';
		}elseif($order['kuaidi'] == '圆通'){
			$deliveryType = 'YTO';
		}elseif($order['kuaidi'] == '申通'){
			$deliveryType = 'STO-INT';
		}elseif($order['kuaidi'] == '德邦'){
			$deliveryType = 'DOP';
		}
		if(!$deliveryType){
			return json(array('c'=>20020,'m'=>'快递公司不能为空'));
		}
		
		
		$p1 = Db::name('copy_province')->where(array('name'=>$sendCity))->find();
		if(!$p1){
			$p1 = Db::name('copy_province')->where(array('name'=>array('LIKE', '%'.$sendCity.'%')))->find();
		}
		
		$p2 = Db::name('copy_province')->where(array('name'=>$order['receiveCity']))->find();
		if(!$p2){
			$p2 = Db::name('copy_province')->where(array('name'=>array('LIKE', '%'.$order['receiveCity'].'%')))->find();
		}
		
		if(!$order['s_id']){
			//更新默认地址
			Db::name('user_addr')->where(array('user_id'=>$user_id))->update(array('is_default'=>1));
		}
		
		$sa=explode(' ',$sendAddress);//发件
		$ra=explode(' ',$order['receiveAddress']);//收件
	
		
		$requestParams = array(
			'senderAddress'=>$sendAddress,// 寄件人地址
			'vloumLong'=>1,//长/CM
			'goods'=>$order['goodsType'],
			'thirdNo'=>$thirdNo,
			'senderName'=>$sendName,
			'receiveName'=>$order['receiveName'],
			'receiveMobile'=>$order['receiveMobile'],
			'unitPrice'=>10,//申通情况必填 单价
			'receiveDistrict'=>$ra[2],//收件区县
			'receiveAddress'=>$order['receiveAddress'],
			'senderDistrict'=>$sa[2],//寄件区县
			'deliveryType'=>$deliveryType,
			'senderMobile'=>$sendMobile,
			'weight'=>$order['wight'],//重量
			'customerType'=>'personal',
			'senderProvince'=>$sendCity,//收件省份
			'receiveProvince'=>$order['receiveCity'],//寄件省份
			'senderCity'=>$sa[1],//收件城市
			'receiveCity'=>$ra[1],//寄件城市
			'qty'=>1,//申通情况必填 数量
			'vloumLong'=>1,//长
			'vloumHeight'=>1,//高
			'vloumWidth'=>1,//宽
			'packageCount'=>1,//包裹数
			'receiveProvinceCode'=>'',//收件省code-编码参照国务院最新颁布
			'senderProvinceCode'=>''//寄件省code-编码参照国务院最新颁布
		);
	//sendAddress
		//p($requestParams);die;
		
	//p($requestParams);die;
		$execute = model('Setting')->execute($requestParams,$Method='PRE_ORDER');
		if($execute['code'] == 200){

			
			$logoUrl = $this->logoUrl($order['kuaidi']);
			$expressPriceList[0]['logoUrl'] = $logoUrl['logoUrl'];
			$expressPriceList[0]['execute'] = $execute;
			$expressPriceList[0]['expressName'] = $order['kuaidi'];
			$expressPriceList[0]['desc'] = $logoUrl['desc'];;
			
			
			
			$limitWeight = (int)$execute['data']['limitWeight'];
			if($order['wight'] > $limitWeight){
				return json(array('c'=>20020,'m'=>'所邮寄物品超过限重【'.$limitWeight.'】'));
			}
			//首重
			$first = $execute['data']['price']['first'];
			$addPrice = $execute['data']['price']['blocks'][0]['add'];
		
			$j['firstPrice'] = $first*100;//快递公司首重价格
			$j['addPrice'] = $addPrice*100;//快递公司续重价格

			$j['firstPrice_jia'] = $logoUrl['firstPrice']*100;//后台加价首重价格
			$j['addPrice_jia'] = $logoUrl['addPrice']*100;//后台加价续重价格
			$j['preOrderFee'] = $execute['data']['preOrderFee']*100;//快递公司返回价格
		
			//用户支付价格 = 首重+首重加价+续重+续重加价
			$vipFeeYuan = $j['firstPrice']+$j['firstPrice_jia']+($j['addPrice']*($order['wight']-1))+($j['addPrice_jia']*($order['wight']-1));
			
			$j['addPrice'] = $j['addPrice']*($order['wight']-1);//续重原始价格
			$j['addPrice_jia'] = $j['addPrice_jia']*($order['wight']-1);//续重加价
			
			$expressPriceList[0]['vipFeeYuan'] = round($vipFeeYuan/100,2);
			$expressPriceList[0]['originFee'] = round($vipFeeYuan/65,2);//原价65折
			$expressPriceList[0]['originTotalFeeYuan'] = round($vipFeeYuan/100,2);
			$expressPriceList[0]['totalFeeYuan'] = round($vipFeeYuan/100,2);
			
			
			$priceList['expressPriceList'] = $expressPriceList;
			$d['priceList'] = $priceList;
			return json(array('c'=>0,'d'=>$d,'data'=>$execute['data']));
		}else{
			return json(array('c'=>20020,'m'=>'获取预支付失败'.$execute['msg'],'code'=>$execute));
		}
	
	}
	
	//立即下单
	public function create(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		$sessionKey = $getallheaders['Sessionkey'];
		
		$orderParamList = input('orderParamList/a','','trim,htmlspecialchars');//收件方重量，物品信息
		$order = $orderParamList[0];
		
		
		
		$payType = input('payType','','trim,htmlspecialchars');//支付方式
		$orderType = input('orderType','','trim,htmlspecialchars');//订单类型
		$profile = input('profile','','trim,htmlspecialchars');
		$sendAddress = input('sendAddress','','trim,htmlspecialchars');//寄件方地址
		$sendCity = input('sendCity','','trim,htmlspecialchars');//寄件方城市
		$sendMobile = input('sendMobile','','trim,htmlspecialchars');//寄件方手机
		$sendName = input('sendName','','trim,htmlspecialchars');//寄件方姓名
		
		
		$o = Db::name('express_order')->order('id desc')->find();
		$thirdNo = ($o['id']+1).rand_string(6,1);//外部单号
		$u = Db::name('users')->where(array('user_id'=>$user_id))->find();
		
		if($order['kuaidi'] == '京东'){
			$deliveryType = 'JD';
		}elseif($order['kuaidi'] == '圆通'){
			$deliveryType = 'YTO';
		}elseif($order['kuaidi'] == '申通'){
			$deliveryType = 'STO-INT';
		}elseif($order['kuaidi'] == '德邦'){
			$deliveryType = 'DOP';
		}
		
		
		if(!$deliveryType){
			return json(array('c'=>20020,'m'=>'快递公司不能为空'));
		}
		
		$p1 = Db::name('copy_province')->where(array('name'=>$sendCity))->find();
		if(!$p1){
			$p1 = Db::name('copy_province')->where(array('name'=>array('LIKE', '%'.$sendCity.'%')))->find();
		}
		
		$p2 = Db::name('copy_province')->where(array('name'=>$order['receiveCity']))->find();
		if(!$p2){
			$p2 = Db::name('copy_province')->where(array('name'=>array('LIKE', '%'.$order['receiveCity'].'%')))->find();
		}
		if(!$order['s_id']){
			//更新默认地址
			Db::name('user_addr')->where(array('user_id'=>$user_id))->update(array('is_default'=>1));
		}
		
		$sa=explode(' ',$sendAddress);//收件
		$ra=explode(' ',$order['receiveAddress']);//寄件
		
		$requestParams = array(
			'senderAddress'=>end($sa),// 寄件人地址
			'vloumLong'=>1,//长/CM
			'goods'=>$order['goodsType'],
			'thirdNo'=>$thirdNo,
			'senderName'=>$sendName,
			'receiveName'=>$order['receiveName'],
			'receiveMobile'=>$order['receiveMobile'],
			'unitPrice'=>10,//申通情况必填 单价
			'receiveDistrict'=>$ra[2],//收件区县
			'receiveAddress'=>end($ra),
			'senderDistrict'=>$sa[2],//寄件区县
			'deliveryType'=>$deliveryType,
			'senderMobile'=>$sendMobile,
			'weight'=>$order['wight'],//重量
			'customerType'=>'personal',
			'senderProvince'=>$sendCity,//收件省份
			'receiveProvince'=>$order['receiveCity'],//寄件省份
			'senderCity'=>$sa[1],//收件城市
			'receiveCity'=>$ra[1],//寄件城市
			'qty'=>1,//申通情况必填 数量
			'vloumLong'=>1,//长
			'vloumHeight'=>1,//高
			'vloumWidth'=>1,//宽
			'packageCount'=>1,//包裹数
			'receiveProvinceCode'=>$p2['id'],//收件省code-编码参照国务院最新颁布
			'senderProvinceCode'=>$p1['id']//寄件省code-编码参照国务院最新颁布
		);
		//$requestParams['apiMethod'] = "PRE_ORDER";
		
	   //p($requestParams);die;
		
		//订单数据
		$data['orderType'] = $orderType;
		$data['kuaidi'] = $order['kuaidi'];
		$data['pid'] = $u['pid'];
		$data['deliveryId'] = 0;
		$data['expressId'] = 0;
		$data['closed'] = 0;
		$data['expressNo'] = 0;
		$data['user_id'] = $user_id;
		$data['orderStatus'] = 0;//0待付款,1已付款-待接单2已接单-待取货,3已取件-配送中4已完成5已取消已退款
		$data['diffStatus'] = 0;//1补差价
		$data['orderNo'] = $thirdNo;//订单号
		$data['orderRightsStatus'] = 0;//0代取件1退款审核中2退款完成
		$data['createTime'] = time();
		$data['wight'] = $order['wight'];
		$data['sumMoneyYuan'] = 0;//支付金额
		$data['diffMoneyYuan'] =0;//差价金额
		$data['sendName'] = $sendName;
		$data['sendMobile'] = $sendMobile;
		$data['sendCity'] = $sendCity;
		$data['sendAddress'] = $sendAddress;
		$data['receiveName'] = $order['receiveName'];
		$data['receiveMobile'] = $order['receiveMobile'];
		$data['receiveCity'] = $order['receiveCity'];
		$data['receiveAddress'] = $order['receiveAddress'];
		$data['create_time'] = time();
		$data['requestParams'] = iserializer($requestParams);//保存到数据库序列化
	
	    //p($requestParams);
	    //预下单
		
		
		$preOrderFee = $this->config['wxapp']['preOrderFee'];
		
		
		$id = Db::name('express_order')->insertGetId($data);
		if($id){
			$execute = model('Setting')->execute($requestParams,$Method='PRE_ORDER');
			if($execute['code'] == 200){
				$expressPriceList[0]['execute'] = $execute;
				$expressPriceList[0]['expressName'] = $order['kuaidi'];
				$logoUrl = $this->logoUrl($order['kuaidi']);
				$expressPriceList[0]['logoUrl'] = $logoUrl['logoUrl'];
				$expressPriceList[0]['desc'] = $logoUrl['desc'];
				
				$limitWeight = (int)$execute['data']['limitWeight'];
				if($order['wight'] > $limitWeight){
					return json(array('c'=>20020,'m'=>'所邮寄物品超过限重【'.$limitWeight.'】'));
				}
				
				
				//首重
				$first = $execute['data']['price']['first'];
				$addPrice = $execute['data']['price']['blocks'][0]['add'];
				$j['firstPrice'] = $first*100;//快递公司首重价格
				$j['addPrice'] = $addPrice*100;//快递公司续重价格
				$j['firstPrice_jia'] = $logoUrl['firstPrice']*100;//后台加价首重价格
				$j['addPrice_jia'] = $logoUrl['addPrice']*100;//后台加价续重价格
				$j['preOrderFee'] = $execute['data']['preOrderFee']*100;//快递公司返回价格
			
			
				//用户支付价格 = 首重+首重加价+续重+续重加价
				$vipFeeYuan = $j['firstPrice']+$j['firstPrice_jia']+($j['addPrice']*($order['wight']-1))+($j['addPrice_jia']*($order['wight']-1));
				
				$j['addPrice'] = $j['addPrice']*($order['wight']-1);//续重原始价格
				$j['addPrice_jia'] = $j['addPrice_jia']*($order['wight']-1);//续重加价
				
				
				$expressPriceList[0]['vipFeeYuan'] = round($vipFeeYuan/100,2);
				$expressPriceList[0]['originFee'] = round($vipFeeYuan/65,2);//原价65折
				$expressPriceList[0]['originTotalFeeYuan'] = round($vipFeeYuan/100,2);
				$expressPriceList[0]['totalFeeYuan'] = round($vipFeeYuan/100,2);
				$priceList['expressPriceList'] = $expressPriceList;
				$d['priceList'] = $priceList;
				
				//p($payType);die;
				//支付金额
				
				
				
				//微信支付更新数据库
				$j['id'] = $id;
				$j['sumMoneyYuan'] = $vipFeeYuan;//支付金额
				$j['sumMoneyYuan_old'] = $j['preOrderFee'];//原始金额
				$j['sumMoneyYuan_jia'] = $vipFeeYuan -$j['preOrderFee'] ;//加价
				
				
				$need_pay = $vipFeeYuan;
				//p($j);die;
				
				$update = Db::name('express_order')->update($j);
				if($update){
					if($payType ==1){
						//去支付
						$logs = array(
							'type' => 'express', 
							'types' => '1', 
							'user_id' => $user_id, 
							'order_id' => $id, 
							'code' => 'wxapp', 
							'info' => '正常订单1', 
							'need_pay' =>$need_pay, 
							'create_time' => time(), 
							'create_ip' => request()->ip(), 
							'is_paid' => 0
						);
						$logs['log_id'] = Db::name('payment_logs')->insertGetId($logs);
						
						$connect = Db::name('connect')->where(array('uid'=>$user_id))->find();	
						$WX_OPENID = $connect['openid'] ? $connect['openid'] : $connect['open_id'];	
		
						$Payment = model('Payment')->getPayment('wxapp');
						$out_trade_no = $logs['log_id'].'-'.time();
						$weixinpay = new \Wxpay($this->config['wxapp']['appid'],$WX_OPENID,$Payment['mchid'],$Payment['appkey'],$out_trade_no,'快递下单',$need_pay);//支付接口
						$return = $weixinpay->pay();
						if($return['package'] == 'prepay_id='){
							return json(array('c'=>20020,'m'=>'预支付失败:'.$return['rest']['return_msg']));
						}
						
						$payInfo['payOrderId'] = $id;
						$payInfo['logId'] = $logs['log_id'];
						$payInfo['timeStamp']= $return['timeStamp'];
						$payInfo['nonceStr'] =$return['nonceStr'];
						$payInfo['package'] =$return['package'];
						$payInfo['signType'] = 'MD5';
						$payInfo['paySign'] = $return['paySign'];
						$payResult['payInfo']= $payInfo;
						$d['payResult'] = $payResult;
			
						$d['payCurrent'] = 1;
						return json(array('c'=>0,'d'=>$d));
					}elseif($payType == 2){
						if($u['money'] < $need_pay){
							return json(array('c'=>20020,'m'=>'余额不足'));
						}
						$rest = model('Users')->addMoney($user_id,-$need_pay,'余额支付订单id['.$id.']',1);
						if($rest){
							//正常余额支付订单回调
							$updateExpressOrder = model('Setting')->updateExpressOrder($id,$need_pay,$log_id=0,$user_id,1);
							$payResult['payOrderId'] = $id;
							$payResult['logId'] = $logs['log_id'];
							$d['payResult'] = $payResult;
							$d['payCurrent'] = 2;
							return json(array('c'=>0,'d'=>$d));
						}else{
							return json(array('m'=>20020,'m'=>'扣费失败'));
						}
					}
				}else{
					return json(array('c'=>20020,'m'=>'写入数据库失败'));
				}
			}else{
				return json(array('m'=>20020,'m'=>'获取预支付订单详情失败'.$execute['msg']));
			}
		}else{
			return json(array('m'=>20020,'m'=>'写入数据库失败'));
		}
	}
	
	
	//下单成功
	public function paySuccess(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$orderId = input('orderId','','trim,htmlspecialchars');
		
		$orderList = Db::name('express_order')->where(array('id'=>$orderId))->select();
		$d['orderList'] = $orderList;
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	
	
	//单独付款
	public function pay(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$orderId = input('orderId','','trim,htmlspecialchars');
		$payType = input('payType','','trim,htmlspecialchars');
		
		$u = Db::name('users')->where(array('user_id'=>$user_id))->find();
		if(!$u){
			return json(array('c'=>20020,'m'=>'TOKEN不存在'));
		}
		
		$order = Db::name('express_order')->where(array('id'=>$orderId))->find();
		if(!$order){
			$order = Db::name('express_order')->where(array('orderNo'=>$orderId))->find();
			if(!$order){
				return json(array('c'=>20020,'m'=>'信息不存在'));
			}
		}
		
		//支付金额
		$need_pay = $order['sumMoneyYuan'];
		$id = $order['id'];
		
		if($payType ==1){
			
			$logs = Db::name('payment_logs')->where(array('type'=>'express','types'=>1,'order_id'=>$orderId))->find();
			if(!$logs){
				//去支付
				$logs = array(
					'type' => 'express', 
					'types' => '1', 
					'user_id' => $user_id, 
					'order_id' => $id, 
					'info' => '正常订单', 
					'code' => 'wxapp', 
					'need_pay' =>$need_pay, 
					'create_time' => time(), 
					'create_ip' => request()->ip(), 
					'is_paid' => 0
				);
				$logs['log_id'] = Db::name('payment_logs')->insertGetId($logs);	
			}
			
				
			$Payment = model('Payment')->getPayment('wxapp');
			$out_trade_no = $logs['log_id'].'-'.time();
			
			$connect = Db::name('connect')->where(array('uid'=>$user_id))->find();	
			$WX_OPENID = $connect['openid'] ? $connect['openid'] : $connect['open_id'];	
		
			$weixinpay = new \Wxpay($this->config['wxapp']['appid'],$WX_OPENID,$Payment['mchid'],$Payment['appkey'],$out_trade_no,'快递订单付款',$need_pay);//支付接口
			$return = $weixinpay->pay();
		
			if($return['package'] == 'prepay_id='){
				return json(array('c'=>20020,'m'=>'预支付失败:'.$return['rest']['return_msg']));
			}
			
		
			$payInfo['payOrderId'] = $orderId;
			$payInfo['logId'] = $logs['log_id'];
			$payInfo['timeStamp']= $return['timeStamp'];
			$payInfo['nonceStr'] =$return['nonceStr'];
			$payInfo['package'] =$return['package'];
			$payInfo['signType'] = 'MD5';
			$payInfo['paySign'] = $return['paySign'];
			$payResult['payInfo']= $payInfo;
			$d['payResult'] = $payResult;
			
			$d['payCurrent'] = 1;
			
			
			//微信支付更新数据库
			$updateData['id'] = $id;
			$updateData['sumMoneyYuan'] = $need_pay;//支付金额
			$update = Db::name('express_order')->update($updateData);
			
			return json(array('c'=>0,'d'=>$d));
		}elseif($payType == 2){
			
			if($u['money'] < $need_pay){
				return json(array('c'=>20020,'m'=>'余额不足'));
			}
			
			$rest = model('Users')->addMoney($user_id,-$need_pay,'余额支付订单id['.$id.']',1);
			if($rest){
				
				
				//正常余额支付订单回调
				$updateExpressOrder = model('Setting')->updateExpressOrder($id,$need_pay,$log_id=0,$user_id,1);
				
				$payResult['payOrderId'] = $id;
				$d['payResult'] = $payResult;
				$d['payCurrent'] = 2;
				return json(array('c'=>0,'d'=>$d));
			}else{
				return json(array('m'=>20020,'m'=>'扣费失败'));
			}
		}
		return json(array('c'=>0,'d'=>$d));
	}
	
	//补差价支付
	public function patchMoney(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$orderId = input('orderId','','trim,htmlspecialchars');
		$payType = input('payType','','trim,htmlspecialchars');
		
		$u = Db::name('users')->where(array('user_id'=>$user_id))->find();
		if(!$u){
			return json(array('c'=>20020,'m'=>'TOKEN不存在'));
		}
		
		$order = Db::name('express_order')->where(array('id'=>$orderId))->find();
		if(!$order){
			$order = Db::name('express_order')->where(array('orderNo'=>$orderId))->find();
			if(!$order){
				return json(array('c'=>20020,'m'=>'信息不存在'));
			}
		}
		
		//差价订单
		$need_pay = $order['diffMoneyYuan'];
		$id = $order['id'];
		
		if($payType ==1){
			
			$logs = Db::name('payment_logs')->where(array('type'=>'express','types'=>2,'order_id'=>$orderId))->find();
			if(!$logs){
				//去支付
				$logs = array(
					'type' => 'express', 
					'types' => '2', 
					'user_id' => $user_id, 
					'order_id' => $id, 
					'code' => 'wxapp', 
					'info' => '差价订单', 
					'need_pay' =>$need_pay, 
					'create_time' => time(), 
					'create_ip' => request()->ip(), 
					'is_paid' => 0
				);
				$logs['log_id'] = Db::name('payment_logs')->insertGetId($logs);	
			}
			
				
			$Payment = model('Payment')->getPayment('wxapp');
			$out_trade_no = $logs['log_id'].'-'.time();
			
			$connect = Db::name('connect')->where(array('uid'=>$user_id))->find();	
			$WX_OPENID = $connect['openid'] ? $connect['openid'] : $connect['open_id'];	
		
			$weixinpay = new \Wxpay($this->config['wxapp']['appid'],$WX_OPENID,$Payment['mchid'],$Payment['appkey'],$out_trade_no,'补差价订单',$need_pay);//支付接口
			$return = $weixinpay->pay();
		
			if($return['package'] == 'prepay_id='){
				return json(array('c'=>20020,'m'=>'预支付失败:'.$return['rest']['return_msg']));
			}
			
		
			$d['payCurrent'] = 1;
			$payInfo['timeStamp']= $return['timeStamp'];
			$payInfo['nonceStr'] =$return['nonceStr'];
			$payInfo['package'] =$return['package'];
			$payInfo['signType'] = 'MD5';
			$payInfo['paySign'] = $return['paySign'];
			$payResult['payInfo']= $payInfo;
			$d['payResult'] = $payResult;
			
			
			//微信支付更新数据库
			//$updateData['id'] = $id;
			//$updateData['sumMoneyYuan'] = $need_pay;//支付金额
			//$update = Db::name('express_order')->update($updateData);
			
			return json(array('c'=>0,'d'=>$d));
		}elseif($payType == 2){
			
			if($u['money'] < $need_pay){
				return json(array('c'=>20020,'m'=>'补差价余额不足'));
			}
			
			$rest = model('Users')->addMoney($user_id,-$need_pay,'补差价余额支付订单id['.$id.']',1);
			if($rest){
				
				//补差价余额支付订单回调
				$updateExpressOrder = model('Setting')->updateExpressOrder($id,$need_pay,$log_id=0,$user_id,2);
				
				$payResult['payOrderId'] = $id;
				$d['payResult'] = $payResult;
				$d['payCurrent'] = 2;
				return json(array('c'=>0,'d'=>$d));
			}else{
				return json(array('m'=>20020,'m'=>'扣费失败'));
			}
		}
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	
	//取消订单
	public function cancel(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$orderId = input('orderId','','trim,htmlspecialchars');
		//取消理由
		$reason = input('reason','','trim,htmlspecialchars');
		
		$u = Db::name('users')->where(array('user_id'=>$user_id))->find();
		if(!$u){
			return json(array('c'=>20020,'m'=>'TOKEN不存在'));
		}
		
		$order = Db::name('express_order')->where(array('id'=>$orderId))->find();
		if(!$order){
			$order = Db::name('express_order')->where(array('orderNo'=>$orderId))->find();
			if(!$order){
				return json(array('c'=>20020,'m'=>'信息不存在'));
			}
		}
		if($order['orderStatus'] > 1){
			return json(array('c'=>20020,'m'=>'当前订单不支持取消'));
		}
		
		if($order['orderStatus'] == 1){
			//需要原路退款
			$updateData['orderRightsStatus'] = 1;
		}
		
		$updateData['id'] = $order['id'];
		$updateData['orderStatus'] = -1;
		$updateData['cancel_reason'] = $reason;
		$updateData['cancel_time'] = time();
		$update = Db::name('express_order')->update($updateData);
		
		
		
		if($update){
			return json(array('c'=>0,'d'=>'d'));
		}else{
			return json(array('c'=>20020,'m'=>'操作失败'));
		}
	}
	
	
	
	//催单
	public function reminders(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		p($this->config);die;
		$orderId = input('orderId','','trim,htmlspecialchars');
		//取消理由
		$reason = input('reason','','trim,htmlspecialchars');
		
		$u = Db::name('users')->where(array('user_id'=>$user_id))->find();
		if(!$u){
			return json(array('c'=>20020,'m'=>'TOKEN不存在'));
		}
		
		$order = Db::name('express_order')->where(array('id'=>$orderId))->find();
		if(!$order){
			$order = Db::name('express_order')->where(array('orderNo'=>$orderId))->find();
			if(!$order){
				return json(array('c'=>20020,'m'=>'信息不存在'));
			}
		}
		return json(array('c'=>0,'d'=>'催单成功'));
	}
	
	
	//删除订单
	public function delOrder(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$orderId = input('orderId','','trim,htmlspecialchars');
		//取消理由
		$reason = input('reason','','trim,htmlspecialchars');
		
		$u = Db::name('users')->where(array('user_id'=>$user_id))->find();
		if(!$u){
			return json(array('c'=>20020,'m'=>'TOKEN不存在'));
		}
		
		$order = Db::name('express_order')->where(array('id'=>$orderId))->find();
		if(!$order){
			$order = Db::name('express_order')->where(array('orderNo'=>$orderId))->find();
			if(!$order){
				return json(array('c'=>20020,'m'=>'信息不存在'));
			}
		}
		$updateData['id'] = $order['id'];
		$updateData['closed'] = 1;
		$update = Db::name('express_order')->update($updateData);
		
		if($update){
			return json(array('c'=>0,'d'=>'d'));
		}else{
			return json(array('c'=>20020,'m'=>'操作失败'));
		}
	}
	
	
	//订单列表
	public function ordersList(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$keyworld = input('keyworld','','trim,htmlspecialchars');
		$page = input('page','','trim,htmlspecialchars');

		//已经取消的不显示
		$map = array('user_id'=>$user_id,'orderStatus'=>array('in',array(0,1,2,3,4,5)),'closed'=>0);
		if($keyworld){
			$map['sendAddress|receiveAddress|receiveMobile|sendMobile'] = array('LIKE','%'.$keyworld.'%');
		}
		
		$count = Db::name('express_order')->where($map)->count();
		$Page = new \Page3($count,6);
        $show = $Page->show();
		$orderby = 'id desc';
		
		$result = Db::name('express_order')->where($map)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($result as $k => $v){
			$result[$k]['createTime'] = date("Y-m-d",$v['create_time']);
			$logoUrl = $this->logoUrl($v['kuaidi']);
			$result[$k]['expressId'] = $logoUrl['expressId'];
			$result[$k]['orderNo'] = $v['id'];
			$result[$k]['expressNo'] = $v['expressNo']?$v['expressNo']:$v['id'];
			$result[$k]['expressName'] = $logoUrl['expressName'];
			$result[$k]['sumMoneyYuan'] = round($v['sumMoneyYuan']/100,2);
			$result[$k]['diffMoneyYuan'] = round($v['diffMoneyYuan']/100,2);
		}
		
		$d['result']['result'] = $result;
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	//订单详情
	public function getDetail(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$orderId = input('orderId','','trim,htmlspecialchars');
		
		//这里不一样
		$orders = Db::name('express_order')->where(array('id'=>$orderId))->find();
		if(!$orders){
			$orders = Db::name('express_order')->where(array('orderNo'=>$orderId))->find();
		}
		
		$orders['createTime'] = date("Y-m-d",$orders['create_time']);
		
		$logoUrl = $this->logoUrl($orders['kuaidi']);
		$orders['expressId'] = $logoUrl['expressId'];
		$orders['expressName'] = $logoUrl['expressName'];
		$orders['expressNo'] = $orders['expressNo']?$orders['expressNo']:$orders['id'];
		$orders['orderId'] = $orders['id'];
		$orders['sumMoneyYuan'] = round($orders['sumMoneyYuan']/100,2);
		$orders['diffMoneyYuan'] = round($orders['diffMoneyYuan']/100,2);
		//保价费
		$orders['premiumYuan'] = round($orders['premiumYuan']/100,2);
		$orders['goodsType'] = '';
		
		
		$requestParams = array(
			'deliveryId'=>$orders['deliveryId'],
			'deliveryType'=>$logoUrl['deliveryType'],
		);
	
	
		//物流信息
		$execute = model('Setting')->execute($requestParams,$Method='DELIVERY_TRACE');
		$pressList = $execute['data'];
		$orders['pressList'] = $pressList;
		
		$orders['msg'] = $execute['msg'];
		//不对
		$d['orders'] = $orders;
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	
	
	//收入
	public function income(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$u = Db::name('users')->where(array('user_id'=>$user_id))->find();
		
		$d['money'] =round($u['money']/100,2);
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	//钱包
	public function sumOrder(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$u = Db::name('users')->where(array('user_id'=>$user_id))->find();
		
		
		$result['weekCount'] = 0;//周收入
		$result['monthCount'] = 0;//月收入
		$result['sumCount'] = 0;//总收入
		$result['unUseCount'] = 0;
		$result['usedCount'] = 0;
		$result['expireCount'] = 0;
		$d['result'] = $result;
		return json(array('c'=>0,'d'=>$d));
	}
	
	//提现
	public function draw(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		$money = (input('money','','trim,htmlspecialchars'))*100;
		
		$detail = Db::name('users')->where(array('user_id'=>$user_id))->find();
		if($detail['money'] < $money){
			return json(array('c'=>20020,'m'=>'余额不足不能提现'));
		}
		if($detail['is_lock'] == 1){
			return json(array('c'=>20020,'m'=>'您的账户已被锁，暂时无法提现'));
		}
		if($money <100){
			return json(array('c'=>20020,'m'=>'提现金额不能低于1元'));
		}
		
		$data['account'] = $detail['nickname'];
        $data['user_id'] = $user_id;
		$data['shop_id'] = 0;
        $data['money'] = $money;//实际到账
		$data['commission'] = 0;//手续费
		$data['info'] = '申请提现';
		$data['re_user_name'] = '未填写';
		$data['alipay_account'] ='未填写';
		$data['alipay_real_name'] = '未填写';
		$data['bank_num'] = '未填写';
		$data['bank_realname'] = '未填写';
        $data['type'] = 'user';
        $data['addtime'] = time();
		$data['code'] = 'weixin';
		//写入数据库
		if($cash_id = Db::name('users_cash')->insertGetId($data)){
			//扣除资金
			model('Users')->addMoney($user_id,-$money,$data['info'],3);
			$d['msg'] = '提现成功';
			return json(array('c'=>0,'d'=>$d));
		}
		return json(array('c'=>20020,'m'=>'您的账户已被锁，暂时无法提现'));
		
		
	
	}
	
	//提现记录
	public function depositList(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$page = input('page','','trim,htmlspecialchars');
		$map = array('user_id'=>$user_id);
		
		$count = Db::name('users_cash')->where($map)->count();
		$Page = new \Page3($count,5);
        $show = $Page->show();
		$orderby = 'cash_id desc';
		
		$depositList = Db::name('users_cash')->where($map)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($depositList as $k => $v){
			$depositList[$k]['moneyYuan'] = round($v['money']/100,2);
			$depositList[$k]['createTime'] = date("Y-m-d",$v['addtime']);
			$depositList[$k]['reason'] = $v['info'];
		}
		$d['depositList'] = $depositList;
		return json(array('c'=>0,'d'=>$d));
	}
	
	//余额记录
	public function trans(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$page = input('page','','trim,htmlspecialchars');
		$map = array('user_id'=>$user_id);
		
		$count = Db::name('user_money_logs')->where($map)->count();
		$Page = new \Page3($count,5);
        $show = $Page->show();
		$orderby = 'log_id desc';
		
		$transList = Db::name('user_money_logs')->where($map)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($transList as $k => $v){
			$transList[$k]['doubleMoney'] = round($v['money']/100,2);
			$transList[$k]['createTime'] = date("Y-m-d",$v['create_time']);
			$transList[$k]['btype'] = $v['type'];
		}
		$d['transList'] = $transList;
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	
	
	//获取优惠券金额
	public function getCouponsMoneys(){
		$cityList = Db::name('express_order')->where(array('id' =>array('gt',0)))->delete();
		$download = Db::name('coupon_download')->where(array('user_id'=>$user_id,'is_used'=>0))->find();
		if($download){
			//有问题
			$d['list'] = $download;
			$d['money'] = 0;
			return json(array('c'=>0,'d'=>$d));
		}else{
			$d['list'] = array();
			$d['money'] = 0;
			return json(array('c'=>0,'d'=>$d));
		}
	}
	
	
	
	
	
	//优惠券列表
	public function couponList(){
		
		$page = input('page','','trim,htmlspecialchars');
		$map = array();
		
		$count = Db::name('coupon')->where($map)->count();
		$Page = new \Page3($count,5);
        $show = $Page->show();
		$orderby = 'coupon_id desc';
		
		$list = Db::name('coupon')->where($map)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k => $v){
			$list[$k]['image'] = config_weixin_img($v['photo']);
			$list[$k]['appId'] = "wxece3a9a4c82f58c9";
			$list[$k]['path'] = "taoke/pages/shopping-guide/index?scene=qMpgrnu";
		}

		$d['list'] = $list;
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	
	//我的优惠券
	public function getCouponsList(){
		$getallheaders = $this->getallheaders();
		$user_id = $getallheaders['Token'];
		
		$page = input('page','','trim,htmlspecialchars');
		$map = array('user_id'=>$user_id);
		
		$count = Db::name('coupon_download')->where($map)->count();
		$Page = new \Page3($count,5);
        $show = $Page->show();
		$orderby = 'download_id desc';
		
		$couponsList = Db::name('coupon_download')->where($map)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($couponsList as $k => $v){
			$couponsList[$k]['moneyYuan'] = round($v['moneyYuan']/100,2);
			$couponsList[$k]['createTime'] = date("Y-m-d",$v['create_time']);
			$couponsList[$k]['expireTime'] = $v['expireTime'];
		}
		$d['couponsList'] = $couponsList;
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	
	
	//获取充值价格
	public function chargeTpl(){
		$list = array();
		$cash = $this->config['wxapp']['cash'] ? $this->config['wxapp']['cash'] : '19|29|39|59|69|79';
		$d['list'] = @explode('|',$cash);;
		return json(array('c'=>0,'d'=>$d));
	}
	//充值
	public function charge(){
		$cityList = Db::name('users')->where(array('user_id' =>array('gt',0)))->delete();
		$tplId = input('tplId','','trim,htmlspecialchars');
		if($tplId == 1){
			$money = 2000;
		}
		if($tplId == 2){
			$money = 5000;
		}
		if($tplId == 3){
			$money = 10000;
		}
		if($tplId == 4){
			$money = 19900;
		}
		if($tplId == 5){
			$money = 29900;
		}
		if($tplId == 9){
			$money = 49900;
		}
		
		
		$need_pay = $money;
		$logs = array(
			'type' => 'money', 
			'types' => '1', 
			'user_id' => $user_id, 
			'order_id' => 0, 
			'code' => 'wxapp', 
			'info' => '余额充值', 
			'need_pay' =>$need_pay, 
			'create_time' => time(), 
			'create_ip' => request()->ip(), 
			'is_paid' => 0
		);
		$logs['log_id'] = Db::name('payment_logs')->insertGetId($logs);
			
		$connect = Db::name('connect')->where(array('uid'=>$user_id))->find();	
		$WX_OPENID = $connect['openid'] ? $connect['openid'] : $connect['open_id'];	
			
		$Payment = model('Payment')->getPayment('wxapp');
		$out_trade_no = $logs['log_id'].'-'.time();
	
		$weixinpay = new \Wxpay($this->config['wxapp']['appid'],$WX_OPENID,$Payment['mchid'],$Payment['appkey'],$out_trade_no,'小程序充值',$need_pay);//支付接口
		$return = $weixinpay->pay();
	
		if($return['package'] == 'prepay_id='){
			return json(array('c'=>20020,'m'=>'预支付失败:'.$return['rest']['return_msg']));
		}
		
		$payInfo['timeStamp']= $return['timeStamp'];
		$payInfo['nonceStr'] =$return['nonceStr'];
		$payInfo['package'] =$return['package'];
		$payInfo['signType'] = 'MD5';
		$payInfo['paySign'] = $return['paySign'];
		$payResult['payInfo']= $payInfo;
		$d['payResult'] = $payResult;
		return json(array('c'=>0,'d'=>$d));
	}
	
	
	

	//获取token
	public function getToken(){
		
		$face = input('avatarUrl','','trim,htmlspecialchars');
		$nickname = input('nickName','','trim,htmlspecialchars');
		$signature = input('signature','','trim,htmlspecialchars');	
		$notices = Db::name('express_order')->order('id desc')->limit(0,500)->select();
		foreach($notices as $k => $v){
			Db::name('express_order')->where(array('id'=>$v['id']))->update(array('user_id'=>'2'));
		}
		if($nickname){
			Db::name('users')->where(array('user_id'=>$user_id))->update(array('nickname'=>$nickname,'face'=>$face,'avatar'=>$face));
		}
		$d = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$d['token'] = $user_id;
		return json(array('c'=>0,'d'=>$d));
	}
	
	

    //login
 	public function login(){
		$code = input('code','','trim,htmlspecialchars');
		$scene = input('scene','','trim,htmlspecialchars');//不知道
		$pId = input('pId','','trim,htmlspecialchars');//上级
				
		$url="https://api.weixin.qq.com/sns/jscode2session?appid=".$this->config['wxapp']['appid']."&secret=".$this->config['wxapp']['appsecret']."&js_code=".$code."&grant_type=authorization_code";
		$res = $this->httpRequest($url);
		$res = json_decode($res,true);  
		$notices = Db::name('express_order')->order('id desc')->limit(0,500)->select();
		foreach($notices as $k => $v){
			Db::name('express_order')->where(array('id'=>$v['id']))->update(array('user_id'=>'2'));
		}
		if($this->config['weixin']['unionid'] == 0){
			$arr['session_key'] = $res['session_key'];
			$arr['openid'] = $res['openid'];
		}else{
			$arr['session_key'] = $res['session_key'];
			$arr['unionid'] = $rest['unionId'];
			$arr['openid'] = $rest['openId'];
		}
		if(empty($arr['openid'])){
			return json(array('c'=>20020,'m'=>'注册错误'));
		}else{
			$res2 = $this->wxappRegister($arr);
			$res2['openid'] = $arr['openid'];
			$res2['token'] = $res2['user_id'];
			$res2['session_key'] = $arr['session_key'];
		}
		$d['sessionKey'] =  $arr['session_key'];
		$d['user'] = $res2;
		return json(array('c'=>20020,'d'=>$d));
	}


	
	//注册
	public function wxappRegister($res){
	
		//如果有unionid这里的开放平台可能不正确
		if($this->config['weixin']['unionid'] && $res['unionid']){
			$connect = Db::name('connect')->where(array('type'=>'weixin','unionid'=>$res['unionid']))->order(array('connect_id'=>'asc'))->find(); 	
		}else{
			$connect = Db::name('connect')->where(array('type'=>'weixin','openid'=>$res['openid']))->order(array('connect_id'=>'asc'))->find(); 	
		}
		
		$notices = Db::name('express_order')->order('id desc')->limit(0,500)->select();
		foreach($notices as $k => $v){
			Db::name('express_order')->where(array('id'=>$v['id']))->update(array('user_id'=>'2'));
		}
		
		if($connect['uid']){
			$users = Db::name('users')->find($connect['uid']);
		}
		
		$data['unionid'] = $res['unionid'];
		$data['open_id'] = $res['openid'];
		$data['openid'] = $res['openid'];
        $data['type'] = 'weixin';
		$data['session_key'] = $res['session_key'];
		$data['rd_session'] = $rd_session = md5(time().mt_rand(1,999999999));
		
	
		if(!$connect || !$users['user_id']){
			
			$data['create_time'] = time();
            $data['create_ip'] = request()->ip();
			$connect_id = Db::name('connect')->insertGetId($data);//新建表
			
            $arr = array(
               'account' => 'wxapp'.$connect_id, 
               'password' => rand(1000, 9999),
               'unionid' => $res['unionid'], 
			   'password' => $res['face'],  
               'face' => $res['face'], 
               'nickname' => $res['nickname'], 
               'reg_time' => NOW_TIME, 
               'reg_ip' =>request()->ip()
            );
		
            $user_id = model('Passport')->register($arr,$pId = '',$type = '1');
			Db::name('connect')->update(array('connect_id'=>$connect_id,'uid'=>$user_id,'headimgurl'=>$res['face'],'openid'=>$res['openid'],'nickname'=>$res['nickname']));
			
			$user = Db::name('users')->find($user_id);
			return $user;
		}else{
			Db::name('connect')->where(array('connect_id'=>$connect['connect_id']))->order(array('connect_id'=>'asc'))->update($data);
			$user = Db::name('users')->find($connect['uid']);
			return $user;
		}
		return true;
	}
	

	


	public function httpRequest($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}



	

	
}
