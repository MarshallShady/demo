<?php
namespace Admin\Controller;
use Think\Controller;
/**
    基础订单类
    里面有各种与订单相关的接口
*/
class OrderbasicController extends CommenController{
    //订单时间分隔为00：00 点
    //日期下 日期上  状态 省 学校 校区 餐厅 档口
    public function getOrderBusiness($date_start='2016-04-02',$date_end='2016-04-04',$port=0,$canteen=0,$school_ext=1,$school=0,$province=0){
        //切割时间
        $date=$this->dateCut($date_start,$date_end);
        $order_dish=M('order_dish');
        $port_str=$this->getPort($port,$canteen,$school_ext);
        $data=array();
        foreach ($date as $key => $value) {
            $d2=($value+24*3600-1);
            $rules=array(
                'port'=>array('in',$port_str,'AND'),
                'pack_time'=>array('between',"$value,$d2"),
                'status'=>array('in','3,6','AND'),
                // '_logic'=>'AND',
            );
            $order_dish_data=$order_dish->where($rules)->select();
            $data[]=$this->doBusinessData($order_dish_data,$value);
        }
        return $data;

    }
    //将一段日期 分割成一天天 一个数组
    protected function dateCut($date_start,$date_end){
        //对2015-01-02 进行转换成时间戳
        $time_start=strtotime($date_start);
        $time_end=strtotime($date_end);
        $length=(int)(($time_end-$time_start)/(24*3600));
        $date=array();
        for ($i=0; $i<$length; $i++) {
            $date[]=$time_start+$i*24*3600;
        }
        return $date;
    }
    //获取地址指定的所有档口  返回 "id,id2," 的形式 为 IN 查询做准备
    public function getPort($port=0,$canteen=0,$school_ext=0,$school=0,$province=0){
        $m_port=M('canteen_port');
        $m_canteen=M('canteen');
        if($port!='all'){//获取该档口的菜品
            $data=$port.',';
        }elseif($canteen!='all'){//获取该餐厅下所有档口的菜品
            $m_port_data=$m_port->where(array('canteen'=>$canteen))->select();
            $data=$this->arrayToIn($m_port_data);
        }elseif($school_ext!='all'){//获取该校区下所有餐厅对应档口的所有菜品
            $m_canteen_data=$m_canteen->where(array('school_ext'=>$school_ext))->select();
            $canteen_id='';
            foreach ($m_canteen_data as  $value) {
                $canteen_id.=$value['id'].',';
            }
            $m_port_data=$m_port->where(array('canteen'=>array('in',$canteen_id)))->select();
            $data=$this->arrayToIn($m_port_data);
        }else{
            $data=false;
        }
        return $data;
    }
    //将select 查询的数组转化成 "id1,id2"的IN查询字符串
    protected function arrayToIn($data){
        $str='';
        foreach ($data as $value) {
            $str.=$value['id'].',';
        }
        return $str;
    }
    //将分割成一份份的order_dish的select 进行数据处理成一个数组
    protected function doBusinessData($data,$time_start){
        $total=0;
        $num=0;
        $da=array();
        $order_basic=M('order_basic');
        foreach ($data as  $value) {
            $o=$order_basic->where(array('id'=>$value['order']))->find();
            if(($o['status']>=4&&$o['status']<=7)||($o['status']>=11&&$o['status']<=13)||$o['status']==9){
                $total+=$value['money'];
                $num++;
                $da[]=$value;
            }
            // $total+=$value['money'];
            // $num++;
        }
        $result=array(
            'num'=>$num,
            'total'=>$total,
            'date'=>date('Y-m-d',$time_start),
            'data'=>$da,
        );
        return $result;
    }
    /**
        获取楼栋每天的订单信息
        操作order_basic
        5，6，7状态  出单时间 楼栋
    */
    public function getOrderUser($date_start='2016-04-02',$date_end='2016-04-04',$build=0,$school_ext=0,$school=0,$province=0){
        //切割时间
        $date=$this->dateCut($date_start,$date_end);
        $order_basic=M('order_basic');
        $build_str=$this->getBuild($build,$school_ext);
        // dump($port_str,1,'<pre>');
        $data=array();
        foreach ($date as $key => $value) {
            $d2=($value+24*3600);
            $rules=array(
                'building'=>array('in',$build_str),
                //大于小于用不来 只能用between了
                // 'pack_time'=>array('EGT',$value),
                // 'pack_time'=>array('LT',($value+24*3600)),
                'pack_time'=>array('between',"$value,$d2"),
                'status'=>array('in','5,6,7'),
            );
            $order_basic_data=$order_basic->where($rules)->select();
            $data[]=$this->doUserData($order_basic_data,$value);
        }
        return $data;

    }
    //获取楼栋列表
    public function getBuild($build,$school_ext=0,$school=0,$province=0){
        $m_build=M('school_build');
        if($build!='all'){//获取该档口的菜品
            $data=$build.',';
        }elseif($school_ext!='all'){//获取该餐厅下所有档口的菜品
            $m_build_data=$m_build->where(array('school'=>$school))->select();
            $data=$this->arrayToIn($m_build_data);
        }
        else{
            $data=false;
        }
        return $data;
    }
    //处理楼栋的订单数据
    protected function doUserData($data,$time_start){
        $total=0;
        $num=0;
        foreach ($data as  $value) {
            $total+=$value['money'];
            $num++;
        }
        $result=array(
            'num'=>$num,
            'total'=>$total,
            'date'=>date('Y-m-d',$time_start),

        );
        return $result;
    }

}
