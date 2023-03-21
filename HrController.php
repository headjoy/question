<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Weixin\BaseController;
use Illuminate\Http\Request;
use App\Http\Util\MyUtil;

class HrController extends BaseController
{
    private $channel_id="1";
    

    public function onetoone(Request $request){
        $user_id=session('openid');
        $channel_id=$this->channel_id;
        $auth_info=$this->get_rtc_auth($user_id, $channel_id);
        return view('Weixin/Hr/onetoone',[
            'auth_info'=>json_encode($auth_info),
            'user_id'=>$user_id,
            'channel_id'=>$channel_id,
        ]);
    }

    //阿里云鉴权
    public function get_rtc_auth($user_id, $channel_id){
        $appid=env('WEB_RTC_APPID');
        $key=env('WEB_RTC_KEY');
        $channel_id=$this->channel_id;
        $timestamp=time() + 60 * 60 *2;
        $nonce='AK-'.$timestamp;
        $token=hash('sha256', $appid.$key.$channel_id.$user_id.$nonce.$timestamp);
        return [
            "appid"=>$appid,
            "userid"=>$user_id,
            "timestamp"=>$timestamp,
            "nonce"=>$nonce,
            "token"=>$token,
            "gslb"=>["https://rgslb.rtc.aliyuncs.com"],
            "channel"=>$channel_id
        ];
    }
}
