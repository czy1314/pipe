<?php
namespace  Framework\Util;
use \Framework\Base\Object;
class Input extends Object{

    function Input(){
        /* 数据过滤 */
        if (!get_magic_quotes_gpc())
        {
            $_GET   = addslashes_deep($_GET);
            $_POST  = addslashes_deep($_POST);
            $_COOKIE= addslashes_deep($_COOKIE);
        }
    }
    function post($name){
       if(!isset($_POST[$name])){
           return '';
       }
        return $this->clean($_POST[$name]);
    }
    function get($name){
        if(!isset($_GET[$name])){
            return '';
        }
        return $this->clean($_GET[$name]);
    }

    function clean($val){
        $scurity = load('Safety');
        return $scurity->clean($val);
    }
    function cookie($name){
        if(!isset($_COOKIE[$name])){
            return '';
        }
        return $this->clean($_COOKIE[$name]);
    }


}