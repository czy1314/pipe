<?php
return array (
  'version' => '1.0',
  'subject' => '{$site_name}提醒:您的订单{$order.order_sn}{if has_ident}已经审核通过{else}没有审核通过/(ㄒoㄒ)/~~{/if}',
  'content' => '<p>尊敬的{$order.buyer_name}:</p>
<p style="padding-left: 30px;">{$msg} {/if}</p>
<p style="padding-left: 30px;">查看订单详细信息请点击以下链接</p>
<p style="padding-left: 30px;"><a href="{$site_url}/index.php?app=buyer_order&amp;act=view&amp;order_id={$order.order_id}">{$site_url}/index.php?app=buyer_order&amp;act=view&amp;order_id={$order.order_id}</a></p>
<p style="text-align: right;">{$site_name}</p>
<p style="text-align: right;">{$mail_send_time}</p>',
);
?>