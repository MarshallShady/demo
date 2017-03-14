CREATE TABLE `bd_admin`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT  ,
    `name` VARCHAR(16) NOT NULL DEFAULT '未知' COMMENT '后台用户名称',
    `username` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '登入账号',
    `password` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '登入密码',
    `role` VARCHAR(16) NOT NULL DEFAULT ''COMMENT '用户角色',
    `shool` VARCHAR(32) NOT NULL DEFAULT ''COMMENT '低权限用户所属学校',
    `shool_ext` VARCHAR(32) NOT NULL DEFAULT ''COMMENT '低权限用户所属食堂',
    `shool_ext2` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '低权限用户所属档口',
    PRIMARY KEY(`id`)
) DEFAULT CHARSET=UTF8 ENGINE=INNODB COMMENT '后台登录用户表';
