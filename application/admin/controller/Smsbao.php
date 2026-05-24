<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;


class Smsbao extends Base{
	

    public function index(){
		$map = array();
        $keyword = input('keyword','', 'htmlspecialchars');
        if($keyword) {
            $map['mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword ', $keyword );
        }
		
		
		$getSearchDate = $this->getSearchDate();//时间搜索
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
		
        $count = Db::name('sms_bao')->where($map)->count();
        $Page = new \Page($count, 50);
        $show = $Page->show();
        $list = Db::name('sms_bao')->where($map)->order(array('sms_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('count', $count);
		$this->assign('types', model('SmsBao')->getType());
        return $this->fetch();
    }

    public function delete($sms_id = 0){
        if(is_numeric($sms_id) && ($asms_id = (int) $sms_id)){
            Db::name('sms_bao')->where('sms_id',$sms_id)->delete();
            $this->jinMsg('删除成功', url('smsbao/index'));
        }else{
            $sms_id = input('sms_id/a',false);
            if(is_array($sms_id)){
                foreach ($sms_id as $id){
                    Db::name('sms_bao')->where(array('sms_id'=>$id))->delete();
                }
                $this->jinMsg('批量删除成功', url('smsbao/index'));
            }
            $this->jinMsg('请选择要删除的短信宝短信记录');
        }
    }
	
	public function delete_drop(){
        Db::name('sms_bao')->where('sms_id','gt',0)->delete();
        $this->jinMsg('清空短信记录成功', url('smsbao/index'));
    }
   
}