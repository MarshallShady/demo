<?php
namespace Mobile\Controller;
use Think\Controller;
class MediaController extends CommonController {
    /**
        媒体
    */
    protected function _init(){
        if (!$this->temp['role'][13]) $this->alert('您不是媒体来源', U('mobile/index/index'));
        $this->assign('list', $this->list);
        // $this->getAim();
        $this->checkTime();
        // $this->common['aim'][0] = 2;
    }
    public function index(){
        $this->display();
    }

    public function apiShowIndex($len = 0){
        $data = [
            'data' => [],
        ];
        $order_basic = M('order_basic');
        $order_basic->comment('媒体查询'
            )->join([
                // 'INNER JOIN bd_user_role_ext ON bd_user_role_ext.aim = bd_order_basic.state',
                // 'INNER JOIN bd_user_role ON bd_user_role.id = bd_user_role_ext.user_role',
            ])->where([
                'bd_order_basic.state' => ['IN', $this->common['aim']],
                'bd_order_basic.status' => ['IN', '4,5,6,7,11,12,13'],
        ]);
        if ($len == 0) {
            $data = [
                'len' => 0,
                'order' => 0,
                'money' => 0,
                'money_total' => 0,
                'data' => [],
            ];
            $data['money_total'] = (clone $order_basic)->field([
                'sum(money) as money',
                ])->group('bd_order_basic.state')->select()[0]['money'];
            $data = array_merge($data, (clone $order_basic)->group('bd_order_basic.state')->field([
                    'sum(money) as money',
                    'count(*) as `order`',
                ])->where([
                    'bd_order_basic.order_date' => ['EQ', strtotime(date('Y-m-d'))],
            ])->select()[0]);
        }
        $data['data'] = (clone $order_basic)->field([
                'sum(bd_order_basic.money) as money',
                'count(*) as num',
                'bd_order_basic.order_date',
            ])->group('bd_order_basic.order_date')->order([
                'bd_order_basic.`order_date` DESC'
        ])->limit($len . ',10')->select();
        $data['len'] = $len + count($data['data']);
        $this->ajaxReturn($data);
    }



    /**
        操作目标
    */
    private function getAim() {
        $data = M('user_role_ext')->comment('操作范围'
            )->join([
                'INNER JOIN bd_canteen_port ON bd_canteen_port.id = bd_user_role_ext.aim',
                // 'INNER JOIN bd_canteen ON bd_canteen.id = bd_user_role_ext.aim',
                // 'INNER JOIN bd_school_ext ON bd_school_ext.id = bd_canteen.school_ext',
            ])->field([
                'bd_canteen.id',
                // 'bd_school_ext.breakfast_pack_start',
                // 'bd_school_ext.breakfast_order_end',
                // 'bd_school_ext.lunch_pack_start',
                // 'bd_school_ext.lunch_order_end',
                // 'bd_school_ext.dinner_pack_start',
                // 'bd_school_ext.dinner_order_end',
            ])->where([
                'bd_user_role_ext.user_role' => ['EQ', $this->temp['role'][13]],
                'bd_user_role_ext.status' => ['EQ', 1],
        ])->select();
        if (!$data) {
            $this->alert('您不是出单员或未被安排工作！', U('mobile/index/index'));
        } else {
            foreach ($data as $key => $value) {
                $this->common['aim'][] = $value['id'];
                // $this->common['time']['breakfast_pack_start'] = $value['breakfast_pack_start'];
                // $this->common['time']['breakfast_order_end'] = $value['breakfast_order_end'];
                // $this->common['time']['lunch_pack_start'] = $value['lunch_pack_start'];
                // $this->common['time']['lunch_order_end'] = $value['lunch_order_end'];
                // $this->common['time']['dinner_pack_start'] = $value['dinner_pack_start'];
                // $this->common['time']['dinner_order_end'] = $value['dinner_order_end'];
            }
        }
    }

    protected function checkTime(){
        // 当前时间
        $now = time();
        $date = strtotime(date('Y-m-d'));
        // 派送截止前出单截止，
        // if ($this->common['time']['breakfast_order_end'] && $now <= $date + $this->common['time']['breakfast_order_end'] + 7200) {
        //     $when = 1;
        //     $when_name = '早餐时段';
        //     $pay_time = $this->common['time']['breakfast_pack_start'];
        // } elseif ($this->common['time']['lunch_order_end'] && $now <= $date + $this->common['time']['lunch_order_end'] + 7200 && $now >= $date + $this->common['time']['lunch_pack_start'] - 7200) {
        //     $when = 2;
        //     $when_name = '午餐时段';
        //     $pay_time = $this->common['time']['lunch_pack_start'];
        // } elseif ($this->common['time']['dinner_order_end'] && $now <= $date + $this->common['time']['dinner_order_end'] + 7200 && $now >= $date + $this->common['time']['dinner_pack_start'] - 7200) {
        //     $when = 3;
        //     $when_name = '晚餐时段';
        //     $pay_time = $this->common['time']['dinner_pack_start'];
        // }
        $this->common['time']['now'] = $now;
        $this->common['time']['date'] = $date;
        $this->common['time']['when'] = $when;
        $this->common['time']['when_name'] = $when_name;
        $this->common['time']['pay_time'] = $date + $pay_time;
    }
}
