<?php
namespace Mobile\Controller;
use Common\Service\ImgService;
/**
 * 不足：
 * 1、需要实例化，按尺寸保存到本地
 * 2、保存网络图片到本地，再裁剪
 * @var unknown
 */
class ImgController extends HomeController {
    
    public function index($img,$size){
        $imgservice = new ImgService();
        $imgservice->index($img, $size);
    }
    
}