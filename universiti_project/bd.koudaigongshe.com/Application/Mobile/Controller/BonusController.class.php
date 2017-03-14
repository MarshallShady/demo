<?php
namespace Mobile\Controller;
use Think\Controller;
class BonusController extends CommonController {
    /**
        红包首页
    */
    public function index(){
        $this->assign('list', $this->list);
        $this->display();
    }
    public function list($status = 0){
        $this->time = time();
        // 查询条件
        $where = [
            'unionid' => ['eq', $this->unionid],
        ];
        if ($status != 0) $where['status'] = ['eq', $status];
        // 查询
        $user_bonus = M('user_bonus');
        $user_bonus_data = $user_bonus->comment('查询红包'
            )->field([
                'id',
                'money',
                'money_least',
                'create_time',
                'end_time',
                'status',
        ])->where($where)->order('money DESC')->select();
        // 检查超时红包
        foreach ($user_bonus_data as $key => $value) {
            if ($value['status'] == 1 && $value['end_time'] < $this->time) {
                $user_bonus_data[$key]['status'] = 3;
                M('user_bonus')->save($user_bonus_data[$key]);
            }
            $user_bonus_data[$key]['end_time'] = date('Y-m-d', $value['end_time']);
        }
        foreach ($user_bonus_data as $key => $value) {
            if ($value['status'] == 1) $out[] = $value;
        }
        foreach ($user_bonus_data as $key => $value) {
            if ($value['status'] != 1) $out[] = $value;
        }
        echo json_encode($out);
    }
}
