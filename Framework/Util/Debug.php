<?php
class Debug extends \Framework\Base\Object{

    /**
     * 跟踪调试
     *
     * @author LorenLei
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
     * @author LorenLei
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
     * @author LorenLei
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
     * @author LorenLei
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

}