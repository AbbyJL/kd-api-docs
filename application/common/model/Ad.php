<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class Ad extends Base{
	
    protected $pk   = 'ad_id';
    protected $tableName =  'ad';
	
	public function click_number($ad_id){
		if(false!== Db::name('ad')->where(array('ad_id'=>$ad_id))->setInc('click',1)){
            return true;
        }else{
           return false;
        }
	}
	public function get_ad_list($city_ids,$site_id){
		$ad = Db::name('ad')->where(array('closed'=>0,'site_id'=>$site_id,'city_id'=>array('IN', $city_ids),'bg_date' => array('ELT', TODAY),'end_date' => array('EGT', TODAY)))->limit(0,3)->select();
		if(!$ad){
			$ad = Db::name('ad')->where(array('closed'=>0,'site_id'=>$site_id,'bg_date' => array('ELT', TODAY),'end_date' => array('EGT', TODAY)))->limit(0,3)->select();
		}
		return $ad;
	}
	
	public function dingdingTalkWebhook($dd_msg=1,$info='',$mobile=''){
		$config = model('Setting')->fetchAll2();
		$url = $config['config']['webhook']?$config['config']['webhook']:'https://oapi.dingtalk.com/robot/send?access_token=9cafa02cc44622f1b05b833c8b611f7914cffe0d77b15ec44de551d012031685';;
		$secret= $config['config']['webhooksecret'] ? $config['config']['webhooksecret'] : 'SECb847025e133cb0e2b609317f4ff861b275d38a6b0938fb0ce9e5b6440802c34e';
		$atMobiles = $mobile ? $mobile : $config['config']['webhookmobile'] ? $config['config']['webhookmobile'] : '17194348715';
		$webHookContent = $this->webHookContent($dd_msg,$info,$mobile);
		$content = $webHookContent['content'];
		$flag = $webHookContent['flag'];
		if($content && $flag){
			$textString = json_encode(array(
				'msgtype' => 'text',
				'text' => array("content" => "{$content}"),
				'at' => array(
					'atMobiles' =>  array("{$atMobiles}"),
					'isAtAll' => false
				)
			)); 
			$result = $this->dingTalkWebhookRequestCurl($url,$secret,$textString);
			$result = json_decode($result,true);
			//p($textString);
			return '请求成功'.$result['errcode'].$result['errmsg'];	
		}
		return true;
	}
	
	
	public function qqTalkWebhook($dd_msg=1,$info='',$mobile=''){
		$config = model('Setting')->fetchAll2();
		$url = $config['config']['qqwebhook']?$config['config']['qqwebhook']:'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=71cf9828-e21d-4f32-96dc-c354f5a1c254';
		$atMobiles = $config['config']['webhookmobile'] ? $config['config']['webhookmobile'] : '17194348715';
		$webHookContent = $this->webHookContent($dd_msg,$info,$mobile);
		$content = $webHookContent['content'];
		$flag = $webHookContent['flag'];
		if($content){
			$data = array(
				"msgtype"=>"text",
				"text"=>array(
					"content"=>$content,
				)
			);
			$result =$this->qqWebhookrequestPost($url,json_encode($data,'320'),'json');
			$result = json_decode($result,true);
			return '请求成功'.$result['errcode'].$result['errmsg'];	
		}
		return true;
	}
	
	
	public function webHookContent($dd_msg=1,$info='',$mobile=''){
		$config = model('Setting')->fetchAll2();
		$flag = 0;
		if($dd_msg=1 && $config['config']['dd_msg_1']){
			$flag = 1;
			$typeName = '用户注册';
		}
		if($dd_msg=2 && $config['config']['dd_msg_2']){
			$flag = 1;
			$typeName = '积分兑换';
		}
		if($dd_msg=3 && $config['config']['dd_msg_3']){
			$flag = 1;
			$typeName = '用户充值';
		}
		if($dd_msg=4 && $config['config']['dd_msg_4']){
			$flag = 1;
			$typeName = '购买VIP';
		}
		if($dd_msg=5 && $config['config']['dd_msg_5']){
			$flag = 1;
			$typeName = '订单接单';
		}
		if($dd_msg=1 && $config['config']['dd_msg_6']){
			$flag = 6;
			$typeName = '订单取消';
		}
		if($dd_msg=7 && $config['config']['dd_msg_7']){
			$flag = 1;
			$typeName = '订单差价';
		}
		if($dd_msg=8 && $config['config']['dd_msg_8']){
			$flag = 1;
			$typeName = '订单完成';
		}
		if($dd_msg=9 && $config['config']['dd_msg_9']){
			$flag = 1;
			$typeName = '订单未付款';
		}
		$content = $config['site']['sitename'].''.$typeName.''.$info;
		$d['flag'] = $flag;
		$d['ypeName'] = $typeName;
		$d['content'] = $content;
		return $d;
	}
	//发送钉钉企业微信群消息
	public function dingTalkWebhook($dd_msg=1,$info='',$mobile=''){
		$config = model('Setting')->fetchAll2();
		$type = (int)$config['config']['webhook_type'];
		if($type == 0){
			$this->dingdingTalkWebhook($dd_msg,$info,$mobile);
		}elseif($type == 1){
			$this->dingdingTalkWebhook($dd_msg,$info,$mobile);
		}elseif($type == 2){
			$this->qqTalkWebhook($dd_msg,$info,$mobile);
		}elseif($type == 3){
			$this->dingdingTalkWebhook($dd_msg,$info,$mobile);
			$this->qqTalkWebhook($dd_msg,$info,$mobile);
		}
		return true;
	}
	
	
	
	public function qqWebhookrequestPost($url = '', $post_data = array(),$dataType=''){
		if(empty($url) || empty($post_data)){
			return false;
		}
		$curlPost = $post_data;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if($dataType=='json'){
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
					'Content-Length: ' . strlen($curlPost)
				)
			);
		}
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$data = curl_exec($ch);
		return $data;
	}
	
	public function dingTalkWebhookRequestCurl($url,$secret,$textString){
    	$time = time() *1000;
		$sign = hash_hmac('sha256', $time . "\n" . $secret,$secret,true);
		$sign = base64_encode($sign);
		$sign = urlencode($sign);
		$url = "{$url}&timestamp={$time}&sign={$sign}";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS,$textString);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$res = curl_exec($curl);
		curl_close($curl);
		return $res;
	}

    public function mapShipperCode($kuaidi=0){
        if(strstr($kuaidi,'京东') == true){
            $title = 'JD';
        }elseif(strstr($kuaidi,'圆通') == true){
            $title = 'YTO';
        }elseif(strstr($kuaidi,'申通') == true){
            $title = 'STO';
        }elseif(strstr($kuaidi,'德邦') == true){
            $title = 'DBL';
        }elseif(strstr($kuaidi,'极兔') == true){
            $title = 'JTSD';
        }elseif(strstr($kuaidi,'顺丰') == true){
            $title = 'SF';
        }elseif(strstr($kuaidi,'中通') == true){
            $title = 'ZTO';
        }elseif(strstr($kuaidi,'韵达') == true){
            $title = 'YD';
        }elseif(strstr($kuaidi,'ems') == true){
            $title = 'EMS';
        }elseif(strstr($kuaidi,'跨越') == true){
            $title = 'KYSY';
        }
        return $title;
    }


    public function mapGuiji($id=0){
        $v = Db::name('express_order')->where(array('id'=>$id))->find();
        $r = Db::name('user_addr')->where(array('addr_id'=>$v['rmail_id']))->find();
        if(!$r){
            $r = Db::name('user_addr2')->where(array('addr_id'=>$v['rmail_id']))->find();
        }
        $sendMobile = substr($v['sendMobile'],-4);
        $requestParams['ShipperCode'] = $this->mapShipperCode($v['kuaidi']);
        $requestParams['LogisticCode'] = $v['deliveryId'];

        if($requestParams['ShipperCode']=='SF'){
            $requestParams['CustomerName'] = $sendMobile;
        }
        $requestParams['IsReturnRouteMap'] = 1;
        $Receiver['ProvinceName'] = $r['province'];
        $Receiver['CityName'] = $r['city'];
        $Receiver['ExpAreaName'] = $r['area'];
        $Receiver['Address'] =  $r['address'];
        $requestParams['Receiver'] = $Receiver;

        $Sender['ProvinceName'] =  $v['senderProvince'];
        $Sender['CityName'] =  $v['senderCity'];
        $Sender['ExpAreaName'] =  $v['senderCounty'];
        $Sender['Address'] =  $v['sendAddress'];
        $requestParams['Sender'] = $Sender;
        $kdnSendPost= $this->mapKdnSendPost($requestParams,$RequestType='8003');
        $kdnSendPost['Sender'] =explode(',',$kdnSendPost['SenderCityLatAndLng']);
        $kdnSendPost['Receiver'] =explode(',',$kdnSendPost['ReceiverCityLatAndLng']);
        return $kdnSendPost;
    }



    public function mapKdnSendPost($requestParams,$RequestType,$url=''){
        $config = model('Setting')->fetchAll2();
        $kdn_EBusinessID= trim($config['config']['kdn_EBusinessID']);
        $kdn_ApiKey = trim($config['config']['kdn_ApiKey']);
        $url = 'https://api.kdniao.com/api/dist';
        $requestParams = json_encode($requestParams,320);
        $datas = array(
            'EBusinessID' => $kdn_EBusinessID,
            'RequestType' => $RequestType,
            'RequestData' => urlencode($requestParams),
            'DataType' => '2',
        );
        $datas['DataSign'] = urlencode(base64_encode(md5($requestParams.$kdn_ApiKey)));
        $postdata = http_build_query($datas);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15*60
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result,true);
    }

    public function cityDelivery0rderDayinDatas($cdo,$community,$v,$type=1){
        $config = model('Setting')->fetchAll2();
        $community = Db::name('business_community')->where(array('community_id'=>$cdo['community_id']))->find();

        $msg = '';
        $msg .= $config['site']['sitename'].'__________NO:' .$cdo['id'].'<BR>';
        if($v['kuaidi']){
            $msg .= '订单号：' .$v['kuaidi'] .'<BR>';
        }
        if($v['deliveryId']){
            $msg .= '订单号：' .$v['deliveryId'] .'<BR>';
        }

        $msg .= '____________寄件信息____________' .'<BR>';
        $msg .= ''.$v['sendName'] .'-' .$v['sendMobile'] .'-' .$v['sendAddress'].'<BR>';

        $msg .= '____________收件信息____________' .'<BR>';
        $msg .= ''.$v['receiveName'] .'-' .$v['receiveMobile'] .'-' .$v['receiveAddress'].'<BR>';


        $msg .= '取件员姓名：' .$cdo['name'] .'<BR>';
        $msg .= '取件员电话：' .$cdo['mobile'].'<BR>';
        $msg .= '订单金额：' . $cdo['need_pay']/100 .'<BR>';
        $msg .= '下单时间：' .date('Y-m-d H:i:s',$cdo['create_time']).'<BR>';
        $msg .= '备注：' .$cdo['info'].'<BR>';
        return $msg;
    }

    public function cityDelivery0rderDayinData($cdo,$community,$v,$type=1){
        $config = model('Setting')->fetchAll2();
        $city = Db::name('copy_city')->where(array('city_id'=>$cdo['city_id']))->find();
        $area= Db::name('copy_area')->where(array('area_id'=>$cdo['area_id']))->find();
        $business = Db::name('copy_business')->where(array('business_id'=>$cdo['business_id']))->find();
        $community = Db::name('business_community')->where(array('community_id'=>$cdo['community_id']))->find();



       $msg = "<DIRECTION>1</DIRECTION>";
       $msg  .= "<img x='70' y='0'>";
       $msg .= "<TEXT x='12' y='220' font='12' w='1' h='1' r='0'>区域：".$city['name'].'-'.$area['area_name'].'-'.$business['business_name'].'-'.$community ['name']."</TEXT>";
       $msg .= "<TEXT x='12' y='250' font='12' w='1' h='1' r='0'>快递：".$v['kuaidi'] ."</TEXT>";
       $msg .= "<TEXT x='12' y='280' font='12' w='1' h='1' r='0'>运单号：" .$v['deliveryId'] ."</TEXT>";
       $msg .= "<TEXT x='12' y='310' font='12' w='1' h='1' r='0'>——寄件信息——</TEXT>";
       $msg .= "<TEXT x='12' y='340' font='12' w='1' h='1' r='0'>".$v['sendName'] .'-' .$v['sendMobile'] .'-' .$v['sendAddress']."</TEXT>";
       $msg .= "<TEXT x='12' y='370' font='12' w='1' h='1' r='0'>——收件信息——</TEXT>";
       $msg .= "<TEXT x='12' y='400' font='12' w='1' h='1' r='0'>".$v['receiveName'] .'-' .$v['receiveMobile'] .'-' .$v['receiveAddress']."</TEXT>";
       $msg .= "<TEXT x='12' y='430' font='12' w='1' h='1' r='0'>取件员姓名：" .$cdo['name'] ."</TEXT>";
       $msg .= "<TEXT x='12' y='460' font='12' w='1' h='1' r='0'>取件员电话：" .$cdo['mobile'] ."</TEXT>";
       $msg .= "<TEXT x='12' y='490' font='12' w='1' h='1' r='0'>订单金额：" .$cdo['need_pay']/100 ."</TEXT>";
       $msg .= "<TEXT x='12' y='520' font='12' w='1' h='1' r='0'>下单时间：" .date('Y-m-d H:i:s',$cdo['create_time']) ."</TEXT>";
       $msg .= "<TEXT x='12' y='550' font='12' w='1' h='1' r='0'>备注：" .$cdo['info']."</TEXT>";
       return  $msg;
    }




    public function wpPrint($orderInfo=array(),$community=array(),$cdo=array()){

        define('IP','api.feieyun.cn');
        define('PORT',80);
        define('PATH','/Api/Open/');

        $printer_sn = $community['machine_code'];
        $time = time();
        $queryPrinterStatus = array(
            'user'=>$community['partner'],
            'stime'=>$time,
            'sig'=>$this->signature($time,$community['partner'],$community['mKey']),
            'apiname'=>'Open_queryPrinterStatus',
            'sn'=>$printer_sn,
        );
        $client2 = new \HttpClient(IP,PORT);
        if(!$client2->post(PATH,$queryPrinterStatus)){
            return false;
        }else{
            $getContent2 =  $client2->getContent();
            $getContent3 = json_decode($getContent2,true);
            if($getContent3['ret'] !=0){
                $this->error = '打印机离线或者坏了请排查【'.$getContent3['ret'].'】【'.$getContent3['data'].'】';
                return false;
            }
        }
        $img = 'http://demo8.jintaocms.com/attachs/dayin.jpg';
        $content = array(
            'user'=>$community['partner'],
            'stime'=>$time,
            'sig'=>$this->signature($time,$community['partner'],$community['mKey']),
            'apiname'=>'Open_printLabelMsg',
            'sn'=>$printer_sn,
            'content'=>$orderInfo,
            'times'=>1
        );
        $client = new \HttpClient(IP,PORT);
        if(!$client->post(PATH,$content)){
            return false;
        }else{
            $getContent =  $client->getContent();
            $getContent = json_decode($getContent,true);
            if($getContent['msg'] == 'ok'){
                return true;
            }
            $this->error = '飞蛾打印机-'.$getContent['msg'];
            return false;
        }
    }

    public function signature($time,$partner,$mKey){
        return sha1($partner.$mKey.$time);
    }

    function printLabelMsg($orderInfo=array(),$community=array(),$cdo=array()){
        $printer_sn = $community['machine_code'];
        $url = 'http://api.feieyun.cn/Api/Open/';

        $page="pagesB/pages/delivery/deliveryorderdetai";
        $width = '180';
        $res = model('Api')->qrcodeWxapp($cdo['id'],$page,$width,$parameter='deliveryorderdetai',$cdo['id'],2);
        $img = config_weixin_img($res);

        $img = 'http://demo8.jintaocms.com/attachs/dayin.jpg';
        $printer_sn = $community['machine_code'];
        $time = time();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'user' => $community['partner'],
            'stime' =>  $time,
            'sig'=>$this->signature($time,$community['partner'],$community['mKey']),
            'apiname' => 'Open_printLabelMsg',
            'sn' => $printer_sn,
            'content' => $orderInfo,
            'times' => 1,
            'img' => new \CURLFile($img)
        ]);
        $headers = array('Content-Type: multipart/form-data;');
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            echo "cURL error: $error";
        } else {
            // 处理成功的响应
            echo  $response;
        }

        p($url);
        p([
            'user' => $community['partner'],
            'stime' =>  $time,
            'sig'=>$this->signature($time,$community['partner'],$community['mKey']),
            'apiname' => 'Open_printLabelMsg',
            'sn' => $printer_sn,
            'content' => $orderInfo,
            'times' => 1,
            'img' => new \CURLFile($img)

        ]);
        curl_close($ch);
        p($response );
        var_dump( $response );
    }


}