<?php 
/**
 *    Boot框架核心文件，包含最基础的类与函数
*    @author    LiuLei
*/

include ROOT_PATH . '/Common/Common/common.php';
include ROOT_PATH . '/Framework/Lib/time.lib.php';
include ROOT_PATH . '/Framework/Core/Util/Conf.php';
include ROOT_PATH . '/Framework/Core/Util/Lang.php';
include ROOT_PATH . '/Common/Controller/Zm.base.php';;

/*
include ROOT_PATH . '/Common/Util/plugin.base.php';*/

/*---------------------以下是系统底层基础类及工具-----------------------*/
class Boot
{

	/* 启动 */
	static  function run($config = array())
	{
		
		/* 加载初始化文件 */
		//require(ROOT_PATH . '/Framework/Core/controller/BaseController.php');     //基础控制器类
		require(ROOT_PATH . '/Framework/Core/model/BaseModel.php');   //模型基础类

		if (!empty($config['external_libs']))
		{
			foreach ($config['external_libs'] as $lib)
			{
				require($lib);
			}
		}
		/* 数据过滤 */
		if (!get_magic_quotes_gpc())
		{
			$_GET   = addslashes_deep($_GET);
			$_POST  = addslashes_deep($_POST);
			$_COOKIE= addslashes_deep($_COOKIE);
			/*
			 if ( $_FILES)
			 {
			$_FILES = addslashes_deep($_FILES);
			}
			*/
		}

		if(isset($_GET['id']) && !empty($_GET['id']))
		{
			$_GET['id']=str_replace(' ','',$_GET['id']);
		}
		if(isset($_POST['id']) && !empty($_POST['id']))
		{
			$_POST['id']=str_replace(' ','',$_POST['id']);
		}

		if(isset($_GET['sort']) && !empty($_GET['sort']))
		{
			$_GET['sort']=str_replace(' ','',$_GET['sort']);
		}
		if(isset($_POST['sort']) && !empty($_POST['sort']))
		{
			$_POST['sort']=str_replace(' ','',$_POST['sort']);
		}

		$is_ajax = isset($_GET['ajax'])? 1: 0;
		if($is_ajax)
		{
			define('IS_AJAX',1);
		}



		/* 请求转发 */
		$default_app = DEFAULT_APP ? DEFAULT_APP : 'member';
		$default_act = DEFAULT_ACT ? DEFAULT_ACT : 'index';
        $default_mod = DEFAULT_MOD ? DEFAULT_MOD : 'user';
		//匹配任何非单词字符。等价于“[^A-Za-z0-9_]”。
        $mod    = !empty($_REQUEST['mod']) ? preg_replace('/(\W+)/', '', $_REQUEST['mod']) : $default_mod;
		$app    = !empty($_REQUEST['app']) ? preg_replace('/(\W+)/', '', $_REQUEST['app']) : $default_app;
		$act    = !empty($_REQUEST['act']) ? preg_replace('/(\W+)/', '', $_REQUEST['act']) : $default_act;
        define('APP_PATH',ROOT_PATH . '/Application/' .ucfirst( $mod ));
		$app_file = APP_PATH . "/Controller/{$app}.php";

		if (!is_file($app_file))
		{
			exit('Missing controller');
		}

		require($app_file);

        define('MOD', $mod);
		define('APP', $app);
		define('ACT', $act);
		$app_class_name = ucfirst($app) . 'App';


		/* 实例化控制器 */
		$app     = new $app_class_name();
		c($app);
		$app->do_action($act);        //转发至对应的Action
		 
		$app->destruct();

	}
}




?>