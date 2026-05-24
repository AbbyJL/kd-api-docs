<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;


class Weixinshare extends Base{
	
	 public function index(){
		$map = array();
        if($share_id = (int) input('share_id')){
            $map['share_id'] = $share_id;
            $this->assign('share_id', $share_id);
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
		
        $count = Db::name('weixin_share')->where($map)->count(); 
        $Page = new \Page($count, 20); 
        $show = $Page->show(); 
        $list = Db::name('weixin_share')->where($map)->order(array('share_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach($list as $k => $val){
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        return $this->fetch(); 
    }
	
	public function delete($share_id = 0){
        if (is_numeric($share_id) && ($share_id = (int) $share_id)){
            Db::name('weixin_share')->where(array('share_id'=>$share_id))->delete();
            $this->jinMsg('删除分享日志成功', url('weixinshare/index'));
        }else{
            $share_id = input('share_id/a', false);
            if(is_array($share_id)){
                foreach ($share_id as $id){
                    Db::name('weixin_share')->where(array('share_id'=>$id))->delete();
                }
                $this->jinMsg('批量删除分享日志成功', url('weixinshare/index'));
            }
            $this->jinMsg('请选择要删除分享日志');
        }
    }
	
	
	public function delete_drop(){
        Db::name('weixin_share')->where('share_id','gt',0)->delete();
		
        $this->jinMsg('清空全部微信分享日志成功', url('weixinshare/index'));
    }
}