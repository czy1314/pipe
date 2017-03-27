<?php
namespace  Common\Controller;
/**
 *    ZmApp
 *
 *    @author    LiuLei
 *    @usage    none
 */
use \Framework\lib\SessionProcessor;
//include ROOT_PATH . '/Framework/Core/controller/BaseController.php';
use \Framework\Core\controller\BaseController;
class ZmApp extends BaseController
{
    var $outcall;
    function __construct()
    { 
        $this->ZmApp();
    }
    function ZmApp()
    { 
        parent::__construct();
       
        if (!defined('MODULE')) // 临时处理方案，此处不应对模块进行特殊处理
        {
            /* GZIP */
            if ($this->gzip_enabled())
            {
                ob_start('ob_gzhandler');
            }
            else
            {
                ob_start();
            }

            /* 非utf8转码 */
            if (CHARSET != 'utf-8' && isset($_REQUEST['ajax']))
            {
                $_FILES = ecm_iconv_deep('utf-8', CHARSET, $_FILES);
                $_GET = ecm_iconv_deep('utf-8', CHARSET, $_GET);
                $_POST = ecm_iconv_deep('utf-8', CHARSET, $_POST);
            }

            /* 载入配置项 */
            $setting=af('settings');

            Conf::load($setting->getAll());

            /* 初始化访问者(放在此可能产生问题) */
                        
            $this->_init_visitor();
            
            /* 计划任务守护进程 */
           
            $this->_run_cron();
        }
        
    }
    function _init_visitor()
    {
    	
    }

    /**
     *    初始化Session
     *
     *    @author    LiuLei
     *    @param    none
     *    @return    void
     */
    function _init_session()
    {
    	
        import('session.lib');
        if(!defined('SESSION_TYPE'))
        {
           define('SESSION_TYPE','mysql');
        }
        if (SESSION_TYPE == 'mysql' || defined('IN_BACKEND'))
        {
			if(isset($_GET['ssid'])){
				$get_ssid = $_GET['ssid'];
			}else{
				$get_ssid = '';
			}
			
            $this->_session =new SessionProcessor(db(), '`ecm_sessions`', '`ecm_sessions_data`', 'ECM_ID',$get_ssid);
                    
            /* 清理超时的购物车项目 */
            $this->_session->add_related_table('`ecm_cart`', 'cart', 'session_id', 'user_id=0');
        }
        
        else if (SESSION_TYPE == 'memcached')
        {
            $this->_session=new MemcacheSession(SESSION_MEMCACHED, 'ECM_ID');
        }
        else
        {
            exit('Unkown session type.');
        }
        
        define('SESS_ID', $this->_session->get_session_id());
      
        $this->_session->my_session_start();
        env('session', $this->_session);
    }
    function _config_view()
    {
        $this->_view->caching       = ((DEBUG_MODE & 1) == 0);  // 是否缓存
        $this->_view->force_compile = ((DEBUG_MODE & 2) == 2);  // 是否需要强制编译
        $this->_view->direct_output = ((DEBUG_MODE & 4) == 4);  // 是否直接输出
        $this->_view->gzip          = (defined('ENABLED_GZIP') && ENABLED_GZIP === 1);
        $this->_view->lib_base      = site_url() . '/Application/'.MODULE.'/templates/libraries/javascript';
    }

    /**
     *    转发至模块
     *
     *    @author    LiuLei
     *    @param    none
     *    @return    void
     */
    function do_action($action)
    {
        /* 指定了要运行的模块则调用模块控制器 */
    	
        (!empty($_GET['module']) && !defined('MODULE')) && $action = 'run_module';
        parent::do_action($action);
    }

    function _run_action()
    {
        
    	 
    	
        /* 
        if (!$this->visitor->i_can('do_action'))
        {
            if (!$this->visitor->has_login)
            {
                $this->login();
            }
            else
            {
                $this->show_warning($this->visitor->get_error());
            }

            return;
        } */
        
        if ($this->_hook('on_run_action'))
        {
            return;
        }
        parent::_run_action();

        if ($this->_hook('end_run_action'))
        {
            return;
        }
    }

    function run_module()
    {
        $module_name = empty($_REQUEST['module']) ? false : strtolower(preg_replace('/(\W+)/', '', $_REQUEST['module']));
        if (!$module_name)
        {
            $this->show_warning('no_such_module');

            return;
        }
        $file = defined('IN_BACKEND') ? 'admin' : 'index';
        $module_class_file = ROOT_PATH . '/external/modules/' . $module_name . '/' . $file . '.module.php';
        require(APP_PATH . 'Common/Util/module.base.php');
        require($module_class_file);
        define('MODULE', $module_name);
        $module_class_name = ucfirst($module_name) . 'Module';

        /* 判断模块是否启用 */
        $model_module=m('module');
        $find_data = $model_module->find('index:' . $module_name);
        if (empty($find_data))
        {
            /* 没有安装 */
            $this->show_warning('no_such_module');

            return;
        }
        $info = current($find_data);
        if (!$info['enabled'])
        {
            /* 尚未启用 */
            $this->show_warning('module_disabled');

            return;
        }

        /* 加载模块配置 */
        Conf::load(array($module_name . '_config' => unserialize($info['module_config'])));

        /* 运行模块 */
        $module = new $module_class_name();
        
        
        $module->do_action(ACT);
        $module->destruct();
    }


