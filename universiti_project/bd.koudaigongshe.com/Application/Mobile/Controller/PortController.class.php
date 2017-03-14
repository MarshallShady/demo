<?php
namespace Mobile\Controller;
use Think\Controller;
class PortController extends CommonController {
    /**
        档口管理员
    */
    protected function _init(){
        if (!$this->temp['role'][6]) $this->alert('您不是档口管理员', U('mobile/index/index'));
        $this->assign('list', $this->list);
        $this->getAim();
        $this->checkTime();
    }

    public function index(){
        $this->display();
    }

    public function apiShowIndex($len = 0) {
        $data = [
            'name' => '临时档口',
            'dish' => [],//当前时段菜
            'count' => [
                'undo' => 0,//未接单量
                'did' => 0,//已接单量
                'money' => 0,//当前营业额
            ],
            'money' => 0,//总金额
            'history' => [],//历史流水
            'len' => 0,
        ];
        $order_dish = M('order_dish'
            )->comment('f'
            )->join(
                'INNER JOIN bd_order_basic ON bd_order_basic.id = bd_order_dish.`order`'
            )->where([
                'bd_order_dish.port' => ['IN', $this->common['aim']],
                'bd_order_dish.status' => ['IN', '1,2,3,8'],//操作对象状态 已支付未出单
        ]);
        // p($this->common['aim']);
        // $data['']
        if ($len == 0) {

            $order_dish_data = (clone $order_dish)->comment('档口管理员'
                )->join('INNER JOIN bd_dict_order_basic_status ON bd_order_basic.status = bd_dict_order_basic_status.id'
                )->join('INNER JOIN bd_school_building ON bd_school_building.id = bd_order_basic.building'
                )->field([
                    'bd_order_basic.id as order_basic_id',
                    'bd_order_dish.id',
                    'bd_order_basic.name',
                    'bd_order_basic.mobile',
                    'bd_order_basic.create_time',

                    'bd_order_dish.dish as dish_id',
                    'bd_order_dish.name as dish_name',
                    'bd_order_dish.status',
                    'bd_order_dish.money_cost as money',


                    'bd_dict_order_basic_status.name as status_name',
                ])->where([
                    'bd_order_basic.order_date' => ['EQ', $this->common['time']['date']],//当天的订单
                    'bd_order_basic.when' => ['EQ', $this->common['time']['when']],//时间段
                    // 'bd_order_basic.when' => ['EQ', 2],//时间段
                    // '_complex' => [
                    //     'bd_order_basic.pay_time' => ['ELT', $this->common['time']['pay_time']],//支付要早于出单时间
                    //     'bd_school_building.mode' => ['EQ', 2],
                    //     '_logic' => 'OR',
                    // ],

                    'bd_order_basic.status' => ['IN', '4,5,6,7,11,12,13'],//订单为已支付未出单状态
            ])->select();
            $data['money'] += (clone $order_dish)->comment(''
                )->field([
                    'sum(bd_order_dish.money_cost) as money',
                ])->where([
                    'bd_order_basic.status' => ['IN', '4,5,6,7,11,12,13'],
                    // 'bd_order_basic.order_date' => ['EQ', strtotime('2016-5-11')],
                ])->order([
                    'bd_order_basic.order_date,bd_order_basic.when',
            ])->select()[0]['money'];
            foreach ($order_dish_data as $key => $value) {
                $data['dish']['num'];
                $data['dish'][$value['dish_id']]['order'][$value['order_basic_id']]['data'][] = $value;
                $data['dish'][$value['dish_id']]['order'][$value['order_basic_id']]['num'] += 1;
                if ($value['status'] == 1) {
                    // $data['dish']['undo'][] = $value;
                    $data['count']['undo'] += 1;
                } else {
                    // $data['dish']['did'][] = $value;
                    $data['count']['did'] += 1;
                }
                $data['count']['money'] += $value['money'];
            }
        }
        $data['history'] += (clone $order_dish)->comment(''
            )->field([
                'sum(bd_order_dish.money_cost) as money',
                'bd_order_basic.order_date',
                'bd_order_basic.when',
            ])->where([
        ])->group('bd_order_basic.order_date DESC,bd_order_basic.when')->limit($len . ',3')->select();
        $data['len'] += $len + count($data['history']);
        $this->ajaxReturn($data);
    }
    //
    // /**
    //     接单 catch/order_id/ORDER_BASIC_ID/dish_id/ORDER_DISH_ID
    // */
    // public function catch($order_id, $dish_id) {
    //     $order_dish = M('order_dish');
    //     $order_dish->comment('查找'
    //         )->join([
    //             'bd_order_basic ON bd_order_basic.id = bd_order_dish.`order`',
    //         ])->field([
    //             'bd_order_dish.id',
    //         ])->where([
    //             'bd_order_dish.port' => ['IN', $this->common['aim']],
    //             'bd_order_basic.id' => ['EQ', $order_id],
    //             'bd_order_dish.id' => ['EQ', $dish_id],
    //
    //             'bd_order_basic.order_date' => ['EQ', $this->common['time']['date']],//当天的订单
    //             'bd_order_basic.when' => ['EQ', $this->common['time']['when']],//时间段
    //             '_complex' => [
    //                 'bd_order_basic.pay_time' => ['GT', $this->common['time']['pay_time']],//支付要早于出单时间
    //                 'bd_school_building.mode' => ['EQ', 2],
    //                 '_logic' => 'OR',
    //             ],
    //
    //             'bd_order_basic.status' => ['IN', '4,12,13'],//订单为已支付未出单状态
    //             'bd_order_dish.status' => ['IN', '1,2,3,8'],//操作对象状态 已支付未出单
    //     ]);
    //     $order_dish->startTrans();
    //     $order_dish_data = $order_dish->select()[0];
    //     if ($order_dish_data) {
    //         $order_save = [
    //             'id' => $order_dish_data['id'],
    //             'status' => 8,
    //         ];
    //         if ($order_dish->save($order_save) !== false) {
    //             $order_dish->commit();
    //         } else {
    //             $order_dish->rollback();
    //             $this->alert('接单失败，可能已经出单', U('mobile/port/index'));
    //         }
    //     } else {
    //         $order_dish->rollback();
    //         $this->alert('好像没有数据，是不是哪里搞错了？', U('mobile/port/index'));
    //     }
    // }


