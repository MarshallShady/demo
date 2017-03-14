<?php
namespace Mobile\Controller;
use Think\Controller;
class ManagerController extends CommonController {
    /**
        档口管理员
    */
    protected function _init(){
        if (!$this->temp['role'][5]) $this->alert('您不是校园经理', U('mobile/index/index'));
        $this->assign('list', $this->list);
        $this->getAim();
        $this->checkTime();
    }

    public function index(){
        $this->display();
    }

    public function apiShowIndex($school = 0, $len = 0) {

        // $this->common['aim'] = [
        //     0 => '1',
        // ];

        $data = [
            'school' => 0,
            'order' => [],//当前订单内容
            'data' => [
                'school' => [],
            ],
            'count' => [
                'money' => 0,//本时段营业额
                'undo' => 0,//本时段未结单
                'did' => 0,//本时段已完成
            ],
            'history' => [],
            'len' => 0,
        ];
        $order_basic = M('order_basic');
        $school_ext = M('school_ext');
        if ($school == 0) {
            $data['data']['school'] = $school_ext->comment(''
                )->join([
                    'INNER JOIN bd_school ON bd_school_ext.school = bd_school.id',
                ])->field([
                    'bd_school_ext.id',
                    'bd_school.name as school_name',
                    'bd_school_ext.name as ext_name',
                ])->where([
                    'bd_school_ext.id' => ['IN', $this->common['aim']],
            ])->select();
            $data['school'] = $data['data']['school'][0]['id'];

        } else {
            $data['school'] = $school;
        }

        $order_basic->join('bd_school_building ON bd_school_building.id = bd_order_basic.building'
            )->where([
                'bd_school_building.school_ext' => ['EQ', $data['school']],
            ]);
        if ($len == 0) {
            // 首次请求返回order内容
            $order_basic_data = (clone $order_basic)->comment('校园经理'
                )->join('bd_dict_order_basic_status ON bd_order_basic.status = bd_dict_order_basic_status.id'
                )->join('bd_order_dish ON bd_order_basic.id = bd_order_dish.`order`'

                )->join('bd_canteen_port ON bd_canteen_port.id = bd_order_dish.port'
                )->field([
                    'bd_order_basic.id as order_basic_id',
                    'bd_order_dish.id as order_dish_id',

                    'bd_canteen_port.name as port_name',
                    'bd_school_building.name as building_name',
                    'bd_order_basic.dormitory',
                    'bd_order_basic.name',
                    'bd_order_basic.mobile',
                    'bd_order_dish.name as dish_name',
                    'bd_order_basic.money',

                    'bd_order_basic.canteen',
                    'bd_order_basic.pack_time',
                    'bd_order_basic.deliver_man',
                    'bd_order_basic.deliver_time',
                    'bd_order_basic.housemaster',
                    'bd_order_basic.housemaster_time',
                    'bd_order_basic.building',

                    'bd_order_basic.status',
                    'bd_dict_order_basic_status.name as status_name',
                    'bd_order_basic.create_time',
                ])->where([
                    'bd_order_basic.status' => ['IN', '4,5,6,7,11,12,13'],
                    'bd_order_basic.order_date' => $this->common['time']['date'],
                ])->order([
                    'bd_order_basic.id',
            ])->select();
            $school_ext->where([
                'bd_school_ext.id' => ['EQ', $data['school']],
                'bd_user_role_ext.status' => ['EQ', 1],
            ]);
            // 分拨员、目标餐厅
            $deliver_data = (clone $school_ext)->comment('查找'
                )->join('bd_canteen ON bd_canteen.school_ext = bd_school_ext.id'
                )->join('bd_user_role_ext ON bd_user_role_ext.aim = bd_canteen.id'
                )->join('bd_user_role ON bd_user_role.id = bd_user_role_ext.user_role'
                )->join('bd_user_basic ON bd_user_basic.unionid = bd_user_role.unionid'
                )->field([
                    // 'bd_'
                    'bd_user_role.id',
                    'bd_user_role_ext.aim',
                    'bd_user_basic.name',
                    'bd_user_role.mobile',
                ])->where([
            ])->select();
            foreach ($deliver_data as $key => $value) {
                $deliver[$value['aim']][] = $value;
            }
            // 楼长、目标楼
            $houser_data = (clone $school_ext)->comment('查找'
                )->join('bd_school_building ON bd_school_ext.id = bd_school_building.school_ext'
                )->join('bd_user_role_ext ON bd_school_building.id = bd_user_role_ext.aim'
                )->join('bd_user_role ON bd_user_role_ext.user_role = bd_user_role.id'
                )->join('bd_user_basic ON bd_user_basic.unionid = bd_user_role.unionid'
                )->field([

                    'bd_user_role.id',
                    'bd_user_role_ext.aim',
                    'bd_user_basic.name',
                    'bd_user_role.mobile',
                ])->where([
            ])->select();
            foreach ($houser_data as $key => $value) {
                $houser[$value['aim']][] = $value;
            }
            // 必点送、目标餐厅
            $competitor_data = (clone $school_ext)->comment('查找'
                )->join('bd_canteen ON bd_canteen.school_ext = bd_school_ext.id'
                )->join('bd_user_role_ext ON bd_user_role_ext.aim = bd_canteen.id'
                )->join('bd_user_role ON bd_user_role.id = bd_user_role_ext.user_role'
                )->join('bd_user_basic ON bd_user_basic.unionid = bd_user_role.unionid'
                )->field([
                    // 'bd_'
                    'bd_user_role.id',
                    'bd_user_role_ext.aim',
                    'bd_user_basic.name',
                    'bd_user_role.mobile',
                ])->where([
            ])->select();
            foreach ($competitor_data as $key => $value) {
                $data['competitor'][$value['id']] = $value;
            }
            foreach ($order_basic_data as $key => $value) {
                $value['deliver'] = $deliver[$value['canteen']];
                $value['houser'] = $houser[$value['building']];
                if ($value['status'] == 4) {
                    $data['order']['unpack'][$value['order_basic_id']]['dish'][] = $value;
                    $data['order']['unpack'][$value['order_basic_id']]['num'] += 1;
                    $data['count']['undo'] += 1;
                } elseif ($value['status'] == 11) {
                    $data['order']['packed'][$value['order_basic_id']]['dish'][] = $value;
                    $data['order']['packed'][$value['order_basic_id']]['num'] += 1;
                    $data['count']['undo'] += 1;
                } elseif ($value['status'] == 5) {
                    $data['order']['unsend'][$value['order_basic_id']]['dish'][] = $value;
                    $data['order']['unsend'][$value['order_basic_id']]['num'] += 1;
                    $data['count']['undo'] += 1;
                } elseif ($value['status'] == 12) {
                    $data['order']['catched'][$value['order_basic_id']]['dish'][] = $value;
                    $data['order']['catched'][$value['order_basic_id']]['num'] += 1;
                    $data['count']['undo'] += 1;
                } elseif ($value['status'] == 6 || $value['status'] == 13) {
                    $data['order']['sended'][$value['order_basic_id']]['dish'][] = $value;
                    $data['order']['sended'][$value['order_basic_id']]['num'] += 1;
                    $data['count']['did'] += 1;
                } else {
                    $data['order']['unknow'][$value['order_basic_id']]['dish'][] = $value;
                    $data['order']['unknow'][$value['order_basic_id']]['num'] += 1;
                    $data['count']['undo'] += 1;
                }
                $data['count']['money'] += $value['money'];
                // $data['order'][$value['order_basic_id']]['order_basic_id'] = $value['order_basic_id'];

            }
        }
        $data['history'] = $order_basic->comment(''
            )->field([
                'sum(bd_order_basic.money) as money',
                'count(*) as num',
                'bd_order_basic.order_date',
                'bd_order_basic.when',
            ])->where([

                'bd_order_basic.status' => ['IN', '4,5,6,7,11,12,13'],
            ])->order([
                'bd_order_basic.order_date DESC',
                'bd_order_basic.when DESC',
        ])->group('bd_order_basic.order_date,bd_order_basic.when')->limit($len . ',3')->select();
        $data['len'] += $len + count($data['history']);
        $this->ajaxReturn($data);
    }



    /**
        操作目标
    */
    private function getAim(){
        $data = M('user_role_ext')->comment('操作范围'
            )->join([
                'INNER JOIN bd_school_ext ON bd_school_ext.id = bd_user_role_ext.aim',
                // 'INNER JOIN bd_canteen ON bd_canteen.id = bd_user_role_ext.aim',
                // 'INNER JOIN bd_school_ext ON bd_school_ext.id = bd_canteen.school_ext',
            ])->field([
                'bd_school_ext.id',
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
            $this->alert('您已成为校园经理但未被分配任务！', U('mobile/index/index'));
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
