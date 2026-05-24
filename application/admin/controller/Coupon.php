<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Coupon extends Base{

    private $create_fields = array('type','shop_id','cate_id','city_id','area_id','title','day', 'photo','price', 'money','integral','appId','path', 'full_price', 'reduce_price', 'expire_date', 'num','number', 'limit_num', 'intro');
    private $edit_fields = array('type','shop_id','cate_id','city_id','area_id','title', 'day','photo','price', 'money','integral','money','appId','path', 'full_price', 'reduce_price', 'expire_date', 'num','number', 'limit_num', 'intro');
	
	public function _initialize(){
        parent::_initialize();
    }

	
	
	
    public function index(){
		$map = array('closed' => 0);
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
        if($audit = (int) input('audit')){
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = Db::name('coupon')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('coupon')->where($map)->order(array('coupon_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
            if($val['shop_id']){
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	
	
	public function select(){
		$map = array('closed' => 0,'audit' =>1, 'expire_date' => array('EGT', TODAY));
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		$getSearchCityId = $this->getSearchCityId($this->city_id);
		if($getSearchCityId){
			$map['city_id'] = $getSearchCityId;
			$this->assign('city_id',$getSearchCityId);
		}
		
        $count = Db::name('coupon')->where($map)->count();
        $Page = new \Page($count,8);
        $show = $Page->show();
        $list = Db::name('coupon')->where($map)->order(array('coupon_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	
    public function create(){
        if(request()->post()){
           $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['shop_id'] = (int) $data['shop_id'];
			$data['cate_id'] = $data['cate_id'];
			$data['city_id'] = $data['city_id'];
		
			
			$data['title'] = htmlspecialchars($data['title']);
			if (empty($data['title'])) {
				$this->jinMsg('标题不能为空');
			}
			$data['photo'] = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
				$this->jinMsg('请上传优惠券图片');
			}
			if (!isImage($data['photo'])) {
				$this->jinMsg('优惠券图片格式不正确');
			}
			$data['expire_date'] = htmlspecialchars($data['expire_date']);
			if (empty($data['expire_date'])) {
				$this->jinMsg('过期日期不能为空');
			}
			if (!isDate($data['expire_date'])) {
				$this->jinMsg('过期日期格式不正确');
			}
			$data['intro'] = htmlspecialchars($data['intro']);
			if (empty($data['intro'])) {
				$this->jinMsg('优惠券描述不能为空');
			}
			$data['price'] = (int) ($data['price'] * 100);
			$data['money'] = (int) ($data['money'] * 100);
		
		
		    $data['full_price'] = (int) ($data['full_price'] * 100);
			$data['reduce_price'] = (int) ($data['reduce_price'] * 100);
			
			
			$data['num'] = (int) $data['num'];
			$data['limit_num'] = (int) $data['limit_num'];
			$data['create_time'] = time();
			$data['create_ip'] = request()->ip();
			$data['audit'] = 1;
            if(Db::name('coupon')->insert($data)){
                $this->jinMsg('添加成功', url('coupon/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }

	
    public function edit($coupon_id = 0){
		
        if($coupon_id = (int) $coupon_id){
            
            if(!($detail = Db::name('coupon')->find($coupon_id))){
                $this->error('请选择要编辑的优惠券');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['shop_id'] = (int) $data['shop_id'];
				$data['cate_id'] = $data['cate_id'];
				$data['city_id'] = $data['city_id'];
				
				$data['title'] = htmlspecialchars($data['title']);
				if (empty($data['title'])) {
					$this->jinMsg('标题不能为空');
				}
				$data['photo'] = htmlspecialchars($data['photo']);
				if (empty($data['photo'])) {
					$this->jinMsg('请上传优惠券图片');
				}
				if (!isImage($data['photo'])) {
					$this->jinMsg('优惠券图片格式不正确');
				}
				$data['expire_date'] = htmlspecialchars($data['expire_date']);
				if (empty($data['expire_date'])) {
					$this->jinMsg('过期日期不能为空');
				}
				if (!isDate($data['expire_date'])) {
					$this->jinMsg('过期日期格式不正确');
				}
				$data['intro'] = htmlspecialchars($data['intro']);
				if (empty($data['intro'])){
					$this->jinMsg('优惠券描述不能为空');
				}
				$data['price'] = (int) ($data['price'] * 100);
				$data['money'] = (int) ($data['money'] * 100);
    			$data['full_price'] = (int) ($data['full_price'] * 100);
    			$data['reduce_price'] = (int) ($data['reduce_price'] * 100);
			
			
				$data['num'] = (int) $data['num'];
				$data['limit_num'] = (int) $data['limit_num'];
		
		
                $data['coupon_id'] = $coupon_id;
                if (false !== Db::name('coupon')->update($data)){
                    $this->jinMsg('操作成功', url('coupon/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的优惠券');
        }
    }

    public function delete($coupon_id = 0){
        if(is_numeric($coupon_id) && ($coupon_id = (int) $coupon_id)){
            Db::name('coupon')->update(array('coupon_id' => $coupon_id, 'closed' => 1));
            $this->jinMsg('删除成功', url('coupon/index'));
        }else{
            $coupon_id = input('coupon_id/a', false);
            if(is_array($coupon_id)){
                foreach ($coupon_id as $id){
                    Db::name('coupon')->update(array('coupon_id' => $id,'closed' => 1));
                }
                $this->jinMsg('删除成功', url('coupon/index'));
            }
            $this->jinMsg('请选择要删除的优惠券');
        }
    }
	
	
    public function audit($coupon_id = 0){
        if(is_numeric($coupon_id) && ($coupon_id = (int) $coupon_id)){
            Db::name('coupon')->update(array('coupon_id' => $coupon_id, 'audit' => 1));
            $this->jinMsg('审核成功', url('coupon/index'));
        }else{
            $coupon_id = input('coupon_id/a', false);
            if(is_array($coupon_id)){
                foreach ($coupon_id as $id){
                    Db::name('coupon')->update(array('coupon_id' => $id, 'audit' => 1));
                }
                $this->jinMsg('审核成功', url('coupon/index'));
            }
            $this->jinMsg('请选择要审核的优惠券');
        }
    }
	
	public function give($coupon_id = 0){
		
        if($coupon_id = (int) $coupon_id){
           
            if(!($detail = Db::name('coupon')->find($coupon_id))){
                $this->error('请选择要操作的优惠券');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array('user_id'));
				$data['user_id'] = (int) $data['user_id'];
				if(empty($data['user_id'])){
					$this->jinMsg('用户不能为空');
				}
				$users = Db::name('users')->find($data['user_id']);
				if(empty($users)){
					$this->jinMsg('请选择正确的用户');
				}
				if(empty($detail['title'])){
					$this->jinMsg('优惠券不存在');
				}
				model('ExpressOrder')->sendCouponDownload($data['user_id'],$detail['title'],$coupon_id);
				$this->jinMsg('操作成功', url('coupon/index'));
				
            }else{
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要操作的优惠券');
        }
    }
	
	
	public function givesPost($coupon_id=0,$user_id = 0){
		if(!($detail = Db::name('coupon')->find($coupon_id))){
			$this->error('优惠券详情不存在');
		} 
        if(is_numeric($user_id) && ($user_id = (int) $user_id)){
			model('ExpressOrder')->sendCouponDownload($user_id,$detail['title'],$coupon_id);
			$this->jinMsg('赠送成功', url('coupon/gives',array('coupon_id'=>$coupon_id)));
        }else{
            $user_ids = input('user_id/a', false);
            if(is_array($user_ids)){
				$i=0;
                foreach($user_ids as $id){
					$i++;
					model('ExpressOrder')->sendCouponDownload($id,$detail['title'],$coupon_id);
                }
                $this->jinMsg('批量赠送【'.$i.'】成功', url('coupon/gives',array('coupon_id'=>$coupon_id)));
            }
            $this->jinMsg('请批量选择用户');
        }
    }
	
	
	//兑换券列表
	public function codes($coupon_id = 0){
        $coupon_id = (int) $coupon_id;
        $detail = Db::name('coupon')->find($coupon_id);
		$this->assign('detail',$detail);
		$map = array('type'=>1);
		if($keyword = input('keyword','', 'htmlspecialchars')){
			$map['intro|code|exchange_mobile'] = array('LIKE', '%' . $keyword . '%');
			$this->assign('keyword', $keyword);
		}
		if($coupon_id = (int) input('coupon_id')){
			$map['coupon_id'] = $coupon_id;
			$this->assign('coupon_id',$coupon_id);
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
		$count = Db::name('coupon_code')->where($map)->count();
		$Page = new \Page($count,15);
		$page = $Page->show();
		$list = Db::name('coupon_code')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
           $list[$k]['users'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
           $list[$k]['coupon'] = Db::name('coupon')->where(array('coupon_id'=>$val['coupon_id']))->find();
        }
		$this->assign('list', $list);
		$this->assign('count',$count);
		$this->assign('page', $page);
		session('code_index_list', $map);
		return $this->fetch();
    }
	
	//导出优惠券兑换券
    public function code_export($admin_id = 0,$value = 0){
		$map = session('code_index_list');
        $list = Db::name('coupon_code')->where($map)->order(array('id' => 'desc'))->limit(0,2000)->select();
		foreach($list as $k => $val){
           $list[$k]['users'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
           $list[$k]['coupon'] = Db::name('coupon')->where(array('coupon_id'=>$val['coupon_id']))->find();
        }
        $date = date("Y_m_d", time());
        $filetitle = "优惠券ID【".$map['coupon_id']."】兑换券导出";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array(
			'aa' => 'ID',
			'bb' => '密码',
			'cc' => '兑换人信息',
			'dd' => '优惠券名称',
			'ee' => '优惠券金额',
			'ff' => '有效期',
			'gg' => '过期时间',
		);
        foreach($filter as $key => $title){
            $html .= $title . "\t,";
        }
        $html .= "\n";
	
		
        foreach($list as $k => $v){
            $filter = array(
				'aa' => 'ID',
				'bb' => '密码',
				'cc' => '兑换人信息',
				'dd' => '优惠券名称',
				'ee' => '优惠券金额',
				'ff' => '有效期',
				'gg' => '过期时间',
			);
            $list[$k]['aa'] = $v['id'];
            $list[$k]['bb'] = $v['code'];
            $list[$k]['cc'] = $v['users']['user_id'].'/'.$v['users']['nickname'];
            $list[$k]['dd'] = $v['coupon']['title'];
            $list[$k]['ee'] = round($v['coupon']['reduce_price']/100,2);
            $list[$k]['ff'] = $v['coupon']['day'].'天';
            $list[$k]['gg'] = $v['coupon']['expire_date'];
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
		session('code_index_list', null);
        echo $html;
        exit;
    }
	
	//删除优惠券兑换券
	public function CodesDelete($id = 0,$coupon_id=0){
        if(is_numeric($id) && ($id = (int) $id)){
            Db::name('coupon_code')->where(array('id' => $id))->delete();
            $this->jinMsg('操作成功', url('coupon/codes',array('coupon_id'=>$coupon_id)));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach($ids as $id){
                    Db::name('coupon_code')->where(array('id' => $id))->delete();
                }
                $this->jinMsg('操作成功', url('coupon/codes',array('coupon_id'=>$coupon_id)));
            }
            $this->jinMsg('请选择要删除的');
        }
    }
	
	
	//添加优惠券兑换券
	public function createCode($coupon_id = 0){
        if($coupon_id = (int) $coupon_id){
            if(!($detail = Db::name('coupon')->find($coupon_id))){
                $this->error('请选择要操作的优惠券');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array('num','intro'));
				$data['coupon_id'] = (int) $coupon_id;
				if(!$data['coupon_id']){
					$this->jinMsg('优惠券ID错误');
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
					$data['code'] =  model('Setting')->getCode($coupon_id);
					Db::name('coupon_code')->insert($data);
				}
				if($i){
					$this->jinMsg('添加成功【'.$i.'】张兑换卡',url('coupon/index'));
				}else{
					$this->jinMsg('操作失败');
				}
            }else{
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的优惠券');
        }
    }
	
	
	public function gives($coupon_id = 0){
		
        if($coupon_id = (int) $coupon_id){
           
            if(!($detail = Db::name('coupon')->find($coupon_id))){
                $this->error('请选择要操作的优惠券');
            }
			$this->assign('detail',$detail);
			$this->assign('coupon_id',$coupon_id);
			
            $map = array('closed' => array('IN', '0,-1'));
			if($keyword = input('keyword','', 'htmlspecialchars')){
				$map['account|nickname|mobile|user_id|email|ext0'] = array('LIKE', '%' . $keyword . '%');
				$this->assign('keyword', $keyword);
			}
			if($rank_id = (int) input('rank_id')){
				$map['rank_id'] = $rank_id;
				$this->assign('rank_id', $rank_id);
			}
			$order = input('order','','htmlspecialchars');
			$orderby = '';
			switch($order){
				case '6':
					$orderby = array('integral' => 'asc');
					break;
				case '5':
					$orderby = array('integral' => 'asc');
					break;
				case '4':
					$orderby = array('money' => 'asc');
					break;
				case '3':
					$orderby = array('money' => 'desc');
					break;
				case '2':
					$orderby = array('user_id' => 'asc');
					break;
				case '1':
					$orderby = array('user_id' => 'desc');
					break;
				default:
					$orderby = array('user_id' => 'desc');
					break;
			}
			$this->assign('order', $order);
			$count = Db::name('users')->where($map)->count();
			$Page = new \Page($count,25);
			$page = $Page->show();
			$list = Db::name('users')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
			foreach($list as $k => $val){
				$list[$k]['used'] = (int)Db::name('coupon_download')->where(array('user_id'=>$val['user_id']))->count();
				$list[$k]['used_0'] = (int)Db::name('coupon_download')->where(array('user_id'=>$val['user_id'],'is_used'=>0))->count();
				$list[$k]['used_1'] = (int)Db::name('coupon_download')->where(array('user_id'=>$val['user_id'],'is_used'=>1))->count();
			}
			$this->assign('list', $list);
			$this->assign('page', $page);
			$this->assign('ranks', model('UserRank')->fetchAll());
			return $this->fetch();
        }else{
            $this->error('请选择要操作的优惠券');
        }
    }
	
	
	//VIP兑换券列表
	public function codesList($rank_id = 0){
        $rank_id = (int) $rank_id;
        $detail = Db::name('user_rank')->find($rank_id);
		$this->assign('detail',$detail);
		$map = array('type'=>2);
		if($keyword = input('keyword','', 'htmlspecialchars')){
			$map['intro|code|exchange_mobile'] = array('LIKE', '%' . $keyword . '%');
			$this->assign('keyword', $keyword);
		}
		if($rank_id = (int) input('rank_id')){
			$map['rank_id'] = $rank_id;
			$this->assign('rank_id',$rank_id);
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
		$count = Db::name('coupon_code')->where($map)->count();
		$Page = new \Page($count,15);
		$page = $Page->show();
		$list = Db::name('coupon_code')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
           $list[$k]['users'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
           $list[$k]['rank'] = Db::name('user_rank')->where(array('rank_id'=>$val['rank_id']))->find();
        }
		$this->assign('list', $list);
		$this->assign('count',$count);
		$this->assign('page', $page);
		session('code_list_index_list', $map);
		return $this->fetch();
    }
	
	//VIP兑换券导出
    public function code_list_export($admin_id = 0,$value = 0){
		$map = session('code_list_index_list');
        $list = Db::name('coupon_code')->where($map)->order(array('id' => 'desc'))->limit(0,2000)->select();
		foreach($list as $k => $val){
           $list[$k]['users'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
           $list[$k]['rank'] = Db::name('user_rank')->where(array('rank_id'=>$val['rank_id']))->find();
        }
        $date = date("Y_m_d", time());
        $filetitle = "VIP-ID【".$map['rank_id']."】兑换券导出";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array(
			'aa' => 'ID',
			'bb' => '密码',
			'cc' => '兑换人信息',
			'dd' => 'VIP名称',
			'ee' => '天数',
		);
        foreach($filter as $key => $title){
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach($list as $k => $v){
            $filter = array(
				'aa' => 'ID',
				'bb' => '密码',
				'cc' => '兑换人信息',
				'dd' => 'VIP名称',
				'ee' => '天数',
			);
            $list[$k]['aa'] = $v['id'];
            $list[$k]['bb'] = $v['code'];
            $list[$k]['cc'] = $v['users']['user_id'].'/'.$v['users']['nickname'];
            $list[$k]['dd'] = $v['rank']['rank_name'];
            $list[$k]['ee'] = $v['day'].'天';
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
		session('code_list_index_list', null);
        echo $html;
        exit;
    }
	
	//VIP兑换券删除
	public function CodesListDelete($id = 0,$rank_id=0){
        if(is_numeric($id) && ($id = (int) $id)){
            Db::name('coupon_code')->where(array('id' => $id))->delete();
            $this->jinMsg('操作成功', url('coupon/codesList',array('rank_id'=>$rank_id)));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach($ids as $id){
                    Db::name('coupon_code')->where(array('id' => $id))->delete();
                }
                $this->jinMsg('操作成功', url('coupon/codesList',array('rank_id'=>$rank_id)));
            }
            $this->jinMsg('请选择要删除的');
        }
    }
	
	
	//添加VIP兑换券
	public function createCodeList($rank_id = 0){
        if($rank_id = (int) $rank_id){
            if(!($detail = Db::name('user_rank')->find($rank_id))){
                $this->error('请选择要操作的VIP');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array('num','day','intro'));
				$data['rank_id'] = (int) $rank_id;
				if(!$data['rank_id']){
					$this->jinMsg('VIP等级错误');
				}
				$data['intro'] = htmlspecialchars($data['intro']);
				if(empty($data['intro'])){
					$this->jinMsg('描述不能为空');
				}
				$data['type'] =2;
				$data['state'] =1;
				$data['create_time'] = time();
				
				$day = (int)$data['day'];
				if($day < 1){
					$this->jinMsg('VIP兑换券时间错误');
				}
				
				
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
					$data['code'] =  model('Setting')->getCode($rank_id);
					Db::name('coupon_code')->insert($data);
				}
				if($i){
					$this->jinMsg('添加成功【'.$i.'】张VIP兑换卡',url('coupon/codesList',array('rank_id'=>$rank_id)));
				}else{
					$this->jinMsg('操作失败');
				}
            }else{
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的');
        }
    }

	


}