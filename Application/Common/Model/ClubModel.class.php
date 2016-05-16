<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Common\Model;
use Think\Model;
use Think\Page;

/**
 * 活动基础模型
 */
class ClubModel extends Model{

    public $page = '';

    public function clubs_by_ids($club_ids){
        $result = array();
        if(!empty($club_ids) && is_array($club_ids)){
            $clubs = $this->field("club_id,club_name,mobile,logo,telphone")->where("club_id in(".implode(",", $club_ids).")")->select();
            foreach($clubs as $club){
                $result[$club['club_id']] = $club;
            }
        }
        return $result;
    }
}
