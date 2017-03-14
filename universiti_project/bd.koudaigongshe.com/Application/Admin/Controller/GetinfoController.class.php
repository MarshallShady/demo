<?php
namespace Admin\Controller;
use Think\Controller;
class GetinfoController extends Controller {
    /**
        测试用的 没用
    */
    public function index(){
        if (!SESSION('openid')) {
            // 没有openid，判断是否是OAUTH
                $secret='d4624c36b6795d1d99dcf0547af5443d';
                $appid='wx3df3b764f49ee414';
            if (!I('code')) {
                // 不是OAUTH，去OAUTH
                $redirect_url='http://121.42.160.37/bidian/';
                redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_url.'&response_type=code&scope=snsapi_base#wechat_redirect');
            } else {
                // 是OAUTH，取openid

                $wechat = json_decode(file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . "&secret==$secret" . I('code') . '&grant_type=authorization_code'), true);
                if (!$wechat) die;//获取微信信息失败
                SESSION('openid', $wechat['openid']);
                $this->openid = $wechat['openid'];
                dump($wechat,1,'<pre>');
            }
        }

    }
    public function test(){
        $test=M('user_role');
        $data=$test->select();
        dump($data,1,'<pre>');
    }
}
