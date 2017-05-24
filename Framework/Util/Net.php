<?php
namespace Framework\Util;
use Framework\Base\Object;

class Net extends Object{

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





    function getip()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
            $ip = getenv("REMOTE_ADDR");
        } else if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "unknown";
        }
        return $ip;
    }
}