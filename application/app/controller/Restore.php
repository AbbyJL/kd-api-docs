<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

use app\common\model\Setting;

class Restore extends Base{

	//万能积分返利接口
	public function integral(){
           $list = Db::name('user_integral_library')->where(array('integral_library_surplus' => array('gt', 0),'closed' => 0))->order(array('library_id' =>'asc'))->select();
           if($list){
                $i = 0;
                foreach($list as $k => $v) {
					if ($v['integral_library_total_success'] >= $v['integral_library_total'] || $v['integral_library_day'] >= $v['integral_library_surplus']){
						unset($lists[$k]);
					}
					$restore_time = time();//返还时间
                    $day_time = strtotime(TODAY) - 60 * 60 * 24;
                    $restore_date = date('Y-m-d', $day_time);
                    $intro = '每日返积分，返利日期：' . $restore_date.'，当前已返还天数:'.($v['integral_library_total_success']+1);
                    $count = Db::name('user_integral_restore')->where(array('library_id' => $v['library_id'], 'restore_date' => $restore_date))->count();
                    if(!$count){
                        if(model('Users')->addIntegralRestore($v['library_id'],$v['user_id'], $v['integral_library_day'], $intro, 0,$restore_date))
						$i++;
                    }
                }
				if($i){
					$this->success('已给'.$i.'个用户返利');
				}else{
					$this->success('今日已经返利完毕或者无可返利的内容');
				}
            }else{
				$this->success('无可返利的内容');
			}
    }


}
