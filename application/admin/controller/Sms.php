<?php

namespace app\admin\controller;
use think\Db;
use think\Cache;


class Sms extends Base{

    private $create_fields = array('sms_key','sms_explain','sms_tmpl','sms_tmp','sms_tmpl1');
    private $edit_fields = array('sms_key','sms_explain','sms_tmpl','sms_tmp','sms_tmpl1');
	
    public function index(){
        $map = array();
        $count = Db::name('sms')->where($map)->count();
        $Page = new \Page($count,40);
        $show = $Page->show();
        $list = Db::name('sms')->where($map)->order(array('sms_id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
    public function create(){
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['sms_key'] = htmlspecialchars($data['sms_key']);
			if(empty($data['sms_key'])){
				$this->jinMsg('标签不能为空');
			}
			$data['sms_explain'] = htmlspecialchars($data['sms_explain']);
			if (empty($data['sms_explain'])){
				$this->jinMsg('说明不能为空');
			}
			$data['sms_tmpl'] = htmlspecialchars($data['sms_tmpl']);
			if(empty($data['sms_tmpl'])){
				$this->jinMsg('模版不能为空');
			}
            if(Db::name('sms')->insert($data)){
                $this->jinMsg('添加成功', url('sms/index'));
            }
            $this->jinMsg('操作失败');
        }else{
            return $this->fetch();
        }
    }
	
   
	
    public function edit($sms_id = 0){
        if($sms_id = (int) $sms_id){
            if(!($detail = Db::name('sms')->find($sms_id))){
                $this->error('请选择要编辑的短信模版');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['sms_key'] = htmlspecialchars($data['sms_key']);
				if(empty($data['sms_key'])){
					$this->jinMsg('标签不能为空');
				}
				$data['sms_explain'] = htmlspecialchars($data['sms_explain']);
				if(empty($data['sms_explain'])){
					$this->jinMsg('说明不能为空');
				}
				$data['sms_tmpl'] = htmlspecialchars($data['sms_tmpl']);
				if(empty($data['sms_tmpl'])){
					$this->jinMsg('模版不能为空');
				}
		
                $data['sms_id'] = $sms_id;
                if(false !== Db::name('sms')->update($data)){
                    $this->jinMsg('操作成功', url('sms/index'));
                }
                $this->jinMsg('操作失败');
            }else{
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的短信模版');
        }
    }
	
    public function delete2($sms_id = 0){
        if(is_numeric($sms_id) && ($sms_id = (int) $sms_id)){
            Db::name('sms')->where(array('sms_id'=>$sms_id))->delete();
            $this->jinMsg('删除成功', url('sms/index'));
        }else{
            $sms_id = input('sms_id/a', false);
            if(is_array($sms_id)){
                foreach($sms_id as $id){
                    Db::name('sms')->where(array('sms_id'=>$id))->delete();
                }
                $this->jinMsg('批量删除成功', url('sms/index'));
            }
            $this->jinMsg('请选择要删除的短信模版');
        }
    }
	
    public function delete($sms_id = 0){
        if(is_numeric($sms_id) && ($sms_id = (int) $sms_id)){
            Db::name('sms')->update(array('sms_id' => $sms_id, 'is_open' => 0));
            $this->jinMsg('关闭成功！', url('sms/index'));
        }else{
            $sms_id = input('sms_id/a', false);
            if(is_array($sms_id)){
                foreach($sms_id as $id){
                    Db::name('sms')->update(array('sms_id' => $id, 'is_open' => 0));
                }
                $this->jinMsg('关闭成功', url('sms/index'));
            }
            $this->jinMsg('请选择要关闭的短信模版');
        }
    }
	
    public function audit($sms_id = 0){
        if(is_numeric($sms_id) && ($sms_id = (int) $sms_id)){
            Db::name('sms')->update(array('sms_id' => $sms_id, 'is_open' => 1));
            $this->jinMsg('开启成功', url('sms/index'));
        }else{
            $sms_id = input('sms_id/a', false);
            if (is_array($sms_id)) {
                foreach ($sms_id as $id){
                    Db::name('sms')->update(array('sms_id' => $id,'is_open' => 1));
                }
                $this->jinMsg('开启成功！', url('sms/index'));
            }
            $this->jinMsg('请选择要开启的短信模版');
        }
    }
	
	//导入默认数据
	public function daoru($nav_id = 0,$aready = 0){
		$aready = (int)input('aready','','');
		$this->assign('aready',$aready);
		$i=0;
    
		$data[0]['sms_key'] = 'sms_yzm';
		$data[0]['sms_explain'] = '验证码';
		$data[0]['sms_tmpl'] = '【{sitename}】尊敬的用户：您在{sitename}手机认证的验证码是{code}千万别告诉别人';
		$data[0]['sms_tmp'] = '您的验证码为$$，5分钟内有效，请勿泄露。';
	
		$data[1]['sms_key'] = 'sms_code';
		$data[1]['sms_explain'] = '验证码';
		$data[1]['sms_tmpl'] = '【{sitename}】尊敬的用户：您在{sitename}手机认证的验证码是{code}千万别告诉别人';
		$data[1]['sms_tmp'] = '您的验证码为$$，5分钟内有效，请勿泄露。';
	
		$data[2]['sms_key'] = 'send_sms_user_diff_money';
		$data[2]['sms_explain'] = '补差价';
		$data[2]['sms_tmpl'] = '【{sitename}】尊敬的用户{userName}您的快递订单ID{orderId}有待补差价，请登录小程序补差价';
		$data[2]['sms_tmp'] = '尊敬的用户，您的快递订单ID-$$有待补差价，请登录小程序补差价';
		
		$data[3]['sms_key'] = 'sms_user_rank_update';
		$data[3]['sms_explain'] = '会员升级短信通知';
		$data[3]['sms_tmpl'] = '【{sitename}】尊敬的{userName}：您的等级由{oldRankName}成功升级为{newRankName}';
		$data[3]['sms_tmp'] = '尊敬的用户：您的会员等级成功升级为$$';
		
		$data[4]['sms_key'] = 'sms_user_newpwd';
		$data[4]['sms_explain'] = '找回密码';
		$data[4]['sms_tmpl'] = '【{sitename}】您的登录密码被重置成{newpwd}用新密码即可重新登录';
		$data[4]['sms_tmp'] = '您的登录密码被重置成$$用新密码即可重新登录';
		
		$data[5]['sms_key'] = 'send_sms_pay_money';
		$data[5]['sms_explain'] = '待支付运单提醒';
		$data[5]['sms_tmpl'] = '【{sitename}】您的{kuaidi}运单号{deliveryId}需要支付{sumMoneyYuan}元请尽快登陆小程序支付';
		$data[5]['sms_tmp'] = '您的$$运单号$$需要支付$$元请尽快登陆小程序支付';
		
		
		foreach($data as $key => $val){	
			$sms = Db::name('sms')->where(array('sms_key'=>$val['sms_key']))->find();
			if(!$sms){
				$i++;
				Db::name('sms')->insertGetId($val);
			}
        }
        $this->jinMsg('成功导入【'.$i.'】数据', url('sms/index',array('aready'=>$aready)));
    }
}