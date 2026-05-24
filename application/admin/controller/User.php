<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;

class User extends Base{

    private $create_fields = array('parent_id','account', 'password', 'pay_password','rank_id','is_aux', 'face', 'mobile', 'email', 'nickname', 'face', 'province','city','area','ext0');
    private $edit_fields = array('parent_id','account', 'password','pay_password', 'rank_id','is_aux', 'face', 'mobile','count_team','count_user_price','count_team_price', 'email', 'nickname', 'face','province','city','area', 'ext0','stock','is_tuihuo');


	public function _initialize(){
        parent::_initialize();
		$this->assign('ranks', model('UserRank')->fetchAll());
		$this->assign('provinceList',$provinceList = Db::name('copy_province')->limit(0,36)->select());
    }


    public function index(){

		$map = array('closed' => array('IN', '0,1'));
        if($keyword = input('keyword','', 'htmlspecialchars')){
            $map['account|nickname|mobile'] = array('LIKE','%'.$keyword.'%');
            $this->assign('keyword',$keyword);
        }
		
		
        if($rank_id = (int) input('rank_id')){
            $map['rank_id'] = $rank_id;
            $this->assign('rank_id', $rank_id);
        }
		
		if($province = (int) input('province')){
            $map['province'] = $province;
            $this->assign('province', $province);
        }
		
		if($city = (int) input('city')){
            $map['city'] = $city;
            $this->assign('city', $city);
        }
		if($area = (int) input('area')){
            $map['area'] = $area;
            $this->assign('area', $area);
        }
        $getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['reg_time'] = $getSearchDate;
		}
		
		if($closed = (int) input('closed')){
            if($closed != 999){
                $map['closed'] = $closed;
            }
            $this->assign('closed', $closed);
        }else{
            $this->assign('closed', 999);
        }

		if($user_id = (int) input('user_id')){
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
		$order = input('order','','htmlspecialchars');
        $orderby = '';
        switch ($order){
            case '14':
                $orderby = array('count_team' => 'asc');
                break;
            case '13':
                $orderby = array('count_team' => 'desc');
                break;
			case '12':
                $orderby = array('count_team_price' => 'asc');
                break;
            case '11':
                $orderby = array('count_team_price' => 'desc');
                break;
			case '10':
                $orderby = array('count_user_price' => 'asc');
                break;
            case '9':
                $orderby = array('count_user_price' => 'desc');
                break;
			case '8':
                $orderby = array('integral' => 'asc');
                break;
            case '7':
                $orderby = array('integral' => 'desc');
                break;
			case '6':
                $orderby = array('money' => 'asc');
                break;
            case '5':
                $orderby = array('money' => 'desc');
                break;
			case '4':
                $orderby = array('rank_id' => 'asc');
                break;
            case '3':
                $orderby = array('rank_id' => 'desc');
                break;
			case '2':
                $orderby = array('reg_time' => 'asc');
                break;
            case '1':
                $orderby = array('reg_time' => 'desc');
                break;
            default:
                $orderby = array('user_id' => 'desc');
                break;
        }
        $this->assign('order', $order);
		
        $count = Db::name('users')->where($map)->count();
        $Page = new \Page($count,20);
        $show = $Page->show();
        $list = Db::name('users')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$rank_ids = array();
        foreach ($list as $k => $val) {
			$rank_ids[$val['rank_id']] = $val['rank_id'];
			$val['is_qq'] = model('Connect')->check_connect_bing($val['user_id'],2);
			$val['is_weibo'] = model('Connect')->check_connect_bing($val['user_id'],3);
			$val['ridName'] = Db::name('users')->where('user_id',$val['rid'])->value('nickname');
			$val['baseName'] = Db::name('users')->where('user_id',$val['base_tjr'])->value('nickname');
			$val['parent'] = (int)Db::name('users')->where('parent_id',$val['user_id'])->count();
			$closed = Db::name('user_closed')->where(array('phone'=>$val['mobile']))->find();
			$val['cityName'] = Db::name('copy_city')->where(array('city_id'=>$val['city']))->value('name');
			$val['areaName'] = Db::name('copy_area')->where(array('area_id'=>$val['area']))->value('Name');
			$val['provinceName'] = Db::name('copy_province')->where(array('id'=>$val['province']))->value('name');
			if($closed['type'] == 1){
				$val['closeds'] =1;
			}elseif($closed['type'] == 2){
				$val['closeds'] =2;
			}else{
				$val['closeds'] =0;
			}
			$list[$k] = $val;
        }
		//p($list);die;
		
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('rank', model('UserRank')->itemsByIds($rank_ids));
		session('user_index_list', $map);
		$this->assign('p', $p = input('p'));
		
		if($province){
			$this->assign('cityList', $cityList = Db::name('paddlist')->where(array('upid' => $province))->select());
		}else{
			$this->assign('cityList',array());
		}
		
		if($city){
			$this->assign('areaList', $areaList = Db::name('paddlist')->where(array('upid' => $city))->select());
		}else{
			$this->assign('areaList',array());
		}
				
        return $this->fetch();
    }

