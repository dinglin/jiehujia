<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Oauth\Qq\QC;
use User\Oauth\Weibo\SaeTOAuthV2;
use User\Oauth\Weibo\SaeTClientV2;
use User\Api\UserApi;
/**
 * Oauth 2.0
 */
class OauthController extends HomeController {

    protected function _initialize(){
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置
    
        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
    }
    
	//系统首页
    public function index(){
       $this->redirect('Index/index');
    }

    /**
     * QQ
     * APP ID：101110686
     * APP KEY：c6e786909f7416e01230d5bc93f05a13
     */
    /**
     * Weibo
     * App Key：3996360407
     * App Sercet：8ecee503007f40011998cde95e644903
     */
    public function login_qq(){
        $qc = new QC();
        $qc->qq_login();
    }
    public function callback_qq(){
        $type = "qq";
        $qc = new QC();
        $access_token = $qc->qq_callback();
        $open_id = $qc->get_openid();
        /* 调用UC登录接口登录 */
		$user = new UserApi;
		$uid = $user->login_ext($open_id,$type);
		if(0 > $uid){ //注册新用户信息
		    $qc = new QC($access_token,$open_id);
		    $user_info = $qc->get_user_info();
		    $uid = $user->register_ext($open_id, $type,$user_info["nickname"],$user_info["gender"],$user_info['figureurl']);
		}
		/* 登录用户 */
		$Member = D('Member');
		if($Member->login($uid)){ //登录用户
		    //TODO:跳转到登录前页面
		    $this->success('登录成功！',U('Home/User/index'));
		} else {
		    $this->error($Member->getError());
		}
    }
    public function login_weibo(){
        $o = new SaeTOAuthV2( );
        $login_url = $o->getAuthorizeURL( WB_CALLBACK_URL );
        header("Location:$login_url");
    }
    public function callback_weibo(){
        $type = "weibo";
        
        $o = new SaeTOAuthV2( );
        if (isset($_REQUEST['code'])) {
        	$keys = array();
        	$keys['code'] = $_REQUEST['code'];
        	$keys['redirect_uri'] = WB_CALLBACK_URL;
        	try {
        		$token = $o->getAccessToken( 'code', $keys ) ;
        	} catch (OAuthException $e) {
        	}
        }
        $c = new SaeTClientV2( WB_AKEY , WB_SKEY , $token['access_token'] );
        $uid_get = $c->get_uid();
        $weibo_uid = $uid_get['uid'];
        
        $user = new UserApi;
        $uid = $user->login_ext($weibo_uid,$type);
        if(0 > $uid){ //注册新用户信息
            $user_message = $c->show_user_by_id( $weibo_uid);//根据ID获取用户等基本信息
            if($user_message["gender"] =="m"){
                $user_message["gender"] = "男";
            }elseif($user_message["gender"] =='f'){
                $user_message["gender"]="女";
            }else{
                $user_message["gender"] = "男";
            }
            $uid = $user->register_ext($weibo_uid."", $type,$user_message['screen_name'],$user_message["gender"],$user_message['avatar_large']);
        }
        /* 登录用户 */
        $Member = D('Member');
        if($Member->login($uid)){ //登录用户
            //TODO:跳转到登录前页面
            $this->success('登录成功！',U('Home/User/index'));
        } else {
            $this->error($Member->getError());
        }
    }
}