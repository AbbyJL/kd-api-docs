<?php
namespace app\weixin\controller;

use app\common\controller\Common;

use think\Db;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Voice;
use EasyWeChat\Message\News;
use EasyWeChat\Foundation\Application;



use app\common\model\Setting;

class Index extends Base{

    function traceHttp(){
        $this->logger("\n\nREMOTE_ADDR:".$_SERVER["REMOTE_ADDR"].(strstr($_SERVER["REMOTE_ADDR"],'101.226')? " FROM WeiXin": "Unknown IP"));
        $this->logger("QUERY_STRING:".$_SERVER["QUERY_STRING"]);
    }

    function logger($log_content){
        $max_size = 500000;
        $log_filename = "log.txt";
        if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
        file_put_contents($log_filename, date('Y-m-d H:i:s').$log_content."\r\n", FILE_APPEND);
    }
    
	
	
    public function _initialize(){
        parent::_initialize();
		
		config('app_debug',false);
        config('app_trace',false);
		
		//微信平台
        $this->options = model('WeixinConfig')->weixinconfig();
        $this->app = new Application($this->options);//前端调用
        
        if(input('echostr') && $this->checkSignature()){
            return input('echostr');//验证token
        }
        return input('echostr');
    }
	
	
	
	//获取用户ID生成图片
	public function uploadImage($user_id = 0,$fuid = 0){
		$path = model('Api')->addCode($user_id,$fuid);
		$mediaId = $this->app->media->uploadImage($path);
		$image = new Image($mediaId);
		return $image;
	}
	
	
	
	//请求数据
	public function httpRequest($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
		if(!empty($data)){
			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
		}
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	
	
	//永久素材
	public function mass($token,$filename){
		$data = array('media'=>new \CURLFile(ROOT_PATH.$filename));
		$url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$token.'&type=image';
		$result = $this->httpRequest($url,$data);
		$result2 = json_decode($result,true);
		return $result2;
    }

	
	
	public function performAction($open_id=''){
		
		$config = model('Setting')->fetchAll2();
		
		$uid = Db::name('connect')->where(array('type'=>'weixin','open_id'=>$open_id))->value('uid');
		$falg =0;
		$content ='';
		
		if($uid){
			//送优惠券
			if($uid){
				$send = model('ExpressOrder')->sendCouponDownload($uid,'新人有礼');//送优惠券
				if($send){
					$falg =1;
					$content .='恭喜您获得新人有礼优惠券一张';
				}
			}
			//邀请新用户奖励积分
			$follow = (int)$config['integral']['follow'];
			$log = Db::name('user_integral_logs')->where(array('type'=>3,'user_id'=>$uid))->count();
			if($follow && $uid && !$log){
				$falg =1;
				model('Users')->addIntegral($uid,$follow,'关注服务号获取积分',3);
				$content .='，恭喜您关注公众号获得积分'.$follow.'请登录小程序查看';
			}
			if($falg ==1){
				return $content;
			}else{
				return $config['weixin']['description'] ? $config['weixin']['description'] : '感谢您关注请先去访问小程序后再来';
			}
		}else{
			return $config['weixin']['description'] ? $config['weixin']['description'] : '感谢您关注请先去访问小程序后再来';
		}
    }
	
	
	
	public function index(){
        //消息处理
        $this->app->server->setMessageHandler(function ($message){
            switch ($message->MsgType){
				  case 'event':
                    switch ($message->Event){
                        case 'subscribe':
						    $Ekey = $message->EventKey;
							$open_id = $message->FromUserName;
							if($open_id){
								$Text = $this->performAction($open_id);
								$text = new Text(array('content' =>$Text));
								return $text;
							}
							if(isset($Ekey) && !empty($Ekey)){
							    $text = new Text(array('content' => '首次扫码关注'));
                        		return $text;
							}else{
								$text = new Text(array('content' => '首次关注'));
                        		return $text;
                            } 
                            break;
                        case 'SCAN':
							
							$text = new Text(array('content' => '二次扫码'));
                        	return $text;
                            break;
                        case 'CLICK':
                            $EventKey = $message->EventKey;
							$open_id = $message->FromUserName;
							if($open_id){
								$Text = $this->performAction($open_id);
								$text = new Text(array('content' =>$Text));
								return $text;
							}else{
								$text = new Text(array('content' => '用户输入CLICK'));
								return $text;
							}
                    }
                    break;
                case 'text':
					$open_id = $message->FromUserName;
					if($open_id){
						$Text = $this->performAction($open_id);
						$text = new Text(array('content' =>$Text));
						return $text;
					}else{
						$text = new Text(array('content' => '用户输入text'));
						return $text;
					}
                    break;
                case 'image':
                    break;
                case 'voice':
                    break;
                case 'video':
                    break;
                case 'location':
                    break;
                case 'link':
                    break;
                default:
                    break;
            }
        });
        $this->app->server->serve()->send();
	}


   


    private function checkSignature(){
        $signature =input('signature');
        $timestamp = input('timestamp');
        $nonce = input('nonce');
        $token = $this->options['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr,SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            return true;
        }else{
            return false;
        }
    }
	
	private function getImage($img){
		return config_weixin_img($img);
    }
	
	
	
	
	
}