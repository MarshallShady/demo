<?php
namespace Mobile\Controller;
use Think\Controller;
class PersonalController extends CommonController {
    // 用于显示个人信息
    public function index(){
        $this->assign('list', $this->list);
        $this->display();
    }
    // /**
    //     订单中心
    // */
    // public function ordered(){
    //     $this->position = SESSION('position');
    //     $this->list['order'] = M('order_basic')->where([['unionid' => ['eq', $this->unionid]]])->order('create_time desc')->limit('0,20')->select();
    //     if ($this->list['order']) {
    //         foreach ($this->list['order'] as $key => $value) {
    //             $temp[] = $value['building'];
    //         }
    //         $building = M('school_building')->where(['id' => ['in', $temp]])->select();
    //         foreach ($building as $key => $value) {
    //             $temp2[] = $value['school_ext'];
    //         }
    //         $school_ext = M('school_ext')->where(['id' => ['in', $temp2]])->select();
    //         $this->list['school_ext'] = $school_ext[0];
    //         foreach ($this->list['order'] as $key => $value) {
    //             foreach ($building as $key2 => $value2) {
    //                 foreach ($school_ext as $key3 => $value3) {
    //                     if ($value['building'] == $value2['id'] && $value2['school_ext'] == $value3['id']) {
    //                         $this->list['order'][$key]['school_ext'] = $value3['school_ext'];
    //                         break;
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     // p($this->list);
    //     $this->assign('list', $this->list);
    //     $this->display();
    // }

    public function apiShowIndex($len = 0){
        $data = [
            'unionid' => $this->unionid,
            'len' => 0,
            'data' => [],
        ];
        $order_basic = M('order_basic');
        $order_basic->comment('订单列表'
            )->join([
                'INNER JOIN bd_canteen ON bd_order_basic.canteen = bd_canteen.id',
                'INNER JOIN bd_school_ext ON bd_canteen.school_ext = bd_school_ext.id',
                'INNER JOIN bd_dict_order_basic_status ON bd_order_basic.status = bd_dict_order_basic_status.id',
                'LEFT JOIN bd_order_complain ON bd_order_complain.`order` = bd_order_basic.id',
                'LEFT JOIN bd_dish_score_log ON bd_dish_score_log.`order` = bd_order_basic.id',
            ])->field([
                'bd_order_basic.id',
                'bd_order_basic.order_img',
                'bd_order_basic.order_name',
                'bd_order_basic.money',
                'bd_order_basic.when',
                'bd_order_basic.order_date',
                'bd_school_ext.breakfast_send_start',
                'bd_school_ext.breakfast_send_end',
                'bd_school_ext.lunch_send_start',
                'bd_school_ext.lunch_send_end',
                'bd_school_ext.dinner_send_start',
                'bd_school_ext.dinner_send_end',
                'bd_order_basic.status',
                'bd_dict_order_basic_status.name as status_name',
                'bd_order_complain.id as complain',
                'bd_dish_score_log.id as score',
            ])->where([
                'bd_order_basic.unionid' => ['EQ', $this->unionid],
            ])->order([
                'bd_order_basic.create_time DESC',
        ]);
        $order_basic_data = $order_basic->group('bd_order_basic.id')->limit($len . ',20')->select();
        $data['len'] = $len + count($order_basic_data);
        $data['data'] = $order_basic_data;
        $this->ajaxReturn($data);
    }

