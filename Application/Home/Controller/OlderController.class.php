<?php
namespace Home\Controller;
use User\Api\UserApi;

/**
 * 老人管理
 */
class OlderController extends NHController {

    protected function _initialize(){
        parent::_initialize();
        $user_session = session('user_auth');
        if(!empty($user_session)){
            $this->assign('group_type',$user_session['group_type']);
        }
        $this->assign('nav', 'older');
        $this->assign("PAGE_TITLE",'老人管理-会员中心');
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
        $list   = $this->lists('Older', $map);
        //int_to_string($list);
        $this->assign('_list', $list);
        $this->display();
    }
    

    public function add(){
        if(IS_POST){
            $name = I('post.name');
    
            empty($name) && $this->error('请输入名称');
            
            $data   =   array('name' => $name,"create_time"=>time(),'update_time'=>time(),"status"=>0);
            $insert_id = M('Older')->add($data);
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
    

    public function edit(){
        if(IS_POST){
            //获取参数
            $name = I('post.name');
    
            $id = I('post.id');
            empty($name) && $this->error('请输入名称');
    
            $Older =   M('Older');
            $data   =   array('name' => $name,'update_time'=>time());
            $res = $Older->where(array('id'=>$id))->save($data);
            if($res){
                $this->success('修改成功！',U('index'));
            }else{
                $this->error('修改失败！');
            }
        }else{
            //获取左边菜单
    
            $id = I('get.id');
            empty($id) && $this->error('参数不能为空！');
            $data = M('Older')->where("id=$id")->find();
    
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
                $this->delete('Older', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }

}