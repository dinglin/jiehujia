<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mobile\Controller;
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

    public function login_qq(){
        $qc = new QC();
        $qc->qq_login();
    }
    public function login_weibo(){
        $o = new SaeTOAuthV2( );
        $login_url = $o->getAuthorizeURL( WB_CALLBACK_URL );
        header("Location:$login_url");
    }
}