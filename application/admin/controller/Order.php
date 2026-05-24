<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;
use app\common\model\Setting as SettingModel;

class Order extends Base{
	
    private $create_fields = array();
	protected $config = array();
    protected $ranks = array();
	public function _initialize(){
        parent::_initialize();
		$this->config  = SettingModel::config(0);
		$this->assign('getorderStatus', $getorderStatus = model('Setting')->getorderStatus());
		$this->assign('getdiffStatus', $getdiffStatus = model('Setting')->getdiffStatus());
        $this->assign('getorderRightsStatus', $getorderRightsStatus = model('Setting')->getorderRightsStatus());
		$this->assign('getCompanyApiTypes', $getCompanyApiTypes = model('Setting')->getCompanyApiTypes());
		$this->config  = SettingModel::config(0);
    }
	
    
	
	
    public function orderCreate($user_id = 0){
		$type = input('user_id','','trim,htmlspecialchars');
		$this->assign('user_id', $user_id);
		$this->assign('copy_province', $copy_province = Db::name('copy_province')->select());
		return $this->fetch();
    }
	
	
	//后台下单
	public function create(){
		
		
		if(request()->post()){
            $data = $this->checkFields(input('data/a', false),array(
				'user_id','cargoName','wight','kuaidi','remark','sendName','sendMobile','senderProvince', 'senderCity','senderCounty','sendAddress',
				'receiveName','receiveMobile','receiveProvince','receiveCity','receiveCounty','receiveAddress','orderStatus','sumMoneyYuan',
				'deliveryId'
			));
			$user_id = (int) $data['user_id'];
			$orderStatus = (int) $data['orderStatus'];
			$cargoName = htmlspecialchars($data['cargoName']);
			$kuaidi = htmlspecialchars($data['kuaidi']);
			$deliveryId = htmlspecialchars($data['deliveryId']);
			$sumMoneyYuan = (int) ($data['sumMoneyYuan']*100);
			
			$datas['sender_province'] = htmlspecialchars($data['senderProvince']);
			$datas['sender_city'] = htmlspecialchars($data['senderCity']);
			$datas['sender_area'] = htmlspecialchars($data['senderCounty']);
			$datas['sender_address'] = htmlspecialchars($data['sendAddress']);
			$datas['sender_name'] = htmlspecialchars($data['sendName']);
			$datas['sender_mobile'] = htmlspecialchars($data['sendMobile']);
			$datas['sender_phone'] = htmlspecialchars($data['sendMobile']);
			$datas['recipients_province'] = htmlspecialchars($data['receiveProvince']);
			$datas['recipients_city'] = htmlspecialchars($data['receiveCity']);
			$datas['recipients_area'] = htmlspecialchars($data['receiveCounty']);
			$datas['recipients_address'] = htmlspecialchars($data['receiveAddress']);
			$datas['recipients_name'] = htmlspecialchars($data['receiveName']);
			$datas['recipients_mobile'] = htmlspecialchars($data['receiveMobile']);
			$datas['recipients_phone'] = htmlspecialchars($data['receiveMobile']);
			
		
			$totalWeight = htmlspecialchars($data['wight']);
			$totalWeight = @ceil($totalWeight);
			$totalWeight =  (int)$totalWeight;
			$remark = htmlspecialchars($data['remark']);
			
			
			$u = Db::name('users')->where(array('user_id'=>$user_id))->field('user_id,parent_id')->find();
			
			$datas['sender_province'] = htmlspecialchars($data['senderProvince']);
			$datas['sender_city'] = htmlspecialchars($data['senderCity']);
			$datas['sender_area'] = htmlspecialchars($data['senderCounty']);
			$datas['sender_address'] = htmlspecialchars($data['sendAddress']);
			$datas['sender_name'] = htmlspecialchars($data['sendName']);
			$datas['sender_mobile'] = htmlspecialchars($data['sendMobile']);
			$datas['sender_phone'] = htmlspecialchars($data['sendMobile']);
			$datas['recipients_province'] = htmlspecialchars($data['receiveProvince']);
			$datas['recipients_city'] = htmlspecialchars($data['receiveCity']);
			$datas['recipients_area'] = htmlspecialchars($data['receiveCounty']);
			$datas['recipients_address'] = htmlspecialchars($data['receiveAddress']);
			$datas['recipients_name'] = htmlspecialchars($data['receiveName']);
			$datas['recipients_mobile'] = htmlspecialchars($data['receiveMobile']);
			$datas['recipients_phone'] = htmlspecialchars($data['receiveMobile']);
			
			
			$oid = Db::name('express_order')->order('id desc')->limit(0,1)->value('id');
			$thirdNo = ($oid+1).rand_string(6,1);//外部单号
			$data['is_pei'] = 0;
			$data['orderType'] = 0;
			$data['is_piliang'] = 3;
			$data['kuaidi'] = $kuaidi;
			$data['cargoName'] = $cargoName;
			$data['pid'] = $u['parent_id'];
			$data['deliveryId'] = $deliveryId;
			$data['expressId'] = 0;
			$data['closed'] = 0;
			$data['expressNo'] = 0;
			$data['user_id'] = $user_id;
			$data['orderStatus'] = $orderStatus;
			$data['diffStatus'] = 0;
			$data['orderNo'] = $thirdNo;
			$data['orderRightsStatus'] = 0;
			$data['createTime'] = time();
			$data['wight'] = $totalWeight;
			$data['totalNumber'] = 1;
			$data['insuranceValue'] = 0;
			$data['insurancePrice'] = 0;
			$data['wight'] = $totalWeight;
			$data['totalVolume'] = '';
			$data['sumMoneyYuan'] = $sumMoneyYuan;
			$data['diffMoneyYuan'] =0;
			$data['sendName'] = $datas['sender_name'];
			$data['sendMobile'] = $datas['sender_mobile'];
			$data['senderProvince'] = $datas['sender_province'];
			$data['sendCity'] = $datas['sender_city'];
			$data['senderArea'] = $datas['sender_area'];
			$data['sendAddress'] = $datas['sender_address'];
			$data['receiveName'] = $datas['recipients_name'];
			$data['receiveMobile'] = $datas['recipients_mobile'];
			$data['receiveProvince'] = $datas['recipients_province'];
			$data['receiveCity'] = $datas['recipients_city'];
			$data['receiveArea'] = $datas['recipients_area'];
			$data['receiveAddress'] = $datas['recipients_address'];
			$data['create_time'] = time();
			$data['remark'] = $remark;
			$data['yuyuetime'] = '';
			$data['is_company'] = 3;
			
			
			$order_id = Db::name('express_order')->insertGetId($data);
			if($order_id){
				$need_pay = $order_money*100; 
				$types = 1;
				$info = '后台快递下单';
				$logs = array(
					'type' => 'express', 
					'types' => $types, 
					'user_id' => $user_id, 
					'order_id' => $order_id, 
					'code' => 'wxapp', 
					'info' => $info, 
					'need_pay' =>$need_pay, 
					'create_time' => time(), 
					'create_ip' => request()->ip(), 
					'is_paid' => 0
				);
				$logs['log_id'] = Db::name('payment_logs')->insertGetId($logs);
				
				$this->jinMsg('添加成功', url('order/index'));
			}else{
				$this->jinMsg('写入数据库失败');
			}
		}else{
			echo $this->fetch();
		}
	}
	
	
	
	public function senderProvince() {
		$upid = input('upid');
		$outArr = array();
		$List = Db::name('copy_city')->where(array('ParentId' =>$upid))->select();
		foreach($List as $k => $v){
			$MergerName = explode(',',$v['MergerName']);
			$List[$k]['Name'] = $MergerName[2] ? $MergerName[2] : $v['name'];
		}
		return json($List);
	}
    public function senderCity() {
		$upid = input('upid');
		$outArr = array();
		$List = Db::name('copy_area')->where(array('city_id' =>$upid))->select();
		return json($List);
	}
	
	
	public function index(){
        $map = array('is_piliang'=>3);
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
		
        return $this->fetch('express/index');
    }
	
	
}