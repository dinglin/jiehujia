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
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class TravelNoteController extends AdminController {

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $title       =   I('title');
        $status      =  isset($_GET['status'])?$_GET['status']:0;
        
        if($status==0){
            $map['status'] = array('eq',0);
        }else{
            $map['status']  =   array('eq',$status);
        }
       
        
        if(is_numeric($title)){
            $map['id|title']=   array(intval($title),array('like','%'.$title.'%'),'_multi'=>true);
        }elseif($title){
            $map['title']    =   array('like', '%'.(string)$title.'%');
        }
        $list   = $this->lists('Travel_notes', $map);
        int_to_string($list);
     
        $this->assign('_list', $list);
        $this->assign('status', $status);
        $this->meta_title = '游记信息';
        $this->display();
    }

    

    /**
     * 会员状态修改
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function changeStatus(){
        $id = array_unique((array)I('id',0));
        $status = I('get.status');
        $method = I('get.method');
        if( in_array(C('USER_ADMINISTRATOR'), $id)){
            $this->error("不允许对超级管理员执行该操作!");
        }
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] =   array('in',$id);
        switch ( strtolower($method) ){
            case 'check':
                $this->setTNStatus($status, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    
    private function setTNStatus($status,$map){
        $data['status'] = $status;
        $status = M("Travel_notes")->where($map)->save($data);
        if($status){
            $data['status']  = 1;$data['content'] = '修改成功';
            $this->ajaxReturn($data);
        }else{
            $data['status']  = 0;$data['content'] = '修改失败';
            $this->ajaxReturn($data);
        }
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            default:  $error = '未知错误';
        }
        return $error;
    }

}
