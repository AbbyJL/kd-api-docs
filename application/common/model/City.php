<?php
namespace app\common\model;
use think\Db;
use think\Model;

class City extends Base{
    protected $pk = 'city_id';
    protected $tableName = 'city';
    protected $token = 'city';
    protected $orderby = array('orderby' => 'asc');

    public function setToken($token){
        $this->token = $token;
    }

    public function isOpen($city_id){
        if($rest = Db::name('city')->update(array('city_id' => $city_id,'is_open' => 1))){
            return true;
        }else{
            $this->error = '审核失败';
            return false;
        }
    }


    public function getAreaNum($city_id){
        $Area = (int)Db::name('area')->where(array('city_id' =>$city_id))->count();
        return $Area;
    }



    //云洋同城配送接口
    public function cityServiceList($data){

        $config = model('Setting')->fetchAll2();
        $model = (int)$config['delivery']['model'];


        if($data['recipients_phone']!='undefined' && $data['recipients_phone']){
            $recipients_mobile = $data['recipients_phone'];
        }elseif($data['recipients_mobile']!='undefined' && $data['recipients_mobile']){
            $recipients_mobile = $data['recipients_mobile'];
        }else{
            $recipients_mobile = '17194348715';
        }
        if($data['sender_phone']!='undefined' && $data['sender_phone']){
            $sender_mobile = $data['sender_phone'];
        }elseif($data['sender_mobile']!='undefined' && $data['sender_mobile']){
            $sender_mobile = $data['sender_mobile'];
        }else{
            $sender_mobile = '17194348715';
        }

        $cate_id = (int)$data['cate_id'];
        $expressList = array();
        $tc_feilu = 10;



        if($config['delivery']['yy_open']==1 && $model==0){

            $content['sender']=$data['sender_name'];
            $content['senderMobile']= $sender_mobile;
            if($data['sender_getAddr'] && $data['sender_getAddr']!='undefined' && $data['sender_getAddr']!='null'){
                $content['senderAddress']= $data['sender_address'].$data['sender_getAddr'];
            }else{
                $content['senderAddress']= $data['sender_address'];
            }
            $content['receiver']=$data['recipients_name'];
            $content['receiverMobile']=$recipients_mobile;



            if($data['recipients_addr'] && $data['recipients_addr']!='undefined' && $data['recipients_addr']!='null'){
                $content['receiveAddress']=$data['recipients_addr'].$data['recipients_getAddr'];
                $content['receiveLocation']= $data['recipients_address'].$data['recipients_getAddr'];
            }else{
                $content['receiveAddress']=$data['recipients_address'];
                $content['receiveLocation']= $data['recipients_address'];
            }
            $content['billRemark']=$data['remark'];
            $content['weight']= (int)$data['totalWeight'];
            $content['senderLat']= $data['sender_lat'];
            $content['senderLng']= $data['sender_lng'];
            $content['receiveLat']=$data['recipients_lat'];
            $content['receiveLgt']=$data['recipients_lng'];

            $performance = model('City')->performance($content,$Method ='QUERY_DELIVER_FEE');


            if($performance['code'] == 0){
                $this->error = '同城下单错误'.$performance['message'];
                return false;
            }else{
                foreach($performance['result'] as $k=>$v){
                    $expressList[$k]['distance'] = $v['distance'];//保价费
                    $expressList[$k]['freightInsured'] = 0;//保价费
                    $expressList[$k]['c_type'] =11;
                    $expressList[$k]['lanshou'] ='';
                    $expressList[$k]['info'] ='';
                    $expressList[$k]['orderby'] =0;
                    $expressList[$k]['img'] =config_weixin_img($v['third_logistics_icon']);
                    $expressList[$k]['nickname'] = cut_msubstr($v['third_logistics_name'],0,8,true);
                    $expressList[$k]['name'] = $v['third_logistics_name'];
                    $expressList[$k]['freight'] = $v['fee'];
                    $expressList[$k]['channelId'] = $v['third_logistics_id'];
                    $expressList[$k]['channel'] = $v['third_logistics_id'];
                    $expressList[$k]['transportType'] = $v['third_logistics_id'];
                    $expressList[$k]['type'] = 11;
                    $expressList[$k]['tag']= '';
                    $tongchengPrice = model('City')->tongchengPrice($data['uid'],$v['fee']*100,$data,$coupon_pmt=0);//同城价格获取
                    $expressList[$k]['discount'] =$tongchengPrice['discount'];//普通用户运费
                    $expressList[$k]['vip_discount'] = $tongchengPrice['vip_discount'];//VIP价格
                    $expressList[$k]['original_cost'] = $tongchengPrice['original_cost'];//原价
                    $expressList[$k]['sumMoneyYuan'] = (int)$tongchengPrice['sumMoneyYuan'];
                    $expressList[$k]['getCatePrice'] = $tongchengPrice;
                    $expressList[$k]['is_baojia'] = 1;
                    $expressList[$k]['is_yuyue'] = 1;
                }
            }
        }
        return @array_values($expressList);
    }



