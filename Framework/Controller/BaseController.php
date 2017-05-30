<?php
/**
 *    控制器基础类
 *
 *    @author    LorenLei
 *    @usage    none
 */

namespace Framework\Controller;
use \Framework\Base\Object;
use \Framework\Lib\SessionProcessor;
class BaseController extends Object
{
    /* 建立到视图的链接 */
    var $_view = null;

    function __construct()
    {
    	
        $this->BaseController();
    }

    function BaseController()
    {

        /* 初始化Session */
    	$this->_init_session();
        $this->load('Evn');
       
    }

    /**
     *    运行指定的动作
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    function do_action($action)
    {  
    	       
    	    	
        if ($action && $action{0} != '_' && method_exists($this, $action))
        {
            $this->$action();
        }
        else
        {
            exit('missing_action');
        }
    }
    function index()
    {
        echo 'Hello! Programmer';
    }

    /**
     *    给视图传递变量
     *
     *    @author    LorenLei
     *    @param     string $k
     *    @param     mixed  $v
     *    @return    void
     */
    function assign($k, $v = null)
    {
        $this->_init_view();
        if (is_array($k))
        {
            $args  = func_get_args();
            foreach ($args as $arg)     //遍历参数
            {
                foreach ($arg as $key => $value)    //遍历数据并传给视图
                {
                    $this->_view->assign($key, $value);
                }
            }
        }
        else
        {
            $this->_view->assign($k, $v);
        }
    }

    /**
     *    显示视图
     *
     *    @author    LorenLei
     *    @param     string $n
     *    @return    void
     */
    function display($n)
    {
        $this->_init_view();
        $this->_view->display($n);
    }
	/**
     *  获取输出页面内容
     * 调用内置的模板引擎fetch方法，
     * @access protected
     * @param string $n 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @return string
     */
    function fetch($n = '') {
        $this->_init_view();
        return $this->_view->fetch($n);
    }
    /**
     *    初始化视图连接
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    function _init_view()
    {
        if ($this->_view === null)
        {
            //获取Template对象
            $this->_view =& v();
            
            $this->_config_view();  //配置
        }
    }

    /**
     *    配置视图
     *
     *    @author    LorenLei
     *    @return    void
     */
    function _config_view()
    {
        $this->_view->template_dir = APP_PATH.MOD . '/View/'.ACT;
    }



    /**
     *    初始化Session
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    function _init_session()
    {   
        import('session');
        $db = db();
        if(isset($_GET['ssid'])){
        	$get_ssid = $_GET['ssid'];
        }else{
        	$get_ssid = '';
        }
        $table_name = defined('SESSION_TABLE_NAME') ? SESSION_TABLE_NAME: 'sessions';
        $this->_session = new SessionProcessor($db, $table_name, $table_name.'_data', 'PIPE_ID',$get_ssid);
        define('SESS_ID', $this->_session->get_session_id());
        $this->_session->my_session_start();
    }

    /**
     *    获取程序运行时间
     *
     *    @author:    LorenLei
     *    @param:     int $precision
     *    @return:    float
     */
    function _get_run_time($precision = 5)
    {
        return round(pipe_microtime() - START_TIME, $precision);
    }

    /**
     *  控制器结束运行后执行
     *
     *  @author LorenLei
     *  @return void
     */
    function __destruct(){
        collect_error();
    }



}

?>