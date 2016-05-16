<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 词库管理
 */
class WordsController extends AdminController {

    const WORD_TYPE_BASE  = 4;
    const WORD_TYPE_PLACE = 2;
    const WORD_TYPE_TRIP  = 3;
    const WORD_TYPE_HUWAI = 1;
    public function index(){
        $this->c_base  = M('Words')->where("cate=".self::WORD_TYPE_BASE)->count();  
        $this->c_place = M('Words')->where("cate=".self::WORD_TYPE_PLACE)->count();
        $this->c_trip  = M('Words')->where("cate=".self::WORD_TYPE_TRIP)->count();
        $this->c_huwai = M('Words')->where("cate=".self::WORD_TYPE_HUWAI)->count();
        $this->meta_title = '词库';
        $this->display();
    }
    public function do_words(){
      
        //$ws = M("Words")->where("cate=".self::WORD_TYPE_TRIP)->getField("word",true);      
        //$this->put_words_to_dict($ws); 
        //$ws = M("Words")->where("cate=".self::WORD_TYPE_HUWAI)->getField("word",true);      
        //$this->put_words_to_dict($ws); 
    }
    public function check(){
        $kw = I("kw");
        if($kw){
            $one = M("Words")->where(array("word"=>trim($kw)))->find();
            if(!empty($one)){
                $this->success("has");
            }else{
                $this->success("no");
            }
        }
        $this->error("error");
    }
    public function add(){
        $kw = I("kw");
        if($kw){
            $id = M("Words")->add(array("word"=>trim($kw),"cate"=>self::WORD_TYPE_HUWAI));
            if($id){
                $this->put_words_to_dict(trim($kw));
            }
        }
        $this->success("success");
    }
    //
    private function put_words_to_dict($word){
       if($word){
          $words = "";
          if(is_array($word)){
              foreach($word as $wd){
                 $words .= $wd."\t1\nx:1\n";  
              }

          }else{
             $words = $word."\t1\nx:1\n";   
          }
          if( $words ){
             file_put_contents(THINK_PATH."../Data/words/dict.txt",$words,FILE_APPEND); 
          }
       } 
    }
    //将词库文件中的词写入数据库，不用考虑重复
    private function doWords($path,$cate=self::WORD_TYPE_HUWAI){
        if(file_exists($path)){
            $content= @file_get_contents($path);
            if($content){
                $arr_con = explode("\n", $content);
                foreach($arr_con as $arr){
                    if($arr && trim($arr)){
                        $word = $this->getChinese($arr);
                        if($word){
                            M("Words")->add(array("word"=>trim($word),"cate"=>$cate));
                        }
                    }
                }
            }
        }
    }
    //已有词库
    private function get_word_path_txt(){
        return array(
               self::WORD_TYPE_PLACE => THINK_PATH."../Data/words/place.txt",
               self::WORD_TYPE_TRIP => THINK_PATH."../Data/words/trip.txt",
               self::WORD_TYPE_BASE => THINK_PATH."../Data/words/unigram.txt",
        );
    }
    //提取中文，去除其它字符 
    private function getChinese($str,$join=''){
        preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $str, $matches); //将中文字符全部匹配出来
        $str = join($join, $matches[0]); //从匹配结果中重新组合
        return $str;
    }
}
