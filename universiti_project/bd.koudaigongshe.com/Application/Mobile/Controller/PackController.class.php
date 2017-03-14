<?php
namespace Mobile\Controller;
use Think\Controller;
class PackController extends CommonController {
    /**
        出单员（8）
    */
    protected function _init(){
        if (!$this->temp['role'][8]) $this->alert('您不是出单员', U('mobile/index/index'));
        $this->assign('list', $this->list);
        $this->getAim();
        $this->checkTime();
    }
    public function index(){
        $this->display();
    }
    public function apiShow(){
        $this->data = [
            'port' => [],
            'count' => [
                'order_undo' => [
                    'num' => 0,
                    'content' => '未出单订单量',
                ],
                'order_did' => [
                    'num' => 0,
                    'content' => '已出订单量',
                ],
                'dish_undo' => [
                    'num' => 0,
                    'content' => '未出单品量',
                ],
                'dish_packed' => [
                    'num' => 0,
                    'content' => '已出单品量',
                ],
                'dish_backuped' => [
                    'num' => 0,
                    'content' => '底单已出量',
                ],
                'dish_total' => [
                    'num' => 0,
                    'content' => '全部单品量',
                ],
            ],
        ];
        $dish = M('canteen_port');
        $dish->startTrans();
        $dish->comment('查找要出的菜品'
            )->join([
                'INNER JOIN bd_order_dish ON bd_order_dish.port = bd_canteen_port.id',
                'INNER JOIN bd_order_basic ON `bd_order_basic`.`id` = `bd_order_dish`.`order`',
                'INNER JOIN bd_school_building ON `bd_order_basic`.`building` = `bd_school_building`.`id`',
                'INNER JOIN bd_canteen ON bd_canteen.id = bd_canteen_port.canteen',
            ])->field([
                'bd_order_dish.id as order_dish_id',
                'bd_order_basic.id as order_basic_id',
                'bd_order_basic.status as order_basic_status',
                'bd_school_building.id as building_id',
                'bd_order_dish.status as order_dish_status',
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
                'bd_canteen.device',
            ])->where([
                'bd_canteen_port.canteen' => ['EQ', $this->common['aim']],

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
        // p($dish_data);
        foreach ($dish_data as $key => $value) {
            // p($value);
            $this->data['port'][$value['port']]['id'] = $value['port'];
            $this->data['port'][$value['port']]['port_name'] = $value['port_name'];
            $this->data['port'][$value['port']]['num'] += 1;
            $this->data['count']['dish_total']['num'] += 1;
            if ($value['order_basic_status'] == 4) {
                // 待出单
                $this->data['count']['order_undo']['num'] += 1;
            } else {
                // 已出单
                $this->data['count']['order_did']['num'] += 1;
            }
            if ($value['order_dish_status'] == 1) {
                $this->data['count']['dish_undo']['num'] += 1;
            } elseif ($value['order_dish_status'] == 2) {
                $this->data['count']['dish_packed']['num'] += 1;
            } elseif ($value['order_dish_status'] == 3) {
                $this->data['count']['dish_backuped']['num'] += 1;
            }
        }
        $this->ajaxReturn($this->data);
    }
    /**
        出单
    */
    public function packHandle(){
        // 打印订单测试
        // if ($this->common['time']['when'] == 2 && $this->common['time']['now'] < $this->common['time']['date'] + $this->common['time']['lunch_pack_start']) {
        //     $this->alert('时辰未到！', U('mobile/pack/index'));
        // } elseif ($this->common['time']['when'] == 3 && $this->common['time']['now'] < $this->common['time']['date'] + $this->common['time']['dinner_pack_start']) {
        //     $this->alert('时辰未到！', U('mobile/pack/index'));
        // }
        $dish = M('canteen_port');
        $dish->startTrans();
        $dish->comment('查找要出的菜品'
            )->join([
                'INNER JOIN bd_order_dish ON bd_order_dish.port = bd_canteen_port.id',
                'INNER JOIN bd_order_basic ON `bd_order_basic`.`id` = `bd_order_dish`.`order`',
                'INNER JOIN bd_school_building ON `bd_order_basic`.`building` = `bd_school_building`.`id`',
                'INNER JOIN bd_canteen ON bd_canteen.id = bd_canteen_port.canteen',
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
                'bd_canteen.device',
            ])->where([
                'bd_canteen_port.canteen' => ['EQ', $this->common['aim']],

                'bd_order_basic.status' => ['IN', '4,5,6,7,11'],//订单为已支付未出单状态
                'bd_order_dish.status' => ['EQ', '1'],//操作对象状态 已支付未出单

                '_complex' => [
                    'bd_order_basic.pay_time' => ['ELT', $this->common['time']['pay_time']],//支付要早于出单时间
                    'bd_school_building.mode' => ['NEQ', 2],
                    '_logic' => 'AND',
                ],
                'bd_order_basic.order_date' => ['EQ', $this->common['time']['date']],//当天的订单
                'bd_order_basic.when' => ['EQ', $this->common['time']['when']],//时间段
        ]);
        $dish_data = (clone $dish)->order('bd_canteen_port.order_pack DESC,bd_order_dish.port,bd_order_dish.dish')->limit('0,10')->select();
        // p($dish_data);
        if ($dish_data) {
            $this->arrangeOrder($dish_data, 2);
            // 检查剩余单量
            $dish_count = $dish->count();
        }

        // 当有剩余订单时会提示继续出单
        if ($dish_count) {
            $this->alert('成功出' . count($dish_data) . '份，请继续出单直至出单完成', U('mobile/pack/packHandle'));
        } else {
            $order = M('order_basic');
            $order->comment('查找要出的菜品')->join([
                    'bd_school_building ON bd_order_basic.building = bd_school_building.id',
                ])->field([
                    'bd_order_basic.id',
                ])->where([
                    'bd_order_basic.canteen' => ['EQ', $this->common['aim']],
                    'bd_order_basic.status' => ['EQ', '4'],//订单为已支付未出单状态

                    '_complex' => [
                        'bd_order_basic.pay_time' => ['ELT', $this->common['time']['pay_time']],//支付要早于出单时间
                        'bd_school_building.mode' => ['NEQ', 2],
                        '_logic' => 'AND',
                    ],
                    'bd_order_basic.order_date' => ['EQ', $this->common['time']['date']],//当天的订单
                    'bd_order_basic.when' => ['EQ', $this->common['time']['when']],//时间段
            ]);
            $order->startTrans();
            $order_data = $order->select();
            foreach ($order_data as $key => $value) {
                $order_save_id[] = $value['id'];
            }
            // p($order_data);
            if ($order_data) {
                $order_save = [
                    'status' => 11,
                    'pack_time' => $this->common['time']['now'],
                ];
                if (false === M('order_basic')->where(['id' => ['IN', $order_save_id]])->save($order_save)) {
                    $order->rollback();
                    $this->alert('失败', U('mobile/pack/index'));
                }
            }
            $order->commit();
            $this->alert($when_name . '出单成功，请出底单并核对信息！', U('mobile/pack/index'));
        }
    }

    public function copyHandle(){
        // if ($this->common['time']['when'] == 2 && $this->common['time']['now'] < $this->common['time']['date'] + $this->common['time']['lunch_pack_start']) {
        //     $this->alert('时辰未到！', U('mobile/pack/index'));
        // } elseif ($this->common['time']['when'] == 3 && $this->common['time']['now'] < $this->common['time']['date'] + $this->common['time']['dinner_pack_start']) {
        //     $this->alert('时辰未到！', U('mobile/pack/index'));
        // }
        // 出单员可以操作的档口
        $port = M('canteen_port');
        $port->comment('查找工作对象'
            )->field([
                'bd_canteen_port.id',
            ])->where([
                'bd_canteen_port.canteen' => ['EQ', $this->common['aim']],
                'bd_canteen_port.status' => ['EQ', 1],
            ])->order([
                'bd_canteen_port.order_pack DESC',
        ]);

        $port_data = $port->select();
        // p($port_data);
        foreach ($port_data as $key => $value) {
            $dish = M('canteen_port');
            $dish->comment('查找要出的菜品'
                )->join([
                    'INNER JOIN bd_order_dish ON bd_order_dish.port = bd_canteen_port.id',
                    'INNER JOIN bd_order_basic ON `bd_order_basic`.`id` = `bd_order_dish`.`order`',
                    'INNER JOIN bd_school_building ON `bd_order_basic`.`building` = `bd_school_building`.`id`',
                    'INNER JOIN bd_canteen ON bd_canteen.id = bd_canteen_port.canteen',
                ])->field([
                    'bd_canteen_port.name as port_name',

                    'bd_order_dish.id as order_dish_id',
                    'bd_order_basic.id as order_basic_id',
                    'bd_order_basic.status as order_basic_status',
                    'bd_order_dish.status as dish_status',
                    'bd_order_dish.port',
                    'bd_order_dish.dish as order_dish_dish',

                    'bd_order_dish.when',
                    'bd_canteen_port.name as port_name',
                    'bd_order_dish.name as order_dish_name',
                    'bd_order_dish.money_cost as money',
                    'bd_order_basic.word as word',

                    'bd_canteen.device',
                ])->where([
                    'bd_order_basic.canteen' => ['EQ', $this->common['aim']],
                    'bd_order_dish.port' => ['EQ', $value['id']],

                    'bd_order_basic.status' => ['in', '4,5,6,7,11'],//订单为已支付未出单状态
                    'bd_order_dish.status' => ['eq', '2'],//操作对象状态 已支付未出单


                    '_complex' => [
                        'bd_order_basic.pay_time' => ['ELT', $this->common['time']['pay_time']],//支付要早于出单时间
                        'bd_school_building.mode' => ['NEQ', 2],
                        '_logic' => 'AND',
                    ],
                    // 'bd_order_basic.pay_time' => ['ELT', $this->common['time']['pay_time']],//支付要早于出单时间
                    'bd_order_basic.order_date' => ['EQ', $this->common['time']['date']],//当天的订单
                    'bd_order_basic.when' => ['EQ', $this->common['time']['when']],//时间段
            ]);
            $dish->startTrans();
            $dish_data = $dish->order('bd_canteen_port.order_pack DESC,bd_canteen_port.id')->select();
            if ($dish_data) $this->arrangeCopy($dish_data, 3);
        }
        $this->alert($when_name . '底单已完成，请核对！', U('mobile/pack/index'));
    }

    public function packOrder($id) {
        $dish = M('canteen_port');
        $dish->comment('查找要出的菜品'
            )->join([
                'INNER JOIN bd_order_dish ON bd_order_dish.port = bd_canteen_port.id',
                'INNER JOIN bd_order_basic ON `bd_order_basic`.`id` = `bd_order_dish`.`order`',
                'INNER JOIN bd_school_building ON `bd_order_basic`.`building` = `bd_school_building`.`id`',
                'INNER JOIN bd_canteen ON `bd_canteen`.`id` = `bd_canteen_port`.`canteen`',
            ])->field([
                'bd_order_dish.id as order_dish_id',
                'bd_order_basic.id as order_basic_id',
                'bd_order_basic.status as order_basic_status',
                'bd_order_dish.status as dish_status',
                'bd_order_dish.port',
                'bd_order_dish.dish as order_dish_dish',

                'bd_school_building.name as building_name',
                'bd_order_basic.dormitory',

                'bd_order_dish.when',
                'bd_canteen_port.name as port_name',
                'bd_order_dish.name as order_dish_name',
                'bd_order_dish.money as money',
                'bd_order_basic.word as word',

                'bd_canteen.device',
            ])->where([
                'bd_canteen_port.canteen' => ['EQ', $this->common['aim']],
                'bd_order_dish.port' => ['eq', $id],

                'bd_order_basic.status' => ['in', '4,5,6,7,11'],

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
        // p($dish_data)
        if ($dish_data) $this->arrangeOrder($dish_data, 0);
        else $this->alert('本档口没有单', U('mobile/pack/index'));
    }

    public function packCopy($id) {
        $dish = M('canteen_port');
        $dish->comment('查找要出的菜品'
            )->join([
                'INNER JOIN bd_order_dish ON bd_order_dish.port = bd_canteen_port.id',
                'INNER JOIN bd_order_basic ON `bd_order_basic`.`id` = `bd_order_dish`.`order`',
                'INNER JOIN bd_school_building ON `bd_order_basic`.`building` = `bd_school_building`.`id`',
                'INNER JOIN bd_canteen ON `bd_canteen`.`id` = `bd_canteen_port`.`canteen`',
            ])->field([
                'bd_order_dish.id as order_dish_id',
                'bd_order_basic.id as order_basic_id',
                'bd_order_basic.status as order_basic_status',
                'bd_order_dish.status as dish_status',
                'bd_order_dish.port',
                'bd_order_dish.dish as order_dish_dish',

                'bd_order_basic.dormitory',
                'bd_school_building.name as building_name',
                'bd_canteen.device',
                'bd_order_dish.when',
                'bd_canteen_port.name as port_name',
                'bd_order_dish.name as order_dish_name',
                'bd_order_dish.money_cost as money',
                'bd_order_basic.word as word',
            ])->where([
                'bd_canteen_port.canteen' => ['EQ', $this->common['aim']],
                'bd_order_dish.port' => ['eq', $id],

                'bd_order_basic.status' => ['in', '4,5,6,7,11'],

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
        // p($dish_data)
        if ($dish_data) $this->arrangeCopy($dish_data, 0);
        else $this->alert('本档口没有单', U('mobile/pack/index'));
    }

    /**
        出单实现
    */
    private function arrangeOrder($dish_data, $status = 0){
        foreach ($dish_data as $key => $value) {
            // 更新菜品状态
            $xiaopiao .= "\n\n" . "--------------------------------\n必点 食堂外卖\n美食城饭菜 帮你打包 送上寝室\n--------------------------------\n\n档口：" . $value['port_name'] . "\n价格：￥" . $value['money'] / 100 . "\n小票：" . $value['order_dish_id'] . "  订单：" . $value['order_basic_id'] . "\n" . date("Y-m-d h:i:s") . " " . $this->common['time']['when_name'] . "\n<Font# Bold=0 Width=2 Height=2>" . $value['order_dish_name'] . "</Font#>\n留言：" . $value['word'] . "\n<Font# Bold=0 Width=2 Height=2>" . $value['building_name'] . ' ' . $value['dormitory'] . " 室</Font#>\n" . $value['name'] . " " . $value['mobile'] . "\n--------------------------------";
            $dish_save_where[] = $value['order_dish_id'];
        }
        // $device = $value['device'];
        // 需要保存
        if ($status != 0) {
            $dish_save = [
                'status' => $status,
                'pack_time' => $this->common['time']['now'],
            ];
            $dish = M('order_dish');
            if (false === $dish->where(['id' => ['IN', $dish_save_where]])->save($dish_save)) {
                $dish->rollback();
                $this->alert('数据更新异常', U('mobile/pack/index'));
            }
        }
        // if (testSendFreeMessage($xiaopiao, $value['device']) == 0) {
        // p($xiaopiao);
        if (testSendFreeMessage($xiaopiao, $value['device']) == 0) {
            if ($status != 0) $dish->commit();
            else $this->alert('成功', U('mobile/pack/index'));
        } else {
            $dish->rollback();
            $this->alert('打印中途异常', U('mobile/pack/index'));
        }
    }
    private function arrangeCopy($dish_data, $status = 0){
        foreach ($dish_data as $key => $value) {
            // 更新菜品状态
            $money += $value['money'];
            $copy .= '#' . $value['port_name'] . ' 价格：￥' . $value['money'] / 100 . "\n小票：" . $value['order_dish_id'] . "\n菜品：" . $value['order_dish_name'] . "\n--------------------------------\n";
            $dish_save_where[] = $value['order_dish_id'];
        }
        $xiaopiao = "<Font# Bold=0 Width=2 Height=2>存根联</Font#>\n" . date('Y-m-d h:i:s') . "\n" . $this->common['time']['when_name'] . " 小票存根\n--------------------------------\n<Font# Bold=0 Width=2 Height=2>" . $value['port_name'] . "</Font#>\n" . "\n总计单量：" . count($dish_data) . " 总金额：" . $money / 100 ."\n--------------------------------" . "\n" . $copy;
        // p($xiaopiao);
        if ($status != 0) {
            $dish_save = [
                'status' => $status,
                'pack_time' => $this->common['time']['now'],
            ];
            $dish = M('order_dish');
            $dish_save_return = M('order_dish'
                )->comment('出底单状态修改'
                )->where([
                    'id' => ['IN', $dish_save_where],
                ])->save($dish_save);
            if (false === $dish_save_return) {
                $dish->rollback();
                $this->alert('数据更新异常', U('mobile/pack/index'));
            }
        }
        if (testSendFreeMessage($xiaopiao, $value['device']) == 0) {
            if ($status != 0) {
                $dish->commit();
            }
            usleep(400000);
        } else {
            $dish->rollback();
            $this->alert('打印中途异常', U('mobile/pack/index'));
        }
    }
    /**
        操作目标
    */
    private function getAim(){
        $data = M('user_role_ext')->comment('操作范围')->join([
            'INNER JOIN bd_canteen ON bd_canteen.id = bd_user_role_ext.aim',
            'INNER JOIN bd_school_ext ON bd_school_ext.id = bd_canteen.school_ext',
        ])->field([
            'bd_canteen.id',
            'bd_school_ext.breakfast_pack_start',
            'bd_school_ext.breakfast_order_end',
            'bd_school_ext.lunch_pack_start',
            'bd_school_ext.lunch_order_end',
            'bd_school_ext.dinner_pack_start',
            'bd_school_ext.dinner_order_end',
        ])->where([
            'bd_user_role_ext.user_role' => ['EQ', $this->temp['role'][8]],
            'bd_user_role_ext.status' => ['EQ', 1],
        ])->select();
        if (!$data) {
            $this->alert('您不是出单员或未被安排工作！', U('mobile/index/index'));
        } else {
            foreach ($data as $key => $value) {
                $this->common['aim'] = $value['id'];
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
        } elseif ($this->common['time']['lunch_order_end'] && $now <= $date + $this->common['time']['lunch_order_end'] + 7200 && $now >= $date + $this->common['time']['lunch_pack_start'] - 7200) {
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