    function login()
    {
        $this->display('login.html');
    }
    function logout()
    {
        $this->visitor->logout();
    }
    function jslang($lang)
    {
        header('Content-Encoding:'.CHARSET);
        header("Content-Type: application/x-javascript\n");
        header("Expires: " .date(DATE_RFC822, strtotime("+1 hour")). "\n");
        if (!$lang)
        {
            echo 'var lang = null;';
        }
        else
        {
            echo 'var lang = ' . ecm_json_encode($lang) . ';';
            echo <<<EOT
lang.get = function(key){
    eval('var langKey = lang.' + key);
    if(typeof(langKey) == 'undefined'){
        return key;
    }else{
        return langKey;
    }
}
EOT;
        }
    }

    /**
     *    插件
     *
     *    @author    LiuLei
     *    @param    none
     *    @return    void
     */
    function _hook($event, $data = array())
    {
        if ($this->outcall)
        {
            return;
        }
        static $plugins = null;
        $conf_file = ROOT_PATH . '/data/plugins.inc.php';
        if ($plugins === null)
        {
            is_file($conf_file) && $plugins = include($conf_file);
            if (!is_array($plugins))
            {
                $plugins = false;
            }
        }
        if (!isset($plugins[$event]))
        {
            return null;
        }

        /* 获取可用插件列表 */
        $plugin_list = $plugins[$event];
        if (empty($plugin_list))
        {
            return null;
        }
        foreach ($plugin_list as $plugin_name => $plugin_info)
        {
            $plugin_main_file = ROOT_PATH . "/external/plugins/{$plugin_name}/main.plugin.php";
            if (is_file($plugin_main_file))
            {
                include_once($plugin_main_file);
            }
            $plugin_class_name = ucfirst($plugin_name) . 'Plugin';
            $plugin = new $plugin_class_name($data, $plugin_info);
            $this->outcall = true;

            /* 返回一个结果，若要停止当前控制器流程则会返回true */
            $stop_flow = $this->_run_plugin($plugin);
            $plugin = null;
            $this->outcall = false;
            /* 停止原控制器流程 */
            if ($stop_flow)
            {
                return $stop_flow;
            }
        }
    }

    /**
     *    运行插件
     *
     *    @author    LiuLei
     *    @param     Plugin $plugin
     *    @return    void
     */
    function _run_plugin(&$plugin)
    {
        return $plugin->execute();
    }

    /**
     *    head标签内的内容
     *
     *    @author    LiuLei
     *    @param     string $contents
     *    @return    void
     */
    function headtag($string)
    {
        $this->_init_view();
        $this->assign('_head_tags', $this->_view->fetch('str:' . $string));
    }

    /**
     *    导入资源到模板
     *
     *    @author    LiuLei
     *    @param     mixed $resources
     *    @return    string
     */
    function import_resource($resources, $spec_type = null)
    {
        $headtag = '';
        if (is_string($resources) || $spec_type)
        {
            !$spec_type && $spec_type = 'script';
            $resources = $this->_get_resource_data($resources);
            foreach ($resources as $params)
            {
                $headtag .= $this->_get_resource_code($spec_type, $params) . "\r\n";
            }
            $this->headtag($headtag);
        }
        elseif (is_array($resources))
        {
            foreach ($resources as $type => $res)
            {
                $headtag .= $this->import_resource($res, $type);
            }
            $this->headtag($headtag);
        }

        return $headtag;
    }
    
    /**
     * 配置seo信息
     *
     * @param array/string $seo_info
     * @return void
     */
    function _config_seo($seo_info, $ext_info = null)
    {
        if (is_string($seo_info))
        {
            $this->_assign_seo($seo_info, $ext_info);
        }
        elseif (is_array($seo_info))
        {
            foreach ($seo_info as $type => $info)
            {
                $this->_assign_seo($type, $info);
            }
        }
    }
    
    function _assign_seo($type, $info)
    {
        $this->_init_view();
        $_seo_info = $this->_view->get_template_vars('_seo_info');
        if (is_array($_seo_info))
        {
            $_seo_info[$type] = $info;
        }
        else
        {
            $_seo_info = array($type => $info);
        }
        $this->assign('_seo_info', $_seo_info);
        $this->assign('page_seo', $this->_get_seo_code($_seo_info));
    }
    
