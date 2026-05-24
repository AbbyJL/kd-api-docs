<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Dayu extends Base{

	public function index(){
		$map = array();
		$count = Db::name('dayu_tag')->where($map)->count(); 
        $Page = new \Page($count,40); 
        $show = $Page->show(); 
		$list = Db::name('dayu_tag')->order(array('dayu_id' =>'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
			$list[$k]['num_1'] = (int)Db::name('DayuSms')->where(array('status'=>1,'code'=>$val['dayu_local']))->count();//成功
			$list[$k]['num_0'] = (int)Db::name('DayuSms')->where(array('status'=>0,'code'=>$val['dayu_local']))->count();//失败
        }
		$this -> assign('tag', $list);
        $this->assign('page', $show); 
		return $this->fetch();
	}


	public function create(){
		if(request()->post()){
			$data = input('data/a', false);
			$data['dayu_name'] = htmlspecialchars($data['dayu_name']);
			$data['dayu_tag'] = htmlspecialchars($data['dayu_tag']);
			$data['dayu_country_tag'] = htmlspecialchars($data['dayu_country_tag']);
			$data['qcloudsms_id'] = htmlspecialchars($data['qcloudsms_id']);
			$data['dayu_note'] = htmlspecialchars($data['dayu_note']);

			$info = Db::name('dayu_tag')->where(array('dayu_id'=>$dayu_id))->find();
			if($info['dayu_tag'] != $data['dayu_tag']){
				$check_info = model('DayuTag')->where(array('dayu_tag'=>$data['dayu_tag']))->select();
				if(count($check_info) > 0){
					$this->jinMsg('模板ID已存在');
				}
			}
			if($data['dayu_name'] == NULL){
				$this->jinMsg('模板名不能为空');
			}
			if($data['dayu_note'] == NULL){
				$this->jinMsg('模板说明不能为空');
			}
			if(Db::name('dayu_tag')->insert($data)){
				$this->jinMsg('操作成功', url('dayu/index'));
			}
			$this->jinMsg('操作失败');
		}else{
			return $this->fetch();
		}
	}

	public function edit($dayu_id){
		if(request()->post()){
			$data = input('data/a', false);
			$data['dayu_name'] = htmlspecialchars($data['dayu_name']);
			$data['dayu_tag'] = htmlspecialchars($data['dayu_tag']);
			$data['dayu_country_tag'] = htmlspecialchars($data['dayu_country_tag']);
			$data['qcloudsms_id'] = htmlspecialchars($data['qcloudsms_id']);
			$data['dayu_note'] = htmlspecialchars($data['dayu_note']);
			
			$info = model('DayuTag')->where(array('dayu_id'=>$dayu_id))->find();
			if($info['dayu_tag'] != $data['dayu_tag']){
				$check_info = model('DayuTag')->where(array('dayu_tag'=>$data['dayu_tag']))->select();
				if(count($check_info) > 0){
					$this->jinMsg('模板ID已存在');
				}
			}
			if($data['dayu_name'] == NULL){
				$this->jinMsg('模板名不能为空');
			}
			if($data['dayu_note'] == NULL){
				$this->jinMsg('模板说明不能为空');
			}
			if(Db::name('dayu_tag')->where(array('dayu_id'=>$dayu_id))->update($data)){
				$this->jinMsg('操作成功', url('dayu/index'));
			}
			$this->jinMsg('操作失败');
		}else{
			$this->assign('info',$info = Db::name('dayu_tag')->where(array('dayu_id'=>$dayu_id))->find());
			return $this->fetch();
		}
	}

	public function delete($dayu_id = 0){
		if(is_numeric($dayu_id) && ($dayu_id = (int) $dayu_id)){
			if(Db::name('dayu_tag')->update(array('dayu_id' => $dayu_id,'is_open' => 0))){
				$this->jinMsg('操作成功', url('dayu/index'));
			}
			$this->jinMsg('操作失败');
		}else{
			$dayu_ids = input('dayu_id/a', false);
            if(is_array($dayu_ids)){
                foreach($dayu_ids as $id){
					Db::name('dayu_tag')->update(array('dayu_id' => $id,'is_open' => 0));
                }
                $this->jinMsg('操作成功', url('dayu/index'));
            }
            $this->jinMsg('操作失败');
		}
	}
	
	
	
	public function audit($dayu_id = 0){
        if(is_numeric($dayu_id) && ($dayu_id = (int) $dayu_id)){
            Db::name('dayu_tag')->update(array('dayu_id' => $dayu_id,'is_open' => 1));
            $this->jinMsg('开启成功', url('Dayu/index'));
        }else{
            $dayu_id = input('dayu_id/a', false);
            if(is_array($dayu_id)){
                foreach($dayu_id as $id){
                    Db::name('dayu_tag')->update(array('dayu_id' =>$id,'is_open' => 1));
                }
                $this->jinMsg('开启成功', url('Dayu/index'));
            }
            $this->jinMsg('请选择要开启的短信模版');
        }
    }
}