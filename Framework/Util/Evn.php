<?php
/**
*环境相关
 */
namespace Framework\Util;
use Framework\Base\Object;
class Evn extends Object{

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
     * 获得服务器上的 GD 版本
     *
     * @return int 可能的值为0，1，2
     */
    function gd_version() {
        import ( 'image' );
        return imageProcessor::gd_version ();
    }

    /**
     * 获取当前时间的微秒数
     *
     * @author LorenLei
     * @return float
     */
    function pipe_microtime() {
        if (PHP_VERSION >= 5.0) {
            return microtime ( true );
        } else {
            list ( $usec, $sec ) = explode ( " ", microtime () );

            return (( float ) $usec + ( float ) $sec);
        }
    }

    /**
     * 返回是否是通过浏览器访问的页面
     *
     * 
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
     *    获取环境变量
     *
     *    @author    LorenLei
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



    function get_ret_url($query = '')
    {
        if (!empty($_GET['ret_url'])) {
            $ret_url = trim($_GET['ret_url']);
        } else {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $ret_url = $_SERVER['HTTP_REFERER'];
            } elseif (!$url) {
                $ret_url = SITE_URL . '/index.php';
            } else {
                $ret_url = SITE_URL . '/index.php?' . $query;
            }
        }
    }

}