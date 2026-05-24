<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Api extends Base{



	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->host = $this->config['site']['host'];

    }

    public function timeFanWei($sj=''){
        $t=0;
        $dtrq = date('Y-m-d ',time());
        $jz = strtotime($dtrq."$sj");
        $dq = time();
        if($dq <= $jz){
            $t = 0;
        }else{
            $t = 1;
        }
        return $t;
    }

	public function push(){
		$file_get_contents = file_get_contents("php://input");
		$input = @json_decode($file_get_contents,true);
		$data['deliveryId'] = $input['deliveryId'];
		$data['orderNo'] = $input['orderNo'];
		$data['deliveryType'] = $input['deliveryType'];
		$data['pushType'] = $input['pushType'];
		$data['context'] = $input['context'];
		$data['type'] = 1;
		$data['status'] = 1;
		$data['create_time'] = time();
		$id = 0;
		if($data['deliveryId']){
			$id =Db::name('express_order_push')->insert($data);
		}
		return json(array('success'=>true,'msg'=>'接收成功','code'=>200));
	}
	
	

	public function push1(){
		$file_get_contents = file_get_contents("php://input");
		$input = @json_decode($file_get_contents,true);
		$data['deliveryId'] = $input['waybill'];
		$data['orderNo'] = $input['shopbill'];
		$data['deliveryType'] = $input['billType'];
		$data['pushType'] = 1;
		$data['type'] = 2;
		$data['context'] = $file_get_contents;
		$data['status'] = 1;
		$data['create_time'] = time();
		$id = 0;
		if($data['deliveryId']){
			$id =Db::name('express_order_push')->insert($data);
		}
		return json(array('message'=>'推送成功！','code'=>1));
	}
	
	

	//京东回调
	public function push3(){
		$file_get_contents = file_get_contents("php://input");
		$input = @json_decode($file_get_contents,true);
		$data['deliveryId'] = $input['waybillCode'];
		$data['orderNo'] = $input['orderId'];
		$data['deliveryType'] = 1;
		$data['pushType'] = 1;
		$data['type'] = 3;
		$data['context'] = $file_get_contents;
		$data['status'] = 1;
		$data['create_time'] = time();
		$id = 0;
		if($data['deliveryId']){
			$id =Db::name('express_order_push')->insert($data);
		}
		return json(array('msg'=>'推送成功','code'=>200));
	}
	
	//快递鸟推送
	public function push4(){
		if(!empty($_POST)){
            foreach($_POST as $key => $v){
                $_GET[$key] = $v;
            }
        }
		$input = @json_decode($_GET['RequestData'],true);
		$data['deliveryId'] = $input['Data'][0]['LogisticCode'];
		$data['orderNo'] = $input['Data'][0]['KDNOrderCode'];
		$data['deliveryType'] = $input['Data'][0]['State'];
		$data['pushType'] = 1;
		$data['type'] = 4;
		$data['context'] = json_encode($input['Data'][0],320);
		$data['status'] = 1;
		$data['create_time'] = time();
		$id = 0;
		if($data['deliveryId']){
			$id =Db::name('express_order_push')->insert($data);
		}
		return json(array('EBusinessID'=>$this->config['wxapp']['kdn_EBusinessID'],'UpdateTime'=>date('Y-m-d H:i:s',time()),'Success'=>true,'Reason'=>'回调成功'));
	}
	
	
	//跨越回调
	public function push5(){
		$file_get_contents = file_get_contents("php://input");
		$input = @json_decode($file_get_contents,true);
        $input = $input[0];
		$data['deliveryId'] = $input['mailno'];
		$data['orderNo'] = '';
		$data['deliveryType'] = 1;
		$data['pushType'] = 1;
		$data['type'] = 5;
		$data['context'] = json_encode($input,320);
		$data['status'] = 1;
		$data['create_time'] = time();
		$id = 0;
		if($data['deliveryId']){
			$id =Db::name('express_order_push')->insert($data);
		}
		return json(array('msg'=>'推送成功','code'=>200));
	}
	
	//领航当日达
	public function push6(){
		$t = time();
		$file_get_contents = file_get_contents("php://input");
		$input = @json_decode($file_get_contents,true);
		
		file_put_contents("__".$t."push6_$input.txt", var_export($input,true));
	
		$data['deliveryId'] = $input['luggageOrder']['orderCode'];
		$data['orderNo'] = $input['luggageInfos'][0]['luggageCode'];
		$data['deliveryType'] = $input['luggageOrder']['status'];
		$data['pushType'] = 1;
		$data['type'] = 6;
		$data['context'] = json_encode($input);
		$data['status'] = 1;
		$data['create_time'] = time();
		$id = 0;
		if($data['deliveryId']){
			$id =Db::name('express_order_push')->insert($data);
		}
		echo 'success';
	}
	
	
	//q必达推送
	public function push7(){
		$file_get_contents = file_get_contents("php://input");
		$input = @json_decode($file_get_contents,true);
		$data['deliveryId'] = $input['waybillNo'];
		$data['orderNo'] = $input['orderNo'];
		$data['deliveryType'] =1;
		$data['pushType'] = $input['pushType'];
		$data['type'] = 7;
		$data['context'] = json_encode($input['data'],320);
		$data['status'] = 1;
		$data['create_time'] = time();
		$id = 0;
		if($data['deliveryId']){
			$id =Db::name('express_order_push')->insert($data);
		}
		return 'SUCCESS';
	}

    public function push9(){
        $file_get_contents = file_get_contents("php://input");
        $input = @json_decode($file_get_contents,true);
        $data['deliveryId'] = $input['waybill'];
        $data['orderNo'] = $input['shopbill'];
        $data['deliveryType'] = $input['billType'];
        $data['pushType'] = 1;
        $data['type'] = 9;
        $data['context'] = $file_get_contents;
        $data['status'] = 1;
        $data['create_time'] = time();
        $id = 0;
        if($data['deliveryId'] || $data['orderNo']){
            $id =Db::name('express_order_push')->insert($data);
        }
        return json(array('message'=>'推送成功！','code'=>1));
    }
	
	
	//众发物流回调（订单/物流状态、轨迹等统一入口）
	public function push8(){
		$this->zfDebugLog('push8', '=== 进入 push8 === method='.(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '').' uri='.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '').' ip='.(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''));
		$raw = file_get_contents("php://input");
		$this->zfDebugLog('push8', 'raw_len='.strlen((string)$raw).' body='.substr((string)$raw, 0, 1000));
		$input = @json_decode($raw, true);
		$payload = is_array($input) ? $input : array();
		$eventData = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : array();
		if(empty($eventData) && isset($payload['status'])){
			$eventData = $payload;
		}

		$data = array();
		// deliveryId 优先运单号 waybillNo；众发 orderNo(ORD…) 为平台商户单号，不是运单号
		$data['deliveryId'] = '';
		if(isset($eventData['waybillNo']) && $eventData['waybillNo'] !== ''){
			$data['deliveryId'] = (string)$eventData['waybillNo'];
		}elseif(isset($payload['waybillNo']) && $payload['waybillNo'] !== ''){
			$data['deliveryId'] = (string)$payload['waybillNo'];
		}elseif(isset($eventData['orderNo']) && $eventData['orderNo'] !== ''){
			$data['deliveryId'] = (string)$eventData['orderNo'];
		}elseif(isset($payload['orderNo']) && $payload['orderNo'] !== ''){
			$data['deliveryId'] = (string)$payload['orderNo'];
		}
		// orderNo 存 outOrderNo（本地订单号），供 handlePushOrder 按 expressNo/orderNo/id 匹配
		$data['orderNo'] = '';
		if(isset($eventData['outOrderNo']) && $eventData['outOrderNo'] !== ''){
			$data['orderNo'] = (string)$eventData['outOrderNo'];
		}elseif(isset($payload['outOrderNo']) && $payload['outOrderNo'] !== ''){
			$data['orderNo'] = (string)$payload['outOrderNo'];
		}
		$data['deliveryType'] = isset($eventData['status']) ? (string)$eventData['status'] : (isset($payload['status']) ? (string)$payload['status'] : '');
		$data['pushType'] = isset($payload['eventType']) ? (string)$payload['eventType'] : 'ORDER_STATUS_CHANGE';
		$data['type'] = 8;
		$data['context'] = $raw ? $raw : json_encode($payload, JSON_UNESCAPED_UNICODE);
		$data['status'] = 1;
		$data['create_time'] = time();

		$pushId = 0;
		if(trim((string)$raw) !== ''){
			$pushId = Db::name('express_order_push')->insertGetId($data);
			$this->zfDebugLog('push8', '入库 push_id='.$pushId.' deliveryId='.$data['deliveryId'].' orderNo(outOrderNo)='.$data['orderNo'].' status='.$data['deliveryType'].' eventType='.$data['pushType']);
			if($pushId){
				$this->handlePushOrder($pushId, 0, 0);
				$this->zfDebugLog('push8', '已同步执行 handlePushOrder push_id='.$pushId);
			}
		}else{
			$this->zfDebugLog('push8', '未入库: 请求体为空');
		}
		return json(array('code' => 0, 'message' => 'success'));
	}
	//执行订单推送
	public function handlePushOrder($id=0,$user_id=0,$bug=0){
		if($id && $bug){
			$list = Db::name('express_order_push')->where(array('id'=>$id))->order('id asc')->limit(0,50)->select(); 
		}elseif($id){
			$list = Db::name('express_order_push')->where(array('status'=>1,'id'=>$id))->order('id asc')->limit(0,50)->select(); 
		}elseif($user_id){
			$list = Db::name('express_order_push')->where(array('status'=>1,'user_id'=>$user_id,))->order('id asc')->limit(0,50)->select(); 
		}else{
			$list = Db::name('express_order_push')->where(array('status'=>1))->order('id asc')->limit(0,50)->select(); 
		}
		$i=0;
		foreach($list as $k=>$v){
			if($v['deliveryId'] || $v['orderNo']){
				$i++;
				if($v['deliveryId']){
					$eo = Db::name('express_order')->where(array('deliveryId'=>$v['deliveryId']))->find(); 
				}
				if(!$eo){
				   $eo = Db::name('express_order')->where(array('deliveryId'=>$v['orderNo']))->find();  
				   if(!$eo){
    				   $eo = Db::name('express_order')->where(array('expressId'=>$v['orderNo']))->find();  
    				   if(!$eo){
        				   $eo = Db::name('express_order')->where(array('expressNo'=>$v['orderNo']))->find();  
        				   if(!$eo){
            				   $eo = Db::name('express_order')->where(array('orderNo'=>$v['orderNo']))->find();  
            				}
        				}
    				}
				}
				$c = $this->handlePushOrderUpdate($v,$eo);
			}
		}
		$msg = '一共更新订单【'.$i.'】次';
		return $msg;
	}
	
	
	
	
	
	//京东订单订阅
	public function jdtraceSubscribe($id=0,$user_id=0,$bug=0){
		$config = $this->config;
		$t=time()+60;
		$t1 = strtotime(TODAY);
		$list = Db::name('express_order')->where(array('create_time'=>array(array('ELT',$t),array('EGT',$t1)),'type'=>3,'subscribe'=>0,'orderStatus'=>array('in',array(1,2))))->order('id desc')->limit(0,50)->select(); 
		$i=0;
		foreach($list as $k=>$v){
			$subscribe = model('JdApi')->subscribe($v);//京东订阅
			if($subscribe != false){
				$i++;
			}
		}
		$msg = '订阅更新订单【'.$i.'】次';
		return $msg;
	}
	
	
	
 	public function handlePush(){
		$id = (int)input('id','','trim,htmlspecialchars');	
		$user_id = (int)input('user_id','','trim,htmlspecialchars');
		$bug = (int)input('bug','','trim,htmlspecialchars');	
		$msg .=$this->handlePushOrder($id,$user_id,$bug);
		$msg .=$this->handleOrderDiff();
		$msg .=$this->jdtraceSubscribe($id,$user_id,$bug);
		return json(array('c'=>0,'d'=>$d,'c'=>$c,'msg'=>$msg));
	}

	
	public function handleOrderDiff(){
		$config = model('Setting')->fetchAll2();
		$diff_money_day = (int)$config['wxapp']['diff_money_day'];
		$diff_money_day_mobile_1 = (int)$config['wxapp']['diff_money_day_mobile_1'];
		$diff_money_day_mobile_2 = (int)$config['wxapp']['diff_money_day_mobile_2'];
		$diff_money_day_mobile_3 = (int)$config['wxapp']['diff_money_day_mobile_3'];
		
		if(!$diff_money_day){
			$msg = '未开启此功能';
			return $msg;
		}
		$str = '-'.$diff_money_day.' day';
        $str2 = strtotime(date('Y-m-d', strtotime($str)));
		$current_hour = $this->timeFanWei("08:00:00");
        if($current_hour==1){
           $list = Db::name('express_order')->where(array('diff_time'=>array('ELT',$str2),'diffStatus'=>1))->order(array('id'=>'asc'))->limit(0,5)->select();
        }else{
           $list = Db::name('express_order')->where(array('diff_time'=>array('ELT',$str2),'diffStatus'=>1))->order(array('id'=>'asc'))->limit(0,5)->select();
        }
		$i=$i1=$i2=$i3=0;
		foreach($list as $k=>$v){
			$sendMobile = $v['sendMobile'];
			if($sendMobile && $diff_money_day_mobile_1){
				$i++;
				$ucData['name'] = $v['sendName'];
				$ucData['type'] = 2;
				$ucData['phone'] = $sendMobile;
				$ucData['remark'] = '订单号'.$v['id'].'-下单未补差价【'.round($v['diffMoneyYuan']/100,2).'】拉黑【超时'.$diff_money_day.'天】拉黑寄件手机';
				$ucData['createTime'] = time();
				$ucData['create_time'] = time();
				$uc = Db::name('user_closed')->where(array('phone'=>$sendMobile))->find();
				if($uc){
					$ucData['id'] = $uc['id'];
					Db::name('user_closed')->update($ucData);
					$msg .= '更新寄件手机拉黑';
				}else{
					Db::name('user_closed')->insert($ucData);
					$msg .= '添加寄件手机拉黑';
				}
			}
			$receiveMobile = $v['receiveMobile'];
			if($receiveMobile && $diff_money_day_mobile_2){
				$i1++;
				$ucData['name'] = $v['receiveName'];
				$ucData['type'] = 2;
				$ucData['phone'] = $receiveMobile;
				$ucData['remark'] = '订单号'.$v['id'].'-下单未补差价【'.round($v['diffMoneyYuan']/100,2).'】拉黑【超时'.$diff_money_day.'天】拉黑收件手机';
				$ucData['createTime'] = time();
				$ucData['create_time'] = time();
				$uc2 = Db::name('user_closed')->where(array('phone'=>$receiveMobile))->find();
				if($uc2){
					$ucData['id'] = $uc2['id'];
					Db::name('user_closed')->update($ucData);
					$msg .= '更新收件手机拉黑';
				}else{
					Db::name('user_closed')->insert($ucData);
					$msg .= '添加收件手机拉黑';
				}
			}
			if($v['pid']){
				$parent = Db::name('users')->where(array('user_id'=>$v['pid']))->find();	
				if($parent['moneys'] >=$v['diffMoneyYuan']){
					$i2++;
					$res = model('Users')->addMoneys($parent['user_id'],-$v['diffMoneyYuan'],'下级未付差价订单抵扣金',2,$v['id'],$parent['user_id']);//只扣除一次
					if($res){
						$msg .= $v['id'].'扣除抵扣金';
					}
				}
			}
			$mobile = trim($config['site']['config_mobile']);
			if($mobile && $diff_money_day_mobile_3 && $v['diffMoneyYuan'] >= 500){
				$sb = (int)Db::name('sms_bao')->where(array('order_id'=>$v['id'],'mobile'=>$mobile))->count();
				if($sb==0){
					$account=trim($config['sms']['sms_bao_account']);
					$content = '订单号'.$v['id'].'-用户未补差价'.round($v['diffMoneyYuan']/100,2).'元-已超时'.$diff_money_day.'天';
					$password = md5(trim($config['sms']['sms_bao_password'])); //短信平台密码
					$config['sms']['url']='http://api.smsbao.com/sms?u='.$account.'&p='.$password.'&m='.$mobile.'&c='.$content;
					$http = tmplToStr($config['sms']['url'], $local);
					$res = file_get_contents($http);
					$sbData['shop_id'] = 1;
					$sbData['order_id'] = $v['id'];
					$sbData['mobile'] =$mobile;
					$sbData['status'] = $res;
					$sbData['content'] = $content;
					$sbData['create_time'] = time();
					Db::name('sms_bao')->insert($sbData);
					$msg .='短信通知平台管理员-短信发送状态【'.$res.'】';
				}
			}
		}
		return $msg;
	}
					
					
	
	//分类筛选
	public function getPushData(){
	    $t = model('Setting')->getCompanyApiTypes();
        $push = Db::name('express_order_push')->where(array('status'=>2))->limit(0,10)->order('id desc')->select();
        $str = '';
		$i=0;
        foreach($push as $k=>$v){
			$i++;
			$u = $config['site']['host']."/admin/express/index/order_id/".$v['deliveryId'];
            $str.='<div class="n-'.$i.' n">'.$i.':'.$t[$v['type']].'接口运单号'.$v['deliveryId'].'已执行操作'.$v['handle_info'].'-执行时间：'.date('Y-m-d H:i:s ',$v['handle_time']).'<a href="'.$u.'">[查看]</a></div>'."\n\r";      
        }
        echo $str;die;
    }



	
	//内容页面新增快捷导航
	public function getFastNavigationRule(){
		return jsonp(array('state'=>'101','info'=>'功能未开发'));
	}



	//H5绑定小程序openid
	public function Bind(){
		  $js_code = input('js_code','','trim,htmlspecialchars');
		  $uid = input('uid','','trim,htmlspecialchars');
		  $grant_type = input('grant_type','','trim,htmlspecialchars');


		  $url="https://api.weixin.qq.com/sns/jscode2session?appid=".$this->config['wxapp']['appid']."&secret=".$this->config['wxapp']['appsecret']."&js_code=".$js_code."&grant_type=authorization_code";
		  $res = $this->httpRequest($url);
		  $result = json_decode($res,true);

		  if(!$uid){
			  return json(array('status'=>1,'msg'=>'无会员ID绑定失败'));
		  }
		  $users = Db::name('users')->where(array('user_id'=>$uid))->find();

		  if(empty($result['openid'])){
			 return json(array('status'=>1,'msg'=>'openid获取失败'));
		  }
		  //如果有unionid这里的开放平台可能不正确
		  if($this->config['weixin']['unionid'] && $result['unionid']){
			 $connect = Db::name('connect')->where(array('type'=>'weixin','unionid'=>$result['unionid']))->order(array('connect_id'=>'asc'))->find();
		  }else{
			 $connect = Db::name('connect')->where(array('type'=>'weixin','openid'=>$result['openid']))->order(array('connect_id'=>'asc'))->find();
		  }

		  if($connect){
			 if($res2 = Db::name('connect')->where(array('connect_id'=>$connect['connect_id']))->update(array('openid'=>$result['openid']))){
				return json(array('status'=>0,'msg'=>'绑定成功'));
			}else{
				return json(array('status'=>0,'msg'=>'绑定失败'));
			}
		 }else{
			$data['uid']  = $uid;
			$data['type']  = 'weixin';
			$data['openid']  = $result['openid'];
			$data['nickname']  = 'wxapp_bind_'.$uid;
			$data['headimgurl']  = $users['face'];
			if($res3 = Db::name('connect')->insert($data)){
				return json(array('status'=>0,'msg'=>'绑定成并注册成功'));
			}else{
				return json(array('status'=>0,'msg'=>'绑定失败2'));
			}
		 }
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


	//商城分类
	public function shopcate($parent_id=0){
        $datas = model('ShopCate')->fetchAll();
        $str = '';
        foreach($datas as $var){
            if($var['parent_id'] == 0 && $var['cate_id'] == $parent_id){
                foreach($datas as $var2){
                    if($var2['parent_id'] == $var['cate_id']){
                        $str.='<option value="'.$var2['cate_id'].'">'.$var2['cate_name'].'</option>'."\n\r";
                    }
                }
            }
        }
        echo $str;die;
    }


	//getInonfont
    public function getInonfont($url = '',$qq = '0'){
	    $config = Setting::config();
		if(!($url = input('url',0,'htmlspecialchars'))){
			return json(array('code'=>0,'msg'=>'非法操作'));
        }
		if(!($qq = input('qq',0,'htmlspecialchars'))){
			return json(array('code'=>0,'msg'=>'非法操作'));
        }
		if($config['config']['iconfont']){
			return json(array('code'=>1,'msg'=>'请复制这段连接：'.$config['config']['iconfont']));
		}else{
			return json(array('code'=>0,'msg'=>'站点没配置'));
		}
	}


	
	

	//获取验注册证码
	public function verify_register(){
		return $this->verify_build('register');
    }
	//获取登录验证码
	public function verify_login(){
		return $this->verify_build('login');
    }

	//获取短信API
	public function sendsms(){
		session('scode', null);
		if(!($verify = input('verify',0,'htmlspecialchars'))) {
			return json(array('code'=>0,'msg'=>'请输入正确的图片验证码'));
        }
		if(!$this->get_verify_check($verify,'register')){
			return json(array('code'=>0,'msg'=>'图片验证码错误'));
		}


		if(!($mobile = trim(input('mobile',0,'htmlspecialchars')))) {
			return json(array('code'=>0,'msg'=>'请输入正确的手机号码'));
        }
        if(!isMobile($mobile)){
			return json(array('code'=>0,'msg'=>'手机号码格式不正确'));
        }
        if($user = model('Users')->getUserByMobile($mobile)){
			return json(array('code'=>0,'msg'=>'手机号码已经存在'));
        }


		session('mobile', $mobile);
		$randstring = session('scode');
		if(!empty($randstring)){
			session('scode',null);
		}
        $randstring = rand_string(4,1);
        session('scode', $randstring);


		if(model('Sms')->sms_yzm($mobile, $randstring)){
			return json(array('code'=>1,'msg'=>'短信发送成功'));
		}else{
			return json(array('code'=>0,'msg'=>'短信发送失败'));
		}
	}

	//获取短信API2
	public function sendsms2(){
		session('scode', null);
		if(!($mobile = trim(input('mobile',0,'htmlspecialchars')))){
			return json(array('code'=>0,'msg'=>'请输入正确的手机号码'));
        }
        if(!isMobile($mobile)){
			return json(array('code'=>0,'msg'=>'手机号码格式不正确'));
        }
        if($user = model('Users')->getUserByMobile($mobile)){
			return json(array('code'=>0,'msg'=>'手机号码已经存在'));
        }
		session('mobile', $mobile);
		$randstring = session('scode');
		if(!empty($randstring)){
			session('scode',null);
		}
        $randstring = rand_string(4,1);
        session('scode', $randstring);

		if(model('Sms')->sms_yzm($mobile, $randstring)){
			return json(array('code'=>1,'msg'=>'恭喜短信发送成功'));
		}else{
			return json(array('code'=>0,'msg'=>'抱歉短信发送失败'));
		}
	}

	//获取短信API3
	public function sendsms3(){
		session('scode', null);
		if(!($mobile = trim(input('mobile',0,'htmlspecialchars')))){
			return json(array('code'=>0,'msg'=>'请输入正确的手机号码'));
        }
        if(!isMobile($mobile)){
			return json(array('code'=>0,'msg'=>'手机号码格式不正确'));
        }
		session('mobile', $mobile);
		$randstring = session('scode');
		if(!empty($randstring)){
			session('scode',null);
		}
        $randstring = rand_string(4,1);
        session('scode', $randstring);
		if(model('Sms')->sms_yzm($mobile, $randstring)){
			return json(array('code'=>1,'msg'=>'恭喜短信发送成功'));
		}else{
			return json(array('code'=>0,'msg'=>'抱歉短信发送失败'));
		}
	}


	//获取短信API4后台登录
	public function adminLoginSms(){
		session('scode', null);
		if(!($username = trim(input('username',0,'htmlspecialchars')))){
			return json(array('code'=>0,'msg'=>'请输入管理员账户'));
        }
		if(!$detail = Db::name('admin')->where(array('username'=>$username))->find()){
			return json(array('code'=>0,'msg'=>'非法操作'));
        }
		if(empty($detail['mobile'])){
			return json(array('code'=>0,'msg'=>'该管理员未绑定手机号'));
        }
		if($detail['is_username_lock'] == 1){
			return json(array('code'=>0,'msg'=>'该管理员已经被冻结'));
        }
        if(!isMobile($detail['mobile'])){
			return json(array('code'=>0,'msg'=>'管理员手机号码格式不正确'));
        }
		session('mobile',$detail['mobile']);
		$randstring = session('scode');
		if(!empty($randstring)){
			session('scode',null);
		}
        $randstring = rand_string(4,1);
        session('scode', $randstring);
		if(model('Sms')->sms_yzm($detail['mobile'],$randstring)){
			return json(array('code'=>1,'msg'=>'短信发送成功'));
		}else{
			return json(array('code'=>0,'msg'=>'短信发送失败'));
		}
	}




	
	

	//上传微信视频
	public function fileVodie(){

		$config = Setting::config();

		$key = input('key', '','htmlspecialchars');
		$token = input('token', '','htmlspecialchars');
		$name = input('name', '','htmlspecialchars');


		$vname = $_FILES['file']['type'];
		//获取文件的名字
		$key = $_FILES['file']['name'];
		$filePath=$_FILES['file']['tmp_name'];

		//获取token值
		$upinfo = Db::name('uploadset')->where(array('type'=>'Qiniu'))->find();
		$conf = json_decode($upinfo['para'],true);
		$bucket = $conf['bucket'];
		$domain= $conf['domain'];
		//初始化签权对象
		$auth = new Auth($conf['accessKey'],$conf['secrectKey']);

		//生成上传Token
		$token = $auth->uploadToken($bucket);
		$uploadMgr = new UploadManager();
		//调用 UploadManager 的 putFile 方法进行文件的上传。
		list($ret,$err) = $uploadMgr->putFile($token, $key, $filePath);
		if($err !== null){
			return json(array('code'=>1,'message'=>'上传失败'.$err));
        }

		//获取视频的时长
		//第一步先获取到到的是关于视频所有信息的json字符串
		$shichang = file_get_contents('http://'.$domain.'/'.$key.'?avinfo');
		// 第二部转化为对象
		$shi =json_decode($shichang);

		// 第三部从中取出视频的时长
		$chang = $shi->format->duration;
		//获取封面
		$vpic = 'http://'.$domain.'/'.$key.'?vframe/jpg/offset/1';
		$path ='http://'.$domain.'/'.$ret['key'];


		$data['code'] = 0;
		$data['upType'] = 5;
		$data['name'] = $vname;
		$data['type'] = 'video/mp4';
		$data['size'] = $shi->format->size;
		$data['duration'] = $chang;
		$data['key'] = 'file';
		$data['width'] = $shi->streams[0]->width;
		$data['height'] = $shi->streams[0]->height;
		$data['extension'] = 'mp4';
		$data['savepath'] = $path;
		$data['savename'] = $vname;

		$data['cover']=$vpic;
		$data['path'] = $path;
		$data['url'] = $path;
		$data['preview'] = $path;
		$data['id'] = Db::name('thread_post_pic')->insertGetId($data);

		return json($data);
	}



	//生成二维码
    public function qrcode(){
        $data = input('data','','trim,htmlspecialchars');
		$token = 'share_qrcode_' .rand_string(6,0);
		$file = ToQrCode($token,$data,8,'');
		$file = config_weixin_img($file);
		$file = $file;
		header('Content-type:image/png');
		echo file_get_contents($file);
    }



	
	public function family($user_id){
		$user_id = (int) input('user_id');
        if(!$user_id){
            $user_id = '1';
        }
		$data = $this->getChildFamily($user_id,1);
		return json($data);
	}
	
	
	public function getChildFamily($user_id){
		static $arr=array();  
		$data=Db::name('users')->where(array('parent_id'=>$user_id))->field('user_id,parent_id,nickname,mobile')->select();
		foreach($data as $key => $value){
			$data[$key]['id'] = $value['user_id'];
			$data[$key]['name'] = $value['nickname'];
			$arr[] = $data[$key];
			$this->getChildFamily($value['user_id']);//循环
		}
		return $arr;
	}
	
	
	public function getChildDetail($user_id){
		
		$v = Db::name('users')->where(array('user_id'=>$user_id))->find();
		
		$data['nickname'] = $v['nickname'];
		$data['id'] = $v['user_id'];
		$data['user_id'] = $v['user_id'];
		$data['parent_id'] = $v['parent_id'];
		$data['mobile'] = $v['mobile'];
		$rank_name = Db::name('user_rank')->where('rank_id',$v['rank_id'])->value('rank_name');
		$data['rank_name'] = $rank_name ? $rank_name : '无等级';
		$data['yao_name'] = Db::name('users')->where('user_id',$v['parent_id'])->value('nickname');
		$data['yao_num'] = (int)model('UserProfitLogs')->getUserFuidRankCount($v['user_id'],0);
		$getUserFuidCount = model('UserProfitLogs')->getUserFuidCount($v['user_id']);
		$data['tuan_num'] = $getUserFuidCount;
		$getUserLevelPriceCount = model('UserProfitLogs')->getUserLevelPriceCount($v['user_id'],1);
		$data['user_price'] = $getUserLevelPriceCount['price2'];
		$data['tuan_price'] = $getUserLevelPriceCount['price3'];
		$data['time'] = date("Y-m-d H:i:s",$v['reg_time']);
		return $data;
	}
	
	
	//更新订单状态
	public function handlePushOrderUpdate($eop,$eo){
		$this->zfDebugLog('handlePushOrderUpdate', '进入 push_id='.(isset($eop['id']) ? $eop['id'] : '').' type='.(isset($eop['type']) ? $eop['type'] : '').' status='.(isset($eop['status']) ? $eop['status'] : '').' deliveryId='.(isset($eop['deliveryId']) ? $eop['deliveryId'] : '').' orderNo='.(isset($eop['orderNo']) ? $eop['orderNo'] : '').' 匹配订单='.($eo ? 'id='.$eo['id'].' orderStatus='.$eo['orderStatus'].' type='.$eo['type'] : '未找到'));
		$config = model('Setting')->fetchAll2();
		$plan_order_cancel = (int)$config['config']['plan_order_cancel'];//自动退款
		$v = $eo;
		$context = @json_decode($eop['context'],true);
		if($context === null && isset($eop['context']) && $eop['context'] !== ''){
			$this->zfDebugLog('handlePushOrderUpdate', 'context JSON解析失败 push_id='.(isset($eop['id']) ? $eop['id'] : '').' err='.json_last_error_msg());
		}
		
		//云洋推送回调
		if($eop['type'] == 2){
		  $transferWeight = $context["transferWeight"];//分拣称重
		  $freightInsured = $context["freightInsured"];//保价费
		  $comments = $context["comments"];//快递员信息
		  $parseWeight = $context["parseWeight"];//体积换算重量
		  $totalPrice = $context["totalPrice"];//原价
		  $calWeight = $context["calWeight"];//计费重量（最终的扣费重量）
		  $billType = $context["billType"];//快递类型
		  $freight = $context["freight"];//运费（当feeOver等于0时为预付冻结 等于1时则是最终的扣款 不在发生变化）
		  
		  $weight = $context["weight"];//下单实际重量
		  $realWeight = $context["realWeight"];//站点称重（京东、申通将返回此重量 其他快递不返回
		 
		  $type = $context["type"];//运单状态
		  $billOrderId = $context["billOrderId"];
		  $changeBillFreight = $context["changeBillFreight"];//逆向费
		  $linkName = $context ["linkName"];//下单账户
		  $volume =  $context["volume"];//体积 （京东、德邦、申通 将返回体积 其他不返回）
		  $feeOver = $context["feeOver"];//订单扣费状态（1:已扣费 0:冻结）
		  $changeBill =  $context["changeBill"];//换单号
		  $freightHaocai =  $context["freightHaocai"];//增值费用
		
		  if($v['kuaidi'] == '德邦'){
			   $shopbill = $context["waybill"];//德邦商家单号
		  	   $waybill =  $context["shopbill"];//德邦运单号
		  }else{
			  $shopbill = $context["shopbill"];//商家单号
		 	  $waybill =  $context["waybill"];///运单号 
		  }
		  
		  $calWeight = $context["calWeight"] ? $context["calWeight"] : $context["weight"];//下单实际重量
		  //重构返回的重量
		  $calWeight = @ceil($calWeight);//进一步定位法
		  $calWeight = (int)$calWeight;

		  
		  //云洋备用
		  $handle_info = '云洋【'.$type.'】';
		 
			
		    $falg =0;
			if($v['orderStatus'] == 1 && $type==''){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 2 && $type==''){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 1 && $type=='分配网点'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 2 && $type=='分配网点'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 1 && $type=='待揽收'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 2 && $type=='待揽收'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 1 && $type=='接单'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 2 && $type=='接单'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 1 && $type=='分单'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 2 && $type=='分单'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 1 && $type=='已接单'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 1 && $type=='已开单'){
				$falg =1;
				$orderStatus =1;
			}elseif($v['orderStatus'] == 1 && $type=='揽收任务分配'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 2 && $type=='揽收任务分配'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 2 && $type=='接货中'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 1 && $type=='接货中'){
				$falg =1;
				$orderStatus =1;
				$fg =0;
			}elseif($v['orderStatus'] == 2 && $type=='分拣中心发货'){
				$falg =1;
				$orderStatus =1;
				$fg =1;
			}elseif($v['orderStatus'] == 1 && $type=='分拣中心发货'){
				$falg =1;
				$orderStatus =1;
				$fg =1;
			}elseif($v['orderStatus'] == 2 && $type=='配送员完成揽收'){
				$falg =1;
				$orderStatus =2;
				$fg =1;
			}elseif($v['orderStatus'] == 1 && $type=='配送员完成揽收'){
				$falg =1;
				$orderStatus =2;
				$fg =1;
			}elseif($v['orderStatus'] == 2 && $type=='已接单'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 1 && $type=='在途中'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 2 && $type=='在途中'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 1 && $type=='运输中'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 2 && $type=='运输中'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 3 && $type=='运输中'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 1 && $type=='已揽件'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 1 && $type=='已正常收件状态'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 2 && $type=='已正常收件状态'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 1 && $type=='揽收成功'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 2 && $type=='揽收成功'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 3 && $type=='揽收成功'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 1 && $type=='正常揽件'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}elseif($v['orderStatus'] == 2 && $type=='正常揽件'){
				$falg =1;
				$orderStatus =3;
				$fg =1;
			}
			
		  $realOrderData = model('ExpressOrder')->realOrderData($comments,$v['kuaidi'],$context);//获取取件码配送信息
		  //云洋补差价
		  $totalPrice = $totalPrice*100;
		  $freight = $freight*100;
		  $freightInsured = $freightInsured*100;
		  $freightHaocai = $freightHaocai*100;
		  $orderFee =  $freight;//运费总价 
		  $orderFee = (int)$orderFee;
		  
		  
		  $orderFees = $freightHaocai+$freightInsured;//保价费+耗材费
		  
		  
		    if($falg==1 && $waybill){

			 	$up['totalNumber'] = $packageCount;
				$up['totalVolume'] = $parseWeight;
				if($fg==1){
					$up['review_weight'] = $calWeight;//实际重量
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
				$up['TotalFee']=$orderFee;
				
				
				$handle_info .= '云洋更新订单状态【'.$type.'】';		
				Db::name('express_order')->where(array('id'=>$v['id']))->update($up); 	
				model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '接单成功提醒',$realOrderData['realOrderName'],$realOrderData['realOrderMobile']);//云洋更新状态
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
			if($v['diffStatus'] == 0 && (int)$v['wight'] < $calWeight && $feeOver==1){
				$bu=1; 
			}
			if($v['diffStatus'] == 0 && (int)$v['wight'] < $calWeight && $feeOver==0){
				$bu=1; 
			}
			if($v['diffStatus'] == 1 && (int)$v['wight'] < $calWeight && $feeOver==1){
				$bu=1; 
			}
			if($v['diffStatus'] == 1 && (int)$v['wight'] < $calWeight && $feeOver==0){
				$bu=1; 
			}
			
			if($bu==2){
				$v['diffMoneyYuan'] = $orderFee-$v['sumMoneyYuan_old'];//云洋实际扣费之间的差价
				$v['diffMoneyYuan'] = (int)$v['diffMoneyYuan']+$orderFees;
				if($v['diffMoneyYuan']){
					$handle_info .= '云洋补差价耗材费【'.round($freightHaocai/100,2).'】+保价费'.round($freightInsured/100,2);	
					$updataData['diffStatus']=1;
					$updataData['review_weight']=$calWeight;
					$updataData['diffMoneyYuan']=$v['diffMoneyYuan'];
					$updataData['insurancePrice']=$freightInsured;
					$updataData['packageServicePrice']=$freightHaocai;
					$updataData['TotalFee']=$orderFee;
					Db::name('express_order')->where(array('id'=>$v['id']))->update($updataData);//更新订单
					model('Sms')->sendSmsTmplSend($v,$v['user_id'],$title = '补差价通知');//云洋判断补差价
					model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '补差价通知');
				}
		    }
		
			
			if($bu==1){
				
				//云洋云端请求数据
				$logoUrl = model('ExpressOrder')->logoUrl($v['kuaidi']);//获取快递公司信息
				$getZhe = model('Setting')->getZhe($v['user_id'],$logoUrl);
                $data['getZhe'] = $getZhe;
				$cate['ratio'] = $logoUrl['ratio'];
				$cate['priceA_type'] = $logoUrl['priceA_type'];
				$cate['priceA_ratio'] = $logoUrl['priceA_ratio'];
				$cate['priceA_price'] = $logoUrl['priceA_price'];
				$cate['priceB_type'] = $logoUrl['priceB_type'];
				$cate['priceB_ratio'] = $logoUrl['priceB_ratio'];
				$cate['priceB_price'] = $logoUrl['priceB_price'];
				$data['logoUrl'] = $cate;
				$o['firstPrice'] = $v['firstPrice'];
				$o['addPrice'] = $v['addPrice'];
				$o['wight'] = $v['wight'];
				$o['sumMoneyYuan_old'] = $v['sumMoneyYuan_old'];
				$data['v'] = $o;
						
				$this->curl = new \Curl();
				$data['type'] = 2;
				$data['user_id'] = $v['user_id'];
				$data['calWeight'] = $calWeight;
				$data['wight'] = $v['wight'];
				$data['TotalFee'] = $orderFee;
				$data['packageServicePrice'] = $freightHaocai;
				$data['insurancePrice'] = $freightInsured;
				$data['host'] = trim($config['site']['host']);
				$data['mobile'] = trim($config['site']['mobile']);
				$url = getHost().'/api/RequestApi/getDiffMoney';
				$result = $this->curl->post($url,json_encode($data));
				$result = json_decode($result,true);
				$diffMoneyYuan = $result['data'];
				
				if($diffMoneyYuan){
					$v['diffMoneyYuan'] = $diffMoneyYuan;
					$cha_weight = $calWeight-$v['wight'];//超重KG
					$handle_info .= '实际重量'.$context["calWeight"].'KG-计费重量'.$calWeight.'KG-补差价实际收费【'.round($orderFee/100,2).'】补差价'.round($diffMoneyYuan/100,2);
					$updataData['diffStatus']=1;
					$updataData['review_weight']=$calWeight;
					$updataData['diffMoneyYuan']=$diffMoneyYuan;
					$updataData['insurancePrice']=$freightInsured;
					$updataData['packageServicePrice']=$freightHaocai;
					$updataData['TotalFee']=$orderFee;
				
					
					Db::name('express_order')->where(array('id'=>$v['id']))->update($updataData);//更新订单
					
					model('Sms')->sendSmsTmplSend($v,$v['user_id'],$title = '补差价通知');//云洋判断补差价
					model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '补差价通知');
				}
			  }
		   }
		   
		    //取消订单
			if($type=='下单取消' && $v['orderStatus'] != '-1'  && $v['orderStatus'] != '5' && $v['orderRightsStatus'] == 0){
				$handle_info = '云洋取消订单';
				if($plan_order_cancel){
					model('ExpressOrder')->cancel($v,$v['id'],$reason='接口方取消订单',$cancel_money=0,$checkOrderStatus=1);//云洋接口方取消订单
				}else{
					Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>-1,'orderRightsStatus'=>1)); //云洋已取消待后台退款
				}	
			}
			//已取消
			if($type=='已取消' && $v['orderStatus'] != '-1'  && $v['orderStatus'] != '5' && $v['orderRightsStatus'] == 0){
				$handle_info = '云洋取消订单';
				if($plan_order_cancel){
					model('ExpressOrder')->cancel($v,$v['id'],$reason='接口方取消订单',$cancel_money=0,$checkOrderStatus=1);//云洋接口方取消订单
				}else{
					Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>-1,'orderRightsStatus'=>1)); //云洋已取消待后台退款
				}
			}
			
			$complete = 0;
			if($type=='已签收' && $v['orderStatus'] == 3){
				$complete = 1;
			}
			if($type=='已签收' && $v['orderStatus'] == 2){
				$complete = 1;
			}
			if($type=='签收' && $v['orderStatus'] == 3){
				$complete = 1;
			}
			if($type=='签收' && $v['orderStatus'] == 2){
				$complete = 1;
			}
			if($type=='正常签收' && $v['orderStatus'] == 3){
				$complete = 1;
			}
			if($type=='正常签收' && $v['orderStatus'] == 2){
				$complete = 1;
			}
			if($type=='已结算' && $v['orderStatus'] == 3){
				$complete = 1;
			}
			if($type=='已结算' && $v['orderStatus'] == 2){
				$complete = 1;
			}
			if($type=='已签收' && $v['orderStatus'] == 3){
				$complete = 1;
			}
			if($type=='已签收' && $v['orderStatus'] == 2){
				$complete = 1;
			}
			if($complete){
				$handle_info = '云洋订单已完成';
				Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>4,'orderStatusName'=>$type,'TotalFee'=>$orderFee));
				model('ExpressOrder')->completeProfit($v,$v['user_id'],'分销');//执行完成分销
				model('ExpressOrder')->orderAddIntegral($v,$v['user_id'],'给用户奖励积分');//赠送优惠券
				model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '签收成功通知');
			}
			
			$eopUp['status'] = 2;
    		$eopUp['handle_time'] = time();
    		$eopUp['handle_info'] = $handle_info;
    		Db::name('express_order_push')->where(array('id'=>$eop['id']))->update($eopUp); 
		}
		
		
		
		
		//易达回调
		if($eop['type'] == 1){
			if($v['kuaidi'] == '中通'){
				$deliveryId = $eop['deliveryId'] ? $eop['deliveryId'] : $eop['orderNo'];
			}else{
				$deliveryId = $eop['deliveryId'];
			}
			
			$handle_info = '易达【'.$eop['pushType'].'】';
			if($eop['pushType'] == 1){
				$ydOrderStatus = $context["ydOrderStatus"];//订单状态
				$orderStatus = $ydOrderStatus;
		  		$ydOrderStatusDesc = $context["ydOrderStatusDesc"];//订单状态描述
				$u =0;
				if($v['orderStatus'] == 1 && $orderStatus==1){
					$u =1;
					$os = 2;
				}elseif($v['orderStatus'] == 1 && $orderStatus==2){
					$u =1;
					$os = 3;
				}elseif($v['orderStatus'] == 2 && $orderStatus==1){
					$u =1;
					$os = 2;
				}elseif($v['orderStatus'] == 2 && $orderStatus==2){
					$u =1;
					$os = 3;
				}
				if($u==1){
					$up['totalNumber'] = $packageCount;
					$up['totalVolume'] = $volume;
					$up['review_vloumn'] = $realVolume;
					$up['orderStatus'] = $os;
					$up['orderStatusName'] = $ydOrderStatusDesc;
					if($deliveryId){
						$up['deliveryId'] = $deliveryId;
					}
					$handle_info = '易达推送订单状态【'.$ydOrderStatusDesc.'】';
					Db::name('express_order')->where(array('id'=>$v['id']))->update($up); 	
				}
				if($ydOrderStatus=='10' && $v['orderStatus'] != '-1'  && $v['orderStatus'] != '5' && $v['orderRightsStatus'] == 0){
					$handle_info = '易达取消订单';
					
					if($plan_order_cancel){
						model('ExpressOrder')->cancel($v,$v['id'],$reason='接口方取消订单',$cancel_money=0,$checkOrderStatus=1);//易达接口方取消订单
					}else{
						Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>-1,'orderRightsStatus'=>1)); //已取消待后台退款
					}	
				}
				$q=0;
				if($orderStatus==3 && $v['orderStatus'] == 2){
					$q=1;
				}
				if($orderStatus==3 && $v['orderStatus'] == 3){
					$q=1;
				}
				if($q){
					$handle_info = '易达推送订单状已签收';
					Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>4,'orderStatusName'=>'已签收'));//订单完成
					model('ExpressOrder')->completeProfit($v,$v['user_id'],'分销');//执行完成分销
					model('ExpressOrder')->orderAddIntegral($v,$v['user_id'],'给用户奖励积分');
					model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '签收成功通知');//完成订单发送通知
				}
			}
			
			if($eop['pushType'] == 2){
				$realWeight = $context['calcFeeWeight'] ? $context['calcFeeWeight'] : $context['realWeight'];
		  		$realVolume = $context["realVolume"];//实际体积
			    $realWeight = @ceil($realWeight);//重构返回的重量进一步定位法
			    $realWeight = (int)$realWeight;
				$feeBlockList = $context["feeBlockList"];//费用明细
				$feeBlockList = @json_decode($feeBlockList,true);
				if($feeBlockList){
					foreach($feeBlockList as $ka=>$va){
						if($va['type'] == '0' || $va['name'] == '实收快递费'){
							$r= $va;
						}
					}
					foreach($feeBlockList as $ka=>$va3){
						if($va3['type'] == '4' || strstr($va3['name'],'保价费')==true){
							$r3= $va3;//保价
						}
					}
					foreach($feeBlockList as $ka=>$va1004){
						if($va1004['type'] == '1004' || strstr($va['name'],'包装费用')==true){
							$r1004= $va1004;//包装
						}
					}
					foreach($feeBlockList as $ka=>$va1005){
						if($va1005['type'] == '1005' || strstr($va['name'],'其他费用')==true){
							$r1005= $va1005;
						}
					}
					foreach($feeBlockList as $ka=>$va6001){
						if($va6001['type'] == '6001' || strstr($va['name'],'耗材费')==true){
							$r6001= $va6001;
						}
					}
					$orderFee = $r['fee']*100;//总价
					
					$up['insurancePrice'] = $r3['fee']*100;//保价
					$up['packageServicePrice'] = ($r1004['fee']*100)+($r1005['fee']*100)+($r6001['fee']*100);//耗材
					$up['review_weight'] = $realWeight;
					$up['review_vloumn'] = $realVolume;
					$up['TotalFee'] = $orderFee;
					
					$orderFee = $orderFee+$up['insurancePrice']+$up['packageServicePrice'];
					$orderFee = (int)$orderFee;
					$calWeight = $realWeight;
					
					
					
					if($orderFee > $v['sumMoneyYuan_old'] && $v['diffStatus'] == 0){
						
						//易达云端请求数据
						$logoUrl = model('ExpressOrder')->logoUrl($v['kuaidi']);//获取快递公司信息
						
						$getZhe = model('Setting')->getZhe($v['user_id'],$logoUrl);
                        $data['getZhe'] = $getZhe;
						$cate['ratio'] = $logoUrl['ratio'];
						$cate['priceA_type'] = $logoUrl['priceA_type'];
						$cate['priceA_ratio'] = $logoUrl['priceA_ratio'];
						$cate['priceA_price'] = $logoUrl['priceA_price'];
						$cate['priceB_type'] = $logoUrl['priceB_type'];
						$cate['priceB_ratio'] = $logoUrl['priceB_ratio'];
						$cate['priceB_price'] = $logoUrl['priceB_price'];
						$data['logoUrl'] = $cate;
						$o['firstPrice'] = $v['firstPrice'];
						$o['addPrice'] = $v['addPrice'];
						$o['wight'] = $v['wight'];
						$o['sumMoneyYuan_old'] = $v['sumMoneyYuan_old'];
						$data['v'] = $o;
						
						$this->curl = new \Curl();
						$data['type'] = 1;
						$data['user_id'] = $v['user_id'];
						$data['calWeight'] = $calWeight;
						$data['wight'] = $v['wight'];
						$data['TotalFee'] = $orderFee;
						$data['packageServicePrice'] = $up['packageServicePrice'];
						$data['insurancePrice'] = $up['insurancePrice'];
						$data['host'] = trim($config['site']['host']);
						$data['mobile'] = trim($config['site']['mobile']);
						$url = getHost().'/api/RequestApi/getDiffMoney';
						$result = $this->curl->post($url,json_encode($data));
						$result = json_decode($result,true);
						$diffMoneyYuan = $result['data'];
					
						if($diffMoneyYuan){
							$v['diffMoneyYuan'] = $diffMoneyYuan;
							$handle_info = '易达实际收费【'.round($orderFee/100,2).'】补差价'.round($diffMoneyYuan/100,2);
							$up['diffStatus'] = 1;
							$up['diffMoneyYuan'] = $diffMoneyYuan;
							$up['review_weight'] = $realWeight;
							$up['TotalFee'] = $orderFee;
							Db::name('express_order')->where(array('id'=>$v['id']))->update($up);//易达更新订单
							model('Sms')->sendSmsTmplSend($v,$v['user_id'],$title = '补差价通知');//易达判断补差价
							model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '补差价通知');
						}
					}else{
						$handle_info = '易达推送计费更新重量';
						Db::name('express_order')->where(array('id'=>$v['id']))->update($up);//更新订单重量体积
					}
				}
			}
			
			//易达推送揽收信息
			if($eop['pushType'] == 3){
				$handle_info = '揽收执行订单状态2成功';
				$realOrderState = '揽件员：'.$context["courierName"].'-电话：'.$context["courierPhone"].'-取件码：'.$context["pickUpCode"];//易达快递员
		  		$up['realOrderState'] = $realOrderState;
				$up['realOrderName'] =$context["courierName"];
				$up['realOrderMobile'] = $context["courierPhone"];
				$up['realOrderCode'] = $context["pickUpCode"];


				if($v['orderStatus'] != '0' && $v['orderStatus'] != '-1'  && $v['orderStatus'] != '5'  && $v['orderStatus'] != '9'){
					$up['orderStatus'] = 2;
					Db::name('express_order')->where(array('id'=>$v['id']))->update($up); 	
    				model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '接单成功提醒',$context["courierName"],$context["courierPhone"]);//易达更新状态
    		   }
			}
			
			$eopUp['status'] = 2;
    		$eopUp['handle_time'] = time();
    		$eopUp['handle_info'] = $handle_info;
    		Db::name('express_order_push')->where(array('id'=>$eop['id']))->update($eopUp); 
		}
		
		
		
		//京东推送
		if($eop['type'] == 3){
			$getstates = model('JdApi')->getstates($context['state']);
			$realOrderState = '揽件员：'.$context['operatorName'].'-电话：'.$context['operatorPhone'].'-操作：'.$context['categoryName'];//易达快递员
			if($context['operatorPhone']){
				$up['realOrderMobile'] =$context['operatorPhone'];
				$up['realOrderName'] = $context['operatorName'];
				$up['realOrderState'] = $realOrderState;
			}
			if($context['waybillCode']){
				$up['deliveryId'] = $context['waybillCode'];
			}
			if($getstates['orderStatus']){
				$up['orderStatus'] = $getstates['orderStatus'];
			}
			$state = $context['state'];

			$up['orderStatusName'] = $getstates['stateName'];
			$handle_info = $getstates['stateName'];
			if($state=='200001'){
				$handle_info = '京东订单揽收';
				model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '接单成功提醒',$context["courierName"],$context["courierPhone"]);//京东更新状态
			}
			if($state=='200010' || $state=='200052'){
				$handle_info = '京东取消订单';
				if($plan_order_cancel){
					model('ExpressOrder')->cancel($v,$v['id'],$reason='接口方取消订单',$cancel_money=0,$checkOrderStatus=1);//京东接口方取消订单
				}else{
					Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>-1,'orderRightsStatus'=>1)); //京东已取消待后台退款
				}
			}
			if($state=='10034' || $state=='10035'){
				$handle_info = '京东推送订单状已签收';
				Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>4,'orderStatusName'=>'已签收'));//京东订单完成
				model('ExpressOrder')->completeProfit($v,$v['user_id'],'分销');//京东执行完成分销
				model('ExpressOrder')->orderAddIntegral($v,$v['user_id'],'给用户奖励积分');
				model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '签收成功通知');//京东完成订单发送通知
			}	
			if($up){
				Db::name('express_order')->where(array('id'=>$v['id']))->update($up); 
			}
			if($getstates['orderStatus'] >=2 && $v['diffStatus'] == 0){
				$handle_info = '京东补差价';
				$updateActualfee = model('JdApi')->updateActualfee($v);//京东补差价
			}
			
				//更新推送表
    		$eopUp['status'] = 2;
    		$eopUp['handle_time'] = time();
    		$eopUp['handle_info'] = $handle_info;
    		Db::name('express_order_push')->where(array('id'=>$eop['id']))->update($eopUp); 
		}
		
		
		//快递鸟推送回调
		if($eop['type'] == 4){
			$result = $this->kdn_push($eop,$eo);
			$eopUp['status'] = 2;
    		$eopUp['handle_time'] = time();
    		$eopUp['handle_info'] = $result['handle_info'];
    		Db::name('express_order_push')->where(array('id'=>$eop['id']))->update($eopUp); 
		}
		
		//跨越推送回调
		if($eop['type'] == 5){
			$result = model('KuayueApi')->kuayue_push($eop,$eo);
			$eopUp['status'] = 2;
    		$eopUp['handle_time'] = time();
    		$eopUp['handle_info'] = $result['handle_info'];
    		Db::name('express_order_push')->where(array('id'=>$eop['id']))->update($eopUp); 
		}
		
		
		if($eop['type'] == 6){
			$result = model('HangkongLinkApi')->hangkongLink_push($eop,$eo);
			$eopUp['status'] = 2;
    		$eopUp['handle_time'] = time();
    		$eopUp['handle_info'] = $result['handle_info'];
    		Db::name('express_order_push')->where(array('id'=>$eop['id']))->update($eopUp); 
		}
		
		//q必达推送回调
		if($eop['type'] == 7){
			$result = $this->ulifego_push($eop,$eo);
			$eopUp['status'] = 2;
    		$eopUp['handle_time'] = time();
    		$eopUp['handle_info'] = $result['handle_info'];
    		Db::name('express_order_push')->where(array('id'=>$eop['id']))->update($eopUp); 
		}


        //云腾旺店管家
        if($eop['type'] == 9){
            $result = model('YtApi')->yt_push($eop,$eo);
            $eopUp['status'] = 2;
            $eopUp['handle_time'] = time();
            $eopUp['handle_info'] = $result['handle_info'];
            Db::name('express_order_push')->where(array('id'=>$eop['id']))->update($eopUp);
        }

		
		return true;
	}
	
	
	//快递鸟订单回调5
	public function kdn_push($eop,$eo){
		$config = model('Setting')->fetchAll2();
		$plan_order_cancel = (int)$config['config']['plan_order_cancel'];//自动退款
		$v = $eo;
		$context = @json_decode($eop['context'],true);
		
		$LogisticCode = $context['LogisticCode'];//快递单号
		$calWeight = $context['Weight'];//下单实际重量
		$Volume = $context['Volume'];//开单体积
		$VolumeWeight = $context['VolumeWeight'];//体积重量
		$calWeight = @ceil($calWeight);//重构返回的重量进一步定位法
		$calWeight = (int)$calWeight;
		$PackageFee =  $context['PackageFee']*100;//包装费
		$overFee =  $context['overFee']*100;//超长超重费
		$otherFee =  $context['otherFee']*100;//其他费用
		$TotalFee =  $context['TotalFee']*100;//总费用
		$cost =  $context['cost']*100;//结算运费
		$type = $context['State'];
		$handle_info = '快递鸟【'.$type.'】';
		$falg =0;
		$freightHaocai = $PackageFee+$overFee+$otherFee;//总耗材费用
		
		if($type=='102'){
			$falg =1;
			$handle_info ='快递公司下单成功，前端同步展示快递公司名称，快递单号，取件码';
			$up['realOrderState'] =$context["Reason"];
			$up['orderStatus'] =1;
		}elseif($type=='103'){
			$falg =1;
			$handle_info ='快递公司分配快递员成功，前端页面同步展示快递小哥姓名和联系方式';
			$up['orderStatus'] =1;
			$up['realOrderName'] =$context["PickerInfo"][0]["PersonName"];
			$up['realOrderMobile'] = $context["PickerInfo"][0]["PersonTel"];
			$up['realOrderCode'] = $context["PickerInfo"][0]["PickupCode"];
			$up['realOrderState'] =$up['realOrderName'].'-'.$up['realOrderMobile'].'-寄件码-'.$context["PickerInfo"][0]['PickupCode'];
			model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '接单成功提醒',$context["PickerInfo"][0]["PersonName"],$context["PickerInfo"][0]["PersonTel"]);//快递鸟更新状态
		}elseif($type=='104'){
			$falg =1;
			$up['orderStatus'] =2;
			$handle_info ='快递小哥已取件，前端页面更新订单状态为已取件';
		}elseif($type=='301' || $type=='601'){
			$falg =1;
			$handle_info ='快递小哥已揽收包裹，前端页面更新订单状态为已揽件';
			$up['orderStatus'] =2;
			$up['packageServicePrice'] =$PackageFee+$overFee+$otherFee;//快递鸟包装费用+超长超重费+其他费用
			if($calWeight){
				$up['review_weight'] = $calWeight;//快递鸟实际重量
			}
			$up['review_vloumn'] = $Volume;//快递鸟体积
			$handle_info = '快递鸟超重';	
			if($TotalFee > $v['sumMoneyYuan_old'] && $v['diffStatus'] == 0 && $v['orderStatus'] != '0' && $v['orderStatus'] != '-1' && $v['orderStatus'] != '5'){
			    
			    
		    	//快递鸟超重
				$logoUrl = model('ExpressOrder')->logoUrl($v['kuaidi']);//获取快递公司信息
				$getZhe = model('Setting')->getZhe($v['user_id'],$logoUrl);
                $data['getZhe'] = $getZhe;
				$cate['ratio'] = $logoUrl['ratio'];
				$cate['priceA_type'] = $logoUrl['priceA_type'];
				$cate['priceA_ratio'] = $logoUrl['priceA_ratio'];
				$cate['priceA_price'] = $logoUrl['priceA_price'];
				$cate['priceB_type'] = $logoUrl['priceB_type'];
				$cate['priceB_ratio'] = $logoUrl['priceB_ratio'];
				$cate['priceB_price'] = $logoUrl['priceB_price'];
				$data['logoUrl'] = $cate;
				$o['firstPrice'] = $v['firstPrice'];
				$o['addPrice'] = $v['addPrice'];
				$o['wight'] = $v['wight'];
				$o['sumMoneyYuan_old'] = $v['sumMoneyYuan_old'];
				$data['v'] = $o;
				
				$this->curl = new \Curl();
				$data['type'] = 1;
				$data['user_id'] = $v['user_id'];
				$data['calWeight'] = $calWeight;
				$data['wight'] = $v['wight'];
				$data['TotalFee'] = $TotalFee;
				$data['packageServicePrice'] = $freightHaocai;
				$data['insurancePrice'] = 0;
				$data['host'] = trim($config['site']['host']);
				$data['mobile'] = trim($config['site']['mobile']);
				$url = getHost().'/api/RequestApi/getDiffMoney';
				$result = $this->curl->post($url,json_encode($data));
				$result = json_decode($result,true);
				$diffMoneyYuan = $result['data'];
			    
			    
				if($diffMoneyYuan){
					$v['diffMoneyYuan'] = $diffMoneyYuan;
					$handle_info = '快递鸟超重实际收费【'.round($TotalFee/100,2).'】补差价'.round($diffMoneyYuan/100,2);	
					$updataData['diffStatus']=1;
					$updataData['review_weight']=$calWeight;
					$updataData['diffMoneyYuan']=$diffMoneyYuan;
					$updataData['insurancePrice']=$freightInsured;
					$updataData['packageServicePrice']=$freightHaocai;//总耗材费用
					$up['TotalFee'] = $TotalFee;
					
					Db::name('express_order')->where(array('id'=>$v['id']))->update($updataData);//快递鸟更新订单
					model('Sms')->sendSmsTmplSend($v,$v['user_id'],$title = '补差价通知');//快递鸟判断补差价
					model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '补差价通知');
				}
		   }
		}elseif($type=='203'){
			$falg =0;
			$handle_info = '快递公司或小哥操作取消，前端订单状态变更为已取消，允许用户重新';
			if($plan_order_cancel){
				model('ExpressOrder')->cancel($v,$v['id'],$reason='快递鸟接口方取消订单',$cancel_money=0,$checkOrderStatus=1);//快递鸟接口方取消订单
			}else{
				Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>-1,'orderRightsStatus'=>1)); //快递鸟已取消待后台退款
			}
		}elseif($type=='206'){
			$falg =0;
			$handle_info = '虚假揽件，订单状态变更为已取消，线上有支付费用需退还用户，并允许用户重新下单';
			if($v['orderStatus'] != '0' && $v['orderStatus'] != '-1' && $v['orderStatus'] != '5'){
				if($plan_order_cancel){
					model('ExpressOrder')->cancel($v,$v['id'],$reason='虚假揽件取消订单',$cancel_money=0,$checkOrderStatus=1);//快递鸟接口方取消订单
				}else{
					Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>-1,'orderRightsStatus'=>1)); //快递鸟已取消待后台退款
				}
			}
		}elseif($type=='207'){
			$falg =0;
			$handle_info ='线下收费，标记订单为线下收费，订单正常发出，线上有支付费用需退还用户';
		}elseif($type=='208'){
			$falg =0;
			$handle_info ='重量修正，获取更新后的重量与运费，并唤起前端用户多退少补的支付操作';
		}elseif($type=='302'){
			$falg =1;
			$handle_info ='前端页面更新快递单号，有接入轨迹查询时需要利用新的快递单号进行查询';
			$up['deliveryId'] =$context["LogisticCode"];
		}elseif($type=='110'){
			$falg =0;
			$handle_info ='前端页面更新预约取件时间';
		}elseif($type=='3'){
			$falg =1;
			$handle_info ='订单状态更新为已签收';
			$up['orderStatus'] =4;
			Db::name('express_order')->where(array('id'=>$v['id']))->update(array('orderStatus'=>4)); 	//订单完成
			model('ExpressOrder')->completeProfit($v,$v['user_id'],'分销');//执行完成分销
			model('ExpressOrder')->orderAddIntegral($v,$v['user_id'],'给用户奖励积分');//赠送优惠券
			model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '签收成功通知');
		}elseif($v['orderStatus'] == 1 && $type=='2'){
			$falg =1;
			$handle_info ='订单状态进入在途中';
			$up['orderStatus'] =3;
		}
		if($falg){
			Db::name('express_order')->where(array('id'=>$v['id']))->update($up);//更新订单
		}
		
		$data['deliveryId'] = $LogisticCode;
		$data['orderStatusName'] = '';
		$data['orderStatus'] = $up['orderStatus'];
		$data['handle_info'] = $handle_info;
		return $data;
	}
	
	
	//众发物流订单/物流状态回调处理
	public function zhongfa_push($eop,$eo){
		$config = model('Setting')->fetchAll2();
		$plan_order_cancel = (int)$config['config']['plan_order_cancel'];
		$v = $eo;
		$this->zfDebugLog('zhongfa_push', '开始 push_id='.(isset($eop['id']) ? $eop['id'] : '').' 匹配订单='.($v ? 'id='.$v['id'].' orderStatus='.$v['orderStatus'] : '未找到'));
		$payload = @json_decode($eop['context'], true);
		if(!is_array($payload)){
			$this->zfDebugLog('zhongfa_push', 'JSON解析失败 context='.substr((string)$eop['context'], 0, 500));
			return array('handle_info' => '众发回调JSON解析失败');
		}
		$eventData = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : array();
		$status = isset($eventData['status']) ? strtoupper((string)$eventData['status']) : '';
		$statusDesc = isset($eventData['description']) ? $eventData['description'] : (isset($eventData['statusDesc']) ? $eventData['statusDesc'] : '');
		$eventContent = isset($eventData['eventContent']) && is_array($eventData['eventContent']) ? $eventData['eventContent'] : array();
		$handle_info = '众发【'.$status.'】'.($statusDesc ? $statusDesc : '');
		$this->zfDebugLog('zhongfa_push', '解析 status='.$status.' waybillNo='.(isset($eventData['waybillNo']) ? $eventData['waybillNo'] : '').' orderNo='.(isset($eventData['orderNo']) ? $eventData['orderNo'] : '').' outOrderNo='.(isset($eventData['outOrderNo']) ? $eventData['outOrderNo'] : ''));
		$up = array();

		if(isset($eventData['waybillNo']) && $eventData['waybillNo']){
			$up['expressNo'] = (string)$eventData['waybillNo'];
		}
		if(isset($eventData['orderNo']) && $eventData['orderNo']){
			$up['deliveryId'] = (string)$eventData['orderNo'];
		}

		if(in_array($status, array('ACCEPT', 'CREATE', 'ASSIGN_SITE', 'ASSIGN_COURIER', 'REASSIGN_SITE', 'REASSIGN_COURIER'), true)){
			if($v && $v['orderStatus'] != '-1' && $v['orderStatus'] != '5' && $v['orderStatus'] != '4'){
				$up['orderStatus'] = 2;
				$up['orderStatusName'] = $statusDesc ? $statusDesc : '已接单';
				if(isset($eventContent['courierName']) || isset($eventContent['pickupPersonName'])){
					$courierName = isset($eventContent['courierName']) ? $eventContent['courierName'] : $eventContent['pickupPersonName'];
					$courierPhone = isset($eventContent['courierPhone']) ? $eventContent['courierPhone'] : (isset($eventContent['pickupPersonPhone']) ? $eventContent['pickupPersonPhone'] : '');
					$up['realOrderName'] = $courierName;
					$up['realOrderMobile'] = $courierPhone;
					$up['realOrderState'] = '揽件员：'.$courierName.'-电话：'.$courierPhone;
					model('WeixinTmpl')->getWeixinTmplSend($v, $v['user_id'], $title = '接单成功提醒', $courierName, $courierPhone);
				}
			}
		}elseif($status === 'GOT'){
			if($v && $v['orderStatus'] != '-1' && $v['orderStatus'] != '5'){
				$up['orderStatus'] = 3;
				$up['orderStatusName'] = $statusDesc ? $statusDesc : '已揽件';
			}
		}elseif(in_array($status, array('CANCEL', 'CP_CANCEL', 'SUBMIT_FAILED'), true)){
			if($v && $v['orderStatus'] != '-1' && $v['orderStatus'] != '5' && $v['orderRightsStatus'] == 0){
				$handle_info = '众发取消订单';
				$cancelReason = isset($eventContent['cancelRemark']) ? $eventContent['cancelRemark'] : '众发接口方取消订单';
				if($plan_order_cancel){
					model('ExpressOrder')->cancel($v, $v['id'], $reason = $cancelReason, $cancel_money = 0, $checkOrderStatus = 1);
				}else{
					Db::name('express_order')->where(array('id' => $v['id']))->update(array('orderStatus' => -1, 'orderRightsStatus' => 1));
				}
			}
		}elseif($status === 'DONE'){
			if($v && in_array((int)$v['orderStatus'], array(2, 3), true)){
				$handle_info = '众发订单已完结';
				Db::name('express_order')->where(array('id' => $v['id']))->update(array('orderStatus' => 4, 'orderStatusName' => $statusDesc ? $statusDesc : '已签收'));
				model('ExpressOrder')->completeProfit($v, $v['user_id'], '分销');
				model('ExpressOrder')->orderAddIntegral($v, $v['user_id'], '给用户奖励积分');
				model('WeixinTmpl')->getWeixinTmplSend($v, $v['user_id'], $title = '签收成功通知');
			}
		}elseif($status === 'BILL'){
			$handle_info = '众发计费回调';
		}

		if($up && $v){
			$updated = Db::name('express_order')->where(array('id' => $v['id']))->update($up);
			$this->zfDebugLog('zhongfa_push', '更新订单 id='.$v['id'].' db='.($updated !== false ? 'ok' : 'fail').' up='.json_encode($up, JSON_UNESCAPED_UNICODE));
		}elseif(!$v){
			$this->zfDebugLog('zhongfa_push', '跳过更新: 未匹配 express_order push_id='.(isset($eop['id']) ? $eop['id'] : '').' status='.$status.' orderNo='.(isset($eventData['orderNo']) ? $eventData['orderNo'] : '').' outOrderNo='.(isset($eventData['outOrderNo']) ? $eventData['outOrderNo'] : ''));
		}else{
			$this->zfDebugLog('zhongfa_push', '无字段更新 id='.$v['id'].' status='.$status.' handle_info='.$handle_info);
		}
		$this->zfDebugLog('zhongfa_push', '结束 '.$handle_info);
		return array('handle_info' => $handle_info);
	}

	/** 众发调试日志 → /tmp/zf_debug.log（与 Setting 联调日志同文件） */
	private function zfDebugLog($step, $message = ''){
		$line = '['.date('Y-m-d H:i:s').']['.$step.'] '.$message."\n";
		@file_put_contents('/tmp/zf_debug.log', $line, FILE_APPEND);
	}
	
	public function dailicity(){
		$province_id = input('province_id');
		$outArr = array();
		$cityList = Db::name('copy_city')->where(array('ParentId' =>$province_id))->select();
		$outStr = json_encode($cityList);
		echo $outStr;
		die();
	}
	
	public function dailiarea(){
		$city_id = input('city_id');
		$outArr = array();
		$areaList = Db::name('copy_area')->where(array('city_id' =>$city_id))->select();
		$outStr = '';
		$outStr = json_encode($areaList);
		echo $outStr;
		die();
	}

    public function dailicommunity(){
        $business_id = input('business_id');
        $outArr = array();
        $communityList = Db::name('business_community')->where(array('business_id' =>$business_id))->select();
        $outStr = '';
        $outStr = json_encode($communityList);
        echo $outStr;
        die();
    }

    public function dailibusiness(){
        $area_id = input('area_id');
        $outArr = array();
        $businessList = Db::name('copy_business')->where(array('area_id' =>$area_id))->select();
        $outStr = '';
        $outStr = json_encode($businessList);
        echo $outStr;
        die();
    }

	
	
}