    function _get_seo_code($_seo_info)
    {
        $html = '';
        foreach ($_seo_info as $type => $info)
        {
            $info = trim(htmlspecialchars($info));
            switch ($type)
            {
                case 'title' :
                    $html .= "<{$type}>{$info}</{$type}>";

                    $this->assign('title',$info);
                    break;
                case 'description' :
                case 'keywords' :
                default :
                    $html .= "<meta name=\"{$type}\" content=\"{$info}\" />";
                    break;
            }
            $html .= "\r\n";
        }        
        return $html;
    }

    /**
     *    获取资源数据
     *
     *    @author    LiuLei
     *    @param     mixed $resources
     *    @return    array
     */
    function _get_resource_data($resources)
    {
        $return = array();
        if (is_string($resources))
        {
            $items = explode(',', $resources);
            array_walk($items, create_function('&$val, $key', '$val = trim($val);'));
            foreach ($items as $path)
            {
                $return[] = array('path' => $path, 'attr' => '');
            }
        }
        elseif (is_array($resources))
        {
            foreach ($resources as $item)
            {
                !isset($item['attr']) && $item['attr'] = '';
                $return[] = $item;
            }
        }

        return $return;
    }

    /**
     *    获取资源文件的HTML代码
     *
     *    @author    LiuLei
     *    @param     string $type
     *    @param     array  $params
     *    @return    string
     */
    function _get_resource_code($type, $params)
    {
        switch ($type)
        {
            case 'script':
                $pre = '<script charset="utf-8" type="text/javascript"';
                $path= ' src="' . $this->_get_resource_url($params['path']) . '"';
                $attr= ' ' . $params['attr'];
                $tail= '></script>';
            break;
            case 'style':
                $pre = '<link rel="stylesheet" type="text/css"';
                $path= ' href="' . $this->_get_resource_url($params['path']) . '"';
                $attr= ' ' . $params['attr'];
                $tail= ' />';
            break;
        }
        $html = $pre . $path . $attr . $tail;

        return $html;
    }

    /**
     *    获取真实的资源路径
     *
     *    @author    LiuLei
     *    @param     string $res
     *    @return    void
     */
    function _get_resource_url($res)
    {
        $res_par = explode(':', $res);
        $url_type = $res_par[0];
        $return  = '';
        switch ($url_type)
        {
            case 'url':
                $return = $res_par[1];
            break;
            case 'res':
                $return = '{res file="' . $res_par[1] . '"}';
            break;
            default:
                $res_path = empty($res_par[1]) ? $res : $res_par[1];
                $return = '{lib file="' . $res_path . '"}';
            break;
        }

        return $return;
    }

    function display($f)
    {
        if ($this->_hook('on_display', array('display_file' => & $f)))
        {
            return;
        }
        $this->assign('site_url', SITE_URL);
        $this->assign('real_site_url', defined('IN_BACKEND') ? dirname(site_url()) : site_url());
        $this->assign('Boot_version', VERSION);
        $this->assign('random_number', rand());

        /* 语言项 */
        $this->assign('lang', Lang::get());

        /* 用户信息 */
        $this->assign('visitor', isset($this->visitor) ? $this->visitor->info : array());

        
        $this->assign('charset', CHARSET);
        $this->assign('price_format', Conf::get('price_format'));
        $this->assign('async_sendmail', $this->_async_sendmail());
        $this->_assign_query_info();
        parent::display($f);

        if ($this->_hook('end_display', array('display_file' => & $f)))
        {
            return;
        }
    }

    /* 页面查询信息 */
    function _assign_query_info()
    {
        $query_time = ecm_microtime() - START_TIME;

        $this->assign('query_time', $query_time);
        $db=db();
        $this->assign('query_count', $db->_query_count);
              
        $this->assign('query_user_count', $this->_session->get_users_count());

        /* 内存占用情况 */
        if (function_exists('memory_get_usage'))
        {
            $this->assign('memory_info', memory_get_usage() / 1048576);
        }

        $this->assign('gzip_enabled', $this->gzip_enabled());
        $this->assign('site_domain', urlencode(get_domain()));
        $this->assign('ecm_version', VERSION . ' ' . RELEASE);
    }

    function gzip_enabled()
    {
        static $enabled_gzip = NULL;

        if ($enabled_gzip === NULL)
        {
            $enabled_gzip = (defined('ENABLED_GZIP') && ENABLED_GZIP === 1 && function_exists('ob_gzhandler'));
        }

        return $enabled_gzip;
    }

    /**
     *    显示错误警告
     *
     *    @author    LiuLei
     *    @param    none
     *    @return    void
     */
    function show_warning()
    {
    	
        $args = func_get_args();       
        call_user_func_array('show_warning', $args);
    }


    /**
     *    显示提示消息
     *
     *    @author    LiuLei
     *    @return    void
     */
    function show_message()
    {
        $args = func_get_args();
        call_user_func_array('show_message', $args);
    }
    
