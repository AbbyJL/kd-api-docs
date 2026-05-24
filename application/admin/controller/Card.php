<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Card extends Base{

 
	
	public function _initialize(){
        parent::_initialize();
		$this->assign('getMoneysTypes',model('Users')->getMoneysTypes());
    }

	
	
	//兑换券列表
	public function codes($pid = 0){
		$map = array('type'=>1);
		if($keyword = input('keyword','', 'htmlspecialchars')){
			$map['intro|code|title'] = array('LIKE', '%' . $keyword . '%');
			$this->assign('keyword', $keyword);
		}
		if($pid = (int) input('pid')){
			$map['pid'] = $pid;
			$this->assign('pid',$pid);
		}
		if($state = (int) input('state')){
			$map['state'] = $state;
			$this->assign('state', $state);
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
		$count = Db::name('card_codes')->where($map)->count();
		$Page = new \Page($count,15);
		$page = $Page->show();
		$list = Db::name('card_codes')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
           $list[$k]['users'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
		   $list[$k]['pusers'] = Db::name('users')->where(array('user_id'=>$val['pid']))->find();
        }
		$this->assign('list', $list);
		$this->assign('count',$count);
		$this->assign('page', $page);
		session('code_index_list', $map);
		return $this->fetch();
    }
	
	
	
	//导出
    public function codes_export($admin_id = 0,$value = 0){
		$map = session('codes_index_list');
        $list = Db::name('card_codes')->where($map)->order(array('id'=>'desc'))->limit(0,2000)->select();
		foreach($list as $k => $val){
           $list[$k]['users'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
           $$list[$k]['pusers'] = Db::name('users')->where(array('user_id'=>$val['pid']))->find();
        }
        $date = date("Y_m_d", time());
        $filetitle = "导出";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array(
			'aa' => 'ID',
			'bb' => '卡号',
			'cc' => '密码',
			'dd' => '标题',
			'ee' => '金额',
			'ff' => '代理人ID',
			'gg' => '兑换状态',
			'hh' => '兑换人信息',
			'ii' => '卡密兑换时间',
			'jj' => '卡密生成时间',
			'kk' => '过期时间'
			
			
		);
        foreach($filter as $key => $title){
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach($list as $k => $v){
            $filter = array(
				'aa' => 'ID',
				'bb' => '卡号',
				'cc' => '密码',
				'dd' => '标题',
				'ee' => '金额',
				'ff' => '代理人ID',
				'gg' => '兑换状态',
				'hh' => '兑换人信息',
				'ii' => '卡密兑换时间',
				'jj' => '卡密生成时间',
				'kk' => '过期时间'
			);
            $list[$k]['aa'] = $v['id'];
            $list[$k]['bb'] = $v['code'];
			$list[$k]['cc'] = $v['password'];
			$list[$k]['dd'] = $v['title'];
			$list[$k]['ee'] = round($v['moneys']/100,2);
			$list[$k]['ff'] = $v['pid'];
			$list[$k]['gg'] = $v['state']==1?'未兑换':'已兑换';
            $list[$k]['hh'] = $v['users']['user_id'].'/'.$v['users']['nickname'];
            $list[$k]['ii'] = date('Y-M-D h:i:s',$v['exchange_time']);
            $list[$k]['jj'] = date('Y-M-D h:i:s',$v['create_time']);
			$list[$k]['ff'] = $v['expire_date'];
            foreach ($filter as $key => $title) {
                $html .= $list[$k][$key] . "\t,";
            }
            $html .= "\n";
        }
        ob_end_clean();
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename={$fileName}.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
		session('codes_index_list', null);
        echo $html;
        exit;
    }
	
	//删除
	public function CodesDelete($id = 0,$coupon_id=0){
        if(is_numeric($id) && ($id = (int) $id)){
            Db::name('card_codes')->where(array('id' => $id))->delete();
            $this->jinMsg('操作成功', url('card/codes',array('coupon_id'=>$coupon_id)));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach($ids as $id){
                    Db::name('card_codes')->where(array('id' => $id))->delete();
                }
                $this->jinMsg('操作成功', url('card/codes',array('coupon_id'=>$coupon_id)));
            }
            $this->jinMsg('请选择要删除的');
        }
    }
	
	
	//添加
	public function createCodes($pid = 0){
        $pid = (int) $pid;
		if(request()->post()){
			$data = $this->checkFields(input('data/a', false),array('num','pid','moneys','title','expire_date','intro'));
			$data['pid'] = (int)$data['pid'];
			if(empty($data['pid'])){
				$this->jinMsg('代理ID不能为空');
			}
			$pusers = Db::name('users')->where(array('user_id'=>$data['pid']))->find();
			if(empty($pusers)){
				$this->jinMsg('代理人会员信息错误');
			}
			
			$data['moneys'] = (int)($data['moneys']*100);
			if(empty($data['moneys'])){
				$this->jinMsg('金额不能为空');
			}
			$data['title'] = htmlspecialchars($data['title']);
			if(empty($data['title'])){
				$this->jinMsg('标题不能为空');
			}
			
			$data['expire_date'] = htmlspecialchars($data['expire_date']);
			if (empty($data['expire_date'])) {
				$this->jinMsg('过期日期不能为空');
			}
			if (!isDate($data['expire_date'])) {
				$this->jinMsg('过期日期格式不正确');
			}
			
			$data['intro'] = htmlspecialchars($data['intro']);
			if(empty($data['intro'])){
				$this->jinMsg('描述不能为空');
			}
			
			$data['type'] =1;
			$data['state'] =1;
			$data['create_time'] = time();
			$num = (int)$data['num'];
			if($num < 1){
				$this->jinMsg('请填写正确的数量');
			}
			if($num > 1000){
				$this->jinMsg('一次最多生成1000张兑换卡');
			}
			$i = 0;
			for($k=1; $k<=$num; $k++ ){
				$i++;
				$data['code'] =  rand_string(10,1);
				$data['password'] =  rand_string(6,1);
				Db::name('card_codes')->insert($data);
			}
			if($i){
				$this->jinMsg('添加成功【'.$i.'】张兑换卡',url('card/codes'));
			}else{
				$this->jinMsg('操作失败');
			}
		}else{
			$this->assign('detail', $detail);
			return $this->fetch();
		}
    }
	
	 public function logs(){
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
        if($keyword = input('keyword','', 'htmlspecialchars')){
            $map['intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		if($type = (int) input('type')){
            if($type != 999){
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        }else{
            $this->assign('type', 999);
        }
		
		
		$order = input('order','','htmlspecialchars');
        $orderby = '';
        switch ($order){
            case '2':
                $orderby = array('moneys' => 'asc');
                break;
            case '1':
                $orderby = array('moneys' => 'desc');
                break;
            default:
                $orderby = array('log_id' => 'desc');
                break;
        }
        $this->assign('order', $order);
		
		
        $count = Db::name('user_moneys_logs')->where($map)->count();
        $Page = new \Page($count, 20);
        $show = $Page->show();
		
        $list = Db::name('user_moneys_logs')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
		
		session('moneys_logs_map',$map);
		session('moneys_logs_orderby',$orderby);
		
        return $this->fetch();
	 }


}