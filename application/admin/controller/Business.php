<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;

class Business extends Base{
	
 	
	private $create_fields = array('business_name','business_name1','user_id','ratio','rise','price','rise2','rise3','out_price','business_attorn_price','intro','business_attorn_intro','pic','photo','lng','lat','orderby');
    private $edit_fields = array(
        'business_name','business_name1','user_id','ratio','rise','price','rise2','rise3','out_price','business_attorn_price',
        'intro','business_attorn_intro','pic','photo','lng','lat','orderby','is_print','partner','mKey','machine_code',
        'open','cate_name','tag','img','shou','xu','ratio1','priceA_type','priceA_ratio','priceA_price','priceB_type','priceB_ratio','priceB_price','is_yuyue','lanshou'
    );
    private $area_id = '';
	
	
    public function _initialize(){
        parent::_initialize();
        $this->area_id = (int) (int) input('area_id');
        $this->assign('area_id', $this->area_id);
    }

	
	
    public function index(){
        import('ORG.Util.Page');
		
		if($this->area_id){
			$map = array('area_id' => $this->area_id);	
		}
        $keyword = input('keyword','','htmlspecialchars');
        if($keyword){
            $map['business_name'] = array('LIKE', '%' . $keyword . '%');
        }
		$city_id = (int) input('city_id');
        if($area_id = (int) input('area_id')){
            $map['area_id'] = $area_id;
			$area = Db::name('area')->find($area_id);
			$this->assign('cityId',$area['city_id'] ? $area['city_id'] : $city_id );
            $this->assign('areaId', $area_id);
        }

        $count = Db::name('business')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('business')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $list[$k]['area'] = Db::name('copy_area')->where(array('area_id'=>$val['area_id']))->find();
            $list[$k]['city'] = Db::name('copy_city')->where(array('city_id'=>$list[$k]['area']['city_id']))->find();
            $list[$k]['province'] = Db::name('copy_province')->where(array('id'=>$list[$k]['city']['ParentId']))->find();
			$user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('keyword', $keyword);
		$this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
		
		$this->assign('area_id',$this->area_id);
        return $this->fetch();
    }
	
	
    public function create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['business_name'] = htmlspecialchars($data['business_name']);
			if(empty($data['business_name'])){
				$this->jinMsg('商圈名称不能为空');
			}
			$data['user_id'] = (int) $data['user_id'];
			$data['ratio'] = (int) ($data['ratio']*100);
			$data['area_id'] = $this->area_id;
			if(empty($data['area_id'])){
				$this->jinMsg('所在区域不能为空，请从商圈列表里面点击进商圈后编辑');
			}
			$data['orderby'] = (int) $data['orderby'];
            if(Db::name('business')->insertGetId($data)) {
                $this->jinMsg('添加成功', url('business/index', array('area_id' => $this->area_id)));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }
	
	
	
    public function edit($business_id = 0){
        if($business_id = (int) $business_id){
            if(!($detail = Db::name('business')->find($business_id))){
                $this->jinMsg('请选择要编辑的商圈管理');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['business_name'] = htmlspecialchars($data['business_name']);
				if (empty($data['business_name'])) {
					$this->jinMsg('商圈名称不能为空');
				}
				$data['user_id'] = (int) $data['user_id'];
				$data['ratio'] = (int) ($data['ratio']*100);
                $data['business_id'] = $business_id;
                if (false !== Db::name('business')->update($data)){
                    $this->jinMsg('操作成功', url('business/index', array('area_id' => $this->area_id)));
                }
                $this->jinMsg('操作失败');
            }else{
				$this->assign('user', Db::name('users')->where(array('user_id'=>$detail['user_id']))->find());
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->jinMsg('请选择要编辑的商圈管理');
        }
    }
	
	
	
	
    public function hots($business_id){
        if($business_id = (int) $business_id){
            if(!($detail = Db::name('business')->find($business_id))){
                $this->jinMsg('请选择商圈');
            }
            $detail['is_hot'] = $detail['is_hot'] == 0 ? 1 : 0;
            Db::name('business')->update(array('business_id' => $business_id, 'is_hot' => $detail['is_hot']));
            $this->jinMsg('操作成功', url('business/index', array('area_id' => $this->area_id)));
        }else{
            $this->jinMsg('请选择商圈');
        }
    }


    public function delete(){
        if(is_numeric($_GET['business_id']) && ($business_id = (int) $_GET['business_id'])){
            Db::name('business')->where(array('business_id'=>$business_id))->delete();
            $this->jinMsg('删除成功', url('business/index', array('area_id' => $this->area_id)));
        }else{
            $business_id = input('business_id/a', false);
            if(is_array($business_id)){
                foreach($business_id as $id){
                    Db::name('business')->where(array('business_id'=>$id))->delete();
                }
            
                $this->jinMsg('删除成功', url('business/index', array('area_id' => $this->area_id)));
            }
            $this->jinMsg('请选择要删除的商圈管理');
        }
    }
	
	
    public function child($area_id = 0){
        $datas = model('Business')->fetchAll();
        $str = '<option value="0">请选择</option>';
        foreach($datas as $val){
            if($val['area_id'] == $area_id){
                $str .= '<option value="' . $val['business_id'] . '">' . $val['business_name'] . '</option>';
            }
        }
        echo $str;
        die;
    }

    public function community(){
        import('ORG.Util.Page');

        if($this->area_id){
            $map = array('area_id' => $this->area_id);
        }
        $keyword = input('keyword','','htmlspecialchars');
        if($keyword){
            $map['business_name'] = array('LIKE', '%' . $keyword . '%');
        }

        $business_id = (int) input('business_id');
        $this->assign('business_id', $business_id);


        $count = Db::name('business_community')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('business_community')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $list[$k]['business'] = Db::name('copy_business')->where(array('business_id'=>$val['business_id']))->find();
            $list[$k]['area'] = Db::name('copy_area')->where(array('area_id'=> $list[$k]['business']['area_id']))->find();
            $list[$k]['city'] = Db::name('copy_city')->where(array('city_id'=>$list[$k]['area']['city_id']))->find();
            $list[$k]['province'] = Db::name('copy_province')->where(array('id'=>$list[$k]['city']['ParentId']))->find();
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('keyword', $keyword);
        $this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);

        $this->assign('area_id',$this->area_id);
        return $this->fetch();
    }


