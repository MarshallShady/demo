<?php
namespace Mobile\Controller;
use Think\Controller;
class RoleController extends CommonController {
    public function housemaster(){
        if (!$this->temp['role'][1]) return;
        else {
            // p($this->temp);
            $timestamp_today = strtotime(date('Y-m-d'));
            $role_ext = M('user_role_ext')->where([
                'user_role' => ['in', $this->temp['role'][1]],
                'status' => ['eq', 1],
            ])->select();
            if (!$role_ext) $this->alert('您已成为楼长但还未被安排任务！', U('mobile/index/index'));

            foreach ($role_ext as $key => $value) $buildingid[] = $value['aim'];
            // 楼
            $building = M('school_building')->where(['id' => ['in', $buildingid]])->select();
            // p($building);
            foreach ($building as $key => $value) {
                $this->list['building'][$value['id']] = $value;
            }
            // 取出所有订单

            if (time() < strtotime(date('Y-m-d') . ' 16:00:00')) $when = 2;
            elseif(time() < strtotime(date('Y-m-d') . ' 21:00:00')) $when = 3;

            // p($buildingid);
            foreach ($this->list['role'] as $key => $value) {
                $this->list['money_total'] += $value['money_total'];
                $this->list['money_rest'] += $value['money_rest'];
                $this->list['num_dish_rest'] += $value['num_dish_rest'];
                $this->list['num_dish_total'] += $value['num_dish_total'];
                $this->list['num_order_rest'] += $value['num_order_rest'];
                $this->list['num_order_total'] += $value['num_order_total'];
            }
            $order = M('order_basic')->where([
                'building' => ['in', $buildingid],
                'when' => ['eq', $when],
                'order_date' => ['eq', $timestamp_today],
                'status' => ['in', '4,11,5,6,7'],
            ])->order('building,dormitory')->select();
            if ($order){
                foreach ($order as $key => $value) $orderid[] = $value['id'];
                $dish = M('order_dish')->where(['order' => ['in', $orderid]])->select();
                foreach ($order as $key => $value) {
                    foreach ($dish as $key2 => $value2) {
                        if ($value2['order'] == $value['id']) {
                            $order[$key]['dish'][] = $value2;
                            $order[$key]['dish_num'] += 1;
                            $portid[] = $value2['port'];
                        }
                    }
                }
                $port = M('canteen_port')->where(['id' => ['in', $portid]])->select();
                foreach ($port as $key => $value) $this->list['port'][$value['id']] = $value;
                foreach ($order as $key => $value) {
                    if ($value['status'] == 4 || $value['status'] == 11) {
                        $this->list['order']['prepare'][] = $value;
                    } elseif ($value['status'] == 5) {
                        $this->list['order']['ready'][] = $value;
                    } elseif ($value['status'] == 7 || $value['status'] == 6){
                        $this->list['order']['finished'][] = $value;
                    }
                }
            }
        }
        // p($this->list);
        $this->assign('list', $this->list);
        $this->display();
    }

