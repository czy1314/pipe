<?php

/**
 *    支付宝支付方式插件
 *
 *    @author    LorenLei
 *    @usage    none
 */

class AlipayPayment extends BasePayment
{
    /* 支付宝网关 */
    //var $_gateway   =   'https://www.alipay.com/cooperate/gateway.do';
    var $_gateway   =   'https://mapi.alipay.com/gateway.do';
    var $_code      =   'alipay';

    /**
     *    获取支付表单
     *
     *    @author    LorenLei
     *    @param     array $order_info  待支付的订单信息，必须包含总费用及唯一外部交易号 flag判断是否是首付还是月付
     *    @return    array
     */
    function get_payform($order_info,$flag=0)
    {
        $service = $this->_config['alipay_service'];
        $agent = 'C4335319945672464113';
        $id = $flag? $order_info['id']: $order_info['order_id'];
        $cate = $flag? 'fq': 'order';
        $price = $flag? $order_info['amount']: $order_info['order_amount'];               
        $params = array(

            /* 基本信息 */
            'service'           => $service,
            'partner'           => $this->_config['alipay_partner'],
            '_input_charset'    => CHARSET,
            'notify_url'        => $this->_create_notify_url($id,$cate),
            'return_url'        => $this->_create_return_url($id,$cate),

            /* 业务参数 */
            'subject'           => $this->_get_subject($order_info,$flag),
            //订单ID由不属签名验证的一部分，所以有可能被客户自行修改，所以在接收网关通知时要验证指定的订单ID的外部交易号是否与网关传过来的一致
            'out_trade_no'      => $this->_get_trade_sn($order_info,$flag),
            'price'             => $price,   //应付总价
            'quantity'          => 1,
            'payment_type'      => 1,
            //'body'				=> 	"订单描述",
            /* 物流参数 */
            //'logistics_type'    => 'EXPRESS',
            //'logistics_fee'     => 0,
            //'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',

            /* 买卖双方信息 */
            'seller_email'      => $this->_config['alipay_account']
        );

        
        $params['sign']         =   $this->_get_sign($params);
        $params['sign_type']    =   'MD5';

        return $this->_create_payform('GET', $params);
    }

    /**
     *    返回通知结果
     *
     *    @author    LorenLei
     *    @param     array $order_info
     *    @param     bool  $strict
     *    @return    array
     */
    function verify_notify($order_info, $cate = 'order', $strict = false)
    {
        if (empty($order_info))
        {
            $this->_error('order_info_empty');

            return false;
        }
       
        /* 初始化所需数据  得到POST或者GET*/
        $notify =   $this->_get_notify();
        /* 验证来路是否可信 */
        if ($strict)
        {
            /* 严格验证 */
            $verify_result = $this->_query_notify($notify['notify_id']);
            if(!$verify_result)
            {
                /* 来路不可信 */
                $this->_error('notify_unauthentic');

                return false;
            }
        }

        /* 验证通知是否可信 */
        $sign_result = $this->_verify_sign($notify);
        if (!$sign_result)
        {
            /* 若本地签名与网关签名不一致，说明签名不可信 */
            $this->_error('sign_inconsistent');
       
            return false;
        }
        /*   以上与参数无关 */       
	    $pay_obj = get_cate_obj($cate);	
        return $pay_obj->verify_status($order_info,$notify);
      
    }

    /**
     *    查询通知是否有效
     *
     *    @author    LorenLei
     *    @param     string $notify_id
     *    @return    string
     */
    function _query_notify($notify_id)
    {
        $query_url = "http://notify.alipay.com/trade/notify_query.do?partner={$this->_config['alipay_partner']}&notify_id={$notify_id}";
		//$query_url = "https://mapi.alipay.com/gateway.do?service=notify_verify&partner={$this->_config['alipay_partner']}&notify_id={$notify_id}";

        return (pipe_fopen($query_url, 60) === 'true');
    }

    /**
     *    获取签名字符串
     *
     *    @author    LorenLei
     *    @param     array $params
     *    @return    string
     */
    function _get_sign($params)
    {
        /* 去除不参与签名的数据 */
        unset($params['sign'], $params['sign_type'], $params['id'], $params['cate'], $params['app'], $params['act']);

        /* 排序 */
        ksort($params);
        reset($params);

        $sign  = '';
        foreach ($params AS $key => $value)
        {
            $sign  .= "{$key}={$value}&";
        }

        return md5(substr($sign, 0, -1) . $this->_config['alipay_key']);
    }

    /**
     *    验证签名是否可信
     *
     *    @author    LorenLei
     *    @param     array $notify
     *    @return    bool
     */
    function _verify_sign($notify)
    {
        $local_sign = $this->_get_sign($notify);

        return ($local_sign == $notify['sign']);
    }
    
    
}

?>