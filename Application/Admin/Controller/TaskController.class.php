<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;

/**
 * 后台任务控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class TaskController extends AdminController {

    const STATUS_DELETE = -1;//删除
    const STATUS_DEFAULT= 1;//进行中
    const STATUS_FINISH = 2;//完成
    const STATUS_DELAY  = 3;//延期-并完成
    
    /**
     * 任务管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $this->display();
    }

    public function add(){
        if(IS_POST){
            $people = I('post.people');
            $name = I('post.name');
            $description = I('post.description');
            $start_time = I('post.start_time');
            $end_time = I('post.end_time');
            
            empty($name) && $this->error('请输入名称');
            
            $data = array('people' => implode(',',$people), 'status' => self::STATUS_DEFAULT,'name'=>$name,
                'description'=>$description,'start_time'=>strtotime($start_time),'end_time'=>strtotime($end_time));
            $id = I('post.id');
            if($id){
                if(!M('Task')->data($data)->where(array("id"=>$id))->save()){
                    $this->error('任务修改失败！');
                } else {
                    $this->success('任务修改成功！',U('Index/index'));
                }
            }else{
                if(!M('Task')->add($data)){
                    $this->error('任务添加失败！');
                } else {
                    $this->success('任务添加成功！',U('Index/index'));
                }
            }
            
        } else {
            $id = I('id');
            $people_task = array();
            if($id){
                $task = M("Task")->where(array('id'=>$id))->find();
                $people_task = explode(",", $task['people']);
                $task['start_time']=date("Y-m-d",$task['start_time']);
                $task['end_time']=date("Y-m-d",$task['end_time']);
                $this->assign("task",$task);
            }
            $people = self::get_popele();
            $people_data = array();
            foreach ($people as $key=>$p){
                $_tp = array("id"=>$key,"name"=>$p,"select"=>0);
                if(in_array($key, $people_task)){
                    $_tp['select'] = 1;
                }
                $people_data[] = $_tp;
            }
            $this->assign("people",$people_data);
            $this->display();
        }
    }
    public function finish(){
        $id = I('id');
        if($id){
            M("Task")->data(array("status"=>self::STATUS_FINISH))->where(array('id'=>$id))->save();
            $this->success('任务完成');
        }else{
            $this->error('参数错误');
        }
    }
    public function finish_delay(){
        if(IS_POST){
            $delay_desc = I('post.delay_desc');
            $data = array('delay_desc' => $delay_desc, 'status' => self::STATUS_DELAY,'real_end_time'=>time());
            $id = I('post.id');
            if($id){
                if(!M('Task')->data($data)->where(array("id"=>$id))->save()){
                    $this->error('任务完成失败！');
                } else {
                    $this->success('任务完成成功！',U('Index/index'));
                }
            }
    
        } else {
            $id = I('id');
            $task = M("Task")->where(array('id'=>$id))->find();
            $this->assign("task",$task);
            $this->display();
        }
    }
    public static function get_popele(){
        return array(
                1=>"江亮",
                2=>"沈春胡",
                3=>"丁林",
                4=>"林冬凌",
                5=>"杨玉轩"
        );
    }

    public static function divide_people($people){
        $peoples = self::get_popele();
        $people = explode(",",$people);
        $str_people = array();
        foreach($people as $po){
            $str_people[] = $peoples[$po];
        }
        return implode(",", $str_people);
    }
}
