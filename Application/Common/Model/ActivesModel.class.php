<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Common\Model;
use Think\Model;
use Think\Page;

/**
 * 活动基础模型
 */
class ActivesModel extends Model{

    public $page = '';

	/**
	 * 获取活动列表
	 * @param  integer  $category 分类ID
	 * @param  string   $order    排序规则
	 * @param  integer  $status   状态
	 * @param  boolean  $count    是否返回总数
	 * @param  string   $field    字段 true-所有字段
	 * @return array              活动列表
	 */
	public function lists($category, $order = '`active_id` DESC', $status = 1, $field = true){
		$map = $this->listMap($category, $status);
		return $this->field($field)->where($map)->order($order)->select();
	}

	/**
	 * 计算列表总数
	 * @param  number  $category 分类ID
	 * @param  integer $status   状态
	 * @return integer           总数
	 */
	public function listCount($category, $status = 1){
		$map = $this->listMap($category, $status);
		return $this->where($map)->count('id');
	}

	/**
	 * 获取详情页数据
	 * @param  integer $id 活动ID
	 * @return array       详细数据
	 */
	public function detail($id){
		/* 获取基础数据 */
		$info = $this->field(true)->find($id);
		if(!(is_array($info) || 1 !== $info['status'])){
			$this->error = '活动被禁用或已删除！';
			return false;
		}
        $info['content'] = $this->content($id);
        $info['imgs'] = $this->imgs($id);
        $info['club'] = $this->club($info['club_id']);
		return $info;
	}

	/**
	 * 获取活动内容
	 * @param integer $id
	 * @return string
	 */
	public function content($id){
	    $content = "";
	    if($id){
	        $con = D('ActiveContent')->where(array('active_id'=>$id))->find();
	        if($con){
	            $content = $con['content'];
	        }
	    }
	    return $content;
	}

	/**
	 * 获取俱乐部
	 */
	public function club($id){
	    $club = array();
	    if($id){
	        $club = D("Club")->where(array("club_id"=>$id))->find();
	    }
	    return $club;
	}
	/**
	 * 获取相册
	 */
	public function imgs($id){
	    $pics = array();
	    if($id){
	        $pics = D("ActivePic")->where(array("active_id"=>$id))->select();
	    }
	    return $pics;
	}
    /**
     * 更新相册
     * @param unknown $active_id
     * @param unknown $img
     * @return boolean
     */
	public function saveImgs($active_id,$img){
	    $pics = D("ActivePic")->where(array("active_id"=>$active_id,"img_src"=>$img))->find();
	    if(empty($pics)){
	        D("ActivePic")->add(array("active_id"=>$active_id,"img_src"=>$img));
	    }
	    return true;
	}
	
	/**
	 * 首页推荐活动
	 * 最新的20个活动，随机取12个
	 */
	public function recom_actives($num=12,$club_id=""){
	    $where = array("status"=>1);
	    if($club_id){
	        $where['club_id'] = $club_id;
	    }
	    
	    $actives = $this->where($where)->order("active_id desc")->limit($num*2)->select();
	    $indexs = array_rand($actives,$num);
	    $result = array();
	    foreach($actives as $in=>$val){
	        if(in_array($in, $indexs)){
	            $result[] = $val;
	        }
	    }
	    return $result;
	}
}
