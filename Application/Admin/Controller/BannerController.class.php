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
 * 后台Banner控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class BannerController extends AdminController {

    /**
     * Banner管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $status = I('status');
        if($status){
            $map['status'] = $status;
        }
        else{
            $map['status']  =   array('egt',0);
        }

        $list   = $this->lists('Banner', $map);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = 'Banner信息';
        $this->display();
    }

    /**
     * 新增Banner
     * @author huajie <banhuajie@163.com>
     */
    public function addAction(){
        $this->meta_title = '新增Banner';
        $this->assign('data',null);
        $this->display('add');
    }

    /**
     * 会员状态修改
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('id',0));
        
        $id = is_array($id) ? implode(',',$id) : $id;
        
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] =   array('in',$id);
        
        if($method == 'forbid'){
            $data['status'] = 0;
        }elseif($method == 'resume'){
            $data['status'] = 1;
        }
        M('Banner')->data($data)->where($map)->save();
        $this->success('Banner修改成功！',U('index'));
    }

    public function add(){
        if(IS_POST){
            $title = I('post.title');
            $link = I('post.link');
            $img = I('post.img');
            empty($title) && $this->error('请输入名称');
            $data = array('title' => $title, 'link' => $link,'img_id'=>$img,'status'=>0);

            if(!M('Banner')->add($data)){
                $this->error('Banner添加失败！');
            } else {
                $this->success('Banner添加成功！',U('index'));
            }
        } else {
            $this->meta_title = '新增Banner';
            $this->display();
        }
    }

}
