<?php
use Framework\Core\util\Lang;
use Framework\Core\util\Conf;
/**
 * 全局函数文件
 */
/* ---------------------以下是系统常量----------------------- */
/* 记录程序启动时间 */
define ( 'START_TIME', ecm_microtime () );

/* 判断请求方式 */
define ( 'IS_POST', (strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST') );

/* 判断请求方式 */
define ( 'IN_ECM', true );

/* 定义PHP_SELF常量 */
define ( 'PHP_SELF', htmlentities ( isset ( $_SERVER ['PHP_SELF'] ) ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME'] ) );

/* 当前Boot程序版本 */
define ( 'VERSION', '2.3.0' );

/* 当前Boot程序Release */
define ( 'RELEASE', '20120918' );

define ( 'IMAGE_FILE_TYPE', 'gif|jpg|jpeg|png' ); // 图片类型，上传图片时使用
/* 特殊文章分类ID */
define ( 'STORE_NAV', - 1 ); // 地区加盟商导航
define ( 'ACATE_HELP', 1 ); // 商城帮助
define ( 'ACATE_NOTICE', 2 ); // 商城快讯（公告）
define ( 'ACATE_SYSTEM', 3 ); // 内置文章
/* 系统文章分类code字段 */
define ( 'ACC_NOTICE', 'notice' ); // acategory表中code字段为notice时——商城公告类别
define ( 'ACC_SYSTEM', 'system' ); // acategory表中code字段为system时——内置文章类别
define ( 'ACC_HELP', 'help' ); // acategory表中code字段为help时——商城帮助类别

/* 邮件的优先级 */
define ( 'MAIL_PRIORITY_LOW', 1 );
define ( 'MAIL_PRIORITY_MID', 2 );
define ( 'MAIL_PRIORITY_HIGH', 3 );

/* 发送邮件的协议类型 */
define ( 'MAIL_PROTOCOL_LOCAL', 0, true );
define ( 'MAIL_PROTOCOL_SMTP', 1, true );

/* 数据调用的类型 */
define ( 'TYPE_GOODS', 1 );

/* 上传文件归属 */
define ( 'BELONG_ARTICLE', 1 );
define ( 'BELONG_USER', 2 );

/* 二级域名开关 */
! defined ( 'ENABLED_SUBDOMAIN' ) && define ( 'ENABLED_SUBDOMAIN', 0 );
/* 环境 */
define ( 'CHARSET', substr ( LANG, 3 ) );
define ( 'IS_AJAX', isset ( $_REQUEST ['ajax'] ) );
/* 短消息的标志 */
define ( 'MSG_SYSTEM', 0 ); // 系统消息
/* 通知类型 */
define ( 'NOTICE_MAIL', 1 ); // 邮件通知
define ( 'NOTICE_MSG', 2 ); // 站内短消息

/* ---------------------以下是PHP在不同版本，不同服务器上的兼容处理----------------------- */

/* 在部分IIS上会没有REQUEST_URI变量 */
$query_string = isset ( $_SERVER ['argv'] [0] ) ? $_SERVER ['argv'] [0] : $_SERVER ['QUERY_STRING'];
if (! isset ( $_SERVER ['REQUEST_URI'] )) {
	$_SERVER ['REQUEST_URI'] = PHP_SELF . '?' . $query_string;
} else {
	if (strpos ( $_SERVER ['REQUEST_URI'], '?' ) === false && $query_string) {
		$_SERVER ['REQUEST_URI'] .= '?' . $query_string;
	}
}
function __autoload($class) {
	if ($class) {
		$file = str_replace ( '\\', '/', $class );
		if (ROOT_PATH . '/' . $file) {
			include_once ROOT_PATH . '/' . $file . '.php';
		}
	}
}
function lang_file($file) {
	return ROOT_PATH . '/Lang/' . LANG . '/' . $file . '.lang.php';
}

/**
 * 获取视图链接
 *
 * @author LiuLei
 * @param string $engine        	
 * @return object
 */
function &v($is_new = false, $engine = 'default') {
	include_once (ROOT_PATH . '/Framework/Core/view/template.php');
	if ($is_new) {
		return new ecsTemplate ();
	} else {
		static $v = null;
		if ($v === null) {
			switch ($engine) {
				case 'default' :
					$v = new ecsTemplate ();
					break;
			}
		}
		
		return $v;
	}
}

/**
 * 获取一个模型
 *
 * @author LiuLei
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
 * 获取一个业务模型
 *
 * @param string $model_name        	
 * @param array $params        	
 * @param bool $is_new        	
 * @return object
 */
function &bm($model_name, $params = array(), $is_new = false) {
	static $models = array ();
	$model_hash = md5 ( $model_name . var_export ( $params, true ) );
	if ($is_new || ! isset ( $models [$model_hash] )) {
		$model_file = ROOT_PATH . '/includes/models/' . $model_name . '.model.php';
		if (! is_file ( $model_file )) {
			/* 不存在该文件，则无法获取模型 */
			return false;
		}
		include_once ($model_file);
		$model_name = ucfirst ( $model_name ) . 'BModel';
		if ($is_new) {
			return new $model_name ( $params, db () );
		}
		$models [$model_hash] = new $model_name ( $params, db () );
	}
	
	return $models [$model_hash];
}

/**
 * 获取当前控制器实例
 *
 * @author LiuLei
 * @return void
 */
function c(&$app) {
	$GLOBALS ['Boot_APP'] = & $app;
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
 * @author LiuLei
 * @return Object
 */
function &cc() {
	return $GLOBALS ['Boot_APP'];
}

/**
 * 导入一个类
 *
 * @author LiuLei
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
 * 将default.abc类的字符串转为$default['abc']
 *
 * @author LiuLei
 * @param string $str        	
 * @return string
 */
function strtokey($str, $owner = '') {
	if (! $str) {
		return '';
	}
	if ($owner) {
		return $owner . '[\'' . str_replace ( '.', '\'][\'', $str ) . '\']';
	} else {
		$parts = explode ( '.', $str );
		$owner = '$' . $parts [0];
		unset ( $parts [0] );
		return strtokey ( implode ( '.', $parts ), $owner );
	}
}
/**
 * 跟踪调试
 *
 * @author LiuLei
 * @param mixed $var        	
 * @return void
 */
function trace($var) {
	static $i = 0;
	echo $i, '.', 	// weileshouyevar_dump($var), '<br />';
	$i ++;
}

/**
 * rdump的别名
 *
 * @author LiuLei
 * @param
 *        	any
 * @return void
 */
function dump($arr) {
	$args = func_get_args ();
	call_user_func_array ( 'rdump', $args );
}

/**
 * 格式化显示出变量
 *
 * @author LiuLei
 * @param
 *        	any
 * @return void
 */
function rdump($arr) {
	// weileshouyeecho '<pre>';
	array_walk ( func_get_args (), create_function ( '&$item, $key', 'print_r($item);' ) );
	// weileshouyeecho '</pre>';
	exit ();
}

/**
 * 格式化并显示出变量类型
 *
 * @author LiuLei
 * @param
 *        	any
 * @return void
 */
function vdump($arr) {
	// weileshouyeecho '<pre>';
	array_walk ( func_get_args (), create_function ( '&$item, $key', '//weileshouyevar_dump($item);' ) );
	// weileshouyeecho '</pre>';
	exit ();
}

/**
 * 创建MySQL数据库对象实例
 *
 * @author wj
 * @return object
 */
function &db() {
	include_once (ROOT_PATH . '/Framework/Core/model/mysql.php');
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
			$db = new cls_mysql ();
			$db->cache_dir = ROOT_PATH . '/temp/query_caches/';
			$db->connect ( $cfg ['host'] . ':' . $cfg ['port'], $cfg ['user'], $cfg ['pass'], $cfg ['path'], $charset );
		} else {
			trigger_error ( 'Unkown database type.', E_USER_ERROR );
		}
	}
	
	return $db;
}

/**
 * 获得当前的域名
 *
 * @return string
 */
function get_domain() {
	/* 协议 */
	$protocol = (isset ( $_SERVER ['HTTPS'] ) && (strtolower ( $_SERVER ['HTTPS'] ) != 'off')) ? 'https://' : 'http://';
	
	/* 域名或IP地址 */
	if (isset ( $_SERVER ['HTTP_X_FORWARDED_HOST'] )) {
		$host = $_SERVER ['HTTP_X_FORWARDED_HOST'];
	} elseif (isset ( $_SERVER ['HTTP_HOST'] )) {
		$host = $_SERVER ['HTTP_HOST'];
	} else {
		/* 端口 */
		if (isset ( $_SERVER ['SERVER_PORT'] )) {
			$port = ':' . $_SERVER ['SERVER_PORT'];
			
			if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
				$port = '';
			}
		} else {
			$port = '';
		}
		
		if (isset ( $_SERVER ['SERVER_NAME'] )) {
			$host = $_SERVER ['SERVER_NAME'] . $port;
		} elseif (isset ( $_SERVER ['SERVER_ADDR'] )) {
			$host = $_SERVER ['SERVER_ADDR'] . $port;
		}
	}
	
	return $protocol . $host;
}

/**
 * 获得网站的URL地址
 *
 * @return string
 */
function site_url() {
	return get_domain () . substr ( PHP_SELF, 0, strrpos ( PHP_SELF, '/' ) );
}

/**
 * 截取UTF-8编码下字符串的函数
 *
 * @param string $str
 *        	被截取的字符串
 * @param int $length
 *        	截取的长度
 * @param bool $append
 *        	是否附加省略号
 *        	
 * @return string
 */
function sub_str($string, $length = 0, $append = true) {
	if (strlen ( $string ) <= $length) {
		return $string;
	}
	
	$string = str_replace ( array (
			'&amp;',
			'&quot;',
			'&lt;',
			'&gt;' 
	), array (
			'&',
			'"',
			'<',
			'>' 
	), $string );
	
	$strcut = '';
	
	if (strtolower ( CHARSET ) == 'utf-8') {
		$n = $tn = $noc = 0;
		while ( $n < strlen ( $string ) ) {
			
			$t = ord ( $string [$n] );
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1;
				$n ++;
				$noc ++;
			} elseif (194 <= $t && $t <= 223) {
				$tn = 2;
				$n += 2;
				$noc += 2;
			} elseif (224 <= $t && $t < 239) {
				$tn = 3;
				$n += 3;
				$noc += 2;
			} elseif (240 <= $t && $t <= 247) {
				$tn = 4;
				$n += 4;
				$noc += 2;
			} elseif (248 <= $t && $t <= 251) {
				$tn = 5;
				$n += 5;
				$noc += 2;
			} elseif ($t == 252 || $t == 253) {
				$tn = 6;
				$n += 6;
				$noc += 2;
			} else {
				$n ++;
			}
			
			if ($noc >= $length) {
				break;
			}
		}
		if ($noc > $length) {
			$n -= $tn;
		}
		
		$strcut = substr ( $string, 0, $n );
	} else {
		for($i = 0; $i < $length; $i ++) {
			$strcut .= ord ( $string [$i] ) > 127 ? $string [$i] . $string [++ $i] : $string [$i];
		}
	}
	
	$strcut = str_replace ( array (
			'&',
			'"',
			'<',
			'>' 
	), array (
			'&amp;',
			'&quot;',
			'&lt;',
			'&gt;' 
	), $strcut );
	
	if ($append && $string != $strcut) {
		$strcut .= '...';
	}
	
	return $strcut;
}

/**
 * 获得用户的真实IP地址
 *
 * @return string
 */
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
 * 验证输入的邮件地址是否合法
 *
 * @param string $email
 *        	需要验证的邮件地址
 *        	
 * @return bool
 */
function is_email($user_email) {
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,5}\$/i";
	if (strpos ( $user_email, '@' ) !== false && strpos ( $user_email, '.' ) !== false) {
		if (preg_match ( $chars, $user_email )) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * 验证手机号码
 */
function is_tel($tel) {
	if (preg_match ( "/1[3458]{1}\d{1}-?\d{8}$/", trim ( $tel ) )) {
		return true;
	} else {
		return false;
	}
}
function validation_filter_id_card($id_card) {
	if (strlen ( $id_card ) == 18) {
		return idcard_checksum18 ( $id_card );
	} elseif ((strlen ( $id_card ) == 15)) {
		$id_card = idcard_15to18 ( $id_card );
		return idcard_checksum18 ( $id_card );
	} else {
		return false;
	}
}
// 计算身份证校验码，根据国家标准GB 11643-1999
function idcard_verify_number($idcard_base) {
	if (strlen ( $idcard_base ) != 17) {
		return false;
	}
	// 加权因子
	$factor = array (
			7,
			9,
			10,
			5,
			8,
			4,
			2,
			1,
			6,
			3,
			7,
			9,
			10,
			5,
			8,
			4,
			2 
	);
	// 校验码对应值
	$verify_number_list = array (
			'1',
			'0',
			'X',
			'9',
			'8',
			'7',
			'6',
			'5',
			'4',
			'3',
			'2' 
	);
	$checksum = 0;
	for($i = 0; $i < strlen ( $idcard_base ); $i ++) {
		$checksum += substr ( $idcard_base, $i, 1 ) * $factor [$i];
	}
	$mod = $checksum % 11;
	$verify_number = $verify_number_list [$mod];
	return $verify_number;
}
// 将15位身份证升级到18位
function idcard_15to18($idcard) {
	if (strlen ( $idcard ) != 15) {
		return false;
	} else {
		// 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
		if (array_search ( substr ( $idcard, 12, 3 ), array (
				'996',
				'997',
				'998',
				'999' 
		) ) !== false) {
			$idcard = substr ( $idcard, 0, 6 ) . '18' . substr ( $idcard, 6, 9 );
		} else {
			$idcard = substr ( $idcard, 0, 6 ) . '19' . substr ( $idcard, 6, 9 );
		}
	}
	$idcard = $idcard . idcard_verify_number ( $idcard );
	return $idcard;
}
// 18位身份证校验码有效性检查
function idcard_checksum18($idcard) {
	if (strlen ( $idcard ) != 18) {
		return false;
	}
	$idcard_base = substr ( $idcard, 0, 17 );
	if (idcard_verify_number ( $idcard_base ) != strtoupper ( substr ( $idcard, 17, 1 ) )) {
		return false;
	} else {
		return true;
	}
}

/**
 * 检查是否为一个合法的时间格式
 *
 * @param string $time        	
 * @return void
 */
function is_time($time) {
	$pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';
	
	return preg_match ( $pattern, $time );
}

/**
 * 获得服务器上的 GD 版本
 *
 * @return int 可能的值为0，1，2
 */
function gd_version() {
	import ( 'image.lib' );
	
	return imageProcessor::gd_version ();
}
/**
 * 变量中的特殊字符进行转义 防止0xbf27变相注入
 *
 * @access public
 * @param mix $value        	
 *
 * @return mix
 */
function _addslashes($value) {
	if (empty ( $value )) {
		return $value;
	}
	$string = preg_replace ( '/&((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $value );
	return addslashes ( $string );
}

/**
 * 递归方式的对变量中的特殊字符进行转义
 *
 * @access public
 * @param mix $value        	
 *
 * @return mix
 */
function addslashes_deep($value) {
	if (empty ( $value )) {
		return $value;
	} else {
		return is_array ( $value ) ? array_map ( 'addslashes_deep', $value ) : addslashes ( $value );
	}
}

/**
 * 将对象成员变量或者数组的特殊字符进行转义
 *
 * @access public
 * @param mix $obj
 *        	对象或者数组
 * @author Xuan Yan
 *        
 * @return mix 对象或者数组
 */
function addslashes_deep_obj($obj) {
	if (is_object ( $obj ) == true) {
		foreach ( $obj as $key => $val ) {
			if (($val) == true) {
				$obj->$key = addslashes_deep_obj ( $val );
			} else {
				$obj->$key = addslashes_deep ( $val );
			}
		}
	} else {
		$obj = addslashes_deep ( $obj );
	}
	
	return $obj;
}

/**
 * 递归方式的对变量中的特殊字符去除转义
 *
 * @access public
 * @param mix $value        	
 *
 * @return mix
 */
function stripslashes_deep($value) {
	if (empty ( $value )) {
		return $value;
	} else {
		return is_array ( $value ) ? array_map ( 'stripslashes_deep', $value ) : stripslashes ( $value );
	}
}
/**
 * 将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
 *
 * @access public
 * @param string $str
 *        	待转换字串
 *        	
 * @return string $str 处理后字串
 */
function make_semiangle($str) {
	$arr = array (
			'０' => '0',
			'１' => '1',
			'２' => '2',
			'３' => '3',
			'４' => '4',
			'５' => '5',
			'６' => '6',
			'７' => '7',
			'８' => '8',
			'９' => '9',
			'Ａ' => 'A',
			'Ｂ' => 'B',
			'Ｃ' => 'C',
			'Ｄ' => 'D',
			'Ｅ' => 'E',
			'Ｆ' => 'F',
			'Ｇ' => 'G',
			'Ｈ' => 'H',
			'Ｉ' => 'I',
			'Ｊ' => 'J',
			'Ｋ' => 'K',
			'Ｌ' => 'L',
			'Ｍ' => 'M',
			'Ｎ' => 'N',
			'Ｏ' => 'O',
			'Ｐ' => 'P',
			'Ｑ' => 'Q',
			'Ｒ' => 'R',
			'Ｓ' => 'S',
			'Ｔ' => 'T',
			'Ｕ' => 'U',
			'Ｖ' => 'V',
			'Ｗ' => 'W',
			'Ｘ' => 'X',
			'Ｙ' => 'Y',
			'Ｚ' => 'Z',
			'ａ' => 'a',
			'ｂ' => 'b',
			'ｃ' => 'c',
			'ｄ' => 'd',
			'ｅ' => 'e',
			'ｆ' => 'f',
			'ｇ' => 'g',
			'ｈ' => 'h',
			'ｉ' => 'i',
			'ｊ' => 'j',
			'ｋ' => 'k',
			'ｌ' => 'l',
			'ｍ' => 'm',
			'ｎ' => 'n',
			'ｏ' => 'o',
			'ｐ' => 'p',
			'ｑ' => 'q',
			'ｒ' => 'r',
			'ｓ' => 's',
			'ｔ' => 't',
			'ｕ' => 'u',
			'ｖ' => 'v',
			'ｗ' => 'w',
			'ｘ' => 'x',
			'ｙ' => 'y',
			'ｚ' => 'z',
			'（' => '(',
			'）' => ')',
			'［' => '[',
			'］' => ']',
			'【' => '[',
			'】' => ']',
			'〖' => '[',
			'〗' => ']',
			'「' => '[',
			'」' => ']',
			'『' => '[',
			'』' => ']',
			'｛' => '{',
			'｝' => '}',
			'《' => '<',
			'》' => '>',
			'％' => '%',
			'＋' => '+',
			'—' => '-',
			'－' => '-',
			'～' => '-',
			'：' => ':',
			'。' => '.',
			'、' => ',',
			'，' => '.',
			'、' => '.',
			'；' => ',',
			'？' => '?',
			'！' => '!',
			'…' => '-',
			'‖' => '|',
			'＂' => '"',
			'＇' => '`',
			'｀' => '`',
			'｜' => '|',
			'〃' => '"',
			'　' => ' ' 
	);
	
	return strtr ( $str, $arr );
}

/**
 * 格式化费用：可以输入数字或百分比的地方
 *
 * @param string $fee
 *        	输入的费用
 */
function format_fee($fee) {
	$fee = make_semiangle ( $fee );
	if (strpos ( $fee, '%' ) === false) {
		return floatval ( $fee );
	} else {
		return floatval ( $fee ) . '%';
	}
}

/**
 * 根据总金额和费率计算费用
 *
 * @param float $amount
 *        	总金额
 * @param string $rate
 *        	费率（可以是固定费率，也可以是百分比）
 * @param string $type
 *        	类型：s 保价费 p 支付手续费 i 发票税费
 * @return float 费用
 */
function compute_fee($amount, $rate, $type) {
	$amount = floatval ( $amount );
	if (strpos ( $rate, '%' ) === false) {
		return round ( floatval ( $rate ), 2 );
	} else {
		$rate = floatval ( $rate ) / 100;
		if ($type == 's') {
			return round ( $amount * $rate, 2 );
		} elseif ($type == 'p') {
			return round ( $amount * $rate / (1 - $rate), 2 );
		} else {
			return round ( $amount * $rate, 2 );
		}
	}
}

/**
 * 获取服务器的ip
 *
 * @access public
 *        
 * @return string
 *
 */
function real_server_ip() {
	static $serverip = NULL;
	
	if ($serverip !== NULL) {
		return $serverip;
	}
	
	if (isset ( $_SERVER )) {
		if (isset ( $_SERVER ['SERVER_ADDR'] )) {
			$serverip = $_SERVER ['SERVER_ADDR'];
		} else {
			$serverip = '0.0.0.0';
		}
	} else {
		$serverip = getenv ( 'SERVER_ADDR' );
	}
	
	return $serverip;
}
/**
 * 获得用户操作系统的换行符
 *
 * @access public
 * @return string
 */
function get_crlf() {
	/* LF (Line Feed, 0x0A, \N) 和 CR(Carriage Return, 0x0D, \R) */
	if (stristr ( $_SERVER ['HTTP_USER_AGENT'], 'Win' )) {
		$the_crlf = "\r\n";
	} elseif (stristr ( $_SERVER ['HTTP_USER_AGENT'], 'Mac' )) {
		$the_crlf = "\r"; // for old MAC OS
	} else {
		$the_crlf = "\n";
	}
	
	return $the_crlf;
}

/**
 * 编码转换函数
 *
 * @author wj
 * @param string $source_lang
 *        	待转换编码
 * @param string $target_lang
 *        	转换后编码
 * @param string $source_string
 *        	需要转换编码的字串
 * @return string
 */
function ecm_iconv($source_lang, $target_lang, $source_string = '') {
	static $chs = NULL;
	
	/* 如果字符串为空或者字符串不需要转换，直接返回 */
	if ($source_lang == $target_lang || $source_string == '' || preg_match ( "/[\x80-\xFF]+/", $source_string ) == 0) {
		return $source_string;
	}
	
	if ($chs === NULL) {
		import ( 'iconv.lib' );
		$chs = new Chinese ( ROOT_PATH . '/' );
	}
	
	return strtolower ( $target_lang ) == 'utf-8' ? addslashes ( stripslashes ( $chs->Convert ( $source_lang, $target_lang, $source_string ) ) ) : $chs->Convert ( $source_lang, $target_lang, $source_string );
}
function ecm_geoip($ip) {
	static $fp = NULL, $offset = array (), $index = NULL;
	
	$ip = gethostbyname ( $ip );
	$ipdot = explode ( '.', $ip );
	$ip = pack ( 'N', ip2long ( $ip ) );
	
	$ipdot [0] = ( int ) $ipdot [0];
	$ipdot [1] = ( int ) $ipdot [1];
	if ($ipdot [0] == 10 || $ipdot [0] == 127 || ($ipdot [0] == 192 && $ipdot [1] == 168) || ($ipdot [0] == 172 && ($ipdot [1] >= 16 && $ipdot [1] <= 31))) {
		return 'LAN';
	}
	
	if ($fp === NULL) {
		$fp = fopen ( ROOT_PATH . 'includes/codetable/ipdata.dat', 'rb' );
		if ($fp === false) {
			return 'Invalid IP data file';
		}
		$offset = unpack ( 'Nlen', fread ( $fp, 4 ) );
		if ($offset ['len'] < 4) {
			return 'Invalid IP data file';
		}
		$index = fread ( $fp, $offset ['len'] - 4 );
	}
	
	$length = $offset ['len'] - 1028;
	$start = unpack ( 'Vlen', $index [$ipdot [0] * 4] . $index [$ipdot [0] * 4 + 1] . $index [$ipdot [0] * 4 + 2] . $index [$ipdot [0] * 4 + 3] );
	for($start = $start ['len'] * 8 + 1024; $start < $length; $start += 8) {
		if ($index {$start} . $index {$start + 1} . $index {$start + 2} . $index {$start + 3} >= $ip) {
			$index_offset = unpack ( 'Vlen', $index {$start + 4} . $index {$start + 5} . $index {$start + 6} . "\x0" );
			$index_length = unpack ( 'Clen', $index {$start + 7} );
			break;
		}
	}
	
	fseek ( $fp, $offset ['len'] + $index_offset ['len'] - 1024 );
	$area = fread ( $fp, $index_length ['len'] );
	
	fclose ( $fp );
	$fp = NULL;
	
	return $area;
}
function ecm_json_encode($value) {
	if (CHARSET == 'utf-8' && function_exists ( 'json_encode' )) {
		return json_encode ( $value );
	}
	
	$props = '';
	if (is_object ( $value )) {
		foreach ( get_object_vars ( $value ) as $name => $propValue ) {
			if (isset ( $propValue )) {
				$props .= $props ? ',' . ecm_json_encode ( $name ) : ecm_json_encode ( $name );
				$props .= ':' . ecm_json_encode ( $propValue );
			}
		}
		return '{' . $props . '}';
	} elseif (is_array ( $value )) {
		$keys = array_keys ( $value );
		if (! empty ( $value ) && ! empty ( $value ) && ($keys [0] != '0' || $keys != range ( 0, count ( $value ) - 1 ))) {
			foreach ( $value as $key => $val ) {
				$key = ( string ) $key;
				$props .= $props ? ',' . ecm_json_encode ( $key ) : ecm_json_encode ( $key );
				$props .= ':' . ecm_json_encode ( $val );
			}
			return '{' . $props . '}';
		} else {
			$length = count ( $value );
			for($i = 0; $i < $length; $i ++) {
				$props .= ($props != '') ? ',' . ecm_json_encode ( $value [$i] ) : ecm_json_encode ( $value [$i] );
			}
			return '[' . $props . ']';
		}
	} elseif (is_string ( $value )) {
		// $value = stripslashes($value);
		$replace = array (
				'\\' => '\\\\',
				"\n" => '\n',
				"\t" => '\t',
				'/' => '\/',
				"\r" => '\r',
				"\b" => '\b',
				"\f" => '\f',
				'"' => '\"',
				chr ( 0x08 ) => '\b',
				chr ( 0x0C ) => '\f' 
		);
		$value = strtr ( $value, $replace );
		if (CHARSET == 'big5' && $value {strlen ( $value ) - 1} == '\\') {
			$value = substr ( $value, 0, strlen ( $value ) - 1 );
		}
		return '"' . $value . '"';
	} elseif (is_numeric ( $value )) {
		return $value;
	} elseif (is_bool ( $value )) {
		return $value ? 'true' : 'false';
	} elseif (empty ( $value )) {
		return '""';
	} else {
		return $value;
	}
}
function ecm_json_decode($value, $type = 0) {
	if (CHARSET == 'utf-8' && function_exists ( 'json_decode' )) {
		return empty ( $type ) ? json_decode ( $value ) : get_object_vars_deep ( json_decode ( $value ) );
	}
	
	if (! class_exists ( 'JSON' )) {
		import ( 'json.lib' );
	}
	$json = new JSON ();
	return $json->decode ( $value, $type );
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
function file_ext($filename) {
	return trim ( substr ( strrchr ( $filename, '.' ), 1, 10 ) );
}

/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access public
 * @param mix $item_list
 *        	列表数组或字符串,如果为字符串时,字符串只接受数字串
 * @param string $field_name
 *        	字段名称
 * @author wj
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
 * 创建目录（如果该目录的上级目录不存在，会先创建上级目录）
 * 依赖于 ROOT_PATH 常量，且只能创建 ROOT_PATH 目录下的目录
 * 目录分隔符必须是 / 不能是 \
 *
 * @param string $absolute_path
 *        	绝对路径
 * @param int $mode
 *        	目录权限
 * @return bool
 */
function ecm_mkdir($absolute_path, $mode = 0777) {
	if (is_dir ( $absolute_path )) {
		return true;
	}
	
	$root_path = ROOT_PATH;
	$relative_path = str_replace ( $root_path, '', $absolute_path );
	$each_path = explode ( '/', $relative_path );
	$cur_path = $root_path; // 当前循环处理的路径
	foreach ( $each_path as $path ) {
		if ($path) {
			$cur_path = $cur_path . '/' . $path;
			if (! is_dir ( $cur_path )) {
				if (@mkdir ( $cur_path, $mode )) {
					fclose ( fopen ( $cur_path . '/index.htm', 'w' ) );
				} else {
					return false;
				}
			}
		}
	}
	
	return true;
}

/**
 * 删除目录,不支持目录中带 .
 * .
 *
 * @param string $dir        	
 *
 * @return boolen
 */
function ecm_rmdir($dir) {
	$dir = str_replace ( array (
			'..',
			"\n",
			"\r" 
	), array (
			'',
			'',
			'' 
	), $dir );
	$ret_val = false;
	if (is_dir ( $dir )) {
		$d = @dir ( $dir );
		if ($d) {
			while ( false !== ($entry = $d->read ()) ) {
				if ($entry != '.' && $entry != '..') {
					$entry = $dir . '/' . $entry;
					if (is_dir ( $entry )) {
						ecm_rmdir ( $entry );
					} else {
						@unlink ( $entry );
					}
				}
			}
			$d->close ();
			$ret_val = rmdir ( $dir );
		}
	} else {
		$ret_val = unlink ( $dir );
	}
	
	return $ret_val;
}
function price_format($price, $price_format = NULL) {
	if (empty ( $price ))
		$price = '0.00';
	$price = number_format ( $price, 2 );
	
	if ($price_format === NULL) {
		$price_format = Conf::get ( 'price_format' );
	}
	
	return sprintf ( $price_format, $price );
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
function ecm_setcookie($key, $value, $expire = 0, $cookie_path = COOKIE_PATH, $cookie_domain = COOKIE_DOMAIN) {
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
function ecm_getcookie($key = '') {
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
function ecm_iconv_deep($source_lang, $target_lang, $value) {
	if (empty ( $value )) {
		return $value;
	} else {
		if (is_array ( $value )) {
			foreach ( $value as $k => $v ) {
				$value [$k] = ecm_iconv_deep ( $source_lang, $target_lang, $v );
			}
			return $value;
		} elseif (is_string ( $value )) {
			return ecm_iconv ( $source_lang, $target_lang, $value );
		} else {
			return $value;
		}
	}
}

/**
 * fopen封装函数
 *
 * @author wj
 * @param string $url        	
 * @param int $limit        	
 * @param string $post        	
 * @param string $cookie        	
 * @param boolen $bysocket        	
 * @param string $ip        	
 * @param int $timeout        	
 * @param boolen $block        	
 * @return responseText
 */
function ecm_fopen($url, $limit = 500000, $post = '', $cookie = '', $bysocket = false, $ip = '', $timeout = 15, $block = true) {
	$return = '';
	$matches = parse_url ( $url );
	$host = $matches ['host'];
	$path = $matches ['path'] ? $matches ['path'] . ($matches ['query'] ? '?' . $matches ['query'] : '') : '/';
	$port = ! empty ( $matches ['port'] ) ? $matches ['port'] : 80;
	
	if ($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		// $out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: ' . strlen ( $post ) . "\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		// $out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen ( ($ip ? $ip : $host), $port, $errno, $errstr, $timeout );
	if (! $fp) {
		return '';
	} else {
		stream_set_blocking ( $fp, $block );
		stream_set_timeout ( $fp, $timeout );
		@fwrite ( $fp, $out );
		$status = stream_get_meta_data ( $fp );
		if (! $status ['timed_out']) {
			while ( ! feof ( $fp ) ) {
				if (($header = @fgets ( $fp )) && ($header == "\r\n" || $header == "\n")) {
					break;
				}
			}
			
			$stop = false;
			while ( ! feof ( $fp ) && ! $stop ) {
				$data = fread ( $fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit) );
				$return .= $data;
				if ($limit) {
					$limit -= strlen ( $data );
					$stop = $limit <= 0;
				}
			}
		}
		@fclose ( $fp );
		return $return;
	}
}

/**
 * 危险 HTML代码过滤器
 *
 * @param string $html
 *        	需要过滤的html代码
 *        	
 * @return string
 */
function html_filter($html) {
	$filter = array (
			"/\s/",
			"/<(\/?)(script|i?frame|style|html|body|title|link|\?|\%)([^>]*?)>/isU", // object|meta|
			"/(<[^>]*)on[a-zA-Z]\s*=([^>]*>)/isU" 
	);
	
	$replace = array (
			" ",
			"&lt;\\1\\2\\3&gt;",
			"\\1\\2" 
	);
	
	$str = preg_replace ( $filter, $replace, $html );
	return $str;
}

/**
 * 清理系统所有编译文件，缓存文件、模板结构数据
 *
 * @author wj
 * @param
 *        	void
 *        	
 * @return void
 */
function clean_cache() {
	/* 清理缓存 */
	$cache_dirs = array (
			ROOT_PATH . '/temp/caches',
			ROOT_PATH . '/temp/compiled/mall/admin',
			ROOT_PATH . '/temp/compiled/mall/',
			ROOT_PATH . '/temp/compiled/store/admin',
			ROOT_PATH . '/temp/compiled/store',
			ROOT_PATH . '/temp/js',
			ROOT_PATH . '/temp/query_caches',
			ROOT_PATH . '/temp/tag_caches',
			ROOT_PATH . '/temp/style' 
	);
	
	foreach ( $cache_dirs as $dir ) {
		$d = dir ( $dir );
		if ($d) {
			while ( false !== ($entry = $d->read ()) ) {
				if ($entry != '.' && $entry != '..' && $entry != '.svn' && $entry != 'admin' && $entry != 'index.html') {
					ecm_rmdir ( $dir . '/' . $entry );
				}
			}
			$d->close ();
		}
	}
	
	/* 主分类缓存数据 */
	if (is_file ( ROOT_PATH . '/temp/query_caches/cache_category.php' )) {
		unlink ( ROOT_PATH . '/temp/query_caches/cache_category.php' );
	}
	
	/* 清除一个周前图片缓存并回收多余目录 */

	$expiry_time = strtotime ( '-1 week' );
	$path = ROOT_PATH . '/temp/thumb';
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
								ecm_rmdir ( $subdir );
							} else {
								$left_dir_count ++;
							}
						}
					}
					$sd->close ();
					if ($left_dir_count == 0)
						ecm_rmdir ( $dir );
				}
			}
		}
		$d->close ();
	}
}

