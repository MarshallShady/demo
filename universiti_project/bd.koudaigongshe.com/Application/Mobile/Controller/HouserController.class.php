<?php
namespace Mobile\Controller;
use Think\Controller;
class HouserController extends CommonController {
    /**
    楼长（1）
    */
    protected function _init(){
        if (!$this->temp['role'][1]) $this->alert('您不是楼长，无法操作！', U('mobile/index/index'));
        $this->assign('list', $this->list);
        $this->getAim();
        $this->checkTime();
    }
    public function index(){
        $this->display();
    }
    public function apiShow(){
        // 接口格式
        $this->data = [
            'prepare' => [],
            'ready' => [],
            'finished' => [],
            'count' => [
                'undo' => 0,
                'did' => 0,
            ],
            'role' => [
                'num_dish_rest' => 0,
            ],
        ];
        // 操作数量
        $user_role_data = M('user_role'
            )->comment('查楼长工作量'
            )->field([
                'num_order_total',
                'num_order_rest',
                'num_dish_total',
                'num_dish_rest',
            ])->where([
                'bd_user_role.id' => ['EQ', $this->temp['role'][1]],
        ])->select();
        if ($user_role_data) {
            $this->data['role']['num_dish_rest'] = $user_role_data[0]['num_dish_rest'];
        }
        // 时段信息
        $dish = M('canteen_port');
        $dish->comment('查找要出的菜品'
            )->join([
                'INNER JOIN bd_order_dish ON bd_order_dish.port = bd_canteen_port.id',
                'INNER JOIN bd_order_basic ON `bd_order_basic`.`id` = `bd_order_dish`.`order`',
                'INNER JOIN bd_school_building ON `bd_order_basic`.`building` = `bd_school_building`.`id`',
            ])->field([
                'bd_order_dish.id as order_dish_id',
                'bd_order_basic.id as order_basic_id',
                'bd_order_basic.status as order_basic_status',
                'bd_school_building.id as building_id',
                'bd_order_dish.status as dish_status',
                'bd_order_dish.port',

                'bd_order_dish.when',
                'bd_canteen_port.name as port_name',
                'bd_order_dish.name as order_dish_name',
                'bd_order_basic.name as name',
                '`bd_order_basic`.`mobile`',
                'bd_school_building.name as building_name',
                'bd_order_basic.dormitory',
                'bd_order_dish.money as money',
                'bd_order_basic.word as word',
            ])->where([
                'bd_order_basic.building' => ['IN', $this->common['aim']],

                'bd_order_basic.status' => ['IN', '4,5,6,7,11'],//订单为已支付未出单状态
                'bd_order_dish.status' => ['IN', '1,2,3'],//操作对象状态 已支付未出单

                '_complex' => [
                    'bd_order_basic.pay_time' => ['ELT', $this->common['time']['pay_time']],//支付要早于出单时间
                    'bd_school_building.mode' => ['NEQ', 2],
                    '_logic' => 'AND',
                ],
                // 'bd_order_basic.pay_time' => ['ELT', $this->common['time']['pay_time']],//支付要早于出单时间
                'bd_order_basic.order_date' => ['EQ', $this->common['time']['date']],//当天的订单
                'bd_order_basic.when' => ['EQ', $this->common['time']['when']],//时间段
        ]);
        $dish_data = $dish->select();
        foreach ($dish_data as $key => $value) {
            if ($value['order_basic_status'] == 4 || $value['order_basic_status'] == 11) {
                $this->data['prepare'][$value['order_basic_id']]['building_name'] = $value['building_name'];
                $this->data['prepare'][$value['order_basic_id']]['dish'][] = $value;
                $this->data['prepare'][$value['order_basic_id']]['count'] += 1;
                $this->data['count']['undo'] += 1;
            } elseif ($value['order_basic_status'] == 5) {
                $this->data['ready'][$value['order_basic_id']]['building_name'] = $value['building_name'];
                $this->data['ready'][$value['order_basic_id']]['dish'][] = $value;
                $this->data['ready'][$value['order_basic_id']]['count'] += 1;
                $this->data['count']['undo'] += 1;
            } else {
                $this->data['finished'][$value['order_basic_id']]['building_name'] = $value['building_name'];
                $this->data['finished'][$value['order_basic_id']]['dish'][] = $value;
                $this->data['finished'][$value['order_basic_id']]['count'] += 1;
                if ($value['deliver_man'] == $this->temp['role'][1]) {
                    $this->data['count']['did'] += 1;
                }
            }
        }
        $this->ajaxReturn($this->data);
    }

