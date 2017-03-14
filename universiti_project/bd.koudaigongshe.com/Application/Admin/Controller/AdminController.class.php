<?php
namespace Admin\Controller;
use Think\Controller;
class AdminController extends CommenController{
    public function verify(){
        $data=$this->roleCtrl();
        $this->assign($data);
        $this->assign(array('url'=>array(
            'getinfo'=>U('Admin/Admin/verifyGetInfo'),
            'verify'=>U('Admin/Admin/verifyVerify'),
            'delete'=>U('Admin/Admin/verifyDelete'),
        )));
        $this->display('verify');
    }
    //获取审核信息
    public function verifyGetInfo(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $province=$post->province;
        if(!is_numeric($province)){
            $msg['code']=634;
            $this->ajaxReturn($msg);
        }
        $apply=M('admin_apply');
        $apply_data=$apply->where(array('province'=>array('in',"$province,all"),'status'=>array('in','2')))->order("`time` DESC")->select();
        $data=$this->doApplyData($apply_data);
        if($data){
            $msg['data']=$data;
            $msg['code']=200;
        }else{
            $msg['code']=635;
        }
        $this->ajaxReturn($msg);
    }
    //处理后台申请表的数据
    public function doApplyData($data){
        $province=M('dict_province');
        $school=M('school');
        $school_ext=M('school_ext');
        $canteen=M('canteen');
        $port=M('canteen_port');
        $role=M('role');
        foreach ($data as &$value) {
            //申请时间
            $value['date']=date('Y-m-d H:i',$value['time']);
            //角色名
            $role_data=$role->where(array('id'=>$value['role']))->find();
            $value['role_name']=$role_data['name'];
            //城市
            if($value['province']=='all'){
                $value['province_name']="全部";
            }else{
                $province_data=$province->where(array('id'=>$value['province']))->find();
                $value['province_name']=$province_data['name'];
            }
            //学校
            if($value['school_ext']=='all'){
                $value['school_ext_name']="全部";
                $value['school_name']='';
            }else{
                $school_ext_data=$school_ext->where(array('id'=>$value['school_ext']))->find();
                $school_data=$school->where(array('id'=>$school_ext_data['school']))->find();
                $value['school_name']=$school_data['name'];
                $value['school_ext_name']=$school_ext_data['name'];
            }
            //餐厅
            if($value['canteen']=='all'){
                $value['canteen_name']="全部";
            }else{
                $canteen_data=$canteen->where(array('id'=>$value['canteen']))->find();
                $value['canteen_name']=$canteen_data['name'];
            }
            //餐厅
            if($value['port']=='all'){
                $value['port_name']="全部";
            }else{
                $port_data=$port->where(array('id'=>$value['port']))->find();
                $value['port_name']=$port_data['name'];
            }
        }
        return $data;
    }
    //审核通过接口
    public function verifyVerify(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        if(!is_numeric($id)){
            $msg['code']="636";
            $this->ajaxReturn($msg);
        }
        $admin_apply=M('admin_apply');
        $admin_account=M('admin_account');
        $apply_data=$admin_apply->where(array('id'=>$id))->find();
        if($apply_data['role']!=13){
            $result1=$admin_account->where(array('unionid'=>$apply_data['unionid'],'role'=>array('neq',13),'status'=>array('in','1,2')))->find();
        }else{
            $result1=$admin_account->where(array('unionid'=>$apply_data['unionid'],'role'=>13,'status'=>array('in','1,2')))->find();
        }
        if($result1){
            $msg['code']=636;
            $msg['error']='他已经是后台管理员了';
            $this->ajaxReturn($msg);
        }
        $data=array(
            'time_recent'=>0,
            'name'=>$apply_data['name'],
            'mobile'=>$apply_data['mobile'],
            'role'=>$apply_data['role'],
            'unionid'=>$apply_data['unionid'],
            'school_ext'=>$apply_data['school_ext'],
            'province'=>$apply_data['province'],
            'canteen'=>$apply_data['canteen'],
            'port'=>$apply_data['port'],
            'status'=>1,
        );
        //在user_role里差数据

        $admin_apply->startTrans();
        $result2=$admin_apply->where(array('id'=>$id))->save(array('status'=>1));
        $result3=$admin_account->add($data);

        //往user_role 表里查数据
        $result4 = $this->verifyUserRole($id);
        if($result2&&$result3&&$result4){
            $admin_apply->commit();
            $msg['code']=200;
        }else{
            $admin_apply->rollback();
            $msg['code']=637;
        }
        $this->ajaxReturn($msg);
    }
    //将审核信息插入手机端的权限管理页面
    public function verifyUserRole($id){
        $admin_apply=M('admin_apply');
        $apply_data=$admin_apply->where(array('id'=>$id))->find();
        switch ($apply_data['role']) {
            case '6':
                $user_role_data = array(
                    'unionid'=>$apply_data['unionid'],
                    'mobile'=>$apply_data['mobile'],
                    'name'=>$apply_data['name'],
                    'role'=>6,
                    'status'=>1,
                );
                $user_role = M('user_role');
                $user_role_ext = M('user_role_ext');
                $user_role->startTrans();
                if(!$this->verifyUserRoleOnly($apply_data['role'],$apply_data['unionid'])){
                    return false ;
                }
                $res1 = $user_role->add($user_role_data);
                $port = M('canteen_port');
                $port_data = $port->where(array('id'=>$apply_data['port']))->find();
                $ext_data = array(
                    'user_role'=>$res1,
                    'aim'=>$apply_data['port'],
                    'aim_name'=>$port_data['name'],
                    'status'=>1,
                );
                $res2 = $user_role_ext->add($ext_data);
                if($res1&&$res2){
                    $user_role->commit();
                    return true ;
                }else{
                    $user_role->rollback();
                    return false;
                }
                break;
            case '13':
                $user_role_data = array(
                    'unionid'=>$apply_data['unionid'],
                    'mobile'=>$apply_data['mobile'],
                    'name'=>$apply_data['name'],
                    'role'=>13,
                    'status'=>1,
                );
                $user_role = M('user_role');
                $user_role_ext = M('user_role_ext');
                $user_role->startTrans();
                if(!$this->verifyUserRoleOnly($apply_data['role'],$apply_data['unionid'])){
                    return false ;
                }
                $res1 = $user_role->add($user_role_data);
                $ext_data = array(
                    'user_role'=>$res1,
                    'aim'=>$apply_data['aim'],
                    'aim_name'=>$apply_data['aim_name'],
                    'status'=>1,
                );
                $res2 = $user_role_ext->add($ext_data);
                if($res1&&$res2){
                    $user_role->commit();
                    return true ;
                }else{
                    $user_role->rollback();
                    return false;
                }
                break;
            case '5':
                $user_role_data = array(
                    'unionid'=>$apply_data['unionid'],
                    'mobile'=>$apply_data['mobile'],
                    'name'=>$apply_data['name'],
                    'role'=>5,
                    'status'=>1,
                );
                $user_role = M('user_role');
                $user_role_ext = M('user_role_ext');
                $user_role->startTrans();
                if(!$this->verifyUserRoleOnly($apply_data['role'],$apply_data['unionid'])){
                    return false ;
                }
                $res1 = $user_role->add($user_role_data);
                $school_ext = M('school_ext');
                $school_ext_data = $school_ext->where(array('id'=>$apply_data['school_ext']))->find();
                $ext_data = array(
                    'user_role'=>$res1,
                    'aim'=>$apply_data['school_ext'],
                    'aim_name'=>$school_ext_data['name'],
                    'status'=>1,
                );
                $res2 = $user_role_ext->add($ext_data);
                if($res1&&$res2){
                    $user_role->commit();
                    return true ;
                }else{
                    $user_role->rollback();
                    return false;
                }
                break;
            default:
                return true;
                break;
        }
    }
    public function verifyUserRoleOnly($role,$unionid){
        $user_role = M('user_role');
        $res = $user_role->where(array('unionid'=>$unionid,'role'=>$role,'status'=>array('in','1,2')))->find();
        if($res){
            return false;
        }else{
            return true;
        }
    }
    //删除审核信息
    public function verifyDelete(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        if(!is_numeric($id)){
            $msg['code']=638;
            $this->ajaxReturn($msg);
        }
        $apply=M('admin_apply');
        $result=$apply->where(array('id'=>$id))->save(array('status'=>0));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=638;
        }
        $this->ajaxReturn($msg);
    }
    /**
        后台账号管理页面
    */
    public function index(){
        $data=$this->roleCtrl();
        $this->assign($data);
        $this->assign(array('url'=>array(
            'getinfo'=>U('Admin/Admin/indexGetInfo'),
            'status'=>U('Admin/Admin/indexStatus'),
            'delete'=>U('Admin/Admin/indexDelete'),
        )));
        $this->display('index');
    }
    //获取后台账号列表
    public function indexGetInfo(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $province=$post->province;
        if(!is_numeric($province)){
            $msg['code']=634;
            $this->ajaxReturn($msg);
        }
        $account=M('admin_account');
        $account_data=$account->where(array('province'=>array("in","$province,all"),'status'=>array('in','1,2')))->select();
        $data=$this->doAccountData($account_data);
        if($data){
            $msg['data']=$data;
            $msg['code']=200;
        }else{
            $msg['code']=635;
        }
        $this->ajaxReturn($msg);
    }
    //处理后台账号表的数据
    public function doAccountData($data){
        $province=M('dict_province');
        $school=M('school');
        $school_ext=M('school_ext');
        $canteen=M('canteen');
        $port=M('canteen_port');
        $role=M('role');
        foreach ($data as &$value) {
            //申请时间
            if($value['time_recent']==0){
                $value['date_recent']='从未登入';
            }else{
                $value['date_recent']=date('Y-m-d H:i',$value['time_recent']);
                // $value['date_recent']='从未登入';
            }
            //角色名
            $role_data=$role->where(array('id'=>$value['role']))->find();
            $value['role_name']=$role_data['name'];
            //城市
            if($value['province']=='all'){
                $value['province_name']="全部";
            }else{
                $province_data=$province->where(array('id'=>$value['province']))->find();
                $value['province_name']=$province_data['name'];
            }
            //学校
            if($value['school_ext']=='all'){
                $value['school_ext_name']="全部";
                $value['school_name']='';
            }else{
                $school_ext_data=$school_ext->where(array('id'=>$value['school_ext']))->find();
                $school_data=$school->where(array('id'=>$school_ext_data['school']))->find();
                $value['school_name']=$school_data['name'];
                $value['school_ext_name']=$school_ext_data['name'];
            }
            //餐厅
            if($value['canteen']=='all'){
                $value['canteen_name']="全部";
            }else{
                $canteen_data=$canteen->where(array('id'=>$value['canteen']))->find();
                $value['canteen_name']=$canteen_data['name'];
            }
            //餐厅
            if($value['port']=='all'){
                $value['port_name']="全部";
            }else{
                $port_data=$port->where(array('id'=>$value['port']))->find();
                $value['port_name']=$port_data['name'];
            }
            if($value['status']==1){
                $value['status_name']='正在使用';
            }elseif($value['status']==2){
                $value['status_name']='暂停使用';
            }else{
                $value['status_name']="异常";
            }
        }
        return $data;
    }
    //改变状态接口
    public function indexStatus(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        if(!is_numeric($id)){
            $msg['code']=641;
            $this->ajaxReturn($msg);
        }
        $account=M('admin_account');
        $account_data=$account->where(array('id'=>$id))->find();
        if($account_data['status']==1){
            $status=2;
        }elseif($account_data['status']==2){
            $status=1;
        }
        $result=$account->where(array('id'=>$id))->save(array('status'=>$status));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=642;
        }
        $this->ajaxReturn($msg);
    }
    //删除接口
    public function indexDelete(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        if(!is_numeric($id)){
            $msg['code']=641;
            $this->ajaxReturn($msg);
        }
        $account=M('admin_account');
        $result=$account->where(array('id'=>$id))->save(array('status'=>0));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=642;
        }
        $this->ajaxReturn($msg);
    }
}
