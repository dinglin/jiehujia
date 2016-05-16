<?php
namespace Admin\Spider;
use Admin\Spider\SpiderBase;
use Admin\Model\PictureModel;
use Admin\Spider\SimpleHtmlDom;
use Admin\Controller\SpiderController;
/**
 * 8264模型
 */

class  Com8264 extends SpiderBase {
    
    /**
     * 初始列表数据
     */
    public static function reload_list_data(SpiderController $controller,SimpleHtmlDom $dom){
        //城市数据列表
        $city_urls = self::get_city_url();
        foreach($city_urls as $c_url_a){
            
            for($i = $c_url_a['min'];$i <= $c_url_a['max'] ; $i++){
                
                $next_page = true;
                
                $c_url = str_replace(URL_VAR, $i, $c_url_a['base_url']);
                
                $view_uls = array();
                try{
                    //读取活动列表
                    $html = $dom::file_get_html($c_url);
                    if(empty($html)){//again
                        $html = $dom::file_get_html($c_url);
                    }
                    $view_uls = self::get_list_urls($html);
                    
                }catch(\Exception $e){
                    $view_uls = array();
                }
                
                if(!empty($view_uls)){
                    foreach($view_uls as $view_url_a){
                        $view = array();
                        $view['from_url'] = $view_url_a['url'];
                        if(isset($view_url_a['club_name'])){
                            $view['club_name'] = $view_url_a['club_name'];
                        }
                        $view['departure'] = $c_url_a['city'];
                        //保存活动数据
                        $res = $controller->create_active_only($view);
                        if(!$res){//遇到已有数据即停止
                            $next_page = false;
                            break;
                        }
                    }
                }
                
                if(!$next_page){
                    break;
                }
            }
        }
    }
    
