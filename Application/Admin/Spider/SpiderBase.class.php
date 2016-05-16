<?php
namespace Admin\Spider;
use Think\Model;
use Admin\Model\PictureModel;
use Admin\Spider\SimpleHtmlDom;
use Admin\Controller\SpiderController;
/**
 * base模型
 */

abstract class  SpiderBase extends Model {
   
    /**
     * 取单页数据
     * @param string $url
     */
    abstract public static function get_view_data($html);
    /**
     * 刷新列表数据
     * @param string $url
     */
    abstract public static function reload_list_data(SpiderController $controller,SimpleHtmlDom $dom);
    
    /**
     * 过滤a标签
     * 保存图片，没有存储到相册
     */
    public static function execute_content($obj_html){
        
        foreach($obj_html->find("a") as $a){
            $a->href= null;
        }
        foreach($obj_html->find("img") as $img){
            $img->src = PictureModel::save_http_pic($img->src);
        }
        foreach($obj_html->find("script") as $img){
            $img->src = "";
        }
        foreach($obj_html->find("iframe") as $img){
            $img->src = "";
        }
        return $obj_html;
    }
}