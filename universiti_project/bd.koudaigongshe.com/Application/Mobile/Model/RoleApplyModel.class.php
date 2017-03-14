<?php
namespace Mobile\Model;
use Think\Model;
class RoleApplyModel extends Model {
	// 自动验证设置
	protected $_validate = [
		['unionid', 'require', '错误', 1],
		['name', '2,10', '姓名长度错误', 0, 'length'],
		['name',' /[\x{4e00}-\x{9fa5}]/u','姓名请使用使用汉字！',0,'regex',3],
		['mobile', 'number', '手机号必须为数字', 0],
		['mobile', 11, '手机号格式错误', 0, 'length'],
		['school', 'require', '学校必须存在', 0],
		['school', 'number', '学校格式错误', 0],
		['school_ext', 'require', '区域必须存在', 0],
		['school_ext', 'number', '区域格式错误', 0],
		['building', 'require', '楼号必须存在', 0],
		['building', 'number', '楼号格式错误', 0],
		['dormitory', 'require', '寝室号必须存在', 0],
        ['job', 'require', '申请信息', 0]
	];

	// protected $_auto = array (
    //     array('create_time', 'time', 1, 'function'),
    //     array('edit_time', 'time', 3, 'function'),
	// );

	// protected $insertFields = ['unionid', 'nickname', 'sex', 'headimgurl', 'subscribe_time', 'remark', 'groupid', 'name', 'mobile', 'building', 'dormitory', 'group', 'has_role_right', 'has_special_right'];
	// protected $updateFields = ['nickname', 'sex', 'headimgurl', 'remark', 'groupid', 'name', 'mobile', 'building', 'dormitory', 'group', 'has_role_right', 'has_special_right'];
	//
	//
    // public function ge(){
    //     $user_role = M('user_role')->where(['openid' => $this->openid])->select();
	// 	$role_right = M('role_right')->where('');
	// 	foreach ($user_role as $key => $value) $temp .= $value['role'];
	// 	M('role_right')->where('id' => $user_role['role'])->select();
	//
    // }

}
