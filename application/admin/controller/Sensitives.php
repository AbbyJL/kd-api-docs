<?php
namespace app\admin\controller;

use think\Model;
use think\Db;



class Sensitives extends Base{

  
    public function index(){
        $map = array();
        if($keyword = input('keyword','', 'htmlspecialchars')){
            $map['words'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = Db::name('sensitive_words')->where($map)->count();
        $Page = new \Page($count,50);
        $show = $Page->show();
        $list = Db::name('sensitive_words')->where($map)->order(array('words_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
    public function create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false),array('words'));
			$data['words'] = htmlspecialchars($data['words']);
			if(empty($data['words'])) {
				$this->jinMsg('关键词不能为空');
			}
			$list = explode('|',$data['words']); 
			$i = 0;
			foreach($list as $key => $val){
				if($val){
					if(!$res = Db::name('sensitive_words')->where('words',$val)->find()){
						$i++;
						Db::name('sensitive_words')->insert(array('words' => $val));
					}
				}
            }
			if($i > 0){
				model('SensitiveWords')->cleanCache();
				$this->jinMsg('添加成功'.$i.'条数据', url('sensitives/index'));
			}else{
				$this->jinMsg('操作失败或者敏感词已存在');
			}
        }else{
            echo $this->fetch();
        }
    }
	
  
    public function edit($words_id = 0){
        if($words_id = (int) $words_id){
            if(!($detail = Db::name('sensitive_words')->find($words_id))){
                $this->error('请选择要编辑的敏感词');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array('words'));
				$data['words'] = htmlspecialchars($data['words']);
				if(empty($data['words'])){
					$this->jinMsg('关键词不能为空');
				}
                $data['words_id'] = $words_id;
                if(false !== Db::name('sensitive_words')->update($data)){
					model('SensitiveWords')->cleanCache();
                    $this->jinMsg('操作成功', url('sensitives/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                echo $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的敏感词');
        }
    }

	//批量删除
    public function delete($words_id = 0){
        if(is_numeric($words_id) && ($words_id = (int) $words_id)){
            Db::name('sensitive_words')->where(array('words_id'=>$words_id))->delete();
			model('SensitiveWords')->cleanCache();
            $this->jinMsg('删除成功', url('sensitives/index'));
        }else{
            $words_id = input('words_id/a', false);
            if(is_array($words_id)){
                foreach($words_id as $id){
                    Db::name('sensitive_words')->where(array('words_id'=>$id))->delete();
                }
				model('SensitiveWords')->cleanCache();
                $this->jinMsg('删除成功', url('sensitives/index'));
            }
            $this->jinMsg('请选择要删除的敏感词');
        }
    }
	
	//删除全部敏感词
	public function delete_drop(){
        Db::name('sensitive_words')->where('words_id','gt',0)->delete();
        $this->jinMsg('清空全部敏感词成功', url('sensitives/index'));
    }
   
}