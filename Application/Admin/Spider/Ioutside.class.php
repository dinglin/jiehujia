<?php
namespace Admin\Spider;
use Admin\Spider\SpiderBase;
use Admin\Model\PictureModel;
use Admin\Spider\SimpleHtmlDom;
use Admin\Controller\SpiderController;
/**
 * ioutside.cn模型
 */

class Ioutside extends SpiderBase {
    
    public static function reload_list_data(SpiderController $controller,SimpleHtmlDom $dom){
        $c_url_a = self::get_list_urls();
        foreach($c_url_a as $c_url_s){
            for($i=$c_url_s['min'];$i<=$c_url_s['max'];$i++){
                $c_url = str_replace(URL_VAR, $i, $c_url_s['base_url']);
                $list_url = array();
                try{
                    //读取活动列表
                    $html = $dom::file_get_html($c_url);
                    if(empty($html)){//again
                        $html = $dom::file_get_html($c_url);
                    }
                    $list_url = self::get_list_data($html,$c_url_s['domain']);
                
                }catch(\Exception $e){
                    $list_url = array();
                }
                if(!empty($list_url)){
                    foreach($list_url as $url){
                        $view = array();
                        $view['from_url'] = $url['href'];
                        $view['destination'] = $url['tar'];
                        //保存活动数据
                        $res = $controller->create_active_only($view);
                    }
                }
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
        $result['club_name'] = "黑眼睛户外（野孩子旅行）";
        $content = "";
        
        //标题
        $title = $html->find("h1[class='line_name']",0)->plaintext;
        $result['title'] = $title;
        //天数
        $days = $html->find("div[class='rili']",0)->find("p[class='num']")->plaintext;
        $result['days'] = $days;
        //列表图
        $result['list_pic'] = $base_url[0]['domain'].$html->find("img[class='line_big']",0)->src;
        $result['list_pic'] = PictureModel::save_http_pic($result['list_pic']);
        
        //出发地
        $result['departure'] = "不限";
        
        //相册 
        $imgs_src = $html->find('img[class="slide_ditu"]',0)->src;
        $imgs_src = $base_url[0]['domain'].$imgs_src;
        $imgs_src = PictureModel::save_http_pic($imgs_src);
        $result['imgs'] = array($imgs_src);
        
        //活动时间
        $mainInfo = $html->find("div[class='line_baoming']",0);
        $times = $mainInfo->find("p[class='rows']",0)->plaintext;
        $times = explode("~", $times);
        $result['start_time'] = strtotime($times[0]);
        $result['end_time'] = strtotime($times[1]);
        
        //价格
        $price = $html->find("p[class='price']",0)->plaintext;
        $price = str_replace("￥", "", $price);
        $price = str_replace("/人", "", $price);
        $price = str_replace("\n", "", $price);
        $result['price'] = intval($price);
        
        //报名截止时间
        $end_apply = $html->find("p[class='rows_ta_c']",0)->plaintext;
        $end_apply = str_replace("报名截止日期：", "", $end_apply);
        $result['end_apply'] = strtotime($end_apply);
        
        //正文
        $box1 = $html->find("div[class='main_line_content']",0);
        $box1 = self::execute_content($box1);
        $content .= $box1->innertext;
        
        $tab_uls = $html->find("ul[class='line_detail_tab']",0);
        $as = $tab_uls->find("a[class='list']");
        foreach($as as $a){
            $html = self::get_html_dom($base_url[0]['domain'].$a->href);
            if(empty($html)){//again
                $html = self::get_html_dom($base_url[0]['domain'].$a->href);
            }
            if($html){
                $box = $html->find("div[class='main_line_content']",0);
                $box = self::execute_content($box);
                $content .= $box->innertext;
            }
        }
        
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
        $as = $html->find("div[class='xianlu']");
        
        foreach ($as as $a){
            $str = $a->find("a[class='join_btn']",0)->plaintext;
            if($str == "报名"){
                $href = $domain.$a->find("a",0)->href;
                $tar  = $a->find("p[class='ta_l']",0)->plaintext;
                $result[] =array(
                        "href"=>$href,
                        "tar" =>$tar
                );
            }
        }
        return $result;
    }
    /**
     * 列表url
     */
    private static function get_list_urls(){
        $result = array(
                array(
                        "base_url"=>"http://www.ioutside.cn/Index/get_xianllulist?&tr_long_short=1&p=".URL_VAR."&month=1,2,3,4,5,6,7,8,9,10,11,12,&pro=1,2,3,4,5,6,7,8,9,10,11,12",
                        "min"=>1,
                        "max"=>6,
                        "city"=>"上海",
                        "domain"=>"http://www.ioutside.cn",
                ),
                array(
                        "base_url"=>"http://www.ioutside.cn/Index/get_xianllulist?&tr_long_short=2&p=".URL_VAR."&month=1,2,3,4,5,6,7,8,9,10,11,12,&pro=1,2,3,4,5,6,7,8,9,10,11,12",
                        "min"=>1,
                        "max"=>1,
                        "city"=>"上海",
                        "domain"=>"http://www.ioutside.cn",
                ),
        );
        return $result;
    }
    //提取dom数据
    private static function get_html_dom($view_url){
        $dom = new SimpleHtmlDom();
        return $dom::file_get_html($view_url);
    }
}