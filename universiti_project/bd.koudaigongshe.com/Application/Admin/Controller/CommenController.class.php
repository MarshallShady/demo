<?php
namespace Admin\Controller;
use Think\Controller;
class CommenController extends Controller{
    protected function _initialize(){
        if(session('status')!='on'){
            $this->success('请重新登入',U('Admin/login/login'));
        }
    }
    //权限控制
    protected function roleCtrl(){
        $role=session('data')['role'];
        $school_ext=session('data')['school_ext'];
        $canteen=session('data')['canteen'];
        $port=session('data')['port'];
        $school=session('data')['school'];;
        $province=session('data')['province'];
        $data=$this->getListInfo($port,$canteen,$school_ext,$school,$province);
        return $data ;
    }
    //获取指定地址的列表 测试ok
    protected function getListInfo($port='all',$canteen='all',$school_ext='all',$school='all',$province='all'){
        $m_province=M('dict_province');
        $m_school=M('school');
        $m_school_ext=M('school_ext');
        $m_canteen=M('canteen');
        $m_port=M('canteen_port');
        //指定档口的列表
        if($port!='all'){
            $port_data[0]=$m_port->where(array('id'=>$port,'status'=>array('in','1,2')))->find();
            $canteen_data[0]=$m_canteen->where(array('id'=>$port_data[0]['canteen'],'status'=>array('in','1,2')))->find();
            $school_ext_data[0]=$m_school_ext->where(array('id'=>$canteen_data[0]['school_ext'],'status'=>array('in','1,2')))->find();
            $school_data[0]=$m_school->where(array('id'=>$school_ext_data[0]['school'],'status'=>array('in','1,2')))->find();
            $province_data[0]=$m_province->where(array('id'=>$school_data[0]['province'],'status'=>array('in','1,2')))->find();
        }
        //指定餐厅的列表
        elseif($canteen!='all'){
            $port_data=$m_port->where(array('canteen'=>$canteen,'status'=>array('in','1,2')))->select();
            $canteen_data[0]=$m_canteen->where(array('id'=>$canteen,'status'=>array('in','1,2')))->find();
            $school_ext_data[0]=$m_school_ext->where(array('id'=>$canteen_data[0]['school_ext'],'status'=>array('in','1,2')))->find();
            $school_data[0]=$m_school->where(array('id'=>$school_ext_data[0]['school'],'status'=>array('in','1,2')))->find();
            $province_data[0]=$m_province->where(array('id'=>$school_data[0]['province'],'status'=>array('in','1,2')))->find();
        }
        //指定校区的列表
        elseif($school_ext!='all'){
            // $canteen_data=$m_canteen->where(array('school_ext'=>$school_ext,'status'=>array('in','1,2')))->select();
            // $school_ext_data[0]=$m_school_ext->where(array('id'=>$school_ext,'status'=>array('in','1,2')))->find();
            // $school_data[0]=$m_school->where(array('id'=>$school_ext_data[0]['school'],'status'=>array('in','1,2')))->find();
            // $province_data[0]=$m_province->where(array('id'=>$school_data[0]['province'],'status'=>array('in','1,2')))->find();
            $canteen_data=$m_canteen->where(array('school_ext'=>array('in',$school_ext),'status'=>array('in','1,2')))->select();
            $school_ext_data=$m_school_ext->where(array('id'=>array('in',$school_ext),'status'=>array('in','1,2')))->select();
            $school_ext_str = $this->arrayToIn2($school_ext_data,'school');
            $school_data=$m_school->where(array('id'=>array('in',$school_ext_str),'status'=>array('in','1,2')))->select();
            $province_str = $this->arrayToIn2($school_data,'province');
            $province_data=$m_province->where(array('id'=>array('in',$province_str),'status'=>array('in','1,2')))->select();
            $port_data=array();
            foreach ($canteen_data as  $value) {
                $p=$m_port->where(array('canteen'=>$value['id'],'status'=>array('in','1,2')))->select();
                $port_data=array_merge($port_data,$p);
            }
        }
        //全部列表
        elseif($school!='all'){
            // $province_data=$m_province->where(array('status'=>array('in','1,2')))->select();
            // $school_data=$m_school->where(array('status'=>array('in','1,2')))->select();
            // $school_ext_data=$m_school_ext->where(array('status'=>array('in','1,2')))->select();
            // $canteen_data=$m_canteen->where(array('status'=>array('in','1,2')))->select();
            // $port_data=$m_port->where(array('status'=>array('in','1,2')))->select();
        }elseif ($province!='all') {
            $province_data=$m_province->where(array('status'=>array('in','1,2'),'id'=>$province))->select();
            $school_data=$m_school->where(array('status'=>array('in','1,2'),'province'=>$province_data[0]['id']))->select();
            $school_str=$this->arrayToIn($school_data);
            $school_ext_data=$m_school_ext->where(array('status'=>array('in','1,2'),'school'=>array('in',$school_str)))->select();
            $school_ext_str=$this->arrayToIn($school_ext_data);
            $canteen_data=$m_canteen->where(array('status'=>array('in','1,2'),'school_ext'=>array('in',$school_ext_str)))->select();
            $canteen_str=$this->arrayToIn($canteen_data);
            $port_data=$m_port->where(array('status'=>array('in','1,2'),'canteen'=>array('in',$canteen_str)))->select();
        }elseif($province=='all'){
            $province_data=$m_province->where(array('status'=>array('in','1,2')))->select();
            $school_data=$m_school->where(array('status'=>array('in','1,2')))->select();
            $school_ext_data=$m_school_ext->where(array('status'=>array('in','1,2')))->select();
            $canteen_data=$m_canteen->where(array('status'=>array('in','1,2')))->select();
            $port_data=$m_port->where(array('status'=>array('in','1,2')))->select();
        }
        //必须都要有值
        if($province_data&&$school_data&&$school_ext_data){
            $data=array(
                'province'=>$province_data,
                'school'=>$school_data,
                'school_ext'=>$school_ext_data,
                'canteen'=>$canteen_data,
                'port'=>$port_data,
            );
            return $data;
        }else{
            return false;
        }
    }
    //获取指定楼栋的信息
    protected function getBuildInfo($build=0,$school_ext='all',$school='all',$province='all'){
        $m_province=M('dict_province');
        $m_school=M('school');
        $m_school_ext=M('school_ext');
        $m_build=M('school_building');
        //指定楼栋的列表
        if($build!='all'){
            $build_data[0]=$m_build->where(array('id'=>$build,'status'=>array('in','1,2')))->find();
            $school_ext_data[0]=$m_school_ext->where(array('id'=>$build_data[0]['school_ext'],'status'=>array('in','1,2')))->find();
            $school_data[0]=$m_school->where(array('id'=>$school_ext_data[0]['school'],'status'=>array('in','1,2')))->find();
            $province_data[0]=$m_province->where(array('id'=>$school_data[0]['province'],'status'=>array('in','1,2')))->find();
        }
        //指定校区的列表
        elseif($school_ext!='all'){
            // $build_data=$m_build->where(array('school_ext'=>$school_ext,'status'=>array('in','1,2')))->select();
            // $school_ext_data[0]=$m_school_ext->where(array('id'=>$school_ext,'status'=>array('in','1,2')))->find();
            // $school_data[0]=$m_school->where(array('id'=>$school_ext_data[0]['school'],'status'=>array('in','1,2')))->find();
            // $province_data[0]=$m_province->where(array('id'=>$school_data[0]['province'],'status'=>array('in','1,2')))->find();
            $school_ext_data=$m_school_ext->where(array('id'=>array('in',$school_ext),'status'=>array('in','1,2')))->select();
            $school_ext_str = $this->arrayToIn2($school_ext_data,'school');
            $build_data=$m_build->where(array('school_ext'=>array('in',$school_ext),'status'=>array('in','1,2')))->select();
            $school_data=$m_school->where(array('id'=>array('in',$school_ext_str),'status'=>array('in','1,2')))->select();
            $province_str = $this->arrayToIn2($school_data,'province');
            $province_data=$m_province->where(array('id'=>array('in',$province_str),'status'=>array('in','1,2')))->select();
        }
        //全部列表
        elseif($school!='all'){
            $province_data=$m_province->where(array('status'=>array('in','1,2')))->select();
            $school_data=$m_school->where(array('status'=>array('in','1,2')))->select();
            $school_ext_data=$m_school_ext->where(array('status'=>array('in','1,2')))->select();
            $build_data=$m_build->where(array('status'=>array('in','1,2')))->select();
        }elseif($province!='all'){
            $province_data=$m_province->where(array('status'=>array('in','1,2'),'id'=>$province))->select();
            $school_data=$m_school->where(array('status'=>array('in','1,2'),'province'=>$province_data[0]['id']))->select();
            $school_str=$this->arrayToIn($school_data);
            $school_ext_data=$m_school_ext->where(array('status'=>array('in','1,2'),'school'=>array('in',$school_str)))->select();
            $school_ext_str=$this->arrayToIn($school_ext_data);
            $build_data=$m_build->where(array('status'=>array('in','1,2'),'school_ext'=>array('in',$school_ext_str)))->select();
        }elseif($province=='all'){
            $province_data=$m_province->where(array('status'=>array('in','1,2')))->select();
            $school_data=$m_school->where(array('status'=>array('in','1,2')))->select();
            $school_ext_data=$m_school_ext->where(array('status'=>array('in','1,2')))->select();
            $build_data=$m_build->where(array('status'=>array('in','1,2')))->select();
        }
        //必须都要有值
        if($province_data&&$school_data&&$school_ext_data){
            $data=array(
                'province'=>$province_data,
                'school'=>$school_data,
                'school_ext'=>$school_ext_data,
                'build'=>$build_data,
            );
            return $data;
        }else{
            return false;
        }
    }
    //控制角色所对应可以获取的列表 从而让他们只可以查询自己对应的数据
    //测试ok
    public function roleUserCtrl(){
        $role=session('data')['role'];
        $school_ext=session('data')['school_ext'];
        $canteen=session('data')['canteen'];
        $port=session('data')['port'];
        $school=session('data')['school'];;
        $province=session('data')['province'];
        $data=$this->getBuildInfo('all',$school_ext,$school,$province);
        return $data ;
    }
    //将select 查询的数组转化成 "id1,id2"的IN查询字符串
    protected function arrayToIn($data){
        $str='';
        foreach ($data as $value) {
            $str.=$value['id'].',';
        }
        return $str;
    }
    //将select 查询的数组转化成 "id1,id2"的IN查询字符串
    protected function arrayToIn2($data,$name){
        $str='';
        foreach ($data as $value) {
            $str.=$value[$name].',';
        }
        return $str;
    }
    public function getAllList(){
        $m_province=M('dict_province');
        $m_school=M('school');
        $m_school_ext=M('school_ext');
        $m_canteen=M('canteen');
        $m_port=M('canteen_port');
        $province_data=$m_province->where(array('status'=>array('in','1,2')))->select();
        $school_data=$m_school->where(array('status'=>array('in','1,2')))->select();
        $school_ext_data=$m_school_ext->where(array('status'=>array('in','1,2')))->select();
        $canteen_data=$m_canteen->where(array('status'=>array('in','1,2')))->select();
        $port_data=$m_port->where(array('status'=>array('in','1,2')))->select();
        $data=array(
            'province'=>$province_data,
            'school'=>$school_data,
            'school_ext'=>$school_ext_data,
            'canteen'=>$canteen_data,
            'port'=>$port_data,
        );
        return $data;
    }
}