    public function community_create($business_id=0){
        $business_id = (int) input('business_id');
        $this->assign('business_id', $business_id);

        $business = Db::name('business')->where(array('business_id'=>$business_id))->find();
        $this->assign('business',$business);

        if(request()->post()){
            $data = $this->checkFields(input('data/a', false),array(
                'name','name1','user_id','ratio', 'open','cate_name','tag','img','shou','xu','ratio1',
                'priceA_type','priceA_ratio','priceA_price','priceB_type','priceB_ratio','priceB_price','is_yuyue','lanshou'
            ));
            $data['name'] = htmlspecialchars($data['name']);
            if(empty($data['name'])){
                $this->jinMsg('名称不能为空');
            }
            $data['user_id'] = (int) $data['user_id'];
            $data['ratio'] = (int) ($data['ratio']*100);
            $data['shou'] = (int) ($data['shou']*100);
            $data['xu'] = (int) ($data['xu']*100);
            $data['orderby'] = (int) $data['orderby'];

            $data['area_id'] = (int) $business['area_id'];
            $data['business_id'] = (int) $business_id;

            if(!$data['business_id']){
                $this->jinMsg('乡镇ID不能为空请从乡镇列表进去添加');
            }
            if(Db::name('business_community')->insertGetId($data)) {
                $this->jinMsg('添加成功', url('business/community', array('area_id' => $this->area_id)));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }





    public function community_edit($community_id=0,$business_id = 0){

        $community_id = (int) input('community_id');
        $this->assign('community_id', $community_id);

        $business_id = (int) input('business_id');
        $this->assign('business_id', $business_id);

        $business = Db::name('business')->where(array('business_id'=>$business_id))->find();
        $this->assign('business',$business);

        if($community_id = (int) $community_id){
            if(!($detail = Db::name('business_community')->find($community_id))){
                $this->jinMsg('请选择要编辑的管理');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array(
                    'name','name1','user_id','ratio', 'open','cate_name','tag','img','shou','xu','ratio1','is_print','partner','mKey','machine_code',
                    'priceA_type','priceA_ratio','priceA_price','priceB_type','priceB_ratio','priceB_price','is_yuyue','lanshou'
                ));
                $data['name'] = htmlspecialchars($data['name']);
                if (empty($data['name'])) {
                    $this->jinMsg('名称不能为空');
                }
                $data['user_id'] = (int) $data['user_id'];
                $data['ratio'] = (int) ($data['ratio']*100);
                $data['shou'] = (int) ($data['shou']*100);
                $data['xu'] = (int) ($data['xu']*100);

                $data['business_id'] = (int) $business_id;
                $data['area_id'] = (int) $business['area_id'];
                $data['community_id'] = $community_id;
                if(!$data['business_id']){
                    $this->jinMsg('乡镇ID不能为空');
                }
                if (false !== Db::name('business_community')->update($data)){
                    $this->jinMsg('操作成功', url('business/community', array('area_id' => $this->area_id)));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('user', Db::name('users')->where(array('user_id'=>$detail['user_id']))->find());
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->jinMsg('请选择要编辑的商圈管理');
        }
    }





    public function community_delete($community_id=0,$business_id = 0){
        $business_id = (int)$business_id;
        $this->assign('business_id',$business_id);



        $community_id = (int)$community_id;
        $this->assign('community_id',$community_id);
        if($community_id){
            Db::name('business_community')->where(array('community_id'=>$community_id))->delete();
            $this->jinMsg('删除成功', url('business/community', array('business_id' => $business_id)));
        }else{
            $community_ids = input('business_id/a', false);
            if(is_array($community_ids)){
                foreach($community_ids as $id){
                    Db::name('business_community')->where(array('community_id'=>$id))->delete();
                }
                $this->jinMsg('删除成功', url('business/community', array('business_id' => $business_id)));
            }
            $this->jinMsg('请选择要删除的管理');
        }
    }




    public function cate_province($business_id = 0,$community_id = 0){
        $business_id = (int)$business_id;
        $this->assign('business_id',$business_id);

        $community_id = (int)$community_id;
        $this->assign('community_id',$community_id);

        $community = Db::name('business_community')->where(array('community_id'=>$community_id))->find();
        $this->assign('community',$community);




        $business = Db::name('business')->where(array('business_id'=>$business_id))->find();
        $this->assign('business',$business);

        $area = Db::name('copy_area')->where(array('area_id'=>$business['area_id']))->find();
        $city = Db::name('copy_city')->where(array('city_id'=>$area['city_id']))->find();

        $this->assign('area',$area);
        $this->assign('city',$city);

        $this->assign('provinceList', $provinceList = Db::name('copy_province')->where(array('id'=>$city['ParentId']))->order(array('id'=>'asc'))->select());
        return $this->fetch();
    }



    public function cate_edit_province($id = 0,$business_id = 0,$community_id = 0){
        $id = (int)$id;
        $this->assign('id',$id);
        $copy_province = Db::name('copy_province')->find($id);
        $this->assign('copy_province',$copy_province);

        $community_id = (int)$community_id;
        $this->assign('community_id',$community_id);


        $community = Db::name('business_community')->where(array('community_id'=>$community_id))->find();
        $this->assign('community',$community);

        $business_id = (int)$business_id;
        $this->assign('business_id',$business_id);
        $business = Db::name('business')->where(array('business_id'=>$business_id))->find();
        $this->assign('business',$business);

        $provinceList = Db::name('copy_province')->order(array('id'=>'asc'))->select();
        foreach($provinceList as $key => $val){
            $business_cate_provinces = Db::name('business_cate_provinces')->where(array('community_id'=>$community_id,'star_province_id'=>$id,'end_province_id'=>$val['id']))->find();
            $provinceList[$key]['shou'] = round($business_cate_provinces['shou']/100,2);
            $provinceList[$key]['xu'] = round($business_cate_provinces['xu']/100,2);
            $provinceList[$key]['info'] = $business_cate_provinces['info'];
        }
        $this->assign('provinceList',$provinceList);
        return $this->fetch();
    }

    public function cate_update_province($id = 0,$business_id = 0,$community_id = 0){

        $deleteList = Db::name('business_cate_provinces')->where(array('community_id'=>$community_id,'star_province_id'=>$id))->order(array('id'=>'asc'))->select();
        $d=0;
        foreach($deleteList as $k => $v){
            $d++;
            Db::name('business_cate_provinces')->where(array('id'=>$v['id']))->delete();
        }
        $option = input('options/a');
        $options = array();
        foreach($option['shou'] as $key => $val){
            $options[] = array(
                'province_id' => $id,
                'star_province_id' => $id,
                'star_province_name' => $option['star_province_name'][$key],
                'end_province_id' => $option['end_province_id'][$key],
                'end_province_name' => $option['end_province_name'][$key],
                'shou' => $option['shou'][$key]*100,
                'xu' => $option['xu'][$key]*100,
                'info' => $option['info'][$key],
                'business_id' => $business_id,
                'community_id' => $community_id,
                'create_time'=>time()
            );
        }
        if(empty($options)){
            $this->jinMsg('没有设置有效');
        }
        $i=0;
        foreach($options as $k => $val){
            if($val['shou']){
                $i++;
                Db::name('business_cate_provinces')->insertGetId($val);
            }
        }
        $this->jinMsg('先删除【'.$d.'】条，再设置运费成功【'.$i.'】条', url('business/cate_edit_province',array('id'=>$id,'business_id'=>$business_id,'community_id'=>$community_id)));
    }




    //清空数据
    public function cate_update_delete($id = 0,$business_id = 0,$community_id = 0){
        $deleteList = Db::name('business_cate_provinces')->where(array('business_id'=>$business_id,'star_province_id'=>$id))->order(array('id'=>'asc'))->select();
        $d=0;
        foreach($deleteList as $k => $v){
            $d++;
            Db::name('business_cate_provinces')->where(array('id'=>$v['id']))->delete();
        }
        $this->jinMsg('删除【'.$d.'】条数据', url('business/cate_edit_province',array('id'=>$id,'business_id'=>$business_id,'community_id'=>$community_id)));
    }

	
	
	
   
}