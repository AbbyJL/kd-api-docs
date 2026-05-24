<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Cache;

use app\common\model\Setting;

class UserSign extends Base{

     protected $pk   = 'user_id';
     protected $tableName =  'user_sign';


     public function getSign($user_id,$city_id,$integral = false){

         $user_id = (int)$user_id;

		 if($integral == 0){
			$data['day'] = 0;
			$data['integral'] = 0;
			$data['is_sign'] = 1;
			return $data;
		 }

         if(!$data = Db::name('user_sign')->where(array('user_id'=>$user_id))->order('sign_id desc')->find()){
             $data = array(
                 'user_id' => $user_id,
				 'city_id' => $city_id,
                 'day' => 0,
                 'last_time' => time() - 86400,
                 'is_first' => 1,
				 'create_time' => time(),
				 'create_ip' => request()->ip(),
             );
             $this->insert($data);
         }

         if($integral!==false){ //返回明日登录积分 及 今天是否登录的状态
             $day=$data['day'] == 0 ? $data['day'] + 2 : $data['day']+1;
             if($day > 1){
                 $integral+=$day; //加上连续登陆的天数
             }

             $data['integral'] = $integral;
             $lastdate = date('Y-m-d',$data['last_time']);

             if($lastdate  == TODAY){
                 $data['is_sign'] = 1;
             }else{
                 $data['is_sign'] = 0;
             }
         }

         return $data;
     }


     public function sign($user_id,$city_id,$integral,$firstintegral = 0){
         $user_id = (int)$user_id;
         $integral = (int) $integral;
		 $config = Setting::config();

         $data = $this->getSign($user_id,$city_id,$integral);

		 //签到过的禁止签到
		 if($res = Db::name('user_sign')->where(array('user_id'=>$user_id,'last_time'=>$last_time))->find()){
			return false;
		 }

         $lastdate = date('Y-m-d',$data['last_time']);


         if($lastdate < TODAY){

			 //隔天了
             if((time() - $data['last_time']) > 86400){
                $data['day']+=1;
             }else{
                $data['day'] =  1;
             }

             if($data['day'] > 1){
                 $integral+=$data['day']; //加上连续登陆的天数
             }


			 $sign =(int)$config['integral']['sign'];
			 if($sign == 0){
				$integral=0;
			 }

             $is_first = false;
             if($data['is_first']){
                 $is_first = true;
                 $data['is_first'] = 0;
             }
			 $data['city_id'] = $city_id;
             $data['last_time'] = strtotime(date("Y-m-d 00:00:00"));
			 $data['create_time'] = time();
        	 $data['create_ip'] = request()->ip();
			 unset($data['sign_id']);

             if(Db::name('user_sign')->insert($data)){

                 $return = $integral;
                if($is_first){
                   model('Users')->addPrestige($user_id,$firstintegral,'首次签到');
                   $return += $firstintegral;
                }
                model('Users')->addIntegral($user_id,$integral,TODAY.'手机签到');
                return $return;
             }
             return false;
         }
         return false;
     }




}
