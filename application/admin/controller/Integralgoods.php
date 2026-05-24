<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;


class Integralgoods extends Base{

    private $create_fields = array('title','cate_id','is_index','face_pic','integral','price','money','num','limit_num','exchange_num','details','goods_details','process_details','attention_details','orderby');
    private $edit_fields = array('title','cate_id','is_index','face_pic','integral','price','money','num','limit_num','exchange_num','details','goods_details','process_details','attention_details','orderby');
	
	
	public function _initialize(){
        parent::_initialize();
		$this->assign('getTypes',$getTypes = model('IntegralExchange')->getTypes());
		$this->assign('cates',$cates =Db::name('integral_goods_cate')->limit(0,100)->select());
    }
	
	public function cate(){
        $list = Db::name('integral_goods_cate')->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
    
    public function cate_edit($cate_id = 0,$parent_id = 0){
		$cate_id = (int) $cate_id;
        $parent_id = (int) $parent_id;
		if(request()->post()){
			$data = $this->checkFields(input('data/a',false),array('cate_id','cate_name','photo','orderby'));
			$data['cate_name'] = htmlspecialchars($data['cate_name']);
			if(empty($data['cate_name'])){
				$this->tuError('分类不能为空');
			}
			$data['cate_id'] = (int) $data['cate_id'];
			$data['photo'] = htmlspecialchars($data['photo']);
			$data['orderby'] = (int) $data['orderby'];
		
			if($data['cate_id'] <= 0){
				$data['parent_id'] = $parent_id;
				$res= Db::name('integral_goods_cate')->insert($data);
				$intro = '添加成功';
			}else{
				$res= Db::name('integral_goods_cate')->update($data);
				$intro = '编辑成功';
			}
			
			if($res){
				$this->jinMsg($intro, url('integralgoods/cate'));
			}
			$this->jinMsg('操作失败');
		}else{
			$this->assign('parent_id', $parent_id);
			
			if(!$parent_id){
				$this->assign('detail',$detail = Db::name('integral_goods_cate')->find($cate_id));
			}
			echo $this->fetch();
		}
        
    }
    
    public function cate_delete($cate_id = 0){
        if(is_numeric($cate_id) && ($cate_id = (int) $cate_id)){
            Db::name('integral_goods_cate')->delete($cate_id);
            $this->jinMsg('删除成功', url('integralgoods/cate'));
        }else{
            $cate_id = input('cate_id/a','',false);
            if(is_array($cate_id)){
                foreach($cate_id as $id){
                     Db::name('integral_goods_cate')->delete($id);
                }
                $this->jinMsg('删除成功', url('integralgoods/cate'));
            }
            $this->jinMsg('请选择要删除的商家分类');
        }
    }
	
	
	
    public function cate_update(){
        $orderby = input('orderby/a','', false);
        foreach ($orderby as $key => $val){
            $data = array('cate_id' => (int) $key, 'orderby' => (int) $val);
             Db::name('integral_goods_cate')->update($data);
        }
        $this->jinMsg('更新成功', url('integralgoods/cate'));
    }
	
	
	
	
	
    public function index(){
		$map = array('closed' => 0);
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if($audit = (int) input('audit')){
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = Db::name('integral_goods')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
		
        $list = Db::name('integral_goods')->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
			$list[$k]['cate'] = Db::name('integral_goods_cate')->where(array('cate_id'=>$val['cate_id']))->find();
		}
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
    public function create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['title'] = htmlspecialchars($data['title']);
			if(empty($data['title'])){
				$this->jinMsg('产品名称不能为空');
			}
			
			$data['cate_id'] = (int) $data['cate_id'];
			$data['face_pic'] = htmlspecialchars($data['face_pic']);
			if (empty($data['face_pic'])) {
				$this->jinMsg('请上传产品图片');
			}
			if (!isImage($data['face_pic'])) {
				$this->jinMsg('产品图片格式不正确');
			}
			$data['integral'] = (int) $data['integral'];
			if (empty($data['integral'])) {
				$this->jinMsg('兑换'.$this->_CONFIG['integral']['name'].'不能为空');
			}
			$data['price'] = (int) $data['price'];
			if (empty($data['price'])) {
				$this->jinMsg('市场价格不能为空');
			}
			$data['num'] = (int) $data['num'];
			if (empty($data['num'])) {
				$this->jinMsg('库存数量不能为空');
			}
			$data['limit_num'] = (int) $data['limit_num'];
			if (empty($data['limit_num'])) {
				$this->jinMsg('限制单用户兑换数量不能为空');
			}
			$data['exchange_num'] = (int) $data['exchange_num'];
			
			$data['money'] = $data['money']*100;
			$data['create_time'] = time();
			$data['create_ip'] = request()->ip();
			$data['orderby'] = (int) $data['orderby'];
			$data['is_options'] = input('is_options');
			
            if($goods_id = Db::name('integral_goods')->insertGetId($data)){
				$photos = input('photos/a',false);
                if(!empty($photos)){ 
					model('IntegralGoods')->upload($goods_id,$photos);
                }
				if($data['is_options'] == 1){
					model('IntegralGoods')->IntegralGoodsOptions($goods_id,1,$type = '0');//更新多属性
				}
                $this->jinMsg('添加成功', url('integralgoods/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }
	
  
	
    public function edit($goods_id = 0){
        if($goods_id = (int) $goods_id){
            if(!($detail = Db::name('integral_goods')->find($goods_id))){
                $this->error('请选择要编辑的'.$this->_CONFIG['integral']['name'].'商品');
            }
            if(request()->post()){
                 $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['title'] = htmlspecialchars($data['title']);
				if (empty($data['title'])) {
					$this->jinMsg('产品名称不能为空');
				}
				$data['cate_id'] = (int) $data['cate_id'];
				$data['face_pic'] = htmlspecialchars($data['face_pic']);
				if (empty($data['face_pic'])) {
					$this->jinMsg('请上传产品图片');
				}
				if (!isImage($data['face_pic'])) {
					$this->jinMsg('产品图片格式不正确');
				}
				$data['integral'] = (int) $data['integral'];
				if (empty($data['integral'])) {
					$this->jinMsg('兑换'.$this->_CONFIG['integral']['name'].'不能为空');
				}
				$data['price'] = (int) $data['price'];
				if (empty($data['price'])) {
					$this->jinMsg('市场价格不能为空');
				}
				$data['num'] = (int) $data['num'];
				if (empty($data['num'])) {
					$this->jinMsg('库存数量不能为空');
				}
				$data['limit_num'] = (int) $data['limit_num'];
				if (empty($data['limit_num'])) {
					$this->jinMsg('限制单用户兑换数量不能为空');
				}
				$data['exchange_num'] = (int) $data['exchange_num'];
				
				$data['money'] = $data['money']*100;
				$data['create_time'] = time();
				$data['create_ip'] = request()->ip();
				$data['orderby'] = (int) $data['orderby'];
                $data['goods_id'] = $goods_id;
				$data['is_options'] = input('is_options');
                if(false !== Db::name('integral_goods')->update($data)){
					$photos = input('photos/a', false);
                    if(!empty($photos)){ 
						model('IntegralGoods')->upload($goods_id,$photos);
                    }else{
						Db::name('integral_goods_photos')->where(array('goods_id'=>$goods_id))->delete();
					}
					if($data['is_options'] == 1){
						model('IntegralGoods')->IntegralGoodsOptions($goods_id,1,$type = '1');
					}
                    $this->jinMsg('操作成功', url('integralgoods/index'));
                }
                $this->jinMsg('操作失败');
            }else{
				$this->assign('photos', model('IntegralGoods')->getPics($goods_id));
				$options = Db::name('integral_goods_options')->where(array('goods_id'=>$goods_id))->select();
				foreach($options as $k => $val){
					$options[$k]['price'] = round($val['price']/100,2);
				}
				$this->assign('options',$options);
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的'.$this->_CONFIG['integral']['name'].'商品');
        }
    }
	
	
    public function delete($goods_id = 0){
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)){
            Db::name('integral_goods')->update(array('goods_id' => $goods_id, 'closed' => 1));
            $this->jinMsg('删除成功！', url('integralgoods/index'));
        }else{
            $goods_id = input('goods_id/a', false);
            if(is_array($goods_id)){
                foreach ($goods_id as $id){
                    Db::name('integral_goods')->update(array('goods_id' => $id, 'closed' => 1));
                }
                $this->jinMsg('删除成功', url('integralgoods/index'));
            }
            $this->jinMsg('请选择要删除的'.$this->_CONFIG['integral']['name'].'商品');
        }
    }
	
	
    public function audit($goods_id = 0){
        if(is_numeric($goods_id) && ($goods_id = (int) $goods_id)){
            Db::name('integral_goods')->update(array('goods_id' => $goods_id, 'audit' => 1));
            $this->jinMsg('审核成功', url('integralgoods/index'));
        }else{
            $goods_id = input('goods_id/a', false);
            if(is_array($goods_id)){
                foreach ($goods_id as $id){
                    Db::name('integral_goods')->update(array('goods_id' => $id, 'audit' => 1));
                }
                $this->jinMsg('审核成功', url('integralgoods/index'));
            }
            $this->jinMsg('请选择要审核的'.$this->_CONFIG['integral']['name'].'商品');
        }
    }
}