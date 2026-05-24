<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


class Navigation extends Base{
	
    private $create_fields = array('nav_name','nav_name1','ioc','url','title','photo','photo2','status','closed','colour','target','is_new','is_wxapp','state','src','wb_src','xcx_name','appid','title_color','title_color2','orderby');
    private $edit_fields = array('nav_name','nav_name1','ioc','url','title','photo','photo2','status','closed','colour','target','is_new', 'is_wxapp','state','src','wb_src','xcx_name','appid','title_color','title_color2','orderby');
	
	 public function _initialize(){
        parent::_initialize();
		$this->assign('getStatus', $getStatus = model('Navigation')->getStatus());
    }
	
    public function main(){
        return $this->fetch();
    }
	
    public function index(){
		$status = (int)input('status','1','');
		if($status==1){
			$map['status'] = array('in',array(0,1));
		}else{
			$map['status'] = $status;
		}
		$this->assign('status',$status);
        $list = Db::name('navigation')->where($map)->order(array('orderby' =>'asc'))->select();
        $this->assign('list', $list);
        $this->assign('aready', $aready);
        $this->assign('page', $show);
        return $this->fetch();
    }
	
	
    public function create($parent_id = 0,$status=0){
		$status = (int) input('status');
        if(request()->post()){
            $data = $this->checkFields(input('data/a', false), $this->create_fields);
			$data['nav_name'] = htmlspecialchars($data['nav_name']);
			$data['title'] = htmlspecialchars($data['title']);
			$data['ioc'] = htmlspecialchars($data['ioc']);
			$data['url'] = $data['url'];
			$data['photo'] = htmlspecialchars($data['photo']);
			$data['closed'] = (int) $data['closed'];
			$data['colour'] = htmlspecialchars($data['colour']);
			$data['target'] = (int) $data['target'];
			$data['is_new'] = (int) $data['is_new'];
			$data['is_wxapp'] = (int) $data['is_wxapp'];
			$data['state'] = (int) $data['state'];
			$data['src'] = htmlspecialchars($data['src']);
			$data['wb_src'] = htmlspecialchars($data['wb_src']);
			$data['xcx_name'] = htmlspecialchars($data['xcx_name']);
			$data['appid'] = htmlspecialchars($data['appid']);
			$data['orderby'] = (int) $data['orderby'];
            $data['parent_id'] = $parent_id;
			$data['status'] = (int) $data['status'];
            if(Db::name('navigation')->insert($data)){
                model('Navigation')->cleanCache();
                $this->jinMsg('添加成功', url('Navigation/index',array('status'=>$status)));
            }
            $this->jinMsg('操作失败');
        }else{
            $this->assign('parent_id', $parent_id);
			$this->assign('status', $status);
            return $this->fetch();
        }
    }
    
  
  
    public function edit($nav_id = 0,$status = 0){
		$status = input('status');
        if($nav_id = (int) $nav_id){
            if(!($detail = Db::name('navigation')->find($nav_id))){
                $this->error('请选择要编辑的手机底部导航');
            }
            if(request()->post()){
                $data = $this->checkFields(input('data/a', false), $this->edit_fields);
				$data['nav_name'] = htmlspecialchars($data['nav_name']);
				$data['title'] = htmlspecialchars($data['title']);
				$data['status'] = (int) $data['status'];
				$data['ioc'] = htmlspecialchars($data['ioc']);
				$data['url'] = $data['url'];
				$data['photo'] = htmlspecialchars($data['photo']);
				$data['closed'] = (int) $data['closed'];
				$data['colour'] = htmlspecialchars($data['colour']);
				$data['target'] = (int) $data['target'];
				$data['is_new'] = (int) $data['is_new'];
				$data['is_wxapp'] = (int) $data['is_wxapp'];
				$data['state'] = (int) $data['state'];
				$data['src'] = htmlspecialchars($data['src']);
				$data['wb_src'] = htmlspecialchars($data['wb_src']);
				$data['xcx_name'] = htmlspecialchars($data['xcx_name']);
				$data['appid'] = htmlspecialchars($data['appid']);
				$data['orderby'] = (int) $data['orderby'];
                $data['nav_id'] = $nav_id;
				$data['status'] = (int) $data['status'];
                if(false !== Db::name('navigation')->update($data)){
                    $this->jinMsg('操作成功', url('navigation/index',array('status'=>$status)));
                }
                $this->jinMsg('操作失败');
            }else{
				$this->assign('status', $status);
                $this->assign('detail', $detail);
                return $this->fetch();
            }
        }else{
            $this->error('请选择要编辑的商家分类');
        }
    }
   
   
	
	
    public function delete($nav_id = 0,$status = 0){
        if($nav_id = (int) $nav_id){
            $navigation = Db::name('navigation')->select();
            foreach($navigation as $val) {
                if($val['parent_id'] == $nav_id){
                    $this->jinMsg('该菜单下还有其他子菜单');
                }
            }
            Db::name('navigation')->where('nav_id',$nav_id)->delete();
            $this->jinMsg('删除成功！', url('Navigation/index',array('status'=>$status)));
        }else{
            $this->jinMsg('ID不存在');
        }
    }
	
	
    public function update(){
        $orderby = input('orderby/a', false);
		$status = (int) input('status');
        foreach($orderby as $key => $val){
            $data = array('nav_id' => (int) $key, 'orderby' =>(int) $val);
            Db::name('navigation')->update($data);
        }
        $this->jinMsg('更新成功', url('navigation/index',array('status'=>$status)));
    }
	
	
	public function reset($nav_id = 0,$aready = 0){
        $aready = input('aready');
		if(!empty($nav_id)){
			Db::name('navigation')->update(array('nav_id' => $nav_id, 'click' => 0));
        	$this->jinMsg('更新点击量成功', url('navigation/index',array('aready'=>$aready)));
		}else{
			$this->jinMsg('请选择要重置的导航点击量');
		}
    }
	
	
	
}