<?php

/**
 *    自动交易
 *
 *    @author    LorenLei
 *    @usage    none
 */
class CleanupTask extends BaseTask
{
    function run()
    {
    	
        /* 自动确认收货 */
        //$this->_auto_confirm();

        /* 自动好评 */
        //$this->_auto_evaluate();

    }

    /**
     *    自动确认指定时间后未确认收货的订单
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    function _auto_confirm()
    {
        $now = gmtime();
        /* 默认15天 */
        $interval = empty($this->_config['confirm_interval']) ? 15 * 24 * 3600 : intval($this->_config['confirm_interval']);
        $model_order =& m('order');

        /* 确认收货 */
        /* 款到发货的订单 */
       /*  $orders = $model_order->find(array(
            'fields'    => 'order_id',
            'conditions'=> "ship_time + {$interval} < {$now} AND status = " . ORDER_SHIPPED,
        )); */
        /* 货到付款的订单 */
      /*   $cod_orders = $model_order->find(array(
            'fields'    => 'order_id',
            'conditions'=> "ship_time + {$interval} < {$now} AND status =" . ORDER_SHIPPED . ' AND payment_code=\'cod\'',
        )); */

        if (empty($orders) && empty($cod_orders))
        {
            return;
        }

        /* 操作日志 */
        $order_logs = array();
        $order_shipped = order_status(ORDER_SHIPPED);
        $order_finished= order_status(ORDER_FINISHED);

        /* 款到发货的订单 */
        if (!empty($orders))
        {
            /* 更新订单状态 */
            $model_order->edit(array_keys($orders), array('status' => ORDER_FINISHED, 'finished_time' => gmtime()));

            /* 更新商品统计 */
            $model_goodsstatistics =& m('goodsstatistics');
            $model_ordergoods =& m('ordergoods');
            $order_goods = $model_ordergoods->find('order_id ' . db_create_in(array_keys($orders)));

            $tmp1 = $tmp2 = array();
            foreach ($order_goods as $goods)
            {
                $tmp1[$goods['goods_id']] += $goods['quantity'];
            }
            foreach ($tmp1 as $_goods_id => $_quantity)
            {
                $tmp2[$_quantity][] = $_goods_id;
            }
            foreach ($tmp2 as $_quantity => $_goods_ids)
            {
                $model_goodsstatistics->edit($_goods_ids, "sales=sales+{$_quantity}");
            }

            /* 操作记录 */
            foreach ($orders as $order_id => $order)
            {
                $order_logs[] = array(
                    'order_id'  => $order_id,
                    'operator'  => '0',
                    'order_status' => $order_shipped,
                    'changed_status' => $order_finished,
                    'remark'    => '',
                    'log_time'  => $now,
                );
            }
        }

        /* 货到付款的订单 */
        if (!empty($cod_orders))
        {
            /* 修改订单状态 */
            $model_order->edit(array_keys($cod_orders), array(
                'status' => ORDER_FINISHED,
                'pay_time' => $now,
                'finished_time' => $now
            ));

            /* 操作记录 */
            foreach ($cod_orders as $order_id => $order)
            {
                $order_logs[] = array(
                    'order_id'  => $order_id,
                    'operator'  => '0',
                    'order_status' => $order_shipped,
                    'changed_status' => $order_finished,
                    'remark'    => '',
                    'log_time'  => $now,
                );
            }
        }

        $order_log =& m('orderlog');
        $order_log->add($order_logs);
    }

    function _auto_evaluate()
    {
        $now = gmtime();

        /* 默认30天未评价自动好评 */
        $interval = empty($this->_config['evaluate_interval']) ? 30 * 24 * 3600 : intval($this->_config['evaluate_interval']);
        $goods_evaluation = array(
            'evaluation'    => 3,
            'comment'       => '',
            'credit_value'  => 1
        );

        /* 获取满足条件的订单 */
        $model_order =& m('order');

        /* 指定时间后已确认收货的未评价的 */
        $orders = $model_order->find(array(
            'conditions'    => "finished_time + {$interval} < {$now} AND evaluation_status = 0 AND status = " . ORDER_FINISHED,
            'fields'        => 'order_id, seller_id',
        ));

        /* 没有满足条件的订单 */
        if (empty($orders))
        {
            return;
        }

        $order_ids = array_keys($orders);

        /* 获取待评价的商品列表 */
        $model_ordergoods =& m('ordergoods');
        $order_goods = $model_ordergoods->find(array(
            'conditions'    => 'order_id ' . db_create_in($order_ids),
            'fields'        => 'rec_id, goods_id',
        ));

        /* 自动好评 */
        $model_ordergoods->edit(array_keys($order_goods), $goods_evaluation);
        $model_order->edit($order_ids, array(
                'evaluation_status' => 1,
                'evaluation_time'   => gmtime()
        ));

        $model_store =& m('store');

        /* 因为地区加盟商ID有可能重复，因此 */
        $sellers = array();
        foreach ($orders as $order_id => $order)
        {
            $sellers[$order['seller_id']] = $order['seller_id'];
        }
        foreach ($sellers as $seller_id)
        {
            $model_store->edit($seller_id, array(
                'credit_value'  =>  $model_store->recount_credit_value($seller_id),
                'praise_rate'   =>  $model_store->recount_praise_rate($seller_id)
            ));
        }

        /* 因为商品ID有可能重复，因此 */
        $comments = array();
        foreach ($order_goods as $rec_id => $og)
        {
            $comments[$og['goods_id']]++;
        }
        $edit_comments = array();
        foreach ($comments as $og_id => $t)
        {
            $edit_comments[$t][] = $og_id;
        }

        $model_goodsstatistics =& m('goodsstatistics');
        foreach ($edit_comments as $times => $goods_ids)
        {
            $model_goodsstatistics->edit($goods_ids, 'comments=comments+' . $times);
        }
    }

    


}

?>
