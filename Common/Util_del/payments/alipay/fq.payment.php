<?php

class FqPayment extends Object
{
	function  verify_status($order_info,$notify)
	{
		if ($order_info['pay_sn'] != $notify['out_trade_no'])
		{
			/* 通知中的订单与欲改变的订单不一致 */
			$this->_error('通知中的订单与欲改变的订单不一致');

			return false;
		}
		if ($order_info['amount'] != $notify['total_fee'])
		{
			/* 支付的金额与实际金额不一致 */
			$this->_error('price_inconsistent');
			 
			return false;
		}
		//至此，说明通知是可信的，订单也是对应的，可信的
		/* 这里不只是付款通知，有可能是发货通知，确认收货通知 */
		
		  
		/* 按通知结果返回相应的结果 */
		switch ($notify['trade_status'])
		{
			case 'WAIT_SELLER_SEND_GOODS':      //买家已付款，等待卖家发货
		
				$order_status = LOG_PAIED;
				break;
		
			case 'WAIT_BUYER_CONFIRM_GOODS':    //卖家已发货
		
				$order_status = ORDER_SHIPPED;
				break;
					
				/* TRADE_FINISHED : 该种交易状态只在两种情况下出现
				 1、开通了普通即时到账，买家付款成功后。
				2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
				modify tyioocom
				*/
			case 'TRADE_FINISHED':              //交易结束
			case 'TRADE_SUCCESS':               // 交易成功
				if ($order_info['status'] == LOG_NO_PAY)
				{
					/* 如果是等待付款中，则说明是即时到账交易，这时将状态改为等待发货 */
					$order_status = LOG_PAIED;
				}
				else
				{
					/* 说明是第三方担保交易，交易结束 */
					$order_status = ORDER_FINISHED;
				}
				break;
					
				/* TRADE_SUCCESS : 该种交易状态只在两种情况下出现
				 1、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
				modify tyioocom
				*/
			case 'TRADE_SUCCESS':              //交易结束
				if ($order_info['status'] == LOG_NO_PAY)
				{
					/* 如果是等待付款中，则说明是即时到账交易，这时将状态改为已付款 */
					$order_status = LOG_PAIED;
				}
				else
				{
					/* 说明是第三方担保交易，交易结束 */
					$order_status = ORDER_FINISHED;
				}
				break;
		
			case 'TRADE_CLOSED':                
				//不变				
				break;
		
			default:
				$this->_error('undefined_status');
				return false;
				break;
		}
		
		switch ($notify['refund_status'])
		{
			case 'REFUND_SUCCESS':              //退款成功，取消订单
				$order_status = ORDER_CANCLED;
				break;
		}
		
		return array(
				'target'    =>  $order_status,
		);
		
	}
	
	function get_order_pcode($id) {
		/* 获取订单信息 */
        $log_model =& m('payback_log');
        $order_info  = $log_model->get($id);
        if (empty($order_info))
        {
        	/* 没有该订单 */
        	$this->show_warning('forbidden');
        	return;
        }
        $payment_code = $order_info['payment_code'];
        return array($order_info,$payment_code);
	}
	
	
	function _change_order_status($id, $notify_result) {
		$log_model = & m('payback_log');
		$one = $log_model->get("id='{$id}' and status = 0");
		if($one){
			//防止重复标记
			$log_model->edit($id,array(
					'status'=>$notify_result['target'],
					'pay_time'=>time()
			)
			);
			if($notify_result['target'] == LOG_PAIED){
				$param = array(
						'conditions' => "log_id='{$id}'",
						'fields'     => 'id'
								);
				$payback_model = & m('payback');
				$GLOBALS['all_payb'] = array();
				$array = $payback_model->findAll($param);
				function dfd ($value, $key){
					$GLOBALS['all_payb'][] = $value['id'];
				};
				array_walk($array,'dfd' );
				$payback_model->edit($GLOBALS['all_payb'],array('status'=>PAYBACKED,'pay_time'=>time()));
			}
		}	
		
	}
}

?>