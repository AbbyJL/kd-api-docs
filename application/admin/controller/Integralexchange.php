<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Integralexchange extends Base{
	

    public function index(){
        $map = array();
		
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
		if($goods_id = (int) input('goods_id')){
            $this->assign('goods_id', $goods_id);
            $map['goods_id'] = $goods_id;
        }
        
        if($audit = (int) input('audit')){
            if($audit != 999) {
                $map['audit'] = $audit;
            }
            $this->assign('audit', $audit);
        }else{
            $this->assign('audit', 999);
        }
		
        $count = Db::name('integral_exchange')->where($map)->count();
        $Page = new \Page($count, 15);
        $show = $Page->show();
        $list = Db::name('integral_exchange')->where($map)->order(array('exchange_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = $good_ids = $addr_ids = array();
        foreach($list as $k => $val){
            $user_ids[$val['user_id']] = $val['user_id'];
            $good_ids[$val['goods_id']] = $val['goods_id'];
			$list[$k]['cate'] = Db::name('integral_goods_cate')->where(array('cate_id'=>$val['cate_id']))->find();
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('goods', model('IntegralGoods')->itemsByIds($good_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	

    public function audit($p = 0){
        $exchange_id = (int) input('exchange_id');
        if(empty($exchange_id)){
            $this->error('请选择订单');
        }
        if(!($detail = Db::name('integral_exchange')->find($exchange_id))){
            $this->error('请选择订单1');
        }
        if(request()->post()){
			$name = input('name','','htmlspecialchars');
			$mobile = input('mobile','','htmlspecialchars');
			$addr = input('addr','','htmlspecialchars');
            $audit = (int)input('audit','','htmlspecialchars');
			$status = (int)input('status','','htmlspecialchars');
		   	$expressName = input('expressName','','htmlspecialchars');
			$mailNo = input('mailNo','','htmlspecialchars');
			$data['exchange_id'] = $exchange_id;
			$data['name'] = $name;
			$data['mobile'] = $mobile;
			$data['addr'] = $addr;
			$data['audit'] = $audit;
			$data['status'] = $status;
			$data['expressName'] = $expressName;
			$data['mailNo'] = $mailNo;
			$rest = Db::name('integral_exchange')->update($data);
    		if($rest){
				$this->jinMsg('操作成功', url('integralexchange/index',array('p'=>$p)));
			}
            $this->jinMsg('操作失败');
        }else{
			$this->assign('p', $p);
            $this->assign('detail',$detail);
			$this->assign('exchange_id',$exchange_id);
            echo $this->fetch();
        }
    }

}