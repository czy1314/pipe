<?php
return array (
  'version' => '1.0',
  'subject' => '{$site_name}{$title}',
  'content' => '<p style="padding-left: 30px;">{$content}</p>
  <p style="padding-left: 30px;">查看详细信息请点击以下链接</p>
  <p style="padding-left: 30px;"><a href="{$url}">{$url}</a></p>
  <p style="text-align: right;">{$site_name}</p>
  <p style="text-align: right;">{$mail_send_time}</p>',
);
?>