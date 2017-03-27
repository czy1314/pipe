<?php
/**
 *    Boot框架全局函数文件
 *    @author    LiuLei
 */

function &cache_server()
{
    import('cache.lib');
    static $CS = null;
    if ($CS === null)
    {
        switch (CACHE_SERVER)
        {
            case 'memcached':
                list($host, $port) = explode(':', CACHE_MEMCACHED);
                $CS = new MemcacheServer(array(
                    'host'  => $host,
                    'port'  => $port,
                ));
            break;
            default:
                $CS = new PhpCacheServer;
                $CS->set_cache_dir(ROOT_PATH . '/temp/caches');
            break;
        }
    }

    return $CS;
}

/**
 *    获取商品类型对象
 *
 *    @author    LiuLei
 *    @param     string $type
 *    @param     array  $params
 *    @return    void
 */
function &gt($type, $params = array())
{
    static $types = array();
    if (!isset($types[$type]))
    {
        /* 加载订单类型基础类 */
        include_once(APP_PATH . 'Common/Util/goods.base.php');
        include(APP_PATH . 'Common/Util/goodstypes/' . $type . '.gtype.php');
        $class_name = ucfirst($type) . 'Goods';
        $types[$type]   =   new $class_name($params);
    }

    return $types[$type];
}

/**
 *    获取订单类型对象
 *
 *    @author    LiuLei
 *    @param    none
 *    @return    void
 */
function &ot($type, $params = array())
{
    static $order_type = null;
    if ($order_type === null)
    {
        /* 加载订单类型基础类 */
        include_once(APP_PATH . 'Common/Util/order.base.php');
        include(APP_PATH . 'Common/Util/ordertypes/' . $type . '.otype.php');
        $class_name = ucfirst($type) . 'Order';
        $order_type = new $class_name($params);
    }

    return $order_type;
}

/**
 *    获取数组文件对象
 *
 *    @author    LiuLei
 *    @param     string $type
 *    @param     array  $params
 *    @return    void
 */
function &af($type, $params = array())
{
    static $types = array();
    if (!isset($types[$type]))
    {
        /* 加载数据文件基础类 */
        include_once(APP_PATH . 'Common/Util/arrayfile.base.php');
        include(APP_PATH . 'Common/Util/arrayfiles/' . $type . '.arrayfile.php');
        $class_name = ucfirst($type) . 'Arrayfile';
        $types[$type]   =   new $class_name($params);
    }

    return $types[$type];
}

/**
 *    连接会员系统
 *
 *    @author    LiuLei
 *    @return    Passport 会员系统连接接口
 */
function &ms()
{
    static $ms = null;
    if ($ms === null)
    {
        include(APP_PATH . 'Common/Util/passport.base.php');
        include(APP_PATH . 'Common/Util/passports/' . MEMBER_TYPE . '.passport.php');
        $class_name  = ucfirst(MEMBER_TYPE) . 'Passport';
        $ms = new $class_name();
    }

    return $ms;
}


/**
 *    获取用户头像地址
 *
 *    @author    LiuLei
 *    @param     string $portrait
 *    @return    void
 */
function portrait($user_id, $portrait, $size = 'small')
{
    switch (MEMBER_TYPE)
    {
        case 'uc':
            return UC_API . '/avatar.php?uid=' . $user_id . '&amp;size=' . $size;
        break;
        default:
            return empty($portrait) ? Conf::get('default_user_portrait') : $portrait;
        break;
    }
}

/**
 *    获取环境变量
 *
 *    @author    LiuLei
 *    @param     string $key
 *    @param     mixed  $val
 *    @return    mixed
 */
function &env($key, $val = null)
{
    !isset($GLOBALS['EC_ENV']) && $GLOBALS['EC_ENV'] = array();
    $vkey = $key ? strtokey("{$key}", '$GLOBALS[\'EC_ENV\']') : '$GLOBALS[\'EC_ENV\']';
    if ($val === null)
    {
        /* 返回该指定环境变量 */
        $v = eval('return isset(' . $vkey . ') ? ' . $vkey . ' : null;');

        return $v;
    }
    else
    {
        /* 设置指定环境变量 */
        eval($vkey . ' = $val;');

        return $val;
    }
}


