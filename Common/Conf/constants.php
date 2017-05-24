<?php

/**
 *    应用配置文件
 * @author    LorenLei
 */
define('DEBUG_MODE', 1);
define('CACHE_TIME', 1);
define('DEFAULT_MOD', 'user');
define('DEFAULT_APP', 'welcome');
define('DEFAULT_ACT', 'index');
define('LANG', 'sc-utf-8');
define('RUNTIEM_URL', 'http://www.daogoge.com/recept.php?url');
define('SITE_URL', 'http://localhost:83/wxmb');
define('SITE_NAME', '51分期');
define('DB','mysql');
define('DB_CONFIG', 'mysql://root:root@localhost:3306/hichat');
define('DB_PREFIX', '');
define('LANG', 'sc-utf-8');
define('COOKIE_DOMAIN', '');
define('COOKIE_PATH', '/');
define('PIPE_KEY', 'af980b1d86f0ce0afaae6685fcedda9a');
define('MALL_SITE_ID', 'EMPEGHt1e7zoRy3b');
define('ENABLED_GZIP', 0);
define('DEBUG_MODE', 0);
define('CACHE_SERVER', 'default');
define('MEMBER_TYPE', 'default');
define('ENABLED_SUBDOMAIN', 0);
define('DEVELOPER', 'nanfengq@sina.com');
define('SUBDOMAIN_SUFFIX', '');
define('SESSION_TYPE', 'mysql');
define('SESSION_MEMCACHED', 'localhost:11211');
define('CACHE_MEMCACHED', 'localhost:11211');
define('ROBOT_KEY', 'c6ef6a42a2b35affbc2d5bbf580abb15');
define('ROBOT_URL', 'http://www.tuling123.com/openapi/api?key=KEY&info=INFO');
define('FUNC', 'editor_multimedia,coupon,enable_radar');
define('RGION_DETAIL', '黑龙江哈尔滨南岗区');
define('TEL', '12345678');
define('RECEIVE_EMAIL', '784139247@qq.com');
define('RECEIVE_TEL', '13244550508');
define('SALT', 'tqqm8@vnd');
define('MSG_UID', 'j19920118');
define('MSG_PWD', '51fenqi');
define('PAYBACK_LINE', 5);
define('FORMAT', 'Y-m-d h:i:s');
define('ZZX_USER', '784139247@qq.com');
define('ZZX_PWD', 'CC2004cc');
define('SPAN_DAYS', 30);
/* 记录程序启动时间 */
define ('START_TIME', pipe_microtime());
/* 判断请求方式 */
define ('IS_POST', (strtoupper($_SERVER ['REQUEST_METHOD']) == 'POST'));
/* 定义PHP_SELF常量 */
define ('PHP_SELF', htmlentities(isset ($_SERVER ['PHP_SELF']) ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME']));
/* 当前Boot程序版本 */
define ('VERSION', '2.3.0');
define ('IMAGE_FILE_TYPE', 'gif|jpg|jpeg|png'); // 图片类型，上传图片时使用

?>
