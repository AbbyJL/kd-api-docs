<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;

class Upset extends Base{
	
    public function index() {
        $list = Db::name('uploadset')->order(array('id' =>'desc'))->select();
        $this->assign('list', $list); 
        return $this->fetch();
    }
	
    public function edit($id = 0){
        if($id = (int)$id){
            if(!$detail = Db::name('uploadset')->find($id)){
                $this->error('请选择要编辑的方式');
            }
			
			
            if(request()->post()){
                $data['status'] = (int)($_POST['status']);
        		$data['para'] = json_encode($_POST['para']);
                $data['id'] = $id;
                if(false !== Db::name('uploadset')->update($data)){
					$filename = 'ueconfig.json';
					
					$datajson = $_POST['para'];
					$datajson['status'] = $_POST['status'];
					$datajson['waterurl'] = config_weixin_img($this->_CONFIG['attachs']['water']);
					
					$datajson['sitehost'] = $this->_CONFIG['site']['host'] ? $_POST['para']['sitehost'] : $_POST['para']['sitehost'];
					
					
					$d = json_encode($datajson);
					file_put_contents(APP_PATH.'../public/qiniu_ueditor/php/'.$filename, $d);
				
	
					
                    $this->jinMsg('操作成功', url('upset/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $detail['para'] = json_decode($detail['para'],true);
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的方式');
        }
    }
	

}