    function show_warning_sql($args)
    {
    	$this->show_warning($this->get_errors_str($args));
    	return ;
    }
    /**
     * 显示数据层错误提示
     * $args Array
     */
    function get_errors_str($args)
    {
    	$result = $args;
    	if(is_array($args)){
	    	foreach ($args as $error)
	    	{
	    		$result .=$error['obj'].'  '.$error['msg'].'<br/>';
	    	}	
    	}
    	return $result;
    }
    /**
     * Make a error message by JSON format
     *
     * @param   string  $msg
     *
     * @return  void
     */
    function json_error ($msg='', $retval=null, $jqremote = false)
    {
        if (!empty($msg))
        {
            $msg = Lang::get($msg);
        }
        $result = array('done' => false , 'msg' => $msg);
        if (isset($retval)) $result['retval'] = $retval;

        $this->json_header();
        $json = ecm_json_encode($result);
        if ($jqremote === false)
        {
            $jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
        }
        if ($jqremote)
        {
            $json = $jqremote . '(' . $json . ')';
        }

        echo $json;
    }

    /**
     * Make a successfully message
     *
     * @param   mixed   $retval
     * @param   string  $msg
     *
     * @return  void
     */
    function json_result ($retval = '', $msg = '', $jqremote = false)
    {
        if (!empty($msg))
        {
            $msg = Lang::get($msg);
        }
        $this->json_header();
        $json = ecm_json_encode(array('done' => true , 'msg' => $msg , 'retval' => $retval));
        if ($jqremote === false)
        {
            $jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
        }
        if ($jqremote)
        {
            $json = $jqremote . '(' . $json . ')';
        }

        echo $json;
    }

    /**
     * Send a Header
     *
     * @author weberliu
     *
     * @return  void
     */
    function json_header()
    {
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Content-type:text/plain;charset=" . CHARSET, true);
    }

    /**
     *    验证码
     *
     *    @author    LiuLei
     *    @return    void
     */
    function _captcha($width, $height)
    {
        import('captcha.lib');
        $word = generate_code();
        $_SESSION['captcha'] = base64_encode($word);
        $code = new Captcha(array(
            'width' => $width,
            'height'=> $height,
        ));
        $code->display($word);
    }

    function ajax_get_code_()
    {
    	
    		$tel = trim($_POST['phone_mob']);    
    		if(is_tel($tel))
    		{    			
    			$code = get_code(6);    			
    			$encode = crypt($code,SALT);
    			$_SESSION[$tel.'_code'] = $encode;    			
    			 
    			$result = $this->send_msg($tel,'欢迎注册51分期，您的验证码为：'.$code);   	
    			       
    			if($result){
					
					$this->pop_message_($encode);
					
				}else{
					$this->pop_message_('获取验证码失败');
				}
    			return ;
    		}
    		else{
    			       
    			$this->pop_warning_('请填写正确的手机号码！');
    		}
    
    }
    function ajax_get_code_find_pwd()
    {
    	 
    	$tel = trim($_POST['phone_mob']);
    	if(is_tel($tel))
    	{
    		$code = get_code(6);
    		$encode = crypt($code,SALT);
    		$_SESSION[$tel.'_code'] = $encode;
    		$result = $this->send_msg($tel,'您正在修改登录密码，验证码为：'.$code);
    		
    		if($result){
			     		
    			$this->pop_message_($encode);
    		}else{
    			$this->pop_message_('获取验证码失败');
    		}
    		
    		return ;
    	}
    	else{
    		$this->pop_warning_('请填写正确的手机号码！');
    		return ;
    	}
    
    }
    	
    
    /**
     *    获取分页信息
     *
     *    @author    LiuLei
     *    @return    array
     */
    function _get_page($page_per = 10)
    {
        $page = empty($_REQUEST['page']) ? 1 : intval($_REQUEST['page']);
        $start = ($page -1) * $page_per;

        return array('limit' => "{$start},{$page_per}", 'curr_page' => $page, 'pageper' => $page_per);
    }

