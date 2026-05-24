<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;

class Setting extends Base{

    public function _initialize(){
        parent::_initialize();
        $this->assign('getBatchApiTypes', $getBatchApiTypes = model('Setting')->getBatchApiTypes());
        $this->assign('getCompanyApiTypes', $getCompanyApiTypes = model('Setting')->getCompanyApiTypes());
    }
	
    public function site(){
        if(request()->post()){
			$data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'site', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('站点设置成功', url('setting/site'));
        }else{
            $this->assign('citys', model('City')->fetchAll());
            $this->assign('ranks', model('UserRank')->fetchAll());
            return $this->fetch();
        }
    }

    public function batch(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'batch', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('设置成功', url('setting/batch'));
        }else{
            $this->assign('getCompanyApiTypes', $getCompanyApiTypes = model('Setting')->getCompanyApiTypes());
            return $this->fetch();
        }
    }
    public function delivery(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'delivery', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('设置成功', url('setting/delivery'));
        }else{
            return $this->fetch();
        }
    }

   public function integral(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'integral', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg(''.$this->_CONFIG['integral']['name'].'设置成功', url('setting/integral'));
        }else{
            return $this->fetch();
        }
    }

 	public function config(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'config', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('全局设置成功', url('setting/config'));
        }else{
            return $this->fetch();
        }
    }

    public function attachs(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'attachs', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('附件设置成功', url('setting/attachs'));
        }else{
            return $this->fetch();
        }
    }




   public function sms(){
		$config = $this->_CONFIG;
		if(!empty($config['sms']['sms_bao_account'])){
			$http = tmplToStr('http://www.smsbao.com/query?u='.$config["sms"]["sms_bao_account"].'&p='.md5($config["sms"]["sms_bao_password"]), $local);
			$res = file_get_contents($http);
			$res1 = explode(",", $res);
			if($res1[1] > 0){
				$number = $res1[1];
			}else{
				$number = '短信宝账户或者密码错了';
			}
		}else{
			$number = '短信宝账或户密码未设置';
		}
		$this->assign('number', $number);
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'sms', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('短信配置成功', url('setting/sms'));
        }else{
            return $this->fetch();
        }
    }
	
	
	public function sms_bao(){
		$config = $this->_CONFIG;
		$mobile = trim($config['site']['config_mobile']);
		$sms_yzm = model('Sms')->sms_yzm($mobile,'123456');
		if($sms_yzm == false){
			return json(array('c'=>1,'msg'=>model('Sms')->getError()));
		}else{
			return json(array('c'=>1,'msg'=>"发送成功"));
		}
	}
	
	public function sms_feige(){
		$config = $this->_CONFIG;
		$mobile = trim($config['site']['config_mobile']);
		$sms_yzm = model('Sms')->sms_yzm($mobile,'123456');
		if($sms_yzm == false){
			return json(array('c'=>1,'msg'=>model('Sms')->getError()));
		}else{
			return json(array('c'=>1,'msg'=>"发送成功"));
		}
	}
	
	public function pay(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'pay', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('设置成功', url('setting/pay'));
        }else{
            return $this->fetch();
        }
    }
	public function jutuike(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'jutuike', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('设置成功', url('setting/jutuike'));
        }else{
            return $this->fetch();
        }
    }

    public function weixin(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'weixin', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('微信设置成功', url('setting/weixin'));
        }else{
            return $this->fetch();
        }
    }

    public function weixinmenu(){
        if(request()->post()){
            $data = input('data/a', false);
            $result = model('Weixin')->weixinmenu($data);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'weixinmenu', 'v' => $data));
            model('Setting')->cleanCache();
            if($result > 1){
				$this->jinMsg('菜单设置错误，错误码：'.$result);
			}else{
				$this->jinMsg('菜单设置成功', url('setting/weixinmenu'));
			}
        }else{
            return $this->fetch();
        }
    }




    public function mail(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'mail', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('邮箱设置成功', url('setting/mail'));
        }else{
            return $this->fetch();
        }
    }

    public function other(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'other', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('设置成功', url('setting/other'));
        }else{
            return $this->fetch();
        }
    }


	public function profit(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'profit', 'v' => $data));
            model('Setting')->cleanCache();
			cache::clear();
            $this->jinMsg('分销设置成功', url('setting/profit'));
        }else{
			$this->assign('ranks', model('UserRank')->fetchAll());
            return $this->fetch();
        }
    }


 


    public function register(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'register', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('登录注册配置成功', url('setting/register'));
        }else{
            return $this->fetch();
        }
    }


    public function cash(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'cash', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('提现设置成功', url('setting/cash'));
        }else{
			$this->assign('ranks', model('UserRank')->fetchAll());
            return $this->fetch();
        }
    }
	public function wxapp(){
        if(request()->post()){
            $data = input('data/a',false);
            $data = serialize($data);
            model('Setting')->update(array('k' =>'wxapp', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('设置成功', url('setting/wxapp'));
        }else{
		
			
			$data0 = @file_get_contents(ROOT_PATH."/data/get_jd_0_site_token.json", "w");
			$data0 = json_decode($data0,true);
			
			$data1 =@file_get_contents(ROOT_PATH."/data/get_jd_1_site_token.json", "w");
			$data1 = json_decode($data1,true);
			
			$this->assign('data0',(array)$data0);
			$this->assign('data1',(array)$data1);
			
            return $this->fetch();
        }
    }

    public function sms_shop(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'sms_shop', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('购买短信设置成功', url('setting/sms_shop'));
        }else{
            return $this->fetch();
        }
    }
	public function toutiao(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'toutiao', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('设置成功', url('setting/toutiao'));
        }else{
            return $this->fetch();
        }
    }
 	public function card(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'card', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('设置成功', url('setting/card'));
        }else{
            return $this->fetch();
        }
    }
    public function dianshang(){
        if(request()->post()){
            $data = input('data/a', false);
            $data = serialize($data);
            model('Setting')->update(array('k' => 'dianshang', 'v' => $data));
            model('Setting')->cleanCache();
            $this->jinMsg('设置成功', url('setting/dianshang'));
        }else{
            return $this->fetch();
        }
    }

}
