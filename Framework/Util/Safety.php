<?php

/**
 * 安全类,包含一些过滤XSS，防止CSRF，过滤SQL注入的工具方法
 */
class Safety extends \Framework\Base\Object{

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


    /**
     * 生成hidden input值
     */
    function create_hidden_value() {
        $num = rand ( 2000, 4000000000 );
        return base64_encode ( $num );
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
     *
     * @author LorenLei
     *  @usage 分配hidden value
     * @param
     * @return
     */
    function assign_hidden_value() {
        $_SESSION ['hidden_value_'] = create_hidden_value ();

        $this->assign ( "hidden_value_", "<input name='hidden_value_' type='hidden' value='" . $_SESSION ['hidden_value_'] . "'/>" );
    }
    /**
     * 获得hidden input 字符串
     *
     * @return string
     */
    function get_hidden_str() {
        if (! $_SESSION ['hidden_value']) {
            $this->show_warning ( "hidden_value不存在!" );
            return '';
        }
        return "<input type='hidden' name='hidden_value' value='{$_SESSION['hidden_value']}' />";
    }
    function auto_hidden() {
        if (IS_POST && isset ( $_POST ['hidden_value_'] )) {

            $hidden_value_ = $_SESSION ['hidden_value_'];
            $p_hidden_value_ = $_POST ['hidden_value_'];

            if (trim ( $p_hidden_value_ ) != $hidden_value_) {

                echo "<script>alert('不能重复提交！');</script>";
                // exit;
                unset ( $_SESSION ['hidden_value_'] );
            }
            unset ( $_SESSION ['hidden_value_'] );
        }
    }
}