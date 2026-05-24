<?php
namespace app\common\model;

use think\Db;
use think\Model;
use think\Cache;



class UserRank extends Base{
 	protected $pk = 'rank_id';
    protected $tableName = 'user_rank';
    protected $token = 'user_rank';
	
	
	//正式升级
	public function updateRank($order_id,$need_pay,$code,$log_id,$user_id,$rank_id){
		
		
		if(!$user_id){
			return false;
		}
		
		//返还欢乐豆
		$addPrestige = model('Users')->addPrestige($user_id,$need_pay,'升级送欢乐豆',10);
		
		
		$fuid=Db::name('users')->where('user_id',$user_id)->find();
		$old_rank=Db::name('user_rank')->where('rank_id',$fuid['rank_id'])->find();
		
		$rank_id =$fuid['rank_id']+1;
		$rank=Db::name('user_rank')->where('rank_id',$rank_id)->find();
		if(!$rank){
			return false;
		}
		
		
		if($rank){
			$data['old_rank_name'] =$old_rank['rank_name'];
			$data['old_rank_id'] =$old_rank['rank_id'];
			$data['new_rank_name'] =$rank['rank_name'];
			$data['new_rank_id'] =$rank['rank_id'];
			$data['type'] =1;
			$data['user_id'] =$user_id;
			$data['info'] ='购买等级';
			$data['price'] =0;
			$data['create_time'] =time();
			$log_id = Db::name('user_rank_logs')->insertGetId($data);
			$update2 = Db::name('users')->where(array('user_id'=>$user_id))->update(array('rank_id'=>$rank_id));
			return true;
		}
		
		
		//调用全局设置
		$config = Setting::config();
		$users = Db::name('users')->where(array('user_id'=>$user_id))->find();
		$update_rank_reward = (int)$config['profit']['update_rank_reward'];
		$update_rank_reward_type =  (int)$config['profit']['update_rank_reward_type'];
		
		//给上级直推奖
		if($users['parent_id'] && $update_rank_reward){
			$reward = ($need_pay*$update_rank_reward)/100;
			if($reward && $update_rank_reward_type == 0){
				model('Users')->addPrestige($users['parent_id'],$reward,'直推奖',7);//奖励到欢乐豆
			}
			if($reward && $update_rank_reward_type == 1){
				model('Users')->addMoney($users['parent_id'],$reward,'直推奖',7);//奖励到余额
			}
		}
		
		
		return false;
	}
	
	
}