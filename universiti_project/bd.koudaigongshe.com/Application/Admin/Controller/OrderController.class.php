<?php
namespace Admin\Controller;
use Think\Controller;
class OrderController extends CommenController{

    public function getAllOrder($status,$build,$school_ext=0,$school=0){

    }
    //根据订单号查询订单信息
    public function getOrderbyId($id=0){
        if(empty($id)){
            $msg['code']=524;
            return $msg;
        }
        $order_basic=M('order_basic');
        $order_dish=M('order_dish');
        $order_basic_data=$order_basic->where(array('id'=>$id))->find();
        if(empty($order_basic_data)){
            $msg['code']=525;
            return $msg;
        }
        $order_basic_data['dish']=$order_dish->where(array('order'=>$order_basic_data['id']))->select();
        // if(empty($order_basic_data['dish'])){
        //     $msg['code']=526;
        //     return $msg;
        // }
        $order_basic_data['code']=200;
        $data=$this->doOrderData($order_basic_data);
        // dump($order_basic_data,1,'<pre>');
        // echo "string";
        return $data;
    }
    //根据流水号查询订单信息
    public function getOrderbyPackId($id){
        if(empty($id)){
            $msg['code']=521;
            $msg['error']="无ID";
            return $msg;
        }
        $order_dish=M('order_dish');
        $order_dish_data=$order_dish->where(array('id'=>$id))->find();
        if(!$order_dish_data){
            $msg['code']=522;
            $msg['error']="无dish";
            return $msg;
        }
        $data=$this->getOrderbyId($order_dish_data['order']);
        if(!($data['code']==200)){
            $msg['code']=523;
            $msg['error']="无order";
            return $msg;
        }
        $data['code']==200;
        return $data;
    }
    //处理以订单为主的查询结果的数据 find()后的数据
    public function doOrderData($data){
        $data['date']=date("Y-m-d H:i",$data['create_time']);
        $data['date_order']=date("Y-m-d",$data['order_date']);
        switch ($data['when']) {
            case '1':
                $data['when_name']='早上';
                break;
            case '2':
                $data['when_name']="中午";
                break;
            case '3':
                $data['when_name']="晚上";
                break;
            default:
                $data['when_name']="无";
                break;
        }
        //寝室楼数据+校区
        $build=M('school_building');
        $user_basic=M('user_basic');
        $build_data=$build->where(array('id'=>$data['building']))->find();
        $data['school_ext']=$build_data['school_ext'];
        $data['build_name']=$build_data['name'];
        //分拨员
        $role_user=M('user_role');
        $role_user_1=$role_user->where(array('id'=>$data['deliver_man']))->find();
        $user_basic_1=$user_basic->where(array('unionid'=>$role_user_1['unionid']))->find();
        $data['deliver_name']=$user_basic_1['name'];
        $data['deliver_mobile']=$role_user_1['mobile'];
        //楼长
        $role_user_2=$role_user->where(array('id'=>$data['housemaster']))->find();
        $user_basic_2=$user_basic->where(array('unionid'=>$role_user_2['unionid']))->find();
        $data['housemaster_name']=$user_basic_2['name'];
        $data['housemaster_mobile']=$role_user_2['mobile'];
        //状态
        $status=M('dict_order_basic_status');
        $data['status_name']=$status->where(array('id'=>$data['status']))->find()['name'];
        //菜品
        $canteen=M('canteen');
        $canteen_port=M('canteen_port');
        foreach($data['dish'] as &$value){
            $v=$canteen_port->where(array('id'=>$value['port']))->find();
            $value['port_name']=$v['name'];
            $value['canteen_name']=$canteen->where(array('id'=>$v['canteen']))->find()['name'];
            $value['status_name']=$this->doDishStatus($value['status']);
        }
        return $data;
    }
    //获取指定时间段 和档口 order_dish的数据 port status 必须是字符
    public function getOrderDish($date_start="2016-4-8",$date_end="2016-4-9",$port="11",$status="3",$when='1,2,3'){
        $time_start=strtotime($date_start);
        $time_end=strtotime($date_end);
        $time_end-=1;
        $order_dish=M('order_dish');
        $rules=array(
            'when'=>array('in',$when),
            'status'=>array('in',$status),
            'port'=>array('in',$port),
            // 'pack_time'=>array('lt',$time_end),
            // 'pack_time'=>array('egt',1460131200,'AND'),
            // 'pack_time'=>array('between',"$time_start,$time_end"),
            'order_date'=>array('between',"$time_start,$time_end")
        );
        $order_dish_data=$order_dish->where($rules)->order('`order_date` DESC,`when` DESC ,`id` DESC ')->select();
        $data['data']=$this->doOrderDishData($order_dish_data);
        $data['total']=0;
        $data['total_cost']=0;
        $data['num']=0;
        $data['total_all']=0;
        $data['total_rest']=0;
        $data['num_rest']=0;
        $data['total_refund']=0;
        $data['num_refund']=0;
        $this->doCount($data);
        $data['code']=200;
        return $data;
    }
    //对order_dish的数据进行处理
    protected function doOrderDishData($data){
        $order_basic=M('order_basic');
        $port=M('canteen_port');
        $build=M('school_building');
        $user_role=M('user_role');
        $user_basic=M('user_basic');
        foreach ($data as &$value) {
            $value['status_dish_name']=$this->doDishStatus($value['status']);
            if($value['pack_time']==0||$value['pack_time']==null){
                $value['pack_date']='未出单';
            }else{
                $value['pack_date']=date('H:i',$value['pack_time']);
            }
            //时间
            $value['create_date']=date('y-m-d H:i',$value['create_time']);
            $value['order_date']=date('y-m-d',$value['order_date']);


            $b=$order_basic->where(array('id'=>$value['order']))->find();
            $value['order_name'] = $b['order_name'];
            $value['user_name']=$b['name'];
            $value['order_status_name']=$this->doOrderStatus($b['status']);
            $value['order_status']=$b['status'];
            $value['mobile']=$b['mobile'];
            $value['dormitory']=$b['dormitory'];
            $value['state']=$b['state'];
            //时段
            $value['when_name']=$this->doWhenData($value['when']);
            if($b['housemaster_time']==0||$b['housemaster_time']==null){
                $value['housemaster_date'] = '未送达';
                // $value['error']=$b;
                $value['time_deliver'] = 0;
            }else{
                $value['housemaster_date'] = date('H:i',$b['housemaster_time']);
                $value['time_deliver'] = (int)(($b['housemaster_time']-$b['deliver_time'])/60);
                $value['style'] = "";
                if($value['time_deliver']>=30){
                    // $value['time_deliver'] = "<span style='font-color:red;'>".$value['time_deliver']."</span>";
                    $value['style'] = "color:red;";
                }
            }
            if($b['status']==12){
                $value['style']='';
                $value['housemaster_date'] = date('y-m-d H:i',$b['deliver_time']);
                $value['time_deliver'] = ($b['deliver_time']-$b['pack_time'])/60;
                if($value['time_deliver']>=30){
                    // $value['time_deliver'] = "<span style='font-color:red;'>".$value['time_deliver']."</span>";
                    $value['style'] = "color:red;";
                }
            }
            //档口
            $c=$port->where(array('id'=>$value['port']))->find();
            $value['port_name']=$c['name'];
            //楼栋
            $d=$build->where(array('id'=>$b['building']))->find();
            $value['build_name']=$d['name'];
            //楼长
            $e=$user_role->where(array('id'=>$b['housemaster']))->find();
            $e2=$user_basic->where(array('unionid'=>$e['unionid']))->find();
            $value['housemaster_name']=$e2['name'];
            $value['housemaster_moible']=$e['mobile'];
            //分拨员
            $f=$user_role->where(array('id'=>$b['deliver_man']))->find();
            $f2=$user_basic->where(array('unionid'=>$f['unionid']))->find();
            $value['deliver_name']=$f2['name'];
            $value['deliver_mobile']=$f['mobile'];
        }
        return $data;
    }

