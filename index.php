<?php

header('Content-Type:text/html;charset=utf-8');
//error_reporting(E_ALL ^ E_NOTICE);
define('IN_ECM',1);
//项目根路径
define('ROOT_PATH', dirname(__FILE__));
//框架文件路径
define('CORE_PATH', ROOT_PATH . '/Framework/');
//应用公共文件路径
define('COMMON_PATH', ROOT_PATH . '/Common/');
//应用路径
define('APP_PATH', ROOT_PATH . '/Application/');
set_include_path(ROOT_PATH.'/');
spl_autoload_extensions('.php');
/**
 * 类库自动加载
 * @param string $class 对象类名
 * @return void
 */
function __autoload($class){
    $class = get_include_path(). $class;
    $class = str_replace('\\', '/', $class) . '.php';
    var_dump($class);
    require_once($class);
}
spl_autoload_register('__autoload');
include(ROOT_PATH . '/Framework/Common/Function/necessary.php');
//引导文件路径
include(ROOT_PATH . '/Framework/Base/Boot.php');
Boot::run();
?>