/**
 *    获取订单状态相应的文字表述
 *
 *    @author    LiuLei
 *    @param     int $order_status
 *    @return    string
 */
function order_status($order_status)
{
    $lang_key = '';
   
    switch ($order_status)
    {
    	
    	case WAIT_CREDIT_AUTH:       //待信用认证
    		$lang_key = wait_credit_auth;
    		break;
    	case PASS_CREDIT_AUTH:       //待信用认证
    		$lang_key = pass_credit_auth;
    		break;
    	case REJECTED_CREDIT_AUTH:       //待信用认证
    		$lang_key = rejected_credit_auth;
    		break;    	  		
    	case WAIT_REAL_IDENTIFY:   //待身份认证
    		$lang_key = wait_real_identify;
    		break;
    	case PASS_REAL_IDENTIFY:       //待信用认证
    		$lang_key = pass_real_identify;
    		break;
    	case REJECTED_REAL_IDENTIFY:       //待信用认证
    		$lang_key = rejected_real_identify;
    		break;    		
    		
    	case WAIT_FIRST_PAY:    //待首付
    		$lang_key = wait_first_pay;
    		break;
    	case HAS_FIRST_PAIED:    //待首付
    		$lang_key = has_first_paied;
    		break;
    		
    	case WAIT_ORDER_SHIP:     //待发货的订单
    		$lang_key = wait_order_ship;
    		break;
    	case ORDER_SHIPPED:     //待发货的订单
    		$lang_key = order_shipped;
    		break;
    		
    	case WAIT_CONFIRM_RECEIVED:     //待签收的订单
    		$lang_key = wait_confirm_received;
    		break;
    	case HAS_CONFIRM_RECEIVED:     //已签收的订单
    		$lang_key = has_confirm_received;
    		break;    		
    	case PAYBACK_STABLY:     //还款正常订单
    		$lang_key = payback_stably;
    		break;
    	case HAVE_DEBT:     //有债务订单
    		$lang_key = have_debt;
    		break;
    	case ORDER_CANCELED:     //已取消的订单
    		$lang_key = order_canceled;
    		break;
    	case ORDER_FINISHED:    //已完成的订单
    		$lang_key = order_finished;
    		break;
    }
   

    return $lang_key  ? Lang::get($lang_key) : $lang_key;
}


/**
 *    获取提现状态相应的文字表述
 *
 *    @author    LiuLei
 *    @param     int $wd_status
 *    @return    string
 */
function wd_status($wd_status)
{
    $lang_key = '';
   
    switch ($wd_status)
    {
    	case RAPLY_SUBMITTED: 
			$lang_key = order_submitted;
    		break;
    	case ORDER_CANCELED:     //已取消的订单
    		$lang_key = order_canceled;
    		break;
    	case ORDER_FINISHED:    //已完成的订单
    		$lang_key = order_finished;
    		break;
    }
   

    return $lang_key  ? Lang::get($lang_key) : $lang_key;
}
function rec_status($rec_status){
	$lang_key = member_status_translator($rec_status);
	return $lang_key  ? Lang::get($lang_key) : $lang_key;
}
function member_status_translator($rec_status)
{
	
	$lang_key = '';
	 
	switch ($rec_status)
	{
		 
		case UPLOADED_NO_BASE_INFO:    
			$lang_key = uploaded_no_base_info;
			break;
		case USER_BEEN_DONGJIE:
			$lang_key = user_been_dongjie;
			break;
		case UPLOADED_BASE_INFO:   
			$lang_key = uploaded_base_info;
			break;

		case USER_NO_PASS_CREDIT:     
			$lang_key = user_no_pass_credit;
			break;
		case USER_PASS_CREDIT:    
			$lang_key = user_pass_credit;
			break;

		/* case UPLOADED_NO_IMG_INFO:    
			$lang_key = uploaded_no_img_info;
			break; */
		case UPLOADED_IMG_INFO:    
			$lang_key = uploaded_img_info;
			break;
		case USER_NO_PASS:     
			$lang_key = be_rejected;
			break;
		case USER_HAS_VERIFIED:    
			$lang_key = user_has_verified;
			break;
		default:$lang_key = uploaded_no_base_info;
			break;
	}
	return $lang_key;
}


