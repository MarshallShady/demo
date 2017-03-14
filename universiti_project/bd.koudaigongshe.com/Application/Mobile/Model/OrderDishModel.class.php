<?php
namespace Mobile\Model;
use Think\Model;
class OrderDishModel extends Model {
	// 自动验证设置

    CREATE TABLE `bd_order_dish`(
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '序号',
        `order` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单号',
        `name` VARCHAR (20) NOT NULL DEFAULT '未知' COMMENT '名称',
        `image` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '配图',
        `port` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '档口',
        -- `bind` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '捆绑',
        `content` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '描述',
        `dish` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '菜品',
        `pack_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '出单时间',
        `dish_num` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '菜品数量',
        `dish_money` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '单价',
        `money` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '总价',
        `status` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态',#出单状态
        INDEX(`order`),
        PRIMARY KEY(`id`)
    ) DEFAULT CHARSET = UTF8 ENGINE = INNODB COMMENT '订单菜品信息';
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
    protected $_map = array(
        'name' =>'username', // 把表单中name映射到数据表的username字段
        'mail'  =>'email', // 把表单中的mail映射到数据表的email字段
    );
    protected $insertFields = ['unionid', 'payway', 'transaction_id', 'name', 'mobile', 'building', 'dormitory', 'when', 'money', 'create_time', 'pack_time', 'deliver_time', 'get_time', 'edit_time', 'status'];
	protected $updateFields = ['payway', 'transaction_id', 'name', 'mobile', 'building', 'dormitory', 'when', 'money', 'pack_time', 'deliver_time', 'get_time', 'edit_time', 'status'];

    public function ge(){
        echo 'wtf';
    }

}
