<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mobile\Controller;
use User\Api\UserApi;

/**
 * 用户收藏控制器
 */
class UserFavoriteController extends HomeController {

    const PAGE_SIZE = 5;
    
	/* 用户中心首页 */
	public function index($p = 1){
        if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		
		$p = intval($p);
		$p = empty($p) ? 1 : $p;
		$start = ($p-1)*self::PAGE_SIZE;
		
		$user = session('user_auth');
		$count = M("Favorite")->where('uid='.$user['uid']." AND status=1")->count("id");
		$favorites = M("Favorite")->where('uid='.$user['uid']." AND status=1")->order("id desc")->limit($start.",".self::PAGE_SIZE)->getField("active_id,uid");
		$actives = array();
		if(!empty($favorites)){
		    $actives = M("Actives")->where("active_id in(".implode(",",array_keys($favorites)).")")->select();
		}
		if($p==1){
		    $this->assign('actives', $actives);
		    
		    //分页
		    $this->build_pages($p,$count);
            //menu显示
            $this->assign('nav', 'favorite');
            $this->assign("PAGE_TITLE",'活动收藏-会员中心');
            $this->assign("BACK_URL",U('User/index'));
    		$this->display();
		}else{
		    $html = $this->build_nextpage_ajax_html($actives,$p);
		    echo $html;exit;
		}
	}
	private function build_nextpage_ajax_html($actives,$nowpage){
	    $content = '<div style="background-color: #F3C6D9;text-align: right;padding-right: 10px;">'.(($nowpage-1)*self::PAGE_SIZE+1).'-'.($nowpage*self::PAGE_SIZE).'&nbsp;&nbsp;'.$nowpage.'页</div>';
	    foreach($actives as $vo){
	        $content .= '<article href="'.U('Home/Actives/detail?id='.$vo['active_id']).'">';
	        $content .= '<div style="background-image: url('.img_size_url($vo['list_pic'],196,176).')" class="pro-pic"></div>';
	        $content .= '<div class="pro-info">';
	        $content .= '<h3>'.$vo['title'].'</h3>';
	        $content .= '<p>';
	        $content .= '<span class="f-color-1 value fl">&yen;'.$vo['price'].'</span>';
	        $content .= '<span class="fr"></span>';
	        $content .= '</p>';
	        $content .= '<p>';
	        $content .= '<span class="fl">时间：'.date("m月d日",$vo['start_time'])."~".date("m月d日",$vo['end_time'])."（".$vo['days'].'天）</span>';
	        $content .= '<span class="fr f-color-1 rebate"> </span>';
	        $content .= '<span class="privilege rose">';
	        $content .= '<a href="'.U('UserFavorite/favorite_delete?active_id='.$vo['active_id']).'" class="ajax-get" style="color:#fff;">取消</a>';
	        $content .= '</span>';
	        $content .= '</p>';
	        $content .= '<p class="f-size-10">{$vo.destination}</p>';
	        $content .= '</div>';
	        $content .= '</article>';
	    }
	    return $content;
	}
	private function build_pages($p,$total,$pagesize=self::PAGE_SIZE){
	    $max_page = ceil($total/$pagesize);
	    if($p==1){
	        $pre_page="";
	    }else{
	        $pre_page= U("UserFavorite/index?p=".($p-1));
	    }
	     
	    if($p<$max_page){
	        $next_page= U("UserFavorite/index?p=".($p+1));;
	    }else{
	        $next_page="";
	    }
	    $this->pages = array("pre_page"=>$pre_page,"next_page"=>$next_page,"total_page"=>$max_page,"now_page"=>$p,"total_num"=>$total);
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