function member_status_translator_reverse($rec_status_str)
{

	$lang_key = '';
	switch ($rec_status_str)
	{
			
		case uploaded_no_base_info:
			$lang_key = UPLOADED_NO_BASE_INFO;
			break;
		case user_been_dongjie:
			$lang_key = USER_BEEN_DONGJIE;
			break;
		case uploaded_base_info:
			$lang_key = UPLOADED_BASE_INFO;
			break;

		case user_no_pass_credit:
			$lang_key = USER_NO_PASS_CREDIT;
			break;
		case user_pass_credit:
			$lang_key = USER_PASS_CREDIT;
			break;

		case uploaded_img_info:
			$lang_key = UPLOADED_IMG_INFO;
			break;
		case user_no_pass:
			$lang_key = BE_REJECTED;
			break;
		case user_has_verified:
			$lang_key = USER_HAS_VERIFIED;
			break;
		case uploaded_no_base_info:
			$lang_key = UPLOADED_NO_BASE_INFO;
			break;
		case user_been_dongjie:
			$lang_key = USER_BEEN_DONGJIE;
			break;
		default:$lang_key = '';
		break;
	}
	return $lang_key;
}
/**
 *    转换订单状态值
 *
 *    @author    LiuLei
 *    @param     string $order_status_text
 *    @return    void
 */
function order_status_translator($order_status_text)
{
    switch ($order_status_text)
    {
        case 'canceled':    //已取消的订单
            return ORDER_CANCELED;
        break;
        case 'all':         //所有订单
            return '';
        break;
        case 'auth':       //待信用认证
            return WAIT_CREDIT_AUTH;
        break;
        case 'identify':   //待身份认证
            return WAIT_REAL_IDENTIFY;
        break;
        case 'first_pay':    //待首付
            return WAIT_FIRST_PAY;
        break;
        case 'ship':     //待发货的订单
            return WAIT_ORDER_SHIP;
        break;
		case 'receive':     //待签收的订单
            return WAIT_CONFIRM_RECEIVED;
        break;
		 case 'payback_stably':     //还款正常订单
            return PAYBACK_STABLY;
        break;
		 case 'debt':     //有债务订单
            return HAVE_DEBT;
        break;
		 case 'canceled':     //已取消的订单
            return ORDER_CANCELED;
        break;
        case 'finished':    //已完成的订单
            return ORDER_FINISHED;
        break;
        default:            //所有订单
            return '';
        break;
    }
}

function bill_status_translator($bill_status_text)
{
    switch ($bill_status_text)
    {
       
        case 'all':        
            return '';
        break;
        case 'no_in_plan':      
            return NOT_IN_PLAN;
        break;
        case 'paybacked':   
            return PAYBACKED;
        break;
        case 'no_payback':    //待首付
            return NO_PAYBACK;
        break;
        case 'has_dlay':     
            return HAS_DLAY;
        
        default:            //所有订单
            return '';
        break;
    }
}


/**
 *    获取邮件内容
 *
 *    @author    LiuLei
 *    @param     string $mail_tpl
 *    @param     array  $var
 *    @return    array
 */
function get_mail($mail_tpl, $var = array())
{
    $subject = '';
    $message = '';

    /* 获取邮件模板 */
    $model_mailtemplate =& af('mailtemplate');
   
    $tpl_info   =  $model_mailtemplate->getOne($mail_tpl);
      
    if (!$tpl_info)
    {
        return false;
    }
         
    /* 解析其中变量 */
    $tpl =& v(true);
    $tpl->direct_output = true;
   
    $tpl->assign('site_name', Conf::get('site_name'));
    $tpl->assign('site_url', SITE_URL);
    $tpl->assign('mail_send_time', local_date('Y-m-d H:i', gmtime()));
    foreach ($var as $key => $val)
    {
        $tpl->assign($key, $val);
    }
    $subject = $tpl->fetch('str:' . $tpl_info['subject']);
    $message = $tpl->fetch('str:' . $tpl_info['content']);

    /* 返回邮件 */
   
    return array(
        'subject'   => $subject,
        'message'   => $message
    );
}

/**
 *    获取消息内容
 *
 *    @author    LiuLei
 *    @param     string $msg_tpl
 *    @param     array  $var
 *    @return    string
 */
