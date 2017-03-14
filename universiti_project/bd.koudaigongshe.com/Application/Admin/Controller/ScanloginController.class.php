<?php
namespace Admin\Controller;
use Think\Controller;
class ScanloginController extends Controller{
    //前端申请页面
    protected $unionid;
    public function index(){
        if(session('status')=='on'){
            $this->success('你已经登入过了',U('admin/index/index/'));
        }else{
            $url="http://bd.koudaigongshe.com/admin/scanlogin/check";
            redirect("https://open.weixin.qq.com/connect/qrconnect?appid=wx56325c3419e5572e&redirect_uri=".$url."&response_type=code&scope=snsapi_login&state=1#wechat_redirect");
        }
    }
    //验证登入
    public function check(){
        $open = json_decode(file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx56325c3419e5572e&secret=01d89588702efc960328b9a9ac230157&code=' . I('code') . '&grant_type=authorization_code'));
        $unionid=$open->unionid;
        $admin_account=M('admin_account');
        $res=$admin_account->where(array('unionid'=>$unionid,'status'=>1,'role'=>array('neq','13')))->find();
        if($res){
                session('status','on');
                $admin_account->where(array('id'=>$res['id']))->save(array('time_recent'=>time()));
                $data=$this->getAdminInfo($res);
                session('data',$data);
                redirect(U('Admin/index/index'));
        }else{
            // echo "<script>alert('登入错误')</script>";
            // redirect(U("Admin/scanlogin/index"));
            $this->success('登入失败,请重试',U("Admin/scanlogin/index"));
        }
    }
    //微信直接验证登入
    public function checkWechat(){
        if (!SESSION('unionid')) {
                // 没有openid，判断是否是OAUTH
                $secret=C('APPSECRET');
                // $appid='wx4d1b293d79f694c7';
                $appid=C('APPID');
            if (!I('code')) {
                // 不是OAUTH，去OAUTH
                $redirect_url='http://bd.koudaigongshe.com/admin/scanlogin/checkWechat';
                redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_url.'&response_type=code&scope=snsapi_base#wechat_redirect');
            } else {
                // 是OAUTH，
                $wechat = json_decode(file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . "&secret=".$secret . "&code=".I('code') . '&grant_type=authorization_code'), true);
                if (!$wechat) die;//获取微信信息失败
                $url="https://api.weixin.qq.com/sns/userinfo?access_token=".$wechat['access_token']."&openid=".$wechat['openid']."&lang=zh_CN";
                $data=json_decode(file_get_contents($url));
                SESSION('unionid', $data->unionid);
                redirect(U('Admin/Scanlogin/checkWechat'));
            }
        }
        $unionid=session('unionid');
        $admin_account=M('admin_account');
        $res=$admin_account->where(array('unionid'=>$unionid,'status'=>1,'role'=>array('neq','13')))->find();
        if($res){
                session('status','on');
                $admin_account->where(array('id'=>$res['id']))->save(array('time_recent'=>time()));
                $data=$this->getAdminInfo($res);
                session('data',$data);
                redirect(U('Admin/index/index'));
        }else{
            // echo "<script>alert('登入错误')</script>";
            // redirect(U("Admin/scanlogin/index"));
            $this->success('登入失败,请重试',U("Admin/scanlogin/index"));
        }
    }
    //微信直接验证登入接口
    public function checkWechatApi($url = ""){
        $url=base64_decode($url);
        if (!SESSION('unionid')) {
                // 没有openid，判断是否是OAUTH
                $secret=C('APPSECRET');
                // $appid='wx4d1b293d79f694c7';
                $appid=C('APPID');
            if (!I('code')) {
                // 不是OAUTH，去OAUTH
                $redirect_url='http://bd.koudaigongshe.com/admin/scanlogin/checkWechat';
                redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_url.'&response_type=code&scope=snsapi_base#wechat_redirect');
            } else {
                // 是OAUTH，
                $wechat = json_decode(file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . "&secret=".$secret . "&code=".I('code') . '&grant_type=authorization_code'), true);
                if (!$wechat) die;//获取微信信息失败
                $url="https://api.weixin.qq.com/sns/userinfo?access_token=".$wechat['access_token']."&openid=".$wechat['openid']."&lang=zh_CN";
                $data=json_decode(file_get_contents($url));
                SESSION('unionid', $data->unionid);
                // redirect(U('Admin/Scanlogin/checkWechatApi'));
            }
        }
        $unionid=session('unionid');
        $admin_account=M('admin_account');
        $res=$admin_account->where(array('unionid'=>$unionid,'status'=>1,'role'=>array('neq','13')))->find();
        if($res){
                session('status','on');
                $admin_account->where(array('id'=>$res['id']))->save(array('time_recent'=>time()));
                $data=$this->getAdminInfo($res);
                session('data',$data);
                redirect($url);
        }else{
            // echo "<script>alert('登入错误')</script>";
            // redirect(U("Admin/scanlogin/index"));
            // $this->success('登入失败,请重试',U("Admin/scanlogin/index"));
        }
    }
    //测试别人的接口
    public function checkWechatApiTest(){

        $unionid=session('unionid');
        $unionid="ofxsbuB-JC6yd6UbMB9zkUABQnLA";
        // $unionid = "ofxsbuHKFQD9rBPURX4iPzKry5W4"; //Larry
        $admin_account=M('admin_account');
        $res=$admin_account->where(array('unionid'=>$unionid,'status'=>1,'role'=>array('neq','13')))->find();
        if($res){
                session('status','on');
                $admin_account->where(array('id'=>$res['id']))->save(array('time_recent'=>time()));
                $data=$this->getAdminInfo($res);
                session('data',$data);
                redirect(U('Admin/index/index'));
        }else{
            // echo "<script>alert('登入错误')</script>";
            // redirect(U("Admin/scanlogin/index"));
            $this->success('登入失败,请重试',U("Admin/scanlogin/index"));
        }
    }
    public function orderIndex(){
        $url=base64_encode(U('Admin/Mobile/order'));
        $url2=U("Admin/Scanlogin/checkWechatApi?url=$url");
        $url3=explode('.',$url2);
        redirect($url3[0]);
    }
    public function userIndex(){
        $url=base64_encode(U('Admin/Mobile/user'));
        $url2=U("Admin/Scanlogin/checkWechatApi?url=$url");
        $url3=explode('.',$url2);
        redirect($url3[0]);
    }
    //获取并封装后台管理员的详细资料：名字 角色 权限
    public function getAdminInfo($admin=array()){
        $role=M('role');
        $role_data=$role->where("id='".$admin['role']."'")->find();
        $admin_data=array('name'=>$admin['name'],
                            'school_ext'=>$admin['school_ext'],
                            'canteen'=>$admin['canteen'],
                            'port'=>$admin['port'],
                            'role'=>$role_data['name'],
                            'role_id'=>$admin['id'],
                            'school'=>'all',
                            'province'=>$admin['province'],
                            );
        return $admin_data;
    }
    public function apply(){
        // session('unionid','22222');
        if (!SESSION('unionid')) {
                // 没有openid，判断是否是OAUTH
                $secret=C('APPSECRET');
                // $appid='wx4d1b293d79f694c7';
                $appid=C('APPID');
            if (!I('code')) {
                // 不是OAUTH，去OAUTH
                $redirect_url='http://bd.koudaigongshe.com/admin/scanlogin/apply';
                redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_url.'&response_type=code&scope=snsapi_userinfo#wechat_redirect');
            } else {
                // 是OAUTH，
                $wechat = json_decode(file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . "&secret=".$secret . "&code=".I('code') . '&grant_type=authorization_code'), true);
                if (!$wechat) die;//获取微信信息失败
                SESSION('unionid', $wechat['unionid']);
                redirect(U('Admin/Scanlogin/apply'));
            }
        }
        $data=$this->getAllList();
        $this->assign($data);
        $this->assign('url',array(
            'apply'=>U('Admin/Scanlogin/applyApi'),
        ));
        //获取媒体列表
        $media = M('dict_media');
        $media_data = $media->where(array('status'=>array('in','1,2')))->select();
        $this->assign('media',$media_data);
        // $this->unionid=session('unionid');
        $this->assign('unionid',session('unionid'));
        $this->display('apply');
    }
    //申请提交接口
    public function applyApi(){
        $postData=file_get_contents("php://input",true);
        $post=json_decode($postData);
        $name=$post->name;
        $mobile=$post->mobile;
        $role=$post->role;
        $unionid=$post->unionid;
        $province=$post->province;
        $school_ext=$post->school_ext;
        $canteen=$post->canteen;
        $port=$post->port;
        $aim = $post->media;
        $aim_name = $post->media_name;
        $data=array(
            'name'=>$name,
            'mobile'=>$mobile,
            'unionid'=>$unionid,
            'role'=>$role,
            'aim'=>$aim,
            'aim_name'=>$aim_name,
            'status'=>2,
            'time'=>time(),
        );
        $rules=array(
            array('name','require','name'),
            array('mobile','require','mobile'),
            array('unionid','require','unionid'),
            array('role','number','role'),
            array('province','require','province'),
            array('school_ext','require','school_ext'),
            array('canteen','require','canteen'),
            array('port','require','port'),
            array('aim','number','aim'),
            array('aim_name','require','aim_name'),
        );
        switch ($role) {
            case 4:
                $data['province']='all';
                $data['school_ext']='all';
                $data['canteen']='all';
                $data['port']='all';
                break;
            case 5:
                $data['province'] = $province;
                $data['school_ext']=$school_ext;
                $data['canteen']='all';
                $data['port']='all';
                break;
            case 6:
                $data['province']=$province;
                $data['school_ext']=$school_ext;
                $data['canteen']=$canteen;
                $data['port']=$port;
                break;
            case 9:
                $data['province']=$province;
                $data['school_ext']=$school_ext;
                $data['canteen']=$canteen;
                $data['port']='all';
                break;
            case 10:
                $data['province']='all';
                $data['school_ext']='all';
                $data['canteen']='all';
                $data['port']='all';
                break;
            case 12:
                $data['province'] = $province;
                $data['school_ext']='all';
                $data['canteen']='all';
                $data['port']='all';
                break;
            case 13:
                $data['province']='all';
                $data['school_ext']='all';
                $data['canteen']='all';
                $data['port']='all';
                break;
            default:
                $msg['code']=630;
                $this->ajaxReturn($msg);
                break;
        }

        $apply=M('admin_apply');
        if(!$apply->validate($rules)->create($data)){
            $msg['code']=631;
            $msg['error']=$apply->getError();
            $this->ajaxReturn($msg);
        }
        $result=$apply->add($data);
        if($result){
            $msg['code']=200;
        }else{
            $msg['code']=632;
        }
        $this->ajaxReturn($msg);
    }
    //获取全部联级列表
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
