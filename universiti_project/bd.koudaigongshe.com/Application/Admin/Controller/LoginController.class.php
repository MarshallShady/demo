<?php
namespace Admin\Controller;
use Think\Controller;
/**
    登入界面
    @author by larry
*/
class LoginController extends Controller{
    //显示登入界面
    public function login(){
        if(session('status')=='on'){
            $this->success('你已经登入过了',U('admin/index/index/'));
        }else{
            redirect(U('admin/scanlogin/index'));
        }
    }

    // 退出功能
    public function logout(){
        if(session('status')=='on'){
            session('status','off');
            session('data','');
            session('unionid','');
            $this->success('正在注销',U('admin/login/login'));
        }else{
            $this->error('页面错误');
        }
    }
    //获取并封装后台管理员的详细资料：名字 角色 权限
    public function getAdminInfo($admin=array()){
        $role=M('role');
        $role_data=$role->where("id='".$admin['role']."'")->find();
        $role_right=M('role_right');
        $role_right_data=$role_right->where("role='".$admin['role']."'")->field("right")->select();
        // $right=M('right');
        // $right_data=array();
        // foreach ($role_right_data as $value) {
        //     $data=$right->where("id='".$value['right']."'")->find();
        //     $right_data[]=$data['name'];
        // }
        // $admin_data=array('name'=>$admin['name'],
        //                   'role'=>$role_data['name'],
        //                   'right'=>$right_data);
        $admin_data=array('name'=>$admin['name'],
                            'school_ext'=>$admin['school'],
                            'canteen'=>$admin['school_ext'],
                            'port'=>$admin['school_ext2'],
                            'role'=>$role_data['name'],
                            'role_id'=>$admin['id'],
                            );
        return $admin_data;
    }
    //验证账号密码是否正确
    public function checklogin(){
        $username=I('username');
        $password=I('password');
        $login=M('admin');
        $res=$login->where('username="'.$username.'"')->find();
        // dump($res,1,'<pre>');
        if($res){
            if($res['password']==$password){
                $msg=array('code'=>200,'msg'=>'ok');
                session('status','on');
                $data=$this->getAdminInfo($res);
                session('data',$data);
            }else{
                $msg=array('code'=>400,'msg'=>'Invalid Passerod');
            }
        }else{
            $msg=array('code'=>401,'msg'=>'Invalid Username');
        }
        $this->ajaxReturn($msg);
        // return json_encode($msg);
    }
}