    /**
        送达按钮（楼长）
    */
    public function sendedHandle($id){
        // 权限拦截
        $time = time();
        $order = M('order_basic');
        $order->startTrans();
        $order_basic_data = $order->comment('查数据'
            )->join('INNER JOIN bd_user_basic ON bd_user_basic.unionid = bd_order_basic.unionid'
            )->field([
                'bd_user_basic.openid',
                'bd_order_basic.deliver_time',
                'bd_order_basic.status',
                'bd_order_basic.building',
            ])->where([
                'bd_order_basic.id' => ['EQ', $id],
                'bd_order_basic.status' => ['EQ', 5],
                'bd_order_basic.building' => ['IN', $this->common['aim']],
        ])->select()[0];
        $money = 0;

        if (!$order_basic_data) {
            $order->rollback();
            $this->alert('该订单可能由于各种原因导致无法确认送达');
        } else {
            // 订单状态更新
            $data_save['id'] = $id;
            $data_save['housemaster_time'] = $time;
            $data_save['edit_time'] = $time;
            $data_save['housemaster'] = $this->temp['role'][1];
            $data_save['status'] = 6;

            $num = M('order_dish')->where([
                'order' => ['EQ', $id],
                'status' => ['IN', '1,2,3'],
            ])->count();
            if ($num) {
                $user_role = [
                    'id' => $this->temp['role'][1],
                    'num_order_total' => ['exp', 'num_order_total + 1'],
                    'num_order_rest' => ['exp', 'num_order_rest + 1'],
                    'num_dish_total' => ['exp', 'num_dish_total + ' . $num],
                    'num_dish_rest' => ['exp', 'num_dish_rest + ' . $num],
                ];

                if (M('user_role')->save($user_role) === false || $order->save($data_save) === false) {
                    $order->rollback();
                    $this->alert('失败！', U('mobile/houser/index'));
                } else {
                    $order->commit();
                    $message = [
                        'first' => [
                            'value' => '幸福时刻，必点一下',
                            'color' => '#000000',
                        ],
                        'OrderSn' => [
                            'value' => $id,
                            'color' => '#000000',
                        ],
                        'OrderStatus' => [
                            'value' => '已送达',
                            'color' => '#008080',
                        ],
                        'remark' => [
                            'value' => "\n本次配送用时" . intval(($time - $order_basic_data['deliver_time']) / 60) . '分钟' . "\n点击“详情”评价或投诉",
                            'color' => '#FF8040',
                        ],
                    ];
                    SendTempletMessage($order_basic_data['openid'], 'http://bd.koudaigongshe.com/mobile/personal/index', $message, 1);
                    $this->alert('成功！', U('mobile/houser/index'));
                }
            } else {
                $this->alert('该订单没有单品，是不是哪里搞错了？', U('mobile/houser/index'));
            }
        }
    }
    /**
        操作目标
    */
    private function getAim(){
        $data = M('user_role_ext')->comment('操作范围')->join([
            'INNER JOIN bd_school_building ON bd_school_building.id = bd_user_role_ext.aim',
            'INNER JOIN bd_school_ext ON bd_school_ext.id = bd_school_building.school_ext',
        ])->field([
            'bd_school_building.id',
            'bd_school_ext.breakfast_pack_start',
            'bd_school_ext.breakfast_order_end',
            'bd_school_ext.lunch_pack_start',
            'bd_school_ext.lunch_order_end',
            'bd_school_ext.dinner_pack_start',
            'bd_school_ext.dinner_order_end',
        ])->where([
            'user_role' => ['EQ', $this->temp['role'][1]],
            'bd_user_role_ext.status' => ['EQ', 1],
        ])->select();
        if (!$data) {
            $this->alert('您已成为楼长但还未被分配工作', U('mobile/index/index'));
        } else {
            foreach ($data as $key => $value) {
                $this->common['aim'][] = $value['id'];
                $this->common['time']['breakfast_pack_start'] = $value['breakfast_pack_start'];
                $this->common['time']['breakfast_order_end'] = $value['breakfast_order_end'];
                $this->common['time']['lunch_pack_start'] = $value['lunch_pack_start'];
                $this->common['time']['lunch_order_end'] = $value['lunch_order_end'];
                $this->common['time']['dinner_pack_start'] = $value['dinner_pack_start'];
                $this->common['time']['dinner_order_end'] = $value['dinner_order_end'];
            }
        }
    }

    protected function checkTime(){
        // 当前时间
        $now = time();
        $date = strtotime(date('Y-m-d'));
        // 派送截止前出单截止，
        if ($this->common['time']['breakfast_order_end'] && $now <= $date + $this->common['time']['breakfast_order_end'] + 7200) {
            $when = 1;
            $when_name = '早餐时段';
            $pay_time = $this->common['time']['breakfast_pack_start'];
        } elseif ($this->common['time']['lunch_order_end'] && $now <= $date + $this->common['time']['lunch_order_end'] + 10800) {
            $when = 2;
            $when_name = '午餐时段';
            $pay_time = $this->common['time']['lunch_pack_start'];
        } elseif ($this->common['time']['dinner_order_end']) {
            $when = 3;
            $when_name = '晚餐时段';
            $pay_time = $this->common['time']['dinner_pack_start'];
        }
        $this->common['time']['now'] = $now;
        $this->common['time']['date'] = $date;
        $this->common['time']['when'] = $when;
        $this->common['time']['when_name'] = $when_name;
        $this->common['time']['pay_time'] = $date + $pay_time;
    }
}
