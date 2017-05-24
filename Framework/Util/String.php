<?php
/**
 * 字符串操作相关方法
 */

namespace Framework\Util;

use Framework\Base\Object;

class String extends Object
{

    /**
     * 去除字符串右侧可能出现的乱码
     *
     * @param string $str
     *   字符串
     * @return string
     */
    function trim_right($str)
    {
        $len = strlen($str);
        /* 为空或单个字符直接返回 */
        if ($len == 0 || ord($str{$len - 1}) < 127) {
            return $str;
        }
        /* 有前导字符的直接把前导字符去掉 */
        if (ord($str{$len - 1}) >= 192) {
            return substr($str, 0, $len - 1);
        }
        /* 有非独立的字符，先把非独立字符去掉，再验证非独立的字符是不是一个完整的字，不是连原来前导字符也截取掉 */
        $r_len = strlen(rtrim($str, "\x80..\xBF"));
        if ($r_len == 0 || ord($str{$r_len - 1}) < 127) {
            return sub_str($str, 0, $r_len);
        }

        $as_num = ord(~$str{$r_len - 1});
        if ($as_num > (1 << (6 + $r_len - $len))) {
            return $str;
        } else {
            return substr($str, 0, $r_len - 1);
        }
    }

    /**
     * 编码转换函数
     *
     * 
     * @param string $source_lang
     *            待转换编码
     * @param string $target_lang
     *            转换后编码
     * @param string $source_string
     *            需要转换编码的字串
     * @return string
     */
    function pipe_iconv($source_lang, $target_lang, $source_string = '')
    {
        static $chs = NULL;

        /* 如果字符串为空或者字符串不需要转换，直接返回 */
        if ($source_lang == $target_lang || $source_string == '' || preg_match("/[\x80-\xFF]+/", $source_string) == 0) {
            return $source_string;
        }

        if ($chs === NULL) {
            import('iconv');
            $chs = new Chinese (ROOT_PATH . '/');
        }

        return strtolower($target_lang) == 'utf-8' ? addslashes(stripslashes($chs->Convert($source_lang, $target_lang, $source_string))) : $chs->Convert($source_lang, $target_lang, $source_string);
    }

    function pipe_geoip($ip)
    {
        static $fp = NULL, $offset = array(), $index = NULL;

        $ip = gethostbyname($ip);
        $ipdot = explode('.', $ip);
        $ip = pack('N', ip2long($ip));

        $ipdot [0] = ( int )$ipdot [0];
        $ipdot [1] = ( int )$ipdot [1];
        if ($ipdot [0] == 10 || $ipdot [0] == 127 || ($ipdot [0] == 192 && $ipdot [1] == 168) || ($ipdot [0] == 172 && ($ipdot [1] >= 16 && $ipdot [1] <= 31))) {
            return 'LAN';
        }

        if ($fp === NULL) {
            $fp = fopen(ROOT_PATH . 'includes/codetable/ipdata.dat', 'rb');
            if ($fp === false) {
                return 'Invalid IP data file';
            }
            $offset = unpack('Nlen', fread($fp, 4));
            if ($offset ['len'] < 4) {
                return 'Invalid IP data file';
            }
            $index = fread($fp, $offset ['len'] - 4);
        }

        $length = $offset ['len'] - 1028;
        $start = unpack('Vlen', $index [$ipdot [0] * 4] . $index [$ipdot [0] * 4 + 1] . $index [$ipdot [0] * 4 + 2] . $index [$ipdot [0] * 4 + 3]);
        for ($start = $start ['len'] * 8 + 1024; $start < $length; $start += 8) {
            if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
                $index_offset = unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
                $index_length = unpack('Clen', $index{$start + 7});
                break;
            }
        }

        fseek($fp, $offset ['len'] + $index_offset ['len'] - 1024);
        $area = fread($fp, $index_length ['len']);

        fclose($fp);
        $fp = NULL;

        return $area;
    }

    function pipe_json_encode($value)
    {
        if (CHARSET == 'utf-8' && function_exists('json_encode')) {
            return json_encode($value);
        }

        $props = '';
        if (is_object($value)) {
            foreach (get_object_vars($value) as $name => $propValue) {
                if (isset ($propValue)) {
                    $props .= $props ? ',' . pipe_json_encode($name) : pipe_json_encode($name);
                    $props .= ':' . pipe_json_encode($propValue);
                }
            }
            return '{' . $props . '}';
        } elseif (is_array($value)) {
            $keys = array_keys($value);
            if (!empty ($value) && !empty ($value) && ($keys [0] != '0' || $keys != range(0, count($value) - 1))) {
                foreach ($value as $key => $val) {
                    $key = ( string )$key;
                    $props .= $props ? ',' . pipe_json_encode($key) : pipe_json_encode($key);
                    $props .= ':' . pipe_json_encode($val);
                }
                return '{' . $props . '}';
            } else {
                $length = count($value);
                for ($i = 0; $i < $length; $i++) {
                    $props .= ($props != '') ? ',' . pipe_json_encode($value [$i]) : pipe_json_encode($value [$i]);
                }
                return '[' . $props . ']';
            }
        } elseif (is_string($value)) {
            // $value = stripslashes($value);
            $replace = array(
                '\\' => '\\\\',
                "\n" => '\n',
                "\t" => '\t',
                '/' => '\/',
                "\r" => '\r',
                "\b" => '\b',
                "\f" => '\f',
                '"' => '\"',
                chr(0x08) => '\b',
                chr(0x0C) => '\f'
            );
            $value = strtr($value, $replace);
            if (CHARSET == 'big5' && $value{strlen($value) - 1} == '\\') {
                $value = substr($value, 0, strlen($value) - 1);
            }
            return '"' . $value . '"';
        } elseif (is_numeric($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (empty ($value)) {
            return '""';
        } else {
            return $value;
        }
    }

    function pipe_json_decode($value, $type = 0)
    {
        if (CHARSET == 'utf-8' && function_exists('json_decode')) {
            return empty ($type) ? json_decode($value) : get_object_vars_deep(json_decode($value));
        }

        if (!class_exists('JSON')) {
            import('json');
        }
        $json = new JSON ();
        return $json->decode($value, $type);
    }



    /**
     * 截取UTF-8编码下字符串的函数     *
     * @param string $str        	被截取的字符串
     * @param int $length        	截取的长度
     * @param bool $append        	是否附加省略号
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
     * 将default.abc类的字符串转为$default['abc']
     *
     * @author LorenLei
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


}