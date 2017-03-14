<?php
namespace Mobile\Controller;
use Think\Controller;
class CouponController extends CommonController {
    public function _initialize(){
        // 权限检测
        $this->checkUnionid();
        $this->_init();
    }
    public function index(){
        $this->display();
    }

    public function getby($id){
        $this->time = time();
        // 查指定红包
        $url_basic = M('coupon_url_basic');
        $url_basic->startTrans();
        $url_basic_data = $url_basic->comment('查id'
            )->field([
                'id',
                'money',
                'type',
                'area',
                'aim',
                'end_time',
                'status',
            ])->where([
                'id' => ['eq', $id],
                'status' => ['eq', 1],
                'end_time' => ['gt', $this->time],
        ])->select();
        if (!$this->checkUrlType($url_basic_data[0]['type'])) {
            $url_basic->rollback();
            $this->alert('对不起，您不满足优惠条件', U('mobile/index/index'));
        }
        if (!$url_basic_data) {
            $url_basic->rollback();
            $this->alert('无法操作', U('mobile/index/index'));
        }
        // 查是否可用
        $url_use_where = [
            'unionid' => $this->unionid,
            'coupon' => $url_basic_data[0]['type'],
        ];
        $url_use = M('coupon_url_use');
        $url_use_data = $url_use->comment('查是否已使用')->where($url_use_where)->select();
        if ($url_use_data) {
            $url_basic->rollback();
            $this->alert('您已参加本次优惠', U('mobile/index/index'));
        }
        // 可用分配红包
        $bonus_id = $this->addBonus($url_basic_data[0], 0, $url_basic_data[0]['type']);
        if ($bonus_id != false) {
            // 添加一个使用数据
            $url_use_add = [
                'unionid' => $this->unionid,
                'coupon' => $url_basic_data[0]['type'],
                'bonus' => $bonus_id,
            ];
            $url_use->comment('添加已使用数据')->add($url_use_add);
            $url_basic->commit();
            $this->assign('list', $this->list);
            $this->display();
        } else {
            $url_basic->rollback();
            $this->alert('网络异常，请重新尝试', U('mobile/index/index'));
        }
    }

    public function checkCode(){
        if (!IS_POST) exit();
        $code = I('coupon');
        $this->time = time();
        // 查码验权
        $code_basic = M('coupon_code_basic');
        $code_basic->startTrans();
        $code_basic_data = $code_basic->comment('查码是否可用'
            )->join([
                'INNER JOIN bd_coupon_code_type ON bd_coupon_code_basic.type = bd_coupon_code_type.id',
            ])->field([
                'bd_coupon_code_type.id',
                'bd_coupon_code_basic.code',
                'bd_coupon_code_type.money',
                'bd_coupon_code_type.area',
                'bd_coupon_code_type.aim',
                'bd_coupon_code_type.end_time',
            ])->where([
                'bd_coupon_code_basic.code' => ['eq', $code],
                'bd_coupon_code_basic.end_time' => ['gt', $this->time],
                'bd_coupon_code_basic.status' => ['eq', 1],

                'bd_coupon_code_type.end_time' => ['gt', $this->time],
                'bd_coupon_code_type.status' => ['eq', 1],
        ])->select();
        // 无此码
        if (!$code_basic_data) {
            $code_basic->rollback();
            echo '2';die;//无此码
        }
        // 可领发红包
        $bonus_id = $this->addBonus($code_basic_data[0], $code_basic_data[0]['id'], $code);
        if ($bonus_id != false) {
            // 修改数据
            $code_use_add = [
                'unionid' => $this->unionid,
                'bonus' => $bonus_id,
                'code' => $code,
            ];
            $code_basic_save = [
                'code' => $code,
                // 'create_time' => $this->time,
                'status' => 2,
            ];
            if (M('coupon_code_use')->add($code_use_add) != false && M('coupon_code_basic')->save($code_basic_save) !== false) {
                $code_basic->commit();
                echo 1;
            } else {
                $code_basic->rollback();
                echo 4;
            }
        } else {
            // 失败的情况
            $code_basic->rollback();
            echo 4;
        }
    }

    private function checkUrlType($type){
        switch ($type) {
            case '1':
                $order_basic_data = M('order_basic')->where([
                    'unionid' => ['eq', $this->unionid],
                ])->limit('0,1')->order('bd_order_basic.create_time DESC')->select();
                if (count($order_basic_data) != 0) {
                    $this->alert('您是老用户，故不满足本次优惠条件', U('mobile/index/index'));
                } else return true;
            default:
                break;
        }
    }
    /**
        添加红包
    */
    private function addBonus($coupon, $get_type, $get_from){
        $probability = rand(0, 9999);
        $money_arr = json_decode($coupon['money']);
        foreach ($money_arr as $key => $value) {
            if ($probability < $value[2]) {
                $money = $value[0];
                $money_least = $value[1];
                break;
            }
        }
        // p($coupon);
        $bonus_add = [
            'unionid' => $this->unionid,
            'money' => $money,
            'money_least' => $money_least,
            'create_time' => $this->time,
            'end_time' => $coupon['end_time'],
            'get_type' => $get_type,
            'get_from' => $get_from,
            'status' => 1,
        ];
        // p($bonus_add);
        $this->list['bonus'] = $bonus_add;
        $bonus = M('user_bonus');
        return $bonus->comment('添加红包')->add($bonus_add);
    }
}
