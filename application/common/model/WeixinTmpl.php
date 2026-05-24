<?php
namespace app\common\model;

use think\Db;
use think\Model;

use EasyWeChat\Foundation\Application;
use app\common\model\Setting;

class WeixinTmpl extends Base{
 	protected $pk   = 'tmpl_id';
    protected $tableName =  'weixin_tmpl';

	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
    }

	//轮询给配送员选择模板消息推送
	public function getWeixinTmplSend($detail=array(),$user_id=0,$title = '签收成功通知',$name='取件员',$mobile=''){
		
		if($title == '签收成功通知'){
			$template_id = Db::name('weixin_tmpl')->where(array('type'=>1,'title'=>'签收成功通知'))->value('template_id');
		}elseif($title == '补差价通知'){
			$template_id = Db::name('weixin_tmpl')->where(array('type'=>1,'title'=>'补差价通知'))->value('template_id');
		}elseif($title == '接单成功提醒'){
			$template_id = Db::name('weixin_tmpl')->where(array('type'=>1,'title'=>'接单成功提醒'))->value('template_id');
		}elseif($title == '优惠券发放通知'){
			$template_id = Db::name('weixin_tmpl')->where(array('type'=>1,'title'=>'优惠券发放通知'))->value('template_id');
		}elseif($title == '收益到账通知'){
			$template_id = Db::name('weixin_tmpl')->where(array('type'=>1,'title'=>'收益到账通知'))->value('template_id');
		}elseif($title == '待支付运单提醒'){
			$template_id = Db::name('weixin_tmpl')->where(array('type'=>1,'title'=>'待支付运单提醒'))->value('template_id');
		}
		
		
		$openid = Db::name('connect')->where(array('uid'=>$user_id))->value('openid');
		
		
		if($title == '签收成功通知'){
			$formwork ='{
				"touser": "'.$openid.'",
				"template_id": "'.$template_id.'",
				"page":"pages/index/index",
				"data":{
				  "character_string1":{
					  "value": "'.$detail['deliveryId'].'"
				  },
				  "thing5":{
					  "value": "'.cut_msubstr($detail['kuaidi'],0,5,false).'"
				  },
				  "time3":{
					  "value": "'.date('Y-m-d H:i:s',time()).'"
				  } ,
				  "thing4":{
					  "value": "'.$detail['id'].'订单已签收"
				  }
			   }
			}';
		}elseif($title == '补差价通知'){
			$formwork ='{
				"touser": "'.$openid.'",
				"template_id": "'.$template_id.'",
				"page":"pages/index/index",
				
				"data": {
				  "character_string1":{
					   "value": "'.$detail['deliveryId'].'"
				  },
				  "amount2": {
					 "value": "'.round($detail['diffMoneyYuan']/100,2).'"
				  },
				  "thing5": {
					  "value": "请尽快去补差价"
				  }
			   }
			}';
		}elseif($title == '接单成功提醒'){
			if($name=='nul'){
				$name='取件员';
			}
			$name=@preg_replace("/\\d+/",'', $name);//去掉取件员的数字
			if($mobile=='电话null，工号:nul'){
				$mobile='打开订单查询';
			}elseif($mobile=='手机null，工号:nul'){
				$mobile='打开订单查询';
			}
			
			$formwork ='{
				"touser": "'.$openid.'",
				"template_id": "'.$template_id.'",
				"page":"pages/index/index",
				
				"data": {
				  "thing7": {
					  "value": "'.cut_msubstr($detail['receiveAddress'],0,12,false).'"
				  },
				  "phrase14": {
					  "value": "'.cut_msubstr($name,0,4,false).'"
				  },
				  "time4": {
					   "value": "'.date('Y-m-d H:i:s',time()).'"
				  } ,
				  "thing5": {
					  "value": "手机'.$mobile.'"
				  }
			   }
			}';
		}elseif($title == '优惠券发放通知'){
			$formwork ='{
				"touser": "'.$openid.'",
				"template_id": "'.$template_id.'",
				"page":"pages/index/index",
				
				"data": {
				  "thing1": {
					  "value": "'.cut_msubstr($detail['title'],0,12,false).'"
				  },
				  "amount2": {
					  "value": "'.round($detail['reduce_price']/100,2).'"
				  },
				  "thing3": {
					   "value": "'.$detail['expire_date'].'"
				  } ,
				  "thing5": {
					  "value": "请尽快使用"
				  }
			   }
			}';
		}elseif($title == '收益到账通知'){
			$formwork ='{
				"touser": "'.$openid.'",
				"template_id": "'.$template_id.'",
				"page":"pages/index/index",
				
				"data": {
				  "amount1": {
					  "value": "'.round($mobile/100,2).'"
				  },
				  "phrase2": {
					  "value": "收益"
				  },
				  "thing3": {
					   "value": "请注意到钱包账户查收"
				  }
			   }
			}';
		}elseif($title == '待支付运单提醒'){
			$formwork ='{
				"touser": "'.$openid.'",
				"template_id": "'.$template_id.'",
				"page":"pages/find/index/index",
				
				"data": {
				  "character_string1": {
					  "value": "'.$detail['deliveryId'].'"
				  },
				  "快递公司": {
					  "value": "'.$detail['kuaidi'].'"
				  },
				  "amount3": {
					  "value": "'.round($detail['sumMoneyYuan']/100,2).'"
				  },
				  "date2": {
					   "value": "'.date('Y-m-d H:i:s',time()).'"
				  },
				  "thing6": {
					   "value": "请尽快登陆小程序支付"
				  }
			   }
			}';
		}
		
		if($template_id){
			$send = $this->send($formwork,$template_id,$user_id,$openid,$detail['id'],$detail['id'],$type='express',$title);
		}
		return true;
	}
	
	
	
	
	//主体发送
	public function send($formwork = '',$template_id = '',$user_id = '',$openid = '',$order_id ='',$id = 0,$type='express',$title=''){
		if(!$openid){
			return false;
		}
		
        $sendMessage = $this->sendMessage($formwork);
	    $sendMessage= json_decode($sendMessage,true);
		
		
	    $formworks= json_decode($formwork,true);
		
	    $arr['template_id'] = $template_id;
		$arr['user_id'] = $user_id;
		$arr['running_id'] = $id;
		$arr['order_id'] = $id;
		$arr['formwork'] = $formwork;
		$arr['wxapp'] = 1;
		$arr['type'] = $type;
		$arr['cate'] = 1;
		$arr['open_id'] = $openid;
		$arr['status'] = $sendMessage['errcode'];
		$arr['title'] = $title;
		$arr['info'] = $sendMessage['errmsg'];
		if($sendMessage['errmsg'] == 'ok'){
			$arr['is_send'] = 1;
		}
		
		$html = '';
		foreach($formworks['data'] as $v){
			$html .= $v['value'].',';
		}
		$arr['comment'] = $html;
		$arr['create_time'] = time();
		
		//p($arr);die;
		if($arr){
			$msg_id = Db::name('weixin_msg')->insertGetId($arr);
		}
		return array('msg_id'=>$msg_id,'sendMessage'=>$sendMessage);
    }
	
	
	
	
	
	
	//公众号推送模板消息
	public function net($uid,$template_id='',$title = '',$data='',$openid='',$id=''){
		$uid = (int)$uid;
		if($template_id){
            $data['template_id'] = $template_id;
            $data['touser']  = $openid;
			$msg['title'] =$title;
			$msg['wxapp'] =2;
			$msg['type'] = 1;
			$msg['cate'] = 2;
			$msg['user_id'] = $uid;
			$msg['open_id'] = $openid;
			$msg['serial'] = $serial;
			$arr['running_id'] = $id;
			$arr['order_id'] = $id;
			$msg['template_id'] = $tmpl['template_id'];
			$html = '';
			foreach($data['data'] as $v){
				$html .= $v['value'].'<br>';
			}
			$msg['comment'] = $html;
			$msg['create_time'] = time();
			if($msg_id = Db::name('weixin_msg')->insertGetId($msg)){
				return model('Weixin')->tmplMsg($data,$msg_id);
			};
		}
	}
	

	
	public function sendMessage($formwork){
	    $access_token = model('Weixin')->getWxappSiteToken(1,0);
	    $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=".$access_token."";
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
	    curl_setopt($ch, CURLOPT_POST,1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS,$formwork);
	    $data = curl_exec($ch);
	    curl_close($ch);
		$result['errcode'] = 0;
		$result = json_decode($data,true);
		if($result['errcode']=='41001'){
			model('Weixin')->getWxappSiteToken(1,1);
		}
	    return $data;
	}



}