function get_msg($msg_tpl, $var = array())
{
    /* 获取消息模板 */
    $ms = &ms();
    $msg_content = Lang::get($msg_tpl);
    $var['site_url'] = SITE_URL; // 给短消息模板中设置一个site_url变量
    $search = array_keys($var);
    $replace = array_values($var);

    /* 解析其中变量 */
    array_walk($search, create_function('&$str', '$str = "{\$" . $str. "}";'));
    $msg_content = str_replace($search, $replace, $msg_content);
    return $msg_content;
}

/**
 *    获取邮件发送网关
 *
 *    @author    LiuLei
 *    @return    object
 */
function &get_mailer()
{
    static $mailer = null;
    if ($mailer === null)
    {
        /* 使用mailer类 */
        import('mailer.lib');
        $sender     = Conf::get('site_name');
        $from       = Conf::get('email_addr');
        $protocol   = Conf::get('email_type');
        $host       = Conf::get('email_host');
        $port       = Conf::get('email_port');
        $username   = Conf::get('email_id');
        $password   = Conf::get('email_pass');
        $mailer = new Mailer($sender, $from, $protocol, $host, $port, $username, $password);
    }

    return $mailer;
}

function get_hidden($name,$value) {
	return '<input name='.$name.' value="'.$value.'" type="hidden" />';
}
/**
 *    模板列表
 *
 *    @author    LiuLei
 *    @param     strong $who
 *    @return    array
 */
function list_template($who)
{
    $theme_dir = ROOT_PATH . '/templates/' . $who;
    $dir = dir($theme_dir);
    $array = array();
    while (($item  = $dir->read()) !== false)
    {
        if (in_array($item, array('.', '..')) || $item{0} == '.' || $item{0} == '$')
        {
            continue;
        }
        $theme_path = $theme_dir . '/' . $item;
        if (is_dir($theme_path))
        {
            if (is_file($theme_path . '/theme.info.php'))
            {
                $array[] = $item;
            }
        }
    }

    return $array;
}

/**
 *    列表风格
 *
 *    @author    LiuLei
 *    @param     string $who
 *    @return    array
 */
function list_style($who, $template = 'default')
{
    $style_dir = ROOT_PATH . '/templates/' . $who . '/' . $template . '/styles';
    $dir = dir($style_dir);
    $array = array();
    while (($item  = $dir->read()) !== false)
    {
        if (in_array($item, array('.', '..')) || $item{0} == '.' || $item{0} == '$')
        {
            continue;
        }
        $style_path = $style_dir . '/' . $item;
        if (is_dir($style_path))
        {
            if (is_file($style_path . '/style.info.php'))
            {
                $array[] = $item;
            }
        }
    }

    return $array;
}


/**
 *    获取挂件列表
 *
 *    @author    LiuLei
 *    @return    array
 */
function list_widget()
{
    $widget_dir = ROOT_PATH . '/external/widgets';
    static $widgets    = null;
    if ($widgets === null)
    {
        $widgets = array();
        if (!is_dir($widget_dir))
        {
            return $widgets;
        }
        $dir = dir($widget_dir);
        while (false !== ($entry = $dir->read()))
        {
            if (in_array($entry, array('.', '..')) || $entry{0} == '.' || $entry{0} == '$')
            {
                continue;
            }
            if (!is_dir($widget_dir . '/' . $entry))
            {
                continue;
            }
            $info = get_widget_info($entry);
            $widgets[$entry] = $info;
        }
    }

    return $widgets;
}

/**
 *    获取挂件信息
 *
 *    @author    LiuLei
 *    @param     string $id
 *    @return    array
 */
function get_widget_info($name)
{
    $widget_info_path = ROOT_PATH . '/external/widgets/' . $name . '/widget.info.php';

    return include($widget_info_path);
}

function i18n_code()
{
    $code = 'zh-CN';
    $lang_code = substr(LANG, 0, 2);
    switch ($lang_code)
    {
        case 'sc':
            $code = 'zh-CN';
        break;
        case 'tc':
            $code = 'zh-TW';
        break;
        default:
            $code = 'zh-CN';
        break;
    }

    return $code;
}