    //云洋执行接口
    public function performance($content,$Method ='CHECK_CHANNEL'){
        $config = model('Setting')->fetchAll2();
        $appid = trim($config['wxapp']['yy_appid']);
        $requestId = rand_string(32,3);
        list($t1,$t2) = explode(' ',microtime());
        $timeStamp = (int)((floatval($t1)+floatval($t2))*1000);
        $timeStamp = (string) $timeStamp;
        $secretKey = trim($config['wxapp']['yy_secretKey']);
        $url = 'https://api.yunyangwl.com/api/wuliu/cityService';
        $body = array(
            "serviceCode" =>$Method,
            "timeStamp" => $timeStamp,
            "requestId"=> $requestId,
            "appid" => $appid,
            "sign"=> model('Setting')->getSign($appid,$requestId,$timeStamp,$secretKey),
            "content"=> $content,
        );
        $header = array("Content-Type:application/json");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body,320));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }



    public function tongchengPrice($uid,$totalfee,$getdata,$coupon_pmt=0){
        $config = model('Setting')->fetchAll2();
        //重量
        $totalWeight = $getdata['totalWeight'] ? $getdata['totalWeight'] : $totalWeight;
        $yy_tc_feilu = (int)$config['delivery']['yy_tc_feilu'];

        $getZhe = model('Setting')->getZhe($uid,'');
        $zhe = $getZhe['zhe'];
        $zhe2= $getZhe['zhe2'];

        $totalfee = $totalfee;
        $data['firstPrice'] = $totalfee;//快递公司首重价格
        $data['addPrice'] = 0;//快递公司续重价格
        $data['addPrice_jia'] = 0;//后台加价续重价格


        $preOrderFee = $totalfee;//预支付金额
        $data['preOrderFee'] = $preOrderFee;//预支付金额
        $data['originalFee'] = $preOrderFee*2;//官网原价(仅供参考)
        $data['preBjFee'] =  0;//保价金额
        $data['originalFee'] = $data['originalFee'];


        //原价的加价
        $firstPrice = (int)(($totalfee*$yy_tc_feilu)/100);//首重+价
        $preOrderFee = $totalfee+$firstPrice;
        $data['firstPrice_jia'] = $firstPrice;//后台加价首重价格
        $data['addPrice'] = 0;//续重原始价格
        $data['addPrice_jia'] =0;//续重加价


        $vipFeeYuan = (($preOrderFee+$data['addPrice'])*$zhe)/10;//用户自己的折扣
        $vipFeeYuan2 = (($preOrderFee+$data['addPrice'])*$zhe2)/10;//VIP等级折扣

        if(($vipFeeYuan-$coupon_pmt) > 0){
            $vipFeeYuan = $vipFeeYuan-$coupon_pmt;
        }

        //获取最低折扣
        $vip_discount = $vipFeeYuan<$vipFeeYuan2?$vipFeeYuan:$vipFeeYuan2;

        $data['coupon_pmt'] = $coupon_pmt;//优惠金额
        $data['sumMoneyYuan'] = $vipFeeYuan;//支付金额
        $data['sumMoneyYuan_old'] = $data['preOrderFee'];//原始金额
        $data['sumMoneyYuan_jia'] = $vipFeeYuan-$data['preOrderFee'];//目前加价
        $data['discount'] = round($vipFeeYuan/100,2);//普通用户运费
        $data['vip_discount'] = round(($vip_discount)/100,2);//VIP价格运费
        $data['original_cost'] = round(($data['originalFee'])/100,0);//原价
        $data['priceA2'] = 0;
        $data['priceB2'] = 0;

        return $data;
    }





}