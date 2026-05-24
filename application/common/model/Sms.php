<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

use app\common\model\Setting;


class Sms extends Base{
    protected $pk = 'sms_id';
    protected $tableName = 'sms';
    protected $token = 'sms';




	//发送短信中间件
	public function send($code,$shop_id,$mobile, $data){
		$config = Setting::config();
		if($config['sms']['dxapi'] == 'bo'){
           $this->smsBaoSend($code,$shop_id, $mobile, $data);
        }elseif($config['sms']['dxapi'] == 'feige'){
           $this->smsFeigeSend($code,$shop_id, $mobile, $data);
        }elseif($config['sms']['dxapi'] == 'feige'){
            $this->smsFeigeSend($code,$shop_id, $mobile, $data);
        }else{
			return false;
		}
		return true;
	}



 	//飞鸽传书接口
    public function smsFeigeSend($code,$shop_id,$mobile,$data){
		$config = Setting::config();
		list($sms_id,$shop_id,$content,$d) = $this->getSmsContent($code,$shop_id,$mobile, $data);
		$local = array('mobile'=>$mobile, 'content' => $content);
		if($shop_id){
			$sms_shop = Db::name('sms_shop')->where(array('type'=>'shop','status'=>'0','shop_id'=>$shop_id))->find();
			if($sms_shop['num'] <= 1){
				model('SmsBao')->ToUpdate($sms_id,$shop_id,$res = '-1');//更新状态未-1
				return true;
			}
		}
		$sms_tmpl1 = Db::name('sms')->where(array('sms_key'=>$code))->value('sms_tmpl1');
		$sms_apikey=trim($config['sms']['sms_apikey']);
		$sms_secret=trim($config['sms']['sms_secret']);
		$sms_sign_id=trim($config['sms']['sms_sign_id']);
		$sms_tmpl1=trim($sms_tmpl1);
		
		
		if($sms_tmpl1){
			if($code=='sms_code' || $code=='sms_yzm'){
				$content = $data['code'];
			}
			if($code=='send_sms_user_diff_money'){
				$content = $data['orderId'];
			}
			if($code=='sms_user_rank_update'){
				$content = $data['newRankName'];
			}
			if($code=='sms_user_newpwd'){
				$content = $data['newpwd'];
			}
			
			$postData = array (
				'apikey'  =>  $sms_apikey,
				'secret' => $sms_secret,
				'content' => $content,
				'mobile' => $sms_tmpl1,
				'mobile' => $mobile,
				'template_id' => $sms_tmpl1
			);
			$result = $this->feigeCurlPost('https://api.4321.sh/sms/template',$postData);
		}else{
			$postData = array (
				'apikey'  =>  $sms_apikey,
				'secret' => $sms_secret,
				'content' => $content,
				'mobile' => $mobile,
				'sign_id' => $sms_sign_id
			);
			$result = $this->feigeCurlPost('https://api.4321.sh/sms/send',$postData);
		}
		$result = json_decode($result,true);
		if($result['code']==0){
			model('SmsBao')->ToUpdate($sms_id,$shop_id,1);//更新短信宝状态
			return true;
		}else{
			$this->error = '发送失败'.$result['msg'].$result['msg_no'];
			return false;
		}
    }



