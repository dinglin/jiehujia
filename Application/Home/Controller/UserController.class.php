<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 * 用户类型，0=老人，1=子女，2=机构人员，3=系统管
 */
class UserController extends HomeController {
    
    protected function _initialize(){
        parent::_initialize();
        $user_session = session('user_auth');
        if(!empty($user_session)){
            $this->assign('group_type',$user_session['group_type']);
        }
    }

	/* 用户中心首页 */
	public function index(){
            if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
        $user_session = session('user_auth');
        $nhadmins = $user_session["nh_admins"];
        if($nhadmins){
            $ngs = M("Nursinghome")->where("id in(".implode(",", array_keys($nhadmins)).")")->select();
            $this->assign("ngs",$ngs);
        }
                //menu显示
        $this->assign('nav', 'index');
        $this->assign("PAGE_TITLE",'会员首页-会员中心');
		$this->display("index-new");
	}

	/* 注册页面 */
	public function register($username = '', $password = '', $repassword = '', $email = '', $verify = ''){
        if(!C('USER_ALLOW_REGISTER')){
            $this->error('注册已关闭');
        }
		if(IS_POST){ //注册用户
			/* 检测验证码 */
			if(!check_verify($verify)){
				$this->error('验证码输入错误！');
			}

			/* 检测密码 */
			if($password != $repassword){
				$this->error('密码和重复密码不一致！');
			}			

			/* 调用注册接口注册用户 */
            $User = new UserApi;
			$uid = $User->register($username, $password, $email);
			if(0 < $uid){ //注册成功
				//TODO: 发送验证邮件
				$this->success('注册成功！',U('login'));
			} else { //注册失败，显示错误信息
				$this->error($this->showRegError($uid));
			}

		} else { //显示注册表单
                    $this->assign("PAGE_TITLE",'会员注册-会员中心');
			$this->display();
		}
	}

	/* 登录页面 */
	public function login($username = '', $password = '', $verify = ''){
		if(IS_POST){ //登录验证
			/* 检测验证码 */
		    if($verify != "index"){
		        if(!check_verify($verify)){
		            $this->error('验证码输入错误！');
		        }
		    }

			/* 调用UC登录接口登录 */
			$user = new UserApi;
			$uid = $user->login($username, $password);
			if(0 < $uid){ //UC登录成功
				/* 登录用户 */
				$Member = D('Member');
				if($Member->login($uid)){ //登录用户
					//TODO:跳转到登录前页面

					$this->success('登录成功！',U('Home/User/index'));
				} else {
					$this->error($Member->getError());
				}

			} else { //登录失败
				switch($uid) {
					case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
					case -2: $error = '密码错误！'; break;
					default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
				}
				$this->error($error);
			}

		} else { //显示登录表单
            $this->assign("PAGE_TITLE",'会员登陆-会员中心');
			$this->display();
		}
	}

	/* 退出登录 */
	public function logout(){
		if(is_login()){
			D('Member')->logout();
			$this->success('退出成功！', U('User/login'));
		} else {
			$this->redirect('User/login');
		}
	}