    /**
        操作目标
    */
    private function getAim(){
        $data = M('user_role_ext')->comment('操作范围'
            )->join([
                'INNER JOIN bd_canteen_port ON bd_canteen_port.id = bd_user_role_ext.aim',
                'INNER JOIN bd_canteen ON bd_canteen.id = bd_canteen_port.canteen',
                'INNER JOIN bd_school_ext ON bd_school_ext.id = bd_canteen.school_ext',
            ])->field([
                'bd_canteen_port.id',
                'bd_school_ext.breakfast_pack_start',
                'bd_school_ext.breakfast_order_end',
                'bd_school_ext.lunch_pack_start',
                'bd_school_ext.lunch_order_end',
                'bd_school_ext.dinner_pack_start',
                'bd_school_ext.dinner_order_end',
            ])->where([
                'bd_user_role_ext.user_role' => ['EQ', $this->temp['role'][6]],
                'bd_user_role_ext.status' => ['EQ', 1],
        ])->select();
        if (!$data) {
            $this->alert('您不是档口管理员或未被安排工作！', U('mobile/index/index'));
        } else {
            foreach ($data as $key => $value) {
                $this->common['aim'][] = $value['id'];
            }
            $this->common['time']['breakfast_pack_start'] = $value['breakfast_pack_start'];
            $this->common['time']['breakfast_order_end'] = $value['breakfast_order_end'];
            $this->common['time']['lunch_pack_start'] = $value['lunch_pack_start'];
            $this->common['time']['lunch_order_end'] = $value['lunch_order_end'];
            $this->common['time']['dinner_pack_start'] = $value['dinner_pack_start'];
            $this->common['time']['dinner_order_end'] = $value['dinner_order_end'];
        }
        // p($this->common['aim']);
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
        } elseif ($this->common['time']['lunch_order_end'] && $now <= $date + $this->common['time']['lunch_order_end'] + 7200 && $now >= $date + $this->common['time']['lunch_pack_start'] - 7200) {
            $when = 2;
            $when_name = '午餐时段';
            $pay_time = $this->common['time']['lunch_pack_start'];
        } elseif ($this->common['time']['dinner_order_end'] && $now <= $date + $this->common['time']['dinner_order_end'] + 7200 && $now >= $date + $this->common['time']['dinner_pack_start'] - 7200) {
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
