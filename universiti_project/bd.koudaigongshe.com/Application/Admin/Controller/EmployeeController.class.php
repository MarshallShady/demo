<?php
namespace Admin\Controller;
use Think\Controller;
class EmployeeController extends CommenController{
    /**
        分拨员管理
    */
    public function deliver(){
        // if(!(session('data')['role']=='超级管理员'||session('data')['role']=='校园经理'||session('data')['role']=='城市经理')){
        //     echo "无权限";
        //     return ;
        // }
        $data=$this->roleCtrl();
        $this->assign($data);
        $this->assign('url',json_encode(array(
                                    'deliveredit'=>U('Admin/Employee/DeliverEdit'),
                                    'deliverdelete'=>U('Admin/Employee/DeliverDelete'),
                                    'deliverstatus'=>U('Admin/Employee/DeliverStatus'),
                                    'getinfo'=>U('Admin/Employee/deliverGetInfo'),
                                    'getcanteen'=>U('Admin/Employee/deliverGetCanteen'),
                                    'aim_status'=>U('Admin/Employee/deliverAimStatus'),
                                    'addcanteen'=>U('Admin/Employee/deliverAddCanteen'),
                                )));
        // $data2=$this->getListInfo();
        // $this->assign($data2);
        $this->display('deliver');
    }
    public function doData($data,$role=0){
            $m_role=M('role');
            $result=$m_role->where(array('id'=>$role))->find();
            $building=M('school_building');
            $school_ext=M('school_ext');
            $school=M('school');
            $user_basic = M('user_basic');
            foreach ($data as &$value) {
                $value['role_name']=$result['name'];
                $building_data=$building->where(array('id'=>$value['building']))->find();
                $value['building_name']=$building_data['name'];
                $school_ext_data=$school_ext->where(array('id'=>$building_data['school_ext']))->find();
                $value['school_ext_name']=$school_ext_data['name'];
                $value['school_ext']=$school_ext_data['id'];
                $school_data=$school->where(array('id'=>$school_ext_data['school']))->find();
                $value['school']=$school_data['id'];
                $value['school_name']=$school_data['name'];
                if($value['status']==1){
                    $value['status_2']='工作';
                    $value['status_3']='休息';
                }elseif($value['status']==2){
                    $value['status_2']='休息';
                    $value['status_3']='工作';
                }
                //覆盖名字
                $user_basic_data = $user_basic->where(array('unionid'=>$value['unionid']))->find();
                $value['name'] = $user_basic_data['name'];
            }
            return $data;
    }
    public function deliverGetInfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $school_ext=$post->school_ext;
        if(empty($school_ext)||!is_numeric($school_ext)){
            $msg['code']=525;
            $this->ajaxReturn($msg);
        }
        $build=M('school_building');
        $Model = new \Think\Model();
        // $sql="SELECT * FROM/ `bd_user_role`,`bd_user_basic` WHERE `bd_user_basic`.`unionid`=(SELECT * FROM `bd_user_role` WHERE `role`=7 AND `role_ext`=$school_ext).`unionid`";
        // $sql1="SELECT `b`.unionid,`b`.`role`,`b`.`mobile`,`b`.`alipay`,`b`.`comment`,`b`.`status`,`a`.`name`,`a`.``building,`` FROM `bd_user_basic` as b,(SELECT * FROM `bd_user_role` WHERE `role`=7) as a WHERE `bd_user_basic`.`unionid`=a.`unionid`";
        $sql="SELECT * FROM `bd_user_basic`,(SELECT * FROM `bd_user_role` WHERE `role`=7 AND `status` IN(1,2) AND `school_ext`=$school_ext) as a WHERE `bd_user_basic`.`unionid`=a.`unionid`";
        // $sql="SELECT * FROM ($sql1) as b WHERE b.`building`=1";
        $data=$Model->query($sql);
        $data=$this->doData($data,7);
        $msg['code']=200;
        $msg['data']=$data;
        $this->ajaxReturn($msg);
    }
    //获取特定类型业务员的信息
    public function getEmployeeInfo($role){
        if(!$role){
            echo "无数据";
            return ;
        }
        $user_role=M('user_role');
        $data=$user_role->where(array('role'=>$role))->join('bd_user_basic ON bd_user_basic.unionid=bd_user_role.unionid')->select();
        $m_role=M('role');
        $result=$m_role->where(array('id'=>$role))->find();
        $building=M('school_building');
        $school_ext=M('school_ext');
        $school=M('school');
        foreach ($data as &$value) {
            $value['role_name']=$result['name'];
            $building_data=$building->where(array('id'=>$value['building']))->find();
            $value['building_name']=$building_data['name'];
            $school_ext_data=$school_ext->where(array('id'=>$building_data['school_ext']))->find();
            $value['school_ext_name']=$school_ext_data['name'];
            $value['school_ext']=$school_ext_data['id'];
            $school_data=$school->where(array('id'=>$school_ext_data['school']))->find();
            $value['school']=$school_data['id'];
            $value['school_name']=$school_data['name'];
            if($value['status']==1){
                $value['status_2']='工作';
                $value['status_3']='休息';
            }else{
                $value['status_2']='休息';
                $value['status_3']='工作';
            }
        }
        // dump($data,1,'<pre>');
        return $data;
    }
    //修改工作状态
    public function deliverStatus(){
        $role=M('user_role');
        $result=$role->where(array('id'=>I('id')))->save(array('status'=>I('changeto')));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=401;
        }
        $this->ajaxReturn($msg);
    }
    //删除此分拨员
    public function deliverDelete(){
        $id=I('id');
        if($id==''){
            echo "无数据";
            return ;
        }
        $role=M('user_role');
        $result=$role->where(array('id'=>$id))->save(array('status'=>0));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=403;
        }
        $this->ajaxReturn($msg);
    }
    public function deliverEdit(){
        $role=M('user_role');
        $user_basic=M('user_basic');// $result=$role->validate($rules)->create($data2);
        $unionid=$role->where(array('id'=>I('id')))->find()['unionid'];
        // echo $unionid;
        $result=$user_basic->where(array('unionid'=>$unionid))->save(array('name'=>I('name'),'mobile'=>I('mobile')));
        // dump($user_basic->getError());
        if($result){
            $msg['code2']=200;
        }else{
            $msg['code2']=405;
            $msg['error']=$role->getError();
        }
        if($role->where(array('id'=>I('id')))->save(array('alipay'=>I('alipay'),'comment'=>I('comment')))){
            $msg['code1']=200;
        }
        else{
            $msg['code1']=406;
        }
        if($msg['code1']==200||$msg['code2']==200){
            $msg['code']=200;
        }else{
            $msg['code']=407;
        }
        $this->ajaxReturn($msg);
    }
    /**
        分拨员多餐厅切换管理
    */
    public function deliverGetCanteen(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $school_ext=$post->school_ext;
        $user_role_ext=M('user_role_ext');
        $canteen=M('canteen');
        $user_role_ext_data=$user_role_ext->where(array('user_role'=>$id))->select();
        $canteen_data=$canteen->where(array('school_ext'=>$school_ext))->select();
        //给status 中文名
        foreach ($user_role_ext_data as &$value) {
            if($value['status']==1){
                $value['status_name']='上班';
            }else{
                $value['status_name']='休息';
            }
        }
        $data['canteen']=$canteen_data;
        $data['aim']=$user_role_ext_data;
        $data['code']=200;
        $this->ajaxReturn($data);
    }
    public function deliverAimStatus(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $user_role_ext=M('user_role_ext');
        $user_role_ext_data=$user_role_ext->where(array('id'=>$id))->find();
        if($user_role_ext_data){
            if($user_role_ext_data['status']==2){
                $to=1;
            }elseif($user_role_ext_data['status']==1){
                $to=2;
            }
        }else{
            $msg['code']=591;
            $msg['error']="无此数据";
            $this->ajaxReturn($msg);
        }
        $result=$user_role_ext->where(array('id'=>$id))->save(array('status'=>$to));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=592;
        }
        $this->ajaxReturn($msg);
    }
    public function deliverAddCanteen(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $canteen=$post->canteen;
        $canteen_name=$post->canteen_name;
        $user_role_ext=M('user_role_ext');
        $ver=$user_role_ext->where(array('user_role'=>$id,'aim'=>$canteen))->find();
        if($ver){
            $msg['code']=593;
            $msg['error']=$ver;
            $this->ajaxReturn($msg);
        }
        $data=array(
            'aim'=>$canteen,
            'aim_name'=>$canteen_name,
            'status'=>1,
            'user_role'=>$id,
        );
        $result=$user_role_ext->add($data);
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=594;
        }
        $this->ajaxReturn($msg);
    }
    /**
        楼长管理
    */
    public function buildleader(){
        // if(!(session('data')['role']=='超级管理员'||session('data')['role']=='校园经理'||session('data')['role']=='城市经理')){
        //     echo "无权限";
        //     return ;
        // }
        $data=$this->roleCtrl();
        $this->assign($data);
        $this->assign('url',json_encode(array(
                                    'buildleaderedit'=>U('Admin/Employee/buildleaderEdit'),
                                    'buildleaderdelete'=>U('Admin/Employee/buildleaderDelete'),
                                    'buildleaderstatus'=>U('Admin/Employee/buildleaderStatus'),
                                    'getinfo'=>U('Admin/Employee/buildleaderGetInfo'),
                                )));
        $this->display('buildleader');
    }
    //获取楼长信息
    public function buildleaderGetInfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $school_ext=$post->school_ext;
        if(empty($school_ext)||!is_numeric($school_ext)){
            $msg['code']=525;
            $this->ajaxReturn($msg);
        }
        // dump($school_ext);
        // $school_ext=1;
        $build=M('school_building');
        // $build_data=$build->where(array('school_ext'=>$school_ext))->select();
        // $build_in=$this->arrayToIn($build_data);
        $Model = new \Think\Model();
        // $sql="SELECT * FROM/ `bd_user_role`,`bd_user_basic` WHERE `bd_user_basic`.`unionid`=(SELECT * FROM `bd_user_role` WHERE `role`=7 AND `role_ext`=$school_ext).`unionid`";
        // $sql1="SELECT `b`.unionid,`b`.`role`,`b`.`mobile`,`b`.`alipay`,`b`.`comment`,`b`.`status`,`a`.`name`,`a`.``building,`` FROM `bd_user_basic` as b,(SELECT * FROM `bd_user_role` WHERE `role`=7) as a WHERE `bd_user_basic`.`unionid`=a.`unionid`";
        $sql="SELECT * FROM `bd_user_basic`,(SELECT * FROM `bd_user_role` WHERE `role`=1 AND `status` IN(1,2) AND `school_ext`=$school_ext) as a WHERE `bd_user_basic`.`unionid`=a.`unionid`";
        // $sql="SELECT * FROM ($sql1) as b WHERE b.`building`=1";
        // echo $sql;
        $data=$Model->query($sql);
        $data=$this->doData($data,1);
        $msg['code']=200;
        $msg['data']=$data;
        $this->ajaxReturn($msg);
    }

    //修改工作状态
    public function buildleaderStatus(){
        $role=M('user_role');
        $result=$role->where(array('id'=>I('id')))->save(array('status'=>I('changeto')));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=401;
        }
        $this->ajaxReturn($msg);
    }
    //删除此分拨员
    public function buildleaderDelete(){
        $id=I('id');
        if($id==''){
            echo "无数据";
            return ;
        }
        $role=M('user_role');
        $result=$role->where(array('id'=>$id))->save(array('status'=>0));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=403;
        }
        $this->ajaxReturn($msg);
    }
    //楼长资料修改
    public function buildleaderEdit(){
        $role=M('user_role');
        $user_basic=M('user_basic');// $result=$role->validate($rules)->create($data2);
        $unionid=$role->where(array('id'=>I('id')))->find()['unionid'];
        // echo $unionid;
        $result=$user_basic->where(array('unionid'=>$unionid))->save(array('name'=>I('name'),'mobile'=>I('mobile')));
        // dump($user_basic->getError());
        if($result){
            $msg['code1']=200;
        }else{
            $msg['code1']=405;
            $msg['error']=$role->getError();
        }
        if($role->where(array('id'=>I('id')))->save(array('alipay'=>I('alipay'),'comment'=>I('comment')))){
            $msg['code2']=200;
        }
        else{
            $msg['code2']=406;
        }
        if($msg['code1']==200||$msg['code2']==200){
            $msg['code']=200;
        }else{
            $msg['code']=407;
        }
        $this->ajaxReturn($msg);
    }
    /**
        业务员审核页面
    */
    public function Verify(){
        // if(!(session('data')['role']=='超级管理员'||session('data')['role']=='校园经理'||session('data')['role']=='城市经理')){
        //     echo "无权限";
        //     return ;
        // }
        $data=$this->roleCtrl();
        $this->assign($data);
        $this->assign('url',json_encode(array(
                                    'verify'=>U('Admin/Employee/verifyTrue'),
                                    'getinfo'=>U('Admin/Employee/getVerifyInfo'),
                                    'delete'=>U('Admin/Employee/verifyDelete'),
                                )));
        $this->display('verify');
    }
    //删除申请信息
    public function verifyDelete(){
        $postData = file_get_contents('php://input','true');
        $post = json_decode($postData);
        $id=$post->id;
        if(!is_numeric($id)){
            $msg['code']=654;
            $this->ajaxReturn($msg);
        }
        $role_apply=M('role_apply');
        if($role_apply->where(array('id'=>$id))->save(array('status'=>0))){
            $msg['code'] = 200;
        }else{
            $msg['code'] = 655;
        }
        $this->ajaxReturn($msg);
    }
    //获取待审核数据
    public function getVerifyInfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $school_ext=$post->school_ext;
        $build=M('school_building');
        $build_data=$build->where(array('school_ext'=>$school_ext))->select();
        $build_sql=$this->arrayToIn($build_data);
        $user_role=M('role_apply');
        $building=M('school_building');
        $data=$user_role->where(array('status'=>2,'building'=>array('in',$build_sql)))->select();
        foreach ($data as &$value) {
            $building_data=$building->where(array('id'=>$value['building']))->find();
            $value['build_name']=$building_data['name'];
        }
        $msg['code']=200;
        $msg['data']=$data;
        $this->ajaxReturn($msg);
    }
    //修改审核状态表
    public function verifyTrue(){
        $id=I('id');
        if($id==''||!is_numeric($id)){
            echo "无数据";
            return ;
        }
        $role_apply=M('role_apply');
        $role_apply_data=$role_apply->where(array('id'=>$id))->find();
        $building=M('school_building');
        $building_data=$building->where(array('id'=>$role_apply_data['building']))->find();
        $role_user=M('user_role');
        $role_user_ext=M('user_role_ext');
        switch ($role_apply_data['job']) {
            case '校园经理':
                //获取此校区所有的楼栋
                $re1=$this->insertBuildleader($building_data['school_ext'],$id);
                $re2=$this->insertDeliver($id);
                $re3=$this->insertPack($id);
                // $re2=$this->insert($building_data['school_ext'],7,$id);
                // $re3=$this->insert($building_data['school_ext'],8,$id);
                if($re1&&$re2['code']==200&&$re3['code']==200){
                    $res=$role_apply->where(array('id'=>$id))->save(array('status'=>1));
                    if($res){
                        $msg['code']=200;
                    }else{
                        $msg['code']=531;
                    }
                }else{
                    $msg['code']=534;
                    $msg['error']=$re1;
                    $msg['error2']=$re2;
                    $msg['error3']=$re3;
                }
                break;
            case '楼长':
                $data=array(
                    'unionid'=>$role_apply_data['unionid'],
                    'mobile'=>$role_apply_data['mobile'],
                    'role'=>'1',
                    'school_ext'=>$building_data['school_ext'],
                    'status'=>1,
                );
                $building=M('school_building');
                $building_data=$building->where(array('id'=>$role_apply_data['building']))->find();
                if(!$this->verifyOnly($role_apply_data['unionid'],1)){
                    $msg['code']=548;
                    $msg['error']="他已经是楼长了";
                    $this->ajaxReturn($msg);
                }
                $role_user->startTrans();
                $id1=$role_user->add($data);
                $data2=array(
                    'user_role'=>$id1,
                    'aim'=>$role_apply_data['building'],
                    'aim_name'=>$building_data['name'],
                    'status'=>1,
                );
                $res=$role_user_ext->add($data2);
                if(empty($res)||empty($id1)){
                    $role_user->rollback();
                    $msg['code']=531;
                }else{

                    $res=$role_apply->where(array('id'=>$id))->save(array('status'=>1));
                    if($res){
                        $role_user->commit();
                        $msg['code']=200;
                    }else{
                        $role_user->rollback();
                        $msg['code']=532;
                    }
                }
                break;
            case '配送员':
                $msg=$this->insertDeliver($id);
                if($msg['code']==200){
                    $res=$role_apply->where(array('id'=>$id))->save(array('status'=>1));
                    if($res){
                        $msg['code']=200;
                    }else{
                        $msg['code']=532;
                    }
                }else{
                    $msg['code']=544;
                }
                break;
            case '出单员':
                $msg=$this->insertPack($id);
                if($msg['code']==200){
                    $res=$role_apply->where(array('id'=>$id))->save(array('status'=>1));
                    if($res){
                        $msg['code']=200;
                    }else{
                        $msg['code']=533;
                    }
                }else{
                    $msg['code']=545;
                }
                break;
            case '必点送':
                $msg=$this->insertBddeliver($id);
                if($msg['code']==200){
                    $res=$role_apply->where(array('id'=>$id))->save(array('status'=>1));
                    if($res){
                        $msg['code']=200;
                    }else{
                        $msg['code']=533;
                    }
                }else{
                    $msg['code']=545;
                }
                break;
        }
        $this->ajaxReturn($msg);
    }
    public function insertBuildleader($school_ext=0,$id=0){
        $role_apply=M('role_apply');
        $role_apply_data=$role_apply->where(array('id'=>$id))->find();
        //获取楼栋的名字
        $building=M('school_building');
        $building_data=$building->where(array('id'=>$role_apply_data['building']))->find();
        $role_user=M('user_role');
        $role_user_ext=M('user_role_ext');
        $build_all=$building->where(array('school_ext'=>$school_ext))->select();
        //往user_role里插入角色信息
        $data=array(
            'unionid'=>$role_apply_data['unionid'],
            'mobile'=>$role_apply_data['mobile'],
            'role'=>1,
            'school_ext'=>$building_data['school_ext'],
            'status'=>'1',
        );
        if(!$this->verifyOnly($role_apply_data['unionid'],1)){
            // $msg['code']=544;
            return false;
        }
        $uid=$role_user->add($data);
        // dump($id,1,'<pre>');
        //往user_role_ext里插入所有楼栋
        $sql=array();
        foreach ($build_all as $value) {
            $sql[]=array(
                'user_role'=>$uid,
                'aim'=>$value['id'],
                'aim_name'=>$value['name'],
                'status'=>1,
            );
        }
        $result=$role_user_ext->addAll($sql);
        // dump($result,1,'<pre>');
        if($result){
            return true;
        }else{
            return false;
        }
    }
    //配送员的通过审核函数
    public function insertDeliver($id){
        $role_apply=M('role_apply');
        $role_apply_data=$role_apply->where(array('id'=>$id))->find();
        $building=M('school_building');
        $building_data=$building->where(array('id'=>$role_apply_data['building']))->find();
        $role_user=M('user_role');
        $role_user_ext=M('user_role_ext');
        $data=array(
            'unionid'=>$role_apply_data['unionid'],
            'mobile'=>$role_apply_data['mobile'],
            'role'=>'7',
            'school_ext'=>$building_data['school_ext'],
            'status'=>'1',
        );
        if(!$this->verifyOnly($role_apply_data['unionid'],7)){
            $msg['code']=545;
            $msg['error']="他已经是分拨员了";
            $this->ajaxReturn($msg);
        }
        $id1=$role_user->add($data);
        if(empty($id1)){
            $msg['code']=533;
        }else{
            $msg['code']=200;
        }
        return $msg;
    }
    //插入出单员的数据
    public function insertPack($id){
        $role_apply=M('role_apply');
        $role_apply_data=$role_apply->where(array('id'=>$id))->find();
        $building=M('school_building');
        $building_data=$building->where(array('id'=>$role_apply_data['building']))->find();
        $role_user=M('user_role');
        $role_user_ext=M('user_role_ext');
        $data=array(
            'unionid'=>$role_apply_data['unionid'],
            'mobile'=>$role_apply_data['mobile'],
            'role'=>'8',
            'school_ext'=>$building_data['school_ext'],
            'status'=>'1',
        );
        if(!$this->verifyOnly($role_apply_data['unionid'],8)){
            $msg['code']=545;
            $msg['error']="他已经是出单员了";
            $this->ajaxReturn($msg);
        }
        $id1=$role_user->add($data);
        if(!$id1){
            $msg['code']=535;
        }else{
            $msg['code']=200;
        }
        return $msg;
    }
    //插入必点送的数据
    public function insertBddeliver($id){
        $role_apply=M('role_apply');
        $role_apply_data=$role_apply->where(array('id'=>$id))->find();
        $building=M('school_building');
        $building_data=$building->where(array('id'=>$role_apply_data['building']))->find();
        $role_user=M('user_role');
        $role_user_ext=M('user_role_ext');
        $data=array(
            'unionid'=>$role_apply_data['unionid'],
            'mobile'=>$role_apply_data['mobile'],
            'role'=>11,
            'school_ext'=>$building_data['school_ext'],
            'status'=>'1',
        );
        if(!$this->verifyOnly($role_apply_data['unionid'],11)){
            $msg['code']=545;
            $msg['error']="他已经是必点送了";
            $this->ajaxReturn($msg);
        }
        $id1=$role_user->add($data);
        if(!$id1){
            $msg['code']=535;
        }else{
            $msg['code']=200;
        }
        return $msg;
    }
    //判断验证函数 判断user_role 里 unionid role 同时相同的 人
    public function verifyOnly($unionid,$role){
        $user_role=M('user_role');
        $result=$user_role->where(array('unionid'=>$unionid,'role'=>$role,'status'=>array('in','1,2')))->find();
        if($result){
            return false;
        }else{
            return true;
        }
    }
    /**
        楼栋管理，和楼栋对应的楼长管理
    */
    public function building(){
        $this->assign(array('url'=>array(
            'getstatus'=>U('Admin/Employee/buildGetStatus'),
            'getinfo'=>U('Admin/Employee/getBuildleaderInfo'),
            'editstatus'=>U('Admin/Employee/buildEditStatus'),
            'editcontent'=>U('Admin/Employee/buildEditContent'),
            'addbuildleader'=>U('Admin/Employee/buildAddBuildleader'),
            'editbuildleader'=>U('Admin/Employee/buildEditbuildleader'),
            'deletebuildleader'=>U('Admin/Employee/buildDeleteBuildleader'),
            'getbuild'=>U('Admin/Employee/buildGetBuildleader'),
            'delete'=>U('Admin/Employee/buildDelete'),
            'addbuild'=>U('Admin/Employee/buildAddBuild'),
        )));
        $data=$this->roleUserCtrl();
        if(!$data){
            $msg['code']=502;
            $this->ajaxReturn($msg);
        }
        $this->assign($data);
        $this->display('build');
    }
    //过去楼栋状态信息
    public function buildGetStatus(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->build;
        $build=M('school_building');
        $data=$build->where(array('id'=>$id))->find();
        if($data['status']==2){
            $data['status_name']="停运";
            $data['code']=200;
        }else if($data['status']==1){
            $data['status_name']="营运";
            $data['code']=200;
        }else{
            $data['code']=509;
            $data['error']=$data;
        }
        if($data['mode']==2){
            $data['mode_name']="只有必点送";
        }elseif($data['mode']==1){
            $data['mode_name']="除“只有必点送”的所有模式";
        }else{
            $data['mode_name']="异常";
        }
        $this->ajaxReturn($data);
    }
    //修改楼栋状态
    public function buildEditStatus(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $status=$post->status;
        $build=M('school_building');
        $rules=array(
            array('status','number',"状态出错"),
            array('id','number',"id"),
        );
        if(!$build->validate($rules)->create(array('status'=>$status,'id'=>$id))){
            $msg['code']=535;
            $this->ajaxReturn($msg);
        }
        $result=$build->where(array('id'=>$id))->save(array('status'=>$status));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=537;
        }
        $this->ajaxReturn($msg);
    }
    //修改楼栋关闭提示
    public function buildEditContent(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $content=$post->content;
        $ist_tohome=$post->ist_tohome;
        $nd_tohome=$post->nd_tohome;
        $mode=$post->mode;
        $build=M('school_building');
        $rules=array(
            // array('on_status_two','',"提示内容出错"),
            array('id','number',"id"),
            array('ist_tohome','number','ist'),
            array('nd_tohome','number','nd'),
            array('mode','number','mode'),
        );
        if(!$build->validate($rules)->create(array('id'=>$id))){
            $msg['code']=539;
            $msg['error']=$build->getError();
            $this->ajaxReturn($msg);
        }
        $result=$build->where(array('id'=>$id))->save(array('on_status_two'=>$content,'IST_tohome'=>$ist_tohome,'ND_tohome'=>$nd_tohome,'mode'=>$mode));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=537;
        }
        $this->ajaxReturn($msg);
    }
    //获取指定楼栋所属的楼长
    public function getBuildleaderInfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $build=$post->build;
        if(empty($build)||!is_numeric($build)){
            $msg['code']=566;
            $this->ajaxReturn($msg);
        }
        $data['data']=$this->getBuildleaderData($build);
        if(!$data['data']){
            $msg['code']=567;
            $this->ajaxReturn($msg);
        }
        $data['code']=200;
        $this->ajaxReturn($data);
    }
    //获取指定楼栋的楼长 所有状态的楼长并处理
    public function getBuildleaderData($build){
        $user_role_ext=M('user_role_ext');
        $user_role=M('user_role');
        $user_role_data=$user_role->where(array('role'=>1,'status'=>array('in','1,2')))->select();
        $role_str=$this->arrayToIn($user_role_data);
        $rules=array(
            'status'=>array('in','1,2'),
            'user_role'=>array('in',$role_str),
            'aim'=>$build,
        );
        $user_role_ext_data=$user_role_ext->where($rules)->select();
        $data=$this->doRoleExtData($user_role_ext_data);
        return $data;
    }
    //处理上面函数select 的数据
    public function doRoleExtData($data){
        $user_role=M('user_role');
        $user_basic=M('user_basic');
        foreach ($data as &$value) {
            $user_role_data=$user_role->where(array('id'=>$value['user_role']))->find();
            $user_basic_data=$user_basic->where(array('unionid'=>$user_role_data['unionid']))->find();
            $value['mobile']=$user_role_data['mobile'];
            $value['name']=$user_basic_data['name'];
            if($value['status']==1){
                $value['status_name']="上班";
            }else{
                $value['status_name']="休息";
            }
        }
        return $data;
    }
    //获取此学校的所有楼长
    public function buildGetBuildleader(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $school_ext=$post->school_ext;
        $user_role=M('user_role');
        $user_basic=M('user_basic');
        $user_role_data=$user_role->where(array('school_ext'=>$school_ext,'status'=>array('in','1,2'),'role'=>1))->select();
        foreach ($user_role_data as &$value) {
            $user_basic_data=$user_basic->where(array('unionid'=>$value['unionid']))->find();
            if($user_basic_data){
                $value['name']=$user_basic_data['name'];
            }else{
                $value['name']="未知";
            }
        }
        if($user_role_data){
            $data['code']=200;
            $data['data']=$user_role_data;
        }else{
            $data['code']=578;
        }
        $this->ajaxReturn($data);
    }
    //增加此楼栋的楼长
    public function buildAddBuildleader(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $user_role=$post->user_role;
        $build=$post->build;
        $build_name=$post->build_name;
        // $building=M('school_building');
        // $building_data=$building->where(array('id'=>$build));
        $user_role_ext=M('user_role_ext');
        $ver=$user_role_ext->where(array('user_role'=>$user_role,'aim'=>$build))->find();
        if(!$ver){
            $data=array(
                'user_role'=>$user_role,
                'aim'=>$build,
                'aim_name'=>$build_name,
                'status'=>1,
            );
            $result=$user_role_ext->add($data);
            if($result){
                $msg['code']=200;
            }else{
                $msg['code']=581;
            }
            $this->ajaxReturn($msg);
        }elseif($ver['status']==1||$ver['status']==2){
            $msg['code']=580;
            $msg['error']="此人已经负责此楼了";
            $this->ajaxReturn($msg);
        }elseif($ver['status']==0){
            $result=$user_role_ext->where(array('id'=>$ver['id']))->save(array('status'=>1));
            if($result){
                $msg['code']=200;
            }else{
                $msg['code']=581;
            }
            $this->ajaxReturn($msg);
        }
    }
    //删除此楼栋下的指定楼长
    public function buildDeleteBuildleader(){
        $postData = file_get_contents('php://input',true);
        $post = json_decode($postData);
        $id = $post->id;
        if(!is_numeric($id)){
            $msg['code'] = 405;
            $this->ajaxReturn($msg);
        }
        $user_role_ext = M('user_role_ext');
        $result = $user_role_ext->where(array('id'=>$id))->save(array('status'=>0));
        if($result){
            $msg['code'] = 200;
        }else{
            $msg['code'] = 406;
        }
        $this->ajaxReturn($msg);
    }
    //修改稿此楼栋的楼长状态
    public function buildEditBuildleader(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        // $build=$post->build;
        $id=$post->id;
        $user_role_ext=M('user_role_ext');
        $data=$user_role_ext->where(array('id'=>$id))->find();
        if($data['status']==1){
            $status=2;
        }elseif($data['status']==2){
            $status=1;
        }else{
            $msg['code']=578;
            $this->ajaxReturn($msg);
        }
        $result=$user_role_ext->where(array('id'=>$id))->save(array('status'=>$status));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=579;
        }
        $this->ajaxReturn($msg);
    }
    //添加楼栋接口
    public function buildAddBuild(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        $build_name=$post->build_name;
        $rules=array(
            array('name','require','name'),
            array('status','number','status'),
            array('school_ext','number','school_ext'),
        );
        $data=array(
            'name'=>$build_name,
            'school_ext'=>$id,
            'status'=>1,
            'mode'=>1,
            'ist_tohome'=>1,
            'nd_tohome'=>2,
        );
        $building=M('school_building');
        if(!$building->validate($rules)->create($data)){
            $msg['code']=645;
            $msg['error']=$building->getError();
        }
        $res=$building->where(array('name'=>$build_name,'school_ext'=>$id,'status'=>array('in','1,2')))->find();
        if($res){
            $msg['code']=647;
            $msg['error']='这个楼栋在此学校已经添加过了';
            $this->ajaxReturn($msg);
        }
        $result=$building->add($data);
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=646;
        }
        $this->ajaxReturn($msg);
    }
    //删除楼栋
    public function buildDelete(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        if(!is_numeric($id)){
            $msg['code']=648;
            $this->ajaxReturn($msg);
        }
        $building=M('school_building');
        $res=$building->where(array('id'=>$id))->save(array('status'=>0));
        if($res){
            $msg['code']=200;
        }else{
            $msg['code']=649;
        }
        $this->ajaxReturn($msg);
    }
    /**
        分拨员管理
    */
    public function packman(){
        // if(!(session('data')['role']=='超级管理员'||session('data')['role']=='校园经理'||session('data')['role']=='城市经理')){
        //     echo "无权限";
        //     return ;
        // }
        $data=$this->roleCtrl();
        $this->assign($data);
        $this->assign('url',json_encode(array(
                                    'edit'=>U('Admin/Employee/packmanEdit'),
                                    'delete'=>U('Admin/Employee/packmanDelete'),
                                    'status'=>U('Admin/Employee/packmanStatus'),
                                    'getinfo'=>U('Admin/Employee/packmanGetInfo'),
                                    'getcanteen'=>U('Admin/Employee/packmanGetCanteen'),
                                    'aim_status'=>U('Admin/Employee/packmanAimStatus'),
                                    'addcanteen'=>U('Admin/Employee/packmanAddCanteen'),
                                )));
        // $data2=$this->getListInfo();

        // $this->assign($data2);

        $this->display('packman');

    }
    //获取出单员的列表
    public function packmanGetInfo(){

        $postData=file_get_contents('php://input', true);

        $post=json_decode($postData);

        $school_ext=$post->school_ext;

        if(empty($school_ext)||!is_numeric($school_ext)){

            $msg['code']=525;

            $this->ajaxReturn($msg);

        }

        $build=M('school_building');

        $Model = new \Think\Model();

        // $sql="SELECT * FROM/ `bd_user_role`,`bd_user_basic` WHERE `bd_user_basic`.`unionid`=(SELECT * FROM `bd_user_role` WHERE `role`=7 AND `role_ext`=$school_ext).`unionid`";

        // $sql1="SELECT `b`.unionid,`b`.`role`,`b`.`mobile`,`b`.`alipay`,`b`.`comment`,`b`.`status`,`a`.`name`,`a`.``building,`` FROM `bd_user_basic` as b,(SELECT * FROM `bd_user_role` WHERE `role`=7) as a WHERE `bd_user_basic`.`unionid`=a.`unionid`";

        $sql="SELECT * FROM `bd_user_basic`,(SELECT * FROM `bd_user_role` WHERE `role`=8 AND `status` IN(1,2) AND `school_ext`=$school_ext) as a WHERE `bd_user_basic`.`unionid`=a.`unionid`";

        // $sql="SELECT * FROM ($sql1) as b WHERE b.`building`=1";

        $data=$Model->query($sql);

        $data=$this->doData($data,8);

        $msg['code']=200;

        $msg['data']=$data;

        $this->ajaxReturn($msg);

    }
    //修改工作状态
    public function packmanStatus(){

        $role=M('user_role');

        $result=$role->where(array('id'=>I('id')))->save(array('status'=>I('changeto')));

        if($result){

            $msg['code']=200;

        }else{

            $msg['code']=401;

        }

        $this->ajaxReturn($msg);

    }

    //删除此分拨员

    public function packmanDelete(){

        $id=I('id');

        if($id==''){

            echo "无数据";

            return ;

        }

        $role=M('user_role');

        $result=$role->where(array('id'=>$id))->save(array('status'=>0));

        if($result){

            $msg['code']=200;

        }else{

            $msg['code']=403;

        }

        $this->ajaxReturn($msg);

    }

    public function packmanEdit(){
        $role=M('user_role');
        $user_basic=M('user_basic');// $result=$role->validate($rules)->create($data2);
        // $unionid=$role->where(array('id'=>I('id')))->find()['unionid'];
        // echo $unionid;
        // $result=$user_basic->where(array('unionid'=>$unionid))->save(array('mobile'=>I('mobile')));
        //
        // // dump($user_basic->getError());
        //
        // if($result){
        //
        //     $msg['code2']=200;
        //
        // }else{
        //
        //     $msg['code2']=405;
        //
        //     $msg['error']=$role->getError();
        //
        // }
        if($role->where(array('id'=>I('id')))->save(array('alipay'=>I('alipay'),'comment'=>I('comment'),'mobile'=>I('mobile')))){
            $msg['code1']=200;
        }
        else{
            $msg['code1']=406;
        }
        if($msg['code1']==200){
            $msg['code']=200;
        }else{
            $msg['code']=407;
        }
        $this->ajaxReturn($msg);
    }
    /**
        出票员多餐厅切换管理
    */
    public function packmanGetCanteen(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $school_ext=$post->school_ext;
        $user_role_ext=M('user_role_ext');
        $canteen=M('canteen');
        $user_role_ext_data=$user_role_ext->where(array('user_role'=>$id))->select();
        $canteen_data=$canteen->where(array('school_ext'=>$school_ext))->select();
        //给status 中文名
        foreach ($user_role_ext_data as &$value) {
            if($value['status']==1){
                $value['status_name']='负责';
            }else{
                $value['status_name']='不负责';
            }
        }
        $data['canteen']=$canteen_data;
        $data['aim']=$user_role_ext_data;
        $data['code']=200;
        $this->ajaxReturn($data);
    }

    public function packmanAimStatus(){

        $postData=file_get_contents('php://input', true);

        $post=json_decode($postData);
        $user_role=$post->user_role;
        $id=$post->id;
        $user_role_ext=M('user_role_ext');
        $user_role_ext_data=$user_role_ext->where(array('id'=>$id))->find();
        $all=$user_role_ext->where(array('user_role'=>$user_role_ext_data['user_role'],'status'=>array('in','1,2')))->select();

        if($user_role_ext_data){
            if($user_role_ext_data['status']==2){
                $to=1;
                //判断他是否同时负责两个餐厅
                $bool=true;
                foreach ($all as $value) {
                    if($value['id']!=$id){
                        if($value['status']==1){
                            $bool=false;
                            break;
                        }
                    }
                }
                if(!$bool){
                    $msg['code']=598;
                    $msg['error']='出票不能同时负责两个餐厅，他同时还负责另一餐厅，请先让他不负责那个餐厅';
                    $this->ajaxReturn($msg);
                }
            }elseif($user_role_ext_data['status']==1){
                $to=2;
            }
        }else{
            $msg['code']=591;
            $msg['error']="无此数据";
            $this->ajaxReturn($msg);
        }
        $result=$user_role_ext->where(array('id'=>$id))->save(array('status'=>$to));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=592;
        }
        $this->ajaxReturn($msg);
    }
    //添加餐厅
    public function packmanAddCanteen(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $canteen=$post->canteen;
        $canteen_name=$post->canteen_name;
        $user_role_ext=M('user_role_ext');
        $ver=$user_role_ext->where(array('user_role'=>$id,'aim'=>$canteen))->find();
        if($ver){
            $msg['code']=593;
            $msg['error']=$ver;
            $this->ajaxReturn($msg);
        }
        $data=array(
            'aim'=>$canteen,
            'aim_name'=>$canteen_name,
            'status'=>2,
            'user_role'=>$id,
        );
        $result=$user_role_ext->add($data);
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=594;
        }
        $this->ajaxReturn($msg);
    }
    /**
        必点送页面
    */
    public function bddeliver(){
        // if(!(session('data')['role']=='超级管理员'||session('data')['role']=='校园经理'||session('data')['role']=='城市经理')){
        //     echo "无权限";
        //     return ;
        // }
        $data=$this->roleCtrl();
        $this->assign($data);
        $this->assign('url',json_encode(array(
                                    'edit'=>U('Admin/Employee/bddeliverEdit'),
                                    'delete'=>U('Admin/Employee/bddeliverDelete'),
                                    'status'=>U('Admin/Employee/bddeliverStatus'),
                                    'getinfo'=>U('Admin/Employee/bddeliverGetInfo'),
                                    'getcanteen'=>U('Admin/Employee/bddeliverGetCanteen'),
                                    'aim_status'=>U('Admin/Employee/bddeliverAimStatus'),
                                    'addcanteen'=>U('Admin/Employee/bddeliverAddCanteen'),
                                )));
        $this->display('bddeliver');
    }
    //获取必点送的信息
    public function bddeliverGetInfo(){
        $postData = file_get_contents("php://input",true);
        $post = json_decode($postData);
        $school_ext=$post->school_ext;
        $user_role=M('user_role');
        if(!is_numeric($school_ext)){
            $msg['code']=653;
            $this->ajaxReturn($msg);
        }
        $data=$user_role->where(array('`bd_user_role`.`role`'=>11,'`bd_user_role`.`school_ext`'=>$school_ext,'`bd_user_role`.`status`'=>array('in','1,2')))->join(
            '`bd_user_basic` ON `bd_user_role`.`unionid`=`bd_user_basic`.`unionid`'
        )->join(
            '`bd_role` ON `bd_role`.`id`=`bd_user_role`.`role`'
        )->join(
            '`bd_school_ext` ON `bd_school_ext`.`id`=`bd_user_role`.`school_ext`'
        )->join(
            '`bd_school` ON `bd_school`.`id`=`bd_school_ext`.`school`'
        )->field(array(
            '`bd_role`.`name`'=>'role_name',

            '`bd_user_basic`.`name`'=>'name',

            '`bd_user_role`.`alipay`'=>'alipay',
            '`bd_user_role`.`mobile`'=>'mobile',
            '`bd_user_role`.`comment`'=>'comment',
            '`bd_user_role`.`id`'=>'id',
            '`bd_user_role`.`status`'=>'status',

            '`bd_school`.`name`'=>'school_name',
            '`bd_school_ext`.`name`'=>'school_ext_name',
        ))->select();
        foreach ($data as $key => &$value) {
            if($value['status']==1){
                $value['status_name']='上班';
            }elseif($value['status']==2){
                $value['status_name']='休息';
            }else{
                $value['status_name']='异常';
            }
        }
        if($data){
            $msg['code']=200;
            $msg['data']=$data;
        }else{
            $msg['code']=654;
        }
        $this->ajaxReturn($msg);
    }
    //修改状态
    public function bddeliverStatus(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        $user_role=M('user_role');
        $data=$user_role->where(array('id'=>$id))->find();
        if($data['status']==1){
            $status=2;
        }elseif($data['status']==2){
            $status=1;
        }else{
            $msg['code']=652;
            $this->ajaxReturn($msg);
        }
        $result=$user_role->where(array('id'=>$id))->save(array('status'=>$status));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=403;
        }
        $this->ajaxReturn($msg);
    }
    //修改食堂的机器码
    public function bddeliverEdit(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        $alipay=$post->alipay;
        $mobile=$post->mobile;
        $comment=$post->comment;
        $data=array(
            'id'=>$id,
            'alipay'=>$alipay,
            'mobile'=>$mobile,
            'comment'=>$comment,
        );
        $rules=array(
            array('id','number','id'),
            array('alipay','/.*/','alipay'),
            array('comment','/.*/','coment'),
            array('mobile','number','mobile'),
        );
        $user_role=M('user_role');
        if(!$user_role->validate($rules)->create($data)){
            $msg['code']=649;
            $msg['error']=$user_role->getError();
            $this->ajaxReturn($msg);
        }
        if($user_role->save($data)){
            $msg['code']=200;
        }else{
            $msg['code']=650;
        }
        $this->ajaxReturn($msg);
    }
    public function bddeliverDelete(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        $user_role=M('user_role');
        $result=$user_role->where(array('id'=>$id))->save(array('status'=>0));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=402;
        }
        $this->ajaxReturn($msg);
    }
    /**
        必点送多餐厅切换管理
    */
    public function bddeliverGetCanteen(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $school_ext=$post->school_ext;
        $user_role_ext=M('user_role_ext');
        $canteen=M('canteen');
        $user_role_ext_data=$user_role_ext->where(array('user_role'=>$id,'status'=>array('in','1,2')))->select();
        $canteen_data=$canteen->where(array('school_ext'=>$school_ext,'status'=>array('in','1,2')))->select();
        //给status 中文名
        foreach ($user_role_ext_data as &$value) {
            if($value['status']==1){
                $value['status_name']='负责';
            }else{
                $value['status_name']='不负责';
            }
        }
        $data['canteen']=$canteen_data;
        $data['aim']=$user_role_ext_data;
        $data['code']=200;
        $this->ajaxReturn($data);
    }

    public function bddeliverAimStatus(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        // $user_role=$post->user_role;
        $id=$post->id;
        $user_role_ext=M('user_role_ext');
        $user_role_ext_data=$user_role_ext->where(array('id'=>$id,'status'=>array('in','1,2')))->find();
        $all=$user_role_ext->where(array('user_role'=>$user_role_ext_data['user_role'],'status'=>array('in','1,2')))->select();

        if($user_role_ext_data){
            if($user_role_ext_data['status']==2){
                $to=1;
                //判断他是否同时负责两个餐厅
                // $bool=true;
                // foreach ($all as $value) {
                //     if($value['id']!=$id){
                //         if($value['status']==1){
                //             $bool=false;
                //             break;
                //         }
                //     }
                // }
                // if(!$bool){
                //     $msg['code']=598;
                //     $msg['error']='必点送不能同时抢两个餐厅的单哦~，他同时还负责另一餐厅，请先让他不负责那个餐厅';
                //     $this->ajaxReturn($msg);
                // }
            }elseif($user_role_ext_data['status']==1){
                $to=2;
            }
        }else{
            $msg['code']=591;
            $msg['error']="无此数据";
            $this->ajaxReturn($msg);
        }
        $result=$user_role_ext->where(array('id'=>$id))->save(array('status'=>$to));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=592;
        }
        $this->ajaxReturn($msg);
    }
    //添加餐厅
    public function bddeliverAddCanteen(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $canteen=$post->canteen;
        $canteen_name=$post->canteen_name;
        $user_role_ext=M('user_role_ext');
        $ver=$user_role_ext->where(array('user_role'=>$id,'aim'=>$canteen,'status'=>array('in','1,2')))->find();
        if($ver){
            $msg['code']=593;
            $msg['error']='此餐厅已经添加过了';
            $this->ajaxReturn($msg);
        }
        $data=array(
            'aim'=>$canteen,
            'aim_name'=>$canteen_name,
            'status'=>1,
            'user_role'=>$id,
        );
        $result=$user_role_ext->add($data);
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=594;
        }
        $this->ajaxReturn($msg);
    }
}
