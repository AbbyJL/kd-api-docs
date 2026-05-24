<?php

namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

use EasyWeChat\Foundation\Application;
use app\common\model\Setting;

class Api extends Base{
	
	
	 public function qrcodeWxapp($id,$page,$width,$parameter='postId',$scene = '',$t = 0,$pint='wx'){	 
	    $config = Setting::config();
		$patch = ROOT_PATH.'attachs/poster/'.$parameter.'_'.$id.'.png';
		$patch2 = '/attachs/poster/'.$parameter.'_'.$id.'.png';
		
		if($pint == 'mp-toutiao'){
			$appid =trim($config['toutiao']['AppID']);
			$secret= trim($config['toutiao']['Secret']);
			$url = 'https://developer.toutiao.com/api/apps/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
			$data=$this->https_request($url);
			$data = json_decode($data,true);
		}else{
			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$config['wxapp']['appid'].'&secret='.$config['wxapp']['appsecret'];
			$data=$this->getCurlWxapp($url);
			$data = json_decode($data,true);
		}
		$postdata['scene']=$scene;
		$postdata['width']=$width;
		$postdata['page']=$page;
		$postdata['auto_color']=false;
		$postdata['line_color']=array('r'=>'0','g'=>'0','b'=>'0');
		$postdata['is_hyaline']=false;
		$post_data = json_encode($postdata);
		if($pint == 'mp-toutiao'){
			$url="https://developer.toutiao.com/api/apps/qrcode";
			$qrcode = array(
				'access_token' => $data['access_token'],
				'scene'=> $scene, 
				'appname'=> 'douyin',
				'width'=> $width,
				'path'=> urlencode("pages/index/index?promoteId=".$scene),
				'platform'=> 'miniapp',
				'set_icon'=> true
			);
			$result = $this->https_post($url,json_encode($qrcode));
		}else{
			$url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$data['access_token'];
			$result =$this->postCurlWxapp($url,$post_data);
		}
	    file_put_contents($patch,$result);
		$return = $config['site']['host'].$patch2;

        if($t =2) {
             return $return;
        }else {
            Db::name('users')->where(array('user_id' => $id))->update(array('qrcode' => $return));
            if (file_exists($patch)) {
                if ($t = 1) {
                    return $patch2;
                } else {
                    return $return;
                }
            } else {
                if ($t = 1) {
                    return config_weixin_img($config['site']['wxappcode']);
                } else {
                    return $config['site']['wxappcode'];
                }
            }
        }
	}
	

	#curl请求
    public function https_request($url, $data = null){
        if(function_exists('curl_init')){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        }else{
            return false;
        }
    }

