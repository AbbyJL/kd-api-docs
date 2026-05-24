<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Ad extends Base{
	
    private $create_fields = array('site_id','title','background','intro','city_id','link_url', 'photo', 'code', 'bg_date', 'end_date','is_target','is_wxapp','state','src','wb_src','xcx_name','appid','orderby');
    private $edit_fields = array('site_id','title','background','intro', 'city_id','link_url', 'photo', 'code', 'bg_date', 'end_date','is_target','is_wxapp','state','src','wb_src','xcx_name','appid','orderby');
    public function _initialize(){
        parent::_initialize();
        $this->citys = model('City')->fetchAll();
        $this->assign('citys', $this->citys);
    }
	
    public function index(){
		$map = array('closed' => 0);
        $keyword = input('keyword','', 'htmlspecialchars');
        if($keyword){
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
	    if($site_id = (int) input('site_id')){
            if($site_id != 999){
               $map['site_id'] = $site_id;
            }
            $this->assign('site_id', $site_id);
        }else{
            $this->assign('site_id', 999);
        }
		
	    $getSearchCityId = $this->getSearchCityId($this->city_id);
		if($getSearchCityId){
			$map['city_id'] = $getSearchCityId;
			$this->assign('city_id',$getSearchCityId);
		}
		
		if($is_target = (int) input('is_target')){
            if($is_target != 999){
               $map['is_target'] = $is_target;
            }
            $this->assign('is_target', $is_target);
        }else{
            $this->assign('is_target', 999);
        }
		if($is_wxapp = (int) input('is_wxapp')){
            if($is_wxapp != 999){
               $map['is_wxapp'] = $is_wxapp;
            }
            $this->assign('is_wxapp', $is_wxapp);
        }else{
            $this->assign('is_wxapp', 999);
        }
        $count = Db::name('ad')->where($map)->count();
        $Page = new \Page($count, 15);
        $show = $Page->show();
        $list = Db::name('ad')->where($map)->order(array('ad_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('sites', model('AdSite')->fetchAll());
        $this->assign('types', model('AdSite')->getType());
        return $this->fetch();
    }
	
    public function create($site_id = 0){
        if(request()->post()){
            $data = $this->createCheck();
            if(Db::name('ad')->insert($data)) {
                $this->jinMsg('添加成功', url('ad/index', array('site_id' => $site_id)));
            }
            $this->jinMsg('操作失败！');
        }else{
            $this->assign('site_id', $site_id);
            $this->assign('sites', model('AdSite')->fetchAll());
            $this->assign('types', model('AdSite')->getType());
            return $this->fetch();
        }
    }
	
    private function createCheck(){
        $data = $this->checkFields(input('data/a', false), $this->create_fields);
        $data['site_id'] = (int) $data['site_id'];
        if(empty($data['site_id'])) {
            $this->jinMsg('所属广告位不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if(empty($data['title'])) {
            $this->jinMsg('广告名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
       
        $data['link_url'] = $data['link_url'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if(!empty($data['photo']) && !isImage($data['photo'])) {
            $this->jinMsg('广告图片格式不正确');
        }
        $data['code'] = $data['code'];
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if(empty($data['bg_date'])) {
            $this->jinMsg('开始时间不能为空');
        }
        if(!isDate($data['bg_date'])) {
            $this->jinMsg('开始时间格式不正确');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if(empty($data['end_date'])) {
            $this->jinMsg('结束时间不能为空');
        }
        if(!isDate($data['end_date'])) {
            $this->jinMsg('结束时间格式不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
		$data['is_target'] = (int) $data['is_target'];
		$data['is_wxapp'] = (int) $data['is_wxapp'];
		$data['state'] = (int) $data['state'];
		$data['src'] = htmlspecialchars($data['src']);
		$data['wb_src'] = htmlspecialchars($data['wb_src']);
		$data['xcx_name'] = htmlspecialchars($data['xcx_name']);
		$data['appid'] = htmlspecialchars($data['appid']);
        $data['city_id'] = (int) $data['city_id'];
        return $data;
    }
	
	
	
    public function edit($ad_id = 0){
        if($ad_id = (int) $ad_id){
            if(!($detail = Db::name('ad')->find($ad_id))){
                $this->error('请选择要编辑的广告');
            }
            if(request()->post()){
                $data = $this->editCheck();
                $data['ad_id'] = $ad_id;
                if (false !== Db::name('ad')->update($data)){
                    $this->jinMsg('操作成功', url('ad/index', array('site_id' => $data['site_id'])));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                $this->assign('sites', model('AdSite')->fetchAll());
                $this->assign('types', model('AdSite')->getType());
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的广告');
        }
    }
	
    private function editCheck(){
        $data = $this->checkFields(input('data/a', false), $this->edit_fields);
        $data['site_id'] = (int) $data['site_id'];
        if(empty($data['site_id'])) {
            $this->jinMsg('所属广告位不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if(empty($data['title'])){
            $this->jinMsg('广告名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
      
        $data['link_url'] = $data['link_url'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if(!empty($data['photo']) && !isImage($data['photo'])) {
            $this->jinMsg('广告图片格式不正确');
        }
        $data['code'] = $data['code'];
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if(empty($data['bg_date'])) {
            $this->jinMsg('开始时间不能为空');
        }
        if(!isDate($data['bg_date'])) {
            $this->jinMsg('开始时间格式不正确');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if(empty($data['end_date'])){
            $this->jinMsg('结束时间不能为空');
        }
        if(!isDate($data['end_date'])){
            $this->jinMsg('结束时间格式不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
		$data['is_target'] = (int) $data['is_target'];
		$data['is_wxapp'] = (int) $data['is_wxapp'];
		$data['state'] = (int) $data['state'];
		$data['src'] = htmlspecialchars($data['src']);
		$data['wb_src'] = htmlspecialchars($data['wb_src']);
		$data['xcx_name'] = htmlspecialchars($data['xcx_name']);
		$data['appid'] = htmlspecialchars($data['appid']);
        $data['city_id'] = (int) $data['city_id'];
        return $data;
    }
	
    public function delete($ad_id = 0){
        if (is_numeric($ad_id) && ($ad_id = (int) $ad_id)) {
			$detail = Db::name('ad')->where(array('ad_id' => $ad_id))->find();
            Db::name('ad')->where('ad_id',$ad_id)->delete();
            $this->jinMsg('删除成功', url('ad/index',array('site_id'=>$detail['site_id'])));
        }else{
            $ad_id = input('ad_id/a', false);
            if(is_array($ad_id)){
                foreach ($ad_id as $id){
					Db::name('ad')->where('ad_id',$id)->delete();
                }
                $this->jinMsg('批量删除成功', url('adsite/index'));
            }
            $this->jinMsg('请选择要删除的广告');
        }
    }
	
	public function reset($ad_id = 0,$site_id = 0) {
        $ad_id = (int) $ad_id;
		$site_id = (int) $site_id;
		if(!empty($ad_id)){
			Db::name('ad')->update(array('ad_id' => $ad_id, 'click' => 0,'reset_time' => time()));
        	$this->jinMsg('更新点击量成功', url('ad/index',array('site_id'=>$site_id)));
		}else{
			$this->jinMsg('请选择要重置的广告点击量');
		}
        
    }
}