<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mobile\Controller;
use Common\Service\SmsService;

/**
 * 5间房
 */
class FiveRoomController extends HomeController {

	//系统首页
    public function index(){
        $this->display("apply");
    }

    
    public function apply() {
        if(IS_POST){
            
            $nickname = I("post.nickname");
            $gender = I("post.gender");
            $mylike = I("post.mylike");
            $age = I("post.age");
            $reason = I("post.reason");
            $mobile = I("post.mobile");
            $code = I("post.code");
            if(!$nickname || !$gender || !$mylike || !$age || !$reason || !$mobile || !$code){
                $this->error('输入参数错误！');
            }
            $sms = new SmsService();
            //检测验证码
            if(!$sms->verify($mobile,$code)){
                $this->error('验证码输入错误！');
            }
            $apply = M("Fjf_apply")->where(array("mobile"=>$mobile))->getField("id,mobile");
            if($apply){
                $this->error('您已经提交了申请信息，请耐心等待哦！');
            }
            $data = array(
                    "nickname" =>$nickname,
                    "gender" =>$gender,
                    "mylike" =>$mylike,
                    "age"    =>intval($age),
                    "reason" =>$reason,
                    "mobile" =>$mobile,
            );
            M("Fjf_apply")->add($data);
            //发送短信
            $sms->sendMsgOnApplySubmit($mobile);
            $this->success("申请成功");
        }else{
            $this->display();
        }
    }
    /**
     * 发送验证码
     */
    public function sendRandomCode(){
        if(IS_POST){
            $mobile = I("post.mobile");
            if(!$mobile || strlen($mobile)!=11){
                $this->error('手机号错误');
            }
            $apply = M("Fjf_apply")->where(array("mobile"=>$mobile))->getField("id,mobile");
            if($apply){
                $this->error('您已经提交了申请信息，请耐心等待哦！');
            }
            
            $sms = new SmsService();
            $res = $sms->sendRandomCode($mobile);
            $this->success($res);
        }else{
            $this->display("apply");
        }
    }
    
    //抢房
    public function detail() {
        if(IS_POST){
            $mobile = I("post.mobile");
            $code = I("post.code");
            if(!$mobile || !$code){
                $this->error('输入参数错误！');
            }
            $sms = new SmsService();
            //检测验证码
            if(!$sms->verify($mobile,$code)){
                $this->error('验证码输入错误！');
            }
            $data = array(
                    "mobile" =>$mobile,
                    "ip"=>get_client_ip(),
                    "add_time"=>time()
            );
            if($mobile=='18616208302'){
                M("Fif_get_room_log")->add($data);
                $this->success("true");
                
            }else{
                $this->error('false');
            }
        }else{
            $this->assign("webroot", $_SERVER['HTTP_HOST']);
            $this->display();
        }
    }

}