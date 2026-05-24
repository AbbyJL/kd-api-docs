<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;

class City extends Base{
	
	public function _initialize(){
        parent::_initialize();
		$this->assign('getorderStatus', $getorderStatus = model('Setting')->getorderStatus());
		$this->assign('getdiffStatus', $getdiffStatus = model('Setting')->getdiffStatus());
        $this->assign('getorderRightsStatus', $getorderRightsStatus = model('Setting')->getorderRightsStatus());
		$this->assign('getCompanyApiTypes', $getCompanyApiTypes = model('Setting')->getCompanyApiTypes());
    }
   
    public function index(){
		$map = array();
        $keyword = input('keyword','', 'htmlspecialchars');
        if($keyword){
            $map['name|pinyin|areacode'] = array('LIKE', '%'.$keyword.'%');
        }    
        $this->assign('keyword',$keyword);
	    if($user_id = (int) input('user_id')){
            $map['user_id'] = $user_id;
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		
		if($is_open = input('is_open','', 'htmlspecialchars')){
            if($is_open != 999){
                $map['is_open'] = $is_open;
            }
            $this->assign('is_open', $is_open);
        }else{
            $this->assign('is_open', 999);
        }
		
		$getSearchCityId = $this->getSearchCityId($this->city_id);
		if($getSearchCityId){
			$map['city_id'] = $getSearchCityId;
			$this->assign('city_id',$getSearchCityId);
		}
		
        $count = Db::name('city')->where($map)->count(); 
        $Page = new \Page($count, 25); 
        $show = $Page->show(); 
        $list = Db::name('city')->where($map)->order(array('city_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$agent_ids = $user_ids = array();
		foreach($list as $k => $val){
			$val['code'] = Db::name('city_code')->where(array('city'=>array('LIKE','%'.$val['name'].'%')))->find();
			$val['user'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
			$user_ids[$val['user_id']] = $val['user_id'];
			$list[$k] = $val;
        }
		$this->assign('users', model('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show); 
        return $this->fetch();
    }
	
	//高德区域编码
	public function areacode(){
		$map = array();
        $keyword = input('keyword','', 'htmlspecialchars');
        if($keyword){
            $map['name'] = array('LIKE', '%'.$keyword.'%');
        }    
        $this->assign('keyword',$keyword);
        $count = Db::name('copy_city')->where($map)->count(); 
        $Page = new \Page($count,15); 
        $show = $Page->show(); 
        $list = Db::name('copy_city')->where($map)->order(array('city_id' =>'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
			$list[$k]['city'] = Db::name('city')->where(array('city_id'=>$val['city_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show); 
        return $this->fetch();
    }
	
	//百度区域编码
	public function citycode(){
		$map = array();
        $keyword = input('keyword','', 'htmlspecialchars');
        if($keyword){
            $map['city|citycode'] = array('LIKE','%'.$keyword.'%');
        }    
        $this->assign('keyword',$keyword);
        $count = Db::name('city_code')->where($map)->count(); 
        $Page = new \Page($count,15); 
        $show = $Page->show(); 
        $list = Db::name('city_code')->where($map)->order(array('id'=>'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show); 
        return $this->fetch();
    }

 	public function selectcitycode(){
		$map = array();
        $keyword = input('keyword','', 'htmlspecialchars');
        if($keyword){
            $map['city|citycode'] = array('LIKE','%'.$keyword.'%');
        }    
        $this->assign('keyword',$keyword);
        $count = Db::name('city_code')->where($map)->count(); 
        $Page = new \Page($count,15); 
        $show = $Page->show(); 
        $list = Db::name('city_code')->where($map)->order(array('id'=>'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show); 
        return $this->fetch(); 
        
    }
	

    public function create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false),array('name','name1','user_id','ratio','ratio_vip','areacode','CityCode','pinyin','photo','is_open','lng','lat','orderby','first_letter'));
			$data['name'] = htmlspecialchars($data['name']);
			if (empty($data['name'])){
				$this->jinMsg('城市名称不能为空');
			} 
			$data['user_id'] = (int) $data['user_id'];
			$data['ratio'] = (int) ($data['ratio']*100);
			$data['ratio_vip'] = (int) ($data['ratio_vip']*100);
			$data['areacode'] = $data['areacode'];
			$data['CityCode'] = $data['CityCode'];
			
			$data['pinyin'] = htmlspecialchars($data['pinyin']);
			if (empty($data['pinyin'])) {
				$this->jinMsg('城市拼音不能为空');
			}
			$data['photo'] = htmlspecialchars($data['photo']);
			$data['is_open'] = (int)($data['is_open']);
			$data['lng'] = htmlspecialchars($data['lng']);
			$data['lat'] = htmlspecialchars($data['lat']);
			$data['first_letter'] = htmlspecialchars($data['first_letter']);
			$data['orderby'] = (int)($data['orderby']);
			$data['create_time'] = time();
			$data['create_ip'] = request()->ip();
            if(Db::name('city')->insert($data)){
                $this->jinMsg('添加成功', url('city/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }


  


    public function edit($city_id = 0){
        if($city_id = (int) $city_id){
            if(!$detail = Db::name('city')->find($city_id)){
                $this->error('请选择要编辑的城市站点');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array(
					'name','name1','user_id','ratio','ratio_vip','areacode','CityCode','pinyin','photo','img','is_open','cate_name','open','shou','xu','lng','lat','orderby','first_letter',
					'tag','is_yuyue','ratio1','priceA_type','priceA_ratio','priceA_price','priceB_type','priceB_ratio','priceB_price','lanshou'
				));
				$data['name'] = htmlspecialchars($data['name']);
				if(empty($data['name'])){
					$this->jinMsg('城市名称不能为空');
				} 
				$data['user_id'] = (int) $data['user_id'];
				$data['ratio'] = (int) ($data['ratio']*100);
				$data['ratio_vip'] = (int) ($data['ratio_vip']*100);
				$data['shou'] = (int) ($data['shou']*100);
				$data['xu'] = (int) ($data['xu']*100);
				$data['areacode'] = $data['areacode'];
				$data['CityCode'] = $data['CityCode'];
				
				
				$data['pinyin'] = htmlspecialchars($data['pinyin']);
				if(empty($data['pinyin'])){
					$this->jinMsg('城市拼音不能为空');
				}
				$data['photo'] = htmlspecialchars($data['photo']);
				$data['is_open'] = (int)($data['is_open']);
				$data['lng'] = htmlspecialchars($data['lng']);
				$data['lat'] = htmlspecialchars($data['lat']);
				$data['first_letter'] = htmlspecialchars($data['first_letter']);
				$data['orderby'] = (int)($data['orderby']);
                $data['city_id'] = $city_id;
                if(false !== Db::name('city')->update($data)){
                    $this->jinMsg('操作成功', url('city/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
				$this->assign('user', Db::name('users')->where(array('user_id'=>$detail['user_id']))->find());
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的城市站点');
        }
    }
	
   public function is_open($city_id = 0) {
        if($city_id = (int) $city_id){
			if(model('City')->isOpen($city_id)){
				$this->jinMsg('审核成功', url('city/index'));
			}else{
				$this->jinMsg(model('City')->getError());
			}
        }else{
            $this->jinMsg('请选择你要审核的站点');
        }
    }


	
	//根据分类获取价格
	public function getcitydata($city_id){
        if(!$city_id = (int)$city_id){
			return json(array('code'=>'0','msg'=>'ID不存在'));
        }
        if(!$detail = Db::name('city')->find($city_id)){
			return json(array('code'=>'0','msg'=>'没找到城市'));
        }
		return json(array('code'=>'1','msg'=>'成功匹配城市','lng'=>$detail['lng'],'lat'=>$detail['lat'])); 
    }
	
	//城市删除功能
    public function delete($city_id = 0){
        if($city_id = (int) $city_id){
			
				//查找区域列表
				$areas = Db::name('area')->where(array('city_id'=>$city_id))->select();
				if(is_array($areas)){
					$k1 = 1;
					foreach($areas as $var){
						//查找商圈列表
						$businesss = Db::name('business')->where(array('area_id'=>$var['area_id']))->select();
						if(is_array($businesss)){
							$k2 = 1;
							foreach($businesss as $var2){
								$k2++;
								Db::name('business')->where(array('business_id'=>$var2['business_id']))->delete();//删除商圈
							}
						}
						$k1++;
						Db::name('area')->where(array('area_id'=>$var['area_id']))->delete();//删除区域
					}
				}
				
				if($k1){
					$msg .= '删除区域'.$k1.'个<br>';
				}
				if($k2){
					$msg .= '删除乡镇'.$k2.'个<br>';
				}
				if(Db::name('city')->where(array('city_id'=>$city_id))->delete()){
					model('City')->cleanCache();
           			$this->jinMsg($msg, url('city/index'));
				}else{
					$this->jinMsg('删除城市失败');	
				}
        }else{
            $this->jinMsg('请选择要删除的城市站点');
        }
    }


    //备用城市表中插入数据
	public function add($city_id = 0){
		if(!$city_id){
			$this->jinMsg('请选择你要添加的城市');
		}
		if(!$copy_city = Db::name('copy_city')->find($city_id)){
			$this->jinMsg('城市库没东西存在');
		}
		$res = Db::name('city')->where('name',$copy_city['name'])->find();
		if($res){
			$this->jinMsg('貌似您系统城市ID【'.$res['city_id'].'】已经添加了吧');
		}
		$res1 = Db::name('city')->where('areacode',$copy_city['city_id'])->find();
		if($res1){
			$this->jinMsg('系统城市ID【'.$res1['city_id'].'】跟城市库的ID有重复请检查');
		}
		
		
		$arr['city_id'] = $copy_city['city_id'];
		$arr['name'] = $copy_city['name'];
		$arr['areacode'] = $copy_city['city_id'];
		
		$code = Db::name('city_code')->where(array('city'=>array('LIKE','%'.$copy_city['name'].'%')))->find();//百度城市编码
		$arr['CityCode'] = $code['citycode'];
		
		$arr['pinyin'] = $copy_city['pinyin'];
		$arr['is_open'] = 1;
		$arr['lng'] = $copy_city['lng'];
		$arr['lat'] = $copy_city['lat'];
		$arr['first_letter'] = $copy_city['first_letter'];
		$arr['ShortName'] = $copy_city['ShortName'];
		$arr['LevelType'] = $copy_city['LevelType'];
		$arr['CityCode'] = $copy_city['CityCode'];
		$arr['ZipCode'] = $copy_city['ZipCode'];
		$arr['MergerName'] = $copy_city['MergerName'];
		$arr['ParentId'] = $copy_city['ParentId'];
		$arr['create_time'] = time();
		$arr['create_ip'] = request()->ip();
		//添加城市数据
		$city_ids = Db::name('city')->insertGetId($arr);
		
		//查找区域列表
		$copy_areas = Db::name('copy_area')->where(array('city_id'=>$city_id))->select();
		if(is_array($copy_areas)){
			$k1 = 1;
			foreach($copy_areas as $var){
			    //查找商圈列表
				$copy_businesss = Db::name('copy_business')->where(array('area_id'=>$var['area_id']))->select();
				if(is_array($copy_businesss)){
					$k2 = 1;
					foreach($copy_businesss as $var2){
						$k2++;
						$arr2['business_id'] = $var2['business_id'];
						$arr2['business_name'] = $var2['business_name'];
						$arr2['area_id'] = $var['area_id'];//这里应该是上一级商圈ID
						$arr2['areacode'] = $var2['business_id'];
						$arr2['lng'] = $var2['lng'];
						$arr2['lat'] = $var2['lat'];
						Db::name('business')->insert($arr2);//循环插入商圈
					}
				}
				$k1++;
				$arr1['area_id'] = $var['area_id'];
				$arr1['city_id'] = $copy_city['city_id'];//这里应该是上一级城市ID
				$arr1['area_name'] = $var['area_name'];
				$arr1['areacode'] = $var['area_id'];
				$arr1['areacode'] = $var['area_id'];
				$arr1['Name'] = $var['Name'];
				$arr1['LevelType'] = $var['LevelType'];
				$arr1['CityCode'] = $var['CityCode'];
				$arr1['ZipCode'] = $var['ZipCode'];
				$arr1['MergerName'] = $var['MergerName'];
				$arr1['lng'] = $var['lng'];
				$arr1['Lat'] = $var['Lat'];
				$arr1['pinyin'] = $var['pinyin'];
				Db::name('area')->insert($arr1);//循环插入区域数据
			}
		}
		
		if($city_ids){
			$msg .= '成功添加城市【'.$copy_city['name'].'】<br>';
		}
		if($k1){
			$msg .= '添加区域'.$k1.'个<br>';
		}
		if($k2){
			$msg .= '添加乡镇'.$k2.'个<br>';
		}
		
        if($city_ids){
			model('City')->cleanCache();//清理缓存
           	$this->jinMsg($msg, url('city/index'));
        }else{
            $this->jinMsg('添加失败');
        }
    }
	

	public function agent($city_id=0,$area_id=0,$type=2){
		$city_id = (int) $city_id;
		$this->assign('city_id',$city_id);
		
		$area_id = (int) $area_id;
		$this->assign('area_id',$area_id);
		
		$type = (int) $type;
		$this->assign('type',$type);
		
		
		$map = array('type'=>$type);
		if($type==2){
			$map['city_id'] = $city_id;
		}
		if($type==3){
			$map['area_id'] = $area_id;
		}
		
        $count = Db::name('city_agent')->where($map)->count(); 
        $Page = new \Page($count,15); 
        $show = $Page->show(); 
        $list = Db::name('city_agent')->where($map)->order(array('city_id' =>'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
			$list[$k]['city'] = Db::name('city')->where(array('city_id'=>$val['city_id']))->find();
			$list[$k]['area'] = Db::name('area')->where(array('area_id'=>$val['area_id']))->find();
			$list[$k]['user'] = Db::name('users')->where(array('user_id'=>$val['user_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show); 
        return $this->fetch();
    }
	
	
	public function agentEdit($id=0,$city_id=0,$area_id=0,$type=2){
		$id = (int) $id;
		$this->assign('id',$id);
		
		$city_id = (int) $city_id;
		$this->assign('city_id',$city_id);
		
		$area_id = (int) $area_id;
		$this->assign('area_id',$area_id);
		$type = (int) $type;
		$this->assign('type',$type);
		
        $detail = Db::name('city_agent')->where(array('id'=>$id))->find();
		
		
		if(request()->post()){
			$data = $this->checkFields(input('data/a', false),array('user_id','ratio','ratio_vip'));
			$data['user_id'] = (int) $data['user_id'];
			$data['ratio'] = (int) ($data['ratio']*100);
			$data['ratio_vip'] = (int) ($data['ratio_vip']*100);
			$data['type'] = (int) $type;
			$data['city_id'] = (int) $city_id;
			$data['area_id'] = (int) $area_id;
			if($id){
				$data['id'] = (int)$id;
				$r = Db::name('city_agent')->update($data);
			}else{
				$data['create_time'] = time();
				$r = Db::name('city_agent')->insert($data);
			}
			if($r){
				$this->jinMsg('操作成功', url('city/agent',array('city_id'=>$city_id,'area_id'=>$area_id,'type'=>$type)));
			}
			$this->jinMsg('操作失败');
		}else{
			$this->assign('detail', $detail);
			$this->assign('user', Db::name('users')->where(array('user_id'=>$detail['user_id']))->find());
			return $this->fetch();
		}
    }
	
	
	
	public function agentDelete($id=0,$city_id=0,$area_id=0,$type=2){
        if(is_numeric($id) && ($id = (int) $id)){
			Db::name('city_agent')->where(array('id'=>$id))->delete();
            $this->jinMsg('删除成功', url('city/agent',array('city_id'=>$city_id,'area_id'=>$area_id,'type'=>$type)));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach ($ids as $id) {
                    Db::name('city_agent')->where(array('id'=>$id))->delete();
                }
                $this->jinMsg('批量删除成功', url('city/agent',array('city_id'=>$city_id,'area_id'=>$area_id,'type'=>$type)));
            }
            $this->jinMsg('请选择要删除的区域管理');
        }
    }
	
	
	public function order(){
        $map = array('is_pei'=>2);
        $id = (int)input('id','', 'trim,htmlspecialchars');
        if($id){
            $map['id'] = $id;
            $this->assign('id', $id);
        }
        $deliveryId = input('deliveryId');
        if($deliveryId){
            $map['deliveryId'] = $deliveryId;
			$this->assign('deliveryId', $deliveryId);
        }		
        if($user_id = (int) input('user_id')){
            $map['user_id'] = $user_id;
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		$getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		$input = input('post.');
		$orderStatus= input('orderStatus');
		if($orderStatus != NUll && $orderStatus != 999){
			$map['orderStatus'] = $orderStatus;
		}
		if(isset($input['orderStatus']) || isset($input['orderStatus'])){
			$orderStatus = $input['orderStatus'];
		}else{
			$orderStatus = $orderStatus?$orderStatus:'999';
		}
		$this->assign('orderStatus',$orderStatus);
		if(isset($input['diffStatus']) || isset($input['diffStatus']) || input('diffStatus')){
            $diffStatus= (int) input('diffStatus');
            if($diffStatus != 999){
                $map['diffStatus'] = $diffStatus;
            }
            $this->assign('diffStatus', $diffStatus);
        }else{
            $this->assign('diffStatus', 999);
        }
		if(isset($input['orderRightsStatus']) || isset($input['orderRightsStatus'])){
            $orderRightsStatus= (int) input('orderRightsStatus');
            if($orderRightsStatus != 999){
                $map['orderRightsStatus'] = $orderRightsStatus;
            }
            $this->assign('orderRightsStatus', $orderRightsStatus);
        }else{
            $this->assign('orderRightsStatus', 999);
        }
        $count = Db::name('express_order')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('express_order')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k =>$v){
			$list[$k]['user'] = Db::name('users')->find($v['user_id']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		
		cookie('express_order_map',$map);
		
		
		if($this->is_tj==0){
			$this->getOrderStatus = model('Setting')->getorderStatus();
			$getOrderStatus = array();
			foreach($this->getOrderStatus as $k2 =>$v2){   
				$getOrderStatus[$k2]['id'] = $k2; 
				$getOrderStatus[$k2]['name'] = $v2; 
				$getOrderStatus[$k2]['count'] = (int)Db::name('express_order')->where(array('orderStatus'=>$k2,'closed'=>0))->count();
			}
			$this->assign('getOrderStatus',$getOrderStatus);
			$this->assign('count',$count);
			$this->assign('sumMoneyYuan',$sumMoneyYuan = (int)Db::name('express_order')->where($map)->sum('sumMoneyYuan'));
			$this->assign('sumMoneyYuan_old',$sumMoneyYuan_old = (int)Db::name('express_order')->where($map)->sum('sumMoneyYuan_old'));
			$this->assign('sumMoneyYuan_jia',$sumMoneyYuan_jia = (int)Db::name('express_order')->where($map)->sum('sumMoneyYuan_jia'));
			$this->assign('diffMoneyYuan',$diffMoneyYuan = (int)Db::name('express_order')->where($map)->sum('diffMoneyYuan'));
		}
        return $this->fetch('express/index');
    }
	
	
	public function delivery(){
		$map = array('closed'=>0);
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['name|mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if($user_id = (int) input('user_id')){
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        $count = Db::name('city_delivery')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('city_delivery')->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $v){
			$list[$k]['user'] = Db::name('users')->where(array('user_id'=>$v['user_id']))->find();
			$list[$k]['city'] = Db::name('city')->where(array('city_id'=>$v['city_id']))->find();
			$list[$k]['area'] = Db::name('area')->where(array('area_id'=>$v['area_id']))->find();
            $list[$k]['business'] = Db::name('business')->where(array('business_id'=>$v['business_id']))->find();
            $list[$k]['community'] = Db::name('business_community')->where(array('community_id'=>$v['community_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	
	public function delivery_edit($id = 0){
		$id = (int) $id;
		$detail = Db::name('city_delivery')->find($id);
		
		if(request()->post()){
			$data = $this->checkFields(input('data/a', false),array('user_id','city_id','area_id','business_id','community_id','photo','area','name','mobile','addr','money','status'));
			$data['user_id'] = (int) $data['user_id'];
			if(empty($data['user_id'])){
				$this->jinMsg('用户不能为空');
			}
			$data['name'] = htmlspecialchars($data['name']);
			if(empty($data['name'])){
				$this->jinMsg('收货人不能为空');
			}
			$data['mobile'] = htmlspecialchars($data['mobile']);
			if(empty($data['mobile'])){
				$this->jinMsg('手机号码不能为空');
			}
			if(!isMobile($data['mobile'])){
				$this->jinMsg('手机号码格式不正确');
			}
			$data['addr'] = htmlspecialchars($data['addr']);
			$data['city_id'] = (int) $data['city_id'];
			$data['area_id'] = (int) $data['area_id'];
            $data['business_id'] = (int) $data['business_id'];
            $data['community_id'] = (int) $data['community_id'];
			$data['money'] = (int) ($data['money']*100);
			$data['create_time'] = time();
			if($id){
				$data['id'] = $id;
				if(false !== Db::name('city_delivery')->update($data)){
					$this->jinMsg('编辑成功', url('city/delivery'));
				}
			}else{
				if(Db::name('city_delivery')->insert($data)){
					$this->jinMsg('添加成功', url('city/delivery'));
				}
			}
		}else{
			$this->assign('detail', $detail);
			$this->assign('cityList', $cityList = Db::name('city')->select());
			$this->assign('areaList', $areaList = Db::name('area')->where(array('city_id'=>$detail['city_id']))->select());
            $this->assign('businessList', $businessList = Db::name('business')->where(array('area_id'=>$detail['area_id']))->select());
            $this->assign('communityList', $communityList = Db::name('business_community')->where(array('business_id'=>$detail['businessid']))->select());
			$this->assign('user', model('Users')->where(array('user_id' => $detail['user_id']))->find());
		
			return $this->fetch();
		}
    }
	
	
	
	public function delivery_status($id = 0){
        if(is_numeric($id) && ($id = (int) $id)){
			Db::name('city_delivery')->update(array('id' => $id,'status' => 1));
            $this->jinMsg('审核成功', url('express/addrs'));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach($ids as $id){
                    Db::name('city_delivery')->update(array('id' => $id, 'status' => 1));
                }
                $this->jinMsg('审核成功', url('city/delivery'));
            }
            $this->jinMsg('请选择要审核的取件员');
        }
    }
	
	
	
	public function delivery_delete($id = 0){
        if(is_numeric($id) && ($id = (int) $id)){
			Db::name('city_delivery')->where(array('id'=>$id))->delete();
            $this->jinMsg('删除成功', url('city/delivery'));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach ($ids as $id){
                    Db::name('city_delivery')->where(array('id'=>$id))->delete();
                }
                $this->jinMsg('删除成功', url('city/delivery'));
            }
            $this->jinMsg('请选择要删除的取件员');
        }
    }
	
	
	public function deliveryOrder(){
        $map = array();
        $id = (int)input('id','', 'trim,htmlspecialchars');
        if($id){
            $map['id'] = $id;
            $this->assign('id', $id);
        }
        $delivery_id = input('delivery_id');
        if($delivery_id){
            $map['delivery_id'] = $delivery_id;
			$this->assign('delivery_id', $delivery_id);
        }	
		$order_id = input('order_id');
        if($order_id){
            $map['order_id'] = $order_id;
			$this->assign('order_id',$order_id);
        }
        if($user_id = (int) input('user_id')){
            $map['user_id'] = $user_id;
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		$getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
		$input = input('post.');
		$orderStatus= input('orderStatus');
		if($orderStatus != NUll && $orderStatus != 999){
			$map['orderStatus'] = $orderStatus;
		}
		if(isset($input['orderStatus']) || isset($input['orderStatus'])){
			$orderStatus = $input['orderStatus'];
		}else{
			$orderStatus = $orderStatus?$orderStatus:'999';
		}
		$this->assign('orderStatus',$orderStatus);
		
        $count = Db::name('city_delivery_order')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('city_delivery_order')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k =>$v){
			$list[$k]['user'] = Db::name('users')->where(array('user_id'=>$v['user_id']))->find();
			$list[$k]['order'] = Db::name('express_order')->where(array('id'=>$v['order_id']))->find();
			$list[$k]['delivery'] = Db::name('city_delivery')->where(array('id'=>$v['delivery_id']))->find();
			$list[$k]['city'] = Db::name('copy_city')->where(array('city_id'=>$v['city_id']))->find();
            $list[$k]['area'] = Db::name('copy_area')->where(array('area_id'=>$v['area_id']))->find();
            $list[$k]['business'] = Db::name('copy_business')->where(array('business_id'=>$v['business_id']))->find();
            $list[$k]['community'] = Db::name('business_community')->where(array('community_id'=>$v['community_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
	public function delivery_order_edit($id = 0){
		$id = (int) $id;
		$detail = Db::name('city_delivery_order')->where(array('id'=>$id))->find();
		$v = Db::name('express_order')->where(array('id'=>$detail['order_id']))->find();	
		
		
		if(request()->post()){
			$data = $this->checkFields(input('data/a', false),array('delivery_id','deliveryId','name','mobile','money','info','img','orderStatus'));
			$data['delivery_id'] = (int) $data['delivery_id'];
			if(empty($data['delivery_id'])){
				$this->jinMsg('delivery_id不能为空');
			}
			
			$d= Db::name('city_delivery')->where(array('id' =>$data['delivery_id']))->find();
			if(!$d){
				$this->jinMsg('业务员不存在');
			}
			$data['money'] = (int) ($data['money']*100);
			$data['deliveryId'] = htmlspecialchars($data['deliveryId']);
			if(empty($data['deliveryId'])){
				$this->jinMsg('单号不能为空');
			}
			
			if($data['orderStatus']==2){
				$data['delivery_time'] = time();
			}
			if($data['orderStatus']==3){
				$data['peisong_time'] = time();
			}
			if($data['orderStatus']==5){
				$this->jinMsg('当前订单状态【'.$data['orderStatus'].'】拒绝修改');
			}
			if($data['orderStatus']==8){
				if(!$v){
					$this->jinMsg('订单不存在');
				}
				if($v['orderStatus'] !=2 || $v['orderStatus'] !=3){
					$this->jinMsg('订单状态码【'.$v['orderStatus'].'】错误不能支持完成操作');
				}
				$data['end_time'] = time();
			}
			
			
			$r = Db::name('city_delivery_order')->where(array('id'=>$id))->update($data);
			if($r){
				
				
				if($data['orderStatus']==2){
					$eoData['deliveryId'] = $data['deliveryId'];
					$eoData['realOrderName'] = $d['name'] ? $d['name'] : $data['name'];
					$eoData['realOrderMobile'] = $d['mobile'] ? $d['mobile'] : $data['mobile'];
					$eoData['orderStatus'] = 2;
					$e = Db::name('express_order')->where(array('id'=>$detail['order_id']))->update($eoData);
				}
				if($data['orderStatus']==3){
					$eoData['orderStatus'] = 3;
					$e = Db::name('express_order')->where(array('id'=>$detail['order_id']))->update($eoData);
				}
				if($data['orderStatus']==5){
					
				}
				if($data['orderStatus']==8){
					$m = $d['money'];
					$i = '订单ID【'.$r['order_id'].'】配送员完成订单奖励';
					if($m> 0){
						$rest = model('Users')->addMoney($uid,$m,$i,7);
					}
					model('ExpressOrder')->completeProfit($v,$v['user_id'],'分销');//执行完成分销
					model('ExpressOrder')->orderAddIntegral($v,$v['user_id'],'给用户奖励积分');//赠送优惠券
					model('WeixinTmpl')->getWeixinTmplSend($v,$v['user_id'],$title = '签收成功通知');
					$eoData['orderStatus'] = 8;
					$e = Db::name('express_order')->where(array('id'=>$detail['order_id']))->update($eoData);
				}
				if($e){
					$this->jinMsg('编辑成功', url('city/deliveryOrder'));
				}else{
					$this->jinMsg('编辑失败');
				}
			}
			$this->jinMsg('操作失败');
		}else{
			$this->assign('detail', $detail);
			$this->assign('delivery', $delivery = Db::name('city_delivery')->where(array('id' =>$detail['delivery_id']))->find());
			return $this->fetch();
		}
    }
	
	 public function select($id=0,$city_id=0){
		 $id = (int) $id;
		 $city_id = (int) $city_id;
		 
		 $map = array('status' =>1);
		 if($city_id){
			 $map['city_id'] = $city_id;
		 }
         if($keyword = input('keyword','', 'htmlspecialchars')){
            $map['user_id|name|mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
         }
         $count = Db::name('city_delivery')->where($map)->count();
         $Page = new \Page($count,5);
         $pager = $Page->show();
         $list = Db::name('city_delivery')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
         $this->assign('list', $list);
         $this->assign('page', $pager);
		 $this->assign('id', $id);
		 $this->assign('city_id', $city_id);
         return $this->fetch();
    }
	
	
	
	
	
}
