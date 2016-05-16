<?php
namespace Home\Controller;
use Common\Service\WeChatService;
class WechatController extends HomeController {
    protected function _initialize(){
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置
    }
    public function index($id){
        $uid = $id;
        if($uid){
            $uid = base64_decode($uid);
            if($uid){
                $wcs = new WeChatService();
                $wcs->bind_or_response($uid);
            }
        }
    }
   
}