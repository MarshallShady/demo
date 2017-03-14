<?php
namespace Admin\Controller;
use Think\Controller;
class ExcelController extends CommenController{

    public function index(){
        $name_excel="test";
        $coupon=M('coupon_code_basic');
        $data=$coupon->limit(0,2500)->select();
        set_time_limit(10);
        Vendor('excel/PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle("优惠券");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '创建时间');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '优惠码');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "类型");
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "到期时间");
        $objPHPExcel->getActiveSheet()->setCellValue('E1', "状态");
        foreach ($data as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($key+2), $value['create_time']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($key+2), $value['code']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($key+2), $value['type']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($key+2), $value['end_time']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($key+2), $value['status']);
        }
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Disposition:attachment;filename=$name_excel.xlsx");
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
    //处理餐厅结算的账单并下载
    public function downloadBalanceCanteen($data,$dish,$name_excel){
        Vendor('excel/PHPExcel');
        set_time_limit(10);
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle("每日金额");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        // $objPHPExcel->getActiveSheet()->setCellValue('B1', "总订单数");
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '总菜品数');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "总饭菜金额(元)");
        foreach ($data as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($key+2), $value['date']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($key+2), $value['num']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($key+2), $value['total_cost']/100);
            // $objPHPExcel->getActiveSheet()->setCellValue('A'.($key+1), $value['total']);
        }
        //设置第二章 sheet  全部菜品
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle("全部菜品");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '餐厅');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', "订单时间");
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "时段");
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "订单ID");
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '菜品ID');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '档口名称');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', "菜名");
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '金额');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', "订单状态");
        $objPHPExcel->getActiveSheet()->setCellValue('J1', "菜品状态");
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '打包费');
        foreach ($dish as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($key+2), $value['canteen_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($key+2), $value['order_date']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($key+2), $value['when_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($key+2), $value['order_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($key+2), $value['dish_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($key+2), $value['port_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($key+2), $value['dish_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($key+2), $value['money']/100);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($key+2), $value['basic_status_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($key+2), $value['dish_status_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($key+2), $value['money_pack']/100);
        }
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Disposition:attachment;filename=$name_excel.xlsx");
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
    //处理餐厅结算的账单并下载2
    public function downloadBalanceCanteen2($data,$dish,$order,$name_excel){
        Vendor('excel/PHPExcel');
        set_time_limit(10);
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle("每日金额");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        // $objPHPExcel->getActiveSheet()->setCellValue('B1', "总订单数");
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '总菜品数');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "总饭菜金额(元)");
        foreach ($data as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($key+2), $value['date']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($key+2), $value['num']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($key+2), $value['total_cost']/100);
            // $objPHPExcel->getActiveSheet()->setCellValue('A'.($key+1), $value['total']);
        }
        //设置第二章 sheet  全部菜品
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle("全部菜品");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '餐厅');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', "订单时间");
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "时段");
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "订单ID");
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '菜品ID');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '档口名称');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', "菜名");
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '金额');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', "订单状态");
        $objPHPExcel->getActiveSheet()->setCellValue('J1', "菜品状态");
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '打包费');
        foreach ($dish as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($key+2), $value['canteen_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($key+2), $value['order_date']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($key+2), $value['when_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($key+2), $value['order_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($key+2), $value['dish_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($key+2), $value['port_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($key+2), $value['dish_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($key+2), $value['money']/100);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($key+2), $value['basic_status_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($key+2), $value['dish_status_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($key+2), $value['money_pack']/100);
        }

        //设置第三章 sheet  全部菜品
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(2);
        $objPHPExcel->getActiveSheet()->setTitle("全部订单表");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '餐厅');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', "订单时间");
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "时段");
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "订单ID");
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '支付金额');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '配送费');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', "订单状态");
        foreach ($order as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($key+2), $value['canteen_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($key+2), $value['order_date']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($key+2), $value['when_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($key+2), $value['order_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($key+2), $value['money']/100);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($key+2), $value['money_delivery']/100);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($key+2), $value['basic_status_name']);
        }
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Disposition:attachment;filename=$name_excel.xlsx");
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
    //下载code优惠券
    public function downloadCouponCode($data,$name_excel){
        // $name_excel="xx.xls";
        set_time_limit(10);
        Vendor('excel/PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle("优惠券");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '创建时间');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '优惠码');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "类型");
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "到期时间");
        $objPHPExcel->getActiveSheet()->setCellValue('E1', "状态");
        foreach ($data as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($key+2), $value['date_create']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($key+2), $value['code']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($key+2), $value['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($key+2), $value['date_end']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($key+2), $value['status_name']);
        }
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Disposition:attachment;filename=$name_excel.xlsx");
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
    //下载校园结算 对楼长总结算时的账单
    public function downloadBalanceSchoolClear($data,$name_excel){
        Vendor('excel/PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle("校园楼长结算");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '下载账单日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', "总订单数");
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '总菜品数');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "总结算(元)");
        $objPHPExcel->getActiveSheet()->setCellValue('A2', date('Y-m-d H:i',time()));
        $objPHPExcel->getActiveSheet()->setCellValue('B2', $data['num_order']);
        $objPHPExcel->getActiveSheet()->setCellValue('C2', $data['num_dish']);
        $objPHPExcel->getActiveSheet()->setCellValue('D2', $data['money']);
        //打印各个楼长的金额
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '学校');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', "结算日期");
        $objPHPExcel->getActiveSheet()->setCellValue('C3', '管理员id');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', "管理员");
        $objPHPExcel->getActiveSheet()->setCellValue('E3', '业务员id');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', "业务员姓名");
        $objPHPExcel->getActiveSheet()->setCellValue('G3', '身份');
        //金额
        $objPHPExcel->getActiveSheet()->setCellValue('H3', "历史总订单数");
        $objPHPExcel->getActiveSheet()->setCellValue('I3', '历史总饭菜数');
        $objPHPExcel->getActiveSheet()->setCellValue('J3', "历史总金额(元)");
        $objPHPExcel->getActiveSheet()->setCellValue('K3', "结算订单数");
        $objPHPExcel->getActiveSheet()->setCellValue('L3', '结算饭菜数');
        $objPHPExcel->getActiveSheet()->setCellValue('M3', "奖金(元)");
        $objPHPExcel->getActiveSheet()->setCellValue('N3', "结算金额(含奖金)(元)");
        foreach ($data['data'] as $key => $value) {
            //打印各个楼长的金额
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($key+4), $value['school_name'].$value['school_ext_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($key+4), $value['date_clear']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($key+4), $value['admin_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($key+4), $value['admin_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($key+4), $value['user_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($key+4), $value['user_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($key+4), $value['role_name']);
            //金额
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($key+4), $value['num_order_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($key+4), $value['num_dish_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($key+4), $value['money_total']/100);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($key+4), $value['num_order_rest']);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.($key+4), $value['num_dish_rest']);
            $objPHPExcel->getActiveSheet()->setCellValue('M'.($key+4), $value['bonus']/100);
            $objPHPExcel->getActiveSheet()->setCellValue('N'.($key+4), $value['money_rest']/100);
        }
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Disposition:attachment;filename=$name_excel.xlsx");
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
}
