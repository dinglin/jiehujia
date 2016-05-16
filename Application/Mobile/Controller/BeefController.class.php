<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mobile\Controller;

/**
 */
class BeefController extends HomeController {

    
	/* 文档模型列表页 */
	public function index($p = 1){
	    
	}

	/* 文档模型详情页 */
	public function detail($code = 0){
	    $code = $code?$code:I("code");
		/* 标识正确性检测 */
		if(!($code)){
			$this->error('错误！');
		}
		$where = array();
		if(is_numeric($code)){
		    $where["id"] = $code;
		}else{
		    $where["code"] = $code;
		}
		
		$data = M('Beef')->where($where)->find();
		
		if($data){
		    $pro = M('Beef_project')->where("id=".$data["project_id"])->find();
		    $this->assign('pro',$pro);
		}
		
		/* 模板赋值并渲染模板 */
		$this->assign('info', $data);
		$this->PAGE_TITLE = $info["name"];
		$this->display();
	}
}
