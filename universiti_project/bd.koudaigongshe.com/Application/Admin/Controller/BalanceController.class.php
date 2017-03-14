<?php
namespace Admin\Controller;
use Think\Controller;
class BalanceController extends OrderController{
    public function buildleader(){
        if(!(session('data')['role']=='超级管理员'||session('data')['role']=='校园经理'||session('data')['role']=='城市经理')){
            $this->display('/denied');
            return ;
        }
        $this->assign(array('url'=>array(
            'getinfo'=>U('Admin/Balance/getBuildleaderInfo'),
            'clear'=>U('Admin/Balance/buildClear'),
            'history'=>U('Admin/Balance/buildGetHistory'),
        )));
        $data=$this->roleCtrl();
        if(!$data){
            $msg['code']=502;
            $this->ajaxReturn($msg);
        }
        $this->assign($data);
        $this->display('buildleader');
    }
    //获取指定校区的楼长的数据接口
    public function getBuildleaderInfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $province=$post->province;
        $school=$post->school;
        $school_ext=$post->school_ext;
        // $canteen=$post->canteen;
        // dump($school_ext);
        if((!(is_numeric($school_ext))||empty($school_ext))){
            $msg['code']='512';
            $msg['error']='滚!';
            $this->ajaxReturn($msg);
        }
        $data=$this->getEmployeeInfo(1,$school_ext);
        if($data){
            $msg['code']=200;
            $msg['data']=$data;
        }else{
            $msg['code']=515;
        }
        $this->ajaxReturn($msg);
    }
    //楼长结算接口
    public function buildClear(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        if(empty($id)||(!is_numeric($id))){
            $msg['code']=514;
            $msg['error']="滚";
            $this->ajaxReturn($msg);
        }
        $result=$this->userClear($id,1);
        if($result['code']==200){
            $msg['code']=200;
        }else{
            $msg['code']=597;
            $msg['error']=$result;
        }
        $this->ajaxReturn($msg);
    }
    //获取指定楼长的结算历史
    public function buildGetHistory(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $user_id=$post->id;
        $d=$this->getUserHistory($user_id,1);
        if($d['code']==200){
            $msg['code']=200;
            $msg['data']=$d['data'];
        }else{
            $msg['code']=598;
            $msg['error']=$d;
        }
        $this->ajaxReturn($msg);
    }

    /**
        分拨员
    */
    public function deliver(){
        if(!(session('data')['role']=='超级管理员'||session('data')['role']=='校园经理'||session('data')['role']=='城市经理')){
            $this->display('/denied');
            return ;
        }
        $this->assign(array('url'=>array(
            'getinfo'=>U('Admin/Balance/deliverGetInfo'),
            'clear'=>U('Admin/Balance/deliverClear'),
            'history'=>U('Admin/Balance/deliverGetHistory'),
        )));
        $data=$this->roleCtrl();
        if(!$data){
            $msg['code']=502;
            $this->ajaxReturn($msg);
        }
        $this->assign($data);
        $this->display('deliver');
    }
    public function deliverGetInfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $province=$post->province;
        $school=$post->school;
        $school_ext=$post->school_ext;
        // $canteen=$post->canteen;
        // dump($school_ext);
        if((!(is_numeric($school_ext))||empty($school_ext))){
            $msg['code']='512';
            $msg['error']='滚!';
            $this->ajaxReturn($msg);
        }
        $data=$this->getEmployeeInfo(7,$school_ext);
        if($data){
            $msg['code']=200;
            $msg['data']=$data;
        }else{
            $msg['code']=515;
        }
        $this->ajaxReturn($msg);
    }
    //分拨员结算接口
    public function deliverClear(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        if(empty($id)||(!is_numeric($id))){
            $msg['code']=514;
            $msg['error']="滚";
            $this->ajaxReturn($msg);
        }
        $result=$this->userClear($id,7);
        if($result['code']==200){
            $msg['code']=200;
        }else{
            $msg['code']=598;
            $msg['error']=$result;
        }
        $this->ajaxReturn($msg);
    }
    //分拨员获取历史结算接口
    public function deliverGetHistory(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $user_id=$post->id;
        $d=$this->getUserHistory($user_id,7);
        if($d['code']==200){
            $msg['code']=200;
            $msg['data']=$d['data'];
        }else{
            $msg['code']=599;
            $msg['error']=$d;
        }
        $this->ajaxReturn($msg);
    }
    //业务员结算接口
    //只有楼长有用
    public function userClear($id,$role){
        $user_role=M('user_role');
        $user_role->startTrans();
        $balance_user=M('balance_user');
        $user_role_data=$user_role->where(array('id'=>$id))->find();
        $user_basic=M('user_basic');
        $user_basic_data=$user_basic->where(array('unionid'=>$user_role_data['unionid']))->find();
        //修改user_roel 表
        $clear_data=array(
            'money_rest'=>0,
            'num_dish_rest'=>0,
            'num_order_rest'=>0,
        );
        $result=$user_role->where(array('id'=>$user_role_data['id']))->save($clear_data);
        //插入结算记录
        $clear_histroy=array(
            'time_clear'=>time(),
            'admin_id'=>session('data')['role_id'],
            'admin_name'=>session('data')['name'],
            'user_id'=>$id,
            'user_name'=>$user_basic_data['name'],
            'role'=>$role,
            'bonus'=>$this->getBonus($role,$user_role_data['num_order_rest'],$user_role_data['num_dish_rest'],$user_role_data['school_ext']),
            'money_total'=>$this->getUserRoleMoney($role,$user_role_data['school_ext'],$user_role_data['num_order_total'],$user_role_data['num_dish_total']),
            'money_rest'=>$this->getUserRoleMoney($role,$user_role_data['school_ext'],$user_role_data['num_order_rest'],$user_role_data['num_dish_rest']),
            'num_order_total'=>$user_role_data['num_order_total'],
            'num_order_rest'=>$user_role_data['num_order_rest'],
            'num_dish_rest'=>$user_role_data['num_dish_rest'],
            'num_dish_total'=>$user_role_data['num_dish_total'],
        );
        $result2=$balance_user->add($clear_histroy);
        if($result&&$result2&&$user_basic_data&&$user_role_data){
            $user_role->commit();
            $files= fopen("Public/log/usrClear.txt", "a") or die("Unable to open file!");
            // $str=date('Y-m-d H:i')."\t".time()."\t".session('data')['name']."\t".session('data')['role_id']."\t".$user_role_data['id']."\t".$user_basic_data['name']."\t".$user_role_data['money_total']."\t".$user_role_data['money_rest']."\t".$user_role_data['num_dish_total'];
            $str=json_encode($clear_histroy);
            fwrite($files,$str."\n");
            fclose($files);
            $msg['code']=200;
            $this->ajaxReturn($msg);
        }else{
            $user_role->rollback();
            $msg['code']=596;
            $this->ajaxReturn($msg);
        }
    }
    //获取业务员结算历史接口
    public function getUserHistory($user_id,$role){
        $history=M('balance_user');
        $history_data=$history->where(array('user_id'=>$user_id,'role'=>$role))->order(array('time_clear'=>'desc'))->select();
        $m_role=M('role');
        $user_role=M('user_role');
        foreach ($history_data as &$value) {
            $value['date']=date('Y-m-d H:i',$value['time_clear']);
            $value['role_name']=$m_role->where(array('id'=>$role))->find()['name'];
            $v=$user_role->where(array('id'=>$value['user_id']))->find();
            $value['alipay']=$v['alipay'];
        }
        if($history_data){
            $msg['code']=200;
            $msg['data']=$history_data;
        }else{
            $msg['code']=597;
        }
        return $msg;
    }
    //处理user_role 和 user_basic 联级查询出来的数据 添加内容
    public function doData($data,$role=0){
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
                // $value['school_ext']=$school_ext_data['id'];
                $school_data=$school->where(array('id'=>$school_ext_data['school']))->find();
                $value['school']=$school_data['id'];
                $value['school_name']=$school_data['name'];
                if($value['status']==1){
                    $value['status_name']='工作';
                }elseif($value['status']==2){
                    $value['status_name']='休息';
                }
                //计算金额
                // $value['money_rest_2']=$this->getBulidLearderMoney($value['num_order_rest'],$value['num_dish_rest'],$value['school_ext']);
                // $value['money_total_2']=$this->getBulidLearderMoney($value['num_order_total'],$value['num_dish_total'],$value['school_ext']);
                $value['money_rest_1']=$this->getUserRoleMoney($role,$value['school_ext'],$value['num_order_rest'],$value['num_dish_rest']);
                $value['money_total_1']=$this->getUserRoleMoney($role,$value['school_ext'],$value['num_order_total'],$value['num_dish_total']);
                $value['bonus']=$this->getBonus($role,$value['num_order_rest'],$value['num_dish_rest'],$value['school_ext']);

            }
            return $data;
    }
    //获取指定角色 指定校区的 业务员的数据
    public function getEmployeeInfo($role=0,$school_ext=0){
        $Model = new \Think\Model();
        // $sql="SELECT * FROM/ `bd_user_role`,`bd_user_basic` WHERE `bd_user_basic`.`unionid`=(SELECT * FROM `bd_user_role` WHERE `role`=7 AND `role_ext`=$school_ext).`unionid`";
        // $sql1="SELECT `b`.unionid,`b`.`role`,`b`.`mobile`,`b`.`alipay`,`b`.`comment`,`b`.`status`,`a`.`name`,`a`.``building,`` FROM `bd_user_basic` as b,(SELECT * FROM `bd_user_role` WHERE `role`=7) as a WHERE `bd_user_basic`.`unionid`=a.`unionid`";
        // $sql="SELECT * FROM (SELECT * FROM `bd_user_role` WHERE `role`=$role AND `status` IN(1,2) AND `school_ext`=$school_ext ) as a ,`bd_user_basic` WHERE `bd_user_basic`.`unionid`=a.`unionid`";
        // $data=$Model->query($sql);
        $user_role=M('user_role');
        $data=$user_role->join(
            '`bd_user_basic` ON `bd_user_basic`.`unionid`=`bd_user_role`.`unionid`'
        )->field(array(
            '`bd_user_basic`.`name`'=>'name',
            '`bd_user_basic`.`building`'=>'building',
            '`bd_user_role`.`id`'=>'id',
            '`bd_user_role`.`mobile`'=>'mobile',
            '`bd_user_role`.`num_order_rest`'=>'num_order_rest',
            '`bd_user_role`.`num_order_total`'=>'num_order_total',
            '`bd_user_role`.`num_dish_rest`'=>'num_dish_rest',
            '`bd_user_role`.`num_dish_total`'=>'num_dish_total',
            '`bd_user_role`.`school_ext`'=>'school_ext',
            '`bd_user_role`.`alipay`'=>'alipay',
        ))->where(array('`bd_user_role`.`role`'=>$role,'`bd_user_role`.`school_ext`'=>$school_ext,'`bd_user_role`.`status`'=>array('in','0,1,2')))->select();
        $data=$this->doData($data,$role);
        return $data;
    }
    /**        餐厅结算    */
    public function canteen(){
        $this->assign('url',array(
            'getinfo'=>U('Admin/Balance/canteenGetInfo'),
            'history'=>U('Admin/Balance/canteenHistory'),
            'clear'=>U('Admin/Balance/canteenClear'),
            'download'=>U('Admin/Balance/canteenDownload')
        ));
        $this->assign('admin',session('data'));
        // $t=time()*1000+24*3600*1000;
        $t=time()*1000;
        $e=time()*1000-30*24*3600*1000;
        $this->assign('date',array(
            'd_start'=>$e,
            'd_end'=>$t,
        ));
        $data=$this->roleCtrl();
        if(!$data){
            $msg['code']=501;
            $this->ajaxReturn($msg);
        }
        $this->assign($data);
        $this->display('canteen');
    }
    //获取指定餐厅 知道时间段的 金额
    public function canteenGetInfo(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $canteen_id=$post->canteen;
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $port=M('canteen_port');
        $port_data=$port->where(array('canteen'=>$canteen_id))->select();
        $port_str=$this->arrayToIn($port_data);
        $data=$this->getCanteenDaily($date_start,$date_end,$port_str);
        $msg=array(
            'total'=>0,
            'num'=>0,
        );
        foreach ($data as $value) {
            $msg['total']+=$value['total'];
            $msg['total_cost']+=$value['total_cost'];
            $msg['num']+=$value['num'];
        }
        $msg['data']=$data;
        $msg['code']=200;
        $this->ajaxReturn($msg);
    }
    //获取餐厅结算历史记录
    public function canteenHistory(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $canteen_id=$post->canteen;
        // $school_ext=$post->school_ext;
        $canteen=M('balance_canteen');
        $canteen_data=$canteen->where(array('id'=>$canteen_id))->order('`time_clear` DESC')->find();
        // if($school_ext!=$canteen_data['school_ext']){
        //     $msg['code']=545;
        //     $this->ajaxReturn($msg);
        // }
        $balance_canteen=M('balance_canteen');
        $data=$balance_canteen->where(array('canteen_id'=>$canteen_id))->select();
        foreach ($data as &$value) {
            $value['date']=date('Y-m-d H:i',$value['time_clear']);
            $value['date_start']=date('Y-m-d',$value['time_start']);
            $value['date_end']=date('Y-m-d',$value['time_end']);
        }
        $msg['code']=200;
        $msg['data']=$data;
        $this->ajaxReturn($msg);
    }
    //结算接口
    public function canteenClear(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $canteen_id=$post->canteen_id;
        $canteen_name=$post->canteen_name;
        $total=$post->total;
        $num=$post->num;
        $time_start=strtotime($date_start);
        $time_end=strtotime($date_end);
        if(strtotime($date_end)>time()){
            $msg['code']=607;
            $msg['error']='结算日期一定要选择今天或是今天之前';
            $this->ajaxReturn($msg);
        }
        $balance_canteen=M('balance_canteen');
        $data=array(
            'canteen_name'=>$canteen_name,
            'canteen_id'=>$canteen_id,
            'admin_id'=>session('data')['role_id'],
            'admin_name'=>session('data')['name'],
            'time_clear'=>time(),
            'time_start'=>$time_start,
            'time_end'=>$time_end,
            'money_total'=>$total,
            'num_dish_total'=>$num,
        );
        $history=$balance_canteen->where(array('canteen_id'=>$canteen_id))->select();

        if(!$history){
            $result=$balance_canteen->add($data);
            if($result){
                $msg['code']=200;
            }else{
                $msg['code']=595;
            }
            $this->ajaxReturn($msg);
        }
        if($history[0]['time_end']==$time_start){
            $result=$balance_canteen->add($data);
            if($result){
                $msg['code']=200;
            }else{
                $msg['code']=596;
            }
            $this->ajaxReturn($msg);
        }else{
            $msg['code']=597;
            $msg['error']='请从上次结算截止日期开始结算';
            $this->ajaxReturn($msg);
        }
    }
    public function canteenDownload($data){
        // $postData=file_get_contents('php://input',true);
        // $postData=I('data');
        $post=json_decode($data);
        $canteen_id=$post->canteen;
        $canteen_name=$post->canteen_name;
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $port=M('canteen_port');
        $port_data=$port->where(array('canteen'=>$canteen_id))->select();
        $port_str=$this->arrayToIn($port_data);
        $dish=$this->getBalancePortDish(strtotime($date_start),strtotime($date_end),$port_str);
        // $dish=$this->getBalancePortDish("2016-4-8","2016-4-10",$port_str);
        $data=$this->getCanteenDaily($date_start,$date_end,$port_str);
        //获取指定日期内 订单详情
        $order = $this->getBalancePortOrder(strtotime($date_start),strtotime($date_end),$port_str);
        //获取全部菜品
        $download=A('Excel');
        $school=M('school');
        $school_ext=M('school_ext');
        $canteen=M('canteen');
        $date_s=date('Y-m-d',strtotime($date_start));
        $date_e=date('Y-m-d',strtotime($date_end));
        $canteen_data=$canteen->where(array('id'=>$canteen_id))->find();
        $school_ext_data=$school_ext->where(array('id'=>$canteen_data['school_ext']))->find();
        $school_data=$school->where(array('id'=>$school_ext_data['school']))->find();
        $excel_name=$school_data['name'].$school_ext_data['name']."-$canteen_name-$date_s-$date_e.xlsx";
        $download->downloadBalanceCanteen2($data,$dish,$order,$excel_name);
        // $msg['code']=200;
        // $msg=$order;
        // $this->ajaxReturn($msg);
    }
    /**
        校区拨款页面
    */
    public function schoolClear(){
        $this->assign('url',array(
            'getinfo'=>U('Admin/Balance/schoolClearGetInfo'),
            'clear'=>U('Admin/Balance/schoolClearClear'),
            'history'=>U('Admin/Balance/schoolClearHistory'),
            'download'=>U('Admin/Balance/schoolClearDownload'),
        ));
        // $t=time()*1000+24*3600*1000;
        $t=time()*1000;
        $e=time()*1000-30*24*3600*1000;
        $this->assign('date',array(
            'd_start'=>$e,
            'd_end'=>$t,
        ));
        $data=$this->roleCtrl();
        if(!$data){
            $msg['code']=501;
            $this->ajaxReturn($msg);
        }
        $this->assign($data);
        $this->display('schoolclear');
    }
    //获取拨款页面信息 结算总金额 和 结算历史
    public function schoolClearGetInfo(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $school_ext_id=$post->school_ext;
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $role=$post->role;
        if(empty($school_ext_id)||empty($date_start)||empty($date_end)){
            $msg['code']=620;
            $msg['data']=$post;
            $this->ajaxReturn($msg);
        }
        $school_ext=M('school_ext');
        // $school=M('school');
        $data=array(
            'money'=>0,
            'num_order'=>0,
            'num_dish'=>0,
            'bonus'=>0,
        );
        $user_role=M('user_role');
        $user_role_data=$user_role->where(array('school_ext'=>$school_ext_id,'status'=>array('in','0,1,2'),'role'=>$role))->select();
        $user_role_str=$this->arrayToIn($user_role_data);
        $data['data']=$this->getBalanceSchool(strtotime($date_start),strtotime($date_end),$user_role_str,"$role");
        $this->doSchoolData($data);
        $data['code']=200;
        $this->ajaxReturn($data);
    }
    public function getBalanceSchool($time_start,$time_end,$user_role_str,$role){
        $balance_user=M('balance_user');
        $rules=array(
            'time_clear'=>array('between',"$time_start,$time_end"),
            'role'=>array('in',"$role"),
            'user_id'=>array('in',$user_role_str),
        );
        $data=$balance_user->where($rules)->select();
        return $data;
    }
    public function doSchoolData(&$data){
        $user_role=M('user_role');
        $school_ext=M('school_ext');
        $school=M('school');
        $role=M('role');
        foreach ($data['data'] as &$value) {
            $u=$user_role->where(array('id'=>$value['user_id']))->find();
            $e=$school_ext->where(array('id'=>$u['school_ext']))->find();
            $school_data=$school->where(array('id'=>$e['school']))->find();
            $value['school_name']=$school_data['name'];
            $value['school_ext_name']=$e['name'];
            $data['bonus']+=$value['bonus'];
            $data['money']+=$value['money_rest'];
            $data['num_order']+=$value['num_order_rest'];
            $data['num_dish']+=$value['num_dish_rest'];
            //角色
            $role_data=$role->where(array('id'=>$u['role']))->find();
            $value['role_name']=$role_data['name'];
            //时间
            $value['date_clear']=date('Y-m-d H:i',$value['time_clear']);
        }
        return ;
    }
    //结算接口
    public function schoolClearClear(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $num_order=$post->num_order;
        $num_dish=$post->num_dish;
        $money=$post->money;
        $bonus = $post->bonus;
        $school_ext=$post->school_ext;
        $school_ext_name=$post->school_ext_name;
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $role=$post->role;
        // if(strtotime($date_end)>time()){
        //     $msg['code']=607;
        //     $msg['error']='结算日期一定要选择今天或是今天之前';
        //     $this->ajaxReturn($msg);
        // }
        $balance=M('balance_school_ext');
        $data=array(
            'school_ext'=>$school_ext,
            'school_ext_name'=>$school_ext_name,
            'role'=>$role,
            'time_clear'=>time(),
            'admin_id'=>session('data')['role_id'],
            'admin_name'=>session('data')['name'],
            'time_start'=>strtotime($date_start),
            'time_end'=>strtotime($date_end),
            'money'=>$money,
            'num_dish'=>$num_dish,
            'num_order'=>$num_order,
            'bonus'=>$bonus,
        );
        $verify=array(
            array('time_clear','number','时间必须数子'),
            array('admin_id','number','管理员id'),
            array('admin_name','require','name'),
            array('time_start','number','start'),
            array('time_end','number','end'),
            array('school_ext','number','school'),
            array('school_ext_name','require','school_name'),
            array('role','number','role'),
            array('money','number','money'),
            array('bonus','number','bonus'),
            array('num_dish','number','num_dish'),
            array('num_order','number','num_dish'),
        );
        if(!$balance->validate($verify)->create($data)){
            $msg['code']=624;
            $msg['error']=$balance->getError();
            $this->ajaxReturn($msg);
        }
        $balance_data=$balance->where(array("school_ext"=>$school_ext,'role'=>$role))->order('time_end desc')->select();
        if($balance_data){
            if($balance_data[0]['time_end']!=strtotime($date_start)){
                $msg['code']=625;
                $msg['error']='请从上次结算结束时间开始结算';
                $this->ajaxReturn($msg);
            }
        }
        $result=$balance->add($data);
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=623;
        }
        $this->ajaxReturn($msg);
    }
    //获取拨款历史接口 balance_school_ext
    public function schoolClearHistory(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $school_ext=$post->school_ext;
        $role=$post->role;
        $balance=M('balance_school_ext');
        $m_school_ext=M('school_ext');
        $m_school=M('school');
        $balance_data=$balance->where(array("school_ext"=>$school_ext,'role'=>$role))->order('time_end desc')->select();
        foreach ($balance_data as &$value) {
            $value['date_clear']=date('Y-m-d H:i',$value['time_clear']);
            $value['date_start']=date('Y-m-d',$value['time_start']);
            $value['date_end']=date('Y-m-d',$value['time_end']);
            $e=$m_school_ext->where(array('id'=>$value['school_ext']))->find();
            $v=$m_school->where(array('id'=>$e['school']))->find();
            $value['school']=$v['name'];
        }
        $data['data']=$balance_data;
        $data['code']=200;
        $this->ajaxReturn($data);
    }
    //下载学校楼长结算账单
    public function schoolClearDownload($data){
        // $postData=I('data');
        // print_r(I('data'));
        $post=json_decode($data);
        // dump($post,1,'<pre>');
        $school_ext_id=$post->school_ext;
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $role = $post->role;
        $role_name = $post->role_name;
        if(empty($school_ext_id)||empty($date_start)||empty($date_end)){
            $msg['code']=620;
            $msg['data']=$post;
            $this->ajaxReturn($msg);
        }
        $school_ext=M('school_ext');
        // $school=M('school');
        $data=array(
            'money'=>0,
            'num_order'=>0,
            'num_dish'=>0,
        );
        $user_role=M('user_role');
        $user_role_data=$user_role->where(array('school_ext'=>$school_ext_id,'status'=>array('in','0,1,2'),'role'=>$role))->select();
        $user_role_str=$this->arrayToIn($user_role_data);
        $data['data']=$this->getBalanceSchool(strtotime($date_start),strtotime($date_end),$user_role_str,"$role");
        $this->doSchoolData($data);
        //下载账单
        $school=M('school');
        // $school_ext=M('school_ext');
        $date_s=date('Y-m-d',strtotime($date_start));
        $date_e=date('Y-m-d',strtotime($date_end));
        $school_ext_data=$school_ext->where(array('id'=>$school_ext_id))->find();
        $school_data=$school->where(array('id'=>$school_ext_data['school']))->find();
        $excel=A('Excel');
        $name_excel=$school_data['name'].$school_ext_data['name']."\ ".$date_s."-".$date_e."校园结算账单";
        $excel->downloadBalanceSchoolClear($data,$name_excel);
    }
    //必点送 结算页面
    public function bddeliver(){
        $this->assign('url',array(
            'getinfo'=>U('Admin/Balance/bddeliverGetInfo'),
            'clear'=>U('Admin/Balance/bddeliverClear'),
            'history'=>U('Admin/Balance/bddeliverHistory'),
            'download'=>U('Admin/Balance/bddeliverDownload'),
        ));
        $data=$this->roleCtrl();
        if(!$data){
            $msg['code']=501;
            $this->ajaxReturn($msg);
        }
        $this->assign($data);
        $this->display('bddeliver');
    }
    //获取必点送 的结算金额
    public function bddeliverGetInfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $province=$post->province;
        $school=$post->school;
        $school_ext=$post->school_ext;
        // $canteen=$post->canteen;
        // dump($school_ext);
        if((!(is_numeric($school_ext))||empty($school_ext))){
            $msg['code']='512';
            $msg['error']='滚!';
            $this->ajaxReturn($msg);
        }
        $data=$this->getEmployeeInfo(11,$school_ext);
        if($data){
            $msg['code']=200;
            $msg['data']=$data;
        }else{
            $msg['code']=515;
        }
        $this->ajaxReturn($msg);
    }
    //必点送结算接口
    public function bddeliverClear(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        if(empty($id)||(!is_numeric($id))){
            $msg['code']=514;
            $msg['error']="滚";
            $this->ajaxReturn($msg);
        }
        $result=$this->userClear($id,11);
        if($result['code']==200){
            $msg['code']=200;
        }else{
            $msg['code']=598;
            $msg['error']=$result;
        }
        $this->ajaxReturn($msg);
    }
    //必点送获取历史结算接口
    public function bddeliverHistory(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $user_id=$post->id;
        $d=$this->getUserHistory($user_id,11);
        if($d['code']==200){
            $msg['code']=200;
            $msg['data']=$d['data'];
        }else{
            $msg['code']=599;
            $msg['error']=$d;
        }
        $this->ajaxReturn($msg);
    }
    //各个业务员的金额结算
    public function getUserRoleMoney($role,$school_ext,$num_order,$num_dish){
        switch ($role) {
            case '1':
                return $this->getBulidLearderMoney($num_order,$num_dish,$school_ext);
                break;
            case '11':
                return $this->getBddeliverMoney($school_ext,$num_order,$num_dish);
            default:
                # code...
                break;
        }
    }
    //获取必点送 的金额(分)
    public function getBddeliverMoney($school_ext,$num_order,$num_dish){
        $money = $num_order*150+($num_dish-$num_order)*30+$this->getBonus('11',$num_order,$num_dish);
        return $money;
    }
    //获取业务员的奖金
    public function getBonus($role,$num_order,$num_dish,$school_ext=0){
        switch ($role) {
            case '11':
                if($num_order>=0&&$num_order<70){
                    $bonus=0;
                }elseif($num_order>=70&&$num_order<100){
                    $bonus=1000;
                }elseif($num_order>=100&&$num_order<140){
                    $bonus=2000;
                }elseif($num_order>=140&&$num_order<200){
                    $bonus=3000;
                }elseif($num_order>=200&&$num_order<280){
                    $bonus=5000;
                }elseif($num_order>=280){
                    $bonus=10000;
                }else{
                    $bonus=0;
                }
                break;
            default:
                $bonus=0;
                break;
        }
        return $bonus;
    }
}
