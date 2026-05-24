<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Pao extends Base{


	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->host = $this->config['site']['host'];
		$this->curl = new \Curl();
    }


	public function getallheaders(){ 
       foreach($_SERVER as $name =>$value){ 
           if(substr($name,0,5) == 'HTTP_'){ 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
	
	public function getUserId(){
		$token = input('token','','trim,htmlspecialchars');
		$user_id = Db::name('users')->where(array('token'=>$token))->value('user_id');
		return (int)$user_id;
	}
	

	
	
	public function getSet(){
		$uid = $this->getUserId();
		if(!$uid){
			return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
		}

		$data['delivery'] = $this->config['delivery'];

        $Exp = new Exp();
        $Exp = $Exp->yuyuetime();
        $Exp = json_decode($Exp->getContent(),true);
        $data['dataSource'] = $Exp['data'];


        return json(array('code'=>'1','time'=>time(),'msg'=>'接收成功','data'=>$data));
	}

    public function getNeedPayData(){
        $uid = $this->getUserId();
        if(!$uid){
            return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
        }
        $status = input('status','','trim,htmlspecialchars');
        $tip_money = input('tip_money','','trim,htmlspecialchars');
        $wight = input('wight','','trim,htmlspecialchars');
        $num = input('num','','trim,htmlspecialchars');


        $d = $this->config['delivery'];
        if($status==0){
            $status_money = $d['status_money_1'];
            $status_money_kg_1 = $d['status_money_kg_1'];
        }
        if($status==1){
            $status_money = $d['status_money_2'];
            $status_money_kg_2 = $d['status_money_kg_2'];

        }
        if($status==2){
            $status_money = $d['status_money_3'];
            $status_money_kg_3 = $d['status_money_kg_3'];
        }
        if($status==3){
            $status_money = $d['status_money_4'];
            $status_money_kg_4 = $d['status_money_kg_4'];
        }
        if($status==4){
            $status_money = $d['status_money_5'];
            $status_money_kg_5 = $d['status_money_kg_5'];
        }
        if($status==5){
            $status_money = $d['status_money_6'];
            $status_money_kg_6 = $d['status_money_kg_6'];
        }
        $status_money = $status_money*100;

        if($wight>=100){
            $status_wight = $d['status_wight_6'];
        }
        if($wight<100 && $wight>=50){
            $status_wight = $d['status_wight_6'];
        }
        if($wight<50 && $wight>=30){
            $status_wight = $d['status_wight_5'];
        }
        if($wight<30 && $wight>=20){
            $status_wight = $d['status_wight_4'];
        }
        if($wight<20 && $wight>=10){
            $status_wight = $d['status_wight_3'];
        }
        if($wight<10 && $wight>=5){
            $status_wight = $d['status_wight_2'];
        }
        if($wight<5){
            $status_wight = $d['status_wight_1'];
        }
        $status_wight = $status_wight*100;

        $tip_money = $tip_money*100;
        $needpay = $status_wight+$status_money;
        $needpay = $needpay*$num;
        $needpay = $needpay+$tip_money;


        $data['needpay'] = $needpay;
        $data['need_pay'] = round($needpay/100,2);;

        return json(array('code'=>'1','time'=>time(),'msg'=>'接收成功','data'=>$data));
    }


    public function orderAdd(){
        $uid = $this->getUserId();
        if (!$uid) {
            return json(array('code' => 0, 'time' => time(), 'msg' => 'TOKEN失效', 'data' => ''));
        }
        $param = $this->request->param();
        $param['type'] = $param['status'];
        unset($param['status']);

        $params = @json_decode($param['detail'],true);
        $data = @array_merge($param,$params);


        $op = $data['sendlat'].','.$data['sendlng'];
        $this->curl = new \Curl();
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?location=".$op."&key=IRTBZ-7KIR5-E6CIW-QEK5Z-EZIU5-JVFV7&get_poi=0&coord_type=1";
        $html = file_get_contents($url);
        if(!$html){
            $html = $this->curl->get($url);
        }
        $html = json_decode($html,true);

        if($html['status'] == 0){
            $town = $html['result']['address_reference']['town'];
            $business_id = $town['id'];
            $area_id = Db::name('copy_business')->where('business_id',$business_id)->value('area_id');
            $city_id = Db::name('copy_area')->where('area_id',$area_id)->value('city_id');
            $province_id = Db::name('copy_city')->where('city_id',$city_id)->value('ParentId');
            $data['province_id'] = $province_id;
            $data['area_id'] = $area_id;
            $data['city_id'] = $city_id;
            $data['business_id'] = $business_id;
        }


        $info = amapDistance($data['sendlng'],$data['sendlat'],$data['receivelng'],$data['receivelat'],1);
        unset($info['info']);
        $data = @array_merge($data,$info);

        $data['needpay'] = $data['need_pay'];
        $data['need_pay'] = $data['need_pay']*100;
        $data['senddate'] = $data['hourMinuteSecond'];
        $data['receivedate'] = $data['hourMinuteSecond2'];
        $data['status'] = 0;
        $data['uid'] = $uid;
        $data['userid'] = $uid;
        $data['create_time'] = time();

        $need_pay = $data['need_pay']*100;
        if (!$need_pay) {
            return json(array('code' => 0, 'time' => time(), 'msg' => '支付金额错误'));
        }
        $id = Db::name('delivery_order')->insertGetId($data);
        if($id){
            $logs = array(
                'type' => 'pao',
                'types' => 1,
                'user_id' => $uid,
                'order_id' => $id,
                'code' => 'wxapp',
                'info' => $info,
                'need_pay' =>$need_pay,
                'create_time' => time(),
                'create_ip' => request()->ip(),
                'is_paid' => 0
            );
            $logs['log_id'] = Db::name('payment_logs')->insertGetId($logs);

            $info = '跑腿订单-'.$id.'支付';
            $connect = Db::name('connect')->where(array('uid'=>$uid))->find();
            $WX_OPENID = $connect['openid'] ? $connect['openid'] : $connect['open_id'];
            $Payment = model('Payment')->getPayment('wxapp');
            $out_trade_no = $logs['log_id'].'-'.time();
            $weixinpay = new \Wxpay($this->config['wxapp']['appid'],$WX_OPENID,$Payment['mchid'],$Payment['appkey'],$out_trade_no,$info,$need_pay);//支付接口
            $return = $weixinpay->pay();
            if($return['package'] == 'prepay_id='){
                return json(array('code'=>0,'info'=>$info,'need_pay'=>$need_pay,'msg'=>'预支付失败:'.$return['rest']['return_msg']));
            }
            $data['timeStamp']= $return['timeStamp'];
            $data['nonceStr'] =$return['nonceStr'];
            $data['package'] =$return['package'];
            $data['paySign'] = $return['paySign'];
            $data['signType'] = 'MD5';
            return json(array('code'=>'1','time'=>time(),'msg'=>'接收成功','data'=>$data));
        }
        return json(array('code' => 0, 'time' => time(), 'msg' => '下单失败', 'data' => ''));

    }
    public function paoDelivery(){
        $uid = $this->getUserId();
        if(!$uid){
            return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
        }
        $id = input('id','','trim,htmlspecialchars');
        $cdo = Db::name('city_delivery_order')->where(array('id'=>$id))->find();


        $cd = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();
        $cd['city'] = Db::name('city')->where(array('city_id'=>$cd['city_id']))->find();
        $cd['area'] = Db::name('area')->where(array('area_id'=>$cd['area_id']))->find();
        $cd['business'] = Db::name('business')->where(array('business_id'=>$cd['business_id']))->find();
        $cd['photo'] = config_weixin_img($cd['photo']);
        if(!$cd){
            return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
        }
        $cd['cdo'] = $cdo;
        $cd['cdo']['img'] = config_weixin_img($cdo['img']);
        return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$cd));
    }

    public function deliveryOrder(){

        $getTypes = model('Delivery')->getTypes();
        $getStatus = model('DeliveryOrder')->getStatus();

        $uid = $this->getUserId();
        if(!$uid){
            return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
        }
        $cd = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();
        if(!$cd){
            return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
        }
        $status = (int)input('status','','trim,htmlspecialchars');
        $keywords = input('keywords','','trim,htmlspecialchars');
        $id = (int)input('id','','trim,htmlspecialchars');
        $page = input('page','','trim');
        $row = input('row','','trim');

        $map = [];
        if($status){
            $map['status'] =$status;
        }
        if($status>1){
            $map['delivery_id'] = $cd['id'];
        }
        if($id){
            $map['id'] = $id;
        }
        if($keywords){
            $map['id|sendname|sendmobile|receivename|receivemobile'] = array('LIKE','%'.$keywords.'%');
        }

        $count = Db::name('delivery_order')->where($map)->count();
        if($page == 1){
            $firstRow = 0;
            $listRows = $row;
        }else{
            $firstRow = $page*$row;
            $listRows = $row;
        }
        $Page = new \Page3($count,10);
        $show = $Page->show();

        if($Page->totalPages < $page){
            $list = array();
        }else{
            $list = Db::name('delivery_order')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
            foreach($list as $k=>$v){
                $list[$k]['city'] = Db::name('copy_city')->where(array('city_id'=>$v['city_id']))->find();
                $list[$k]['area'] = Db::name('copy_area')->where(array('area_id'=>$v['area_id']))->find();
                $list[$k]['business'] = Db::name('copy_business')->where(array('business_id'=>$v['business_id']))->find();
                $list[$k]['createtime'] = date('Y-m-d H:i:s',$v['create_time']);
                $list[$k]['deliverytime'] = date('Y-m-d H:i:s',$v['delivery_time']);
                $list[$k]['peisongtime'] = date('Y-m-d H:i:s',$v['peisong_time']);
                $list[$k]['endtime'] = date('Y-m-d H:i:s',$v['end_time']);
                $list[$k]['refundtime'] = date('Y-m-d H:i:s',$v['refund_time']);
                $list[$k]['imgs'] = config_weixin_img($v['img']);
                $list[$k]['imgs1'] = config_weixin_img($v['img1']);
                $list[$k]['getTypes'] = $getTypes[$v['type']];
                $list[$k]['getStatus'] = $getTypes[$v['status']];
            }
        }

        $data['count'] = $count;
        $data['totalPages'] = $Page->totalPages;
        $data['page'] = $page;
        $data['firstRow'] = $Page->firstRow;
        $data['listRows'] = $Page->listRows;
        $data['data'] = $list;
        $data['total'] = $count;
        return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$data));
    }


    public function paoQiang(){
        $uid = $this->getUserId();
        if(!$uid){
            return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
        }
        $d = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();
        if(!$d){
            return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
        }
        $id = input('id','','trim,htmlspecialchars');
        $do = Db::name('delivery_order')->where(array('id'=>$id))->find();
        if(!$do){
            return json(array('code'=>0,'time'=>time(),'msg'=>'配送订单不存在','data'=>''));
        }
        if($uid == $do['user_id']){
            return json(array('code'=>0,'time'=>time(),'msg'=>'当前uid【'.$uid.'】不能抢【'.$do['user_id'].'】的订单','data'=>''));
        }

        $deliveryId = input('deliveryId','','trim,htmlspecialchars');
        $imgs = input('img','','trim,htmlspecialchars');
        $name = input('name','','trim,htmlspecialchars');
        $mobile = input('mobile','','trim,htmlspecialchars');
        $info = input('info','','trim,htmlspecialchars');

        $data['delivery_id'] = $d['id'];
        $data['imgs'] = $imgs;
        $data['name'] = $name;
        $data['mobile'] = $mobile;
        $data['info2'] = $info;
        $data['status'] = 2;
        $r = Db::name('delivery_order')->where(array('id'=>$id))->update($data);
        if($r){
            return json(array('code'=>0,'time'=>time(),'msg'=>'编辑失败1','data'=>''));
        }
        return json(array('code'=>'1','time'=>time(),'msg'=>'接单成功','data'=>$do));
    }

    public function paoPeisong(){
        $uid = $this->getUserId();
        if(!$uid){
            return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
        }
        $d = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();
        if(!$d){
            return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
        }
        $id = input('id','','trim,htmlspecialchars');
        $do = Db::name('delivery_order')->where(array('id'=>$id))->find();
        if(!$do){
            return json(array('code'=>0,'time'=>time(),'msg'=>'配送订单不存在','data'=>''));
        }
        $data['status'] = 3;
        $data['peisong_time'] = time();
        $r = Db::name('delivery_order')->where(array('id'=>$id))->update($data);
        if($r){
            return json(array('code'=>0,'time'=>time(),'msg'=>'操作失败1','data'=>''));
        }
        return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$do));
    }




    public function paoEnd(){
        $uid = $this->getUserId();
        if(!$uid){
            return json(array('code'=>0,'time'=>time(),'msg'=>'TOKEN失效','data'=>''));
        }
        $d = Db::name('city_delivery')->where(array('user_id'=>$uid))->find();
        if(!$d){
            return json(array('code'=>0,'time'=>time(),'msg'=>'您不是配送员','data'=>''));
        }
        $id = input('id','','trim,htmlspecialchars');
        $do = Db::name('delivery_order')->where(array('id'=>$id))->find();
        if(!$do){
            return json(array('code'=>0,'time'=>time(),'msg'=>'配送订单不存在','data'=>''));
        }
        $data['delivery_time'] = time();
        $data['status'] = 8;
        $r = Db::name('delivery_order')->where(array('id'=>$id))->update($data);
        if($r){
            $m = $d['money'];
            $i = '订单ID【'. $id.'】配送员完成订单奖励';
            if($m> 0){
                $rest = model('Users')->addMoney($uid,$m,$i,7);
            }
            return json(array('code'=>1,'time'=>time(),'msg'=>'操作成功','data'=>$do));
        }
        return json(array('code'=>1,'time'=>time(),'msg'=>'接收成功','data'=>$do));
    }

}
