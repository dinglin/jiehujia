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
class ActivesController extends HomeController {

    const PAGE_SIZE = 10;
    
	/* 文档模型列表页 */
	public function index($p = 1,$srh = ""){
	    
		$p = intval($p);
		$p = empty($p) ? 1 : $p;
		
		$this->build_search($srh);
		
		$where = $this->build_where();
		
		$count = M("Actives")->where($where)->count("active_id");
		
		$start = ($p-1)*self::PAGE_SIZE;
	    $actives = M("Actives")->where($where)->order("active_id desc")->limit($start.",".self::PAGE_SIZE)->select();
	    
	    /////////////俱乐部信息
	    $club_ids = array();
	    foreach($actives as $active){
	        $club_ids[] = $active['club_id'];
	    }
	    $clubs = D("Club")->clubs_by_ids($club_ids);
	    
	    foreach($actives as &$active){
	        $active['club'] = $clubs[$active['club_id']];
	    }
	    
	    
	    $this->assign('actives', $actives);
	    
	    //分页
	    $this->build_pages($p,$count);
	    
		$this->display();
	}
	
	private function get_province_id($str){
		$province_id = 0;
		
		if(!$str){
			return $province_id;
		}
		//省份
		$provinces = M('Region')->where('parent_id=1')->getField('region_name,region_id');
		
		foreach ($provinces as $key=>$val){
			if(str_replace($key, "", $str)!= $str){
				$province_id = $val;
			}
		}
		return $province_id;
		
	}
	
	private function build_where(){
	    $where = " status=1";
	    if($this->search_m){
	        if($this->search_m=="w"){
	            $w = date('w');
	            $start = mktime(0,0,0,date('m'),date('d')+6-$w,date('Y'));
	            $end   = mktime(0,0,0,date('m'),date('d')+7-$w,date('Y'));
	        }else{
	            $start = mktime(0,0,0,$this->search_m,1,date('Y'));
	            $end   = mktime(0,0,0,$this->search_m+1,0,date('Y'));
	        }
	        $where .= " AND start_time >=".$start." AND start_time<=".$end;
	    }
	    if($this->search_d){
	         $days = explode('|',$this->search_d);
	         $where .= " AND days >=".$days[0];
	         if($days[1]){
	             $where .= " AND days <=".$days[1];
	         }
	    }
	    if($this->search_s){
	    	//省份解析
	        $province_id = $this->get_province_id($this->search_s);
	        
	        if($province_id){
	        	$where .= " AND province_id=".$province_id;
	        }else{
	        	$where .= " AND (destination like '%".$this->search_s."%' OR title like '%".$this->search_s."%')";
	        }
	    }
	    return $where;
	}
	
	private function build_pages($p,$total,$pagesize=self::PAGE_SIZE){
	    $max_page = ceil($total/$pagesize);
	    if($p==1){
	        $pre_page="";
	    }else{
	        $pre_page= U("Actives/index?p=".($p-1)."&srh=".$this->build_search_param($this->search_m,$this->search_d,$this->search_s));
	    }
	    
	    if($p<$max_page){
	        $next_page= U("Actives/index?p=".($p+1)."&srh=".$this->build_search_param($this->search_m,$this->search_d,$this->search_s));;
	    }else{
	        $next_page="";
	    }
	    $this->pages = array("pre_page"=>$pre_page,"next_page"=>$next_page,"total_page"=>$max_page,"now_page"=>$p,"total_num"=>$total);
	}
	private function build_search($search){
	    if($search){
	        $se = explode("-", $search);
	        $this->search_m = substr($se[0], 1);
	        $this->search_d = substr($se[1], 1);
	        $this->search_s = substr($se[2], 1);
	    }else{
	        $this->search_m = "0";//月份
	        $this->search_d = "0";//天数
	        $this->search_s = "";
	    }
	    $c_month = $this->get_const_month();
	    foreach($c_month as &$m){
	        $m['url'] = U("Actives/index?p=1&srh=".$this->build_search_param($m['key'],$this->search_d,$this->search_s));
	    }
	    $this->c_month = $c_month;
	    
	    $c_days = $this->get_const_days();
	    foreach($c_days as &$d){
	        $d['url'] = U("Actives/index?p=1&srh=".$this->build_search_param($this->search_m,$d['key'],$this->search_s));
	    }
	    $this->c_days = $c_days;
	    
	    $this->c_search = U("Actives/index?p=1&srh=".$this->build_search_param($this->search_m,$this->search_d,""));
	    $this->c_search = str_replace("-s.html", "-s", $this->c_search);
	}
	private function build_search_param($m,$d,$s){
	    return "m".$m."-d".$d."-s".$s;
	}

	/* 文档模型详情页 */
	public function detail($id = 0){
		/* 标识正确性检测 */
		if(!($id && is_numeric($id))){
			$this->error('文档ID错误！');
		}

		/* 获取详细信息 */
		$Actives = D('Actives');
		$info = $Actives->detail($id);
		if(!$info){
			$this->error($Actives->getError());
		}
		
		$tmpl = 'Actives/detail';
		
		/* 更新浏览数 */
		$map = array('active_id' => $id);
		$Actives->where($map)->setInc('hits');

		//处理图片尺寸
		$info['content'] = $this->resize_img_src($info['content']);
		
		/* 模板赋值并渲染模板 */
		$this->assign('info', $info);
		$this->PAGE_TITLE = $info["title"];
		$this->display($tmpl);
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
	/* 文档分类检测 */
	private function category($id = 0){
		/* 标识正确性检测 */
		$id = $id ? $id : I('get.category', 0);
		if(empty($id)){
			$this->error('没有指定文档分类！');
		}

		/* 获取分类信息 */
		$category = D('Category')->info($id);
		if($category && 1 == $category['status']){
			switch ($category['display']) {
				case 0:
					$this->error('该分类禁止显示！');
					break;
				//TODO: 更多分类显示状态判断
				default:
					return $category;
			}
		} else {
			$this->error('分类不存在或被禁用！');
		}
	}

	private function get_const_days(){
	    return array(
	            array("key"=>"0","val"=>"不限"),
	            array("key"=>"1|3","val"=>"1~3天"),
	            array("key"=>"4|7","val"=>"4~7天"),
	            array("key"=>"8|5","val"=>"8~15天"),
	            array("key"=>"15|0","val"=>"15天以上"),
	    );
	}
	
	private function get_const_month(){
	    return array(
	            array("key"=>"0","val"=>"不限"),
	            array("key"=>"w","val"=>"本周末"),
	            array("key"=>"1","val"=>"1月"),
	            array("key"=>"2","val"=>"2月"),
	            array("key"=>"3","val"=>"3月"),
	            array("key"=>"4","val"=>"4月"),
	            array("key"=>"5","val"=>"5月"),
	            array("key"=>"6","val"=>"6月"),
	            array("key"=>"7","val"=>"7月"),
	            array("key"=>"8","val"=>"8月"),
	            array("key"=>"9","val"=>"9月"),
	            array("key"=>"10","val"=>"10月"),
	            array("key"=>"11","val"=>"11月"),
	            array("key"=>"12","val"=>"12月"),
	    );
	}
}
