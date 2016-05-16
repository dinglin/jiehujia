<?php
namespace Admin\Spider;
use Think\Model;
use Admin\Spider\SimpleHtmlDom;
use Admin\Spider\Com8264;
use Admin\Spider\Quyeba;
use Admin\Spider\Youxiake;
use Admin\Spider\Com54traveler;
use Admin\Spider\Sanfo;
use Admin\Spider\Ioutside;
use Admin\Controller\SpiderController;
/**
 * base模型
 */

class  SpiderFactory extends Model {
    /**
     * domain=>class
     * @return multitype:string
     */
    private static function SITE_CONFIG(){
        return array(
                "8264.com"           => 'Admin\Spider\Com8264',
                "thenorthface.com.cn"=> 'Admin\Spider\Quyeba',
                "youxiake.com"       => 'Admin\Spider\Youxiake',
                "54traveler.com"     => 'Admin\Spider\Com54traveler',
                "sanfo.com"          => 'Admin\Spider\Sanfo',
                "ioutside.cn"       => 'Admin\Spider\Ioutside',
        );
    }
	/**
	 * 更新列表数据
	 * @param SpiderController $controller
	 * @param String $domain
	 */
    public static function reload_list_urls(SpiderController $controller,$domain){
        define("URL_VAR", "@var");
        $dom = new SimpleHtmlDom();
        $com_model = self::get_spider_object($domain);
        $com_model::reload_list_data($controller,$dom);
    }
    /**
     * 获取单页数据
     * @param unknown $url
     * @return array
     */
    public static function get_view_data($url){
        $result = array();
        if($url){
            $html = self::get_html_dom($url);
            if(empty($html)){//again
                $html = self::get_html_dom($url);
            }
            if($html){
                $object = self::get_spider_object($url);
                try{
                    $result  = $object::get_view_data($html);
                }catch(\Exception $e){
                    $result = array();
                }
            }
        }
        return $result;
    }
    /**
     * 获取爬虫对象
     * @param string $from_url
     * @return Ambigous <\Admin\Spider\SpiderBase>
     */
    private static function get_spider_object($from_url){
        $com_model = "";
        $configs = self::SITE_CONFIG();
        foreach($configs as $domain => $class){
            if(self::is_form_url($domain,$from_url)){
                $com_model = new $class();
            }
        }
        return $com_model;
    }
    /**
     * 判断地址
     * @param unknown $str
     * @param unknown $url
     * @return boolean
     */
    private static function is_form_url($str,$url){
        return str_replace($str, "", $url) != $url;
    }
    
    //提取dom数据
    private static function get_html_dom($view_url){
        $dom = new SimpleHtmlDom();
        return $dom::file_get_html($view_url);
    }
}