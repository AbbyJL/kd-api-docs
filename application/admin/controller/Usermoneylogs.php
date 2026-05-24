<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;

class Usermoneylogs extends Base{
	
	public function _initialize(){
        parent::_initialize();
        $this->assign('getMoneyTypes',model('Users')->getMoneyTypes());
		$this->assign('getIntegralTypes',model('Users')->getIntegralTypes());
    }
	
	
	
    public function index(){
        $map = array();
        $getSearchDate = $this->getSearchDate();//时间搜索
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
		
		
		if($types = (int) input('types')){
            if($types == 1){
				$map['money'] = array('gt',0);
			}elseif($types == 2){
				$map['money'] = array('lt',0);
			}
            $this->assign('types', $types);
        }
		
		$order = input('order','','htmlspecialchars');
        $orderby = '';
        switch ($order){
            case '2':
                $orderby = array('money' => 'asc');
                break;
            case '1':
                $orderby = array('money' => 'desc');
                break;
            default:
                $orderby = array('log_id' => 'desc');
                break;
        }
        $this->assign('order', $order);
		
		
        $count = Db::name('user_money_logs')->where($map)->count();
        $Page = new \Page($count, 20);
        $show = $Page->show();
		
        $list = Db::name('user_money_logs')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
		
		session('money_logs_map',$map);
		session('money_logs_orderby',$orderby);
		
        return $this->fetch();
    }
	
	
	
	public function draw(){
        $map = array();
        $getSearchDate = $this->getSearchDate();//时间搜索
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
		if($types = (int) input('types')){
            if($types == 1){
				$map['money'] = array('gt',0);
			}elseif($types == 2){
				$map['money'] = array('lt',0);
			}
            $this->assign('types', $types);
        }
		$order = input('order','','htmlspecialchars');
        $orderby = '';
        switch ($order){
            case '2':
                $orderby = array('money' => 'asc');
                break;
            case '1':
                $orderby = array('money' => 'desc');
                break;
            default:
                $orderby = array('log_id' => 'desc');
                break;
        }
        $this->assign('order', $order);
        $count = Db::name('user_money_draw')->where($map)->count();
        $Page = new \Page($count, 20);
        $show = $Page->show();
        $list = Db::name('user_money_draw')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	public function stock(){
        $map = array();
        $getSearchDate = $this->getSearchDate();//时间搜索
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
		if($types = (int) input('types')){
            if($types == 1){
				$map['money'] = array('gt',0);
			}elseif($types == 2){
				$map['money'] = array('lt',0);
			}
            $this->assign('types', $types);
        }
		$order = input('order','','htmlspecialchars');
        $orderby = '';
        switch ($order){
            case '2':
                $orderby = array('money' => 'asc');
                break;
            case '1':
                $orderby = array('money' => 'desc');
                break;
            default:
                $orderby = array('log_id' => 'desc');
                break;
        }
        $this->assign('order', $order);
        $count = Db::name('user_stock_logs')->where($map)->count();
        $Page = new \Page($count, 20);
        $show = $Page->show();
        $list = Db::name('user_stock_logs')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	
	public function edu(){
        $map = array();
        $getSearchDate = $this->getSearchDate();//时间搜索
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
		if($types = (int) input('types')){
            if($types == 1){
				$map['money'] = array('gt',0);
			}elseif($types == 2){
				$map['money'] = array('lt',0);
			}
            $this->assign('types', $types);
        }
		$order = input('order','','htmlspecialchars');
        $orderby = '';
        switch ($order){
            case '2':
                $orderby = array('money' => 'asc');
                break;
            case '1':
                $orderby = array('money' => 'desc');
                break;
            default:
                $orderby = array('log_id' => 'desc');
                break;
        }
        $this->assign('order', $order);
        $count = Db::name('user_edu')->where($map)->count();
        $Page = new \Page($count, 20);
        $show = $Page->show();
        $list = Db::name('user_edu')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	//列表导出
    public function export(){
        $arr = Db::name('user_money_logs')->where($_SESSION['money_logs_map'])->order($_SESSION['money_logs_orderby'])->select();
        $date = date("Y_m_d", time());
        $filetitle = "余额日志列表";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array(
			'aa' => '日志编号', 
			'bb' => '年', 
			'cc' => '月', 
			'dd' => '日', 
			'ee' => '日志生成时间', 
			'ff' => '会员ID', 
			'gg' => '会员姓名', 
			'hh' => '会员手机', 
			'ii' => '会员邮箱', 
			'jj' => '余额数量', 
			'kk' => '余额类型', 
			'll' => '余额说明' 
		);
        foreach ($filter as $key => $title){
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach($arr as $k => $v){
            $Users = Db::name('users')->find($v['user_id']);
            $createTime = date('H:i:s', $v['create_time']);
            $createTimeYear = date('Y', $v['create_time']);
            $createTimeMonth = date('m', $v['create_time']);
            $createTimeDay = date('d', $v['create_time']);
            $filter = array(
				'aa' => '日志编号', 
				'bb' => '年', 
				'cc' => '月', 
				'dd' => '日', 
				'ee' => '日志生成时间', 
				'ff' => '会员ID', 
				'gg' => '会员姓名', 
				'hh' => '会员手机', 
				'ii' => '会员邮箱', 
				'jj' => '余额数量', 
				'kk' => '余额类型', 
				'll' => '余额说明' 
			);
            $arr[$k]['aa'] = $v['log_id'];
            $arr[$k]['bb'] = $createTimeYear;
            $arr[$k]['cc'] = $createTimeMonth;
            $arr[$k]['dd'] = $createTimeDay;
            $arr[$k]['ee'] = $createTime;
            $arr[$k]['ff'] = $v['user_id'];
            $arr[$k]['gg'] = $Users['nickname'];
            $arr[$k]['hh'] = $Users['mobile'];
            $arr[$k]['ii'] = $Users['email'];
            $arr[$k]['jj'] = round($v['money']/100,2);
            $arr[$k]['kk'] = '暂无';
            $arr[$k]['ll'] = $v['intro'];
            foreach ($filter as $key => $title) {
                $html .= $arr[$k][$key] . "\t,";
            }
            $html .= "\n";
        }
        ob_end_clean();
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename={$fileName}.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
		session('money_logs_map',null);
		session('money_logs_orderby',null);
        echo $html;
        exit;
    }
	
	
}