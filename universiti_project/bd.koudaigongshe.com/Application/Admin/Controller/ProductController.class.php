<?php
namespace Admin\Controller;
use Think\Controller;
class ProductController extends CommenController{
    /**
        学校管理
        @author by larry
    */
    public function school(){
        // session('data')['role']=='超级管理员'||session('data')['role']=='城市经理'
        if(true){
            //获取校区列表
            $school_ext=M('school_ext');
            $school_ext_data=$school_ext->select();
            $this->assign('school_ext',$school_ext_data);

            $school=M('school');
            $result=$school->select();
            $this->assign("school",$result);
            $this->assign("url",U('admin/product/schoolchange'));
            $this->display('school');
        }else{
            $this->display('/denied');
        }
    }
    //改变学校状态
    public function schoolChange(){
        $school=M('school');
        $result=$school->where(array('id'=>I('id')))->save(array('status'=>I('change')));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=401;
        }
        $this->ajaxReturn($msg);
    }
    /**
        食堂管理
    */
    public function canteen(){
        if(true){
            $this->assign("url",json_encode(array(
                                        'canteenchange'=>U('admin/product/canteenchange'),
                                        'canteendelete'=>U('admin/product/canteenDelete'),
                                        'getinfo'=>U('Admin/Product/canteenGetInfo'),
                                        'edit'=>U('Admin/Product/canteenEdit'),
                                        'delete'=>U('Admin/Product/canteenDelete'),
                                        'status'=>U('Admin/Product/canteenStatus'),
                                        'add'=>U('Admin/Product/canteenAdd'),
                                    )));
            //输出经权限过滤过的联级列表
            $data=$this->roleCtrl();
            $this->assign($data);
            $this->display('canteen');
        }else{
            $this->display('/denied');
        }
    }
    //获取食堂数据接口
    public function canteenGetInfo(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $school_ext=$post->school_ext;
        if(!is_numeric($school_ext)){
            $msg['code']=651;
            $this->ajaxReturn($msg);
        }
        $canteen=M('canteen');
        $data=$canteen->where(array('`bd_canteen`.`school_ext`'=>$school_ext,'`bd_canteen`.`status`'=>array('in','1,2')))->join(
            '`bd_school_ext` ON `bd_school_ext`.`id`=`bd_canteen`.`school_ext`'
        )->join(
            '`bd_school` ON `bd_school`.`id`=`bd_school_ext`.`school`'
        )->field(array(
            '`bd_school`.`name`'=>'school_name',
            '`bd_school_ext`.`name`'=>'school_ext_name',

            '`bd_canteen`.`name`'=>'name',
            '`bd_canteen`.`id`'=>'id',
            '`bd_canteen`.`device`'=>'device',
            '`bd_canteen`.`status`'=>'status',
        ))->select();
        foreach ($data as  &$value) {
            if($value['status']==1){
                $value['status_name']="正运营";
            }elseif($value['status']==2){
                $value['status_name']="已停业";
            }
        }
        if($data){
            $msg['code']=200;
            $msg['data']=$data;
        }else{
            $msg['code']=651;
        }
        $this->ajaxReturn($msg);
    }
    //修改状态
    public function canteenStatus(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        $canteen=M('canteen');
        $data=$canteen->where(array('id'=>$id))->find();
        if($data['status']==1){
            $status=2;
        }elseif($data['status']==2){
            $status=1;
        }else{
            $msg['code']=652;
            $this->ajaxReturn($msg);
        }
        $result=$canteen->where(array('id'=>$id))->save(array('status'=>$status));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=402;
        }
        $this->ajaxReturn($msg);
    }
    //修改食堂的机器码
    public function canteenEdit(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        $name=$post->name;
        $device=$post->device;
        $data=array(
            'id'=>$id,
            'name'=>$name,
            'device'=>$device,
        );
        $rules=array(
            array('id','number','id'),
            array('name','require','name'),
            array('device','require','device'),
        );
        $canteen=M('canteen');
        if(!$canteen->validate($rules)->create($data)){
            $msg['code']=649;
            $msg['error']=$canteen->getError();
            $this->ajaxReturn($msg);
        }
        if($canteen->save($data)){
            $msg['code']=200;
        }else{
            $msg['code']=650;
        }
        $this->ajaxReturn($msg);
    }
    public function canteenDelete(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        $canteen=M('canteen');
        $result=$canteen->where(array('id'=>$id))->save(array('status'=>0));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=402;
        }
        $this->ajaxReturn($msg);
    }
    //添加餐厅
    public function canteenAdd(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $name=$post->name;
        $school_ext=$post->school_ext;
        $data=array(
            'name'=>$name,
            'school_ext'=>$school_ext,
            'status'=>1,
        );
        $rules=array(
            array('name','require','name'),
            array('school_ext','number','school_ext'),
            array('status','number','status'),
        );
        $canteen=M('canteen');
        if(!$canteen->validate($rules)->create($data)){
            $msg['code']=652;
            $msg['error']=$canteen->getError();
            $this->ajaxReturn($msg);
        }
        if($canteen->add($data)){
            $msg['code']=200;
        }else{
            $msg['code']=653;
        }
        $this->ajaxReturn($msg);
    }
    /**
        档口管理
    */
    public function port(){

        $this->assign("url",json_encode(array('portchange'=>U('admin/product/portChange'),
                                    'portdelete'=>U('admin/product/portDelete'),
                                    'addport'=>U('Admin/Product/portAddPort'),
                                    'getinfo'=>U('Admin/Product/portGetInfo'),
                                    'edit'=>U('Admin/Product/portEdit'),
                                    )));
        if(true){
            //获取权限相应的联级列表
            $data=$this->roleCtrl();
            $this->assign($data);
            //获取全部档口数据 在前端筛选
            $port=M('canteen_port');
            $port_data=$port->where(array('status'=>array('in','1,2')))->select();
            foreach ($port_data as &$value) {
                if($value['status']==1){
                    $value['status_2']='已上线';
                    $value['status_3']='下线';
                }elseif($value['status']==2){
                    $value['status_2']='已下线';
                    $value['status_3']='上线';
                }
            }
            $this->assign('port',json_encode($port_data));
            $this->display('port');
        }else{
            $this->display('/denied');
        }
    }
    //改变档口状态操作
    public function portChange(){
        $id=I('id');
        $changeto=I('changeto');
        if($id==''||$changeto==''){
            echo "无数据";
            return ;
        }
        $port=M('canteen_port');
        $result=$port->where(array('id'=>$id))->save(array('status'=>$changeto));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=402;
        }
        $this->ajaxReturn($msg);
    }
    //删除档口操作 但未删除相应的菜品数据
    public function portDelete(){
        $id=I('id');
        if($id==''){
            echo "无数据";
            return ;
        }

        $port=M('canteen_port');
        $result=$port->where(array('id'=>$id))->save(array('status'=>0));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=403;
        }
        $this->ajaxReturn($msg);
    }
    //获取档口信息接口
    public function portGetInfo(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $canteen=$post->canteen;
        $port=M('canteen_port');
        $data=$port->where(array('canteen'=>$canteen,'status'=>array('in','1,2')))->select();
        foreach ($data as &$value) {
            if($value['status']==1){
                $value['status_2']='已上线';
                $value['status_3']='下线';
            }elseif($value['status']==2){
                $value['status_2']='已下线';
                $value['status_3']='上线';
            }
        }
        if($data){
            $msg['code']=200;
        }else{
            $msg['code']=603;
        }
        $msg['data']=$data;
        $this->ajaxReturn($msg);
    }
    //添加档口接口
    public function portAddPort(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $canteen=$post->canteen;
        $port_name=$post->port_name;
        $port_content=$post->port_content;
        if(empty($canteen)||empty($port_name)){
            $msg['code']=601;
            $this->ajaxReturn($msg);
        }
        if(empty($port_content)){
            $port_content="";
        }
        $port=M('canteen_port');
        $data=array(
            'canteen'=>$canteen,
            'name'=>$port_name,
            'content'=>$port_content,
            'status'=>'1',
        );
        $result=$port->add($data);
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=602;
        }
        $this->ajaxReturn($msg);
    }
    //修改档口资料
    public function portEdit(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $id=$post->id;
        $name=$post->name;
        $content=$post->content;
        $order=$post->order;
        $order_pack=$post->order_pack;
        if(!is_numeric($id)){
            $msg['code']=643;
            $this->ajaxReturn($msg);
        }
        $data=array(
            'name'=>$name,
            'content'=>$content,
            'order'=>$order,
            'order_pack'=>$order_pack,
        );
        $rules=array(
            array('name','require','name'),
            // array('content','/./','content请使用使用汉字！',1,'regex',3),
            array('order','number','order'),
            array('order_pack','number','order_pack'),
        );
        $port=M('canteen_port');
        if(!$port->validate($rules)->create($data)){
            $msg['code']=642;
            $msg['error']=$port->getError();
            $this->ajaxReturn($msg);
        }
        $result=$port->where(array('id'=>$id))->save($data);
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=644;
        }
        $this->ajaxReturn($msg);
    }
    /**
        菜品管理
    */
    public function dish(){
        $this->assign("url",json_encode(array(
                                    'dishchange'=>U('admin/product/dishChange'),
                                    'dishdelete'=>U('admin/product/dishDelete'),
                                    'dishrest'=>U('admin/product/dishRest'),
                                    'dishrestdefault'=>U('admin/product/dishRestDefault'),
                                    'getinfo'=>U("Admin/Product/dishgetinfo"),
                                    'add'=>U('Admin/Product/dishAddDish'),
                                    )));

        //第一层权限过滤
        if(true){
            //获取权限相应的联级列表
            $data=$this->roleCtrl();
            $this->assign($data);
            $this->display('dish');
        }else{
            $this->display('/denied');
        }

    }
    public function dishGetInfo(){
        $port=I('port');
        if(!$port){
            $msg['code']=503;
            $this->ajaxReturn($msg);
        }
        $dish=M('canteen_port_dish');
        $dish_data=$dish->where(array('status'=>array('in','1,2'),'port'=>$port))->select();
        foreach ($dish_data as &$value){
            if($value['status']==1){
                $value['status_2']='已上线';
                $value['status_3']='下线';
            }elseif($value['status']==2){
                $value['status_2']='已下线';
                $value['status_3']='上线';
            }
            $value['breakfast_id']="breakfast_".$value['id'];
            $value['lunch_id']="lunch_".$value['id'];
            $value['dinner_id']="dinner_".$value['id'];
            if($value['breakfast']==1){
                $value['breakfast_name']="出售";
            }else{
                $value['breakfast_name']="未售";
            }
            if($value['lunch']==1){
                $value['lunch_name']="出售";
            }else{
                $value['lunch_name']="未售";
            }
            if($value['dinner']==1){
                $value['dinner_name']="出售";
            }else{
                $value['dinner_name']="未售";
            }
        }
        $this->ajaxReturn($dish_data);

    }
    //并删除相应的菜品数据
    public function dishDelete(){
        $id=I('id');
        if($id==''){
            echo "无数据";
            return ;
        }

        $port=M('canteen_port_dish');
        $result=$port->where(array('id'=>$id))->save(array('status'=>0));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=403;
        }
        $this->ajaxReturn($msg);
    }
    //改变菜品状态
    public function dishChange(){
        $id=I('id');
        $status=I('changeto');
        if($id==''||$status==''){
            echo "无数据";
            return ;
        }
        $dish=M('canteen_port_dish');
        $result=$dish->where(array('id'=>$id))->save(array('status'=>$status));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=404;
        }
        $this->ajaxReturn($msg);
    }
    //修改菜品库存
    public function dishRest(){
        $id=I('id');
        $rest=I('rest');
        $data=array(
            'rest'=>$rest,
            'breakfast'=>I('breakfast'),
            'lunch'=>I('lunch'),
            'dinner'=>I('dinner'),
            'content'=>I('content'),
            'money'=>100*I('money'),
            'order'=>I('order'),
            'name'=>I('name'),
            'limit_tag'=>I('limit_tag'),
            'money_pack'=>I('money_pack')*100,
            'money_cost'=>I('money_cost')*100,
            'update_time'=>time(),
        );
        if($id==''||$rest==''){
            echo "无数据";
            return ;
        }
        $dish=M('canteen_port_dish');
        $result=$dish->where(array('id'=>$id))->save($data);
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=404;
        }
        $this->ajaxReturn($msg);
    }
    //修改菜品预设库存
    public function dishRestDefault(){
        $id=I('id');
        $restdefault=I('restdefault');
        if($id==''||$restdefault==''){
            echo "无数据";
            return ;
        }
        $dish=M('canteen_port_dish');
        $result=$dish->where(array('id'=>$id))->save(array('rest_default'=>$restdefault));
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=404;
        }
        $this->ajaxReturn($msg);
    }
    //新的添加菜品功能
    public function dishAddDish(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $img=$post->img;
        $name_dish=$post->name;
        $money=$post->money;
        $rest=$post->rest;
        $rest_default=$post->rest_default;
        $breakfast=$post->breakfast;
        $lunch=$post->lunch;
        $dinner=$post->dinner;
        $content=$post->content;
        $port=$post->port;
        $money_pack=$post->money_pack;
        $limit_tag=$post->limit_tag;
        $money_cost =$post->money_cost;

        $name=time().'.jpg';
        $savePath="/Public/Uploads/dish/".$name;
        $e=explode(",",$img);
        $image=base64_decode($e[1]);
        $result=file_put_contents("/data/wwwroot/bd.koudaigongshe.com".$savePath,$image);
        if(!$result){
            $msg['code']=613;
            $msg['error']="图片保存失败";
            $this->ajaxReturn($msg);
        }
        $rules=array(
            array('name','require','菜品名称已经存在！'),
            array('money',"number","价格不是数字"),
            array('money_pack',"number","打包不是数字"),
            array('limit_tag',"number","xianjia不是数字"),
            array('rest',"number","库存不是数字"),
            array('rest_default',"number","预存库存不是数字"),
            array('port','number','档口必须选择'),
            array('breakfast','number','数字必须选择'),
            array('lunch','number','档口必须选择1'),
            array('dinner','number','档口必须选择3'),
            // array('content','','content'),
            array('image','require','图片'),
            array('money_cost','number','货价'),
        );
        $data=array(
            'name'=>$name_dish,
            'money'=>100*$money,
            'money_pack'=>100*$money_pack,
            'limit_tag'=>$limit_tag,
            'rest'=>$rest,
            'rest_default'=>$rest_default,
            'port'=>$port,
            'image'=>$savePath,
            'status'=>1,
            'breakfast'=>$breakfast,
            'lunch'=>$lunch,
            'dinner'=>$dinner,
            'content'=>$content,
            'money_cost'=>$money_cost*100,
            'create_time'=>time(),
        );
        $dish=M('canteen_port_dish');
        if(!$dish->validate($rules)->create($data)){
            $msg['code']=615;
            $msg['error']=$dish->getError();
            $this->ajaxReturn($msg);
        }
        $result2=$dish->add($data);
        if(!$result2){
            $msg['code']=616;
            $msg['error']=$dish->getError();
            $this->ajaxReturn($msg);
        }
        $msg['code']=200;
        $msg['error']=$e[1];
        // $msg['error']=$result;
        $this->ajaxReturn($msg);
    }
    /**
        学校校区管理
    */
    public function school_ext(){
        $this->assign("url",json_encode(array(
                                    'edit'=>U('Admin/Product/school_extEdit'),
                                    'getinfo'=>U("Admin/Product/school_extGetinfo"),
                                    'status'=>U('Admin/Product/school_extStatus'),
                                    'getschool'=>U('Admin/Product/school_extGetSchool'),
                                    'addprovince'=>U('Admin/Product/school_extAddProvince'),
                                    'addschool'=>U('Admin/Product/school_extAddSchool'),
                                    'addschool_ext'=>U('Admin/Product/school_extAddSchool_ext'),
                                    'delete'=>U('Admin/Product/school_extDelete'),
                                    'deleteAll'=>U('Admin/Product/school_extDeleteAll'),
                                    )));

        //第一层权限过滤
        if(true){
            //获取权限相应的联级列表
            $data=$this->roleCtrl();
            $this->assign($data);
            $this->display('school_ext');
        }else{
            $this->display('/denied');
        }
    }
    //获取学校校区数据
    public function school_extGetinfo(){
        $postData=file_get_contents('php://input',TRUE);
        $post=json_decode($postData);
        $id=$post->id;
        $school=M('school');
        $school_data=$school->where(array('province'=>$id,'status'=>array('in','1,2')))->select();
        $school_str=$this->arrayToIn($school_data);

        $school_ext=M('school_ext');
        $school_ext_data=$school_ext->where(array('school'=>array('in',$school_str),'status'=>array('in',"1,2")))->select();
        $data['data']=$this->doSchool_extData($school_ext_data);
        $data['code']=200;
        $this->ajaxReturn($data);
    }
    //处理学校校区数据
    public function doSchool_extData($data){
        $school=M('school');
        foreach ($data as &$value) {
            $school_data=$school->where(array('id'=>$value['school']))->find();
            $value['school_name']=$school_data['name'];
            //状态
            if($value['status']==1){
                $value['status_name']='上线';
            }else if($value['status']==2){
                $value['status_name']='下线';
            }
            //时间
            $time=strtotime('1970-01-01');

            // $value['date_breakfast_order_start']=date('H:i',$time+$value['breakfast_start']);
            $value['date_breakfast_order_end']=date('H:i',$time+$value['breakfast_order_end']);
            $value['date_breakfast_send_start']=date('H:i',$time+$value['breakfast_send_start']+28800);
            $value['date_breakfast_send_end']=date('H:i',$time+$value['breakfast_send_end']+28800);

            // $value['date_lunch_order_start']=date('H:i',$time+$value['lunch_start']);
            $value['date_lunch_order_end']=date('H:i',$time+$value['lunch_order_end']);
            $value['date_lunch_send_start']=date('H:i',$time+$value['lunch_send_start']+28800);
            $value['date_lunch_send_end']=date('H:i',$time+$value['lunch_send_end']+28800);

            // $value['date_dinner_order_start']=date('H:i',$time+$value['dinner_start']);
            $value['date_dinner_order_end']=date('H:i',$time+$value['dinner_order_end']);
            $value['date_dinner_send_start']=date('H:i',$time+$value['dinner_send_start']+28800);
            $value['date_dinner_send_end']=date('H:i',$time+$value['dinner_send_end']+28800);

            $value['date_breakfast_pack_start']=date('H:i',$time+$value['breakfast_pack_start']);
            $value['date_dinner_pack_start']=date('H:i',$time+$value['dinner_pack_start']);
            $value['date_lunch_pack_start']=date('H:i',$time+$value['lunch_pack_start']);

            $value['date_bl_break_point'] = date('H:i',$time+$value['bl_break_point']);
            $value['date_ld_break_point'] = date('H:i',$time+$value['ld_break_point']);
            $value['error1'] = $time;
            $value['error2'] = date('H:i',$time);

        }
        return $data;
    }
    //修改信息
    public function school_extEdit(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $id=$post->school_ext;
        $breakfast_order_start=$post->breakfast_order_start;
        $breakfast_order_end=$post->breakfast_order_end;
        $breakfast_send_start=(int)$post->breakfast_send_start-28800;
        $breakfast_send_end=(int)$post->breakfast_send_end-28800;

        $lunch_order_start=$post->lunch_order_start;
        $lunch_order_end=$post->lunch_order_end;
        $lunch_send_start=$post->lunch_send_start-28800;
        $lunch_send_end=$post->lunch_send_end-28800;

        $dinner_order_start=$post->dinner_order_start;
        $dinner_order_end=$post->dinner_order_end;
        $dinner_send_start=$post->dinner_send_start-28800;
        $dinner_send_end=$post->dinner_send_end-28800;

        $breakfast_pack_start=$post->breakfast_pack_start;
        $lunch_pack_start=$post->lunch_pack_start;
        $dinner_pack_start=$post->dinner_pack_start;

        $bl_break_point = $post->bl_break_point;
        $ld_break_point = $post->ld_break_point;
        $money_delivery = $post->money_delivery*100;
        $money_least = $post->money_least*100;

        $rules=array(
            // array('breakfast_start','number','bs'),
            array('breakfast_order_end','number','be'),
            // array('breakfast_send_start','number','bss'),
            // array('breakfast_send_end','number','bse'),

            // array('lunch_start','number','ls'),
            array('lunch_order_end','number','le'),
            array('lunch_send_start','number','lss'),
            array('lunch_send_end','number','lse'),

            // array('dinner_start','number','ls'),
            array('dinner_order_end','number','de'),
            array('dinner_send_start','number','dss'),
            array('dinner_send_end','number','dse'),

            array('breakfast_pack_start','number','dse'),
            array('lunch_pack_start','number','dse'),
            array('dinner_pack_start','number','dse'),

            array('bl_break_point','number','bl'),
            array('ld_break_point','number','ld'),
        );
        $data=array(
            'id'=>$id,
            // 'breakfast_start'=>$breakfast_order_start,
            'breakfast_order_end'=>$breakfast_order_end,
            'breakfast_send_start'=>$breakfast_send_start,
            'breakfast_send_end'=>$breakfast_send_end,

            // 'lunch_start'=>$lunch_order_start,
            'lunch_order_end'=>$lunch_order_end,
            'lunch_send_start'=>$lunch_send_start,
            'lunch_send_end'=>$lunch_send_end,

            // 'dinner_start'=>$dinner_order_start,
            'dinner_order_end'=>$dinner_order_end,
            'dinner_send_start'=>$dinner_send_start,
            'dinner_send_end'=>$dinner_send_end,

            'breakfast_pack_start'=>$breakfast_pack_start,
            'lunch_pack_start'=>$lunch_pack_start,
            'dinner_pack_start'=>$dinner_pack_start,

            'bl_break_point'=>$bl_break_point,
            'ld_break_point'=>$ld_break_point,
            'money_delivery'=>$money_delivery,
            'money_least'=>$money_least,
        );
        $m_school_ext=M('school_ext');
        if(!$m_school_ext->validate($rules)->create($data)){
            $msg['code']=620;
            $msg['error']=$m_school_ext->getError();
            $msg['error2'] = $breakfast_send_start;
            $this->ajaxReturn($msg);
        }
        $result=$m_school_ext->save($data);
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=621;
            $msg['error']=$data;
        }
        $this->ajaxReturn($msg);
    }
    //改变状态
    public function school_extStatus(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $id=$post->id;
        if(empty($id)||!is_numeric($id)){
            $msg['code']=616;
            $this->ajaxReturn($msg);
        }
        $school_ext=M('school_ext');
        $data=$school_ext->where(array('id'=>$id))->find();
        if($data['status']==1){
            $status=2;
        }elseif($data['status']==2){
            $status=1;
        }else{
            $status=0;
        }
        $result=$school_ext->where(array('id'=>$id))->save(array('status'=>$status));
        if($result){
            $msg['code']=200;
        }else{
            $msg['error']=618;
        }
        $this->ajaxReturn($msg);
    }
    //获取学校 校区楼级
    public function school_extGetSchool(){
        $province=M('dict_province');
        $school=M('school');
        $province_data=$province->where(array('status'=>array('in','1,2')))->select();
        $school_data=$school->where(array('status'=>array('in','1,2')))->select();
        $data=array(
            'code'=>200,
            'province'=>$province_data,
            'school'=>$school_data,
        );
        $this->ajaxReturn($data);
    }
    //添加城市
    public function school_extAddProvince(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $name=$post->name;
        if(!$name){
            $msg['code']=653;
            $this->ajaxReturn($msg);
        }
        $province=M('dict_province');
        if($province->where(array('name'=>$name,'status'=>array('in','1,2')))->find()){
            $msg['code']=654;
            $msg['error']='此城市你已经添加过了哦~';
            $this->ajaxReturn($msg);
        }
        $res=$province->add(array('name'=>$name,'status'=>1));
        if($res){
            $msg['code']=200;
        }else{
            $msg['code']=653;
        }
        $this->ajaxReturn($msg);
    }
    //添加学校
    public function school_extAddSchool(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $name=$post->name;
        $province=$post->province;
        if(!$name||!$province){
            $msg['code']=653;
            $this->ajaxReturn($msg);
        }
        $school=M('school');
        if($school->where(array('name'=>$name,'province'=>$province,'status'=>array('in','1,2')))->find()){
            $msg['code']=654;
            $msg['error']='此学校你已经添加过了哦~';
            $this->ajaxReturn($msg);
        }
        $res=$school->add(array('name'=>$name,'status'=>1,'province'=>$province));
        if($res){
            $msg['code']=200;
        }else{
            $msg['code']=653;
        }
        $this->ajaxReturn($msg);
    }
    //添加学校校区
    public function school_extAddSchool_ext(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $name=$post->name;
        $school=$post->school;
        if(!$name||!$school){
            $msg['code']=653;
            $this->ajaxReturn($msg);
        }
        $school_ext=M('school_ext');
        if($school_ext->where(array('name'=>$name,'school'=>$school,'status'=>array('in','1,2')))->find()){
            $msg['code']=654;
            $msg['error']='此校区你已经添加过了哦~';
            $this->ajaxReturn($msg);
        }
        $res=$school_ext->add(array('name'=>$name,'status'=>1,'school'=>$school,'money_delivery'=>100,'money_least'=>600));
        if($res){
            $msg['code']=200;
        }else{
            $msg['code']=653;
        }
        $this->ajaxReturn($msg);
    }
    //删除校区
    public function school_extDelete(){
        $postData = file_get_contents('php://input',true);
        $post = json_decode($postData);
        $id = $post->id;
        if(!is_numeric($id)){
            $msg['code']=601;
            $this->ajaxReturn($msg);
        }
        $school_ext = M('school_ext');
        $result = $school_ext->where(array('id'=>$id))->save(array('status'=>0));
        if($result){
            $msg['code'] = 200;
        }else{
            $msg['code'] = 602;
            $msg['error'] = $school_ext->getError;
        }
        $this->ajaxReturn($msg);
    }
    //删除校区和学校
    public function school_extDeleteAll(){
        $postData = file_get_contents('php://input',true);
        $post = json_decode($postData);
        $id = $post->id;
        if(!is_numeric($id)){
            $msg['code']=601;
            $this->ajaxReturn($msg);
        }
        $school_ext = M('school_ext');
        $school_ext->startTrans();
        $school_ext_data = $school_ext->where(array('id'=>$id))->find();
        $result = $school_ext->where(array('id'=>$id))->save(array('status'=>0));
        $school = M('school');
        $result2 = $school->where(array('id'=>$school_ext_data['school']))->save(array('status'=>0));
        if($result&&$result2){
            $school_ext->commit();
            $msg['code'] = 200;
        }else{
            $school_ext->rollback();
            $msg['code'] = 602;
        }
        $this->ajaxReturn($msg);
    }
    /**
        优惠卷
    */
    public function coupon(){
        // if(!(session('data')['role']=='超级管理员')){
        //     $this->display('/denied');
        //     return ;
        // }
        $this->assign("url",json_encode(array(
                                    'getinfo'=>U("Admin/Product/couponGetinfo"),
                                    'add'=>U('Admin/Product/couponAdd'),
                                    'download'=>U('Admin/Product/couponDownload'),
                                    )));
        $coupon_type=M('coupon_code_type');
        $type=$coupon_type->where(array('status'=>array('in','1,2')))->select();
        $type_data=$this->arrayToIn($type);
        $type2[]=array('id'=>$type_data,'name'=>'全部');
        $type3=array_merge($type2,$type);
        $t=time()*1000+24*3600*1000;
        $e=time()*1000;
        $this->assign('date',array(
            'd_start'=>$e,
            'd_end'=>$t,
        ));
        $this->assign('type',$type3);
        $this->admin=session('data');
        $data=$this->roleCtrl();
        $this->assign($data);
        $this->display('coupon');
    }
    //查询优惠券信息
    public function couponGetinfo(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $status=$post->status;
        $type=$post->type;
        $time_start=strtotime($date_start);
        $time_end=strtotime($date_end);

        // $time_start=1461670000;
        // $time_end=1461884153;
        // $type='1';
        // $status='1';
        $coupon=M('coupon_code_basic');
        $rules=array(
            '`bd_coupon_code_basic`.`status`'=>array('in',$status),
            '`bd_coupon_code_basic`.`create_time`'=>array('between',"$time_start,$time_end"),
            'type'=>array('in',$type),
        );
        $data=$coupon->join('`bd_coupon_code_type` ON `bd_coupon_code_type`.`id`=`bd_coupon_code_basic`.`type`'
            )->field(array(
                '`bd_coupon_code_basic`.`code`',
                '`bd_coupon_code_basic`.`type`',
                '`bd_coupon_code_basic`.`create_time`',
                '`bd_coupon_code_basic`.`end_time`',
                '`bd_coupon_code_basic`.`status`',
                '`bd_coupon_code_type`.`name`',
                '`bd_coupon_code_type`.`money`',
                // '`bd_coupon_code_type`.`status` as type_status',
            ))->where($rules)->select();
            $num=0;
        foreach ($data as &$value) {
            $value['status_name']=$this->doCouponStatus($value['status']);
            $value['date_create']=date('Y-m-d H:i',$value['create_time']);
            $value['date_end']=date('Y-m-d H:i',$value['end_time']);
            $num++;
        }
        if($data){
            $msg['data']=$data;
            $msg['num']=$num;
            $msg['code']=200;
        }else{
            $msg['code']=641;
            $msg['error']='后台无数据';
        }
        $this->ajaxReturn($msg);
    }
    //下载优惠券
    public function couponDownload($data){
        // $postData=file_get_contents('php://input',true);
        $post=json_decode($data);
        $date_start=$post->date_start;
        $date_end=$post->date_end;
        $status=$post->status;
        $type=$post->type;
        $time_start=strtotime($date_start);
        $time_end=strtotime($date_end);

        // $time_start=1461670000;
        // $time_end=1461884153;
        // $type='1';
        // $status='1';
        $coupon=M('coupon_code_basic');
        $rules=array(
            '`bd_coupon_code_basic`.`status`'=>array('in',$status),
            '`bd_coupon_code_basic`.`create_time`'=>array('between',"$time_start,$time_end"),
            'type'=>array('in',$type),
        );
        $data=$coupon->join('`bd_coupon_code_type` ON `bd_coupon_code_type`.`id`=`bd_coupon_code_basic`.`type`'
            )->field(array(
                '`bd_coupon_code_basic`.`code`',
                '`bd_coupon_code_basic`.`type`',
                '`bd_coupon_code_basic`.`create_time`',
                '`bd_coupon_code_basic`.`end_time`',
                '`bd_coupon_code_basic`.`status`',
                '`bd_coupon_code_type`.`name`',
                '`bd_coupon_code_type`.`money`',
                // '`bd_coupon_code_type`.`status` as type_status',
            ))->where($rules)->order('`bd_coupon_code_basic`.`create_time` DESC')->select();
        foreach ($data as &$value) {
            $value['status_name']=$this->doCouponStatus($value['status']);
            $value['date_create']=date('Y-m-d H:i',$value['create_time']);
            $value['date_end']=date('Y-m-d H:i',$value['end_time']);
        }
        $name_excel="优惠券".date('Y-m-d');
        // $name_excel="优惠券";
        $excel=A('Excel');
        // $this->ajaxReturn($data);
        $excel->downloadCouponCode($data,$name_excel);
    }
    //添加优惠码
    public function couponAdd(){
        $postData=file_get_contents('php://input',true);
        $post=json_decode($postData);
        $number=$post->number;
        $date_end=$post->date;
        $type=$post->type;
        if(!is_numeric($number)){
            $msg['code']=644;
            $this->ajaxReturn($msg);
        }
        $time_end=strtotime($date_end);
        $time=time();
        $data=array();
        for ($i=0; $i < $number; $i++) {
            $data[]=array(
                'code'=>'BDYH'.substr(md5($time.$i),8,16),
                'type'=>$type,
                'end_time'=>($time_end+24*3600),
                'status'=>1,
                'create_time'=>$time,
            );
        }
        $rules=array(
            array('code','require','code'),
            array('type','number','type'),
            array('end_time','number','end'),
        );
        $coupon=M('coupon_code_basic');
        if(!$coupon->validate($rules)->create($data)){
            $msg['code']=645;
            $this->ajaxReturn($msg);
        }
        if($coupon->addAll($data)){
            $msg['code']=200;
            // $msg['error']=md5('sddggg',true);
            $this->ajaxReturn($msg);
        }else{
            $msg['code']=646;
            $this->ajaxReturn($msg);
        }
    }
    //处理coupon code 状态
    public function doCouponStatus($status){
        switch ($status) {
            case '1':
                $msg='未使用';
                break;
            case '2':
                $msg='已使用';
                break;
            case '3':
                $msg='超时';
                break;
            default:
                $msg="异常";
                break;
        }
        return $msg;
    }

}
