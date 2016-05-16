<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

    const CATE_ID_ZIXUN = 2;//首页咨询模块分类id
	//系统首页
    public function index(){

        //推荐活动
        //$actives = $this->recom_actives();
        //$this->assign("recomactives",$actives);
        
        //搜索
        //$this->base_search = U("Actives/index?p=1&srh=0");
        //$this->base_search = str_replace("srh/0.html", "srh/", $this->base_search);
        
        //户外咨询
       // $articles = $this->get_article_zixun();
        //$this->assign("articles",$articles);
        //$this->assign("banners",$this->get_banners());
        //$this->assign("PAGE_TITLE",'');
        $this->assign("isIndex",true);
        $this->display();
    }
    //获取banner图
    private function get_banners(){
        $banners = M("Banner")->where(array("status"=>1))->getField("img_id,title,link");
        if(!empty($banners)){
            $imgs = M("Picture")->where("id in(".implode(",",array_keys($banners)).")")->getField("id,path");
            foreach($banners as $img_id=>&$ban){
                $ban['src'] = $imgs[$img_id];
            }
        }
        return $banners;
    }
    private function get_article_zixun(){
        $Document = D('Document');
        return $Document->lists_limit(self::CATE_ID_ZIXUN,8);
    }
    
    /**
     * 推荐活动
     * @return unknown
     */
    private function recom_actives(){
        
        $actives = D("Actives")->recom_actives(6);
        $club_ids = array();
        foreach($actives as $active){
            $club_ids[] = $active['club_id'];
        }
        $clubs = D("Club")->clubs_by_ids($club_ids);
        
        foreach($actives as &$active){
            $active['club'] = $clubs[$active['club_id']];
        }
        
        return $actives;
    }
    
    public function feedback(){
        $content   =   I('post.content');
        if(empty($content)){
            $data['status']  = 0;$data['content'] = '反馈内容不能为空！';$this->ajaxReturn($data);
        }
        $user_name   =   I('post.user_name');
        $ip = get_client_ip();
        $add_time = time();
        
        $feedback = M('feedback');
        $info['content'] = $content;
        $info['user_name'] = $user_name;
        $info['ip'] = $ip;
        $info['add_time'] = $add_time;
        $status = $feedback->add($info);
        if($status){
            $data['status']  = 1;$data['content'] = '反馈成功';$this->ajaxReturn($data);
        }else{
            $data['status']  = 0;$data['content'] = '反馈失败';$this->ajaxReturn($data);
        }
    }
    
    
    public function pay_index() {
        if (IS_POST) {
            //页面上通过表单选择在线支付类型，支付宝为alipay 财付通为tenpay
            $paytype = I('post.paytype');
            $order_sn = I('post.order_sn');
            $rec_id = I('post.rec_id');
            $order_id = I('post.order_id');
            $cname = I('post.cname');
            $card = I('post.card');
            $mobile = I('post.mobile');
            M('Order_goods_people')->where(array('order_id'=>$order_id,'rec_id'=>$rec_id))->delete();
            foreach ($cname as $key => $value) {
                $data['rec_id'] = $rec_id;
                $data['order_id'] = $order_id;
                $data['cname'] = $value;
                $data['mobile'] = $mobile[$key];
                $data['card_id'] = $card[$key];
                M('Order_goods_people')->add($data);
            }
            if(empty($order_sn)){
                echo "非法入口";return;
            }
            $order_sn_tmp = substr($order_sn, 2);
            if(!is_numeric($order_sn_tmp)){
                echo "非法参数";return;
            }
            session("order_sn", $order_sn);
            
            $pay = new \Think\Pay($paytype, C('payment.' . $paytype));
            $order_no = $pay->createOrderNo();
            
            $vo = new \Think\Pay\PayVo();
            $vo->setBody("商品描述")
                    ->setFee(I('post.money')) //支付金额
                    ->setOrderNo($order_no)
                    ->setTitle("商品标题")
                    ->setCallback("Home/Index/pay")
                    ->setUrl(U("Home/Index/paySuccess"))
                    ->setRoyalty_parameters("13817346713^0.01^分你的")
                    ->setRoyalty_type("10")
                    ->setParam(array('order_id' => $order_sn));//goods1业务订单号
            echo $pay->buildRequestForm($vo);
        } else {
            //在此之前goods1的业务订单已经生成，状态为等待支付
            $order_sn = I('get.order_sn');
            if(empty($order_sn)){
                echo "非法入口";return;
            }
            $order_sn_tmp = substr($order_sn, 2);
            if(!is_numeric($order_sn_tmp)){
                echo "非法参数";return;
            }
            $Order_info = M('Order_info')->where(array('order_sn'=>$order_sn))->find();
            
            $Order_info['goods'] = M('Order_goods')->where(array('order_id'=>$Order_info['order_id']))->find();
            
            $people = M('Order_goods_people')->where(array('order_id'=>$Order_info['order_id'],'rec_id'=>$Order_info['goods']['rec_id']))->select();
            
            if(empty($people)){
                $people = array();
                for($i=0;$i<$Order_info['goods']['goods_number'];$i++){
                    $people[] = array('cname'=>'','mobile'=>'','card_id'=>'');
                }
            }
            $this->assign("people",$people);
            $this->assign("order",$Order_info);
            $this->display();
        }
    }

    /**
     * 订单支付成功
     * @param type $money
     * @param type $param
     */
    public function pay($money, $param) {
        if (session("pay_verify") == true) {
            session("pay_verify", null);
            //处理goods1业务订单、改变good1业务订单状态
            $data = array();
            $data['money_paid'] = $money;
            $data['order_status'] = 1;
            $data['pay_status'] = 2;
            $data['pay_time'] = time();
            $temp = M("Order_info")->where(array('order_sn' => $param['order_id']))->save($data);
        } else {
            E("Access Denied");
        }
    }
    
     /**
     * 订单支付成功
     * @param type $money
     * @param type $param
     */
    public function paySuccess() {
        $order_sn = session("order_sn");
        $Order_info = M("Order_info")->where(array('order_sn' => $order_sn))->find();
        $this->assign("order",$Order_info);
        $this->display();
    }

}