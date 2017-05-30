<?php
//temp

 function _exception_handler($exception)
{
    exit('Exception: '.$exception->getMessage().$exception->getFile().$exception->getLine()); // EXIT_ERROR
}
//temp
function _error_handler($exception)
{

}
//temp
function _shutdown_handler($exception)
{

}

set_error_handler('_error_handler');
set_exception_handler('_exception_handler');
register_shutdown_function('_shutdown_handler');

if(file_exists(ROOT_PATH . '/Framework/Common/Conf/constants.php')){
    require_once ROOT_PATH . '/Framework/Common/Conf/constants.php';
}
//temp，没做覆盖处理
if(file_exists(ROOT_PATH . '/Common/Conf/constants.php')){
    require_once ROOT_PATH . '/Common/Conf/constants.php';
}

$query_string = isset ( $_SERVER ['argv'] [0] ) ? $_SERVER ['argv'] [0] : $_SERVER ['QUERY_STRING'];
if (! isset ( $_SERVER ['REQUEST_URI'] )) {
    $_SERVER ['REQUEST_URI'] = PHP_SELF . '?' . $query_string;
} else {
    if (strpos ( $_SERVER ['REQUEST_URI'], '?' ) === false && $query_string) {
        $_SERVER ['REQUEST_URI'] .= '?' . $query_string;
    }
}

define('IS_AJAX',! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
define ( 'CORE_LANG_PATH',ROOT_PATH.'Lang/'  );
define ( 'IS_CLI',PHP_SAPI === 'cli' OR defined('STDIN'));

/**
 * Boot框架全局函数文件
 */

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
    require_once($class);
}
spl_autoload_register('__autoload');

class Lang
{
    static function  instance(){
        return load('Lang');
    }

}