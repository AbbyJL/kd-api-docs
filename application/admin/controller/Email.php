<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;


class Email extends Base{

  
    public function index(){
        $map = array();
        $count = Db::name('email')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('email')->where($map)->order(array('email_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
    public function create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false),array('email_key', 'email_explain', 'email_tmpl'));
			$data['email_key'] = htmlspecialchars($data['email_key']);
			if(empty($data['email_key'])){
				$this->jinMsg('标签不能为空');
			}
			$data['email_explain'] = htmlspecialchars($data['email_explain']);
			if(empty($data['email_explain'])){
				$this->jinMsg('说明不能为空');
			}
			$data['email_tmpl'] = SecurityEditorHtml($data['email_tmpl']);
			if(empty($data['email_tmpl'])){
				$this->jinMsg('模版内容不能为空');
			}
            if(Db::name('email')->insert($data)){
                $this->jinMsg('添加成功', url('email/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }
	

	
    public function edit($email_id = 0){
        if($email_id = (int) $email_id){
            if(!($detail = Db::name('email')->find($email_id))){
                $this->error('请选择要编辑的邮件模版');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array('email_key', 'email_explain', 'email_tmpl'));
				$data['email_key'] = htmlspecialchars($data['email_key']);
				if(empty($data['email_key'])){
					$this->jinMsg('标签不能为空');
				}
				$data['email_explain'] = htmlspecialchars($data['email_explain']);
				if(empty($data['email_explain'])){
					$this->jinMsg('说明不能为空');
				}
				$data['email_tmpl'] = SecurityEditorHtml($data['email_tmpl']);
				if(empty($data['email_tmpl'])){
					$this->jinMsg('模版内容不能为空');
				}
                $data['email_id'] = $email_id;
                if (false !== Db::name('email')->update($data)){
                    $this->jinMsg('操作成功', url('email/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的邮件模版');
        }
    }
	
	
   
	
    public function delete($email_id = 0){
        if(is_numeric($email_id) && ($email_id = (int) $email_id)){
            Db::name('email')->update(array('email_id' => $email_id, 'is_open' => 0));
            $this->jinMsg('关闭成功', url('email/index'));
        }else{
            $email_id = input('email_id/a', false);
            if(is_array($email_id)){
                foreach ($email_id as $id){
                    Db::name('email')->update(array('email_id' => $id, 'is_open' => 0));
                }
                $obj->cleanCache();
                $this->jinMsg('关闭成功', url('email/index'));
            }
            $this->jinMsg('请选择要关闭的邮件模版');
        }
    }
	
    public function audit($email_id = 0){
        if(is_numeric($email_id) && ($email_id = (int) $email_id)){
            Db::name('email')->update(array('email_id' => $email_id, 'is_open' => 1));
            $this->jinMsg('开启成功', url('email/index'));
        }else{
            $email_id = input('email_id/a', false);
            if(is_array($email_id)){
                foreach ($email_id as $id){
                    Db::name('email')->update(array('email_id' => $id, 'is_open' => 1));
                }
                $this->jinMsg('开启成功', url('email/index'));
            }
            $this->jinMsg('请选择要开启的邮件模版');
        }
    }
}