    /**
     * 格式化分页信息
     * @param   array   $page
     * @param   int     $num    显示几页的链接
     */
    function _format_page(&$page, $num = 7)
    {
        $page['page_count'] = ceil($page['item_count'] / $page['pageper']);
        $mid = ceil($num / 2) - 1;
        if ($page['page_count'] <= $num)
        {
            $from = 1;
            $to   = $page['page_count'];
        }
        else
        {
            $from = $page['curr_page'] <= $mid ? 1 : $page['curr_page'] - $mid + 1;
            $to   = $from + $num - 1;
            $to > $page['page_count'] && $to = $page['page_count'];
        }

        /*
        if (preg_match('/[&|\?]?page=\w+/i', $_SERVER['REQUEST_URI']) > 0)
        {
            $url_format = preg_replace('/[&|\?]?page=\w+/i', '', $_SERVER['REQUEST_URI']);
        }
        else
        {
            $url_format = $_SERVER['REQUEST_URI'];
        }
        */

        /* 生成app=goods&act=view之类的URL */
        if (preg_match('/[&|\?]?page=\w+/i', $_SERVER['QUERY_STRING']) > 0)
        {
            $url_format = preg_replace('/[&|\?]?page=\w+/i', '', $_SERVER['QUERY_STRING']);
        }
        else
        {
            $url_format = $_SERVER['QUERY_STRING'];
        }

        $page['page_links'] = array();
        $page['first_link'] = ''; // 首页链接        
        $page['first_suspen'] = ''; // 首页省略号
        $page['last_link'] = ''; // 尾页链接
        $page['last_suspen'] = ''; // 尾页省略号
        for ($i = $from; $i <= $to; $i++)
        {
            $page['page_links'][$i] = url("{$url_format}&page={$i}");
        }
        if (($page['curr_page'] - $from) < ($page['curr_page'] -1) && $page['page_count'] > $num)
        {
            $page['first_link'] = url("{$url_format}&page=1");
            if (($page['curr_page'] -1) - ($page['curr_page'] - $from) != 1)
            {
                $page['first_suspen'] = '..';
            }
        }
        if (($to - $page['curr_page']) < ($page['page_count'] - $page['curr_page']) && $page['page_count'] > $num)
        {
            $page['last_link'] = url("{$url_format}&page=" . $page['page_count']);
            if (($page['page_count'] - $page['curr_page']) - ($to - $page['curr_page']) != 1)
            {
                $page['last_suspen'] = '..';
            }
        }

        $page['prev_link'] = $page['curr_page'] > $from ? url("{$url_format}&page=" . ($page['curr_page'] - 1)) : "";
        $page['next_link'] = $page['curr_page'] < $to ? url("{$url_format}&page=" . ($page['curr_page'] + 1)) : "";
    }

    /**
     *    获取查询条件
     *
     *    @author    LiuLei
     *    @param    none
     *    @return    void
     */
    function _get_query_conditions($query_item){
        $str = '';
        $query = array();
        foreach ($query_item as $options)
        {
            if (is_string($options))
            {
                $field = $options;
                $options['field'] = $field;
                $options['name']  = $field;
            }
            !isset($options['equal']) && $options['equal'] = '=';
            !isset($options['assoc']) && $options['assoc'] = 'AND';
            !isset($options['type'])  && $options['type']  = 'string';
            !isset($options['name'])  && $options['name']  = $options['field'];
            !isset($options['handler']) && $options['handler'] = 'trim';
            if (isset($_GET[$options['name']]))
            {
                $input = $_GET[$options['name']];
                $handler = $options['handler'];
                $value = ($input == '' ? $input : $handler($input));
                if ($value === '' || $value === false)  //若未输入，未选择，或者经过$handler处理失败就跳过
                {
                    continue;
                }
                strtoupper($options['equal']) == 'LIKE' && $value = "%{$value}%";
                if ($options['type'] != 'numeric')
                {
                    $value = "'{$value}'";      //加上单引号，安全第一
                }
                else
                {
                    $value = floatval($value);  //安全起见，将其转换成浮点型
                }
                $str .= " {$options['assoc']} {$options['field']} {$options['equal']} {$value}";
                $query[$options['name']] = $input;
            }
        }
       
        $this->assign('query', stripslashes_deep($query));

        return $str;
    }

