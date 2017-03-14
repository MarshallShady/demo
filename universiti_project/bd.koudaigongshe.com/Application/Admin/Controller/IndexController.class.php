<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends CommenController{
    /**
        主页
    */
    public function index(){
        $admin=array('name'=>session('data')['name'],
                    'role'=>session('data')['role']);
        $Array['admin']=$admin;
        $Array['url']=array(
                                'url_logout'=>U('admin/login/logout'),
                                'product_school'=>U('admin/product/school'),
                                'product_canteen'=>U('admin/product/canteen'),
                                'product_port'=>U('admin/product/port'),
                                'product_dish'=>U('admin/product/dish'),
                                'product_school_ext'=>U('admin/product/school_ext'),
                                'product_coupon'=>U('admin/product/coupon'),
                                'employee_buildleader'=>U('admin/employee/buildleader'),
                                'employee_deliver'=>U('admin/employee/deliver'),
                                'employee_verify'=>U('admin/employee/verify'),
                                'employee_pack'=>U('admin/employee/packman'),
                                'employee_build'=>U('admin/employee/building'),
                                'employee_bddeliver'=>U('Admin/employee/bddeliver'),
                                'statistic_build'=>U('admin/statistic/build'),
                                'statistic_port'=>U('admin/statistic/port'),
                                'statistic_canteen'=>U('admin/statistic/canteen'),
                                'balance_deliver'=>U('admin/balance/deliver'),
                                'balance_buildleader'=>U('admin/balance/buildleader'),
                                'balance_canteen'=>U('admin/balance/canteen'),
                                'balance_school'=>U('admin/balance/schoolClear'),
                                'balance_bddeliver'=>U('admin/balance/bddeliver'),
                                'statistic_all'=>U('admin/feedback/allOrder'),
                                'feedback_refund'=>U('admin/feedback/refund'),
                                'feedback_history'=>U('Admin/feedback/refundHistory'),
                                'feedback_complain'=>U('Admin/feedback/complain'),
                                'admin_verify'=>U('Admin/admin/verify'),
                                'statistic_order'=>U('Admin/mobile/order'),
                                'statistic_user'=>U('Admin/mobile/user'),
                                'statistic_investigate'=>U('Admin/statistic/investigate'),

                            );
        $this->assign($Array);
        $this->display('index');
    }
}
