<?php
error_reporting(E_ALL ^ E_NOTICE);
/* 应用根目录 */
define('APP_ROOT', dirname(__FILE__).'/');          //该常量只在后台使用
define('APP_PATH', ROOT_PATH . 'Application/Admin');
define('ROOT_PATH', dirname(APP_ROOT));   //该常量是lib要求的


//项目根路径
define('ROOT_PATH', dirname(__FILE__));
//项目公共文件路径
define('COMMON_PATH', ROOT_PATH . '/Common/');
define('MODULE', 'Admin');
//项目文件路径
define('APP_PATH', ROOT_PATH . 'Application/'. MODULE);
//引导文件路径
define('IN_BACKEND', true);
include(ROOT_PATH . '/Framework/Core/Boot.php');

/* 定义配置信息 */
define_all(ROOT_PATH . '/Common/Conf/config.php');

/* 启动Boot */
$ec = new Boot();
$ec->run();

?>