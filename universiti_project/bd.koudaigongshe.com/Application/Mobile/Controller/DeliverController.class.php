<?php
namespace Mobile\Controller;
use Think\Controller;
class DeliverController extends CommonController {
    /**
    分拨员（7）
    */
    protected function _init(){
        if (!$this->temp['role'][7]) $this->alert('您不是分拨员，无法操作！', U('mobile/index/index'));
        $this->assign('list', $this->list);
        $this->getAim();
        $this->checkTime();
    }

    public function index(){
        $this->assign('list', $this->list);
        $this->display();
    }

    public function apiShow(){
        // 接口格式
        $this->data = [
            'prepare' => [],
            'finished' => [],
            'ready' => [],
            'count' => [
                'undo' => 0,
                'did' => 0,
            ],
        ];
        // 操作数量
        $user_role_data = M('user_role'
            )->comment('查分拨员工作量'
            )->field([
                'num_order_total',
                'num_order_rest',
                'num_dish_total',
                'num_dish_rest',
            ])->where([
                'bd_user_role.id' => ['EQ', $this->temp['role'][7]],
        ])->select();
        $this->data['role'] = $user_role_data[0];
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
            ])->where([
                'bd_canteen_port.canteen' => ['IN', $this->common['aim']],

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
        $dish_data = $dish->order(['bd_order_basic.building'])->select();
        foreach ($dish_data as $key => $value) {
            if ($value['order_basic_status'] == 4) {
                $this->data['prepare'][$value['building_id']]['building_name'] = $value['building_name'];
                $this->data['prepare'][$value['building_id']]['building_id'] = $value['building_id'];
                $this->data['prepare'][$value['building_id']]['dish'][] = $value;
                $this->data['prepare'][$value['building_id']]['count'] += 1;
                $this->data['prepare'][$value['building_id']]['houser'] = [];
                $this->data['count']['undo'] += 1;
            } elseif ($value['order_basic_status'] == 11) {
                $this->data['ready'][$value['building_id']]['building_name'] = $value['building_name'];
                $this->data['ready'][$value['building_id']]['building_id'] = $value['building_id'];
                $this->data['ready'][$value['building_id']]['dish'][] = $value;
                $this->data['ready'][$value['building_id']]['count'] += 1;
                $this->data['ready'][$value['building_id']]['houser'] = [];
                $this->data['count']['undo'] += 1;
            } else {
                $this->data['finished'][$value['building_id']]['building_name'] = $value['building_name'];
                $this->data['finished'][$value['building_id']]['building_id'] = $value['building_id'];
                $this->data['finished'][$value['building_id']]['dish'][] = $value;
                $this->data['finished'][$value['building_id']]['count'] += 1;
                $this->data['finished'][$value['building_id']]['houser'] = [];
                if ($value['deliver_man'] == $this->temp['role'][7]) {
                    $this->data['count']['did'] += 1;
                }
            }
            $buildingid[] = $value['building_id'];
        }
        // echo 'a';
        // p($buildingid);
        if ($buildingid) {
            array_unique($buildingid);
            $houser_data = M('user_role_ext'
                )->comment('楼长'
                )->join([
                    'bd_user_role ON bd_user_role.id = bd_user_role_ext.user_role',
                    'bd_user_basic ON bd_user_basic.unionid = bd_user_role.unionid',
                ])->field([
                    'bd_user_role_ext.aim as id',
                    'bd_user_basic.name',
                    'bd_user_role.mobile',
                ])->where([
                    'bd_user_role_ext.aim' => ['IN', $buildingid],
                    'bd_user_role_ext.status' => ['EQ', 1],
            ])->select();
            foreach ($houser_data as $key => $value) {
                if ($this->data['finished'][$value['id']]) {
                    $this->data['finished'][$value['id']]['houser'][] = $value;
                }
                if ($this->data['prepare'][$value['id']]) {
                    $this->data['prepare'][$value['id']]['houser'][] = $value;
                }
                if ($this->data['ready'][$value['id']]) {
                    $this->data['ready'][$value['id']]['houser'][] = $value;
                }
            }
        }
        $this->ajaxReturn($this->data);
    }
    /**
        派送按钮（分拨员）
    */
    public function sendHandle($id){
        // 权限拦截
        $time = time();
        // $this->temp
        $this->checkTime();
        $order = M('order_basic');
        $order->startTrans();
        $noworder = $order->comment('查找派送'
            )->join([
                'INNER JOIN bd_user_basic ON bd_user_basic.unionid = bd_order_basic.unionid',
                'INNER JOIN bd_school_building ON bd_school_building.id = bd_order_basic.building',
            ])->field([
                'bd_order_basic.order_name',
                'bd_order_basic.id',
                'bd_order_basic.status',
                'bd_user_basic.openid',
            ])->where([
                'bd_order_basic.status' => ['EQ', 11],
                'bd_order_basic.building' => ['EQ', $id],
                '_complex' => [
                    'bd_order_basic.pay_time' => ['ELT', $this->common['time']['pay_time']],//支付要早于出单时间
                    'bd_school_building.mode' => ['NEQ', 2],
                    '_logic' => 'AND',
                ],
        ])->select();
        foreach ($noworder as $key => $value) {
            $order_id[] = $value['id'];
        }
        // 总量
        $order_dish_count = M('order_dish')->where([
            'bd_order_dish.status' => ['IN', '1,2,3'],
            'bd_order_dish.`order`' => ['IN', $order_id],
        ])->count();

        if ($noworder) {
            foreach ($noworder as $key => $value) {
                $message = [
                    'first' => [
                        'value' => '开始配送了，美食马上到！',
                        'color' => '#398DEE',
                    ],
                    'OrderSn' => [
                        'value' => $value['id'] . '（' . $value['order_name'] . '）',
                        'color' => '#000000',
                    ],
                    'OrderStatus' => [
                        'value' => '开始派送',
                        'color' => '#FF6550',
                    ],
                    'remark' => [
                        'value' => '点此查看实时状态或投诉',
                        'color' => '#9F9F9F',
                    ],
                ];
                SendTempletMessage($value['openid'], 'http://bd.koudaigongshe.com/mobile/personal/index?id=' . $value['id'], $message, 1);
                // 修改订单数据
                $order_save['id'] = $value['id'];
                $order_save['status'] = 5;
                $order_save['deliver_time'] = $time;
                $order_save['edit_time'] = $time;
                $order_save['deliver_man'] = $this->temp['role'][7];
                $user_role_save = [
                    'id' => $this->temp['role'][7],
                    'num_order_rest' => ['exp', 'num_order_rest + ' . count($noworder)],
                    'num_order_total' => ['exp', 'num_order_total + ' . count($noworder)],
                    'num_dish_rest' => ['exp', 'num_dish_rest + ' . $order_dish_count],
                    'num_dish_total' => ['exp', 'num_dish_total + ' . $order_dish_count],
                ];
                if ($order->save($order_save) === false || M('user_role')->save($user_role_save) === false) {
                    $order->rollback();
                    $this->alert('错误！', U('mobile/deliver/index'));
                }
            }
            $order->commit();
            // 模板消息
            $housemaster = M('user_role_ext'
                )->join([
                    'INNER JOIN bd_user_role ON bd_user_role.id = bd_user_role_ext.user_role',
                    'INNER JOIN bd_user_basic ON bd_user_role.unionid = bd_user_basic.unionid',
                    'INNER JOIN bd_school_building ON bd_school_building.id = bd_user_role_ext.aim',
                ])->where([
                    'bd_user_role_ext.aim' => ['EQ', $id],
                    'bd_user_role.role' => ['eq', 1],
                    'bd_user_role_ext.status' => ['eq', 1],
                    'bd_user_role.status' => ['eq', 1],
                ])->field([
                    'bd_user_basic.openid',
                    'bd_school_building.name',
                    'bd_user_role_ext.user_role',
                    'bd_user_role_ext.aim',
            ])->select();
            foreach ($housemaster as $key => $value) {
                $message = [
                    'first' => [
                        'value' => '任务通知',
                        'color' => '#743A3A',
                    ],
                    'OrderSn' => [
                        'value' => '楼栋 ' . $value['name'],
                        'color' => '#743A3A',
                    ],
                    'OrderStatus' => [
                        'value' => '分拨员已送达',
                        'color' => '#743A3A',
                    ],
                    'remark' => [
                        'value' => '请及时前去派送',
                        'color' => '#743A3A',
                    ],
                ];
                SendTempletMessage($value['openid'], 'http://bd.koudaigongshe.com/mobile/houser/index', $message, 1);
            }
            $this->alert('成功！', U('mobile/deliver/index'));
        } else {
            $this->alert('没有数据，是不是哪里搞错了', U('mobile/deliver/index'));
        }
    }
    /**
        操作目标
    */
    private function getAim(){
        $data = M('user_role_ext'
            )->comment('操作范围'
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
                'user_role' => ['EQ', $this->temp['role'][7]],
        ])->select();
        if (!$data) {
            $this->alert('您已成为分拨员但还未被分配餐厅', U('mobile/index/index'));
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
        } elseif ($this->common['time']['lunch_order_end'] && $now <= $date + $this->common['time']['lunch_order_end'] + 7200) {
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
