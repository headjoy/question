@extends('Weixin.base3')
@section('content')
<script src="{{asset('js/aliyun-webrtc-sdk.js')}}"></script>
<div class="w-100 relative" style="height:100%;">
    <div class="w-100 absolute bg-blue red tc" style="bottom:1rem; z-index:1;">
        <span class="w-25 inline-block fl mb-40" onclick="join_channel();">加入</span>
        <span class="w-25 inline-block fl mb-40" onclick="publish();">发布</span>
        <span class="w-25 inline-block fl mb-40" onclick="subscribe();">订阅</span>
        <span class="w-25 inline-block fl mb-40" onclick="dispose();">退出</span>
        <span class="w-25 inline-block fl mb-40" onclick="start_preview();">开始预览</span>
        <span class="w-25 inline-block fl mb-40" onclick="stop_preview();">停止预览</span>
        <span class="w-25 inline-block fl mb-40" onclick="leave_channel();">离开</span>
        <span class="w-25 inline-block fl mb-40" onclick="unpublish();">取消发布</span>
        <span class="w-25 inline-block fl mb-40" onclick="unsubscribe();">取消订阅</span>
    </div>
    <video preload="auto" autoplay="autoplay" x-webkit-airplay="true" playsinline ="true" webkit-playsinline ="true" x5-video-player-type="h5" x5-video-player-fullscreen="true" x5-video-orientation="portraint" class="w-100" id="video" autoplay playsinline></video>
</div>
<script>
var aliWebrtc;
var publisherList = [];
var user_id="{{$user_id}}";
var auth_info={!!$auth_info!!};
var pageW,pageH;

document.addEventListener('DOMContentLoaded', function(){
    pageW=document.documentElement.clientWidth;
    pageH=document.documentElement.clientHeight;
    //alert(pageW+'===='+pageH);
    try{
        aliWebrtc = new AliRtcEngine();
        support();
    }catch(error){
        console.log(error);
    }
}, false);

/**
 * isSupport webrtc能力检测 
 */
function support() {
    aliWebrtc.isSupport().then(re => {
        init();
    }).catch(error => {
        alert(error.message);
    });
}
function init(){
    aliWebrtc.on("onJoin", (publisher) => {
        alert('onJoin事件');
        alert(JSON.stringify(publisher));
        if(publisher.userId){
            //updateUserList();
        }
        //重置订阅状态
        //默认订阅远端音频和视频大流，但需要调用subscribe才能生效
        //这里取消默认订阅，根据需求进行订阅
        aliWebrtc.configRemoteAudio(publisher.userId, true);
        aliWebrtc.configRemoteCameraTrack(publisher.userId, true, true);
    });
    aliWebrtc.on("onPublisher", (publisher) => {
        alert('onPublisher事件');
        alert(JSON.stringify(publisher));
    });

    /**
     * remote流结束发布事件 onUnPublisher
     * 推流列表删除该用户
     * 移除用户视图
     * 初始化订阅状态
     */ 
    aliWebrtc.on("onUnPublisher", (publisher) => {
        alert('onUnPublisher事件');
        alert(JSON.stringify(publisher));
        // detelePublisher(publisher.userId);
        // removeDom(publisher.userId);
        // initialization(publisher.userId);
    });
    
    /**
     * 被服务器踢出或者频道关闭时回调 onBye
     */
    aliWebrtc.on("onBye",(message) =>{
        alert('onBye事件');
        //1:被服务器踢出
        //2:频道关闭
        //3:同一个ID在其他端登录,被服务器踢出
        var msg;
        switch (message.code) {
            case 1: msg = "被服务器踢出";
                break;
            case 2: msg = "频道关闭";
                break;
            case 3: msg = "同一个ID在其他端登录,被服务器踢出";
                break;
            default: msg = "onBye";
        }
        alert(msg);
    });
    /**
     *  错误信息
     */ 
    aliWebrtc.on("onError", (error) => {
        alert(JSON.stringify(error));
    });
}
/**
 * 角色类型，非通信模式下角色类型才有效。取值：
 * 0：互动身份。
 * 1（默认值）：观众身份。
 */
function set_client_role(){
    aliWebrtc.setClientRole(1);
}
/**
 * 频道模式。取值：
 * 0（默认值）：普通模式。
 * 1：互动模式。
 * 2：低延迟互动直播模式。
 */
function set_channel_profile(){
    aliWebrtc.setChannelProfile(0);
}
function start_preview(){
    //开启预览
    //var video=document.getElementById('video');
    aliWebrtc.startPreview(
        document.getElementById('video')
    ).then((obj)=>{
        // aliWebrtc.setVideoProfile({ //设置屏幕分享
        //     width: pageH,
        //     height: pageW
        // }, 2);
        set_channel_profile();
        set_client_role();
        document.getElementById('video').play();
        alert(JSON.stringify(obj));
    }).catch((error) => {
        // 预览失败
        alert('预览失败');alert(JSON.stringify(error));
        console.log(error);
    });
}
function stop_preview(){
    aliWebrtc.stopPreview().then((obj)=>{
        alert('停止预览成功');
        alert(JSON.stringify(obj));
    }).catch((error) => {
        // 结束预览失败
        alert(JSON.stringify(error));
    });
}
function join_channel(){
    aliWebrtc.configLocalAudioPublish = true;
    aliWebrtc.configLocalCameraPublish = true;
    //加入频道
    aliWebrtc.joinChannel(auth_info,'eddie').then(()=>{
        // 入会成功
        document.getElementById('video').play();
        alert('加入成功');
    } ,(error)=>{
        // 入会失败，打印错误内容，可以看到失败原因
        alert('加入频道失败');
        alert(JSON.stringify(error));
    });
}
function publish(){
    aliWebrtc.publish().then((publisher)=>{
        alert('发布成功');
        alert(JSON.stringify(publisher));
        console.log(publisher);
        document.getElementById('video').play();
    } ,(error)=>{
        alert('发布失败');
        alert(error.message);
        console.log(error.message);
    });
}
function unpublish(){
    aliWebrtc.configLocalAudioPublish = false;
    aliWebrtc.configLocalCameraPublish = false;
    aliWebrtc.publish().then((publisher)=>{
        if(publisher.userId){
            obj=publisher;
        }
        alert('取消发布成功');
        console.log(publisher);
    } ,(error)=>{
        alert('取消发布失败');
        alert(error.message);
        console.log(error.message);
    });
}
function subscribe(){alert('订阅');alert(user_id);
    aliWebrtc.configRemoteAudio(user_id, true);
    aliWebrtc.configRemoteCameraTrack(user_id, true, true);
    aliWebrtc.subscribe(user_id).then((user_id)=>{
        alert('订阅成功');
        alert(user_id);
        document.getElementById('video').play();
    },(error)=>{
        alert(user_id);
        alert(error.message);
        alert(JSON.stringify(error));
        console.log(error.message);
    });
}
function unsubscribe(){alert('取消订阅');
    aliWebrtc.configRemoteAudio(user_id, false);
    aliWebrtc.configRemoteCameraTrack(user_id, false, false);
    aliWebrtc.subscribe(user_id).then((user_id)=>{
        alert('取消订阅成功');
        alert(user_id);
    },(error)=>{
        alert(error.message);
        alert(JSON.stringify(error));
        console.log(error.message);
    });
}
function leave_channel(){
    aliWebrtc.leaveChannel().then((obj)=>{
        alert('离开频道成功');
        alert(JSON.stringify(obj));
    } ,(error)=>{
        console.log(error.message);
    });
}
function dispose(){
    aliWebrtc.dispose();
}
</script>
@endsection