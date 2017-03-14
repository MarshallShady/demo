<?php
namespace Mobile\Controller;
use Think\Controller;
class CommonController extends Controller {


    protected $unionid = '';//unionid
    protected $time = 0;
    protected $position = [];//位置
    // dormitory/building/ext
    protected $user = [];
    protected $openid = '';
    protected $output = [];//不具有重复结构性质的输出数据
    protected $list = [];//结构性质的数据
    protected $temp = [];
    protected $data = [];
    protected $common = [];

    public function _initialize(){
        // 权限检测
        $this->cleanTimeoutOrder();
        $this->checkUnionid();
        $this->checkPosition();
        $this->checkRole();
        $this->_init();
    }

    protected function cleanTimeoutOrder(){
        $order_basic = M('order_basic');
        $order_dish = M('order_dish');

        $order_basic_save = [
            'status' => 2,
        ];
        $order_basic->startTrans();
        $order_basic->comment('查订单'
            )->join([
            ])->field([
                'bd_order_basic.id',
            ])->where([
                'bd_order_basic.status' => ['EQ', 1],
                'bd_order_basic.create_time' => ['LT', time() - 3600],
            ]);
        $order_basic_data = $order_basic->select();
        if ($order_basic_data) {
            foreach ($order_basic_data as $key => $value) {
                $orderid[] = $value['id'];
            }
            $order_dish_data = $order_dish->comment('查菜品'
                )->field([
                    'bd_order_dish.dish as id',
                    'count(*) as num',
                ])->where([
                    'bd_order_dish.id' => ['IN', $orderid],
                ])->group('bd_order_dish.id');
            foreach ($order_dish_data as $key => $value) {
                $order_dish_save = [
                    'bd_canteen_port_dish.rest' => ['exp', 'bd_order_dish.rest + ' . $value['num']],
                    'bd_canteen_port_dish.total' => ['exp', 'bd_order_dish.rest - ' . $value['num']],
                ];
                if (M('canteen_port_dish')->where(['id' => ['EQ', $value['id']]])->save($order_dish_save) === false) {
                    $order_basic->rollback();
                }
            }

            if (M('order_basic')->where(['id' => ['IN', $orderid]])->save($order_basic_save) !== false) {
                $order_basic->commit();
            } else {
                $order_basic->rollback();
            }
        } else {
            $order_basic->rollback();
        }
    }

    /**
        校验Unionid
    */
    protected function checkUnionid(){
        if (I('state') && is_numeric(I('state'))) SESSION('state', I('state'));
        elseif(!SESSION('state')) SESSION('state', 1);
        if (I('code')) {
            $open = json_decode(file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . C('APPID') . '&secret=' . C('APPSECRET') . '&code=' . I('code') . '&grant_type=authorization_code'));
            $this->openid = $open->openid;
            $union = json_decode(file_get_contents('https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . getAccessToken() . '&openid=' . $open->openid . '&lang=zh_CN'));
            // $this->unionid = $union->unionid;
            $temp = M('user_basic')->where(['unionid' => ['eq', $union->unionid]])->field('openid,unionid')->select();
            // p($this->openid);
            if ($temp && $temp[0]['openid'] == '') {
                $temp[0]['openid'] = $this->openid;
                M('user_basic')->save($temp[0]);
            }
            if ($union) {
                $this->unionid = $union->unionid;
                SESSION('unionid', $this->unionid);
            } elseif ($SESSION('unionid')) {
                $this->unionid = $SESSION('unionid');
            } else {
                wechatRedirect('mobile/index/index');
            }
            return ;
        }
        // p(SESSION('unionid'));
        if (!SESSION('unionid')) {
            // 没有unionid，判断是否是OAUTH
            if (!I('code')) {
                // 不是OAUTH，去OAUTH
                wechatRedirect('mobile/index/index');
            } else {
                // 是OAUTH，取unionid
                $open = json_decode(file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . C('APPID') . '&secret=' . C('APPSECRET') . '&code=' . I('code') . '&grant_type=authorization_code'));
                $this->openid = $open->openid;
                $union = json_decode(file_get_contents('https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . getAccessToken() . '&openid=' . $open->openid . '&lang=zh_CN'));
                $this->unionid = $union->unionid;
                if (!$union) return;//获取微信信息失败
                SESSION('unionid', $union->unionid);
            }
        }
        $this->unionid = SESSION('unionid');
    }

    /**
        检验位置
    */
    protected function checkPosition(){
        $this->position = SESSION('position');
        if (!$this->position['building'] || !$this->position['dormitory']) {
            // 没有楼号寝号
            $user = M('user_basic')->getByUnionid($this->unionid);
            if (!$user) $this->alert('由于您是首次登入，请先填写个人信息', U('mobile/position/index'));
            $this->position['building'] = $user['building'];
            $this->position['dormitory'] = $user['dormitory'];
        }
        if (!$this->position['ext']) {
            // 没有区域
            $school_building = M('school_building')->getById($this->position['building']);
            $this->position['ext'] = $school_building['school_ext'];
        }
        SESSION('position', $this->position);
    }

    /**
        权限管理
    */
    protected function checkRole(){
        // list.role用户正常状态的角色
        // temp.role.用户角色的类型.角色id
        $this->list['role'] = M('user_role')->where([
            'unionid' => ['eq', $this->unionid],
            'status' => ['eq', 1],
        ])->select();
        if (!count($this->list['role'])) $this->list['has_role_right'] = 0;
        else {
            $this->list['has_role_right'] = 1;
            foreach ($this->list['role'] as $key => $value) $this->temp['role'][$value['role']] = $value['id'];
        }
    }

    protected function checkTime(){
        // 默认时间，同时返回时间信息
        $school_ext = M('school_ext');
        $school_ext_data = $school_ext->comment('区域数据查询'
            )->where([
                'id' => ['eq', $this->position['ext']],
                'status' => ['eq', 1],
        ])->select();
        if ($school_ext_data) $this->data['school_ext'] = $school_ext_data[0];
        else {
            $return = [
                'status' => 2,
                'content' => '未取得位置数据',
                'message' => '网络异常，请重新尝试',
                'url' => $_SERVER['HTTP_REFERER'],
            ];
            $this->ajaxReturn($return, 'JSON');
        }
    }
    protected function getSchoolExt(){
        // 默认时间，同时返回时间信息
        $school_ext = M('school_ext');
        $school_ext_data = $school_ext->comment('区域数据查询'
            )->where([
                'id' => ['eq', $this->position['ext']],
                'status' => ['eq', 1],
        ])->select();
        if ($school_ext_data) {
            $this->common['school_ext'] = $school_ext_data[0];
            // return $school_ext_data[0];
        } else {
            $return = [
                'status' => 0,
                'content' => '未取得位置数据',
                'message' => '网络异常，请重新尝试',
                'url' => $_SERVER['HTTP_REFERER'],
            ];
            $this->ajaxReturn($return, 'JSON');
        }
    }
    /**
        回调方法
    */
    protected function _init(){}

    protected function alert($message, $url){
        echo '<script>alert("' . $message . '");location.href="' . $url . '";</script>';
        exit() ;
    }
}