    /**
        获取地理位置
    */
    public function apiPosition(){
        $user_basic = M('user_basic');
        $user_basic_data = $user_basic->comment('下单个人信息'
            )->join([
                'INNER JOIN bd_school_building ON bd_user_basic.building = bd_school_building.id',
                'INNER JOIN bd_school_ext ON bd_school_building.school_ext = bd_school_ext.id',
                'INNER JOIN bd_school ON bd_school_ext.school = bd_school.id',

            ])->field([
                'bd_school.name as school_name',
                'bd_school_ext.name as ext_name',
                'bd_school_building.name as building_name',
                'bd_user_basic.dormitory',
                'bd_user_basic.mobile',
            ])->where([
                'bd_user_basic.unionid' => ['EQ', $this->unionid],
        ])->select();
        $this->ajaxReturn($user_basic_data);
    }
    /**
        订单详情
    */
    public function detail($id){
        // $this->list['order'] = M('order_basic')->getById($id);
        // $this->list['order']['dish'] = M('order_dish')->where(['order' => ['eq', $id]])->select();
        // $this->list['building'] = M('school_building')->getById($this->list['order']['building']);
        // $this->list['school_ext'] = M('school_ext')->getById($this->list['building']['school_ext']);
        // if ($this->list['order']['when'] == 1) {
        //     $this->list['order']['when'] = '早餐';
        //     $this->list['order']['when_time'] = date('H:i', $this->list['school_ext']['breakfast_send_start']) . ' ~ ' . date('H:i', $this->list['school_ext']['breakfast_send_end']);
        // } elseif ($this->list['order']['when'] == 2) {
        //     $this->list['order']['when'] = '午餐';
        //     $this->list['order']['when_time'] = date('H:i', $this->list['school_ext']['lunch_send_start']) . ' ~ ' . date('H:i', $this->list['school_ext']['lunch_send_end']);
        // } elseif ($this->list['order']['when'] == 3) {
        //     $this->list['order']['when'] = '晚餐';
        //     $this->list['order']['when_time'] = date('H:i', $this->list['school_ext']['dinner_send_start']) . ' ~ ' . date('H:i', $this->list['school_ext']['dinner_send_end']);
        // }
        // p($this->list);
        $this->assign('list', $this->list);
        $this->display();
    }

    public function apiShowDetail($id) {
        $data = [
            'unionid' => $this->unionid,
            'order' => [],
            'dish' => [],
            'bonus' => 0,
        ];
        $order_basic = M('order_basic');
        $user_bonus = M('user_bonus');
        $order_basic_data = ($order_basic->comment('订单详情订单信息'
            )->join([
                'INNER JOIN bd_school_building ON bd_school_building.id = bd_order_basic.building',
                'INNER JOIN bd_school_ext ON bd_school_ext.id = bd_school_building.school_ext',

                'INNER JOIN bd_school ON bd_school.id = bd_school_ext.school',
                'INNER JOIN bd_user_basic ON bd_user_basic.unionid = bd_order_basic.unionid',
                'INNER JOIN bd_dict_order_basic_status ON bd_order_basic.status = bd_dict_order_basic_status.id',
            ])->field([
                'bd_order_basic.id',
                'bd_school.name as school_name',
                'bd_school_ext.name as ext_name',
                'bd_school_building.name as building_name',
                'bd_order_basic.name',
                'bd_order_basic.mobile',
                'bd_order_basic.dormitory',
                'bd_order_basic.order_date',
                'bd_order_basic.money',
                'bd_order_basic.money_delivery',

                'bd_order_basic.when',
                'bd_order_basic.word',
                'bd_school_ext.breakfast_send_start',
                'bd_school_ext.breakfast_send_end',
                'bd_school_ext.lunch_send_start',
                'bd_school_ext.lunch_send_end',
                'bd_school_ext.dinner_send_start',
                'bd_school_ext.dinner_send_end',
                'bd_order_basic.status',
                'bd_dict_order_basic_status.name as status_name',
            ])->where([
                'bd_order_basic.id' => ['EQ', $id],
                'bd_order_basic.unionid' => ['EQ', $this->unionid],
        ])->select())[0];
        // p($order_basic_data);
        if ($order_basic_data) {
            $order_basic_data['money'] /= 100;
            $order_basic_data['money_delivery'] /= 100;
            $order_basic_data['order_date'] = date('Y-m-d', $order_basic_data['order_date']);
            $data['order'] = $order_basic_data;
            // 红包
            $data['bonus'] = ($user_bonus->where([
                'bd_user_bonus.`order`' => ['EQ', $order_basic_data['id']],
            ])->select())[0]['money'] / 100;
            $order_dish = M('order_dish'
                )->comment('订单详情查菜品'
                )->where([
                    'bd_order_dish.`order`' => ['EQ', $id],
            ])->select();
            foreach ($order_dish as $key => $value) {
                $value['money'] /= 100;
                $value['money_pack'] /= 100;
                $data['dish'][] = $value;
            }
            // p($data);
            $this->ajaxReturn($data);
        } else {
            $this->ajaxReturn($data);
        }
    }


