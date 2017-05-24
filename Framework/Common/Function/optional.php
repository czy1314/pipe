<?php

/**
 * 全局函数文件
 */

/**
 * 替换影响csv文件的字符     *
 * @param $str string 处理字符串
 */
function _replace_special_char($str, $replace = true)
{
    $str = str_replace("\r\n", "", $str);
    $str = str_replace("\t", "    ", $str);
    $str = str_replace("\n", "", $str);
    if ($replace == true)
    {
        $str = '"' . str_replace('"', '""', $str) . '"';
    }
    return $str;
}

?>