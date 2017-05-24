<?php

/**
 *    微信支付插件
 *
 *    @author    LorenLei
 *    @usage    none
 */

class WeixinPayment extends BasePayment
{

    /**
     *    获取支付表单
     *
     *    @author    LorenLei
     *    @param     array $order_info  待支付的订单信息，必须包含总费用及唯一外部交易号
     *    @return    array
     */
    function get_payform($order_info)
    {
        define('APPID' , $this->_config['weixin_appid']);  //appid
		define('APPKEY' ,$this->_config['weixin_paySignkey']); //paysign key
		define('SIGNTYPE', "sha1"); //method
		define('PARTNERKEY',$this->_config['weixin_partnerKey']);//通加密串
		define('APPSERCERT', $this->_config['weixin_appSecret']);
        require_once(dirname(__FILE__)."/weixin/WxPayHelper.php");
		$commonUtil = new CommonUtil();
        $wxPayHelper = new WxPayHelper();
		$wxPayHelper->setParameter("bank_type", "WX");
		$wxPayHelper->setParameter("body", $this->_get_trade_sn($order_info));
		$wxPayHelper->setParameter("partner",$this->_config['weixin_partnerId']);
		$wxPayHelper->setParameter("out_trade_no", $this->_get_trade_sn($order_info));
		$wxPayHelper->setParameter("total_fee", strval(intval($order_info['order_amount']*100)));
		$wxPayHelper->setParameter("fee_type", "1");
		$wxPayHelper->setParameter("notify_url", $this->_create_notify_url($order_info['order_id']));
		$wxPayHelper->setParameter("spbill_create_ip", real_ip());
		$wxPayHelper->setParameter("input_charset", strtoupper(CHARSET));
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

		$html = '<script language="javascript">';
        $html .= 'function callpay(){';
        if($allow_use_wxPay === false)
        {
            $html .= 'alert("您的微信不支持支付功能,请更新您的微信版本")';
        }
        else
        {
            $html .= "WeixinJSBridge.invoke('getBrandWCPayRequest',".$wxPayHelper->create_biz_package().",function(res){";
            $html .= "if(res.err_msg == 'get_brand_wcpay_request:ok'){window.location.href='WxPaySuccess.php?order_sn=".$order['order_sn']."'}";
            $html .= "if(res.err_msg == 'get_brand_wcpay_request:cancel'){alert('您已经取消此次支付')}";
            $html .= "if(res.err_msg == 'get_brand_wcpay_request:fail'){alert('支付失败')}";
            $html .= "})";
        }
        $html .= '}</script>';
        $html .= '<button type="button" onclick="callpay()">微信支付</button>';
        return $html;
    }

    /**
     *    返回通知结果
     *
     *    @author    LorenLei
     *    @param     array $order_info
     *    @param     bool  $strict
     *    @return    array
     */
    function verify_notify($order_info, $strict = false)
    {
        $partnerKey = $this->_config['weixin_partnerKey'];
        ksort($_GET);
        reset($_GET);
        $sign = '';
        foreach ($_GET AS $key=>$val)
        {
            if ($key != 'sign' && $key != 'act' && $key != 'order_id' && $key != 'app' && $val != '')
            {
                $sign .= "$key=$val&";
            }
        }
        $sign .='key='.$payment['partnerKey'];
		
		if(strtoupper(md5($sign)) != $_GET['sign'])
		{
			/* 若本地签名与网关签名不一致，说明签名不可信 */
            $this->_error('sign_inconsistent');

            return;
		}

		 if ($order_info['out_trade_sn'] != $_GET['out_trade_no'])
        {
            /* 通知中的订单与欲改变的订单不一致 */
            $this->_error('order_inconsistent');

            return false;
        }

		if ($order_info['order_amount']*100 != $_GET['total_fee'])
        {
            /* 支付的金额与实际金额不一致 */
            $this->_error('price_inconsistent');

            return false;
        }

        return true;
    }

    
}

?>