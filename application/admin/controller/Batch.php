<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Batch extends Base{
	
	 public function _initialize(){
        parent::_initialize();
		$this->assign('getorderStatus', $getorderStatus = model('Setting')->getorderStatus());
		$this->assign('getdiffStatus', $getdiffStatus = model('Setting')->getdiffStatus());
        $this->assign('getorderRightsStatus', $getorderRightsStatus = model('Setting')->getorderRightsStatus());
		$this->assign('getCompanyApiTypes', $getCompanyApiTypes = model('Setting')->getCompanyApiTypes());
		$this->assign('getBatchApiTypes', $getBatchApiTypes = model('Setting')->getBatchApiTypes());
    }
	
	
	public function cate(){
        $list = Db::name('express_cates')->order("cate_id asc")->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
   
    public function cate_create($parent_id = 0){

        if(request()->post()){
			$data = $this->checkFields(input('data/a', false),array(
			'cate_name','type','is_jia','name','pinyin','pinyin2','photo','charging','tag','info','is_bao','baojia_rate','is_yuyue',
			'ratio','priceA_type','priceA_ratio','priceA_price','priceB_type','priceB_ratio','priceB_price',
			'firstPrice','lanshou','firstPrice1','firstPrice2','addPrice','addPrice1','addPrice2','limitFirstPrice','limitAddPrice','tel','is_pei','area','orderby'));
			$data['cate_name'] = htmlspecialchars($data['cate_name']);
			if(empty($data['cate_name'])){
				$this->jinMsg('分类不能为空');
			}
			$data['is_pei'] = (int) $data['is_pei'];
			if($data['is_pei']==2 && !$data['area']){
				$this->jinMsg('请填写指定区域');
			}
			$data['orderby'] = (int) $data['orderby'];
            if($cate_id = Db::name('express_cates')->insertGetId($data)){
                $this->jinMsg('添加成功', url('batch/cate'));
            }
            $this->jinMsg('操作失败');
        }else{
            echo $this->fetch();
        }
    }
 	
	
    public function cate_edit($cate_id = 0){
        if($cate_id = (int) $cate_id) {
            if(!($detail = Db::name('express_cates')->find($cate_id))){
                $this->error('请选择要编辑的类型');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array(
				'cate_name','is_jia','type','name','pinyin','pinyin2','photo','charging','tag','info','is_bao','baojia_rate','is_yuyue',
				'ratio','priceA_type','priceA_ratio','priceA_price','priceB_type','priceB_ratio','priceB_price',
				'firstPrice','lanshou','firstPrice1','firstPrice2','addPrice','addPrice1','addPrice2','limitFirstPrice','limitAddPrice','tel','is_pei','area','orderby'));
				$data['cate_name'] = htmlspecialchars($data['cate_name']);
				if(empty($data['cate_name'])){
					$this->jinMsg('分类不能为空');
				}
				$data['is_pei'] = (int) $data['is_pei'];
				if($data['is_pei']==2 && !$data['area']){
					$this->jinMsg('请填写指定区域');
				}
				$data['orderby'] = (int) $data['orderby'];
                $data['cate_id'] = $cate_id;
                if(false !== Db::name('express_cates')->update($data)){
                    $this->jinMsg('操作成功', url('batch/cate'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                echo $this->fetch();
            }
        }else{
            $this->jinMsg('请选择要编辑的活动类型');
        }
    }
	

	
    public function cate_delete($cate_id = 0){
        if(is_numeric($cate_id) && ($cate_id = (int) $cate_id)){
            Db::name('express_cates')->where(array('cate_id'=>$cate_id))->delete();
            $this->jinMsg('删除成功', url('batch/cate'));
        }else{
            $cate_id = input('cate_id/a', false);
            if(is_array($cate_id)){
                foreach($cate_id as $id){
                    Db::name('express_cates')->where(array('cate_id'=>$id))->delete();
                }
                $this->jinMsg('删除成功', url('batch/cate'));
            }
            $this->jinMsg('请选择要删除的活动类型');
        }
    }
	
	
    public function cate_update(){
        $orderby = input('orderby/a', false);
        foreach($orderby as $key => $val){
            $data = array('cate_id' => (int) $key, 'orderby' => (int) $val);
            Db::name('express_cates')->update($data);
        }
        $this->jinMsg('更新成功', url('batch/cate'));
    }

	
    public function order(){
        $map = array('is_piliang'=>2);
        $id = (int)input('id','', 'trim,htmlspecialchars');
        if($id){
            $map['id'] = $id;
            $this->assign('id', $id);
        }
		
        $deliveryId = input('deliveryId');
        if($deliveryId){
            $map['deliveryId'] = $deliveryId;
			$this->assign('deliveryId', $deliveryId);
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
		$orderStatus= input('orderStatus');
		if($orderStatus != NUll && $orderStatus != 999){
			$map['orderStatus'] = $orderStatus;
		}
		if(isset($input['orderStatus']) || isset($input['orderStatus'])){
			$orderStatus = $input['orderStatus'];
		}else{
			$orderStatus = $orderStatus?$orderStatus:'999';
		}
		$this->assign('orderStatus',$orderStatus);
		if(isset($input['diffStatus']) || isset($input['diffStatus']) || input('diffStatus')){
            $diffStatus= (int) input('diffStatus');
            if($diffStatus != 999){
                $map['diffStatus'] = $diffStatus;
            }
            $this->assign('diffStatus', $diffStatus);
        }else{
            $this->assign('diffStatus', 999);
        }
		if(isset($input['orderRightsStatus']) || isset($input['orderRightsStatus'])){
            $orderRightsStatus= (int) input('orderRightsStatus');
            if($orderRightsStatus != 999){
                $map['orderRightsStatus'] = $orderRightsStatus;
            }
            $this->assign('orderRightsStatus', $orderRightsStatus);
        }else{
            $this->assign('orderRightsStatus', 999);
        }
        $count = Db::name('express_order')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('express_order')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k =>$v){
			$list[$k]['user'] = Db::name('users')->find($v['user_id']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		
		cookie('express_order_map',$map);
		
		$this->getOrderStatus = model('Setting')->getorderStatus();
		//统计数量
		$getOrderStatus = array();
		foreach($this->getOrderStatus as $k2 =>$v2){   
		    $getOrderStatus[$k2]['id'] = $k2; 
		    $getOrderStatus[$k2]['name'] = $v2; 
			$getOrderStatus[$k2]['count'] = (int)Db::name('express_order')->where(array('orderStatus'=>$k2,'closed'=>0))->count();
		}
		$this->assign('getOrderStatus',$getOrderStatus);
		$this->assign('count',$count);
		
		$this->assign('sumMoneyYuan',$sumMoneyYuan = (int)Db::name('express_order')->where($map)->sum('sumMoneyYuan'));
		$this->assign('sumMoneyYuan_old',$sumMoneyYuan_old = (int)Db::name('express_order')->where($map)->sum('sumMoneyYuan_old'));
		$this->assign('sumMoneyYuan_jia',$sumMoneyYuan_jia = (int)Db::name('express_order')->where($map)->sum('sumMoneyYuan_jia'));
		$this->assign('diffMoneyYuan',$diffMoneyYuan = (int)Db::name('express_order')->where($map)->sum('diffMoneyYuan'));
		
        return $this->fetch('express/index');
    }
	
	
	


	


	
	
}