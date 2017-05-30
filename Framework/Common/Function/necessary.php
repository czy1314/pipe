<?php
/**
 * 获取视图链接
 *
 * @author LorenLei
 * @param string $engine
 * @return object
 */
function &v($is_new = false, $engine = 'default') {
    include_once (ROOT_PATH . '/Framework/Core/View/Template.php');
    if ($is_new) {
        return new Template ();
    } else {
        static $v = null;
        if ($v === null) {
            switch ($engine) {
                case 'default' :
                    $v = new Template ();
                    break;
            }
        }

        return $v;
    }
}

/**
 * 获取一个模型

 * @param string $model_name
 * @param array $params
 * @param book $is_new
 * @return object
 */
function &m($model_name, $params = array(), $is_new = false) {
    static $models = array ();
    $model_hash = md5 ( $model_name . var_export ( $params, true ) );
    if ($is_new || ! isset ( $models [$model_hash] )) {
        $model_file = ROOT_PATH . '/includes/models/' . $model_name . '.model.php';
        if (! is_file ( $model_file )) {
            /* 不存在该文件，则无法获取模型 */
            return false;
        }
        include_once ($model_file);
        $model_name = ucfirst ( $model_name ) . 'Model';
        if ($is_new) {
            // db（）返回的一个static类型数据库对象的引用
            return new $model_name ( $params, db () );
        }
        $models [$model_hash] = new $model_name ( $params, db () );
    }

    return $models [$model_hash];
}


/**
 * 获取当前控制器实例
 *
 * @author LorenLei
 * @return void
 */
function c(&$app) {
    $GLOBALS ['CUR_APP'] = & $app;
}
/**
 * 调用其他controller
 *
 * @param unknown $app
 * @return unknown
 */
function bc($app) {
    $config = $GLOBALS ['sys'] ['config'];
    $app_file = app_root . "/Controller/{$app}.php";
    if (! is_file ( $app_file )) {
        exit ( 'Missing controller' );
    }

    require ($app_file);
    $app_class_name = ucfirst ( $app ) . 'App';
    /* 实例化控制器 */
    $app = new $app_class_name ();
    return $app;
}

/**
 * 获取当前控制器
 *
 * @author LorenLei
 * @return Object
 */
function &cc() {
    return $GLOBALS ['CUR_APP'];
}

/**
 * 实例化并且缓存一个类，默认加载框架文件下面的工具类（/Framework/Util），参数可以是相对于项目根目录的一个文件具体路径,
 * @author LorenLei
 * @return boolean|object
 */
function load($class_name) {
    $class_name = trim($class_name);
    if (empty ( $class_name )) {
        return false;
    }
    static $loader = null;
    //有路径
    if(($pos = strrpos($class_name,'\/')) !==  false || ($pos = strrpos($class_name,'\\')) !== false){
        //修正路径
        $class_name = (strpos($class_name,'\/') !==  0 && strpos($class_name,'\\') !==  0) ? DIRECTORY_SEPARATOR.$class_name:$class_name;
        $path =  ROOT_PATH . $class_name . 'php';
        $class_name = substr($path,$pos);
    }else{
        $class_name = ucfirst($class_name);
        $path = ROOT_PATH . '/Framework/Util/' . $class_name . '.php';
    }
    $md5_path = md5($path);
    if(!empty($loader[$md5_path])){
        return $loader[$md5_path];
    }

    if(file_exists($path)){
        include_once($path);
        if(class_exists($class_name,false)){
            $loader[$md5_path] = new $class_name();
            return $loader[$md5_path];
        }
    }
    return false;

}

/**
 * 导入一个lib库
 *
 * @author LorenLei
 * @return void
 */
function import() {
    $c = func_get_args ();
    if (empty ( $c )) {
        return;
    }
    array_walk ( $c, create_function ( '$item, $key', 'include_once(ROOT_PATH . \'/Framework/Lib/\' . $item . \'.php\');' ) );
}


/**
 * 创建MySQL数据库对象实例
 *
 * 
 * @return object
 */
function &db() {
    include_once (ROOT_PATH . '/Framework/Model/Drivers/'.DB.'.php');
    static $db = null;
    if ($db === null) {
        $cfg = parse_url ( DB_CONFIG );

        if ($cfg ['scheme'] == 'mysql') {
            if (empty ( $cfg ['pass'] )) {
                $cfg ['pass'] = '';
            } else {
                $cfg ['pass'] = urldecode ( $cfg ['pass'] );
            }
            $cfg ['user'] = urldecode ( $cfg ['user'] );

            if (empty ( $cfg ['path'] )) {
                trigger_error ( 'Invalid database name.', E_USER_ERROR );
            } else {
                $cfg ['path'] = str_replace ( '/', '', $cfg ['path'] );
            }

            $charset = (CHARSET == 'utf-8') ? 'utf8' : CHARSET;
            $db = new PipeMysql ();
            $db->cache_dir = ROOT_PATH . '/Temp/query_caches/';
            $db->connect ( $cfg ['host'] . ':' . $cfg ['port'], $cfg ['user'], $cfg ['pass'], $cfg ['path'], $charset );
        } else {
            trigger_error ( 'Unkown database type.', E_USER_ERROR );
        }
    }

    return $db;
}


