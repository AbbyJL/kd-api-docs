<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;

class Menu extends Base{
    private $create_fields = array('parent_id', 'menu_name');
    private $edit_fields = array('parent_id', 'menu_name');
	
	
    public function index(){
        $menu = Db::name('menu')->order(array('orderby' =>'asc'))->select();
        $this->assign('datas', $menu);
        return $this->fetch();
    }
	
	
	
    public function create($parent_id = 0){
        if(request()->post()){
             $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['parent_id'] = (int) $data['parent_id'];
			if (empty($data['menu_name'])) {
				$this->jinMsg('请输入菜单名称');
			}
			$data['menu_name'] = htmlspecialchars($data['menu_name'], ENT_QUOTES, 'UTF-8');
			$data['is_show'] = 1;
		
            $obj = model('Menu');
            if($obj->insert($data)){
                $obj->cleanCache();
                $this->jinMsg('添加成功', url('menu/index'));
            }
            $this->jinMsg('操作失败！');
        }else{
            $menu = model('Menu')->fetchAll();
            $this->assign('datas', $menu);
            $this->assign('parent_id', (int) $parent_id);
            echo $this->fetch();
        }
    }
    public function action($parent_id = 0){
        if(!($parent_id = (int) $parent_id)){
            $this->error('请选择正确的父级菜单');
        }
        if(request()->post()){
            $data = input('data/a', false);
            $new = input('new/a', false);
            $obj = model('Menu');
			
			if(!empty($data)){
				foreach ($data as $k => $val){
					$local = array();
					$local['menu_id'] = (int) $k;
					$local['menu_name'] = htmlspecialchars($val['menu_name'], ENT_QUOTES, 'UTF-8');
					$local['orderby'] = (int) $val['orderby'];
					$local['menu_action'] = htmlspecialchars($val['menu_action'], ENT_QUOTES, 'UTF-8');
					$local['is_show'] = (int) $val['is_show'];
					if (!empty($local['menu_name']) && !empty($local['menu_id']) && !empty($val['menu_action'])){
						$obj->update($local);
					}
				}
			}
            if(!empty($new)){
                foreach ($new as $k => $val){
                    $local = array();
                    $local['menu_name'] = htmlspecialchars($val['menu_name'], ENT_QUOTES, 'UTF-8');
                    $local['orderby'] = (int) $val['orderby'];
                    $local['menu_action'] = htmlspecialchars($val['menu_action'], ENT_QUOTES, 'UTF-8');
                    $local['is_show'] = (int) $val['is_show'];
                    $local['parent_id'] = $parent_id;
                    if (!empty($local['menu_name']) && !empty($val['menu_action'])) {
                        $obj->insert($local);
                    }
                }
            }
            $obj->cleanCache();
            $this->jinMsg('更新成功', url('menu/index'));
        }else{
            $menu = model('Menu')->fetchAll();
            $this->assign('datas', $menu);
            $this->assign('parent_id', $parent_id);
            echo $this->fetch();
        }
    }
	
	
    public function update(){
        $orderby = input('orderby/a', false);
        $obj = model('Menu');
        foreach ($orderby as $key => $val){
            $data = array('menu_id' => (int) $key, 'orderby' => (int) $val);
            $obj->update($data);
        }
        $obj->cleanCache();
        $this->jinMsg('更新成功', url('menu/index'));
    }
	
	
	
    public function edit($menu_id = 0){
        if ($menu_id = (int) $menu_id){
            $obj = model('Menu');
            $menu = $obj->fetchAll();
            if(!isset($menu[$menu_id])){
                $this->jinMsg('请选择要编辑的菜单');
            }
            if(request()->post()){
				
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['parent_id'] = (int) $data['parent_id'];
				if(empty($data['menu_name'])){
					$this->jinMsg('请输入菜单名称');
				}
				$data['menu_name'] = htmlspecialchars($data['menu_name'], ENT_QUOTES, 'UTF-8');
				
				$data['is_show'] = 1;
                $data['menu_id'] = $menu_id;
                if($obj->update($data)){
                    $obj->cleanCache();
                    $this->jinMsg('操作成功', url('menu/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $menu[$menu_id]);
                $this->assign('datas', $menu);
                echo $this->fetch();
            }
        }else{
            $this->jinMsg('请选择要编辑的菜单');
        }
    }
	
	public function is_show($menu_id = 0){
        if($menu_id = (int) $menu_id){
            $obj = model('Menu');
			if($menu_id == 1 || $menu_id == 11){
				$this->jinMsg('系统菜单无法操作');
			}
			if($detail = $obj->find($menu_id)){
				$data = array('menu_id' => $menu_id, 'is_show' => 0);
				if($detail['is_show'] == 0){
					$data['is_show'] = 1;
				}
				if($obj->update($data)){
					$obj->cleanCache();
					$this->jinMsg('操作成功', url('menu/index'));
				}else{
					 $this->jinMsg('操作失败');
				}
			}else{
				$this->jinMsg('菜单不存在');
			}
        }else{
			$this->jinMsg('请选择要删除的菜单');
		}
    }
	
	
    public function delete($menu_id = 0) {
        if($menu_id = (int) $menu_id){
            $obj = model('Menu');
            $menu = $obj->fetchAll();
            foreach($menu as $val) {
                if($val['parent_id'] == $menu_id){
                    $this->jinMsg('该菜单下还有其他子菜单');
                }
            }
            $obj->where(array('menu_id'=>$menu_id))->delete();
            $obj->cleanCache();
            $this->jinMsg('删除菜单成功', url('menu/index'));
        }
        $this->jinMsg('请选择要删除的菜单');
    }
	
	
}