<?php
namespace Admin\Controller;
use Think\Controller;
class ApiController extends OrderController{
    public function orderDaily(){
        $postData = file_get_contents("php://input",true);
        $post = json_decode($postData);
        $school_ext=$post->school_ext;
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        // $school_ext=1;
        // $date_start="2016-4-12";
        // $date_end="2016-5-1";
        $building=M('school_building');
        $building_data=$building->where(array('school_ext'=>$school_ext))->select();
        $building_str=$this->arrayToIn($building_data);
        $data=$this->getBuildingDaily($date_start,$date_end,$building_str);
        $msg=array(
            'total'=>0,
            'num'=>0,
        );

        foreach ($data as $value) {
            $msg['total']+=$value['total'];
            $msg['num']+=$value['num'];
            $msg['s_num'][]=$value['num'];
            $msg['s_total'][]=$value['total']/100;
            $msg['s_date'][]=$value['date'];

            $msg['data'][]=array(
                'date'=>$value['date'],
                'total'=>$value['total']/100,
                'num'=>$value['num'],
                'total_all'=>$msg['total']/100,
                'num_all'=>$msg['num'],
            );
        }
        $msg['code']=200;
        $this->ajaxReturn($msg);
    }
    public function getCanteenOrder($canteen=0,$when=0,$time_start=0,$time_end=0){
        // $canteen=1;
        // $when="1,2,3";
        // $time_start=strtotime('2016-5-7');
        // $time_end=strtotime('2016-5-8');
        // $building=M('school_building');
        // $building_data=$building->where(array('school_ext'=>1))->select();
        // $building_str=$this->arrayToIn($building_data);
        // $data=$this->getOrder($time_start,$time_end,$building_str);
        // foreach ($data as $key => $value) {
        //     $res3['num']++;
        //     $res3['money']+=$value['money'];
        // }
        $time_end-=1;
        $order_basic=M('order_basic');
        $rules = array(
            'canteen'=>$canteen,
            'order_date'=>array('between',"$time_start,$time_end"),
            'status'=>array('in',"4,5,6,7,8,9,10,11,12,13"),
            'when'=>array('in',$when),
        );
        // $rules2 = array(
        //     'building'=>array('in',$building_str),
        //     'order_date'=>array('between',"$time_start,$time_end"),
        //     'status'=>array('in',"4,5,6,7,8,9,10,11,12,13"),
        //     'when'=>array('in',$when),
        // );
        $res = $order_basic->where($rules)->field('sum(money) AS money,count(*) AS num_order')->find();
        // $res2 = $order_basic->where($rules2)->field('sum(money) AS money,count(*) AS num_order')->find();
        // return $res;
        // dump($res,1,'<pre>');
        // dump($res2,1,'<pre>');
        // dump($res3,1,'<pre>');
        return $res;
    }
    //获取指定日期内 每天的日期内 的注册用户量
    public function getRU($time_start,$time_end,$school_ext){
        // $time_start = strtotime('2016-5-10');
        // $time_end = strtotime('2016-5-20');
        $time_end -= 1;
        // $school_ext= 1;
        $user_basic = M("user_basic");
        $rules1 = array(
            '`bd_user_basic`.`create_time`'=>array('between',"$time_start,$time_end"),
            '_string'=>"`bd_user_basic`.`create_time`=0 AND `bd_user_basic`.`edit_time`>=$time_start AND `bd_user_basic`.`edit_time`<=$time_end",
            '_logic'=>'or',
        );
        $rules = array(
            '_complex' =>$rules1,
            '`bd_school_building`.`school_ext`'=>array('in',"$school_ext"),
            '_logic'=>'AND',
        );
        $user_basic_data = $user_basic->where($rules)->join(
            'bd_school_building ON `bd_school_building`.`id`=`bd_user_basic`.`building`'
        )->field(array(
            'count(*) as sum'
        ))->find();
        return $user_basic_data;
    }
    //获取下过单用户
    public function getUserUsed($time_start=0,$time_end=0,$school_ext=0){
        // $time_start = strtotime('2016-5-10');
        // $time_end = strtotime('2016-5-20');
        $time_end -= 1;
        // $school_ext = "1";
        $user_basic = M("user_basic");
        // $rules1 = array(
        //     'time_order'=>array('between',"$time_start,$time_end"),
        //     '`bd_school_building`.`school_ext`'=>array('in',"1"),
        // );
        // $user_basic_data = $user_basic->where($rules1)->join(
        //     '`bd_school_building` ON `bd_school_building`.`id`=`bd_user_basic`.`building`'
        // )->join(
        //     '`bd_order_basic` ON `bd_order_basic`.`unionid`=`bd_user_basic`.`unionid`'
        // )->field(array(
        //     'count(*) as sum',
        //     'min(`bd_order_basic`.`order_date`) as time_order',
        // ))->group('`bd_user_basic`.`unionid`')->select();
        //子查询1
        $sql1= $user_basic->where(array('`bd_school_building`.`school_ext`'=>array('in',$school_ext)))->join(
            '`bd_school_building` ON `bd_school_building`.`id`=`bd_user_basic`.`building`'
        )->join(
            '`bd_order_basic` ON `bd_order_basic`.`unionid`=`bd_user_basic`.`unionid`'
        )->field(array(
            'min(`bd_order_basic`.`order_date`) as time_order',
        ))->group('`bd_user_basic`.`unionid`')->buildSql();
        $sum = $user_basic ->table($sql1.'a')->where(array('`a`.`time_order`'=>array('between',"$time_start,$time_end"),))->field(array(
            'count(*) as sum',
        ))->find();
        return $sum;
    }
    //获取用户注册信息对外接口
    public function getUserDaily(){
        $postData = file_get_contents('php://input',true);
        $post = json_decode($postData);
        $date_start = $post->date_start;
        $date_end = $post->date_end;
        $school_ext = $post->school_ext;

        $date = $this->dateCut($date_start,$date_end);
        $ru= array();
        $userUsed = array();
        $user_sum = 0;
        $user_used = 0;
        foreach ($date as $key => $value) {
            $sum = $this->getRU($value,$value+24*3600,"$school_ext");

            $user_sum +=$sum['sum'];
            $sum2 = $this->getUserUsed($value,$value+24*3600,"$school_ext");
            // $userUsed[] = array(
            //     'date'=>date('Y-m-d',$value),
            //     'sum'=>$sum2['sum'],
            // );
            $data_date[]=date('Y-m-d',$value);
            $data_sum[]=$sum['sum'];
            $data_sum_used[]=$sum2['sum'];
            $user_used += $sum2['sum'];
            $ru[] = array(
                'date'=>date('Y-m-d',$value),
                'sum'=>$sum['sum'],
                'sum_used'=>$sum2['sum'],
                'total'=>$user_sum,
                'total_used'=>$user_used,
            );

        }
        $data= array(
            'total'=>$user_sum,
            'total_used'=>$user_used,
            'data_date'=>$data_date,
            'data_sum'=>$data_sum,
            'data_sum_used'=>$data_sum_used,
            'data'=>$ru,
            'code'=>200,
        );
        $this->ajaxReturn($data);

    }
}
?>
