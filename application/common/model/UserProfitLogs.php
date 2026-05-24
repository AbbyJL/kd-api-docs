<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

use app\common\model\Setting;




class UserProfitLogs extends Base{

    protected $pk = 'log_id';
    protected $tableName = 'user_profit_logs';

	protected function _initialize(){
        parent::_initialize();
		$this->config = model('Setting')->fetchAll2();
    }


	protected $Type = array(
        'goods' => '商城',
		'rank'=>'会员购买等级',
    );

	protected $separate = array(
        1 => '已分成',
        2 => '已取消',
    );

    public function getType(){
        return $this->Type;
    }

    public function getSeparate(){
        return $this->separate;
    }

	//反转数组
	public function get_money_type($type){
		$types = $this->getType();
		$result = array_flip($types);//反转数组
		$types = array_search($type, $result);
		if(!empty($types)){
			return $types;
		}else{
			return false;
		}
        return false;
	}


	protected $_type = array(
		'goods' => '商城',
	);



	//判断分销权限
	public function determinePower($uid){

		$this->config = model('Setting')->fetchAll2();

		$Users = Db::name('users')->find($uid);
		if($this->config['profit']['profit_min_rank_id'] == 0){
			return true;
		}
		$rank = Db::name('user_rank')->find($config['profit']['profit_min_rank_id']);//后台分销配置
		$userRank = Db::name('user_rank')->find($Users['rank_id']);//会员的分销配置
        if($rank){
			//会员的分销配置不等于空还有会员的会员等级积分>= 网站要求等级的积分
            if($userRank && $userRank['integral'] >= $rank['integral']){
                return true;
            }else{
               return false;
            }
        }else{
            return false;
        }
		return true;
	}
	
	


	//新增个人业绩
	public function countUserPrice($order_id,$user_id,$num,$type='2',$info){
	
		$old_num = Db::name('users')->where('user_id',$user_id)->value('count_user_price');
		$new_num = $old_num + $num;
		
		$count = Db::name('users_count_log')->where(array('user_id'=>$user_id,'order_id'=>$order_id,'type'=>$type))->count();
		if(!$count && $num > 0){
			//更新个人业绩
			Db::name('users')->where('user_id',$user_id)->update(array('count_user_price'=>$new_num));
			Db::name('users_count_log')->insert(array(
				'order_id' => $order_id,
				'user_id' => $user_id,
				'old_num' => $old_num,
				'new_num' => $new_num,
				'num' => $num,
				'type' => $type,
				'info' => $info,
				'time' => time(),
				'year' => date('Y',time()),
				'month' => date('Ym',time()),
				'day' => date('Ymd',time()),
				'create_time' => time() 
			));
			return true;
		}
		
	}
	
	//新增团队业绩
	public function teamPrice($order_id,$user_id,$num,$type='3',$info){
	
		//自己的上级
		$users = Db::name('users')->where('user_id',$user_id)->find();
		
		if($users['parent_id']){
			
			//上级
			$fuid = Db::name('users')->where('user_id',$users['parent_id'])->find();
			
			$old_num = Db::name('users')->where('user_id',$users['parent_id'])->value('count_team_price');
			$new_num = $old_num + $num;
			
			$count = Db::name('users_count_log')->where(array('user_id'=>$users['parent_id'],'order_id'=>$order_id,'type'=>$type))->count();
			if(!$count && $num > 0){
				
				//更新个人业绩
				Db::name('users')->where('user_id',$users['parent_id'])->update(array('count_team_price'=>$new_num));
				
				Db::name('users_count_log')->insert(array(
					'order_id' => $order_id,
					'user_id' => $users['parent_id'],
					'old_num' => $old_num,
					'new_num' => $new_num,
					'num' => $num,
					'type' => $type,
					'info' => $info.'团队业绩增加ID'.$users['user_id'].'-'.$users['parent_id'],
					'time' => time(),
					'year' => date('Y',time()),
					'month' => date('Ym',time()),
					'day' => date('Ymd',time()),
					'create_time' => time() 
				));
				
				if($fuid['parent_id']){
					$this->teamPrice($order_id,$fuid['user_id'],$num,$type='3',$info);
				}
			}
		}
		return true;
	}
	
	
	