    public function pack(){
        $timestamp_today = strtotime(date('Y-m-d'));
        $role = M('user_role_ext')->where([
            'user_role' => ['eq', $this->temp['role'][8],
            'status' => ['eq', 1],
        ]])->select();
        foreach ($role as $key => $value) $aimid[] = $value['aim'];
        if (!$aimid) $this->alert('您已成为出单员但还没有被分配餐厅！', U('mobile/index/index'));
        $aim = M('canteen_port')->where(['canteen' => ['in', $aimid]])->select();
        foreach ($aim as $key => $value) $candoportid[] = $value['id'];
        if (time() < strtotime(date('Y-m-d') . ' 13:00:00')) {
            $when = 2;
            $when_name = '午餐时段';
        } elseif(time() >= strtotime(date('Y-m-d') . ' 13:00:00')) {
            $when = 3;
            $when_name = '晚餐时段';
        }

        $dish = M('order_dish')->where([
            'order_date' => ['eq', $timestamp_today],
            'when' => ['eq', $when],
            'port' => ['in', $candoportid],
            'status' => ['in', '1,2,3']
        ])->field('id,order')->select();
        // p($dish);
        foreach ($dish as $key => $value) $orderid_temp[] = $value['order'];
        if ($orderid_temp) {
            $order = M('order_basic')->where([
                'id' => ['in', $orderid_temp],
                'when' => ['eq', $when],
                'status' => ['in', '4,11,5,6,7'],
                'order_date' => $timestamp_today,
            ])->field('id')->select();
            foreach ($order as $key => $value) $orderid[] = $value['id'];
            if ($orderid) {
                // 全部订单菜品
                // p($orderid);
                $this->list['total_num'] = M('order_dish')->where([
                    'order_date' => ['eq', $timestamp_today],
                    'when' => ['eq', $when],
                    'port' => ['in', $candoportid],
                    'status' => ['in', '1,2,3'],
                    'order' => ['in', $orderid]
                ])->count();
                // p($this->list['total_num']);
                // 未出单菜品
                $this->list['dish_num_unpack'] = M('order_dish')->where([
                    'order_date' => ['eq', $timestamp_today],
                    'when' => ['eq', $when],
                    'port' => ['in', $candoportid],
                    'status' => ['in', '1'],
                    'order' => ['in', $orderid]
                ])->count();
                // 未出单订单
                $this->list['order_num'] = count($order);;
                // 已出订单菜品
                $this->list['dish_num_packed'] = M('order_dish')->where([
                    'order_date' => ['eq', $timestamp_today],
                    'when' => ['eq', $when],
                    'port' => ['in', $candoportid],
                    'status' => ['in', '2,3'],
                    'order' => ['in', $orderid],
                ])->count();
                // 已出底单菜品
                $this->list['dish_num_end'] = M('order_dish')->where([
                    'order_date' => ['eq', $timestamp_today],
                    'when' => ['eq', $when],
                    'port' => ['in', $candoportid],
                    'status' => ['eq', 3],
                    'order' => ['in', $orderid],
                ])->count();
            }
        }

        $this->assign('list', $this->list);
        $this->display();
    }

    public function admin_apply(){
        $this->list['province'] = M('dict_province')->select();
        $this->list['school'] = M('school')->order('province asc')->select();
        $this->list['school_ext'] = M('school_ext')->order('school asc')->select();
        $this->list['school_building'] = M('school_building')->order('school_ext asc')->select();

        foreach ($this->list['school_ext'] as $key => $value) {
            $this->list['count'][$value['school']] += 1;
        }
        $this->list['user'] = M('user_basic')->getByUnionid($this->unionid);
        foreach ($this->list['school_building'] as $key => $value) {
            if ($value['id'] == $this->list['user']['building']) {
                $temp = $this->list['school_building'][0];
                $this->list['school_building'][0] = $this->list['school_building'][$key];
                $this->list['school_building'][$key] = $temp;
                $temp2 = $value['school_ext'];
                break;
            }
        }
        foreach ($this->list['school_ext'] as $key => $value) {
            if ($value['id'] == $temp2) {
                $temp = $this->list['school_ext'][0];
                $this->list['school_ext'][0] = $this->list['school_ext'][$key];
                $this->list['school_ext'][$key] = $temp;
                $temp2 = $value['school'];
                break;
            }
        }
        foreach ($this->list['school'] as $key => $value) {
            if ($value['id'] == $temp2) {
                $temp = $this->list['school'][0];
                $this->list['school'][0] = $this->list['school'][$key];
                $this->list['school'][$key] = $temp;
                $temp2 = $value['province'];
                break;
            }
        }
        // p($this->list);
        foreach ($this->list['province'] as $key => $value) {
            if ($value['id'] == $temp2) {
                $temp = $this->list['province'][0];
                $this->list['province'][0] = $this->list['province'][$key];
                $this->list['province'][$key] = $temp;
                break;
            }
        }
        // p($this->list);
        $this->assign('list', $this->list);
        $this->display();
    }


    public function admin_applyHandle(){
        if(!IS_POST) die;
        // p(I('post.'));
        $data = I('post.');
        $data['unionid'] = $this->unionid;
        $data['status'] = 2;
        $User_Basic = D('AdminApply');
        if (false == $User_Basic->create($data)) {
            echo '<script>alert("' . $User_Basic->getError() . '");location.href="' . U('mobile/role/admin_apply') . '";</script>';
            return ;
        }
        if ($User_Basic->add() != false) {
            echo '<script>alert("提交成功");location.href="' . U('mobile/index/index') . '";</script>';
        } else {
            echo '<script>alert("提交失败");location.href="' . U('mobile/position/apply') . '";</script>';
        }
    }
}
