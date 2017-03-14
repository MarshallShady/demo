<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index(){
        $this->display();

    }
    public function login(){
        $open = json_decode(file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx56325c3419e5572e&secret=01d89588702efc960328b9a9ac230157&code=' . I('code') . '&grant_type=authorization_code'));
        p($open);
    }
    public function logint(){
        // https://open.weixin.qq.com/connect/qrconnect?appid=wx4d1b293d79f694c7&redirect_uri=http://bd.koudaigongshe.com/home/index/login&response_type=code&scope=snsapi_login&state=1#wechat_redirect
        // https://open.weixin.qq.com/connect/qrconnect?appid=wx4d1b293d79f694c7&redirect_uri=http://bd.koudaigongshe.com/home/index/login&response_type=code&scope=snsapi_login&state=1#wechat_redirect
        // https://open.weixin.qq.com/connect/qrconnect?appid=wx56325c3419e5572e&redirect_uri=http%3A%2F%2Fbd.koudaigongshe.com/home/index/login&response_type=code&scope=snsapi_login&state=1#wechat_redirect
    }

    public function notify(){
        M('test')->add(['name' => json_encode($GLOBALS["HTTP_RAW_POST_DATA"])]);
        ini_set('date.timezone','Asia/Shanghai');
        error_reporting(E_ERROR);
        vendor('Wxpay.lib.WxPay#Api');
        vendor('Wxpay.lib.WxPay#Notify');
        vendor('Wxpay.example.log');
        vendor('Wxpay.lib.PayNotifyCallBack');
        // vendor('wpay.lib.WxPay#Api');
        // vendor('wpay.lib.WxPay#Notify');
        // vendor('wpay.log');
        // vendor('wpay.nnc');
        //初始化日志
        M('test')->add(['name' => 'once']);
        $logHandler= new \CLogFileHandler("../logs/".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);

        \Log::DEBUG("begin notify!");
        M('test')->add(['name' => 'beforecreate']);
        $notify = new \NativeNotifyCallBack();
        M('test')->add(['name' => 'beforehandle']);
        $notify->Handle(true);
    }
}
