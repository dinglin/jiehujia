<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

/**
 * 俱乐部模型控制器
 * 俱乐部模型列表和详情
 */
class ClubController extends HomeController {

    const PAGE_SIZE = 5;
    
    protected function _initialize(){
        parent::_initialize();
        
        $club_id = I('id');
        $where = array("club_id"=>$club_id);
        $this->club_info = M("Club")->where($where)->find();
        if(empty($this->club_info)){
            $this->_empty();
        }
        //活动量
        $this->count_actives = M("Actives")->where(array("club_id"=>$club_id,"status"=>1))->count("active_id");
        
        //图片量
        $this->imgs= $this->get_active_imgs_club($club_id);
         
        $this->count_imgs = $this->imgs?count($this->imgs):0;
    }
    
	public function detail($id,$p=1){
		$this->active($id,$p);
	}
	public function story($id){
	    $this->tab_club = "story";
	    $this->display();
	}
	public function active($id,$p=1){
	    
	    $this->is_club_home = true;
	    
	    $p = intval($p);
	    $p = empty($p) ? 1 : $p;
	    
	    $club_id = intval($id);
	    
	    $where = array("club_id"=>$club_id,"status"=>1);
	    
	    $start = ($p-1)*self::PAGE_SIZE;
	    $actives = M("Actives")->where($where)->order("start_time desc")->limit($start.",".self::PAGE_SIZE)->select();
	     
	    $actives = $this->build_actives_imgs($actives);
	    
	    $this->build_pages($p,$this->count_actives,$club_id,"detail");
	    
	    $this->assign('actives', $actives);
	    $this->assign('may_like', $this->may_like());
	    
	    $tmpl = 'Club/detail';
	    $this->tab_club = "active";
	    $this->display($tmpl);
	}
	private function may_like(){
	    $actives = array();
	    if(!empty($this->imgs)){
	        foreach($this->imgs as $img){
	            $actives[$img['active_id']] = $img;
	        }
	    }
	    return array_splice($actives, 0,5);
	}
	private function build_actives_imgs($actives){
	    $imgs = array();
	    if(!empty($this->imgs)){
	        foreach($this->imgs as $img){
	            $imgs[$img['active_id']][] = $img['img_src'];
	        }
	    }
	    $new_actives = array();
	    foreach($actives as $active){
	        $new_actives[$active['active_id']] = $active;
	        $imgs[$active['active_id']][] = $active['list_pic'];
	    }
	    foreach($new_actives as &$active){
	        if($imgs[$active['active_id']]){
	            $tmp_img = $imgs[$active['active_id']];
	            $tmp_img = array_unique($tmp_img);
	            $tmp_img = array_splice($tmp_img, 0,3);
	            $active["imgs"] = $tmp_img;
	        }
	    }
	    return $new_actives;
	}
	
	private function build_pages($p,$total,$club_id,$type,$pagesize=self::PAGE_SIZE){
	    $max_page = ceil($total/$pagesize);
	    if($p==1){
	        $pre_page="";
	    }else{
	        $pre_page= U("Club/".$type."?id=".$club_id."&p=".($p-1));
	    }
	     
	    if($p<$max_page){
	        $next_page= U("Club/".$type."?id=".$club_id."&p=".($p+1));
	    }else{
	        $next_page="";
	    }
	    $this->pages = array("pre_page"=>$pre_page,"next_page"=>$next_page,"total_page"=>$max_page,"now_page"=>$p,"total_num"=>$total);
	}
	
	public function fans($id){
	    $tmpl = 'Club/user';
	    $this->tab_club = "fans";
	    $this->display($tmpl);
	}
	public function guide($id){
	    $tmpl = 'Club/user';
	    $this->tab_club = "guide";
	    $this->display($tmpl);
	}
	public function user($id){
	    $tmpl = 'Club/user';
	    $this->tab_club = "user";
	    $this->display($tmpl);
	}
	public function photo($id){
	    $this->tab_club = "photo";
	    $this->display();
	}
	public function about($id){
	    $this->tab_club = "about";
	    $this->display();
	}
	public function message($id){
	    $this->tab_club = "about";
	    $this->display();
	}
	
	//获取俱乐部的活动图片
	private function get_active_imgs_club($club_id){
	    $pics = array();
	    $active_ids = M("Actives")->where(array("club_id"=>$club_id,"status"=>1))->getField("active_id,title");
	    if(!empty($active_ids)){
	        $pics = D("ActivePic")->where("active_id in(".implode(",", array_keys($active_ids)).")")->select();
	        foreach ($pics as &$pic){
	            $pic['active_name']=$active_ids[$pic['active_id']];
	        }
	    }
	    return $pics;
	}
	private function get_actives($club_id,$p){
	    
	}
}
