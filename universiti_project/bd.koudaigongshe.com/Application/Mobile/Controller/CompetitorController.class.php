<?php
namespace Mobile\Controller;
use Think\Controller;
class CompetitorController extends CommonController {
    /**
        必点送（11）
    */
    protected function _init(){
        if (!$this->temp['role'][11]) $this->alert('您不是必点送，无法操作！', U('mobile/index/index'));
        $this->assign('list', $this->list);
        $this->getAim();
        $this->checkTime();
    }

    public function index(){
        $this->display();
    }

    public function apiShow(){
        $this->data = [
            'undo' => [],
            'did' => [],
            'finished' => [],
            'count' => [
                'undo' => 0,
                'did' => 0,
                'finished' => 0,
            ],
            'role' => [
                'num_order_total' => 0,
                'num_order_rest' => 0,
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
                'bd_user_role.id' => ['EQ', $this->temp['role'][11]],
        ])->select();
        if ($user_role_data) {
            $this->data['role'] = $user_role_data[0];
        }
        $dish = M('canteen_port');
        $dish->comment('抢单中心查询'
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

                'bd_order_basic.pay_time',
                'bd_order_dish.when',
                'bd_canteen_port.name as port_name',
                'bd_order_dish.name as order_dish_name',
                'bd_order_basic.name as name',

                'bd_order_basic.deliver_man',
                'bd_order_basic.deliver_time',

                '`bd_order_basic`.`mobile`',
                'bd_school_building.name as building_name',
                'bd_order_basic.dormitory',
                'bd_order_dish.money as money',
                'bd_order_basic.word as word',
            ])->where([
                'bd_canteen_port.canteen' => ['IN', $this->common['aim']],
                'bd_order_basic.deliver_man' => [
                    ['EQ', 0],
                    ['EQ', $this->temp['role'][11]],
                    'OR',
                ],
                'bd_order_basic.status' => ['IN', '4,12,13'],//订单为已支付未出单状态
                'bd_order_dish.status' => ['IN', '1,2,3,8'],//操作对象状态 已支付未出单


                '_complex' => [
                    'bd_order_basic.pay_time' => ['GT', $this->common['time']['pay_time']],//支付要早于出单时间
                    'bd_school_building.mode' => ['EQ', 2],
                    '_logic' => 'OR',
                ],
                // 'bd_order_basic.pay_time' => ['GT', $this->common['time']['pay_time']],//支付要早于出单时间
                'bd_order_basic.order_date' => ['EQ', $this->common['time']['date']],//当天的订单
                'bd_order_basic.when' => ['EQ', $this->common['time']['when']],//时间段
        ]);
        $dish_data = $dish->order('bd_order_basic.pay_time ASC')->select();
        foreach ($dish_data as $key => $value) {
            $value['pay_time'] = date('H:i', $value['pay_time']);
            if ($value['deliver_man'] == 0) {
                // 未抢
                $this->data['undo'][$value['order_basic_id']]['order'][] = $value;
                $this->data['undo'][$value['order_basic_id']]['count'] += 1;
            } elseif ($value['deliver_man'] == $this->temp['role'][11] && $value['order_basic_status'] == 12) {
                $this->data['did'][$value['order_basic_id']]['order'][] = $value;
                $this->data['did'][$value['order_basic_id']]['count'] += 1;
            } elseif ($value['deliver_man'] == $this->temp['role'][11] && $value['order_basic_status'] == 13) {
                $this->data['finished'][$value['order_basic_id']]['order'][] = $value;
                $this->data['finished'][$value['order_basic_id']]['count'] += 1;
            }
        }
        $this->data['count']['undo'] = count($this->data['undo']);
        $this->data['count']['did'] = count($this->data['did']);
        $this->data['count']['finished'] = count($this->data['finished']);
        $this->ajaxReturn($this->data);
    }
    /**
        抢单操作
    */
    public function catch($id){
        $dish = M('canteen_port');
        $dish->comment('抢单中心查询'
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
                'bd_order_dish.money_cost',
                'bd_order_basic.word as word',
                'bd_canteen.device',
            ])->where([
                'bd_canteen_port.canteen' => ['IN', $this->common['aim']],
                'bd_order_basic.id' => ['EQ', $id],

                'bd_order_basic.status' => ['IN', '4'],//订单为已支付未出单状态
                'bd_order_dish.status' => ['IN', '1,2,3,8'],//操作对象状态 已支付未出单

                '_complex' => [
                    'bd_order_basic.pay_time' => ['GT', $this->common['time']['pay_time']],//支付要早于出单时间
                    'bd_school_building.mode' => ['EQ', 2],
                    '_logic' => 'OR',
                ],
                // 'bd_order_basic.pay_time' => ['GT', $this->common['time']['pay_time']],//支付要早于出单时间
                'bd_order_basic.order_date' => ['EQ', $this->common['time']['date']],//当天的订单
                'bd_order_basic.when' => ['EQ', $this->common['time']['when']],//时间段
        ]);
        $dish->startTrans();
        $dish_data = $dish->select();
        // 出单
        if ($dish_data) $this->arrangeOrder($dish_data, 3);
        else $this->alert('抢单失败', U('mobile/competitor/index'));
    }
    /**
        送达操作
    */
    public function sended($id){
        $order = M('order_basic');
        $order->startTrans();
        $order_data = $order->comment('查订单'
            )->join('INNER JOIN bd_user_basic ON bd_user_basic.unionid = bd_order_basic.unionid'
            )->field([
                'bd_user_basic.openid',
                'bd_order_basic.pack_time',
            ])->where([
                'id' => ['EQ', $id],
                'deliver_man' => ['EQ', $this->temp['role'][11]],
                'status' => ['EQ', 12],
        ])->select()[0];
        $dish_count = M('order_dish')->where([
            'bd_order_dish.`order`' => ['EQ', $id],
            'bd_order_dish.status' => ['IN', '1,2,3,8'],
        ])->count();
        $user_role_save = [
            'id' => $this->temp['role'][11],
            'num_order_rest' => ['exp', 'num_order_rest + 1'],
            'num_order_total' => ['exp', 'num_order_total + 1'],
            'num_dish_total' => ['exp', 'num_dish_total + ' . $dish_count],
            'num_dish_rest' => ['exp', 'num_dish_rest + ' . $dish_count],
        ];
        if ($order_data) {
            $order_save = [
                'housemaster_time' => $this->common['time']['now'],
                'housemaster' => $this->temp['role'][11],
                'status' => 13,
            ];
            if ($order->where(['id' => ['EQ', $id]])->save($order_save) !== false &&
                M('user_role')->save($user_role_save)
            ) {
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
                        'value' => "\n本次配送用时" . intval(($this->common['time']['now'] - $order_data['pack_time']) / 60) . '分钟' . "\n点击“详情”评价或投诉",
                        'color' => '#FF8040',
                    ],
                ];
                SendTempletMessage($order_data['openid'], 'http://bd.koudaigongshe.com/mobile/personal/index', $message, 1);
                $this->alert('成功', U('mobile/competitor/index'));
            } else {
                $order->rollback();
                $this->alert('失败', U('mobile/competitor/index'));
            }
        } else {
            $order->rollback();
            $this->alert('您无法操作这个订单', U('mobile/competitor/index'));
        }
    }

    /**
        补单
    */
    public function backupHandle($id){
        $dish = M('canteen_port');
        $dish->comment('抢单中心查询'
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
                'bd_order_dish.money_cost',
                'bd_order_basic.word as word',
                'bd_canteen.device',
            ])->where([
                'bd_canteen_port.canteen' => ['IN', $this->common['aim']],
                'bd_order_basic.id' => ['EQ', $id],
                'bd_order_basic.deliver_man' => ['EQ', $this->temp['role'][11]],

                'bd_order_basic.status' => ['IN', '12,13'],//订单为已支付未出单状态
                'bd_order_dish.status' => ['IN', '1,2,3,8'],//操作对象状态 已支付未出单

                '_complex' => [
                    'bd_order_basic.pay_time' => ['GT', $this->common['time']['pay_time']],//支付要早于出单时间
                    'bd_school_building.mode' => ['EQ', 2],
                    '_logic' => 'OR',
                ],
                'bd_order_basic.order_date' => ['EQ', $this->common['time']['date']],//当天的订单
                'bd_order_basic.when' => ['EQ', $this->common['time']['when']],//时间段
        ]);
        $dish_data = $dish->order('bd_canteen_port.id')->select();
        // p($dish_data);
        if ($dish_data) $this->arrangeOrder($dish_data, 0);
        else $this->alert('好像没有数据，是不是哪里搞错了？', U('mobile/competitor/index'));
    }
    /**
        出单实现
    */
    private function arrangeOrder($dish_data, $status = 0){
        $i = 0;
        foreach ($dish_data as $key => $value) {
            // 更新菜品状态
            $xiaopiao .= "--------------------------------\n必点 食堂外卖\n美食城饭菜 帮你打包 送上寝室\n--------------------------------\n\n档口：" . $value['port_name'] . "\n价格：￥" . $value['money'] / 100 . "\n小票：" . $value['order_dish_id'] . "  订单：" . $value['order_basic_id'] . "\n" . date("Y-m-d h:i:s") . " " . $when_name . "\n<Font# Bold=0 Width=2 Height=2>" . $value['order_dish_name'] . "</Font#>\n留言：" . $value['word'] . "\n<Font# Bold=0 Width=2 Height=2>" . $value['building_name'] . ' ' . $value['dormitory'] . " 室</Font#>\n" . $value['name'] . " " . $value['mobile'] . "\n--------------------------------" . "\n\n";

            if ($value['port'] != $dish_data[$key - 1]['port']) {
                $copy[$i]['head'] = "\n<Font# Bold=0 Width=2 Height=2>存根联</Font#>\n" . date('Y-m-d h:i:s') . "\n" . $this->common['time']['when_name'] . " 小票存根\n--------------------------------\n<Font# Bold=0 Width=2 Height=2>" . $value['port_name'] . "</Font#>\n\n";
                $i += 1;
            }
            $copy[$i - 1]['foot'] .= '#' . $value['port_name'] . ' 价格：￥' . $value['money_cost'] / 100 . "\n小票：" . $value['order_dish_id'] . "\n菜品：" . $value['order_dish_name'] . "\n--------------------------------\n";

            $dish_save_where[] = $value['order_dish_id'];
            $id = $value['order_basic_id'];
            $copy[$i - 1]['money_cost'] += $value['money_cost'];
            $copy[$i - 1]['num'] += 1;
        }
        $device = $value['device'];
        $xiaopiao .= "\n\n";
        foreach ($copy as $key => $value) {
            $xiaopiao .= $value['head'] . "总计单量：" . $value['num'] . " 总金额：" . $value['money_cost'] / 100 . "\n--------------------------------\n" . $value['foot'] . "\n\n";
        }
        // p($xiaopiao);
        // 需要保存
        if ($status != 0) {
            // 首次出单修改状态
            $dish_save = [
                'status' => $status,
                'pack_time' => $this->common['time']['now'],
            ];
            $dish = M('order_dish');
            // 修改订单状态
            $order_save = [
                'id' => $id,
                'status' => 12,
                'pack_time' => $this->common['time']['now'],
                'deliver_time' => $this->common['time']['now'],
                'deliver_man' => $this->temp['role'][11],
            ];
            if (M('order_basic')->save($order_save) !== false && false !== $dish->where(['id' => ['IN', $dish_save_where]])->save($dish_save)) {
                if (testSendFreeMessage($xiaopiao, $device) != 0) {
                    $dish->rollback();
                    $this->alert('打印异常', U('mobile/competitor/index'));
                }
                $dish->commit();
                $this->alert('抢单成功', U('mobile/competitor/index'));
            } else {
                $dish->rollback();
                $this->alert('网络异常', U('mobile/competitor/index'));
            }
        } elseif (testSendFreeMessage($xiaopiao, $device) == 0){
            // 非首次出单成功
            $this->alert('成功！', U('mobile/competitor/index'));
        } else {
            // 非首次出单失败
            $this->alert('打印中途异常', U('mobile/competitor/index'));
        }
    }
    /**
        操作目标
    */
    private function getAim(){
        $data = M('user_role_ext')->comment('操作范围'
            )->join([
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
                'bd_user_role_ext.user_role' => ['EQ', $this->temp['role'][11]],
                'bd_user_role_ext.status' => ['EQ', 1],
        ])->select();
        if (!$data) {
            $this->alert('您已成为必点送但还未被分配餐厅！', U('mobile/index/index'));
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
        if ($pay_time == 0) $pay_time = -345600;

        $this->common['time']['now'] = $now;
        $this->common['time']['date'] = $date;
        $this->common['time']['when'] = $when;
        $this->common['time']['when_name'] = $when_name;
        $this->common['time']['pay_time'] = $date + $pay_time;
    }
}