function addslashes_deep($value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
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



/**
 * 返回由对象属性组成的关联数组
 *
 * @access pubilc
 * @param obj $obj
 *
 * @return array
 */
function get_object_vars_deep($obj) {
    if (is_object ( $obj )) {
        $obj = get_object_vars ( $obj );
    }
    if (is_array ( $obj )) {
        foreach ( $obj as $key => $value ) {
            $obj [$key] = get_object_vars_deep ( $value );
        }
    }
    return $obj;
}

/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access public
 * @param mix $item_list
 *        	列表数组或字符串,如果为字符串时,字符串只接受数字串
 * @param string $field_name
 *        	字段名称
 * 
 *
 * @return void
 */
function db_create_in($item_list, $field_name = '') {
    if (empty ( $item_list )) {
        return $field_name . " IN ('') ";
    } else {
        if (! is_array ( $item_list )) {
            $item_list = explode ( ',', $item_list );
            foreach ( $item_list as $k => $v ) {
                $item_list [$k] = intval ( $v );
            }
        }

        $item_list = array_unique ( $item_list );
        $item_list_tmp = '';
        foreach ( $item_list as $item ) {
            if ($item !== '') {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty ( $item_list_tmp )) {
            return $field_name . " IN ('') ";
        } else {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}


/**
 * 设置COOKIE
 *
 * @access public
 * @param string $key
 *        	要设置的COOKIE键名
 * @param string $value
 *        	键名对应的值
 * @param int $expire
 *        	过期时间
 * @return void
 */
function pipe_setcookie($key, $value, $expire = 0, $cookie_path = COOKIE_PATH, $cookie_domain = COOKIE_DOMAIN) {
    setcookie ( $key, $value, $expire, $cookie_path, $cookie_domain );
}

/**
 * 获取COOKIE的值
 *
 * @access public
 * @param string $key
 *        	为空时将返回所有COOKIE
 * @return mixed
 */
function pipe_getcookie($key = '') {
    return isset ( $_COOKIE [$key] ) ? $_COOKIE [$key] : 0;
}

/**
 * 对数组转码
 *
 * @param string $func
 * @param array $params
 *
 * @return mixed
 *
 */
function pipe_iconv_deep($source_lang, $target_lang, $value) {
    if (empty ( $value )) {
        return $value;
    } else {
        if (is_array ( $value )) {
            foreach ( $value as $k => $v ) {
                $value [$k] = pipe_iconv_deep ( $source_lang, $target_lang, $v );
            }
            return $value;
        } elseif (is_string ( $value )) {
            return pipe_iconv ( $source_lang, $target_lang, $value );
        } else {
            return $value;
        }
    }
}


/**
 * 清理系统所有编译文件，缓存文件、模板结构数据
 *
 * 
 * @param
 *        	void
 *
 * @return void
 */
function clean_cache() {
    /* 清理缓存 */
    $cache_dirs = array (
        ROOT_PATH . '/Temp/caches',
        ROOT_PATH . '/Temp/compiled/mall/admin',
        ROOT_PATH . '/Temp/compiled/mall/',
        ROOT_PATH . '/Temp/compiled/store/admin',
        ROOT_PATH . '/Temp/compiled/store',
        ROOT_PATH . '/Temp/js',
        ROOT_PATH . '/Temp/query_caches',
        ROOT_PATH . '/Temp/tag_caches',
        ROOT_PATH . '/Temp/style'
    );

    foreach ( $cache_dirs as $dir ) {
        $d = dir ( $dir );
        if ($d) {
            while ( false !== ($entry = $d->read ()) ) {
                if ($entry != '.' && $entry != '..' && $entry != '.svn' && $entry != 'admin' && $entry != 'index.html') {
                    pipe_rmdir ( $dir . '/' . $entry );
                }
            }
            $d->close ();
        }
    }


    /* 清除一个周前图片缓存并回收多余目录 */

    $expiry_time = strtotime ( '-1 week' );
    $path = ROOT_PATH . '/Temp/thumb';
    $d = dir ( $path );
    if ($d) {
        while ( false !== ($entry = $d->read ()) ) {
            if ($entry != '.' && $entry != '..' && $entry != '.svn' && is_dir ( ($dir = ($path . '/' . $entry)) )) {
                $sd = dir ( $dir );
                if ($sd) {
                    $left_dir_count = 0;
                    while ( false !== ($entry = $sd->read ()) ) {
                        if ($entry != '.' && $entry != '..' && is_dir ( ($subdir = ($dir . '/' . $entry)) )) {
                            $fsd = dir ( $subdir );
                            $left_file_count = 0;
                            while ( false !== ($entry = $fsd->read ()) ) {
                                if ($entry != '.' && $entry != '..' && $entry != 'index.htm' && is_file ( ($file = $subdir . '/' . $entry) )) {
                                    if (filemtime ( $file ) < $expiry_time) {
                                        unlink ( $file );
                                    } else {
                                        $left_file_count ++;
                                    }
                                }
                            }
                            $fsd->close ();
                            if ($left_file_count == 0) {
                                // 清除空目录
                                pipe_rmdir ( $subdir );
                            } else {
                                $left_dir_count ++;
                            }
                        }
                    }
                    $sd->close ();
                    if ($left_dir_count == 0)
                        pipe_rmdir ( $dir );
                }
            }
        }
        $d->close ();
    }
}

/**
 * 如果系统不存在file_put_contents函数则声明该函数
 *
 * 
 * @param string $file
 * @param mix $data
 * @return int
 */
if (! function_exists ( 'file_put_contents' )) {
    define ( 'FILE_APPEND', 'FILE_APPEND' );
    if (! defined ( 'LOCK_EX' )) {
        define ( 'LOCK_EX', 'LOCK_EX' );
    }
    function file_put_contents($file, $data, $flags = '') {
        $contents = (is_array ( $data )) ? implode ( '', $data ) : $data;

        $mode = ($flags == 'FILE_APPEND') ? 'ab+' : 'wb';

        if (($fp = @fopen ( $file, $mode )) === false) {
            return false;
        } else {
            $bytes = fwrite ( $fp, $contents );
            fclose ( $fp );

            return $bytes;
        }
    }
}

/**
 * 通过该函数运行函数可以抑制错误
 *
 * @author weberliu
 * @param string $fun
 *        	要屏蔽错误的函数名
 * @return mix 函数执行结果
 */
function call($fun) {
    $arg = func_get_args ();
    unset ( $arg [0] );
    $ret_val = @call_user_func_array ( $fun, $arg );

    return $ret_val;
}

/**
 * 调用外部函数
 *
 * @author weberliu
 * @param string $func
 * @param array $params
 *
 * @return mixed
 */
function outer_call($func, $params = null) {
    restore_error_handler ();

    $res = call_user_func_array ( $func, $params );

    set_error_handler ( 'exception_handler' );

    return $res;
}
function reset_error_handler() {
    set_error_handler ( 'exception_handler' );
}



/**
 *
 * @param string $query
 *        	查询参数
 * @param string $url
 *        	完整url
 */
function redirect($query = '', $url = '') {
    if ($query) {
        header ( 'Location:' . site_url () . '/index.php?' . $query );
    } else {
        header ( 'Location:' . $url );
    }
}
function &cache_server()
{
    import('cache');
    static $CS = null;
    if ($CS === null) {
        switch (CACHE_SERVER) {
            case 'memcached':
                list($host, $port) = explode(':', CACHE_MEMCACHED);
                $CS = new MemcacheServer(array(
                    'host' => $host,
                    'port' => $port,
                ));
                break;
            default:
                $CS = new PhpCacheServer;
                $CS->set_cache_dir(ROOT_PATH . '/Temp/caches');
                break;
        }
    }

    return $CS;
}





/**
 * 时间差计算
 *
 * @param Timestamp $time
 * @return String Time Elapsed
 * @author Shelley Shyan
 * @copyright http://phparch.cn (Professional PHP Architecture)
 */
function time2Units($time)
{
    $year = floor($time / 60 / 60 / 24 / 365);
    $time -= $year * 60 * 60 * 24 * 365;
    $month = floor($time / 60 / 60 / 24 / 30);
    $time -= $month * 60 * 60 * 24 * 30;
    $week = floor($time / 60 / 60 / 24 / 7);
    $time -= $week * 60 * 60 * 24 * 7;
    $day = floor($time / 60 / 60 / 24);
    $time -= $day * 60 * 60 * 24;
    $hour = floor($time / 60 / 60);
    $time -= $hour * 60 * 60;
    $minute = floor($time / 60);
    $time -= $minute * 60;
    $second = $time;
    $elapse = '';

    $unitArr = array('年' => 'year', '个月' => 'month', '周' => 'week', '天' => 'day',
        '小时' => 'hour', '分钟' => 'minute', '秒' => 'second'
    );

    foreach ($unitArr as $cn => $u) {
        if ($$u > 0) {
            $elapse = $$u . $cn;
            break;
        }
    }

    return $elapse;
}


/**
 *    获取URL地址
 *
 * @author    LorenLei
 * @param     mixed $query
 * @param     string $rewrite_name
 * @return    string
 */
function url($query, $rewrite_name = null)
{
    $re_on = Conf::get('rewrite_enabled');
    $url = '';
    if (!$re_on) {
        /* Rewrite未开启 */
        $url = 'index.php?' . $query;
    } else {
        /* Rewrite已开启 */
        $re =& rewrite_engine();
        $rewrite = $re->get($query, $rewrite_name);

        $url = ($rewrite !== false) ? $rewrite : 'index.php?' . $query;
    }

    return str_replace('&', '&amp;', $url);
}




/**
 *    计算剩余时间
 *
 * @author    LorenLei
 * @param     string $format
 * @param     int $time ;
 * @return    string
 */
function lefttime($time, $format = null)
{
    $lefttime = $time - gmtime();
    if ($lefttime < 0) {
        return '';
    }
    if ($format === null) {
        if ($lefttime < 3600) {
            $format = Lang::get('lefttime_format_1');
        } elseif ($lefttime < 86400) {
            $format = Lang::get('lefttime_format_2');
        } else {
            $format = Lang::get('lefttime_format_3');
        }
    }
    $d = intval($lefttime / 86400);
    $lefttime -= $d * 86400;
    $h = intval($lefttime / 3600);
    $lefttime -= $h * 3600;
    $m = intval($lefttime / 60);
    $lefttime -= $m * 60;
    $s = $lefttime;

    return str_replace(array('%d', '%h', '%i', '%s'), array($d, $h, $m, $s), $format);
}


/**
 * 多维数组排序（多用于文件数组数据）
 *
 * @author Hyber
 * @param array $array
 * @param array $cols
 * @return array
 *
 * e.g. $data = array_msort($data, array('sort_order'=>SORT_ASC, 'add_time'=>SORT_DESC));
 */
function array_msort($array, $cols)
{
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) {
            $colarr[$col]['_' . $k] = strtolower($row[$col]);
        }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\'' . $col . '\'],' . $order . ',';
    }
    $eval = substr($eval, 0, -1) . ');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k, 1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;
}

function format_datetime($time)
{
    return date('Y-m-d h:i:s', $time);
}

function real_ip() {
    static $realip = NULL;

    if ($realip !== NULL) {
        return $realip;
    }

    if (isset ( $_SERVER )) {
        if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
            $arr = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );

            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ( $arr as $ip ) {
                $ip = trim ( $ip );

                if ($ip != 'unknown') {
                    $realip = $ip;

                    break;
                }
            }
        } elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
            $realip = $_SERVER ['HTTP_CLIENT_IP'];
        } else {
            if (isset ( $_SERVER ['REMOTE_ADDR'] )) {
                $realip = $_SERVER ['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv ( 'HTTP_X_FORWARDED_FOR' )) {
            $realip = getenv ( 'HTTP_X_FORWARDED_FOR' );
        } elseif (getenv ( 'HTTP_CLIENT_IP' )) {
            $realip = getenv ( 'HTTP_CLIENT_IP' );
        } else {
            $realip = getenv ( 'REMOTE_ADDR' );
        }
    }

    preg_match ( "/[\d\.]{7,15}/", $realip, $onlineip );
    $realip = ! empty ( $onlineip [0] ) ? $onlineip [0] : '0.0.0.0';

    return $realip;
}
/**
 *    将default.abc类的字符串转为$default['abc']
 *
 *    @author    Garbin
 *    @param     string $str
 *    @return    string
 */
function strtokey($str, $owner = '')
{
    if (!$str)
    {
        return '';
    }
    if ($owner)
    {
        return $owner . '[\'' . str_replace('.', '\'][\'', $str) . '\']';
    }
    else
    {
        $parts = explode('.', $str);
        $owner = '$' . $parts[0];
        unset($parts[0]);
        return strtokey(implode('.', $parts), $owner);
    }
}

function collect_error($msg=''){
    static $errors = array();
    if($msg){
        $errors = array_merge($errors,array($msg));
    }else{
        if(!IS_AJAX){
            showDebug(implode('<br>',$errors));
        }else{
            return   implode('|',$errors);
        }
    }


}
function showDebug($msg){
        echo  '<div  onclick="show_dbg(event)" style="position: fixed;z-index:9999999;border:1px solid palevioletred;top:0;right: 0;width: 100%;height: 100px;overflow-y: auto;background: lightgray;" id="debug_win">'.$msg.'</div>
<script>
	function show_dbg(e) {
	    e = e || window.event;
	    var el = e.target;
		if(el.className==\'block\' || !el.className){
			el.style.width =el.style.height = \'20px\';
			el.className = \'none\'
		} else{
			el.style.width =\'100%\';
			el.style.height = \'100px\'
			el.className=\'block\';
		}
	}
</script>
';
function createRandCode($num){


}

}
?>