	/* 验证码，用于登录和注册 */
	public function verify(){
		$verify = new \Think\Verify();
		$verify->entry(1);
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


    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function changepwd(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
        if ( IS_POST ) {
            //获取参数
            $uid        =   is_login();
            $password   =   I('post.old');
            $repassword = I('post.repassword');
            $data['password'] = I('post.password');
            empty($password) && $this->error('请输入原密码');
            empty($data['password']) && $this->error('请输入新密码');
            empty($repassword) && $this->error('请输入确认密码');

            if($data['password'] !== $repassword){
                $this->error('您输入的新密码与确认密码不一致');
            }

            $Api = new UserApi();
            $res = $Api->updateInfo($uid, $password, $data);
            if($res['status']){
                $this->success('修改密码成功！');
            }else{
                $this->error($res['info']);
            }
        }else{
            //menu显示
            $this->assign('nav', 'changepwd');
            $this->assign("PAGE_TITLE",'修改密码-会员中心');
            
            $this->assign('is_ext_login_default', $this->is_ext_login_default());
            
            $this->display();
        }
    }
    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function profile(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        if ( IS_POST ) {
            //获取参数
            $uid        =   is_login();
            $nickname   =   I('post.nickname');
            $phone      = I('post.phone');
            empty($nickname) && $this->error('请输入昵称');
            empty($phone) && $this->error('请输入手机号');
            if(!preg_match("/^13[0-9]{1}[0-9]{8}$|15[01289]{1}[0-9]{8}$|189[0-9]{8}$/",$phone)){
              $this->error('请输入正确的手机号');
            }
            $data = array();
            $data['uid'] = $uid;
            $data['nickname'] = $nickname;
            $data['phone'] = $phone;
            $data['sex'] = I('post.sex');
            $data['birthday'] = I('post.birthday');
            $Api = new UserApi();
            $res = $Api->update_profile($data);
            $this->success('修改个人信息成功！');
        }else{
            //menu显示
            $this->assign('nav', 'profile');
            $uid        =   is_login();
            $profile = M("Member")->where(array("uid"=>$uid))->find();
            $profile['birthday'] = strtotime($profile['birthday'])>0?$profile['birthday']:date("Y-m-d");
            $this->assign("user",$profile);
            $this->assign("PAGE_TITLE",'个人信息-会员中心');
    
            $this->assign('is_ext_login_default', $this->is_ext_login_default());
    
            $this->display();
        }
    }
    
    /**
     * 判断是否是第三方登录，默认用户名和密码
     * @return boolean
     */
    private function is_ext_login_default(){
        $result = false;
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        $user_info = M("UcenterMember")->where(array('id'=>$member_id))->find();
        if($user_info){
            $username = $user_info['username'];
            $password = $user_info['password'];
            $open_id = $user_info['open_id'];
            $ext_login = $user_info['ext_login'];
            
            $open_id = md5($open_id);
            $open_id = substr($open_id, 0,16);
            if($ext_login && $open_id==$username){
                $result = true;
            }
        }
        return $result;
    }
    public function actives(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        
        //$title       =   I('title');
        //$club_id     = I('cid');
     
        $map['member_id']  =   $member_id;
        
//        if($member_id){
//            $map['club_id']  =   array('eq',$member_id);
//        }
        /* if(is_numeric($title)){
            $map['active_id|title']=   array(intval($title),array('like','%'.$title.'%'),'_multi'=>true);
        }elseif($title){
            $map['title']    =   array('like', '%'.(string)$title.'%');
        } */
        $list   = $this->lists('Actives', $map);
        /* $Club = M('Club')->getField('club_id,club_id,club_name');
        foreach($list as &$li){
            if($li['club_id']){
                $li['club_name'] = $Club[$li['club_id']]['club_name'];
            }
        } */
        $this->assign('_list', $list);
        //menu显示
        $this->assign('nav', 'actives');
        $this->assign("PAGE_TITLE",'活动列表-会员中心');
        $this->meta_title = '活动信息';
        $this->display();
    }
    
    public function addactive(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
            
        if(IS_POST){
            $title = I('post.title');
            $guide_id = I('post.guide_id');
            $club_id = I('post.club_id');
            $list_pic = I('post.logo');
            if(is_numeric($list_pic)){
                $list_pic = get_cover_file($list_pic,'savename');
            }
            
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
            empty($title) && $this->error('请输入活动名称');
            
            $data = array('title' => $title, 'status' => 0,'active_status'=>0,'hits'=>0,'guide_id'=>$guide_id,
                'club_id'=>$club_id,'list_pic'=>$list_pic,'start_time'=>$start_time,'end_time'=>$end_time,
                'departure'=>$departure,'destination'=>$destination,'people_limit'=>$people_limit,'price'=>$price,
                'price_child'=>$price_child,'end_apply'=>$end_apply,'seo_kwords'=>$seo_kwords,'from_url'=>$from_url,
                'add_time'=>$add_time,'province_id'=>$province_id,'member_id'=>$member_id);

            $insert_id = M('Actives')->add($data);
            if(!$insert_id){
                $this->error('活动添加失败！');
            } else {
                
                M('Active_content')->add(array('active_id'=>$insert_id,'content'=>$content));
                
                $imgs = get_img_src_from_html($content);
                $imgs[] = $list_pic;
                //新增imgs
                foreach($imgs as $img){
                    D('Actives')->saveImgs($insert_id,$img);
                }
                
                
                $this->success('活动添加成功！',U('actives'));
            }
        } else {
            $Club = M('Club')->where("member_id =$member_id ")->getField('club_id,club_id,club_name');
            $this->assign('club',$Club);
            //俱乐部领队
            $guides = M('Club_user')->where("member_id =$member_id ")->getField('cuser_id,cuser_id,nick_name');
            $this->assign('guides',$guides);
            
            //省份
            $provinces = M('Region')->where('parent_id=1')->getField('region_id,region_id,region_name');
            $this->assign('provinces',$provinces);
            
            //menu显示
            $this->assign('nav', 'actives');
            $this->meta_title = '新增活动';
            $this->assign("PAGE_TITLE",'新增活动-会员中心');
            $this->display();
        }
    }
    
    public function editactive(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        
          if(IS_POST){
            //获取参数
            $title = I('post.title');
            $guide_id = I('post.guide_id');
            $club_id = I('post.club_id');
            $list_pic = I('post.logo');
            if(is_numeric($list_pic)){
                $list_pic = get_cover_file($list_pic,'savename');
            }
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
            empty($title) && $this->error('请输入活动名称');

            $Actives =   M('Actives');
            $data   =   array('title' => $title, 'status' => 0,'guide_id'=>$guide_id,
                'club_id'=>$club_id,'list_pic'=>$list_pic,'start_time'=>$start_time,'end_time'=>$end_time,
                'departure'=>$departure,'destination'=>$destination,'people_limit'=>$people_limit,'price'=>$price,
                'price_child'=>$price_child,'end_apply'=>$end_apply,'seo_kwords'=>$seo_kwords,'from_url'=>$from_url,
                'province_id'=>$province_id,'member_id'=>$member_id);
            $res = $Actives->where(array('active_id'=>$id))->save($data);
            
            $Actives_content =   M('Active_content');
            $data   =  array('content'=>$content);
            $Actives_content->where(array('active_id'=>$id))->save($data);

            $imgs = get_img_src_from_html($content);
            $imgs[] = $list_pic;
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
            $data = M('Actives')->where("active_id=$id and member_id=$member_id")->find();
            
            $data_content = M('Active_content')->where("active_id=$id")->find();
            
            $Club = M('Club')->where("member_id =$member_id ")->getField('club_id,club_id,club_name');
            $this->assign('club',$Club);
            //俱乐部领队
            $guides = M('Club_user')->where("member_id =$member_id ")->getField('cuser_id,cuser_id,nick_name');
            $this->assign('guides',$guides);
            
            //省份
            $provinces = M('Region')->where('parent_id=1')->getField('region_id,region_id,region_name');
            $this->assign('provinces',$provinces);

            $this->assign('data',$data);
            $this->assign('data_content',$data_content);
            //menu显示
            $this->assign('nav', 'actives');
            $this->meta_title = '编辑活动';
            $this->assign("PAGE_TITLE",'编辑活动-会员中心');
            $this->display();
        }
    }


    public function clubs(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        
        $club_name       =   I('club_name');
        $map['member_id']  =  $member_id;
        if(is_numeric($club_name)){
            $map['club_id|club_name']=   array(intval($club_name),array('like','%'.$club_name.'%'),'_multi'=>true);
        }elseif($club_name){
            $map['club_name']    =   array('like', '%'.(string)$club_name.'%');
        }
        $list   = $this->lists('Club', $map);
        $this->assign('_list', $list);
        //menu显示
        $this->assign('nav', 'clubs');
        $this->meta_title = '俱乐部列表';
        $this->assign("PAGE_TITLE",'俱乐部列表-会员中心');
        
        $this->display();
    }
    
    public function editclub(){
    if ( !is_login() ) {
        $this->error( '您还没有登陆',U('User/login') );
    }
        if ( IS_POST ) {
            //获取参数
            $club_name = I('post.club_name');
            $address = I('post.address');
            $description = I('post.description');
            $mobile = I('post.mobile');
            $logo = I('post.logo');
            $tag = I('post.tag');
            $telphone = I('post.telphone');
            $qq = I('post.qq');
            $xl_wb = I('post.xl_wb');
            $qq_wb = I('post.qq_wb');
            $wx = I('post.wx');
            $other = I('post.other');
            $disclaimer = I('post.disclaimer');
            $pay_before = I('post.pay_before');
            $pay_back = I('post.pay_back');
            $id = I('post.id');
            empty($club_name) && $this->error('请输入俱乐部名称');

            $Club =   M('Club');
            $data   =   $Club->create(array('club_name' => $club_name, 'status' => 1,'address'=>$address,
                'description'=>$description,'mobile'=>$mobile,'logo'=>$logo,'tag'=>$tag,'telphone'=>$telphone,
                'qq'=>$qq,'xl_wb'=>$xl_wb,'qq_wb'=>$qq_wb,'wx'=>$wx,'other'=>$other,'disclaimer'=>$disclaimer,
                'pay_before'=>$pay_before,'pay_back'=>$pay_back));
            if(!$data){
                $this->error($Club->getError());
            }
            $res = $Club->where(array('club_id'=>$id))->save($data);

            if($res){
                $this->success('修改成功！');
            }else{
                $this->error('修改失败！');
            }
        }else{
            $id = I('get.id');
            $user = session('user_auth');
            $member_id = empty($user)?0:$user['uid'];
            empty($id) && $this->error('参数不能为空！');
            $data = M('Club')->where("club_id=$id AND member_id=".$member_id)->find();
            if(empty($data)){
                $this->error('参数错误',U("User/index"));
            }
            $this->assign('data',$data);
            //menu显示
            $this->assign('nav', 'clubs');
            $this->meta_title = '编辑俱乐部';
            $this->assign("PAGE_TITLE",'编辑俱乐部-会员中心');
            $this->display();
        }
    }


    public function addclub(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        if ( IS_POST ) {
            $club_name = I('post.club_name');
            $address = I('post.address');
            $description = I('post.description');
            $mobile = I('post.mobile');
            $logo = I('post.logo');
            $tag = I('post.tag');
            $telphone = I('post.telphone');
            $qq = I('post.qq');
            $xl_wb = I('post.xl_wb');
            $qq_wb = I('post.qq_wb');
            $wx = I('post.wx');
            $other = I('post.other');
            $disclaimer = I('post.disclaimer');
            $pay_before = I('post.pay_before');
            $pay_back = I('post.pay_back');
            $user = session('user_auth');
            $member_id = empty($user)?0:$user['uid'];
            empty($club_name) && $this->error('请输入俱乐部名称');
            $data = array('club_name' => $club_name, 'status' => 1,'address'=>$address,
                'description'=>$description,'mobile'=>$mobile,'logo'=>$logo,'tag'=>$tag,'telphone'=>$telphone,
                'qq'=>$qq,'xl_wb'=>$xl_wb,'qq_wb'=>$qq_wb,'wx'=>$wx,'other'=>$other,'disclaimer'=>$disclaimer,
                'pay_before'=>$pay_before,'pay_back'=>$pay_back,'member_id'=>$member_id);

            if(!M('Club')->add($data)){
                $this->error('俱乐部添加失败！');
            } else {
                $this->success('俱乐部添加成功！',U('clubs'));
            }
        }else{
            //menu显示
            $this->assign('nav', 'clubs');
            $this->assign("PAGE_TITLE",'添加俱乐部-会员中心');
            $this->display();
        }
    }
    
    public function clubusers(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        
        $nickname       =   I('nick_name');
        $map['member_id']  =  $member_id;
        if(is_numeric($nickname)){
            $map['cuser_id|nick_name']=   array(intval($nickname),array('like','%'.$nickname.'%'),'_multi'=>true);
        }else{
            $map['nick_name']    =   array('like', '%'.(string)$nickname.'%');
        }

        $list   = $this->lists('Club_user', $map);
        
        $Club = M("Club");
        foreach($list as $k=>$v){
            $list[$k]['club_name']  = $Club->where('club_id='.$v['club_id'])->getField('club_name');
        }
        $this->assign('_list', $list);
        //menu显示
        $this->assign('nav', 'clubusers');
        $this->meta_title = '领队列表';
        $this->assign("PAGE_TITLE",'领队列表-会员中心');
        $this->display();
    }

    public function addclubuser(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        
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
            
            empty($nickname) && $this->error('请输入领队名称');
            
            $user = array('nick_name' => $nickname, 'status' => 1,'club_id'=>$club_id,'address'=>$address,
                'description'=>$description,'mobile'=>$mobile,'logo'=>$logo,'tag'=>$tag,'telphone'=>$telphone,
                'qq'=>$qq,'xl_wb'=>$xl_wb,'qq_wb'=>$qq_wb,'wx'=>$wx,'member_id'=>$member_id);

            if(!M('Club_user')->add($user)){
                $this->error('领队添加失败！');
            } else {
                $this->success('领队添加成功！',U('clubusers'));
            }
        } else {
            $Club = M('Club')->where(array('member_id'=>$member_id))->getField('club_id,club_id,club_name');

            $this->assign('club',$Club);

            //menu显示
            $this->assign('nav', 'clubusers');
            $this->meta_title = '新增领队';
            $this->assign("PAGE_TITLE",'新增领队-会员中心');
            $this->display();
        }
    }
    
