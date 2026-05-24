<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;
use app\common\model\Setting;

class Express extends Base{
	
	 public function _initialize(){
        parent::_initialize();
		$this->assign('getorderStatus', $getorderStatus = model('Setting')->getorderStatus());
		$this->assign('getdiffStatus', $getdiffStatus = model('Setting')->getdiffStatus());
        $this->assign('getorderRightsStatus', $getorderRightsStatus = model('Setting')->getorderRightsStatus());
		$this->assign('getCompanyApiTypes', $getCompanyApiTypes = model('Setting')->getCompanyApiTypes());
    }



    public function photos(){
        $map = array();
        if($order_id = (int) input('order_id')){
            $this->assign('order_id', $order_id);
            $map['order_id'] = $order_id;
        }
        if($id = (int) input('id')){
            $this->assign('id', $id);
            $map['id'] = $id;
        }
        $count = Db::name('express_order_photos')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('express_order_photos')->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
    public function photos_delete($id = 0){
        if(is_numeric($id) && ($id = (int) $id)){
            $photos = Db::name('express_order_photos')->where(array('id'=>$id))->find();
            $array = explode($this->_CONFIG['site']['host'], $photos['photo']);
            $file = ROOT_PATH .$array[1];
            if (file_exists($file)) {
                unlink($file);
                Db::name('express_order_photos')->where(array('id'=>$id))->delete();
                $this->jinMsg('删除成功', url('express/photos'));
            } else {
                $this->jinMsg('删除失败');
            }
        }else{
            $i=0;
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach ($ids as $id){
                    $photos = Db::name('express_order_photos')->where(array('id'=>$id))->find();
                    $array = explode($this->_CONFIG['site']['host'], $photos['photo']);
                    $file = ROOT_PATH .$array[1];
                    if (file_exists($file)) {
                        $i++;
                        Db::name('express_order_photos')->where(array('id'=>$id))->delete();
                    }
                }
                $this->jinMsg('删除成功【'.$i.'】', url('express/photos'));
            }
            $this->jinMsg('请选择要删除的取件员');
        }
    }