    /**
     *    使用编辑器
     *
     *    @author    LiuLei
     *    @param     array $params
     *    @return    string
     */
    function _build_editor($params = array())
    {
        $name = isset($params['name']) ?  $params['name'] : null;
        $theme = isset($params['theme']) ?  $params['theme'] : 'normal';
        $ext_js = isset($params['ext_js']) ? $params['ext_js'] : true;
        $content_css = isset($params['content_css']) ? 'content_css:"' . $params['content_css'] . '",' : null;
        $if_media = false;
        $visit = $this->visitor->check_do_action();
        $store_id = isset($visit) ? intval($visit) : 0;
        $privs = $this->visitor->get('privs');
        if (!empty($privs))
        {
            if ($privs == 'all')
            {
                $if_media = true;
            }
            else
            {
                $privs_array = explode(',', $privs);
                if (in_array('article|all', $privs_array))
                {
                    $if_media = true;
                }
            }
        }
       

        $include_js = $ext_js ? '<script type="text/javascript" src="{lib file="tiny_mce/tiny_mce.js"}"></script>' : '';

        /* 指定哪个(些)textarea需要编辑器 */
        if ($name === null)
        {
            $mode = 'mode:"textareas",';
        }
        else
        {
            $mode = 'mode:"exact",elements:"' . $name . '",';
        }

        /* 指定使用哪种主题 */
        $themes = array(
            'normal'    =>  'plugins:"inlinepopups,preview,fullscreen,paste'.($if_media ? ',media' : '' ).'",
            theme:"advanced",
            theme_advanced_buttons1:"code,fullscreen'.($content_css ? ',preview' : '' ).',removeformat,|,bold,italic,underline,strikethrough,|," +
                "formatselect,fontsizeselect,|,forecolor,backcolor",
            theme_advanced_buttons2:"bullist,numlist,|,outdent,indent,blockquote,|,justifyleft,justifycenter," +
                "justifyright,justifyfull,|,link,unlink,charmap,image,|,pastetext,pasteword,|,undo,redo,|,media",
            theme_advanced_buttons3 : "",',
            'simple'    =>  'theme:"simple",',
        );
        switch ($theme)
        {
            case 'simple':
                $theme_config = $themes['simple'];
            break;
            case 'normal':
                $theme_config = $themes['normal'];
            break;
            default:
                $theme_config = $themes['normal'];
            break;
        }
        /* 配置界面语言 */
        $_lang = substr(LANG, 0, 2);
        switch ($_lang)
        {
            case 'sc':
                $lang = 'zh_cn';
            break;
            case 'tc':
                $lang = 'zh';
            break;
            case 'en':
                $lang = 'en';
            break;
            default:
                $lang = 'zh_cn';
            break;
        }

        /* 输出 */
        $str = <<<EOT
$include_js
<script type="text/javascript">
    tinyMCE.init({
        {$mode}
        {$theme_config}
        {$content_css}
        language:"{$lang}",
        convert_urls : false,
        relative_urls : false,
        remove_script_host : false,
        theme_advanced_toolbar_location:"top",
        theme_advanced_toolbar_align:"left"
});
</script>
EOT;

        return $this->_view->fetch('str:' . $str);;
    }

    /**
     *    使用swfupload
     *
     *    @author    Hyber
     *    @param     array $params
     *    @return    string
     */
    function _build_upload($params = array())
    {
        $belong = isset($params['belong']) ? $params['belong'] : 0; //上传文件所属模型
        $item_id = isset($params['item_id']) ? $params['item_id']: 0; //所属模型的ID
        $file_size_limit = isset($params['file_size_limit']) ? $params['file_size_limit']: '2 MB'; //默认最大2M
        $button_text = isset($params['button_text']) ? Lang::get($params['button_text']) : Lang::get('bat_upload'); //上传按钮文本
        $image_file_type = isset($params['image_file_type']) ? $params['image_file_type'] : IMAGE_FILE_TYPE;
        $upload_url = isset($params['upload_url']) ? $params['upload_url'] : 'index.php?app=swfupload';
        $button_id = isset($params['button_id']) ? $params['button_id'] : 'spanButtonPlaceholder';
        $progress_id = isset($params['progress_id']) ? $params['progress_id'] : 'divFileProgressContainer';
        $if_multirow = isset($params['if_multirow']) ? $params['if_multirow'] : 0;
        $define = isset($params['obj']) ? 'var ' . $params['obj'] . ';' : '';
        $assign = isset($params['obj']) ? $params['obj'] . ' = ' : '';
        $ext_js = isset($params['ext_js']) ? $params['ext_js'] : true;
        $ext_css = isset($params['ext_css']) ? $params['ext_css'] : true;

        $include_js = $ext_js ? '<script type="text/javascript" charset="utf-8" src="{lib file="swfupload/swfupload.js"}"></script>
<script type="text/javascript" charset="utf-8" src="{lib file="swfupload/js/handlers.js"}"></script>' : '';
        $include_css = $ext_css ? '<link type="text/css" rel="stylesheet" href="{lib file="swfupload/css/default.css"}"/>' : '';
        /* 允许类型 */
        $file_types = '';
        $image_file_type = explode('|', $image_file_type);
        foreach ($image_file_type as $type)
        {
            $file_types .=  '*.' . $type . ';';
        }
        $file_types = trim($file_types, ';');
        $str = <<<EOT

{$include_js}
{$include_css}
<script type="text/javascript">
{$define}
$(function(){
    {$assign}new SWFUpload({
        upload_url: "{$upload_url}",
        flash_url: "{lib file="swfupload/swfupload.swf"}",
        post_params: {
            "ECM_ID": "{$_COOKIE['ECM_ID']}",
            "HTTP_USER_AGENT":"{$_SERVER['HTTP_USER_AGENT']}",
            'belong': {$belong},
            'item_id': {$item_id},
            'ajax': 1
        },
        file_size_limit: "{$file_size_limit}",
        file_types: "{$file_types}",
        custom_settings: {
            upload_target: "{$progress_id}",
            if_multirow: {$if_multirow}
        },

        // Button Settings
        button_image_url: "{lib file="swfupload/images/SmallSpyGlassWithTransperancy_17x18.png"}",
        button_width: 86,
        button_height: 18,
        button_text: '<span class="button">{$button_text}</span>',
        button_text_style: '.button { font-family: Helvetica, Arial, sans-serif; font-size: 12pt; font-weight: bold; color: #3F3D3E; } .buttonSmall { font-size: 10pt; }',
        button_text_top_padding: 0,
        button_text_left_padding: 18,
        button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
        button_cursor: SWFUpload.CURSOR.HAND,

        // The event handler functions are defined in handlers.js
        file_queue_error_handler: fileQueueError,
        file_dialog_complete_handler: fileDialogComplete,
        upload_progress_handler: uploadProgress,
        upload_error_handler: uploadError,
        upload_success_handler: uploadSuccess,
        upload_complete_handler: uploadComplete,
        button_placeholder_id: "{$button_id}",
        file_queued_handler : fileQueued
    });
});
</script>
EOT;
        return $this->_view->fetch('str:' . $str);
    }

