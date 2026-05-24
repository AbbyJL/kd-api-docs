<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;

class Userrank extends Base{
	
	
    private $create_fields = array('rank_name','number','number2','discount','reward','icon','icon1','integral','is_buy','is_prestige','prestige','rebate', 'price','rate1','rate2','rate3','total','photo','day','total','total1','xieyi');
    private $edit_fields = array('rank_name','number','number2','iscount','reward','icon','icon1','integral','is_buy','is_prestige','prestige','rebate','price','rate1','rate2','rate3','total','photo','day','total','total1','xieyi');
	
	
    public function index(){
		$map = array();
        $count = Db::name('user_rank')->where($map)->count();
        $Page = new \Page($count, 15);
        $show = $Page->show();
        $list = Db::name('user_rank')->where($map)->order(array('rank_id'=>'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k =>$val){
			$list[$k]['count'] = Db::name('users')->where(array('rank_id'=>$val['rank_id'],'closed'=>'0'))->count();
		}
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	 public function logs(){
		$map = array();
        $count = Db::name('user_rank_logs')->where($map)->count();
        $Page = new \Page($count, 15);
        $show = $Page->show();
        $list = Db::name('user_rank_logs')->where($map)->order(array('log_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
    public function create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['rank_name'] = htmlspecialchars($data['rank_name']);
			if(empty($data['rank_name'])){
				$this->jinMsg('等级名称不能为空');
			}
			$data['number'] = htmlspecialchars($data['number']);
			$data['discount'] = htmlspecialchars($data['discount']);
			$data['reward'] = htmlspecialchars($data['reward']);
			$data['integral'] = (int) $data['integral'];
			$data['prestige'] = (int) $data['prestige'];
			$data['photo'] = htmlspecialchars($data['photo']);
			if(empty($data['photo'])){
				$this->jinMsg('请上传缩略图');
			}
			if(!isImage($data['photo'])){
				$this->jinMsg('缩略图格式不正确');
			}
			
			$data['total'] = (int) ($data['total']*100);
			$data['total1'] = (int) ($data['total1']*100);
			$data['price'] = (int) ($data['price']*100); 
			$data['rate1'] = (int) ($data['rate1']);
			$data['rate2'] = (int) ($data['rate2']);
			$data['rate3'] = (int) ($data['rate3']);

			
            if(Db::name('user_rank')->insert($data)){
                $this->jinMsg('添加成功', url('userrank/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            echo $this->fetch();
        }
    }
	
	
	
    public function edit($rank_id = 0){
        if($rank_id = (int) $rank_id){
            $obj = model('UserRank');
            if(!($detail = Db::name('user_rank')->find($rank_id))){
                $this->error('请选择要编辑的会员等级');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['rank_name'] = htmlspecialchars($data['rank_name']);
				if(empty($data['rank_name'])){
					$this->jinMsg('等级名称不能为空');
				}
				$data['number'] = htmlspecialchars($data['number']);
				$data['discount'] = htmlspecialchars($data['discount']);
				$data['reward'] = htmlspecialchars($data['reward']);
				$data['integral'] = (int) $data['integral'];
				$data['prestige'] = (int) $data['prestige'];
				$data['photo'] = htmlspecialchars($data['photo']);
				if(empty($data['photo'])){
					$this->jinMsg('请上传缩略图');
				}
				if(!isImage($data['photo'])){
					$this->jinMsg('缩略图格式不正确');
				}
				
				$data['total'] = (int) ($data['total']*100);
			    $data['total1'] = (int) ($data['total1']*100);
				$data['price'] = (int) ($data['price']*100); 
				$data['rate1'] = (int) ($data['rate1']);
				$data['rate2'] = (int) ($data['rate2']);
				$data['rate3'] = (int) ($data['rate3']);

                $data['rank_id'] = $rank_id;
                if (false !== Db::name('user_rank')->update($data)){
                    $this->jinMsg('操作成功', url('userrank/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                echo $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的会员等级');
        }
    }
	
   
    public function delete($rank_id = 0){
		$rank_id = (int) $rank_id;
        if($rank_id){
			$detail = Db::name('users')->where(array('rank_id'=>$rank_id))->find();
			if($detail['user_id']){
				$this->jinMsg('会员ID【'.$detail['user_id'].'】，姓名【'.$detail['nickname'].'】还在使用该等级，暂时无法删除');
			}
			if(Db::name('user_rank')->where('rank_id',$rank_id)->delete()){
            	$this->jinMsg('删除成功', url('userrank/index'));
			}else{
				$this->jinMsg('操作失败');
			}
        }else{
            $this->jinMsg('请选择要删除的会员等级');
        }
    }
	
	
	 public function order(){
		$map = array('type'=>'vip');
        $getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		if($code = input('code','', 'htmlspecialchars')){
            if(!empty($code) && $code !=999 ){
                $map['code'] = $code;
            }
            $this->assign('code', $code);
        }else{
            $this->assign('code', 999);
        }
		
		if($status = input('status','', 'htmlspecialchars')){
            if($status == 1){
                $map['is_paid'] = 1;
            }else{
				$map['is_paid'] = 0;
			}
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
        }
		
		
        if($keyword = input('keyword','', 'htmlspecialchars')){
            $map['log_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if($order_id = input('order_id','', 'htmlspecialchars')){
            $map['order_id'] = array('LIKE', '%' . $order_id . '%');
            $this->assign('order_id', $order_id);
        }
        $count = Db::name('payment_logs')->where($map)->count();
        $Page = new \Page($count, 15);
        $show = $Page->show();
        $list = Db::name('payment_logs')->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
            $list[$k]['type'] = model('PaymentLogs')->get_payment_logs_type($val['type']);
			$list[$k]['user'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
}