	private function feigeCurlPost($url,$postFields){
		$postFields = json_encode($postFields);
		$ch = curl_init ();
		curl_setopt( $ch,CURLOPT_URL, $url); 
		curl_setopt( $ch,CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8'
			)
		);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt( $ch, CURLOPT_TIMEOUT,1); 
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
		$ret = curl_exec ($ch);
		curl_close ($ch);
		return $ret;
	}



    //短信宝发接口
    public function smsBaoSend($code,$shop_id,$mobile,$data){
		$config = Setting::config();
		list($sms_id,$shop_id,$content) = $this->getSmsContent($code, $shop_id,$mobile, $data);
		$local = array('mobile' => $mobile, 'content' => $content);
		if($shop_id){
			$sms_shop = Db::name('sms_shop')->where(array('type'=>'shop','status'=>'0','shop_id'=>$shop_id))->find();
			if($sms_shop['num'] <= 1){
				model('SmsBao')->ToUpdate($sms_id,$shop_id,$res = '-1');//更新状态未-1
				return true;
			}
		}
		$account=trim($config['sms']['sms_bao_account']);
		$password = md5(trim($config['sms']['sms_bao_password'])); //短信平台密码
		$config['sms']['url']='http://api.smsbao.com/sms?u='.$account.'&p='.$password.'&m={mobile}&c={content}';
		$http = tmplToStr($config['sms']['url'], $local);

		$res = file_get_contents($http);
		model('SmsBao')->ToUpdate($sms_id,$shop_id,$res);//更新短信宝状态
		if($res!=1){
			$this->error = '发送失败'.$res;
			return false;
		}
		return true;
    }



	//获取发送详情万能短信接口模板这里万能接口跟腾讯云公用
	public function getSmsContent($code,$shop_id,$mobile,$data){
		$d = $data;
		$config = Setting::config();
		if($detail = Db::name('sms')->where('sms_key',$code)->find()){
			$content = $detail['sms_tmpl'];
            $data['sitename'] = $config['site']['sitename'];
            $data['tel'] = $config['site']['tel'];
            foreach ($data as $k => $val) {
                $val = str_replace('【', '', $val);
                $val = str_replace('】', '', $val);
                $content = str_replace('{' . $k . '}', $val, $content);
            }
            if(is_array($mobile)) {
                $mobile = join(',', $mobile);
            }
            if($config['sms']['charset']) {
                $content = auto_charset($content, 'UTF8', 'gbk');
            }
			$sms_id = $this->sms_bao_add($mobile,$shop_id, $content);//添加数据
			return array($sms_id,$shop_id,$content,$d);
		}else{
			return array(1,1,'【错误】没找到对应的模板');
		}
	}





	//短信宝添加
    public function sms_bao_add($mobile,$shop_id, $content){
        $sms_data = array();
        $sms_data['mobile'] = $mobile;
		$sms_data['shop_id'] = $shop_id;
        $sms_data['content'] = $content;
        $sms_data['create_time'] = time();
        $sms_data['create_ip'] = request()->ip();
        if ($sms_id = Db::name('sms_bao')->insertGetId($sms_data)){
            return $sms_id;
        }
        return true;
    }







    //验证码
    public function sms_yzm($mobile, $randstring){
		$this->send('sms_yzm',$shop_id = '0', $mobile, array('code' => $randstring));
        return true;
    }

	 //用户重置新密码
    public function sms_user_newpwd($mobile, $password){
		$_config = Setting::config();
       $this->send('sms_user_newpwd',$shop_id = '0', $mobile, array(
			'sitename' => cut_msubstr($_config['site']['sitename'],0,16, false),
		    'newpwd' => $password
	   ));
       return true;
    }


    //用户下载优惠劵通知用户手机
    public function coupon_download_user($download_id, $uid){
        $Coupondownload = Db::name('coupon_download')->find($download_id);
        $Coupon = Db::name('coupon')->find($Coupondownload['coupon_id']);
        $user = Db::name('users')->find($uid);

		$this->send('coupon_download_user',$Coupondownload['shop_id'], $user['mobile'], array(
			'couponTitle' => cut_msubstr($Coupon['title'],0,16, false),
			'code' => $Coupondownload['code'],
			'expireDate' => $Coupon['expire_date']
		));
        return true;
    }



	 //后台账户异地登录通知管理员
    public function sms_admin_login_admin($mobile,$user_name,$time){
        $this->send('sms_admin_login_admin', $shop_id = '0',$mobile, array(
			'userName' => cut_msubstr($user_name, 0, 8, false),
			'time' => $time
		));
        return true;
    }


	//新用户注册短信通知接口，支持扣除商家短信
    public function register($user_id,$mobile,$account,$password,$shop_id){
		$this->send('register',0,$mobile, array(
			'userId' => $user_id,
			'userAccount' => cut_msubstr($account, 0, 8, false),
			'userPassword' => $password,
			'shopName' =>cut_msubstr($shop['shop_name'],0, 8, false),
		));
        return true;
    }




	//会员升级短信通知
	public function sms_user_rank_update($log_id){
		$logs = Db::name('user_rank_logs')->where(array('log_id'=>$log_id))->find();
		
		$users = Db::name('users')->find($logs['user_id']);
		$mobile = $users['mobile'];
		
		$this->send('sms_user_rank_update',0,$mobile, array(
			'userName' => cut_msubstr($users['nickname'],0, 12, false),
			'oldRankName' => cut_msubstr($logs['old_rank_name'],0, 12, false),
			'newRankName' => cut_msubstr($logs['new_rank_name'],0, 12, false),
			'logId' => $logs['log_id']
		));
	}
	
	
	//补差价
	public function sendSmsTmplSend($detail=array(),$user_id=0,$title = '补差价'){
		$_config = Setting::config();
		$users = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$mobile = $users['mobile'] ? $users['mobile'] : $detail['sendMobile'];
		
		#缓存计数器解决并发重复订单问题
		$request =Cache('send_sms_user_diff_money_'.$detail['id']);
		$request =$request+1;
		Cache('send_sms_user_diff_money_'.$detail['id'],$request,30);
		if($mobile && $request <= 1){
			$this->send('send_sms_user_diff_money',0,$mobile, array(
				'userName' => cut_msubstr($users['nickname'],0,12,false),
				'orderId' => $detail['id'],
				'sitename' => cut_msubstr($_config['site']['sitename'],0,16, false)
			));
			return true;
		}
		return true;
	}
	
	
	//补差价
	public function sendSmsPaySend($detail=array(),$user_id=0,$title = '待支付运单提醒'){
		$_config = Setting::config();
		$mobile = $detail['receiveMobile'];
		
		#缓存计数器解决并发重复订单问题
		$request =Cache('send_sms_pay_'.$detail['deliveryId']);
		$request =$request+1;
		Cache('send_sms_pay_'.$detail['deliveryId'],$request,30);
		if($mobile && $request <= 1){
			$this->send('send_sms_pay_money',0,$mobile, array(
				'deliveryId' => cut_msubstr($detail['nickname'],0,12,false),
				'sumMoneyYuan' => round($detail['sumMoneyYuan']/100,2),
				'kuaidi' => $detail['kuaidi'],
				'wight' => $detail['wight'],
				'sitename' => cut_msubstr($_config['site']['sitename'],0,16, false)
			));
			return true;
		}
		return true;
	}
	
	
	
	//提交货运信息
	public function sendSmsExpressTransport($data=array()){
		$_config = Setting::config();
		$mobile = $_config['site']['config_mobile'];
		$this->send('send_sms_express_transport',0,$mobile, array(
			'sender' => $data['sender_province'].'-'.$data['sender_city'].'-'.$data['sender_area'],
			'recipients' => $data['recipients_province'].'-'.$data['recipients_city'].'-'.$data['recipients_area'],
			'mobile' => $data['mobile']
		));
	}
	//网站后台推送短信
    public function smsAdminPush($detail,$mobile){

		if($detail['title'] && $detail['intro'] && $detail['url']){
			$news_title = cut_msubstr($detail['title'],0,12, false).'内容：'.cut_msubstr($detail['intro'],0,38, false).'链接：'.cut_msubstr($detail['url'],0,80, false);
		}elseif($detail['title'] && $detail['intro']){
			$news_title = cut_msubstr($detail['title'],0,12, false).'内容：'.cut_msubstr($detail['intro'],0,38, false);
		}else{
			$news_title = cut_msubstr($detail['title'],0,12, false);
		}
		$this->send('sms_shop_news_push',$shop_id = 0, $mobile, array(
			'newsTitle' => cut_msubstr($news_title,0,16, false), //标题
			'newsSource' => cut_msubstr($config['site']['sitename'], 0, 8, false), //作者
		));
        return true;
    }




}
