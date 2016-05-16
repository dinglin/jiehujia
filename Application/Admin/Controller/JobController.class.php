<?php
namespace Admin\Controller;

/**
 * 后台活动控制器
 */
class JobController extends AdminController {

    //处理历史数据，天数
    public function active_days(){
        $actives = M("Actives")->getField('active_id,start_time,end_time');
        foreach ($actives as $active){
            if($active['start_time'] && $active['end_time']){
                $time = $active['end_time'] - $active['start_time'];
                $one_day = 24 * 60 * 60;
                $days = ceil($time/$one_day);
                if($days){
                    M("Actives")->where("active_id=".$active['active_id'])->data(array("days"=>$days))->save();
                }
            }
        }
    }
    
    //处理历史数据，列表图
    public function list_pic(){
        set_time_limit(0);
        //actives
        $actives = M("Actives")->getField('active_id,list_pic');
        foreach($actives as $active_id =>$list_pic){
            $pic_url = "";
            if(!$list_pic){
                //pic
                $list_pic = M('ActivePic')->where('active_id='.$active_id)->find();
                if(!empty($list_pic)){
                    $pic_url = $list_pic['img_src'];
                }else{//content
                    $content = M('ActiveContent')->where('active_id='.$active_id)->find();
                    $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/"; 
                    preg_match_all($pattern,$content['content'],$match);
                    if($match && $match[1]){
                        $pic_url = $match[1][0];
                    }
                }
                
            }
            //set pic
            if($pic_url){
                M("Actives")->where('active_id='.$active_id)->data(array("list_pic"=>$pic_url))->save();
            }
        }
    }
    
    public function club_photo(){
        get_img_src_from_html;
    }
}