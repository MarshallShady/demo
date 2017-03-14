<?php
namespace Mobile\Controller;
use Think\Controller;
class PositionController extends CommonController {
    // 用于显示地理位置设置

    public function _initialize(){
        // 权限检测
        $this->checkUnionid();
        $this->checkRole();
    }

    public function index(){
        $this->list['province'] = M('dict_province')->select();
        $this->list['school'] = M('school')->join([
                'bd_school_ext ON bd_school_ext.school = bd_school.id',
                'INNER JOIN bd_school_building ON bd_school_building.school_ext = bd_school_ext.id',
            ])->field([
                'bd_school.name',
                'bd_school.id',
                'bd_school.province',
            ])->where([
                'bd_school_building.status' => ['IN', '1,2'],
                'bd_school_ext.status' => ['IN', '1,2'],
                'bd_school.status' => ['IN', '1,2'],
            ])->order('bd_school.province asc')->group('bd_school.id')->select();
        $this->list['school_ext'] = M('school_ext')->join([
                'bd_school ON bd_school.id = bd_school_ext.school',

                'INNER JOIN bd_school_building ON bd_school_building.school_ext = bd_school_ext.id',
            ])->field([
                'bd_school_ext.id',
                'bd_school_ext.name',
                'bd_school_ext.school',
            ])->where([
                'bd_school_building.status' => ['IN', '1,2'],
                'bd_school_ext.status' => ['IN', '1,2'],
                'bd_school.status' => ['IN', '1,2'],
        ])->order('bd_school_ext.school asc')->group('bd_school_ext.id')->select();
        $this->list['school_building'] = M('school_building')->join([
                'bd_school_ext ON bd_school_ext.id = bd_school_building.school_ext',
                'bd_school ON bd_school.id = bd_school_ext.school',
            ])->field([
                'bd_school_building.id',
                'bd_school_building.name',
                'bd_school_building.school_ext',
            ])->where([
                'bd_school_building.status' => ['IN', '1,2'],
                'bd_school_ext.status' => ['IN', '1,2'],
                'bd_school.status' => ['IN', '1,2'],
        ])->order('bd_school_building.school_ext asc')->select();


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

    public function apply(){
        $this->checkPosition();
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

    /**
        表单处理
    */
    public function changePosition(){
        if(!IS_POST) die;
        // p(I('post.'));
        $data = I('post.');
        $data['unionid'] = $this->unionid;
        if ($this->openid != '') $data['openid'] = $this->openid;
        elseif (SESSION('openid') != '') $data['openid'] = SESSION('openid');

        $User_Basic = D('UserBasic');
        if (false == $User_Basic->create($data)) {
            echo '<script>alert("' . $User_Basic->getError() . '");location.href="' . U('mobile/position/index') . '";</script>';
            return ;
        }
        if (M('user_basic')->getByUnionid($this->unionid)) {
            if ($User_Basic->save() === false) $this->alert('写入失败，请重新尝试！', U('mobile/position/index'));
            else {
                $position['school'] = I('school');
                $position['ext'] = I('school_ext');
                $position['building'] = I('building');
                $position['dormitory'] = I('dormitory');
                SESSION('position', $position);
                echo '<script>alert("提交成功");location.href="' . U('mobile/position/index') . '";</script>';
            }
        } else {
            $User_Basic->create_time = time();
            if ($User_Basic->add() == false) $this->alert('写入失败，请重新尝试！', U('mobile/position/index'));
            else {
                $position['school'] = I('school');
                $position['ext'] = I('school_ext');
                $position['building'] = I('building');
                $position['dormitory'] = I('dormitory');
                SESSION('position', $position);
                echo '<script>alert("提交成功");location.href="' . U('mobile/index/index') . '";</script>';
            }
        }
    }

    public function applyHandle(){
        if(!IS_POST) die;
        // p(I('post.'));
        $data = I('post.');
        $data['unionid'] = $this->unionid;
        $data['status'] = 2;
        $User_Basic = D('RoleApply');
        if (false == $User_Basic->create($data)) {
            echo '<script>alert("' . $User_Basic->getError() . '");location.href="' . U('mobile/position/apply') . '";</script>';
            return ;
        }
        if ($User_Basic->add() != false) {
            echo '<script>alert("提交成功");location.href="' . U('mobile/index/index') . '";</script>';
        } else {
            echo '<script>alert("提交失败");location.href="' . U('mobile/position/apply') . '";</script>';
        }
    }
}