	//会员绑定列表首页
	public function binding(){
		$map = array();
       	if($keyword = input('keyword','', 'htmlspecialchars')){
            $map['nickname|open_id|uid'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if($uid = (int) input('uid')){
            $map['uid'] = $uid;
            $this->assign('uid', $uid);
        }
		if($type = (int) input('type')){
            if ($type == 1) {
                $map['type'] = 'weixin';
            }elseif($type == 2){
				$map['type'] = 'qq';
			}elseif($type == 3){
				$map['type'] = 'weibo';
			}
            $this->assign('type', $type);
        } else {
            $this->assign('type', 999);
        }
        $count = Db::name('connect')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('connect')->where($map)->order(array('connect_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$uids = array();
        foreach ($list as $k => $val) {
            if ($val['uid']) {
                $uids[$val['uid']] = $val['uid'];
            }
        }
        $this->assign('users', model('Users')->itemsByIds($uids));
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('p', $p = input('p'));
        return $this->fetch();
    }


	//会员回收站
	public function recycle(){
		$map = array('closed' =>1);
        if($account = input('account', '','htmlspecialchars')){
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        if($nickname = input('nickname','', 'htmlspecialchars')){
            $map['nickname'] = array('LIKE', '%' . $nickname . '%');
            $this->assign('nickname', $nickname);
        }
        if($mobile = input('mobile','', 'htmlspecialchars')){
            $map['mobile'] = array('LIKE', '%' . $mobile . '%');
            $this->assign('mobile', $mobile);
        }
        $count = Db::name('users')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('users')->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$rank_ids = array();
        foreach ($list as $k => $val) {
			$rank_ids[$val['rank_id']] = $val['rank_id'];
			$list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('rank', model('UserRank')->itemsByIds($rank_ids));
        return $this->fetch();
    }



	//回收站的会员彻底删除
    public function binding_delete($connect_id = 0){
        $connect_id = (int) $connect_id;
		if(Db::name('connect')->where(array('connect_id'=>$connect_id))->delete()){
			$this->jinMsg('删除会员绑定成功', url('user/binding'));
		}else{
			$this->jinMsg('操作失败');
		}
    }


	//会员小黑屋
	public function closeds(){
		$map = array();
        if($keyword = input('keyword','', 'htmlspecialchars')){
            $map['phone|remark|name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('mobile', $mobile);
        }
		if($type = (int) input('type')){
            $map['type'] = $type;
            $this->assign('type',$type);
        }
        $count = Db::name('user_closed')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('user_closed')->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	 //添加更新
	 public function closeds_edit($id = 0){
        $id = (int) $id;
        $detail = Db::name('user_closed')->find($id);
		if(request()->post()){
			$data = $this->checkFields(input('data/a', false),array('name','remark','phone','createBy'));
			$data['name'] = htmlspecialchars($data['name']);
			if(empty($data['name'])){
				$this->jinMsg('name不能为空');
			}
			$data['phone'] = htmlspecialchars($data['phone']);
			if(empty($data['phone'])){
				$this->jinMsg('phone不能为空');
			}
			$data['remark'] = SecurityEditorHtml($data['remark']);
			if(empty($data['remark'])) {
				$this->jinMsg('原因不能为空');
			}
			if($id){
				$data['id'] = $id;
				if(false !== Db::name('user_closed')->update($data)){
					$this->jinMsg('更新成功', url('user/closeds'));
				}
			}else{
				$data['type'] = 2;
				$data['createTime'] = date('Y-m-d H:i:s',time());
				$data['create_time'] = time();
				if(false !== Db::name('user_closed')->insert($data)){
					$this->jinMsg('添加成功', url('user/closeds'));
				}
			}
			$this->jinMsg('操作失败');
		}else{
			$this->assign('detail', $detail);
			return $this->fetch();
		}
    }
	
	
	public function closeds_update($id = 0){
		set_time_limit(0);
		$this->curl = new \Curl();
		$result = $this->curl->get('https://www.yida178.cn/prod-api/thirdApi/getBlackList');
		$result = json_decode($result,true);
		//p($result);
		if($result['code'] == 200){
			$list = $result['data'];
			$i=0;
			foreach($list as $k => $v){
				$insert['name'] = $v['name'];
				$insert['type'] = 1;
				$insert['phone'] = $v['phone'];
				$insert['remark'] = $v['remark'];
				$insert['createTime'] = $v['createTime'];
				$insert['create_time'] = time();
				$closed = Db::name('user_closed')->where(array('phone'=>$v['phone']))->find();
				if(!$closed){
					$i++;
					Db::name('user_closed')->insert($insert);
				}
			}
			$this->jinMsg('更新成功数据【'.$i.'】条', url('user/closeds'));
		}else{
			$this->jinMsg('操作错误'.$result['msg']);
		}
	}

	//会员小黑屋彻底删除
    public function closeds_delete($id = 0){
        $id = (int) $id;
		if(Db::name('user_closed')->where(array('id'=>$id))->delete()){
			$this->jinMsg('删除成功', url('user/closeds'));
		}else{
			$this->jinMsg('操作失败');
		}
    }
	
	//测地清空数据
    public function closeds_deletes($id = 0){
        $id = (int) $id;
		if(Db::name('user_closed')->where(array('id'=>array('gt',0)))->delete()){
			$this->jinMsg('全部删除成功', url('user/closeds'));
		}else{
			$this->jinMsg('操作失败');
		}
    }


    public function select(){
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
        $Page = new \Page($count, 8);
        $pager = $Page->show();
        $list = Db::name('users')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        return $this->fetch();
    }

     public function select2(){
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
        $Page = new \Page($count, 8);
        $pager = $Page->show();
        $list = Db::name('users')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        return $this->fetch();
    }



    public function create(){
        if(request()->post()){

            $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['account'] = htmlspecialchars($data['account']);
			if(empty($data['account'])) {
				$this->jinMsg('账户不能为空');
			}
			if(model('Users')->getUserByAccount($data['account'])) {
				$this->jinMsg('该账户已经存在');
			}

			$data['mobile'] = htmlspecialchars($data['mobile']);
			if(!isMobile($data['mobile'])){
				$this->jinMsg('手机号格式不正确');
			}
			if(model('Users')->getUserByAccount($data['mobile'])){
				$this->jinMsg('当前手机号已有会员使用请更换手机号');
			}

			$data['password'] = htmlspecialchars($data['password']);
			if(empty($data['password'])){
				$this->jinMsg('密码不能为空');
			}
			$data['password'] = md5($data['password']);
			$data['pay_password'] = htmlspecialchars($data['pay_password']);
			
			
			$data['pay_password'] = md5(md5($data['pay_password']));
			$data['nickname'] = htmlspecialchars($data['nickname']);
			if(empty($data['nickname'])){
				$this->jinMsg('姓名不能为空');
			}
			$data['email'] = htmlspecialchars($data['email']);
			$data['face'] = htmlspecialchars($data['face']);
			$data['ext0'] = htmlspecialchars($data['ext0']);
			$data['reg_ip'] = request()->ip();
			$data['reg_time'] = time();
			
			
			$data['requestCode'] = model('Passport')->getRequestCode();//邀请码

            if($user_id = Db::name('users')->insertGetId($data)){
				
				//新增团队人数
				$count_team = model('UserProfitLogs')->countTeam($user_id,$user_id,1,$type='1',$info='管理员添加用户ID【'.$user_id.'】');
				
                $this->jinMsg('添加成功', url('user/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            $this->assign('ranks', model('UserRank')->fetchAll());
            echo $this->fetch();
        }
    }




    public function edit($user_id = 0,$p = 0){
        if($user_id = (int) $user_id){
            if(!($detail = Db::name('users')->find($user_id))){
                $this->error('请选择要编辑的会员');
            }
			
			$old_rank_id = $detail['user_id'];//旧版等级
			$old_pid = $detail['parent_id'];
			
            if(request()->post()){
				
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['account'] = htmlspecialchars($data['account']);
				if(empty($data['account'])){
					$this->jinMsg('账户不能为空');
				}
				if($data['password'] == '******'){
					unset($data['password']);
				}else{
					$data['password'] = htmlspecialchars($data['password']);
					if(empty($data['password'])){
						$this->jinMsg('密码不能为空');
					}
					$data['password'] = md5($data['password']);
				}

				if($data['pay_password'] == '******'){
					unset($data['pay_password']);
				}else{
					$data['pay_password'] = htmlspecialchars($data['pay_password']);
					$data['pay_password'] = md5(md5($data['pay_password']));
				}

				$data['mobile'] = htmlspecialchars($data['mobile']);
				if(!isMobile($data['mobile'])){
					$this->jinMsg('手机号格式不正确');
				}

				$data['nickname'] = htmlspecialchars($data['nickname']);
				if(empty($data['nickname'])){
					$this->jinMsg('姓名不能为空');
				}
				$data['face'] = htmlspecialchars($data['face']);
				$data['email'] = htmlspecialchars($data['email']);
				$data['ext0'] = htmlspecialchars($data['ext0']);
				$data['rank_id'] = (int) $data['rank_id'];
				
				$data['count_user_price'] = $data['count_user_price']*100;
				$data['count_team_price'] = $data['count_team_price']*100;
				$data['stock'] = $data['stock']*100;

                $data['user_id'] = $user_id;

				$count = Db::name('users')->where(array('user_id'=>array('neq',$data['user_id']),'mobile'=>$data['mobile']))->count();
				if($count){
					$this->jinMsg('手机号重复请修改');
				}
				
				

                if(false !== Db::name('users')->update($data)){
					//改动会员等级
					if($data['rank_id'] != $old_pid){
						//旧版等级
						$old_rank=Db::name('user_rank')->where('rank_id',$old_rank_id)->find();
						//新版等级
						$rank=Db::name('user_rank')->where('rank_id',$data['rank_id'])->find();
						$info ='【后台管理员调整会员等级】';
						$data2['old_rank_name'] =$old_rank['rank_name'];
						$data2['old_rank_id'] =$old_rank['rank_id'];
						$data2['new_rank_name'] =$rank['rank_name'];
						$data2['new_rank_id'] =$rank['rank_id'];
						$data2['type'] =3;
						$data2['user_id'] =$user_id;
						$data2['info'] =$info;
						$data2['price'] =0;
						$data2['create_time'] =time();
						$log_id = Db::name('user_rank_logs')->insertGetId($data2);
						//短信通知会员升级
					}
					
					//改动会员上级
					if($data['parent_id'] != $old_pid){
						$data3['user_id'] =$user_id;
						$data3['old_pid'] =$old_pid;
						$data3['new_pid'] =$data['parent_id'];
						$data3['info'] ='【后台管理员调整会员等级】'.$data['old_pid'].''.$data['new_pid'];
						$data3['create_time'] =time();
						$id = Db::name('user_profit_update_logs')->insertGetId($data3);
					}
					
                    $this->jinMsg('操作成功', url('user/index',array('p'=>$p)));
                }
                $this->jinMsg('操作失败');
            }else{
				$this->assign('p', $p);
                $this->assign('detail', $detail);
                $this->assign('ranks', model('UserRank')->fetchAll());
				$this->assign('user', Db::name('users')->where(array('user_id'=>$detail['parent_id']))->find());
				
				$this->assign('provinceList',$provinceList = Db::name('copy_province')->limit(0,36)->select());
				$this->assign('cityList',$cityList = Db::name('copy_city')->where(array('ParentId'=>$detail['province']))->limit(0,100)->select());
				$this->assign('areaList',$areaList = Db::name('copy_area')->where(array('city_id'=>$detail['city']))->limit(0,100)->select());
				
                echo $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的会员');
        }
    }




    //删除会员重写
    public function delete($user_id = 0,$p = 0){
        if(is_numeric($user_id) && ($user_id = (int) $user_id)){
            Db::name('users')->update(array('user_id'=>$user_id,'closed'=>1));
            $this->jinMsg('移动会员到回收站成功', url('user/index',array('p'=>$p)));
        }else{
            $user_id = input('user_id/a', false);
            if(is_array($user_id)){
                $obj = model('Users');
                foreach($user_id as $id){
					Db::name('users')->update(array('user_id'=>$id,'closed'=>1));
                }
                $this->jinMsg('批量移动会员到回收站成功', url('user/index',array('p'=>$p)));
            }
            $this->jinMsg('请选择要删除的会员');
        }
    }


  	public function closed($user_id = 0,$p = 0){
        if(is_numeric($user_id) && ($user_id = (int) $user_id)){
            Db::name('users')->update(array('user_id'=>$user_id,'closed'=>1));
            $this->jinMsg('拉黑成功', url('user/index',array('p'=>$p)));
        }else{
            $user_id = input('user_id/a', false);
            if(is_array($user_id)){
                foreach($user_id as $id){
                    Db::name('users')->update(array('user_id'=>$id,'closed'=>1));
                }
                $this->jinMsg('拉黑成功', url('user/index',array('p'=>$p)));
            }
            $this->jinMsg('请选择要拉黑的会员');
        }
    }


    public function audit($user_id = 0,$p = 0){
        if(is_numeric($user_id) && ($user_id = (int) $user_id)){
            Db::name('users')->update(array('user_id'=>$user_id,'closed'=>0));
            $this->jinMsg('审核成功', url('user/index',array('p'=>$p)));
        }else{
            $user_id = input('user_id/a', false);
            if(is_array($user_id)){
                foreach($user_id as $id){
                    Db::name('users')->update(array('user_id'=>$id,'closed'=>0));
                }
                $this->jinMsg('审核成功', url('user/index',array('p'=>$p)));
            }
            $this->jinMsg('请选择要审核的会员');
        }
    }


	//恢复会员
	public function renew($user_id = 0,$p = 0){
        if(is_numeric($user_id) && ($user_id = (int) $user_id)){
            Db::name('users')->update(array('user_id'=>$user_id,'closed'=> 0));
            $this->jinMsg('恢复成功', url('user/recycle',array('p'=>$p)));
        }else{
            $user_id = input('user_id/a', false);
            if(is_array($user_id)){
                foreach($user_id as $id){
                    Db::name('users')->update(array('user_id'=> $id,'closed'=> 0));
                }
                $this->jinMsg('批量恢复审核成功', url('user/recycle',array('p'=>$p)));
            }
            $this->jinMsg('请选择要恢复的会员');
        }
    }


	//回收站的会员彻底删除，这里以后再次封装
    public function recycle_delete($user_id = 0,$p = 0){
        $user_id = (int) $user_id;

		if(!($detail = Db::name('users')->find($user_id))){
            $this->jinMsg('删除的会员不存在');
        }



		$connect = Db::name('connect')->where(array('uid'=>$user_id))->select();
		foreach ($connect as $k => $v){
			Db::name('connect')->where(array('connect_id'=>$v['connect_id']))->delete();
        }
		if(false !== Db::name('users')->where(array('user_id'=>$user_id))->delete()){
			$this->jinMsg('彻底删除成功', url('user/recycle',array('p'=>$p)));
		}else{
			$this->jinMsg('操作失败');
		}
    }






   //管理用户
   public function manage(){
        $user_id = (int) input('user_id');
        if(empty($user_id)){
            $this->error('请选择用户');
        }

        if(!($detail = Db::name('users')->find($user_id))){
            $this->error('没有该用户');
        }
        setUid($user_id,time());
        header("Location:" . url('user/index/index'));
        die;
    }

	//修改用户余额
    public function money($p = 0){
        $user_id = (int) input('user_id');
        if(empty($user_id)){
            $this->error('请选择用户');
        }
        if(!($detail = Db::name('users')->find($user_id))){
            $this->error('没有该用户');
        }
        if(request()->post()){
            $money = (int) (input('money') * 100);
            if($money == 0){
                $this->jinMsg('请输入正确的余额数');
            }
            $intro = input('intro','','htmlspecialchars');
			if(empty($intro)){
                $this->jinMsg('添加余额必须输入说明');
            }
            if($detail['money'] + $money < 0){
                $this->jinMsg('余额不足');
            }
            Db::name('users')->update(array('user_id'=>$user_id,'money'=>$detail['money']+$money));

            Db::name('user_money_logs')->insert(array(
				'user_id' => $user_id,
				'money' => $money,
				'intro' => '管理员后台操作说明：'.$intro,
				'create_time' => time(),
				'year' => date('Y',time()),
			    'month' => date('Ym',time()),
			    'day' => date('Ymd',time()),
				'type' => 2,
				'create_ip' => request()->ip()
			));
            $this->jinMsg('操作会员余额成功', url('user/index',array('p'=>$p)));
        }else{
			$this->assign('p', $p);
            $this->assign('user_id', $user_id);
            echo $this->fetch();
        }
    }
	
	//修改用户抵扣金
    public function moneys($p = 0){
        $user_id = (int) input('user_id');
        if(empty($user_id)){
            $this->error('请选择用户');
        }
        if(!($detail = Db::name('users')->find($user_id))){
            $this->error('没有该用户');
        }
        if(request()->post()){
            $moneys = (int) (input('moneys') * 100);
            if($moneys == 0){
                $this->jinMsg('请输入正确的抵扣金数');
            }
            $intro = input('intro','','htmlspecialchars');
			if(empty($intro)){
                $this->jinMsg('添加抵扣金必须输入说明');
            }
            if($detail['moneys'] + $moneys < 0){
                $this->jinMsg('抵扣金不足');
            }
            Db::name('users')->update(array('user_id'=>$user_id,'moneys'=>$detail['moneys']+$moneys));

            Db::name('user_moneys_logs')->insert(array(
				'user_id' => $user_id,
				'moneys' => $moneys,
				'intro' => '管理员后台操作说明：'.$intro,
				'create_time' => time(),
				'year' => date('Y',time()),
			    'month' => date('Ym',time()),
			    'day' => date('Ymd',time()),
				'type' => 4,
				'create_ip' => request()->ip()
			));
            $this->jinMsg('操作会员抵扣金成功', url('user/index',array('p'=>$p)));
        }else{
			$this->assign('p', $p);
            $this->assign('user_id', $user_id);
            echo $this->fetch();
        }
    }

	

	//修改用户积分账户
	public function integral($p = 0){
        $user_id = (int) input('user_id');
        if(empty($user_id)){
            $this->error('请选择用户');
        }
        if(!($detail = Db::name('users')->find($user_id))){
            $this->error('没有该用户');
        }
        if(request()->post()){
			
			$integral = (int) (input('integral'));
            if($integral == 0) {
                $this->jinMsg('请输入正确的积分数');
            }
            $intro = input('intro','','htmlspecialchars');
			if(empty($intro)){
                $this->jinMsg('积分说明不能为空');
            }
            if($detail['integral'] + $integral < 0){
                $this->jinMsg('积分余额不足！');
            }

			Db::name('users')->update(array('user_id' => $user_id, 'integral' => $detail['integral'] + $integral));

            Db::name('user_integral_logs')->insert(array(
				'user_id' => $user_id,
				'integral' => $integral,
				'intro' => '管理员后台操作说明：'.$intro,
				'create_time' => time(),
				'type' => 2,
				'year' => date('Y',time()),
			    'month' => date('Ym',time()),
			    'day' => date('Ymd',time()),
				'create_ip' => request()->ip()
			));
            $this->jinMsg('操作会员积分成功', url('user/index',array('p'=>$p)));
        }else{
			$this->assign('p', $p);
            $this->assign('user_id', $user_id);
            echo $this->fetch();
        }
    }




	public function prestige($p = 0){
        $user_id = (int) input('user_id');
        if(empty($user_id)){
            $this->error('请选择用户');
        }
        if(!($detail = Db::name('users')->find($user_id))){
            $this->error('没有该用户');
        }
        if(request()->post()){
			
			$prestige = (int) (input('prestige') * 100);
            if($prestige == 0) {
                $this->jinMsg('请输入正确的积分数');
            }
            $intro = input('intro','','htmlspecialchars');
			if(empty($intro)){
                $this->jinMsg('说明不能为空');
            }
            if($detail['prestige'] + $prestige < 0){
                $this->jinMsg('余额不足');
            }

			Db::name('users')->update(array('user_id' => $user_id, 'prestige' => $detail['prestige'] + $prestige));

            Db::name('user_prestige_logs')->insert(array(
				'user_id' => $user_id,
				'integral' => $prestige,
				'intro' => '管理员后台操作说明：'.$intro,
				'create_time' => time(),
				'type' => 2,
				'year' => date('Y',time()),
			    'month' => date('Ym',time()),
			    'day' => date('Ymd',time()),
				'create_ip' => request()->ip()
			));
            $this->jinMsg('操作成功', url('user/index',array('p'=>$p)));
        }else{
			$this->assign('p', $p);
            $this->assign('user_id', $user_id);
            echo $this->fetch();
        }
    }




	//会员绑定编辑
	public function binding_edit($connect_id = 0){
        if($connect_id = (int) $connect_id){
            if(!($detail = Db::name('connect')->find($connect_id))){
                $this->error('请选择要编辑的绑定会员');
            }
            if(request()->post()){
                $$data = $this->checkFields(input('data/a', false),array('uid','open_id','openid','nickname'));
				$data['uid'] = (int) $data['uid'];
				if(empty($data['uid'])){
					$this->jinMsg('请选择会员');
				}
				$data['open_id'] = htmlspecialchars($data['open_id']);
				if(empty($data['open_id'])){
					$this->jinMsg('open_id不能为空');
				}
				$data['openid'] = htmlspecialchars($data['openid']);
				$data['nickname'] = htmlspecialchars($data['nickname']);
				if(empty($data['nickname'])){
					$this->jinMsg('姓名不能为空');
				}
                $data['connect_id'] = $connect_id;
                if(false !== Db::name('connect')->update($data)){
                    $this->jinMsg('操作成功', url('user/binding'));
                }
                $this->jinMsg('操作失败');
            }else{
				$this->assign('user', Db::name('users')->find($detail['uid']));
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->jinMsg('请选择要编辑的会员');
        }
    }


public function tree($user_id=1){
		$users = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$this->assign('users', $users);
		$map = array('parent_id'=>$user_id);
		$count = Db::name('users')->where($map)->count();
        $Page = new \Page($count,50);
        $show = $Page->show();
        $list = Db::name('users')->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $v){
			$list[$k] = $this->getChildDetail($v['user_id']);//技师获取详情
		}
        $this->assign('list', $list);
		$this->assign('user_id', $user_id);
		return $this->fetch('tree');
	}

	public function getChildFamily($user_id,$k){
		static $arr=array();  
		$data=Db::name('users')->where(array('parent_id'=>$user_id))->select();
		foreach($data as $key => $value){
			$data[$key]= $this->getChildDetail($value['user_id']);
			$data[$key]['level']= $k;
			$arr[] = $data[$key];
			$this->getChildFamily($value['user_id'],$k+1);//循环
		}
		return $arr;
	}
	


	public function getChildDetail($user_id){
		
		$v = Db::name('users')->where(array('user_id'=>$user_id))->find();
		
		$data['id'] = $user_id;
		$data['user_id'] = $user_id;
		$data['parent_id'] = $v['parent_id'];
		$data['mobile'] = $v['mobile'];
		$data['nickname'] = $v['nickname'];
		$rank_name = Db::name('user_rank')->where('rank_id',$v['rank_id'])->value('rank_name');
		$data['rank_name'] = $rank_name ? $rank_name : '无等级';
		$data['yao_name'] = Db::name('users')->where('user_id',$v['parent_id'])->value('nickname');
		
		$data['yao_num'] = (int)Db::name('users')->where(array('parent_id'=>$user_id))->count();
		
		$data['tuan_num'] = $v['count_team'];
		$data['user_price'] = $v['count_user_price'];
		$data['tuan_price'] = $v['count_team_price'];
		$data['time'] = date("Y-m-d H:i:s",$v['reg_time']);
		return $data;
	}

	
	
	//自己【订单】获取业绩
	public function getChildUserOrderPrice($parent_id){
		$arr=Db::name('order')->where(array('user_id'=>$parent_id,'status'=>8))->select();
		$price3 = array_sum(array_column($getChildUserOrderPrice,'need_pay'));
		return $price3;
	}
	
	
	
	
	public function tree4($user_id){
		$user_id = (int)$user_id;
		$users = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$this->assign('users', $users);
		$this->assign('user_id',$user_id);
		
        $list = Db::name('users')->where(array('parent_id'=>$user_id))->order(array('user_id' => 'desc'))->limit(0,100)->select();
		foreach($list as $k => $v){
			$list[$k] = $this->getChildDetail($v['user_id']);
			$list2 = Db::name('users')->where(array('parent_id'=>$v['user_id']))->order(array('user_id' => 'desc'))->limit(0,100)->select();
			foreach($list2 as $k2 => $v2){
				$list2[$k2] = $this->getChildDetail($v2['user_id']);
				$list3 = Db::name('users')->where(array('parent_id'=>$v2['user_id']))->order(array('user_id' => 'desc'))->limit(0,100)->select();
				foreach($list3 as $k3 => $v3){
					$list3[$k3] = $this->getChildDetail($v3['user_id']);
					$list4 = Db::name('users')->where(array('parent_id'=>$v3['user_id']))->order(array('user_id' => 'desc'))->limit(0,100)->select();
					foreach($list4 as $k4 => $v4){
						$list4[$k4] = $this->getChildDetail($v4['user_id']);
						$list5 = Db::name('users')->where(array('parent_id'=>$v4['user_id']))->order(array('user_id' => 'desc'))->limit(0,100)->select();
						foreach($list5 as $k5 => $v5){
							$list5[$k5] = $this->getChildDetail($v5['user_id']);
							$list6 = Db::name('users')->where(array('parent_id'=>$v5['user_id']))->order(array('user_id' => 'desc'))->limit(0,100)->select();
							foreach($list6 as $k6 => $v6){
								$list6[$k6] = $this->getChildDetail($v6['user_id']);
								$list7 = Db::name('users')->where(array('parent_id'=>$v6['user_id']))->order(array('user_id' => 'desc'))->limit(0,100)->select();
								foreach($list7 as $k7 => $v7){
									$list7[$k7] = $this->getChildDetail($v7['user_id']);
									$list8 = Db::name('users')->where(array('parent_id'=>$v7['user_id']))->order(array('user_id' => 'desc'))->limit(0,100)->select();
									foreach($list8 as $k8 => $v8){
										$list8[$k8] = $this->getChildDetail($v8['user_id']);
										$list9 = Db::name('users')->where(array('parent_id'=>$v8['user_id']))->order(array('user_id' => 'desc'))->limit(0,100)->select();
										foreach($list9 as $k9 => $v9){
											$list9[$k9] = $this->getChildDetail($v9['user_id']);
											$list10 = Db::name('users')->where(array('parent_id'=>$v9['user_id']))->order(array('user_id' => 'desc'))->limit(0,100)->select();
											foreach($list10 as $k10 => $v10){
												$list10[$k10] = $this->getChildDetail($v10['user_id']);
											}
											$list8[$k8]['children'] = $list9;
										}
										$list7[$k7]['children'] = $list8;
										}
									$list7[$k7]['children'] = $list8;
								}
								$list6[$k6]['children'] = $list7;//有问题
							}
							$list5[$k5]['children'] = $list6;
						}
						$list4[$k4]['children'] = $list5;
					}
					$list3[$k3]['children'] = $list4;
				}
				$list2[$k2]['children'] = $list3;
			}
			$list[$k]['children'] = $list2;
		}
		//p($list);die;
        $this->assign('list',$list);
		return $this->fetch();
	}
	
	
	//业绩修改记录
	public function countlog(){
		$map = array();
		
		$keyword = input('keyword','', 'htmlspecialchars');
        if($keyword){
            $map['info'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		if($order_id = (int) input('order_id')){
            $this->assign('order_id', $order_id);
            $map['order_id'] = $order_id;
        }
		
		if($type = (int) input('type')){
            $this->assign('type', $type);
            $map['type'] = $type;
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
        $count = Db::name('users_count_log')->where($map)->count(); 
        $Page = new \Page($count, 25); 
        $show = $Page->show(); 
        $list = Db::name('users_count_log')->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
            $list[$k]['users'] =  Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
        }
		
		$this->assign('num',$num = Db::name('users_count_log')->where($map)->sum('num'));
		$this->assign('old_num',$old_num = Db::name('users_count_log')->where($map)->sum('old_num'));
		$this->assign('new_num',$new_num = Db::name('users_count_log')->where($map)->sum('new_num'));
		
		$this->assign('count', $count); 
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        return $this->fetch(); 
    }
	
	
	//会员订单列表导出
    public function export_code(){
		$admin_id = (int) $_POST['admin_id'];
        if(empty($admin_id)){
            return json(array('status' => 'error', 'msg' => '非法错误'));
        }
		$value = input('value', 'htmlspecialchars');
        if(empty($value)){
            return json(array('status' => 'error', 'msg' => '请填写导出密码'));
        }
		if($value != 123456){
            return json(array('status' => 'error', 'msg' => '导出密码错误'));
        }else{
			session('export_code', md5($admin_id.'--'.$value));
			return json(array('status' => 'success', 'msg' => '输入密码成功，正在为你跳转', 'url' => url('user/export',array('admin_id'=>$admin_id,'value'=>$value))));
		}

    }

	



	//会员订单列表导出
    public function export($admin_id = 0,$value = 0){
		$admin_id = (int) $admin_id;
		$value = input('value', 'htmlspecialchars');
		$export_code = session('export_code');
		
		$map = session('user_index_list');
		
        $list = Db::name('users')->where($map)->order(array('user_id' => 'desc'))->select();
        $date = date("Y_m_d", time());
        $filetitle = "会员列表";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array(
			'aa' => '会员ID',
			'bb' => '账户',
			'cc' => '姓名',
			'dd' => '余额',
			'ee' => '会员等级',
			'ff' => '手机号',
			'gg' => '推荐人ID',
		);
        foreach($filter as $key => $title){
            $html .= $title . "\t,";
        }
        $html .= "\n";
	
		
        foreach($list as $k => $v){
            $filter = array(
				'aa' => '会员ID',
				'bb' => '账户',
				'cc' => '姓名',
				'dd' => '余额',
				'ee' => '会员等级',
				'ff' => '手机号',
				'gg' => '推荐人ID'
			);
			
			$rank = Db::name('user_rank')->where(array('rank_id'=>$v['rank_id']))->find();
			
            $list[$k]['aa'] = $v['user_id'];
            $list[$k]['bb'] = $v['account'];
            $list[$k]['cc'] = $v['nickname'];
            $list[$k]['dd'] = $v['money']/100;
            $list[$k]['ee'] = $rank['rank_name'];
            $list[$k]['ff'] = $v['mobile'];
            $list[$k]['gg'] = $v['parent_id'];
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
		session('export_code', null);
        echo $html;
        exit;
    }




	

	public function treeform($user_id =1){
		$user_id = (int)$user_id;
		$users = Db::name('users')->where(array('user_id'=>$user_id))->find();
		if($this->_CONFIG['profit']['gongpai_type'] == 1){
			$list = Db::name('users_gongpai')->where(array('rid'=>$user_id))->select();
			foreach($list as $k => $v){
				$list[$k]['children'] = Db::name('users_gongpai')->where(array('rid'=>$v['user_id']))->select();
			}
		}else{
			$list = Db::name('users')->where(array('rid'=>$user_id))->select();
			foreach($list as $k => $v){
				$list[$k]['children'] = Db::name('users')->where(array('rid'=>$v['user_id']))->select();
			}
		}
		$this->assign('list',$list);
		$this->assign('users',$users);
		return $this->fetch();
	}

//兑换奖品
    public function codes($p = 0){
        $user_id = (int) input('user_id');
        if(empty($user_id)){
            $this->error('请选择用户');
        }
        if(!($detail = Db::name('users')->find($user_id))){
            $this->error('没有该用户');
        }
		$uid= $user_id;
        if(request()->post()){
			$code = input('code','','trim');
			$cc = Db::name('coupon_code')->where(array('code'=>$code))->find();
			if(!$cc){
				$this->jinMsg('兑奖码错误');
			}
			if($cc['state'] == 2){
				$this->jinMsg('兑奖码已兑换');
			}
			if($cc['user_id']){
				$this->jinMsg('当前密码已被兑换');
			}
			
			if($cc['type'] ==1 && !$cc['coupon_id']){
				$this->jinMsg('优惠券ID不存在');
			}
			if($cc['type'] ==2 && !$cc['rank_id']){
				$this->jinMsg('会员等级ID不存在');
			}
			$count = Db::name('coupon_code')->where(array('user_id'=>$uid,'state'=>2))->count();
			if($count>10){
				$this->jinMsg('兑换不能超过10次');
			}
			if($cc['type'] ==1){
				$send = model('ExpressOrder')->sendCouponDownload($uid,'',$cc['coupon_id'],0);
				if($send != false){
					$updateData['user_id'] = $uid;
					$updateData['exchange_info'] = '管理员兑换优惠券';
					$updateData['exchange_time'] = time();
					$updateData['state'] = 2;
					$r = Db::name('coupon_code')->where(array('id'=>$cc['id']))->update($updateData);
					if(!$r){
						$this->jinMsg('更新数据库失败');
					}
					$data['code']=1;
					$data['type']=1;
					$this->jinMsg('兑换优惠券成功', url('user/index',array('p'=>$p)));
				}else{
					$this->jinMsg('兑换失败'.model('ExpressOrder')->getError());
				}
			}else{
				$ur = Db::name('user_rank')->where(array('rank_id'=>$cc['rank_id']))->find();
				if(!$ur){
					$this->jinMsg('会员等级不存在');
				}
				if($cc['day'] <=0){
					$this->jinMsg('兑换码有效期时间不存在');
				}
				$info = '兑换码【'.$code.'】兑换等级';
				$updatePayUserRank = model('Users')->updatePayUserRank($uid,$cc['rank_id'],$cc['day'],$info,0);
				if($updatePayUserRank != false){
					$updateData['user_id'] = $uid;
					$updateData['exchange_info'] = '小程序兑换VIP等级';
					$updateData['exchange_time'] = time();
					$updateData['state'] = 2;
					$r = Db::name('coupon_code')->where(array('id'=>$cc['id']))->update($updateData);
					if(!$r){
						$this->jinMsg('更新数据库失败');
					}
					$data['code']=1;
					$data['type']=2;
					$this->jinMsg('兑换VIP成功', url('user/index',array('p'=>$p)));
				}else{
					$this->jinMsg('兑换失败'.model('ExpressOrder')->getError());
				}
			}
        }else{
			$this->assign('p', $p);
            $this->assign('user_id', $user_id);
            echo $this->fetch();
        }
    }
	
	
	public function set(){
		$map = array();
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['name|mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if($status = (int) input('status')){
            $this->assign('status', $status);
            $map['status'] = $status;
        }
        if($user_id = (int) input('user_id')){
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        $count = Db::name('users_set')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('users_set')->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $v){
			$list[$k]['user'] = Db::name('users')->where(array('user_id'=>$v['user_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	
	public function set_edit($id = 0){
		$id = (int) $id;
		$detail = Db::name('users_set')->find($id);
		
		if(request()->post()){
			$data = $this->checkFields(input('data/a', false),array('user_id','name','mobile','addr','weixin','img','info','status'));
			$data['user_id'] = (int) $data['user_id'];
			if(empty($data['user_id'])){
				$this->jinMsg('用户不能为空');
			}
			$data['name'] = htmlspecialchars($data['name']);
			if(empty($data['name'])){
				$this->jinMsg('收货人不能为空');
			}
			$data['mobile'] = htmlspecialchars($data['mobile']);
			if(empty($data['mobile'])){
				$this->jinMsg('手机号码不能为空');
			}
			if(!isMobile($data['mobile'])){
				$this->jinMsg('手机号码格式不正确');
			}
			$data['addr'] = htmlspecialchars($data['addr']);
			if($data['status']==2){
				$data['audit_time'] = time();
			}
			if($id){
				$data['id'] = $id;
				$data['edit_time'] = time();
				if(false !== Db::name('users_set')->update($data)){
					$this->jinMsg('编辑成功', url('user/set'));
				}
			}else{
				$data['create_time'] = time();
				if(Db::name('users_set')->insert($data)){
					$this->jinMsg('添加成功', url('user/set'));
				}
			}
		}else{
			$this->assign('detail', $detail);
			$this->assign('user', model('Users')->where(array('user_id' =>$detail['user_id']))->find());
			return $this->fetch();
		}
    }
	
	
	
	public function set_status($id = 0){
        if(is_numeric($id) && ($id = (int) $aid)){
			Db::name('users_set')->update(array('id' => $id,'audit_time' =>time(),'status' => 2));
            $this->jinMsg('审核成功', url('user/set'));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach($ids as $id){
                    Db::name('users_set')->update(array('id' => $id,'audit_time' =>time(), 'status' => 2));
                }
                $this->jinMsg('审核成功', url('user/set'));
            }
            $this->jinMsg('请选择要审核的取件员');
        }
    }
	
	
	
	public function set_delete($id = 0){
        if(is_numeric($id) && ($id = (int) $addr_id)){
			Db::name('users_set')->where(array('id'=>$id))->delete();
            $this->jinMsg('删除成功', url('user/set'));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach ($ids as $id){
                    Db::name('users_set')->where(array('id'=>$id))->delete();
                }
                $this->jinMsg('删除成功', url('user/set'));
            }
            $this->jinMsg('请选择要删除的取件员');
        }
    }

}