	//新增团队人数
	public function countTeam($order_id,$user_id,$num,$type='1',$info){
	
		//自己的上级
		$users = Db::name('users')->where('user_id',$user_id)->find();
		
		if($users['parent_id']){
			
			//上级
			$fuid = Db::name('users')->where('user_id',$users['parent_id'])->find();
			
			$old_num = Db::name('users')->where('user_id',$users['parent_id'])->value('count_team');
			$new_num = $old_num + $num;
			
			$count = Db::name('users_count_log')->where(array('user_id'=>$users['parent_id'],'order_id'=>$order_id,'type'=>$type))->count();
			
			if(!$count && $num > 0){
				
				//更新团队人数
				Db::name('users')->where('user_id',$users['parent_id'])->update(array('count_team'=>$new_num));
				
				Db::name('users_count_log')->insert(array(
					'order_id' => $order_id,
					'user_id' => $users['parent_id'],
					'old_num' => $old_num,
					'new_num' => $new_num,
					'num' => $num,
					'type' => $type,
					'info' => $info.'团队人数增加ID'.$users['user_id'].'-'.$users['parent_id'],
					'time' => time(),
					'year' => date('Y',time()),
					'month' => date('Ym',time()),
					'day' => date('Ymd',time()),
					'create_time' => time() 
				));
				
				if($fuid['parent_id']){
					$this->countTeam($order_id,$fuid['user_id'],$num,$type='1',$info);
				}
			}
		}
		return true;
	}
	
	

	//获取下级直推人数【直推】
	public function getUserFuidRankCount($fuid,$rank_id = 0){
		if($rank_id){
			$c = (int)Db::name('users')->where(array('parent_id'=>$fuid,'rank_id'=>$rank_id))->count();
		}else{
			$c = (int)Db::name('users')->where(array('parent_id'=>$fuid))->count();
		}
		return $c;
	}
	
	
	
	
	
	public function getDownline($members,$mid,$level=0){
		$arr=array();
		foreach ($members as $key => $v) {
			if($v['parent_id']==$mid){
				$v['level'] = $level+1;
				$arr[]=$v;
				$arr = array_merge($arr,$this->getDownline($members,$v['user_id'],$level+1));
			}
		}
		return $arr;
	}
	
	
	//递归获取下级人数
	public function getChilds($parent_id,$level){
		static $arr=array(); 
		$data=Db::name('users')->where('parent_id',$parent_id)->select();
		foreach($data as $key => $value){
			$value['level'] = $level;
			$arr[] = $value;
			$this->getChilds($value['user_id'],$level +1);
		}
		return $arr;
	}

	//递归获取下级人数
	public function getChilds2($parent_id,$level,$i=0){
		static $arr=array(); 
		$data=Db::name('users_gongpai')->where('pid',$parent_id)->select();
		//p($data);
		foreach($data as $key => $value){
			$value['level'] = $level;
			$arr[] = $value;
			if($i < 3){
				//p($value['user_id']);
				$this->getChilds2($value['user_id'],$level +1,$i+1);
			}
		}
		return $arr;
	}
	
