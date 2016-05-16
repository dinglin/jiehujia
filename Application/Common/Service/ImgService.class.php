<?php
namespace Common\Service;

/**
 * 不足：
 * 1、需要实例化，按尺寸保存到本地
 * 2、保存网络图片到本地，再裁剪
 * 3、允许只截取定宽或定高
 * 4、允许图片尺寸不足时返回原尺寸
 * 5、允许增加水印
 * @var unknown
 */
class ImgService {
    
    const IMG_CACHE_TIME = 86400;
    const DEFAULT_ACTIVE_IMG = "Public/Home/images/default_active.jpg";
    const IMG_BASE_PATH = ROOT_PATH ;
    
    
    public function index($img,$size){
        
        $img_path = trim($img);
        $img_size = trim($size);
       
        if($img_path && $img_size){
        
            $img_path = base64_decode($img_path);
            
            $img_size = $this->check_img_size_open($img_size);
        
            if($img_size){
        
                $img_path = self::IMG_BASE_PATH.$img_path;
                
                if(!file_exists($img_path) || $img_path == self::IMG_BASE_PATH){//404 默认图
                    
                    $img_path = self::IMG_BASE_PATH.self::DEFAULT_ACTIVE_IMG;
                }
                
                $suffix = $this->img_file_type($img_path);
                
                $suffix = strtolower($suffix);
                
                $img_source = $this->resize_img($img_path,$img_size['width'],$img_size['height'],$suffix);
            }
        }
        exit;
    }
    
    /**
     * 检查图片尺寸
     */
    function check_img_size_open($with_height){
        $size = array(
                "196*176"=>array("width"=>196,"height"=>176),//活动列表
                "272*300"=>array("width"=>272,"height"=>300),//活动单页
                "196*196"=>array("width"=>196,"height"=>196),//活动单页
                "180*180"=>array("width"=>180,"height"=>180),//俱乐部logo
                "400*160"=>array("width"=>400,"height"=>160),//活动单页
                "600*400"=>array("width"=>600,"height"=>400),//活动单页
                "1000*280"=>array("width"=>1000,"height"=>280),//俱乐部首页
                "290*260"=>array("width"=>290,"height"=>260),//活动相册单页
        );
        return isset($size[$with_height])?$size[$with_height]:false;
    }
    
    /**
     * 防止外链
     */
    function check_domain($BASE_DOMAIN){
        if(str_replace($BASE_DOMAIN, "", $_SERVER['HTTP_HOST'])!=$_SERVER['HTTP_HOST']){
            echo "404";
            exit;
        }
    }
    /**
     * 裁剪
     */
    function resize_img($src_img,$dst_w,$dst_h,$suffix){
    
        $source=$this->img_resource($src_img, $suffix);
        
        if(!$source){//文件存在，创建不了对象，
            $src_img = self::IMG_BASE_PATH.self::DEFAULT_ACTIVE_IMG;
            
            $suffix = $this->img_file_type($src_img);
            $suffix = strtolower($suffix);
            
            $source=$this->img_resource($src_img, $suffix);
        }
        
        list($src_w,$src_h)=getimagesize($src_img);  // 获取原图尺寸
    
        $dst_scale = $dst_h/$dst_w; //目标图像长宽比
         
        $src_scale = $src_h/$src_w; // 原图长宽比
    
        if($src_scale>=$dst_scale)
        {
            // 过高
            $w = intval($src_w);
            $h = intval($dst_scale*$w);
            $x = 0;
            $y = ($src_h - $h)/3;
        }
        else
        {
            // 过宽
            $h = intval($src_h);
            $w = intval($h/$dst_scale);
            $x = ($src_w - $w)/2;
            $y = 0;
        }
        // 剪裁
        $croped=imagecreatetruecolor($w, $h);
        imagecopy($croped,$source,0,0,$x,$y,$src_w,$src_h);
    
        // 缩放
        $scale = $dst_w/$w;
        $target = imagecreatetruecolor($dst_w, $dst_h);
        //$final_w = intval($w*$scale);
        //$final_h = intval($h*$scale);
        imagecopyresampled($target,$croped,0,0,0,0,$dst_w,$dst_h,$w,$h);
        // 保存
        //$timestamp = time();
        //imagejpeg($target, "$timestamp.jpg");
        //imagedestroy($target);
    
        header("Content-Type: ".$this->img_content_type($suffix));//设置文件类型
        
        //cache
        $start_cache = strtotime(date("Y-m-d"));
        header('Pragma: ');
        header("Cache-Control: max-age=".self::IMG_CACHE_TIME);//1天
        header("Last-Modified: " . gmdate ('r', $start_cache));
        header('Expires:'. preg_replace('/.{5}$/', 'GMT', gmdate('r', $start_cache + self::IMG_CACHE_TIME)));
        
        if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $start_cache) {
            header("HTTP/1.1 304 Not Modified"); //服务器发出文件不曾修改的指令
            exit();
        }
        $this->img_show($target,$suffix);
        imagedestroy($target);
    }
    /**
     * 根据来源文件的文件类型创建一个图像操作的标识符
     */
    function img_resource($img_file, $suffix)
    {
        $res = "";
        switch ($suffix)
        {
            case 'gif':
                $res = imagecreatefromgif($img_file);
                break;
    
            case "jpeg":
            case 'jpg':
            case 'jpe':
                $res = imagecreatefromjpeg($img_file);
                break;
    
            case 'png':
                $res = imagecreatefrompng($img_file);
                break;
    
            default:
                return false;
        }
    
        return $res;
    }
    /**
     * 展示图片
     */
    function img_show($img_source, $suffix)
    {
        switch ($suffix)
        {
            case 'gif':
                imagegif($img_source);
                break;
    
            case "jpeg":
            case 'jpg':
            case 'jpe':
                imagejpeg($img_source);
                break;
    
            case 'png':
                imagepng($img_source);
                break;
    
            default:
                return false;
        }
    }
    /**
     * 获取图片类型
     */
    function img_content_type($suffix){
        $type = array(
                'bmp' => 'image/bmp',
                'gif' => 'image/gif',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'jpe' => 'image/jpeg',
                'png' => 'image/png',
        );
        return $type[$suffix];
    }
    /**
     *  返回文件后缀名，如‘.php’
     */
    function img_file_type($path){
        
        $pos = strrpos($path, '.');
        if ($pos !== false){
            return substr($path, $pos+1);
        }
        else{
            return '';
        }
    }
    
}