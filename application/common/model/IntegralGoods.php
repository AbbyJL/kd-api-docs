<?php
namespace app\common\model;
use think\Db;
use think\Cache;


class IntegralGoods extends Base{

    protected $pk = 'goods_id';
    protected $tableName = 'integral_goods';
	
	
	 public  function upload($goods_id,$photos){
        Db::name('integral_goods_photos')->where(array('goods_id'=>$goods_id))->delete();
        foreach($photos as $val){
            Db::name('integral_goods_photos')->insert(array(
                'goods_id' => $goods_id,
                'photo' => htmlspecialchars($val)
            ));
        }
        return true;
    }
    
	public function getOptions($goods_id){
        $options = Db::name('integral_goods_options')->where(array('goods_id'=>$goods_id))->select();
		foreach($options as $k => $val){
			$options[$k]['price'] = round($val['price']/100,2);
		}
		return $options;
    }
	
	
    public function getPics($goods_id){
        $goods_id = (int) $goods_id;
        return Db::name('integral_goods_photos')->where(array('goods_id'=>$goods_id))->select();
    }
	
	
	
	public function getImgs($goods_id,$face_pic=''){
        $goods_id = (int) $goods_id;
        $list =  Db::name('integral_goods_photos')->where(array('goods_id'=>$goods_id))->limit(0,10)->select();
		foreach($list as $key => $val){
			$list[$key]['logo'] = config_weixin_img($val['photo']);
		}
		$list2['goods_id'] = $goods_id;
		$list2['pic_id'] = $goods_id;
		$list2['photo'] = config_weixin_img($val['face_pic']);
		$list2['logo'] = config_weixin_img($face_pic);
		array_unshift($list,$list2);
		return $list;
    }
	
	
	
	public function IntegralGoodsOptions($goods_id,$shop_id,$type){
		$option = input('options/a');
		$options = array();
		foreach($option['name'] as $key => $val){
			$options[] = array(
				'id' => $option['id'][$key],
				'name' => $option['name'][$key],
				'price' => $option['price'][$key]*100,//价格
				'total' => intval($option['total'][$key]),//库存
				'displayorder' => intval($option['displayorder'][$key]),
			);
		}
		if(empty($options)){
			$this->error = '没有设置有效的规格项';
			return false;
		}
		$ids = array();
		foreach($options as $k => $val){
			if($val['name'] && $val['price']){
				$option_id = $val['id'];
				if($option_id > 0){
					$val['shop_id'] = $shop_id;
					$val['goods_id'] = $goods_id;
					Db::name('integral_goods_options')->where(array('id'=>$val['id']))->update($val); 
				}else{
					$val['id'] = $option_id;
					$val['goods_id'] = $goods_id;
					$option_id = Db::name('integral_goods_options')->insertGetId($val); 
				}
				$ids[] = $option_id;
			}
		}
		
		$idss = array();
		foreach($ids as $v){
			if($v){
				$idss[] = $v;
			}
		}
		$ids = @implode(',',$idss);
		if($ids){
			$res = Db::name('integral_goods_options')->where(array('goods_id'=>$goods_id,'id'=>array('not in',$ids)))->select();
			foreach($res as $k =>$vv){
				 Db::name('integral_goods_options')->where(array('id'=>$vv['id']))->delete();//先删除全部规格
			}
		}
		return true;
	}
	
	public function getChildren($id){
        $local = array();
        $data = Db::name('integral_goods_cate')->select();
        foreach($data  as $val){
            if($val['parent_id'] == $id){
                $child = true;
                foreach($data as  $val1){
                    if($val1['parent_id'] == $val['cate_id']){
                        $child = FALSE;
                        $local[]=$val1['cate_id'];
                    }
                }
                if($child){
                    $local[]=$val['cate_id'];
                }
            }         
        }
        return $local;
    }
	
	

	
}

