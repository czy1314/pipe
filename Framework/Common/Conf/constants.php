<?php
/* 记录程序启动时间 */
define ( 'START_TIME', pipe_microtime () );

/* 判断请求方式 */
define ( 'IS_POST', (strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST') );

/* 判断请求方式 */
define ( 'IN_ECM', true );

/* 定义PHP_SELF常量 */
define ( 'PHP_SELF', htmlentities ( isset ( $_SERVER ['PHP_SELF'] ) ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME'] ) );

/* 当前Boot程序版本 */
define ( 'VERSION', '2.3.0' );

/* 当前Boot程序Release */
define ( 'RELEASE', '20120918' );

define ( 'IMAGE_FILE_TYPE', 'gif|jpg|jpeg|png' ); // 图片类型，上传图片时使用
/* 特殊文章分类ID */
define ( 'STORE_NAV', - 1 ); // 地区加盟商导航
define ( 'ACATE_HELP', 1 ); // 商城帮助
define ( 'ACATE_NOTICE', 2 ); // 商城快讯（公告）
define ( 'ACATE_SYSTEM', 3 ); // 内置文章
/* 系统文章分类code字段 */
define ( 'ACC_NOTICE', 'notice' ); // acategory表中code字段为notice时——商城公告类别
define ( 'ACC_SYSTEM', 'system' ); // acategory表中code字段为system时——内置文章类别
define ( 'ACC_HELP', 'help' ); // acategory表中code字段为help时——商城帮助类别

/* 邮件的优先级 */
define ( 'MAIL_PRIORITY_LOW', 1 );
define ( 'MAIL_PRIORITY_MID', 2 );
define ( 'MAIL_PRIORITY_HIGH', 3 );

/* 发送邮件的协议类型 */
define ( 'MAIL_PROTOCOL_LOCAL', 0, true );
define ( 'MAIL_PROTOCOL_SMTP', 1, true );

/* 数据调用的类型 */
define ( 'TYPE_GOODS', 1 );

/* 上传文件归属 */
define ( 'BELONG_ARTICLE', 1 );
define ( 'BELONG_USER', 2 );

/* 二级域名开关 */
! defined ( 'ENABLED_SUBDOMAIN' ) && define ( 'ENABLED_SUBDOMAIN', 0 );
/* 环境 */
define ( 'CHARSET', substr ( LANG, 3 ) );
define ( 'IS_AJAX', isset ( $_REQUEST ['ajax'] ) );
/* 短消息的标志 */
define ( 'MSG_SYSTEM', 0 ); // 系统消息
/* 通知类型 */
define ( 'NOTICE_MAIL', 1 ); // 邮件通知
define ( 'NOTICE_MSG', 2 ); // 站内短消息