	public function getUserGongpais($fuid,$level =0){
		
		$members = Db::name('users_gongpai')->where(array('pid'=>$fuid))->select();
		$getChilds = $this->getChilds2($fuid,$level +1);
		//p($getChilds);die;
		//去掉重复
		$getChilds =second_array_unique_bykey($getChilds,'user_id');//去掉重复
		$rest = array();
		foreach($getChilds as $item) {
		  $rest[$item['level']][$item['user_id']] = $item;
		}
		$count['level1'] = (int)count($rest[1]);
		$count['level2'] = (int)count($rest[2]);
		$count['level3'] = (int)count($rest[3]);
		$count['level4'] = (int)count($rest[4]);
		$count['level5'] = (int)count($rest[5]);
		$count['level6'] = (int)count($rest[6]);
		$count['level7'] = (int)count($rest[7]);
		$count['level8'] = (int)count($rest[8]);
		$count['level9'] = (int)count($rest[9]);
		$count['level10'] = (int)count($rest[10]);
		$count['level11'] = (int)count($rest[11]);
		$count['level12'] = (int)count($rest[12]);
		$count['level13'] = (int)count($rest[13]);
		$count['level14'] = (int)count($rest[14]);
		$count['level15'] = (int)count($rest[15]);
		return array('getChilds'=>$getChilds,'count'=>$count);
	}
	
	
	
	

	public function getUserFuids($fuid,$level =0){
		$members = Db::name('users')->where(array('parent_id'=>$fuid))->select();
		$getChilds = $this->getChilds($fuid,$level +1);
		//去掉重复
		$getChilds =second_array_unique_bykey($getChilds,'user_id');//去掉重复
		$rest = array();
		foreach($getChilds as $item) {
		  $rest[$item['level']][$item['user_id']] = $item;
		}
		$count['level1'] = (int)count($rest[1]);
		$count['level2'] = (int)count($rest[2]);
		$count['level3'] = (int)count($rest[3]);
		$count['level4'] = (int)count($rest[4]);
		$count['level5'] = (int)count($rest[5]);
		$count['level6'] = (int)count($rest[6]);
		$count['level7'] = (int)count($rest[7]);
		$count['level8'] = (int)count($rest[8]);
		$count['level9'] = (int)count($rest[9]);
		$count['level10'] = (int)count($rest[10]);
		$count['level11'] = (int)count($rest[11]);
		$count['level12'] = (int)count($rest[12]);
		$count['level13'] = (int)count($rest[13]);
		$count['level14'] = (int)count($rest[14]);
		$count['level15'] = (int)count($rest[15]);
		return array('getChilds'=>$getChilds,'count'=>$count);
	}
	
	//查找下面人数【直推人数】
	public function getFuids($fuid,$rank_id=0){
		$members = Db::name('users')->where(array('parent_id'=>$fuid,'rank_id'=>array('egt',$rank_id)))->select();
		return $members;
	}
	
	
	//查找直推人数
	public function getGongpaiRids($fuid,$type =1){
		if($type ==1){
			$count = (int)Db::name('users_gongpai')->where(array('parent_id'=>$fuid))->count();
		}else{
			$count = (int)Db::name('users_gongpai')->where(array('rid'=>$fuid))->count();
		}
		return $count;
	}
	
	
	//更新自己的等级
	public function updateRank($user_id){
		$users=Db::name('users')->where('user_id',$user_id)->find();
		$old_rank=Db::name('user_rank')->where('rank_id',$fuid['rank_id'])->find();
		
		
		$rank_id =$users['rank_id']+1;
		$rank=Db::name('user_rank')->where('rank_id',$rank_id)->find();
		if(!$rank){
			return '最高等级无需升级';
		}
		$prestige = $rank['prestige']*100;
		
		
		if($users['prestige'] >= $rank['prestige']){
			$data['old_rank_name'] =$old_rank['rank_name'];
			$data['old_rank_id'] =$old_rank['rank_id'];
			$data['new_rank_name'] =$rank['rank_name'];
			$data['new_rank_id'] =$rank['rank_id'];
			$data['type'] =1;
			$data['user_id'] =$user_id;
			$data['info'] ='会员欢乐豆金额达到多少钱';
			$data['price'] =0;
			$data['create_time'] =time();
			
			$log_id = Db::name('user_rank_logs')->insertGetId($data);
			$update2 = Db::name('users')->where(array('user_id'=>$user_id))->update(array('rank_id'=>$rank_id));
		
		}
		
		return $info;
	}
	
	
	