    public function editclubuser(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        
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
            
            empty($nickname) && $this->error('请输入领队名称');

            $Club_user =   D('Club_user');
            $data   =   $Club_user->create(array('nick_name'=>$nickname,'club_id'=>$club_id,'address'=>$address,
                'description'=>$description,'mobile'=>$mobile,'logo'=>$logo,'tag'=>$tag,'telphone'=>$telphone,
                'qq'=>$qq,'xl_wb'=>$xl_wb,'qq_wb'=>$qq_wb,'wx'=>$wx,'member_id'=>$member_id));
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
            if(empty($data)){
                $this->error('参数错误！',U("User/index"));
            }
            $Club = M('Club')->where(array('member_id'=>$member_id,"club_id"=>$data['club_id']))->getField('club_id,club_id,club_name');
            if(empty($Club)){
                $this->error('参数错误！',U("User/index"));
            }
            $this->assign('data',$data);
            $this->assign('club',$Club);
            //menu显示
            $this->assign('nav', 'clubusers');
            $this->meta_title = '编辑领队';
            $this->assign("PAGE_TITLE",'编辑领队-会员中心');
            $this->display();
        }
        
    }
    
    public function addOrder() {
        $active_id = I('post.active_id');
        $number = I('post.number');
        $mobile = I('post.mobile');
        if(!is_numeric($active_id)){
            $data['status']  = 0;$data['err'] = 400;$this->ajaxReturn($data);
        }
        if(!is_numeric($number)){
            $data['status']  = 0;$data['err'] = 401;$this->ajaxReturn($data);
        }
        if ( !is_login() ) {
            $data['status']  = 0;$data['err'] = 403;$this->ajaxReturn($data);
        }
        $user = session('user_auth');
        $user_id = empty($user)?0:$user['uid'];
        
        $active = M('Actives')->where("active_id=$active_id")->find();
        
        //检查是否存在该活动未付款订单
        $Order_info = M('Order_info');
        $temp_order = $Order_info->join("left join __ORDER_GOODS__ on __ORDER_INFO__.order_id = __ORDER_GOODS__.order_id ")->
                where("goods_id='$active_id' and user_id='$user_id' and order_status=0 and pay_status=0 ")->select();
        if(!empty($temp_order)){
            $data['status']  = 0;$data['err'] = 404;$this->ajaxReturn($data);
        }
        
        //生成订单数据
        $order = array();
        $order_sn = 'HW'.time().rand(1000, 9999);
        $order['order_sn'] = $order_sn;
        $order['user_id'] = $user_id;
        $order['order_status'] = 0;
        $order['pay_status'] = 0;
        $order['goods_amount'] = $active['price']*intval($number);
        $order['order_amount'] = $active['price']*intval($number);
        $order['mobile'] = $mobile;
        $order['add_time'] = time();
        
        $order_id = $Order_info->add($order);
        
        if($order_id>0){
            $order_goods = array();
            $order_goods['order_id'] = $order_id;
            $order_goods['goods_id'] = $active_id;
            $order_goods['goods_name'] = $active['title'];
            $order_goods['goods_sn'] = $order_sn;
            $order_goods['goods_number'] = $number;
            $order_goods['market_price'] = $active['price'];
            $order_goods['goods_price'] = $active['price'];
            $Order_goods = M('Order_goods');
            $Order_goods->add($order_goods);
            $data['status']  = 1;$data['content'] = array('order_sn'=>$order_sn);$this->ajaxReturn($data);
        }else{
            $data['status']  = 0;$data['err'] = 405;$this->ajaxReturn($data);
        }
  
    }
    
    public function orderlist(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        
        $map['user_id']  =  $member_id;
      
        $list   = $this->lists('Order_info', $map);
        foreach ($list as $key => $value) {
            $list[$key]['order_status_txt'] = $this->get_order_status($value);
            $goods_list = M('Order_goods')->where(array('order_id'=>$value['order_id']))->select();
            $list[$key]['goods_list'] = $goods_list;
        }
        $this->assign('_list', $list);
        //menu显示
        $this->assign('nav', 'order_list');
        $this->meta_title = '订单列表';
        $this->assign("PAGE_TITLE",'订单列表-会员中心');
        
        $this->display();
    }
    
    public function delOrder(){
        $order_sn = I('post.order_sn');
        if(empty($order_sn)){
            $data['status']  = 0;$data['err'] = 500;$this->ajaxReturn($data);
        }
        if ( !is_login() ) {
            $data['status']  = 0;$data['err'] = 501;$this->ajaxReturn($data);
        }
        $user = session('user_auth');
        $user_id = empty($user)?0:$user['uid'];
        
        $Order_info = M('Order_info')->where(array('order_sn'=>$order_sn,'user_id'=>$user_id))->find();
        
        if(!empty($Order_info)){
            $order_id = $Order_info['order_id'];
            M('Order_info')->where(array('order_id'=>$order_id,'user_id'=>$user_id))->delete();
            M('Order_goods')->where(array('order_id'=>$order_id))->delete();
            $data['status']  = 1;$data['content'] = array('order_sn'=>$order_sn);$this->ajaxReturn($data);
        }else{
            $data['status']  = 0;$data['err'] = 502;$this->ajaxReturn($data);
        }
    }
    //查看活动的订单
    public function activeorders(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $active_id = I('id');
        $list = array();
        $applynum = 0;
        $paynum   = 0;
        if ( is_login() ) {
            $user = session('user_auth');
            $club_id = empty($user)?0:$user['uid'];
            
            $active_info = M("Actives")->where(array("active_id"=>$active_id,"club_id"=>$club_id))->find();
            
            if($active_info){
                $user_ids = array();
                $Order_info = M('Order_info');
                $temp_order = $Order_info->join("left join __ORDER_GOODS__ on __ORDER_INFO__.order_id = __ORDER_GOODS__.order_id ")->where("goods_id='$active_id' and order_status in(0,1) ")->select();
                $applynum = count($temp_order);
                foreach ($temp_order as $value) {
                    if($value['pay_status'] == 2){
                        $user_ids[$value['user_id']][] = $value;
                        $paynum ++;
                    }
                }
                $user_id = array_keys($user_ids);
                $users = M('Member')->where(" uid in(".implode(",",$user_id).")")->getField("uid,nickname");
                foreach($user_ids as $actives){
                    foreach($actives as $active){
                        $active['user_name'] = $users[$active['user_id']];
                        $list[] = $active;
                    }
                }
            }
        }
        $this->assign('nav', 'actives');
        $this->meta_title = '订单列表';
        $this->assign("PAGE_TITLE",'活动订单列表-会员中心');
        
        $this->assign('_active_info', $active_info);
        $this->assign('_list', $list);
        $this->assign('_applynum', $applynum);
        $this->assign('_paynum', $paynum);
        $this->display();
    }
    
    private function get_order_status($value) {
        $status = "";
        if($value['order_status']<2){
                if($value['pay_status']==0){
                    $status = "未付款";
                }else if($value['pay_status']==1){
                    $status = "付款中";
                }else if($value['pay_status']==2){
                    $status = "订单完成";
                }
        }else if($value['order_status']==2){
            $status = "已取消";
        }else if($value['order_status']==3){
            $status = "无效";
        }
        return $status;
    }
    
    
    public function travelNotes(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        
        $title       =   I('title');
        $map['member_id']  =  $member_id;
        if(!empty($title)){
            $map['title']    =   array('like', '%'.$title.'%');
        }

        $list   = $this->lists('Travel_notes', $map);
        
        $this->assign('_list', $list);
        //menu显示
        $this->assign('nav', 'travelnotes');
        $this->meta_title = '领队列表';
        $this->assign("PAGE_TITLE",'游记列表-会员中心');
        $this->display();
    }

    public function travelNotesAdd(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        
        if(IS_POST){
            $title = I('post.title');
            $content = I('post.content');
            $keywords = I('post.keywords');
            $description = I('post.description');
            $status = 0;
            $outline = I('post.outline');
            $add_time =  time();
            
            empty($title) && $this->error('请输入标题');
            empty($content) && $this->error('请输入内容');
            
            $pic_url = $this->get_img_src($content);
            
            $data = array('title' => $title, 'content' => $content,'keywords'=>$keywords,'description'=>$description,
                'status'=>$status,'member_id'=>$member_id,'outline'=>$outline,'pic_url'=>$pic_url,'add_time'=>$add_time);

            if(!M('Travel_notes')->add($data)){
                $this->error('游记提交失败！');
            } else {
                $this->success('游记提交成功！',U('travelNotes'));
            }
        } else {
            //menu显示
            $this->assign('nav', 'travelnotes');
            $this->meta_title = '撰写游记';
            $this->assign("PAGE_TITLE",'撰写游记-会员中心');
            $this->display();
        }
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
    
    public function travelNotesEdit(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $member_id = empty($user)?0:$user['uid'];
        
        if(IS_POST){
            //获取参数
            $title = I('post.title');
            $content = I('post.content');
            $keywords = I('post.keywords');
            $description = I('post.description');
            $status = 0;
            $outline = I('post.outline');
            $id = I('post.id');
            $add_time =  time();
            
            empty($title) && $this->error('请输入标题');
            empty($content) && $this->error('请输入内容');
            
            $pic_url = $this->get_img_src($content);

            $Travel_notes =   D('Travel_notes');
            $data   =   $Travel_notes->create(array('title' => $title, 'content' => $content,'keywords'=>$keywords,'description'=>$description,
                'status'=>$status,'member_id'=>$member_id,'outline'=>$outline,'pic_url'=>$pic_url,'add_time'=>$add_time));
            if(!$data){
                $this->error($Travel_notes->getError());
            }
            $res = $Travel_notes->where(array('id'=>$id))->save($data);

            if($res){
                $this->success('修改成功！',U('travelNotes'));
            }else{
                $this->error('修改失败！');
            }
        }else{
            //获取左边菜单
            
            $id = I('get.id');
            empty($id) && $this->error('参数不能为空！');
            $data = M('Travel_notes')->where("id=$id")->find();
            
            $this->assign('data',$data);
            //menu显示
            $this->assign('nav', 'travelnotes');
            $this->meta_title = '修改游记';
            $this->assign("PAGE_TITLE",'修改游记-会员中心');
            $this->display();
        }
        
    }
    
    public function travelNotesDelete(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $id = I('post.id');
        $user = session('user_auth');
        $user_id = empty($user)?0:$user['uid'];
        
        $res = M('Travel_notes')->where(array('id'=>$id,'user_id'=>$user_id))->delete();
        
        if($res){
            $data['status']  = 1;$data['content'] = array('id'=>$id);$this->ajaxReturn($data);
        }else{
            $data['status']  = 0;$data['err'] = 502;$this->ajaxReturn($data);
        }
    }
    
    public function wechat(){
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        $user = session('user_auth');
        $wechat = D("Wechat")->where(array("uid"=>$user['uid']))->find();
        if(IS_POST){
            //获取参数
            $appid = I('post.appid');
            $appsecret = I('post.appsecret');
            if($appid && $appsecret){
                D("Wechat")->where(array("uid"=>$user['uid']))->save(array("appid"=>$appid,"appsecret"=>$appsecret));
                $bind_set_ok = 0;
                if($wechat['is_bind']){//都绑定后，设置menu
                    $bind_set_ok = 1;
                    #$wechat = new \WeChatService($appid,$appsecret);
                    #$wechat->create_menu($user['uid']);
                }
                $this->success('修改成功！',U('User/wechat',array("bind_set_ok"=>$bind_set_ok)));
            }else{
                $this->error('绑定失败！');
            }
            
        }else{
            if(empty($wechat)){
                $key = md5($user['uid'].time());
                D("Wechat")->add(array("uid"=>$user['uid'],"token"=>$key,"is_bind"=>0));
                $wechat['token'] = $key;
            }
            $this->assign('url',      U("Wechat/index",array("id"=>base64_encode($user['uid'])),true,true));
            $this->assign('bind_set_ok',I('get.bind_set_ok'));
            $this->assign('wechat',      $wechat);
            
            $this->display();
        }
    }
}