/**
 * 如果系统不存在file_put_contents函数则声明该函数
 *
 * @author wj
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
 * 去除字符串右侧可能出现的乱码
 *
 * @author wj
 * @param string $str
 *        	字符串
 *        	
 *        	
 * @return string
 */
function trim_right($str) {
	$len = strlen ( $str );
	/* 为空或单个字符直接返回 */
	if ($len == 0 || ord ( $str {$len - 1} ) < 127) {
		return $str;
	}
	/* 有前导字符的直接把前导字符去掉 */
	if (ord ( $str {$len - 1} ) >= 192) {
		return substr ( $str, 0, $len - 1 );
	}
	/* 有非独立的字符，先把非独立字符去掉，再验证非独立的字符是不是一个完整的字，不是连原来前导字符也截取掉 */
	$r_len = strlen ( rtrim ( $str, "\x80..\xBF" ) );
	if ($r_len == 0 || ord ( $str {$r_len - 1} ) < 127) {
		return sub_str ( $str, 0, $r_len );
	}
	
	$as_num = ord ( ~ $str {$r_len - 1} );
	if ($as_num > (1 << (6 + $r_len - $len))) {
		return $str;
	} else {
		return substr ( $str, 0, $r_len - 1 );
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
function _at($fun) {
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
 * 返回是否是通过浏览器访问的页面
 *
 * @author wj
 * @param
 *        	void
 * @return boolen
 */
function is_from_browser() {
	static $ret_val = null;
	if ($ret_val === null) {
		$ret_val = false;
		$ua = isset ( $_SERVER ['HTTP_USER_AGENT'] ) ? strtolower ( $_SERVER ['HTTP_USER_AGENT'] ) : '';
		if ($ua) {
			if ((strpos ( $ua, 'mozilla' ) !== false) && ((strpos ( $ua, 'msie' ) !== false) || (strpos ( $ua, 'gecko' ) !== false))) {
				$ret_val = true;
			} elseif (strpos ( $ua, 'opera' )) {
				$ret_val = true;
			}
		}
	}
	return $ret_val;
}

/**
 * 从文件或数组中定义常量
 *
 * @author LiuLei
 * @param mixed $source        	
 * @return void
 */
//function define_all($source) {
//	if (is_string ( $source )) {
//		/* 导入数组 */
//		$source = include ($source);
//	}
//	if (! is_array ( $source )) {
//		/* 不是数组，无法定义 */
//		return false;
//	}
//	foreach ( $source as $key => $value ) {
//		if (is_string ( $value ) || is_numeric ( $value ) || is_bool ( $value ) || is_null ( $value )) {
//			/* 如果是可被定义的，则定义 */
//			define ( strtoupper ( $key ), $value );
//		}
//	}
//}

/**
 * 获取当前时间的微秒数
 *
 * @author LiuLei
 * @return float
 */
function ecm_microtime() {
	if (PHP_VERSION >= 5.0) {
		return microtime ( true );
	} else {
		list ( $usec, $sec ) = explode ( " ", microtime () );
		
		return (( float ) $usec + ( float ) $sec);
	}
}
function html_script($text) {
	$str = "'<script[^>]*?>.*?</script\s*>'si";
	$text = preg_replace ( '/onerror/', '', $text );
	$text = preg_replace ( $str, '', $text );
	$text = str_replace ( '[', '&#091;', $text );
	$text = str_replace ( ']', '&#093;', $text );
	$text = str_replace ( '|', '&#124;', $text );
	return $text;
}

/* 自定义sql防注入 */
function inject_check($sql_str) {
	return eregi ( 'select|insert|and|or|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile', $sql_str );
}
function verify_id($id = null) {
	if (! $id) {
		exit ( '没有提交参数！' );
	} elseif (inject_check ( $id )) {
		exit ( '提交的参数非法！' );
	} elseif (! is_numeric ( $id )) {
		exit ( '提交的参数非法！' );
	}
	$id = intval ( $id );
	
	return $id;
}
function str_check($str) {
	if (! get_magic_quotes_gpc ()) {
		$str = addslashes ( $str ); // 进行过滤
	}
	$str = str_replace ( "_", "\_", $str );
	$str = str_replace ( "%", "\%", $str );
	
	return $str;
}
function post_check($post) {
	if (! get_magic_quotes_gpc ()) {
		$post = addslashes ( $post );
	}
	$post = str_replace ( "_", "\_", $post );
	$post = str_replace ( "%", "\%", $post );
	$post = nl2br ( $post );
	$post = htmlspecialchars ( $post );
	
	return $post;
}
/**
 * 删除该目录下所有文件
 * 
 * @param unknown $dir        	
 *
 */
function rm_file($dir) {
	if (is_dir ( $dir )) {
		$res = opendir ( $dir );
		// 列出 images 目录中的文件
		while ( ($file = readdir ( $res )) !== false ) {
			@unlink ( $dir . '/' . $file );
		}
		closedir ( $res );
	}
}
function curl_file_get_contents($durl, $data) {
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $durl . $data );
	curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$r = curl_exec ( $ch );
	$info = curl_getinfo ( $ch );
	curl_close ( $ch );
	if ($r === false) {
		$result = file_get_contents ( $durl . $data );
		if (! $result) {
			return false;
		} else {
			return $result;
		}
	}
	
	return $r;
}
function request_zzx($durl, $data) {
	$headers = array (
			'Content-Type:application/json',
			'Authorization: Basic ' . base64_encode ( ZZX_USER . ':' . ZZX_PWD ) 
	);
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $ch, CURLOPT_URL, $durl );
	curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
	if ($data)
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$r = curl_exec ( $ch );
	$info = curl_getinfo ( $ch );
	curl_close ( $ch );
	if ($r === false) {
		return $info;
	}
	return $r;
}
/**
 * 定时器
 */
function set_interval($time) {
	sleep ( $time );
	abc ();
	set_interval ( $time );
}
function abc() {
	file_put_contents ( "./test.log", "test" . time () . "\r\n", FILE_APPEND );
}
function get_code($bite) {
	// return 666666;
	$code = '';
	for($i = 0; $i < $bite; $i ++) {
		$code .= rand ( 1, 9 );
	}
	return $code;
}
/**
 * 生成hidden input值
 */
function create_hidden_value() {
	$num = rand ( 2000, 4000000000 );
	return base64_encode ( $num );
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
function get_token() {
	;
}

?>