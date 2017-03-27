<?php           	
    
header('Content-Type:text/html;charset=utf-8');
//error_reporting(E_ALL ^ E_NOTICE);

if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
	 define('WX_WAP', 1);
	 define('Boot_WAP',1);
}
function define_all($source) {
    if (is_string ( $source )) {
        /* 导入数组 */
        $source = include ($source);
    }
    if (! is_array ( $source )) {
        /* 不是数组，无法定义 */
        return false;
    }
    foreach ( $source as $key => $value ) {
        if (is_string ( $value ) || is_numeric ( $value ) || is_bool ( $value ) || is_null ( $value )) {
            /* 如果是可被定义的，则定义 */
            define ( strtoupper ( $key ), $value );
        }
    }
}
//项目根路径
define('ROOT_PATH', dirname(__FILE__));
define('MODULE', 'User');
//项目公共文件路径
define('COMMON_PATH', ROOT_PATH . '/Common/');
//项目文件路径
//define('APP_PATH', ROOT_PATH . '/Application/'. MODULE);
//配置文件路径
define_all(ROOT_PATH . '/Common/Conf/config.php');
include(ROOT_PATH . '/Common/Common/global_fnc.php');
//引导文件路径
include(ROOT_PATH . '/Framework/Core/base/Boot.php');
Boot::run();
?>