	//根据业绩提升等级
	public function updateAchievementRank($user_id,$levelPrice=0){
		$fuid=Db::name('users')->where('user_id',$user_id)->find();
		$old_rank=Db::name('user_rank')->where('rank_id',$fuid['rank_id'])->find();
		
		$getFuids = $this->getFuids($user_id,$fuid['rank_id']);
		$count = count($getFuids);
		
		$rank_id =$fuid['rank_id']+1;
		$rank=Db::name('user_rank')->where('rank_id',$rank_id)->find();
		if(!$rank){
			return false;
		}
		
		$info = '累积业绩'.round($levelPrice/100,2).'元，达到并同等级下级人数达到'.$count.'人';
		
		//p($levelPrice);
		//p($count);
		//p($rank);die;
		
		if($levelPrice >= $rank['price'] && $count >= $rank['number']){
			
			$data['old_rank_name'] =$old_rank['rank_name'];
			$data['old_rank_id'] =$old_rank['rank_id'];
			$data['new_rank_name'] =$rank['rank_name'];
			$data['new_rank_id'] =$rank['rank_id'];
			$data['type'] =1;
			$data['user_id'] =$user_id;
			$data['info'] =$info;
			$data['price'] =0;
			$data['create_time'] =time();
			$log_id = Db::name('user_rank_logs')->insertGetId($data);
			$update2 = Db::name('users')->where(array('user_id'=>$user_id))->update(array('rank_id'=>$rank_id));
			
			//短信通知会员升级
			model('Sms')->sms_user_rank_update($log_id);
		}
		
		return true;
	}
	
	
	//递归获取公排下级人数
	public function getGongpaiChild($user_id){
		static $arr=array(); 
		$data=Db::name('users_gongpai')->where('rid',$user_id)->select();
		//p($data);
		foreach($data as $key => $value){
			$arr[] = $value;
			if($value['user_id']){
				$this->getGongpaiChild($value['user_id']);
			}
			
		}
		return $arr;
	}


	//获取公排团队人数
	public function getGongpaiUserFuidCount($user_id){
		$getGongpaiChild = $this->getGongpaiChild($user_id);
		//p($getGongpaiChild);die;
		$getGongpaiChild =second_array_unique_bykey($getGongpaiChild,'user_id');//去掉重复
		$getGongpaiChild = count($getGongpaiChild);
		return (int)$getGongpaiChild;
	}
	




	//递归获取下级人数
	public function getChild($user_id,$rank_id = 0){
		static $arr=array(); 
		$data=Db::name('users')->where('parent_id',$user_id)->select();
		//p($data);
		foreach($data as $key => $value){
			$arr[] = $value;
			if($value['user_id']){
				$this->getChild($value['user_id'],$rank_id);
			}
		}
		return $arr;
	}


	//获取团队人数
	public function getUserFuidCount($user_id,$rank_id = 0){
		$getChild = $this->getChild($user_id,$rank_id);
		$getChild =second_array_unique_bykey($getChild,'user_id');//去掉重复
		
		$getChild = $this->filter_by_value($getChild,'rank_id',$rank_id); 
		
		return count($getChild);
	}
	
	
	
	public function filter_by_value ($array, $index, $value){  
        if(is_array($array) && count($array)>0)  { 
           foreach(array_keys($array) as $key){  
                $temp[$key] = $array[$key][$index];  
                if ($temp[$key] == $value){ 
                   $newarray[$key] = $array[$key];  
                }  
           }  
       }  
      return $newarray;  
    }

	//递归报单获取下级业绩
	public function getChildLevelPrice($parent_id){
		static $arr=array();  
		$data=Db::name('user_baodan')->where(array('parent_id'=>$parent_id,'type'=>2,'status'=>1))->select();
		foreach($data as $key => $value){
			$arr[] = $value;
			$this->getChildLevelPrice($value['user_id']);
		}
		return $arr;
	}
	
