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
 * 活动模型控制器
 * 活动模型列表和详情
 */
class TravelNotesController extends HomeController {

    const PAGE_SIZE = 12;
    
	/* 文档模型列表页 */
	public function index($p = 1,$srh = ""){
                                    $this->tab = 'default';
		$p = intval($p);
		$p = empty($p) ? 1 : $p;
		
		$this->build_search($srh);
		
		$where = $this->build_where();
		
		$count = M("Travel_notes")->where($where)->count("id");
		
		$start = ($p-1)*self::PAGE_SIZE;
                
                                    
                                    if($this->search_n){
                                         $order_by = " add_time desc";
                                    }else{
                                        $order_by = "  id desc,clicknum desc";
                                    }
                                    
	    $travel_notes = M("Travel_notes")->where($where)->order($order_by)->limit($start.",".self::PAGE_SIZE)->select();
	    
           
                        foreach($travel_notes as $k=>$v){
                                $travel_notes[$k]['nickname'] = M("Member")->where("uid=$v[member_id]")->getField('nickname');
                                $travel_notes[$k]['add_time'] = date('Y-m-d',$v['add_time']);
                        }
                
                      $this->assign('travel_notes', $travel_notes);
	    //分页
	    $this->build_pages($p,$count);
            if($this->search_h==1){
                $this->tab = 'hot';
            }else if($this->search_r==1){
                $this->tab = 'recom';
            }else if($this->search_n==1){
                $this->tab = 'new';
            }else{
                $this->tab = 'default';
            }
	    $this->assign('tab', $this->tab);
		$this->display();
	}
	
	
	private function build_where(){
	    $where = " status=1";
	    if($this->search_h){
	        $where .= " AND is_hot =1 ";
	    }
	    if($this->search_r){
	         $where .= " AND is_recom =1 ";
	    }
	    return $where;
	}
	
	private function build_pages($p,$total,$pagesize=self::PAGE_SIZE){
	    $max_page = ceil($total/$pagesize);
	    if($p==1){
	        $pre_page="";
	    }else{
	        $pre_page= U("TravelNotes/index?p=".($p-1)."&srh=".$this->build_search_param($this->search_d,$this->search_h,$this->search_r,$this->search_n));
	    }
	    
	    if($p<$max_page){
	        $next_page= U("TravelNotes/index?p=".($p+1)."&srh=".$this->build_search_param($this->search_d,$this->search_h,$this->search_r,$this->search_n));;
	    }else{
	        $next_page="";
	    }
                      $default = U("TravelNotes/index?p=1&srh=".$this->build_search_param(1,0,0,0));
                      $hot = U("TravelNotes/index?p=1&srh=".$this->build_search_param(0,1,0,0));
                      $recom = U("TravelNotes/index?p=1&srh=".$this->build_search_param(0,0,1,0));
                      $new= U("TravelNotes/index?p=1&srh=".$this->build_search_param(0,0,0,1));
	    $this->pages = array("pre_page"=>$pre_page,"next_page"=>$next_page,"total_page"=>$max_page,"now_page"=>$p,"total_num"=>$total,"default"=>$default,"hot"=>$hot,"recom"=>$recom,"new"=>$new);
	}
	private function build_search($search){
	    if($search){
	        $se = explode("-", $search);
	        $this->search_d = substr($se[0], 1);//默认
	        $this->search_h = substr($se[1], 1);//热门
	        $this->search_r = substr($se[2], 1);//推荐
                          $this->search_n = substr($se[3], 1);//最新
	    }else{
	        $this->search_d = "1";
	        $this->search_h = "0";
	        $this->search_r = "0";
                          $this->search_n = "0";
	    }
	   
	    
	    
	    $this->c_search = U("Travel_notes/index?p=1&srh=".$this->build_search_param($this->search_d,$this->search_h,$this->search_r,$this->search_n));
	    $this->c_search = str_replace("-s.html", "-s", $this->c_search);
	}
	private function build_search_param($d,$h,$r,$n){
	    return "d".$d."-h".$h."-r".$r."-n".$n;
	}

	/* 文档模型详情页 */
	public function detail($id = 0){
		/* 标识正确性检测 */
		if(!($id && is_numeric($id))){
			$this->error('游记ID错误！');
		}

		/* 获取详细信息 */
		$Travel_notes = M('Travel_notes');
		$info = $Travel_notes->where("id=$id")->find($id);
		if(!$info){
                                            $this->error($Travel_notes->getError());
		}
		
		
		/* 更新浏览数 */
		$map = array('id' => $id);
		$Travel_notes->where($map)->setInc('clicknum');

		//处理图片尺寸
		$info['content'] = $this->resize_img_src($info['content']);
                                    $info['nickname'] = M("Member")->where("uid=$info[member_id]")->getField('nickname');
                                    $info['add_time'] = date('Y-m-d',$info['add_time']);
		//print_r($info);
		/* 模板赋值并渲染模板 */
		$this->assign('info', $info);
		$this->PAGE_TITLE = $info["title"];
		$this->display();
	}
	private function resize_img_src($content){
	    $pic_url= "";
	    $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
	    preg_match_all($pattern,$content,$match);
	    if($match && $match[1]){
	        foreach($match[1] as $img_src){
	            $img_src_new = img_size_url($img_src,600,400);
	            $content = str_replace($img_src, $img_src_new, $content);
	        }
	    }
	    return $content;
	}
	

	
	
}
