<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;


class Adsite extends Base{
	
    private $create_fields = array('site_name','theme','site_type','site_place','site_price');
    private $edit_fields = array('site_name','theme','site_type','site_place','site_price');
	
    public function index(){
		$adsite = Db::name('ad_site')->order(array('site_id' =>'desc'))->select();
        foreach($adsite as $k=>$val){
            $adsite[$k]['count'] = Db::name('ad')->where(array('site_id'=>$val['site_id'],'closed'=>'0'))->count();
        }
        $this->assign('adsite', $adsite);
        $this->assign('types', model('AdSite')->getType());
        $this->assign('place', model('AdSite')->getPlace());
        return $this->fetch();
    }
	
    public function create($site_place = 0){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['site_name'] = htmlspecialchars($data['site_name']);
			if(empty($data['site_name'])){
			   $this->jinMsg('广告位名称不能为空');
			}
			$data['site_price'] = (int) $data['site_price'];
			if(empty($data['site_price'])){
				$this->jinMsg('广告价格不能为空');
			}
			if($data['site_price'] < 1){
				$this->jinMsg('广告价格不正确');
			}
            if(Db::name('ad_site')->insert($data)){
                $this->jinMsg('添加成功', url('adsite/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            $this->assign('adsite', model('AdSite')->fetchAll());
            $this->assign('types', model('AdSite')->getType());
            $this->assign('place', model('AdSite')->getPlace());
			$this->assign('site_place',$site_place);
            return $this->fetch();
        
		}
    }
	
    public function edit($site_id = 0){
        if($site_id = (int) $site_id){
            $obj = model('AdSite');
            if(!($detail = Db::name('ad_site')->find($site_id))){
                $this->error('请选择需要编辑的广告位');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->create_fields);
				$data['site_name'] = htmlspecialchars($data['site_name']);
				if(empty($data['site_name'])){
					$this->jinMsg('广告位名称不能为空');
				}
				$data['site_price'] = (int) $data['site_price'];
				if(empty($data['site_price'])) {
					$this->jinMsg('广告价格不能为空');
				}
				if($data['site_price'] < 1){
					$this->jinMsg('广告价格不正确');
				}
                $data['site_id'] = $site_id;
                if (false !== Db::name('ad_site')->update($data)) {
                    $this->jinMsg('操作成功', url('adsite/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('adsite',model('AdSite')->fetchAll());
                $this->assign('types', model('AdSite')->getType());
                $this->assign('place', model('AdSite')->getPlace());
                $this->assign('detail', $detail);
                return $this->fetch();
                
            }
        } else {
            $this->error('请选择要编辑的商家分类');
        }
    }
	
    public function delete($site_id = 0){
        if($site_id = (int) $site_id){
			$count = Db::name('ad')->where('site_id',$site_id)->count();
			if($count >= 1){
				$this->jinMsg('该广告位下面还有广告');
			}
            Db::name('ad_site')->where('site_id',$site_id)->delete();
            $this->jinMsg('删除成功', url('adsite/index'));
        }else{
            $this->jinMsg('请选择要删除的广告位');
        }
    }

}