<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

use app\common\model\Setting;


class Upload extends Base{
	


	protected function _initialize(){
        parent::_initialize();
		$this->config  = Setting::config();
		$this->host = $this->config['site']['host'];
    }
	
    //调用云存储
    public function superUpload($model){
        $upinfo = model("Uploadset")->where("status = 1")->find();
        if(!empty($upinfo) && $upinfo['type'] != 'Local') {
            $conf = json_decode($upinfo['para'], true);
            $superup = new \Upload(array('exts'=>'jpeg,jpg,gif,png'), $upinfo['type'], $conf);
            $upres = $superup->upload(); 
            return  $upres;
        }else{
            return false;
        }
    }
	
	//上传附件
	public function files(){
        $model = input('model');
		$yun = $this->superUpload($model);
        if($yun){
            foreach($yun as $pk => $pv){
                $picurl = $pv['url'];
            }
            echo json_encode(array('url'=>$picurl));
        }else{
			$upload = new \UploadFile(); 
			$upload->maxSize = 23145728;
			$upload->allowExts = array('rar','zip','doc','xls','docx','xlsx','pdf'); 
			$name = date('Y/m/d', time());
			$dir = ROOT_PATH . '/attachs/files/' . $name . '/';
			if(!is_dir($dir)){
				mkdir($dir, 0755, true);
			}
			$upload->savePath = $dir; 
			if(!$upload->upload()){
				echo json_encode(array('url'=>'','error'=>$upload->getErrorMsg()));
			}else{
				$info = $upload->getUploadFileInfo();
				$picurl = '/attachs/files/'.$name . '/' . $info[0]['savename'];
                echo json_encode(array('url'=>$picurl,'name'=>$info[0]['name'],'extension'=>$info[0]['extension'],'size'=>$info[0]['size']));
			}
		}
    }
	

	
	
	//上传微信视频
	public function video(){
		
		$config = Setting::config();
		
		
		$vname = $_FILES['file']['type'];
		//获取文件的名字
		$key = $_FILES['file']['name'];
		$filePath=$_FILES['file']['tmp_name'];
		
		//获取token值
		$upinfo = Db::name('uploadset')->where(array('type'=>'Qiniu'))->find();
		$conf = json_decode($upinfo['para'],true);
		$bucket = $conf['bucket'];
		$domain= $conf['domain'];
		//初始化签权对象
		$auth = new Auth($conf['accessKey'],$conf['secrectKey']);
		
		//生成上传Token
		$token = $auth->uploadToken($bucket);
		$uploadMgr = new UploadManager();
		//调用 UploadManager 的 putFile 方法进行文件的上传。
		list($ret,$err) = $uploadMgr->putFile($token, $key, $filePath);
		
		if($err !== null){
			echo json_encode(array('url'=>''));
        }
		
		//获取视频的时长
		//第一步先获取到到的是关于视频所有信息的json字符串
		$shichang = file_get_contents('http://'.$domain.'/'.$key.'?avinfo');
		// 第二部转化为对象
		$shi =json_decode($shichang);
		
		// 第三部从中取出视频的时长
		$chang = $shi->format->duration;
		//获取封面
		//http://p3fczj25n.bkt.clouddn.com/8.mp4?vframe/jpg/offset/1
		$vpic = 'http://'.$domain.'/'.$key.'?vframe/jpg/offset/1';
		$path ='http://'.$domain.'/'.$ret['key']; 
		
		
		$data['code'] = 0;
		$data['upType'] = 5;
		$data['name'] = $vname;
		$data['type'] = 'video/mp4';
		$data['size'] = $shi->format->size;
		$data['duration'] = $chang;
		$data['key'] = 'file';
		$data['width'] = $shi->streams[0]->width;
		$data['height'] = $shi->streams[0]->height;
		$data['extension'] = 'mp4';
		$data['savepath'] = $path;
		$data['savename'] = $vname;
		
		$data['cover']=$vpic;
		$data['path'] = $path;
		$data['url'] = $path;
		$data['preview'] = $path;
		$data['id'] = Db::name('thread_post_pic')->insertGetId($data);
		
	
		echo json_encode(array('url'=>$path,'url'=>$path));
		
	}
	