    public static function get_view_data($html){
        $result = array();
        if(empty($html)){
            return $result;
        }
        
        //领队
        $lxch_l = $html->find("div[class='lxch_l']",0);
        $result['guide_name'] = $lxch_l->find("a",0)->plaintext;
        $result['guide_logo'] = $lxch_l->find("img",0)->src;
        $result['guide_logo'] =  PictureModel::save_http_pic($result['guide_logo']);
        //标题
        $result['title'] = $html->find("a[id='thread_subject']",0)->plaintext;
        //$list_img
        $right = $html->find("div[class='t_fsz_new']",0);
        $list_img = $right->find("img",0)->src;
        $list_img =  PictureModel::save_http_pic($list_img);
        if(!$list_img){
            $list_img =  PictureModel::save_http_pic($list_img);
        }
        $result['list_pic'] = $list_img;
        //类型
        $basic_info = $right->find("div[class='actioncon']",0);
        $result['active_type'] = $basic_info->find("h3",0)->plaintext;
        //价格￥650/
        $price = $basic_info->find("b",0)->plaintext;
        $price = str_replace("￥", "", $price);
        $result['price'] = str_replace("/人", "", $price);
        //
        $ems = $basic_info->find("em"); 
        if(strpos($ems[0]->plaintext, "至") >0){
            $date = str_replace("商定", "", $ems[0]->plaintext);
            $date = explode("至", $date);
            $result['start_time'] = strtotime(trim($date[0]).":00");
            $result['end_time'] = strtotime(trim($date[1]).":00");
        }else{
            $result['start_time'] = strtotime(trim($ems[0]->plaintext).":00");
        }
        //目的地
        $result['destination'] = trim($ems[1]->plaintext);
        //报名结束
        $result['end_apply'] = strtotime(trim($ems[4]->plaintext).":00");
        //正文
        $content = $right->find("table",0);
        $content = self::do_content_tags($content);
        $result['content'] = $content['content'];
        //相册
        $result['imgs'] = self::do_content_imgs($html->find("div[class='lxch_new clboth']"),$content['content']);
        return $result;
    }
    /**
     * 处理正文中图片
     * @param unknown $html
     * @param unknown $imgs
     * @return multitype:unknown string
     */
    private static function do_content_imgs($html,$imgs){
        $result = array();
        for($i=1;$i<count($html);$i++){
            foreach($html[$i]->find("img") as $img){
                if(isset($img->file)){
                    $src = $img->file;
                }else{
                    $src = $img->src;
                }
                if(strpos($src, "image.8264.com/forum/")){
                    $md5_img = strval(md5($src));
                    if(!isset($imgs[$md5_img])){
                        $result[$md5_img] = PictureModel::save_http_pic($src);
                    }else{
                        $result[$md5_img] = $imgs[$md5_img];
                    }
                }
            }
        }
        return $result;
    }
    /**
     * 处理正文
     * @param unknown $content
     * @return multitype:mixed multitype:string
     */ 
    private static function do_content_tags($content){
        $imgs_res = array();
        $imgs = array();
        foreach($content->find("img") as $img){
            if(isset($img->file)){
                $src = $img->file;
            }else{
                $src = $img->src;
            }
           $image = PictureModel::save_http_pic($src);
           if($image){
               $imgs[] = $image;
               $imgs_res[strval(md5($src))] = $image;
           }
        }
        foreach($content->find("font") as $font){
            if(isset($font->style)){
                $font->style = null;
            }
            if(isset($font->face)){
                $font->face = null;
            }
        }
        foreach($content->find("div") as $div){
            if(isset($div->style)){
                $div->style = null;
            }
        }
        $content = preg_replace('/<img[^>]*>/i', "@!img#@", $content->outertext);
        $content = preg_replace('/<br[^>]*>/i', "@!br#@", $content);
        $content = preg_replace('/<p[^>]*>/i', "@!pl#@", $content);
        $content = preg_replace('/<\/\s*p>/i', "@!pr#@", $content);
    
        $content = strip_tags($content);
        
        $content = explode("@!br#@", $content);
        $content = implode("<br/>", $content);
       
        $content = explode("@!pl#@", $content);
        $content = array_filter($content);
        foreach ($content as &$con){
            $con = "<p>" . $con;
        }
        $content = implode("", $content);
        
        $content = explode("@!pr#@", $content);
        $content = array_filter($content);
        foreach ($content as &$con){
            $con =  $con . "</p>" ;
        }
        $content = implode("", $content);
        
        $content = explode("@!img#@", $content);
        foreach ($imgs as $key=>$img){
            $img = "<br/><img src='$img' /><br/>";
            $content[$key] .=$img;
        }
        $content = implode("", $content);
        
        $content = preg_replace('/上传[^\)]*KB\)/i', "", $content);
        $content = str_replace("下载积分: 驴币 -1", "", $content);
        for($i=0;$i<4;$i++){
            $content = preg_replace('/\s*<br\/>\s*<br\/>\s*/i', "<br/>", $content);
            $content = preg_replace('/\s*<p>\s*<br\/>\s*/i', "<p>", $content);
            $content = preg_replace('/\s*<br\/>\s*<p\/>\s*/i', "<p/>", $content);
        }
        return array("content"=>$content,"imgs"=>$imgs_res);
    }
    /**
     * 获取列表数据
     */
    private static function get_list_urls($html){
        $result = array();
        if(empty($html)){
            return $result;
        }
        
        //俱乐部or领队
        $bbslistbox = $html->find("table[class='bbslistbox']",0)->find("tr");
        $i=0;
        foreach($bbslistbox as $tr){
            if($i>0 && !empty($tr)){
                $title = $tr->find("table[class='titletable']",0);
                if($title){
                    $club_name = $title->find("td[class='fl_17_no']",1);
                    if($club_name){
                        $result[$i]['club_name'] = strip_tags($club_name->plaintext);
                    }
                    $result[$i]['url'] = $title->find("a[class='f_16']",0)->href;
                }
            }
            $i++;
        }
        return $result;
    }
    /**
     * 获取文章id
     */
    function get_view_id($url){
        $id = 0;
        if($url){
            $url = explode("-",$url);
            $id = $url[1];
        }
        return $id;
    }
    /**
     * 获取城市
     */
    private static function get_city_url(){
        $result = array(
                array(
                    "base_url"=>"http://bbs.8264.com/forum-forumdisplay-fid-48-filter-specialtype-specialtype-activity-page-".URL_VAR.".html",
                    "min"=>1,
                    "max"=>5,
                    "city"=>"上海"
                ), 
               /*  array(
                        "base_url"=>"http://bbs.8264.com/forum-forumdisplay-fid-109-filter-specialtype-specialtype-activity-page-".URL_VAR.".html",
                        "min"=>1,
                        "max"=>4,
                        "city"=>"江苏"
                ),
                array(
                        "base_url"=>"http://bbs.8264.com/forum-forumdisplay-fid-147-filter-specialtype-specialtype-activity-page-".URL_VAR.".html",
                        "min"=>1,
                        "max"=>4,
                        "city"=>"浙江"
                ), */
        );
        return $result;
    }
}
