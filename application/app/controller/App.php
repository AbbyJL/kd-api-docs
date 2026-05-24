<?php


namespace app\app\controller;
use think\Db;
use think\Cache;

class App extends Base{
	

	//短信信息直接输出开启
	public function IsSms(){
		echo 1;
	}

	//获取系统帮助文章
	public function GetHelp(){
		$res = Db::name('article')->order(array('create_time' => 'desc'))->limit(0,6)->select();
		foreach($res as $k => $val){
			$res[$k]['id'] = $val['article_id'];
		    $res[$k]['detail'] = cleanhtml($val['details']);
	    }
        $json_str = json_encode($res);
        exit($json_str); 
	}
	
	//发送短信 
	public function Sms(){
		$tel = input('tel','','trim,htmlspecialchars');
		$code = input('code','','trim,htmlspecialchars');
		$res = model('Sms')->sms_yzm($tel,$code);//发送验证码
		echo $res;
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
                    $picurl =  $this->_CONFIG['site']['host'].'/attachs/'.$name . '/thumb_' . $info[0]['savename'];
                    echo $picurl;
                }else{
                    $picurl = $this->_CONFIG['site']['host'].'/attachs/'.$name . '/' . $info[0]['savename'];
                    echo $picurl;
                }
			}
		}
    }

  	
	
}