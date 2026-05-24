<?php
namespace app\admin\controller;

use app\common\controller\Common;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Login extends Base{
	
	
    public function index(){
        return $this->fetch();
    }
	
	public function verify(){
		return $this->verify_build('aid');
    }
	
	
    public function loging(){
		$config = Setting::config();
		if(!$this->verify_check('aid')){
			$this->jinMsgCode('图形验证码错误点击图片验证码可切换');
		}
     
        $username = input('username', 'trim');
        $password = md5(input('password', 'trim'));
		$password2 = (input('password', 'trim'));
		
        $admin = model('Admin')->getAdminByUsername($username);
		
		
	
        if(empty($admin)){
            session('verify', null);
            $this->jinMsg('账户错误');
        }
		if($admin['closed'] == 1){
			//关闭账户
            session('verify', null);
            $this->jinMsg('该账户已经被禁用');
        }
		
		//开启短信验证码登录后判断	
		if($config['register']['admin_login_sms'] ==1){
			$mobile2 = session('mobile');
			if($admin['mobile'] != $mobile2){
				$this->jinMsg('非法操作');
			}
				$scode = input('scode','', 'htmlspecialchars');
			if(!$scode){
				$this->jinMsg('请输入短信验证码');
			}
			$scode2 = session('scode');
			if(empty($scode2)){
				$this->jinMsg('请获取短信验证码');
			}
			
			if($scode != $scode2){
				$this->jinMsg('请输入正确的短信验证码');
			}
		}
		
       
        if($admin['password'] != $password){
			$this->jinMsg('用户名或密码不正确');
			Db::name('admin_login_log')->insert(array('type'=>2,'username'=>$username,'password'=>$password2,'last_time'=>time(),'last_ip'=>request()->ip(),'audit'=>0));
			session('verify', null);
        }
       
       
	
		
		if($log['last_ip'] != request()->ip()){
			$arr['is_lock'] = 1;
		}
		
		Db::name('admin')->where(array('admin_id'=>$admin['admin_id']))->update($arr);

		
		
        session('admin',$admin);
		cookie('admin',$admin);
		
		session('scode', null);
		session('mobile', null);
		
		$adminType = $admin['type'] == 1 ? '系统管理员' : '分站管理员';
		$intro = '恭喜您登陆成功【'.$adminType.'】';
        $this->jinMsg($intro, url('index/index'));
    }
	//后台退出
    public function logout(){
        $admin_ids = $this->_admin = session('admin');
        Db::name('admin')->where(array('admin_id' =>$admin_ids['admin_id']))->update(array('is_ip' =>0,'is_admin_lock'=>0,'lock_admin_mum'=>0,'is_admin_lock_time'=>''));
        session('admin',null);
		cookie('admin',null);
        $this->success('退出后台成功', url('login/index'));
    }
	
  

	
   
}