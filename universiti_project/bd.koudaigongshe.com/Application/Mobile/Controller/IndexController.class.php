<?php
namespace Mobile\Controller;
use Think\Controller;
class IndexController extends CommonController {
    // 用于显示订餐首页
    /**
        菜品展示
    */
    public function index(){
        $this->assign('list', $this->list);
        $this->display();
    }

    public function apiShow($canteen = 0, $canteen_port = 0, $when = 0, $len = 0){
        $data = [];
        $where = [];
        if (!is_numeric($len)) exit();
        $nowwhere = M('school_building');
        $now = [
            'date' => strtotime(date('Y-m-d')),
            'when' => time(),
        ];
        $data['building'] = ($nowwhere->comment(''
            )->join([
                'bd_school_ext ON bd_school_ext.id = bd_school_building.school_ext',
            ])->where([
                'bd_school_building.id' => ['EQ', $this->position['building']]
            ])->field([
                'bd_school_ext.money_least',
                'bd_school_ext.money_delivery',
                'bd_school_building.status',
                'bd_school_building.ist_tohome',
                'bd_school_building.nd_tohome',
                'bd_school_building.mode',
        ])->select())[0];
        if ($when == 0) {
            // 默认时间，同时返回时间信息
            $this->getSchoolExt();
            $data['data']['time'] = $this->common['school_ext'];
            // 早餐截止前，晚餐截止后
            if ($data['data']['time']['breakfast_order_end'] && ($now['when'] < $now['date'] + $data['data']['time']['breakfast_order_end'] || $now['when'] > $now['date'] + $data['data']['time']['dinner_order_end'])) $data['when'] = 1;
            // // 早餐截止后，午餐截止前
            elseif ($data['data']['time']['lunch_order_end'] && ($now['when'] < $now['date'] + $data['data']['time']['lunch_order_end'] || $now['when'] > $now['date'] + $data['data']['time']['dinner_order_end'])) $data['when'] = 2;
            // 午餐截止后，晚餐截止前
            else {
                $data['when'] = 3;
            }
        } else {
            $data['when'] = $when;
        }
        // 时间
        if ($data['when'] == 1) $where['bd_canteen_port_dish.breakfast'] = ['eq', 1];
        elseif ($data['when'] == 2) $where['bd_canteen_port_dish.lunch'] = ['eq', 1];
        else $where['bd_canteen_port_dish.dinner'] = ['eq', 1];
        if ($canteen == 0) {
            // 默认餐厅，默认档口，同时返回菜单
            $canteen_data = M('canteen')->where([
                'school_ext' => ['eq', $this->position['ext']],
                'status' => ['eq', 1],
            ])->select();
            if ($canteen_data) {
                foreach ($canteen_data as $key => $value) {
                    $data['data']['position'][$value['id']] = $value;
                    $canteenid[] = $value['id'];
                }
                $canteen_port_data = M('canteen_port')->where([
                    'canteen' => ['in', $canteenid],
                    'status' => ['eq', 1],
                ])->order('`order` DESC')->select();
                if ($canteen_port_data) {
                    foreach ($canteen_port_data as $key => $value) {
                        $data['data']['position'][$value['canteen']]['port'][] = $value;
                        // $data['data']['position'][$value['canteen']]['port'][$value['id']] = $value;
                    }
                    $data['canteen'] = $canteenid[0];
                    $where['bd_canteen_port.canteen'] = ['EQ', $canteenid[0]];
                    $data['canteen_port'] = 0;
                } else {
                    $data['status'] = 2;
                    $data['message'] = '暂时没有档口可供使用';
                }
            } else {
                $data['status'] = 2;
                $data['message'] = '暂时没有餐厅可供使用';
            }
            // p($data);
        } else {
            // 指定餐厅
            $where['bd_canteen_port.canteen'] = ['EQ', $canteen];
            $data['canteen'] = $canteen;
            if ($canteen_port == 0) $data['canteen_port'] = 0;
            else {
                // 指定档口
                $data['canteen_port'] = $canteen_port;
                $where['bd_canteen_port_dish.port'] = ['eq', $canteen_port];
            }
        }
        $where['bd_canteen_port.status'] = ['EQ', 1];
        $where['bd_canteen_port_dish.status'] = ['eq', 1];
        $where['bd_canteen_port.status'] = ['eq', 1];
        // $where['bd_canteen_port_dish.rest'] = ['GT', 0];

        // p($where);
        if ($data['status'] != 2) {
            $data['status'] = 1;
            $data['data']['dish'] = M('canteen_port_dish'
                )->comment('查'
                )->join([
                    'INNER JOIN bd_canteen_port ON bd_canteen_port.id = bd_canteen_port_dish.port',
                ])->where($where)->field([
                    'bd_canteen_port_dish.`id` as dish_id',
                    'bd_canteen_port_dish.image',
                    'bd_canteen_port_dish.name as name',
                    'bd_canteen_port_dish.money_pack',
                    'bd_canteen_port.`id` as port_id',
                    'bd_canteen_port.name as `from`',
                    'bd_canteen_port_dish.`total` as sellNum',
                    'bd_canteen_port_dish.limit_tag',
                    'bd_canteen_port_dish.rest',
                    'bd_canteen_port_dish.content',
                    'bd_canteen_port_dish.money',
                ])->order([
                    'bd_canteen_port_dish.`order` DESC',
                    'bd_canteen_port_dish.id',
            ])->limit($len . ',20')->select();
            $data['len'] = $len + count($data['data']['dish']);
            if (!count($data['data']['dish'])) {
                $data['status'] = 2;
                $data['message'] = '没有更多数据';
            }
        }
        $order_basic = M('order_basic');
        $order_basic_data = $order_basic->comment('切换所有订单状态'
            )->field([
                'bd_order_basic.id',
            ])->where([
                'bd_order_basic.order_date' => ['LT', strtotime(date('Y-m-d'))],
                'bd_order_basic.status' => ['IN', '11,5,12'],
            ])->order([
                'bd_order_basic.order_date DESC',
        ])->limit('0,100')->select();
        if ($order_basic_data) {
            foreach ($order_basic_data as $key => $value) {
                $ff_save[] = $value['id'];
            }
            $order_basic->where([
                'id' => ['IN', $ff_save],
            ])->save(['status' => 7]);
        }

        // p($order_basic_data);
        $this->ajaxReturn($data);
    }

    public function apiMenu(){
        $menu = [
            0 => [
                'name' => '首页',
                'url' => U('mobile/index/index'),
            ],
            1 => [
                'name' => '个人中心',
                'url' => U('mobile/position/index'),
            ],
            2 => [
                'name' => '订单中心',
                'url' => U('mobile/personal/ordered'),
            ],
        ];
        if ($this->temp['role'][1]) {
            $menu[] = [
                'name' => '楼长中心',
                'url' => U('mobile/houser/index'),
            ];
        }
        if ($this->temp['role'][7]) {
            $menu[] = [
                'name' => '分拨中心',
                'url' => U('mobile/deliver/index'),
            ];
        }
        if ($this->temp['role'][8]) {
            $menu[] = [
                'name' => '出单中心',
                'url' => U('mobile/pack/index'),
            ];
        }
        if ($this->temp['role'][11]) {
            $menu[] = [
                'name' => '必点送中心',
                'url' => U('mobile/competitor/index'),
            ];
        }
        $this->ajaxReturn($menu);
    }

    public function apiGetSchoolExt(){
        $this->ajaxReturn((M('school_ext')->where(['id' => ['EQ', $this->position['ext']]])->select())[0]);
    }
}
