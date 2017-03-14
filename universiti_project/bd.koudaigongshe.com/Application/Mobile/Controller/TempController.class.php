<?php
namespace Mobile\Controller;
use Think\Controller;
class TempController extends CommonController {
    // ofxsbuDQTi8jxGlvX3oe_YRF5RtA 赵航
    // ofxsbuHBswAakOM72-tieqIM6Y3E 冯林源
    // 杜渊
    public function temp1()
    {
        if (!IS_POST) {
            $this->display();
        } else {
            $return = [
                'status' => 0,
            ];
            $data = I('');
            $data['unionid'] = $this->unionid;
            $data['create_time'] = time();
            $temp1 = M('temp1');
            if ($temp1->add($data) == false) {
                $return['status'] = 2;
            } else {
                $return['status'] = 1;
                $return['data'] = $temp1->select();
            }
            $this->ajaxReturn(json_encode($return));

        }
    }
    // public function index(){
    //     $message = [
    //         'first' => [
    //             'value' => '开始配送了，美食马上到！',
    //             'color' => '#398DEE',
    //         ],
    //         'OrderSn' => [
    //             'value' => '2345',
    //             'color' => '#000000',
    //         ],
    //         'OrderStatus' => [
    //             'value' => '开始派送',
    //             'color' => '#FF6550',
    //         ],
    //         'remark' => [
    //             'value' => '改不改啊',
    //             'color' => '#9F9F9F',
    //         ],
    //     ];
    //     SendTempletMessage('otHIZs3bNbhsCrM8FDniSSTWE2vY', 'http://bd.koudaigongshe.com/mobile/personal/detail/id/' . $value['id'], $message, 1);
    // }

}