    //处理时段
    public function doWhenData($when){
        switch ($when) {
            case '1':
                $when_name='早餐';
                break;
            case '2':
                $when_name='午餐';
                break;
            case '3':
                $when_name='晚餐';
                break;
            default:

                break;
        }
        return $when_name;
    }
    //统计多条菜品订单的金额统计
    public function doCount(&$data){
        foreach ($data['data'] as $key=>$value) {
            if(($value['order_status']>=4&&$value['order_status']<=7)||$value['order_status']==11||$value['order_status']==12||$value['order_status']==13){
                if($value['status']==2||$value['status']==3||$value['status']==6||$value['status']==8){
                    $data['total']+=$value['money'];
                    $data['total_cost']+=$value['money_cost'];
                    $data['num']++;
                }elseif($value['status']==1){
                    $data['total_rest']+=$value['money_cost'];
                    $data['num_rest']++;
                }
                // $data['error']++;
            }elseif($value['order_status']==9){
                if($value['status']==2||$value['status']==3||$value['status']==6){
                    $data['total']+=$value['money'];
                    $data['total_cost']+=$value['money_cost'];
                    $data['num']++;
                }
                $data['error']++;
                $data['num_refund']++;
                $data['total_refund']+=$value['money'];
            }elseif($value['order_status']==8){
                if($value['status']==2||$value['status']==3||$value['status']==6||$value['status']==8){
                    $data['total']+=$value['money'];
                    $data['total_cost']+=$value['money_cost'];
                    $data['num']++;
                }
            }else{
                $data['error']+=1;
            }
            if($value['state']==100){
                unset($data['data'][$key]);
            }
            if($value['port_name']=="鲜品屋"){
                unset($data['data'][$key]);
            }
        }
    }    //将一段日期 分割成一天天 一个数组
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
    //获取时间段内 每天 各个档口的总金额
    public function getCanteenDaily($date_start=0,$date_end=0,$port_str=0){
        $date=$this->dateCut($date_start,$date_end);
        $data=array();
        foreach ($date as $value) {
            $d=$this->getBalanceDish($value,$value+24*3600,$port_str,'2,3,6');
            $data[]=array(
                'total'=>$d['total'],
                'total_cost'=>$d['total_cost'],
                'num'=>$d['num'],
                'date'=>date('Y-m-d',$value),
                'num_refund'=>$d['num_refund'],
                'total_refund'=>$d['total_refund'],
                'error'=>$d['error'],
                'num_rest'=>$d['num_rest'],
                'total_rest'=>$d['total_rest'],
            );
        }
        return $data;
    }
    //获取指定时间段 和档口 order_dish的数据 port status 必须是字符
    public function getBalanceDish($time_start='0',$time_end='0',$port="11",$status="3"){
        $time_end-=1;
        $order_dish=M('order_dish');
        $rules=array(
            'status'=>array('in',$status),
            'port'=>array('in',$port),
            // 'pack_time'=>array('lt',$time_end),
            // 'pack_time'=>array('egt',1460131200,'AND'),
            'pack_time'=>array('between',"$time_start,$time_end"),
            // 'order_date'=>array('between',"$time_start,$time_end"),
        );
        $order_dish_data=$order_dish->where($rules)->select();
        $data['data']=$this->doOrderDishData($order_dish_data);
        $data['total']=0;
        $data['num']=0;
        $data['total_cost'] = 0;//跟餐厅结算的价格
        $data['total_rest']=0;
        $data['num_rest']=0;
        $data['total_refund']=0;
        $data['num_refund']=0;
        $data['error']=0;
        $this->doCount($data);
        $data['code']=200;
        return $data;
    }
    /**
        获取订单统计信息
    */
    //获取指定时间段内的订单数据
    public function getOrder($time_start='0',$time_end='0',$building="11",$status="4,5,6,7,8,9,10,11,12,13"){
        $time_end-=1;
        $order_basic=M('order_basic');
        $rules=array(
            'status'=>array('in',$status),
            'building'=>array('in',$building),
            'order_date'=>array('between',"$time_start,$time_end"),
        );
        $order_basic_data=$order_basic->where($rules)->select();
        return $order_basic_data;
    }
    //获取时间段内 每天 各个楼栋的订单数据
    public function getBuildingDaily($date_start=0,$date_end=0,$building_str=0){
        $date=$this->dateCut($date_start,$date_end);
        $data=array();
        foreach ($date as $key => $value) {
            $d=$this->getOrder($value,$value+24*3600,$building_str);
            if($d){
                $data[$key]=array(
                    'total'=>0,
                    'num'=>0,
                    'date'=>date('Y-m-d',$value),
                    'data'=>$d,
                );
            }else{
                $data[$key]=array(
                    'total'=>0,
                    'num'=>0,
                    'date'=>date('Y-m-d',$value),
                    'data'=>$d,
                );
            }
            foreach ($d as $value) {
                $data[$key]['num']++;
                $data[$key]['total']+=$value['money'];
            }
        }
        return $data;
    }
    //获取指定日期 指定餐厅下所有档口的 所有结算的菜品
    public function getBalancePortDish($time_start=0 ,$time_end=0,$port='8'){
        // $time_end=strtotime('2016-4-9');
        // $time_start=strtotime('2016-4-8');
        $time_end-=1;
        $order_dish=M('order_dish');
        $rules=array(
            '`bd_order_basic`.`status`'=>array('in','4,5,6,7,8,9,11,12,13'),
            '`bd_order_dish`.`status`'=>array('in','2,3,6'),
            '`bd_order_dish`.`pack_time`'=>array('between',"$time_start,$time_end"),
            '`bd_order_dish`.`port`'=>array('in',$port),
        );
        $order_dish_data=$order_dish->where($rules)->join(
            '`bd_order_basic` ON `bd_order_basic`.`id`=`bd_order_dish`.`order`'
        )->join('`bd_canteen_port` ON  `bd_canteen_port`.`id`=`bd_order_dish`.`port`'
        )->join('`bd_canteen` ON `bd_canteen`.`id`=`bd_canteen_port`.`canteen`'
        )->join('`bd_dict_order_basic_status` ON `bd_dict_order_basic_status`.`id`=`bd_order_basic`.`status`'
        )->field(array(
            '`bd_order_basic`.`status`'=>'basic_status',
            '`bd_dict_order_basic_status`.`name`'=>'basic_status_name',
            '`bd_order_dish`.`status`'=>'dish_status',
            '`bd_order_dish`.`name`'=>'dish_name',
            '`bd_order_dish`.`order_date`'=>'order_time',
            '`bd_order_dish`.`money_cost`'=>'money',
            '`bd_order_dish`.`when`'=>'when',
            '`bd_order_dish`.`id`'=>'dish_id',
            '`bd_order_dish`.`order`'=>'order_id',
            '`bd_order_dish`.`money_pack`'=>'money_pack',
            //档口 餐厅名字
            '`bd_canteen_port`.`name`'=>'port_name',
            '`bd_canteen`.`name`'=>'canteen_name',
        ))->select();
        foreach ($order_dish_data as $key => &$value) {
            $value['order_date']=date('Y-m-d',$value['order_time']);
            $value['when_name']=$this->doWhenData($value['when']);
            $value['dish_status_name']=$this->doDishStatus($value['dish_status']);
        }
        return $order_dish_data;
    }
    //获取指定日期 指定餐厅下所有档口的 所有结算的 订单
    public function getBalancePortOrder($time_start=0 ,$time_end=0,$port='8'){
        // $time_end=strtotime('2016-4-9');
        // $time_start=strtotime('2016-4-8');
        $time_end-=1;
        $order_dish=M('order_dish');
        $rules=array(
            '`bd_order_basic`.`status`'=>array('in','4,5,6,7,8,9,11,12,13'),
            '`bd_order_dish`.`status`'=>array('in','2,3,6'),
            '`bd_order_dish`.`pack_time`'=>array('between',"$time_start,$time_end"),
            '`bd_order_dish`.`port`'=>array('in',$port),
        );
        $order_dish_data=$order_dish->where($rules)->join(
            '`bd_order_basic` ON `bd_order_basic`.`id`=`bd_order_dish`.`order`'
        )->join('`bd_canteen` ON `bd_canteen`.`id`=`bd_order_basic`.`canteen`'
        )->join('`bd_dict_order_basic_status` ON `bd_dict_order_basic_status`.`id`=`bd_order_basic`.`status`'
        )->field(array(
            '`bd_order_basic`.`status`'=>'basic_status',
            '`bd_dict_order_basic_status`.`name`'=>'basic_status_name',
            '`bd_order_basic`.`when`'=>'when',
            '`bd_order_basic`.`id`'=>'order_id',
            '`bd_order_basic`.`money`'=>'money',
            '`bd_order_basic`.`money_delivery`'=>'money_delivery',
            '`bd_order_basic`.`order_date`'=>'order_time',
            //档口 餐厅名字
            '`bd_canteen`.`name`'=>'canteen_name',
        ))->group('`bd_order_dish`.`order`')->select();
        foreach ($order_dish_data as $key => &$value) {
            $value['order_date']=date('Y-m-d',$value['order_time']);
            $value['when_name']=$this->doWhenData($value['when']);
        }
        return $order_dish_data;
    }
    //计算楼长的结算金额
    public function getBulidLearderMoney($num_order,$num_dish,$school_ext){
        switch ($school_ext) {
            case '1':
                $money=$num_order*80+($num_dish-$num_order)*50;
                break;
            default:
                $money=$num_order*80+($num_dish-$num_order)*30;
                break;
        }
        // print_r($money);
        return $money;
    }
    //处理状态
    public function doDishStatus($status){
        switch ($status) {
            case '1':
                $data="待出单";
                break;
            case '2':
                $data='订单完成';
                break;
            case '3':
                $data='底单完成';
                break;
            case '6':
                $data='已出单退款';
                break;
            case '7':
                $data='未出单退款';
                break;
            case '8':
                $data='必点送已接单';
                break;
            default:
                $data='异常';
                break;
        }
        return $data;
    }
    //订单状态
    public function doOrderStatus($status){
        $order_status=M('dict_order_basic_status');
        $o=$order_status->where(array('id'=>$status))->find();
        return $o['name'];
    }
}
