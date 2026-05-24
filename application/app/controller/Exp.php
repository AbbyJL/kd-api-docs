<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Exp extends Base{



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
	
	public function navigationApi2(){
		$data = Db::name('navigation')->where(array('status'=>1))->order('orderby asc')->limit(0,10)->select();
		if(!$data){
			$data = Db::name('navigation')->where(array('status'=>0))->order('orderby asc')->limit(0,10)->select();
		}
		foreach($data as $k=>$v){
			$data[$k]['name'] = cut_msubstr($v['nav_name'],0,4,false);
			$data[$k]['info'] = $v['title'];
			$data[$k]['tag'] = $v['colour'];
			$data[$k]['url'] = $v['url'];
			$data[$k]['icon'] = config_weixin_img($v['photo']);
		}
        $chunks = @array_chunk($data,2);
		$data['menuList'] = $data;
		$data['menuList1'] = $chunks[0];
		$data['menuList2'] = $chunks[1];
		$data['menuList3'] =    $chunks[2];
		$data['menuList4'] =  $this->getPlugins($uid);
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	public function getPlugins($uid){
		$pub_id = $this->config['jutuike']['jutuike_pub_id'];
		$static = $this->config['site']['host'].'/static/default/wap/img/cps/';
		$data  = array(
			'0' => array('name'=>'电影票','tag' =>'','icon' =>$static.'dianying.png','url' =>'plugin-private://wx89752980e795bfde/pages/index/index?pub_id='.$pub_id.'&sid='.$uid),
			'1' => array('name'=>'大牌点餐','tag' =>'','icon' =>$static.'diancan.png','url' =>'plugin://jtkDc/index?pub_id='.$pub_id.'&sid='.$uid),
			'2' => array('name'=>'同城福利','tag' =>'','icon' =>$static.'tongcheng.png','url' =>'plugin://bdshPlugin/index?pub_id='.$pub_id.'&sid='.$uid),
			'3' => array('name'=>'景点门票','tag' =>'','icon' =>$static.'menpiao.png','url' =>'plugin://menpiao-plugin/home?pub_id='.$pub_id.'&sid='.$uid),
			'4' => array('name'=>'酒店预定','tag' =>'','icon' =>$static.'hotel.png','url' =>'plugin://hotel-plugin/index?pub_id='.$pub_id.'&sid='.$uid),
			'5' => array('name'=>'打车出行','tag' =>'','icon' =>$static.'dache.png','url' =>'plugin://meishi/shop?pub_id='.$pub_id.'&type=didi&sid='.$uid),
			'6' => array('name'=>'优惠购物','tag' =>'','icon' =>$static.'shop.png','url' =>'plugin://meishi/dianshang?pub_id='.$pub_id.'&source=jd,taobao,douyin,pdd,vip&coupon=0&shareRate=0.9'),
			'7' => array('name'=>'抖音团购','tag' =>'','icon' =>$static.'douyin.png','url' =>'plugin://meishi/douyin?pub_id='.$pub_id.'&sid='.$uid),
			'8' => array('name'=>'京东精选','tag' =>'','icon' =>$static.'jingdong.png','url' =>'plugin://meishi/jingdong?pub_id='.$pub_id.'&eliteId=1&sid='.$uid),
			'9' => array('name'=>'美团劵包','tag' =>'','icon' =>$static.'meituan.png','url' =>'plugin://meishi/coupon?pub_id='.$pub_id.'&sid='.$uid),
		);
		return $data;
	}
    public function jumpApi(){
        $uid = $this->getUserId();
        $is_tuihuo = Db::name('users')->where(array('user_id'=>$uid))->value('is_tuihuo');
        $model = (int)$this->config['wxapp']['model'];
        $data['index_to']= (int)$this->config['config']['index_to'];
        $data['to'] = 0;
        if($is_tuihuo==1 && $model==1){
            $data['to'] = 1;
        }
        return json(array('code'=>1,'data'=>$data));
    }
	
	public function banner(){
		$ad = model('Ad')->get_ad_list(array(),115);
		foreach($ad as $k=>$v){
			$ad[$k]['banner_url'] = config_weixin_img($v['photo']);
			$ad[$k]['jump_url'] = $v['link_url'];
			$ad[$k]['id'] = $v['ad_id'];
			$ad[$k]['click'] = '1';
		}
		$navigation = Db::name('navigation')->where(array('status'=>2))->order('orderby asc')->limit(0,4)->select();
		foreach($navigation as $k=>$v){
			$navigation[$k]['name'] = $v['nav_name'];
			$navigation[$k]['info'] = $v['title'];
			$navigation[$k]['tag'] = $v['colour'];
			$navigation[$k]['url'] = $v['url'];
			$navigation[$k]['icon'] = config_weixin_img($v['photo']);
		}
		$data['ad'] = $ad;
		$data['navigation'] = $navigation;


        $goods = model('integral_goods')->where(array('is_index'=>1,'closed'=>0,'audit'=>1))->limit(0,6)->order('orderby asc')->select();
        foreach($goods as $k=>$v){
            $goods[$k]['photo'] = config_weixin_img($v['face_pic']);
            $goods[$k]['url'] = '/pages/integral/integralinfo/integralinfo?id='.$v['goods_id'];
            $goods[$k]['id'] = $v['goods_id'];
            $goods[$k]['money'] = round($v['money']/100,2);
            $goods[$k]['click'] = '1';
        }
        $data['goods'] = $goods;

		return json(array('code'=>1,'data'=>$data));
	}
	
	
	public function banner2(){
		$ad1 = model('Ad')->get_ad_list(array(),120);
		foreach($ad1 as $k=>$v){
			$ad1[$k]['banner_url'] = config_weixin_img($v['photo']);
			$ad1[$k]['jump_url'] = $v['link_url'];
			$ad1[$k]['id'] = $v['ad_id'];
			$ad1[$k]['click'] = '1';
		}
		$data['ad1'] = $ad1;
		
		$ad2 = model('Ad')->get_ad_list(array(),121);
		foreach($ad2 as $k=>$v){
			$ad2[$k]['banner_url'] = config_weixin_img($v['photo']);
			$ad2[$k]['jump_url'] = $v['link_url'];
			$ad2[$k]['id'] = $v['ad_id'];
			$ad2[$k]['click'] = '1';
		}
		$data['ad2'] = $ad2;
		
		$ad3 = model('Ad')->get_ad_list(array(),122);
		foreach($ad3 as $k=>$v){
			$ad3[$k]['banner_url'] = config_weixin_img($v['photo']);
			$ad3[$k]['jump_url'] = $v['link_url'];
			$ad3[$k]['id'] = $v['ad_id'];
			$ad3[$k]['click'] = '1';
		}
		$data['ad3'] = $ad3;
		return json(array('code'=>1,'data'=>$data));
	}
	
	
	public function getwxsharelist(){
	    $data['photo'] =$this->config['config']['index_share'];
		$data['title']=  $this->config['config']['index_share_title'] ? $this->config['config']['index_share_title']:'快递寄件折扣平台 运费低至5元起';
		return json(array('code'=>1,'msg'=>"获取成功看你妈",'data'=>$data));
	}

    public function getsharediscount(){
        $uid = $this->getUserId();
        $open = (int)$this->config['config']['place_order_open'];
        $m1 = $this->config['config']['place_order_money_1'];
        $m2 = $this->config['config']['place_order_money_2'];
        $m3 = $this->config['config']['place_order_money_3'];
        $m4 = $this->config['config']['place_order_money_4'];
        $m5 = $this->config['config']['place_order_money_5'];
        $cu = (int)Db::name('users')->where(array('parent_id'=>$uid))->count();
        if($cu>=20){
            $m = $m5;
        }elseif($cu<20 && $cu>=10){
            $m = $m4;
        }elseif($cu<10 && $cu>=6){
            $m = $m4;
        }elseif($cu<6 && $cu>=3){
            $m = $m2;
        }elseif($cu<3 && $cu>=0){
            $m = $m1;
        }
        $data['discountmoney']= $m;
        $rankId = (int)Db::name('users')->where(array('user_id'=>$uid))->value('rank_id');
        if($rankId){
            $data['isVip']= 1;
        }else{
            $data['isVip']= 0;
        }
        $cd= (int)Db::name('coupon_download')->where(array('expire_date'=>array('ELT',TODAY),'user_id'=>$uid,'is_used'=>0))->count();
        if($cd){
            $data['isCoupon']= 1;
        }else{
            $data['isCoupon']= 0;
        }
        if($open==0){
            $data['isVip']= 1;
            $data['isCoupon']= 1;
        }
        return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
    }
	

	//首页
	public function configindex(){
		$data['hidden_other']= false;
		$data['host']= $this->config['site']['host'];
		$data['index_title']= $this->config['site']['sitename'];
		$data['kfmobile']= $this->config['site']['tel'];
		$data['kfphone']= $this->config['site']['tel'];
		$data['onlineServiceShow']= (int)$this->config['wxapp']['onlineServiceShow'];
		$data['onlineServiceType']= (int)$this->config['wxapp']['onlineServiceType'];
		$data['corpId']= $this->config['wxapp']['corpId'];
		$data['extInfo']= $this->config['wxapp']['extInfo'];
		$data['logo']=  config_weixin_img($this->config['site']['logo']);
		$data['wxcode']=  config_weixin_img($this->config['site']['wxcode']);
		$data['index_share']=  config_weixin_img($this->config['config']['index_share']);
		$data['index_share_title']=  $this->config['config']['index_share_title'] ? $this->config['config']['index_share_title']:'快递寄件折扣平台 运费低至5元起';
		$data['wxqcode']=  config_weixin_img($this->config['site']['wxqcode']);
		$data['wxqcode6']=  config_weixin_img($this->config['site']['wxqcode6']);
		$data['wxcode1']=  config_weixin_img($this->config['site']['wxcode']);
		$data['member_moneys_show']= (int)$this->config['card']['member_moneys_show'];
		$data['order_duihuan_open']= (int)$this->config['card']['order_duihuan_open'];
		$data['index_jijian_2']= (int)$this->config['config']['index_jijian_2'];
        $data['index_to']= (int)$this->config['config']['index_to'];
        $data['model']= (int)$this->config['wxapp']['model'];
        $data['follow']= $this->config['weixin']['follow']?$this->config['weixin']['follow']:'';


        $data['pop_open']= (int)$this->config['dianshang']['pop_open'];
        $data['pop_title']= $this->config['dianshang']['pop_title'];
        $data['pop_info']= $this->config['dianshang']['pop_info'];
        $data['pop_url']= $this->config['dianshang']['pop_url'];

        $data['juke']= $this->config['jutuike']['juke']?$this->config['jutuike']['juke']:'0';
        $data['juke1']= $this->config['jutuike']['juke1']?$this->config['jutuike']['juke1']:'0';
        $data['juke2']= $this->config['jutuike']['juke2']?$this->config['jutuike']['juke2']:'0';



        $data['uu_appid']= "";
		$data['uu_h5_url']= "";
		$data['uu_mini_url']="";
		$data['check_login']= (int)$this->config['config']['check_login'];
		$ad = model('Ad')->get_ad_list(array(),118);
		foreach($ad as $k=>$v){
			$ad[$k]['banner_url'] = config_weixin_img($v['photo']);
			$ad[$k]['jump_url'] = $v['link_url'];
			$ad[$k]['ad_id'] = $v['ad_id'];
			$ad[$k]['click'] = '1';
		}
		$data['ad']=$ad;
		
		
		$data['wx_cooperate_appid']= $this->config['wxapp']['appid'];
		$data['wx_cooperate_appname']= $this->config['site']['sitename'];
		$data['wx_third_appid']= $this->config['wxapp']['appid'];
		$data['wx_third_appname']= $this->config['site']['sitename'];
		
		
		$ad1 = model('Ad')->get_ad_list(array(),120);
		foreach($ad1 as $k=>$v){
			$ad1[$k]['banner_url'] = config_weixin_img($v['photo']);
			$ad1[$k]['jump_url'] = $v['link_url'];
			$ad1[$k]['id'] = $v['ad_id'];
			$ad1[$k]['click'] = '1';
		}
		$data['ad1'] = $ad1;
		
		$ad2 = model('Ad')->get_ad_list(array(),121);
		foreach($ad2 as $k=>$v){
			$ad2[$k]['banner_url'] = config_weixin_img($v['photo']);
			$ad2[$k]['jump_url'] = $v['link_url'];
			$ad2[$k]['id'] = $v['ad_id'];
			$ad2[$k]['click'] = '1';
		}
		$data['ad2'] = $ad2;
		
		$ad3 = model('Ad')->get_ad_list(array(),122);
		foreach($ad3 as $k=>$v){
			$ad3[$k]['banner_url'] = config_weixin_img($v['photo']);
			$ad3[$k]['jump_url'] = $v['link_url'];
			$ad3[$k]['id'] = $v['ad_id'];
			$ad3[$k]['click'] = '1';
		}
		$data['ad3'] = $ad3;
		
		
		
		$data['tmpl'][0] = Db::name('weixin_tmpl')->where(array('title'=>'收益到账通知'))->value('template_id');
		$data['tmpl'][1] = Db::name('weixin_tmpl')->where(array('title'=>'优惠券发放通知'))->value('template_id');
		$data['tmpl'][2] = Db::name('weixin_tmpl')->where(array('title'=>'待支付运单提醒'))->value('template_id');


		$uid = $this->getUserId();
		if($uid){
			$pid = Db::name('users')->where(array('user_id'=>$uid))->value('parent_id');	
			if($pid){
				$us = Db::name('users_set')->where(array('user_id'=>$pid,'status'=>2))->field('id,name,weixin,img,addr,mobile')->find();	
				if($us){
					$us['img'] = config_weixin_img($us['img']);
				}	
			}
		}
		$data['us'] = $us;
		
		return json(array('code'=>1,'msg'=>"查询成功",'data'=>$data));
	}
	
	
	//获取数据[通知]
	public function settravel(){
		$detail = input('detail','','trim,htmlspecialchars');
		if($detail){
			$data = Db::name('article')->where(array('title'=>$detail))->find();
			$data['content'] = $data['details'];	
		}
		return json(array('code'=>1,'msg'=>"查询成功",'data'=>$data));
	}
	
	//xieyi
	public function xieyi(){
		$title = input('title','','trim,htmlspecialchars');
		if($title){
			$data = Db::name('article')->where(array('title'=>$title))->find();
			$data['content'] = $data['details'];	
		}
		return json(array('code'=>1,'msg'=>"查询成功",'data'=>$data));
	}
	
	

	public function myhandleCount(){
		$uid = $this->getUserId();
		$data =(int)Db::name('express_order')->where(array('user_id'=>$uid,'diffStatus'=>1,'closed'=>0))->count();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	public function bindMobile(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	

	public function getUserCheckin(){
		$uid = $this->getUserId();
		$num = input('num','','trim,htmlspecialchars');//第几天签到
		$integral = 0;
		if($num == 4){
			$integral = (int)$this->config['integral']['sign_4'];
		}elseif($num == 7){
			$integral = (int)$this->config['integral']['sign_7'];
		}else{
			$integral = (int)$this->config['integral']['sign_0'];
		}
		if(!empty($num)){
			$Data['user_id'] = $uid;
			$Data['num'] = $num;
			$Data['day'] = date('Y-m-d h:s:i',time());
			$Data['integral'] = $integral;
			$Data['create_time'] = time();
			$id = Db::name('user_sign_list')->insertGetId($Data);
			if($id && $integral){
				model('Users')->addIntegral($uid,$integral,$Data['day'].'签到奖励积分',1);
			}
		}
		
		$count = (int)Db::name('user_sign_list')->where(array('user_id'=>$uid))->count();
		if($count >=7){
			$count =0;
		}
		$pointsList = Db::name('user_sign_list')->where(array('user_id'=>$uid))->order('id desc')->limit(0,7)->select();
		foreach($pointsList as $k=>$v){
			$pointsList[$k]['day'] = $v['num'];
		}
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$count,'pointsList'=>$pointsList));
	}
	
    //获取签到信息
	public function useCheckin(){
		$uid = $this->getUserId();
		$num = input('num','','trim,htmlspecialchars');//第几天签到
		$integral = 0;
		if($num == 4){
			$integral = (int)$this->config['integral']['sign_4'];
		}elseif($num == 7){
			$integral = (int)$this->config['integral']['sign_7'];
		}else{
			$integral = (int)$this->config['integral']['sign_0'];
		}
		$bg_time = strtotime(TODAY);
		$user_sign_list = Db::name('user_sign_list')->where(array('create_time' => array(array('ELT', time()), array('EGT', $bg_time)),'user_id'=>$uid))->order('id desc')->find();
		if($user_sign_list){
			return json(array('code'=>0,'msg'=>"今日已签到"));
		}
		if(!empty($num)){
			$Data['user_id'] = $uid;
			$Data['num'] = $num;
			$Data['day'] = date('Y-m-d h:s:i',time());
			$Data['integral'] = $integral;
			$Data['create_time'] = time();
			$id = Db::name('user_sign_list')->insertGetId($Data);
			if($id && $integral){
				model('Users')->addIntegral($uid,$integral,$Data['day'].'签到奖励积分',1);
			}
		}
		$count = (int)Db::name('user_sign_list')->where(array('user_id'=>$uid))->count();
		if($count >=7){
			$count =0;
		}
		$pointsList = Db::name('user_sign_list')->where(array('user_id'=>$uid))->order('id desc')->limit(0,7)->select();
		foreach($pointsList as $k=>$v){
			$pointsList[$k]['day'] = $v['num'];
		}
		//p($pointsList);die;
		if(count($pointsList) >= 7){
			foreach($pointsList as $k=>$v){
				Db::name('user_sign_list')->where(array('id'=>$v['id']))->delete();
			}
			$pointsList = array();
		}

		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$count,'pointsList'=>$pointsList));
	}
	
	
	//积分兑换优惠券订单
	public function cashCoupon(){
		$uid = $this->getUserId();
		$promotion_id = input('promotion_id','','trim,htmlspecialchars');//优惠券列表
		$coupon = Db::name('coupon')->where(array('coupon_id'=>$promotion_id))->find();
		if($coupon['expire_date'] < TODAY){
			return json(array('code'=>0,'msg'=>'优惠券已过期'));
		}
		$integral = $coupon['integral'];
		
		
		$bg_time = strtotime(TODAY);
		$str = '-30 day';
        $bg_time_yesterday = strtotime(date('Y-m-d', strtotime($str)));
		$coupon_download = Db::name('coupon_download')->where(array('create_time' => array(array('ELT',time()),array('EGT', $bg_time_yesterday)),'user_id'=>$uid))->order('download_id desc')->find();
		if($coupon_download){
			//return json(array('code'=>0,'msg'=>"一个月之内您已下载过优惠券"));
		}
		$users = Db::name('users')->where(array('user_id'=>$uid))->find();	
		if($users['integral'] < $integral){
			return json(array('code'=>0,'msg'=>'余额不足'));
		}
		$rest = model('Users')->addIntegral($uid,-$integral,'积分兑换优惠券订单',1);
		if($rest){
			//正常积分支付支付订单回调
			$updateCouponOrder = model('Setting')->updateCouponOrder($promotion_id,$integral,$log_id=0,$uid,1);
			if($updateCouponOrder == false){
				return json(array('code'=>0,'msg'=>'积分兑换优惠券付款回调失败未知错误'.model('Setting')->getError()));
			}
			return json(array('code'=>1,'msg'=>"积分兑换优惠券下单成功",'data'=>$data));
		}else{
			return json(array('code'=>0,'msg'=>'积分兑换优惠券扣费失败'));
		}
		
	}
	
	
	//购买优惠券
	public function buyCoupon(){
		$uid = $this->getUserId();
		$promotion_id = input('promotion_id','','trim,htmlspecialchars');//优惠券列表
		$paytype = input('paytype','','trim,htmlspecialchars');//支付方式
		$pint = input('pint','','trim,htmlspecialchars');
		
		$coupon = Db::name('coupon')->where(array('coupon_id'=>$promotion_id))->find();
		if($coupon['expire_date'] < TODAY){
			return json(array('code'=>0,'msg'=>'优惠券已过期'));
		}
		
		$bg_time = strtotime(TODAY);
		$str = '-30 day';
        $bg_time_yesterday = strtotime(date('Y-m-d', strtotime($str)));
		$coupon_download = Db::name('coupon_download')->where(array('create_time' => array(array('ELT',time()),array('EGT', $bg_time_yesterday)),'user_id'=>$uid))->order('download_id desc')->find();
		
		
		
		
		$need_pay = $coupon['money'];
		if($paytype== 'balance'){
			$money = $vip;
			$type = 'coupon';
			$code = 'money';
			$info = '优惠券购买';
		}else{
			$money = $money*100;
			$type = 'coupon';
			$code = 'wxapp';
			$info = '优惠券购买';
		}
		if($pint=='mp-toutiao'){
			$code = 'toutiao';
		}
		
		$logs = array(
			'type' => $type, 
			'types' => '1', 
			'user_id' => $uid, 
			'order_id' => $promotion_id, 
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
		}elseif($paytype== 'balance'){
			$users = Db::name('users')->where(array('user_id'=>$uid))->find();	
			if($users['money'] < $need_pay){
				return json(array('code'=>0,'msg'=>'余额不足'));
			}
			$rest = model('Users')->addMoney($uid,-$need_pay,'购买优惠券订单',1);
			if($rest){
				$updateCouponOrder = model('Setting')->updateCouponOrder($promotion_id,$need_pay,$log_id=0,$uid,1);
				if($updateCouponOrder == false){
					return json(array('code'=>0,'msg'=>'购买优惠券付款回调失败未知错误'.model('Setting')->getError()));
				}
				return json(array('code'=>1,'msg'=>"购买优惠券余额支付下单成功",'data'=>$data));
			}else{
				return json(array('code'=>0,'msg'=>'购买优惠券扣费失败'));
			}
		}else{
			$connect = Db::name('connect')->where(array('uid'=>$uid))->order(array('connect_id'=>'desc'))->find();	
			$WX_OPENID = $connect['open_id'] ? $connect['open_id'] : $connect['openid'];	
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
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}
	}
	
	
	
	
	public function wxappLogin2(){
		$open_id = input('open_id','','trim,htmlspecialchars');
		$unionid = input('unionid','','trim,htmlspecialchars');
		$iv = input('iv','','trim,htmlspecialchars');
		$edata = input('edata','','trim,htmlspecialchars');
		$session_key = input('session_key','','trim');
		$parent_id = input('invite_id','','trim,htmlspecialchars');
		$pint = input('pint','','trim,htmlspecialchars');
		if($pint == 'mp-toutiao'){
			
			$appid = trim($this->config['toutiao']['AppID']);
			$result['phoneNumber'] = '';
			include ROOT_PATH.'extend/jiemi/WXBizDataCrypt.php';
			$WXBizDataCrypt = new \WXBizDataCrypt($appid,$session_key);
			$errCode = $WXBizDataCrypt->decryptData($edata,$iv,$data);
			$result = json_decode($data,true);  
			$mobile = $result['phoneNumber'];
			if(!$mobile){
				return json(array('code'=>0,'msg'=>"手机号获取失败，请重新搜索小程序访问操作"));
			}
			$count = (int)Db::name('users')->where(array('mobile'=>$mobile))->count();
			if($count>1 && $mobile){
				return json(array('code'=>0,'msg'=>"手机号授权失败【数据库中存在多个相同手机号-".$mobile."，请联系网找客服处理】"));
			}
			$addData['unionid'] = $unionid;
			$addData['session_key'] = $session_key;
			if(!$open_id){
				return json(array('code'=>0,'msg'=>"open_id获取失败，请稍后再试试"));
			}
			$addData['openid'] = $open_id;
			$addData['parent_id'] = $parent_id;
			$addData['mobile'] = $mobile;
			$addRegisterUser = $this->addToutiaoRegisterUser($addData);
			$addRegisterUser['token'] = $addRegisterUser['token'];
			$addRegisterUser['avatar'] = config_weixin_img($addRegisterUser['face']);
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$addRegisterUser));
		}else{
			$result['phoneNumber'] = '';
			include ROOT_PATH.'extend/jiemi/WXBizDataCrypt.php';
			$WXBizDataCrypt = new \WXBizDataCrypt($this->config['wxapp']['appid'],$session_key);
			$errCode = $WXBizDataCrypt->decryptData($edata,$iv,$data);
			$result = json_decode($data,true);  
			$mobile = $result['phoneNumber'];
			$check_mobile = (int)$this->config['config']['appid'];
			$check_login = (int)$this->config['config']['check_login'];
			if(!$mobile && $check_mobile==1 && $check_login==0){
				//不强制授权
				return json(array('code'=>0,'msg'=>"手机号获取失败，请重新搜索小程序访问操作"));
			}
		
		
			$count = (int)Db::name('users')->where(array('mobile'=>$mobile))->count();
			if($count>1 && $mobile){
				return json(array('code'=>0,'msg'=>"手机号授权失败【数据库中存在多个相同手机号-".$mobile."，请联系网找客服处理】"));
			}
			
			
			$addData['unionid'] = $unionid;
			$addData['session_key'] = $session_key;
			if(!$open_id){
				return json(array('code'=>0,'msg'=>"open_id获取失败，请稍后再试试"));
			}
			
			$addData['openid'] = $open_id;
			$addData['parent_id'] = $parent_id;
			$addData['mobile'] = $mobile;
			
			
			$addRegisterUser = $this->addRegisterUser($addData);
			
			$addRegisterUser['token'] = $addRegisterUser['token'];
			$addRegisterUser['avatar'] = config_weixin_img($addRegisterUser['face']);
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$addRegisterUser));
		}
	}
	
	
	public function addToutiaoRegisterUser($result){
		if($result['unionid'] && $result['unionid'] !='undefined'){
			$connect = Db::name('connect')->where(array('type'=>'toutiao','unionid'=>$result['unionid']))->order(array('connect_id'=>'desc'))->find(); 	
		}elseif($result['mobile']){
			$users = Db::name('users')->where(array('mobile'=>$result['mobile']))->order(array('user_id'=>'desc'))->find();
			$connect = Db::name('connect')->where(array('type'=>'toutiao','uid'=>$users['user_id']))->order(array('connect_id'=>'desc'))->find(); 	
		}elseif($result['openid']){
			$connect = Db::name('connect')->where(array('type'=>'toutiao','openid'=>$result['openid']))->order(array('connect_id'=>'desc'))->find(); 	; 	
		}else{
			$connect = Db::name('connect')->where(array('type'=>'toutiao','openid'=>$result['openid']))->order(array('connect_id'=>'desc'))->find(); 	
		}
		if(!$users){
		    $users['user_id'] = 0;
		}
		if($connect['uid']){
			$users = Db::name('users')->where(array('user_id'=>$connect['uid']))->find();
		}
		$data['unionid'] = $result['unionid'];
		$data['open_id'] = '';
		$data['openid'] = $result['openid'];
        $data['type'] = 'toutiao';
		$data['session_key'] = $result['session_key'];
		$data['rd_session'] = md5(time().mt_rand(1,999999999));
		if(!$users['user_id']){
			if(!$connect){
				$data['create_time'] = time();
				$data['create_ip'] = request()->ip();
				$connect_id = Db::name('connect')->insertGetId($data);//新建表
			}else{
				$connect_id = $connect['connect_id'];//新建表
			}
			$rand = rand(1000,9999);
			$account = 'Exp_toutiao_'.$connect_id.'_'.$rand;
            $arr = array(
               'account' => $account, 
			   'mobile' => $result['mobile'],
               'password' => $rand,
               'unionid' => $result['unionid'], 
               'face' => '/attachs/default.jpg', 
               'nickname' => $account, 
               'reg_time' => time(), 
               'reg_ip' =>request()->ip()
            );
		
            $user_id = model('Passport')->register($arr,$result['parent_id'],1);
			if($user_id){
				Db::name('connect')->update(array('connect_id'=>$connect_id,'uid'=>$user_id));
				$user = Db::name('users')->where(array('user_id'=>$user_id))->find();
				return $this->getUserData($user_id);
			}
		}else{
			
			$token = md5(uniqid());
			$updateData['connect_id'] = $connect['connect_id'];
			$updateData['openid'] = $result['openid'];
			$updateData['unionid'] = $result['unionid'];
			$user = Db::name('users')->where(array('user_id'=>$users['user_id']))->update(array('token'=>$token));
			$user_id = $users['user_id'];
			if($connect['connect_id']){
				Db::name('connect')->where(array('connect_id'=>$connect['connect_id']))->update($updateData);
			}else{
				$data['create_time'] = time();
				$data['uid'] = $user_id;
				$data['create_ip'] = request()->ip();
				if($user_id){
					$connect_id = Db::name('connect')->insertGetId($data);//新建表
				}
			}
			return $this->getUserData($connect['uid']);
		}
		return true;
	}
	
	
	
	public function byteLogin2(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function aliLogin2(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	public function wxappLogin1(){
		$code = input('code','','trim,htmlspecialchars');
		$pint = input('pint','','trim,htmlspecialchars');
		if($pint == 'mp-toutiao'){
			$params = array(
				"appid" => trim($this->config['toutiao']['AppID']),
				"secret" => trim($this->config['toutiao']['Secret']),
				"anonymous_code" => '',
				"code" => $code
			);
			$params = json_encode($params);
			$url = "https://developer.toutiao.com/api/apps/v2/jscode2session";
			$rest =$this->curl->post($url,$params);
			$rest = json_decode($rest,true); 

			$data['openid'] = $rest['data']['openid'];
			$data['session_key'] = $rest['data']['session_key'];
			$data['unionid'] = $rest['data']['unionid'];
			
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}else{
			$url="https://api.weixin.qq.com/sns/jscode2session?appid=".$this->config['wxapp']['appid']."&secret=".$this->config['wxapp']['appsecret']."&js_code=".$code."&grant_type=authorization_code";
			$data = $this->httpRequest($url);
			$data = json_decode($data,true); 
			$siright = (int)$this->config['config']['default_option_siright'];
			$data['siright'] = $siright;
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}
		
	}
	
	
	public function wxappLogin3(){
		$uid = $this->getUserId();
		$nickname = input('nickname','','trim,htmlspecialchars');
		$avatar = input('avatar','','trim,htmlspecialchars');
		$Data['nickname'] = $nickname;
		$Data['face'] = $avatar;
		$Data['uc_id'] = 1;
		$Data['last_time'] = time();
		if(!$uid){
			return json(array('code'=>0,'msg'=>"没获取到UID请删除小程序后重新搜索小程序名字访问"));
		}
		$r = Db::name('users')->where('user_id',$uid)->update($Data);
		if($r){
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}else{
			return json(array('code'=>0,'msg'=>"更新失败【删除小程序》重新搜索小程序》再次登录】"));
		}
	}
	
	public function byteLogin1(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function aliLogin1(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function aliLogin3(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	//提交问题反馈
	public function addopinion(){
		$content = input('content','','trim,htmlspecialchars');
		$contact = input('contact','','trim,htmlspecialchars');
		$uid = $this->getUserId();
		$images = input('images','','trim');
		//整合图片
		$images = @substr($images,1);
		$images = @substr($images,0,-1);
		$images = @explode(",",$images);
		$img = array();
		foreach($images as $k=>$v){
			$s = @substr($v,1);
			$s = @substr($s,0,-1);
			$img[$k] = $s;
		}
		$i = @implode(",",$img);
		$Data['user_id'] = $uid;
		$Data['content'] = $content;
		$Data['contact'] = $contact;
		$Data['images'] = $i;
		$Data['create_time'] = time();
		
		Db::name('express_msg')->insertGetId($Data);
		
		return json(array('code'=>1,'msg'=>"提交反馈成功",'data'=>$data));
	}
	
	public function getUserId(){
		$token = input('token','','trim,htmlspecialchars');
		$user_id = Db::name('users')->where(array('token'=>$token))->value('user_id');
		return (int)$user_id;
	}
	
	
	public function getUserInfo(){
		$user_id = $this->getUserId();
		$data = $this->getUserData($user_id);
		if(!$data){
			return json(array('code'=>0,'msg'=>'会员信息不存在'));
		}
		
		if($data['subscribe_status'] == 0){
			$subscribeUser = model('Weixin')->subscribeUser($user_id);//0弹出 1不弹出
			if($subscribeUser == 1){
				//关注
				$update = Db::name('users')->where(array('user_id'=>$user_id))->update(array('subscribe_status'=>1));
				$data['subscribe_status'] = 1;
			}
		}
		if(!$this->config['weixin']['appid']){
			$data['subscribe_status'] = 1;
		}
		if(!$this->config['weixin']['appsecret']){
			$data['subscribe_status'] = 1;
		}
		
		$province = Db::name('copy_province')->where(array('user_id'=>$user_id))->find();
		$roleName =$province['name'].'省代理';
		$role = 1;
		if(!$province){
			$city = Db::name('city')->where(array('user_id'=>$user_id))->find();
			$roleName = $city['name'].'市代理';
			$role = 2;
			if(!$city){
				$area = Db::name('area')->where(array('user_id'=>$user_id))->find();
				$roleName = $area['area_name'].'区代理';
				$role = 3;
				if(!$area){
					$troleName ='';
					$role = 0;
				}
			}
		}
		
		
		$data['role'] = $role;
		$data['roleName'] = $roleName;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	public function getall(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}


    public function aisarea(){
        $latitude = input('latitude','','trim,htmlspecialchars');
        $longitude= input('longitude','','trim,htmlspecialchars');
        $address  = input('address ','','trim,htmlspecialchars');
        $longitude= input('longitude','','trim,htmlspecialchars');
        $op = $latitude.','.$longitude;
        $this->curl = new \Curl();
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?location=".$op."&key=IRTBZ-7KIR5-E6CIW-QEK5Z-EZIU5-JVFV7&get_poi=0&coord_type=1";
        $html = file_get_contents($url);
        if(!$html){
            $html = $this->curl->get($url);
        }
        $html = json_decode($html,true);
        if($html['status'] == 0){
            $data['province'] = $html['result']['address_component']['province'];
            $data['city'] = $html['result']['address_component']['city'];
            $data['district'] = $html['result']['address_component']['district'];
            $data['town'] = $html['result']['address_component']['street_number'] ? $html['result']['address_component']['street_number'] : $html['result']['address_component']['street'];
            $data['name'] = $html['result']['address'];
            $data['code'] = $html['result']['ad_info']['adcode'];
            $data['lat'] = $html['result']['location']['lat'];
            $data['lng'] = $html['result']['location']['lng'];

            $town = $html['result']['address_reference']['town'];
            $town_id = $town['id'];


            $data['result'] = $html['result'];
            $address_reference = $html['result']['address_reference'];
            $town = $address_reference['town'];
            $title = $town['title'];
            $arr = explode($data['district'],$address);
            $a = $arr[1];
            if($data['district'] && $address && $arr){
                $data['street'] = $title.''.$a;
                $data['street_number'] = $html['result']['address_component']['street_number'].$name;
            }else{
                $data['street'] = $title.''.$html['result']['address_component']['street'];
                $data['street_number'] = $html['result']['address_component']['street_number'].$name;
            }




            //选择当前小区
            $communityList = Db::name('business_community')->where(array('business_id'=>$town_id))->limit(0.100)->select();
            foreach ($communityList as $k=>$v){
                $communityList[$k]['ioc'] = config_weixin_img($v['img']);
            }

            //周边小区
            $business = Db::name('business')->where(array('business_id'=>$town_id))->find();
            $communityList2 = Db::name('business_community')->where(array('area_id'=>$business['area_id']))->limit(0.100)->select();
            foreach ($communityList2 as $k=>$v){
                $communityList2[$k]['ioc'] = config_weixin_img($v['img']);
            }

            $data['community'] = 0;
            if($communityList){
                $data['community'] = 1;
            }
            $data['communityList'] = $communityList;
            $data['communityList2'] = $communityList2;
            $data['town'] = $town;
            $data['result'] = $html['result'];
            return json(array('code'=>1,'msg'=>'获取定位成功','data'=>$data));
        }else{
            return json(array('code'=>0,'msg'=>'定位失败'));
        }

    }



	//智能识别地址
	public function aiarea(){
		$content= input('content','','trim,htmlspecialchars');	
		$addr_type = (int)$this->config['wxapp']['addr_type'];
		$addr_key = trim($this->config['wxapp']['addr_key']);
		if($addr_type == 0){
			$parArr = array('key'=>$addr_key,'text' => $content);	
			$result = $this->curl->post('https://apis.tianapi.com/addressparse/index',$parArr);
			$result = json_decode($result,true);//将json解析成数组
			$data = $result['result'];
			if($result['code'] == 200){
				$data['phone'] =  $data['mobile'];
				$data['area'] =  $data['district'];
				$data['addr'] =  $data['detail'];
				$data['address'] =  $data['detail'];
				$data['name'] =  $data['name'];
				if($data['province'] =='天津'){
					$data['province'] =  '天津市';	
				}elseif($data['province'] =='重庆'){
					$data['province'] =  '重庆市';	
				}elseif($data['province'] =='北京'){
					$data['province'] =  '北京市';	
				}elseif($data['province'] =='上海'){
					$data['province'] =  '上海市';	
				}else{
					$data['province'] =  $data['province'];
				}
				$data['city'] =  $data['city'];
				$data['type'] =  1;
				return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
			}else{
				return json(array('code'=>0,'msg'=>$result['msg']));
			}
		}elseif($addr_type == 1){
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
            if(strpos($showapi_res_body['detail'],$showapi_res_body['town']) !== false){
                $data['address'] = $showapi_res_body['detail'];
            }else{
                $data['address'] = $showapi_res_body['town'].$showapi_res_body['detail'];
            }
            if(strpos($showapi_res_body['detail'],$showapi_res_body['town']) !== false){
                $data['addr'] = $showapi_res_body['detail'];
            }else{
                $data['addr'] = $showapi_res_body['town'].$showapi_res_body['detail'];
            }
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
			$data['city'] =  $showapi_res_body['city'];
			$data['type'] =  1;
			$data['showapi_res_body'] =  $showapi_res_body;
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}elseif($addr_type == 2){
			
			$data['client_id'] = trim($this->config['wxapp']['client_id']);
			$data['client_secret'] = trim($this->config['wxapp']['client_secret']);
			$data['text'] = $content;
			$data['host'] = trim($this->config['site']['host']);
			$data['mobile'] = trim($this->config['site']['mobile']);
			$url = getHost().'/api/RequestApi/addressAnalysis';
			$result = $this->curl->post($url,json_encode($data));
			$result = json_decode($result,true);
			$body = $result['data'];
			
			if($result['code']==1){
				$data['phone'] = $body['phonenum'];
				$data['area'] =  $body['county'];
				$data['address'] = $body['town'].$body['detail'];
				$data['addr'] = $body['town'].$body['detail'];
				$data['name'] =  $body['person'];
				if($body['province'] =='天津'){
					$data['province'] =  '天津市';	
				}elseif($body['province'] =='重庆'){
					$data['province'] =  '重庆市';	
				}elseif($body['province'] =='北京'){
					$data['province'] =  '北京市';	
				}elseif($body['province'] =='上海'){
					$data['province'] =  '上海市';	
				}else{
					$data['province'] = $body['province'];
				}
				$data['city'] =  $body['city'];
				$data['type'] =  1;
				$data['result'] =  $body;
				return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
			}else{
				return json(array('code'=>0,'msg'=>$result['msg']));
			}
		}
	}
	
	
	
	public function add(){
		$name= input('name','','trim,htmlspecialchars');
		$phone= input('phone','','trim,htmlspecialchars');
		$mobile= input('mobile','','trim,htmlspecialchars');
		$province= input('province','','trim,htmlspecialchars');
		$city= input('city','','trim,htmlspecialchars');
		$area= input('area','','trim,htmlspecialchars');
        $getAddr= input('getAddr','','trim,htmlspecialchars');
        $uaddr= input('addr','','trim,htmlspecialchars');
		$address= input('address','','trim,htmlspecialchars');
		$is_default= input('is_default','','trim,htmlspecialchars');	
		$uid = $this->getUserId();
		$mode= input('mode','','trim,htmlspecialchars');
        $cate= input('cate','','trim,htmlspecialchars');
        $lat= input('lat','','trim,htmlspecialchars');
        $lng= input('lng','','trim,htmlspecialchars');
        $community_id= input('community_id','','trim,htmlspecialchars');
        $tc= input('tc','','trim,htmlspecialchars');
		
		$updateData['type'] = $mode;
		$updateData['name'] = $name;
		$updateData['linkMan'] = $name;
		$updateData['address'] = deleteHtml($address);
		$updateData['city'] =$city;		
		$updateData['province']  = $province;		
		$updateData['area']  = $area;	
		$updateData['phone']  = $phone;	
		$updateData['mobile']  = $mobile;		
		$updateData['user_id'] = $uid;
		$updateData['is_default'] = $is_default;
        $updateData['lat'] = $lat;
        $updateData['lng'] = $lng;
        $updateData['community_id'] = $community_id;
		$updateData['createTime'] = time();

        if($tc==11){
            $updateData['tc'] = 11;
            $updateData['addr'] = $uaddr;
            $updateData['getAddr'] = $getAddr;
        }

        if($tc!=9 && !$province){
            return json(array('code'=>'0','msg'=>'省份不能为空'));
        }
        if($tc!=9 && !$city){
            return json(array('code'=>'0','msg'=>'城市不能为空'));
        }
        if($tc!=9 && !$area){
            return json(array('code'=>'0','msg'=>'区县不能为空'));
        }
        if(!$address){
            return json(array('code'=>'0','msg'=>'地址不能为空'));
        }
        if($tc==11 && !$lat){
            return json(array('code'=>'0','msg'=>'经度不能为空'));
        }
        if($tc==11 && !$lng){
            return json(array('code'=>'0','msg'=>'纬度不能为空'));
        }
        if($cate=='4'||$cate=='5'||$cate=='7'){
            $c = (int)Db::name('user_addr')->where(array('cate'=>4,'user_id'=>$uid))->count();
            if($c>=3){
                return json(array('code'=>'0','data'=>'申请电商地址不能超过9个'));
            }
            $updateData['cate'] = $cate;
        }


		$addr_id = Db::name('user_addr')->insertGetId($updateData);
		
		$updateData['id'] = $addr_id;
		$updateData['sender_province'] = $province;
		$updateData['sender_city'] = $city;
		$updateData['sender_area'] = $area;
		$updateData['sender_address'] = $address;
		$updateData['sender_mobile'] = $mobile;
		$updateData['sender_name'] = $name;
		$updateData['sender_phone'] = $phone;
        $updateData['sender_addr'] = $uaddr;
        $updateData['sender_getAddr'] = $getAddr;
		
		$data['type'] = $mode;
		$data['info'] = $updateData;
		$u = Db::name('users')->where(array('user_id'=>$uid))->field('user_id,province,area,city')->find();
		$copy_area = Db::name('copy_area')->where(array('Name'=>$area))->field('Name,city_id,area_id')->find();
		if($copy_area['city_id'] && !$u['area']){
			$copy_city = Db::name('copy_city')->where(array('city_id'=>$copy_area['city_id']))->field('name,ParentId,city_id')->find();
			if($copy_city){
				$upDataUsers['user_id'] = $uid;
				$upDataUsers['province'] = $copy_city['ParentId'];
				$upDataUsers['area'] = $copy_area['area_id'];
				$upDataUsers['city'] = $copy_area['city_id'];
				Db::name('users')->update($upDataUsers);
			}
		}
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data,'info'=>$updateData));
	}
	
	//编辑地址
	public function edit(){
		$addr_id= input('addr_id','','trim,htmlspecialchars');
		$name= input('name','','trim,htmlspecialchars');
		$phone= input('phone','','trim,htmlspecialchars');
		$province= input('province','','trim,htmlspecialchars');
		$city= input('city','','trim,htmlspecialchars');
		$area= input('area','','trim,htmlspecialchars');
		$address= input('address','','trim,htmlspecialchars');
        $getAddr= input('getAddr','','trim,htmlspecialchars');
        $uaddr= input('addr','','trim,htmlspecialchars');
		$is_default= (int)input('is_default','','trim,htmlspecialchars');	
		$uid = $this->getUserId();
		$mobile= input('mobile','','trim,htmlspecialchars');
        $lat= input('lat','','trim,htmlspecialchars');
        $lng= input('lng','','trim,htmlspecialchars');
        $community_id= input('community_id','','trim,htmlspecialchars');
        $tc= input('tc','','trim,htmlspecialchars');
		
		$addr = Db::name('user_addr')->where(array('addr_id'=>$addr_id))->find();

		$updateData['addr_id'] = $addr_id;
		$updateData['name'] = $name;
		$updateData['linkMan'] = $name;
		$updateData['address'] = deleteHtml($address);
		$updateData['city'] =$city;		
		$updateData['province']  = $province;		
		$updateData['area']  = $area;	
		$updateData['phone']  = $phone;	
		$updateData['mobile']  = $mobile;		
		$updateData['user_id'] = $uid;
		$updateData['is_default'] = $is_default;
        $updateData['lat'] = $lat;
        $updateData['lng'] = $lng;
        $updateData['community_id'] = $community_id;
		$updateData['createTime'] = time();


        if($tc==11){
            $updateData['tc'] = 11;
            $updateData['addr'] = $uaddr;
            $updateData['getAddr'] = $getAddr;
        }
        if($tc!=11){
            $updateData['tc'] = 1;
        }
        if(!$name){
            return json(array('code'=>'0','msg'=>'姓名不能为空'));
        }
        if(!isPhone($phone) && !isMobile($phone) && !$mobile){
            return json(array('code'=>'0','msg'=>'手机号码格式不正确'));
        }
        if($tc!=11 && !$province){
            return json(array('code'=>'0','msg'=>'省份不能为空'));
        }
        if($tc!=11 && !$city){
            return json(array('code'=>'0','msg'=>'城市不能为空'));
        }
        if($tc!=11 && !$area){
            return json(array('code'=>'0','msg'=>'区县不能为空'));
        }
        if(!$address){
            return json(array('code'=>'0','msg'=>'地址不能为空'));
        }
        if($tc==11 && !$lat){
            return json(array('code'=>'0','msg'=>'经度不能为空'));
        }
        if($tc==11 && !$lng){
            return json(array('code'=>'0','msg'=>'纬度不能为空'));
        }
        $strlen = strlen($updateData['address']);
        if($strlen<=8){
            return json(array('code'=>'0','msg'=>'地址最少为4个汉字'));
        }


		
		$r = Db::name('user_addr')->update($updateData);
		
		$updateData['id'] = $addr_id;
		$updateData['sender_province'] = $province;
		$updateData['sender_city'] = $city;
		$updateData['sender_area'] = $area;
		$updateData['sender_address'] = $province.$city.$area.$address;
		$updateData['sender_mobile'] = $mobile;
		$updateData['sender_name'] = $name;
        $updateData['sender_addr'] = $uaddr;
        $updateData['sender_getAddr'] = $getAddr;
		
		$data['mode'] = $addr['type'];
		$data['info'] = $updateData;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function del(){
		$addr_id= input('addr_id','','trim,htmlspecialchars');
		$user_id= $uid = $this->getUserId();
		if(!$user_id){
			return json(array('code'=>'0','msg'=>'TOKEN不存在'));
		}
		if(!$addr_id){
			return json(array('code'=>'0','msg'=>'ID不存在'));
		}
		$rest = Db::name('user_addr')->where(array('user_id'=>$user_id,'addr_id'=>$addr_id))->find();
		if($rest){
			$r = Db::name('user_addr')->where(array('user_id'=>$user_id,'addr_id'=>$addr_id))->delete();
			if($r){
				return json(array('code'=>1,'msg'=>"删除成功",'data'=>$data));
			}else{
				return json(array('code'=>'0','msg'=>'删除失败'));
			}
		}else{
			return json(array('code'=>'0','msg'=>'地址不存在'));
		}
	}
	
	public function getUserAreaList(){
		$uid = $this->getUserId();
		$addkey = input('addkey','','trim,htmlspecialchars');
		$type = input('type','','trim,htmlspecialchars');
        $cate = input('cate','','trim,htmlspecialchars');
		$page = input('page','','trim,htmlspecialchars');
        $tc = (int)input('tc','','trim,htmlspecialchars');
		
		
		$map['closed'] =0;
		$map['user_id'] =$uid;
		if($addkey && $addkey != '' ){
            $map['address'] = array('LIKE', '%'.$addkey.'%');
        }
		if($type){
            $map['type'] = $type;
        }


        if($tc==11){
            $map['tc'] = 11;
        }elseif($cate==4){
            $map['cate'] = $cate;
        }elseif($cate==5){
            $map['cate'] = 4;
        }elseif($cate==6){
            $map['cate'] = 4;
        }else{
            $map['cate'] = 1;
        }

		$count = Db::name('user_addr')->where($map)->count();
		$Page = new \Page3($count,20);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('user_addr')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('addr_id desc')->select(); 	
			foreach($list as $k=>$v){
				$list[$k]['id'] = $v['addr_id'];
				$list[$k]['sender_name'] = $v['name'];
				$list[$k]['sender_phone'] = $v['phone'];
				$list[$k]['sender_mobile'] = $v['mobile'];
				$list[$k]['sender_address'] = $v['province'].$v['city'].$v['area'].$v['address'];
				$list[$k]['sender_province'] = $v['province'];
				$list[$k]['sender_area'] = $v['area'];
				$list[$k]['sender_city'] = $v['city'];
                $list[$k]['address'] = $v['address'];
                $list[$k]['addr'] = $v['addr'];
                $list[$k]['getAddr'] = $v['getAddr'];
                $list[$k]['lat'] = $v['lat'];
                $list[$k]['lng'] = $v['lng'];
			}
		}
		$data['list'] = $list;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	//得物地址
	public function getfixed(){
		$type = (int)input('type','','trim,htmlspecialchars');
		$classify = (int)input('classify','','trim,htmlspecialchars');
		if($classify==14){
			$classify=2;
		}
		$list = Db::name('user_addr_dewu')->where(array('type'=>$classify))->select();
		foreach($list as $k => $v){
			$list[$k]['sender_address'] = $v['sender_province'].$v['sender_city'].$v['sender_area'].$v['sender_address'];
		}
		$data['list'] = $list;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	public function getUserArea(){
		$id = input('id','','trim,htmlspecialchars');
		$v = Db::name('user_addr')->where(array('addr_id'=>$id))->find();
		
		$info['sender_name'] = $v['name'];
		$info['sender_phone'] = $v['phone'];
		$info['sender_mobile'] = $v['mobile'];
        $info['address'] = $v['address'];
        $info['addr'] = $v['addr'];
        $info['getAddr'] = $v['getAddr'];
		$info['sender_province'] = $v['province'];
		$info['sender_city'] = $v['city'];
		$info['sender_area'] = $v['area'];
        $info['lat'] = $v['lat'];
        $info['lng'] = $v['lng'];
        $info['tc'] = $v['tc'];
		$data['info']= $info;			
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	//提交货运信息
	public function queryAdd(){
		$data['sender_province'] = input('sender_province','','trim,htmlspecialchars');
		$data['sender_city'] = input('sender_city','','trim,htmlspecialchars');
		$data['sender_area'] = input('sender_area','','trim,htmlspecialchars');
		$data['recipients_province'] = input('recipients_province','','trim,htmlspecialchars');
		$data['recipients_city'] = input('recipients_city','','trim,htmlspecialchars');
		$data['recipients_area'] = input('recipients_area','','trim,htmlspecialchars');
		$data['mobile'] = input('mobile','','trim,htmlspecialchars');
		if(!$data['mobile']){
			return json(array('code'=>0,'msg'=>'手机号不能为空'));
		}
		$data['long'] = input('long','','trim,htmlspecialchars');
		$data['width'] = input('width','','trim,htmlspecialchars');
		$data['height'] = input('height','','trim,htmlspecialchars');
		$data['user_id'] = $this->getUserId();
		$data['create_time'] = time();
		
		
		
		$id = Db::name('express_transport')->insertGetId($data);//新建表
		if(!$id){
			return json(array('code'=>0,'msg'=>'提交货运信息时报'));
		}else{
			model('Sms')->sendSmsExpressTransport($data);//发短信
			return json(array('code'=>1,'msg'=>"提交成功",'data'=>$choosecom));
		}
	}



    public function goTrackingApi(){
        $logisticID = input('logisticID','','trim,htmlspecialchars');
        $info = Db::name('express_order')->where(array('id'=>$logisticID))->find();
        $data['tranceList'] =model('ExpressOrder')->logisticsInfo($info,2,'');
        return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
    }
	
	
	public function getOrderInfo($v,$t=0,$s=0){
		$v['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
		$logoUrl = model('ExpressOrder')->logoUrl($v['kuaidi']);
		$v['logoUrl'] = $logoUrl['photo'];//回复
		$v['is_repay'] = '';//回复
		$v['id'] = $v['id'];
		$v['order_id'] = $v['id'];
		$v['mailNo'] = $v['deliveryId']?$v['deliveryId']:$v['id'];
		$v['expressNo'] = $v['deliveryId']?$v['deliveryId']:$v['id'];
		$v['yuyuetime'] = $v['yuyuetime']&&$v['yuyuetime']!=''&&$v['yuyuetime']!=' '?$v['yuyuetime']:false;
		$v['express_name'] = $logoUrl['expressName'];
		$v['sender_province'] = $v['sendCity'];
		$v['sender_name'] = $v['sendName'];
		$v['sender_phone'] = $v['sendMobile'];
		if($v['is_piliang']==3){
			$v['sender_address'] = $v['senderProvince']. $v['senderCity']. $v['senderArea'].$v['sendAddress'];
		}else{
			$v['sender_address'] = $v['sendAddress'];
		}

        $p = Db::name('express_order_photo')->where(array('order_id'=>$v['id']))->select();
        foreach($p as $k2 => $v2){
            $imgUrl[$k2] = $v2['photo'];
        }
        if($imgUrl){
            $v['imgUrl'] = $imgUrl;
            $v['imgU'] = 1;
        }else{
            $v['imgUrl'] = array();
            $v['imgU'] = 0;
        }

        $ps = Db::name('express_order_photos')->where(array('order_id'=>$v['id']))->select();
        foreach($ps as $k3 => $v3){
            if($v3['photo']){
                $imgUrls[$k3] = $v3['photo'];
            }
        }
        if($imgUrls){
            $v['imgUrls'] = $imgUrls;
            $v['imgUs'] = 1;
        }else{
            $v['imgUrls'] = array();
            $v['imgUs'] = 0;
        }

		
		$v['logisticID'] = $v['id'];//渠道单号
		$v['courier'] = $v['realOrderState'];//快递员
		$v['courier_phone'] = $v['realOrderMobile'];//快递员
		
		$v['recipients_province'] = $v['receiveCity'];
		$v['recipients_name'] = $v['receiveName'];
		$v['recipients_phone'] = $v['receiveMobile'];
		if($v['is_piliang']==3){
			$v['recipients_address'] = $v['receiveProvince']. $v['receiveCity']. $v['receiveArea'].$v['receiveAddress'];
		}else{
			$v['recipients_address'] = $v['receiveAddress'];
		}
		
		
		$v['cargoName'] = $v['cargoName'] ? $v['cargoName'] : '日用品';//寄托物
		$v['totalWeight'] = $v['wight'];//下单重量
		$v['sender_money'] = round($v['sumMoneyYuan']/100,2);//运单运费
		$v['coupon_pmt'] = round($v['coupon_pmt']/100,2);//已优惠
		$v['over_money'] = round($v['diffMoneyYuan']/100,2);//需补运费
		$v['order_money'] = round($v['sumMoneyYuan']/100,2);//sumMoneyYuan合计支付
		
		$v['charged_weight'] = $v['review_weight'];
		$v['cost_type'] = 0;
		$v['pay_money'] = round($v['sumMoneyYuan']/100,2);
		$v['money'] = round($v['diffMoneyYuan']/100,2);
		
		$v['insurancePrice'] = round($v['insurancePrice']/100,2);
		$v['insuranceValue'] = round($v['insuranceValue']/100,2);
		$v['packageServicePrice'] = round($v['packageServicePrice']/100,2);


        $v['pdfUrl'] = config_weixin_img($v['pdfUrl']);
		$v['typename'] = $v['message'] ? $v['message'] : '暂无';//异常类型
		$v['ctime'] = date("Y-m-d H:i:s",$v['pay_time']);
		
		
		$options = Db::name('express_order_options')->where(array('order_id'=>$v['id']))->limit(0,100)->order('sort asc')->select();
		if($options){
			$v['is_options'] = 1;
		}
		$v['options'] = $options;
		
		if($t==1 && $v['diffStatus'] == 1){
			 $statusName = '待补差价';
			 $v['is_nocommission'] = '1';
			 $button_arr[0]['name'] = '补差价';
		}elseif($v['orderStatus'] == 0){
			 $statusName = '未付款';
			 $button_arr[0]['name'] = '立即支付';
			 $button_arr[1]['name'] = '撤销运单';
			 $button_arr[2]['name'] = '删除';
		}elseif($v['orderStatus'] == 1){
			 $statusName = '已付款';
			 $v['is_nocommission'] = '1';
			 $button_arr[0]['name'] = '再来一单';
			 $button_arr[1]['name'] = '撤销运单';
             $button_arr[2]['name'] = '通知收件人';
		}elseif($v['orderStatus'] == 2){
			 $statusName = '已接单';
			 $v['is_nocommission'] = '1';
			 $button_arr[0]['name'] = '再来一单';
			 $button_arr[1]['name'] = '撤销运单';
             $button_arr[2]['name'] = '物流轨迹';
            $button_arr[3]['name'] = '通知收件人';
		}elseif($v['orderStatus'] == 3){
			 $statusName = '在路上';
			 $button_arr[0]['name'] = '再来一单';
             $button_arr[1]['name'] = '物流轨迹';
             if($v['type'] == 2) {
                $button_arr[2]['name'] = '附件';
                $button_arr[3]['name'] = '通知收件人';
             }else{
                 $button_arr[2]['name'] = '通知收件人';
             }
			 $v['is_nocommission'] = '1';
		}elseif($v['orderStatus'] == 4){
			 $statusName = '已签收';
			 $button_arr[0]['name'] = '再来一单';
             $button_arr[1]['name'] = '物流轨迹';
             if($v['type'] == 2) {
                $button_arr[2]['name'] = '附件';
                $button_arr[3]['name'] = '通知收件人';
             }else{
                 $button_arr[2]['name'] = '通知收件人';
             }
		}elseif($v['orderStatus'] == 5){
			 $statusName = '已取消退款';
			 $button_arr[0]['name'] = '删除';
		}elseif($v['orderStatus'] == -1){
			 $statusName = '已取消';
			 $button_arr[0]['name'] = '删除';
		}elseif($v['orderStatus'] == 9){
			 $statusName = '订单异常';
			 $button_arr[0]['name'] = '撤销运单';
		}else{
			 $statusName = '未知状态';
		}
		
	
		$v['status'] = $statusName;
		$v['button_arr'] = $button_arr;
		if($s==1){
			$v['logisticsInfo'] = model('ExpressOrder')->logisticsInfo($v);
		}else{
			$v['logisticsInfo'] = array();
		}
		$v['is_logistics'] = 0;
		return $v;
	}


    //上传图片
    public function orderUploadApi(){
        $recipients_id = input('recipients_id','','trim,htmlspecialchars');
        $images = input('imgUrls','','trim');
        $images = @substr($images,1);
        $images = @substr($images,0,-1);
        $images = @explode(",",$images);
        $img = array();
        foreach($images as $k=>$v){
            $s = @substr($v,1);
            $s = @substr($s,0,-1);
            $img[$k] = $s;
        }
        $imgs = $img;
        $photo = Db::name('express_order_photos')->where(array('order_id'=>$recipients_id))->find();
        if($photo){
            return json(array('code'=>0,'msg'=>'请不要重复上传图片'));
        }
        if(!$imgs){
            return json(array('code'=>0,'msg'=>'请选择图片'));
        }
        $i=0;
        foreach ($imgs as $k=>$v){
            if($v){
                $data['order_id'] = $recipients_id;
                $data['photo'] = $v;
                $data['create_time'] = time();
                $i++;
                Db::name('express_order_photos')->insertGetId($data);
            }
        }
        if($i){
            return json(array('code'=>1,'msg'=>"上传成功【". $i."】张图片",'data'=>$data));
        }else{
            return json(array('code'=>0,'msg'=>'上传失败'));
        }
    }
	
	//订单列表
	public function lists(){
		$mailNo = input('mailNo','','trim,htmlspecialchars');
		$uid = $this->getUserId();
		$receiveMobile = Db::name('users')->where(array('user_id'=>$uid))->value('mobile');
		
		$page = input('page','','trim,htmlspecialchars');
		$limit = input('limit','','trim,htmlspecialchars');
		$handle = input('handle','','trim,htmlspecialchars');//1未处理1已处理
		
		$map = array('user_id'=>$uid,'closed'=>0);
		if($mailNo){
			//$handle = 6;
			$map['deliveryId|expressNo|sendAddress|receiveAddress|receiveMobile|sendMobile'] = array('LIKE','%'.$mailNo.'%');
		}
		if($handle == 2){
			$map['orderStatus'] = array('in',array(1,2));
		}
		if($handle == 3){
			$map['orderStatus'] = 3;
		}
		if($handle == 4){
			$map['orderStatus'] = 4;
		}
		if($handle == 5){
			$map['orderStatus'] = array('in',array(5,9,-1));
		}
		if($handle == 6){
			$map['receiveMobile'] = $receiveMobile;
		}
		$count = Db::name('express_order')->where($map)->count();
		
		if($page == 1){
			 $firstRow = 0;
			 $listRows = $limit;
		}else{
			 $firstRow = $page*$limit;
			 $listRows = $limit;
		}
		
		$Page = new \Page3($count,5);
        $show = $Page->show();
		if($Page->totalPages < $page){
			if($mailNo){
				$getLogisticsInfo = $this->getLogisticsInfo($mailNo);
				$list = $getLogisticsInfo;
				$data['logisticsInfo'] = $list;
			}else{
				 $list = array();
			}
        }else{
			$list = Db::name('express_order')->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $k => $v){
				$getOrderInfo = $this->getOrderInfo($v,0,0);
				$list[$k] = $getOrderInfo;
			}
		}
		$data['Jicount'] = $count;
		$data['Shcount'] = '0';
		$data['handle'] = $handle;
		$data['list'] = $list;
		$data['activeTabJiNum1'] = (int)Db::name('express_order')->where(array('user_id'=>$uid,'orderStatus'=>array('in',array(1,2)),'closed'=>0))->count();
		$data['activeTabJiNum2'] = (int)Db::name('express_order')->where(array('user_id'=>$uid,'orderStatus'=>3,'closed'=>0))->count();
		$data['activeTabJiNum3'] = (int)Db::name('express_order')->where(array('user_id'=>$uid,'orderStatus'=>4,'closed'=>0))->count();
		$data['activeTabJiNum4'] = (int)Db::name('express_order')->where(array('user_id'=>$uid,'orderStatus'=>array('in',array(5,9,-1)),'closed'=>0))->count();
		
		
		if($receiveMobile){
			$data['activeTabJiNum5'] = (int)Db::name('express_order')->where(array('receiveMobile'=>$receiveMobile,'closed'=>0))->count();
		}else{
			$data['activeTabJiNum5'] = 0;
		}
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data,'firstRow'=>$Page->firstRow,'listRows'=>$Page->listRows));
	}
	
	
	
	public function getLogisticsInfo($mailNo){
		$post_data = array();
		$post_data["customer"] = $this->config['config']['express_api_customer'];
		$key= $this->config['config']['express_api_key'];
		
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
		$result['logistics_info']= $result['com'];
		$result['mailNo']= $mailNo;
		$result['lanshou_time']= '';
		$result['express_status']= '';
		return $result;
	}
	
	
	
	
	
	
	
	//订单详情
	public function detail(){
		$recipients_id = input('recipients_id','','trim,htmlspecialchars');
		$info = Db::name('express_order')->where(array('id'=>$recipients_id))->find();
		$info = $this->getOrderInfo($info,0,1);
		$data = $info;
		$data['logistics_info'] =model('ExpressOrder')->logisticsInfo($info,0,1);
		$data['logistics'] =model('ExpressOrder')->logisticsInfo($info,0,1);
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	//删除订单
	public function cancels(){
		$data = array();
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'msg'=>'TOKEN失效请重新登录'));
		}
		$recipients_id = input('recipients_id','','trim,htmlspecialchars');
		$id = (int) $recipients_id;
		if(!$id){
			return json(array('code'=>0,'msg'=>'id不存在'));
		} 
		if(!($sign = Db::name('express_order')->where(array('id'=>$id))->find())){
			return json(array('code'=>0,'msg'=>'订单不存在'));
		}
		if($sign['orderStatus'] == 1){
			return json(array('code'=>0,'msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		if($sign['orderStatus'] == 2){
			return json(array('code'=>0,'msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		if($sign['orderStatus'] == 3){
			return json(array('code'=>0,'msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		if($sign['orderStatus'] == 4){
			return json(array('code'=>0,'msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		if($sign['orderStatus'] == 9){
			return json(array('code'=>0,'msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		
		if($sign['user_id'] != $uid){
			return json(array('code'=>0,'msg'=>'非法操作'));
		}
		$up = Db::name('express_order')->where(array('id'=>$id))->update(array('closed'=>1));
		if($up){
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}else{
			return json(array('code'=>0,'msg'=>'操作失败'));
		}
	}
	
	//退费补差
	public function myhandle(){
		$mailNo = input('mailNo','','trim,htmlspecialchars');
		$uid = $this->getUserId();
		$page = input('page','','trim,htmlspecialchars');
		$limit = input('limit','','trim,htmlspecialchars');
		$handle = input('handle','','trim,htmlspecialchars');//我的寄件
		
		$map = array('user_id'=>$uid,'orderStatus'=>array('in',array(0,1,2,3,4,5,8)),'diffMoneyYuan'=>array('GT',0),'closed'=>0);
		if($mailNo){
			$map['expressNo|sendAddress|receiveAddress|receiveMobile|sendMobile'] = array('LIKE','%'.mailNo.'%');
		}
		if($handle == 1){
			$map['diffStatus'] = 1;
		}
		if($handle == 2){
			$map['diffStatus'] = 2;
		}
		$count = Db::name('express_order')->where($map)->count();
		$Page = new \Page3($count,$limit);
        $show = $Page->show();
		
		$list = Db::name('express_order')->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k => $v){
			$getOrderInfo = $this->getOrderInfo($v,1);
			$list[$k] = $getOrderInfo;
		}
		$data['list'] = $list;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function repay(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	//补差价
	public function tosettle(){
		$logisticID = input('logisticID','','trim,htmlspecialchars');
		$overid = input('overid','','trim,htmlspecialchars');
		$uid = $this->getUserId();
		$data['order_id'] = $overid;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	//撤销订单
	public function cancel(){
		$data = array();
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'msg'=>'TOKEN失效请重新登录'));
		}
		$recipients_id = input('recipients_id','','trim,htmlspecialchars');
		$reason = input('reason','','trim,htmlspecialchars');
		$id = (int) $recipients_id;
		if(!$id){
			return json(array('code'=>0,'msg'=>'id不存在'));
		} 
		if(!($sign = Db::name('express_order')->where(array('id'=>$id))->find())){
			return json(array('code'=>0,'msg'=>'订单不存在'));
		}
		
		if($sign['orderStatus'] == 3){
			return json(array('code'=>0,'msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		if($sign['orderStatus'] == 4){
			return json(array('code'=>0,'msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		if($sign['orderStatus'] == 5){
			return json(array('code'=>0,'msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		if($sign['orderRightsStatus'] != 0){
			return json(array('code'=>0,'msg'=>'退款订单状态【'.$sign['orderRightsStatus'].'】不正确'));
		}
		if($sign['user_id'] != $uid){
			return json(array('code'=>0,'msg'=>'非法操作'));
		}
		$cancel = model('ExpressOrder')->cancel($sign,$id,$reason);
		if($cancel == false){
			return json(array('code'=>0,'msg'=>'取消失败'.model('ExpressOrder')->getError()));
		}else{
			return json(array('code'=>1,'msg'=>"退款成功",'data'=>$data));
		}
	}
	
	public function copy(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	//余额日志
	public function mybalance(){
		$getMoneyTypes = model('Users')->getMoneyTypes();
		$uid = $this->getUserId();
		$page = input('page','','trim,htmlspecialchars');
		$limit = input('limit','','trim,htmlspecialchars');
		
		$map = array('user_id'=>$uid);
		$count = Db::name('user_money_logs')->where($map)->count();
		
		if($page == 1){
			 $firstRow = 0;
			 $listRows = $limit;
		}else{
			 $firstRow = $page*$limit;
			 $listRows = $limit;
		}
		
		$Page = new \Page3($count,5);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('user_money_logs')->where($map)->order('log_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $k => $v){
				$list[$k]['memo'] = $getMoneyTypes[$v['type']];
				$list[$k]['id'] = $v['log_id'];
				$list[$k]['money'] = round($v['money']/100,2);
				$list[$k]['curr_balance'] = round($v['new_num']/100,2);
				$list[$k]['createtime'] =  date("Y-m-d H:i:s",$v['create_time']);
			}
		}
		$data['list'] = $list;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	//分佣信息
	public function mycommission(){
		$getorderStatus = model('Setting')->getorderStatus();
		$uid = $this->getUserId();
		$page = input('page','','trim,htmlspecialchars');
		$limit = input('limit','','trim,htmlspecialchars');
		$map = array('user_id'=>$uid);
		$count = Db::name('user_profit_logs')->where($map)->count();
		if($page == 1){
			 $firstRow = 0;
			 $listRows = $limit;
		}else{
			 $firstRow = $page*$limit;
			 $listRows = $limit;
		}
		$Page = new \Page3($count,10);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('user_profit_logs')->where($map)->order('log_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $k => $v){
				$list[$k]['id'] = $v['log_id'];
				$list[$k]['money'] = round($v['money']/100,2);
				$express_order =  Db::name('express_order')->where(array('id'=>$v['order_id']))->find();
				if($v['is_separate'] == 0){
					$list[$k]['memo_type'] = 1;
					$list[$k]['memo'] =$v['info']. '【等待分佣】';
				}
				if($v['is_separate'] == 1){
					$list[$k]['memo_type'] =2;
					$list[$k]['memo'] =$v['info']. '【已分佣】';
				}
				$list[$k]['ctime'] =  date("Y-m-d H:i:s",$v['create_time']);
			}
		}
		$bg_time = strtotime(TODAY);
		$str = '-30 day';
        $bg_time_yesterday = strtotime(date('Y-m-d', strtotime($str)));
		
		$data['day_invite'] = (int) Db::name('users')->where(array('reg_time'=>array(array('ELT',time()),array('EGT',$bg_time)),'parent_id'=>$uid))->count();
		$data['month_invite'] = (int) Db::name('users')->where(array('reg_time'=>array(array('ELT',time()), array('EGT',$bg_time_yesterday)),'parent_id'=>$uid))->count();;
		$data['sum_invite'] =(int) Db::name('users')->where(array('parent_id'=>$uid))->count();
		
		$ishave = Db::name('user_profit_logs')->where(array('user_id'=>$uid,'is_separate'=>1))->sum('money');
		$data['ishave'] = round($ishave/100,2);
		
		$nohave = Db::name('user_profit_logs')->where(array('user_id'=>$uid,'is_separate'=>0))->sum('money');
		$data['nohave'] = round($nohave/100,2);
		$data['list'] = $list;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	public function comdetail(){
		$log_id = (int)input('commission_id','','trim,htmlspecialchars');
		$data = Db::name('user_profit_logs')->where(array('log_id'=>$log_id))->find();
		$data['id'] =$data['log_id'];
		$data['money'] = round($data['money']/100,2);
		$data['memo'] =$data['info'];
		$data['ctime'] =  date("Y-m-d H:i:s",$data['create_time']);
		$data['create_time'] =  date("Y-m-d H:i:s",$data['create_time']);
		$data['logisticID'] =$data['order_id'];
		$u1 = Db::name('users')->where(array('user_id'=>$data['user_id']))->field('user_id,nickname')->find();
		$data['receipt_name'] = $u1['nickname'];
		$order = Db::name('express_order')->where(array('id'=>$data['order_id']))->find();
		$u2 = Db::name('users')->where(array('user_id'=>$order['user_id']))->field('user_id,nickname')->find();
		$data['contribute_name'] = $u2['nickname'];
		$getorderStatus = model('Setting')->getorderStatus();
		$data['status'] =$getorderStatus[$order['orderStatus']];
		$data['totalWeight'] =$order['wight'];
		$data['review_weight'] =$order['review_weight'];
		$data['charged_weight'] ='';
		$data['sender_money'] =round($order['sumMoneyYuan']/100,2);
		if($data['is_separate'] == 0){
			$data['is_expire'] ='等待分佣';
		}
		if($data['is_separate'] == 1){
			$data['is_expire'] ='已分佣';
		}
		$data['remark'] =$data['info'];
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	public function mywithdraw(){
		$uid = $this->getUserId();
		$page = input('page','','trim,htmlspecialchars');
		$limit = input('limit','','trim,htmlspecialchars');
		
		$map = array('user_id'=>$uid);
		$count = Db::name('users_cash')->where($map)->count();
		
		if($page == 1){
			 $firstRow = 0;
			 $listRows = $limit;
		}else{
			 $firstRow = $page*$limit;
			 $listRows = $limit;
		}
		$Page = new \Page3($count,5);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('users_cash')->where($map)->order('cash_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $k => $v){
				if($v['status'] == 0){
					$apply_status = '审核中';
				}elseif($v['status'] == 1){
					$apply_status = '已通过';
				}elseif($v['status'] == 2){
					$apply_status = '已拒绝';
				}
				$list[$k]['apply_status'] = $apply_status;
				$list[$k]['id'] = $v['cash_id'];
				$list[$k]['money'] = round($v['money']/100,2);
				$list[$k]['is_pay'] =$v['info'];
				$list[$k]['create_time'] =  date("Y-m-d H:i:s",$v['addtime']);
			}
		}
		$data['list'] = $list;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	
	public function recharge(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'msg'=>'uid不能为空'));
		}
		$money = (int)input('money','','trim,htmlspecialchars');
		if($money <= 0){
			return json(array('code'=>0,'msg'=>'价格有误'));
		}
		$scene = input('scene','','trim,htmlspecialchars');
		$rank_id = input('rank_id','','trim,htmlspecialchars');
		
		//p($rank_id);die;
		
		$pint = input('pint','','trim,htmlspecialchars');
		
		
		if($scene == 'vip'){
			$vip = Db::name('user_rank')->where(array('rank_id'=>$rank_id))->value('price');	
			$money = $vip;
			if($money <= 0){
				return json(array('code'=>0,'msg'=>'价格有误1'));
			}
			$type = 'vip';
			$info = 'VIP购买';
		}else{
			$money = $money*100;
			$type = 'money';
			$info = '余额充值';
		}
		if($pint=='mp-toutiao'){
			$code = 'toutiao';
		}else{
			$code = 'wxapp';
		}
		
		$need_pay = $money;
		$logs = array(
			'type' => $type, 
			'types' => '1', 
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
			$out_trade_no = $logs['log_id'];
			$ttPay = new \TtPay($this->config['toutiao']['AppID'],$this->config['toutiao']['token'],$this->config['toutiao']['SALT'],$out_trade_no,$info,$need_pay);//支付接口
			$pay = $ttPay->order();
			if($pay['err_no'] != '0'){
				return json(array('code'=>0,'msg'=>'抖音预支付失败'));
			}
			return json(array('code'=>1,'msg'=>"抖音支付下单成功",'data'=>$pay));
		}else{
			$connect = Db::name('connect')->where(array('uid'=>$uid))->order(array('connect_id'=>'desc'))->find();	
			$WX_OPENID = $connect['openid'] ? $connect['openid'] : $connect['open_id'];	
			$Payment = model('Payment')->getPayment('wxapp');
			if(!$Payment){
				return json(array('code'=>0,'msg'=>'支付信息不存在'));
			}
			$out_trade_no = $logs['log_id'].'-'.time();
			if(!$WX_OPENID){
				return json(array('code'=>0,'msg'=>'WX_OPENID不能为空'));
			}
			$weixinpay = new \Wxpay($this->config['wxapp']['appid'],$WX_OPENID,$Payment['mchid'],$Payment['appkey'],$out_trade_no,$info,$need_pay);//支付接口
			$return = $weixinpay->pay();
			if($return['package'] == 'prepay_id='){
				return json(array('code'=>0,'msg'=>'预支付失败:'.$return['rest']['return_msg'].''.$return['rest']['err_code_des']));
			}
			$data['timeStamp']= $return['timeStamp'];
			$data['nonceStr'] =$return['nonceStr'];
			$data['package'] =$return['package'];
			$data['signType'] = 'MD5';
			$data['paySign'] = $return['paySign'];
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}
	}
	
	
	
	public function ttrecharge(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	//申请
	
	public function withdraw(){
		$uid = $this->getUserId();
		$money = input('money','','trim,htmlspecialchars');
		$info = input('info','','trim,htmlspecialchars');
		if($info == 'undefined'){
			return json(array('code'=>0,'msg'=>'提现说明不能为空'));
		}
		$money = $money*100;
		$u = Db::name('users')->where(array('user_id'=>$uid))->find();
		if($u['money'] < $money){
			return json(array('code'=>0,'msg'=>'余额不足不能提现'));
		}
		if($detail['is_lock'] == 1){
			return json(array('code'=>0,'msg'=>'您的账户已被锁，暂时无法提现'));
		}
		if($money <100){
			return json(array('code'=>0,'msg'=>'提现金额不能低于1元'));
		}
		
		$alipay_account = input('alipay_account','','trim,htmlspecialchars');
		$alipay_real_name = input('alipay_real_name','','trim,htmlspecialchars');
		$draw_type = (int)input('draw_type','','trim,htmlspecialchars');
		
		
		//整合图片
		$images = input('imgUrl','','trim');
		$images = @substr($images,1);
		$images = @substr($images,0,-1);
		$images = @explode(",",$images);
		$img = array();
		foreach($images as $k=>$v){
			$s = @substr($v,1);
			$s = @substr($s,0,-1);
			$img[$k] = $s;
		}
		$bank_name = $img[0];
		
		
		if($draw_type==0){
			$code = 'weixin';
		}
		if($draw_type==1){
			$code = 'alipay';
		}
		$data['account'] = $u['nickname'];
		$data['bank_name'] = $bank_name;
        $data['user_id'] = $uid;
		$data['shop_id'] = 0;
        $data['money'] = $money - $commission;//实际到账
		$data['commission'] =$commission;//手续费
		$data['info'] = $info;
		$data['re_user_name'] = '未填写';
		$data['alipay_account'] =$alipay_account;
		$data['alipay_real_name'] = $alipay_real_name;
		$data['bank_num'] = '未填写';
		$data['bank_realname'] = '未填写';
        $data['type'] = 'user';
        $data['addtime'] = time();
		$data['code'] = $code;
		
		//写入数据库
		if($cash_id = Db::name('users_cash')->insertGetId($data)){
			//扣除资金
			model('Users')->addMoney($uid,-$money,$data['info'],3);
			return json(array('code'=>1,'msg'=>"提现成功",'data'=>$data));
		}
		return json(array('code'=>0,'msg'=>'操作错误'));
	}
	
	public function baldetail(){
		$balanace_id = input('balanace_id','','trim,htmlspecialchars');
		
		$data= Db::name('user_money_logs')->where(array('log_id'=>$balanace_id))->find();
		$u = Db::name('users')->where(array('user_id'=>$data['user_id']))->find();
		
		$data['money'] = round($data['money']/100,2);
		$data['affiliated'] = $data['log_id'];
		$data['username'] = $u['nickname'];
		$data['after'] =  round($data['new_num']/100,2);
		$data['memo'] = $data['intro'];
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	//海报
	public function poster(){
		$uid = $this->getUserId();
		$pint = input('pint','','trim,htmlspecialchars');
		$page="pages/index/index";//路径
		$width = '200';
		for($i=0; $i<3; $i++){
			$poster_url[$i]['key'] = $i+1;
			$poster_url[$i]['url'] = model('Api')->getWxappPoster($uid,$page,$width,$i+1,$pint);
		}
		$data['poster_url'] = $poster_url;
		$data['profit'] = $this->config['profit'];
		
		$url_link = model('Weixin')->getWxappUrlLink($uid);
		$data['url_link'] = $url_link;
		$data['appid'] = $this->config['wxapp']['appid'];
		$data['url_info'] = $this->config['profit']['url_info']?$this->config['profit']['url_info']:'寄快递上门取件，运费5元起，顺丰，京东，德邦，韵达，等上门取件';
		
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	//邀请海报
	public function posterCre(){
		$uid = $this->getUserId();
		$page="pages/index/index";//路径
		$width = '200';
		$poster_key = input('poster_key','','trim,htmlspecialchars');
		$u = Db::name('users')->where(array('user_id'=>$uid))->find(); 
		if($poster_key == 1 && empty($u['qrcode1'])){
			$user_poster_url = model('Api')->getWxappPoster($uid,$page,$width,1);
		}elseif($poster_key == 1 && !empty($u['qrcode1'])){
			$user_poster_url = $u['qrcode1'];
		}
		if($poster_key == 2 && empty($u['qrcode2'])){
			$user_poster_url = model('Api')->getWxappPoster($uid,$page,$width,2);
		}elseif($poster_key == 2 && !empty($u['qrcode2'])){
			$user_poster_url = $u['qrcode2'];
		}
		if($poster_key == 3 && empty($u['qrcode3'])){
			$user_poster_url = model('Api')->getWxappPoster($uid,$page,$width,3);
		}elseif($poster_key ==3 && !empty($u['qrcode3'])){
			$user_poster_url = $u['qrcode3'];
		}
		$data['user_poster_url'] = $user_poster_url;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	public function couponList(){
		$uid = $this->getUserId();
		$list3 = Db::name('coupon')->where(array('type'=>0,'closed'=>0))->limit(0,30)->select(); 	
		foreach($list3 as $k=>$v){
			$list3[$k]['id'] = $v['coupon_id'];
			$list3[$k]['money'] = round($v['reduce_price']/100,2);
			$list3[$k]['name'] = $v['title'];
			$list3[$k]['remark'] = $v['intro'];
			$list3[$k]['etime'] = $v['expire_date'];
			$list3[$k]['stime'] = TODAY;
			$list3[$k]['counts'] = (int)$v['number']==0 ? 1: (int)$v['number'];
			$list3[$k]['root'] = round($v['price']/100,2);
			$list3[$k]['buy_money'] = round($v['money']/100,2);
			$list3[$k]['cash_points'] = $v['integral'];
			$list3[$k]['photo'] = config_weixin_img($v['photo']);
			if($v['limit_num']){
				$list3[$k]['conditions_that'] = '第'.$v['limit_num'].'单可用';
			}elseif($v['full_price']){
				$list3[$k]['conditions_that'] = '满'.round($v['full_price']/100,2).'可用';
			}else{
				$list3[$k]['conditions_that'] = '无门槛优惠券';
			}
			if($v['title'] == '新人有礼'){
				$is_left =1;
			}
			if($v['title'] == '寄件返礼'){
				$is_left =0;
			}
			if($v['title'] == '满额返礼'){
				$is_left =0;
			}
			$list3[$k]['is_left'] = $is_left;
		}
		
		
		$list = Db::name('coupon')->where(array('type'=>4,'closed'=>0))->limit(0,30)->select(); 	
		foreach($list as $k=>$v){
			$list[$k]['id'] = $v['coupon_id'];
			$list[$k]['money'] = round($v['reduce_price']/100,2);
			$list[$k]['name'] = $v['title'];
			$list[$k]['remark'] = $v['intro'];
			$list[$k]['etime'] = $v['expire_date'];
			$list[$k]['stime'] = TODAY;
			$list[$k]['counts'] = (int)$v['number']==0 ? 1: (int)$v['number'];;
			$list[$k]['root'] = round($v['price']/100,2);
			$list[$k]['buy_money'] = round($v['money']/100,2);
			$list[$k]['cash_points'] = $v['integral'];
			$list[$k]['photo'] = config_weixin_img($v['photo']);
			if($v['limit_num']){
				$list[$k]['conditions_that'] = '第'.$v['limit_num'].'单可用';
			}elseif($v['full_price']){
				$list[$k]['conditions_that'] = '满'.round($v['full_price']/100,2).'可用';
			}else{
				$list[$k]['conditions_that'] = '无门槛优惠券';
			}
			if($v['title'] == '新人有礼'){
				$is_left =1;
			}
			if($v['title'] == '寄件返礼'){
				$is_left =0;
			}
			if($v['title'] == '满额返礼'){
				$is_left =0;
			}
			$list[$k]['is_left'] = $is_left;
		}
		
		$list2 = Db::name('coupon')->where(array('type'=>5,'closed'=>0))->limit(0,30)->select(); 	
		foreach($list2 as $k=>$v){
			$list2[$k]['id'] = $v['coupon_id'];
			$list2[$k]['money'] = round($v['reduce_price']/100,2);
			$list2[$k]['name'] = $v['title'];
			$list2[$k]['remark'] = $v['intro'];
			$list2[$k]['etime'] = $v['expire_date'];
			$list2[$k]['photo'] = config_weixin_img($v['photo']);
			$list2[$k]['stime'] = TODAY;
			$list2[$k]['counts'] = (int)$v['number']==0 ? 1: (int)$v['number'];;
			$list2[$k]['root'] = round($v['price']/100,2);
			$list2[$k]['buy_money'] = round($v['money']/100,2);
			$list2[$k]['cash_points'] = $v['integral'];
			if($v['limit_num']){
				$list2[$k]['conditions_that'] = '第'.$v['limit_num'].'单可用';
			}elseif($v['full_price']){
				$list2[$k]['conditions_that'] = '满'.round($v['full_price']/100,2).'可用';
			}else{
				$list2[$k]['conditions_that'] = '无门槛优惠券';
			}
			if($v['title'] == '新人有礼'){
				$is_left =1;
			}
			if($v['title'] == '寄件返礼'){
				$is_left =0;
			}
			if($v['title'] == '满额返礼'){
				$is_left =0;
			}
			$list2[$k]['is_left'] = $is_left;
		}
		
		$data['list'][2] = $list;
		$data['list'][1] = $list2;
		$data['list'][3] = $list3;
		
		
		$users = Db::name('users')->where(array('user_id'=>$uid))->find();	
		$rank_id = $users['rank_id']+1;
		$rank = Db::name('user_rank')->where(array('rank_id'=>$rank_id))->find();	
		if($rank){
			$rank['money'] = round($rank['price']/100,2);
		}else{
			$rank = Db::name('user_rank')->order(array('rank_id'=>'desc'))->find();	
			$rank['rank_name'] = '无需升级';
			$rank['money'] = '0.00';
			$rank['rank_id'] = '0';
		}
		$data['rank'] = $rank;



        $day_num = (int)$this->config['integral']['day_num'];
        $bg_time = strtotime(TODAY);
        $count = (int) Db::name('user_integral_logs')->where(array('create_time' => array(array('ELT', time()), array('EGT', $bg_time)), 'user_id' =>$uid, 'type' =>5))->count();
        $data['count'] = $count;
        $data['day_num'] = $day_num;


		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	public function myCoupon(){
		$type = input('type','','trim,htmlspecialchars');//类型
		$uid = $this->getUserId();
		$page = input('page','','trim,htmlspecialchars');
		$limit = input('limit','','trim,htmlspecialchars');
		$pay_money = input('pay_money','','trim,htmlspecialchars');
		$pay_money = (int)($pay_money*100);
		
		//删除优惠券过期的
		$where = array('user_id'=>$uid,'is_used'=>0,'expire_date' => array('ELT', TODAY));
		$downloads = Db::name('coupon_download')->where($where)->limit(0,20)->select();
		foreach($downloads as $key => $val){
			Db::name('coupon_download')->where(array('download_id'=>$val['download_id']))->update(array('used_time'=>time(),'is_used'=>1));
		}
		
		
		$map = array('user_id'=>$uid,'is_used'=>0);
		if($type == 2){
			$map = array('user_id'=>$uid,'is_used'=>1);
		}
		
		$count = Db::name('coupon_download')->where($map)->count();
		if($page == 1){
			 $firstRow = 0;
			 $listRows = $limit;
		}else{
			 $firstRow = $page*$limit;
			 $listRows = $limit;
		}
		
		$Page = new \Page3($count,50);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('coupon_download')->where($map)->order(array('download_id'=>'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
		
			foreach($list as $k => $v){
				$list[$k]['id'] = $v['download_id'];
				$list[$k]['promotion_id'] = $v['download_id'];
				$list[$k]['coupon_code'] = $v['download_id'];
				$coupon = Db::name('coupon')->where(array('coupon_id'=>$v['coupon_id']))->find();
				$list[$k]['money'] = round($coupon['reduce_price']/100,2);
				$list[$k]['name'] = round($v['reduce_price']/100,2).'元优惠券';
				$list[$k]['remark'] = $coupon['intro'];
				$list[$k]['endtime'] = $v['expire_date'];
				$list[$k]['limit_num'] = $coupon['limit_num'];
				$list[$k]['limit_num_info'] = '限制第【'.$coupon['limit_num'].'】单使用';
				
				if($coupon['full_price']){
					$list[$k]['ruletext'] .='●单笔订单满【'.round($coupon['full_price']/100,2).'】减去【'.round($coupon['reduce_price']/100,2).'】元<br>';
				}
				if($v['expire_date']){
					$list[$k]['ruletext'] .='●过期时间【'.$v['expire_date'].'】<br>';
				}
				if($coupon['limit_num']){
					$list[$k]['ruletext'] .='●限制第【'.$coupon['limit_num'].'】单使用<br>';
				}
				if($coupon['intro']){
					$list[$k]['ruletext'] .='●'.$coupon['intro'].'<br>';
				}
				$list[$k]['no_sati'] = 1;
				
				$list[$k]['no_sati'] = 0;
				if($v['is_used'] == 0){
					$list[$k]['type'] = 1;
				}
				if($v['is_used'] == 1){
					$list[$k]['type'] = 2;
				}
				$list[$k]['falg'] = 1;
				if($pay_money && $pay_money < $coupon['full_price']){
					$list[$k]['falg'] = 0;
				}
				if($pay_money && $pay_money < $coupon['reduce_price']){
					$list[$k]['falg'] = 0;
				}
				if($pay_money && $v['expire_date'] < TODAY){
					$list[$k]['falg'] = 0;
				}
			}
		}
		
		foreach($list as $k => $v){
		    if($v['falg'] ==0){
			    unset($list[$k]);
		    }
		}
		$list =@array_values($list);
		$data['list'] = $list;
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	//积分规则
	public function pointsRule(){
		$data[0]['title'] = $this->config['integral']['title_1'];
		$data[0]['content'][0] = $this->config['integral']['info_1'];
		
		$data[1]['title'] = $this->config['integral']['title_2'];
		$data[1]['content'][0] = $this->config['integral']['info_2'];
		
		$data[2]['title'] = $this->config['integral']['title_3'];
		$data[2]['content'][0] = $this->config['integral']['info_3'];
		
		$data[3]['title'] = $this->config['integral']['title_4'];
		$data[3]['content'][0] = $this->config['integral']['info_4'];
		
		$data[4]['title'] = $this->config['integral']['title_5'];
		$data[4]['content'][0] = $this->config['integral']['info_5'];
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	//积分任务
	public function pointsTask(){
		$uid = $this->getUserId();
		$data[0]['title'] = '寄快递';
		$data[0]['content'] = '寄快递消费1元得'.(int)$this->config['integral']['exp'].'积分';
		$data[0]['status'] = 1;//1未完成0已完成
		$data[0]['button_name'] = '未完成';
		$data[0]['url'] = '/pages/find/index/index';
		$data[0]['img'] = $this->config['site']['host'].'/static/default/wap/img/fl1.png';
		$data[0]['tabbar'] = 1;//1代表tabbar0不是
		
		
		$data[1]['title'] = '邀请新用户';
		$data[1]['content'] = '邀请一个新用户获得'.(int)$this->config['integral']['yao'].'积分';
		$data[1]['status'] = 1;
		$data[1]['button_name'] = '去邀请';
		$data[1]['img'] = $this->config['site']['host'].'/static/default/wap/img/fl2.png';
		$data[1]['url'] = '/pages/member/invite/invite';
		$data[1]['tabbar'] =0;
		
		$data[2]['title'] = '关注服务号';
		$data[2]['content'] = '关注服务号获得'.(int)$this->config['integral']['follow'].'积分';
		$data[2]['status'] = 0;
		$data[2]['button_name'] = '去关注';
		$data[2]['id'] = 3;
		$data[2]['img'] = $this->config['site']['host'].'/static/default/wap/img/fl3.png';
		$data[2]['tabbar'] =0;
		
		$data[3]['title'] = '签到';
		$data[3]['content'] = '签到一次'.(int)$this->config['integral']['sign_0'].'积分，累计签到有惊喜';
		$data[3]['status'] = 0;
		$data[3]['button_name'] = '去签到';
		$data[3]['img'] = $this->config['site']['host'].'/static/default/wap/img/fl4.png';
		$data[3]['url'] = '/pages/gift/index/index';
		$data[3]['tabbar'] =1;
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	//积分明细列表
	public function pointsListApi(){
		$mailNo = input('mailNo','','trim,htmlspecialchars');
		$uid = $this->getUserId();
		$page = input('page','','trim,htmlspecialchars');
		$limit = input('limit','','trim,htmlspecialchars');
		$map = array('user_id'=>$uid);
		$count = Db::name('user_integral_logs')->where($map)->count();
		if($page == 1){
			 $firstRow = 0;
			 $listRows = $limit;
		}else{
			 $firstRow = $page*$limit;
			 $listRows = $limit;
		}
		$Page = new \Page3($count,5);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('user_integral_logs')->where($map)->order('log_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $k => $v){
				$list[$k]['points'] = $v['integral'];
				$list[$k]['remark'] = $v['intro'];
				$list[$k]['ctime'] = '';
			}
		}
		
		
		$data['list'] = $list;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	public function getCoupon(){
		$data = array();
		$uid = $this->getUserId();
		$id = (int)input('id','','trim,htmlspecialchars');
		$coupon = Db::name('coupon')->where(array('coupon_id'=>$id,'type'=>3,'audit'=>1,'closed' => 0,'expire_date' => array('EGT', TODAY),'num'=>array('gt',0)))->find();	
		if(!$coupon){
			return json(array('code'=>0,'msg'=>"优惠券不存在"));
		}
		$download = (int)Db::name('coupon_download')->where(array('user_id'=>$uid,'coupon_id'=>$coupon['coupon_id']))->count();	
		if($download==1){
			return json(array('code'=>0,'msg'=>"不能重复领取"));	
		}
		$sendCouponDownload = model('ExpressOrder')->sendCouponDownload($uid,$coupon['title']);//送优惠券寄件返礼
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function getCount(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
    public function checkIndexHongbao(){
		$uid = $this->getUserId();
		$data = Db::name('coupon')->where(array('type'=>3,'audit'=>1,'closed' => 0, 'expire_date' => array('EGT', TODAY),'num'=>array('gt',0)))->limit(0,1)->find();	
		$data['reduce_price'] = round($data['reduce_price']/100,2);
		$data['title'] = cut_msubstr($data['title'],0,4,false);
		$download = (int)Db::name('coupon_download')->where(array('user_id'=>$uid,'coupon_id'=>$data['coupon_id']))->count();	
		if($data['coupon_id'] && $download==0){
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));	
		}
	}
	
	
	public function recommend(){
		$uid = $this->getUserId();
		$page = input('page','','trim,htmlspecialchars');
		$limit = input('limit','','trim,htmlspecialchars');
		$type = input('type','','trim,htmlspecialchars');
		$map['closed'] =0;
		$map['parent_id'] =$uid;
		if($page == 1){
			$star = 0;
			$end = $limit;
		}else{
			$star = $limit*$page;
			$end = $limit;
		}
			
		$count = Db::name('users')->where($map)->count();	
		$Page = new \Page3($count,10);
        $show = $Page->show();
		if($Page->totalPages < $page){
            $list = array();
        }else{
			$list = Db::name('users')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();	
			foreach($list as $k=>$v){
				$list[$k]['avatar'] = config_weixin_img($v['face']);
				$list[$k]['nickname'] = $v['nickname'];
				$mobile = substr_replace($v['mobile'],'****',3,4);
				$list[$k]['mobile'] = $mobile;
				$list[$k]['ctime'] = date('Y-m-d H:i:s',$v['reg_time']);
			}
		}	
		$data['list'] = $list;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	public function getdefaultarea(){
		$uid = input('uid','','trim,htmlspecialchars');
        $type = (int)input('type','','trim,htmlspecialchars');
        if($type==4){
            $v = Db::name('user_addr')->where(array('user_id'=>$uid,'cate'=>4,'is_default'=>1))->find();
            if(!$v){
                $v = Db::name('user_addr')->where(array('user_id'=>$uid,'cate'=>4,'is_default'=>0))->order('addr_id desc')->find();
            }
        }elseif($type==5){
            $v = Db::name('user_addr')->where(array('user_id'=>$uid,'cate'=>4,'is_default'=>1))->find();
            if(!$v){
                $v = Db::name('user_addr')->where(array('user_id'=>$uid,'cate'=>4,'is_default'=>0))->order('addr_id desc')->find();
            }
        } elseif($type==6){
            $v = Db::name('user_addr')->where(array('user_id'=>$uid,'cate'=>4,'is_default'=>1))->find();
            if(!$v){
                $v = Db::name('user_addr')->where(array('user_id'=>$uid,'cate'=>4,'is_default'=>0))->order('addr_id desc')->find();
            }
        }else{
            $v = Db::name('user_addr')->where(array('user_id'=>$uid,'type'=>1,'cate'=>1,'is_default'=>1))->find();
            if(!$v){
                $v = Db::name('user_addr')->where(array('user_id'=>$uid,'type'=>1,'cate'=>1,'is_default'=>0))->order('addr_id desc')->find();
            }
        }
		if($v){
			$data['sender_name'] = $v['name'];
			$data['sender_phone'] = $v['phone'];
			$data['sender_mobile'] = $v['mobile'];
			if(strpos($v['address'],$v['province']) !== false){ 
			 	$data['sender_address'] = $v['address'];
			}else{
			 	$data['sender_address'] = $v['province'].$v['city'].$v['area'].$v['address'];
			}
			$data['sender_province'] = $v['province'];
			$data['sender_city'] = $v['city'];
			$data['sender_area'] = $v['area'];
			$data['id'] = $v['addr_id'];
            $data['lat'] = $v['lat'];
            $data['lng'] = $v['lng'];
            $data['tc'] = $v['tc'];
            $data['addr'] = $v['addr'];
            $data['getAddr'] = $v['getAddr'];
		}
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}


	public function help(){
		$data = Db::name('article')->where(array('closed'=>0))->order('orderby asc')->limit(0,30)->select();
		foreach($data as $k=>$v){
			$data[$k]['content'] = $v['details'];
			$data[$k]['id'] = $v['article_id'];
		}
		return json(array('code'=>1,'data'=>$data));
	}
	public function helpdetail(){
			$id = (int)input('id','','trim,htmlspecialchars');
		$type = (int)input('type','','trim,htmlspecialchars');
		if($type==0&&$id){
			$data = Db::name('article')->where(array('article_id'=>$id))->find();
		}
		if($type==1){
			$data = Db::name('article')->where(array('title'=>array('LIKE','%服务协议%'),'closed'=>0))->find();
		}
		if($type==2){
			$data = Db::name('article')->where(array('title'=>array('LIKE','%隐私政策%'),'closed'=>0))->find();
		}
		$data['content'] = $data['details'];
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	//运费计算规则
	public function getJFrule(){
		$data['0']= $this->config['wxapp']['first_gz_1'];
		$data['1']= $this->config['wxapp']['first_gz_2'];
		$data['2']= $this->config['wxapp']['first_gz_3'];
		$data['3']= $this->config['wxapp']['first_gz_4'];
		$data['4']= $this->config['wxapp']['first_gz_5'];
		$data['5']= $this->config['wxapp']['first_gz_6'];
		return json(array('code'=>1,'msg'=>"查询成功",'data'=>$data));
	}   

	public function addtickets(){
		$content = input('content','','trim,htmlspecialchars');
		$username = input('username','','trim,htmlspecialchars');
		$phone = input('phone','','trim,htmlspecialchars');
		$logisticID = input('logisticID','','trim,htmlspecialchars');
		$types = input('type','','trim,htmlspecialchars');
		$uid = $this->getUserId();
		$images = input('images','','trim');
		//整合图片
		$images = @substr($images,1);
		$images = @substr($images,0,-1);
		$images = @explode(",",$images);
		$img = array();
		foreach($images as $k=>$v){
			$s = @substr($v,1);
			$s = @substr($s,0,-1);
			$img[$k] = $s;
		}
		$i = @implode(",",$img);
		
		$Data['type'] = 2;
		$Data['user_id'] = $uid;
		$Data['content'] = $content;
		$Data['contact'] = $username;
		$Data['phone'] = $phone;
		$Data['username'] = $username;
		$Data['logisticID'] = $logisticID;
		$Data['types'] = $types;
		$Data['images'] = $i;
		$Data['create_time'] = time();
		
		Db::name('express_msg')->insertGetId($Data);
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function gettickets(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	public function getTicketsType(){
		$data[0] = '售后类型';
		$data[1] = '订单类型';
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	public function getDetailedrule(){
		$data[0] = $this->config['profit']['profit_xize_1'];
		$data[1] = $this->config['profit']['profit_xize_2'];
		$data[2] = $this->config['profit']['profit_xize_3'];
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	

    //获取时间戳
	public function getNextWeekOf($date){
		$dates = array();
		$time  = strtotime($date.' 12:00:00');
		$nextMonday = 0;
		for($i = $nextMonday; $i<$nextMonday + 3; $i++){
			$strDate = date('Y-m-d', $time + 3600*24*$i);
			if($i == 0){
				$dates[$i]['name'] = '今天';
			}
			if($i ==1){
				$dates[$i]['name'] = '明天';
			}
			if($i == 2){
				$dates[$i]['name'] = '后天';
			}
			$dates[$i]['dates']= $strDate;
		}
		$dates = array_values($dates);
		return $dates;
	}
	
	
	//获取小时
	public function getTimes($dates){
        $times =  array(
            1=>array('name' => '09:00-11:00', 'num' =>'9:00'),
            2=>array('name' => '11:00-13:00', 'num' =>'11:00'),
            3=>array('name' => '13:00-15:00', 'num' =>'12:00'),
            4=>array('name' => '15:00-17:00', 'num' =>'15:00'),
            5=>array('name' => '17:00-19:00', 'num' =>'17:00')
        );
		$t = array();
		foreach($times as $k => $v){
			$strtotime = strtotime($dates.$v['num']);
			if($strtotime < (time()+600)){
				unset($times[$k]);
			}else{
				$t[$k] = $v['name'];
			}
		}
		$t = @array_values($t);
		return $t;
    }
	
	

	
	
	//预约时间
	public function yuyuetime(){
		$getNextWeekOf = $this->getNextWeekOf(TODAY);
		$data = array();
		foreach($getNextWeekOf as $k => $v){
			$times= $this->getTimes($v['dates']);
			$h = date('H');
			if($v['name'] == '今天'){
				if($h >= 18){
					unset($k);
				}else{
					$data[$k]['children'] = $times;
					$data[$k]['key'] = $k;
					$data[$k]['name'] = $v['name'];
				}
			}else{
				$data[$k]['children'] = $times;
				$data[$k]['key'] = $k;
				$data[$k]['name'] = $v['name'];
			}
		}
		$data = array_values($data);
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}       
	
	//温馨提示
	public function xiadantxt(){
		//快递公司
		$express_code = input('express_code','','trim,htmlspecialchars');
		$express_channel = input('express_channel','','trim,htmlspecialchars');
		$uid = $this->getUserId();
		
		$data = array();
		$eo= Db::name('express_order')->where(array('orderStatus'=>array('in',array(0,1,2,3)),'user_id'=>$uid))->limit(0,3)->select();
		foreach($eo as $k => $v){
			if($v['orderStatus'] == 0){
				$t = '订单ID-'.$v['id'].'未付款请支付';
			}elseif($v['orderStatus'] == 1){
				$t = '订单ID-'.$v['id'].'已付款等待取件，单号【'.$v['expressNo'].'】';
			}elseif($v['orderStatus'] == 2){
				$t = '订单ID-'.$v['id'].'已取件等待取件看快递员【'.$v['realOrderState'].'】';
			}elseif($v['orderStatus'] == 3){
				$t = '订单ID-'.$v['id'].'已取件等待取件看快递员【'.$v['realOrderState'].'】';
			}
			$data[] = $t;
		}
		//p($data);die;
		//0代表失败
		if(count($data)){
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}else{
			return json(array('code'=>0,'msg'=>"获取成功",'data'=>$data));
		}
	}
	public function autoGetNewRen(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function etSubscriptionsId(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function getOrderInformation(){
		$express_code = input('express_code','','trim,htmlspecialchars');
		$data['title'] = $express_code;
		$data['volumetext'] = '①下单分配快递员后，打电话约定上门取件时间，如果不上门取件，请直接取消订单，更换其他快递。②超重费在平台补差价，不要给快递员！③快递员上门时与他核对清楚体积重量。④货物外包装大，货物重量又很轻，按照箱子体积计算收费（一般情况是超重费的3倍）。';
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	//订阅消息ID
	public function getSubscriptionsId(){
		$scene = input('scene','','trim,htmlspecialchars');
		$uid = $this->getUserId();
		//订阅消息ID
		$data[0] = Db::name('weixin_tmpl')->where(array('title'=>'接单成功提醒'))->value('template_id');
		$data[1] = Db::name('weixin_tmpl')->where(array('title'=>'补差价通知'))->value('template_id');
		$data[2] = Db::name('weixin_tmpl')->where(array('title'=>'签收成功通知'))->value('template_id');
		
		return json(array('code'=>1,'msg'=>"获取模板消息成功",'data'=>$data));
	}
	public function newslist(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	public function navigationApi(){
		$data = Db::name('navigation')->where(array('status'=>0))->order('orderby asc')->limit(0,8)->select();
		foreach($data as $k=>$v){
			$data[$k]['name'] = $v['nav_name'];
			$data[$k]['info'] = $v['title'];
			$data[$k]['tag'] = $v['colour'];
			$data[$k]['url'] = $v['url'];
			$data[$k]['icon'] = config_weixin_img($v['photo']);
		}
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
			

	public function bannerdetail(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}    	 
		 
    public function informdetail(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	//更新模板消息
	public function updateUserSubscribe(){
		$uid = $this->getUserId();
		$data['uid'] =1;
		return json(array('code'=>1,'msg'=>"操作成功",'data'=>$data));
	}
	//最高保价
	public function computeOffer(){
		//保价金额
		$insuranceValue= input('insuranceValue','','trim,htmlspecialchars');
		//快递公司
		$express_code= input('express_code','','trim,htmlspecialchars');
		
		//保价/2
		//保价费率
		
		$baojia_rate = $this->config['wxapp']['baojia_rate'] ? $this->config['wxapp']['baojia_rate'] : '0.005';
		$data = round($insuranceValue*$baojia_rate,2);
		
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	//分享海报
	public function createOrderPoster(){
		$uid = $this->getUserId();
		$order_id= input('logisticID','','trim,htmlspecialchars');
		$page="pages/index/index";//路径
		$width = '200';
		$res = model('Api')->qrcodeWxapp($uid,$page,$width,$parameter='userId',$uid);
		$data['user_poster_url'] = config_weixin_img($res);
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	public function sms(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function getSignPackage(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}   
	
    public function decactivity(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function getCouponAll(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	
	
	//检测优惠券是否可用
	public function isUseCoupon(){
		$uid = $this->getUserId();
		$pay_money = input('pay_money','','trim,htmlspecialchars');
		$promotion_id = (int)input('promotion_id','','trim,htmlspecialchars');
		$download = Db::name('coupon_download')->where(array('download_id'=>$promotion_id))->find();
		if(!$download){
			return json(array('code'=>0,'msg'=>"未选择优惠券"));
		}
		$coupon = Db::name('coupon')->where(array('coupon_id'=>$download['coupon_id']))->find();
		if(!$coupon){
			return json(array('code'=>0,'msg'=>"参数错误"));
		}
		
		if($coupon['expire_date'] < TODAY){
			return json(array('code'=>0,'msg'=>"优惠券已过期"));
		}
		$count = (int)Db::name('express_order')->where(array('orderStatus'=>4,'user_id'=>$uid))->count();
		if($coupon['limit_num']){
			if($count < $coupon['limit_num']){
				return json(array('code'=>0,'msg'=>"单数不够，当前优惠券需要完成【".$coupon['limit_num']."】单才能使用，您已经完成【".$count."】单请重新选择"));
			}
		}
		
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	public function getAnnoucements(){
		$data = Db::name('user_profit_logs')->order('log_id desc')->limit(0,5)->select();
		foreach($data as $k=>$v){
			$u = Db::name('users')->where(array('user_id'=>$v['user_id']))->field('user_id,nickname')->find();
			$data[$k]['title'] = '恭喜'.$u['nickname'].'获得'.round($v['money']/100,2).'元奖励，点击分享返佣';
			$data[$k]['jump_url'] = "/pages/member/invite/invite";
			$data[$k]['click'] = '1';
		}
		if(!$data){
			$data[0]['title'] = '快去分享获取佣金吧';
			$data[0]['jump_url'] = "/pages/member/invite/invite";
			$data[0]['click'] = '1';
		}
		return json(array('code'=>1,'data'=>$data));
	}
	
	
	public function getzdDate(){
		$time=mktime(18,0,0,date('m'),date('d'),date('Y'));
		$data = date('Y-m-d H:i:s',$time);
		return json(array('code'=>1,'data'=>$data));
	}
	
	
	
	public function checkPayCode(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	public function comroll(){
		$data = Db::name('user_profit_logs')->order('log_id desc')->limit(0,5)->select();
		foreach($data as $k=>$v){
			$u = Db::name('users')->where(array('user_id'=>$v['user_id']))->field('user_id,nickname')->find();
			$data[$k]['title'] = '恭喜'.$u['account'].'获得'.round($v['money']/100,2).'元奖励，点击分享返佣';
			$data[$k]['jump_url'] = "/pages/member/invite/invite";
			$data[$k]['click'] = '1';
		}
		if(!$data){
			$data[0]['title'] = '快去分享获取佣金吧';
			$data[0]['jump_url'] = "/pages/member/invite/invite";
			$data[0]['click'] = '1';
		}
		return json(array('code'=>1,'data'=>$data));
	}
	public function TicketsListApi(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}  
	
	public function looks(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function addanswer(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function end(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
    public function notirecip(){
        $uid = $this->getUserId();
        if(!$uid){
            return json(array('code'=>0,'msg'=>'TOKEN失效请重新登录'));
        }
        $res = $this->config['config']['order_share'];
        $data['user_poster_url'] = config_weixin_img($res);
        return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
    }
	public function checkNotire(){
		$data = 1;
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
    public function checkNotireMob(){
        $code = input('code','','trim,htmlspecialchars');
        $recipients_id = input('recipients_id','','trim,htmlspecialchars');
        $info = Db::name('express_order')->where(array('id'=>$recipients_id))->find();
        $sendMobile = substr($info['sendMobile'],-4);
        $receiveMobile = substr($info['receiveMobile'],-4);
        $c=0;
        if($code==$sendMobile){
            $c=1;
        }
        if($code==$receiveMobile){
            $c=1;
        }
        if($c==0){
            return json(array('code'=>0,'msg'=>'验证码错误'));
        }
        $info = $this->getOrderInfo($info,0);
        $data = $info;
        $data['logistics_info'] =model('ExpressOrder')->logisticsInfo($info,0,$mailNo='');
        $data['logistics'] =model('ExpressOrder')->logisticsInfo($info,0,$mailNo='');
        return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
    }

    public function getCanList(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function applyInvoice(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function getInvoiceTit(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function addInvoiceTit(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function editInvoiceTit(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function selectInvoiceTitById(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}  
	
	public function delInvoiceTit(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function getInvoicehis(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function getInvoiceHelp(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function sendInvoiceEmail(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function sumOfMoney(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function applyAllInvoice(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}  
	
	
	public function sentoptxt(){

        $uid = $this->getUserId();
        $u = Db::name('users')->where(array('user_id'=>$uid))->find();

        $userInfos['money1'] = (int)($this->config['dianshang']['money1']);
        $userInfos['money2'] = (int)($this->config['dianshang']['money2']);
        $userInfos['money1_1'] = (int)($this->config['dianshang']['money1']*100);
        $userInfos['money2_1'] = (int)($this->config['dianshang']['money2']*100);
        $userInfos['money0'] = (int)($u['money']);
        $userInfos['money'] = round($u['money']/100,2);

        $data['userInfos'] = $userInfos;

		$data['content'] = $this->config['wxapp']['tip1'] ? $this->config['wxapp']['tip1'] : "您好，如需修改运单信息，可点【再来一单】重新下单，并将错误运单撤消。";
		$data['id'] = 11;
		$data['title'] = "注意事项";



		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	public function getFebCouList(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function checkFebRes(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	public function newspop(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}
	
	
	public function defendpop(){
		$close = (int)$this->config['site']['web_close'];
		if($close == 1){
			$data['status'] = 1;
		}else{
			$data['status'] = 0;
		}
		$data['content'] = $this->config['site']['web_close_title'];
		$data['title'] = "升级维护公告";
		
		$this->config['site']['pub_id'] = $this->config['jutuike']['pub_id'] ? $this->config['jutuike']['pub_id'] : $this->config['site']['pub_id'];
		$this->config['site']['haojingke_apikey'] = $this->config['jutuike']['haojingke_apikey'] ? $this->config['jutuike']['haojingke_apikey'] : $this->config['site']['haojingke_apikey'];
		$data['site'] = $this->config['site'];
		$data['site']['index_share']=  config_weixin_img($this->config['config']['index_share']);
		$data['site']['index_share_title']=  $this->config['config']['index_share_title'] ? $this->config['config']['index_share_title']:'快递寄件折扣平台 运费低至5元起';
		$data['site']['juke']= $this->config['jutuike']['juke']?$this->config['jutuike']['juke']:'0';
		$data['site']['pay'] = $this->config['pay'];
		$data['site']['profit'] = $this->config['profit'];
        $data['site']['follow']= $this->config['weixin']['follow']?$this->config['weixin']['follow']:'';



		$data['integral'] = $this->config['integral'];
		$data['cps'] = $this->config['jutuike'];
		$data['integral']['video_cover'] = config_weixin_img($this->config['integral']['video_cover']);
		$data['wxapp'] = $this->config['wxapp'];
		$data['profit'] = $this->config['profit'];
		unset(
			$data['wxapp']['appsecret'],
			$data['wxapp']['yy_appid'],
			$data['wxapp']['yy_secretKey'],
			$data['wxapp']['kdn_EBusinessID'],
			$data['wxapp']['kdn_ApiKey'],
			$data['wxapp']['yd_name'],
			$data['wxapp']['yd_secret'],
			$data['wxapp']['appsecret'],
			$data['wxapp']['db_appkey']
		);
		
		return json(array('code'=>1,'msg'=>"获取成功看你妈",'data'=>$data));
	} 
	
	
	
	public function defendpop1(){

		$defendpop = (int)$this->config['wxapp']['defendpop'];
		$close = (int)$this->config['site']['web_close'];
		
		if($defendpop == 1 && $close==0){
			$data['status'] = 1;
		}else{
			$data['status'] = 0;
		}
		$data['content'] = $this->config['wxapp']['defendpop_info'];
		$data['title'] = $this->config['wxapp']['defendpop_title'];
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	} 
	
	
	//选择快递公司
	public function choosecom(){
        $data['sender_id'] = input('sender_id','','trim,htmlspecialchars');
		$data['sender_province'] = input('sender_province','','trim,htmlspecialchars');
		$data['sender_city'] = input('sender_city','','trim,htmlspecialchars');
		$data['sender_area'] = input('sender_area','','trim,htmlspecialchars');
		$data['sender_address'] = input('sender_address','','trim,htmlspecialchars');
        $data['sender_addr'] = input('sender_addr','','trim,htmlspecialchars');
        $data['sender_getAddr'] = input('sender_getAddr','','trim,htmlspecialchars');
        $data['sender_name'] = input('sender_name','','trim,htmlspecialchars');
		$data['sender_name'] = input('sender_name','','trim,htmlspecialchars');
		$data['sender_mobile'] = input('sender_mobile','','trim,htmlspecialchars');
		$data['sender_phone'] = input('sender_phone','','trim,htmlspecialchars');
        $data['sender_lat'] = input('sender_lat','','trim,htmlspecialchars');
        $data['sender_lng'] = input('sender_lng','','trim,htmlspecialchars');
		$data['recipients_province'] = input('recipients_province','','trim,htmlspecialchars');
		$data['recipients_city'] = input('recipients_city','','trim,htmlspecialchars');
		$data['recipients_area'] = input('recipients_area','','trim,htmlspecialchars');
		$data['recipients_address'] = input('recipients_address','','trim,htmlspecialchars');
        $data['recipients_addr'] = input('recipients_addr','','trim,htmlspecialchars');
        $data['recipients_getAddr'] = input('recipients_getAddr','','trim,htmlspecialchars');
		$data['recipients_name'] = input('recipients_name','','trim,htmlspecialchars');
		$data['recipients_mobile'] = input('recipients_mobile','','trim,htmlspecialchars');
		$data['recipients_phone'] = input('recipients_phone','','trim,htmlspecialchars');
        $data['recipients_lat'] = input('recipients_lat','','trim,htmlspecialchars');
        $data['recipients_lng'] = input('recipients_lng','','trim,htmlspecialchars');
		$data['long'] = input('long','','trim,htmlspecialchars');
		$data['width'] = input('width','','trim,htmlspecialchars');
		$data['height'] = input('height','','trim,htmlspecialchars');
		
		$totalWeight = input('totalWeight','','trim,htmlspecialchars');
		$totalWeight = @ceil($totalWeight);
		$totalWeight =  (int)$totalWeight;
		$data['totalWeight'] = $totalWeight;
		
		
		if(!$data['sender_province'] || $data['sender_province'] == 'undefined'){
			return json(array('code'=>0,'msg'=>'请填写发货地址'));
		}
		if(!$data['recipients_province'] || $data['recipients_province'] == 'undefined'){
			return json(array('code'=>0,'msg'=>'请填写收货地址'));
		}
		if(!$data['totalWeight'] || $data['totalWeight'] == 'undefined'){
			return json(array('code'=>0,'msg'=>'请填写商品信息'));
		}
		
		if($data['recipients_mobile']=='undefined' || $data['recipients_mobile']=='' || $data['recipients_mobile']==NULL){
			$data['recipients_mobile']= '17194348715';
		}
		if($data['recipients_address']=='undefined' || $data['recipients_address']=='' || $data['recipients_address']==NULL){
			$data['recipients_address']= $data['recipients_province'].$data['recipients_city'].$data['recipients_area'];
		}
		
		if($data['sender_phone']=='undefined' || $data['sender_phone']=='' || $data['sender_phone']==NULL){
			$data['sender_phone']= '17194348715';
		}
		if($data['sender_address']=='undefined' || $data['sender_address']=='' || $data['sender_address']==NULL){
			$data['sender_address']= $data['recipients_province'].$data['sender_city'].$data['sender_area'];
		}
		
		
		//重新换算重量取消体积
		
		$getCalculateWeight= model('Setting')->getCalculateWeight($data['long'],$data['width'],$data['height'],'圆通',$data['totalWeight']);
		//p($getCalculateWeight);
		if($getCalculateWeight > $data['totalWeight']){
			$data['long'] = 1;
			$data['width'] = 1;
			$data['height'] = 1;
			$data['totalWeight'] = $getCalculateWeight;
		}else{
			$data['long'] = 1;
			$data['width'] = 1;
			$data['height'] = 1;
			$data['totalWeight'] = $data['totalWeight'];
		}
		
		//p($data);die;
	
		$data['type'] = (int)input('type','','trim,htmlspecialchars');
		$data['cate_id'] = (int)input('cate_id','','trim,htmlspecialchars');
		$data['uid'] = $this->getUserId();
		
		//p($data);die;
		
		$choosecom = model('Setting')->choosecom($data);//查询快递
		//p($choosecom);
		if($choosecom == false){
			return json(array('code'=>0,'msg'=>'预下单错误:【'.model('Setting')->getError().'】'));
		}else{
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$choosecom));
		}
	}
	
	
	
	//下单准备
	public function create(){
		$config = model('Setting')->fetchAll2();
		$smail_id = input('smail_id','','trim,htmlspecialchars');
		$rmail_id = input('rmail_id','','trim,htmlspecialchars');
		$cargodata = input('cargodata','','trim');
		$totalNumber = (int)input('totalNumber','1','trim,htmlspecialchars');
		
		//ceil() 函数：进一法取整，即取得比当前数大的下一位整数
		$totalWeight = input('totalWeight','','trim,htmlspecialchars');
		$totalWeight = @ceil($totalWeight);
		$totalWeight =  (int)$totalWeight;
		
		
		$long = input('long','','trim,htmlspecialchars');
		$width = input('width','','trim,htmlspecialchars');
		$height = input('height','','trim,htmlspecialchars');
		$height = input('height','','trim,htmlspecialchars');
		
		$isShared = (int)input('isShared','','trim,htmlspecialchars');
		if($isShared==1){
			$discountmoney = input('discountmoney','','trim,htmlspecialchars');
			$discountmoney  = $discountmoney * 100;//分享立减费用
		}else{
			$discountmoney  = 0;
		}
		
		$sendStartTime = input('sendStartTime','','trim,htmlspecialchars');
		$sendEndTime = input('sendEndTime','','trim,htmlspecialchars');
		$remark = input('remark','','trim,htmlspecialchars');
		$insuranceValue = input('insuranceValue','','trim,htmlspecialchars');//保障金额
		$insurancePrice = input('insurancePrice','','trim,htmlspecialchars');//保费
		$coupon_code = (int)input('coupon_code','','trim,htmlspecialchars');
		$source = input('source','','trim,htmlspecialchars');//类型
		$is_dw = input('is_dw','','trim,htmlspecialchars');
		$orderType = (int)input('type','','trim,htmlspecialchars');

        $imgUrl = input('imgUrl','','trim');
        $imgUrl = @substr($imgUrl,1);
        $imgUrl = @substr($imgUrl,0,-1);
        $imgUrl = @explode(",",$imgUrl);
        $img = array();
        foreach($imgUrl as $k=>$v){
            $s = @substr($v,1);
            $s = @substr($s,0,-1);
            $img[$k] = $s;
        }


		$uid = $this->getUserId();
		$cargodata =  json_decode($cargodata,true);
		
		//p($cargodata['express_code']);die;
		$getCalculateWeight= model('Setting')->getCalculateWeight($long,$width,$height,$cargodata['express_code'],$totalWeight);
		if($getCalculateWeight > $totalWeight){
			$totalWeight =  $getCalculateWeight;
			$long = 1;
			$width = 1;
			$height = 1;
		}else{
			$totalWeight =  $totalWeight;
			$long = 1;
			$width = 1;
			$height = 1;
		}
		//p($totalWeight);die;
		
		//寄件地址
		$s = Db::name('user_addr')->where(array('addr_id'=>$smail_id))->find();
		if(!$s){
			return json(array('code'=>0,'msg'=>'寄件地址【'.$smail_id.'】不存在'));
		}
		
		
		//收件地址
		if($is_dw == 1){
			//得物地址
			$r = Db::name('user_addr_dewu')->where(array('id'=>$rmail_id))->find();
			$r['province'] = $r['sender_province'];
			$r['city'] = $r['sender_city'];
			$r['area'] = $r['sender_area'];
			$r['phone'] = $r['sender_phone'];
			$r['mobile'] = $r['sender_mobile'];
			$r['address'] = $r['sender_address'];
			$r['name'] = $r['sender_name'];
		}else{
			$r = Db::name('user_addr')->where(array('addr_id'=>$rmail_id))->find();
		}
		if(!$r){
			return json(array('code'=>0,'msg'=>'收件地址【'.$rmail_id.'】不存在'));
		}
		
		
		
		//返回订单号
		$t = (int)$cargodata['type'];
		if($t==0){
			return json(array('code'=>0,'msg'=>'接口模式有误'));
		}
		
		$u = Db::name('users')->where(array('user_id'=>$uid))->find();
		if(!$u){
			return json(array('code'=>0,'msg'=>'用户不存在'));
		}



		if($t==5){
			$e = Db::name('express_cate')->where(array('pinyin'=>$cargodata['transportType'],'type'=>5))->find();
		}elseif($t==7){
			$e = Db::name('express_cate')->where(array('pinyin'=>$cargodata['express_channel'],'type'=>$cargodata['type']))->find();
		}elseif($t==8){
			if($cargodata['transportType']=='area'){
				$e = Db::name('area')->where(array('area_id'=>$cargodata['express_channel'],'open'=>1))->find();
			}
			if($cargodata['transportType']=='city'){
				$e = Db::name('city')->where(array('city_id'=>$cargodata['express_channel'],'open'=>1))->find();
			}
            if($cargodata['transportType']=='community'){
                $e = Db::name('business_community')->where(array('community_id'=>$cargodata['express_channel']))->find();
            }
            if($cargodata['transportType']=='customize'){
                $e = Db::name('express_cate')->where(array('cate_id'=>$cargodata['express_channel']))->find();
            }

        }elseif($t==9){
            $e = Db::name('express_cate')->where(array('cate_name'=>$cargodata['express_code'],'type'=>9))->find();
        }elseif($t==10){
            $e = Db::name('express_cate')->where(array('cate_name'=>$cargodata['express_code'],'type'=>10))->find();
        }else{
			$e = Db::name('express_cate')->where(array('cate_name'=>$cargodata['express_code']))->find();
		}
		
		
		$area = Db::name('copy_area')->where(array('area_name'=>$s['area']))->find();
		if(!$area){
			$Name = @mb_substr($s['area'],0,2);
			$area = Db::name('copy_area')->where(array('area_name'=>array('LIKE','%'.$Name.'%')))->find();
		}
		$city = Db::name('copy_city')->where(array('city_id'=>$s['city']))->find();
		if(!$city){
			$cityName = @mb_substr($s['city'],0,2);
			$city = Db::name('copy_city')->where(array('name'=>array('LIKE','%'.$cityName.'%')))->find();
		}
		
		$data['province']  = $city['ParentId'] ? $city['ParentId'] : $u['province'];	
		$data['city'] =$city['city_id'] ? $city['city_id'] : $u['city'];		
		$data['area']  =$area['area_id'] ? $area['area_id'] : $u['area'];	
		
		$data['coupon_pmt'] = 0;
		if($coupon_code){
			$coupon_id = Db::name('coupon_download')->where(array('download_id'=>$coupon_code))->value('coupon_id');
			$co = Db::name('coupon')->where(array('coupon_id'=>$coupon_id))->find();
			if($co['expire_date'] > TODAY && $co['reduce_price']){
				$data['coupon_pmt'] = $co['reduce_price'];//优惠金额
				$data['coupon_download_id'] = $coupon_code;//优惠券使用ID
			}
		}
		if(!$s){
			return json(array('code'=>0,'msg'=>'寄件地址详情不存在'));
		}
		if(!$r){
			return json(array('code'=>0,'msg'=>'收件地址详情不存在'));
		}
		if(!$uid){
			return json(array('code'=>0,'msg'=>'会员信息不存在'));
		}
		$eos = Db::name('express_order')->where(array('user_id'=>$uid))->order('id desc')->field('user_id,id,create_time,diffStatus')->find();
		$tm = time();
		$ctm = $tm-$eos['create_time'];
		$catm = $ctm-30;
		if($ctm < 30){
			return json(array('code'=>0,'msg'=>'下单速度过快，请稍后【'.abs($catm).'】秒后重试'));
		}
		$eos2 = Db::name('express_order')->where(array('user_id'=>$uid,'diffStatus'=>1,'diffMoneyYuan'=>array('gt',0),'closed'=>0))->order('id desc')->field('user_id,id,create_time,diffStatus,diffMoneyYuan')->find();
		if($eos2){
			return json(array('code'=>0,'msg'=>'订单【'.$eos2['id'].'】还有差价【'.round($eos2['diffMoneyYuan']/100,2).'】元未补齐，补齐差价后下单'));
		}
		
		
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

		
		//内部单号
		$oid = Db::name('express_order')->order('id desc')->limit(0,1)->value('id');
		$thirdNo = ($oid+1).rand_string(6,1);//外部单号
		
		$siteWxapp = $this->config['wxapp'];
		$is_add_order = (int)$siteWxapp['is_add_order'];
		$is_add_order_money = (int)($siteWxapp['is_add_order_money']*100);
		$is_new_order0 = (int)$siteWxapp['is_new_order0'];
		$is_new_order1 = (int)$siteWxapp['is_new_order1'];
		$is_new_order2 = (int)$siteWxapp['is_new_order2'];
		$is_new_order3 = (int)$siteWxapp['is_new_order3'];
		$is_new_order4 = (int)$siteWxapp['is_new_order4'];
		
		$tg=0;
		if($u['money']>$is_add_order_money){
			$tg=1;
		}
		if($u['rank_id']>0){
			$tg=1;
		}
		
		$orderStatus4 = (int)Db::name('express_order')->where(array('orderStatus'=>4,'user_id'=>$uid))->count();
		if($tg==0 && $orderStatus4==0 && $is_new_order0){
			$count = (int)Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'user_id'=>$uid))->count();
			if($count >= $is_new_order0){
				return json(array('code'=>0,'msg'=>'1请等待订单签收后再次下单【'.$is_new_order0.'】'));
			}
		}
		if($tg==0 && $orderStatus4==1 && $is_new_order1){
			$count = (int)Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'user_id'=>$uid))->count();
			if($count >= $is_new_order1){
				return json(array('code'=>0,'msg'=>'2请等待订单签收后再次下单【'.$is_new_order1.'】'));
			}
		}
		if($tg==0 && $orderStatus4==2 && $is_new_order2){
			$count = (int)Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'user_id'=>$uid))->count();
			if($count >= $is_new_order2){
				return json(array('code'=>0,'msg'=>'3请等待订单签收后再次下单【'.$is_new_order2.'】'));
			}
		}
		if($tg==0 && $orderStatus4==3 && $is_new_order3){
			$count = (int)Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'user_id'=>$uid))->count();
			if($count >= $is_new_order3){
				return json(array('code'=>0,'msg'=>'4请等待订单签收后再次下单【'.$is_new_order3.'】'));
			}
		}
		if($tg==0 && $orderStatus4==4  && $is_new_order4){
			$count = (int)Db::name('express_order')->where(array('orderStatus'=>array('in',array(1,2,3)),'user_id'=>$uid))->count();
			if($count >= $is_new_order4){
				return json(array('code'=>0,'msg'=>'5请等待订单签收后再次下单【'.$is_new_order4.'】'));
			}
		}
		
		//订单数据
		$data['is_pei'] = (int)$e['is_pei'];
		$data['orderType'] = 0;
		$data['kuaidi'] = $cargodata['express_code'];
		$data['cargoName'] = $cargodata['name'];//商品名称
		
		
		$u = Db::name('users')->where(array('user_id'=>$uid))->find();
		$profit_uu= model('ExpressOrder')->profit_uu($uid);
		$uu1 = $profit_uu['uu1'];
		$uu2 = $profit_uu['uu2'];
		$uu3 = $profit_uu['uu3'];
		
	
	
		$data['pid'] = $u['parent_id'];
		$data['smail_id'] = $smail_id;
		$data['rmail_id'] = $rmail_id;
		$data['rank1_uid'] = $uu1['user_id'];
		$data['rank2_uid'] = $uu2['user_id'];
		$data['rank3_uid'] = $uu3['user_id'];
		$data['discountmoney'] = $discountmoney;
		
		
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
		$data['wight'] = $totalWeight;//重量
		$data['totalNumber'] = $totalNumber;//数量
		$data['insuranceValue'] = $insuranceValue*100;//保障金额
		$data['insurancePrice'] = $insurancePrice*100;//保费
		$data['wight'] = $totalWeight;//重量

        $data['province'] = $u['province'];
        $data['city'] = $u['city'];
        $data['area'] = $u['area'];

        if($e['business_id']){
            $copy_area = Db::name('copy_area')->where(array('area_id'=>$e['area_id']))->find();
            if(!$e['area_id']){
                return json(array('code'=>0,'msg'=>'下单数据错误'));
            }
            $data['province'] = $copy_area['province_id'];
            $data['city'] = $copy_area['city_id'];
            $data['area'] = $e['area_id'];
            $data['business'] = $e['business_id'];
            $data['community'] = $e['community_id'];
        }
		$data['totalVolume'] = '';//体积
		$data['sumMoneyYuan'] = 0;//支付金额
		$data['diffMoneyYuan'] =0;//差价金额
		$data['sendName'] = $s['name'];
		$data['sendMobile'] = $senderMobile;
		$data['sendCity'] = $s['city'];
		$data['sendAddress'] = $s_address;
		$data['receiveName'] = $r['name'];
		$data['receiveMobile'] = $receiveMobile;
		$data['receiveCity'] = $r['city'];
		$data['receiveAddress'] = $r_address;
		$data['year'] = date('Y',time());
		$data['month'] = date('Ym',time());
		$data['day'] = date('Ymd',time());
		$data['create_time'] = time();
		$data['remark'] = $remark;//备注

		$time = $sendStartTime;
		//定义一个日间我相把把它变成2010-01-1
		$splitDate = explode("-",$time);
		//进行拆分以"-"分开
		$stime = mktime(0,0,0,$splitDate[1],$splitDate[2],$splitDate[0]);
		//再用mktime把它转换成时间载
		if(intval( $splitDate[1] )<10 && substr( $splitDate[1],0,1) !='0' ){
			$splitDate[1] = '0'.$splitDate[1];
		}
		//对月分取一个数字判断如果是01这种格式就不操作反之就加个0
		if( intval( $splitDate[2] )<10 && substr( $splitDate[2],0,1) !='0' ){
			$splitDate[2] = '0'.$splitDate[2];
		}
		$sendStartTime = $splitDate[0].'-'.$splitDate[1].'-'.$splitDate[2];
		
		$data['yuyuetime'] = $sendStartTime.' '.$sendEndTime;
		
		
		if($sendEndTime == '09:00-11:00'){
			$st = $sendStartTime.' 09:00:00';
			$et = $sendStartTime.' 11:00:00';
		}elseif($sendEndTime == '11:00-13:00'){
			$st = $sendStartTime.' 11:00:00';
			$et = $sendStartTime.' 13:00:00';
		}elseif($sendEndTime == '13:00-15:00'){
			$st = $sendStartTime.' 13:00:00';
			$et = $sendStartTime.' 15:00:00';
		}elseif($sendEndTime == '15:00-17:00'){
			$st = $sendStartTime.' 15:00:00';
			$et = $sendStartTime.' 17:00:00';
		}elseif($sendEndTime == '17:00-19:00'){
			$st = $sendStartTime.' 17:00:00';
			$et = $sendStartTime.' 19:00:00';
		}
	
		if($t == 1){
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

            $estimateData = array(
                'sender' => model('Setting')->buildZhongfaContact(
                    $s['name'],
                    $senderMobile,
                    '',
                    $s['province'],
                    $s['city'],
                    $s['area'],
                    $s_address
                ),
                'receiver' => model('Setting')->buildZhongfaContact(
                    $r['name'],
                    $receiveMobile,
                    '',
                    $r['province'],
                    $r['city'],
                    $r['area'],
                    $r_address
                ),
                'packageInfo' => model('Setting')->buildZhongfaPackageInfo(
                    $cargodata['name'],
                    $long,
                    $width,
                    $height,
                    $totalNumber,
                    $totalWeight
                ),
                'additionalService' => array(
                    'insuranceValue' => round($insuranceValue / 100,2),
                ),
                'remark' => $remark,
            );
            if(!empty($cargodata['expressCode'])){
                $estimateData['expressCode'] = $cargodata['expressCode'];
            }
            if(!empty($cargodata['productCode'])){
                $estimateData['productCode'] = $cargodata['productCode'];
            }
            if($st){
                $estimateData['sendStartTime'] = $st;
            }
            if($et){
                $estimateData['sendEndTime'] = $et;
            }

            $estimateResult = model('Setting')->zhongfaExecute($estimateData, 'estimate');
            if(!isset($estimateResult['code']) || $estimateResult['code'] != 0){
                $errorMsg = isset($estimateResult['msg']) ? $estimateResult['msg'] : (isset($estimateResult['message']) ? $estimateResult['message'] : '接口异常');
                return json(array('code'=>0,'msg'=>'众发物流预估失败：'.$errorMsg));
            }
            if(empty($estimateResult['data']) || !is_array($estimateResult['data'])){
                return json(array('code'=>0,'msg'=>'众发物流预估失败：未返回可用线路'));
            }

            $selectedQuote = array();
            foreach($estimateResult['data'] as $quote){
                if(
                    !empty($cargodata['productCode']) &&
                    isset($quote['productCode']) &&
                    $quote['productCode'] == $cargodata['productCode']
                ){
                    $selectedQuote = $quote;
                    break;
                }
                if(
                    !empty($cargodata['expressCode']) &&
                    isset($quote['expressCode']) &&
                    $quote['expressCode'] == $cargodata['expressCode']
                ){
                    $selectedQuote = $quote;
                }
            }
            if(empty($selectedQuote)){
                $selectedQuote = $estimateResult['data'][0];
            }

            // 众发 totalAmount 单位已是「分」，勿再 *100
            $TotalFee = isset($selectedQuote['totalAmount']) ? (int)round($selectedQuote['totalAmount']) : 0;
            if($TotalFee > 0 && $TotalFee < 100){
                $TotalFee = (int)round($TotalFee * 100);
            }
            if($TotalFee <= 0){
                return json(array('code'=>0,'msg'=>'众发物流预估失败：线路金额异常'));
            }
            $originalFee = $TotalFee;
            
            // 应用调试价格
            $TotalFee = model('Setting')->applyZhongfaDebugPriceFen($TotalFee);

            $getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$TotalFee,0,0,$originalFee,0,$e,$insurancePrice,$co,$data['coupon_pmt'],$expressValue);

            $requestParams = array(
                'outOrderNo' => '',
                'expressCode' => isset($selectedQuote['expressCode']) ? $selectedQuote['expressCode'] : (isset($cargodata['expressCode']) ? $cargodata['expressCode'] : ''),
                'productCode' => isset($selectedQuote['productCode']) ? $selectedQuote['productCode'] : (isset($cargodata['productCode']) ? $cargodata['productCode'] : ''),
                'payType' => 0,
                'remark' => $remark,
                'sender' => $estimateData['sender'],
                'receiver' => $estimateData['receiver'],
                'orderer' => array(
                    'name' => $s['name'],
                    'mobile' => $senderMobile,
                ),
                'packageInfo' => $estimateData['packageInfo'],
                'additionalService' => $estimateData['additionalService'],
                'estimateOrderNo' => isset($selectedQuote['orderNo']) ? $selectedQuote['orderNo'] : '',
            );
            if($st){
                $requestParams['sendStartTime'] = $st;
            }
            if($et){
                $requestParams['sendEndTime'] = $et;
            }
                  model('Setting')->fillZhongfaPickupTimes($requestParams, array('yuyuetime' => $data['yuyuetime']));
            if(isset($selectedQuote['providerProductId'])){
                $requestParams['extendField'] = array(
                    'itemVersion' => (int)$selectedQuote['providerProductId'],
                );
            }

            $data['requestParams'] = iserializer($requestParams);
            $data['firstPrice'] = $getCatePrice['firstPrice'];
            $data['addPrice'] = $getCatePrice['addPrice'];
            $data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
            $data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
            $data['preOrderFee'] = $getCatePrice['preOrderFee'];
            $data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
            $data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
            $data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
            $data['type'] =15;
        }elseif($t == 1){
	
			
			//易达接口下单保存数据
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
			
			if($deliveryType == 'SF' && !$data['yuyuetime']){
				return json(array('code'=>0,'msg'=>'顺丰必须填写预约时间'));
			}
			
			
			if($orderType == '3'){
				$customerType = 'ky';
			}elseif($orderType == '1'){
				$customerType = 'kd';
			}elseif($orderType == '2'){
				$customerType = 'poizon';
			}else{
				$customerType = 'kd';
			}
			
			$requestParams2['senderAddress']=$s_address;// 寄件人地址
			$requestParams2['goods']=$cargodata['name'];
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
			$requestParams2['customerType']=$customerType;
			if($cargodata['express_code'] == '德邦'){
				$requestParams2['deliveryBusiness']=$cargodata['transportType'];
			}
			$requestParams2['senderProvince']=$s['province'];//收件省份
			$requestParams2['receiveProvince']=$r['province'];//寄件省份
			$requestParams2['senderCity']=$s['city'];//收件城市
			$requestParams2['receiveCity']=$r['city'];//寄件城市
			$requestParams2['unitPrice']=10;//申通情况必填 单价
			$requestParams2['qty']=$totalNumber;//申通情况必填 数量
			$requestParams2['pickUpStartTime']=$st;//顺丰预约时间
			$requestParams2['pickUpEndTime']=$et;
			$requestParams2['vloumLong']=$long ? $long : 1;//长
			$requestParams2['vloumHeight']=$height ? $height : 1;//高
			$requestParams2['vloumWidth']=$width ? $width : 1;//宽
			$requestParams2['packageCount']=$totalNumber?$totalNumber:1;//包裹数
			$requestParams2['guaranteeValueAmount']=$insurancePrice;//保价
			$requestParams2['receiveProvinceCode']='';//收件省code-编码参照国务院最新颁布
			$requestParams2['senderProvinceCode']='';//寄件省code-编码参照国务院最新颁
			$requestParams2['channelId']=$cargodata['express_channel'];//寄件省code-编码参照国务院最新颁布
			
			$data['requestParams2'] = iserializer($requestParams2);//易达接口保存到数据库序列化
			$execute = model('Setting')->execute($requestParams2,$Method='SMART_PRE_ORDER');
			
			
			if($execute['code'] == 200){
				
				$v = $execute['data'][$e['pinyin']][0];
				$logoUrl = model('ExpressOrder')->logoUrl($cargodata['express_code']);
				//原价计费规则 
				$originalPrice = $v['originalPrice'];
				$originalPrice =  @json_decode($originalPrice,true);
				$first = $v['price'];
				$first =  @json_decode($first,true);
				
			
				//易达云端请求数据
				$this->curl = new \Curl();
				$getZhe = model('Setting')->getZhe($uid,$e);
				$postData['v'] = $v;
				$postData['type'] = 1;
				$postData['uid'] = $uid;
				$postData['getZhe'] = $getZhe;
				$postData['totalWeight'] = $totalWeight;
				$postData['TotalFee'] = $v['preOrderFee']*100;
				$postData['priceA'] = 0;
				$postData['priceB'] = 0;
				$postData['originalFee'] = $v['originalFee']*100;
				$postData['preBjFee'] = 0;
				$postData['logoUrl'] = $e;
				$postData['insurancePrice'] = $insurancePrice;
				$postData['co'] = $co;
				$postData['coupon_pmt'] = $data['coupon_pmt'];
				$postData['host'] = trim($config['site']['host']);
				$postData['mobile'] = trim($config['site']['mobile']);
				$url = getHost().'/api/RequestApi/getCatePrice';
				$result = $this->curl->post($url,json_encode($postData));
				$result = json_decode($result,true);
				$getCatePrice = $result['data'];
				
				$data['firstPrice'] = $getCatePrice['firstPrice'];
				$data['addPrice'] =$getCatePrice['addPrice'];
				$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
				$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
				$data['preOrderFee'] = $getCatePrice['preOrderFee'];
				$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
				$data['sumMoneyYuan_old'] =$getCatePrice['sumMoneyYuan_old'];
				$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
				$data['type'] =1;//易达接口模式
				
				
			}else{
				return json(array('code'=>0,'msg'=>'YD获取预支付订单详情失败'.$execute['msg']));
			}
		}elseif($t == 2){
	
			if($orderType == '3'){
				$channelTag = "重货";
			}elseif($orderType == '1'){
				$channelTag = "智能";
			}elseif($orderType == '2'){
				$channelTag = "得物";
			}else{
				$channelTag = "智能";
			}
			$content['channelTag']=$channelTag;
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
			$content['packageCount']= $totalNumber ? $totalNumber : 1;
			$content['insured']= $insurancePrice;
			$content['vloumLong']= $long ? $long : 1;
			$content['vloumWidth']= $width ? $width : 1;
			$content['vloumHeight']=$height ? $height :1;
			$content['autoMatchLevel']= 1;
			$content['billType']=0;
			$content['subType']= 'wds';
			
			
			
			//云洋下单保存数据
			$requestParams['channelTag']=$channelTag;
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
			$requestParams['packageCount']= $totalNumber ? $totalNumber : 1;
			$requestParams['itemName']= $cargodata['name'];
			$requestParams['senderCompany']= "";
			$requestParams['receiveCompany']= "";
			$requestParams['insured']= $insurancePrice;//保费
			$requestParams['vloumLong']= '';
			$requestParams['vloumWidth']= '';
			$requestParams['vloumHeight']= '';
			$requestParams['warehouseCode']= "";
			$requestParams['channelId']= $cargodata['express_channel'];
		
			if($st && $et){
				$requestParams['pickupStartTime']= null;
				$requestParams['pickupStopTime']= null;
			}else{
				$requestParams['pickupStartTime']= $st;
				$requestParams['pickupStopTime']= $et;
			}
		    
		    
		    if($orderType == '3' && $cargodata['express_code']=='德邦'){
				$tag=1;
		    }
		    if($cargodata['express_code']=='德邦重货' || $cargodata['express_code']=='跨越' || $cargodata['express_code']=='顺心捷达'){
			    $tag=1;
			}
			
			if($tag==1){
				$requestParams['itemName']= $cargodata['name'];
				$requestParams['senderCompany']= "A公司";
				$requestParams['receiveCompany']= "B公司";
			}
			
			$data['requestParams'] = iserializer($requestParams);//保存到数据库序列化
		
			$performance = model('Setting')->performance($content,$Method ='CHECK_CHANNEL_INTELLECT');
			//云洋计算单价
			if($performance['code'] == 1){
				
				$result = $performance['result'];
				foreach($result as $ks=>$vs){
					if($cargodata['express_channel'] == $vs['channelId']){
						$v = $vs;
					}
				}
				
				//云洋云端请求数据
				$priceA = $v['priceOne']?$v['priceOne']:$v['price']['priceOne'];//云洋首重
				$priceB = $v['priceMore']?$v['priceMore']:$v['price']['priceMore'];//云洋续重
				$this->curl = new \Curl();
				$getZhe = model('Setting')->getZhe($uid,$e);
				$postData['v'] = $v;
				$postData['type'] = 2;
				$postData['uid'] = $uid;
				$postData['getZhe'] = $getZhe;
				$postData['totalWeight'] = $totalWeight;
				
			
				if($totalWeight==1 && $priceA < $v['freight']){
					//加起来价格不对
					$postData['TotalFee'] = $v['freight']*100;
					$postData['priceA'] = 0;
					$postData['priceB'] = 0;
				}else{
					$postData['TotalFee'] = $v['freight']*100;
					$postData['priceA'] = $priceA*100;
					$postData['priceB'] = $priceB*100;
				}
				
				$postData['originalFee'] = $v['originalPrice']*100;
				$postData['preBjFee'] = $v['freightInsured']*100;
				$postData['logoUrl'] = $e;
				$postData['insurancePrice'] = $insurancePrice;
				$postData['co'] = $co;
				$postData['coupon_pmt'] = $data['coupon_pmt'];
				$postData['host'] = trim($config['site']['host']);
				$postData['mobile'] = trim($config['site']['mobile']);
				$url = getHost().'/api/RequestApi/getCatePrice';
				$result = $this->curl->post($url,json_encode($postData));
				$result = json_decode($result,true);
				$getCatePrice = $result['data'];
				 model('Setting')->fillZhongfaPickupTimes($requestParams, array('yuyuetime' => $data['yuyuetime']));
				 if(!empty($requestParams['estimateOrderNo'])){
                $data['deliveryId'] = $requestParams['estimateOrderNo'];
            }
				
				$data['firstPrice'] = $getCatePrice['firstPrice'];
				$data['addPrice'] = $getCatePrice['addPrice'];
				$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
				$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
				$data['preOrderFee'] = $getCatePrice['preOrderFee'];
				$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
				$data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
				$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
				$data['type'] =2;//云洋接口模式
				
			}else{
				return json(array('code'=>0,'msg'=>'YY获取价格失败-'.$performance['message']));
			}
		}elseif($t == 3){
			
			
			//京东预下单
			$content['orderOrigin'] = 1;
			$content['settleType'] = 3;
			$content['customerCode'] = $config['wxapp']['jd_customerCode'];
			
			
			$receiverContact['fullAddress'] = $r_address;
			$content['receiverContact'] = $receiverContact;
			$senderContact['fullAddress'] = $s_address;
			$content['senderContact'] = $senderContact;
			$cargoes['name'] = $cargodata['name'];
			$cargoes['quantity'] = $totalNumber ? $totalNumber : 1;;
			$cargoes['weight'] = $totalWeight;
			$cargoes['volume'] = '1';
			$cargoes['length'] = '1';
			$cargoes['width'] = '0';
			$cargoes['hight'] = '0';
			$content['cargoes'] = $cargoes;
			$productsReq['productCode'] = 'ed-m-0001';
			$content['productsReq'] = $productsReq;
			$content = array($content);
			
			
			//京东下单
			$requestParams['orderId'] = $thirdNo;
			$requestParams['orderOrigin'] = 1;
			$requestParams['settleType'] = 3;
			$requestParams['customerCode'] = $config['wxapp']['jd_customerCode'];
			
			
				$receiverContact['name'] = $r['name'];
				$receiverContact['mobile'] = $receiveMobile;
				$receiverContact['fullAddress'] = $r_address;
			$requestParams['receiverContact'] = $receiverContact;
				$senderContact['name'] = $s['name'];
				$senderContact['mobile'] = $senderMobile;;
				$senderContact['fullAddress'] = $s_address;
			$requestParams['senderContact'] = $senderContact;
				$cargoes['name'] = $cargodata['name'];
				$cargoes['quantity'] = $totalNumber ? $totalNumber : 1;;
				$cargoes['weight'] = $totalWeight;
				$cargoes['volume'] = '1';
				$cargoes['length'] = '1';
				$cargoes['width'] = '0';
				$cargoes['hight'] = '0';
			$requestParams['cargoes'] = $cargoes;
				$productsReq['productCode'] = 'ed-m-0001';
			$requestParams['productsReq'] = $productsReq;
			
			
				$extendProps['key'] = 'autoSubscribe';
				$extendProps['value'] = '1';
			$requestParams['extendProps'] = $extendProps;
			
			$requestParams = array($requestParams);
			
			
			$data['requestParams'] = iserializer($requestParams);//保存到数据库序列化
			
			$kjd_post = model('JdApi')->jd_post($content,$method='/ecap/v1/orders/precheck');
			
			if($kjd_post['code']==0){
				$v = $kjd_post['data'];
				$this->curl = new \Curl();
				$getZhe = model('Setting')->getZhe($uid,$e);
				$postData['type'] = 2;
				$postData['uid'] = $uid;
				$postData['getZhe'] = $getZhe;
				$postData['totalWeight'] = $totalWeight;
				$postData['TotalFee'] = $v['totalFreightPre']*100;
				$postData['priceA'] = 0;
				$postData['priceB'] = 0;
				$postData['originalFee'] = $v['totalFreightStandard']*100;
				$postData['preBjFee'] = 0;
				$postData['logoUrl'] = $e;
				$postData['insurancePrice'] = $insurancePrice;
				$postData['co'] = $co;
				$postData['coupon_pmt'] = $data['coupon_pmt'];
				$postData['host'] = trim($config['site']['host']);
				$postData['mobile'] = trim($config['site']['mobile']);
				$url = getHost().'/api/RequestApi/getCatePrice';
				$result = $this->curl->post($url,json_encode($postData));
				$result = json_decode($result,true);
				$getCatePrice = $result['data'];
				
				$data['firstPrice'] = $getCatePrice['firstPrice'];
				$data['addPrice'] = $getCatePrice['addPrice'];
				$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
				$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
				$data['preOrderFee'] = $getCatePrice['preOrderFee'];
				$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
				$data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
				$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
				$data['type'] =3;//京东
			}else{
				return json(array('code'=>0,'msg'=>'jd获取价格失败-'.$kjd_post['msg']));
			}
		}elseif($t == 4) {
            if($orderType == '3'){
                $ShipperType = '6';
            }elseif ($orderType == '1'){
                $ShipperType = '2';
            }elseif ($orderType == '2'){
                $ShipperType = '2';
            }else{
                $ShipperType = '2';
            }
            if($long && $width && $height){
                $totalVolume = ($long / 100) * ($width / 100) * ($height / 100);
                if(!$totalVolume) {
                    $totalVolume = null;
                }else{
                    $totalVolume = number_format($totalVolume, 2);
                }
            }else{
                $totalVolume = null;
            }
            if($totalVolume == '0.00'){
                $totalVolume = null;
            }

            $requestParams['ShipperCode'] = $cargodata['express_channel'];
            $requestParams['ShipperType'] = $ShipperType;
            $requestParams['OrderCode'] = rand_string(8, 0);
            $requestParams['ExpType'] = 1;
            $requestParams['PayType'] = 3;
            $Receiver['Company'] = '';
            $Receiver['Name'] = $r['name'];
            $isMobile = isMobile($receiveMobile);
            if (!$isMobile) {
                $Receiver['TeL'] = $receiveMobile;
            } else {
                $Receiver['Mobile'] = $receiveMobile;
            }
            $Receiver['ProvinceName'] = $r['province'];
            $Receiver['CityName'] = $r['city'];
            $Receiver['ExpAreaName'] = $r['area'];
            $Receiver['Address'] = $r['address'];
            $requestParams['Receiver'] = $Receiver;
            $Sender['Company'] = '';
            $Sender['Name'] = $s['name'];
            $isMobile1 = isMobile($senderMobile);
            if(!$isMobile1){
                $Sender['TeL'] = $senderMobile;
            }else{
                $Sender['Mobile'] = $senderMobile;
            }
            $Sender['ProvinceName'] = $s['province'];
            $Sender['CityName'] = $s['city'];
            $Sender['ExpAreaName'] = $s['area'];
            $Sender['Address'] = $s['address'];
            $requestParams['Sender'] = $Sender;
            $requestParams['StartDate'] = null;
            $requestParams['EndDate'] = null;
            $requestParams['Weight'] = $totalWeight;
            $requestParams['Quantity'] = 1;
            $requestParams['Volume'] = $totalVolume;

            $requestParams['Remark'] = $remark;
            $Commodity['GoodsName'] = $cargodata['name'];
            $Commodity['Goodsquantity'] = 1;
            $Commodity['GoodsPrice'] = '';
            $requestParams['Commodity'] = $Commodity;

            $data['requestParams5'] = iserializer($requestParams);//快递鸟保存到数据库序列化

            $sendPost['Weight'] = $totalWeight;
            $sendPost['InsureAmount'] = '';
            $sendPost['PremiumFee'] = '';
            $Receivers['ProvinceName'] = $r['province'];
            $Receivers['CityName'] = $r['city'];
            $Receivers['ExpAreaName'] = $r['city'];
            $sendPost['Receiver'] = $Receivers;
            $Senders['ProvinceName'] = $s['province'];
            $Senders['CityName'] = $s['city'];
            $Senders['ExpAreaName'] = $s['area'];
            $sendPost['Sender'] = $Senders;
            $kdnSendPost = model('Setting')->kdnSendPost($sendPost, $RequestType = '1815');

            if($kdnSendPost['Success'] == false){
                return json(array('code' => 0, 'msg' => '预下单错误请检查参数' . $kdnSendPost['Reason']));
            }else{
                $i = 0;
                foreach ($kdnSendPost['Data'] as $key => $val) {
                    if ($cargodata['express_channel'] == $val['shipperCode']) {
                        if ($val['shipperCode'] == 'JTSD') {
                            $cate_name = '极兔';
                        }
                        if ($val['shipperCode'] == 'SF') {
                            $cate_name = '顺丰';
                        }
                        if ($val['shipperCode'] == 'STO') {
                            $cate_name = '申通';
                        }
                        if ($val['shipperCode'] == 'YD') {
                            $cate_name = '韵达';
                        }
                        if ($val['shipperCode'] == 'YTO') {
                            $cate_name = '圆通';
                        }
                        if ($val['shipperCode'] == 'JDKY') {
                            $cate_name = '京东';
                        }
                        if ($val['shipperCode'] == 'JD') {
                            $cate_name = '京东';
                        }
                        if ($val['shipperCode'] == 'DBL') {
                            $cate_name = '德邦';
                        }
                        if ($val['shipperCode'] == 'ZTO') {
                            $cate_name = '中通';
                        }
                        if ($val['shipperCode'] == 'ZTO') {
                            $cate_name = '中通';
                        }
                        if ($val['shipperCode'] == 'EMS') {
                            $cate_name = 'EMS';
                        }
                        if ($val['shipperCode'] == 'KYSY') {
                            $cate_name = '跨域';
                        }
                        $getData['totalWeight'] = $totalWeight;

                        $totalFee = $val['totalFee'] * 100;
						$firstWeightAmount = $val['firstWeightAmount']*100;
						$continuousWeightAmount = $val['continuousWeightAmount']*100;
						$continuousWeightAmount = $continuousWeightAmount/($totalWeight-1);
						$continuousWeightAmount = (int)$continuousWeightAmount;
						
                        //快递鸟云端请求数据
                        $this->curl = new \Curl();
                        $getZhe = model('Setting')->getZhe($uid,$e);
                        $postData['v'] = $v;
                        $postData['type'] = 5;
                        $postData['uid'] = $uid;
                        $postData['getZhe'] = $getZhe;
                        $postData['totalWeight'] = $totalWeight;
                        $postData['TotalFee'] = $val['totalFee'] * 100;
                        $postData['priceA'] = $firstWeightAmount;
                        $postData['priceB'] = $continuousWeightAmount;
                        $postData['originalFee'] = 0;
                        $postData['preBjFee'] = 0;
                        $postData['logoUrl'] = $e;
                        $postData['insurancePrice'] = $insurancePrice;
                        $postData['co'] = $co;
                        $postData['coupon_pmt'] = $data['coupon_pmt'];
                        $postData['host'] = trim($config['site']['host']);
                        $postData['mobile'] = trim($config['site']['mobile']);
                        $url = getHost(). '/api/RequestApi/getCatePrice';
                        $result = $this->curl->post($url, json_encode($postData));
                        $result = json_decode($result, true);
                        $getCatePrice = $result['data'];
                    }
                }

            }
            if($getCatePrice['sumMoneyYuan']){
                $data['firstPrice'] = $getCatePrice['firstPrice'];
                $data['addPrice'] = $getCatePrice['addPrice'];
                $data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
                $data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
                $data['preOrderFee'] = $getCatePrice['preOrderFee'];
                $data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
                $data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
                $data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
                $data['type'] = 4;//快递鸟接口模式
            }else{
                return json(array('code' => 0, 'msg' => '获取价格错误'));
            }
        }elseif($t == 5) {
		   	$orderCreate = model('KuayueApi')->orderCreate($uid,$u,$e,$shop,$s,$r,$smail_id,$rmail_id,$is_dw,$coupon_code,$data,$cargodata,$thirdNo,$totalWeight,$totalNumber,$remark,$sendStartTime,$sendEndTime);
			if($orderCreate == false){
				return json(array('code'=>0,'msg'=>model('Ad')->getError()));
			}
			 if($orderCreate['sumMoneyYuan'] <=0){
                return json(array('code' => 0, 'msg' => '获取价格错误'));
            }
			$data = @array_merge($data,$orderCreate);
        }elseif($t==6){
		
		
			$senderInfos['address'] = $s['address'];//寄件人完整详细寄件地址，包括省市区街道门牌等
				$senderInfos['cityName'] = $s['city'];
				$senderInfos['areaName'] = $s['area'];
				$senderInfos['fullAddressDetail'] = $s_address;
				$senderInfos['name'] = $s['name'];
				$senderInfos['mobile'] = $senderMobile;
				$senderInfos['provinceName'] = $s['province'];
			$request['senderInfo'] = $senderInfos;
				$receiverInfos['address'] = $r['address'];//寄件人完整详细寄件地址，包括省市区街道门牌等
				$receiverInfos['cityName'] = $r['city'];
				$receiverInfos['areaName'] = $r['area'];
				$receiverInfos['fullAddressDetail'] = $r_address;
				$receiverInfos['name'] = $r['name'];
				$receiverInfos['mobile'] = $receiveMobile;
				$receiverInfos['provinceName'] = $r['province'];
			$request['receiverInfo'] = $receiverInfos;
			$request['itemVersion'] = $this->config['wxapp']['cainiao_link_itemVersion'] ? $this->config['wxapp']['cainiao_link_itemVersion'] : '1';//寄件服务类型版本号，通过服务预查询接口获取
			
			if($sendStartTime && $sendStartTime != '-0-0'){
				$sendEndTime = explode("-", $sendEndTime);
				$request['timeType'] = '2';
				$request['appointGotStartTime'] = $sendStartTime.' '.$sendEndTime[0].':00';
				$request['appointGotEndTime'] = $sendStartTime.' '.$sendEndTime[1].':00';
			}else{
				$request['timeType'] = '1';//时效类型：1-实时单，2-预约单
			}
			$request['userRemark'] = $remark;
			
			
			$area1 = Db::name('copy_area')->where(array('Name'=>$s['area']))->find();
			if(!$area1){
				$Name = @mb_substr($s['area'],0,2);
				$area1 = Db::name('copy_area')->where(array('Name'=>array('LIKE','%'.$Name.'%')))->find();
			}
			$city1 = Db::name('copy_city')->where(array('city_id'=>$area1['city_id']))->find();
			
			
			$area2 = Db::name('copy_area')->where(array('Name'=>$r['area']))->find();
			if(!$area2){
				$Name = @mb_substr($r['area'],0,2);
				$area2 = Db::name('copy_area')->where(array('Name'=>array('LIKE','%'.$Name.'%')))->find();
			}
			$city2 = Db::name('copy_city')->where(array('city_id'=>$area2['city_id']))->find();
			
			
			
			$luggageOrder['endAddress'] = $r_address; 
				$luggageOrder['endAreaCode'] = $area2['area_id']; 
				$luggageOrder['endCityCode'] = $area2['city_id']; 
				$luggageOrder['endProvinceCode'] = $city2['ParentId']; 
				$luggageOrder['getMethod'] = 2; 
				$luggageOrder['luggageNum'] =1; 
				$luggageOrder['mobPhone'] =$senderMobile; 
				$luggageOrder['orderAmount'] = 0; 
				$luggageOrder['orderType'] = 3; 
				$luggageOrder['sendingMethod'] = 2; 
				$luggageOrder['sourceCode'] = ""; //8LJ31M0YBAQD8F018
				$luggageOrder['name'] = $s['name']; 
				$luggageOrder['consigneeName'] = $r['name']; 
				$luggageOrder['consigneePhone'] = $receiveMobile; 
				$luggageOrder['startAddress'] = $s_address; 
				$luggageOrder['startAreaCode'] = $area1['area_id']; 
				$luggageOrder['startCityCode'] = $area1['city_id']; 
				$luggageOrder['startProvinceCode'] = $city1['ParentId']; 
				$luggageOrder['takeEndTime'] = ""; 
				$luggageOrder['takeStartTime'] = ""; 
				$luggageOrder['isFollow'] = 2; 
				$luggageOrder['itemType'] =5; 
			$requestParams['luggageOrder'] = $luggageOrder;
			//商品信息
				$luggageInfos['goods'] = "高尔夫盒";
				$luggageInfos['insureInfos'][]['insureCode'] = "RBHWYSX-A";
			$requestParams['luggageInfos'][] = $luggageInfos;
			$data['requestParams'] = iserializer($requestParams);//保存到数据库序列化
			
			
			$TotalFee = $this->config['wxapp']['hangkong_Money']*100;
			$TotalFee = (int)$TotalFee;
			if($TotalFee<=0){
				return json(array('code'=>0,'msg'=>'获取价格错误'));
			}
			$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$TotalFee,0,0,0,0,$e,$insurancePrice,$co,$data['coupon_pmt'],$expressValue);
			if($getCatePrice['sumMoneyYuan']){
				$data['firstPrice'] = $getCatePrice['firstPrice'];
				$data['addPrice'] = $getCatePrice['addPrice'];
				$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
				$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
				$data['preOrderFee'] = $getCatePrice['preOrderFee'];
				$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
				$data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
				$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
				$data['type']=6;
			}else{
				return json(array('code'=>0,'msg'=>'获取价格错误'));
			}
		}elseif($t==7){
			
			$getExpressCateType = model('UlifegoApi')->getExpressCateType($e);
			$requestParams['type'] = $getExpressCateType;
			$requestParams['deliveryType'] = '';
			
			if($e['cate_name']=='顺丰'){
				$requestParams['promiseTimeType'] = '7';
			}else{
				$requestParams['promiseTimeType'] = '';
			}
			$requestParams['senderPhone'] = $senderMobile;
			$requestParams['senderName'] = $s['name'];
			$requestParams['senderAddress'] = $s_address;
			$requestParams['receivePhone'] = $receiveMobile;
			$requestParams['receiveName'] = $r['name'];
			$requestParams['receiveAddress'] = $r_address;
			$requestParams['weight'] = $totalWeight;
			$requestParams['packageNum'] =1;
			if($insurancePrice == 0){
			    $requestParams['guaranteeValueAmount']= '';//保费
			}else{
			    $requestParams['guaranteeValueAmount']= round($insuranceValue/100,0);//保费 
			}
			$requestParams['goods'] = $cargodata['name'];
			$requestParams['length'] = $long;
			$requestParams['width'] = $width;
			$requestParams['height'] = $height;
			$requestParams['payMethod'] = $payMethod;
			$requestParams['remark'] = $remark;
			$data['requestParams6'] = iserializer($requestParams);//q必达保存到数据库序列化
		
	
			$listData['type'] = model('UlifegoApi')->getExpressCateType($e);
			$listData['deliveryType'] = '';
			$listData['promiseTimeType'] = '';
			if($e['cate_name']=='顺丰'){
				$listData['promiseTimeType'] = '1';
			}else{
				$listData['promiseTimeType'] = '';
			}
			$listData['sendPhone'] = $senderMobile;
			$listData['sendAddress'] = $s_address;
			$listData['receiveAddress'] = $r_address;
			$listData['weight'] = $totalWeight;
			$listData['packageNum'] = '1';
			$listData['goodsValue'] = '0';
			$listData['length'] = $long;
			$listData['width'] = $width;
			$listData['height'] =$height;
			$listData['payMethod'] = (int)$payMethod;
			
			if($orderType == '3'){
    			$expressType = '2';
    		}elseif($orderType == '1'){
    			$expressType = '1';
    		}elseif($orderType == '2'){
    			$expressType = '3';
    		}else{
    			$expressType = '1';
    		}
		    $listData['expressType'] = $expressType;
		
			$ulifego = model('UlifegoApi')->ulifego_post($listData,$method='/openApi/getPriceList');
			if($ulifego['code'] !=0){
				return json(array('code'=>0,'msg'=>'预下单错误6-【'.$ulifego['msg'].'】'));
			}else{
				$list = $ulifego['data'];
				foreach($list as $k=>$val){
					if($val['productCode'] == $cargodata['express_channel']){
						$v = $val;
					}
				}
				$getData['totalWeight'] = $totalWeight;
				$channelFee = $v['channelFee']*100;
				$priceA = $v['priceA']*100;
				$priceB = $v['priceB']*100;
				$originalFee = $v['originalFee']*100;
				$getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$channelFee,$priceA,$priceB,$originalFee,0,$e,$insurancePrice,$co,$data['coupon_pmt'],$expressValue);
			}
			if($getCatePrice['sumMoneyYuan']){
				$data['firstPrice'] = $getCatePrice['firstPrice'];
				$data['addPrice'] = $getCatePrice['addPrice'];
				$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
				$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
				$data['preOrderFee'] = $getCatePrice['preOrderFee'];
				$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
				$data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
				$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
				$data['type']=7;//q必达接口模式
			}else{
				return json(array('code'=>0,'msg'=>'获取价格错误'));
			}
		}elseif($t==8){
            $e['ratio'] = $e['ratio1'];
            $getZhe = model('Setting')->getZhe($uid,$v);
            $zhe = $getZhe['zhe'];
            $zhe2= $getZhe['zhe2'];

            if($cargodata['transportType']=='community'){
                $ecp = Db::name('business_cate_provinces')->where(array('community_id'=>$e['community_id'],'star_province_name'=>$s['province'],'end_province_name'=>$r['province']))->find();
                if(!$ecp){
                    return json(array('code'=>0,'msg'=>'未获取到自定义价格寄件城市【'.$s['province'].'】-【'.$r['province'].'】'));
                }
                $getCalculateWeight= model('Setting')->getCalculateWeight($long,$width,$height,$data['kuaidi'],$data['wight']);
                $w = $getCalculateWeight-1;
                if($w > 1){
                    $TotalFee = $ecp['shou']+($ecp['xu']*$w);
                }else{
                    $TotalFee = $ecp['shou'];
                }
                $getCatePrice = model('Setting')->getCatePrice($uid,$getCalculateWeight,$TotalFee,$e['shou'],$e['xu'],0,0,$e,$insurancePrice,$co,$data['coupon_pmt'],$expressValue);
            }else{


                $getCalculateWeight= model('Setting')->getCalculateWeight($long,$width,$height,$data['kuaidi'],$data['wight']);
                $ecp = Db::name('express_cate_province')->where(array('cate_id'=>$e['cate_id'],'star_province_name'=>$s['province'],'end_province_name'=>$r['province']))->find();
                if(!$ecp){
                    return json(array('code'=>0,'msg'=>'未获取到自定义价格寄件城市【'.$s['province'].'】-【'.$r['province'].'】'));
                }
                $getCalculateWeight= model('Setting')->getCalculateWeight($long,$width,$height,$data['kuaidi'],$data['wight']);
                $w = $getCalculateWeight-1;
                if($w >=1){
                    $TotalFee = $ecp['shou']+($ecp['xu']*$w);
                }else{
                    $TotalFee = $ecp['shou'];
                }
                $getCatePrice = model('Setting')->getCatePrice($uid,$getCalculateWeight,$TotalFee,$ecp['shou'],$ecp['xu'],0,0,$e,$insurancePrice,$co,$data['coupon_pmt'],$expressValue);
            }

			$data['firstPrice'] = $getCatePrice['firstPrice'];
			$data['addPrice'] = $getCatePrice['addPrice'];
			$data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
			$data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
			$data['preOrderFee'] = $getCatePrice['preOrderFee'];
			$data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
			$data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
			$data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
			$data['type'] =8;
			$data['is_pei'] = (int)2;
			$data['realOrderCode'] = rand_string(6,1);//取件码
		}elseif($t == 9){

            if($orderType == '3'){
                $channelTag = '重货';
            }elseif($orderType == '5'){
                $channelTag = '智能';
            }elseif($orderType == '6'){
                $channelTag = "重货";
            }elseif($orderType == '1'){
                $channelTag = '智能';
            }elseif($orderType == '2'){
                $channelTag = '得物';
            }else{
                $channelTag = '智能';
            }


            $content['channelTag']=$channelTag;
            $content['channelSubTag']='';
            $content['channelSubType']='智能';//当channelSubTag为京东、德邦时必填，具体值参考指南内的产品类型;若要匹配全部渠道传智能
            $content['sender']=$s['name'];
            $content['senderMobile']= $senderMobile;
            $content['senderProvince']= $s['province'];
            $content['senderCity']= $s['city'];
            $content['senderCounty']= $s['area'];
            $content['senderTown']='';
            $content['senderLocation']= $s['address'];
            $content['senderAddress']= $s['province'].$s['city'].$s['area'].$s['address'];
            $content['receiver']=$r['name'];
            $content['receiverMobile']= $receiveMobile;
            $content['receiveProvince']= $r['province'];
            $content['receiveCity']= $r['city'];
            $content['receiveCounty']= $r['area'];
            $content['receiveTown']= '';
            $content['receiveLocation']= $r['address'];
            $content['receiveAddress']=$r['address'];
            $content['weight']= $totalWeight;
            $content['itemName']= '日用品';
            $content['packageCount']= $totalNumber;
            $content['insured']= $insurancePrice;
            $content['customerFreight']= 0;
            $content['collectionMoney']= 0;
            $content['vloumLong']= '';
            $content['vloumWidth']= '';
            $content['vloumHeight']='';


            $requestParams['channelTag']=$channelTag;
            $requestParams['sender']=$s['name'];
            $requestParams['senderMobile']= $senderMobile;
            $requestParams['senderProvince']= $s['province'];
            $requestParams['senderCity']= $s['city'];
            $requestParams['senderCounty']= $s['area'];
            $requestParams['senderTown']= '';
            $requestParams['senderLocation']= $s['address'];
            $requestParams['senderAddress']= $s_address;
            $requestParams['receiver']= $r['name'];
            $requestParams['receiverMobile']= $receiveMobile;
            $requestParams['receiveProvince']= $r['province'];
            $requestParams['receiveCity']= $r['city'];
            $requestParams['receiveCounty']= $r['area'];
            $requestParams['receiveTown']= '';

            $d2 = strstr($r['address'],$r['province']);
            if($d2 == false){
                $r_address = $r['address'];
            }else{
                $r_address = str_replace($r['province'].$r['city'].$r['area'],"",$r['address']);
            }


            $requestParams['receiveLocation']= $r['address'];
            $requestParams['receiveAddress']= $r_address;



            $requestParams['weight']= $totalWeight;
            $requestParams['billType']= $e['cate_id'];
            $requestParams['packageCount']= $totalNumber;
            $requestParams['itemName']= $cargodata['name'];
            $requestParams['senderCompany']= "";
            $requestParams['receiveCompany']= "";
            if($insurancePrice == 0){
                $requestParams['insured']= '';//保费
            }else{
                $requestParams['insured']= round($insuranceValue/100,0);//保费
            }
            $requestParams['vloumLong']= '';
            $requestParams['vloumWidth']= '';
            $requestParams['vloumHeight']= '';
            $requestParams['warehouseCode']= "";
            $requestParams['channelId']= $cargodata['express_channel'];
            if($st && $et){
                $requestParams['pickupStartTime']= $st;
                $requestParams['pickupStopTime']= $et;
            }else{
                $requestParams['pickupStartTime']= null;
                $requestParams['pickupStopTime']= null;
            }
            $requestParams['billRemark']= $remark;


            $data['requestParams'] = iserializer($requestParams);//保存到数据库序列化
            $performance = model('YtApi')->performance($content,$Method ='CHECK_CHANNEL_INTELLECT');//查询价格


            if($performance['code'] == 1){
                $result = $performance['result'];
                foreach($result as $ks=>$vs){
                    if($cargodata['express_channel'] == $vs['channelId']){
                        $v = $vs;
                    }
                }
                $getData['totalWeight'] = $totalWeight;
                $getData['sender_province'] = $s['province'];
                $getData['recipients_province'] = $r['province'];
                if(!$e){
                    $e = model('ExpressOrder')->logoUrl($cargodata['express_code'],$uid,18,$v);
                }

                $priceA = $v['priceOne']?$v['priceOne']:$v['price']['priceOne'];
                $priceB = $v['priceMore']?$v['priceMore']:$v['price']['priceMore'];

                $totalFreight = $v['totalFreight']?$v['totalFreight']:$v['freight'];
                $totalFreight = $totalFreight*100;

                $freightHaocai = $v['freightHaocai']*100;
                $freightInsured = $v['freightInsured']*100;
                $other = $freightHaocai+$freightInsured;
                $originalPrice = $v['originalPrice']*100;


                $pag =0;
                if($orderType == 1 && $priceA < $v['freight']){
                    $pag =1;
                }
                if($orderType == '5'){
                    $pag =1;
                }
                if($orderType == '6'){
                    $pag =0;
                }
                if($pag==1){
                    //加起来价格不对
                    $getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$totalFreight,0,0,$originalPrice,0,$e,$insurancePrice,$co,$data['coupon_pmt'],$expressValue);
                }else{
                    $getCatePrice = model('Setting')->getCatePrice($uid,$totalWeight,$totalFreight,$priceA*100,$priceB*100,$originalPrice,$freightInsured,$e,$other,$co,$data['coupon_pmt'],$expressValue);
                }
                $data['firstPrice'] = $getCatePrice['firstPrice'];
                $data['addPrice'] = $getCatePrice['addPrice'];
                $data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
                $data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
                $data['preOrderFee'] = $getCatePrice['preOrderFee'];
                $data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
                $data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
                $data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
                $data['type'] =9;//云腾旺店管家
            }else{
                return json(array('code'=>0,'msg'=>'Yt获取价格失败-'.$performance['message']));
            }
        }elseif($t==10){
            $getZhe = model('Setting')->getZhe($uid,$v);
            $zhe = $getZhe['zhe'];
            $zhe2= $getZhe['zhe2'];

            $ecp = Db::name('express_cate_province')->where(array('cate_id'=>$e['cate_id'],'star_province_name'=>$s['province'],'end_province_name'=>$r['province']))->find();
            if(!$ecp){
                return json(array('code'=>0,'msg'=>'未获取到自定义价格寄件城市【'.$s['province'].'】-【'.$r['province'].'】'));
            }
            //获取重量
            $getCalculateWeight= model('Setting')->getCalculateWeight($long,$width,$height,$data['kuaidi'],$data['wight']);
            $w = $getCalculateWeight-1;
            if($w >=1){
                $TotalFee = $ecp['shou']+($ecp['xu']*$w);
            }else{
                $TotalFee = $ecp['shou'];
            }
            $getCatePrice = model('Setting')->getCatePrice($uid,$getCalculateWeight,$TotalFee,$ecp['shou'],$ecp['xu'],0,0,$e,$insurancePrice,$co,$data['coupon_pmt'],$expressValue);
            $data['firstPrice'] = $getCatePrice['firstPrice'];
            $data['addPrice'] = $getCatePrice['addPrice'];
            $data['firstPrice_jia'] = $getCatePrice['firstPrice_jia'];
            $data['addPrice_jia'] = $getCatePrice['addPrice_jia'];
            $data['preOrderFee'] = $getCatePrice['preOrderFee'];
            $data['sumMoneyYuan'] = $getCatePrice['sumMoneyYuan'];
            $data['sumMoneyYuan_old'] = $getCatePrice['sumMoneyYuan_old'];
            $data['sumMoneyYuan_jia'] = $getCatePrice['sumMoneyYuan_jia'];
            $data['type'] =10;
        }elseif($t==11){
            $model = (int)$this->config['delivery']['model'];
            if($this->config['delivery']['yy_open']==1 && $model==0){
                $content['sender']=$data['sendName'];
                $content['senderMobile']= $data['sendMobile'];
                $content['senderAddress']= $data['sendAddress'];
                $content['receiver']=$data['receiveName'];
                $content['receiverMobile']=$data['receiveMobile'];
                $content['receiveLocation']= $data['receiveAddress'];
                $content['receiveAddress']=$data['receiveAddress'];
                $content['weight']= (int)$data['wight'];
                $content['senderLat']= $s['lat'];
                $content['senderLng']= $s['lng'];
                $content['receiveLat']=$r['lat'];
                $content['receiveLgt']=$r['lng'];
                $data['requestParams'] = iserializer($content);//保存到数据库序列化
                $performance = model('City')->performance($content,$Method ='QUERY_DELIVER_FEE');
                if($performance['code'] == 0){
                    return json(array('code'=>0,'msg'=>'同城下单参数错误'));
                }else{
                    foreach($performance['result'] as $k=>$v){
                        if($cargodata['express_channel'] == $v['third_logistics_id']){
                            $vs = $v;
                        }
                    }
                    $tongchengPrice = model('City')->tongchengPrice($data['user_id'],$vs['fee']*100,$data,$coupon_pmt=0);//同城价格获取
                }
                $data['expressId'] = $performance['message'];//运力ID
                $data['expressNo'] = $vs['third_logistics_id'];//运力ID
                $data['firstPrice'] = $tongchengPrice['firstPrice'];
                $data['addPrice'] = $tongchengPrice['addPrice'];
                $data['firstPrice_jia'] = $tongchengPrice['firstPrice_jia'];
                $data['addPrice_jia'] = $tongchengPrice['addPrice_jia'];
                $data['preOrderFee'] = $tongchengPrice['preOrderFee'];
                $data['sumMoneyYuan'] = $tongchengPrice['sumMoneyYuan'];
                $data['sumMoneyYuan_old'] = $tongchengPrice['sumMoneyYuan_old'];
                $data['sumMoneyYuan_jia'] = $tongchengPrice['sumMoneyYuan_jia'];
                $data['type'] =11;//同城模式
                $data['is_pei'] = 11;//同城取件模式
                $data['orderType'] = 0;
            }

        }
		
		
		
		//用户实际支付金额【用于前台支付】
		$data['sumMoneyYuan'] = $data['sumMoneyYuan'] -$discountmoney;
		$order_money = round($data['sumMoneyYuan']/100,2);
	
		
		if($order_money <= 0){
			return json(array('code'=>0,'msg'=>'获取价格失败，请重新选择快递公司下单'));
		}
		
		
		$card = (int)$this->config['card']['order_duihuan_open'];
		$is_agree2 = input('is_agree2','','trim,htmlspecialchars');
		if($card==1){
			if($is_agree2){
				if($u['moneys'] < $getCatePrice['dikou2']){
					return json(array('code'=>0,'msg'=>'您抵扣金额不够请不要勾选'));
				}				
				$data['moneys'] = $getCatePrice['dikou2'];
				$data['originalFee'] = $getCatePrice['originalFee2'];
				$data['sumMoneyYuan'] = $data['sumMoneyYuan'];
				$order_money = $order_money;
			}
			
			if(!$is_agree2){
				//没开抵扣
				$data['originalFee'] = $getCatePrice['originalFee2'];
				$data['sumMoneyYuan'] = $getCatePrice['originalFee2'] -$discountmoney;
				$order_money = round($getCatePrice['originalFee2']/100,2);
				
			}
		}
		
		
		$order_id = Db::name('express_order')->insertGetId($data);
		if($order_id){
			$data['order_id'] = $order_id;	
			$data['recipients_id'] = $uid;	
			$data['order_money'] = $order_money;	
			$data['is_jump_list'] = false;
            if($img){
                foreach ($img as $k=>$v){
                    $expressOrderPhotoData['order_id'] = $order_id;
                    $expressOrderPhotoData['photo'] = $v;
                    $expressOrderPhotoData['create_time'] = time();
                    Db::name('express_order_photo')->insertGetId($expressOrderPhotoData);
                }
            }
			return json(array('code'=>1,'msg'=>"下单成功",'data'=>$data,'v'=>$v,'getCatePrice'=>$getCatePrice));
		}else{
			return json(array('code'=>0,'msg'=>'写入数据库失败'));
		}
	}  
	
	
	
	//下单支付
	public function submitpay(){
		
		 $order_id = input('order_id','','trim,htmlspecialchars');
		 $paytype= input('paytype','','trim,htmlspecialchars');
		 $ordertype = input('ordertype','','trim,htmlspecialchars');
		 $is_jump_list = input('is_jump_list','','trim,htmlspecialchars');
		 $platform = input('platform','','trim,htmlspecialchars');
		 $uid = $this->getUserId();
		 $u = Db::name('users')->where(array('user_id'=>$uid))->find();
		 $o = Db::name('express_order')->where(array('id'=>$order_id))->find();
		
		 
		 if($ordertype == 1){
			 $need_pay = $o['sumMoneyYuan']; 
			 $types = 1;
			 $info = '快递下单';
		 }elseif($ordertype == 3){
			 $need_pay = $o['diffMoneyYuan']; 
			 $types = 2;
			 $info = '差价订单';
		 }else{
			 $need_pay = $o['sumMoneyYuan']; 
			 $types = 1;
			 $info = '快递下单';
		 }
		 
		 
		 if($paytype =='wechat'){
			//微信支付
			$logs = array(
				'type' => 'express', 
				'types' => $types, 
				'user_id' => $uid, 
				'order_id' => $order_id, 
				'code' => 'wxapp', 
				'info' => $info, 
				'need_pay' =>$need_pay, 
				'create_time' => time(), 
				'create_ip' => request()->ip(), 
				'is_paid' => 0
			);
			$logs['log_id'] = Db::name('payment_logs')->insertGetId($logs);
			
			
			$connect = Db::name('connect')->where(array('uid'=>$uid))->order(array('connect_id'=>'desc'))->find();	
			$WX_OPENID = $connect['openid'] ? $connect['openid'] : $connect['open_id'];	

			$Payment = model('Payment')->getPayment('wxapp');
			$out_trade_no = $logs['log_id'].'-'.time();
			$weixinpay = new \Wxpay($this->config['wxapp']['appid'],$WX_OPENID,$Payment['mchid'],$Payment['appkey'],$out_trade_no,$info,$need_pay);//支付接口
			$return = $weixinpay->pay();
			if($return['package'] == 'prepay_id='){
				return json(array('code'=>0,'msg'=>'预支付失败:'.$return['rest']['return_msg']));
			}
			$payInfo['timeStamp']= $return['timeStamp'];
			$payInfo['nonceStr'] =$return['nonceStr'];
			$payInfo['package'] =$return['package'];
			$payInfo['signType'] = 'MD5';
			$payInfo['paySign'] = $return['paySign'];

			return json(array('code'=>1,'msg'=>"微信支付下单成功",'data'=>$payInfo));
		}elseif($paytype =='toutiao'){
			//头条支付
			$logs = array(
				'type' => 'express', 
				'types' => $types, 
				'user_id' => $uid, 
				'order_id' => $order_id, 
				'code' => 'toutiao', 
				'info' => $info, 
				'need_pay' =>$need_pay, 
				'create_time' => time(), 
				'create_ip' => request()->ip(), 
				'is_paid' => 0
			);
			$logs['log_id'] = Db::name('payment_logs')->insertGetId($logs);
			$out_trade_no = $logs['log_id'];
		
			$ttPay = new \TtPay($this->config['toutiao']['AppID'],$this->config['toutiao']['token'],$this->config['toutiao']['SALT'],$out_trade_no,$info,$need_pay);//支付接口
			$pay = $ttPay->order();
			if($pay['err_no'] != '0'){
				return json(array('code'=>0,'msg'=>'抖音预支付失败'));
			}
			return json(array('code'=>1,'msg'=>"抖音支付下单成功",'data'=>$pay));
		}elseif($paytype == 'balance'){
			if($u['money'] < $need_pay){
				return json(array('code'=>0,'msg'=>'余额不足'));
			}
			$rest = model('Users')->addMoney($uid,-$need_pay,'余额支付订单id-'.$order_id,1);
			if($rest){
				
				 if($ordertype == 1){
			 		//正常余额支付订单回调
					$updateExpressOrder = model('Setting')->updateExpressOrder($order_id,$need_pay,$log_id=0,$uid,1);
				 }elseif($ordertype == 3){
					 //差价订单支付回调
					$updateExpressOrder = model('Setting')->updateExpressOrder($order_id,$need_pay,$log_id=0,$uid,2);
				 }
				
				if($updateExpressOrder == false){
					return json(array('code'=>0,'msg'=>'付款回调失败未知错误'.model('Setting')->getError()));
				}
				return json(array('code'=>1,'msg'=>"余额支付下单成功",'data'=>$data));
			}else{
				return json(array('code'=>0,'msg'=>'扣费失败'));
			}
		}
	}
	
	
	
	
    public function createOrder(){
		$data = array();
		return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
	}      
    
	
	
	//调用云存储
    public function superUpload($model){
        $upinfo = model("Uploadset")->where("status = 1")->find();
        if(!empty($upinfo) && $upinfo['type'] != 'Local') {
            $conf = json_decode($upinfo['para'], true);
            $superup = new \Upload(array('exts'=>'jpeg,jpg,gif,png'), $upinfo['type'], $conf);
            $upres = $superup->upload(); 
            return  $upres;
        }else{
            return false;
        }
    }
	//图片上传
	public function upload(){
        $model = input('model');
        $yun = $this->superUpload($model);
        if($yun){
            foreach($yun as $pk => $pv){
                $picurl = $pv['url'];
            }
			return json(array('code'=>1,'data'=>config_weixin_img($picurl)));   
        }else{
            $upload = new \UploadFile(); 
            $upload->maxSize = 3145728; 
            $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); 
            $name = date('Y/m/d', time());
            $dir = ROOT_PATH . '/attachs/' . $name . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $upload->savePath = $dir; 
            if(!$upload->upload()){
                $this->error($upload->getErrorMsg());
            }else{
                $info = $upload->getUploadFileInfo();
                if($upload->thumb){
                    $picurl =  '/attachs/'.$name . '/thumb_' . $info[0]['savename'];
					return json(array('code'=>1,'data'=>config_weixin_img($picurl)));
                }else{
                    $picurl = '/attachs/'.$name . '/' . $info[0]['savename'];
					return json(array('code'=>1,'data'=>config_weixin_img($picurl)));                }
            }
        }
    }
	
	
	
	//获取会员详细数据
	public function getUserData($user_id){
		$data = Db::name('users')->where(array('user_id'=>$user_id))->find();
			if($data){
			$data['token'] = $data['token'];
			$data['avatar'] = config_weixin_img($data['face']);
			$check_face = (int)$this->config['config']['check_face'];

            $data['money1'] = (int)($this->config['dianshang']['money1']*100);
            $data['money2'] = (int)($this->config['dianshang']['money2']*100);
            $data['money0'] = (int)($data['money']);

			if($data['uc_id'] == 0 && $check_face==1){
				$data['avatar_auth'] = 1;
			}
			
			$rank = Db::name('user_rank')->where(array('rank_id'=>$data['rank_id']))->find();
			if($rank['photo']){
				$data['photo'] = config_weixin_img($rank['photo']);
				$data['rank_name'] = $rank['rank_name'];
			}else{
				$data['photo'] = '';
				$data['rank_name'] = '';
			}
			if($data['mobile']){
				$mobile = substr_replace($data['mobile'],'******',5,4);
				$data['mobile'] = $mobile;	
			}else{
				$data['mobile'] = '暂未绑定手机';	
			}
			
			$coupon_download = (int)Db::name('coupon_download')->where(array('is_used'=>'0','user_id'=>$user_id))->count(); 	
			$data['coupon_num'] = $coupon_download;
			$data['points'] = $data['integral'];
			$data['promoteId'] = $data['user_id'];
			$data['money'] = round($data['money']/100,2);
			$data['moneys'] = round($data['moneys']/100,2);
			$data['can_withdraw'] = $data['money'];//可提现余额
			$data['withdraw'] = round($data['frozen_money']/100,2);//冻结金额
			$data['id'] = $data['user_id'];
			$data['grade'] = 1;
			if($data['rank_id']){
				$data['grade'] = 2;//2代表是VIP
			}
			$data['subscribe_status'] = $data['subscribe_status'];//1代表已关注	
			return $data;
		}
		return false;
	}
	
	
	//视频列表
	public function couponViewList(){
		$uid = (int)input('uid','','trim,htmlspecialchars');
		$d['list'] = array();
		return json(array('c'=>0,'d'=>$d));
	}
	
	

	//看广告领取积分
	public function reward(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'msg'=>'TOKEN失效请重新登录'));
		}
		$integral = (int)$this->config['integral']['adunit_integral'];
        $day_num = (int)$this->config['integral']['day_num'];
        $day_num = $day_num?$day_num:1;
        if(!$integral){
            return json(array('code'=>0,'msg'=>'积分配置为空'));
        }
		$bg_time = strtotime(TODAY);
        $c = (int) Db::name('user_integral_logs')->where(array('create_time' => array(array('ELT', time()), array('EGT', $bg_time)),'user_id' =>$uid, 'type' =>5))->count();
        if($c>$day_num) {
            return json(array('c'=>20020,'m'=>'今日已领取过积分【'.$c.'】次'));
        }
        $rest = model('Users')->addIntegral($uid,$integral,'看视频奖励积分',5);
        if($rest){
            $d['money'] = $integral;
            $d['moneyYuan'] = $integral;
            return json(array('c'=>0,'d'=>$d));
        }else{
            return json(array('c'=>20020,'m'=>'领取失败'));
        }
	}
	
	
	
	

	//注册
	public function addRegisterUser($result){
		
		if($result['mobile']){
			$users = Db::name('users')->where(array('mobile'=>$result['mobile']))->order(array('user_id'=>'desc'))->find();
			$connect = Db::name('connect')->where(array('type'=>'weixin','uid'=>$users['user_id']))->order(array('connect_id'=>'desc'))->find(); 	
		}elseif($result['unionid'] && $result['unionid'] !='undefined'){
			$connect = Db::name('connect')->where(array('type'=>'weixin','unionid'=>$result['unionid']))->order(array('connect_id'=>'desc'))->find(); 	
		}elseif($result['openid']){
			$connect = Db::name('connect')->where(array('type'=>'weixin','openid'=>$result['openid']))->order(array('connect_id'=>'desc'))->find(); 	; 	
		}else{
			$connect = Db::name('connect')->where(array('type'=>'weixin','openid'=>$result['openid']))->order(array('connect_id'=>'desc'))->find(); 	
		}
		
		
		if(!$users){
		    $users['user_id'] = 0;
		}
		if($connect['uid']){
			$users = Db::name('users')->where(array('user_id'=>$connect['uid']))->find();
		}
		
		
		$data['unionid'] = $result['unionid'];
		$data['open_id'] = '';
		$data['openid'] = $result['openid'];
        $data['type'] = 'weixin';
		$data['session_key'] = $result['session_key'];
		$data['rd_session'] = md5(time().mt_rand(1,999999999));
		
		//注册会员
		if(!$users['user_id']){
			if(!$connect){
				$data['create_time'] = time();
				$data['create_ip'] = request()->ip();
				$connect_id = Db::name('connect')->insertGetId($data);//新建表
			}else{
				$connect_id = $connect['connect_id'];//新建表
			}
			
			$rand = rand(1000,9999);
			$account = 'Exp_'.$connect_id.'_'.$rand;
            $arr = array(
               'account' => $account, 
			   'mobile' => $result['mobile'],
               'password' => $rand,
               'unionid' => $result['unionid'], 
               'face' => '/attachs/default.jpg', 
               'nickname' => $account, 
               'reg_time' => time(), 
               'reg_ip' =>request()->ip()
            );
		
            $user_id = model('Passport')->register($arr,$result['parent_id'],1);
			if($user_id){
				Db::name('connect')->update(array('connect_id'=>$connect_id,'uid'=>$user_id));
				$user = Db::name('users')->where(array('user_id'=>$user_id))->find();
				return $this->getUserData($user_id);
			}
		}else{
			
			
			
			$token = md5(uniqid());
			$updateData['connect_id'] = $connect['connect_id'];
			$updateData['openid'] = $result['openid'];
			$updateData['unionid'] = $result['unionid'];
			 
			$user = Db::name('users')->where(array('user_id'=>$users['user_id']))->update(array('token'=>$token));
			$user_id = $users['user_id'];
			
			if($connect['connect_id']){
				Db::name('connect')->where(array('connect_id'=>$connect['connect_id']))->update($updateData);
			}else{
				$data['create_time'] = time();
				$data['uid'] = $user_id;
				$data['create_ip'] = request()->ip();
				if($user_id){
					$connect_id = Db::name('connect')->insertGetId($data);//新建表
				}
			}
			
			
			return $this->getUserData($connect['uid']);
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

	public function wxappLogin3Api(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'msg'=>'TOKEN失效'));
		}
		$nickname = input('nickname','','trim,htmlspecialchars');
		$avatar = input('avatar','','trim,htmlspecialchars');
		$Data['nickname'] = $nickname;
		$Data['face'] = $avatar;
		$Data['uc_id'] = 1;
		$Data['last_time'] = time();
		if(!$avatar){
			return json(array('code'=>0,'msg'=>"请上传头像"));
		}
		if(!$nickname){
			return json(array('code'=>0,'msg'=>"请上传昵称"));
		}
		
		$r = Db::name('users')->where('user_id',$uid)->update($Data);
		if($r){
			return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
		}else{
			return json(array('code'=>0,'msg'=>"更新失败【删除小程序》重新搜索小程序》再次登录】"));
		}
	}

    public function href(){
        $id = input('id','','trim,htmlspecialchars');
        $Info = Db::name('express_order')->where(array('id'=>$id))->find();
        $data['guiji'] = model('Ad')->mapGuiji($Info['id']);
        if($Info){
            return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
        }else{
            return json(array('code'=>0,'msg'=>"更新失败【删除小程序》重新搜索小程序》再次登录】"));
        }
    }

    public function getPdf(){
        $id = input('id','','trim,htmlspecialchars');
        $info = Db::name('express_order')->where(array('id'=>$id))->find();
        if($info['pdfUrl']){
            $data = $info;
            $data['pdfUrl'] = config_weixin_img($info['pdfUrl']);
            return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
        }
        $requestParams = array('waybills'=>$info['deliveryId']);
        $pdfBytes = model('Setting')->performance($requestParams,$Method ='PRINT');
        $name = date('Y/m/d/',time());
        $md5 = md5($id);
        $patch =ROOT_PATH.'/attachs/'.'weixin/'.$name;
        if(!file_exists($patch)){
            mkdir($patch,0755,true);
        }
        $file = '/attachs/weixin/'.$name.$md5.'.pdf';
        $fileName  =ROOT_PATH.''.$file;

        $writeResult = file_put_contents($fileName, $pdfBytes);
        if($writeResult==true){
            Db::name('express_order')->where(array('id'=>$id))->update(array('pdfUrl'=>$file));
            $data = $info;
            $data['pdfUrl'] = config_weixin_img($file);
            return json(array('code'=>1,'msg'=>"获取成功",'data'=>$data));
        }else{
            return json(array('code'=>0,'msg'=>"获取失败"));
        }
    }








	
}
