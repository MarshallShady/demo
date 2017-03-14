<?php
namespace Mobile\Controller;
use Think\Controller;
class AppController extends Controller {
    public function index($unionid = ''){
        SESSION('unionid', $unionid);
        echo $unionid . '<br>';
        echo SESSION('unionid');
        return ;
    }
}
