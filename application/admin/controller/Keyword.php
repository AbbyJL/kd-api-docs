<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Keyword extends Base{
	

    private $create_fields = array('keyword', 'type');
    private $edit_fields = array('keyword', 'type');
    public function _initialize(){
        parent::_initialize();
        $this->type = model('Keyword')->getKeyType();
        $this->assign('types', $this->type);
    }
	
	
    public function index(){
        $map = array();
        if($keys = input('keys','', 'htmlspecialchars')){
            $map['keyword'] = array('LIKE', '%' . $keys . '%');
            $this->assign('keys', $keys);
        }
        $type = (int) input('type');
        if(!empty($type)){
            $map['type'] = $type;
            $this->assign('type', $type);
        }
        $count = Db::name('keyword')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('keyword')->where($map)->order(array('key_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
    public function create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['keyword'] = htmlspecialchars($data['keyword']);
			if (empty($data['keyword'])) {
				$this->jinMsg('关键字不能为空');
			}
			$data['type'] = (int) $data['type'];
			$data['create_time'] = time();
			$data['create_ip'] = request()->ip();
		
            if(Db::name('keyword')->insert($data)){
                $this->jinMsg('添加成功', url('keyword/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }
   
   
	
    public function edit($key_id = 0){
        if($key_id = (int) $key_id){
            if(!($detail = Db::name('keyword')->find($key_id))){
                $this->jinMsg('请选择要编辑的关键字');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['keyword'] = htmlspecialchars($data['keyword']);
				if(empty($data['keyword'])) {
					$this->jinMsg('关键字不能为空');
				}
				$data['type'] = (int) $data['type'];
		
                $data['key_id'] = $key_id;
                if (false !== Db::name('keyword')->update($data)){
                    $this->jinMsg('操作成功', url('keyword/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        } else {
            $this->jinMsg('请选择要编辑的关键字');
        }
    }
  
    public function delete($key_id = 0){
        if(is_numeric($key_id) && ($key_id = (int) $key_id)){
            Db::name('keyword')->where(array('key_id'=>$key_id))->delete();
            $this->jinMsg('删除成功', url('keyword/index'));
        }else{
            $key_id = input('key_id/a', false);
            if(is_array($key_id)){
                foreach ($key_id as $id){
                    Db::name('keyword')->where(array('key_id'=>$id))->delete();
                }
                $this->jinMsg('删除成功', url('keyword/index'));
            }
            $this->jinMsg('请选择要删除的关键字');
        }
    }
}