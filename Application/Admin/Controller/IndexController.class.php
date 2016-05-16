<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class IndexController extends AdminController {

    /**
     * 后台首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        if(UID){
            $this->meta_title = '管理首页';
            $this->get_tasks();
            $this->display();
        } else {
            $this->redirect('Public/login');
        }
    }
    
    private function get_tasks(){
        $today = strtotime(date("Y-m-d"));
        //今天的
        $tasks_today = M("Task")->where(" status in(".TaskController::STATUS_DEFAULT.",".TaskController::STATUS_FINISH.") AND start_time<=".$today." AND end_time>=".$today)->select();
        $tasks_today = $this->do_task($tasks_today);
        $this->assign("today",$tasks_today);
        $w = date("w",$today);
        $w = $w=="0"?"日":$w;
        $this->assign("today_date",date("m月d日 周",$today).$w);
        
        //延期的
        $tasks_delay = M("Task")->where(" status=".TaskController::STATUS_DEFAULT." AND end_time<".$today)->select();
        $tasks_delay = $this->do_task($tasks_delay);
        foreach($tasks_delay as $k=>$v){
            $tasks_delay[$k]['delay_time'] = '<span style="color:white;">已延期：'.$this->maktimes($v['end_time']).'</span>';
        }
        $this->assign("delay",$tasks_delay);
        
        $tomorrow = $today+24*60*60;
        //明天的
        if(date('H')>18 ){
            $tasks_tomorrow = M("Task")->where(" status=".TaskController::STATUS_DEFAULT." AND start_time<=".$tomorrow." AND end_time>=".$tomorrow)->select();
            $tasks_tomorrow = $this->do_task($tasks_tomorrow);
            $this->assign("tomorrow",$tasks_tomorrow);
            $w = date("w",$tomorrow);
            $w = $w=="0"?"日":$w;
            $this->assign("tomorrow_date",date("m月d日 周",$tomorrow).$w);
        }
    }
    
    private function maktimes($time)
  {
   $t=time()-$time;
      $f=array(
        '31536000'=> '年',
        '2592000' => '个月',
        '604800'  => '星期',
        '86400'   => '天',
        '3600'    => '小时',
        '60'      => '分钟',
        '1'       => '秒'
    );
    foreach ($f as $k=>$v){        
        if (0 !=$c=floor($t/(int)$k)){
            return $c.$v.'';
        }
    }
  } 
    
    
    private function do_task($tasks){
        $now_time = time();
        $result = array();
        foreach($tasks as $task){
            $task['people'] = TaskController::divide_people($task['people']);
            //today
            $end_time = $task['end_time']+24*60*60;
            $task['schedule'] = intval(($now_time-$task['start_time'])/($end_time-$task['start_time'])*100);
            if($task['status']==TaskController::STATUS_FINISH){
                $task['schedule'] = 100;
            }
            $task['schedule'] = $task['schedule']>100?100:$task['schedule'];
            
            $task['days'] = intval(($end_time-$task['start_time'])/(24*60*60));
            $result[] = $task;
        }
        return $result;
    }

}
