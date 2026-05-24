<?php
namespace app\common\model;
use think\Model;
use think\Db;

use app\common\model\Setting;

class Passport{
    private $CONFIG = array();
    private $charset = 0;
    private $error = null;
    private $domain = '@qq.com';
    private $token = array();
    private $user  = array();
    private $_CONFIG = array();

    public function __construct(){
        $config = Setting::config();
        $this->_CONFIG = $config;
    }

    public function getToken(){
        return $this->token;
    }

    public function getUserInfo(){
        return $this->user;
    }

    public function getError(){
        return $this->error;
    }


    public function logout(){
        clearUid();
        return true;
    }

    public function uppwd($account,$oldpwd,$newpwd){
		$user = model('Users')->getUserByAccount($account);
        if($user['password'] == $newpwd){
            Db::name('users')->update(array('user_id'=>$user['user_id'],'password'=>md5($newpwd),'is_lock'=>0,'lock_num'=>0,'is_lock_time'=>''));
            return true;
        }else{
            return Db::name('users')->update(array('user_id'=>$user['user_id'],'password'=>md5($newpwd),'is_lock'=>0,'lock_num'=>0,'is_lock_time'=>''));
        }
    }



    //UC用邮件登录
    public function login($account, $password){

        $this->token = array(
            'token' => md5(uniqid())
        );
		if(isMobile($account)){
            $user = model('Users')->getUserByMobile($account);
		}else{
			$user = model('Users')->getUserByAccount($account);
		}

        if(empty($user)){
           $this->error = '账号或密码不正确';
           return false;
        }
        if($user['closed'] == 1) {
            $this->error = '用户已被拉黑';
            return false;
         }

        if($user['password'] != md5($password)){
			$this->error = '账号或密码不正确';
            return false;
			Db::name('admin_login_log')->insert(array('type'=>1,'username' =>$account,'password'=> $password,'last_time' =>time(),'last_ip' =>request()->ip(),'audit' =>0));
        }

        $data = array(
            'last_time' => time(),
            'last_ip' => request()->ip(),
            'user_id' => $user['user_id'],
            'token' => $this->token['token'],
        );
        Db::name('users')->update($data);
        setUid($user['user_id'],time());
        $connect = session('connect');

        if(!empty($connect)){
            Db::name('connect')->update(array('connect_id' => $connect,'uid' => $user['user_id']));
        }

        $this->user = $user;
        $this->token['uid'] = $user['user_id'];

        return true;
    }

	//获取邀请码
	public function getRequestCode(){
        $i = 0;
        while(true){
            $i++;
            $code = rand_string(5,1);
            $data = Db::name('users')->where(array('requestCode'=>$requestCode))->find();
            if(empty($data)){
                return $code;
            }
            if($i > 20){
                return $code;
            }
        }
    }


	//新版自动注册
    public function register($data = array(),$fid = '',$types = '0'){
		
		$config = Setting::config();//调用全局设置
		$gongpai_number = (int)$config['profit']['gongpai_number'] ? (int)$config['profit']['gongpai_number'] : '3';

        $this->token = array(
            'token' => md5(uniqid())
        );

		$data['requestCode'] = $this->getRequestCode();//邀请码
        $data['reg_time'] = time();
        $data['reg_ip'] = request()->ip();

        if($fid){
		 	$fuid = $fid;
		}else{
			$fuid = (int) cookie('fuid');
		}
		$data['parent_id'] = $fuid;
		
	
		$ISWEIXIN = is_weixin();
		if(empty($data)){
			if($ISWEIXIN == true){
				echo '注册信息不存在';	die;
				return false;
			}else{
				$this->error = '注册信息不存在';
				return false;
			}
		}
	
	
		$data['password'] = md5($data['password']);
	
		$user = Db::name('users')->where(array('account'=>$data['account'],'closed'=>0))->find();
		$users = Db::name('users')->where(array('mobile'=>$data['account'],'closed'=>0))->find();
	
		$user = $user ? $user :  $users;
	
	
		//如果没注册会员
		if(!$user){
			if(isMobile($data['account'])){
				$data['mobile'] = $data['account'];
				
				if($data['mobile']){
					$getUserByMobile = model('Users')->getUserByMobile($data['mobile']);
					if($getUserByMobile){
						$this->error = '该手机号重复请请更换手机号注册';
						return false;
					}
				}
			}
			
			
			$data['token'] = md5(uniqid());	
			$data['user_id'] = Db::name('users')->insertGetId($data);
			
			
			$this->token['uid'] = $data['user_id'];
			
			$connect = session('connect');
			if(!empty($connect)){
			   Db::name('connect')->update(array('connect_id' => $connect,'uid' => $data['user_id']));
			}
			
			//邀请新用户奖励积分
			$yao = (int)$config['integral']['yao'];
			if($yao && $fuid){
				model('Users')->addIntegral($fuid,$yao,'邀请新用户'.$data['user_id'].'获取积分',2);
			}
			$user_id = $data['user_id'];
		}else{
			$this->token['uid'] = $user['user_id'];
			$user_id = $user['user_id'];
		}
	
		
		if($types == 1){
			return $user_id;
		}else{
			setUid($user_id,time());
			return true;
		}
  }
	
	
	
}
