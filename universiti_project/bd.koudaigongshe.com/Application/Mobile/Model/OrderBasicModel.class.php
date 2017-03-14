<?php
namespace Mobile\Model;
use Think\Model;
class OrderBasicModel extends Model {
	// 自动验证设置
	protected $_validate = array(
		array('name', '2,10', '姓名长度错误', 1, 'length'),
		// array('name', '', '您已报名', 0, 'unique', self::MODEL_INSERT),
		array('name',' /[\x{4e00}-\x{9fa5}]/u','姓名请使用使用汉字！',1,'regex',3),
		array('mobile', 'number', '手机号必须为数字', 1, ),
		array('mobile', 11, '手机号格式错误', 1, 'length'),
		array('building', 'require', '楼号必须存在', 1),
		array('building', 'number', '楼号格式错误', 1),
		array('dormitory', 'require', '寝室号必须存在', 1),
	);

    protected $_auto = array (
        array('create_time', 'time', 1, 'function'),
        array('edit_time', 'time', 3, 'function'),
	);

    protected $insertFields = ['unionid', 'payway', 'transaction_id', 'name', 'mobile', 'building', 'dormitory', 'when', 'money', 'create_time', 'pack_time', 'deliver_time', 'get_time', 'edit_time', 'status'];
	protected $updateFields = ['payway', 'transaction_id', 'name', 'mobile', 'building', 'dormitory', 'when', 'money', 'pack_time', 'deliver_time', 'get_time', 'edit_time', 'status'];

    public function ge(){
        echo 'wtf';
    }

}