    public function addrs(){
        $map = array('closed'=>0,'cate'=>4);
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['name|mobile|addr'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if($user_id = (int) input('user_id')){
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if($cate = (int) input('cate')){
            $this->assign('cate', $cate);
            $map['cate'] = $cate;
        }
        $count = Db::name('user_addr')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('user_addr')->where($map)->order(array('addr_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
            $list[$k]['user'] = Db::name('users')->find($val['user_id']);
            $list[$k]['community'] = Db::name('business_community')->where(array('community_id'=>$val['community_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }

    public function addrs_edit($addr_id = 0){
        $addr_id = (int) $addr_id;
        $detail = Db::name('user_addr')->find($addr_id);

        if(request()->post()){
            $data = $this->checkFields(input('data/a', false),array('user_id','province','city','area','name','mobile','address'));
            $data['user_id'] = (int) $data['user_id'];
            if(empty($data['user_id'])){
                $this->jinMsg('用户不能为空');
            }
            $data['name'] = htmlspecialchars($data['name']);
            if(empty($data['name'])){
                $this->jinMsg('收货人不能为空');
            }
            $data['linkMan'] = $data['name'];
            $data['mobile'] = htmlspecialchars($data['mobile']);
            if(empty($data['mobile'])){
                $this->jinMsg('手机号码不能为空');
            }
            if(!isMobile($data['mobile'])){
                $this->jinMsg('手机号码格式不正确');
            }
            $data['phone'] = $data['mobile'];
            $data['address'] = htmlspecialchars($data['address']);
            if(empty($data['address'])){
                $this->jinMsg('具体地址不能为空');
            }
            $data['province'] = (int) $data['province'];
            if(empty($data['province'])){
                $this->jinMsg('省份地址不能为空');
            }
            $data['city'] = (int) $data['city'];
            if(empty($data['city'])) {
                $this->jinMsg('城市地址不能为空');
            }
            $data['area'] = (int) $data['area'];
            if(empty($data['area'])) {
                $this->jinMsg('区县地址不能为空');
            }

            $provinfo = Db::name('copy_province')->where(array('id' => $data['province']))->find();
            $cityinfo = Db::name('copy_city')->where(array('city_id' => $data['city']))->find();
            $areainfo = Db::name('copy_area')->where(array('area_id' => $data['area']))->find();


            $data['province'] = $provinfo['name'];
            $data['city'] = $cityinfo['name'];
            $data['area'] = $areainfo['area_name'];
            $data['province_id'] = $provinfo['id'];
            $data['city_id'] = $cityinfo['city_id'];
            $data['area_id'] = $areainfo['area_id'];


            $data['type'] = 1;
            $data['cate'] = 4;
            $data['createTime'] = time();
            if($addr_id){
                $data['addr_id'] = $addr_id;
                if(false !== Db::name('user_addr')->update($data)){
                    $this->jinMsg('编辑成功', url('express/addrs'));
                }
            }else{
                if(Db::name('user_addr')->insert($data)){
                    $this->jinMsg('添加成功', url('express/addrs'));
                }
            }
        }else{
            $this->assign('detail', $detail);
            $this->assign('provinceList', $provinceList = Db::name('copy_province')->limit(0,36)->select());
            $this->assign('cityList', $cityList = Db::name('copy_city')->where(array('ParentId' => $detail['province_id']))->select());
            $this->assign('areaList', $areaList = Db::name('copy_area')->where(array('city_id' => $detail['city_id']))->select());
            $this->assign('user', model('Users')->where(array('user_id' => $detail['user_id']))->find());

            return $this->fetch();
        }
    }

    public function addrs_default($addr_id = 0){
        if(is_numeric($addr_id) && ($addr_id = (int) $addr_id)){
            Db::name('user_addr')->update(array('addr_id' => $addr_id,'is_default' => 1));
            $this->jinMsg('审核成功', url('express/addrs'));
        }else{
            $addr_id = input('addr_id/a', false);
            if(is_array($addr_id)){
                foreach($addr_id as $id){
                    Db::name('user_addr')->update(array('addr_id' => $id, 'is_default' => 1));
                }
                $this->jinMsg('审核成功', url('express/addrs'));
            }
            $this->jinMsg('请选择要审核的取件员');
        }
    }



    public function addrs_delete($addr_id = 0){
        if(is_numeric($addr_id) && ($addr_id = (int) $addr_id)){
            Db::name('user_addr')->where(array('addr_id'=>$addr_id))->delete();
            $this->jinMsg('删除成功', url('express/addrs'));
        }else{
            $addr_id = input('addr_id/a', false);
            if(is_array($addr_id)){
                foreach ($addr_id as $id){
                    Db::name('user_addr')->where(array('addr_id'=>$addr_id))->delete();
                }
                $this->jinMsg('删除成功', url('express/addrs'));
            }
            $this->jinMsg('请选择要删除的取件员');
        }
    }
	
	
    public function addr(){
		$map = array('closed'=>0);
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['name|mobile|addr'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if($user_id = (int) input('user_id')){
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        $count = Db::name('user_addr')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('user_addr')->where($map)->order(array('addr_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['user'] = Db::name('users')->find($val['user_id']);
            $list[$k]['community'] = Db::name('business_community')->where(array('community_id'=>$val['community_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }

  /**
	 * 订单列表「推送」：处理该订单下未消费的 express_order_push（含众发 type=8）
	 */
	protected function syncOrderPushByOrderId($orderId){
		$orderId = (int)$orderId;
		$this->adminPushLog('syncOrderPushByOrderId order_id='.$orderId);
		if(!$orderId){
			$this->jinMsg('订单ID不存在');
		}
		$order = Db::name('express_order')->where(array('id'=>$orderId))->find();
		if(!$order){
			$this->jinMsg('订单不存在');
		}
		$keys = array();
		if(!empty($order['deliveryId'])){
			$keys[] = $order['deliveryId'];
		}
		if(!empty($order['expressId'])){
			$keys[] = $order['expressId'];
		}
		if(!empty($order['expressNo'])){
			$keys[] = $order['expressNo'];
		}
		$keys[] = (string)$orderId;
		$keys = array_values(array_unique(array_filter($keys)));
		$list = Db::name('express_order_push')
			->where(array('status'=>1))
			->where('deliveryId|orderNo', 'in', $keys)
			->order('id asc')
			->limit(50)
			->select();
		if(empty($list)){
			$tip = ((int)$order['type'] === 15) ? '；众发订单请确认已配置回调 push8 且众发已 POST 到服务器' : '';
			$this->adminPushLog('sync 无待处理记录 keys='.json_encode($keys, JSON_UNESCAPED_UNICODE).' order_type='.$order['type']);
			$this->jinMsg('暂无待处理推送记录'.$tip, url('express/index'));
		}
		$done = 0;
		foreach($list as $push){
			$this->adminPushLog('sync 处理 push_id='.$push['id'].' deliveryId='.$push['deliveryId'].' orderNo='.$push['orderNo'].' type='.$push['type']);
			action('app/api/handlePushOrder', array('id'=>$push['id'], 'user_id'=>0, 'bug'=>0));
			$done++;
		}
		$this->adminPushLog('sync 完成 done='.$done);
		$this->jinMsg('已处理推送【'.$done.'】条', url('express/index'));
	}
	
    public function addr_delete($addr_id = 0){
        if (is_numeric($addr_id) && ($addr_id = (int) $addr_id)){
			Db::name('user_addr')->update(array('addr_id' => $addr_id,'closed' => 1));
            $this->jinMsg('删除成功！', url('express/addr'));
        }else{
            $addr_id = input('addr_id/a', false);
            if(is_array($addr_id)){
                foreach ($addr_id as $id){
                    Db::name('user_addr')->update(array('addr_id' => $id, 'closed' => 1));
                }
                $this->jinMsg('删除成功', url('express/addr'));
            }
            $this->jinMsg('请选择要删除的收货地址');
        }
    }
	
	
protected function adminPushLog($message = ''){
		$line = '['.date('Y-m-d H:i:s').'][admin.express.push] '.$message."\n";
		@file_put_contents('/tmp/zf_debug.log', $line, FILE_APPEND);
		if(defined('RUNTIME_PATH')){
			$logDir = RUNTIME_PATH.'log'.DS;
			if(!is_dir($logDir)){
				@mkdir($logDir, 0755, true);
			}
			@file_put_contents($logDir.'zf_debug.log', $line, FILE_APPEND);
		}
	}

	public function push(){
		$reqUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$allParam = request()->param();
		$this->adminPushLog(
			'进入 push URI='.$reqUri
			.' param='.json_encode($allParam, JSON_UNESCAPED_UNICODE)
			.' sync='.(int)input('sync')
			.' order_id='.(int)input('order_id')
			.' deliveryId='.input('deliveryId','', 'trim,htmlspecialchars')
		);
				$this->adminPushLog('是否走 sync 分支 order_id='.$orderId);
		if(($orderId = (int)input('order_id'))){
			$this->adminPushLog('走 sync 分支 order_id='.$orderId);
			return $this->syncOrderPushByOrderId($orderId);
		}
		$map = array();
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['deliveryId|orderNo'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		if($deliveryId = input('deliveryId','', 'trim,htmlspecialchars')){
            $map['deliveryId'] = $deliveryId;
            $this->assign('deliveryId', $deliveryId);
        }
        $getSearchDate = $this->getSearchDate();//时间搜索
		if(is_array($getSearchDate)){
			$map['create_time'] = $getSearchDate;
		}
        if($status= (int) input('status')){
            if($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
        }
		if($pushType= (int) input('pushType')){
            if($pushType != 999) {
                $map['pushType'] = $pushType;
            }
            $this->assign('pushType', $pushType);
        }else{
            $this->assign('pushType',999);
        }
		if($type= (int) input('type')){
            if($type != 999) {
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        }else{
            $this->assign('type',999);
        }
        $count = Db::name('express_order_push')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('express_order_push')->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['order'] = Db::name('express_order')->where(array('deliveryId'=>$val['deliveryId']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('count',$count);
        return $this->fetch();
    }

  
  
    public function push_delete($id = 0){
        if (is_numeric($id) && ($id = (int) $id)){
			Db::name('express_order_push')->where(array('id' => $id))->delete();
            $this->jinMsg('删除成功', url('express/push'));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach ($ids as $id){
                    Db::name('express_order_push')->where(array('id'=> $id))->delete();
                }
                $this->jinMsg('删除成功', url('express/push'));
            }
            $this->jinMsg('请选择要删除');
        }
    }
	
	
	public function push_detail($id = 0,$p = 0){
        $var = Db::name('express_order_push')->where(array('id' => $id))->find();
        $list = @json_decode($var['context'],true);
		$this->assign('var',$var);
   		$this->assign('list',$list);
        echo $this->fetch();
    }
	
	
	
	 public function dewu(){
		$map = array('closed'=>0);
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['name|mobile|addr'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if($user_id = (int) input('user_id')){
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        $count = Db::name('user_addr_dewu')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('user_addr_dewu')->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }

  
    public function dewu_delete($id = 0){
        if (is_numeric($id) && ($id = (int) $id)){
			Db::name('user_addr_dewu')->where(array('id' => $id))->delete();
            $this->jinMsg('删除成功', url('express/addr'));
        }else{
            $addr_id = input('addr_id/a', false);
            if(is_array($id)){
                foreach ($id as $id){
                    Db::name('user_addr_dewu')->where(array('id' => $id))->delete();
                }
                $this->jinMsg('删除成功', url('express/dewu'));
            }
            $this->jinMsg('请选择要删除的收货地址');
        }
    }
	
	public function article(){
		$map = array('closed' => 0);
        if($keyword = input('keyword','', 'trim,htmlspecialchars')){
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = Db::name('article')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('article')->where($map)->order(array('article_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }

	
    public function article_create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false),array('title','details','orderby'));
			$data['title'] = htmlspecialchars($data['title']);
			if (empty($data['title'])) {
				$this->jinMsg('标题不能为空');
			}
			$data['details'] = SecurityEditorHtml($data['details']);
			if (empty($data['details'])) {
				$this->jinMsg('详细内容不能为空');
			}
			$data['create_time'] = time();
			$data['orderby'] = (int) $data['orderby'];
			$data['audit'] = 1;
            if(Db::name('article')->insert($data)){
                $this->jinMsg('添加成功', url('express/article'));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }
	
  
	
    public function article_edit($article_id = 0){
        if($article_id = (int) $article_id){
            if(!($detail = Db::name('article')->find($article_id))){
                $this->error('请选择要编辑的文章');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array('title','details','orderby'));
				$data['title'] = htmlspecialchars($data['title']);
				if(empty($data['title'])){
					$this->jinMsg('标题不能为空');
				}
				$data['details'] = SecurityEditorHtml($data['details']);
				if(empty($data['details'])) {
					$this->jinMsg('详细内容不能为空');
				}
				$data['orderby'] = (int) $data['orderby'];
				$data['audit'] = 1;
                $data['article_id'] = $article_id;
                if (false !== Db::name('article')->update($data)) {
                    $this->jinMsg('操作成功', url('express/article'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        } else {
            $this->error('请选择要编辑的文章');
        }
    }
	
  

	
	
    public function article_delete($article_id = 0){
        if(is_numeric($article_id) && ($article_id = (int) $article_id)){
            Db::name('article')->update(array('article_id' => $article_id, 'closed' => 1));
            $this->jinMsg('删除成功', url('express/article'));
        }else{
            $article_id = input('article_id/a', false);
            if(is_array($article_id)){
                foreach ($article_id as $id){
                    Db::name('article')->update(array('article_id' => $id, 'closed' => 1));
                }
                $this->jinMsg('批量删除成功', url('express/article'));
            }
            $this->jinMsg('请选择要删除的文章');
        }
    }
	
	
	public function msg(){
		$map = array();
        $count = Db::name('express_msg')->where($map)->count();
        $Page = new \Page($count, 50);
        $show = $Page->show();
        $list = Db::name('express_msg')->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k =>$v){
			$list[$k]['img'] = explode(",",$v['images']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	public function msg_delete($id = 0){
        if(is_numeric($id) && ($id = (int) $id)){
            Db::name('express_msg')->where(array('id'=>$id))->delete();
            $this->jinMsg('删除成功', url('express/msg'));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach($ids as $id){
                    Db::name('express_msg')->where(array('id'=>$id))->delete();
                }
                $this->jinMsg('删除成功', url('express/msg'));
            }
            $this->jinMsg('请选择要删除的');
        }
    }

	public function transport(){
		$map = array();
        $count = Db::name('express_transport')->where($map)->count();
        $Page = new \Page($count, 50);
        $show = $Page->show();
        $list = Db::name('express_transport')->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k =>$v){
			$list[$k]['user'] = Db::name('users')->where(array('user_id'=>$v['user_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	public function transport_delete($id = 0){
        if(is_numeric($id) && ($id = (int) $id)){
            Db::name('express_transport')->where(array('id'=>$id))->delete();
            $this->jinMsg('删除成功', url('express/transport'));
        }else{
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach($ids as $id){
                    Db::name('express_transport')->where(array('id'=>$id))->delete();
                }
                $this->jinMsg('删除成功', url('express/transport'));
            }
            $this->jinMsg('请选择要删除的');
        }
    }



	
	public function cate(){
		$type = (int)input('type','1','');
        $map['type'] = $type;
		$this->assign('type',$type);
        $list = Db::name('express_cate')->where($map)->order("cate_id asc")->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	  public function expressCateRank($cate_id){
        $option = $_POST['options'];
		foreach($option['rank_id'] as $key => $val){
			$options[] = array(
				'rank_id' => $option['rank_id'][$key],
				'zhe' => $option['zhe'][$key],
			);
		}
		//先删除
		$delete = Db::name('express_cate_rank')->where(array('cate_id'=>$cate_id))->delete(); 
		foreach($options as $k => $val){
			if($val['rank_id']){
				$val['cate_id'] = $cate_id;
				$id = Db::name('express_cate_rank')->insert($val); 
			}
		}
		return true;
		
    }
	
    public function cate_create($parent_id = 0){
        $type = (int)input('type','','');
		$this->assign('type',$type);
        if(request()->post()){
			$data = $this->checkFields(input('data/a', false),array(
			'cate_name','type','is_jia','name','pinyin','pinyin2','photo','charging','tag','info','is_bao','baojia_rate','is_yuyue','volumetext',
			'ratio','priceA_type','priceA_ratio','priceA_price','priceB_type','priceB_ratio','priceB_price',
			'firstPrice','lanshou','firstPrice1','firstPrice2','addPrice','addPrice1','addPrice2','limitFirstPrice','limitAddPrice','tel','is_pei','area','orderby'));
			$data['cate_name'] = htmlspecialchars($data['cate_name']);
			if(empty($data['cate_name'])){
				$this->jinMsg('分类不能为空');
			}
			$data['is_pei'] = (int) $data['is_pei'];
			if($data['is_pei']==2 && !$data['area']){
				$this->jinMsg('请填写指定区域');
			}
			$data['orderby'] = (int) $data['orderby'];
			$data['firstPrice'] = (int) $data['firstPrice'];
            if($cate_id = Db::name('express_cate')->insertGetId($data)){
				$this->expressCateRank($cate_id); //更新等级折扣
                $this->jinMsg('添加成功', url('express/cate',array('type'=>$type)));
            }
            $this->jinMsg('操作失败');
        }else{
			
			$ranks = model('UserRank')->fetchAll();
			foreach($ranks as $k => $v){
				$ecr = Db::name('express_cate_rank')->where(array('cate_id'=>$cate_id,'rank_id'=>$v['rank_id']))->find();
				$ranks[$k]['zhe'] = $ecr['zhe'];
				$ranks[$k]['id'] = $ecr['id'];
			}
			$this->assign('ranks',$ranks);
			
            echo $this->fetch();
        }
    }
 	
	
    public function cate_edit($cate_id = 0){
        if($cate_id = (int) $cate_id) {
            if(!($detail = Db::name('express_cate')->find($cate_id))){
                $this->error('请选择要编辑的类型');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array(
				'cate_name','is_jia','type','name','pinyin','pinyin2','photo','charging','tag','info','is_bao','baojia_rate','is_yuyue','volumetext',
				'ratio','priceA_type','priceA_ratio','priceA_price','priceB_type','priceB_ratio','priceB_price',
				'firstPrice','lanshou','firstPrice1','firstPrice2','addPrice','addPrice1','addPrice2','limitFirstPrice','limitAddPrice','tel','is_pei','area','orderby'));
				$data['cate_name'] = htmlspecialchars($data['cate_name']);
				if(empty($data['cate_name'])){
					$this->jinMsg('分类不能为空');
				}
				$data['is_pei'] = (int) $data['is_pei'];
				if($data['is_pei']==2 && !$data['area']){
					$this->jinMsg('请填写指定区域');
				}
				$data['orderby'] = (int) $data['orderby'];
				$data['firstPrice'] = (int) $data['firstPrice'];
                $data['cate_id'] = $cate_id;
                if(false !== Db::name('express_cate')->update($data)){
					$this->expressCateRank($cate_id); //更新等级折扣
                    $this->jinMsg('操作成功', url('express/cate',array('type'=>$data['type'])));
                }
                $this->jinMsg('操作失败');
            }else{
				
				$ranks = model('UserRank')->fetchAll();
				foreach($ranks as $k => $v){
					$ecr = Db::name('express_cate_rank')->where(array('cate_id'=>$cate_id,'rank_id'=>$v['rank_id']))->find();
					$ranks[$k]['zhe'] = $ecr['zhe'];
					$ranks[$k]['id'] = $ecr['id'];
				}
				$this->assign('ranks',$ranks);
				
                $this->assign('detail', $detail);
                echo $this->fetch();
            }
        }else{
            $this->jinMsg('请选择要编辑的活动类型');
        }
    }
	

	
     public function cate_delete($cate_id = 0,$type = 0){
		$type = (int) $type;
        if(is_numeric($cate_id) && ($cate_id = (int) $cate_id)){
            Db::name('express_cate')->where(array('cate_id'=>$cate_id))->delete();
            $this->jinMsg('删除成功', url('express/cate',array('type'=>$type)));
        }else{
            $cate_id = input('cate_id/a', false);
            if(is_array($cate_id)){
                foreach($cate_id as $id){
                    Db::name('express_cate')->where(array('cate_id'=>$id))->delete();
                }
                $this->jinMsg('删除成功', url('express/cate',array('type'=>$type)));
            }
            $this->jinMsg('请选择要删除的类型');
        }
    }
	
	
    public function cate_update(){
		$type = (int)input('type','','');
		$this->assign('type',$type);
        $orderby = input('orderby/a', false);
        foreach($orderby as $key => $val){
            $data = array('cate_id' => (int) $key, 'orderby' => (int) $val);
            Db::name('express_cate')->update($data);
        }
        $this->jinMsg('更新成功', url('express/cate',array('type'=>$type)));
    }
	
	
   public function index(){
        $map = array();
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
       
	   
	   
	    $diffStatus= input('diffStatus');
		if($diffStatus != NUll && $diffStatus != 999){
			$map['diffStatus'] = $diffStatus;
		}
		if(isset($input['diffStatus']) || isset($input['diffStatus']) || input('diffStatus')){
            $diffStatus= (int) input('diffStatus');
            if($diffStatus != 999){
                $map['diffStatus'] = $diffStatus;
            }
            $this->assign('diffStatus', $diffStatus);
        }else{
            $this->assign('diffStatus', 999);
        }
		
		
		$orderRightsStatus= input('orderRightsStatus');
		if($orderRightsStatus != NUll && $orderRightsStatus != 999){
			$map['orderRightsStatus'] = $orderRightsStatus;
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
		$this->getOrderStatus = model('Setting')->getorderStatus();
		//统计数量
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
        return $this->fetch();
    }
	
	
	
     public function edit($id = 0){
        if($id = (int) $id){
            if(!($detail = Db::name('express_order')->find($id))){
                $this->error('请选择要编辑');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array(
					'kuaidi','user_id','deliveryId','orderStatus','orderStatusName','realOrderState','realOrderName','realOrderMobile','realOrderCode','diffMoneyYuan','diffStatus','wight','review_weight','remark','message',
					'sendName','sendMobile','senderProvince', 'senderCity','senderCounty','sendAddress',
					'receiveName','receiveMobile','receiveProvince','receiveCity','receiveCounty','receiveAddress','sumMoneyYuan','sumMoneyYuan_old','sumMoneyYuan_jia'
				));
				$data['id'] = $id;
				$data['sumMoneyYuan'] = $data['sumMoneyYuan']*100;
				$data['sumMoneyYuan_old'] = $data['sumMoneyYuan_old']*100;
				$data['sumMoneyYuan_jia'] = $data['sumMoneyYuan_jia']*100;
				$data['diffMoneyYuan'] = $data['diffMoneyYuan']*100;
                if(false !== Db::name('express_order')->update($data)) {
                    $this->jinMsg('操作成功', url('express/index'));
                }
                $this->jinMsg('操作失败');
            }else{
				$this->assign('var', $detail);
				$cates = Db::name('express_cate')->order(array('cate_id'=>'asc'))->limit(0,100)->select();
				$this->assign('cates',$cates);
				$this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑');
        }
    }
	
	//订单详情
	public function detail($id = 0){
        if($id = (int) $id){
            if(!($detail = Db::name('express_order')->find($id))){
                $this->error('请选择要编辑');
            }
            $this->assign('var', $detail);
			$this->assign('detail', $detail);

            $photos = Db::name('express_order_photo')->where(array('order_id'=>$id))->select();
            $this->assign('photos',$photos);

            $photos2 = Db::name('express_order_photos')->where(array('order_id'=>$id))->select();
            $this->assign('photos2',$photos2);


			$pressList =model('ExpressOrder')->logisticsInfo($detail,1,$mailNo='');
			$this->assign('pressList',$pressList);


			return $this->fetch();
           
        }else{
            $this->error('请选择要编辑的');
        }
    }
	
	
	
	
	
	
    public function export(){
        $getorderStatus = model('Setting')->getorderStatus();
		$getdiffStatus = model('Setting')->getdiffStatus();
        $getorderRightsStatus = model('Setting')->getorderRightsStatus();
		$getCompanyApiTypes = model('Setting')->getCompanyApiTypes();
		
        $orders = Db::name('express_order')->where(cookie('express_order_map'))->order(array('id'=>'desc'))->limit(0,3000)->select();
        $date = date("Y_m_d H:i:s", time());
        $filetitle = "订单列表";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array(
			'aa' => 'ID', 
			'bb' => '支付金额', 
			'cc' => '差价金额', 
			'dd' => '快递', 
			'ee' => '寄件地址', 
			'ff' => '收件地址', 
			'gg' => '订单状态', 
			'hh' => '退款状态', 
			'ii' => '重量', 
			'jj' => '用户ID', 
			'kk' => '订单号',
			'mm' => '用户手机',
			'll' => '平台价格',
			'nn' => '时间' 
		);
        foreach ($filter as $key => $title){
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach ($orders as $k => $v){
            $filter = array(
				'aa' => 'ID', 
				'bb' => '支付金额', 
				'cc' => '差价金额', 
				'dd' => '快递', 
				'ee' => '寄件地址', 
				'ff' => '收件地址', 
				'gg' => '订单状态', 
				'hh' => '退款状态', 
				'ii' => '重量', 
				'jj' => '用户ID', 
				'kk' => '订单号',
				'mm' => '用户手机',
				'll' => '平台价格',
				'nn' => '时间' 
			);
            $orders[$k]['aa'] = $v['id'];
            $orders[$k]['bb'] = round($v['sumMoneyYuan']/100,2);
            $orders[$k]['cc'] = round($v['diffMoneyYuan']/100,2);
            $orders[$k]['dd'] = $v['kuaidi'];
            $orders[$k]['ee'] = deleteHtml($v['sendAddress']);
            $orders[$k]['ff'] = deleteHtml($v['receiveAddress']);
            $orders[$k]['gg'] = $getorderStatus[$v['orderStatus']];
            $orders[$k]['hh'] = $getorderRightsStatus[$v['orderRightsStatus']];
            $orders[$k]['ii'] = $v['wight'];
            $orders[$k]['jj'] = $v['user_id'];
			$orders[$k]['kk'] = $v['deliveryId'];
			$orders[$k]['mm'] = Db::name('users')->where(array('user_id'=>$v['user_id']))->value('mobile');
			$orders[$k]['ll'] = round($v['TotalFee']/100,2);
            $orders[$k]['nn'] = date('Y-m-d H:i:s',$v['create_time']);
            foreach($filter as $key => $title){
                $html .= $orders[$k][$key] . "\t,";
            }
            $html .= "\n";
        }
        ob_end_clean();
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename={$fileName}.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $html;
        exit;
    }


    public function delete($id = 0){
        if(is_numeric($id) && ($id = (int) $id)){
            model("express_order")->startTrans();
            try{
                $sign = Db::name('express_order')->where(array('id'=>$id))->find();
                if($sign['orderStatus'] == 0){
                    Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>-1,'reason'=>'管理员删除'));
                    if($sign['coupon_download_id']){
                        model('ExpressOrder')->cancelCompleted($sign);
                    }
                    Db::name('express_order')->where('id',$id)->delete();
                    model('express_order')->commit();
                    $this->jinMsg('删除成功', url('express/index'));
                }
            }catch(\Exception $e){
                model('express_order')->rollback();
                $this->jinMsg('操作失败'.$e->getMessage());
            }
        }else{
            $i=0;
            $ids = input('id/a', false);
            if(is_array($ids)){
                foreach($ids as $id){
                    $sign = Db::name('express_order')->where(array('id'=>$id))->find();
                    if($sign['orderStatus'] == 0){
                        $i++;
                        Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>-1,'reason'=>'管理员删除'));
                        if($sign['coupon_download_id']){
                            model('ExpressOrder')->cancelCompleted($sign);
                        }
                        Db::name('express_order')->where('id',$id)->delete();
                    }
                }
                $this->jinMsg('批量删除成功【'.$i.'】条', url('express/index'));
            }
            $this->jinMsg('未选择数据', url('express/index'));
        }
    }

	

	
	
	//订单发货
	public function deliver($id = 0){
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($sign = Db::name('express_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		if($sign['orderStatus'] != 1){
			return json(array('code'=>'0','msg'=>'订单状态【'.$sign['orderStatus'].'】不正确'));
		}
		
		model('express_order')->startTrans();
		try{
			$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>2));
			model('express_order')->commit();
			$this->jinMsg('操作成功', url('express/index'));
		}catch(\Exception $e){
			model('express_order')->rollback();
			$this->jinMsg($e->getMessage());
		}
    }
	
	
	
	//管理员取消订单并退款remove
	public function remove($id = 0){
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($sign = Db::name('express_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		if($sign['orderStatus'] > 2){
			$this->jinMsg('订单状态【'.$sign['orderStatus'].'】不正确');
		}
		$cancel = model('ExpressOrder')->cancel($sign,$id,$reason='管理员取消订单并退款');
		if($cancel == false){
			$this->jinMsg('取消失败'.model('ExpressOrder')->getError());
		}else{
			$this->jinMsg('操作成功', url('express/index'));
		}
    }
	
	
	
	
	//用户取消订单并退款cancel
	public function cancel($id = 0){
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($sign = Db::name('express_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		if($sign['orderStatus'] == 0){
			$this->jinMsg('未付款订单不知此退款');
		}
		if($sign['orderStatus'] == 1){
			$this->jinMsg('已付款订单支持退款');
		}
		if($sign['orderStatus'] == 2){
			$this->jinMsg('已接单不支持退款');
		}
		if($sign['orderStatus'] == 3){
			$this->jinMsg('已取件不支持退款');
		}
		if($sign['orderStatus'] == 4){
			$this->jinMsg('已完成订单不支持退款');
		}
		if($sign['orderRightsStatus'] == 5){
			$this->jinMsg('已取消已退款订单不支持退款');
		}
		$cancel = model('ExpressOrder')->cancel($sign,$id,$reason='取消订单并退款');
		if($cancel == false){
			$this->jinMsg('取消失败'.model('ExpressOrder')->getError());
		}else{
			$this->jinMsg('操作成功', url('express/index'));
		}
    }
	
	
	
	
	
	//订单完成
	public function complete($id = 0){
		$config = $this->config;
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($sign = Db::name('express_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		if($sign['orderStatus'] != 2){
			$this->jinMsg('订单状态【'.$sign['orderStatus'].'】不正确');
		}
	
		$r = Db::name('express_order')->where(array('id'=>$id))->update(array('orderStatus'=>4));
		model('ExpressOrder')->completeProfit($sign,$sign['user_id'],'分销');
		model('ExpressOrder')->orderAddIntegral($sign,$sign['user_id'],'给用户奖励积分');
		
		model('express_order')->commit();
		$this->jinMsg('成功', url('express/index'));
    }
	
	
	//京东订阅
	public function subscribe($id = 0){
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($sign = Db::name('express_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		
		$subscribe = model('JdApi')->subscribe($sign);
		if($subscribe != false){
			$this->jinMsg('操作成功', url('express/index'));
		}
		$this->jinMsg('操作失败');
    }

	//京东查询运费
	public function updateActualfee($id = 0){
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($sign = Db::name('express_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		$updateActualfee = model('JdApi')->updateActualfee($sign);
		if($updateActualfee != false){
			$this->jinMsg('操作成功', url('express/index'));
		}
		$this->jinMsg('操作失败');
    }
    
    
    //更新易达得物地址
	public function dewu_yida($id = 0,$p = 0){
		$execute = model('Setting')->execute(NULL,$Method='QUERY_POIZON_ADDRESS');//同步易达得物地址
		if($execute['code'] == 200){
			$dewus = Db::name('user_addr_dewu')->where(array('type'=>1))->select();
			foreach($dewus as $k=>$v){
                Db::name('user_addr_dewu')->where(array('id'=>$v['id']))->delete();
            }
			$list = $execute['data'];
			$i=0;
			//p($list);die;
			foreach($list as $k => $v){
				$insert['is_dw'] = 1;
				$insert['type'] = 1;
				$insert['sender_id'] = $v['id'];
				$insert['sender_province'] = $v['receiveProvince'];
				$insert['sender_city'] = $v['receiveCity'];
				$insert['sender_area'] = $v['receiveDistrict'];
				$insert['sender_name'] = $v['receiveName'];
				$insert['sender_phone'] = $v['receiveTel'];
				$insert['sender_mobile'] = $v['receiveMobile'];
				$insert['sender_address'] = $v['receiveAddress'];
				$insert['create_time'] = time();
				if($v['id']){
					$i++;
					Db::name('user_addr_dewu')->insert($insert);
				}
			}
			$this->jinMsg('更新成功数据【'.$i.'】条', url('express/dewu'));
		}else{
			$this->jinMsg('获取失败'.$execute['msg']);
		}
    }
	

	
	
	//更新云洋得物地址
	public function dewu_yy($id = 0,$p = 0){
		$content['id'] = 'yy';
		$performance = model('Setting')->performance($content,'QUERY_DEWU_ADDRESS');//云洋物流地址
		if($performance['code'] == 1){
			$dewus = Db::name('user_addr_dewu')->where(array('type'=>2))->select();
			foreach($dewus as $k=>$v){
                Db::name('user_addr_dewu')->where(array('id'=>$v['id']))->delete();
            }
			$list = $performance['result'];
			$i=0;
			foreach($list as $k => $v){
				$insert['is_dw'] = 1;
				$insert['type'] = 2;
				$insert['sender_id'] = $v['id'];
				$insert['sender_province'] = $v['province'];
				$insert['sender_city'] = $v['city'];
				$insert['sender_area'] = $v['county'];
				$insert['sender_name'] = $v['sender'];
				$insert['sender_phone'] = $v['senderMobile'];
				$insert['sender_mobile'] = $v['senderMobile'];
				$insert['sender_address'] = $v['location'];
				$insert['create_time'] = time();
				if($v['id']){
					$i++;
					Db::name('user_addr_dewu')->insert($insert);
				}
			}
			$this->jinMsg('更新成功数据【'.$i.'】条', url('express/dewu'));
		}else{
			$this->jinMsg('获取失败'.$performance['message']);
		}
     }
	 
	 
	//导入默认数据
	public function cate_import(){
		$type = (int)input('type','','');
		$this->assign('type',$type);
		if($type==5){
			$w=1;
			$cate_imports = model('KuayueApi')->cate_imports();
			foreach($cate_imports as $key => $val){
				$cate = Db::name('express_cate')->where(array('cate_name'=>$val['name'],'type'=>$type))->find();
				if(!$cate){
					$data['type'] = $type;
					$data['cate_name'] = $val['name'];
					$data['ratio'] = 20;
					$data['priceA_type'] = 0;
					$data['priceA_ratio'] = 20;
					$data['priceA_price'] = 1;
					$data['priceB_type'] = 0;
					$data['priceB_ratio'] = 20;
					$data['priceB_price'] = 1;
					$data['info'] = '';
					$data['photo'] = $this->config['site']['host'].'/static/default/wap/img/'.$val['ioc'];
					$data['pinyin'] = $val['productCode'];
					$data['pinyin2'] = $val['productName'];
					$data['volumetext'] = '如您寄的是抛货或外包装太大，体积重量大于实际包裹重量时，体积重量将作为计费重量来计算运费';
					$data['lanshou'] = 80;
					$data['orderby'] = $key;
					$data['firstPrice'] = 0;
					if($data){
						$w++;
						Db::name('express_cate')->insertGetId($data);
					}
				}
			}
			$this->jinMsg('成功导入【'.(int)$w.'】数据请编辑快递公司详情', url('express/cate',array('type'=>$type)));
		}
		if($type==6){
			$cate = Db::name('express_cate')->where(array('cate_name'=>'领航当日达','type'=>$type))->find();
			if(!$cate){
				$data['type'] = $type;
				$data['cate_name'] = '领航当日达';
				$data['ratio'] = 20;
				$data['priceA_type'] = 0;
				$data['priceA_ratio'] = 20;
				$data['priceA_price'] = 1;
				$data['priceB_type'] = 0;
				$data['priceB_ratio'] = 20;
				$data['priceB_price'] = 1;
				$data['info'] = '';
				$data['photo'] = $this->config['site']['host'].'/static/default/wap/img/drd.png';
				$data['pinyin'] = 'GUOGUO';
				$data['pinyin2'] = $val['type'];
				$data['lanshou'] = 80;
				$data['orderby'] = $key;
				$data['firstPrice'] = 0;
				if($data){
					$q++;
					Db::name('express_cate')->insertGetId($data);
				}
			}
			$this->jinMsg('成功导入领航当日达', url('express/cate',array('type'=>$type)));	
		}
		
		if($type==7){
			$q=1;
			$productCodes = model('UlifegoApi')->productCodes();
			foreach($productCodes as $key => $val){
				$cate = Db::name('express_cate')->where(array('cate_name'=>$val['name'],'type'=>$type))->find();
				if(!$cate && $type!=8){
					$data['type'] = $type;
					$data['cate_name'] = $val['name'];
					$data['ratio'] = 20;
					$data['priceA_type'] = 0;
					$data['priceA_ratio'] = 20;
					$data['priceA_price'] = 1;
					$data['priceB_type'] = 0;
					$data['priceB_ratio'] = 20;
					$data['priceB_price'] = 1;
					$data['info'] = '';
					$data['photo'] = $this->config['site']['host'].'/static/default/wap/img/'.$val['ioc'];
					$data['pinyin'] = $val['productCode'];
					$data['pinyin2'] = $val['type'];
					$data['volumetext'] = '如您寄的是抛货(如羽绒服)或外包装太大，体积重量大于实际包裹重量时，体积重量将作为计费重量来计算运费';
					$data['lanshou'] = 80;
					$data['orderby'] = $key;
					$data['firstPrice'] = 0;
					if($data){
						$q++;
						Db::name('express_cate')->insertGetId($data);
					}
				}
			}
			$this->jinMsg('成功导入【'.(int)$q.'】数据请编辑快递公司详情', url('express/cate',array('type'=>$type)));
		}

        if($type==9){
            $y=0;
            $productCodes = model('YtApi')->productCodes();
            foreach($productCodes as $key => $val){
                $cate = Db::name('express_cate')->where(array('cate_name'=>$val['name'],'type'=>$type))->find();
                if(!$cate && $type==9){
                    $data['type'] = $type;
                    $data['cate_name'] = $val['name'];
                    $data['ratio'] = 20;
                    $data['priceA_type'] = 0;
                    $data['priceA_ratio'] = 20;
                    $data['priceA_price'] = 1;
                    $data['priceB_type'] = 0;
                    $data['priceB_ratio'] = 20;
                    $data['priceB_price'] = 1;
                    $data['info'] = '';
                    $data['photo'] = $this->config['site']['host'].'/static/default/wap/img/'.$val['ioc'];
                    $data['pinyin'] = 0;
                    $data['pinyin2'] = $val['productCode'];
                    $data['volumetext'] = '';
                    $data['lanshou'] = 80;
                    $data['orderby'] = $key;
                    $data['firstPrice'] = 0;
                    if($data){
                        $y++;
                        Db::name('express_cate')->insertGetId($data);
                    }
                }
            }
            $this->jinMsg('成功导入【'.(int)$y.'】数据请编辑快递公司详情', url('express/cate',array('type'=>$type)));
        }
        if($type==10){
            $this->jinMsg('【当前渠道只支持手动添加】联系微信120585022添加');
        }
		
		$this->jinMsg('其他渠道导入功能未开启');
    }
	
	//航空支付
	public function orderPay($id = 0){
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($v = Db::name('express_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		if($v['orderStatus'] != 1){
			$this->jinMsg('订单状态【'.$v['orderStatus'].'】不正确');
		}
		model('express_order')->startTrans();
		try{
			$orderPay = model('HangkongLinkApi')->orderPay($v);
			model('express_order')->commit();
			if($orderPay == false){
				$this->jinMsg(model('HangkongLinkApi')->getError());
			}else{
				$this->jinMsg('操作成功', url('express/index'));
			}
		}catch(\Exception $e){
			model('express_order')->rollback();
			$this->jinMsg($e->getMessage());
		}
    }
	
	
	//航空托运详情
	public function orderInfo($id = 0){
		$id = (int) $id;
		if(!$id){
			$this->jinMsg('id不存在');
		} 
		if(!($v = Db::name('express_order')->where(array('id'=>$id))->find())){
			$this->jinMsg('订单不存在');
		}
		$this->assign('v',$v);
		$this->assign('detail',$v);
		$orderInfo = model('HangkongLinkApi')->orderInfo($v);
		$this->assign('orderInfo',$orderInfo);
		
		$luggageOrder = $orderInfo['luggageOrder'];
		$this->assign('luggageOrder',$luggageOrder);
        echo $this->fetch();	
    }
	
	
	 public function options($id = 0){
        if($id = (int) $id){
            if(!($detail = Db::name('express_order')->find($id))){
                $this->error('请选择内容');
            }
            if(request()->post()){
                
				$option = input('options/a');
				$options = array();
				foreach($option['name'] as $key => $val){
					$options[] = array(
						'id' => $option['id'][$key],
						'name' => $option['name'][$key],
						'title' => $option['title'][$key],
						'time' => $option['time'][$key],
						'desc' => $option['desc'][$key],
						'sort' => $option['sort'][$key],
					);
				}
				if(empty($options)){
					$this->error = '没有设置有效的数据';
					return false;
				}
				$ids = array();
				foreach($options as $k => $val){
					if($val['name'] && $val['desc']){
						$option_id = $val['id'];
						if($option_id > 0){
							$val['shop_id'] = $shop_id;
							$val['order_id'] = $id;
							Db::name('express_order_options')->where(array('id'=>$val['id']))->update($val); 
						}else{
							$val['id'] = $option_id;
							$val['order_id'] = $id;
							$option_id = Db::name('express_order_options')->insertGetId($val); 
						}
						$ids[] = $option_id;
					}
				}
				
				$idss = array();
				foreach($ids as $v){
					if($v){
						$idss[] = $v;
					}
				}
				$ids = @implode(',',$idss);
				if($ids){
					$res = Db::name('express_order_options')->where(array('order_id'=>$id,'id'=>array('not in',$ids)))->select();
					foreach($res as $k =>$vv){
						 Db::name('express_order_options')->where(array('id'=>$vv['id']))->delete();
					}
				}
                $this->jinMsg('操作成功', url('express/options',array('id'=>$id)));
               
            }else{
				
				$options = Db::name('express_order_options')->where(array('order_id'=>$id))->select();
				$this->assign('options',$options);
				
				$this->assign('id', $id);
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        } else {
            $this->error('请选择要编辑的文章');
        }
    }

    public function cate_province($cate_id = 0){
        $cate_id = (int)$cate_id;
        $this->assign('cate_id',$cate_id);

        $express_cate = Db::name('express_cate')->find($cate_id);
        $this->assign('express_cate',$express_cate);

        $this->assign('provinceList', $provinceList = Db::name('copy_province')->order(array('id'=>'asc'))->select());
        return $this->fetch();
    }



    public function cate_edit_province($id = 0,$cate_id = 0){
        $id = (int)$id;
        $this->assign('id',$id);
        $copy_province = Db::name('copy_province')->find($id);
        $this->assign('copy_province',$copy_province);


        $cate_id = (int)$cate_id;
        $this->assign('cate_id',$cate_id);
        $express_cate = Db::name('express_cate')->find($cate_id);

        $this->assign('name',$copy_province['name']);
        $this->assign('express_cate',$express_cate);

        $provinceList = Db::name('copy_province')->order(array('id'=>'asc'))->select();
        foreach($provinceList as $key => $val){
            $express_cate_province = Db::name('express_cate_province')->where(array('cate_id'=>$cate_id,'star_province_id'=>$id,'end_province_id'=>$val['id']))->find();
            $provinceList[$key]['jia'] =$express_cate_province['jia'];
            $provinceList[$key]['shou'] = round($express_cate_province['shou']/100,2);
            $provinceList[$key]['xu'] = round($express_cate_province['xu']/100,2);
            $provinceList[$key]['shou1'] = round($express_cate_province['shou1']/100,2);
            $provinceList[$key]['xu1'] = round($express_cate_province['xu1']/100,2);
            $provinceList[$key]['shou2'] = round($express_cate_province['shou2']/100,2);
            $provinceList[$key]['xu2'] = round($express_cate_province['xu2']/100,2);
            $provinceList[$key]['shou3'] = round($express_cate_province['shou3']/100,2);
            $provinceList[$key]['xu3'] = round($express_cate_province['xu3']/100,2);
            $provinceList[$key]['shou4'] = round($express_cate_province['shou4']/100,2);
            $provinceList[$key]['xu4'] = round($express_cate_province['xu4']/100,2);
            $provinceList[$key]['info'] = $express_cate_province['info'];
        }

        $this->assign('provinceList',$provinceList);
        return $this->fetch();
    }

    public function cate_update_province($id = 0,$cate_id = 0){

        $deleteList = Db::name('express_cate_province')->where(array('cate_id'=>$cate_id,'star_province_id'=>$id))->order(array('id'=>'asc'))->select();
        $d=0;

        foreach($deleteList as $k => $v){
            $d++;
            Db::name('express_cate_province')->where(array('id'=>$v['id']))->delete();
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
                'jia' => $option['jia'][$key],
                'shou' => $option['shou'][$key]*100,
                'xu' => $option['xu'][$key]*100,
                'shou2' => $option['shou2'][$key]*100,
                'xu2' => $option['xu2'][$key]*100,
                'shou3' => $option['shou4'][$key]*100,
                'xu3' => $option['xu4'][$key]*100,
                'shou4' => $option['shou3'][$key]*100,
                'xu4' => $option['xu3'][$key]*100,
                'info' => $option['info'][$key],
                'cate_id' => $cate_id,
                'create_time'=>time()
            );
        }
        if(empty($options)){
            $this->jinMsg('没有设置有效的规格项');
        }
        $i=0;
        foreach($options as $k => $val){
            if($val['shou']){
                $i++;
                Db::name('express_cate_province')->insertGetId($val);
            }
        }
        $this->jinMsg('先删除【'.$d.'】条，再设置运费成功【'.$i.'】条', url('express/cate_edit_province',array('id'=>$id,'cate_id'=>$cate_id)));
    }

    public function cate_daoru_province($id = 0,$cate_id = 0,$name=''){
        $this->curl = new \Curl();

        $id = (int)$id;
        $copy_province = Db::name('copy_province')->find($id);

        $cate_id = (int)$cate_id;
        $express_cate = Db::name('express_cate')->find($cate_id);

        $postData['id'] = $id;
        $postData['name'] = $name;


        $postData['host'] = trim($this->config['site']['host']);
        $postData['mobile'] = trim($this->config['site']['mobile']);
        $url = getHost().'/api/Defaultdata/express_cate_provinces2';
        $result = $this->curl->post($url,json_encode($postData));
        $result = json_decode($result,true);
        $arr = $result['data'];


        $deleteList = Db::name('express_cate_province')->where(array('cate_id'=>$cate_id,'star_province_id'=>$id))->order(array('id'=>'asc'))->select();
        $d=0;
        foreach($deleteList as $k => $v){
            $d++;
            Db::name('express_cate_province')->where(array('id'=>$v['id']))->delete();
        }
        $i=0;
        foreach($arr as $key => $val){
            $insertGetIdData['province_id']= $id;
            $insertGetIdData['star_province_id']=  $id;
            $insertGetIdData['star_province_name']=  $val['star_province_name'];
            $insertGetIdData['end_province_id' ]=  $val['end_province_id'];
            $insertGetIdData['end_province_name' ]=  $val['end_province_name'];
            $insertGetIdData['jia']= 0;
            $insertGetIdData['shou']= $val['shou'];
            $insertGetIdData['xu']=  $val['xu'];
            $insertGetIdData['shou2']= $val['shou2'];
            $insertGetIdData['xu2']=  $val['xu2'];
            $insertGetIdData['shou3']= $val['shou2']-100;
            $insertGetIdData['xu3']=  $val['xu2']-50;
            $insertGetIdData['info']=  '云端导入';
            $insertGetIdData['cate_id']=  $cate_id;
            $insertGetIdData[ 'create_time']=time();
            if($val){
                $i++;
                Db::name('express_cate_province')->insertGetId($insertGetIdData);
            }
        }
        $this->jinMsg('先删除【'.$d.'】条，再云端导入运费成功【'.$i.'】条', url('express/cate_edit_province',array('id'=>$id,'cate_id'=>$cate_id)));
    }



    public function tuihuo(){
        $map = array();
        $id = (int)input('id','', 'trim,htmlspecialchars');
        if($id){
            $map['id'] = $id;
            $this->assign('id', $id);
        }

        $waybillNo = input('waybillNo');
        if($waybillNo){
            $map['waybillNo'] = $waybillNo;
            $this->assign('waybillNo', $waybillNo);
        }
        if($user_id = (int) input('user_id')){
            $map['user_id'] = $user_id;
            $users = Db::name('users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        $getSearchDate = $this->getSearchDate();
        if(is_array($getSearchDate)){
            $map['create_time'] = $getSearchDate;
        }
        $count = Db::name('express_order_tuihuo')->where($map)->count();
        $Page = new \Page($count, 25);
        $show = $Page->show();
        $list = Db::name('express_order_tuihuo')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k =>$v){
            $list[$k]['photo'] = Db::name('express_order_tuihuo_photo')->where(array('tuihuo_id'=>$v['id']))->select();
            $list[$k]['user'] = Db::name('users')->where(array('user_id'=>$v['user_id']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        cookie('express_order_tuihuo',$map);
        $this->assign('count',$count);
        return $this->fetch();
    }

    public function tuihuo_edit($id = 0){
        if($id = (int) $id){
            if(!($detail = Db::name('express_order_tuihuo')->find($id))){
                $this->error('请选择要编辑');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false),array('waybillNo','banci','info'));
                $data['id'] = $id;
                if(false !== Db::name('express_order_tuihuo')->update($data)) {
                    $this->jinMsg('操作成功', url('express/tuihuo'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑');
        }
    }


    public function tuihuo_delete($id = 0){
        if (is_numeric($id) && ($id = (int)$id)) {
            Db::name('express_order_tuihuo')->where('id',$id)->delete();
            Db::name('express_order_tuihuo_photo')->where('tuihuo_id',$id)->delete();
            $this->jinMsg('删除成功', url('express/tuihuo'));
        } else {
            $this->jinMsg('删除失败');
        }
    }
    public function express_order_tuihuo_photo($v){
         $photos = Db::name('express_order_tuihuo_photo')->where(array('tuihuo_id'=>$v['id']))->select();
            $last_key = key(end($photos));
            foreach($photos as $k2 =>$v2) {
                if ($k2 === $last_key) {
                    $photo .= $v2['photo'];
                } else {
                    $photo .= $v2['photo'] . ';';
                }
            }
        return $photo;
    }
    public function tuihuo_export(){
        $orders = Db::name('express_order_tuihuo')->where(cookie('express_order_tuihuo'))->order(array('id'=>'desc'))->limit(0,3000)->select();
        foreach($orders as $k =>$v){
            $orders[$k]['photo'] = $this->express_order_tuihuo_photo($v);
        }
        $date = date("Y_m_d H:i:s", time());
        $filetitle = "订单列表";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array(
            'aa' => '日期',
            'bb' => '站点',
            'cc' => '站点代码',
            'dd' => '班次',
            'ee' => '重量',
            'ff' => '单号',
            'gg' => '破损图片',
            'hh' => '备注'
        );
        foreach ($filter as $key => $title){
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach ($orders as $k => $v){
            $filter = array(
                'aa' => '日期',
                'bb' => '站点',
                'cc' => '站点代码',
                'dd' => '班次',
                'ee' => '重量',
                'ff' => '单号',
                'gg' => '破损图片',
                'hh' => '备注'
            );
            $orders[$k]['aa'] = date('Y-m-d H:i:s',$v['create_time']);
            $orders[$k]['bb'] = '站点';
            $orders[$k]['cc'] = '站点代码';
            $orders[$k]['dd'] = $v['banci'];
            $orders[$k]['ee'] = '';
            $orders[$k]['ff'] = $v['waybillNo'];
            $orders[$k]['gg'] = $v['photo'];
            $orders[$k]['hh'] = $v['info'];
            foreach($filter as $key => $title){
                $html .= $orders[$k][$key] . "\t,";
            }
            $html .= "\n";
        }
        ob_end_clean();
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename={$fileName}.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $html;
        exit;
    }

}