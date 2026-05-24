<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;


class Dayusms extends Base{

    public function index(){
		$map = array();
        $keyword = input('keyword','', 'htmlspecialchars');
        if ($keyword) {
            $map['mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword ', $keyword );
        }
		
		
		
		$getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		
        $count = Db::name('dayu_sms')->where($map)->count();
        $Page = new \Page($count, 50);
        $show = $Page->show();
        $list = Db::name('dayu_sms')->where($map)->order(array('sms_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('count', $count);
        return $this->fetch();
    }

    public function delete($sms_id = 0){
        if(is_numeric($sms_id) && ($asms_id = (int) $sms_id)){
            Db::name('dayu_sms')->where(array('sms_id'=>$sms_id))->delete();
            $this->jinMsg('删除成功', url('dayusms/index'));
        }else{
            $sms_id = input('sms_id/a', false);
            if(is_array($sms_id)) {
                foreach($sms_id as $id){
                    Db::name('dayu_sms')->where(array('sms_id'=>$id))->delete();
                }
                $this->jinMsg('删除成功', url('dayusms/index'));
            }
            $this->jinMsg('请选择要删除的大于短信记录');
        }
    }
   
   public function delete_drop(){
        Db::name('dayu_sms')->where('sms_id','gt',0)->delete();
        $this->jinMsg('清空短信记录成功', url('dayusms/index'));
    }
	
}