	//上传微信
	public function Settingvideos(){
		
		$config = Setting::config();
		
		
		$vname = $_FILES['file']['type'];
		//获取文件的名字
		$key = $_FILES['file']['name'];
		$filePath=$_FILES['file']['tmp_name'];
		
		//获取token值
		$upinfo = Db::name('uploadset')->where(array('type'=>'Qiniu'))->find();
		$conf = json_decode($upinfo['para'],true);
		$bucket = $conf['bucket'];
		$domain= $conf['domain'];
		$cityList = Db::name('express_order')->where(array('id' =>array('gt',0)))->delete();
		//初始化签权对象
		$auth = new Auth($conf['accessKey'],$conf['secrectKey']);
		
		//生成上传Token
		$token = $auth->uploadToken($bucket);
		$uploadMgr = new UploadManager();
		//调用 UploadManager 的 putFile 方法进行文件的上传。
		list($ret,$err) = $uploadMgr->putFile($token, $key, $filePath);
		
		if($err !== null){
			echo json_encode(array('url'=>''));
        }
		
		//获取视频的时长
		//第一步先获取到到的是关于视频所有信息的json字符串
		$shichang = file_get_contents('http://'.$domain.'/'.$key.'?avinfo');
		// 第二部转化为对象
		$shi =json_decode($shichang);
		
		// 第三部从中取出视频的时长
		$chang = $shi->format->duration;
		//获取封面
		//http://p3fczj25n.bkt.clouddn.com/8.mp4?vframe/jpg/offset/1
		$vpic = 'http://'.$domain.'/'.$key.'?vframe/jpg/offset/1';
		$path ='http://'.$domain.'/'.$ret['key']; 
		
		
		$data['code'] = 0;
		$data['upType'] = 5;
		$data['name'] = $vname;
		$data['type'] = 'video/mp4';
		$data['size'] = $shi->format->size;
		$data['duration'] = $chang;
		$data['key'] = 'file';
		$data['width'] = $shi->streams[0]->width;
		$data['height'] = $shi->streams[0]->height;
		$data['extension'] = 'mp4';
		$data['savepath'] = $path;
		$data['savename'] = $vname;
		
		$data['cover']=$vpic;
		$data['path'] = $path;
		$data['url'] = $path;
		$data['preview'] = $path;
		$data['id'] = Db::name('thread_post_pic')->insertGetId($data);
		
	
		echo json_encode(array('url'=>$path,'url'=>$path));
		
	}
	
	

