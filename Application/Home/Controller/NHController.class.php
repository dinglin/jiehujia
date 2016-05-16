<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/5/14
 */
namespace Home\Controller;

/**
 * 养老院管理基类
 * @package Home\Controller
 */
class NHController extends HomeController {

    protected function _initialize(){
        parent::_initialize();
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login') );
        }
    }
}