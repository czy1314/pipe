<?php

/**
 *    买家的订单管理控制器
 *
 *    @author    LorenLei
 *    @usage    none
 */
class Seller_orderApp extends StoreadminBaseApp
{
    function index()
    {
        /* 获取订单列表 */
        $this->_get_orders();

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('order_manage'), 'index.php?app=seller_order',
                         LANG::get('order_list'));

        /* 当前用户中心菜单 */
        $type = (isset($_GET['type']) && $_GET['type'] != '') ? trim($_GET['type']) : 'all_orders';
        $this->_curitem('order_manage');
        $this->_curmenu($type);
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_manage'));
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/templates/ui-lightness/jquery.ui.css',
        ));
        /* 显示订单列表 */
        $this->display('seller_order.index.html');
    }

    /**
     *    查看订单详情
     *
     *    @author    LorenLei
     *    @return    void
     */
    function view()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

        $model_order =& m('order');
        $order_info  = $model_order->findAll(array(
            'conditions'    => "order_alias.order_id={$order_id}",
            'join'          => 'has_orderextm',
        ));
        $order_info = current($order_info);
        if (!$order_info)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* 团购信息 */
        if ($order_info['extension'] == 'groupbuy')
        {
            $groupbuy_mod = &m('groupbuy');
            $group = $groupbuy_mod->get(array(
                'join' => 'be_join',
                'conditions' => 'order_id=' . $order_id,
                'fields' => 'gb.group_id',
            ));
            $this->assign('group_id',$group['group_id']);
        }

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('order_manage'), 'index.php?app=seller_order',
                         LANG::get('view_order'));

        /* 当前用户中心菜单 */
        $this->_curitem('order_manage');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('detail'));

        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        $spec_ids = array();
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
            $spec_ids[] = $goods['spec_id'];

        }

        /* 查出最新的相应的货号 */
        $model_spec =& m('goodsspec');
        $spec_info = $model_spec->find(array(
            'conditions'    => $spec_ids,
            'fields'        => 'sku',
        ));
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            $order_detail['data']['goods_list'][$key]['sku'] = $spec_info[$goods['spec_id']]['sku'];
        }

        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->display('seller_order.view.html');
    }
    /**
     *    收到货款
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    function received_pay()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(ORDER_PENDING);
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('seller_order.received_pay.html');
        }
        else
        {
            $model_order    =&  m('order');
            $model_order->edit(intval($order_id), array('status' => ORDER_ACCEPTED, 'pay_time' => gmtime()));
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            #TODO 发邮件通知
            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_ACCEPTED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));

            /* 发送给买家邮件，提示等待安排发货 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_offline_pay_success_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_accepted'),
                'actions'   => array(
                    'cancel',
                    'shipped'
                ), //可以取消可以发货
            );

            $this->pop_warning('ok');
        }

    }
    
    /**
     *    信用认证 
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    function auth()
    {
    	list($order_id, $order_info)    = $this->_get_valid_order_info(WAIT_CREDIT_AUTH);
    	if (!$order_id)
    	{
    		echo Lang::get('no_such_order');
    
    		return;
    	}
    	if (!IS_POST)
    	{
    		header('Content-Type:text/html;charset=' . CHARSET);
    		$this->assign('order', $order_info);
    		$this->assign('title','信用审核');
    		$this->display('seller_order.auth.html');
    	}
    	else
    	{
    		$auth_result = $this->auto_auth();
    		if($auth_result === true)
    		{
    			//信用审核通过， 转为等待实地认证
    			$status = WAIT_REAL_IDENTIFY;
    			$mail_tpl = 'tobuyer_real_identify_success';
    		}
    		else if($auth_result === false)
    		{
    			$mail_tpl = 'tobuyer_real_identify_fail';
    			$status = REJECTED_REAL_IDENTIFY;
    		}
    		else
    		{
    			show_warning('Hack attcking!');
    			return;
    		}
    		
    		$model_order    =&  m('order');
    		$model_order->edit(intval($order_id), array('status' => $status));
    		if ($model_order->has_error())
    		{
    			$this->pop_warning($model_order->get_error());
    
    			return;
    		}
    
    		#TODO 发邮件通知
    		/* 记录订单操作日志 */
    		$order_log =& m('orderlog');
    		$order_log->add(array(
    		'order_id'  => $order_id,
    		'operator'  => addslashes($this->visitor->get('user_name')),
    		'order_status' => order_status($order_info['status']),
    		'changed_status' => order_status($status),
    		'remark'    => $_POST['remark'],
    		'log_time'  => gmtime(),
    		));
    
    		/* 发送给买家邮件，提醒信用审核情况 */
    		$model_member =& m('member');
    		$buyer_info   = $model_member->get($order_info['buyer_id']);
    		$mail = get_mail($mail_tpl, array('order' => $order_info,'reason' => htmlspecialchars($_POST['remark'])));
    		$this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));
    
           /*  $new_data = array(
    			'status'    => Lang::get('order_accepted'),
    			'actions'   => array('cancel','shipped'), //可以取消可以发货
    		); */
    
    		$this->pop_warning('ok');
    	}
    
    }

   
    /**
     * 信用自动审核接口 
     * 
     * @return boolean
     */
    function  auto_auth()
    {
    	$is_pass = !empty($_POST['is_pass']) ? $_POST['is_pass']: 0;
    	if($is_pass==1)
    	{
    		return true;
    	}
    	if($is_pass==2)
    	{
    		return false;
    	}
    	return '';
    }
    
    /**
     *    实地认证
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    function  identify()
    {
    	list($order_id, $order_info)    = $this->_get_valid_order_info(WAIT_REAL_IDENTIFY);
    	if (!$order_id)
    	{
    		echo Lang::get('no_such_order');
    
    		return;
    	}
    	if (!IS_POST)
    	{
    		header('Content-Type:text/html;charset=' . CHARSET);
    		$this->assign('order', $order_info);
    		$this->assign('title',Lang::get('identify_user'));
    		$this->display('seller_order.identify.html');
    	}
    	else
    	{
    		$auth_result = $this->auto_auth();
    		if($auth_result === true)
    		{
    			if(is_numeric($order_info['firstpay']))
    			{
    				//信用审核通过， 转为等待付首付
    				$status = WAIT_FIRST_PAY;
    			}
    			else
    			{
    				//信用审核通过， 转为等待发货状态
    				$status = WAIT_ORDER_SHIP;
    				$user_model = & m('member');
    				$user_model->edit($this->visitor->get('user_id'),array('is_ident'=>1));
    			}
    			//形成分期月计划表   			
    			    
    			$this->create_plan($order_info);
    			$mail_tpl = 'tobuyer_real_identify_success';
    		}
    		else if($auth_result === false)
    		{
    			$mail_tpl = 'tobuyer_real_identify_fail';
    			$status = REJECTED_REAL_IDENTIFY;
    		}
    		else
    		{
    			show_warning('Hack attcking!');
    			return;
    		}
    
    		$model_order    =&  m('order');
    		$model_order->edit(intval($order_id), array('status' => $status));
    		if ($model_order->has_error())
    		{
    			$this->pop_warning($model_order->get_error());
    
    			return;
    		}
    
    		#TODO 发邮件通知
    		/* 记录订单操作日志 */
    		$order_log =& m('orderlog');
    		$order_log->add(array(
    		'order_id'  => $order_id,
    		'operator'  => addslashes($this->visitor->get('user_name')),
    		'order_status' => order_status($order_info['status']),
    		'changed_status' => order_status($status),
    		'remark'    => $_POST['remark'],
    		'log_time'  => gmtime(),
    		 ));
    
    				/* 发送给买家邮件，提醒信用审核情况 */
    		$model_member =& m('member');
    		$buyer_info   = $model_member->get($order_info['buyer_id']);
    		$mail = get_mail($mail_tpl, array('order' => $order_info,'reason' => htmlspecialchars($_POST['remark'])));
    		$this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));
        	$this->pop_warning('ok');
    	}
    
    }
    

    /**
     *    确认收到首付款项
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    function  first_pay()
    {
    	list($order_id, $order_info)    = $this->_get_valid_order_info(WAIT_FIRST_PAY);
    	if (!$order_id)
    	{
    		echo Lang::get('no_such_order');
    
    		return;
    	}
    	if (!IS_POST)
    	{
    		header('Content-Type:text/html;charset=' . CHARSET);
    		$this->assign('order', $order_info);
    		$this->assign('title','手动确认收到首付款项');
    		$this->display('seller_order.firstpay.html');
    	}
    	else
    	{
    		$auth_result = $this->auto_auth();
    		if($auth_result === true)
    		{
    			//收到首付款项， 转为等待发货状态
    			$status = WAIT_ORDER_SHIP;    			
    			 
    			$mail_tpl = 'tobuyer_real_identify_success';
    		}
    		else if($auth_result === false)
    		{
    			
    		}
    		else
    		{
    			show_warning('Hack attcking!');
    			return;
    		}
    
    		$model_order    =&  m('order');
    		$model_order->edit(intval($order_id), array('status' => $status));
    		if ($model_order->has_error())
    		{
    			$this->pop_warning($model_order->get_error());
    
    			return;
    		}
    
    		#TODO 发邮件通知
    		/* 记录订单操作日志 */
    		$order_log =& m('orderlog');
    		$order_log->add(array(
    		'order_id'  => $order_id,
    		'operator'  => addslashes($this->visitor->get('user_name')),
    		'order_status' => order_status($order_info['status']),
    		'changed_status' => order_status($status),
    		'remark'    => $_POST['remark'],
    				'log_time'  => gmtime(),
    				));
    
    				/* 发送给买家邮件，提醒信用审核情况 */
    		$model_member =& m('member');
    		$buyer_info   = $model_member->get($order_info['buyer_id']);
    		$mail = get_mail($mail_tpl, array('order' => $order_info,'reason' => htmlspecialchars($_POST['remark'])));
    		$this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));
    				$this->pop_warning('ok');
    	}
    
    }
    
    		
    /**
     *    货到付款的订单的确认操作
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    function confirm_order()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(ORDER_SUBMITTED);
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('seller_order.confirm.html');
        }
        else
        {
            $model_order    =&  m('order');
            $model_order->edit($order_id, array('status' => ORDER_ACCEPTED));
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_ACCEPTED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));

            /* 发送给买家邮件，订单已确认，等待安排发货 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_confirm_cod_order_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_accepted'),
                'actions'   => array(
                    'cancel',
                    'shipped'
                ), //可以取消可以发货
            );

            $this->pop_warning('ok');;
        }
    }

    /**
     *    调整费用
     *
     *    @author    LorenLei
     *    @return    void
     */
    function adjust_fee()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(array(ORDER_SUBMITTED, ORDER_PENDING));
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order    =&  m('order');
        $model_orderextm =& m('orderextm');
        $shipping_info   = $model_orderextm->get($order_id);
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->assign('shipping', $shipping_info);
            $this->display('seller_order.adjust_fee.html');
        }
        else
        {
            /* 配送费用 */
            $shipping_fee = isset($_POST['shipping_fee']) ? abs(floatval($_POST['shipping_fee'])) : 0;
            /* 折扣金额 */
            $goods_amount     = isset($_POST['goods_amount'])     ? abs(floatval($_POST['goods_amount'])) : 0;
            /* 订单实际总金额 */
            $order_amount = round($goods_amount + $shipping_fee, 2);
            if ($order_amount <= 0)
            {
                /* 若商品总价＋配送费用扣队折扣小于等于0，则不是一个有效的数据 */
                $this->pop_warning('invalid_fee');

                return;
            }
            $data = array(
                'goods_amount'  => $goods_amount,    //修改商品总价
                'order_amount'  => $order_amount,     //修改订单实际总金额
                'pay_alter' => 1    //支付变更
            );

            if ($shipping_fee != $shipping_info['shipping_fee'])
            {
                /* 若运费有变，则修改运费 */

                $model_extm =& m('orderextm');
                $model_extm->edit($order_id, array('shipping_fee' => $shipping_fee));
            }
            $model_order->edit($order_id, $data);

            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }
            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status($order_info['status']),
                'remark'    => Lang::get('adjust_fee'),
                'log_time'  => gmtime(),
            ));

            /* 发送给买家邮件通知，订单金额已改变，等待付款 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_adjust_fee_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'order_amount'  => price_format($order_amount),
            );

            $this->pop_warning('ok');
        }
    }

    /**
     *    待发货的订单发货
     *
     *    @author    LorenLei
     *    @return    void
     */
    function shipped()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(WAIT_ORDER_SHIP);
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order    =&  m('order');
        if (!IS_POST)
        {
            /* 显示发货表单 */
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('seller_order.shipped.html');
        }
        else
        {
            if (!$_POST['invoice_no'])
            {
                $this->pop_warning('invoice_no_empty');

                return;
            }
            $edit_data = array('status' => PAYBACK_STABLY, 'invoice_no' => $_POST['invoice_no']);
            $is_edit = true;
            if (empty($order_info['invoice_no']))
            {
                /* 不是修改发货单号 */
                $edit_data['ship_time'] = gmtime();
                $is_edit = false;
            }
            $model_order->edit(intval($order_id), $edit_data);
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            #TODO 发邮件通知
            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(PAYBACK_STABLY),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));


            /* 发送给买家订单已发货通知 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $order_info['invoice_no'] = $edit_data['invoice_no'];
            $mail = get_mail('tobuyer_shipped_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_shipped'),
                'actions'   => array(
                    'cancel',
                    'edit_invoice_no'
                ), //可以取消可以发货
            );
            if ($order_info['payment_code'] == 'cod')
            {
                $new_data['actions'][] = 'finish';
            }

            $this->pop_warning('ok');
        }
    }
    /**
     * 查看还款记录
     */
	function view_payback() {
		echo  '查看还款记录';
	}
    /**
     *    取消订单
     *
     *    @author    LorenLei
     *    @return    void
     */
    function cancel_order()
    {
        /* 取消的和完成的订单不能再取消 */
        //list($order_id, $order_info)    = $this->_get_valid_order_info(array(ORDER_SUBMITTED, ORDER_PENDING, ORDER_ACCEPTED, ORDER_SHIPPED));
        $order_id = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
        if (!$order_id)
        {
            echo Lang::get('no_such_order');
        }
        $status = array(ORDER_SUBMITTED, ORDER_PENDING, ORDER_ACCEPTED, ORDER_SHIPPED);
        $order_ids = explode(',', $order_id);
        if ($ext)
        {
            $ext = ' AND ' . $ext;
        }

        $model_order    =&  m('order');
        /* 只有已发货的货到付款订单可以收货 */
        $order_info     = $model_order->find(array(
            'conditions'    => "order_id" . db_create_in($order_ids) . " status " . db_create_in($status) . $ext,
        ));
        $ids = array_keys($order_info);
        if (!$order_info)
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('orders', $order_info);
            $this->assign('order_id', count($ids) == 1 ? current($ids) : implode(',', $ids));
            $this->display('seller_order.cancel.html');
        }
        else
        {
            $model_order    =&  m('order');
            foreach ($ids as $val)
            {
                $id = intval($val);
                $model_order->edit($id, array('status' => ORDER_CANCELED));
                if ($model_order->has_error())
                {
                    //$_erros = $model_order->get_error();
                    //$error = current($_errors);
                    //$this->json_error(Lang::get($error['msg']));
                    //return;
                    continue;
                }

                /* 加回订单商品库存 */
                $model_member = & m('member');
                $temp_order = $model_order->get($id);               
                $model_member->auto_drop_score( $temp_order['buyer_id'],1, $temp_order['discount']);               
                $model_order->change_stock('+', $id);
                $cancel_reason = (!empty($_POST['remark'])) ? $_POST['remark'] : $_POST['cancel_reason'];
                /* 记录订单操作日志 */
                $order_log =& m('orderlog');
                $order_log->add(array(
                    'order_id'  => $id,
                    'operator'  => addslashes($this->visitor->get('user_name')),
                    'order_status' => order_status($order_info[$id]['status']),
                    'changed_status' => order_status(ORDER_CANCELED),
                    'remark'    => $cancel_reason,
                    'log_time'  => gmtime(),
                ));

                /* 发送给买家订单取消通知 */
                $model_member =& m('member');
                $buyer_info   = $model_member->get($order_info[$id]['buyer_id']);
                $mail = get_mail('tobuyer_cancel_order_notify', array('order' => $order_info[$id], 'reason' => $_POST['remark']));
                $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

                $new_data = array(
                    'status'    => Lang::get('order_canceled'),
                    'actions'   => array(), //取消订单后就不能做任何操作了
                );
            }
            $this->pop_warning('ok', 'seller_order_cancel_order');
        }

    }

    /**
     *    完成交易(货到付款的订单)
     *
     *    @author    LorenLei
     *    @return    void
     */
    function finished()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(ORDER_SHIPPED, 'payment_code=\'cod\'');
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            /* 当前用户中心菜单 */
            $this->_curitem('seller_order');
            /* 当前所处子菜单 */
            $this->_curmenu('finished');
            $this->assign('_curmenu','finished');
            $this->assign('order', $order_info);
            $this->display('seller_order.finished.html');
        }
        else
        {
			
            $now = gmtime();
            $model_order    =&  m('order');
            $model_order->edit($order_id, array('status' => ORDER_FINISHED, 'pay_time' => $now, 'finished_time' => $now));
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_FINISHED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));

            /* 更新累计销售件数 */
            $model_goodsstatistics =& m('goodsstatistics');
            $model_ordergoods =& m('ordergoods');
            $order_goods = $model_ordergoods->find("order_id={$order_id}");
            foreach ($order_goods as $goods)
            {
                $model_goodsstatistics->edit($goods['goods_id'], "sales=sales+{$goods['quantity']}");
            }
            /*给用户加积分*/
			//$model_member = & m('member');
            //$model_member->auto_add_score($order_info['buyer_id'],1,$order_info['goods_amount']);           
            
            /* 发送给买家交易完成通知，提示评论 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_cod_order_finish_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_finished'),
                'actions'   => array(), //完成订单后就不能做任何操作了
            );

            $this->pop_warning('ok');
        }

    }

    /**
     *    获取有效的订单信息
     *
     *    @author    LorenLei
     *    @param     array $status
     *    @param     string $ext
     *    @return    array
     */
    function _get_valid_order_info($status, $ext = '')
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {

            return array();
        }
        if (!is_array($status))
        {
            $status = array($status);
        }

        if ($ext)
        {
            $ext = ' AND ' . $ext;
        }

        $model_order    =&  m('order');
        /* 只有已发货的货到付款订单可以收货 */
        $order_info     = $model_order->get(array(
            'conditions'    => "order_id={$order_id}"." AND status " . db_create_in($status) . $ext,
        ));
        if (empty($order_info))
        {

            return array();
        }

        return array($order_id, $order_info);
    }
    /**
     * 形成分期月计划表
     */
    function create_plan($order_info) {
    	//list($order_id, $order_info)    = $this->_get_valid_order_info(WAIT_REAL_IDENTIFY);
    	
    	if($order_info){
    		$payback_model = & m('payback');
    		$log_model = & m('payback_log');
    	    $data = array( 
				'pay_time' => '0', 
				'total' =>  $order_info['mon_fee'],
				'user_id' => $order_info['buyer_id'],
				'order_id' => $order_info['order_id'], 
				'payment_id' => '0',
				'belong_month' => '0', 
				'capital' => $order_info['capital'],
				'service_fee' => $order_info['mon_service'], 
				'stay_money' => '0.00', 
				'status' => NO_PAYBACK, 
				'end_time' => '0',
				'start_time' => time(), 
				'user_name' => $order_info['buyer_name'], 
				'log_id' => '0', 
			);
    		
    	    $payback_model->start();
    		$payback_model->db->no_limit(); 
    		for($i=0; $i<$order_info['current_month']; $i++){
    			$data['belong_month'] = 1+ $i;
    			$data['end_time'] = strtotime('+ '.$data['belong_month'].' month');
    			$data['end_time'] = strtotime('+ 5 day',$data['end_time']);
    			$data['start_time'] = strtotime('- 5 day',strtotime('- 1 month',$data['end_time']));
    			$payback_model->add(addslashes_deep($data));
    		} 
    		$payback_model->db->commit();     	  
    		
    	}	
    }
    
    function test() {
    	
    	$mod = & m('payback');
    
    	$row = $mod->get_info(1);
    	var_export($row);
    }
    /**
     *    获取订单列表
     *
     *    @author    LorenLei
     *    @return    void
     */
    function _get_orders()
    {
        $page = $this->_get_page();
        $model_order =& m('order');
        !$_GET['type'] && $_GET['type'] = 'all_orders';

        $conditions = '';
        $conditions .= $this->_get_query_conditions(array(
            array(      //按订单状态搜索
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //按买家名称搜索
                'field' => 'buyer_name',
                'equal' => 'LIKE',
            ),
            array(      //按下单时间搜索,起始时间
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),
            array(      //按下单时间搜索,结束时间
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'=> 'gmstr2time_end',
            ),
            array(      //按订单号
                'field' => 'order_sn',
            ),
        ));
             
        /* 查找订单 */
        $orders = $model_order->findAll(array(
            'conditions'    => " 1=1 {$conditions}",
            'count'         => true,
            'join'          => 'has_orderextm',
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
        ));
       
		$member_mod =& m('member');
        $model_spec =& m('goodsspec');
		
        foreach ($orders as $key1 => $order)
        {
        	if(isset($order['order_goods'])){
        		foreach ($order['order_goods'] as $key2 => $goods)
        		{
        			empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
        		
        			$spec = $model_spec->get(array('conditions'=>'spec_id='.$goods['spec_id'],'fields'=>'sku'));
        			$orders[$key1]['order_goods'][$key2]['sku'] = $spec['sku'];
        		}
        		// psmb
        		$orders[$key1]['goods_quantities'] = count($order['order_goods']);
        		$orders[$key1]['buyer_info'] = $member_mod->get(array('conditions'=>'user_id='.$order['buyer_id'],'fields'=>'real_name,im_qq,im_aliww,im_msn'));
        		
        	}
        }
        $count = count($orders);
        $_COOKIE['now']=$count;

        $page['item_count'] = $model_order->getCount();
        $this->_format_page($page);
        $this->assign('types', array('all' => Lang::get('all_orders'),
                                     'pending' => Lang::get('pending_orders'),
                                     'submitted' => Lang::get('submitted_orders'),
                                     'accepted' => Lang::get('accepted_orders'),
                                     'shipped' => Lang::get('shipped_orders'),
                                     'finished' => Lang::get('finished_orders'),
                                     'canceled' => Lang::get('canceled_orders')));
        $this->assign('type', $_GET['type']);

        $this->assign('orders', $orders);
        $this->assign('page_info', $page);
    }
    /**
     *    获取订单列表
     *
     *    @author    LorenLei
     *    @return    void
     */
    function ajax_get_count_of_order()
    {
        $page = $this->_get_page();
        $model_order =& m('order');

        !$_GET['type'] && $_GET['type'] = 'all_orders';

        $conditions = '';

        // 团购订单
        if (!empty($_GET['group_id']) && intval($_GET['group_id']) > 0)
        {
            $groupbuy_mod = &m('groupbuy');
            $order_ids = $groupbuy_mod->get_order_ids(intval($_GET['group_id']));
            $order_ids && $conditions .= ' AND order_alias.order_id' . db_create_in($order_ids);
        }

        $conditions .= $this->_get_query_conditions(array(
            array(      //按订单状态搜索
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //按买家名称搜索
                'field' => 'buyer_name',
                'equal' => 'LIKE',
            ),
            array(      //按下单时间搜索,起始时间
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),
            array(      //按下单时间搜索,结束时间
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'=> 'gmstr2time_end',
            ),
            array(      //按订单号
                'field' => 'order_sn',
            ),
        ));
        //echo $conditions;
        /* 查找订单 */

        $orders = $model_order->findAll(array(
            'conditions'    => "{$conditions}",
            'count'         => true,
            'join'          => 'has_orderextm',
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
        ));

        // psmb
        $member_mod =& m('member');
        $model_spec =& m('goodsspec');

        foreach ($orders as $key1 => $order)
        {
            foreach ($order['order_goods'] as $key2 => $goods)
            {
                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');

                $spec = $model_spec->get(array('conditions'=>'spec_id='.$goods['spec_id'],'fields'=>'sku'));
                $orders[$key1]['order_goods'][$key2]['sku'] = $spec['sku'];
            }
            // psmb
            $orders[$key1]['goods_quantities'] = count($order['order_goods']);
            $orders[$key1]['buyer_info'] = $member_mod->get(array('conditions'=>'user_id='.$order['buyer_id'],'fields'=>'real_name,im_qq,im_aliww,im_msn'));
        }

        $page['item_count'] = $model_order->getCount();
        $this->_format_page($page);
        $this->assign('types', array('all' => Lang::get('all_orders'),
            'pending' => Lang::get('pending_orders'),
            'submitted' => Lang::get('submitted_orders'),
            'accepted' => Lang::get('accepted_orders'),
            'shipped' => Lang::get('shipped_orders'),
            'finished' => Lang::get('finished_orders'),
            'canceled' => Lang::get('canceled_orders')));

        //setcookie('before'.$this->visitor->check_do_action())=;
        //$count = count($orders);
       // $_COOKIE['count']=$count;


        echo json_encode(array('succ'=>1,'count'=>count($orders)));




    }


    /*三级菜单*/
    function _get_member_submenu()
    {
        $array = array(
            array(
                'name' => 'all_orders',
                'url' => 'index.php?app=seller_order&amp;type=all_orders',
            ),
            array(
                'name' => 'auth',//待信用认证
                'url' => 'index.php?app=seller_order&amp;type=auth',
            ),
            array(
                'name' => 'identify',
                'url' => 'index.php?app=seller_order&amp;type=identify',
            ),
            array(
                'name' => 'first_pay',
                'url' => 'index.php?app=seller_order&amp;type=first_pay',
            ),
            array(
                'name' => 'ship',
                'url' => 'index.php?app=seller_order&amp;type=ship',
            ),
           /*  array(
                'name' => 'receive',
                'url' => 'index.php?app=seller_order&amp;type=receive',
            ), */
        	array(
        		'name' => 'payback_stably',
        		'url' => 'index.php?app=seller_order&amp;type=payback_stably',
        	),
        	array(
        		'name' => 'debt',
        		'url' => 'index.php?app=seller_order&amp;type=debt',
        	),
        	array(
        		'name' => 'finished',
        		'url' => 'index.php?app=seller_order&amp;type=finished',
        	),
            array(
                'name' => 'canceled',
                'url' => 'index.php?app=seller_order&amp;type=canceled',
        ),
        );
        return $array;
    }
}

?>
