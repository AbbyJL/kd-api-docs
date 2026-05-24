<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;

class Weixinmsg extends Base{
	
	 public function index(){
		$map = array();
        if($msg_id = (int) input('msg_id')){
            $map['msg_id'] = $msg_id;
            $this->assign('msg_id', $msg_id);
        }
		
		if($cate= (int) input('cate')){
            $map['cate'] = $cate;
            $this->assign('cate', $cate);
        }
		
		if($is_send= (int) input('is_send')){
            if($is_send != 999){
                $map['is_send'] = $is_send;
            }
            $this->assign('is_send', $is_send);
        }else{
            $this->assign('is_send', 999);
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
        $count = Db::name('weixin_msg')->where($map)->count(); 
        $Page = new \Page($count,25); 
        $show = $Page->show(); 
        $list = Db::name('weixin_msg')->where($map)->order(array('msg_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
			$list[$k]['serial']  = Db::name('weixin_tmpl')->where(array('template_id'=>$val['template_id']))->find();
        }
		
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
		$this->assign('count',(int)$count); 
        return $this->fetch(); 
    }
	
	 public function delete($msg_id = 0){
        if(is_numeric($msg_id) && ($msg_id = (int) $msg_id)){
            Db::name('weixin_msg')->where(array('msg_id'=>$msg_id))->delete();
            $this->jinMsg('删除模板消息成功', url('weixinmsg/index'));
        }else{
            $msg_id = input('msg_id/a', false);
            if(is_array($msg_id)){
                foreach($msg_id as $id){
                    Db::name('weixin_msg')->where(array('msg_id'=>$id))->delete();
                }
                $this->jinMsg('批量删除模板消息成功', url('weixinmsg/index'));
            }
            $this->jinMsg('请选择要删除模板消息');
        }
    }
	
	public function delete_drop(){
        Db::name('weixin_msg')->where('msg_id','gt',0)->delete();
        $this->jinMsg('清空全部模板消息成功', url('weixinmsg/index'));
    }
}