    /**
     *    发送邮件
     *
     *    @author    LiuLei
     *    @param     mixed  $to
     *    @param     string $subject
     *    @param     string $message
     *    @param     int    $priority
     *    @return    void
     */
    function _mailto($to, $subject, $message, $priority = MAIL_PRIORITY_MID)
    {    //echo 88888;
        /* 加入邮件队列，并通知需要发送 */
        $model_mailqueue=m('mailqueue');
        $mails = array();
        //echo 2;
        $to_emails = is_array($to) ? $to : array($to);
        foreach ($to_emails as $_to)
        {
            $mails[] = array(
                'mail_to'       => $_to,
                'mail_encoding' => CHARSET,
                'mail_subject'  => $subject,
                'mail_body'     => $message,
                'priority'      => $priority,
                'add_time'      => gmtime(),
            );
        }

       return $model_mailqueue->add($mails);
       // echo 3;
        /* 默认采用异步发送邮件，这样可以解决响应缓慢的问题 */
      //  return $this->_sendmail();
    }

    /**
     * @param array 
     * $arr = array (
	 *    'user_msgs' => array (
	 *	  		'user_id' => 1,
	 *	  		'tpl' => 'user_order_notify',
	 *	  		'info'=>$info,
	 *	  		'send_msg' => 1,
	 *	 		 'tel_msg'=>'这是短息测试',
	 *	  ),
	 *	 'seller_msgs' => array (
	 *			'user_id' => 1,
	 *			'tpl' => 'seller_order_notify'
	 *			'send_msg' => 1,
	 *			'info'=>$info
	 *    )
	 *);
	 *    		
     *$send_msg 是否给用户发送短息 
     * @return boolean 
     *  
     */
    function  send_email($arr){

    	/* 连接用户系统 */
    	$ms =& ms();    	
    	$model_member =& m('member');
    	/* 发送给买家下单通知 */
    	if($arr['user_msgs']['user_id'])
    	{   
    		$msg_id = $ms->pm->send($this->visitor->get('user_id'),
    				 array($arr['user_msgs']['user_id']), '', $arr['user_msgs']['tel_msg']);
    		if (!$msg_id)
    		{
    			
    			$rs = $ms->pm->get_error();
    			$msg = current($rs);
    			$this->show_warning($msg['msg'], 'go_back', 'index.php?app=message&act=send');
    			return;
    		}
    		$member_info  = $model_member->get($arr['user_msgs']['user_id']);    		
			$user_address = $member_info['email'];	 
			$buyer_mail = get_mail($arr['user_msgs']['tpl'],$arr['user_msgs']['info']);			
			$this->_mailto($user_address, addslashes($buyer_mail['subject']), addslashes($buyer_mail['message']));	 	
    		if($arr['user_msgs']['send_msg'] == 1)
    		{
    			$tel = $member_info['phone_mob'];
    			if(!$tel)
    			{
    				$memberrec = & m('memberrec');
    				$rec_info  = $memberrec->get("user_id=".$arr['user_msgs']['user_id']);
    				$tel = $rec_info['tel'];
    				$arr['user_msgs']['tel'] = $tel;
    			}
    			/* 如果有短息内容 */
    			if($arr['user_msgs']['tel_msg'] && $tel)
    			{
    				$this->send_msg($tel,$arr['user_msgs']['tel_msg']);
    			}   					
    		}
    	}

    	/* 发送给卖家新订单通知 */
    	if(isset($arr['seller_msgs']))
    	{
    		if($arr['seller_msgs']['user_id'])
    		{
    			$seller_info  = $model_member->get($arr['seller_msgs']['user_id']);    			
    			$seller_address = $seller_info['email'];
    		}
    		else
    		{
    			$seller_address = RECEIVE_EMAIL;
    		}
    		
    		$seller_mail = get_mail($arr['seller_msgs']['tpl'], array('info' => $arr['seller_msgs']['info']));
    		$this->_mailto($seller_address, addslashes($seller_mail['subject']), addslashes($seller_mail['message']));
    	}
    	
    	return true;
    }
    
