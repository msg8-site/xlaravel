<?php

//全局常量定义
define('COMM_SYSFLAG', basename((dirname(__DIR__))));  //定义系统标识名，用于session前缀等标识
define('COMM_SECIP', true);  //是否为双层ip，默认 false 仅判断REMOTE_ADDR
define('COMM_DBLOCK', false);  //数据库迁移回滚锁，防止误删除， true 允许回滚删除数据表，其他值如 false ，不允许回滚删除
define('COMM_SYSNAME', 'xlaravel后台框架');  //平台名称
define('COMM_CODETYPE', 'imgcode');  //验证类型， google 为谷歌动态码， imgcode 为图形验证码

//定义存储session的变量名称
define('SESS_USERNAME', COMM_SYSFLAG . '_username');  //用户名
define('SESS_MORECKEY', COMM_SYSFLAG . '_moreclientkey');  //用户登录标识

