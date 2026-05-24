<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting as SettingModel;
class Weixintmpl extends Base{
	
	
	protected function _initialize(){
        parent::_initialize();
			$this->config  = SettingModel::config();
			$this->curl = new \Curl();
    }
	

    public function index(){
        if($data = input('data/a', false)){
            $on = true;
			
            foreach($data as $item){
                $is = isset($item['tmpl_id']);
                if($is){
                    $item['update_time'] = time();
                }else{
                    $item['create_time'] = time();
                }
				
                if(!Db::name('weixin_tmpl')->update($item)){
                    $this->jinMsg(model('WeixinTmpl')->getError());
                    continue;
                }else{
                    if($is){
                        if(Db::name('weixin_tmpl')->update($item)){
                            $on = false;
                            $this->jinMsg('更新失败或者您未修改数据');
                            continue;
                        }
                    }else{
                        if(Db::name('weixin_tmpl')->insert($item)){
                            $on = false;
                            $this->jinMsg('添加失败');
                            continue;
                        }
                    }
                }
            }
            if($on){
                $this->jinMsg('操作成功', url('Weixintmpl/index'));
            }
        }else{
            $this->assign('list',$list = Db::name('weixin_tmpl')->order(array('tmpl_id desc'))->select());
            return $this->fetch();
        }
    }
	
	
	public function delete($tmpl_id = 0){
        if($tmpl_id){
            Db::name('weixin_tmpl')->where(array('tmpl_id'=>$tmpl_id))->delete();
            $this->jinMsg('删除成功', url('Weixintmpl/index'));
        }else{
            $this->jinMsg('删除失败');
        }
    }
	
	
	

	public function mangeTemplateAuto($type = 1){
		$this->curl = new \Curl();
		$postData['type'] = $type;
		$postData['appid'] = $this->config['wxapp']['appid'];
		$postData['appsecret'] = $this->config['wxapp']['appsecret'];
		$postData['wx_appid'] = trim($this->config['weixin']['appid']);
		$postData['wx_appsecret'] = trim($this->config['weixin']['appsecret']);
		$postData['host'] = trim($this->config['site']['host']);
		$postData['mobile'] = trim($this->config['site']['mobile']);
		$url = getHost().'/api/Weixintmplapi/mangeTemplateAuto';
		$result = $this->curl->post($url,json_encode($postData));
		$result = json_decode($result,true);
		$arr = $result['data'];
		
		if($type==1){
			$delete = Db::name('weixin_tmpl')->where(array('type'=>1))->delete();
			$i = 0;
			if(is_array($arr)){
				foreach($arr as $v){
					if($v['template_id']){
						$i++;
						Db::name('weixin_tmpl')->insert($v);
					}
				}
			}
			$this->jinMsg('已添加'.$i.'条订阅号模板消息', url('Weixintmpl/index'));
		}else{
			$i = 0;
			$delete = Db::name('weixin_tmpl')->where(array('type'=>2))->delete();
			if(is_array($arr)){
				foreach($arr as $v){
					if($v['template_id']){
						$i++;
						Db::name('weixin_tmpl')->insertGetId($v);
					}
				}
			}
			$this->jinMsg('已添加'.$i.'条公众号模板消息，如果添加模板条数为0为授权异常', url('Weixintmpl/index'));
		}
	}
	
	
	
}