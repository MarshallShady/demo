<?php
namespace Mobile\Controller;
use Think\Controller;
class AdminController extends CommonController {

    public function index(){
        // 出单员可以操作的档口
        p($this->position);
    }
}
