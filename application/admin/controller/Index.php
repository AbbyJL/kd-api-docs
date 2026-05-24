<?php
namespace app\admin\controller;

use think\Db;
use think\Cache;

use app\common\model\Setting;

class Index extends Base{
	
	
	public function _initialize(){
        parent::_initialize();
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); 
		header('Content-type: text/json; charset=utf-8');
		$this->assign('getCompanyApiTypes', $getCompanyApiTypes = model('Setting')->getCompanyApiTypes());
    }
	
	
	
	
	public function url_link(){
		$config = model('Setting')->fetchAll2();
		$uid = (int)$config['profit']['user_id'];
		$result = model('Weixin')->getWxappUrlLink($uid);
		$this->assign('url_link',$result);
		return $this->fetch();
	}
	
	
	//谷歌保存地址
	public function ajaxlngtlat(){
		$lng = input('lng', '', 'trim,htmlspecialchars');
		$lat = input('lat', '', 'trim,htmlspecialchars');
		
		
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lng.'&key=AIzaSyAMY3Kyvzj8d0FhwsIbDmwHJpXYUlrpf4Y&language=en$s&sensor=false&region=es';
        $html = file_get_contents($url);
		$rest = json_decode($html);
		
		
		if($rest->status == 0 && $lat){
			$msg['city'] = '';
			$msg['lng'] = $lng;
			$msg['lng'] = $lng;
			$msg['region_addr'] = $rest->plus_code->compound_code;
			$msg['region_name'] = $rest->plus_code->compound_code;
			return json(array('error'=>false,'status'=>$rest->status,'msg'=>$msg));
		}else{
			return json(array('error'=>true));
		}
    }

	//搜索谷歌地址
	public function querytextsearch(){
		$query = input('lquery','','trim,htmlspecialchars');
		$choice_lng = input('choice_lng','','trim,htmlspecialchars');
		$choice_lat = input('choice_lat','','trim,htmlspecialchars');
		
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$choice_lat.','.$choice_lng.'&key=AIzaSyAMY3Kyvzj8d0FhwsIbDmwHJpXYUlrpf4Y&language=zh$s&sensor=false&region=es';
        $html = file_get_contents($url);
		$rest = json_decode($html);
		

		$list = $rest->results->address_components;
		foreach($list as $k => $v){
           $list[$k]['address'] = $v['long_name'];
		   $list[$k]['location'] = $v['short_name'];
		   $list[$k]['name'] = $v['short_name'];
        }
		if($list){
			return json(array('error'=>false,'msg'=>$list));
		}else{
			return json(array('error'=>true));
		}
    }
	
	

	
	
    public function index(){
        $menu = model('Menu')->fetchAll();
        if($this->_admin['role_id'] != 1){
            if($this->_admin['menu_list']){
                foreach($menu as $k => $val){
                    if(!empty($val['menu_action']) && !in_array($k, $this->_admin['menu_list'])){
                        unset($menu[$k]);
                    }
                }
                foreach($menu as $k1 => $v1){
                    if($v1['parent_id'] == 0){
                        foreach($menu as $k2 => $v2) {
                            if($v2['parent_id'] == $v1['menu_id']){
                                $unset = true;
                                foreach($menu as $k3 => $v3){
                                    if($v3['parent_id'] == $v2['menu_id']){
                                        $unset = false;
                                    }
                                }
                                if($unset){
                                    unset($menu[$k2]);
                                }
                            }
                        }
                    }
                }
                foreach($menu as $k1 => $v1){
                    if($v1['parent_id'] == 0){
                        $unset = true;
                        foreach($menu as $k2 => $v2) {
                            if($v2['parent_id'] == $v1['menu_id']){
                                $unset = false;
                            }
                        }
                        if($unset){
                            unset($menu[$k1]);
                        }
                    }
                }
            }else{
                $menu = array();
            }
        }
        $this->assign('menuList', $menu);
        return $this->fetch();
    }
	
	
	public function updates(){
		$data = @file_get_contents(ROOT_PATH.'/log.json');
		$this->assign('data',$data);
	
		$use_version = (include ROOT_PATH . '/version.php');
		$now_version = $use_version['version'];
		$log = Db::name('update_log')->select();
		$this->assign('version', $now_version);
		$this->assign('log', $log);
		
		return $this->fetch();
	}
	
	
	public function checkversion(){
		$use_version = (include ROOT_PATH . '/version.php');
		$now_version = $use_version['version'];
		$this->curl = new \Curl();
		$postData['host'] = trim($this->_CONFIG['site']['host']);
		$postData['mobile'] = trim($this->_CONFIG['site']['mobile']);
		$url = getHost().'/api/Requestapi/get_site_version1';
		$r = $this->curl->post($url,json_encode($postData));
		$r = json_decode($r, true);
		if($r['code'] == '0'){
			return json(array('code' => '0', 'msg' =>$r['msg']));
		}
		$res = $r['data'];
		$url = $res['url'];
		$new_version = $res['version'];
		$desc = $res['desc'];
		if($now_version < $new_version){
			return json(array('code' => '1', 'msg' => '有新版更新', 'data' => $res));
		}else{
			return json(array('code' => '0', 'msg' => '您已经是最新版本'));
		}
	}
	
	public function start_upgrade(){
		ini_set('display_errors', '1');
		error_reporting(-1);
		$use_version = (include ROOT_PATH . '/version.php');
		$now_version = $use_version['version'];
		$this->curl = new \Curl();
		
		$postData['host'] = trim($this->_CONFIG['site']['host']);
		$postData['mobile'] = trim($this->_CONFIG['site']['mobile']);
		$url = getHost().'/api/Requestapi/get_site_version1';
		$r = $this->curl->post($url,json_encode($postData));
		$r = json_decode($r, true);
		if ($r['code'] == '0') {
			$this->success($r['msg'], url('login/index'));
		}
		$res = $r['data'];
		$url = $res['url'];
		$new_version = $res['version'];
		
		if($now_version < $new_version && !empty($url)){
			$down_url = $url;
			$tempath = ROOT_PATH . 'tmp';
			$remote_fp = fopen($down_url, 'rb');
			if(!is_dir($tempath)){
				mkdir($tempath, 511, true);
			}
			$file = $tempath . '/' . date('Ymd') . '.zip';
			if(is_file($file)){
				unlink($file);
			}
			$local_fp = fopen($file, 'wb');
			ob_flush();
			while (!feof($remote_fp)){
				fwrite($local_fp, fread($remote_fp, 128));
			}
			fclose($remote_fp);
			fclose($local_fp);
			ob_flush();
			$this->unzip($file, ROOT_PATH);
			if(file_exists(ROOT_PATH . 'application/update/update.sql')){
				$sqls = file_get_contents(ROOT_PATH . 'application/update/update.sql');
				$sqls = explode(';\r\n', $sqls);
				foreach ($sqls as $sql){
					Db::query($sql);
				}
				$this->deldir(ROOT_PATH . 'application/update');
			}
			$this->deldir(ROOT_PATH . 'tmp');
			$new_version_arr = array('version' => $new_version);
			$str = '<?php \r\n\r\n return ' . var_export($new_version_arr, true) . ';';
			file_put_contents(ROOT_PATH . '/version.php', $str);
			try{
				$data = array('version' => $new_version, 'desc' => $res['desc'], 'addtime' => date('Y-m-d H:i:s'));
				Db::name('update_log')->insert($data);
			}catch (Exception $e){
				
			}
			$this->success('更新成功！', url('index/updates'));
		}else{
			$this->success('已是最新版本！', url('index/updates'));
		}
	}
	
	public function unzip($filePath, $path){
		if(empty($path) || empty($filePath)){
			return false;
		}
		$zip = new \ZipArchive();
		if($zip->open($filePath) === true){
			$zip->extractTo($path);
			$zip->close();
			return true;
		}else{
			return false;
		}
	}
	
	protected function deldir($dir){
		$dh = opendir($dir);
		while($file = readdir($dh)){
			if($file != '.' && $file != '..'){
				$fullpath = $dir . '/' . $file;
				if(!is_dir($fullpath)) {
					@unlink($fullpath);
				}else{
					$this->deldir($fullpath);
				}
			}
		}
		closedir($dh);
		if (rmdir($dir)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	
	
	public function clear(){
		Cache::clear();
		$File = new \File();
		$res = $File->rmFiles($path = 'runtime/temp');
		$this->success('请求清理缓存成功【'.$res.'】');
	}
	
	
	public function action(){
		$File = new \File();
		$res = $File->rmFiles($path = 'attachs/weixin');
		$this->success('操作成功【'.$res.'】');
	}
	
	public function qrcode(){
		Cache::clear();
		$File = new \File();
		$res = $File->rmFiles($path = 'attachs/qrcode');
		$intro = $this->delUserPoster();//删除会员海报
		
		$this->success('操作成功【'.$res.'】'.$intro.'');
	}
	
	public function clearToken(){
		$datas = new \stdClass();
		$datas->expire_time = time() - 7200;
		$datas->access_token = '';
		$datas->create_time= time();
		$fp = fopen(ROOT_PATH."/get_wxapp_site_token.json", "w");
		fwrite($fp, json_encode($datas));
		fclose($fp);
		$this->success('重置成功');
	}
	
	
	public function deleteqrcode(){
		$i=0;
		$list = Db::name('users')->where(array('qrcode1'=>array('neq','')))->order(array('user_id'=>'desc'))->select(); 
		foreach($list as $k=>$v){
			$i++;
			Db::name('users')->where(array('user_id'=>$v['user_id']))->update(array('qrcode'=>'','qrcode1'=>'','qrcode2'=>'','qrcode3'=>'')); 	
		}
		Cache::clear();
		$File = new \File();
		$res = $File->rmFiles($path = 'attachs/poster');
		if($res){
			$this->success('删除【'.$i.'】人的海报并成功删除海报文件');
		}
		$this->success('删除【'.$i.'】人的海报');
	}
	
	public function poster(){
		$intro = $this->delUserPoster();
		$this->success($intro);
	}
	
	
	public function delUserPoster(){
		$res = Db::name('users')->where(array('poster' => array('neq',''),'closed' => 0))->select();
        if($res){
           $i = 0;
           foreach($res as $k => $v) {
			  Db::name('users')->where('user_id',$v['user_id'])->update(array('poster'=>'','poster_media_id'=>''));
			  $i++;
           }
		   $File = new \File();
		   $res = $File->rmFiles($path = 'attachs/poster');
           return '已删除海报人数【'.$i.'】';
        }
		return '暂无会员有海报';
	}
	

	
	//地图调用
    public function maps(){
		$config = Setting::config();
        $lat = input('lat','', 'trim,htmlspecialchars');
        $lng = input('lng','','trim,htmlspecialchars');
        $this->assign('lat', $lat ? $lat : $this->_CONFIG['site']['lat']);
        $this->assign('lng', $lng ? $lng : $this->_CONFIG['site']['lng']);
		$view = ($config['config']['map'] == 1) ? 'maps' : 'amap';
        return $this->fetch($view);
    }
	
	
    public function main(){
		//搜索开始
        $map = array('is_show'=>'1','parent_id'=>array('gt',0));
        $keyword = input('keyword','', 'trim,htmlspecialchars');
        if($keyword){
            $map['menu_name|menu_action'] = array('LIKE', '%' . $keyword . '%');
        }
		
		if($keyword){
			$lists = Db::name('menu')->where($map)->select();
			if(is_array($lists)){
				foreach($lists as $k => $val){
					if(empty($val['menu_action'])){
						unset($lists[$k]);
					}
				}
			}
			$count3 = count($lists);
			$Page = new \Page($count3, 10);
			$show = $Page->show();
			$lists = array_slice($lists, $Page->firstRow, $Page->listRows);
			$this->assign('keyword', $keyword);
			$this->assign('page', $show);
			$this->assign('lists', $lists);
		}
        
		//搜索结束
		
		
        $actions = Db::name('admin_action_logs')->count();
	    $count2 = count($actions);
		$Page2 = new \Page($count2,5);
		$show2 = $Page2->show();
		$action = Db::name('admin_action_logs')->order('log_id desc')->select();
		foreach($action as $k => $val){
			$Admin = Db::name('admin')->where(array('admin_id'=>$v['admin_id']))->find();
          	$action[$k]['admin'] = $Admin;
        }
		$this->assign('page2', $show2);
		$this->assign('action', $action);
		
		$this->assign('warning',$warning = model('Admin')->find($this->_admin['admin_id']));
		
		
		$this->assign('v',date('Y-m-d',time()));
		 
		 
		$bg_time = time() - 86400 * 30;
		$bgtime = strtotime(TODAY);
		$bg_date = date('Y-m-d',$bg_time);
        $end_date = date('Y-m-d',time());
		$this->assign('bg_date', $bg_date);
        $this->assign('end_date', $end_date);
		
		$data = model('Api')->getDbHighcharts($bg_time,time(),$city_id = '0',$id = '0',$db = 'users',$pk = 'user_id');
        $this->assign('data',$data);
		
		$data1 = model('Api')->getDbHighcharts($bg_time,time(),$city_id = '0',$id = '0',$db = 'express_order',$pk = 'id');
        $this->assign('data1',$data1);
		
		$data2 = model('Api')->getDbHighcharts($bg_time,time(),$city_id = '0',$id = '0',$db = 'payment_logs',$pk = 'log_id');
        $this->assign('data2',$data2);
		
		
		$counts['paymentlogs'] =(int)Db::name('payment_logs')->where(array('is_paid'=>'1'))->sum('need_pay');
		$counts['day_paymentlogs'] =(int)Db::name('payment_logs')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime)),'is_paid' => '1'))->sum('need_pay');
		
		$count['money'] =(int)Db::name('users')->sum('money');
		$count['user_cash'] =(int)Db::name('users_cash')->sum('money');
		$count['user_cash_1'] =(int)Db::name('users_cash')->where(array('status'=>'1'))->sum('money');
		$count['user_cash_2'] =(int)Db::name('users_cash')->where(array('status'=>'0'))->sum('money');
		$count['user'] =(int)Db::name('users')->where(array('closed'=>'0'))->count();
		
		
		
		$counts['day_express'] =(int)Db::name('express_order')->where(array('create_time' => array(array('ELT',time()), array('EGT',$bgtime)),'closed'=>0))->count();
		$counts['express'] =(int)Db::name('express_order')->where(array('closed'=>0))->count();
		$counts['users'] = (int) Db::name('users')->count();
		$counts['totay_user'] = (int) Db::name('users')->where(array('reg_time' => array(array('ELT',time()), array('EGT',$bgtime))))->count();
		$counts['user_moblie'] = (int) Db::name('users')->where(array('mobile'=>array('EXP','IS NULL')))->count();
		
		
		$this->getOrderStatus = model('Setting')->getorderStatus();
		//统计数量
		$getOrderStatus = array();
		foreach($this->getOrderStatus as $k2 =>$v2){   
		    $getOrderStatus[$k2]['id'] = $k2; 
		    $getOrderStatus[$k2]['name'] = $v2; 
			$getOrderStatus[$k2]['count'] = (int)Db::name('express_order')->where(array('orderStatus'=>$k2,'closed'=>0))->count();
		}
		$this->assign('getOrderStatus',$getOrderStatus);
		$this->assign('counts', $counts);
		
		$pushcount = (int) Db::name('express_order_push')->where(array('status'=>1))->count();
		$this->assign('pushcount',$pushcount);
		
		$this->assign('push',$push = Db::name('express_order_push')->where(array('status'=>2))->limit(0,10)->order('id desc')->select());
        return $this->fetch();
    }
	
	
	//删除日志
	public function delete($log_id = 0){
        if($log_id = (int) $log_id){
            Db::name('admin_action_logs')->delete($log_id);
            $this->jinMsg('删除日志成功', url('index/main'));
        }else{
            $this->jinMsg('ID不存在');
        }
    }
	
	//批量删除日志
	public function deleteall($log_id = 0){
        if(Db::name('admin_action_logs')->where(array('log_id'=>array('gt',0)))->delete()){
            $this->success('删除全部操作日志成功', url('index/main'));
        }else{
            $this->success('删除失败');
        }
    }
	
   
}