<?php
namespace Common\Service;
class WeChatService {

    const APPID = "wx309a4210dd2211ab";
    const APPSECRET = "9f0e91a36c118a493fc637183250c28e";

    const MENU_WGW = "default_menu_wgw_";
    const MENU_HD  = "default_menu_hd_";
    const MENU_LD  = "default_menu_ld_";
    private $appid = "";
    private $appsecret = "";
    
    function WeChatService($appid,$appsecret){
        $this->appid = $appid;
        $this->appsecret = $appsecret;
    }
    /**
     * 创建menu
     * @param int $company_id
     * @return array("errcode"=>0,"errmsg"=>'ok')
     */
    function create_menu($company_id) {
        $access_token = $this->get_access_token();
        $result = 0;
        if (!empty($access_token)) {
            $post_data = $this->get_menu_data_tpl($company_id);
            $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
            $menu_json_str = $this->simple_https_curl($url, $post_data);
            $result = json_decode($menu_json_str, true);
        }
        return $result;
    }

    /**
     * 查询menu
     * @return array:
     */
    function get_menu() {
        $access_token = $this->get_access_token();
        $result = array();
        if (!empty($access_token)) {
            $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$access_token}";
            $menu_json_str = $this->simple_https_curl($url);
            $menu_arr = json_decode($menu_json_str, true);
            if (!empty($menu_arr)) {
                $result = $menu_arr['menu']['button'];
            }
        }
        return $result;
    }

    /**
     * 绑定微信，回复
     */
    function bind_or_response($uid){
        //绑定
        if (!empty($_GET['echostr'])) {
        
            $echostr = $_GET["echostr"];
            $wechat = D("Wechat")->where(array("uid"=>$uid))->find();
            if($wechat){
                $token = $wechat['token'];
                if($this->valid($token)){
                    D("Wechat")->where(array("uid"=>$uid))->save(array("is_bind"=>1));
                    echo $echostr;
                    exit;
                }
            }
            echo "";
            exit;
        } else {
            $xml_res = "";
            $xml_res = $this->responseMsg($uid);
            header('Content-Type:text/xml; charset=utf-8');
            echo $xml_res;
            exit;
        }
    }
    private function get_access_token() {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;
        $json_str = $this->simple_https_curl($url);
        $arr = json_decode($json_str, true);
        $access_token = 0;
        if (!empty($arr['access_token'])) {
            $access_token = $arr['access_token'];
        }
        return $access_token;
    }

    private function simple_https_curl($url, $post_data = array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        //curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($post_data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    private function get_menu_data_tpl($company_id=0){
        /*$config = array(
                "button"=>array(
                        array(
                                "name"=>"线路活动",
                                "type"=>"view",
                                "key"=>build_m_trip_list_url($company_id)
                        ),
                        array(
                                "name"=>"俱乐部",
                                "sub_button"=>array(
                                        array(
                                                "name"=>"微官网",
                                                "type"=>"view",
                                                "url"=>build_m_home_url($company_id)
                                        ),
                                        array(
                                                "name"=>"领队",
                                                "type"=>"view",
                                                "key"=>build_m_guide_list_url($company_id),
                                        ),
                                        array(
                                                "name"=>"俱乐部新闻",
                                                "type"=>"view",
                                                "url"=>build_m_company_news_url($company_id)
                                        ),
                                        array(
                                                "name"=>"户外知识",
                                                "type"=>"view",
                                                "url"=>build_m_company_knowledge_url($company_id)
                                        ),
                                        array(
                                                "name"=>"俱乐部介绍",
                                                "type"=>"view",
                                                "url"=>build_m_company_url($company_id)
                                        ),
                                        array(
                                                "name"=>"联系我们",
                                                "type"=>"view",
                                                "url"=>build_m_contact_url($company_id)
                                        ),
                                )
                        ),
                        array(
                                "name"=>"会员中心",
                                "sub_button"=>array(
                                        array(
                                                "name"=>"我的积分",
                                                "type"=>"view",
                                                "key"=>build_m_usert_active_url($company_id),
                                        ),
                                        array(
                                                "name"=>"参加的活动",
                                                "type"=>"view",
                                                "url"=>build_m_user_points_url($company_id)
                                        )
                                )
                        )
                )
        );
        return $this->encode_json($config);*/
    }

    //屏蔽json_encode的时候，中文会被unicode编码
    private function encode_json($str) {
        return urldecode(json_encode($this->url_encode($str)));
    }

    private function url_encode($str) {
        if(is_array($str)) {
            foreach($str as $key=>$value) {
                $str[urlencode($key)] = $this->url_encode($value);
            }
        } else {
            $str = urlencode($str);
        }

        return $str;
    }
    
    /**
     * 验证微信绑定
     */
    private function valid($token)
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
    
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
    
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;;
        }
    }
    private function responseMsg($company_id)
    {
        $postStr = file_get_contents("php://input");
        if(!empty($postStr)){
    
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $msgType = "news";
            $text_msg = $this->get_default_menu_data($company_id);//补充的帮助提示
            $articles = $this->get_articles_wgw($company_id);
    
            $xml_res = $this->get_reponse_xml($postObj,$msgType,$text_msg,$articles);
            return $xml_res;
        }else {
            return "";
        }
    }
    
    private function get_articles_wgw($company_id){
        $company_logo = null;//$this->get_company_logo($company_id);
    
        $data = array(
                array(
                 "title"=>"微官网",
                        "description"=>"",
                        "picurl"=>base_domain()."/Public/Mobile/images/mobile_index.jpg",
                        "url"=>U("Index/index",array("cid"=>$company_id),true,true),
    
                ), 
                array(
                        "title"=>"线路活动",
                        "description"=>"",
                        "picurl"=>base_domain()."/Public/Mobile/images/wx_home_active2.jpg",
                        "url"=>U("Actives/index",array("cid"=>$company_id),true,true),
    
                ), 
                array(
                        "title"=>"领队",
                        "description"=>"",
                        "picurl"=>base_domain()."/Public/Mobile/images/wx_home_leader2.jpg",
                        "url"=>U("Club/guide",array("cid"=>$company_id),true,true),
    
                ),
                array(
                        "title"=>"俱乐部介绍",
                        "description"=>"",
                        "picurl"=>base_domain()."/Public/Mobile/images/wx_home_dec2.jpg",
                        "url"=>U("Club/about",array("cid"=>$company_id),true,true),
                
                ), 
                array(
                        "title"=>"俱乐部相册",
                        "description"=>"",
                        "picurl"=>base_domain()."/Public/Mobile/images/wx_home_new2.jpg",
                        "url"=>U("Club/photo",array("cid"=>$company_id),true,true),
    
                )
        );
        return $data;
    }
    
    //补充的帮助提示
    private function get_default_menu_data($u_id){
        $html = '<a href="'.U("Actives/index",array("uid"=>$u_id),true,true).'">最新活动，【点击查看】</a>';
        return $html;
    }
    
    private function get_reponse_xml($postObj,$msgType,$text_msg,$articles){
    
        $fromUsername = (array)$postObj->FromUserName;
        $toUsername = (array)$postObj->ToUserName;
        $time = time();
        $xml_params = array(
                'FromUserName' => $fromUsername[0],
                'ToUserName' => $toUsername[0],
                'CreateTime' => $time,
                'msgType' => $msgType,
        );
        $xml_res = "";
        if ($msgType == "text") {
            $xml_params['Content'] = $text_msg;
            $xml_res = $this->get_simple_txt_xml_msg($xml_params);
        } else if ($msgType == "news") {
            $xml_params['ArticleCount'] = count($articles);
            $xml_params['Articles'] = $articles;
            $xml_res = $this->get_xml_msg($xml_params);
        }
        return $xml_res;
    }
    
    /**
     * Enter description here ...
     * @param array $xml_params
     */
    private function get_simple_txt_xml_msg($xml_params) {
        $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
    
        $resultStr = sprintf($textTpl, $xml_params['FromUserName'], $xml_params['ToUserName'], $xml_params['CreateTime'], $xml_params['msgType'], $xml_params['Content']);
    
        return $resultStr;
    }
    
    /**
     * Enter description here ...
     * @param array $xml_params
     */
    private function get_xml_msg($xml_params) {
        $textTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        %s
        </xml>";
    
        $article_cnt = $xml_params['ArticleCount'];
        $article_tpl .= "<ArticleCount>{$article_cnt}</ArticleCount><Articles>";
        for ($i=0; $i<$article_cnt; $i++) {
            if ($i == 0) {
                $tmp = $xml_params['Articles'][$i]['title'];// 14
                $article_tpl .= "<item>
                <Title><![CDATA[{$tmp}]]></Title>
                <Description><![CDATA[{$xml_params['Articles'][$i]['description']}]]></Description>
                <PicUrl><![CDATA[{$xml_params['Articles'][$i]['picurl']}]]></PicUrl>
                <Url><![CDATA[{$xml_params['Articles'][$i]['url']}]]></Url>
                </item>";
                } else {
        $tmp = $xml_params['Articles'][$i]['title']."\r\n".$xml_params['Articles'][$i]['description'];
                //$tmp = Util_StringUtils::truncate($tmp, 64, '');
                $article_tpl .= "<item>
                <Title><![CDATA[{$tmp}]]></Title>
                <Description><![CDATA[{$xml_params['Articles'][$i]['description']}]]></Description>
                <PicUrl><![CDATA[{$xml_params['Articles'][$i]['picurl']}]]></PicUrl>
                <Url><![CDATA[{$xml_params['Articles'][$i]['url']}]]></Url>
                </item>";
                }
    }
    $article_tpl .= "</Articles>";
    
    $resultStr = sprintf($textTpl, $xml_params['FromUserName'], $xml_params['ToUserName'], $xml_params['CreateTime'], $xml_params['msgType'], $article_tpl);
    
            return $resultStr;
    }
    private function get_company_logo($company_id){
        $sql = "SELECT logo FROM ecs_company WHERE company_id=".$company_id;
            $logo = $GLOBALS['db']->getOne($sql);
        return build_new_img_url($logo, "360", "200");
    }
}