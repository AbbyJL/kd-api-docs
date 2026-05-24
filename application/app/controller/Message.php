<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Message extends Base{



	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->curl = new \Curl();
		$this->host = $this->config['site']['host'];
    }
	
	
	
	public function getTemplateId($title){
		if($title='接单成功提醒'){
			$tmpl = Db::name('weixin_tmpl')->where(array('title'=>'接单成功通知'))->field('serial,title,template_id')->find(); 
		}elseif($title='补差价通知'){
			$tmpl = Db::name('weixin_tmpl')->where(array('title'=>'订单补差通知'))->field('serial,title,template_id')->find(); 
		}elseif($title='签收成功通知'){
			$tmpl = Db::name('weixin_tmpl')->where(array('title'=>'订单完成通知'))->field('serial,title,template_id')->find(); 
		}elseif($title='优惠券发放通知'){
			$tmpl = Db::name('weixin_tmpl')->where(array('title'=>'兑换结果通知'))->field('serial,title,template_id')->find(); 
		}elseif($title='收益到账通知'){
			$tmpl = Db::name('weixin_tmpl')->where(array('title'=>'结算成功通知'))->field('serial,title,template_id')->find(); 
		}
		return $tmpl;
	}
	
	
	
	//拼装小程序
	//'pagepath'=>'pages/find/detail/detail?id='.$order['id'].'&handle=1', 
	public function templ_user_array($v){
		//$v['user_id'] = '51785';
		$getTemplateId = $this->getTemplateId($v['title']);
		
		$users = Db::name('users')->where(array('user_id'=>$v['user_id']))->field('open_id,user_id,subscribe_status')->find(); 
		$order = Db::name('express_order')->where(array('id'=>$v['order_id']))->find();
		
		if(!$users['open_id'] || $users['subscribe_status'] == 0){
			return false;//未关注不推送
		}
		if($order['orderStatus'] == '0' || $order['orderStatus'] == '-1' || $order['orderStatus'] == '5' || $order['orderStatus'] == '9'){
			return false;//订单状态不对不推送
		}
		//必须有接单员
		if($getTemplateId['title'] =='接单成功通知' && $order['realOrderName']){
			$data =array(
				'url'=> $this->config['site']['host'],
				'miniprogram'=> array(
					'appid'=>$this->config['wxapp']['appid'],
					'pagepath'=>'pages/index/index', 
				),
				'data'=> array(
					'first'=>array('value'=>'接单成功通知','color'=>'#000000'),
					'keyword1'=>array('value'=>$order['id'],'color'=>'#000000'), 
					'keyword2'=>array('value'=>date('Y-m-d H:i:s',time()),'color'=>'#000000'), 
					'keyword3'=>array('value'=>$order['realOrderName'],'color'=>'#000000'), 
					'keyword4'=>array('value'=>$order['realOrderMobile'],'color'=>'#000000'), 
					'remark'  =>array('value'=>'您的订单有配送员接单了请到小程序查看','color'=>'#000000')
				)
			);
		}
	
		//必须有差价金额
		if($getTemplateId['title'] =='订单补差通知' && $order['diffMoneyYuan'] && $order['diffStatus'] ==1){
			$data =array(
				'url'=> $this->config['site']['host'],
				'miniprogram'=> array(
					'appid'=>$this->config['wxapp']['appid'],
					'pagepath'=>'pages/index/index', 
				),
				'data'=> array(
					'first'=>array('value'=>'订单补差通知','color'=>'#000000'),
					'keyword1'=>array('value'=>$order['id'],'color'=>'#000000'), 
					'keyword2'=>array('value'=>round(($order['sumMoneyYuan']+$order['diffMoneyYuan'])/100,2),'color'=>'#000000'), 
					'keyword3'=>array('value'=>round($order['diffMoneyYuan']/100,2),'color'=>'#000000'), 
					'keyword4'=>array('value'=>'超重补差','color'=>'#000000'), 
					'remark'  =>array('value'=>'您有新的订单等待补差价','color'=>'#000000')
				)
			);
		}
		
		if($getTemplateId['title'] =='订单完成通知'){
			$data =array(
				'url'=> $this->config['site']['host'],
				'miniprogram'=> array(
					'appid'=>$this->config['wxapp']['appid'],
					'pagepath'=>'pages/index/index', 
				),
				'data'=> array(
					'first'=>array('value'=>'订单完成通知','color'=>'#000000'),
					'keyword1'=>array('value'=>$order['id'],'color'=>'#000000'), 
					'keyword2'=>array('value'=>date('Y-m-d H:i:s',time()),'color'=>'#000000'), 
					'remark'  =>array('value'=>'您的订单已完成','color'=>'#000000')
				)
			);
		}
		
		if($getTemplateId['title'] =='兑换结果通知'){
			$data =array(
				'url'=> $this->config['site']['host'],
				'miniprogram'=> array(
					'appid'=>$this->config['wxapp']['appid'],
					'pagepath'=>'pages/index/index', 
				),
				'data'=> array(
					'first'=>array('value'=>'兑换结果通知','color'=>'#000000'),
					'keyword1'=>array('value'=>'优惠券','color'=>'#000000'), 
					'keyword2'=>array('value'=>'登录小程序查看','color'=>'#000000'), 
					'keyword3'=>array('value'=>date('Y-m-d H:i:s',time()),'color'=>'#000000'), 
					'remark'  =>array('value'=>'您的优惠券已到账请注意查收','color'=>'#000000')
				)
			);
		}
		if($getTemplateId['title'] =='结算成功通知'){
			$data =array(
				'url'=> $this->config['site']['host'],
				'miniprogram'=> array(
					'appid'=>$this->config['wxapp']['appid'],
					'pagepath'=>'pages/index/index', 
				),
				'data'=> array(
					'first'=>array('value'=>'结算成功通知','color'=>'#000000'),
					'keyword1'=>array('value'=>'***','color'=>'#000000'), 
					'keyword2'=>array('value'=>'***','color'=>'#000000'), 
					'keyword3'=>array('value'=>date('Y-m-d H:i:s',time()),'color'=>'#000000'), 
					'remark'  =>array('value'=>'您有新的结算订单请登录小程序查看','color'=>'#000000')
				)
			);
		}
		if($data){
			return model('WeixinTmpl')->net($v['user_id'],$getTemplateId['template_id'],$getTemplateId['title'],$data,$users['open_id'],$v['order_id']);
		}
		return true;
	}
	
	
	
	//推送公众号模板消息
	public function Send(){
		$config = $this->config;
		$msg_id = (int)input('msg_id','','trim,htmlspecialchars');	
		$order_id = (int)input('order_id','','trim,htmlspecialchars');
		$user_id = (int)input('user_id','','trim,htmlspecialchars');
		$bug = (int)input('bug','','trim,htmlspecialchars');	
			
		$t = time();
		$i=0;
		$bt = NOW_TIME-1800;//30分钟订单	
			
		if($msg_id && $bug){
			$list = Db::name('weixin_msg')->where(array('msg_id'=>$msg_id))->order('msg_id desc')->field('msg_id,title,order_id,user_id,info')->limit(0,20)->select(); 
		}elseif($order_id){
			$list = Db::name('weixin_msg')->where(array('order_id'=>$order_id))->order('msg_id desc')->field('msg_id,title,order_id,user_id,info')->limit(0,20)->select(); 
		}elseif($user_id){
			$list = Db::name('weixin_msg')->where(array('is_send'=>0,'wxapp'=>1,'user_id'=>$user_id))->field('msg_id,title,order_id,user_id,info')->order('msg_id desc')->limit(0,20)->select(); 
		}else{
			$list = Db::name('weixin_msg')->where(array('create_time'=>array(array('ELT',$t),array('EGT',$bt)),'is_send'=>0,'wxapp'=>1))->order('msg_id desc')->field('msg_id,title,order_id,user_id,info')->limit(0,20)->select(); 
		}
		//p($list);die;
		$i=0;
		foreach($list as $k=>$v){
			if($v['title']){
				$i++;
				$send = $this->templ_user_array($v);
				Db::name('weixin_msg')->where(array('msg_id'=>$v['msg_id']))->update(array('is_send'=>1,'info'=>'公众号重复推送'.$v['info'])); 
			}
		}
		$msg .= '一共推送【'.$i.'】次';
		return json(array('c'=>0,'msg'=>$msg));
	}
	
}