	#post请求
    public function https_post($url, $data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno' . curl_errno($curl);
        }
        curl_close($curl);
        return $result;
    }

	
	//生成小程序海报get请求
	public function getCurlWxapp($url){
		$info=curl_init();
		curl_setopt($info,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($info,CURLOPT_HEADER,0);
		curl_setopt($info,CURLOPT_NOBODY,0);
		curl_setopt($info,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($info,CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($info,CURLOPT_URL,$url);
		$output= curl_exec($info);
		curl_close($info);
		return $output;
	}
	
	
	//生成小程序海报post请求
	public function postCurlWxapp($url,$data){
		$ch = curl_init();
		$header[] = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$tmpInfo = curl_exec($ch);
		if(curl_errno($ch)){
			return false;
		}else{
			return $tmpInfo;
		}
	}
	
	
	
	
	//会员二维码改版
	public function bindcode($fuid){
		$token = 'fuid_' . $fuid;
		$url = url('Wap/passport/register',array('fuid'=>$fuid,'laiyuan'=>'bindcode'));
		$file = ToQrCode($token,$url,8,'');
		return $file;
	}

	
	
    //生成带参数二维码自动关注【这里后台可控制】
	
    public function getQrcode($fuid){
		$config = Setting::config();
		
		//是否生成带关注的二维码
		$subscribe_qrcode = (int)$config['profit']['subscribe_qrcode'];
		$openId = Db::name('connect')->where(array('type'=>'weixin','uid'=>$fuid))->value('open_id');
		
		if($openId && $subscribe_qrcode){
			$qrcode = $this->getWeixinCodePng($fuid);//获取微信二维码
			if(!file_exists(ROOT_PATH .$qrcode)){
				$qrcode = $this->bindcode($fuid);//获取二维码;
			}else{
				$qrcode = $qrcode;
			}
		}else{
			$qrcode = $this->bindcode($fuid);//获取二维码
		}
		
		$users = Db::name('users')->where(array('user_id'=>$fuid))->find();
		$bgqrcode = config_weixin_img($config['profit']['qrcode']);
	
		$codedata = array( 
			 'thumb' =>array('thumb'=>config_weixin_img($bgqrcode),'left' =>0,'top' =>0,'width' =>350,'height'=>650),  //背景图
			 'qrcode' =>array('thumb'=>config_weixin_img($qrcode), 'left'=> 65,'top' => 185,'width' => 210,'height' =>210), //二维码
			 'desc' =>array('text'=>'长按二维码关注注册','left' =>85,'top' =>420,'size' =>14,'color'=>'#FFF') //长按二维码扫码购买
		 );
		
		//商品ID，二维码，描述，用户id，分享二维码
		$parameter = array('id'=>$shop_id,'qrcode' =>config_weixin_img($file),'codedata'=>$codedata,'mid'=>$fuid,'codeshare'=>3,'config'=>$config['profit']);
		$goodscode = model('Activity')->createcode($parameter);
		
		return $goodscode;
    }
	
	
		
	//获取带参数的微信二维码
	//如果有问题再处理
	public function getWeixinCodePng($fuid){
		$options = model('weixinConfig')->weixinconfig();
		$app = new Application($options);
		$qrcode = $app->qrcode;
		$result = $qrcode->forever('881'.$fuid);//第一位881是会员，882是商家，883是外卖，884是以此类推
		$ticket = $result->ticket; 
		$url = $qrcode->url($ticket);
		$content = file_get_contents($url); 
		$file = '/attachs/qrcode/fuid_'.$fuid.'_code.jpg';
		$fileName  = ROOT_PATH.''.$file;
		file_put_contents($fileName, $content); 
		return $file;
	 }
	 	
		
		
		 
	//获取头像
	public function getWeixinUserFacePng($fuid){
		 $token = 'face_' . $fuid;
		 $name = date('Y/m/d/',time());
		 $md6 = md5($token);
		 $patch = ROOT_PATH.'/attachs/'. 'poster/'.$name;
		 $catalog = '/attachs/'. 'poster/'.$name;
		 
		 $Connect = Db::name('connect')->where(array('uid'=>$fuid,'type'=>'weixin'))->find();
		 $Users = Db::name('users')->where(array('user_id'=>$fuid))->find();
		 
		 if($Connect['headimgurl']){
			$arr = $this->getImage($Connect['headimgurl'],$patch,$md6,$catalog,1);
			$res = $this->imagepress($arr['save_path'], 140, 140);
			return $arr;
		 }elseif($Users['face']){
			$arr = $this->getImage(config_weixin_img($Users['face']),$patch,$md6,$catalog,1);
			$res = $this->imagepress($arr['save_path'], 140, 140);
			return $arr;
		 }else{
			return false;
				
		 }
		 return false;
	 }
	 
	 
	 
	 
	 
	//生成带参数二维码1 
    public  function getPoster($fuid,$type){
		$config = Setting::config();
		$Users = Db::name('users')->where(array('user_id'=>$fuid))->find();
		if($Users['poster']){
			return $Users['poster'];
		}else{
			
			
			if($config['weixin']['user_add']){
				$qrcode = $this->getWeixinCodePng($fuid);//获取微信二维码
				
				$face = $this->getWeixinUserFacePng($fuid);//获取微信二维码
				
				if($config['profit']['photo']){
					$poster_path = config_weixin_img($config['profit']['photo']);//后台配置的
				}else{
					$poster_path = $config['site']['host'].'/attachs/poster.png';
				}
				
			
				
				if(empty($face['catalog'])){
					$catalog = $config['site']['host'].'/attachs/avatar.jpg';
				}else{
					$catalog = $config['site']['host'].''.$face['catalog'];
				}
				
				$imgs = array(
					'poster' => $poster_path,
					'qrcode' => $config['site']['host'].''.$qrcode,
					'logo' => config_weixin_img($config['site']['logo']),
					'face' =>$catalog,
					'userName' =>config_user_name($Users['nickname']),
				);
				$poster = $this->mergerImg($imgs,$face['save_path'],$face['catalog']);
				if($poster){
					Db::name('users')->where(array('user_id'=>$fuid))->update(array('poster'=>$poster));
				}
				return $poster;
			}else{
				$token = 'fuid_' . $fuid;
				$url = url('Wap/passport/register', array('fuid' => $fuid));
				$file = ToQrCode($token,$url,8,'');
			}
		}
		return false;
    }
	
	
	//生成带参数二维码微擎综合法 
    public  function getPoster2($fuid,$type){
		$config = Setting::config();
	    $Users = Db::name('users')->where(array('user_id'=>$fuid))->find();
		if($Users['poster']){
			return $Users['poster'];
		}else{
			$qrcode = $this->getWeixinCodePng($fuid);//获取微信二维码
			$face = $this->getWeixinUserFacePng($fuid);//获取微信头像
			$face = $face['catalog'] ? $face['catalog'] : $Users['face'];
			$poster_path = $config['site']['host'].'/attachs/poster.png';
			$codedata = array( 
				 "portrait" => array( "thumb" => config_weixin_img($face), "left" => 40, "top" => 40, "width" => 100, "height" => 100 ), 
				 "shopname" => array( "text" => cut_msubstr($Users['nickname'],0,14, false), "left" => 160, "top" => 80, "size" => 28, "width" => 360, "height" => 50, "color" => "#333" ), 
				 "thumb" => array( "thumb" => $poster_path, "left" => 40, "top" => 160, "width" => 560, "height" => 360 ), 
				 "qrcode" => array( "thumb" => config_weixin_img($qrcode), "left" => 23, "top" => 730, "width" => 220, "height" => 220 ), 
				 "title" => array( "text" => $config['site']['sitename'], "left" => 230, "top" => 770, "size" => 24, "width" => 360, "height" => 50, "color" => "#333" ), 
				 "price" => array( "text" => $Users['nickname'], "left" => 270, "top" => 880, "size" => 30, "color" => "#f20" ), 
				 "desc" => array( "text" => '点击图片可下载到手机', "left" => 210, "top" => 980, "size" => 18, "color" => "#666" ) 
			 );
			$parameter = array( "goods_id" => 1, "qrcode" => config_weixin_img($qrcode), "codedata" => $codedata, "mid" => $fuid, "codeshare" =>1);
			$goodscode = model('Api')->createcode($parameter);
			return $goodscode;
		}
		return false;
    }
	
//添加参数
    public  function getWxappPoster($uid,$page,$width,$type = 1,$pint=''){
		$config = Setting::config();
		
		
		$u = Db::name('users')->where(array('user_id'=>$uid))->find(); 
		
		if($type == 1 && empty($u['qrcode1'])){
			if(!$u['qrcode']){
				$qrcode = model('Api')->qrcodeWxapp($uid,$page,$width,$parameter='userId',$uid,$t=1,$pint);
			}else{
				$qrcode = $u['qrcode'];
			}
			$poster_path = config_weixin_img($config['profit']['qrcode1']);
			$codedata = array( 
				 "thumb" => array( "thumb" => $poster_path, "left" => 0, "top" => 0, "width" => 750, "height" => 1200 ), 
				 "qrcode" => array( "thumb" => config_weixin_img($qrcode), "left" => 268, "top" => 897, "width" => 215, "height" => 215 ), 
			 );
			$parameter = array( "qrcode" => config_weixin_img($qrcode), "codedata" => $codedata, "codeshare" =>1);
			$r = model('Api')->createWxappPoster($parameter);
			Db::name('users')->where(array('user_id'=>$uid))->update(array('qrcode1'=>config_weixin_img($r)));
		}elseif($type == 1 && !empty($u['qrcode1'])){
			$r = $u['qrcode1'];
		}
		if($type == 2 && empty($u['qrcode2'])){
			if(!$u['qrcode']){
				$qrcode = model('Api')->qrcodeWxapp($uid,$page,$width,$parameter='userId',$uid,$t=1,$pint);
			}else{
				$qrcode = $u['qrcode'];
			}
			$poster_path = config_weixin_img($config['profit']['qrcode2']);
			$codedata = array( 
				 "thumb" => array( "thumb" => $poster_path, "left" => 0, "top" => 0, "width" => 750, "height" => 1200 ), 
				 "qrcode" => array( "thumb" => config_weixin_img($qrcode), "left" => 268, "top" => 897, "width" => 215, "height" => 215 ), 
			 );
			$parameter = array( "qrcode" => config_weixin_img($qrcode), "codedata" => $codedata, "codeshare" =>1);
			$r = model('Api')->createWxappPoster($parameter);
			Db::name('users')->where(array('user_id'=>$uid))->update(array('qrcode2'=>config_weixin_img($r)));
		}elseif($type == 2 && !empty($u['qrcode2'])){
			$r = $u['qrcode2'];
		}
		if($type == 3 && empty($u['qrcode3'])){
			if(!$u['qrcode']){
				$qrcode = model('Api')->qrcodeWxapp($uid,$page,$width,$parameter='userId',$uid,$t=1,$pint);
			}else{
				$qrcode = $u['qrcode'];
			}
			$poster_path = config_weixin_img($config['profit']['qrcode3']);
			$codedata = array( 
				 "thumb" => array( "thumb" => $poster_path, "left" => 0, "top" => 0, "width" => 750, "height" => 1200 ), 
				 "qrcode" => array( "thumb" => config_weixin_img($qrcode), "left" => 268, "top" => 897, "width" => 215, "height" => 215 ), 
			 );
			$parameter = array( "qrcode" => config_weixin_img($qrcode), "codedata" => $codedata, "codeshare" =>1);
			$r = model('Api')->createWxappPoster($parameter);
			Db::name('users')->where(array('user_id'=>$uid))->update(array('qrcode3'=>config_weixin_img($r)));
		}elseif($type == 3 && !empty($u['qrcode3'])){
			$r = $u['qrcode3'];
		}
		return config_weixin_img($r);
    }

	
	public function imagepress($filepath, $new_width, $new_height){  
		$source_info   = getimagesize($filepath);  
		$source_width  = $source_info[0];  
		$source_height = $source_info[1];  
		$source_mime   = $source_info['mime'];  
		$source_ratio  = $source_height / $source_width;  
		$target_ratio  = $new_height / $new_width;  
		if($source_ratio > $target_ratio){  
			$cropped_width  = $source_width;  
			$cropped_height = $source_width * $target_ratio;  
			$source_x = 0;  
			$source_y = ($source_height - $cropped_height) / 2;  
		}elseif($source_ratio < $target_ratio){
			$cropped_width  = $source_height / $target_ratio;  
			$cropped_height = $source_height;  
			$source_x = ($source_width - $cropped_width) / 2;  
			$source_y = 0;  
		}else{
			$cropped_width  = $source_width;  
			$cropped_height = $source_height;  
			$source_x = 0;  
			$source_y = 0;  
		}  
		switch($source_mime){  
			case 'image/gif':  
				$source_image = imagecreatefromgif($filepath);  
				break;  
			case 'image/jpeg':  
				$source_image = imagecreatefromjpeg($filepath);  
				break;  
			case 'image/png':  
				$source_image = imagecreatefrompng($filepath);  
			break;  
				default:  
				return false;  
			break;  
		}  
		$target_image  = imagecreatetruecolor($new_width, $new_height);  
		$cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);  
		imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);  
		imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $new_width, $new_height, $cropped_width, $cropped_height);  
		imagejpeg($target_image,$filepath);
		imagedestroy($source_image);  
		imagedestroy($target_image);  
		imagedestroy($cropped_image);  
		return true; 
	}  
	
	
	
	public function isPng($pngPath){  
		$size = getimagesize ($pngPath);   
		$file_extension = strtolower(substr(strrchr($pngPath,'.'),1));  
		if('image/png' != $size['mime'] || $file_extension != 'png'){   
			return FALSE;   
		}else{  
			return TRUE;  
		}  
	} 
  
	
	//合并图片
	public function mergerImg($imgs,$save_path,$catalog){
        list($max_poster_width, $max_poster_height) = getimagesize($imgs['poster']);
        $posters = imagecreatetruecolor($max_poster_width, $max_poster_height);
        $poster_im = imagecreatefrompng($imgs['poster']);
        imagecopy($posters,$poster_im,0,0,0,0,$max_poster_width,$max_poster_height);
        imagedestroy($poster_im);
		
        $qrcode_im = imagecreatefromjpeg($imgs['qrcode']);
        $qrcode_info = getimagesize($imgs['qrcode']);
        imagecopy($posters,$qrcode_im,($max_poster_width-$qrcode_info[0])/2,($max_poster_height/2)-100,0,0,$qrcode_info[0],$qrcode_info[1]);
        imagedestroy($qrcode_im);
		
		if($this->isPng($imgs['face'])){
			$face_im = imagecreatefrompng($imgs['face']);
		}else{
			$face_im = imagecreatefromjpeg($imgs['face']);
		}
		
        $face_info = getimagesize($imgs['face']);
        imagecopy($posters,$face_im,($max_poster_width-$face_info[0])/5,$max_poster_height -1010,0,0, $face_info[0],$face_info[1]);
        imagedestroy($face_im);
		$font = '/public/font/simsun.ttc';//字体路径
		$black = imagecolorallocate($posters, 255,255,255);//字体颜色
		imagefttext($posters, 28, 0,($max_poster_width-$face_info[0])/1.8, $max_poster_height -900, $black, $font, $imgs['userName']);
        imagejpeg($posters,$save_path);
		return $catalog; 
	}


	
	//远程下载微信头像
	public function getImage($url,$save_dir='',$filename='',$catalog,$type=0){  
		$ext=".png";//以jpg的格式结尾  
		clearstatcache();//清除文件缓存  
		if(trim($url)==''){  
			return array('file_name'=>'','save_path'=>'','error'=>1);  
		}  
		if(trim($save_dir)==''){  
			$save_dir='./';  
		}  
		if(trim($filename)==''){//保存文件名  
			$filename=time().$ext;  
		}else{  
			$filename = $filename.$ext;  
		}  
		if(0!==strrpos($save_dir,'/')){  
			$save_dir.='/';  
		}  
		//创建保存目录  
		if(!is_dir($save_dir)){
			mkdir(iconv("UTF-8", "GBK", $save_dir),0777,true);  
		}  
		//获取远程文件所采用的方法   
		if($type){  
			$ch=curl_init();  
			$timeout=3;  
			curl_setopt($ch,CURLOPT_URL,$url);  
			if(ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')){
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			}
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
			$img=curl_exec($ch);  
			curl_close($ch);  
		}else{  
			ob_start();   
			readfile($url);  
			$img=ob_get_contents();   
			ob_end_clean();   
		}  
		$size=strlen($img);  
		$fp2=@fopen($save_dir.$filename,'w');  
		fwrite($fp2,$img);  
		fclose($fp2);  
		unset($img,$url);  
		return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'catalog'=>$catalog.''.$filename,'error'=>0);  
	}  
	
 
	
	 //后台首页统计获取统计
     public function getDbHighcharts($bg_time,$end_time,$city_id,$id,$db,$pk){
		 
		if($db == 'users'){
			$tableName = 'jin_users';  
			$pk_id = 'user_id';
			$FROM_UNIXTIME = 'reg_time';
		}elseif($db == 'express_order'){
			 $tableName = 'jin_express_order';   
			 $pk_id = 'id';
			 $FROM_UNIXTIME = 'create_time';
		}elseif($db == 'payment_logs'){
			 $tableName = 'jin_payment_logs';   
			 $pk_id = 'log_id';
			 $FROM_UNIXTIME = 'create_time';
		}
		
        if($city_id && $id){
            $data = $this->query(" SELECT count(".$pk_id .") as num,FROM_UNIXTIME(".$FROM_UNIXTIME.",'%m%d') as day from  ".$tableName." where  ".$FROM_UNIXTIME." >= '{$bg_time}' AND ".$FROM_UNIXTIME." <= '{$end_time}' and city_id='{$city_id}' and ".$pk_id."='{$id}'  group by  FROM_UNIXTIME(".$FROM_UNIXTIME.",'%m%d')");
        }elseif($id){
            $data = $this->query(" SELECT count(".$pk_id .") as num,FROM_UNIXTIME(".$FROM_UNIXTIME.",'%m%d') as day from  ".$tableName." where  ".$FROM_UNIXTIME." >= '{$bg_time}' AND ".$FROM_UNIXTIME." <= '{$end_time}' and city_id='{$city_id}'  group by  FROM_UNIXTIME(".$FROM_UNIXTIME.",'%m%d')");
		}else{
            $data = $this->query(" SELECT count(".$pk_id .") as num,FROM_UNIXTIME(".$FROM_UNIXTIME.",'%m%d') as day from  ".$tableName." where  ".$FROM_UNIXTIME." >= '{$bg_time}' AND ".$FROM_UNIXTIME." <= '{$end_time}'  group by  FROM_UNIXTIME(".$FROM_UNIXTIME.",'%m%d')");
        }
		
		
        $showdata = array();
        $days = array();
        for($i = $bg_time; $i<=$end_time; $i += 86400){
            $days[date('md',$i)] = '\''.date('m/d',$i).'\''; 
        }
        $num = array();
        foreach($days  as $k=>$v){
            $num[$k] = 0;
            foreach($data as $val){
                if($val['day'] == $k){
                    $num[$k] = $val['num'];
                }
            }
        }
		
       $showdata['day'] = join(',',$days);
       $showdata['num'] = join(',',$num);
	  
       return $showdata;
    }   
	
	
	
	
	//生成小程序
	public function createWxappPoster($parameter){
	
		$name = date('Y/m/d/',time());
		$path = ROOT_PATH.'/attachs/'.'poster/'.$name;
		$paths = '/attachs/'.'poster/'.$name;
		
        $qrcode = $parameter["qrcode"];
        $data = $parameter["codedata"];
		
		if(!is_dir($path)){
			mkdir(iconv("UTF-8", "GBK", $path),0777,true);  
		} 
		
        $md5 = md5(json_encode(array("codedata" => $data)));
        $file = $md5.".jpg";
		
		
        if(!is_file($path . $file)){
            set_time_limit(0);
            @ini_set("memory_limit", "256M");
            
			$target = imagecreatetruecolor(750, 1200);
			$color = imagecolorAllocate($target, 245, 244, 249);
			imagefill($target, 0, 0, $color);
			imagecopy($target, $target, 0, 0, 0, 0, 750, 1200);
			$thumb = preg_replace("/\\/0\$/i", "/96", $data["thumb"]["thumb"]);
			$target = $this->mergeImage($target, $data["thumb"], $thumb);
			$qrcode = preg_replace("/\\/0\$/i", "/96", $data["qrcode"]["thumb"]);
			$target = $this->mergeImage($target, $data["qrcode"], $qrcode);
			imagepng($target, $path . $file);
			imagedestroy($target);
		}
        
        $img = $paths. $file;
        return $img;//返回
    }
	//生成分销海报
	public function createcode($parameter){
	
		$name = date('Y/m/d/',time());
		$path = ROOT_PATH.'/attachs/'.'poster/'.$name;
		$paths = '/attachs/'.'poster/'.$name;
		
        $goods_id = $parameter["goods_id"];//商品ID
        $qrcode = $parameter["qrcode"];
        $data = $parameter["codedata"];
        $mid = $parameter["mid"];
        $codeshare = $parameter["codeshare"];
		
		
		//创建保存目录  
		if(!is_dir($path)){
			//文件夹不存在，则新建  
			mkdir(iconv("UTF-8", "GBK", $path),0777,true);  
		} 
		
        $md5 = md5(json_encode(array("goods_id" => $goods_id, "title" => $data["title"]["text"],"price" =>$data["price"]["text"],"codeshare" => $parameter["codeshare"], "codedata" => $data, "mid" => $mid)));
        $file = $md5 . ".jpg";
		
		//p($parameter);die;
		
        if(!is_file($path . $file)){
            set_time_limit(0);
            @ini_set("memory_limit", "256M");
            if($codeshare == 1){
                $target = imagecreatetruecolor(640, 1060);
                $color = imagecolorAllocate($target, 255, 255, 255);
                imagefill($target, 0, 0, $color);
                imagecopy($target, $target, 0, 0, 0, 0, 640, 1060);
                $target = $this->mergeText($target, $data["shopname"], $data["shopname"]["text"]);
                $thumb = preg_replace("/\\/0\$/i", "/96", $data["portrait"]["thumb"]);
                $target = $this->mergeImage($target, $data["portrait"], $thumb);
                $thumb = preg_replace("/\\/0\$/i", "/96", $data["thumb"]["thumb"]);
                $target = $this->mergeImage($target, $data["thumb"], $thumb);
                $qrcode = preg_replace("/\\/0\$/i", "/96", $data["qrcode"]["thumb"]);
                $target = $this->mergeImage($target, $data["qrcode"], $qrcode);
                $target = $this->mergeText($target, $data["title"], $data["title"]["text"]);
                $target = $this->mergeText($target, $data["price"], $data["price"]["text"]);
                $target = $this->mergeText($target, $data["desc"], $data["desc"]["text"]);
                imagepng($target, $path . $file);
                imagedestroy($target);
            }
            if($codeshare == 2){
                $target = imagecreatetruecolor(640, 640);
                $color = imagecolorAllocate($target, 255, 255, 255);
                imagefill($target, 0, 0, $color);
                $colorline = imagecolorallocate($target, 0, 0, 0);
                imageline($target, 0, 190, 640, 190, $colorline);
                $red = imagecolorallocate($target, 254, 155, 68);
                imagefilledrectangle($target, 0, 560, 640, 640, $red);
                imagecopy($target, $target, 0, 0, 0, 0, 640, 640);
                $thumb = preg_replace("/\\/0\$/i", "/96", $data["thumb"]["thumb"]);
                $target = $this->mergeImage($target, $data["thumb"], $thumb);
                $target = $this->mergeText($target, $data["title"], $data["title"]["text"]);
                $target = $this->mergeText($target, $data["price"], $data["price"]["text"]);
                $qrcode = preg_replace("/\\/0\$/i", "/96", $data["qrcode"]["thumb"]);
                $target = $this->mergeImage($target, $data["qrcode"], $qrcode);
                $target = $this->mergeText($target, $data["desc"], $data["desc"]["text"]);
                $target = $this->mergeText($target, $data["shopname"], $data["shopname"]["text"], true);
                imagepng($target, $path . $file);
                imagedestroy($target);
            }else{
                if($codeshare == 3){
                    $target = imagecreatetruecolor(640, 1060);
                    $color = imagecolorAllocate($target, 245, 244, 249);
                    imagefill($target, 0, 0, $color);
                    imagecopy($target, $target, 0, 0, 0, 0, 640, 1008);
                    $target = $this->mergeText($target, $data["title"], $data["title"]["text"]);
                    $target = $this->mergeText($target, $data["price"], $data["price"]["text"]);
                    $target = $this->mergeText($target, $data["desc"], $data["desc"]["text"]);
                    $thumb = preg_replace("/\\/0\$/i", "/96", $data["thumb"]["thumb"]);
                    $target = $this->mergeImage($target, $data["thumb"], $thumb);
                    $qrcode = preg_replace("/\\/0\$/i", "/96", $data["qrcode"]["thumb"]);
                    $target = $this->mergeImage($target, $data["qrcode"], $qrcode);
                    imagepng($target, $path . $file);
                    imagedestroy($target);
                }
            }

        }
        $img = $paths. $file;
        return $img;//返回
    }
	
	
	
    //添加图片
    public function createImage($imgurl){
        $config = Setting::config();
        $count = str_replace($config["site"]["host"], ROOT_PATH . "/", $imgurl);
        $imgurl = file_get_contents($count);
        return imagecreatefromstring($imgurl);
    }


    //合并图片
    public function mergeImage($target, $data, $imgurl){
        $img = $this->createImage($imgurl);
        $w = imagesx($img);
        $h = imagesy($img);
        imagecopyresized($target, $img, $data["left"], $data["top"], 0, 0, $data["width"], $data["height"], $w, $h);
        imagedestroy($img);
        return $target;
    }



    //合并文字
    public function mergeText($target, $data, $text, $center = false) {
        $font = "/public/font/simsun.ttc";
        $colors = $this->hex2rgb($data["color"]);
        $color = imagecolorallocate($target, $colors["red"], $colors["green"], $colors["blue"]);
        if($center){
            $fontBox = imagettfbbox($data["size"], 0, $font, $data["text"]);
            imagettftext($target, $data["size"], 0, ceil(($data["width"] - $fontBox[2]) / 2), $data["top"] + $data["size"], $color, $font, $text);
        }else{
            imagettftext($target, $data["size"], 0, $data["left"], $data["top"] + $data["size"], $color, $font, $text);
        }
        return $target;
    }


    //合并颜色
    public function hex2rgb($colour){
        if($colour[0] == "#"){
            $colour = substr($colour, 1);
        }
        if(strlen($colour) == 6){
            list($r, $g, $b) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
        }
        else{
            if(strlen($colour) == 3){
                list($r, $g, $b) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
            }else{
                return false;
            }
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array( "red" => $r, "green" => $g, "blue" => $b );
    }   
			
			
			
		
}