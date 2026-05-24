<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;

class Area extends Base{
   
	public function _initialize(){
        parent::_initialize();
        $this->assign('citys', model('City')->fetchAll());
    }
	
	
    public function index(){
		$map = array();
        $keyword = input('keyword','', 'htmlspecialchars');
        if($keyword){
            $map['area_name|areacode'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);
		
        $getSearchCityId = $this->getSearchCityId($this->city_id);
		if($getSearchCityId){
			$map['city_id'] = $getSearchCityId;
			$this->assign('city_id',$getSearchCityId);
		}
		
		if($user_id = (int) input('user_id')){
            $map['user_id'] = $user_id;
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        $count = Db::name('area')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('area')->where($map)->order(array('area_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
		foreach ($list as $k => $val){
			$val['city'] = Db::name('city')->find($val['city_id']);
			$user_ids[$val['user_id']] = $val['user_id'];
			$val['user'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
			$list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('users', model('Users')->itemsByIds($user_ids));
        return $this->fetch();
    }
	
	
	
    public function create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false),array('area_name','area_name1', 'city_id','ratio','ratio_vip','areacode','user_id', 'orderby'));
			$data['area_name'] = htmlspecialchars($data['area_name']);
			if(empty($data['area_name'])) {
				$this->jinMsg('区域名称不能为空');
			}
			$data['areacode'] = (int) $data['areacode'];
			$data['orderby'] = (int) $data['orderby'];
			$data['city_id'] = (int) $data['city_id'];
			$data['ratio'] = (int) ($data['ratio']*100);
			$data['ratio_vip'] = (int) ($data['ratio_vip']*100);
            if(Db::name('area')->insert($data)){
                $this->jinMsg('添加成功', url('area/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            echo $this->fetch();
        }
    }
	

	
    public function edit($area_id = 0){
        if($area_id = (int) $area_id){
            if(!($detail = Db::name('area')->find($area_id))){
                $this->error('请选择要编辑的区域管理');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array(
				'area_name', 'area_name1','city_id','areacode','user_id', 'ratio','ratio_vip','orderby',
				'open','cate_name','tag','img','shou','xu','ratio1','priceA_type','priceA_ratio','priceA_price','priceB_type','priceB_ratio','priceB_price','is_yuyue','lanshou'
				));
				$data['area_name'] = htmlspecialchars($data['area_name']);
				if(empty($data['area_name'])){
					$this->jinMsg('区域名称不能为空');
				}
				$data['areacode'] = (int) $data['areacode'];
				$data['city_id'] = (int) $data['city_id'];
				$data['user_id'] = (int) $data['user_id'];
				$data['ratio'] = (int) ($data['ratio']*100);
				$data['ratio_vip'] = (int) ($data['ratio_vip']*100);
				$data['shou'] = (int) ($data['shou']*100);
				$data['xu'] = (int) ($data['xu']*100);
				
				
				$data['orderby'] = (int) $data['orderby'];
                $data['area_id'] = $area_id;
                if(false !== Db::name('area')->update($data)){
                    $this->jinMsg('操作成功', url('area/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
				$this->assign('user', model('Users')->where(array('user_id'=>$detail['user_id']))->find());
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的区域管理');
        }
    }
	

    public function delete($area_id = 0){
        if(is_numeric($area_id) && ($area_id = (int) $area_id)){
			$count =  Db::name('business')->where(array('area_id'=>$area_id))->count;
			if($count > 0){
				$this->jinMsg('该区域下面还有商圈，请先删除对应的商圈');
			}
            Db::name('area')->where(array('area_id'=>$area_id))->delete();
            $this->jinMsg('删除成功', url('area/index'));
        }else{
            $area_id = input('area_id/a', false);
            if(is_array($area_id)){
                foreach ($area_id as $id) {
					$count = Db::name('business')->where(array('area_id'=>$id))->count;
					if($count > 0){
						$this->jinMsg('该区域下面还有商圈，请先删除对应的商圈');
					}
                    Db::name('area')->where(array('area_id'=>$id))->delete($id);
                }
                $this->jinMsg('批量删除成功', url('area/index'));
            }
            $this->jinMsg('请选择要删除的区域管理');
        }
    }
	
	
	public function platformpsrange($area_id = 0){
		
		$arealnglatstring = '';
		
		
		$detail = Db::name('area')->where(array('area_id'=>$area_id))->find();
		$this->assign('detail',$detail);
		$this->assign('city_id',$city_id = $detail['city_id']);
		$this->assign('area_id',$area_id = $detail['area_id']);
		
	
		$platpssetinfo = Db::name('ele_platpsset')->where(array('area_id'=>$area_id))->find();
		
		
		if(!empty($platpssetinfo)){
			$arealnglatstring =$platpssetinfo['waimai_psrange'];
		}
		$temparealnglatarr = array();
		if(!empty($arealnglatstring)){
			$arealnglatarr = explode('#',$arealnglatstring);
			$temparealnglatarr = $arealnglatarr;
		}
		$this->assign('arealnglatarr',$arealnglatarr);
 	 	return $this->fetch();
	}
	
	
	
	
    public function sublnglatpsrange(){
		
		 $area_id = input('area_id','','trim,htmlspecialchars');
		 $detail = Db::name('area')->where(array('area_id'=>$area_id))->find();
		 $city_id = $detail['city_id'];
		 
		 $savearray['waimai_psrange'] = input('waimai_psrange');
		
		
			
	 	 $platpssetinfo = Db::name('ele_platpsset')->where('area_id',$area_id)->find();
		
		
		 if($savearray['waimai_psrange']){
			if(!empty($platpssetinfo)){
				 Db::name('ele_platpsset')->where(array('area_id'=>$area_id))->update(array('waimai_psrange'=>$savearray['waimai_psrange']));
			 }else{
				 $savearray['cityid'] = $cityid;
				 Db::name('ele_platpsset')->insert(array('area_id'=>$area_id,'waimai_psrange'=>$savearray['waimai_psrange']));
			 }
		 }
		 
		 return json(array('error'=>0,'msg' =>'保存成功', 'url'=>url('area/platformpsrange',array('area_id'=>$area_id))));
 	}
	
	
	
	public function map($area_id = 0){
		
		$detail = Db::name('area')->find($area_id);
			
        $positionList = Db::name('area_map')->where(array('area_id'=>$area_id))->select();
        for ($i = 0; $i < count($positionList); $i++){
            $positionList[$i]['position']    = unserialize($positionList[$i]['position']);
            $positionList[$i]['create_time'] = date('Y-m-d H:i:s', $positionList[$i]['create_time']);
        }
        $this->assign('positionList', $positionList);
		$this->assign('detail',$detail);
        return $this->fetch();
    }


    //添加区域
    public function pushPosition (){
		
		
        if(!request()->post()){
            $result = [
                'status'  => false,
                'message' => '非法操作'
            ];
        }else{
            $data['position']    = $this->toFloat($_POST['position'], 6);
            $data['name']  = input('name');
            $data['create_time'] = time();
			$data['area_id'] = $_POST['area_id'];
			//p($data);die;

            if($data && is_array($data)){
                $res = Db::name('area_map')->insert($data);
                if($res){
                    $result = [
                        'status'  => true,
                        'data'    => $_POST['position'],
                        'message' => '添加成功'
                    ];
                }else{
                    $result = [
                        'status'  => false,
                        'message' => '添加失败'
                    ];
                }
            }else{
                $result = [
                    'status'  => false,
                    'message' => '数据不合理'
                ];
            }
        }
        echo json_encode($result, true);
    }


    //过滤坐标值为6位小数,序列化坐标数组
    private function toFloat ($data, $num){
        $data = json_decode($data, true);
        if($data && is_array($data)){
            foreach($data as $k => $v){
                (array)$v;
                $v['lng'] = sprintf("%." . $num . "f", $v['lng']);
                $v['lat'] = sprintf("%." . $num . "f", $v['lat']);
            }
        }
        return serialize($data);
    }
	

    //获取区域列表
    public function poList(){
        $positionList = Db::name('area_map')->field('id,position,color')->select();
        for($i = 0; $i < count($positionList); $i++){
            $positionList[$i]['position'] = unserialize($positionList[$i]['position']);
        }
        echo json_encode($positionList, true);
    }

    //删除区域
    public function del($id){
        Db::name('area_map')->where(array('id' => $id))->delete();
        $$this->jinMsg('删除成功', url('map/index'));
    }

    //更新区域颜色
    public function upColor(){
        if(!request()->post()){
            $result = [
                'status'  => false,
                'message' => '非法操作'
            ];
        }else{
            $color = $_POST['color'];
            $id    = $_POST['id'];
			$area_id    = $_POST['area_id'];
			//p($_POST);die;

            if($color && $id){
                $res = Db::name('area_map')->where(array('id' => $id))->update(array('color'=>$color,'area_id'=>$area_id));
                if($res) {
                    $result = [
                        'status'  => true,
                        'message' => '更新成功'
                    ];
                }else{
                    $result = [
                        'status'  => false,
                        'message' => '数据更新失败'
                    ];
                }
            }else{
                $result = [
                    'status'  => false,
                    'message' => '参数不正确'
                ];
            }
        }
        echo json_encode($result, true);
    }


    public function inArea ($lng, $lat){
        (int)$int = model('AreaMap')->checkPoint($lng, $lat);
        if($int >= 0) {
            return $int;
        }else{
            return false;
        }
    }
	
	
	
}