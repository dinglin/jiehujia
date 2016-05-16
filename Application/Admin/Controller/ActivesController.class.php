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
 * 后台活动控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ActivesController extends AdminController {

    /**
     * 活动管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $title       =   I('title');
        $status      =  isset($_GET['status'])?$_GET['status']:1;
        $m      =  isset($_GET['m'])?$_GET['m']:0;//是否为俱乐部活动 member_id > 0
        $club_id     = I('cid');
        if($status==0){
            $map['status'] = array('eq',0);
        }else{
            $map['status']  =   array('eq',$status);
        }
        if($m){
            $map['member_id']  =   array('gt',0);
        }
        if($club_id){
            $map['club_id']  =   array('eq',$club_id);
        }
        
        if(is_numeric($title)){
            $map['active_id|title']=   array(intval($title),array('like','%'.$title.'%'),'_multi'=>true);
        }elseif($title){
            $map['title']    =   array('like', '%'.(string)$title.'%');
        }
        $list   = $this->lists('Actives', $map);
        int_to_string($list);
        $Club = M('Club')->getField('club_id,club_id,club_name');
        foreach($list as &$li){
            if($li['club_id']){
                $li['club_name'] = $Club[$li['club_id']]['club_name'];
            }
        }
        $this->assign('_list', $list);
        $this->assign('status', $status);
        $this->meta_title = '活动信息';
        $this->display();
    }

    /**
     * 修改昵称初始化
     * @author huajie <banhuajie@163.com>
     */
    public function updateNickname(){
        $nickname = M('Member')->getFieldByUid(UID, 'nickname');
        $this->assign('nickname', $nickname);
        $this->meta_title = '修改昵称';
        $this->display();
    }

    /**
     * 修改昵称提交
     * @author huajie <banhuajie@163.com>
     */
    public function submitNickname(){
        //获取参数
        $nickname = I('post.nickname');
        $password = I('post.password');
        empty($nickname) && $this->error('请输入昵称');
        empty($password) && $this->error('请输入密码');

        //密码验证
        $User   =   new UserApi();
        $uid    =   $User->login(UID, $password, 4);
        ($uid == -2) && $this->error('密码不正确');

        $Member =   D('Member');
        $data   =   $Member->create(array('nickname'=>$nickname));
        if(!$data){
            $this->error($Member->getError());
        }

        $res = $Member->where(array('uid'=>$uid))->save($data);

        if($res){
            $user               =   session('user_auth');
            $user['username']   =   $data['nickname'];
            session('user_auth', $user);
            session('user_auth_sign', data_auth_sign($user));
            $this->success('修改昵称成功！');
        }else{
            $this->error('修改昵称失败！');
        }
    }

    /**
     * 修改密码初始化
     * @author huajie <banhuajie@163.com>
     */
    public function updatePassword(){
        $this->meta_title = '修改密码';
        $this->display();
    }

    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function submitPassword(){
        //获取参数
        $password   =   I('post.old');
        empty($password) && $this->error('请输入原密码');
        $data['password'] = I('post.password');
        empty($data['password']) && $this->error('请输入新密码');
        $repassword = I('post.repassword');
        empty($repassword) && $this->error('请输入确认密码');

        if($data['password'] !== $repassword){
            $this->error('您输入的新密码与确认密码不一致');
        }

        $Api    =   new UserApi();
        $res    =   $Api->updateInfo(UID, $password, $data);
        if($res['status']){
            $this->success('修改密码成功！');
        }else{
            $this->error($res['info']);
        }
    }

    /**
     * 活动活动列表
     * @author huajie <banhuajie@163.com>
     */
    public function action(){
        //获取列表数据
        $Action =   M('Action')->where(array('status'=>array('gt',-1)));
        $list   =   $this->lists($Action);
        int_to_string($list);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->assign('_list', $list);
        $this->meta_title = '活动活动';
        $this->display();
    }

    /**
     * 新增活动
     * @author huajie <banhuajie@163.com>
     */
    public function addAction(){
        $this->meta_title = '新增活动';
        $this->assign('data',null);
        $this->display('editaction');
    }

    /**
     * 编辑活动
     * @author huajie <banhuajie@163.com>
     */
    public function editAction(){
        $id = I('get.id');
        empty($id) && $this->error('参数不能为空！');
        $data = M('Action')->field(true)->find($id);

        $this->assign('data',$data);
        $this->meta_title = '编辑活动';
        $this->display();
    }

    /**
     * 更新活动
     * @author huajie <banhuajie@163.com>
     */
    public function saveAction(){
        $res = D('Action')->update();
        if(!$res){
            $this->error(D('Action')->getError());
        }else{
            $this->success($res['id']?'更新成功！':'新增成功！', Cookie('__forward__'));
        }
    }

    /**
     * 会员状态修改
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('id',0));
        if( in_array(C('USER_ADMINISTRATOR'), $id)){
            $this->error("不允许对超级管理员执行该操作!");
        }
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['active_id'] =   array('in',$id);
        switch ( strtolower($method) ){
            case 'forbid':
                $this->forbid('Actives', $map );
                break;
            case 'resume':
                $this->resume('Actives', $map );
                break;
            case 'delete':
                $this->delete('Actives', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }

    public function add(){
        if(IS_POST){
            $title = I('post.title');
            $guide_id = I('post.guide_id');
            $club_id = I('post.club_id');
            $list_pic = I('post.logo');
            $start_time = strtotime(I('post.start_time'));
            $end_time = strtotime(I('post.end_time'));
            $departure = I('post.departure');
            $destination = I('post.destination');
            $people_limit = I('post.people_limit');
            $price = I('post.price');
            $price_child = I('post.price_child');
            $end_apply = strtotime(I('post.end_apply'));
            $seo_kwords = I('post.seo_kwords');
            $from_url = I('post.from_url');
            $content = I('post.content');
            $province_id = I('post.province_id');
            $add_time = time();
            
            $days = $this->build_days($start_time,$end_time);
            
            $list_pic = $this->get_pic_url($list_pic);
            
            $imgs = get_img_src_from_html($content);
            if(!$list_pic && $imgs){
                $list_pic = $imgs[0];
            }else{
                $imgs[] = $list_pic;
            }
            
            $data = array('title' => $title, 'status' => 1,'active_status'=>0,'hits'=>0,'guide_id'=>$guide_id,
                'club_id'=>$club_id,'list_pic'=>$list_pic,'start_time'=>$start_time,'end_time'=>$end_time,"days"=>$days,
                'departure'=>$departure,'destination'=>$destination,'people_limit'=>$people_limit,'price'=>$price,
                'price_child'=>$price_child,'end_apply'=>$end_apply,'seo_kwords'=>$seo_kwords,'from_url'=>$from_url,
                'add_time'=>$add_time,'province_id'=>$province_id);

            $insert_id = M('Actives')->add($data);
            if(!$insert_id){
                $this->error('活动添加失败！');
            } else {
                //新增imgs
                foreach($imgs as $img){
                    D('Actives')->saveImgs($insert_id,$img);
                }
                
                M('Active_content')->add(array('active_id'=>$insert_id,'content'=>$content));
                $this->success('活动添加成功！',U('index'));
            }
        } else {
            $Club = M('Club')->where("status > 0")->getField('club_id,club_id,club_name');
            $this->assign('club',$Club);
            //俱乐部领队
            $guides = M('Club_user')->getField('cuser_id,cuser_id,nick_name');
            $this->assign('guides',$guides);
            
            //省份
            $provinces = M('Region')->where('parent_id=1')->getField('region_id,region_id,region_name');
            $this->assign('provinces',$provinces);
            
            $this->meta_title = '新增活动';
            $this->display();
        }
    }
    private function get_pic_url($list_pic){
        if($list_pic && is_numeric($list_pic)){
            $pic = M("Picture")->where(array('id'=>intval($list_pic)))->getField("id,path");
            
            $list_pic = $pic[$list_pic];
        }
        return $list_pic;
    }
     /**
     * 编辑活动
     * @author huajie <banhuajie@163.com>
     */
    public function edit(){
        if(IS_POST){
            //获取参数
            $title = I('post.title');
            $guide_id = I('post.guide_id');
            $club_id = I('post.club_id');
            $list_pic = I('post.logo');
            $start_time = strtotime(I('post.start_time'));
            $end_time = strtotime(I('post.end_time'));
            $departure = I('post.departure');
            $destination = I('post.destination');
            $people_limit = I('post.people_limit');
            $price = I('post.price');
            $price_child = I('post.price_child');
            $end_apply = strtotime(I('post.end_apply'));
            $seo_kwords = I('post.seo_kwords');
            $from_url = I('post.from_url');
            $content = I('post.content');
            $province_id = I('post.province_id');
            $id = I('post.id');
            empty($title) && $this->error('请输入名称');
            
            $days = $this->build_days($start_time,$end_time);
            
            $list_pic = $this->get_pic_url($list_pic);
            
            $imgs = get_img_src_from_html($content);
            if(!$list_pic && $imgs){
                $list_pic = $imgs[0];
            }else{
                $imgs[] = $list_pic;
            }
            
            $Actives =   M('Actives');
            $data   =   array('title' => $title, 'guide_id'=>$guide_id,
                'club_id'=>$club_id,'list_pic'=>$list_pic,'start_time'=>$start_time,'end_time'=>$end_time,"days"=>$days,
                'departure'=>$departure,'destination'=>$destination,'people_limit'=>$people_limit,'price'=>$price,
                'price_child'=>$price_child,'end_apply'=>$end_apply,'seo_kwords'=>$seo_kwords,'from_url'=>$from_url,
                'province_id'=>$province_id);
            $res = $Actives->where(array('active_id'=>$id))->save($data);
            
            $Actives_content =   M('Active_content');
            $data   =  array('content'=>$content);
            $Actives_content->where(array('active_id'=>$id))->save($data);

            //新增imgs
            foreach($imgs as $img){
                D('Actives')->saveImgs($id,$img);
            }
            
            if($res){
                $this->success('修改成功！');
            }else{
                $this->error('修改失败！');
            }
        }else{
            //获取左边菜单
            
            $id = I('get.id');
            empty($id) && $this->error('参数不能为空！');
            $data = M('Actives')->where("active_id=$id")->find();
            
            $data_content = M('Active_content')->where("active_id=$id")->find();
            
            $Club = M('Club')->getField('club_id,club_id,club_name');
            $this->assign('club',$Club);
            //俱乐部领队
            $guides = M('Club_user')->getField('cuser_id,cuser_id,nick_name');
            $this->assign('guides',$guides);
            
            //省份
            $provinces = M('Region')->where('parent_id=1')->getField('region_id,region_id,region_name');
            $this->assign('provinces',$provinces);

            $this->assign('data',$data);
            $this->assign('data_content',$data_content);
            $this->meta_title = '编辑活动';
            $this->display();
        }
        
    }
    
    public function copy(){
        //获取左边菜单
            
            $id = I('get.id');
            empty($id) && $this->error('参数不能为空！');
            $data = M('Actives')->where("active_id=$id")->find();
            
            $data_content = M('Active_content')->where("active_id=$id")->find();
            
            $Club = M('Club')->getField('club_id,club_id,club_name');
            $this->assign('club',$Club);
            //俱乐部领队
            $guides = M('Club_user')->getField('cuser_id,cuser_id,nick_name');
            $this->assign('guides',$guides);
            
            //省份
            $provinces = M('Region')->where('parent_id=1')->getField('region_id,region_id,region_name');
            $this->assign('provinces',$provinces);

            $this->assign('data',$data);
            $this->assign('data_content',$data_content);
            $this->meta_title = '复制活动';
            $this->display();
        
    }
    
    
    public function spider(){
    	$data = M('ActiveSpider')->select();
    	$this->assign('data',$data);
    	$this->display();
    }
    private function build_days($start_time,$end_time){
        $days = 0;
        if($start_time && $end_time){
            $time = $end_time - $start_time;
            $one_day = 24 * 60 * 60;
            $days = ceil($time/$one_day);
        }
        return $days;
    }
    private function get_img_src($content){
        $pic_url= "";
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern,$content,$match);
        if($match && $match[1]){
            $pic_url = $match[1][0];
        }
        return $pic_url;
    }
    /**
     * 获取活动注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '活动名长度必须在16个字符以内！'; break;
            case -2:  $error = '活动名被禁止注册！'; break;
            case -3:  $error = '活动名被占用！'; break;
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
