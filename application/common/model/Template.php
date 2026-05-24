<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

class Template extends Base{
	
    protected $pk = 'template_id';
    protected $tableName = 'template';
    protected $token = 'template';
	
	
	//模板类型
	public function getType(){
        return array(
            '1' => '网站模板',
            '2' => '商家模板',
            '3' => '会员模板',
        );
    }
	
	
    public function fetchAll(){
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        if(!($data = $cache->get($this->token))) {
            $result = $this->order($this->orderby)->select();
            $data = array();
            foreach ($result as $row){
                $data[$row['theme']] = $row;
            }
            $cache->set($this->token, $data);
        }
        return $data;
    }
	
    public function getDefaultTheme(){
        $data = $this->fetchAll();
        foreach($data as $k => $v){
            if($v['is_default']){
                return $v['theme'];
            }
        }
        return C('DEFAULT_THEME');
    }
	
	
	//获取模板函数，控制器名称，商家ID，类型，函数名字，控制器名字
	public function getTemplate($control,$shop_id,$type = 0,$method){
		if(!$control){
			return false;
		}
		if(!$method){
			return false;
		}
		
		return false;
    }
	
	
}