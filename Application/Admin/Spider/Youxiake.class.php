<?php
namespace Admin\Spider;
use Admin\Spider\SpiderBase;
use Admin\Model\PictureModel;
use Admin\Spider\SimpleHtmlDom;
use Admin\Controller\SpiderController;
/**
 * 8264模型
 */

class Youxiake extends SpiderBase {
    
    public static function reload_list_data(SpiderController $controller,SimpleHtmlDom $dom){
        //区域入口
        $areas = self::get_area_urls();
        foreach ($areas as $area){
            //板块
            $sub_datas = array();
            try{
                //读取活动列表
                $html = $dom::file_get_html($area['base_url']);
                if(empty($html)){//again
                    $html = $dom::file_get_html($area['base_url']);
                }
                $sub_datas = self::get_area_sub_data($html,$area['base_url'],$area['cate']);
            
            }catch(\Exception $e){
                $sub_datas = array();
            }
            foreach ($sub_datas as $c_url){
                
                $view_uls = array();
                try{
                    //读取活动列表
                    $html = $dom::file_get_html($c_url);
                    if(empty($html)){//again
                        $html = $dom::file_get_html($c_url);
                    }
                    $view_uls = self::get_active_list($html);
                
                }catch(\Exception $e){
                    $view_uls = array();
                }
                foreach($view_uls as $url){
                    $view = array();
                    $view['from_url'] = $area['base_url'].$url;
                    //保存活动数据
                    $res = $controller->create_active_only($view);
                    if(!$res){//遇到已有数据即停止
                        break;
                    }
                }
            }
        }
    }
    /**
     * 列表url no base url
     */
    private static function get_active_list($html){
        $result = array();
        if(empty($html)){
            return $result;
        }
        //标题
        foreach($html->find("font[color]") as $a){
            $result[] = $a->parent()->href;
        }
        return $result;
    }
    
    /**
     * 获取活动单页数据
     */
    public static function get_view_data($html){
        $result = array();
        if(empty($html)){
            return array();
        }
        //标题
        $result['title'] = $html->find("span[id='rtitle']",0)->plaintext;
        //图片
        $img = $html->find("div[id='actpic']",0);
        $result['list_pic'] = $img->find("img",0)->src;
        $result['list_pic'] = PictureModel::save_http_pic($result['list_pic']);
        
        $left_content = $html->find("td[class='left_border']", 0);
        
        $table = $left_content->children(0)->find("table",0);
        
        //活动类型
        $result['active_type'] = $table->find("tr",0)->find("td",1)->find("a",0)->plaintext;
        //活动时间
        $time = $table->find("tr",1)->find("td",1)->plaintext;
        $time = str_replace("&nbsp;", "", $time);
        $time = explode("~", $time);
        $result['start_time'] = strtotime($time[0]);
        $result['end_time'] = strtotime($time[1]);
        //交通工具
        $result['traffic'] = $table->find("tr",1)->find("td",3)->plaintext; 
        //目 的 地
        $target_place = $table->find("tr",2)->find("td",0)->next_sibling();
        $places = $target_place->plaintext;
        $index = strpos($places, "集 结 地");
        $places = substr($places, 0,$index);
        $result['destination'] = $places;
        //集 结 地
        $target_place = $table->find("tr",2)->find("td",3)->find("a");
        $places = array();
        foreach($target_place as $place){
            $places[] = $place->plaintext;
        }
        $result['departure'] = implode(" ",$places);
        //活动费用
        $prices = $table->find("tr",3)->find("td",1);
        $result['price'] = $prices->find("span",0)->plaintext;
        $result['price_child'] = $prices->find("span",1)->plaintext;
        //正文
        $content = $left_content->children(1)->children(1);
        foreach($content->find("a") as $a){
            $a->href= null;
        }
        $imgs = array();
        foreach($content->find("img") as $img){
            $img->src = PictureModel::save_http_pic($img->src);
            $imgs[] = $img->src;
        }
        $result['imgs'] = $imgs;
        $result['content'] = str_replace("(点击这里哦)", "", $content->innertext);
        return $result;
    }
    
    /*圈子下子区域入口*/
    private static function get_area_sub_data($html,$url,$cate){
        $result = array();
        if(empty($html)){
            return $result;
        }
        foreach($cate as $cat){
            $table = $html->find("table[id='$cat']",0);
            if(!empty($table)){
                $urls = array();
                foreach($table->find('a') as $a){
                    $urls[] = $url.$a->href ;
                }
                array_shift($urls);
                $result = array_merge($result,$urls);
            }
        }
        return $result;
    }
    /*圈子数据入口*/
    private static function get_area_urls(){
        $data = array(
                array(
                        "base_url"=>"http://xibu.youxiake.com/",
                        "cate"    =>array("act251"),
                ),
                array(
                        "base_url"=>"http://photo.youxiake.com/",
                        "cate"    =>array("act50","act116","act168"),
                ),
                array(
                        "base_url"=>"http://caoyuan.youxiake.com/",
                        "cate"    =>array("act252"),
                ),
                array(
                        "base_url"=>"http://in.youxiake.com/",
                        "cate"    =>array("act24"),
                ),
                array(
                        "base_url"=>"http://out.youxiake.com/",
                        "cate"    =>array("act250","act259"),
                ),
                array(
                        "base_url"=>"http://shanghai.youxiake.com/",
                        "cate"    =>array("act179","act186","act228"),
                ),
                array(
                        "base_url"=>"http://xia.youxiake.com/",
                        "cate"    =>array("act11","act13","act46"),
                ),
        );
        return $data;
    }
}