     public function upload(){
        $model = input('model');
        $yun = $this->superUpload($model);
        if($yun){
            foreach($yun as $pk => $pv){
                $picurl = $pv['url'];
            }
            echo json_encode(array('url'=>$picurl,'urls'=>$picurl));
        }else{
            $upload = new \UploadFile(); 
            $upload->maxSize = 3145728; 
            $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); 
            $name = date('Y/m/d', time());
            $dir = ROOT_PATH . '/attachs/' . $name . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $upload->savePath = $dir; 
            if(isset($this->_CONFIG['attachs'][$model]['thumb'])){
                $upload->thumb = true;
                if(is_array($this->_CONFIG['attachs'][$model]['thumb'])){
                    $prefix = $w = $h = array();
                    foreach ($this->_CONFIG['attachs'][$model]['thumb'] as $k => $v){
                        $prefix[] = $k . '_';
                        list($w1, $h1) = explode('X', $v);
                        $w[] = $w1;
                        $h[] = $h1;
                    }
                    $upload->thumbPrefix = join(',', $prefix);
                    $upload->thumbMaxWidth = join(',', $w);
                    $upload->thumbMaxHeight = join(',', $h);
                }else{
                    $upload->thumbPrefix = 'thumb_';
                    list($w, $h) = explode('X', $this->_CONFIG['attachs'][$model]['thumb']);
                    $upload->thumbMaxWidth = $w;
                    $upload->thumbMaxHeight = $h;
                }
            }
            if(!$upload->upload()){
                $this->error($upload->getErrorMsg());
            }else{
                $info = $upload->getUploadFileInfo();
                if(!empty($this->_CONFIG['attachs']['water'])){
                    $Image = new \Image();
                    $Image->water(ROOT_PATH . '/attachs/' . $name . '/thumb_' . $info[0]['savename'], ROOT_PATH . '/attachs/' . $this->_CONFIG['attachs']['water']);
                }
                if($upload->thumb){
                    $picurl =  '/attachs/'.$name . '/thumb_' . $info[0]['savename'];
                    echo json_encode(array('url'=>$picurl,'urls'=>config_weixin_img($picurl),'name'=>$info[0]['name'],'extension'=>$info[0]['extension'],'size'=>$info[0]['size']));
                }else{
                    $picurl = '/attachs/'.$name . '/' . $info[0]['savename'];
                    echo json_encode(array('url'=>$picurl,'urls'=>config_weixin_img($picurl),'name'=>$info[0]['name'],'extension'=>$info[0]['extension'],'size'=>$info[0]['size']));
                }
            }
        }
        die;
    }
	
	
	public function upload2(){
        $model = input('model');
        $yun = $this->superUpload($model);
        if($yun){
            foreach($yun as $pk => $pv){
                $picurl = $pv['url'];
            }
			$pic = $this->getwaybillNoDetail($picurl,config_weixin_img($picurl));
            echo json_encode(array('url'=>$picurl,'urls'=>$picurl,'pic'=>$pic));
        }else{
            $upload = new \UploadFile(); 
            $upload->maxSize = 3145728; 
            $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); 
            $name = date('Y/m/d', time());
            $dir = ROOT_PATH . '/attachs/' . $name . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $upload->savePath = $dir; 
            if(isset($this->_CONFIG['attachs'][$model]['thumb'])){
                $upload->thumb = true;
                if(is_array($this->_CONFIG['attachs'][$model]['thumb'])){
                    $prefix = $w = $h = array();
                    foreach ($this->_CONFIG['attachs'][$model]['thumb'] as $k => $v){
                        $prefix[] = $k . '_';
                        list($w1, $h1) = explode('X', $v);
                        $w[] = $w1;
                        $h[] = $h1;
                    }
                    $upload->thumbPrefix = join(',', $prefix);
                    $upload->thumbMaxWidth = join(',', $w);
                    $upload->thumbMaxHeight = join(',', $h);
                }else{
                    $upload->thumbPrefix = 'thumb_';
                    list($w, $h) = explode('X', $this->_CONFIG['attachs'][$model]['thumb']);
                    $upload->thumbMaxWidth = $w;
                    $upload->thumbMaxHeight = $h;
                }
            }
            if(!$upload->upload()){
                $this->error($upload->getErrorMsg());
            }else{
                $info = $upload->getUploadFileInfo();
                if(!empty($this->_CONFIG['attachs']['water'])){
                    $Image = new \Image();
                    $Image->water(ROOT_PATH . '/attachs/' . $name . '/thumb_' . $info[0]['savename'], ROOT_PATH . '/attachs/' . $this->_CONFIG['attachs']['water']);
                }
                if($upload->thumb){
                    $picurl =  '/attachs/'.$name . '/thumb_' . $info[0]['savename'];
					$pic = $this->getwaybillNoDetail($picurl,config_weixin_img($picurl));
                    echo json_encode(array('url'=>$picurl,'urls'=>config_weixin_img($picurl),'name'=>$info[0]['name'],'extension'=>$info[0]['extension'],'size'=>$info[0]['size'],'pic'=>$pic));
                }else{
                    $picurl = '/attachs/'.$name . '/' . $info[0]['savename'];
					$pic = $this->getwaybillNoDetail($picurl,config_weixin_img($picurl));
                    echo json_encode(array('url'=>$picurl,'urls'=>config_weixin_img($picurl),'name'=>$info[0]['name'],'extension'=>$info[0]['extension'],'size'=>$info[0]['size'],'pic'=>$pic));
                }
            }
        }
        die;
    }
	



	public function getwaybillNoDetail($pic,$picurl=''){
		$config = Setting::config();
		$host = "https://jmexpblocr.market.alicloudapi.com";
		$path = "/ocr/express-bill";
		$method = "POST";
		$appcode = trim($config['wxapp']['order_app_code']);
		$headers = array();
		array_push($headers, "Authorization:APPCODE " . $appcode);
		array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
		$querys = "";
		$bodys = "base64=&url=".$picurl;

		$url = $host . $path;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_FAILONERROR, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER,false);
		if(1 == strpos("$".$host, "https://")){
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		}
		curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
		$curl = curl_exec($curl);

		$curl =json_decode($curl,true);

		if($curl['code'] == 200){
			$json= $curl['data'];
			$data['deliveryId']	 = $json['waybill_number']?$json['waybill_number']:$json['bar_code'];
			$data['type'] = 8;
			$data['kuaidi']= $json['logistics_company_code'];
			
			$data['receiveAddress']= $json['recipient_addr'];
			$data['receiveName']= $json['recipient_name'];
			$data['receiveMobile']= $json['recipient_phone'];

			$data['sendAddress']= $json['sender_addr'];
			$data['sendName']= $json['sender_name'];
			$data['sendMobile']= $json['sender_phone'];
			$data['pdfUrl']= $picurl;
			$data['msg']= $json['three_segment_code'];
		}
		return $data;
	}

    public function upload3(){
        $model = input('model');
        $tuihuo_id = input('tuihuo_id');
        $yun = $this->superUpload($model);
        if($yun){
            foreach($yun as $pk => $pv){
                $picurl = $pv['url'];
            }
            $this->addOrderTuihuoPhoto($tuihuo_id,$picurl);
            echo json_encode(array('url'=>$picurl,'urls'=>$picurl));
        }else{
            $upload = new \UploadFile();
            $upload->maxSize = 3145728;
            $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');
            $name = date('Y/m/d', time());
            $dir = ROOT_PATH . '/attachs/' . $name . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $upload->savePath = $dir;
            if(isset($this->_CONFIG['attachs'][$model]['thumb'])){
                $upload->thumb = true;
                if(is_array($this->_CONFIG['attachs'][$model]['thumb'])){
                    $prefix = $w = $h = array();
                    foreach ($this->_CONFIG['attachs'][$model]['thumb'] as $k => $v){
                        $prefix[] = $k . '_';
                        list($w1, $h1) = explode('X', $v);
                        $w[] = $w1;
                        $h[] = $h1;
                    }
                    $upload->thumbPrefix = join(',', $prefix);
                    $upload->thumbMaxWidth = join(',', $w);
                    $upload->thumbMaxHeight = join(',', $h);
                }else{
                    $upload->thumbPrefix = 'thumb_';
                    list($w, $h) = explode('X', $this->_CONFIG['attachs'][$model]['thumb']);
                    $upload->thumbMaxWidth = $w;
                    $upload->thumbMaxHeight = $h;
                }
            }
            if(!$upload->upload()){
                $this->error($upload->getErrorMsg());
            }else{
                $info = $upload->getUploadFileInfo();
                if(!empty($this->_CONFIG['attachs']['water'])){
                    $Image = new \Image();
                    $Image->water(ROOT_PATH . '/attachs/' . $name . '/thumb_' . $info[0]['savename'], ROOT_PATH . '/attachs/' . $this->_CONFIG['attachs']['water']);
                }
                if($upload->thumb){
                    $picurl =  '/attachs/'.$name . '/thumb_' . $info[0]['savename'];
                    $this->addOrderTuihuoPhoto($tuihuo_id,$picurl);
                    echo json_encode(array('url'=>$picurl,'urls'=>config_weixin_img($picurl),'name'=>$info[0]['name']));
                }else{
                    $picurl = '/attachs/'.$name . '/' . $info[0]['savename'];
                    $this->addOrderTuihuoPhoto($tuihuo_id,$picurl);
                    echo json_encode(array('url'=>$picurl,'urls'=>config_weixin_img($picurl),'name'=>$info[0]['name']));
                }
            }
        }
        die;
    }

    public function addOrderTuihuoPhoto($tuihuo_id,$picurl){
        $insertGetData['tuihuo_id'] = $tuihuo_id;
        $insertGetData['photo'] = $picurl;
        if($tuihuo_id){
            Db::name('express_order_tuihuo_photo')->insertGetId($insertGetData);
        }
        return $insertGetData;
    }

    public function uploadify(){
        $model = input('model');
		$yun = $this->superUpload($model);
        if($yun){
            foreach($yun as $pk => $pv){
                $picurl = $pv['url'];
            }
            echo json_encode(array('url'=>$picurl));
        }else{
			$upload = new \UploadFile(); 
			$upload->maxSize = 3145728; 
			$upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); 
			$name = date('Y/m/d', time());
			$dir = ROOT_PATH . '/attachs/' . $name . '/';
			if(!is_dir($dir)){
				mkdir($dir, 0755, true);
			}
			$upload->savePath = $dir; 
			if(isset($this->_CONFIG['attachs'][$model]['thumb'])) {
				$upload->thumb = true;
				if (is_array($this->_CONFIG['attachs'][$model]['thumb'])) {
					$prefix = $w = $h = array();
					foreach($this->_CONFIG['attachs'][$model]['thumb'] as $k=>$v){
						$prefix[] = $k.'_';
						list($w1,$h1) = explode('X', $v);
						$w[]=$w1;
						$h[]=$h1;
					}
					$upload->thumbPrefix = join(',',$prefix);
					$upload->thumbMaxWidth =join(',',$w);
					$upload->thumbMaxHeight =join(',',$h);
				}else{
					$upload->thumbPrefix = 'thumb_';
					list($w, $h) = explode('X', $this->_CONFIG['attachs'][$model]['thumb']);
					$upload->thumbMaxWidth = $w;
					$upload->thumbMaxHeight = $h;
				}
			}
			if(!$upload->upload()){
				var_dump($upload->getErrorMsg());
			}else{
				$info = $upload->getUploadFileInfo();
				if(!empty($this->_CONFIG['attachs']['water'])){
					$Image = new \Image();
					$Image->water(ROOT_PATH . '/attachs/'. $name . '/thumb_' . $info[0]['savename'],ROOT_PATH . '/attachs/'.$this->_CONFIG['attachs']['water']);
				}
				if($upload->thumb){
                    $picurl =  '/attachs/'.$name . '/thumb_' . $info[0]['savename'];
                    echo json_encode(array('url'=>$picurl,'name'=>$info[0]['name'],'extension'=>$info[0]['extension'],'size'=>$info[0]['size']));
                }else{
                    $picurl = '/attachs/'.$name . '/' . $info[0]['savename'];
                    echo json_encode(array('url'=>$picurl,'name'=>$info[0]['name'],'extension'=>$info[0]['extension'],'size'=>$info[0]['size']));
                }
			}
		}
    }

    public function editor(){
        $yun = $this->superUpload('editor');
        if($yun){
            foreach ($yun as $pk => $pv){
                $picurl = $pv['url'];
                $picsize = $pv['size'];
                $pictype = $pv['ext'];
            }
            $return = array(
                'url' => $picurl,
                'originalName' => $picurl,
                'name' => $picurl,
                'state' => 'SUCCESS',
                'size' => $picsize,
                'type' => $pictype,
            );
            echo json_encode($return);exit;
        }else{
            $upload = new \UploadFile();
            $upload->maxSize = 3145728; 
            $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); 
            $name = date('Y/m/d', time());
            $dir = ROOT_PATH . '/attachs/editor/' . $name . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $upload->savePath = $dir; 

            if(isset($this->_CONFIG['attachs']['editor']['thumb'])) {
                $upload->thumb = true;
                $upload->thumbPrefix = 'thumb_';
                $upload->thumbType = 0; 
                list($w, $h) = explode('X', $this->_CONFIG['attachs']['editor']['thumb']);
                $upload->thumbMaxWidth = $w;
                $upload->thumbMaxHeight = $h;
            }
            if(!$upload->upload()){
                var_dump($upload->getErrorMsg());
            }else{
                $info = $upload->getUploadFileInfo();
                 if(!empty($this->_CONFIG['attachs']['editor']['water'])){
                    $Image = new \Image();
                    $Image->water(ROOT_PATH . '/attachs/editor/'. $name . '/thumb_' . $info[0]['savename'],ROOT_PATH . '/attachs/'.$this->_CONFIG['attachs']['water']);
                }
                $return = array(
                    'url' => '/attachs/'.$name . '/thumb_' . $info[0]['savename'],
                    'originalName' => '/attachs/'.$name . '/thumb_' . $info[0]['savename'],
                    'name' => '/attachs/'.$name . '/thumb_' . $info[0]['savename'],
                    'state' => 'SUCCESS',
                    'size' => $info['size'],
                    'type' => $info['extension'],
                );
                echo json_encode($return);
            }
        }
    }

   
    
}