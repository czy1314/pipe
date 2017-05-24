<?php
/**
 *    Boot框架核心文件，包含最基础的类与函数
*    @author  LorenLei
 */
require_once ROOT_PATH . '/Framework/Util/Conf.php';
require_once ROOT_PATH . '/Framework/Controller/ZmApp.php';
class Boot
{

	/* 启动 */
	static  function run($config = array())
	{
        $query_string = isset ( $_SERVER ['argv'] [0] ) ? $_SERVER ['argv'] [0] : $_SERVER ['QUERY_STRING'];
        if (! isset ( $_SERVER ['REQUEST_URI'] )) {
            $_SERVER ['REQUEST_URI'] = PHP_SELF . '?' . $query_string;
        } else {
            if (strpos ( $_SERVER ['REQUEST_URI'], '?' ) === false && $query_string) {
                $_SERVER ['REQUEST_URI'] .= '?' . $query_string;
            }
        }


		/* 加载初始化文件 */
        define_all(ROOT_PATH . '/Framework/Common/Conf/BaseController.php');     //基础控制器类
		require(ROOT_PATH . '/Framework/Model/BaseModel.php');   //模型基础类
		/* 数据过滤 */
		if (!get_magic_quotes_gpc())
		{
			$_GET   = addslashes_deep($_GET);
			$_POST  = addslashes_deep($_POST);
			$_COOKIE= addslashes_deep($_COOKIE);
		}





		$is_ajax = isset($_GET['ajax'])? 1: 0;
		if($is_ajax)
		{
			define('IS_AJAX',1);
		}

		/* 请求转发 */
		$default_app = DEFAULT_APP ? DEFAULT_APP : 'welcome';
		$default_act = DEFAULT_ACT ? DEFAULT_ACT : 'index';
        $default_mod = DEFAULT_MOD ? DEFAULT_MOD : 'user';
		//匹配任何非单词字符。等价于“[^A-Za-z0-9_]”。
        $mod    = ucfirst(!empty($_REQUEST['m']) ? preg_replace('/(\W+)/', '', $_REQUEST['m']) : $default_mod);
		$app    = ucfirst(!empty($_REQUEST['c']) ? preg_replace('/(\W+)/', '', $_REQUEST['c']) : $default_app);
		$act    = ucfirst(!empty($_REQUEST['a']) ? preg_replace('/(\W+)/', '', $_REQUEST['a']) : $default_act);
		$app_file = APP_PATH. "{$mod}/Controller/{$app}.php";
		if (!is_file($app_file))
		{
			exit('The controller is not found!');
		}
		require($app_file);
        define('MOD', $mod);
		define('APP', $app);
		define('ACT', $act);
		$app_class_name = 'Application\\'.$mod.'\\Controller\\'.$app;
		/* 实例化控制器 */
		$app     = new $app_class_name();
		c($app);
        //转发至对应的Action
		$app->do_action($act);

		$app->destruct();

	}
}




?>