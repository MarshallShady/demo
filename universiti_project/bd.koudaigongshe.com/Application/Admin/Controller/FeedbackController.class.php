<?php
namespace Admin\Controller;
use Think\Controller;
class FeedbackController extends OrderController{
    public function test(){
        $port = M('canteen_port');
        $port_data=$port->where(array('canteen'=>36))->select();
        $port_str=$this->arrayToIn($port_data);
        echo $port_str;
    }
    /**
        全部订单页面
    */
    public function allOrder(){
        //权限判断
        // if(!(session('data')['role']=='超级管理员'||session('data')['role']=='城市经理'||session("data")['role']=='校园经理'||session("data")['role']=='档口管理员'||session('data')['role']=='餐厅经理'||session('data')['role']=='客服人员')){
        //     $this->display('/denied');
        //     return ;
        // }
        $this->assign("url",json_encode(array(
                                    'getinfo'=>U("Admin/feedback/allOrderGetinfo"),
                                    'getinfocanteen'=>U('Admin/feedback/getCanteenAllOrder'),
                                    )));
        $t=time()*1000+24*3600*1000;
        // $e=date('Y-m-d',(time()-3600*24));
        $e=time()*1000;
        $this->assign('date',array(
            'd_start'=>$e,
            'd_end'=>$t,
        ));
        $this->admin=session('data');
        $data=$this->roleCtrl();
        $this->assign($data);
        $this->display('allOrder');
    }
    //查询指定地址 时间的 order_dish 数据
    public function allOrderGetinfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        // $province=$post->province;
        // $school=$post->school;
        // $school_ext=$post->school_ext;
        $canteen=$post->canteen;
        $port=$post->port;
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $when=$post->when;
        if(empty($date_end)||empty($date_start)||empty($port)){
            $msg['code']=594;
            $this->ajaxReturn($msg);
        }
        if($when==''){
            $when_str='1,2,3';
        }else{
            $when_str=$when;
        }
        $data=$this->getOrderDish($date_start,$date_end,"$port","1,2,3,6,7,8",$when_str);
        // $data=$this->getOrderDish("2016-04-04","2016-04-09","11","3");

