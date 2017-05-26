<?php

header('Content-Type:text/html;charset=GBK');
//error_reporting(E_ALL ^ E_NOTICE);
define('PIPE',1);
//项目根路径
define('ROOT_PATH', dirname(__FILE__));
//框架文件路径
define('CORE_PATH', ROOT_PATH . '/Framework/');
//应用公共文件路径
define('COMMON_PATH', ROOT_PATH . '/Common/');
//应用路径
define('APP_PATH', ROOT_PATH . '/Application/');
//加载核心文件
require_once(CORE_PATH . 'Base/Init.php');
require_once CORE_PATH . 'Base/Lang.php';
require_once CORE_PATH.'Base/Conf.php';
require_once(CORE_PATH. 'Common/Function/necessary.php');
require_once(CORE_PATH . 'Base/Boot.php');
Boot::run();

