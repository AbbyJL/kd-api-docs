<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Links extends Base{
	
    private $create_fields = array('city_id','link_name', 'link_url','link_email','link_intro','orderby');
    private $edit_fields = array('city_id','link_name', 'link_url','link_email','link_intro','orderby');
	
	
    public function index(){
		$map = array('colsed' => 0);
		if($keyword = input('keyword','', 'htmlspecialchars')){
            $map['link_name|link_url'] = array('LIKE', '%' . $keyword . '%');
			$this->assign('keyword', $keyword);
        }
        $getSearchCityId = $this->getSearchCityId($this->city_id);
		if($getSearchCityId){
			$map['city_id'] = $getSearchCityId;
			$this->assign('city_id',$getSearchCityId);
		}
		$count = Db::name('links')->where($map)->count(); 
        $Page = new \Page($count, 15);
        $show = $Page->show(); 
        $list = Db::name('links')->where($map)->order(array('orderby' => 'asc','create_time' => 'desc'))->select();
		$this->assign('citys', model('City')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
    public function create(){
        if(request()->post()){
            $$data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['city_id'] = (int) $data['city_id'];
			if(empty($data['city_id'])){
				$this->jinMsg('城市不能为空');
			}
			$data['link_name'] = htmlspecialchars($data['link_name']);
			if(empty($data['link_name'])) {
				$this->jinMsg('链接名称不能为空');
			}
			$data['link_url'] = htmlspecialchars($data['link_url']);
			if (empty($data['link_url'])) {
				$this->jinMsg('链接地址不能为空');
			}
			$data['link_email'] = htmlspecialchars($data['link_email']);
			$data['link_intro'] = htmlspecialchars($data['link_intro']);
			$data['orderby'] = (int) $data['orderby'];
			$data['create_time'] = time();
			$data['create_ip'] = request()->ip();
		
            if(Db::name('links')->insert($data)){
                $this->jinMsg('添加成功', url('links/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }
	
   
	
    public function edit($link_id = 0){
        if($link_id = (int) $link_id){
            if(!($detail = Db::name('links')->find($link_id))){
                $this->error('请选择要编辑的友情链接');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['city_id'] = (int) $data['city_id'];
				if (empty($data['city_id'])) {
					$this->jinMsg('城市不能为空');
				}
				$data['link_name'] = htmlspecialchars($data['link_name']);
				if (empty($data['link_name'])) {
					$this->jinMsg('链接名称不能为空');
				}
				$data['link_url'] = htmlspecialchars($data['link_url']);
				if (empty($data['link_url'])) {
					$this->jinMsg('链接地址不能为空');
				}
				$data['link_email'] = htmlspecialchars($data['link_email']);
				$data['link_intro'] = htmlspecialchars($data['link_intro']);
				$data['orderby'] = (int) $data['orderby'];
		
                $data['link_id'] = $link_id;
                if(false !== Db::name('links')->update($data)){
                    $this->jinMsg('操作成功', url('links/index'));
                }
                $this->jinMsg('操作失败');
            }else{
				$this->assign('areas', model('Area')->select());
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的友情链接');
        }
    }
	
   
	
    public function delete($link_id = 0){
        if(is_numeric($link_id) && ($link_id = (int) $link_id)){
            Db::name('links')->update(array('link_id' => $link_id, 'colsed' => 1));
            $this->jinMsg('删除成功', url('links/index'));
        }else{
            $link_id = input('link_id/a', false);
            if(is_array($link_id)) {
                foreach ($link_id as $id){
                    Db::name('links')->update(array('link_id' => $id, 'colsed' => 1));
                }
                $this->jinMsg('删除成功', url('links/index'));
            }
            $this->jinMsg('请选择要删除的友情链接');
        }
    }
	
	
	public function audit($link_id = 0){
        if(is_numeric($link_id) && ($link_id = (int) $link_id)){
            Db::name('links')->update(array('link_id' => $link_id, 'audit' => 1));
            $this->jinMsg('审核成功', url('links/index'));
        }else{
            $link_id = input('link_id/a', false);
            if(is_array($link_id)){
                foreach($link_id as $id){
                    Db::name('links')->update(array('link_id' => $id, 'audit' => 1));
                }
                $this->jinMsg('审核成功', url('links/index'));
            }
            $this->jinMsg('请选择要审核的友情链接');
        }
    }
}