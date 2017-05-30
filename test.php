<?php
//DECIMAL[(M,[D])] 或 NUMERIC
$str = "tinyint(2)  unsigned,smallint(8) ,  mediumint(23), bigint(8), int(10) unsigned,char(10), varchar(100)";
$arr = explode(',',$str);
foreach($arr as $k => $val){
    //validNumber($val);
}

function validNumber($num){
    $num = trim($num);
    $ma =array();
    preg_match_all('/(\w*int)\(\d+\)\s*(unsigned)*/',$num,$ma);
    var_dump($ma);
    $arr = array('tinyint'=>127,'smallint'=>32767,'mediumint'=>8388607,'int'=>2147483647,''=>9223372036854775807);
    foreach($arr as $name=>$val){
        if(isset($ma[1][0]) && $ma[1][0] == $name){
            if(isset($ma[2][0]) && $ma[2][0] = trim($ma[2][0])){
                $min = 0;
                $max = $val*2+1;
            }else{
                $min = -($val+1);
                $max = $val;
            }
            break;
        }
    }



    var_dump($min);
    var_dump($max);
}

function validString($num){
    //char(2) varchar(2) *text(232)
    $num = trim($num);
    $ma =array();
    $max = 0;
    preg_match_all('/(\w*char)\((\d+)\)|(\w*text)/i',$num,$ma);
    if(isset($ma[2][0])){
        $max = $ma[2][0];
    }
    if(isset($ma[3][0])){
        $ma[3][0] = strtolower($ma[3][0]);
        switch($ma[3][0]){
            case 'tinytext':$max = 255 ;break;
            case 'text':$max = 65535 ;break;
            case 'mediumtext':$max = 16777215 ;break;
            case 'nediumtext':$max = 16777215 ;break;
            case 'longtext':$max = 4294967295 ;break;
        }
    }
    var_dump($max);
    return $max;

}

$str = "char(10),TinyText, varchar(100),text,
TinyText,最大长度255个字元(2^8-1),
Blob,	最大长度65535个字元(2^16-1),
Text,	最大长度65535个字元(2^16-1),
MediumBlob	,最大长度 16777215 个字元(2^24-1),
MediumText,	最大长度 16777215 个字元(2^24-1),
LongBlob,	最大长度4294967295个字元 (2^32-1),
LongText,	最大长度4294967295个字元 (2^32-1)";
$arr = explode(',',$str);
foreach($arr as $k => $val){
    //validString($val);
}

function validTel($arr){
    return  preg_match('/手机|电话|tel|phone/',$arr['column_comment']) || preg_match('/tel|phone|handset/',$arr['column_name']);
}

function validEmail($arr){
    return  preg_match('/邮箱|email|邮件/',$arr['column_comment']) || preg_match('/email|mail|post|dak/',$arr['column_name']);
}

function validIp($arr){
    return  preg_match('/ip/',$arr['column_comment']) || preg_match('/ip/',$arr['column_name']);
}

//var_dump(validTel(array('column_comment'=>'码','column_name'=>'handsetNumber')));