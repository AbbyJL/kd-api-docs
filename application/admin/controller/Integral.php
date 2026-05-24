<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Integral extends Base{


	private $library_create_fields = array('user_id', 'integral_library', 'integral_library_surplus', 'integral_library_total', 'intro');
    private $library_edit_fields = array('user_id', 'integral_library', 'integral_library_surplus', 'integral_library_total', 'intro');


    public function library(){
        $obj = model('UserIntegralLibrary');
		$map = array('closed'=>0);

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

        $count = $obj->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('library_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val){
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }



	//添加
	public function library_create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false), $this->library_create_fields);
			$data['user_id'] = (int) $data['user_id'];
			if(empty($data['user_id'])){
				$this->jinMsg('用户不能为空');
			}
			$data['integral_library'] = (int) $data['integral_library'];
			if(empty($data['integral_library'])){
				$this->jinMsg('积分库不能为空');
			}
			$data['integral_library_surplus'] = (int) $data['integral_library'];//剩余积分库相同

			$data['integral_library_total'] = (int) $data['integral_library_total'];
			if(empty($data['integral_library_total'])){
				$this->jinMsg('返还总天数不能为空');
			}
			$data['integral_library_day'] = round(($data['integral_library']/$data['integral_library_total']),2);//剩余积分库相同
			if(($data['integral_library_day']*$data['integral_library_total']) != $data['integral_library']){
				$this->jinMsg('填写的积分总数除以天数不为整数');
			}
			$data['intro'] = htmlspecialchars($data['intro']);
			if(empty($data['intro'])){
				$this->jinMsg('活动简介不能为空');
			}
			if($words = model('SensitiveWords')->checkWords($data['intro'])){
				$this->jinMsg('活动简介含有敏感词：' . $words);
			}
			$data['create_time'] = time();
			$data['create_ip'] = request()->ip();

            $obj = model('UserIntegralLibrary');
            if ($obj->insert($data)){
                $this->jinMsg('添加成功', url('integral/library'));
            }
            $this->jinMsg('操作失败！');
        }else{
            return $this->fetch();
        }
    }





    public function library_edit($library_id = 0){
        if($library_id = (int) $library_id){
            $obj = model('UserIntegralLibrary');
            if(!($detail = $obj->find($library_id))){
                $this->error('请选择要编辑的活动');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->library_edit_fields);
				 $data['user_id'] = (int) $data['user_id'];
				if(empty($data['user_id'])){
					$this->jinMsg('用户不能为空');
				}
				$data['integral_library'] = (int) $data['integral_library'];
				if(empty($data['integral_library'])){
					$this->jinMsg('积分库不能为空');
				}
				$data['integral_library_surplus'] = (int) $data['integral_library'];//剩余积分库相同

				$data['integral_library_total'] = (int) $data['integral_library_total'];
				if(empty($data['integral_library_total'])){
					$this->jinMsg('返还总天数不能为空');
				}
				$data['integral_library_day'] = round(($data['integral_library']/$data['integral_library_total']),2);//剩余积分库相同
				if(($data['integral_library_day']*$data['integral_library_total']) != $data['integral_library']){
					$this->jinMsg('填写的积分总数除以天数不为整数');
				}
				$data['intro'] = htmlspecialchars($data['intro']);
				if(empty($data['intro'])){
					$this->jinMsg('活动简介不能为空');
				}
				if($words = model('SensitiveWords')->checkWords($data['intro'])){
					$this->jinMsg('活动简介含有敏感词：' . $words);
				}
				$data['create_time'] = time();
				$data['create_ip'] = request()->ip();


                $data['library_id'] = $library_id;
                if (false !== $obj->update($data)){
                    $this->jinMsg('操作成功', url('integral/library'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('user', model('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的活动');
        }
    }




  public function library_delete($library_id = 0){
        $library_id = (int) $library_id;
		if($library_id){
            $obj = model('UserIntegralLibrary');
			if($detail = $obj->find($library_id)){
				if($detail['integral_library_total_success'] != 0){
					$this->jinMsg('积分已经开始返还不能再删除');
				}
				if($obj->update(array('library_id' => $library_id, 'closed' => 1))){
					$this->jinMsg('删除成功！', url('integral/library'));
				}else{
					$this->jinMsg('删除失败');
				}
			}else{
				$this->jinMsg('没有找到积分库');
			}
        }else{
            $this->jinMsg('请选择要删除的活动');
        }
    }



	//返还列表
	 public function restore($library_id = 0){
		$library_id = (int) $library_id;
        $obj = model('UserIntegralRestore');
		$map = array();

        $getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}

		if($library_id){
            $library = model('UserIntegralLibrary')->find($library_id);
            $this->assign('library', $library);
            $map['library_id'] = $library_id;
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
        $count = $obj->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('restore_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach($list as $k => $val){
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }

	 //商家核销积分列表
	 public function cancel(){
        $obj = model('UserIntegralCancel');
		$map = array();

        $getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}

		
        if ($user_id = (int) input('user_id')) {
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if ($keyword = input('keyword','', 'htmlspecialchars')) {
            $map['intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('cancel_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
       
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }




	//兑换码列表
	public function code(){
        $map = array();
		$keyword = input('keyword','','htmlspecialchars');
        if($keyword){
            $map['card_num|intro'] = array('LIKE', '%' . $keyword . '%');
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

		if($status = (int) input('status')){
            if($status != 999){
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
        }

        $count = Db::name('integral_exchange_code')->where($map)->count();

        $Page = new \Page($count,30);
        $show = $Page->show();
        $list = Db::name('integral_exchange_code')->where($map)->order(array('code_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
             if($Users = Db::name('users')->where(array('user_id'=>$val['user_id']))->find()){
                $list[$k]['user'] = $Users;
             }
        }

		$this->assign('count', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('p', $p = input('p'));

		session('integral_exchange_code_list',$map);

        return $this->fetch();
    }






	//兑换码导出
    public function codeExport($admin_id = 0,$value = 0){
		$admin_id = (int) $admin_id;
        $list = Db::name('integral_exchange_code')->where($_SESSION['integral_exchange_code_list'])->order(array('code_id'=>'desc'))->select();
        $date = date("Y_m_d",time());
        $filetitle = "兑换码列表";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";

        $filter = array(
			'aa' => 'ID',
			'bb' => '会员ID',
			'cc' => '卡号',
			'ee' => '过期时间',
			'gg' => '金额',
			'mm' => '备注',
		);
        foreach($filter as $key => $title){
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach($list as $k => $v){

            if($v['status'] == 0){
                $statusName = '未激活';
            }elseif($v['status'] == 1){
                $statusName = '已激活';
            }else{
                $statusName = '未知状态';
            }

            $filter = array(
				'aa' => 'ID',
				'bb' => '会员ID',
				'cc' => '卡号',
				'dd' => '密码',
				'ee' => '过期时间',
				'ff' => '金额',
				'gg' => '金额',
				'mm' => '备注',
			);

            $list[$k]['aa'] = $v['card_id'];
            $list[$k]['bb'] = $v['user_id'];
            $list[$k]['cc'] = $v['card_num'];
            $list[$k]['ee'] = $v['end_date'];
			$list[$k]['gg'] = $statusName;
			$list[$k]['mm'] = $v['intro'];

            foreach($filter as $key => $title){
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
        echo $html;
        exit;
    }




    public function codeCreate(){


        if(request()->post()){


			$end_date = input('end_date','','htmlspecialchars');
			if(!isDate($end_date)){
				$this->jinMsg('兑换码有效期不正确');
			}

			$num = input('num') ? input('num') : 1;

            if(empty($num)){
                $this->jinMsg('数量不能为空');
            }

			if($num > 500){
                $this->jinMsg('单次生成不能超过500');
            }


			$card_num = input('card_num','','htmlspecialchars');


			$intro = input('intro','','htmlspecialchars');
			if(empty($intro)){
                $this->jinMsg('备注不能为空');
            }
			if(strlen($intro) > 20){
				$this->jinMsg('备注不能大于10个字');
			}


			$i = 0;
			for($x=0; $x<=$num; $x++){
				$i++;
				$data = array(
					'card_num' => rand_string(10,1),
					'end_date' => $end_date,
					'status' => 0, //0未激活1已激活
					'intro' => $intro,
					'end_time' => strtotime($end_date),
					'create_time' => time(),
					'create_ip' => get_client_ip()
				);
				Db::name('integral_exchange_code')->insert($data);
			}

			if($i){
				$this->jinMsg('录入兑换码成功【'.$i.'】条',url('integral/code',array('p'=>$p)));
			}else{
				$this->jinMsg('录入兑换码失败');
			}

        }else{
            return $this->fetch();
        }
    }




    public function codeDelete($code_id = 0,$p = 0){
        if(is_numeric($code_id) && ($code_id = (int) $code_id)){
            Db::name('integral_exchange_code')->where(array('code_id'=>$code_id))->delete();
            $this->jinMsg('删除成功', url('integral/code',array('p'=>$p)));
        }else{
            $code_id = input('code_id/a',false);
            if(is_array($code_id)){
                foreach($code_id as $id){
                    Db::name('integral_exchange_code')->where(array('code_id'=>$id))->delete();
                }
                $this->jinMsg('删除成功',url('integral/code',array('p'=>$p)));
            }
            $this->jinMsg('请选择要删除的兑换码');
        }
    }



	//抽奖项目列表
	public function project(){
        $map = array();
		$keyword = input('keyword','','htmlspecialchars');
        if($keyword){
            $map['name|intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $getSearchDate = $this->getSearchDate();
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		if($status = (int) input('status')){
            if($status != 999){
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
        }

        $count = Db::name('integral_exchange_project')->where($map)->count();

        $Page = new \Page($count,30);
        $show = $Page->show();
        $list = Db::name('integral_exchange_project')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

		$this->assign('count', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('p', $p = input('p'));
        return $this->fetch();
    }


	 public function projectEdit($id = 0){

		$detail = Db::name('integral_exchange_project')->find($id);
		$this->assign('detail', $detail);
		$this->assign('id', $id);

        if(request()->post()){


			$data['id'] = (int)input('id');

			$data['name'] = input('name','','htmlspecialchars');
			if(empty($data['name'])){
				$this->jinMsg('项目名称不能为空');
			}


			$data['name_1'] = input('name_1','','htmlspecialchars');
			if(empty($data['name_1'])){
				$this->jinMsg('奖品1名称不能为空');
			}
			$data['name_2'] = input('name_2','','htmlspecialchars');
			$data['name_3'] = input('name_3','','htmlspecialchars');


			$data['num_1'] = (int)input('num_1');
			if(empty($data['num_1'])){
				$this->jinMsg('奖品1数量不能为空');
			}
			$data['num_2'] = (int)input('num_2');
			$data['num_3'] = (int)input('num_3');

			$data['rate_1'] = (int)input('rate_1');
			if(empty($data['rate_1'])){
				$this->jinMsg('奖品1中奖率不能为空');
			}
			$data['rate_2'] = (int)input('rate_2');
			$data['rate_3'] = (int)input('rate_3');

			$data['end_date'] = input('end_date','','htmlspecialchars');


		    if($data['id'] > 0){
			    $res = Db::name('integral_exchange_project')->update($data);
				$info = '更新项目成功';
			}else{
				$data['create_time'] = time();
				$res = Db::name('integral_exchange_project')->insert($data);
				$info = '添加项目成功';
		    }

			if($res){
				$this->jinMsg($info,url('integral/project',array('p'=>$p)));
			}else{
				$this->jinMsg('操作失败');
			}

        }else{


            return $this->fetch();
        }
    }


	//抽奖人员列表
	public function order(){
        $map = array();
		$keyword = input('keyword','','htmlspecialchars');
        if($keyword){
            $map['order_id|name|mobile|code'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $getSearchDate = $this->getSearchDate();
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}

		if($id = (int) input('id')){
            $map['id'] = $id;
            $this->assign('id', $id);
        }

		if($status = (int) input('status')){
            if($status != 999){
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
        }

        $count = Db::name('integral_exchange_order')->where($map)->count();

        $Page = new \Page($count,30);
        $show = $Page->show();
        $list = Db::name('integral_exchange_order')->where($map)->order(array('order_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
     	foreach($list as $key => $val){
			$list[$key]['project'] = Db::name('integral_exchange_project')->where(array('id'=>$val['id']))->find();
			$list[$key]['codes'] = Db::name('integral_exchange_code')->where(array('code_id'=>$val['code_id']))->find();
        }

        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('p', $p = input('p'));
        return $this->fetch();
    }






	//兑换订单删除功能
    public function orderDelete($order_id = 0,$p = 0){
        if($order_id){
			$order = Db::name('integral_exchange_order')->where(array('order_id'=>$order_id))->find();
			if($order['status']  == 1 ){
				$this->jinMsg('当前订单已中奖，不能删除');
			}
            $res = Db::name('integral_exchange_order')->where(array('order_id'=>$order_id))->delete();
			if($res){
				$this->jinMsg('删除成功', url('integral/order',array('p'=>$p)));
			}
			$this->jinMsg('删除失败');
        }else{
            $this->jinMsg('请选择要删除的兑换码');
        }
    }

	//兑换订单设置已中奖功能
    public function orderDraw($order_id = 0,$p = 0){
        if($order_id){
			$order = Db::name('integral_exchange_order')->where(array('order_id'=>$order_id))->find();
			if($order['status']  == 1 ){
				$this->jinMsg('当前订单已中奖，不能设置');
			}
            $res = Db::name('integral_exchange_order')->where(array('order_id'=>$order_id))->update(array('status'=>1));
			if($res){
				$this->jinMsg('操作成功', url('integral/order',array('p'=>$p)));
			}
			$this->jinMsg('操作失败');
        }else{
            $this->jinMsg('请选择要操作的兑换码');
        }
    }


}
