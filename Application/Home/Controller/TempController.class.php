<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class TempController extends Controller {
    protected function _initialize(){
    }
	//系统首页
    public function appcenter(){
        $this->assign('nav', 'appcenter');
        $this->display();
    }
    
    public function acount(){
        $this->assign('nav', 'acount');
        $this->display();
    }
    
    public function service(){
        $this->assign('nav', 'service');
        $this->display();
    }
    
    public function vip(){
        $this->assign('nav', 'vip');
        $this->display();
    }
    
    public function employee(){
        $this->assign('nav', 'employee');
        $this->display();
    }
    
    public function capability(){
        $this->assign('nav', 'capability');
        $this->display();
    }
    
    public function healthy(){
        $this->assign('nav', 'healthy');
        $this->display();
    }
    
    public function alarm(){
        $this->assign('nav', 'alarm');
        $this->display();
    }

}