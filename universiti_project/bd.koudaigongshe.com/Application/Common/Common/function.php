<?php
function p($data){
	// var_dump($data);die;
	dump($data, 1, '<pre>', 0);
	die;
}

//打印输出数组信息
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}
/**
    微信重定向
*/
function wechatRedirect($uri, $scope = 'snsapi_base'){
    redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('APPID') . '&redirect_uri=' . U($uri, '', true, true) . '&response_type=code&scope=' . $scope . '&state=1#wechat_redirect');
}

/**
    获取微信access_token
*/
function getAccessToken(){
    $wechat = M('wechat_basic')->select();
    if ($wechat[0]['time'] > time()) return $wechat[0]['access_token'];
    else {
        // 超时重新获取
        $wechat = json_decode(file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . C('APPID') . '&secret=' . C('APPSECRET')), true);
        if (!$wechat['access_token']) die;
        else {
            $wechat['time'] = time() + 5400;
            if (M('wechat_basic')->where('id=1')->save($wechat) !== false) {
                return $wechat['access_token'];
            } else {
                echo '更新access_token失败';
                die;
            }
        }
    }
}

/**
    交换订单状态
    # 1未支付，2支付超时取消，3主动取消，4已支付待派送，5已派送待送达，6已送达待确认，7已确认，8退款中，9退款完成，10退款驳回
*/
function getOrderStatus($id){
    switch ($id) {
        case 1:
            return '未支付';
        case 2:
            return '支付超时';
        case 3:
            return '已取消';
        case 4:
            return '待出单';
        case 5:
            return '派送中';
        case 6:
            return '已送达';
        case 7:
            return '已确认';
        case 8:
            return '退款中';
        case 9:
            return '退款完成';
        case 10:
            return '退款失败';
        case 11:
            return '待派送';
        case 12:
            return '已被抢单';
        case 13:
            return '抢单送达';
        default:
            return '错误';
    }
}


define('MEMBER_CODE', '58457904425c11e5b13752540008b6e6');
define('FEYIN_KEY', 'b723019d');
define('DEVICE_NO', '4600403475003094');


//以下2项是平台相关的设置，您不需要更改
define('FEYIN_HOST','my.feyin.net');
define('FEYIN_PORT', 80);

//$msgNo = testSendFormatedMessage();

//$msgNo = testSendFreeMessage();

//testQueryState($msgNo);

//testListDevice();

//testListException();

//die;

function SendTempletMessage($openid, $url, $data, $template){

	if ($template == 1) $template = 'I3_RPryGzIjduQPxVq_OkOlcdZgWv4BJ4ii-oEpz_Po';
	elseif($template == 2){
		$template = "cScYGEluA8rHwAnEf73GV1lZEcfIGj3MsUWkSeHerWU";
	}
	$message = json_encode([
		'touser' => $openid,
		'template_id' => $template,
		'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('APPID') . '&redirect_uri=' . $url . '&response_type=code&scope=snsapi_base&state=1#wechat_redirect',
		'data' => $data,
	]);
	$opts = [
		'http' => [
			'method' => "POST",
			'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
						"Content-length:".strlen($message)."\r\n" .
						"Cookie: foo=bar\r\n" .
						"\r\n",
			'content' => $message,
		]
	];
	$cxContext = stream_context_create($opts);
	$sFile = file_get_contents('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . getAccessToken(), false, $cxContext);
}

function testSendFreeMessage($xiaopiao, $deviceNo){
	$msgNo = time()+1;
	/*
	 自由格式的打印内容
	*/
	$freeMessage = array(
		'memberCode'=> '58457904425c11e5b13752540008b6e6',
		'msgDetail'=>$xiaopiao,
		'deviceNo'=>$deviceNo,
		'msgNo'=>$msgNo,
	);

	$is_print=sendFreeMessage($freeMessage);

	return $is_print;
}

function sendFreeMessage($msg) {
	$msg['reqTime'] = number_format(1000*time(), 0, '', '');
	$content = $msg['memberCode'].$msg['msgDetail'].$msg['deviceNo'].$msg['msgNo'].$msg['reqTime'].'b723019d';
	$msg['securityCode'] = md5($content);
	$msg['mode']=2;

	return sendMessage($msg);
}
function sendMessage($msgInfo) {
    vendor('Feiyin/HttpClient#class');
	$client = new \HttpClient('my.feyin.net', 80);
	if(!$client->post('/api/sendMsg',$msgInfo)){ //提交失败
		return 'faild';
	}
	else{
		return $client->getContent();
	}
}


?>
