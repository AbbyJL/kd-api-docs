<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Delivery extends Base{
	
	 public function _initialize(){
        parent::_initialize();
        $this->assign('getTypes', $getTypes = model('Delivery')->getTypes());
		$this->assign('getStatus', $getStatus = model('DeliveryOrder')->getStatus());
    }

	
	public function order(){
        $map = array('closed'=>0);
        $id = (int)input('id','', 'trim,htmlspecialchars');
        if($id){
            $map['id'] = $id;
            $this->assign('id', $id);
        }
        if($user_id = (int) input('user_id')){
            $map['user_id'] = $user_id;
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		$getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		$input = input('post.');
		$status= input('status');
		if($status != NUll && $status != 999){
			$map['status'] = $status;
		}
		if(isset($input['status']) || isset($input['status'])){
			$status = $input['status'];
		}else{
			$status = $status?$status:'999';
		}
		$this->assign('status',$status);
        $count = Db::name('delivery_order')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('delivery_order')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list',$list);
        $this->assign('page',$show);
		$this->assign('count',$count);
        return $this->fetch();
    }
	
	

	public function detail(){
		$id = input('id','','trim,htmlspecialchars');
		$v = Db::name('delivery_order')->where(array('id'=>$id))->find();
		$this->assign('var',$v);
		$this->assign('detail',$v);
        $this->assign('payment_logs',$payment_logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'transport'))->find());
		return $this->fetch();
	}
	
	
	public function delete($id = 0){
        $id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($sign = Db::name('delivery_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		if($sign['status'] != 0){
			return json(array('code'=>'0','msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		$r = Db::name('delivery_order')->where(array('id'=>$id))->delete();
		if($r){
			$this->jinMsg('操作成功', url('delivery/order'));
		}else{
			$this->jinMsg('操作失败');
		}
    }
	
	

	public function deliver($id = 0){
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($sign = Db::name('delivery_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		if($sign['status'] != 1){
			return json(array('code'=>'0','msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		$r = Db::name('delivery_order')->where(array('id'=>$id))->update(array('status'=>2));
		if($r){
			$this->jinMsg('操作成功', url('delivery/order'));
		}else{
			$this->jinMsg('操作失败');
		}
    }
	
	
	

	public function cancel($id = 0){
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($sign = Db::name('delivery_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		if($sign['status'] == 0){
			$this->jinMsg('未付款订单不知此退款');
		}
		$r = Db::name('delivery_order')->where(array('id'=>$id))->update(array('status'=>5));
		if($r){
			$this->jinMsg('操作成功', url('delivery/order'));
		}else{
			$this->jinMsg('操作失败');
		}
    }


    public function refund($id = 0){
        $id = (int) $id;
        if(!$id){
            $this->jinMsg('id不存在');
        }
        if(!($sign = Db::name('delivery_order')->where(array('id'=>$id))->find())){
            $this->jinMsg('订单不存在');
        }
        if($sign['status'] == 0){
            $this->jinMsg('未付款订单不知此退款');
        }
        $logs = Db::name('payment_logs')->where(array('order_id'=>$id,'type'=>'transport','is_paid'=>1))->find();
        if($logs){
            $orderWeixinRefund = model('PaymentLogs')->orderWeixinRefund($sign,$logs['need_pay'],'订单'.$sign['id'].'退款',$logs);
            if($orderWeixinRefund == false){
                $this->jinMsg('原路退款异常'.$id.'【'.model('PaymentLogs')->getError().'】请稍后再试');
            }
        }
        $r = Db::name('delivery_order')->where(array('id'=>$id))->update(array('status'=>5));
        if($r){
            $this->jinMsg('操作成功', url('delivery/order'));
        }else{
            $this->jinMsg('操作失败');
        }
    }
	
	

	public function complete($id = 0){
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($sign = Db::name('delivery_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		if($sign['status'] != 2){
			$this->jinMsg('订单状态【'.$sign['orderStatus'].'】不正确');
		}
		$r = Db::name('delivery_order')->where(array('id'=>$id))->update(array('status'=>4));
		if($r){
			$this->jinMsg('操作成功', url('delivery/order'));
		}else{
			$this->jinMsg('操作失败');
		}
    }



	
	
}