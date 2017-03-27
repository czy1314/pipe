<?php
/**
 *    微信支付插件
 *
 *    @author    vchuangcn.taobao.com
 *    
 */
class WxjsapiPayment extends BasePayment
{
	

	function get_payform($order_info)
	{
		if(!defined('WXAPPID'))
        {
            define("WXAPPID",'wxcb135342f8c486b7');
            define("WXMCHID",'10013737');
            define("WXKEY", '6ac4d0de6186ce7b889c6f2f5a32c497');
            define("WXAPPSECRET", '560a1c4aa467123796c07d1bd83bee7b');
            define("WXCURL_TIMEOUT", 30);
            //define('WXNOTIFY_URL',$this->_create_notify_url($order_info['order_id']));
			define('WXNOTIFY_URL','http://www.daogoge.com/mall/wxpaty/demo/notify_url.php');
		    //define('WXNOTIFY_URL','http://www.daogoge.com/mall/wx_callback.php');            
            define('WXJS_API_CALL_URL',$this->_create_notify_url($order_info['order_id']));
            define('WXSSLCERT_PATH',ROOT_PATH.'/data/cacert/'.$order_info['seller_id'].'/apiclient_cert.pem');
            define('WXSSLKEY_PATH',ROOT_PATH.'/data/cacert/'.$order_info['seller_id'].'/apiclient_key.pem');
        }
        require_once(dirname(__FILE__)."/WxPayPubHelper/WxPayPubHelper.php");

		$jsApi = new JsApi_pub();
		$out_trade_no = $this->_get_trade_sn($order_info);
		if (!isset($_GET['code']))
        {
            $redirect = urlencode(SITE_URL.'/index.php?app=cashier&act=wxjsapi&order_id='.$order_info['order_id']);
            $url = $jsApi->createOauthUrlForCode($redirect);
			//echo $url;
            Header("Location: $url"); 
        }else
        {
            
            
            $code = $_GET['code'];
            
            $jsApi->setCode($code);
            $openid = $jsApi->getOpenId();
			
        }
        if($openid)
        {
            $unifiedOrder = new UnifiedOrder_pub();

            $unifiedOrder->setParameter("openid","$openid");//商品描述
            $unifiedOrder->setParameter("body",$out_trade_no);//商品描述
            $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号 
            $unifiedOrder->setParameter("attach",strval($order_info['order_id']));//商户支付日志
            $unifiedOrder->setParameter("total_fee",strval(intval($order_info['order_amount']*100)));//总金额
            $unifiedOrder->setParameter("notify_url",WXNOTIFY_URL);//通知地址 
            $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型


            $prepay_id = $unifiedOrder->getPrepayId();

            $jsApi->setPrepayId($prepay_id);

            $jsApiParameters = $jsApi->getParameters();
			
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
            $allow_use_wxPay = true;

            if(strpos($user_agent, 'MicroMessenger') === false)
            {
                $allow_use_wxPay = false;
            }
            else
            {
                preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches);
                if($matches[2] < 5.0)
                {
                    $allow_use_wxPay = false;
                }
            }
            $html .= '<script language="javascript">';
           
            if($allow_use_wxPay)
            {
                $html .= "function jsApiCall(){";
                $html .= "WeixinJSBridge.invoke(";
                $html .= "'getBrandWCPayRequest',";
                $html .= $jsApiParameters.",";
                $html .= "function(res){";
                $html .= "WeixinJSBridge.log(res.err_msg);";
                $html .= "}";
                $html .= ");";
                $html .= "}";
                $html .= "function callpay(){";
                $html .= 'if (typeof WeixinJSBridge == "undefined"){';
                $html .= "if( document.addEventListener ){";
                $html .= "document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);";
                $html .= "}else if (document.attachEvent){";
                $html .= "document.attachEvent('WeixinJSBridgeReady', jsApiCall); ";
                $html .= "document.attachEvent('onWeixinJSBridgeReady', jsApiCall);";
                $html .= "}";
                $html .= "}else{";
                $html .= "jsApiCall();";
                $html .= "}}";
            }
            else
            {
                $html .= 'function callpay(){';
                $html .= 'alert("您的微信不支持支付功能,请更新您的微信版本")';
                $html .= "}";

            }

            $html .= '</script>';
            $html .= '<button type="button" class="btn btn-primary btn-lg btn-block" onclick="callpay()">确认微信支付</button>';
            //$html .= '<button class="red_btn" type="button" onclick="callpay()">hhhh</button>';

        }
		else
        {
            $html .= '<script language="javascript">';
            $html .= 'function callpay(){';
            $html .= 'alert("您的微信不支持支付功能,请更新您的微信到最新版本")';
            $html .= "}";
            $html .= '</script>';
            $html .= '<button type="button" class="btn btn-primary btn-lg btn-block" onclick="callpay()">确认微信支付</button>';
            //$html .= '<button class="red_btn" type="button" onclick="callpay()">微信支付</button>';

           
        }
        return $html;      
	}

    function _create_notify_url($order_id)
    {
        return SITE_URL . "/wx_callback.php";
    }

	function verify_notify($order_info, $strict = false)
    {
        
        if(!defined('WXAPPID'))
        {
            /* define("WXAPPID", $this->_config['appid']);
            define("WXMCHID", $this->_config['mchid']);
            define("WXKEY", $this->_config['key']);
            define("WXAPPSECRET", $this->_config['appsecret']); */
			define("WXAPPID",'wxcb135342f8c486b7');
            define("WXMCHID",'10013737');
            define("WXKEY", '6ac4d0de6186ce7b889c6f2f5a32c497');
            define("WXAPPSECRET", '560a1c4aa467123796c07d1bd83bee7b');
            define("WXCURL_TIMEOUT", 30);
            //define('WXNOTIFY_URL',$this->_create_notify_url($order_info['order_id']));
			define('WXNOTIFY_URL','http://www.daogoge.com/mall/wxpaty/demo/notify_url.php');
		    //define('WXNOTIFY_URL','http://www.daogoge.com/mall/wx_callback.php');
            define('WXJS_API_CALL_URL',$this->_create_notify_url($order_info['order_id']));
            define('WXSSLCERT_PATH',ROOT_PATH.'/data/cacert/'.$order_info['seller_id'].'/apiclient_cert.pem');
            define('WXSSLKEY_PATH',ROOT_PATH.'/data/cacert/'.$order_info['seller_id'].'/apiclient_key.pem');
        }
         require_once(dirname(__FILE__)."/WxPayPubHelper/WxPayPubHelper.php");
        $notify = new Notify_pub();
        $xml = $order_info['xml'];
		
        $notify->saveData($xml);
        if($notify->checkSign() == true)
        {
            return true;

        }
        else
        {
            $this->_error('sign_inconsistent');
            return false;
        }       

    }
	function  log_result($file,$word) 
	{
	    $fp = fopen($file,"a");
	    flock($fp, LOCK_EX) ;
	    fwrite($fp,"执行日期：".strftime("%Y-%m-%d-%H：%M：%S",time())."\n".$word."\n\n");
	    flock($fp, LOCK_UN);
	    fclose($fp);
	}

}
?>