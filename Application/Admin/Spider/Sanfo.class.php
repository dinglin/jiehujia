<?php
namespace Admin\Spider;
use Admin\Spider\SpiderBase;
use Admin\Model\PictureModel;
use Admin\Spider\SimpleHtmlDom;
use Admin\Controller\SpiderController;
/**
 * Sanfo.com模型
 */

class Sanfo extends SpiderBase {
    
    public static function reload_list_data(SpiderController $controller,SimpleHtmlDom $dom){
        $c_url_a = self::get_list_urls();
        $c_url = $c_url_a['base_url'];
        $list_url = array();
        try{
            //读取活动列表
            $html = $dom::file_get_html($c_url);
            if(empty($html)){//again
                $html = $dom::file_get_html($c_url);
            }
            $list_url = self::get_list_data($html,$c_url_a['domain']);
        
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
        $base_url = self::get_list_urls();
        
        //俱乐部or领队
        $result['club_name'] = "三夫户外";
        $content = "";
        //列表图
        $mainPic = $html->find("div[class='mainPic']",0);
        $result['list_pic'] = $base_url['domain'].$mainPic->find("img",0)->src;
        $result['list_pic'] = str_replace("img_t_", "", $result['list_pic']);
        $result['list_pic'] = PictureModel::save_http_pic($result['list_pic']);
        
        //标题
        $title = $mainPic->find("div[class='title']",0)->plaintext;
        $title = trim(str_replace("新上线", "", $title));
        $title = str_replace("&nbsp;", "", $title);
        $result['title'] = $title;
        
        //目的地
        $result['destination'] = '';
        
        //出发地
        $result['departure'] = "上海";
        
        //相册 
        $imgs_src = $html->find("div[class='picScroll']",0)->find('img');
        $imgs = array();
        foreach($imgs_src as $img){
            $img->src = str_replace("img_s_", "", $img->src);
            $img->src = PictureModel::save_http_pic($base_url['domain'].$img->src);
            $imgs[] = $img->src;
        }
        $result['imgs'] = $imgs;
        
        //活动时间
        $mainInfo = $html->find("div[class='mainInfo']",0);
        $times = $mainInfo->find("div[class='date']",0)->plaintext;
        $result['start_time'] = strtotime(trim($times));
        $schedule = $mainInfo->find("div[class='schedule']",0)->plaintext;
        $result['end_time'] = $result['start_time']+intval($schedule) * 24 * 60 * 60;
        
        //领队
        $guide_name = $html->find("div[class='otherInfo']",0)->find('div[class="leader"]',0)->find('a',0)->plaintext;
        $result['guide_name'] = $guide_name;
        
        //价格
        $price = $html->find("div[class='otherInfo']",0)->find('div[class="price"]',0)->find('h3',0)->plaintext;
        $price = str_replace("￥", "", $price);
        $price = str_replace("元/人", "", $price);
        $price = str_replace("\n", "", $price);
        $result['price'] = intval($price);
        //报名截止时间
        $result['end_apply'] = $result['start_time'];

        //正文
        $box1 = $html->find("div[id='box_1']",0);
        $box1 = self::execute_content($box1);
        $content .= $box1->innertext;
        
        $box2 = $html->find("div[id='box_2']",0);
        $box2 = self::execute_content($box2);
        $content .= $box2->innertext;
        
        $box_3 = $html->find("div[id='box_3']",0);
        $box_3 = self::execute_content($box_3);
        $content .= $box_3->innertext;
        
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
        $as = $html->find("div[class='listItem2']");
        
        foreach ($as as $a){
            $href = $domain.$a->find("a",0)->href;
            $result[] =$href;
        }
        return $result;
    }
    /**
     * 列表url
     */
    private static function get_list_urls(){
        $result = array(
                "domain"=>"http://travel.sanfo.com",
                "base_url"=>"http://travel.sanfo.com/place/%E4%B8%8A%E6%B5%B7-1.html",
        );
        return $result;
    }
}