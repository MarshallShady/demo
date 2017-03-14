<?php
namespace Admin\Controller;
use Think\Controller;
class StatisticController extends OrderbasicController{
    /**
        校园订单数据统计
    */
    public function school(){
        $this->assign('url',array(
            'getinfo'=>U('Admin/Statistic/schoolGetinfo'),
        ));
        $data=$this->getListInfo();
        if(!$data){
            $msg['code']=410;
            $this->ajaxReturn;
        }
        $this->assign($data);
        $this->display('school');
    }
    /**
        食堂订单数据统计
    */
    public function canteen(){
        $this->assign('url',array(
            'getinfo'=>U('Admin/Balance/canteenGetInfo'),
        ));
        $t=date('Y-m-d',time());
        $e=date('Y-m-d',(time()-3600*24));
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
    //食堂数据统计接口
    public function canteenGetInfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $province=$post->province;
        $school=$post->school;
        $school_ext=$post->school_ext;
        $canteen=$post->canteen;
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $data['data']=$this->getOrderBusiness($date_start,$date_end,"all",$canteen,$school_ext,$school,$province);
        $data['code']=200;
        //计算所查询的天数的所以金额
        $num=0;
        $total=0;
        foreach ($data['data'] as  $value) {
            $total+=$value['total'];
            $num+=$value['num'];
        }
        $data['total']=$total;
        $data['num']=$num;
        $this->ajaxReturn($data);
    }
    /**
        档口订单数据统计
    */
    public function port(){
        $this->assign(array('url'=>array(
            'getinfo'=>U('Admin/Statistic/portGetinfo'),
        )));
        $t=date('Y-m-d',time());
        $e=date('Y-m-d',(time()-3600*24));
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
        $this->display('port');
    }

    //档口数据查询接口
    public function portGetInfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $province=$post->province;
        $school=$post->school;
        $school_ext=$post->school_ext;
        $canteen=$post->canteen;
        $port=$post->port;
        $date_start=$post->date_start;
        $date_end=$post->date_end;

        // $data['data']=$this->getOrderBusiness($date_start,$date_end,$port,$canteen,$school_ext,$school,$province);
        // $data['code']=200;
        // //计算所查询的天数的所以金额
        // $num=0;
        // $total=0;
        // foreach ($data['data'] as  $value) {
        //     $total+=$value['total'];
        //     $num+=$value['num'];
        // }
        // $data['total']=$total;
        // $data['num']=$num;
        // $this->ajaxReturn($data);
        $order = A('order');
        $data=$order->getCanteenDaily($date_start,$date_end,$port);
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
    /**
        校园楼栋数据统计
    */
    public function build(){
        $this->assign(array('url'=>array(
            'getinfo'=>U('Admin/Statistic/buildGetinfo'),
        )));
        $t=date('Y-m-d',time());
        $e=date('Y-m-d',(time()-3600*24));
        $this->assign('date',array(
            'd_start'=>$e,
            'd_end'=>$t,
        ));
        $data=$this->roleUserCtrl();
        if(!$data){
            $msg['code']=502;
            $this->ajaxReturn($msg);
        }
        $this->assign($data);
        $this->display('build');
    }
    //获取楼栋订单信息
    public function buildGetInfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $province=$post->province;
        $school=$post->school;
        $school_ext=$post->school_ext;
        $build=$post->build;
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $data['data']=$this->getOrderBusiness($date_start,$date_end,"all",$build,$school_ext,$school,$province);
        $data['code']=200;
        //计算所查询的天数的所以金额
        $num=0;
        $total=0;
        foreach ($data['data'] as  $value) {
            $total+=$value['total'];
            $num+=$value['num'];
        }
        $data['total']=$total;
        $data['num']=$num;
        $this->ajaxReturn($data);
    }
    /*
        学校评级
    */
    public function investigate(){
        $this->assign(array('url'=>array(
            'getinfo'=>U('Admin/Statistic/investigateGetInfo'),
        )));
        $t=date('Y-m-d',time());
        $e=date('Y-m-d',(time()-3600*24));
        $this->assign('date',array(
            'd_start'=>$e,
            'd_end'=>$t,
        ));
        $data=$this->roleUserCtrl();
        if(!$data){
            $msg['code']=502;
            $this->ajaxReturn($msg);
        }
        $this->assign($data);
        $this->display('investigate');
    }
    //学校评级系统接口
    public function investigateGetInfo(){
        $postData = file_get_contents('php://input',true);
        $post = json_decode($postData);
        $temp1 = M('temp1');
        $user_basic = M('user_basic');
        $temp1_data = $temp1->select();
        foreach ($temp1_data as $key => &$value) {
            $value['date'] = date('Y-m-d',$value['create_time']);
            $user_basic_data = $user_basic->where(array('unionid'=>$value['unionid']))->find();
            $value['name'] = $user_basic_data['name'];
        }

        $msg['code'] = 200;
        $msg['data'] = $temp1_data;
        $this->ajaxReturn($msg);
    }

}