/**
 *    从字符串获取指定日期的结束时间(24:00)
 *
 *    @author    LiuLei
 *    @param     string $str
 *    @return    int
 */
function gmstr2time_end($str)
{
    return gmstr2time($str) + 86400;
}


/**
 * 时间差计算
 *
 * @param Timestamp $time
 * @return String Time Elapsed
 * @author Shelley Shyan
 * @copyright http://phparch.cn (Professional PHP Architecture)
 */
function time2Units ($time)
{
	$year   = floor($time / 60 / 60 / 24 / 365);
	$time  -= $year * 60 * 60 * 24 * 365;
	$month  = floor($time / 60 / 60 / 24 / 30);
	$time  -= $month * 60 * 60 * 24 * 30;
	$week   = floor($time / 60 / 60 / 24 / 7);
	$time  -= $week * 60 * 60 * 24 * 7;
	$day    = floor($time / 60 / 60 / 24);
	$time  -= $day * 60 * 60 * 24;
	$hour   = floor($time / 60 / 60);
	$time  -= $hour * 60 * 60;
	$minute = floor($time / 60);
	$time  -= $minute * 60;
	$second = $time;
	$elapse = '';

	$unitArr = array('年'  =>'year', '个月'=>'month',  '周'=>'week', '天'=>'day',
			'小时'=>'hour', '分钟'=>'minute', '秒'=>'second'
	);

	foreach ( $unitArr as $cn => $u )
	{
		if ( $$u > 0 )
		{
			$elapse = $$u . $cn;
			break;
		}
	}

	return $elapse;
}

/* $past = time(); // Some timestamp in the past
$now  = time()+23;     // Current timestamp
$diff = $now - $past;

echo '发表于' . time2Units($diff) . '前';
 */

/**
 *    获取URL地址
 *
 *    @author    LiuLei
 *    @param     mixed $query
 *    @param     string $rewrite_name
 *    @return    string
 */
function url($query, $rewrite_name = null)
{
    $re_on  = Conf::get('rewrite_enabled');
    $url = '';
    if (!$re_on)
    {
        /* Rewrite未开启 */
        $url = 'index.php?' . $query;
    }
    else
    {
        /* Rewrite已开启 */
        $re =& rewrite_engine();
        $rewrite = $re->get($query, $rewrite_name);

        $url = ($rewrite !== false) ? $rewrite : 'index.php?' . $query;
    }

    return str_replace('&', '&amp;', $url);
}

/**
 *    获取rewrite engine
 *
 *    @author    LiuLei
 *    @return    Object
 */
function &rewrite_engine()
{
    $re_name= Conf::get('rewrite_engine');
    static $re = null;
    if ($re === null)
    {
        include(APP_PATH . 'Common/Util/rewrite.base.php');
        include(APP_PATH . 'Common/Util/rewrite_engines/' . $re_name . '.rewrite.php');
        $re_class_name = ucfirst($re_name) . 'Rewrite';
        $re = new $re_class_name();
    }

    return $re;
}

/**
 *    转换团购活动状态值
 *
 *    @author    LiuLei
 *    @param     string $status_text
 *    @return    void
 */
function groupbuy_state_translator($state_text)
{
    switch ($state_text)
    {
        case 'all':         //全部团购活动
            return '';
        break;
        case 'on':         //进行中的团购活动
            return GROUP_ON;
        break;
        case 'canceled':    //已取消的团购活动
            return GROUP_CANCELED;
        break;
        case 'pending':     //未发布的团购活动
            return GROUP_PENDING;
        break;
        case 'finished':     //已完成的团购活动
            return GROUP_FINISHED;
        break;
        case 'end':     //已完成的团购活动
            return GROUP_END;
        break;
        default:            //全部团购活动
            return '';
        break;
    }
}

/**
 *    获取团购状态相应的文字表述
 *
 *    @author    LiuLei
 *    @param     int $group_state
 *    @return    string
 */
function group_state($group_state)
{
    $lang_key = '';
    switch ($group_state)
    {
        case GROUP_PENDING:
            $lang_key = 'group_pending';
        break;
        case GROUP_ON:
            $lang_key = 'group_on';
        break;
        case GROUP_CANCELED:
            $lang_key = 'group_canceled';
        break;
        case GROUP_FINISHED:
            $lang_key = 'group_finished';
        break;
        case GROUP_END:
            $lang_key = 'group_end';
        break;
    }

    return $lang_key  ? Lang::get($lang_key) : $lang_key;
}


