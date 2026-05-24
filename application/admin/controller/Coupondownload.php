<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Coupondownload extends Base{

    public function index(){
        $map = array();
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if($is_used = (int) input('is_used')){
            $map['is_used'] = $is_used === 1 ? 1 : 0;
            $this->assign('is_used', $is_used);
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
		
		
        $count = Db::name('coupon_download')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('coupon_download')->where($map)->order(array('download_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $coupons = array();
        foreach($list as $k => $val){
			$list[$k]['coupon'] = Db::name('coupon')->where(array('coupon_id' =>$val['coupon_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	//批量失效
	public function used($download_id = 0){
        if(is_numeric($download_id) && ($download_id = (int) $download_id)){
			Db::name('coupon_download')->update(array('download_id'=>$download_id,'is_used' => 1,'used_time' => time(),'used_ip' =>request()->ip()));
            $this->jinMsg('操作成功', url('coupondownload/index'));
        }else{
            $download_id = input('download_id/a', false);
            if(is_array($download_id)){
                foreach($download_id as $id){
                    Db::name('coupon_download')->update(array('download_id'=>$id,'is_used' => 1,'used_time' => time(),'used_ip' =>request()->ip()));
                }
                $this->jinMsg('操作成功', url('coupondownload/index'));
            }
            $this->jinMsg('操作失败');
        }
    }
}