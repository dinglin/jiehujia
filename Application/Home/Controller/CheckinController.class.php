<?php
namespace Home\Controller;
use User\Api\UserApi;

/**
 * 入住管理
 */
class CheckinController extends NHController {

    protected function _initialize(){
        parent::_initialize();
        $user_session = session('user_auth');
        if(!empty($user_session)){
            $this->assign('group_type',$user_session['group_type']);
        }
        $this->assign('nav', 'checkin');
        $this->assign("PAGE_TITLE",'入住管理-会员中心');
    }
    
    public function index(){
    
        $title       =   I('name');
        $status      =  isset($_GET['status'])?$_GET['status']:0;
        if($status==0){
            $map['status'] = array('eq',0);
        }else{
            $map['status']  =   array('eq',$status);
        }
        if($title){
            $map['name']=   array('like','%'.$title.'%');
        }
        $map['type']=0;
        $list   = $this->lists('Bed', $map);
        //int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '老人信息';
        $this->display();
    }
    

    public function in(){
        if(IS_POST){
            $name = I('post.name');
    
            empty($name) && $this->error('请输入名称');
            
            $data   =   array('name' => $name,"create_time"=>time(),'update_time'=>time(),"status"=>0);
            $insert_id = M('Checkin')->add($data);
            if(!$insert_id){
                $this->error('添加失败！');
            } else {
                $this->success('添加成功！',U('index'));
            }
        } else {
            $this->meta_title = '新增';
            $this->display();
        }
    }
    
    public function reserve(){
        if(IS_POST){
            $uname = I('post.uname');
            $id = I('post.bid');
            empty($uname) && $this->error('请输入名称');
            
            
            $Checkin =   M('Checkin');
            $Older =   M('Older');
            
            $data   =   array('name' => $uname,"create_time"=>time(),'update_time'=>time(),"status"=>0);
            $insert_id = $Older->add($data);
            
            $data   =   array('name' => $name,'update_time'=>time());
            $res = $Older->where(array('id'=>$id))->save($data);
            if($res){
                $this->success('预定成功！',U('index'));
            }else{
                $this->error('预定失败！');
            }
        }else{
            $id = I('get.id');
            empty($id) && $this->error('参数不能为空！');
            $Bed = M('Bed')->where("id=$id")->find();
            
            $this->assign('bed',$Bed);
            $this->meta_title = '编辑';
            $this->display();
        }
    }

    public function out(){
        if(IS_POST){
            //获取参数
            $uname = I('post.uname');
            $id = I('post.bid');
            empty($uname) && $this->error('请输入名称');
    
            
            $Checkin =   M('Checkin');
            $Older =   M('Older');
            
            $data   =   array('name' => $name,'update_time'=>time());
            $res = $Older->where(array('id'=>$id))->save($data);
            if($res){
                $this->success('预定成功！',U('index'));
            }else{
                $this->error('预定失败！');
            }
        }else{
            //获取左边菜单
    
            $id = I('get.id');
            empty($id) && $this->error('参数不能为空！');
            $data = M('Checkin')->where("id=$id")->find();
    
            $this->assign('data',$data);
            $this->meta_title = '编辑';
            $this->display("add");
        }
    
    }
    
    /**
     * 删除
     * @param string $method
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('id',0));
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
        $this->error('请选择要操作的数据!');
        }
        $map['id'] =   array('in',$id);
        switch ( strtolower($method) ){
            case 'delete':
                $this->delete('Checkin', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }

}