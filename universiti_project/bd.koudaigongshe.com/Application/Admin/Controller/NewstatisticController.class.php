<?php
namespace Admin\Controller;
use Think\Controller;
class NewstatisticController extends OrderController{
    public function getHouseMasterBalance(){
        // $date_start='2016-4-0';
        // $date_end='2016-4-16';
        // $status="4,5,6,7";
        $time_start=strtotime($date_start);
        $time_end=strtotime($date_end);
        $time_end-=1;
        $user_role=M('user_role');
        $order_dish=M('order_dish');
        $order_basic=M('order_basic');
        $user_basic=M('user_basic');
        $r=array(
            'status'=>array('in','1,2'),
            'role'=>1,
            'school_ext'=>3,
        );
        $build=$user_role->where($r)->select();
        foreach ($build as &$value) {
            $value['num']=0;
            $value['num_dish']=0;
            $user_basic_data=$user_basic->where(array('unionid'=>$value['unionid']))->find();
            $value['name']=$user_basic_data['name'];
            $rules=array(
                'status'=>array('in',$status),
                // 'port'=>array('in',$port),
                'housemaster'=>array('in',$value['id']),
                'pack_time'=>array('between',"$time_start,$time_end")
            );
            $order_basic_data=$order_basic->where($rules)->select();
            foreach ($order_basic_data as $v) {
                $value['num']++;
                $d=$order_dish->where(array('order'=>$v['id'],'status'=>3))->select();
                foreach ($d as $e) {
                    $value['num_dish']++;
                }
            }
        }
        header("Content-type: application/vnd.ms-excel; charset=UTF-8");
        $filename="balance";
        header("Content-Disposition: attachment; filename=".$filename.".xls");
        $record = "名字\t手机\t支付宝\t订单数\t菜品数\n";
        foreach ($build as $value) {
            $record.=$value['name']."\t".$value['mobile']."\t".$value['alipay']."\t".$value['num']."\t".$value['num_dish']."\n";
        }
        echo $record;
        // dump($build,1,'<pre>');;
    }
}
