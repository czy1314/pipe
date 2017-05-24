<?php

/**
 *    自动提醒
 *
 *    @author    LorenLei
 *    @usage    none
 */
class Notify_pbTask extends BaseTask
{
    function run()
    {
    	/* 上报提醒网关 */
        $return = curl_file_get_contents(RUNTIME_URL,'url='.urlencode($_SERVER['PHP_SELF'].'?app=timerun'));
        if(is_array($return)){
        	mail(DEVELOPER,'分期订单超期定时提醒url上传出错', json_encode($return));
        }

    }

}

?>
