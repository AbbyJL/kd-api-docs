<?php
namespace app\agent\controller;
use think\Db;

class Passport extends Base {

    public function login(){
        if(request()->post()){
            $account = input('account','','trim,htmlspecialchars');
            if(empty($account)){
                return json(array('status' => 'error', 'message' => '请输入用户名!'));
            }
            $password = input('password','','trim,htmlspecialchars');
            if(empty($password)){
                return json(array('status' => 'error', 'message' => '请输入登录密码!'));
            }
            $backurl = input('backurl','', 'trim,htmlspecialchars');
            if(empty($backurl)){
                $backurl = url('index/index');
            }
            if(true == model('Passport')->login($account, $password)){
                return json(array('status' => 'success', 'message' => '登录商户中心成功', 'backurl' => $backurl));
            }
            return json(array('status' => 'error', 'message' => model('Passport')->getError()));
        }else{
            if(!empty($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) && !strstr($_SERVER['HTTP_REFERER'], 'passport')) {
                $backurl = $_SERVER['HTTP_REFERER'];
            }else{
                $backurl = url('index/index');
            }
            $this->assign('backurl', $backurl);
            return $this->fetch();
		}
    }
    public function logout(){
        model('Passport')->logout();
        $this->success('退出登录成功', url('passport/login'));
    }
}