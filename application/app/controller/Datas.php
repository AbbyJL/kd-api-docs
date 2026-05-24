<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

class Datas extends Base{


    public function cityareas(){
        $data = array();
        $data['city']  = Db::name('city')->select();
        $data['area']  = Db::name('area')->select();
        echo json_encode($data);
        die;
    }
	
	 
	public function cab_app2() { 
		echo '你妈P2';
        $data = array();
        $data['city']       = D('City')->fetchAll();
        $data['area']       = D('Area')->fetchAll();
        $data['business']   = D('Business')->fetchAll();
        echo $data;
    }

	
	public function tuancata2(){
        echo '你妈P';
    }
	
	
	public  function cityarea(){
        $data = array();
        $data['city']  = Db::name('city')->where(array('is_open'=>'1'))->select();
        $data['area']  = Db::name('area')->select();
        header("Content-Type:application/javascript");
        echo  'var  cityareas = '.  json_encode($data);die;
    }
    
    public function cab() { 
        $name = htmlspecialchars($_GET['name']);
        $data = array();
        $data['city']  = Db::name('city')->select();
        $data['area']   = Db::name('area')->select();
        $data['business']  = Db::name('business')->select();
        header("Content-Type:application/javascript");
        echo  'var '.$name.'='.  json_encode($data).';';
        die;
    }


    //lyer获取城市联动
	public function getCity(){ 
		$id = input('id','','htmlspecialchars');
		
		if(!$id){
			$data = Db::name('city')->where(array('is_open'=>'1'))->select();
			foreach($data as $k => $val){
				$data[$k]['id'] = $val['city_id'];
			}
		}else{
			$data = Db::name('area')->where(array('city_id'=>$id))->select();
			foreach($data as $k => $val){
				$data[$k]['id'] = $val['area_id'];
				$data[$k]['name'] = $val['area_name'];
			}
		}
        echo json_encode($data);
        die;
    }
	
	
	//三级联动
	public function onecity() { 
        $name = input('name','','htmlspecialchars');
        $data = array();
        $data['city'] = Db::name('city')->where(array('is_open'=>'1'))->select();
        header("Content-Type:application/javascript");
        echo  'var '.$name.'='.  json_encode($data).';';
        die;
    }
	
	public function twoarea(){ 
        $cid =  input('cid');
        $data = array();
		$data  = Db::name('area')->where(array('city_id'=>$cid))->select();
        echo json_encode($data);
        die;
    }

   public function tbusiness(){ 
   		//商圈
        $bid =  input('bid');
        $data = array();
		$data  = Db::name('business')->where(array('area_id'=>$bid))->select();
        echo json_encode($data);
        die;
    }
	
	//分类筛选
	public function childareas($city_id=0){
        $datas = Db::name('copy_area')->where('city_id',$city_id)->column('area_id,area_name,orderby'); //获取居住地区域数据
        $str = '';
        foreach($datas as $var){
            $str.='<option value="'.$var['area_id'].'">'.$var['area_name'].'</option>'."\n\r";      
        }
        echo $str;die;
    }

    public function city() {
        $upid = input('upid');
        $callback = input('callback');
        $outArr = array();
        $cityList = Db::name('paddlist')->where(array('upid' =>$upid))->select();
        if (is_array($cityList) && !empty($cityList)) {
            foreach ($cityList as $key => $value) {
                $outArr[$key]['id'] = $value['id'];
                $outArr[$key]['name'] = $value['name'];
            }
        }
        $outStr = '';
        $outStr = json_encode($outArr);
        if ($callback) {
            $outStr = $callback . "(" . $outStr . ")";
        }
        echo $outStr;
        die();
    }

    public function city2() {
        $upid = input('upid');
        $type = input('type');
        $callback = input('callback');
        $outArr = array();

        if($type=='province'){
            $cityList = Db::name('copy_city')->where(array('ParentId' =>$upid))->select();
            if (is_array($cityList) && !empty($cityList)) {
                foreach ($cityList as $key => $value) {
                    $outArr[$key]['id'] = $value['city_id'];
                    $outArr[$key]['name'] = $value['name'];
                }
            }

        }
        if($type=='city'){
            $cityList = Db::name('copy_area')->where(array('city_id' =>$upid))->select();
            if (is_array($cityList) && !empty($cityList)) {
                foreach ($cityList as $key => $value) {
                    $outArr[$key]['id'] = $value['area_id'];
                    $outArr[$key]['name'] = $value['area_name'];
                }
            }

        }
        $outStr = '';
        $outStr = json_encode($outArr);
        if ($callback) {
            $outStr = $callback . "(" . $outStr . ")";
        }
        echo $outStr;
        die();
    }
	
	
	public function datacate($parent_id = 0){
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                    }
                }
            }
        }
        echo $str;
        die;
    }

	//获取商家分类
	public function shopcate($parent_id = 0){
        $datas = model('ShopCate')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                    }
                }
            }
        }
        echo $str;
        die;
    }
    
}