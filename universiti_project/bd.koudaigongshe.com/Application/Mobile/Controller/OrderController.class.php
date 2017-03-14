<?php
namespace Mobile\Controller;
use Think\Controller;
class OrderController extends CommonController {
    /**
        确认订单
    */
    public function confirm(){
        if(!IS_POST) die;//不是post截断
        // 用户信息
        $this->list['user'] = M('user_basic')->getByUnionid($this->unionid);
        $this->checkTime();
        $this->list['lunch_order_end'] = $this->data['school_ext']['lunch_order_end'];
        $this->list['dinner_order_end'] = $this->data['school_ext']['dinner_order_end'];
        // 获取订单数据
        $data = I('');
        if (!$data['data']) $this->alert('您没有选择菜品', U('mobile/index/index'));

        $this->list['data'] = $data['data'];
        $data = json_decode(html_entity_decode($data['data']), true);
        if ($data['type'] > 3 || $data['type'] < 0) die;
        else {
            if ($data['type'] == 1) $this->list['show_deliver_time'] = date('H:i', $this->data['school_ext']['breakfast_send_start']) . ' ~ ' . date('H:i', $this->data['school_ext']['breakfast_send_end']);
            if ($data['type'] == 2) $this->list['show_deliver_time'] = date('H:i', $this->data['school_ext']['lunch_send_start']) . ' ~ ' . date('H:i', $this->data['school_ext']['lunch_send_end']);
            if ($data['type'] == 3) $this->list['show_deliver_time'] = date('H:i', $this->data['school_ext']['dinner_send_start']) . ' ~ ' . date('H:i', $this->data['school_ext']['dinner_send_end']);
        }
        // p($data)
        $this->list['type'] = $data['type'];
        // 查询菜品
        foreach ($data['data'] as $key => $value) $where[] = $value['id'];
        $this->list['dish'] = M('canteen_port_dish')->where(['id' => ['in', $where]])->select();
        $this->list['order']['order_img'] = $this->list['dish'][0]['image'];

        foreach ($this->list['dish'] as $dish_key => $dish_value) {
            foreach ($data['data'] as $data_key => $data_value) {
                if ($dish_value['id'] == $data_value['id']) {
                    $this->list['dish'][$dish_key]['num'] = $data_value['amount'];
                    $this->list['money_total'] += ($this->list['dish'][$dish_key]['money'] + $this->list['dish'][$dish_key]['money_pack']) * $data_value['amount'];
                    break;
                }
            }
            $name .= $dish_value['name'];
        }
        if(mb_strlen($name) > 10){
            $this->list['order']['order_name'] = mb_substr($name, 0, 7);
            $this->list['order']['order_name'] .= '...';
        } else $this->list['order']['order_name'] = $name;
        // 地址
        $this->list['user'] = M('user_basic')->getByUnionid($this->unionid);
        $school_building = M('school_building')->getById($this->list['user']['building']);
        $school_ext = M('school_ext')->getById($school_building['school_ext']);
        $school = M('school')->getById($school_ext['school']);
        $this->list['user']['school_building'] = $school_building['name'];
        $this->list['user']['school_ext'] = $school_ext['name'];
        $this->list['user']['school'] = $school['name'];
        $this->list['money_delivery'] = $school_ext['money_delivery'];
        $this->list['money_total'] += $school_ext['money_delivery'];
        // p($this->list);
        $this->assign('list', $this->list);
        $this->display();
    }
    /**
        支付页面
    */
    public function pay(){
        if(!IS_POST) die;
        // 当前时间
        $time = time();
        $date = strtotime(date('Y-m-d'));
        $this->checkTime();
        // 菜品数据
        $get_data = I('');
        if (!$get_data['data']) return;
        $get_data_ot = $get_data;
        $get_data = json_decode(html_entity_decode($get_data['data']), true);
        // 时间限制
        if ($get_data['type'] > 3 || $get_data['type'] < 0) die;
        else {
            if ($get_data['type'] == 1) {
                if ($get_data_ot['order_date'] + $this->data['school_ext']['breakfast_order_end'] < $time) {
                    $this->alert('您不能下此时段的订单！', U('mobile/index/index'));
                }
            }
            if ($get_data['type'] == 2) {
                if ($get_data_ot['order_date'] + $this->data['school_ext']['lunch_order_end'] < $time) {
                    $this->alert('您不能下此时段的订单！', U('mobile/index/index'));
                }
            }
            if ($get_data['type'] == 3) {
                if ($get_data_ot['order_date'] + $this->data['school_ext']['dinner_order_end'] < $time) {
                    $this->alert('您不能下此时段的订单！', U('mobile/index/index'));
                }
            }
        }
        // 留言长度
        if (mb_strlen(I('word')) > 20) $this->alert('留言不能超过20个字符！', U('mobile/index/index'));
        // 生成订单数据
        $order_dish = M('order_dish');
        $order = M('order_basic');
        $order->startTrans();
        // 宿舍楼判断
        $cando = M('school_building')->getById($this->position['building']);
        if ($cando['status'] == 2) {
            if ($cando['on_status_two']) {
                $this->alert($cando['on_status_two'], U('mobile/index/index'));
            } else {
                $this->alert('您选的楼栋' . $cando['name'] . '暂时没有开放', U('mobile/index/index'));
            }
        }
        elseif ($cando['status'] != 1) $this->alert('您选的楼栋最近被编辑了，请去个人中心重新保存', U('mobile/position/index'));
        // 红包
        $bonus_money = 0;
        $bonus_money_least = 0;
        $bonus_id = I('bonus');
        if (is_numeric(I('bonus')) && I('bonus') != 0) {
            // 使用红包时
            $user_bonus_data = M('user_bonus')->where([
                'id' => ['eq', $bonus_id],
                'unionid' => ['eq', $this->unionid],
                'status' => ['eq', 1],
                'end_time' => ['gt', $time],
            ])->select();
            if (!$user_bonus_data) {
                $order->rollback();
                $this->alert('您的红包无法使用', U('mobile/index/index'));
            }
            $bonus_money = $user_bonus_data[0]['money'];
            $bonus_money_least = $user_bonus_data[0]['money_least'];
        }
        // 找出菜品
        $dish_limit = M('dish_limit');
        foreach ($get_data['data'] as $key => $value) $where[] = $value['id'];
        $dish = M('canteen_port_dish')->where(['id' => ['in', $where]])->select();
        foreach ($dish as $key => $value) {
            $name .= $value['name'];
            foreach ($get_data['data'] as $key2 => $value2) {
                if ($value['id'] == $value2['id']) {
                    $dish[$key]['dish_num'] = $value2['amount'];
                    // 限量
                    // if ($dish[$key]['limit_tag'] == 1) {
                    //     $dish_limit_data = $dish_limit->where([
                    //         'unionid' => ['EQ', $this->unionid],
                    //         'date' => ['EQ', $date],
                    //     ])->count();
                    //     if ($dish[$key]['dish_num'] > 1 || $dish_limit_data) {
                    //         $order->rollback();
                    //         $this->alert('物品超限或网络原因导致无法购买！', U('mobile/index/index'));
                    //     } else {
                    //         $dish_limit_add = [
                    //             'unionid' => $this->unionid,
                    //             'date' => $date,
                    //         ];
                    //         if ($dish_limit->add($dish_limit_add) == false) {
                    //             $order->rollback();
                    //             $this->alert('购买失败请重新尝试！', U('mobile/index/index'));
                    //         }
                    //     }
                    // }
                    $money += ($dish[$key]['money'] + $dish[$key]['money_pack']) * $value2['amount'];
                    break;
                }
            }
        }
        // 用户数据
        $user = M('user_basic')->getByUnionid($this->unionid);

        $data['unionid'] = $user['unionid'];
        if (mb_strlen($name) > 10){
            $data['order_name'] = mb_substr($name, 0, 7);
            $data['order_name'] .= '...';
        } else $data['order_name'] = $name;
        $data['order_img'] = $dish[0]['image'];
        $data['name'] = $user['name'];
        $data['mobile'] = $user['mobile'];
        $data['building'] = $user['building'];
        $data['dormitory'] = $user['dormitory'];
        $data['when'] = $get_data['type'];
        $data['word'] = I('word');
        // 总金额
        $data['money'] = $money + $this->data['school_ext']['money_delivery'];
        // 红包
        if ($bonus_money != null && $bonus_money_least != null) {
            if ($data['money'] > $bonus_money_least) {
                $data['money'] -= $bonus_money;
            } else $this->alert('您的金额无法使用这个红包', U('mobile/index/index'));
            if ($data['money'] <= 0) {
                $data['money'] = 1;
            }
        }
        $data['money_delivery'] = $this->data['school_ext']['money_delivery'];
        $data['origin'] = html_entity_decode($get_data_ot['data']);
        $data['create_time'] = $time;
        $data['edit_time'] = $time;
        $data['status'] = 1;
        $data['order_date'] = $get_data_ot['order_date'];
        $data['state'] = SESSION('state');
        $temp_port_data = M('canteen_port')->getById($dish[0]['port']);
        $data['canteen'] = $temp_port_data['canteen'];
        // 出单时间
        $id = $order->add($data);
        if (!$id) {
            $order->rollback();
            $this->alert('下单失败！请重新尝试', U('mobile/index/index'));
        }
        // 红包
        if ($bonus_money != null && $bonus_money_least != null) {
            $user_bonus_data[0]['status'] = 2;
            $user_bonus_data[0]['order'] = $id;
            if (M('user_bonus')->save($user_bonus_data[0]) === false) {
                $order->rollback();
                $this->alert('网络异常', U('mobile/index/index'));
            }
        }
        foreach ($dish as $key => $value) {
            if (!$value) {
                $order->rollback();
                $this->alert('网络异常，请重新尝试', U('mobile/index/index'));
            }
            if (!$value['rest']) {
                $order->rollback();
                $this->alert('此菜品已抢购完毕', U('mobile/index/index'));
            }
            $dish[$key]['dish'] = $dish[$key]['id'];
            unset($dish[$key]['id']);
            $dish[$key]['status'] = 1;
            $dish[$key]['order'] = $id;
            $dish[$key]['order_date'] = $get_data_ot['order_date'];
            $dish[$key]['when'] = $get_data['type'];
            $dish[$key]['create_time'] = $time;
            $port_dish_save = [
                'id' => ['EQ', $value['id']],
                'total' => ['exp', 'total + ' . $value['dish_num']],
                'rest' => ['exp', 'rest - ' . $value['dish_num']],
            ];
            if (M('canteen_port_dish')->save($port_dish_save) === false){
                $order->rollback();
                $this->alert('错误', U('mobile/index/index'));
            }
        }
        // p($dish);
        foreach ($dish as $key => $value) {
            while ($value['dish_num'] > 0) {
                $dish_add_id = $order_dish->add($value);
                $checkBug = count($order_dish->where([
                    'bd_order_dish.id' => ['EQ', $dish_add_id],
                    'bd_order_dish.`order`' => ['EQ', $id],
                ])->select());
                if ($checkBug == 0) {
                    $order->rollback();
                    $this->alert('下单失败！请重新尝试', U('mobile/index/index'));
                    die;
                }
                $value['dish_num'] -= 1;
            }
        }
        $order->commit();
        echo '<script>alert("下单成功！请支付订单");location.href="http://www.koudaigongshe.com/wxpay/index.php?id=' . $id . '&token=' . md5($this->unionid . $id) . '";</script>';

    }

