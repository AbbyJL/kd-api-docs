<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Jifen extends Base{


	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->host = $this->config['site']['host'];
		$this->curl = new \Curl();
    }
	
	
	public function Ad3() {
		$list = Db::name('ad')->where(array('site_id'=>'116','closed'=>'0'))->select();
		foreach($list as $k => $val){
			$list[$k]['type'] = 1;
			$list[$k]['id'] = $val['ad_id'];
			$list[$k]['logo'] = config_weixin_img($val['photo']);
		}
        $json_str = json_encode($list);
        exit($json_str); 
		
	}

    public function jfType() {
        $type_id = input('type_id','','trim,htmlspecialchars');
		
		$arr = Db::name('integral_goods_cate')->where(array('parent_id'=>$type_id))->limit(0,8)->select();
		if(!$arr){
			$arr = Db::name('integral_goods_cate')->where(array('parent_id'=>$type_id))->limit(0,8)->select();
		}
		
		$kk = 0;
		foreach($arr as $k => $val){
			$kk ++ ;
			$arr[$k]['name'] = $val['cate_name'];
			$arr[$k]['id'] = $val['cate_id'];
			$arr[$k]['map'] = $val['cate_id'];
			$arr[$k]['img'] = config_weixin_img($val['photo']);
		}
        $json_str = json_encode($arr);
        exit($json_str); 
    }
	
	
	public function JfGoods(){
		$list = Db::name('integral_goods')->where(array('closed'=>0,'audit'=>1))->order('create_time desc')->limit(0,50)->select();
        foreach($list as $k => $val){
			$list[$k]['id'] = $val['goods_id'];
			$list[$k]['name'] = $val['title'];
			$list[$k]['integral'] = $val['integral'];
			$list[$k]['money'] = round($val['money']/100,2);
			$list[$k]['type'] = 2;
			$list[$k]['img'] = config_weixin_img($val['face_pic']);
			$list[$k]['imgs'] = model('IntegralGoods')->getImgs($val['goods_id'],$val['face_pic']);
			$list[$k]['options'] = model('IntegralGoods')->getOptions($val['goods_id']);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	public function JftypeGoods(){
		$type_id = input('type_id','','trim');
		$list = Db::name('integral_goods')->where(array('closed'=>0,'audit'=>1,'cate_id'=>$type_id))->order('create_time desc')->limit(0,50)->select();
        foreach($list as $k => $val){
			$list[$k]['id'] = $val['goods_id'];
			$list[$k]['name'] = $val['title'];
			$list[$k]['integral'] = $val['integral'];
			$list[$k]['money'] = round($val['money']/100,2);
			$list[$k]['type'] = 2;
			$list[$k]['img'] = config_weixin_img($val['face_pic']);
			$list[$k]['imgs'] = model('IntegralGoods')->getImgs($val['goods_id'],$val['face_pic']);
			$list[$k]['options'] = model('IntegralGoods')->getOptions($val['goods_id']);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	
	
	public function JfGoodsInfo(){
		$goods_id = input('id','','trim');
		$detail[0] = Db::name('integral_goods')->find($goods_id);
		$detail[0]['id'] = $detail[0]['goods_id'];
		$detail[0]['name'] = $detail[0]['title'];
		$detail[0]['number'] = $detail[0]['num'];
		$detail[0]['integral'] = $detail[0]['integral'];
		$detail[0]['money'] = round($detail[0]['money']/100,2);
		$detail[0]['type'] = 2;
		$detail[0]['img'] = config_weixin_img($detail[0]['face_pic']);
		$detail[0]['imgs'] = model('IntegralGoods')->getImgs($goods_id,$detail[0]['face_pic']);
		$detail[0]['options'] = model('IntegralGoods')->getOptions($goods_id);
        $json_str = json_encode($detail);
        exit($json_str); 
	}
	
	
	//兑换记录
	public function Dhmx(){
		$user_id = input('user_id','','trim');
		$list = Db::name('integral_exchange')->where(array('user_id'=>$user_id))->order('create_time desc')->limit(0,50)->select();
        foreach($list as $k => $val){
			$goods = Db::name('integral_goods')->where(array('goods_id'=>$val['goods_id']))->find();
			$list[$k]['id'] = $val['exchange_id'];
			$list[$k]['integral'] = $val['integral'];
			$list[$k]['money'] = round($val['money']/100,2);
			$list[$k]['time'] = date('Y-m-d H:i:s',$val['create_time']);
			$list[$k]['good_name'] = $goods['title'];
			$list[$k]['good_img'] = config_weixin_img($goods['face_pic']);
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	
	//兑换记录
	public function UserInfo(){
		$user_id = input('user_id','','trim');
		$users = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$users['total_score'] = (int)$users['integral'];
        $json_str = json_encode($users);
        exit($json_str); 
	}


    public function Exchange(){
        $data['user_id'] = (int)input('user_id','','trim');
        $uid = $data['user_id'];
        $users = Db::name('users')->where(array('user_id'=>$data['user_id']))->find();
        if(!$users){
            return json(array('code'=>0,'msg'=>"会员不存在"));
        }
        $good_id = (int)input('good_id','','trim');
        $data['goods_id'] = $good_id;

        $detail = Db::name('integral_goods')->where(array('goods_id'=>$data['goods_id']))->find();
        if(!$detail){
            return json(array('code'=>0,'msg'=>"商品不存在"));
        }
        if($detail['num'] <1){
            return json(array('code'=>0,'msg'=>"当前商品没库存了"));
        }
        $count = Db::name('integral_exchange')->where(array('user_id'=>$data['user_id'],'goods_id'=>$data['goods_id'],'status'=>2))->count();
        if($detail['limit_num'] && $count >= $detail['limit_num']){
            return json(array('code'=>0,'msg'=>"当前商品有兑换限制"));
        }
        $data['name'] = input('user_name','','trim,htmlspecialchars');
        if(!$data['name']){
            return json(array('code'=>0,'msg'=>"必须填写姓名"));
        }
        $data['mobile'] = input('user_tel','','trim,htmlspecialchars');
        if(!$data['mobile']){
            return json(array('code'=>0,'msg'=>"必须填写电话"));
        }
        $data['addr'] = input('address','','trim,htmlspecialchars');
        if(!$data['addr']){
            return json(array('code'=>0,'msg'=>"必须填写地址"));
        }

        $id = (int)input('id','','trim,htmlspecialchars');
        $options = Db::name('integral_goods_options')->where(array('id'=>$id))->find();
        if(!$options && $id){
            return json(array('code'=>0,'msg'=>"规格不存在"));
        }
        if($options['total']<=0 && $id){
            return json(array('code'=>0,'msg'=>"规格库存不足"));
        }
        $price = input('price','','trim,htmlspecialchars');
        $price = $price*100;
        $total = input('total','','trim,htmlspecialchars');
        $name = input('name','','trim,htmlspecialchars');



        if($users['integral'] < $detail['integral'] && $id <=0){
            return json(array('code'=>0,'msg'=>"积分不足"));
        }
        if($name && $id){
            $data['title'] = $detail['title'].'【'.$name.'】';
            $data['money'] = $price;
            $data['integral'] = 0;
            $data['options_id'] = $id;
        }else{
            $data['title'] = $detail['title'];
            $data['money'] = $detail['money'];
            $data['integral'] = $detail['integral'];
            $data['options_id'] = 0;
        }

        $data['num'] = 1;//购买数量
        $data['cate_id'] = $detail['cate_id'];
        if($data['money'] <=0){
            $data['status'] = 2;
        }else{
            $data['status'] = 1;
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = request()->ip();

        $info = '【小程序】兑换产品'.$data['goods_id'];
        $need_pay = $data['money'];

        if($exchange_id = Db::name('integral_exchange')->insertGetId($data)){

            if($data['money'] <= 0){
                model('Users')->addIntegral($data['user_id'],-$detail['integral'],$info,7);
                Db::name('integral_goods')->where(array('goods_id'=>$goods_id))->update(array('num'=>$detail['num']-1,'exchange_num'=>$detail['exchange_num']+1));
                return json(array('code'=>1,'msg'=>"兑换成功"));
            }
            if($data['money'] > 0){
                $logs = array(
                    'type' => 'exchange',
                    'types' => '1',
                    'user_id' => $uid,
                    'order_id' => $exchange_id,
                    'code' => 'wxapp',
                    'info' => $info,
                    'need_pay' =>$need_pay,
                    'create_time' => time(),
                    'create_ip' => request()->ip(),
                    'is_paid' => 0
                );
                $logs['log_id'] = Db::name('payment_logs')->insertGetId($logs);

                $connect = Db::name('connect')->where(array('uid'=>$uid))->find();
                $WX_OPENID = $connect['open_id'] ? $connect['open_id'] : $connect['openid'];
                $out_trade_no = $logs['log_id'].'-'.time();



                $Payment = model('Payment')->getPayment('wxapp');

                $weixinpay = new \Wxpay($this->config['wxapp']['appid'],$WX_OPENID,$Payment['mchid'],$Payment['appkey'],$out_trade_no,$info,$need_pay);//支付接口
                $return = $weixinpay->pay();
                if($return['package'] == 'prepay_id='){
                    return json(array('code'=>0,'msg'=>'预支付失败:'.$return['rest']['return_msg']));
                }
                $data['timeStamp']= $return['timeStamp'];
                $data['nonceStr'] =$return['nonceStr'];
                $data['package'] =$return['package'];
                $data['signType'] = 'MD5';
                $data['paySign'] = $return['paySign'];
                return json(array('code'=>2,'msg'=>"获取成功",'data'=>$data));
            }

        }
        return json(array('code'=>0,'msg'=>"兑换失败"));
    }
	
	
	

	
}
