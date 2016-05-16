<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;
use Admin\Spider\SpiderFactory;
/**
 * 爬虫控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class SpiderController extends AdminController {

    
    public function doSpider($method=null){
        
        set_time_limit(0);
        
        $active_id = I('id',0);
        if( in_array(C('USER_ADMINISTRATOR'), $active_id)){
            $this->error("不允许对超级管理员执行该操作!");
        }
        switch ( strtolower($method) ){
            case 'loadactive'://读取单页
                $this->loadActive( $active_id );
                break;
            case 'loadactivelist'://更新列表
                $this->loadActiveList( );
                break;
            default:
                $this->error('参数非法');
        }
    }
    /**
     * 更新列表
     */
    public function loadActiveList(){
    	$id = I('id',0);
    	if($id){
    		$data = M('ActiveSpider')->where("id=$id")->find();
    		if(!empty($data)){
    			set_time_limit(0);
    			$factory = new SpiderFactory();
    			$factory::reload_list_urls($this,$data["domain"]);
    		}
    		M('ActiveSpider')->where("id=$id")->data(array("last_update"=>time()))->save();
    		$this->success("更新 {$data['name']} 成功"); 
    	}else{
    		$this->error('参数非法');
    	}
    }
    /**
     * 单页读取
     * @param unknown $active_id
     */
    private function loadActive($active_id){
        
       $active_id = intval($active_id);
       
       if($active_id){
           
           $model = M('Actives');
           $active = $model->where(array("active_id"=>$active_id))->find();
           
           if(!empty($active['from_url']) && empty($active['title'])){
               
               $factory = new SpiderFactory();
               $data = $factory::get_view_data($active['from_url']);
               if(!empty($data)){
                   if($active['club_id']){
                       $data['club_id'] = $active['club_id'];
                   }
                   if(!$data['list_pic']&& $data['content']){
                       $data['list_pic'] = $this->get_img_src($data['content']);
                   }
                   if(!$data['list_pic']&& $data['imgs']){
                       $data['list_pic'] = $data['imgs'][0];
                   }
                   $this->set_active($model, $data, $active_id);
               } 
           }
       } 
       if(!empty($data)){
           $this->success($data['title']);
       } else {
           $this->error('活动抓取失败！');
       }
    }
    private function get_img_src($content){
        $pic_url= "";
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern,$content,$match);
        if($match && $match[1]){
            $pic_url = $match[1][0];
        }
        return $pic_url;
    }
    /**
     * 保存活动
     * @param unknown $data
     * @param unknown $active_id
     */
    private function set_active($model,$data,$active_id){
        if($active_id && isset($data['title']) && isset($data['content'])){
            
            $fields = array("status"=>0,"active_status"=>0,"hits"=>0);
            if(!isset($data['add_time']) || !$data['add_time']){
                $data['add_time'] = time();
            }
            $club_data = $this->set_club_guide($data);
            $data = array_merge($data,$fields,$club_data);
            
            if(!isset($data['days']) || !$data['days']){
                $data['days'] = $this->build_days($data['start_time'],$data['end_time']);
            }
            
            $model->where("active_id=$active_id")->data($data)->save();
            //content
            $c_model = M('Active_content');
            $c_data['content'] = str_replace("'", "\"", $data['content']);
            $c_data['active_id'] = $active_id;
            $content = $c_model->where("active_id=$active_id")->find();
            if(empty($content)){
                $c_model->data($c_data)->add();
            }else{
                $c_model->where("active_id=$active_id")->data($c_data)->save();
            }
            //imgs
            $imgs = get_img_src_from_html($data['content']);
            
            if(isset($data['imgs'])){
                $p_model = M('Active_pic');
                $p_data = array();
                foreach($data['imgs'] as $img){
                    if($img && trim($img)){
                        $p_data[] = array("img_src"=>$img,'active_id'=>$active_id);
                    }
                }
                foreach($imgs as $img){
                    if($img && trim($img)){
                        $p_data[] = array("img_src"=>$img,'active_id'=>$active_id);
                    }
                }
                $p_model->where(array('active_id'=>$active_id))->delete();
                $p_model->addAll($p_data);
            }
        }
        return true;
    }
    /**
     * save
     */ 
    private function set_club_guide($data){
        $result = array();
        //判断俱乐部是不是新俱乐部
        if(isset($data['club_name'])){
            //select
            $c_model = M('Club');
            $res = $c_model->where(array("club_name"=>$data['club_name']))->find();
            if(empty($res)){
                $club = array();
                $club["club_name"]=$data['club_name'];
                if(isset($data['club_logo'])){
                    $club["logo"]=$data['club_logo'];
                }
                $result["club_id"] = $c_model->data($club)->add();
            }else{
                $result["club_id"] = $res['club_id'];
                if(isset($data['club_logo']) && empty($res['logo'])){
                    $c_model->where(array("club_id"=>$result["club_id"]))->data(array("logo"=>$data['club_logo']))->save();
                }
            }
            
        }
        //判断是不是新领队
        if(isset($view['guide_name'])){
            //select
            $c_model = M('Club_user');
            $res = $c_model->where(array("nick_name"=>$data['guide_name']))->find();
            if(empty($res)){
                $cuser = array();
                $cuser["nick_name"]=$data['guide_name'];
                if(isset($data['guide_logo'])){
                    $cuser["logo"]=$data['guide_logo'];
                }
                if(isset($data['club_id'])){
                    $cuser["club_id"]=$data['club_id'];
                }
                if(isset($result['club_id'])){
                    $cuser["club_id"]=$result['club_id'];
                }
                $result["guide_id"] = $c_model->data($cuser)->add();
            }else{
                $result["guide_id"] = $res['cuser_id'];
                $c_data = array();
                if(isset($data['guide_logo']) && empty($res['logo'])){
                    $c_data['logo'] = $data['guide_logo'];
                }
                if(isset($data['club_id'])){
                    $c_data["club_id"]=$data['club_id'];
                }
                if(isset($result['club_id'])){
                    $c_data["club_id"]=$result['club_id'];
                }
                if(!empty($c_data)){
                    $c_model->where(array("cuser_id"=>$res["cuser_id"]))->data($c_data)->save();
                }
            }
        }
        return $result;
    }
    
    /**
     * 创建前先验证是否存在
     * @param array $data
     * @param Model $model
     */
    public function create_active_only($data){
        $model = M('Actives');
        $insert_id = 0;
        if(!empty($data) && isset($data['from_url'])){
            $res = $model->where(array("from_url"=>$data['from_url']))->find();
            if(empty($res)){
                $fields = array("status"=>0,"active_status"=>0,"hits"=>0);
                if(!isset($data['add_time'])){
                    $data['add_time'] = time();
                }
                
                $club_data = $this->set_club_guide($data);
                $data = array_merge($data,$fields,$club_data);
                $insert_id = $model->data($data)->add();
            }
        }
        return $insert_id;
    }
    private function build_days($start_time,$end_time){
        $days = 0;
        if($start_time && $end_time){
            $time = $end_time - $start_time;
            $one_day = 24 * 60 * 60;
            $days = ceil($time/$one_day);
        }
        return $days;
    }
}
