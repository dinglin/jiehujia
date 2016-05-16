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
class SmsService {
    
    const ACCOUNT_CODE = "cf_louy";
    const ACCOUNT_PWD = "5TBncL";
    const SMS_URL = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
    const SMS_TEMPLATE_CODE = "您的验验证码是{code}，十分钟有效";//"您在申请5间房试住，验证码是：{code}。十分钟内有效。";
    const SMS_TEMPLATE_APPLY_SUBMIT = "您的试住员申请我们已经收到啦，我们会尽快处理，如果您满足我们的要求，我们会与您联系哦。申请完试住员之后会大大提高您抢中房的概率，要经常去试试手气！";
    const SMS_TEMPLATE_APPLY_SUCCESS = "恭喜您获得{hotel}免费入住一晚，酒店地址：{hoteladdress}，使用截止日期：{endtime}。如果您需要入住了，请提前两天与我们的客服联系：4008-455-005";
    
    /**
     * 申请抢房提交后发送
     * @param unknown $mobile
     */
    public function sendMsgOnApplySubmit($mobile){
        $this->sendMsg($mobile, self::SMS_TEMPLATE_APPLY_SUBMIT);
    }
    /**
     * 抢房成功后发送
     * @param unknown $mobile
     * @param unknown $hotel
     * @param unknown $hotelAddress
     * @param unknown $endTime
     */
    public function sendMsgOnApplySuccess($mobile,$hotel,$hotelAddress,$endTime){
        $template = self::SMS_TEMPLATE_APPLY_SUBMIT;
        $template = str_replace("{hotel}", $hotel, $template);
        $template = str_replace("{hoteladdress}", $hotelAddress, $template);
        $template = str_replace("{endtime}", $endTime, $template);
        $this->sendMsg($mobile, $template);
    }
    
    private function sendMsg($mobile,$template){
        if(!$mobile || strlen($mobile)!=11){
            return;
        }
        
        $post_data = "account=".self::ACCOUNT_CODE."&password=".self::ACCOUNT_PWD."&mobile=".$mobile."&content=".rawurlencode($template);
        //密码可以使用明文密码或使用32位MD5加密
        $gets =  $this->xml_to_array($this->Post($post_data, self::SMS_URL));
        if($gets['SubmitResult']['code']==2){
            M("Fjf_msg_log")->add(array("mobile"=>$mobile,"content"=>$template));
        }
        return $gets['SubmitResult']['msg'];
    }
    
    /**
     * 发送验证码
     * @param unknown $mobile
     */
    public function sendRandomCode($mobile){
        $code = $this->random(6,1);
        if(!$mobile || strlen($mobile)!=11){
            return;
        }
        
        if($_SESSION['mobile_time']&& ((time()-$_SESSION['mobile_time'])<50)){//给个相应时间
            //防用户恶意请求
            return;
        }
        $_SESSION['mobile_time'] = time();//发送时间
        $template = str_replace("{code}", $code, self::SMS_TEMPLATE_CODE);
        $post_data = "account=".self::ACCOUNT_CODE."&password=".self::ACCOUNT_PWD."&mobile=".$mobile."&content=".rawurlencode($template);
        //密码可以使用明文密码或使用32位MD5加密
        $gets =  $this->xml_to_array($this->Post($post_data, self::SMS_URL));
        if($gets['SubmitResult']['code']==2){
            $_SESSION['mobile'] = $mobile;
            $_SESSION['mobile_code'] = $code;
            M("Fjf_msg_log")->add(array("mobile"=>$mobile,"code"=>$code,"content"=>$template));
        }
        return $gets['SubmitResult']['msg'];
    }
    public function verify($mobile,$code){
        if($mobile == $_SESSION['mobile'] && $code == $_SESSION['mobile_code']){
            //已使用
            M("Fjf_msg_log")->where(array("mobile"=>$mobile,"code"=>$code))->save(array("status"=>1));
            //防止再次提交表单
            $_SESSION['mobile_code'] = null;
            return true;
        }
        return false;
    }
    private function random($length = 6 , $numeric = 0) {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if($numeric) {
            $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
            $max = strlen($chars) - 1;
            for($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }
    
    private function Post($curlPost,$url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }
    private function xml_to_array($xml){
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if(preg_match_all($reg, $xml, $matches)){
            $count = count($matches[0]);
            for($i = 0; $i < $count; $i++){
                $subxml= $matches[2][$i];
                $key = $matches[1][$i];
                if(preg_match( $reg, $subxml )){
                    $arr[$key] = $this->xml_to_array( $subxml );
                }else{
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }
}