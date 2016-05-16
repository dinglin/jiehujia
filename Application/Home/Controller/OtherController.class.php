<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class OtherController extends HomeController {

	//系统首页
    public function index(){
        $this->display();
    }
    
    public function about(){
        $this->assign('nav', 'about');
        $this->display();
    }
    
    public function contact(){
        $this->assign('nav', 'contact');
        $this->display();
    }
    
    public function copyright(){
        $this->assign('nav', 'copyright');
        $this->display();
    }
    
    public function events(){
        $this->assign('nav', 'events');
        $this->display();
    }
    
    public function job(){
        $this->assign('nav', 'job');
        $this->display();
    }
    
    public function links(){
        $this->assign('nav', 'links');
        $this->display();
    }
    
    public function privacy(){
        $this->assign('nav', 'privacy');
        $this->display();
    }

}