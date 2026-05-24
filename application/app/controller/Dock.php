<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Dock extends Base{


	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->host = $this->config['site']['host'];
		$this->curl = new \Curl();
    }
	
	
	public function getCompanyTypes(){
        return array(
			'sto' => '申通快递',
			'zt' => '中通快递',
			'yt' => '圆通速递',
			'yd' => '韵达快递',
			'ys' => '优速快递'
		);
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
	
	public function getUserId(){
		$token = input('token','','trim,htmlspecialchars');
		$user_id = Db::name('users')->where(array('token'=>$token))->value('user_id');
		return (int)$user_id;
	}
	
	//订单列表
	public function OrderList(){
		
		$page = input('page','','trim,htmlspecialchars');
		$limit = input('limit','','trim,htmlspecialchars');
		$status = input('status','','trim,htmlspecialchars');
		$map['closed'] = 0;
		if($status==2){
			$map['orderStatus'] =2;
		}
		if($status==3){
			$map['orderStatus'] =3;
		}
		if($status==4){
			$map['orderStatus'] =4;
		}
		if($status==5){
			$map['orderStatus'] =-1;
		}
		if($status==6){
			$map['orderStatus'] =5;
		}
		if($page == 1){
			$star = 0;
			$end = $limit;
		}else{
			$star = $limit*$page;
			$end = $limit;
		}
		$count = Db::name('express_order')->where($map)->count();	
		$Page = new \Page3($count,10);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('express_order')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();	
			foreach($list as $k=>$v){
				$logoUrl = model('ExpressOrder')->logoUrl($v['kuaidi'],$v['user_id'],$v['type']);
				$u = Db::name('users')->where(array('user_id'=>$v['user_id']))->find();
				$list[$k]['Avatar'] = config_weixin_img($u['face']);
				$list[$k]['nickname'] = $u['nickname'];
				$list[$k]['out_trade_no'] = $v['id'];
				$list[$k]['kuaidi_logo'] = config_weixin_img($logoUrl['photo']);
				$list[$k]['kuaidi_title'] = $v['kuaidi'];
				$list[$k]['yundan'] = $logoUrl['deliveryType'].rand_string(12,1);
				$list[$k]['sender_city'] = $v['sendCity'] ? $v['sendCity'] : $v['senderCity'];
				$list[$k]['sender_name'] = $v['sendName'];
				$list[$k]['receive_city'] = $v['receiveCity'];
				$list[$k]['receive_name'] = $v['receiveName'];
				$list[$k]['price'] = round($v['sumMoneyYuan']/100,2);
				$list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
				$order_info['sender_region'] = $v['senderCity'] ? $v['senderCity'] : $v['sendCity'];
				$order_info['receive_region'] = $v['receiveCity'];
				$list[$k]['order_info'] = $order_info;
				if($v['orderStatus'] == 2){
					$status = 2;
				}
				if($v['orderStatus'] == 3){
					$status = 3;
				}
				if($v['orderStatus'] == 4){
					$status = 4;
				}
				if($v['orderStatus'] == 5){
					$status = 6;
				}
				if($v['orderStatus'] == -1){
					$status = 5;
				}
				$list[$k]['senderCity'] = $v['sendCity'] ? $v['sendCity'] : $v['senderCity'];
				$list[$k]['status'] = $status;
				$list[$k]['addtime_text'] = date('Y-m-d H:i:s',$v['create_time']);
			}
		}	
		$data['data'] = $list;
		$data['total'] = $count;
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$data));
	}
	
	
	public function weiboduijiang(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$code = input('code','','trim');
		$cc = Db::name('coupon_code')->where(array('code'=>$code))->find();
		if(!$cc){
			return json(array('code'=>0,'time'=>time(),'msg'=>'兑奖码错误','data'=>'-1'));
		}
		
		if($cc['user_id']){
			return json(array('code'=>0,'time'=>time(),'msg'=>'当前密码已被兑换','data'=>''));
		}
		
		if($cc['type'] ==1 && !$cc['coupon_id']){
			return json(array('code'=>0,'time'=>time(),'msg'=>'优惠券ID不存在','data'=>''));
		}
		$count = Db::name('coupon_code')->where(array('user_id'=>$uid,'state'=>2))->count();
		if($count>10){
			return json(array('code'=>0,'time'=>time(),'msg'=>'兑换不能超过10次','data'=>''));
		}
		
		if($cc['type'] ==1){
		
			$send = model('ExpressOrder')->sendCouponDownload($uid,'',$cc['coupon_id'],0);
			if($send != false){
				$updateData['user_id'] = $uid;
				$updateData['exchange_info'] = '小程序兑换优惠券';
				$updateData['exchange_time'] = time();
				$updateData['state'] = 2;
				$r = Db::name('coupon_code')->where(array('id'=>$cc['id']))->update($updateData);
				if(!$r){
					return json(array('code'=>0,'time'=>time(),'msg'=>'更新数据库失败','data'=>''));
				}
				$data['code']=1;
				$data['type']=1;
				return json(array('code'=>1,'time'=>time(),'msg'=>'优惠券兑换成功','data'=>$data));
			}else{
				return json(array('code'=>0,'time'=>time(),'msg'=>'优惠券兑换失败'.model('ExpressOrder')->getError(),'data'=>''));
			}
		}else{
			
			$ur = Db::name('user_rank')->where(array('rank_id'=>$cc['rank_id']))->find();
			if(!$ur){
				return json(array('code'=>0,'time'=>time(),'msg'=>'会员等级不存在','data'=>'-1'));
			}
			if($cc['day'] <=0){
				return json(array('code'=>0,'time'=>time(),'msg'=>'兑换码有效期时间不存在','data'=>''));
			}
			$users = Db::name('users')->where(array('user_id'=>$uid))->find();
			if(!$users){
				return json(array('code'=>0,'time'=>time(),'msg'=>'会员不存在','data'=>''));
			}
			$info = '兑换码【'.$code.'】兑换等级';
			$updatePayUserRank = model('Users')->updatePayUserRank($uid,$cc['rank_id'],$cc['day'],$info,0);
			if($updatePayUserRank != false){
				$updateData['user_id'] = $uid;
				$updateData['exchange_info'] = '小程序兑换VIP等级';
				$updateData['exchange_time'] = time();
				$updateData['state'] = 2;
				$r = Db::name('coupon_code')->where(array('id'=>$cc['id']))->update($updateData);
				if(!$r){
					return json(array('code'=>0,'time'=>time(),'msg'=>'更新数据库失败','data'=>''));
				}
				$data['code']=1;
				$data['type']=2;
				return json(array('code'=>1,'time'=>time(),'msg'=>'兑换成功','data'=>$data));
			}else{
				return json(array('code'=>0,'time'=>time(),'msg'=>'兑换失败'.model('ExpressOrder')->getError(),'data'=>''));
			}
			
		}
		
	}
	
	public function member_pack(){
		$userId = (int)input('userId','','trim');
		$data = array();
		$users = Db::name('users')->where(array('user_id'=>$userId))->find();
		if(!$users){
			return json(array('code'=>0,'time'=>time(),'msg'=>'会员信息不存在'));
		}
		$items = Db::name('user_rank')->where(array('is_buy'=>0))->limit(0,10)->select();
		foreach($items  as $k => $v){
			$items[$k]['id'] = $v['rank_id'];
			$items[$k]['price'] = round($v['price']/100,2);
			$items[$k]['yuan_price'] = round($v['price']*2/100,2);
			if($v['number']){
				$items[$k]['subtitle'] .='直推人'.$v['number'].'人可免费升级，';
			}
			if($v['rate1']){
				$items[$k]['subtitle'] .='直推享受每单'.$v['rate1'].'%收益，';
			}
			if($v['rate2']){
				$items[$k]['subtitle'] .='间推享受每单'.$v['rate2'].'%收益';
			}
			if($users['rank_id'] >= $v['rank_id']){
				$items[$k]['chongzhi'] = '';
			}else{
				$items[$k]['chongzhi'] = '购买';
			}
			
		}
		$data['items'] = $items;
		$ad = model('Ad')->get_ad_list(array(),118);
		$data['images'] = config_weixin_img($ad[0]['photo']);
		$data['users'] = $users;
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$data));
	}
	
	
	public function member_detail(){
		$rank_id = (int)input('rank_id','','trim');
		$rank = Db::name('user_rank')->where(array('rank_id'=>$rank_id))->find();
		if(!$rank){
			return json(array('code'=>0,'time'=>time(),'msg'=>'信息不存在'));
		}
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$rank));
	}
	
	public function rank_buy(){
		$userId = (int)input('userId','','trim');
		$pint = input('pint','','trim,htmlspecialchars');
		$uid = $userId;
		if(!$userId){
			return json(array('code'=>0,'time'=>time(),'msg'=>'会员ID不存在'));
		}
		$users = Db::name('users')->where(array('user_id'=>$userId))->find();
		if(!$users){
			return json(array('code'=>0,'time'=>time(),'msg'=>'会员信息不存在'));
		}
		$rank_id = (int)input('rank_id','','trim');
		if(!$rank_id){
			return json(array('code'=>0,'time'=>time(),'msg'=>'等级ID不存在'));
		}
		$user_rank = Db::name('user_rank')->where(array('rank_id'=>$rank_id))->find();
		if(!$user_rank){
			return json(array('code'=>0,'time'=>time(),'msg'=>'等级不存在'));
		}
		if($user_rank['is_buy']==1){
			return json(array('code'=>0,'time'=>time(),'msg'=>'当前等级不支持在线购买'));
		}
		
		
		if($users['rank_id'] >= $rank_id){
			return json(array('code'=>0,'time'=>time(),'msg'=>'当前等级已存在或者无需购买'));
		}
		$need_pay = $user_rank['price'];
		if($need_pay <= 0){
			return json(array('code'=>0,'time'=>time(),'msg'=>'当前等级价格错误'));
		}
		$info = '购买等级'.$user_rank['rank_name'];
		if($pint=='mp-toutiao'){
			$code = 'toutiao';
		}else{
			$code = 'wxapp';
		}
		//微信支付
		$logs = array(
			'type' => 'vip', 
			'types' => 1, 
			'user_id' => $uid, 
			'order_id' => $rank_id, 
			'code' => $code, 
			'info' => $info, 
			'need_pay' =>$need_pay, 
			'create_time' => time(), 
			'create_ip' => request()->ip(), 
			'is_paid' => 0
		);
		$logs['log_id'] = Db::name('payment_logs')->insertGetId($logs);
		if($pint=='mp-toutiao'){
			$ttPay = new \TtPay($this->config['toutiao']['AppID'],$this->config['toutiao']['token'],$this->config['toutiao']['SALT'],$logs['log_id'],$info,$need_pay);//支付接口
			$pay = $ttPay->order();
			if($pay['err_no'] != '0'){
				return json(array('code'=>0,'msg'=>'抖音预支付失败'));
			}
			return json(array('code'=>1,'msg'=>"抖音支付下单成功",'data'=>$pay));
		}else{
			$connect = Db::name('connect')->where(array('uid'=>$uid))->order(array('connect_id'=>'desc'))->find();	
			$WX_OPENID = $connect['openid'] ? $connect['openid'] : $connect['open_id'];	
			$Payment = model('Payment')->getPayment('wxapp');
			$out_trade_no = $logs['log_id'].'-'.time();
			$weixinpay = new \Wxpay($this->config['wxapp']['appid'],$WX_OPENID,$Payment['mchid'],$Payment['appkey'],$out_trade_no,$info,$need_pay);//支付接口
			$return = $weixinpay->pay();
			if($return['package'] == 'prepay_id='){
				return json(array('code'=>0,'msg'=>'预支付失败:'.$return['rest']['return_msg']));
			}
			$data['timeStamp']= $return['timeStamp'];
			$data['nonceStr'] =$return['nonceStr'];
			$data['package'] =$return['package'];
			$data['signType'] = 'MD5';
			$data['paySign'] = $return['paySign'];
			$data['code'] = 1;
			return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$data));
			
		}

	}
	
	public function sendSms(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$mobile = input('phone','','trim');
        if(!isMobile($mobile)){
			return json(array('code'=>0,'time'=>time(),'msg'=>'手机号码格式不正确'));
        }
		Cache::set('mobile',$mobile,300);
		$randstring = Cache::get('scode');
		if(!empty($randstring)){
			Cache::set('scode',null);
		}
        $randstring = rand_string(4,1);
		Cache::set('scode',$randstring,300);
		if(model('Sms')->sms_yzm($mobile,$randstring)){
			$data['randstring'] = $randstring;
			$data['code'] = 1;
			return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$data));
		}else{
			return json(array('code'=>0,'time'=>time(),'msg'=>'发送失败'));
		}
		return json(array('code'=>0,'time'=>time(),'msg'=>'未知错误'));
	}
	
	public function bingMobile(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$u = Db::name('users')->where(array('user_id'=>$uid))->find();	
		if($u['mobile']){
			return json(array('code'=>0,'time'=>time(),'msg'=>'您已绑定手机无需再次绑定'));
        }
		$phone = input('phone','','trim');
        if(!isMobile($phone)){
			return json(array('code'=>0,'time'=>time(),'msg'=>'手机号码格式不正确'));
        }
		$phoneCode = input('phoneCode','','trim');
        if(!$phoneCode){
			return json(array('code'=>0,'time'=>time(),'msg'=>'验证码不能为空'));
        }
		$scode = Cache::get('scode');
		$mobile2 = Cache::get('mobile');
		
		if($mobile2 != $phone){
			return json(array('code'=>0,'mobile2'=>$mobile2,'msg'=>'手机号码非法操作请重新输入手机号绑定'));
		}
		if($scode != $phoneCode){
			return json(array('code'=>0,'time'=>time(),'msg'=>'验证码错误'));
		}
		if($users = model('Users')->getUserByAccount($phone)){
			return json(array('code'=>0,'time'=>time(),'msg'=>'当前手机已经被注册'));
		}
		$r = Db::name('users')->where(array('user_id'=>$uid))->update(array('mobile'=>$phone));	
		if($r){
			$data['r'] = $r;
			$data['code'] = 1;
			Cache::set('scode',null);
			Cache::set('mobile',null);
			return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$data));
		}else{
			return json(array('code'=>0,'time'=>time(),'msg'=>'更新手机号失败'));
		}
	}
	
	
	
	public function getUserDelivery(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$id = input('id','','trim,htmlspecialchars');
		$cdo = Db::name('city_delivery_order')->where(array('id'=>$id))->find();	
		
		
		$cd = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();	
		$cd['city'] = Db::name('city')->where(array('city_id'=>$cd['city_id']))->find();
		$cd['area'] = Db::name('area')->where(array('area_id'=>$cd['area_id']))->find();
        $cd['business'] = Db::name('business')->where(array('business_id'=>$cd['business_id']))->find();
        $cd['community'] = Db::name('business_community')->where(array('community_id'=>$cd['community_id']))->find();
		$cd['order'] = Db::name('express_order')->where(array('id'=>$cdo['order_id']))->find();
		$cd['photo'] = config_weixin_img($cd['photo']);
		if(!$cd){
			return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
		}
		$cd['cdo'] = $cdo;
		$cd['cdo']['img'] = config_weixin_img($cdo['img']);
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$cd));
	}
	
	
	public function getUserQiang(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$d = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();	
		if(!$d){
			return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
		}
		$id = input('id','','trim,htmlspecialchars');
		$cdo = Db::name('city_delivery_order')->where(array('id'=>$id))->find();	
		if(!$cdo){
			return json(array('code'=>0,'time'=>time(),'msg'=>'配送订单不存在','data'=>''));
		}

        if($cdo['community_id']){
            if($d['community_id'] != $cdo['community_id']){
                return json(array('code'=>0,'time'=>time(),'msg'=>'小区ID不匹配','data'=>''));
            }
        }

        if($cdo['business_id']){
            if($d['business_id'] != $cdo['business_id']){
                return json(array('code'=>0,'time'=>time(),'msg'=>'乡镇ID不匹配','data'=>''));
            }
        }
        if($cdo['area_id']){
            if($d['area_id'] != $cdo['area_id']){
                return json(array('code'=>0,'time'=>time(),'msg'=>'区县ID不匹配','data'=>''));
            }
        }
        if($cdo['city_id']){
            if($d['city_id'] != $cdo['city_id']){
                return json(array('code'=>0,'time'=>time(),'msg'=>'城市ID不匹配','data'=>''));
            }
        }

		if($uid == $cdo['user_id']){
			return json(array('code'=>0,'time'=>time(),'msg'=>'当前uid【'.$uid.'】不能抢【'.$cdo['user_id'].'】的订单','data'=>''));
		}

		$deliveryId = input('deliveryId','','trim,htmlspecialchars');
		$img = input('img','','trim,htmlspecialchars');
		$name = input('name','','trim,htmlspecialchars');
		$mobile = input('mobile','','trim,htmlspecialchars');
		$info = input('info','','trim,htmlspecialchars');
		
		$data['delivery_id'] = $d['id'];
		$data['deliveryId'] = $deliveryId;
		$data['img'] = $img;
		$data['name'] = $name;
		$data['mobile'] = $mobile;
		$data['info'] = $info;
		$data['orderStatus'] = 2;
		$r = Db::name('city_delivery_order')->where(array('id'=>$id))->update($data);
		if($r){
			$eoData['deliveryId'] = $data['deliveryId'];
			$eoData['realOrderName'] = $d['name'] ? $d['name'] : $data['name'];
			$eoData['realOrderMobile'] = $d['mobile'] ? $d['mobile'] : $data['mobile'];
			$eoData['orderStatus'] = 2;
			$e = Db::name('express_order')->where(array('id'=>$cdo['order_id']))->update($eoData);
			if($e){
				return json(array('code'=>'1','time'=>time(),'msg'=>'接单成功','data'=>$cd));
			}else{
				return json(array('code'=>0,'time'=>time(),'msg'=>'编辑失败','data'=>''));
			}
			return json(array('code'=>0,'time'=>time(),'msg'=>'编辑失败1','data'=>''));
		}
		return json(array('code'=>'1','time'=>time(),'msg'=>'接单成功','data'=>$cd));
	}
	
	public function deliveryPeisong(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$d = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();	
		if(!$d){
			return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
		}
		$id = input('id','','trim,htmlspecialchars');
		$cdo = Db::name('city_delivery_order')->where(array('id'=>$id))->find();	
		if(!$cdo){
			return json(array('code'=>0,'time'=>time(),'msg'=>'配送订单不存在','data'=>''));
		}
		$data['orderStatus'] = 3;
		$data['peisong_time'] = time();
		$r = Db::name('city_delivery_order')->where(array('id'=>$id))->update($data);
		if($r){
			$eoData['orderStatus'] = 3;
			$e = Db::name('express_order')->where(array('id'=>$cdo['order_id']))->update($eoData);
			if($e){
				return json(array('code'=>1,'time'=>time(),'msg'=>'操作成功','data'=>$cd));
			}else{
				return json(array('code'=>0,'time'=>time(),'msg'=>'操作失败','data'=>''));
			}
			return json(array('code'=>0,'time'=>time(),'msg'=>'操作失败1','data'=>''));
		}
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$cd));
	}

    public function deliveryDayin(){
        $uid = $this->getUserId();
        if(!$uid){
            return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
        }
        $d = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();
        if(!$d){
            return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
        }
        $id = input('id','','trim,htmlspecialchars');
        $cdo = Db::name('city_delivery_order')->where(array('id'=>$id))->find();
        if(!$cdo){
            return json(array('code'=>0,'time'=>time(),'msg'=>'配送订单不存在','data'=>''));
        }
        if($cdo['dayin_num']>25){
            return json(array('code'=>0,'time'=>time(),'msg'=>'打印次数超出限制','data'=>''));
        }
        $v = Db::name('express_order')->where(array('id'=>$cdo['order_id']))->find();
        if(!$v){
            return json(array('code'=>0,'time'=>time(),'msg'=>'订单不存在','data'=>''));
        }
        $community = Db::name('business_community')->where(array('community_id'=>$cdo['community_id']))->find();
        if($community['is_print']==0){
            return json(array('code'=>0,'time'=>time(),'msg'=>'打印功能未开启','data'=>''));
        }

        $orderInfo = model('Ad')->cityDelivery0rderDayinData($cdo,$community,$v,1);

        $wpPrint = model('Ad')->printLabelMsg($orderInfo,$community,$cdo);
        p($wpPrint);die;

        $wpPrint = model('Ad')->wpPrint($orderInfo,$community,$cdo);
        if($wpPrint ==false){
            return json(array('code'=>0,'time'=>time(),'msg'=>model('Ad')->getError(),'data'=>''));
        }else{
            $data['dayin_num'] = $cdo['dayin_num']+1;
            $data['dayin_time'] = time();
            $data['dayin_info'] = '飞蛾打印机打印';
            $r = Db::name('city_delivery_order')->where(array('id'=>$id))->update($data);
        }
        if($r){
            return json(array('code'=>1,'time'=>time(),'msg'=>'操作成功','data'=>''));
        }
        return json(array('code'=>0,'time'=>time(),'msg'=>'打印失败','data'=>''));
    }


	
	public function deliveryEnd(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$d = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();	
		if(!$d){
			return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
		}
		$id = input('id','','trim,htmlspecialchars');
		$cdo = Db::name('city_delivery_order')->where(array('id'=>$id))->find();	
		if(!$cdo){
			return json(array('code'=>0,'time'=>time(),'msg'=>'配送订单不存在','data'=>''));
		}
		$v = Db::name('express_order')->where(array('id'=>$cdo['order_id']))->find();	
		if(!$v){
			return json(array('code'=>0,'time'=>time(),'msg'=>'订单不存在','data'=>''));
		}
		if($v['orderStatus'] !=3){
			return json(array('code'=>0,'time'=>time(),'msg'=>'订单状态码【'.$v['orderStatus'].'】错误不能支持完成操作','data'=>''));
		}



		
		$data['delivery_time'] = time();
		$data['orderStatus'] = 8;
		$r = Db::name('city_delivery_order')->where(array('id'=>$id))->update($data);
		if($r){
			$eoData['orderStatus'] = 4;
			$e = Db::name('express_order')->where(array('id'=>$cdo['order_id']))->update($eoData);
			if($e){
				$m = $d['money'];
				$i = '订单ID【'.$v['id'].'】配送员完成订单奖励';
				if($m> 0){
					$rest = model('Users')->addMoney($uid,$m,$i,7);
				}

                $jia = $v['sumMoneyYuan_jia'];
                if($cdo['community_id']){
                    $business_community = Db::name('business_community')->where(array('community_id'=>$cdo['community_id']))->find();
                    if($business_community['ratio'] && $business_community['user_id']){
                        $community_money  = ($jia*$business_community['ratio'])/100;
                        $community_money = (int)$community_money;
                        if($community_money){
                            $ogdata['userid1'] = $business_community['user_id'];
                            $ogdata['commission1'] = $community_money;
                            $ogdata['msg1'] = '小区代理分成';
                            $ogdata['type1'] = 8;
                        }
                    }
                }
                if($cdo['business_id']){
                    $business = Db::name('business')->where(array('business_id'=>$cdo['business_id']))->find();
                    if($business['ratio'] && $business['user_id']){
                        $business_money  = ($jia*$business['ratio'])/100;
                        $business_money = (int)$business_money;
                        if($business_money){
                            $ogdata['userid2'] = $business['user_id'];
                            $ogdata['commission2'] = $business_money;
                            $ogdata['msg2'] = '乡镇代理分成';
                            $ogdata['type2'] = 9;
                        }
                    }
                }
                if($cdo['area_id']){
                    $area = Db::name('area')->where(array('area_id'=>$cdo['area_id']))->find();
                    if($area['ratio'] && $area['user_id']){
                        $area_money  = ($jia*$area['ratio'])/100;
                        $area_money = (int)$area_money;
                        if($area_money){
                            $ogdata['userid3'] = $area['user_id'];
                            $ogdata['commission3'] = $area_money;
                            $ogdata['msg3'] = '区县代理分成';
                            $ogdata['type3'] = 10;
                        }
                    }
                }
                if($cdo['city_id']){
                    $city = Db::name('city')->where(array('city_id'=>$cdo['city_id']))->find();
                    if($city['ratio'] && $city['user_id']){
                        $city_money  = ($jia*$city['ratio'])/100;
                        $city_money = (int)$city_money;
                        if($city_money){
                            $ogdata['userid4'] = $city['user_id'];
                            $ogdata['commission4'] = $city_money;
                            $ogdata['msg4'] = '城市代理分成';
                            $ogdata['type4'] = 11;
                        }
                    }
                }
                if($ogdata['userid1'] && $ogdata['commission1']){
                    $rest1 = model('Users')->addMoney($ogdata['userid1'],$ogdata['commission1'],$ogdata['msg1'],$ogdata['type1']);
                }
                if($ogdata['userid2'] && $ogdata['commission2']){
                    $rest2 = model('Users')->addMoney($ogdata['userid2'],$ogdata['commission2'],$ogdata['msg2'],$ogdata['type2']);
                }
                if($ogdata['userid3'] && $ogdata['commission3']){
                    $rest3 = model('Users')->addMoney($ogdata['userid3'],$ogdata['commission3'],$ogdata['msg3'],$ogdata['type3']);
                }
                if($ogdata['userid4'] && $ogdata['commission4']){
                    $rest4 = model('Users')->addMoney($ogdata['userid4'],$ogdata['commission4'],$ogdata['msg4'],$ogdata['type4']);
                }

				
				model('ExpressOrder')->completeProfit($v,$v['user_id'],'分销');//执行完成分销
				model('ExpressOrder')->orderAddIntegral($v,$v['user_id'],'给用户奖励积分');//赠送优惠券
				model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '签收成功通知');
				
				return json(array('code'=>1,'time'=>time(),'msg'=>'操作成功','data'=>$cd));
			}else{
				return json(array('code'=>0,'time'=>time(),'msg'=>'操作失败','data'=>''));
			}
		}
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$cd));
	}
	
	
	public function userDeliveryLogs(){
		$getMoneyTypes = model('Users')->getMoneyTypes();
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$page = input('page','','trim,htmlspecialchars');
		$current_time = input('current_time','','trim,htmlspecialchars');
		$type = input('type','','trim,htmlspecialchars');
		$map['user_id'] = $uid;
		$current_time  = str_replace('-', '',$current_time);
		if($current_time){
			$map['day'] =$current_time;
		}
		if($type){
			$map['type']  = $type;
		}
		if($page == 1){
			$star = 0;
			$end = $limit;
		}else{
			$star = $limit*$page;
			$end = $limit;
		}
		$count = Db::name('user_money_logs')->where($map)->count();	
		$Page = new \Page3($count,10);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('user_money_logs')->where($map)->order('log_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();	
			foreach($list as $k=>$v){
				$list[$k]['title'] = $getMoneyTypes[$v['type']];
				$list[$k]['price'] = round($v['money']/100,2);
				$list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
				$list[$k]['price_type'] = 1;
				$list[$k]['order_no'] = $v['log_id'];
			}
		}
		
		$data['count'] = $count;
		$data['totalPages'] = $Page->totalPages;
		$data['page'] = $page;
		$data['firstRow'] = $Page->firstRow;
		$data['listRows'] = $Page->listRows;
		$data['data'] = $list;
		$data['total'] = $count;
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$data));
	}
	
	//订单列表
	public function deliveryOrder(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$cd = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();	
		if(!$cd){
			return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
		}
		$status = (int)input('status','','trim,htmlspecialchars');
		$keywords = input('keywords','','trim,htmlspecialchars');
        $id = (int)input('id','','trim,htmlspecialchars');

		if($cd['area_id']){
			$map['area_id'] = $cd['area_id'];
		}
		if(!$cd['area_id'] && $map['city_id']){
			$map['city_id'] = $cd['city_id'];
		}
		if($status){
			$map['orderStatus'] =$status;
		}
		if($status>1){
			$map['delivery_id'] = $cd['id'];
		}
        if($id){
            $map['id'] = $id;
        }
		if($keywords){
            $map['user_id|name|mobile|deliveryId'] = array('LIKE','%'.$keywords.'%');
        }

		$list = Db::name('city_delivery_order')->where($map)->limit(0,50)->order('id desc')->select();
		foreach($list as $k=>$v){
			$list[$k]['order'] = Db::name('express_order')->where(array('id'=>$v['order_id']))->find();
			$list[$k]['city'] = Db::name('copy_city')->where(array('city_id'=>$v['city_id']))->find();
			$list[$k]['area'] = Db::name('copy_area')->where(array('area_id'=>$v['area_id']))->find();
            $list[$k]['business'] = Db::name('business')->where(array('business_id'=>$v['business_id']))->find();
            $list[$k]['community'] = Db::name('business_community')->where(array('community_id'=>$v['community_id']))->find();
			$list[$k]['createtime'] = date('Y-m-d H:i:s',$v['create_time']);
			$list[$k]['deliverytime'] = date('Y-m-d H:i:s',$v['delivery_time']);
			$list[$k]['peisongtime'] = date('Y-m-d H:i:s',$v['peisong_time']);
			$list[$k]['endtime'] = date('Y-m-d H:i:s',$v['end_time']);
			$list[$k]['refundtime'] = date('Y-m-d H:i:s',$v['refund_time']);
			$list[$k]['imgs'] = config_weixin_img($v['img']);
		}
		
		$data['data'] = $list;
		$data['total'] = $count;
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$data));
	}
	
	public function getDeliveryApply(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$cd = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();	
		if($cd['status']==1){
			return json(array('code'=>0,'time'=>time(),'msg'=>'请不要重复申请','data'=>''));
		}
		$img = input('img','','trim,htmlspecialchars');
		$name = input('name','','trim,htmlspecialchars');
		$mobile = input('mobile','','trim,htmlspecialchars');
		$addr = input('addr','','trim,htmlspecialchars');
		$info = input('info','','trim,htmlspecialchars');
		
		$data['user_id'] = $uid;
		$data['photo'] = $img;
		$data['name'] = $name;
		$data['mobile'] = $mobile;
		$data['info'] = $info;
		if($cd['id']){
			$data['id'] = $cd['id'];
			if(false !== Db::name('city_delivery')->update($data)){
				return json(array('code'=>'1','time'=>time(),'msg'=>'接收成功','data'=>$cd));
			}
		}else{
			$data['create_time'] = time();
			if(Db::name('city_delivery')->insert($data)){
				return json(array('code'=>'1','time'=>time(),'msg'=>'接收成功','data'=>$cd));
			}
		}
		return json(array('code'=>0,'time'=>time(),'msg'=>'申请失败','data'=>''));
	}
	
	public function cardExchange(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$users = Db::name('users')->where(array('user_id'=>$uid))->find();
		if(!$users){
			return json(array('code'=>0,'time'=>time(),'msg'=>'登陆错误','data'=>'-1'));
		}
		$code = input('code','','trim');
		if(!$code){
			return json(array('code'=>0,'time'=>time(),'msg'=>'请输入卡号','data'=>''));
		}
		$password = input('password','','trim');
		if(!$password){
			return json(array('code'=>0,'time'=>time(),'msg'=>'请输入密码','data'=>''));
		}
		$cc = Db::name('card_codes')->where(array('code'=>$code,'password'=>$password))->find();
		if(!$cc){
			return json(array('code'=>0,'time'=>time(),'msg'=>'卡号或者密码错误','data'=>'-1'));
		}
		if($cc['moneys']<=0){
			return json(array('code'=>0,'time'=>time(),'msg'=>'当前卡密金额错误','data'=>''));
		}
		if($cc['state']==2){
			return json(array('code'=>0,'time'=>time(),'msg'=>'当前卡密已经被兑换','data'=>''));
		}
		if($cc['user_id']){
			return json(array('code'=>0,'time'=>time(),'msg'=>'当前密码已被他人使用','data'=>''));
		}
		$count = (int)Db::name('card_codes')->where(array('user_id'=>$uid,'state'=>2))->count();
		if($count>20){
			return json(array('code'=>0,'time'=>time(),'msg'=>'单人卡密兑换不能超过20次','data'=>''));
		}
		if($cc['user_id'] == $uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'非法操作','data'=>''));
		}
		if($cc['pid'] == $uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'非法操作2','data'=>''));
		}
		if($cc['expire_date'] < TODAY){
			return json(array('code'=>0,'msg'=>'卡密已过期'));
		}
		
		
		$rest = model('Users')->addMoneys($uid,$cc['moneys'],'【'.$cc['id'].'】用户小程序卡密兑换抵扣金',3);
		if($rest != false){
			$updateData['user_id'] = $uid;
			$updateData['exchange_info'] = '小程序兑换抵扣金';
			$updateData['exchange_time'] = time();
			$updateData['state'] = 2;
			$r = Db::name('card_codes')->where(array('id'=>$cc['id']))->update($updateData);
			if(!$r){
				if($users['parent_id'] != '' && $cc['pid']){
					//更新分销关系
					Db::name('users')->where(array('user_id'=>$uid))->update(array('parent_id'=>$cc['pid']));
				}
				return json(array('code'=>0,'time'=>time(),'msg'=>'更新数据库失败','data'=>''));
			}
			$data['code']=1;
			$data['type']=1;
			return json(array('code'=>1,'time'=>time(),'msg'=>'兑换成功','data'=>$data));
		}else{
			return json(array('code'=>0,'time'=>time(),'msg'=>'兑换失败','data'=>''));
		}
	}
	public function userMoneysLogs(){
		$getMoneysTypes = model('Users')->getMoneysTypes();
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$page = input('page','','trim,htmlspecialchars');
		$current_time = input('current_time','','trim,htmlspecialchars');
		$type = input('type','','trim,htmlspecialchars');
		$map['user_id'] = $uid;
		$current_time  = str_replace('-', '',$current_time);
		if($current_time){
			$map['day'] =$current_time;
		}
		if($type){
			$map['type']  = $type;
		}
		if($page == 1){
			$star = 0;
			$end = $limit;
		}else{
			$star = $limit*$page;
			$end = $limit;
		}
		$count = Db::name('user_moneys_logs')->where($map)->count();	
		$Page = new \Page3($count,10);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('user_moneys_logs')->where($map)->order('log_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();	
			foreach($list as $k=>$v){
				$list[$k]['title'] = $getMoneysTypes[$v['type']];
				$list[$k]['price'] = round($v['moneys']/100,2);
				$list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
				$list[$k]['price_type'] = 1;
				$list[$k]['order_no'] = $v['log_id'];
			}
		}
		
		$data['count'] = $count;
		$data['totalPages'] = $Page->totalPages;
		$data['page'] = $page;
		$data['firstRow'] = $Page->firstRow;
		$data['listRows'] = $Page->listRows;
		$data['data'] = $list;
		$data['total'] = $count;
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$data));
	}
	
	public function getUsersSet(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$id = input('id','','trim,htmlspecialchars');
		$us = Db::name('users_set')->where(array('user_id'=>$uid))->find();	
		$us['img'] = config_weixin_img($us['img']);
		$us['create'] = date('Y-m-d h:i:s',$us['create_time']);
		$us['audit'] = date('Y-m-d h:i:s',$us['audit_time']);
		$us['edit'] = date('Y-m-d h:i:s',$us['edit_time']);
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$us));
	}
	
	public function getUsersDetail(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$id = input('id','','trim,htmlspecialchars');
		$us = Db::name('users_set')->where(array('id'=>$id))->find();	
		$us['img'] = config_weixin_img($us['img']);
		$us['create'] = date('Y-m-d h:i:s',$us['create_time']);
		$us['audit'] = date('Y-m-d h:i:s',$us['audit_time']);
		$us['edit'] = date('Y-m-d h:i:s',$us['edit_time']);
		
		
		$content = $us['info'];
		$domain = $this->config['site']['host'];
		$pattern = '/<img.*?src="(.*?)".*?\/?>/i';
		$replacement = '<img src="' . $domain . '/$1">';
		$newContent = preg_replace($pattern, $replacement, $content);
		$us['info'] = $newContent;;
		
		
		return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$us));
	}
	
	
	
	
	public function getUsersSetAdd(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}
		$us = Db::name('users_set')->where(array('user_id'=>$uid))->find();	
		if($us['status']==2){
			return json(array('code'=>0,'time'=>time(),'msg'=>'已审核就不能编辑了','data'=>''));
		}
		$img = input('img','','trim,htmlspecialchars');
		$name = input('name','','trim,htmlspecialchars');
		$mobile = input('mobile','','trim,htmlspecialchars');
		$weixin = input('weixin','','trim,htmlspecialchars');
		$info = input('info','','trim,htmlspecialchars');
		
		$data['user_id'] = $uid;
		$data['img'] = $img;
		$data['weixin'] = $weixin;
		$data['name'] = $name;
		$data['mobile'] = $mobile;
		$data['info'] = $info;
		if($us['id']){
			$data['status'] = 1;
			$data['id'] = $us['id'];
			$data['edit_time'] = time();
			if(false !== Db::name('users_set')->update($data)){
				return json(array('code'=>'1','time'=>time(),'msg'=>'接收成功','data'=>$cd));
			}
		}else{
			$data['status'] = 1;
			$data['create_time'] = time();
			if(Db::name('users_set')->insert($data)){
				return json(array('code'=>'1','time'=>time(),'msg'=>'接收成功','data'=>$cd));
			}
		}
		return json(array('code'=>0,'time'=>time(),'msg'=>'申请失败','data'=>''));
	}
	
	
	
}