    function send_msg($tel ,$msg) {
    	if(!$tel){
    		return ;
    	}
    	
    	$model_setting = &af('settings');    	
    	//$url = "http://service.winic.org/sys_port/gateway/";
    	$url = $model_setting->getOne('msg_url');
    	$url = trim($url);
    	
    	$data = "id=".$model_setting->getOne('msg_uid')."&pwd=".$model_setting->getOne('msg_pwd')."&to=".$tel."&content=".$msg.'&time';
    	
    	$data = iconv('utf-8', 'GB2312', $data);
    	
        $info = curl_file_get_contents($url,$data); 
        
        if(is_string($info)){			
	        
        	if(preg_match('/000\/Send:[1-9]+\d*\//',$info)){
        		return true;
        	}
			return false;
        }else{
        	
        	return false;
        }      
           
    }
    /**
     *    发送邮件
     *
     *    @author    LiuLei
     *    @param     bool $is_syncccccc
     *    @return    void
     */
    function _sendmail($qid)
    {   
        unset($_SESSION['ASYNC_SENDMAIL']);
        $model_mailqueue =& m('mailqueue');
        $gmtime = time();
        $mails =  $model_mailqueue->find(array(
            'conditions' => db_create_in($qid, 'queue_id'),
            'count' => true,
        ));
        
        $count = $model_mailqueue->getCount();
        $rs = $model_mailqueue->send($count);
        if ($rs['error_count'] > 0)
        {
            $this->_sendmail($qid);
        }
    }

//    function _sendmail($qid,$is_sync = true)
//    {
//        if (!$is_sync)
//        { 
//            /* 采用异步方式发送邮件，与模板引擎配合达到目的 */
//            $_SESSION['ASYNC_SENDMAIL'] = false;

//            return true;
//        }else {
          
//            /* 同步发送邮件，将异步发送的命令去掉 */
//            unset($_SESSION['ASYNC_SENDMAIL']);
//            $model_mailqueue=m('mailqueue');
//            $gmtime = time();
//            $mails =  $model_mailqueue->find(array(
//                'conditions' => db_create_in($qid, 'queue_id'),
//                'count' => true,
//            ));
//            $count = $model_mailqueue->getCount();
         
//           return $model_mailqueue->send($count);
//        }
//    }

    /**
     *     获取异步发送邮件代码
     *
     *    @author    LiuLei
     *    @return    string
     */
    function _async_sendmail()
    {
        $script = '';
        if (isset($_SESSION['ASYNC_SENDMAIL']) && $_SESSION['ASYNC_SENDMAIL'])
        {
            /* 需要异步发送 */
            $async_sendmail = SITE_URL . '/index.php?app=sendmail';
            $script = '<script type="text/javascript">sendmail("' . $async_sendmail . '");</script>';
        }

        return $script;
    }
    function _get_new_message()
    {
        $user_id = $this->visitor->get('user_id');
        if(empty($user_id))
        {
            return '';
        }
        $ms=ms();
        return $ms->pm->check_new($user_id);
    }

    /**
     *    计划任务守护进程
     *
     *    @author    LiuLei
     *    @return    void
     */
    function _run_cron()
    {

        register_shutdown_function(create_function('', '
            /*if (ob_get_level() > 0)
            {
                ob_end_flush();         //输出
            }*/
            if (!is_file(ROOT_PATH . "/data/tasks.inc.php"))
            {
                $default_tasks = array(
                    "cleanup" =>
                        array (
                            "cycle" => "custom",
                            "interval" => 3600,     //每一个小时执行一次清理
                        ),
                );
                file_put_contents(ROOT_PATH . "/data/tasks.inc.php", "<?php\r\n\r\nreturn " . var_export($default_tasks, true) . ";\r\n\r\n", LOCK_EX);
            }
            import("cron.lib");
            $cron = new Crond(array(
                "task_list" => ROOT_PATH . "/data/tasks.inc.php",
                "task_path" => ROOT_PATH . "/includes/tasks",
                "lock_file" => ROOT_PATH . "/data/crond.lock"
            ));                     //计划任务实例
            $cron->execute();       //执行
        '));
    }

    /**
     * 发送Feed
     *
     * @author LiuLei
     * @param
     * @return void
     **/
    function send_feed($event, $data)
    {
        $ms = &ms();
        if (!$ms->feed->feed_enabled())
        {
            return;
        }

        $feed_config = $this->visitor->get('feed_config');
        $feed_config = empty($feed_config) ? Conf::get('default_feed_config') : unserialize($feed_config);
        if (!$feed_config[$event])
        {
            return;
        }

        $ms->feed->add($event, $data);
    }
    function ts() {
    	
    }
    
}

?>