        if(empty($data)){
            $msg['code']=595;
            $this->ajaxReturn($msg);
        }else{
            $data['code']=200;
            $this->ajaxReturn($data);
        }
    }
    //获取指定餐厅所有档口的订单
    public function  getCanteenAllOrder(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $canteen=$post->canteen;
        $date_start=$post->date_start;
        $when=$post->when;
        $date_end=$post->date_end;
        if(empty($date_end)||empty($date_start)||empty($canteen)){
            $msg['code']=594;
            $this->ajaxReturn($msg);
        }
        if($when==''){
            $when_str='1,2,3';
        }else{
            $when_str=$when;
        }
        // $canteen=1;
        $port=M('canteen_port');
        $port_data=$port->where(array('canteen'=>$canteen))->select();
        $port_str=$this->arrayToIn($port_data);
        $data=$this->getOrderDish($date_start,$date_end,$port_str,"1,2,3",$when_str);
        // $data=$this->getOrderDish("2016-04-04","2016-04-09",$port_str,"1,3");
        //获取此餐厅订单信息
        $time_start=strtotime($date_start);
        $time_end=strtotime($date_end);
        $time_end-=1;
        $order=A('Api');
        $order_data=$order->getCanteenOrder($canteen,$when_str,$time_start,$time_end);
        if($order_data){
            $data['total_order']=$order_data['money'];
            $data['num_order']=$order_data['num_order'];
        }else{
            $data['total_order']=0;
            $data['num_order']=0;
        }
        if(empty($data)){
            $msg['code']=596;
            $this->ajaxReturn($msg);
        }else{
            $data['code']=200;
            $this->ajaxReturn($data);
        }
    }
    /**
        退款页面
    */
    public function refund(){
        //权限判断
        // if(!(session('data')['role']=='超级管理员'||session('data')['role']=='城市经理'||session("data")['role']=='客服人员')){
        //     $this->display('/denied');
        //     return ;
        // }
        $this->assign("url",json_encode(array(
                                    'getinfo'=>U("Admin/feedback/refundGetinfo"),
                                    'refund'=>U('Admin/feedback/refundApi'),
                                    'refunddish'=>U('Admin/feedback/refundDish'),
                                    )));
        $data=$this->roleCtrl();
        $this->assign($data);
        $this->display('refund');
    }
    //查询指定地址 时间的 order_dish 数据
    public function refundGetinfo(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $school_ext=$post->school_ext;
        $id=$post->id;
        $type=$post->type;
        if($type=="order"){
            $msg=$this->getOrderbyId($id);
        }elseif($type=="pack"){
            $msg=$this->getOrderbyPackId($id);
        }else{
            $msg['code']=523;
            $this->ajaxReturn($msg);
        }
        if(!(session('data')['role']=='客服人员'||session('data')['role']=='超级管理员')){
            if($msg['school_ext']!=$school_ext){
                $m['code']=556;
                $m['error']="此订单并非此学校的订单";
                $m['error2']=$msg['school_ext'];
                $m['error3']=$msg;
                $this->ajaxReturn($m);
            }
        }
        $msg['code']=200;
        $this->ajaxReturn($msg);
    }
    //退款处理
    public function refundApi(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $content=$post->content;
        if(empty($id)||!is_numeric($id)){
            $msg['code']=567;
            $msg['error']=$id;
            $this->ajaxReturn($msg);
        }
        $order_basic=M('order_basic');
        $order_basic_data=$order_basic->where(array('id'=>$id))->find();
        $history=M('history_refund');
        // $canteen_port=M('canteen_port');
        // $canteen_port_data=$canteen_port->where(array('id'=>$order_basic_data['port']))->find();
        $canteen=M('canteen');
        if($order_basic_data['canteen']==0){
            $canteen_data['name']='未知';
            $canteen_data['id']=0;
        }else{
            $canteen_data=$canteen->where(array('id'=>$order_basic_data['canteen']))->find();
        }
        $history_data=array(
            'admin_id'=>session('data')['role_id'],
            'admin_name'=>session('data')['name'],
            'time'=>time(),
            'time_order'=>$order_basic_data['order_date'],
            'canteen'=>$canteen_data['id'],
            'canteen_name'=>$canteen_data['name'],
            'money'=>$order_basic_data['money'],
            'order'=>$order_basic_data['id'],
            'dish'=>0,
            'status'=>1,
            'content'=>$content,
        );
        // $order_basic->startTrans();

        $ch=curl_init();
        $transaction_id=$order_basic_data['transaction_id'];
        $refund_fee=$order_basic_data['money'];
        $unionid=$order_basic_data['unionid'];
        $token=md5($unionid.$transaction_id.$refund_fee);
        $url="http://www.koudaigongshe.com/wxpay/refund.php?transaction_id=$transaction_id&refund_fee=$refund_fee&token=$token";
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        // curl_setopt($ch,CURLOPT_HEADER,0);
        // $output=curl_exec($ch);
        $output=file_get_contents($url);
        // $out=json_decode($output);
        // $out=htmlspecialchars_decode($output);
        $out=explode("|",$output);
        $o=$out[1];
        if($o!=200){
            $msg['bool']=($o=="200");
            $msg['code']=590;
            $msg['error']=$output;
            $msg['error2']=$out;
            $msg['url']=$url;
            $this->ajaxReturn($msg);
        }
        $history->startTrans();
        $result2=$history->add($history_data);
        $result=$order_basic->where(array('id'=>$id))->save(array('status'=>9));
        // $data=$order_basic->where(array('id'=>$id))->find();
        // if($data['status']!=8){
        //     $msg['code']=568;
        //     $msg['error']="订单未申请退款";
        //     $this->ajaxReturn($msg);
        // }


        if($result&&$result2){
            $history->commit();
            $res['code']=200;
            $res['error']=$output;
        }else{
            $history->rollback();
            $res['code']=589;
            $res['error']=$result;
            $res['data']=$canteen_data;
            $res['d']=$order_basic_data;
        }
        // curl_close($ch);
        $this->ajaxReturn($res);
    }
    //分菜品退款接口
    public function refundDish(){
        $postData=file_get_contents('php://input', true);
        $post=json_decode($postData);
        $id=$post->id;
        $content=$post->content;
        if(empty($id)||!is_numeric($id)){
            $msg['code']=567;
            $msg['error']=$id;
            $this->ajaxReturn($msg);
        }
        $order_basic=M('order_basic');
        $order_dish=M('order_dish');
        $order_dish_data=$order_dish->where(array('id'=>$id))->find();
        $order_basic_data=$order_basic->where(array('id'=>$order_dish_data['order']))->find();
        $history=M('history_refund');
        $canteen=M('canteen');
        if($order_basic_data['canteen']==0){
            $canteen_data['name']='未知';
            $canteen_data['id']=0;
        }else{
            $canteen_data=$canteen->where(array('id'=>$order_basic_data['canteen']))->find();
        }
        $history_data=array(
            'admin_id'=>session('data')['role_id'],
            'admin_name'=>session('data')['name'],
            'time'=>time(),
            'time_order'=>$order_basic_data['order_date'],
            'canteen'=>$canteen_data['id'],
            'canteen_name'=>$canteen_data['name'],
            'money'=>$order_dish_data['money'],
            'order'=>$order_basic_data['id'],
            'dish'=>$order_dish_data['id'],
            'status'=>2,//1是订单退款 2是分菜品退款
            'content'=>$content,
        );
        // $order_basic->startTrans();
        // $dish = M('canteen_port_dish');
        // $dish_data = $dish->where(array('id'=>$order_dish_data['dish']))
        $transaction_id=$order_basic_data['transaction_id'];
        $refund_fee=$order_dish_data['money']+$order_dish_data['money_pack'];
        // $refund_total=$order_basic_data['money'];
        $unionid=$order_basic_data['unionid'];
        $token=md5($unionid.$transaction_id.$refund_fee);
        $url="http://www.koudaigongshe.com/wxpay/refund2.php?transaction_id=$transaction_id&refund_fee=$refund_fee&token=$token";
        $output=file_get_contents($url);
        $out=explode("|",$output);
        $o=$out[1];
        if($o!=200){
            $msg['bool']=($o=="200");
            $msg['code']=590;
            $msg['error']=$output;
            $msg['error2']=$out;
            $msg['url']=$url;
            $this->ajaxReturn($msg);
        }
        $history->startTrans();
        $result2=$history->add($history_data);
        if($order_dish_data['status']==3||$order_dish_data['status']==2||$order_dish_data['status']==8){
            $status=6;
        }elseif($order_dish_data['status']==1){
            $status=7;
        }else{
            $status=10;
        }
        $result=$order_dish->where(array('id'=>$id))->save(array('status'=>$status));
        // $data=$order_basic->where(array('id'=>$id))->find();
        // if($data['status']!=8){
        //     $msg['code']=568;
        //     $msg['error']="订单未申请退款";
        //     $this->ajaxReturn($msg);
        // }
        if($result&&$result2){
            $history->commit();
            $res['code']=200;
            $res['error']=$output;
        }else{
            $history->rollback();
            $res['code']=589;
            $res['error']=$result;
            $res['data']=$canteen_data;
            $res['d']=$order_basic_data;
        }
        // curl_close($ch);
        $this->ajaxReturn($res);
    }
    /**
        退款历史页面
    */
    public function refundHistory(){
        $this->assign('url',array(
            'getinfo'=>U('Admin/Feedback/histroyGetInfo'),
            'getorder'=>U('Admin/Feedback/refundGetinfo'),
        ));
        // $t=date('Y-m-d',time());
        // $e=date('Y-m-d',(time()-3600*24));
        $t=time()*1000+24*3600*1000;
        // $e=date('Y-m-d',(time()-3600*24));
        $e=time()*1000-29*24*3600*1000;
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
        $this->display('history');
    }
    //获取退款历史
    public function histroyGetInfo(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $canteen_id=$post->canteen;
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $time_start=strtotime($date_start);
        $time_end=strtotime($date_end);
        if(empty($canteen_id)||empty($time_start)||empty($time_end)){
            $msg['code']=622;
            $this->ajaxReturn($msg);
        }
        $history=M('history_refund');
        $rules=array(
            'status'=>array('in','1,2'),
            'time_order'=>array('between',"$time_start,$time_end"),
            'canteen'=>array('in',"$canteen_id"),
        );
        $history_data=$history->where($rules)->select();
        $this->doHistoryData($history_data);
        $data['code']=200;
        $data['data']=$history_data;
        $this->ajaxReturn($data);
    }
    //处理退款历史消息
    public function doHistoryData(&$data){
        foreach ($data as &$value) {
            $value['date']=date('Y-m-d H:i',$value['time']);
            $value['date_order']=date('Y-m-d',$value['time_order']);
            if($value['order']==0){
                $value['order']='未知';
            }
            if($value['dish']==0){
                $value['dish']='未知';
            }
            $value['status_name']=$this->doStatus($value['status']);
        }
    }
    public function doStatus($status){
        if($status==1){
            return '订单退款';
        }elseif ($status==2){
            return '菜品退款';
        }else{
            return '异常';
        }
    }
    /**
        投诉汇总页面
    */
    public function complain(){
        $this->assign('url',array(
            'getinfo'=>U('Admin/Feedback/complainGetInfo'),
            'getorder'=>U('Admin/Feedback/refundGetinfo'),
            'reply'=>U('Admin/Feedback/complainReply'),
        ));
        // $t=date('Y-m-d',time());
        // $e=date('Y-m-d',(time()-3600*24));
        $t=time()*1000+24*3600*1000;
        // $e=date('Y-m-d',(time()-3600*24));
        $e=time()*1000-7*24*3600*1000;
        $this->assign('date',array(
            'd_start'=>$e,
            'd_end'=>$t,
        ));
        // $data=$this->roleCtrl();
        // if(!$data){
        //     $msg['code']=501;
        //     $this->ajaxReturn($msg);
        // }
        // $this->assign($data);
        $this->display('complain');
    }
    //获取投诉汇总信息
    public function complainGetInfo(){
        $postData = file_get_contents("php://input",true);
        $post = json_decode($postData);
        $date_start = $post->date_start;
        $date_end = $post->date_end;
        $status =$post->status;
        $time_start = strtotime($date_start);
        $time_end = strtotime($date_end);
        $time_end -=1;
        $complain = M('order_complain');
        $rules = array(
            '`bd_order_basic`.`order_date`'=>array('between',"$time_start,$time_end"),
            '`bd_order_complain`.`status`'=>array('in',$status),
        );
        $complain_data = $complain->where($rules)->order('`bd_order_basic`.`order_date` DESC,`bd_order_complain`.`id` DESC')->join(
            '`bd_order_basic` ON `bd_order_basic`.`id`=`bd_order_complain`.`order`'
        )->join(
            '`bd_order_dish` ON `bd_order_complain`.`dish`=`bd_order_dish`.`id`'
        )->join(
            '`bd_canteen` ON `bd_order_basic`.`canteen`=`bd_canteen`.`id`'
        )->join(
            '`bd_school_ext` ON `bd_school_ext`.`id`=`bd_canteen`.`school_ext`'
        )->join(
            '`bd_school` ON `bd_school`.`id`=`bd_school_ext`.`school`'
        )->join(
            '`bd_canteen_port` ON `bd_canteen_port`.`id`=`bd_order_dish`.`port`'
        )->field(array(
            '`bd_school`.`name` as school_name',
            '`bd_school_ext`.`name` as school_ext_name',
            '`bd_canteen`.`name` as canteen_name',
            '`bd_canteen_port`.`name` as port_name',

            '`bd_order_basic`.`order_date` as time_order',
            '`bd_order_basic`.`when` as when_id',
            '`bd_order_basic`.`name` as user_name',
            '`bd_order_basic`.`mobile` as mobile',

            '`bd_order_dish`.`name` as dish_name',

            '`bd_order_complain`.`word` as word',
            '`bd_order_complain`.`id` as complain_id',
            '`bd_order_complain`.`order` as order_id',
            '`bd_order_complain`.`dish` as dish_id',
            '`bd_order_complain`.`choice` as choice',
            '`bd_order_complain`.`status` as status',
        ))->select();
        $data = $this->doComplainData($complain_data);
        if(!$data){
            $msg['code'] = 607;
            $msg['data'] = $data;
        }else{
            $msg['code'] = 200;
            $msg['data'] = $data;
        }
        $this->ajaxReturn($msg);
    }
    //处理投诉信息
    public function doComplainData($data){
        foreach ($data as &$value) {
            $value['when_name'] = $this->doWhenData($value['when_id']);
            $value['date_order'] = date('Y-m-d',$value['time_order']);
            switch ($value['choice']) {
                case '2':
                    $value['word'] ="【菜未送达】".$value['word'];
                    break;
                case '3':
                    $value['word'] ="【菜品打错】".$value['word'];
                    break;
                case '4':
                    $value['word'] ="【菜品打翻】".$value['word'];
                    break;
                default:
                    # code...
                    break;
            }
        }
        return $data;
    }
    //回复投诉信息
    public function complainReply(){
        $postData = file_get_contents("php://input",true);
        $post = json_decode($postData,1);
        $id = $post['id'];
        $content = $post['content'];
        $rules = [
            ['id','number','id'],
            ['reply','require','content'],
        ];
        $complain = M('order_complain');
        $complain_data = $complain->where(array('`bd_order_complain`.`id`'=>$id))->join(
            '`bd_order_basic` ON `bd_order_basic`.`id`=`bd_order_complain`.`order`'
        )->join(
            '`bd_user_basic` ON `bd_user_basic`.`unionid`=`bd_order_basic`.`unionid`'
        )->field(array(

            '`bd_order_complain`.`reply` AS reply',
            '`bd_order_complain`.`word` AS word',
            '`bd_order_complain`.`choice` AS choice',

            '`bd_order_basic`.`order_date` as time_order',

            '`bd_user_basic`.`openid` AS openid',
            '`bd_order_basic`.`name` AS name',
        ))->select();
        $complain_data = $this->doComplainData($complain_data)[0];
        if($complain_data['reply']){
            $msg['code'] = 403;
            $this->ajaxReturn($msg);
        }
        $data  = [
            'first' => [
                'value' => "亲爱的{$complain_data['name']}：\n你的投诉我们已经受理。\n\n",
                'color' => '#398DEE',
            ],
            'keyword1' => [
                'value' => "{$complain_data['name']}",
                'color' => '#000000',
            ],
            'keyword2' => [
                'value' => "{$complain_data['word']}\n客服回复：{$content}",
                'color' => '#000000',
            ],
            'keyword3' => [
                'value' => date('Y-m-d',$complain_data['time_order'])."(订单日期)",
                'color' => '#000000',
            ],
            'remark' => [
                'value' => "\n如还有问题，点击“详情”\n联系必点客服",
                'color' => '#FF8000',
            ],
        ];
        SendTempletMessage($complain_data['openid'],"http://bd.koudaigongshe.com/mobile/index/feedback",$data,2);
        $data_update = array(
            'id'=>$id,
            'reply'=>$content,
            'admin_id'=>session('data')['role_id'],
            'status'=>1,
        );
        if(!$complain->validate($rules)->data($data)){
            $msg['code']=501;
            $thia->ajaxReturn($msg);
        }
        $res2 = $complain->save($data_update);
        if($res2){
            $msg['code']=200;
        }else{
            $msg['code']=501;
        }
        $this->ajaxReturn($msg);

    }
}
