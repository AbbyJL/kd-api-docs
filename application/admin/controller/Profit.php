<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Profit extends Base{
	
	public function _initialize(){
        parent::_initialize();
		$this->assign('ranks', model('UserRank')->fetchAll());
    }
	 //分销订单
     public function order(){
		$map = array();
        if($id = (int) input('id')){
            $map['id|order_id'] = $id;
            $this->assign('id', $id);
        }
		
		$getSearchDate = $this->getSearchDate();
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		
		if($user_id = (int) input('user_id')){
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
		
        if($type = input('type')){
            if($type != 999){
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        }else{
            $this->assign('type', 999);
        }
		
		if($status = input('status')){
            if($status != 999){
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
        }
		
        $count = Db::name('user_profit_logs')->where($map)->count(); 
        $Page = new \Page($count, 25); 
        $show = $Page->show(); 
        $list = Db::name('user_profit_logs')->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
            $list[$k]['users'] =  Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
			$list[$k]['parent'] =  Db::name('users')->where(array('user_id'=>$val['parent_id']))->find();
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        return $this->fetch(); 
    }
	
	
	//分销商图标统计
 	public function distributorstatistics(){
		$this->assign('count_mobile',$count_mobile = Db::name('users')->where(array('mobile'=>array('neq','')))->count());
		$this->assign('count_mail',$count_mail = Db::name('users')->where(array('email'=>array('neq','')))->count());
		$this->assign('count_weixin',$count_weixin = Db::name('connect')->where(array('type'=>'weixin'))->count());
		$this->assign('count_weibo',$count_weibo = Db::name('connect')->where(array('type'=>'weibo'))->count());
		$this->assign('count_qq',$count_qq = Db::name('connect')->where(array('type'=>'qq'))->count());
		return $this->fetch(); 
	}

	
	
	//分销改动记录
	public function update(){
		$map = array();
		
		$keyword = input('keyword','', 'htmlspecialchars');
        if($keyword){
            $map['info'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		$getSearchDate = $this->getSearchDate();
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		if($user_id = (int) input('user_id')){
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        $count = Db::name('user_profit_update_logs')->where($map)->count(); 
        $Page = new \Page($count, 25); 
        $show = $Page->show(); 
        $list = Db::name('user_profit_update_logs')->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
            $list[$k]['users'] =  Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
			$list[$k]['old'] =  Db::name('users')->where(array('user_id'=>$val['old_pid']))->find();
			$list[$k]['new'] =  Db::name('users')->where(array('user_id'=>$val['new_pid']))->find();
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        return $this->fetch(); 
    }
	
	 public function delete($log_id = 0){
        if($log_id = (int) $log_id){
			$logs = Db::name('user_profit_logs')->where(array('log_id'=>$log_id))->find();
			if($logs['is_separate'] != 0){
				$this->jinMsg('状态不正确');
			}
            $r = Db::name('user_profit_logs')->where(array('log_id'=>$log_id))->delete();
			if($r){
				$this->jinMsg('操作成功', url('profit/order'));
			}
            $this->jinMsg('操作失败');
        }else{
            $this->jinMsg('操作失败');
        }
    }
	public function queren($log_id = 0){
        if($log_id = (int) $log_id){
			$v2 = Db::name('user_profit_logs')->where(array('log_id'=>$log_id))->find();
			if($v2['is_separate'] != 0){
				$this->jinMsg('分成状态不正确');
			}
			$v = Db::name('express_order')->where(array('id'=>$v2['order_id']))->find(); 
			if($v['orderStatus']==0){
				$this->jinMsg('订单状态【'.$v['orderStatus'].'】不正确');
			}
			if($v['orderStatus']==1){
				$this->jinMsg('订单状态【'.$v['orderStatus'].'】不正确');
			}
			if($v['orderStatus']==5){
				$this->jinMsg('订单状态【'.$v['orderStatus'].'】不正确');
			}
			if($v['orderStatus']==9){
				$this->jinMsg('订单状态【'.$v['orderStatus'].'】不正确');
			}
			if($v['orderStatus']==-1){
				$this->jinMsg('订单状态【'.$v['orderStatus'].'】不正确');
			}
			$r = Db::name('user_profit_logs')->where(array('log_id'=>$v2['log_id']))->update(array('complete_time'=>time(),'is_separate'=>1));
			model('Users')->addMoney($v2['user_id'],$v2['money'],$v2['info'],4,$v2['order_id'],'profit');
			model('WeixinTmpl')->getWeixinTmplSend(array(),$v2['user_id'],$title = '收益到账通知',$type='订单完成',$v2['money']);  
			if($r){
				$this->jinMsg('操作成功', url('profit/order'));
			}
            $this->jinMsg('操作失败');
        }else{
            $this->jinMsg('操作失败');
        }
    }
	
	

}