/**
 *    计算剩余时间
 *
 *    @author    LiuLei
 *    @param     string $format
 *    @param     int $time;
 *    @return    string
 */
function lefttime($time, $format = null)
{
    $lefttime = $time - gmtime();
    if ($lefttime < 0)
    {
        return '';
    }
    if ($format === null)
    {
        if ($lefttime < 3600)
        {
            $format = Lang::get('lefttime_format_1');
        }
        elseif ($lefttime < 86400)
        {
            $format = Lang::get('lefttime_format_2');
        }
        else
        {
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

    return str_replace(array('%d', '%h', '%i', '%s'),array($d, $h,$m, $s), $format);
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
        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;
}

/**
 * 短消息过滤
 *
 * @return string
 */
function short_msg_filter($string)
{
    $ms = & ms();
    return $ms->pm->msg_filter($string);
}
function build_order_no($user_id,$arr)
{
	
	return date('ymdh').$user_id.rand(100000, 900000);
}
/**
 * 
 * @param unknown $code
 * @return unknown
 */
function get_cate_obj($code) {
	 
	include_once(APP_PATH . 'Common/Util/payments/alipay/' . $code . '.payment.php');
	$class_name = ucfirst($code) . 'Payment';
	return new $class_name();

}

function getip(){
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
		$ip = getenv("HTTP_CLIENT_IP");
	} else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	} else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
		$ip = getenv("REMOTE_ADDR");
	} else  if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} else {
		$ip = "unknown";
	}
	return $ip;
}
/**
 * 根据ip获得具体所在城市，如果得不到，返回false，让用户手动定位
 * @param string $ip
 * @return city|false
 */
function getLocation($ip=''){
	empty($ip) && $ip = getip();
	if($ip=="127.0.0.1") return "本机地址";
	$api = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=$ip";
	$json = @file_get_contents($api);//调用新浪IP地址库
	$arr = json_decode($json,true);//解析json
	$country = trim($arr['country']); //取得国家
	$province = trim($arr['province']);//获取省份
	$city = trim($arr['city']); //取得城市
	//分几种情况，c,p,c |c,p,'',|c,'',''|'','',''
	/* if((string)$country == "中国"){	
		if(!$province && !$city){
			//return array('status'=>1,'country'=>$country);
			return false;
		}
		if($province && !$city){
			//return array('status'=>2,'country'=>$country,'province'=>$province);
			return false;
		}
		if($province && $city){
			//return array('status'=>3,'country'=>$country,'province'=>$province,'city'=>$city);
			return false;
		}
		return false;
		
	}else{
		return false;
	} */
	if(!$city){
		return false;
	}
	return $city;
	
}

/**
 * 获取通过ip获取地理位置，带缓存
 */
function get_location_(){
	$location = trim($_SESSION['location']);
	if($location){
		return $location;
	}
	$_SESSION['location'] = getLocation();
	return $_SESSION['location'];
	 
}


function get_ret_url($query = '') {
	if (!empty($_GET['ret_url']))
	{
		$ret_url = trim($_GET['ret_url']);
	}
	else
	{
		if (isset($_SERVER['HTTP_REFERER']))
		{
			$ret_url = $_SERVER['HTTP_REFERER'];
		}
		elseif(!$url)
		{
			$ret_url = SITE_URL . '/index.php';
		}
		else{
			$ret_url = SITE_URL . '/index.php?'.$query;
		}
	}
}

/**
 * 获取用户现在所在商铺
 * @return number
 */
function get_user_instore_id($alert = 0) {
	$store_id =  isset($_SESSION ['current_store'] ['store_id']) ? $_SESSION ['current_store'] ['store_id']:0;
	if(!$store_id && $alert && !isset($_SESSION['alert_select_city'])){
		echo "<script>alert('请选择城市！');</script>";
		//保证只提醒一次，防止反复提醒
		$_SESSION['alert_select_city'] = 1;
		return ;
	}
	return $store_id;
}
function format_datetime($time) {
	return date('Y-m-d h:i:s',$time);
}
?>
