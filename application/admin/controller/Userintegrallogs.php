<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;

class Userintegrallogs extends Base{

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
				$map['integral'] = array('gt',0);
			}elseif($types == 2){
				$map['integral'] = array('lt',0);
			}
            $this->assign('types', $types);
        }

		$order = input('order','','htmlspecialchars');
        $orderby = '';
        switch ($order){
            case '2':
                $orderby = array('integral' => 'asc');
                break;
            case '1':
                $orderby = array('integral' => 'desc');
                break;
            default:
                $orderby = array('log_id' => 'desc');
                break;
        }
        $this->assign('order', $order);


        $count = Db::name('user_integral_logs')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('user_integral_logs')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach($list as $k => $val){
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
		session('integral_logs_map',$map);
		session('integral_logs_orderby',$orderby);
		$this->assign('integral',$integral = Db::name('user_integral_logs')->where($map)->sum('integral'));
		$this->assign('count',$count);
        return $this->fetch();
    }



	public function monitor(){
		$map = array('type'=>1);

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
        $count = Db::name('user_integral_monitor_logs')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('user_integral_monitor_logs')->where($map)->order(array('monitor_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach($list as $k => $val){
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }


	public function update(){
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
        $count = Db::name('user_integral_update_logs')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('user_integral_update_logs')->where($map)->order(array('update_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach($list as $k => $val){
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }


	//列表导出
    public function export(){
        $arr = Db::name('user_integral_logs')->where($_SESSION['integral_logs_map'])->order($_SESSION['integral_logs_orderby'])->select();
        $date = date("Y_m_d", time());
        $filetitle = "积分日志列表";
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
			'jj' => '积分数量',
			'kk' => '积分类型',
			'll' => '积分说明'
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
				'jj' => '积分数量',
				'kk' => '积分类型',
				'll' => '积分说明'
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
            $arr[$k]['jj'] = $v['integral'];
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

		session('integral_logs_map',null);
		session('integral_logs_orderby',null);

        echo $html;
        exit;
    }

}
