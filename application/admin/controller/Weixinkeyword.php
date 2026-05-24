<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Weixinkeyword extends Base{
	
	
    private $create_fields = array('keyword', 'type', 'title', 'contents', 'url', 'photo');
    private $edit_fields = array('keyword', 'type', 'title', 'contents', 'url', 'photo');
    public function index(){
		$map = array();
        if($keyword = input('keyword','', 'htmlspecialchars')){
            $map['keyword'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = Db::name('weixin_keyword')->where($map)->count();
        $Page = new \Page($count, 15);
        $show = $Page->show();
        $list = Db::name('weixin_keyword')->where($map)->order(array('keyword_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
			if (model('WeixinKeyword')->checkKeyword($data['keyword'])){
				$this->jinMsg('关键字已经存在');
			}
			if (empty($data['type'])) {
				$this->jinMsg('类型不能为空');
			}
			$data['title'] = htmlspecialchars($data['title']);
			if (empty($data['contents'])) {
				$this->jinMsg('回复内容不能为空');
			}
			if ($words = model('SensitiveWords')->checkWords($data['contents'])){
				$this->jinMsg('内容含有敏感词：' . $words);
			}
			$data['url'] = htmlspecialchars($data['url']);
			$data['photo'] = htmlspecialchars($data['photo']);
			if (!empty($data['photo']) && !isImage($data['photo'])){
				$this->jinMsg('缩略图格式不正确');
			}
            if(Db::name('weixin_keyword')->insert($data)){
                $this->jinMsg('添加成功', url('weixinkeyword/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }
   
   
    public function edit($keyword_id = 0){
        if($keyword_id = (int) $keyword_id){
            if(!($detail = Db::name('weixin_keyword')->find($keyword_id))){
                $this->error('请选择要编辑的微信关键字');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['keyword'] = htmlspecialchars($data['keyword']);
				if(empty($data['keyword'])){
					$this->jinMsg('关键字不能为空');
				}
				if(empty($data['type'])){
					$this->jinMsg('类型不能为空');
				}
				$data['title'] = htmlspecialchars($data['title']);
				if(empty($data['contents'])){
					$this->jinMsg('回复内容不能为空');
				}
				if($words = model('SensitiveWords')->checkWords($data['contents'])) {
					$this->jinMsg('内容含有敏感词：' . $words);
				}
				$data['url'] = htmlspecialchars($data['url']);
				$data['photo'] = htmlspecialchars($data['photo']);
				if (!empty($data['photo']) && !isImage($data['photo'])){
					$this->jinMsg('缩略图格式不正确');
				}
                $data['keyword_id'] = $keyword_id;
                $local = model('WeixinKeyword')->checkKeyword($data['keyword']);
                if($local && $local['keyword_id'] != $keyword_id){
                    $this->jinMsg('关键字已经存在');
                }
                if (false !== Db::name('weixin_keyword')->update($data)){
                    $this->jinMsg('操作成功', url('weixinkeyword/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->jinMsg('请选择要编辑的微信关键字');
        }
    }
   
   
    public function delete($keyword_id = 0){
        if(is_numeric($keyword_id) && ($keyword_id = (int) $keyword_id)){
            Db::name('weixin_keyword')->where(array('keyword_id'=>$keyword_id))->delete();
            $this->jinMsg('删除成功', url('weixinkeyword/index'));
        }else{
            $keyword_id = input('keyword_id/a', false);
            if (is_array($keyword_id)) {
                foreach($keyword_id as $id){
                    Db::name('weixin_keyword')->where(array('keyword_id'=>$id))->delete($id);
                }
                $this->jinMsg('批量删除成功', url('weixinkeyword/index'));
            }
            $this->jinMsg('请选择要删除的微信关键字');
        }
    }
   
}