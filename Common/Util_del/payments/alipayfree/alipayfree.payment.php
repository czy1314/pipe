<?php

/**
 *    支付宝免签支付方式 
 *
 *    @author    LorenLei
 *    @usage    none
 */
class AlipayfreePayment extends BasePayment {

    function get_payform($order_info) {

        ?>
        <body onLoad="javascript:document.E_FORM.submit()">
            <form name="E_FORM" action="https://shenghuo.alipay.com/send/payment/fill.htm" method="post">  
                <input type="hidden" name="optEmail" value="<?php echo $this->_config['alipayfree_account']; ?>">  
                <input type="hidden" name="payAmount" value="<?php echo $order_info['order_amount'];?>">  
                <input type="hidden" name="title" value="<?php echo $this->_get_subject($order_info);?>">  
                <input type="hidden" name="memo" value="<?php echo $this->_get_memo($order_info);?>">
                <input type="hidden" name="isSend" value="true"/>
                <input type="hidden" name="smsNo" value="<?php echo $this->_payment_desc['desc']; ?>">  
            </form>  
        </body>
        <?php
        exit;
        
    }
    
    
    function _get_memo($order_info){
        $text = "Order_id:".$order_info['order_sn'].";buyer:".$order_info['buyer_name'].";Amount:".$order_info['order_amount'];
        return $text;
    }

}
?>