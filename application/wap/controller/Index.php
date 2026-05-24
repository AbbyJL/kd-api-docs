<?php
namespace app\wap\controller;
use think\Db;
use app\common\model\Setting;
class Index extends Base{
	
    protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->host = $this->config['site']['host'];
		$this->curl = new \Curl();
    }
	
	
	
	
	public function article($article_id = 0){
		$article_id = (int) input('article_id');
		$this->assign('article_id',$article_id);
		
		$detail = Db::name('article')->where(array('article_id'=>$article_id))->find();
		
		$this->assign('detail',$detail);
		return $this->fetch();
    }
	
	
	public function index(){
		$src = 'pages/index/index';
		$this->assign('src',$src);
		return $this->fetch();
	}
	
	//公众号授权
	public function authorize(){
		$appid = $this->config['weixin']['appid'];
		$IS_WEIXIN =  is_weixin();
		$uid = input('uid','','trim,htmlspecialchars');
		$url = urlencode(__HOST__.url('index/wxstart',array('uid'=>$uid)));
		
		if($IS_WEIXIN && $act != 'wxstart'){
			$state = md5(uniqid(rand(),TRUE));
			session('state',$state);
			if(!empty($_SERVER['REQUEST_URI'])){
				$backurl = $_SERVER['REQUEST_URI'];
			}else{
				$backurl = url('index/index');
			}
			cookie('backurl',$backurl);
			$login_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid .'&redirect_uri='.$url.'&response_type=code&scope=snsapi_userinfo&state='.$state.'#wechat_redirect';
			header("location:{$login_url}");
			echo $login_url;
			die;
		}
    }
	
	
	public function wxstart($uid=0){
		$appid = $this->config['weixin']['appid'];
		$appsecret = $this->config['weixin']['appsecret'];
		$state = session('state');
        if($_REQUEST['state']){
            if(empty($_REQUEST['code'])){
                $this->error('授权后才能登陆', U('passport/login'));
            }
            $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid .'&secret='.$appsecret.'&code='.$_REQUEST['code'].'&grant_type=authorization_code';
            $str = $this->curl->get($token_url);
            $params = json_decode($str, true);
            if(!empty($params['errcode'])){
                echo '<h3>error:</h3>' . $params['errcode'];
                echo '<h3>msg  :</h3>' . $params['errmsg'];
                die;
            }
            if(empty($params['openid'])){
                $this->error('获取openid失败',url('index/index'));
            }
            $info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$params['access_token'].'&openid='.$params['openid'].'&lang=zh_CN';
            $info = $this->curl->get($info_url);
            $info = json_decode($info, true);
            $data = array(
				'type' => 'weixin', 
				'uid' => $uid, 
				'open_id' => $params['openid'],
				'token' => $params['refresh_token'], 
				'unionid' => $info['unionid'], 
				'nickname' => $info['nickname'], 
				'headimgurl' => $info['headimgurl']
			);
            $this->wxAutoRegistr($data);
        }
    }
	
	
	//自动注册
    private function wxAutoRegistr($data){
		if(!$data['unionid']){
			$this->error('获取unionid失败，请绑定开放平台后再来操作');
	    }
		if(!$data['open_id']){
			$this->error('获取open_id失败');
	    }
		$connect = Db::name('connect')->where(array('unionid'=>$data['unionid']))->order(array('create_time'=>'desc'))->find();
		if(!$connect){
			$connect = Db::name('connect')->where(array('uid'=>$data['uid']))->order(array('create_time'=>'desc'))->find();
		}
	    if(!$connect){
			$this->error('获取connect信息失败，请先进去小程序后再来绑定');
	    }
		$users = Db::name('users')->where(array('user_id'=>$connect['uid']))->find(); 
		if(!$users){
			$this->error('获取users信息失败，行先进去小程序登录后再来绑定');
	    }
		
		Db::name('connect')->update(array('connect_id'=>$connect['connect_id'],'open_id' =>$data['open_id']));
		Db::name('users')->update(array('user_id'=>$connect['uid'],'open_id' =>$data['open_id'],'unionid' =>$data['unionid']));
		
		header('Location:' . url('index/index'));die;
    }
	
    
   
}