    /**
        取消订单实现
    */
    public function cancelHandle($id){
        $order = M('order_basic');
        $order->startTrans();
        $now = $order->comment('取消订单'
            )->join([
                'INNER JOIN bd_order_dish ON bd_order_basic.id = bd_order_dish.`order`',
            ])->field([
                'bd_order_basic.id',
                'bd_order_basic.status',
                'bd_order_dish.dish as dish_id',
            ])->where([
                'bd_order_basic.id' => ['eq', $id],
                'bd_order_basic.unionid' => ['eq', $this->unionid],
                'bd_order_basic.status' => ['EQ', 1],
        ])->select();
        if (!$now) $this->alert('您的订单不满足取消条件', U('mobile/personal/ordered'));
        $order_save = [
            'id' => $id,
            'status' => 3,
        ];
        if ($now[0]['status'] == 1 && false !== $order->save($order_save)) {
            foreach ($now as $key => $value) {
                $dish_save = [
                    'id' => $value['dish_id'],
                    'rest' => ['exp', 'rest - 1'],
                    'total' => ['exp', 'total - 1'],
                ];
                if (M('canteen_port_dish')->save($dish_save) === false) {
                    $order->rollback();
                    $this->alert('网络异常', U('mobile/personal/ordered'));
                }
            }
            $order->commit();
            echo '<script>alert("取消成功！");location.href="' . U('mobile/personal/ordered') . '";</script>';
        } else {
            $order->rollback();
            echo '<script>alert("取消失败！请重新尝试");location.href="' . U('mobile/personal/ordered') . '";</script>';
        }
        return ;
    }
    /**
        申请取消订单
    */
    public function need_cancelHandle($id){

    }
}