	//递归【下级订单】获取下级业绩
	public function getChildOrderPrice($parent_id){
		static $arr=array();  
		$data=Db::name('order')->where(array('parent_id'=>$parent_id,'status'=>8,'is_baodan'=>0))->select();
		foreach($data as $key => $value){
			$arr[] = $value;
			$this->getChildOrderPrice($value['user_id']);
		}
		return $arr;
	}
	
	
	//递归自己【订单】获取下级业绩
	public function getChildUserOrderPrice($parent_id){
	    //p($parent_id);die;
		$arr=Db::name('order')->where(array('user_id'=>$parent_id,'status'=>8,'is_baodan'=>0))->select();
		return $arr;
	}
	
	
	//获取下级业绩
	public function getUserLevelPriceCount($fuid,$type=0){
		$members = Db::name('users')->where(array('parent_id'=>$fuid))->select();
		
		//获取报单业绩
		$getChildLevelPrice = $this->getChildLevelPrice($fuid);
		//p($getChildLevelPrice);die;
		$getChildLevelPrice =second_array_unique_bykey($getChildLevelPrice,'id');//去掉重复
		$price = array_sum(array_column($getChildLevelPrice,'price'));
		$price = 0;
		
		
		//获取购物订单业绩
		$getChildOrderPrice = $this->getChildOrderPrice($fuid);
		//p($getChildOrderPrice);die;
		
		$getChildOrderPrice =second_array_unique_bykey($getChildOrderPrice,'order_id');//去掉重复
		$price2 = array_sum(array_column($getChildOrderPrice,'need_pay2'));
		//p($price2);
		
		//获取自己购物订单业绩
		$getChildUserOrderPrice = $this->getChildUserOrderPrice($fuid);
		//p($getChildOrderPrice);die;
		$price3 = array_sum(array_column($getChildUserOrderPrice,'need_pay2'));
		//p($price3);die;
	
		if($type){
			return array('price'=>(int)$price,'price2'=>(int)$price2,'price3'=>(int)$price3);
		}else{
			return (int)$price + (int)$price2+ (int)$price3;
		}
		
	}
	

	
	
	
	//购买等级给上面分成
	public function profitUsersLevel($id,$log_id,$typeName='报单'){
		
		$this->config = model('Setting')->fetchAll2();
		$price1 = $price2 = 0;
		
		$baodan = Db::name('user_baodan')->where(array('id'=>$id))->find();
		
		
		//自己的会员信息
		$users = Db::name('users')->where(array('user_id'=>$baodan['user_id']))->find();
		
		$level_1 = $this->config['profit']['level_1'] ? $this->config['profit']['level_1'] : '32';
		$level_2 = $this->config['profit']['level_2'] ? $this->config['profit']['level_2'] : '15';
		
		//自己的上级1级
		$fuid1 = Db::name('users')->where(array('user_id'=>$users['parent_id']))->find();
		if($fuid1 && $level_1){
			$price1 = ($baodan['price'] * $level_1)/100;
			$data['parent_id'] = $fuid1['parent_id'] ? $fuid1['parent_id'] : 0; //自己的上级
			$data['log_id'] = $log_id;
			$data['user_id'] = $fuid1['user_id'];
			$data['type'] = 2;
			$data['price'] = $price1;
			$data['info'] = $typeName.'一级等级分成';
			$data['status'] = 1;
			$data['create_time'] = time();
			if($id = Db::name('user_baodan')->insertGetId($data)){
			   $addMoney = model('Users')->addMoney($fuid1['user_id'],$price1,$data['info'],1,$id);
			}
		}
		
		//自己的上级2级
		$fuid2 = Db::name('users')->where(array('user_id'=>$fuid1['parent_id']))->find();
		if($fuid2 && $level_2){
			$price2 = ($baodan['price'] * $level_2)/100;
			$data['parent_id'] = $fuid2['parent_id'] ? $fuid2['parent_id'] : 0; //自己的上级
			$data['log_id'] = $log_id;
			$data['user_id'] = $fuid2['user_id'];
			$data['type'] = 2;
			$data['price'] = $price2;
			$data['info'] = $typeName.'二级等级分成';
			$data['status'] = 1;
			$data['create_time'] = time();
			
			if($id = Db::name('user_baodan')->insertGetId($data)){
			   $addMoney = model('Users')->addMoney($fuid2['user_id'],$price2,$data['info'],1,$id);
			}
		}
		return $price1 + $price2;
		
	}	
		

	

