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
 * 后台领队控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ClubUserController extends AdminController {

    /**
     * 领队管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $nickname       =   I('nick_name');
        $map['status']  =   array('egt',0);
        if(is_numeric($nickname)){
            $map['cuser_id|nick_name']=   array(intval($nickname),array('like','%'.$nickname.'%'),'_multi'=>true);
        }else{
            $map['nick_name']    =   array('like', '%'.(string)$nickname.'%');
        }

        $list   = $this->lists('Club_user', $map);
        int_to_string($list);
        
        $Club = M("Club");
        foreach($list as $k=>$v){
            $list[$k]['club_name']  = $Club->where('club_id='.$v['club_id'])->getField('club_name');
        }
        $this->assign('_list', $list);
        $this->meta_title = '领队信息';
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
     * 领队领队列表
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
        $this->meta_title = '领队领队';
        $this->display();
    }

    /**
     * 新增领队
     * @author huajie <banhuajie@163.com>
     */
    public function addAction(){
        $this->meta_title = '新增领队';
        $this->assign('data',null);
        $this->display('editaction');
    }

    /**
     * 编辑领队
     * @author huajie <banhuajie@163.com>
     */
    public function editAction(){
        $id = I('get.id');
        empty($id) && $this->error('参数不能为空！');
        $data = M('Action')->field(true)->find($id);

        $this->assign('data',$data);
        $this->meta_title = '编辑领队';
        $this->display();
    }

    /**
     * 更新领队
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
        $map['cuser_id'] =   array('in',$id);
        switch ( strtolower($method) ){
            case 'forbid':
                $this->forbid('Club_user', $map );
                break;
            case 'resume':
                $this->resume('Club_user', $map );
                break;
            case 'delete':
                $this->delete('Club_user', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }

    public function add(){
        if(IS_POST){
            $nickname = I('post.nick_name');
            $address = I('post.address');
            $club_id = I('post.club_id');
            $description = I('post.description');
            $mobile = I('post.mobile');
            $logo = I('post.logo');
            $tag = I('post.tag');
            $telphone = I('post.telphone');
            $qq = I('post.qq');
            $xl_wb = I('post.xl_wb');
            $qq_wb = I('post.qq_wb');
            $wx = I('post.wx');
            $user = array('nick_name' => $nickname, 'status' => 1,'club_id'=>$club_id,'address'=>$address,
                'description'=>$description,'mobile'=>$mobile,'logo'=>$logo,'tag'=>$tag,'telphone'=>$telphone,
                'qq'=>$qq,'xl_wb'=>$xl_wb,'qq_wb'=>$qq_wb,'wx'=>$wx);

            if(!M('Club_user')->add($user)){
                $this->error('领队添加失败！');
            } else {
                $this->success('领队添加成功！',U('index'));
            }
        } else {
            $Club = M('Club')->getField('club_id,club_id,club_name');

            $this->assign('club',$Club);

            $this->meta_title = '新增领队';
            $this->display();
        }
    }
    
     /**
     * 编辑领队
     * @author huajie <banhuajie@163.com>
     */
    public function edit(){
        if(IS_POST){
            //获取参数
            $nickname = I('post.nick_name');
            $address = I('post.address');
            $club_id = I('post.club_id');
            $description = I('post.description');
            $mobile = I('post.mobile');
            $logo = I('post.logo');
            $tag = I('post.tag');
            $telphone = I('post.telphone');
            $qq = I('post.qq');
            $xl_wb = I('post.xl_wb');
            $qq_wb = I('post.qq_wb');
            $wx = I('post.wx');
            $id = I('post.id');
            empty($nickname) && $this->error('请输入昵称');

            $Club_user =   D('Club_user');
            $data   =   $Club_user->create(array('nick_name'=>$nickname,'club_id'=>$club_id,'address'=>$address,
                'description'=>$description,'mobile'=>$mobile,'logo'=>$logo,'tag'=>$tag,'telphone'=>$telphone,
                'qq'=>$qq,'xl_wb'=>$xl_wb,'qq_wb'=>$qq_wb,'wx'=>$wx));
            if(!$data){
                $this->error($Club_user->getError());
            }
            $res = $Club_user->where(array('cuser_id'=>$id))->save($data);

            if($res){
                $this->success('修改成功！');
            }else{
                $this->error('修改失败！');
            }
        }else{
            //获取左边菜单
            
            $id = I('get.id');
            empty($id) && $this->error('参数不能为空！');
            $data = M('Club_user')->where("cuser_id=$id")->find();
            
            $Club = M('Club')->getField('club_id,club_id,club_name');

            $this->assign('data',$data);
            $this->assign('club',$Club);
            $this->meta_title = '编辑领队';
            $this->display();
        }
        
    }

    /**
     * 获取领队注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '领队名长度必须在16个字符以内！'; break;
            case -2:  $error = '领队名被禁止注册！'; break;
            case -3:  $error = '领队名被占用！'; break;
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