    public function complain($id){
        if (!$id) {
            // 异常检测
            $return = [
                'status' => 2,
                'url' => U('mobile/personal/index'),
                'message' => '网络异常',
            ];
        } else {
            $order_complain = M('order_complain');
            $order_basic = M('order_basic');
            if (!IS_POST) {
                if ($order_complain->where(['bd_order_complain.`order`' => ['EQ', $id]])->select()) {
                    $this->alert('您已投诉过该订单', U('mobile/personal/index'));
                }
                $this->assign('list', $this->list);
                $this->display();
            } else {
                $return = [
                    'status' => 0,
                    'message' => '',
                    'url' => U('mobile/personal/index'),
                ];
                $data = json_decode(file_get_contents('php://input'), true);
                $order_complain->startTrans();
                if (!count($order_basic->where([
                        'id' => ['EQ', $id],
                        'unionid' => ['EQ', $this->unionid],
                    ])->select())) {
                    $return['status'] = 2;
                    $return['url'] = U('mobile/personal/index');
                    $return['message'] = '错误';
                    $this->ajaxReturn($return);
                }
                foreach ($data['dish'] as $key => $value) {
                    if ($value['word'] || $value['reason'] != 1) {
                        $add = [
                            'order' => $id,
                            'dish' => $value['id'],
                            'choice' => $value['reason'],
                            'word' => $value['word'],
                            'status' => 2,
                        ];
                        if ($order_complain->add($add) == false) {
                            $return['stauts'] = 2;
                            $return['url'] = U('mobile/personal/index');
                            $return['message'] = '网络异常，请重新尝试';
                            $order_complain->rollback();
                            $this->ajaxReturn($return);
                        }
                    }
                }
                $return['stauts'] = 1;
                $return['url'] = U('mobile/personal/index');
                $return['message'] = '提交成功';
                $order_complain->commit();
                $this->ajaxReturn($return);
            }
        }
    }

    public function score($id){
        if (!$id) die;//异常检测
        else {
            $dish_score_log = M('dish_score_log');
            $order_dish = M('order_dish');
            if (!IS_POST) {
                if ($dish_score_log->where(['bd_dish_score_log.`order`' => ['EQ', $id]])->select()) {
                    $this->alert('您已评价过该订单', U('mobile/personal/index'));
                }
                $this->assign('list', $this->list);
                $this->display();
            } else {
                $return = [
                    'status' => 2,
                    'url' => U('mobile/personal/index'),
                    'message' => '网络异常',
                ];
                $return = [
                    'status' => 1,
                    'message' => '评价成功！',
                    'url' => U('mobile/personal/index'),
                ];
                $data = json_decode(file_get_contents('php://input'), true);
                if ($data['dish']) {
                    foreach ($data['dish'] as $key => $value) {
                        $order_dish_id[] = $value['id'];
                    }
                    $order_dish_count = $order_dish->comment(''
                        )->join([
                            'bd_order_basic ON bd_order_dish.`order` = bd_order_basic.id',
                        ])->field([
                            'bd_order_dish.id',
                        ])->where([
                            'bd_order_basic.unionid' => ['EQ', $this->unionid],
                            'bd_order_basic.id' => ['EQ', $id],
                            'bd_order_dish.id' => ['IN', $order_dish_id],
                            'bd_order_dish.status' => ['IN', '1,2,3'],
                            'bd_order_basic.status' => ['IN', '6,7,13'],
                    ])->select();
                    if (count($order_dish_count) == count($data['dish'])) {
                        // 评价
                        foreach ($data['dish'] as $key => $value) {
                            $add = [
                                'word' => $data['word'],
                                'order' => $id,
                                'dish' => $value['id'],
                                'score' => $value['score'],
                            ];
                            if ($dish_score_log->add($add) == false) {
                                $return['status'] = 2;
                                $return['url'] = U('mobile/personal/index');
                                $return['message'] = '网络异常，请重试';
                                $this->ajaxReturn($return);
                            }
                        }
                    } else {
                        $return['status'] = 2;
                        $return['url'] = U('mobile/personal/index');
                        $return['message'] = '数据异常，请重试';
                        $this->ajaxReturn($return);
                    }
                    //
                    $return['status'] = 1;
                    $return['url'] = U('mobile/personal/index');
                    $return['message'] = '评价成功';
                    $this->ajaxReturn($return);
                } else {
                    $return['status'] = 2;
                    $return['url'] = U('mobile/personal/index');
                    $return['message'] = '错误';
                    $this->ajaxReturn($return);
                }
            }
        }
    }
}
