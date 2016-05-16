<?php
namespace Admin\Spider;
use Admin\Spider\SpiderBase;
use Admin\Model\PictureModel;
use Admin\Spider\SimpleHtmlDom;
use Admin\Controller\SpiderController;
/**
 * 8264模型
 */

class Quyeba extends SpiderBase {
    
    public static function reload_list_data(SpiderController $controller,SimpleHtmlDom $dom){
        
        $c_url_a = self::get_list_urls();
        
        $next_page = true;
        
        for($i = $c_url_a['min'];$i <= $c_url_a['max'] ; $i++){
            
            $c_url = str_replace(URL_VAR, $i, $c_url_a['base_url']);
            
            $c_url = $c_url_a['domain'].$c_url;
            
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
    /**
     * 单页数据
     */
    public static function get_view_data($html){
        $result = array();
        if(empty($html)){
            return $result;
        }
        
        //俱乐部or领队
        $club = $html->find("a[uid]",0);
        $club_logo = $club->find("img",0)->src;
        $index = strpos($club_logo, "!");
        $club_logo = substr($club_logo, 0,$index);
        $result['club_logo'] = PictureModel::save_http_pic($club_logo);
        $result['club_name'] = $club->find("img",0)->alt;
        
        $tabs_1 = $html->find("div[id='tabs-1']",0);
        //标题
        $result['title'] = $tabs_1->find("p[class='title']",0)->plaintext;
        //相册
        $imgs = $tabs_1->find("div[id='spec-list']",0)->find("img");
        foreach($imgs as $img){
            $index = strpos($img->src, "!");
            $src = substr($img->src, 0,$index);
            $result['imgs'][] = PictureModel::save_http_pic($src);
        }
        $trs = $tabs_1->find("tr[class='hdlist']");
        foreach($trs as $tr){
            $tmp = self::get_base_data($tr->find("td",0)->plaintext,$tr->find("td",1)->plaintext);
            $result = array_merge($result,$tmp);
        }
        //content
        $tabs_2_1 = $html->find("div[id='tabs-2']",0)->find("div[class='hdnr']",0);
        foreach($tabs_2_1->find("a") as $a){
            $a->href= null;
        }
        foreach($tabs_2_1->find("img") as $img){
            $img->src = PictureModel::save_http_pic($img->src);
        }
        $tabs_2_2 = $html->find("div[id='tabs-2']",0)->find("div[class='dpbox']",0);
        $tabs_3_1 = $html->find("div[id='tabs-3']",0)->find("div[class='hdnr']",0);
        $result['content'] = $tabs_2_1->innertext.$tabs_2_2->innertext.$tabs_3_1->innertext;
        return $result;
    }
    
    private static function get_base_data($name,$value){
        $result = array();
        switch ($name){
            case "活动日期:"://2014年04月05日12点00分钟~2014年04月09日15点00分钟(5天)
                $index = strpos($value, "(");
                $date = substr($value, 0,$index);
                $date = str_replace("年", "-", $date);
                $date = str_replace("月", "-", $date);
                $date = str_replace("日", " ", $date);
                $date = str_replace("点", ":", $date);
                $date = str_replace("分钟", ":", $date);
                $date = explode("~", $date);
                $result['start_time'] = strtotime(trim($date[0])."00");
                $result['end_time'] = strtotime(trim($date[1])."00");
                break;
            case "出发地:":
                $result['departure'] = trim($value);
                break;
            case "目的地:":
                $result['destination'] = trim($value);
                break;
            case "活动类型:":
                $result['active_type'] = trim($value);
                break;
            case "难度级别:":
                $result['active_grade'] = trim($value);
                break;
            case "活动领队:":
                $result['guide_name'] = trim($value);
                break;
            case "领队电话:":
                $result['guide_phone'] = trim($value);
                break;
            case "队员上限:":
                $result['people_limit'] = trim($value);
                break;
            case "人均价格:":
                $result['price'] = trim($value);
                break;
            case "住宿方式:":
                $result['stay_type'] = trim($value);
                break;
            case "交通工具:":
                $result['traffic'] = trim($value);
                break;
            case "报名截止:":
                '2014年04月02日13点00分或名额满';
                $index = strpos($value, "分");
                $date = substr($value, 0,$index);
                $date = str_replace("年", "-", $date);
                $date = str_replace("月", "-", $date);
                $date = str_replace("日", " ", $date);
                $date = str_replace("点", ":", $date);
                $result['end_apply'] = strtotime($date.":00");
                break;
            default:
                break;
        }
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
        $as = $html->find("div[class='title']");
        
        foreach ($as as $a){
            $result[] = $domain.$a->find("a",0)->href;
        }
        return $result;
    }
    /**
     * 列表url
     */
    private static function get_list_urls(){
        $result = array(
                "domain"=>"http://www.thenorthface.com.cn",
                "base_url"=>"/activity?page=".URL_VAR."&c=%E4%B8%8A%E6%B5%B7",
                "min"=>0,
                "max"=>19
        );
        return $result;
    }
}