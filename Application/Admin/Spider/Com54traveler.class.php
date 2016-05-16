<?php
namespace Admin\Spider;
use Admin\Spider\SpiderBase;
use Admin\Model\PictureModel;
use Admin\Spider\SimpleHtmlDom;
use Admin\Controller\SpiderController;
/**
 * 54traveler.com模型
 */

class Com54traveler extends SpiderBase {
    
    public static function reload_list_data(SpiderController $controller,SimpleHtmlDom $dom){
        $c_url_a = self::get_list_urls();
        self::do_list_data($c_url_a['base_url'],$controller,$dom);
        self::do_list_data($c_url_a['base_url2'],$controller,$dom);
    }
    /**
     * 处理列表
     * @param unknown $c_url
     */
    private static function do_list_data($c_url,SpiderController $controller,SimpleHtmlDom $dom){
        $list_url = array();
        try{
            //读取活动列表
            $html = $dom::file_get_html($c_url);
            if(empty($html)){//again
                $html = $dom::file_get_html($c_url);
            }
            $list_url = self::get_list_data($html);
        
        }catch(\Exception $e){
            $list_url = array();
        }
        if(!empty($list_url)){
            foreach($list_url as $url){
                $view = array();
                $view['from_url'] = $url;
                //保存活动数据
                $res = $controller->create_active_only($view);
            }
        }
    }
    
    /**
     * 单页数据
     */
    public static function get_view_data($html){
        $result = array();
        if(empty($html)){
            return $result;
        }
        
        //俱乐部or领队
        $result['club_name'] = "稻草人旅行";
        $content = "";
        $jianjie = $html->find("div[id='content_wrapper_introduction']",0);
        
        //标题
        $jianjie->find("div[class='days_number_day']",0)->innertext="";
        $result['title'] = $jianjie->find("div[id='content_wrapper_home_name']",0)->plaintext;
        
        //目的地
        $mudidi = $jianjie->find("p[class='text']",1)->plaintext;
        $mudidi = explode("\n", $mudidi);
        $result['destination'] = $mudidi[0];
        //出发地
        $result['departure'] = "不限";
        //相册 获取不到
        
        $tr_ = $html->find("table[class='side_table']",0)->find("tr[class='content_sidebar_infomation']",0);
        //活动时间
        $times = $tr_->find("td",0)->plaintext;
        $times = explode("~", $times);
        $str_time = date("Y").".".$times[0];
        $result['start_time'] = strtotime(str_replace(".","-", $str_time));
        $end_time = date("Y").".".$times[1];
        $result['end_time'] = strtotime(str_replace(".","-", $end_time));
        //价格
        $price = $tr_->find("td",2)->plaintext;
        $result['price'] = intval(str_replace("￥", "", $price));
        //报名截止时间
        $result['end_apply'] = strtotime(str_replace(".","-", $end_time));
        
        $jianjie = self::execute_content($jianjie);
        $content .= $jianjie->innertext;
        
        $con = $html->find("div[id='content_wrapper_light']",0);
        $con = self::execute_content($con);
        $content .= $con->innertext;
        
        $con = $html->find("div[id='content_wrapper_travel']",0);
        $con = self::execute_content($con);
        $content .= $con->innertext;
        
        $con = $html->find("div[id='content_wrapper_note']",0);
        $con = self::execute_content($con);
        $content .= $con->innertext;
        
        $con = $html->find("div[id='content_wrapper_cost']",0);
        $con = self::execute_content($con);
        $content .= $con->innertext;
        
        $content = preg_replace('/<!--[^>]*>/i', "", $content);
        $result['content'] = $content;
        
        return $result;
    }
    /**
     * 获取列表中单页url
     */
    private static function get_list_data($html,$domain){
        $result = array();
        if(empty($html)){
            return $result;
        }
        $as = $html->find("div[class='hero-unit']");
        
        foreach ($as as $a){
            $href = $domain.$a->find("a",0)->href;
            if(str_replace("red_clock", "", $href) !=$href){
                $result[] =$href;
            }
        }
        return $result;
    }
    /**
     * 列表url
     */
    private static function get_list_urls(){
        $result = array(
                "domain"=>"http://www.54traveler.com",
                "base_url"=>"http://www.54traveler.com/index.php?action=expedition&style=chang",
                "base_url2"=>"http://www.54traveler.com/index.php?action=expedition&style=duan",
        );
        return $result;
    }
}