<?php
namespace Admin\Controller;
use Think\Controller;
class MobileController extends CommenController{
    public function order(){
        $this->assign(array('url'=>array(
            'getinfo'=>U('Admin/Api/orderDaily'),
        )));
        $data=$this->roleCtrl();
        if(!$data){
            $msg['code']=502;
            $this->ajaxReturn($msg);
        }
        $this->assign($data);
        $t=time()*1000+24*3600*1000;
        $e=time()*1000-29*24*3600*1000;
        $this->assign('date',array(
            'd_start'=>$e,
            'd_end'=>$t,
        ));
        $this->display('order');
    }
    /**
        用户分析
    */
    public function user(){
        $this->assign(array('url'=>array(
            'getinfo'=>U('Admin/Api/getUserDaily'),
        )));
        $data=$this->roleCtrl();
        if(!$data){
            $msg['code']=502;
            $this->ajaxReturn($msg);
        }
        $this->assign($data);
        $t=time()*1000+24*3600*1000;
        $e=time()*1000-29*24*3600*1000;
        $this->assign('date',array(
            'd_start'=>$e,
            'd_end'=>$t,
        ));
        $this->display('user');
    }

}
?>
