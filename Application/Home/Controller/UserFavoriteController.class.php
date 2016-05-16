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
 * 用户收藏控制器
 */
class UserFavoriteController extends HomeController {
    
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
		$user = session('user_auth');
		$favorites = M("Favorite")->where('uid='.$user['uid']." AND status=1")->order("id desc")->limit(20)->getField("active_id,uid");
		if(!empty($favorites)){
		    $actives = M("Actives")->where("active_id in(".implode(",",array_keys($favorites)).")")->select();
		    $this->assign('actives', $actives);
		}
                //menu显示
        $this->assign('nav', 'favorite');
        $this->assign("PAGE_TITLE",'活动收藏-会员中心');
		$this->display();
	}
	/**
	 * 收藏
	 * @param unknown $active_id
	 */
	public function favorite_add(){
	    $active_id = I("post.active_id");
	    $result = array();
        $active_id = intval($active_id);
        if($active_id){
            if(is_login()){
                $user = session('user_auth');
                $favorite = M("Favorite")->where(array("active_id"=>$active_id,"uid"=>$user['uid'],"status"=>1))->find();
                if(empty($favorite)){
                    M("Favorite")->data(array("active_id"=>$active_id,"uid"=>$user['uid'],"status"=>1,'ftime'=>time()))->add();
                    $result['status']=3;
                    $result['msg']   ="收藏成功";
                }else{
                    $result['status']=2;
                    $result['msg']   ="已经收藏";
                }
                
            }else{
                $result['status']=1;
                $result['msg']   ="请登录后收藏";
            }
        }else{
            $result['status']=0;
            $result['msg']   ="参数错误";
        }
        echo json_encode($result);
        exit;
	}
	/**
	 * 验证是否收藏
	 * @param unknown $active_id
	 */
	public function favorite_is(){
	    $active_id = I("post.active_id");
	    $result = array("status"=>0,"msg"=>"没有收藏");
	    $active_id = intval($active_id);
	    if($active_id){
	        if(is_login()){
	            $user = session('user_auth');
	            $favorite = M("Favorite")->where(array("active_id"=>$active_id,"uid"=>$user['uid'],"status"=>1))->find();
	            if(!empty($favorite)){
	                $result['status']=1;
	                $result['msg']   ="已经收藏";
	            }
	        }
	    }
	    echo json_encode($result);
	    exit;
	}
	/**
	 * 取消收藏
	 */
	public function favorite_del(){
	    $active_id = I("post.active_id");
	    $result = array("status"=>0,"msg"=>"参数错误");
	    $active_id = intval($active_id);
	    if($active_id){
	        if(is_login()){
	            $user = session('user_auth');
	            M("Favorite")->data("status=0")->where(array("active_id"=>$active_id,"uid"=>$user['uid']))->save();
                $result['status']=1;
                $result['msg']   ="取消收藏";
	        }
	    }
	    echo json_encode($result);
	    exit;
	}
	
	public function favorite_delete(){
	    $active_id = I("active_id");
	    $active_id = intval($active_id);
	    if($active_id){
	        if(is_login()){
	            $user = session('user_auth');
	            M("Favorite")->data("status=0")->where(array("active_id"=>$active_id,"uid"=>$user['uid'],"status"=>1))->save();
	            $this->success("取消收藏成功");
	        }
	    }
	    $this->error("参数错误");
	}
}