	//级差
	public function profitUsers($order_id,$goods_id,$log_id,$need_pay,$type){

		$this->config = model('Setting')->fetchAll2();
		
		if(!$order_id){
			return false;
		}
		$order = Db::name('order')->where(array('order_id'=>$order_id))->find();
		if(!$order['user_id']){
			return false;
		}
		$need_pay = $order['need_pay'];
		
		
	    $users = Db::name('users')->where(array('user_id'=>$order['user_id']))->find();
		if($need_pay <= 0){
			return false;
		}
		if(!$users){
			return false;
		}
		return false;
	}


	


	
	public function getUserId($arr,$uid){//自己封装了一个方法，传入Id就会返回Title;
       $result = '';      
	   foreach($arr as $v){
			if($v['user_id'] == $uid){
				$result = $v;
			}else{
				continue;
			}
		}
		return $result;
	}
	
	//当前一条线
	public function getTreeRanks($uid,$array=array()){
		//自己的上级
        $users=Db::name('users')->where('user_id',$uid)->find();
		$u['user_id']=$users['user_id'];
		$u['parent_id']=$users['parent_id'];
		$u['rank_id']=$users['rank_id'];
		$array[] = $u;
		if($u['parent_id']){
            return $this->getTreeLower($u['user_id'],$u['parent_id'],$array);
        }
        return $array;
    }
	
	
	//查找被分成人的下级
	//$user_id备份成人的会员ID
	//$uid
	//$array
	public function getTreeLower($user_id,$uid,$array=array()){
		//自己的上级
        $users=Db::name('users')->where('user_id',$uid)->find();
		$u['user_id']=$users['user_id'];
		$u['parent_id']=$users['parent_id'];
		$u['rank_id']=$users['rank_id'];
		$array[] = $u;
		if($u['parent_id']){
            return $this->getTreeLower($u['user_id'],$u['parent_id'],$array);
        }
        return $array;
    }
	
	


	public function getLowerUser3($user_id,$rank_id,$num,$uid){
		static $arr=array(); 
		//自己的会员信息
		$u=Db::name('users')->where('user_id',$user_id)->find();
		
		//下级会员信息
		$data=Db::name('users')->where(array('user_id'=>$uid,'closed'=>0))->select();
		
		foreach($data as $key => $value){
			$arr[] = $value;
			if($num < 30 && $value['user_id']){
				$this->getLowerUser3($value['user_id'],$u['rank_id'],$num +1,$data['user_id']);
			}
		}
		return $arr;
	}
	
	//递归寻找自己下级最近的等级
	//$user_id被分成人的会员ID
	//$rank_id被分成人的等级ID
	//$num循环次数
	public function getLowerUser2($user_id,$rank_id,$num,$uid){
		static $arr=array(); 
		//自己的会员信息
		$u=Db::name('users')->where('user_id',$user_id)->find();
		
		//下级会员信息
		$data=Db::name('users')->where(array('parent_id'=>$user_id,'closed'=>0))->select();
		foreach($data as $key => $value){
			$arr[] = $value;
			if($num < 30 && $value['user_id']){
				$this->getLowerUser2($value['user_id'],$u['rank_id'],$num +1,$value['user_id']);
			}
		}
		return $arr;
	}
	
	
	
	


	
}
