<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){

	   	$outList = M('tari');

   		if( isset($_GET['data_type']) && $_GET['data_type'] == 2 ){
   			$this->assign('navOn2','weui-bar__item_on');
	   		$this->assign('navOn1','');
	   		$result = $outList->where('data_type=2')->select();
   		} else {
	   		$this->assign('navOn1','weui-bar__item_on');
	   		$this->assign('navOn2','');
			  $result = $outList->where('data_type=1')->select();
   		}
   		
   		// dump( $result );

   		$this->assign('result',$result);

        $this->display();
    }
}