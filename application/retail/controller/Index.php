<?php

namespace app\retail\controller;
use think\Db;

use app\common\model\Setting;


class Index extends Base{
	
	
	protected $enter_price = 0;
	
	
	
	public function _initialize(){
        parent::_initialize();
    }
	
	
	
	
    public function index(){
		
        $this->assign('v',date('Y-m-d',time()));
		$bg_time = time() - 86400 * 30;
		$bgtime = strtotime(TODAY);
		$str = '-30 day';
        $str2 = strtotime(date('Y-m-d', strtotime($str)));
		
		
		if($this->role == 0){
			$counts['day_express'] =(int)Db::name('express_order')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime)),'closed'=>0))->count();
			$counts['express'] =(int)Db::name('express_order')->where(array('closed'=>0))->count();
			$counts['users'] = (int) Db::name('users')->count();
			$counts['totay_user'] = (int) Db::name('users')->where(array('reg_time' => array(array('ELT',time()), array('EGT',$bgtime))))->count();
		}
		if($this->role == 1){
			$counts['day_express'] =(int)Db::name('express_order')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime)),'closed'=>0,'pid'=>$this->uid))->count();
			$counts['express'] =(int)Db::name('express_order')->where(array('closed'=>0,'pid'=>$this->uid))->count();
			$counts['users'] = (int) Db::name('users')->where(array('parent_id'=>$this->uid))->count();
			$counts['totay_user'] = (int) Db::name('users')->where(array('reg_time' => array(array('ELT',time()), array('EGT',$bgtime)),'parent_id'=>$this->uid))->count();
		}
		if($this->role == 2){
			$counts['day_express'] =(int)Db::name('express_order')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime)),'closed'=>0,'rank2_uid'=>$this->uid))->count();
			$counts['express'] =(int)Db::name('express_order')->where(array('closed'=>0,'rank2_uid'=>$this->uid))->count();
			$counts['users'] = (int) Db::name('users')->where(array('parent_id'=>$this->uid))->count();
			$counts['totay_user'] = (int) Db::name('users')->where(array('reg_time' => array(array('ELT',time()), array('EGT',$bgtime)),'parent_id'=>$this->uid))->count();
		}
		if($this->role == 3){
			$counts['day_express'] =(int)Db::name('express_order')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime)),'closed'=>0,'rank3_uid'=>$this->uid))->count();
			$counts['express'] =(int)Db::name('express_order')->where(array('closed'=>0,'rank3_uid'=>$this->uid))->count();
			$counts['users'] = (int) Db::name('users')->where(array('parent_id'=>$this->uid))->count();
			$counts['totay_user'] = (int) Db::name('users')->where(array('reg_time' => array(array('ELT',time()), array('EGT',$bgtime)),'parent_id'=>$this->uid))->count();
		}
		$this->assign('counts', $counts);
		
		return $this->fetch();
	}
	
	
	public function set(){
		if(request()->post()){
            $data = $this->checkFields(input('data/a', false), array('face','nickname','ext0'));
			$data['user_id'] = $this->uid;
            $data['face'] = htmlspecialchars($data['face']);
			$data['nickname'] = htmlspecialchars($data['nickname']);
			$data['ext0'] = htmlspecialchars($data['ext0']);
			$data['ext0'] = substr($data['ext0'],14);
            if(false !== Db::name('users')->update($data)){
				return json(array('code'=>'1','msg'=>'操作成功','url'=>url('index/set')));
            }
			return json(array('code'=>'0','msg'=>'操作失败'));
        }else{
            return $this->fetch();
		}
	}
	
	

}