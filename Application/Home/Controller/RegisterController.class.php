<?php
namespace Home\Controller;
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2016/5/24
 */
class RegisterController extends HomeController
{
    const CATE_ID_ZIXUN = 2;//首页咨询模块分类id

    //预约首页
    public function index()
    {
        $this->assign("isIndex", true);
        $this->display();
    }
}