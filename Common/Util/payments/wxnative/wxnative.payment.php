<?php
/**
 *    微信扫码支付插件
 *
 *    @author    vchuangcn.taobao.com
 *    
 */
class WxnativePayment extends BasePayment
{

	function get_payform($order_info)
	{
		if(!defined('WXAPPID'))
		{
			define("WXAPPID", $this->_config['appid']);
            define("WXMCHID", $this->_config['mchid']);
            define("WXKEY", $this->_config['key']);
            define("WXAPPSECRET", $this->_config['appsecret']);
            define("WXCURL_TIMEOUT", 30);
            define('WXNOTIFY_URL',$this->_create_notify_url($order_info['order_id']));
			define('WXJS_API_CALL_URL',$this->_create_notify_url($order_info['order_id']));
			define('WXSSLCERT_PATH',ROOT_PATH.'/data/cacert/'.$order_info['seller_id'].'/apiclient_cert.pem');
			define('WXSSLKEY_PATH',ROOT_PATH.'/data/cacert/'.$order_info['seller_id'].'/apiclient_key.pem');
		}
		require_once(dirname(__FILE__)."/WxPayPubHelper/WxPayPubHelper.php");

		$unifiedOrder = new UnifiedOrder_pub();
		$out_trade_no = $this->_get_trade_sn($order_info);
        $unifiedOrder->setParameter("body",$out_trade_no);//商品描述
        $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
        $unifiedOrder->setParameter("attach",strval($order_info['order_id']));//商户支付日志 
        $unifiedOrder->setParameter("total_fee",strval(intval($order_info['order_amount']*100)));//总金额
        $unifiedOrder->setParameter("notify_url",WXNOTIFY_URL);//通知地址 
        $unifiedOrder->setParameter("trade_type","NATIVE");//交易类型

        

        $unifiedOrderResult = $unifiedOrder->getResult();

        $html = '<button type="button" onclick="javascript:alert(\'出错了\')">微信支付</button>';

        if($unifiedOrderResult["code_url"] != NULL)
        {
            $code_url = $unifiedOrderResult["code_url"];
            $html = '<div class="wxnative" style="text-align:center">';
            $html .= $this->getcode($code_url,$order_info['order_id']);
            $html .= "</div>";

            $html .= "<div style=\"text-align:center;font-size:14px;\"><a href=\"index.php?app=buyer_order\" style=\"color:#ff0000;\">支付后点击此处</a>查看我的订单</div>";
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
			define("WXAPPID", $this->_config['appid']);
            define("WXMCHID", $this->_config['mchid']);
            define("WXKEY", $this->_config['key']);
            define("WXAPPSECRET", $this->_config['appsecret']);
            define("WXCURL_TIMEOUT", 30);
            define('WXNOTIFY_URL',$this->_create_notify_url($order_info['order_id']));
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
			if ($notify->data["return_code"] == "FAIL")
			{
				return false;
			}
			else
			{
				$total_fee = $notify->data["total_fee"];
				$out_trade_no  = $notify->data["out_trade_no"];
				if ($order_info['out_trade_sn'] != $out_trade_no)
        		{
            		/* 通知中的订单与欲改变的订单不一致 */
            		$this->_error('order_inconsistent');
					return false;
        		}
        		if ($order_info['order_amount']*100 != $total_fee)
        		{
            		/* 支付的金额与实际金额不一致 */
            		$this->_error('price_inconsistent');
					return false;
        		}
        		return array(
            		'target'    =>  ORDER_ACCEPTED,
       			);
			}

		}
		else
		{
			$this->_error('sign_inconsistent');
			return false;
		}

	}






	function getcode($url,$order_id)
	{
		if(file_exists(dirname(__FILE__) . '/phpqrcode.php')){
            include(dirname(__FILE__) . '/phpqrcode.php');
        }
        // 纠错级别：L、M、Q、H 
        $errorCorrectionLevel = 'Q';  
        // 点的大小：1到10 
        $matrixPointSize = 5;
        // 生成的文件名
        $tmp = ROOT_PATH .'/data/qrcode/';
        if(!is_dir($tmp)){
            @mkdir($tmp);
        }
        $filename = $tmp . $errorCorrectionLevel . $matrixPointSize . $order_id . '.png';
        QRcode::png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        return '<img src="data/qrcode/'.basename($filename).'" />';
	}


}
?>