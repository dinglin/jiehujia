<?php
namespace Admin\Controller;
use User\Api\UserApi;

/**
 * 床位管理
 */
class BedController extends AdminController {

    public function index(){
    
        $title       =   I('name');
        $status      =  isset($_GET['status'])?$_GET['status']:0;
        if($status==0){
            $map['status'] = array('eq',0);
        }else{
            $map['status']  =   array('eq',$status);
        }
        if($title){
            $map['code|name']=   array(array('like','%'.$title.'%'),array('like','%'.$title.'%'),'_multi'=>true);
        }
        $list   = $this->lists('Beef', $map);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '床位信息';
        $this->display();
    }

}