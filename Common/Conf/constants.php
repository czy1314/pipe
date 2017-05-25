<?php

/**
 *    应用配置文件
 * @author    LorenLei
 */
defined('DEBUG_MODE') or defined('DEBUG_MODE') or define('DEBUG_MODE', 1);
defined('CHARSET') or define('CHARSET', 'utf-8');
defined('CACHE_TIME') or define('CACHE_TIME', 1);
defined('DEFAULT_MOD') or define('DEFAULT_MOD', 'user');
defined('DEFAULT_APP') or define('DEFAULT_APP', 'welcome');
defined('DEFAULT_ACT') or define('DEFAULT_ACT', 'index');
defined('LANG') or define('LANG', 'sc-utf-8');
defined('SITE_URL') or define('SITE_URL', 'http://localhost:83/wxmb');
defined('SITE_NAME') or define('SITE_NAME', '51分期');
defined('DB') or define('DB','mysql');
defined('DB_CONFIG') or define('DB_CONFIG', 'mysql://root:root@localhost:3306/hichat');
defined('DB_PREFIX') or define('DB_PREFIX', '');
defined('COOKIE_DOMAIN') or define('COOKIE_DOMAIN', '');
defined('COOKIE_PATH') or define('COOKIE_PATH', '/');
defined('CACHE_SERVER') or define('CACHE_SERVER', 'default');
defined('MEMBER_TYPE') or define('MEMBER_TYPE', 'default');
defined('DEVELOPER') or define('DEVELOPER', 'nanfengq@sina.com');
defined('SESSION_TYPE') or define('SESSION_TYPE', 'mysql');
defined('SESSION_MEMCACHED') or define('SESSION_MEMCACHED', 'localhost:11211');
defined('CACHE_MEMCACHED') or define('CACHE_MEMCACHED', 'localhost:11211');
/* 记录程序启动时间 */
defined('START_TIME') or define ( 'START_TIME', pipe_microtime () );

/* 判断请求方式 */
defined('IS_POST') or define ( 'IS_POST', (strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST') );

/* 判断请求方式 */
defined('IN_ECM') or define ( 'IN_ECM', true );

/* 定义PHP_SELF常量 */
defined('PHP_SELF') or define ( 'PHP_SELF', htmlentities ( isset ( $_SERVER ['PHP_SELF'] ) ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME'] ) );

/* 当前Boot程序版本 */
defined('VERSION') or define ( 'VERSION', '2.3.0' );


defined('IMAGE_FILE_TYPE') or define ( 'IMAGE_FILE_TYPE', 'gif|jpg|jpeg|png' ); // 图片类型，上传图片时使用

?>
