<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
class InDataController extends Controller {
    public function index(){

    	if( isset($_GET['data_type']) ){
    		// echo $_GET['data_type'];
    		// echo $_GET['link'];

    		$user = M('tari');

            $data = array(
                'creattime' => time(),
                'title' => $_GET['title'],
                'synopsis' => $_GET['synopsis'],
                'from_author' => $_GET['from_author'],
                'data_type' => $_GET['data_type'],
                'link' => $_GET['link']
            );
    		$user->add( $data );

    		// dump($data);
            echo "<script> location = 'http://' + location.hostname + '/koudai/kdxs/index.php/Home/InData/succeed' </script>";
    	} else {
        	$this->display();
    	}
    }

    public function clickRate(){

    	if( isset($_GET['id']) ){

    		$user = M('tari');
    		$id = $_GET['id'];
    		$data = $user->where("id=".$id)->select();

    		$t = $data[0]["click_rate"];
    		$t++;

    		$result = array('click_rate' => $t );
    		$user->where("id=1")->save($result);

    		echo "succeed";
	    }
	}

    public function succeed(){
        $this->display();
    }
}